<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Inventory_rm extends CI_Controller
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
            $this->load->view('costing/inventory_rm');
        } else {
            redirect('error_access');
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

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_rm_id' => $data->val($i, 2),
                'trans_date' => $data->val($i, 3),
                'invoice_no' => $data->val($i, 4),
                'document_no' => $data->val($i, 5),
                'uom' => $data->val($i, 6),
                'qty' => $data->val($i, 7),
                'price' => $data->val($i, 8),
                'amount' => $data->val($i, 9)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/inventory_rm.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/inventory_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/inventory_rm.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $item_rm = $this->crud->read('item_rm', ["id" => $data['item_rm_id']]);
            $inventory_rm = $this->crud->read('inventory_rm', ["item_rm_id" => $data['item_rm_id']]);

            if (!empty($item_rm)) {
                if (!empty($inventory_rm)) {
                    $this->db->update('inventory_rm',  $data, ["item_rm_id" => $data['item_rm_id']]);
                    echo json_encode(array("title" => "Good Job", "message" => $data['item_rm_id'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $this->crud->create('inventory_rm',  $data);
                    echo json_encode(array("title" => "Not Found", "message" => $data['item_rm_id'] . " Saved Successfully", "theme" => "success"));
                }
            } else {
                echo json_encode(array("title" => "Not Found", "message" => $data['item_rm_id'] . " Part No EBWS Not Found", "theme" => "error"));
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_costing_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

        if($filter_item_family != ""){
            $whereProdfam = "and b.id = '$filter_item_family'";
        }else{
            $whereProdfam = "";

        }

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            b.name as prodfam, 
            a.uom, 
            COALESCE(h.begin_stock,0) AS begin_stock,
            (CASE WHEN COALESCE(l.begin_price,0) > 0 THEN COALESCE(l.begin_price,0) ELSE COALESCE(m.begin_price,0) END) AS begin_price,
            (COALESCE(h.begin_stock,0) * (CASE WHEN COALESCE(l.begin_price,0) > 0 THEN COALESCE(l.begin_price,0) ELSE COALESCE(m.begin_price,0) END)) AS begin_amount
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.item_category_id = 'C01'
        LEFT JOIN (SELECT item_rm_id, COALESCE(ROUND(AVG(price), 2), 0) as begin_price FROM purchase_orders WHERE po_date < '$filter_from' GROUP BY item_rm_id) l ON a.id = l.item_rm_id
        LEFT JOIN (SELECT item_rm_id, price as begin_price FROM inventory_rm WHERE trans_date <= '$filter_from' GROUP BY item_rm_id) m ON a.id = m.item_rm_id
        LEFT JOIN (SELECT
            a.id,
            ((COALESCE(SUM(c.qty_sto_in),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(SUM(cc.qty_sto_out),0) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS begin_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto_in FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto_out FROM os_rm a JOIN os_rm_labels b ON a.id  = b.receipt_id WHERE a.trans_date < '$filter_from' GROUP BY item_rm_id) cc ON a.id = cc.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date < '$filter_from' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date < '$filter_from' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date < '$filter_from' GROUP BY a.item_id) j ON a.id = j.item_id
            LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date < '$filter_from' GROUP BY a.item_id) k ON a.id = k.item_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
            WHERE a.return_date < '$filter_from'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            GROUP BY a.id) h ON a.id = h.id
        WHERE a.id like '%$filter_items%' $whereProdfam
        GROUP BY a.id
        ORDER BY b.number, a.number");

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
                <h3 style="margin:0;">INVENTORY COSTING REPORT (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2" colspan="2">Part No EBWS</th>
                    <th rowspan="2" colspan="2">Part No Supplier</th>
                    <th rowspan="2">Description</th>
                    <th rowspan="2">Uom</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2">Currency</th>
                    <th rowspan="2">Exchange<br>Rate</th>
                    <th colspan="3" width="100">Begin</th>
                    <th colspan="3" width="100">In</th>
                    <th colspan="3" width="100">Out</th>
                    <th colspan="3" width="100">Ending</th>
                </tr>
                <tr>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>';
        $html_summary = "";
        $html_detail = "";
        $html_footer = "";
        $no = 1;
        $total_begin_qty = 0;
        $total_in_qty = 0;
        $total_out_qty = 0;
        $total_ending_qty = 0;
        $total_begin_amount = 0;
        $total_in_amount = 0;
        $total_out_amount = 0;
        $total_ending_amount = 0;
        foreach ($records as $record) {
            $item_rm_id = $record->id;

            $html_detail .= '<tr>
                                <td colspan="24" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2">Trans Type</th>
                                <th rowspan="2">Created By</th>
                                <th rowspan="2">Trans Date</th>
                                <th rowspan="2">Supplier ID</th>
                                <th rowspan="2">Supplier Name</th>
                                <th rowspan="2">Doc No</th>
                                <th rowspan="2">Invoice No</th>
                                <th rowspan="2">Currency</th>
                                <th rowspan="2">Exchange<br>Rate</th>
                                <th colspan="3">Begin</th>
                                <th colspan="3">In</th>
                                <th colspan="3">Out</th>
                                <th colspan="3">Balance</th>
                            </tr>
                            <tr>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Amount</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Amount</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Amount</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Amount</th>
                            </tr>';

            $details = $this->crud->query("SELECT z.*, ROUND(COALESCE(q.middle, 1), 2) as middle FROM (
            SELECT 'STO IN' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, a.id AS invoice_no, COALESCE(SUM(b.qty), 0) AS qty_in, '0' AS qty_out, b.price, 'INR' as currency FROM os_rm a JOIN scan_item_receipts b ON a.id = b.receipt_id JOIN users c ON a.created_by = c.username WHERE a.item_rm_id = '$item_rm_id' GROUP BY a.id
            UNION
            SELECT 'STO OUT' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, a.id AS invoice_no, '0' AS qty_in, COALESCE(SUM(b.qty), 0) AS qty_out, b.price, 'INR' as currency FROM os_rm a JOIN os_rm_labels b ON a.id = b.receipt_id JOIN users c ON a.created_by = c.username WHERE a.item_rm_id = '$item_rm_id' GROUP BY a.id
            UNION
            SELECT 'STO ADJUSTMENT IN' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, '-' AS invoice_no, a.qty AS qty_in, '0' AS qty_out, '0' as price, 'INR' as currency FROM sto_rm a JOIN users c ON a.created_by = c.username WHERE a.type = 'IN' AND a.item_rm_id = '$item_rm_id' GROUP BY a.id
            UNION
            SELECT 'STO ADJUSTMENT OUT' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, '-' AS invoice_no, '0' AS qty_in, a.qty AS qty_out, '0' as price, 'INR' as currency FROM sto_rm a JOIN users c ON a.created_by = c.username WHERE a.type = 'OUT' AND a.item_rm_id = '$item_rm_id' GROUP BY a.id
            UNION
            SELECT 'RECEIPT' AS trans_type, c.name AS created_by, DATE_FORMAT(b.created_date, '%Y-%m-%d') AS trans_date, d.id AS supplier_id, d.name AS supplier_name, a.receipt_no AS document_no, a.bc_document AS invoice_no, SUM(b.qty) AS qty_in, '0' AS qty_out, b.price, e.currency FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id JOIN users c ON a.created_by = c.username JOIN suppliers d ON a.supplier_id = d.id JOIN purchase_orders e ON a.po_no = e.po_no and a.item_rm_id = e.item_rm_id WHERE a.item_rm_id = '$item_rm_id' GROUP BY a.receipt_no, a.bc_document
            UNION
            SELECT 'REJECTION' AS trans_type, c.name AS created_by, DATE_FORMAT(b.created_date, '%Y-%m-%d') AS trans_date, d.id AS supplier_id, d.name AS supplier_name, a.bc_document AS document_no, b.label_no AS invoice_no, '0' AS qty_in, SUM(b.qty) AS qty_out, '0' as price, 'INR' as currency FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id = b.receipt_id JOIN users c ON b.created_by = c.username JOIN suppliers d ON a.supplier_id = d.id WHERE a.item_rm_id = '$item_rm_id' GROUP BY b.id
            UNION
            SELECT 'ISSUED' AS trans_type, created_by, DATE_FORMAT(created_date, '%Y-%m-%d') AS trans_date, '-' AS supplier_id, '-' AS supplier_name, request_no AS document_no, label_no AS invoice_no, '0' AS qty_in, qty AS qty_out, price, 'INR' as currency FROM issued_material_details WHERE item_rm_id = '$item_rm_id'
            UNION
            SELECT 'RETURN MATERIAL' AS trans_type, d.name AS created_by, a.return_date AS trans_date, '-' AS supplier_id, '-' AS supplier_name, a.return_no AS document_no, b.label_no AS invoice_no, b.qty AS qty_in, '0' AS qty_out, c.price, 'INR' as currency FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id JOIN users d ON a.created_by = d.username WHERE a.item_rm_id = '$item_rm_id' GROUP BY b.label_no
            UNION 
            SELECT 'RGP IN' AS trans_type, a.created_by, DATE_FORMAT(c.created_date, '%Y-%m-%d') AS trans_date, b.id AS supplier_id, b.name AS supplier_name, c.rgp_number AS document_no, c.label_no AS invoice_no, c.qty AS qty_in, '0' AS qty_out, c.price, a.currency FROM rgp_transactions a LEFT JOIN suppliers b ON a.company_id = b.id JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id WHERE a.type = 'IN' AND a.item_id = '$item_rm_id' GROUP BY c.id
            UNION
            SELECT 'RGP OUT' AS trans_type, a.created_by, DATE_FORMAT(c.created_date, '%Y-%m-%d') AS trans_date, b.id AS supplier_id, b.name AS supplier_name, c.rgp_number AS document_no, c.label_no AS invoice_no, '0' AS qty_in, c.qty AS qty_out, c.price, a.currency FROM rgp_transactions a LEFT JOIN suppliers b ON a.company_id = b.id JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id WHERE a.type = 'OUT' AND a.item_id = '$item_rm_id' GROUP BY c.id
            ) z 
            LEFT JOIN exchange_rates q ON DATE_FORMAT(z.trans_date, '%Y-%m-%d') between q.start_date and q.end_date and q.currency_from = z.currency
            WHERE z.trans_date between '$filter_from' and '$filter_to' ORDER BY z.trans_date ASC");

            $nod = 1;
            $begin_qty = @$record->begin_stock;
            $begin_price = @$record->begin_price;
            $begin_amount = @$record->begin_amount;
            $ending_qty = @$record->begin_stock;
            $ending_amount = @$record->begin_price;
            $ending_price = @$record->begin_amount;
            $in_qty = 0;
            $out_qty = 0;
            $sum_in_qty = 0;
            $sum_in_amount = 0;
            $sum_out_qty = 0;
            $sum_out_amount = 0;
            foreach ($details as $detail) {
                if($detail->qty_in > 0){
                    $in_qty = $detail->qty_in;
                    $in_price = $detail->price > 0 ? ($detail->price * $detail->middle) : $begin_price;
                    $in_amount = ($in_qty * $in_price);
                    $out_qty = 0;
                    $out_price = round(($begin_amount + $in_amount) / ($begin_qty + $in_qty), 2);
                    $out_amount = round($out_qty * $out_price, 2);
                }else{
                    $in_qty = 0;
                    $in_price = 0;
                    $in_amount = 0;
                    $out_qty = $detail->qty_out;
                    $out_price = $detail->price > 0 ? ($detail->price * $detail->middle) : $begin_price; 
                    // $out_price = round(($begin_amount + $in_amount) / ($begin_qty + $in_qty), 2);
                    $out_amount = round($out_qty * $out_price, 2);
                }

                $ending_qty = ($begin_qty + $in_qty - $out_qty);
                $ending_amount = ($begin_amount + $in_amount - $out_amount);

                if($ending_qty > 0){
                    $ending_price = round($ending_amount / $ending_qty, 2);
                }else{
                    $ending_price = ($in_price > 0) ? $in_price : $out_price;
                }

                $html_detail .= '  <tr>
                                <td style="text-align:center">' . $no.".".$nod . '</td>
                                <td>' . $detail->trans_type . '</td>
                                <td>' . $detail->created_by . '</td>
                                <td>' . $detail->trans_date . '</td>
                                <td>' . $detail->supplier_id . '</td>
                                <td>' . $detail->supplier_name . '</td>
                                <td>' . $detail->document_no . '</td>
                                <td>' . $detail->invoice_no . '</td>
                                <td>' . $detail->currency . '</td>
                                <td>' . $detail->middle . '</td>
                                <td style="text-align:right;">' . number_format($begin_qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format($begin_price, 2) . '</td>
                                <td style="text-align:right;">' . number_format($begin_amount, 2) . '</td>
                                <td style="text-align:right;">' . number_format($in_qty, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($in_price, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($in_amount, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($out_qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format($out_price, 2) . '</td>
                                <td style="text-align:right;">' . number_format($out_amount, 2) . '</td>
                                <td style="text-align:right;">' . number_format($ending_qty, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($ending_price, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($ending_amount, 2)  . '</td>
                            </tr>';

                if($detail->qty_in > 0){
                    $begin_qty += ($in_qty);
                    $begin_price = $ending_price;
                    $begin_amount = ($begin_qty * $begin_price);
                }else{
                    $begin_qty -= ($out_qty);
                    $begin_price = $ending_price;
                    $begin_amount = ($begin_qty * $begin_price);
                }

                $sum_in_qty += $in_qty;
                $sum_in_amount += round($in_amount, 2);
                $sum_out_qty += $out_qty;
                $sum_out_amount += round($out_amount, 2);

                $nod++;
            }

            if($sum_in_qty > 0){
                $price_in = ($sum_in_amount / $sum_in_qty);
            }else{
                $price_in = ($sum_in_amount / 1);
            }

            if($sum_out_qty > 0){
                $price_out = ($sum_out_amount / $sum_out_qty);
            }else{
                $price_out = ($sum_out_amount / 1);
            }

            $html_summary .= '  <tr>
                                    <td style="text-align:center">' . $no . '</td>
                                    <td colspan="2" style="mso-number-format:\@;">' . $record->id . '</td>
                                    <td colspan="2" style="mso-number-format:\@;">' . $record->number . '</td>
                                    <td style="mso-number-format:\@;">' . $record->name . '</td>
                                    <td>' . $record->uom . '</td>
                                    <td>' . $record->prodfam . '</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td style="text-align:right;">' . number_format($record->begin_stock, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record->begin_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record->begin_amount, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($sum_in_qty) . '</td>
                                    <td style="text-align:right;">' . number_format($price_in, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($sum_in_amount, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($sum_out_qty, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($price_out, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($sum_out_amount, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($ending_qty, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($ending_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($ending_amount, 2) . '</td>
                                </tr>';

            $total_begin_qty += $record->begin_stock;
            $total_in_qty += $sum_in_qty;
            $total_out_qty += $sum_in_amount;
            $total_ending_qty += $ending_qty;
            $total_begin_amount += $record->begin_amount;
            $total_in_amount += $sum_in_amount;
            $total_out_amount += $sum_out_amount;
            $total_ending_amount += $ending_amount;
            $no++;
        }

        $html_footer .= '   <tr>
                                <td style="text-align:right; font-weight:bold;" colspan="10">GRAND TOTAL</td>
                                <td style="text-align:right; font-weight:bold;">' . number_format($total_begin_qty, 2) . '</td>
                                <td style="text-align:right; font-weight:bold;">-</td>
                                <td style="text-align:right; font-weight:bold;">' . number_format($total_begin_amount, 2) . '</td>
                                <td style="text-align:right; font-weight:bold;">' . number_format($total_in_qty, 2) . '</td>
                                <td style="text-align:right; font-weight:bold;">-</td>
                                <td style="text-align:right; font-weight:bold;">' . number_format($total_in_amount) . '</td>
                                <td style="text-align:right; font-weight:bold;">' . number_format($total_out_qty, 2) . '</td>
                                <td style="text-align:right; font-weight:bold;">-</td>
                                <td style="text-align:right; font-weight:bold;">' . number_format($total_out_amount) . '</td>
                                <td style="text-align:right; font-weight:bold;">' . number_format($total_ending_qty, 2) . '</td>
                                <td style="text-align:right; font-weight:bold;">-</td>
                                <td style="text-align:right; font-weight:bold;">' . number_format($total_ending_amount, 2) . '</td>
                            </tr>';
        
        $html_footer .= '</table></body></html>';
        echo $html . $html_summary;
        if($filter_display == "DETAIL"){
            echo $html_detail;
        }
        echo $html_footer;
    }
}
