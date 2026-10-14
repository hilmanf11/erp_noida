<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_outstanding_po extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/report_outstanding_po');
        } else {
            redirect('error_access');
        }
    }

    public function readPurchaseOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));

        $supplier_id = $this->input->get("supplier_id");
        
        $sales_orders = $this->crud->query("SELECT `po_no`
            FROM purchase_orders
            WHERE `po_no` like '%$post%'
            -- AND po_date between '$filter_from' and '$filter_to'
            AND supplier_id = '$supplier_id'
            GROUP BY `po_no` 
            ORDER BY `po_no` DESC");
        echo json_encode($sales_orders);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=outstanding_po_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_display = $this->input->get("filter_display");
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status = $this->input->get("filter_status");
        $filter_purchase_order = base64_decode($this->input->get("filter_purchase_order"));

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $sql = $this->db->query("SELECT a.id AS item_rm_id, a.number AS item_rm_number, a.name AS item_rm_name, a.uom, b.po_no, b.po_date, f.currency, b.price, e.name as item_family_name, f.id as supplier_id, f.name as supplier_name,
            COALESCE(d.begin_stock, 0) AS begin_stock,
            COALESCE(SUM(b.qty), 0) AS qty_po, 
            COALESCE(c.qty_receipt, 0) AS qty_receipt, 
            (COALESCE(d.begin_stock, 0) + COALESCE(SUM(b.qty), 0) - COALESCE(c.qty_receipt, 0)) AS outstanding,
            b.status, b.complete
        FROM item_rm a
        JOIN item_familys e ON a.item_family_id = e.id
        JOIN purchase_orders b ON a.id = b.item_rm_id
        JOIN suppliers f ON b.supplier_id = f.id
        LEFT JOIN (SELECT item_rm_id, po_no, SUM(qty_receipt) AS qty_receipt FROM purchase_order_receipts WHERE receipt_date between '$filter_from' and '$filter_to' and status != '2' GROUP BY item_rm_id, po_no) c ON a.id = c.item_rm_id and b.po_no = c.po_no
        LEFT JOIN (SELECT a.id, b.po_no, (COALESCE(SUM(b.qty), 0) - COALESCE(c.qty_receipt, 0)) AS begin_stock
            FROM item_rm a
            JOIN purchase_orders b ON a.id = b.item_rm_id
            LEFT JOIN (SELECT item_rm_id, po_no, SUM(qty_receipt) AS qty_receipt FROM purchase_order_receipts WHERE receipt_date < '$filter_from' and status != '2' GROUP BY item_rm_id, po_no) c ON a.id = c.item_rm_id and b.po_no = c.po_no
            WHERE b.po_date < '$filter_from' and b.supplier_id like '%$filter_supplier%' and a.id like '%$filter_product_no%' and b.po_no like '%$filter_purchase_order%'
            GROUP BY a.id, b.po_no) d ON a.id = d.id and b.po_no = d.po_no
        WHERE b.po_date BETWEEN '$filter_from' AND '$filter_to' and b.supplier_id like '%$filter_supplier%' and a.id like '%$filter_product_no%' and b.po_no like '%$filter_purchase_order%'
        GROUP BY a.id, b.po_no");
        $records = $sql->result_array();

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
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">OUTSTANDING PURCHASE ORDER</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th width="50">Status</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Product Family</th>
                    <th>Supplier ID</th>
                    <th>Supplier Name</th>
                    <th>PO No</th>
                    <th>PO Date</th>
                    <th>Currency</th>
                    <th>Price</th>
                    <th>Uom</th>
                    <th>Begin Balance</th>
                    <th>Qty PO</th>
                    <th>Qty Receipt</th>
                    <th>Outstanding</th>
                </tr>';

        $no = 1;
        foreach ($records as $record) {
            $item_rm_id = $record['item_rm_id'];
            $po_no = $record['po_no'];

            if($record['status'] == 0){
                $status = "OPEN";
                $style = "background: green; color:white;";
                $begin_stock = @$record['begin_stock'];
                $qty_po = @$record['qty_po'];
                $qty_receipt = @$record['qty_receipt'];
                $outstanding = @$record['outstanding'];
            }else{
                if($record['complete'] == 0){
                    $status = "CLOSE";
                    $style = "background: red; color:white;";
                    $begin_stock = @$record['begin_stock'];
                    $qty_po = @$record['qty_po'];
                    $qty_receipt = @$record['qty_receipt'];
                    $outstanding = @$record['outstanding'];
                }else{
                    $status = "COMPLETE";
                    $style = "background: orange; color:white;";
                    $begin_stock = @$record['begin_stock'];
                    $qty_po = @$record['qty_po'];
                    $qty_receipt = @$record['qty_receipt'];
                    $outstanding = 0;
                }
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="text-align:center; '.$style.'">' . $status . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_rm_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_rm_name'] . '</td>
                            <td>' . $record['item_family_name'] . '</td>
                            <td>' . $record['supplier_id'] . '</td>
                            <td>' . $record['supplier_name'] . '</td>
                            <td>' . $record['po_no'] . '</td>
                            <td>' . $record['po_date'] . '</td>
                            <td>' . $record['currency'] . '</td>
                            <td>' . $record['price'] . '</td>
                            <td>' . $record['uom'] . '</td>
                            <td style="text-align:right;">' . number_format($begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($qty_po, 2) . '</td>
                            <td style="text-align:right;">' . number_format($qty_receipt, 2) . '</td>
                            <td style="text-align:right;">' . number_format($outstanding, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="20" style="background:#D1FFC6;"><b>DETAIL OF ' . $record['po_no'] . ' | ' . $record['item_rm_number'] . '</b></td>
                            </tr>
                            <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th width="20">Status</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th>Trans Date</th>
                                <th>Supplier ID</th>
                                <th colspan="2">Supplier Name</th>
                                <th colspan="2">PO No</th>
                                <th colspan="2">Invoice No</th>
                                <th>Begin</th>
                                <th>Qty PO</th>
                                <th>Qty Receipt</th>
                                <th>Balance</th>
                            </tr>';

                $nod = 1;
                $begin = @$record['begin_stock'];
                $balance = @$record['begin_stock'];

                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);

                    //PURCHASE ORDER
                    $purchases = $this->crud->query("SELECT
                        d.name as created_name,
                        a.po_date,
                        a.supplier_id,
                        c.name as supplier_name,
                        a.po_no,
                        a.request_no,
                        a.qty,
                        a.status,
                        a.complete
                    FROM purchase_orders a 
                    JOIN item_rm b ON a.item_rm_id = b.id
                    JOIN suppliers c ON a.supplier_id = c.id
                    JOIN users d ON a.created_by = d.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.po_no = '$po_no' and a.po_date = '$working_date'
                    GROUP BY a.id");

                    //PURCHASE ORDER RECEIPTS
                    $receipts = $this->crud->query("SELECT
                        d.name as created_name,
                        a.receipt_date,
                        a.supplier_id,
                        c.name as supplier_name,
                        a.receipt_no,
                        a.bc_document,
                        a.qty_receipt,
                        a.status
                    FROM purchase_order_receipts a 
                    JOIN item_rm b ON a.item_rm_id = b.id
                    JOIN suppliers c ON a.supplier_id = c.id
                    JOIN users d ON a.created_by = d.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.po_no = '$po_no'  and a.receipt_date = '$working_date'
                    GROUP BY a.id");
                    
                    foreach ($purchases as $purchase) {
                        // if($record['status'] == 0){
                            $qty = $purchase->qty;
                        // }else{
                        //     if($record['complete'] == 0){
                        //         $qty = $purchase->qty;
                        //     }else{
                        //         $qty = 0;
                        //     }
                        // }

                        $balance = ($begin + $qty);
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>-</td>
                                        <td>PURCHASE ORDER</td>
                                        <td>' . $purchase->created_name . '</td>
                                        <td>' . $purchase->po_date . '</td>
                                        <td>' . $purchase->supplier_id . '</td>
                                        <td colspan="2">' . $purchase->supplier_name . '</td>
                                        <td colspan="2">' . $purchase->po_no . '</td>
                                        <td colspan="2">' . $purchase->request_no . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($qty) . '</td>
                                        <td style="text-align:right;">' . number_format(0)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin += $qty;
                        $nod++;
                    }

                    foreach ($receipts as $receipt) {
                        $balance = ($begin - $receipt->qty_receipt);

                        if($receipt->status == 2){
                            $qty_receipt = 0;
                            $status = "CANCEL";
                        }else{
                            if($receipt->status == "0"){
                                $status = "NOT SCAN";
                            }else{
                                $status = "SCAN IN";
                            }

                            $qty_receipt = $receipt->qty_receipt;
                        }

                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>'.$status.'</td>
                                        <td>GATE ENTRY</td>
                                        <td>' . $receipt->created_name . '</td>
                                        <td>' . $receipt->receipt_date . '</td>
                                        <td>' . $receipt->supplier_id . '</td>
                                        <td colspan="2">' . $receipt->supplier_name . '</td>
                                        <td colspan="2">' . $receipt->receipt_no . '</td>
                                        <td colspan="2">' . $receipt->bc_document . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($qty_receipt, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $qty_receipt;
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
