<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Report_sales_dispatch_forecast extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('crud');
	}

	public function index()
	{
		if (empty($this->session->username)) {
			redirect('error_session');
		} elseif ($this->checkuserAccess($this->id_menu()) > 0) {
			$data['button'] = $this->getbutton($this->id_menu());
			$this->load->view('template/header', $data);
			$this->load->view('sales/report_sales_dispatch_forecast');
		} else {
			redirect('error_access');
		}
	}

	public function readCustomers()
	{
		$post = isset($_POST['q']) ? $_POST['q'] : "";
		$this->db->select('id, number, name');
		$this->db->from('customers');
		$this->db->where('status', 0);
		if (!empty($post)) {
			$this->db->group_start();
			$this->db->like('number', $post);
			$this->db->or_like('name', $post);
			$this->db->group_end();
		}
		$this->db->order_by('name', 'ASC');
		$customers = $this->db->get()->result_array();
		echo json_encode($customers);
	}

	public function readPlants()
	{
		$customer_id = $this->input->get('customer_id');
		$post = isset($_POST['q']) ? $_POST['q'] : "";

		$this->db->select("DISTINCT(plant) as id, plant as name");
		$this->db->from('customer_address');
		$this->db->where("plant IS NOT NULL");
		$this->db->where("plant != ''");
		if (!empty($customer_id)) {
			$this->db->where('customer_id', $customer_id);
		}
		if (!empty($post)) {
			$this->db->like('plant', $post);
		}
		$this->db->order_by('plant', 'ASC');
		$plants = $this->db->get()->result_array();
		echo json_encode($plants);
	}

	public function print($option = "")
	{
		if ($option == "excel") {
			$format = date("Ymd");
			header("Content-type: application/vnd-ms-excel");
			header("Content-Disposition: attachment; filename=sales_order_vs_dispatch_forecast_$format.xls");
		}

		$raw_from = $this->input->get("filter_from");
		$raw_to = $this->input->get("filter_to");
		$filter_from = !empty($raw_from) ? (base64_decode($raw_from, true) ? base64_decode($raw_from) : $raw_from) : date("Y-m-01");
		$filter_to = !empty($raw_to) ? (base64_decode($raw_to, true) ? base64_decode($raw_to) : $raw_to) : date("Y-m-t");

		$filter_customer = $this->input->get("filter_customer");
		$filter_plant = $this->input->get("filter_plant");

		// Config perusahaan
		$this->db->select('*');
		$this->db->from('config');
		$config = $this->db->get()->row();

		$filter_year = date("Y", strtotime($filter_from));
		$filter_month = date("m", strtotime($filter_from));

		// Customer and plant labels
		$customer_label = "ALL";
		if (!empty($filter_customer)) {
			$cust_row = $this->crud->read('customers', [], ['id' => $filter_customer]);
			if (!empty($cust_row)) {
				$customer_label = $cust_row->name . " (" . $cust_row->number . ")";
			}
		}

		$plant_label = !empty($filter_plant) ? $filter_plant : "ALL";

		// Query Rekonsiliasi Terpadu
		$sql = "SELECT 
					c.id AS customer_id,
					c.number AS customer_code,
					c.name AS customer_name,
					COALESCE(ca.plant, so.plant, '-') AS cust_plant_name,
					fg.id AS item_fg_id,
					fg.number AS item_code,
					COALESCE(NULLIF(fg.number_customer, ''), fg.name) AS customer_item_code,
					fg.name AS item_fg_name,
					COALESCE(fc.forecast_qty, 0) AS forecast_qty,
					COALESCE(so.so_qty, 0) AS sales_order_qty,
					COALESCE(dn.dispatched_qty, 0) AS dispatched_qty
				FROM item_fg fg
				JOIN customers c ON fg.customer_id = c.id
				LEFT JOIN (
					SELECT 
						item_fg_id, 
						customer_id, 
						customer_address_id,
						plant, 
						SUM(qty) AS so_qty
					FROM sales_orders
					WHERE sales_order_date BETWEEN '$filter_from' AND '$filter_to'
					  AND customer_id LIKE '%$filter_customer%'
					GROUP BY item_fg_id, customer_id, plant
				) so ON fg.id = so.item_fg_id AND c.id = so.customer_id
				LEFT JOIN (
					SELECT 
						item_fg_id, 
						customer_id, 
						plant, 
						SUM(qty) AS dispatched_qty
					FROM delivery_notes
					WHERE status != 2
					  AND delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
					  AND customer_id LIKE '%$filter_customer%'
					GROUP BY item_fg_id, customer_id, plant
				) dn ON fg.id = dn.item_fg_id AND c.id = dn.customer_id AND (so.plant = dn.plant OR (so.plant IS NULL AND dn.plant IS NULL))
				LEFT JOIN (
					SELECT 
						item_fg_id, 
						customer_id, 
						plant, 
						SUM(month_1) AS forecast_qty
					FROM forecasts
					WHERE deleted = 0
					  AND ((p_year = '$filter_year' AND p_month = '$filter_month') OR (issued_date BETWEEN '$filter_from' AND '$filter_to'))
					  AND customer_id LIKE '%$filter_customer%'
					GROUP BY item_fg_id, customer_id, plant
				) fc ON fg.id = fc.item_fg_id AND c.id = fc.customer_id AND (so.plant = fc.plant OR (so.plant IS NULL AND fc.plant IS NULL))
				LEFT JOIN customer_address ca ON so.customer_address_id = ca.id OR (so.customer_id = ca.customer_id AND so.plant = ca.plant)
				WHERE (COALESCE(so.so_qty, 0) > 0 OR COALESCE(fc.forecast_qty, 0) > 0 OR COALESCE(dn.dispatched_qty, 0) > 0)
				  AND (COALESCE(ca.plant, so.plant, '') LIKE '%$filter_plant%' OR '$filter_plant' = '')
				ORDER BY c.name ASC, cust_plant_name ASC, fg.number ASC";

		$records = $this->crud->query($sql);

		// Perhitungan Akumulasi KPI
		$total_forecast_qty = 0;
		$total_so_qty = 0;
		$total_dispatched_qty = 0;
		$total_pending_qty = 0;

		$processed_rows = array();

		foreach ($records as $row) {
			$f_qty = (float) $row->forecast_qty;
			$s_qty = (float) $row->sales_order_qty;
			$d_qty = (float) $row->dispatched_qty;

			$variance = $s_qty - $f_qty;

			// Forecast Accuracy %: max(0, 1 - |B - A| / A) * 100%
			if ($f_qty > 0) {
				$accuracy = max(0, (1 - (abs($s_qty - $f_qty) / $f_qty))) * 100;
			} else {
				$accuracy = ($s_qty == 0) ? 100.0 : 0.0;
			}

			// Pending Qty (Backlog): B - C
			$pending = max(0, $s_qty - $d_qty);

			// Fulfillment Rate %: (C / B) * 100%
			if ($s_qty > 0) {
				$fulfillment = ($d_qty / $s_qty) * 100;
			} else {
				$fulfillment = ($d_qty > 0) ? 100.0 : 100.0;
			}

			$total_forecast_qty += $f_qty;
			$total_so_qty += $s_qty;
			$total_dispatched_qty += $d_qty;
			$total_pending_qty += $pending;

			$processed_rows[] = array(
				'customer_code' => $row->customer_code,
				'customer_name' => $row->customer_name,
				'cust_plant_name' => $row->cust_plant_name,
				'item_code' => $row->item_code,
				'customer_item_code' => $row->customer_item_code,
				'forecast_qty' => $f_qty,
				'sales_order_qty' => $s_qty,
				'forecast_variance' => $variance,
				'forecast_accuracy' => $accuracy,
				'dispatched_qty' => $d_qty,
				'pending_qty' => $pending,
				'fulfillment_rate' => $fulfillment
			);
		}

		// Total Variance
		$total_variance = $total_so_qty - $total_forecast_qty;

		// Total Forecast Accuracy %
		if ($total_forecast_qty > 0) {
			$total_accuracy = max(0, (1 - (abs($total_so_qty - $total_forecast_qty) / $total_forecast_qty))) * 100;
		} else {
			$total_accuracy = ($total_so_qty == 0) ? 100.0 : 0.0;
		}

		// Overall Fulfillment %
		if ($total_so_qty > 0) {
			$overall_fulfillment = ($total_dispatched_qty / $total_so_qty) * 100;
		} else {
			$overall_fulfillment = ($total_dispatched_qty > 0) ? 100.0 : 100.0;
		}

		// Mulai render HTML
		$html = '<!DOCTYPE html>
		<html>
		<head>
			<title>Sales Order vs Dispatch & Forecast Analysis Report</title>
			<style>
				body {
					font-family: Arial, Helvetica, sans-serif;
					font-size: 11px;
					color: #333;
					margin: 10px;
					background: #fff;
				}
				.header-table {
					width: 100%;
					border-collapse: collapse;
					margin-bottom: 8px;
				}
				.header-table td {
					padding: 2px 5px;
					vertical-align: middle;
				}
				.report-title-banner {
					background-color: #1F4E78;
					color: #ffffff;
					text-align: center;
					font-size: 16px;
					font-weight: bold;
					padding: 8px;
					letter-spacing: 0.5px;
					margin-bottom: 12px;
					border-radius: 2px;
				}
				.filter-box {
					width: 100%;
					border-collapse: collapse;
					margin-bottom: 12px;
					font-size: 11px;
				}
				.filter-box th {
					background: #E8EEF5;
					color: #1F4E78;
					text-align: left;
					padding: 4px 8px;
					font-weight: bold;
					border: 1px solid #D2DEEA;
				}
				.filter-box td {
					background: #FAFCFE;
					padding: 4px 8px;
					border: 1px solid #D2DEEA;
				}
				.kpi-container {
					width: 100%;
					border-collapse: collapse;
					margin-bottom: 15px;
				}
				.kpi-card {
					border: 1px solid #B4C6E7;
					background: #F2F5F9;
					text-align: center;
					padding: 6px 10px;
					width: 20%;
				}
				.kpi-label {
					font-size: 10px;
					font-weight: bold;
					color: #44546A;
					text-transform: uppercase;
					margin-bottom: 4px;
				}
				.kpi-value {
					font-size: 17px;
					font-weight: bold;
					color: #1F4E78;
				}
				.data-table {
					border-collapse: collapse;
					width: 100%;
					font-size: 11px;
				}
				.data-table th {
					background-color: #1F4E78;
					color: #ffffff;
					border: 1px solid #16365C;
					padding: 6px 4px;
					text-align: center;
					font-weight: bold;
				}
				.data-table td {
					border: 1px solid #d9d9d9;
					padding: 4px 5px;
				}
				.data-table tr:nth-child(even) {
					background-color: #F9FAFB;
				}
				.data-table tr:hover {
					background-color: #EBF1F5;
				}
				.total-row td {
					background-color: #D9E1F2 !important;
					font-weight: bold;
					border-top: 2px solid #8EA9DB;
					border-bottom: 3px double #1F4E78;
				}
				.text-center { text-align: center; }
				.text-left { text-align: left; }
				.text-right { text-align: right; }
				.text-danger { color: #C00000; font-weight: bold; }
				.text-success { color: #385723; }
			</style>
		</head>
		<body>
			<table class="header-table">
				<tr>
					<td width="50" style="text-align: center;">
						<img src="' . base_url($config->favicon) . '" width="36" onerror="this.style.display=\'none\'">
					</td>
					<td style="text-align: left;">
						<span style="font-size: 15px; font-weight: bold; color: #1F4E78;">' . $config->name . '</span><br>
						<span style="font-size: 11px; color: #555;">' . $config->description . '</span>
					</td>
					<td style="text-align: right; font-size: 10px; color: #666;">
						Print Date: ' . date("d M Y H:i:s") . '<br>
						Print By: ' . $this->session->username . '
					</td>
				</tr>
			</table>

			<div class="report-title-banner">
				SALES ORDER VS DISPATCH &amp; FORECAST ANALYSIS REPORT
			</div>

			<table class="filter-box">
				<tr>
					<th width="12%">Start Date:</th>
					<td width="18%">' . $filter_from . '</td>
					<th width="12%">End Date:</th>
					<td width="18%">' . $filter_to . '</td>
					<th width="12%">Customer:</th>
					<td width="15%">' . $customer_label . '</td>
					<th width="12%">Plant Filter:</th>
					<td width="15%">' . $plant_label . '</td>
				</tr>
			</table>

			<table class="kpi-container">
				<tr>
					<td class="kpi-card">
						<div class="kpi-label">TOTAL FORECAST QTY</div>
						<div class="kpi-value">' . number_format($total_forecast_qty, 0) . '</div>
					</td>
					<td class="kpi-card">
						<div class="kpi-label">TOTAL SALES ORDER QTY</div>
						<div class="kpi-value">' . number_format($total_so_qty, 0) . '</div>
					</td>
					<td class="kpi-card">
						<div class="kpi-label">TOTAL DISPATCHED QTY</div>
						<div class="kpi-value">' . number_format($total_dispatched_qty, 0) . '</div>
					</td>
					<td class="kpi-card">
						<div class="kpi-label">TOTAL PENDING (BACKLOG)</div>
						<div class="kpi-value ' . ($total_pending_qty > 0 ? 'text-danger' : '') . '">' . number_format($total_pending_qty, 0) . '</div>
					</td>
					<td class="kpi-card">
						<div class="kpi-label">OVERALL FULFILLMENT %</div>
						<div class="kpi-value">' . number_format($overall_fulfillment, 1) . '%</div>
					</td>
				</tr>
			</table>

			<table class="data-table">
				<thead>
					<tr>
						<th width="85">Customer<br>Code</th>
						<th>Customer Name</th>
						<th>Cust Plant Name</th>
						<th width="90">Item Code</th>
						<th>Customer Item Code</th>
						<th width="75">Forecast<br>Qty (A)</th>
						<th width="80">Sales Order<br>Qty (B)</th>
						<th width="80">Forecast<br>Variance (B-A)</th>
						<th width="75">Forecast<br>Accuracy %</th>
						<th width="80">Dispatched<br>Qty (C)</th>
						<th width="75">Pending Qty<br>(B - C)</th>
						<th width="75">Fulfillment<br>Rate %</th>
					</tr>
				</thead>
				<tbody>';

		if (empty($processed_rows)) {
			$html .= '<tr><td colspan="12" class="text-center" style="padding: 20px; color: #888;">No Data Found for Selected Period &amp; Parameters.</td></tr>';
		} else {
			foreach ($processed_rows as $item) {
				$var_class = ($item['forecast_variance'] < 0) ? 'text-danger' : 'text-success';
				$pen_class = ($item['pending_qty'] > 0) ? 'text-danger' : '';

				$html .= '<tr>
					<td class="text-center" style="mso-number-format:\@;">' . $item['customer_code'] . '</td>
					<td class="text-left">' . $item['customer_name'] . '</td>
					<td class="text-left">' . $item['cust_plant_name'] . '</td>
					<td class="text-center" style="mso-number-format:\@;">' . $item['item_code'] . '</td>
					<td class="text-left">' . $item['customer_item_code'] . '</td>
					<td class="text-right">' . number_format($item['forecast_qty'], 0) . '</td>
					<td class="text-right">' . number_format($item['sales_order_qty'], 0) . '</td>
					<td class="text-right ' . $var_class . '">' . number_format($item['forecast_variance'], 0) . '</td>
					<td class="text-right">' . number_format($item['forecast_accuracy'], 1) . '%</td>
					<td class="text-right">' . number_format($item['dispatched_qty'], 0) . '</td>
					<td class="text-right ' . $pen_class . '">' . number_format($item['pending_qty'], 0) . '</td>
					<td class="text-right">' . number_format($item['fulfillment_rate'], 1) . '%</td>
				</tr>';
			}
		}

		$total_var_class = ($total_variance < 0) ? 'text-danger' : 'text-success';
		$total_pen_class = ($total_pending_qty > 0) ? 'text-danger' : '';

		$html .= '</tbody>
				<tfoot>
					<tr class="total-row">
						<td colspan="5" class="text-left" style="padding-left: 10px;">Total / Overall</td>
						<td class="text-right">' . number_format($total_forecast_qty, 0) . '</td>
						<td class="text-right">' . number_format($total_so_qty, 0) . '</td>
						<td class="text-right ' . $total_var_class . '">' . number_format($total_variance, 0) . '</td>
						<td class="text-right">' . number_format($total_accuracy, 1) . '%</td>
						<td class="text-right">' . number_format($total_dispatched_qty, 0) . '</td>
						<td class="text-right ' . $total_pen_class . '">' . number_format($total_pending_qty, 0) . '</td>
						<td class="text-right">' . number_format($overall_fulfillment, 1) . '%</td>
					</tr>
				</tfoot>
			</table>
		</body>
		</html>';

		echo $html;
	}
}
