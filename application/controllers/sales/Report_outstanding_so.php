<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_outstanding_so extends CI_Controller
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
        // $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]');
        // $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/report_outstanding_so');
        } else {
            redirect('error_access');
        }
    }

    public function readCustomerOrder()
    {
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $customer_id = $this->input->get("customer_id");

        $customer_orders = $this->crud->query("SELECT customer_order_no, sales_order_no
            FROM sales_orders
            WHERE sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
            AND customer_id = '$customer_id'
            GROUP BY sales_order_no
            ORDER BY sales_order_no ASC");
        echo json_encode($customer_orders);
    }

    public function readItems()
    {
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $filter_sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $customer_id = $this->input->get("customer_id");

        $customer_orders = $this->crud->query("SELECT b.id, b.number, b.name
            FROM sales_orders a
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
            AND a.customer_id = '$customer_id' AND a.sales_order_no = '$filter_sales_order_no'
            GROUP BY a.item_fg_id");
        echo json_encode($customer_orders);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_outstanding_so_$format.xls");
        }

        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $filter_customer_name = base64_decode($this->input->get("filter_customer_name"));
        $filter_customer_order_no = base64_decode($this->input->get("filter_customer_order_no"));
        $filter_sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $filter_item_fg = base64_decode($this->input->get("filter_item_fg"));
        $filter_display = base64_decode($this->input->get("filter_display"));

        $customer = $this->crud->read("customers", [], ["id" => $filter_customer_name]);
        $item_fg = $this->crud->read("item_fg", [], ["id" => $filter_item_fg]);

        $start = strtotime($filter_so_date_from);
        $finish = strtotime($filter_so_date_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                        Print Date ' . date("d M Y H:m:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                    <br><br>
                    <div style="float: centet; font-size: 16px; text-align: center;">
                        <h3>' . $filter_display . ' OUTSTANDING SALES ORDER</h3>
                    </div>
                </center>
                <table style="width: 40%; font-size:12px;">
                    <tr>
                        <th style="width:100px; text-align:left;">Period</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_so_date_from . ' To ' . $filter_so_date_to . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Product No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . @$item_fg->number . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Customer Name</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . @$customer->name . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Customer Order No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_customer_order_no . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Sales Order No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_sales_order_no . '</td>
                    </tr>
                </table>
                <br>';

            $sql = $this->db->query("SELECT a.id AS item_fg_id, b.sales_order_no, a.number AS item_fg_number, a.number_customer, a.name AS item_fg_name, a.uom, 
                    COALESCE(d.begin_stock, 0) AS begin_stock,
                    COALESCE(b.qty_so, 0) AS qty_so, 
                    COALESCE(c.qty_delivery, 0) AS qty_delivery, 
                    (COALESCE(d.begin_stock, 0) + COALESCE(b.qty_so, 0) - COALESCE(c.qty_delivery, 0)) AS outstanding
                FROM item_fg a
                JOIN (SELECT item_fg_id, sales_order_no, SUM(qty) AS qty_so FROM `sales_orders` 
                    WHERE `status` = 0 and sales_order_date BETWEEN '$filter_so_date_from' AND '$filter_so_date_to'
                    AND customer_id LIKE '%$filter_customer_name%'
                    AND item_fg_id LIKE '%$filter_item_fg%'
                    AND customer_order_no LIKE '%$filter_customer_order_no%'
                    AND sales_order_no LIKE '%$filter_sales_order_no%'
                    GROUP BY item_fg_id, sales_order_no) b ON a.id = b.item_fg_id
                LEFT JOIN (SELECT item_fg_id, sales_order_no, SUM(qty) AS qty_delivery FROM delivery_notes WHERE `status` != '2' and delivery_note_date BETWEEN '$filter_so_date_from' AND '$filter_so_date_to' GROUP BY item_fg_id, sales_order_no) c ON a.id = c.item_fg_id and b.sales_order_no = c.sales_order_no
                LEFT JOIN (SELECT a.id, b.sales_order_no,
                    (COALESCE(b.qty_so, 0) - COALESCE(c.qty_delivery, 0)) AS begin_stock
                    FROM item_fg a
                    LEFT JOIN (SELECT item_fg_id, sales_order_no, SUM(qty) AS qty_so FROM `sales_orders` 
                        WHERE `status` = 0 and sales_order_date < '$filter_so_date_from'
                        AND customer_id LIKE '%$filter_customer_name%'
                        AND item_fg_id LIKE '%$filter_item_fg%'
                        AND customer_order_no LIKE '%$filter_customer_order_no%'
                        AND sales_order_no LIKE '%$filter_sales_order_no%'
                        GROUP BY item_fg_id, sales_order_no) b ON a.id = b.item_fg_id
                    LEFT JOIN (SELECT item_fg_id, sales_order_no, SUM(qty) AS qty_delivery FROM delivery_notes WHERE `status` != '2' and delivery_note_date < '$filter_so_date_from' GROUP BY item_fg_id, sales_order_no) c ON a.id = c.item_fg_id and b.sales_order_no = c.sales_order_no
                    GROUP BY a.id) d ON a.id = d.id and b.sales_order_no = d.sales_order_no
                GROUP BY a.id, b.sales_order_no
                ORDER BY a.number_customer ASC");
            $records = $sql->result_array();

            $html .= '<table id="customers" border="1">
                        <tr>
                            <th width="20">No</th>
                            <th colspan="2">Product No EBWS</th>
                            <th colspan="3">Product No Customer</th>
                            <th colspan="2">Description</th>
                            <th>Sales Order No</th>
                            <th>Uom</th>
                            <th>Begin Balance</th>
                            <th>Qty SO</th>
                            <th>Qty Delivery</th>
                            <th>Outstanding</th>
                        </tr>';

            $no = 1;
            $begin_stock = 0;
            $qty_so = 0;
            $qty_delivery = 0;
            $outstanding = 0;
            foreach ($records as $record) {
                $item_fg_id = $record['item_fg_id'];
                $sales_order_no = $record['sales_order_no'];

                $begin_stock += $record['begin_stock'];
                $qty_so += $record['qty_so'];
                $qty_delivery += $record['qty_delivery'];
                $outstanding += $record['outstanding'];
    
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td colspan="2" style="mso-number-format:\@;">' . $record['item_fg_number'] . '</td>
                                <td colspan="3" style="mso-number-format:\@;">' . $record['number_customer'] . '</td>
                                <td colspan="2" style="mso-number-format:\@;">' . $record['item_fg_name'] . '</td>
                                <td style="mso-number-format:\@;">' . $record['sales_order_no'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format(@$record['begin_stock'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($record['qty_so'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($record['qty_delivery'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($record['outstanding'], 2) . '</td>
                            </tr>';
    
                if ($filter_display == "DETAIL") {
                    $html .= '  <tr>
                                    <td colspan="15" style="background:#D1FFC6;"><b>DETAIL OF ' . $record['number_customer'] . ' - ' . $record['item_fg_name'] . '</b></td>
                                </tr>
                                <tr>
                                    <th width="20"></th>
                                    <th width="20">No</th>
                                    <th>Trans Type</th>
                                    <th>Created By</th>
                                    <th>Trans Date</th>
                                    <th>Customer ID</th>
                                    <th>Customer Name</th>
                                    <th>Plant</th>
                                    <th>Doc No</th>
                                    <th>Customer PO</th>
                                    <th>Begin</th>
                                    <th>In</th>
                                    <th>Out</th>
                                    <th>Balance</th>
                                </tr>';
    
                    $nod = 1;
                    $begin = @$record['begin_stock'];
                    $balance = @$record['begin_stock'];
    
                    for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                        $working_date = date('Y-m-d', $i);
    
                        //SALES
                        $sales = $this->crud->query("SELECT
                            d.name as created_name,
                            a.sales_order_date,
                            a.customer_id,
                            c.name as customer_name,
                            a.plant,
                            a.sales_order_no,
                            a.customer_order_no,
                            a.qty
                        FROM sales_orders a 
                        JOIN item_fg b ON a.item_fg_id = b.id
                        JOIN customers c ON a.customer_id = c.id
                        JOIN users d ON a.created_by = d.username
                        WHERE a.item_fg_id = '$item_fg_id' and a.sales_order_date = '$working_date' and a.sales_order_no = '$sales_order_no'
                        GROUP BY a.id");

                        //DELIVERY NOTE
                        $deliveries = $this->crud->query("SELECT
                            d.name as created_name,
                            a.delivery_note_date,
                            a.customer_id,
                            c.name as customer_name,
                            a.plant,
                            a.delivery_note_no,
                            a.delivery_order_no,
                            a.qty
                        FROM delivery_notes a 
                        JOIN item_fg b ON a.item_fg_id = b.id
                        JOIN customers c ON a.customer_id = c.id
                        JOIN users d ON a.created_by = d.username
                        WHERE a.item_fg_id = '$item_fg_id' and a.delivery_note_date = '$working_date' and a.sales_order_no = '$sales_order_no'
                        GROUP BY a.id");
                        
                        //SALES ORDER
                        foreach ($sales as $sale) {
                            $balance = ($begin + $sale->qty);
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>SALES ORDER</td>
                                            <td>' . $sale->created_name . '</td>
                                            <td>' . $sale->sales_order_date . '</td>
                                            <td>' . $sale->customer_id . '</td>
                                            <td>' . $sale->customer_name . '</td>
                                            <td>' . $sale->plant . '</td>
                                            <td>' . $sale->sales_order_no . '</td>
                                            <td>' . $sale->customer_order_no . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($sale->qty) . '</td>
                                            <td style="text-align:right;">' . number_format(0)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $sale->qty;
                            $nod++;
                        }
    
                        //DELIVERY NOTE
                        foreach ($deliveries as $delivery) {
                            $balance = ($begin - $delivery->qty);
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>DELIVERY NOTE</td>
                                            <td>' . $delivery->created_name . '</td>
                                            <td>' . $delivery->delivery_note_date . '</td>
                                            <td>' . $delivery->customer_id . '</td>
                                            <td>' . $delivery->customer_name . '</td>
                                            <td>' . $delivery->plant . '</td>
                                            <td>' . $delivery->delivery_note_no . '</td>
                                            <td>' . $delivery->delivery_order_no . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0) . '</td>
                                            <td style="text-align:right;">' . number_format($delivery->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin -= $delivery->qty;
                            $nod++;
                        }
                    }
                }
                $no++;
            }

            $html .= '  <tr>
                            <td colspan="10"><b>GRAND TOTAL</b></td>
                            <td style="text-align:right; font-weight:bold;">' . number_format($begin_stock, 2) . '</td>
                            <td style="text-align:right; font-weight:bold;">' . number_format($qty_so, 2) . '</td>
                            <td style="text-align:right; font-weight:bold;">' . number_format($qty_delivery, 2) . '</td>
                            <td style="text-align:right; font-weight:bold;">' . number_format($outstanding, 2) . '</td>
                        </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }
}
