<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_rgp extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/report_history_transactions_rgp');
        } else {
            redirect('error_access');
        }
    }

    public function readDocument()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $process_type = $this->input->get('process_type');
        if (!empty($process_type)) {
            $process = " and process_type='$process_type'";
        }
        $send = $this->crud->query("
        select number from rgp_transactions where number LIKE '%$post%' $process group by number");
        echo json_encode($send);
    }

    public function readItemCategory($item_category = "")
    {

        $trans_date = date("Y-m-d");
        //master/bom
        //planning/where_used
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        if ($item_category != 'C08') { // bukan finish good
            $send = $this->crud->query("SELECT a.*,b.name as item_family_name
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN item_categories c ON a.item_category_id = c.id
            WHERE a.status = 0 and c.id = '$item_category'
            and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')");
        } else {
            $send = $this->crud->query("SELECT a.number as id, a.number_customer as number, a.name, a.uom
            FROM item_fg a 
            WHERE a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%'");
        }
        echo json_encode($send);
    }

    public function readEndingStock()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $trans_date = @$this->input->post('trans_date');

            if (@$trans_date == "") {
                $date = date("Y-m-d");
            } else {
                $date = $trans_date;
            }

            $records = $this->crud->query("SELECT
                a.id,
                a.number, 
                a.name, 
                b.name as prodfam, 
                a.uom, 
                COALESCE(0,0) as begin_stock,
                (COALESCE(SUM(c.qty_sto),0) + COALESCE(d.sto_qty_in, 0) + COALESCE(e.qty_receipt, 0)) as qty_in,
                (f.qty + COALESCE(d.sto_qty_out, 0) + COALESCE(h.qty_reject, 0)) as qty_out,
                ((COALESCE(SUM(c.qty_sto),0) + COALESCE(e.qty_receipt, 0)  + COALESCE(d.sto_qty_in, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(h.qty_reject, 0)) as end_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, (CASE WHEN a.type = 'IN' THEN COALESCE(SUM(a.qty), 0) ELSE 0 END) as sto_qty_in, (CASE WHEN a.type = 'OUT' THEN COALESCE(SUM(a.qty), 0) ELSE 0 END) as sto_qty_out FROM sto_rm a WHERE a.trans_date < '$date' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date <= '$date' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date <= '$date'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.id = '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

            echo json_encode($records);
        }
    }

    public function readBalanceWip()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");

            echo json_encode($wip_balances);
        }
    }

    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        $this->db->where('item_category_id', 'C01');
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

        if ($filter_item_family != "") {
            $whereProdfam = "and j.id = '$filter_item_family'";
        } else {
            $whereProdfam = "";
        }

        if ($filter_item_category != "") {
            $whereCategory = "and a.item_category_id = '$filter_item_category'";
        } else {
            $whereCategory = "";
        }


        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $item_fg = $this->crud->read('item_fg', [], ["number" => $filter_items]);
        $item_rm = $this->crud->read('item_rm', [], ["id" => $filter_items]);

        if (!empty(@$item_fg->id)) {
            $filter_items = @$item_fg->id;
        } else {
            $filter_items = @$item_rm->id;
        }

        $records = $this->crud->query("SELECT
		(CASE 
                WHEN g.id is not null THEN g.id
                ELSE h.number
        END) as item_id,
        g.id as item_id_rm,
        h.id as item_id_fg,
		a.uom,
		COALESCE(d.begin_stock,0) as begin_stock,
		COALESCE(d.begin_value,0) as begin_value,
		COALESCE(e.qty_in,0) as qty_in,
		COALESCE(e.value_in,0) as value_in,
		COALESCE(f.qty_out,0) as qty_out,
		COALESCE(f.value_out,0) as value_out,
		COALESCE(d.begin_stock,0) + COALESCE(e.qty_in,0) - COALESCE(f.qty_out,0) as ending_stock,
		COALESCE(d.begin_value,0)  + COALESCE(e.value_in,0) - COALESCE(f.value_out,0) as ending_value,
		(CASE 
				WHEN g.id is not null THEN g.number
				ELSE h.number_customer
		END) as number,
		(CASE 
				WHEN g.id is not null THEN g.name
				ELSE h.name
		END) as name,
		i.name as category_name,
		j.name as family_name 
        FROM (
                    select id,item_category_id,item_family_id,uom from item_rm 
                    union 
                    select id, 'C08' as item_category_id, null as item_family_id,uom from item_fg) a
        LEFT JOIN item_familys b on a.item_family_id = b.id
        LEFT JOIN item_categories c on a.item_category_id = c.id
        LEFT JOIN (select item_id, sum(qty) as begin_stock, sum(value) as begin_value from rgp_transactions where transaction_date < '" . $filter_from . "' group by item_id) d on a.id=d.item_id
        LEFT JOIN (select item_id, sum(qty) as qty_in, sum(value) as value_in from rgp_transactions where type='IN' and transaction_date between '" . $filter_from . "' and '" . $filter_to . "' group by item_id) e on a.id=e.item_id
        LEFT JOIN (select item_id, sum(qty) as qty_out, sum(value) as value_out from rgp_transactions where type='OUT' and transaction_date between '" . $filter_from . "' and '" . $filter_to . "' group by item_id) f on a.id=f.item_id
        LEFT JOIN item_rm g on a.id = g.id
        LEFT JOIN item_fg h on a.id = h.id
        LEFT JOIN item_categories i on a.item_category_id = i.id
        LEFT JOIN item_familys j on a.item_family_id = j.id
        WHERE a.id like '%$filter_items%' $whereProdfam $whereCategory
        ");

        // die($this->db->last_query());

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">RGP/NRGP/FOC REPORT - ' . $filter_display . '</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2" colspan="2">Part No EBWS</th>
                    <th rowspan="2" colspan="3">Part No Supplier</th>
                    <th rowspan="2" colspan="3">Description</th>
                    <th rowspan="2">Category</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2" colspan="2">Uom</th>
                    <th colspan="2" width="100">Begin<br>Stock</th>
                    <th colspan="2" width="100">In</th>
                    <th colspan="2" width="100">Out</th>
                    <th colspan="2" width="100">Ending<br>Stock</th>
                </tr>
                <tr>
                    <th>Qty</th>
                    <th>Value</th>
                    <th>Qty</th>
                    <th>Value</th>
                    <th>Qty</th>
                    <th>Value</th>
                    <th>Qty</th>
                    <th>Value</th>
                </tr>
                ';
        $no = 1;
        foreach ($records as $record) {
            if ($record->item_id_rm != null) {
                $item_id = @$record->item_id_rm;
            } else {
                $item_id = @$record->item_id_fg;
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->item_id . '</td>
                            <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td colspan="3" style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->category_name . '</td>
                            <td>' . $record->family_name . '</td>
                            <td colspan="2">' . $record->uom . '</td>
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format(@$record->begin_value, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->value_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->value_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->ending_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->ending_value, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="21" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2">Process Type</th>
                                <th rowspan="2">Created By</th>
                                <th rowspan="2">Trans Date</th>
                                <th rowspan="2">Return Est Date</th>
                                <th rowspan="2">RGP/NRGP No.</th>
                                <th rowspan="2">Reference No</th>
                                <th rowspan="2">Company Name</th>
                                <th rowspan="2">Plant</th>
                                <th rowspan="2">Departement</th>
                                <th rowspan="2">RGP/NRGP Type</th>
                                <th rowspan="2">RGP/NRGP Out No</th>
                                <th colspan="2">Begin</th>
                                <th colspan="2">In</th>
                                <th colspan="2">Out</th>
                                <th colspan="2">Ending</th>
                            </tr>
                            <tr>
                                <th>Qty</th>
                                <th>Value</th>
                                <th>Qty</th>
                                <th>Value</th>
                                <th>Qty</th>
                                <th>Value</th>
                                <th>Qty</th>
                                <th>Value</th>
                            </tr>
                            ';

                $nod = 1;
                $begin = @$record->begin_stock;
                $begin_value = @$record->begin_value;
                $in_value = @$record->in_value;
                $out_value = @$record->out_value;
                $in_qty = 0;
                $end_qty = 0;
                $balance = @$record->begin_stock;
                $balance_value = @$record->begin_value;

                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);

                    $transactions = $this->crud->query("SELECT
                        a.process_type,a.created_by,
                        a.transaction_date,
                        a.return_date_estimation,
                        a.number,
                        a.reference_number,a.rgp_number,a.type,
                        a.value,
                        SUM(a.qty) as qty,
                        (CASE 
                            WHEN b.id is not null THEN b.name
                            ELSE c.name
                        END) as company_name,
                        (CASE 
                            WHEN d.plant is not null THEN d.plant
                            ELSE NULL
                        END) as plant_number,
                        e.name as departement_name,
                        f.name as rgp_type_name
                    FROM rgp_transactions a 
                    LEFT JOIN suppliers b on a.company_id = b.id
                    LEFT JOIN customers c on a.company_id = c.id
                    LEFT JOIN customer_address d on a.plant = d.plant 
                    LEFT JOIN departements e on a.departement_id = e.id
                    LEFT JOIN rgp_types f on a.rgp_type_id = f.id
                    WHERE a.item_id = '$item_id' and a.transaction_date = '$working_date'
                    GROUP BY a.transaction_date, a.process_type, a.item_id");

                    foreach ($transactions as $transaction) {
                        if ($transaction->type == "IN") {
                            $qty_in = $transaction->qty;
                            $in_value = $transaction->value;
                            $qty_out = 0;
                            $out_value = 0;
                            $balance = ($begin + $transaction->qty);
                            $balance_value = ($begin_value + $transaction->value);
                        } else {
                            $qty_in = 0;
                            $in_value = 0;
                            $qty_out = $transaction->qty;
                            $out_value = $transaction->value;
                            $balance = ($begin - $transaction->qty);
                            $balance_value = ($begin_value - $transaction->value);
                        }
                        $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>' . $transaction->process_type . '</td>
                                            <td>' . $transaction->created_by . '</td>
                                            <td>' . $transaction->transaction_date . '</td>
                                            <td>' . $transaction->return_date_estimation . '</td>
                                            <td>' . $transaction->number . '</td>
                                            <td>' . $transaction->reference_number . '</td>
                                            <td>' . $transaction->company_name . '</td>
                                            <td>' . $transaction->plant_number . '</td>
                                            <td>' . $transaction->departement_name . '</td>
                                            <td>' . $transaction->rgp_type_name . '</td>
                                            <td>' . $transaction->rgp_number . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($begin_value, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($qty_in) . '</td>
                                            <td style="text-align:right;">' . number_format($in_value, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($qty_out)  . '</td>
                                            <td style="text-align:right;">' . number_format($out_value, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance_value, 2)  . '</td>
                                        </tr>';
                        if ($transaction->type == "IN") {
                            $begin += $transaction->qty;
                            $begin_value += $transaction->value;
                        } else {
                            $begin -= $transaction->qty;
                            $begin_value -= $transaction->value;
                        }
                        $nod++;
                    }
                }
            }
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
