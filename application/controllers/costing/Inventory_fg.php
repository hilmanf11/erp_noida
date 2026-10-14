<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Inventory_fg extends CI_Controller
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
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('costing/inventory_fg');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        if ($number != "001") {
            $this->db->where("number", $number);
        } else {
            $this->db->where("number !=", $number);
        }
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function formatNo($number){
        if($number >= 0){
            return "<span>".number_format($number, 2)."</span>";
        }else{
            return "<span style='color:red;'>(".number_format(abs($number), 2).")</span>";
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_fg_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $periode = date("Ym", strtotime($filter_from));
        $periode_bf = date("Ym", strtotime("-1 month", strtotime($filter_from)));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>REPORT INVENTORY HISTORY TRANSACTION FINISH GOOD</small><br>
                                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>';

            if ($filter_display == "RECAP") {
                $sql_begin = $this->crud->query("SELECT c.id as item_rm_id, 
                        c.number_customer AS item_fg_number, c.name AS item_fg_name, c.uom,
                        ROUND(zz.begin_qty, 2) AS begin_qty,
                        ROUND(zz.begin_price, 4) AS begin_price,
                        ROUND(zz.begin_amount, 4) AS begin_amount,
                        ROUND(SUM(zz.qty_in), 2) AS qty_in, 
                        ROUND(SUM(zz.amount_in) / SUM(zz.qty_in), 4) AS price_in,
                        ROUND(SUM(zz.amount_in), 4) AS amount_in, 
                        ROUND(COALESCE(SUM(zz.qty_out), 0), 2) AS qty_out, 
                        ROUND(COALESCE((SUM(zz.amount_out) / SUM(zz.qty_out)), 0), 4) AS price_out,
                        ROUND(COALESCE(SUM(zz.amount_out), 0), 4) AS amount_out,
                        ROUND(zz.begin_qty + SUM(zz.qty_in) - SUM(zz.qty_out), 2) AS bal_qty,
                        ROUND((zz.begin_amount + SUM(zz.amount_in) - SUM(zz.amount_out)) / (zz.begin_qty + SUM(zz.qty_in) - SUM(zz.qty_out)), 2) AS bal_price,
                        ROUND((ROUND(zz.begin_qty + SUM(zz.qty_in) - SUM(zz.qty_out), 2) * ROUND((zz.begin_amount + SUM(zz.amount_in) - SUM(zz.amount_out)) / (zz.begin_qty + SUM(zz.qty_in) - SUM(zz.qty_out)), 2)), 2) AS bal_amount
                    FROM item_fg c
                    LEFT JOIN (SELECT (@no_urut := @no_urut + 1) AS no_urut, 
                        c.number_customer AS item_fg_number, c.name AS item_fg_name, c.uom, z.created_by,
                        z.trans_type, z.trans_date, z.workorder, 
                        (CASE WHEN @item_fg_id = z.item_fg_id THEN '1' ELSE '0' END) AS typee,
                        @begin_qty := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_qty ELSE 0 END, 2) AS begin_qty,
                        @begin_price := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_price ELSE COALESCE(bgin.begin_price, 0) END, 4) AS begin_price,
                        @begin_amount := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_amount ELSE COALESCE(bgin.begin_amount, 0) END, 4) AS begin_amount,
                        @item_fg_id := z.item_fg_id AS item_fg_id,
                        z.qty_in, 
                        ROUND(CASE WHEN z.price_in = 0 THEN @begin_price ELSE z.price_in END, 4) AS price_in,
                        ROUND(CASE WHEN z.amount_in = 0 THEN (z.qty_in * @begin_price) ELSE z.amount_in END, 4) AS amount_in,
                        z.qty_out, 
                        ROUND(CASE WHEN z.qty_in = 0 THEN @begin_price ELSE 0 END, 4) AS price_out, 
                        (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END) AS amount_out,
                        @balance_price := (CASE WHEN z.amount_in = 0 THEN @begin_price ELSE COALESCE(ROUND((@begin_amount + z.amount_in - (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END)) / (@begin_qty + z.qty_in - z.qty_out), 2), 0) END) AS balance_price,
                        @balance_qty := COALESCE(ROUND(@begin_qty + z.qty_in - z.qty_out, 2), 0) AS balance_qty,
                        @balance_amount := COALESCE(ROUND(@balance_qty * @balance_price, 4), 0) AS balance_amount
                    FROM (SELECT a.* FROM (
                        SELECT
                            a.id,
                            b.item_fg_id,
                            'STO FG' AS trans_type,
                            a.created_by,
                            DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date,
                            b.document_no AS workorder,
                            a.qty AS qty_in,
                            COALESCE(c.direct_total, 0) AS price_in,
                            (a.qty * COALESCE(c.direct_total, 0)) AS amount_in,
                            0 AS qty_out
                        FROM scan_item_receipts_fg a
                        JOIN os_fg b ON a.workorder = b.id
                        LEFT JOIN costing_products c ON b.item_fg_id = c.item_fg_id AND c.periode = '$periode'
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                        GROUP BY a.id
                        UNION
                        SELECT
                            a.id,
                            b.item_fg_id,
                            'SCAN FG' AS trans_type,
                            a.created_by,
                            DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date,
                            b.checksheet_number AS workorder,
                            a.qty AS qty_in,
                            COALESCE(c.direct_total, 0) AS price_in,
                            (a.qty * COALESCE(c.direct_total, 0)) AS amount_in,
                            0 AS qty_out
                        FROM scan_item_receipts_fg a
                        JOIN wip_receipts b ON a.workorder = b.workorder AND a.checksheet_number = b.checksheet_number 
                        LEFT JOIN costing_products c ON b.item_fg_id = c.item_fg_id AND c.periode = '$periode'
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                        UNION
                        SELECT
                            a.id,
                            a.item_fg_id,
                            'ADJUSTMENT IN' AS trans_type,
                            a.created_by,
                            a.trans_date,
                            a.document_no AS workorder,
                            a.qty AS qty_in,
                            0 AS price_in,
                            0 AS amount_in,
                            0 AS qty_out
                        FROM sto_fg a
                        WHERE a.type = 'IN' AND (a.approved_to = '' OR a.approved_to IS NULL) AND a.trans_date BETWEEN '$filter_from' AND '$filter_to'
                        GROUP BY a.id
                        UNION
                        SELECT
                            a.id,
                            a.item_fg_id,
                            'ADJUSTMENT OUT' AS trans_type,
                            a.created_by,
                            a.trans_date,
                            a.document_no AS workorder,
                            0 AS qty_in,
                            0 AS price_in,
                            0 AS amount_in,
                            a.qty AS qty_out
                        FROM sto_fg a
                        WHERE a.type = 'OUT' AND (a.approved_to = '' OR a.approved_to IS NULL) AND a.trans_date BETWEEN '$filter_from' AND '$filter_to'
                        GROUP BY a.id
                        UNION
                        SELECT
                            a.id,
                            b.id AS item_fg_id,
                            CONCAT(a.process_type, ' IN') AS trans_type,
                            a.created_by,
                            a.transaction_date AS trans_date,
                            a.number AS workorder,
                            a.qty AS qty_in,
                            a.price AS price_in,
                            (a.qty * a.price) AS amount_in,
                            0 AS qty_out
                        FROM rgp_transactions a
                        JOIN item_fg b ON a.item_id = b.id
                        JOIN scan_item_receipts_fg c ON a.rgp_id = c.checksheet_number AND a.id = c.workorder
                        WHERE a.type = 'IN' AND a.transaction_date BETWEEN '$filter_from' AND '$filter_to'
                        GROUP BY a.id
                        UNION
                        SELECT
                            a.id,
                            b.id AS item_fg_id,
                            CONCAT(a.process_type, ' IN') AS trans_type,
                            a.created_by,
                            a.transaction_date AS trans_date,
                            a.number AS workorder,
                            0 AS qty_in,
                            0 AS price_in,
                            0 AS amount_in,
                            a.qty AS qty_out
                        FROM rgp_transactions a
                        JOIN item_fg b ON a.item_id = b.id
                        JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id
                        WHERE a.type = 'OUT' AND a.transaction_date BETWEEN '$filter_from' AND '$filter_to'
                        GROUP BY a.id
                        UNION
                        SELECT
                            a.id,
                            a.item_fg_id,
                            'DELIVERY' AS trans_type,
                            a.created_by,
                            DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date,
                            a.delivery_note_no AS workorder,
                            0 AS qty_in,
                            0 AS price_in,
                            0 AS amount_in,
                            a.qty AS qty_out
                        FROM delivery_notes a
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and a.status != '2'
                        GROUP BY a.id) a
                        ORDER BY a.item_fg_id, a.trans_date ASC) z
                    LEFT JOIN (SELECT 
                            c.id AS item_fg_id,
                            c.number_customer AS item_fg_number, 
                            (COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) AS begin_qty, 
                            (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END) AS begin_price, 
                            ((COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) * (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END)) AS begin_amount
                        FROM item_fg c
                        LEFT JOIN (
                        SELECT (@no_urut := @no_urut + 1) AS no_urut, z.trans_date,
                            (CASE WHEN @item_fg_id = z.item_fg_id THEN '1' ELSE '0' END) AS typee,
                            @begin_qty := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_qty ELSE 0 END, 2) AS begin_qty,
                            @begin_price := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_price ELSE 0 END, 4) AS begin_price,
                            @begin_amount := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_amount ELSE 0 END, 4) AS begin_amount,
                            @item_fg_id := z.item_fg_id AS item_fg_id,
                            z.qty_in, 
                            ROUND(CASE WHEN z.price_in = 0 THEN @begin_price ELSE z.price_in END, 4) AS price_in,
                            ROUND(CASE WHEN z.amount_in = 0 THEN (z.qty_in * @begin_price) ELSE z.amount_in END, 4) AS amount_in,
                            z.qty_out, 
                            ROUND(CASE WHEN z.qty_in = 0 THEN @begin_price ELSE 0 END, 4) AS price_out, 
                            (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END) AS amount_out,
                            @balance_price := COALESCE(ROUND((@begin_amount + z.amount_in - (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END)) / (@begin_qty + z.qty_in - z.qty_out), 4), 0) AS balance_price,
                            @balance_qty := COALESCE(ROUND(@begin_qty + z.qty_in - z.qty_out, 2), 0) AS balance_qty,
                            @balance_amount := COALESCE(ROUND(@balance_qty * @balance_price, 4), 0) AS balance_amount
                        FROM (
                        SELECT a.* FROM (
                            SELECT
                                a.id,
                                b.item_fg_id,
                                'SCAN FG' AS trans_type,
                                a.created_by,
                                DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date,
                                b.document_no AS workorder,
                                a.qty AS qty_in,
                                c.direct_total AS price_in,
                                (a.qty * c.direct_total) AS amount_in,
                                0 AS qty_out
                            FROM scan_item_receipts_fg a
                            JOIN os_fg b ON a.workorder = b.id
                            LEFT JOIN costing_products c ON b.item_fg_id = c.item_fg_id AND c.periode = '$periode_bf'
                            WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') < '$filter_from'
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                b.item_fg_id,
                                'SCAN FG' AS trans_type,
                                a.created_by,
                                DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date,
                                b.checksheet_number AS workorder,
                                a.qty AS qty_in,
                                c.direct_total AS price_in,
                                (a.qty * c.direct_total) AS amount_in,
                                0 AS qty_out
                            FROM scan_item_receipts_fg a
                            JOIN wip_receipts b ON a.workorder = b.workorder AND a.checksheet_number = b.checksheet_number 
                            LEFT JOIN costing_products c ON b.item_fg_id = c.item_fg_id AND c.periode = '$periode_bf'
                            WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') < '$filter_from'
                            UNION
                            SELECT
                                a.id,
                                a.item_fg_id,
                                'STO FG' AS trans_type,
                                a.created_by,
                                a.trans_date,
                                a.document_no AS workorder,
                                a.qty AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                0 AS qty_out
                            FROM sto_fg a
                            WHERE a.type = 'IN' AND (a.approved_to = '' OR a.approved_to IS NULL) AND a.trans_date < '$filter_from'
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                a.item_fg_id,
                                'STO FG' AS trans_type,
                                a.created_by,
                                a.trans_date,
                                a.document_no AS workorder,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                a.qty AS qty_out
                            FROM sto_fg a
                            WHERE a.type = 'OUT' AND (a.approved_to = '' OR a.approved_to IS NULL) AND a.trans_date < '$filter_from'
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                b.id AS item_fg_id,
                                CONCAT(a.process_type, ' IN') AS trans_type,
                                a.created_by,
                                a.transaction_date AS trans_date,
                                a.number AS workorder,
                                a.qty AS qty_in,
                                a.price AS price_in,
                                (a.qty * a.price) AS amount_in,
                                0 AS qty_out
                            FROM rgp_transactions a
                            JOIN item_fg b ON a.item_id = b.id
                            JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id
                            WHERE a.type = 'IN' AND a.transaction_date < '$filter_from'
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                b.id AS item_fg_id,
                                CONCAT(a.process_type, ' IN') AS trans_type,
                                a.created_by,
                                a.transaction_date AS trans_date,
                                a.number AS workorder,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                a.qty AS qty_out
                            FROM rgp_transactions a
                            JOIN item_fg b ON a.item_id = b.id
                            JOIN scan_item_receipts_fg c ON a.rgp_id = c.checksheet_number AND a.id = c.workorder
                            WHERE a.type = 'OUT' AND a.transaction_date < '$filter_from'
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                a.item_fg_id,
                                'DELIVERY' AS trans_type,
                                a.created_by,
                                DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date,
                                a.delivery_note_no AS workorder,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                a.qty AS qty_out
                            FROM delivery_notes a
                            WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') < '$filter_from' and a.status != '2'
                            GROUP BY a.id) a
                            ORDER BY a.item_fg_id, a.trans_date ASC
                        ) z,
                        (SELECT @no_urut := 0, @balance_qty := 0.0000, @balance_price := 0.0000, @balance_amount := 0.0000) AS zz
                        ORDER BY z.item_fg_id, z.trans_date ASC) zz ON c.id = zz.item_fg_id
                        LEFT JOIN inventory_fg a ON c.id = a.item_fg_id
                        WHERE c.id LIKE '%$filter_items%'
                        GROUP BY c.id) bgin ON z.item_fg_id = bgin.item_fg_id
                    JOIN item_fg c ON z.item_fg_id = c.id,
                    (SELECT @no_urut := 0, @item_fg_id := 0) AS za
                    WHERE z.item_fg_id LIKE '%$filter_items%'
                    ORDER BY z.item_fg_id, z.trans_date, no_urut ASC) zz ON zz.item_fg_id = c.id
                    WHERE c.id LIKE '%$filter_items%'
                    GROUP BY c.id
                    ORDER BY c.number asc"
                );
    
                $html .= '<table id="customers" border="1" style="width: 100%">
                                <tr>
                                    <th width="10" rowspan="2"width="20">No</th>
                                    <th width="100" rowspan="2">Product No</th>
                                    <th width="200" rowspan="2">Product Name</th>
                                    <th width="50" rowspan="2">Uom</th>
                                    <th colspan="3">Begin</th>
                                    <th colspan="3">In</th>
                                    <th colspan="3">Out</th>
                                    <th colspan="3">Ending</th>
                                </tr>
                                <tr>
                                    <th width="50">Qty</th>
                                    <th width="50">Price</th>
                                    <th width="50">Amount</th>
                                    <th width="50">Qty</th>
                                    <th width="50">Price</th>
                                    <th width="50">Amount</th>
                                    <th width="50">Qty</th>
                                    <th width="50">Price</th>
                                    <th width="50">Amount</th>
                                    <th width="50">Qty</th>
                                    <th width="50">Price</th>
                                    <th width="50">Amount</th>
                                </tr>';
                $noh = 1;
                $total_begin_qty = 0;
                $total_begin_amount = 0;
                $total_in_qty = 0;
                $total_in_amount = 0;
                $total_out_qty = 0;
                $total_out_amount = 0;
                $total_bal_qty = 0;
                $total_bal_amount = 0;
                foreach ($sql_begin as $itemReceipt) {
                    $html .= '  <tr>
                                    <td>' . $noh . '</td>
                                    <td style="mso-number-format:\@;">' . $itemReceipt->item_fg_number . '</td>
                                    <td style="mso-number-format:\@;">' . $itemReceipt->item_fg_name . '</td>
                                    <td>' . $itemReceipt->uom . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_qty) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_price) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_amount) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->qty_in) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->price_in) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->amount_in) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->qty_out) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->price_out) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->amount_out) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->bal_qty) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->bal_price) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->bal_amount) . '</td>
                                </tr>';
    
                    $total_begin_qty += $itemReceipt->begin_qty;
                    $total_begin_amount += $itemReceipt->begin_amount;
                    $total_in_qty += $itemReceipt->qty_in;
                    $total_in_amount += $itemReceipt->amount_in;
                    $total_out_qty += $itemReceipt->qty_out;
                    $total_out_amount += $itemReceipt->amount_out;
                    $total_bal_qty += ($itemReceipt->bal_qty);
                    $total_bal_amount += ($itemReceipt->bal_amount);
                    $noh++;
                }
    
                $html .= '  <tr>
                                <td colspan="4" style="text-align:right;"><b>GRAND TOTAL</b></td>
                                <td style="text-align:right;"><b>' . $this->formatNo($total_begin_qty) . '</b></td>
                                <td style="text-align:right;">-</td>
                                <td style="text-align:right;"><b>' . $this->formatNo($total_begin_amount) . '</b></td>
                                <td style="text-align:right;"><b>' . $this->formatNo($total_in_qty) . '</b></td>
                                <td style="text-align:right;">-</td>
                                <td style="text-align:right;"><b>' . $this->formatNo($total_in_amount) . '</b></td>
                                <td style="text-align:right;"><b>' . $this->formatNo($total_out_qty) . '</b></td>
                                <td style="text-align:right;">-</td>
                                <td style="text-align:right;"><b>' . $this->formatNo($total_out_amount) . '</b></td>
                                <td style="text-align:right;"><b>' . $this->formatNo($total_bal_qty) . '</b></td>
                                <td style="text-align:right;">-</td>
                                <td style="text-align:right;"><b>' . $this->formatNo($total_bal_amount) . '</b></td>
                            </tr>';
                $html .= '</table></body></html>';
            } else {
                $itemReceipts = $this->crud->query("SELECT (@no_urut := @no_urut + 1) AS no_urut, 
                    c.number_customer AS item_fg_number, c.name AS item_fg_name, c.uom, z.created_by,
                    z.trans_type, z.trans_date, z.workorder, 
                    (CASE WHEN @item_fg_id = z.item_fg_id THEN '1' ELSE '0' END) AS typee,
                    @begin_qty := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_qty ELSE 0 END, 2) AS begin_qty,
                    @begin_price := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_price ELSE COALESCE(bgin.begin_price, 0) END, 4) AS begin_price,
                    @begin_amount := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_amount ELSE 0 END, 4) AS begin_amount,
                    @item_fg_id := z.item_fg_id AS item_fg_id,
                    z.qty_in, 
                    ROUND(CASE WHEN z.price_in = 0 THEN @begin_price ELSE z.price_in END, 4) AS price_in,
                    ROUND(CASE WHEN z.amount_in = 0 THEN (z.qty_in * @begin_price) ELSE z.amount_in END, 4) AS amount_in,
                    z.qty_out, 
                    ROUND(CASE WHEN z.qty_in = 0 THEN @begin_price ELSE 0 END, 4) AS price_out, 
                    (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END) AS amount_out,
                    @balance_price := (CASE WHEN z.amount_in = 0 THEN @begin_price ELSE COALESCE(ROUND((@begin_amount + z.amount_in - (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END)) / (@begin_qty + z.qty_in - z.qty_out), 2), 0) END) AS balance_price,
                    @balance_qty := COALESCE(ROUND(@begin_qty + z.qty_in - z.qty_out, 2), 0) AS balance_qty,
                    @balance_amount := COALESCE(ROUND(@balance_qty * @balance_price, 4), 0) AS balance_amount
                FROM (SELECT a.* FROM (
                    SELECT
                        a.id,
                        b.item_fg_id,
                        'STO FG' AS trans_type,
                        a.created_by,
                        b.trans_date,
                        b.document_no AS workorder,
                        SUM(a.qty) AS qty_in,
                        COALESCE(c.direct_total, 0) AS price_in,
                        (SUM(a.qty) * COALESCE(c.direct_total, 0)) AS amount_in,
                        0 AS qty_out
                    FROM os_fg b
                    JOIN scan_item_receipts_fg a ON a.workorder = b.id
                    LEFT JOIN costing_products c ON b.item_fg_id = c.item_fg_id AND c.periode = '$periode'
                    WHERE b.trans_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY b.id
                    UNION
                    SELECT
                        a.id,
                        b.item_fg_id,
                        'SCAN FG' AS trans_type,
                        a.created_by,
                        a.created_date AS trans_date,
                        a.checksheet_label AS workorder,
                        a.qty AS qty_in,
                        COALESCE(c.direct_total, 0) AS price_in,
                        (a.qty * COALESCE(c.direct_total, 0)) AS amount_in,
                        0 AS qty_out
                    FROM wip_receipts b
                    JOIN scan_item_receipts_fg a ON a.workorder = b.workorder AND a.checksheet_number = b.checksheet_number 
                    LEFT JOIN costing_products c ON b.item_fg_id = c.item_fg_id AND c.periode = '$periode'
                    WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                    UNION
                    SELECT
                        a.id,
                        a.item_fg_id,
                        'ADJUSTMENT IN' AS trans_type,
                        a.created_by,
                        a.trans_date,
                        a.document_no AS workorder,
                        a.qty AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        0 AS qty_out
                    FROM sto_fg a
                    WHERE a.type = 'IN' AND (a.approved_to = '' OR a.approved_to IS NULL) AND a.trans_date BETWEEN '$filter_from' AND '$filter_to'
                    UNION
                    SELECT
                        a.id,
                        a.item_fg_id,
                        'ADJUSTMENT OUT' AS trans_type,
                        a.created_by,
                        a.trans_date,
                        a.document_no AS workorder,
                        0 AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        a.qty AS qty_out
                    FROM sto_fg a
                    WHERE a.type = 'OUT' AND (a.approved_to = '' OR a.approved_to IS NULL) AND a.trans_date BETWEEN '$filter_from' AND '$filter_to'
                    UNION
                    SELECT
                        a.id,
                        b.id AS item_fg_id,
                        CONCAT(a.process_type, ' IN') AS trans_type,
                        a.created_by,
                        a.transaction_date AS trans_date,
                        a.number AS workorder,
                        a.qty AS qty_in,
                        a.price AS price_in,
                        (a.qty * a.price) AS amount_in,
                        0 AS qty_out
                    FROM rgp_transactions a
                    JOIN item_fg b ON a.item_id = b.id
                    JOIN scan_item_receipts_fg c ON a.rgp_id = c.checksheet_number AND a.id = c.workorder
                    WHERE a.type = 'IN' AND a.transaction_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY a.id
                    UNION
                    SELECT
                        a.id,
                        b.id AS item_fg_id,
                        CONCAT(a.process_type, ' IN') AS trans_type,
                        a.created_by,
                        a.transaction_date AS trans_date,
                        a.number AS workorder,
                        0 AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        a.qty AS qty_out
                    FROM rgp_transactions a
                    JOIN item_fg b ON a.item_id = b.id
                    JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id
                    WHERE a.type = 'OUT' AND a.transaction_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY a.id
                    UNION
                    SELECT
                        a.id,
                        a.item_fg_id,
                        'DELIVERY' AS trans_type,
                        a.created_by,
                        a.created_date AS trans_date,
                        a.delivery_note_no AS workorder,
                        0 AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        a.qty AS qty_out
                    FROM delivery_notes a
                    WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and a.status != '2') a
                    ORDER BY a.item_fg_id, a.trans_date ASC) z
                LEFT JOIN (SELECT 
                        c.id AS item_fg_id,
                        c.number_customer AS item_fg_number, 
                        (COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) AS begin_qty, 
                        (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END) AS begin_price, 
                        ((COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) * (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END)) AS begin_amount
                    FROM item_fg c
                    LEFT JOIN (
                    SELECT (@no_urut := @no_urut + 1) AS no_urut, z.trans_date,
                        (CASE WHEN @item_fg_id = z.item_fg_id THEN '1' ELSE '0' END) AS typee,
                        @begin_qty := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_qty ELSE 0 END, 2) AS begin_qty,
                        @begin_price := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_price ELSE 0 END, 4) AS begin_price,
                        @begin_amount := ROUND(CASE WHEN @item_fg_id = z.item_fg_id THEN @balance_amount ELSE 0 END, 4) AS begin_amount,
                        @item_fg_id := z.item_fg_id AS item_fg_id,
                        z.qty_in, 
                        ROUND(CASE WHEN z.price_in = 0 THEN @begin_price ELSE z.price_in END, 4) AS price_in,
                        ROUND(CASE WHEN z.amount_in = 0 THEN (z.qty_in * @begin_price) ELSE z.amount_in END, 4) AS amount_in,
                        z.qty_out, 
                        ROUND(CASE WHEN z.qty_in = 0 THEN @begin_price ELSE 0 END, 4) AS price_out, 
                        (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END) AS amount_out,
                        @balance_price := COALESCE(ROUND((@begin_amount + z.amount_in - (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END)) / (@begin_qty + z.qty_in - z.qty_out), 4), 0) AS balance_price,
                        @balance_qty := COALESCE(ROUND(@begin_qty + z.qty_in - z.qty_out, 2), 0) AS balance_qty,
                        @balance_amount := COALESCE(ROUND(@balance_qty * @balance_price, 4), 0) AS balance_amount
                    FROM (
                    SELECT a.* FROM (
                        SELECT
                            a.id,
                            b.item_fg_id,
                            'STO FG' AS trans_type,
                            a.created_by,
                            b.trans_date,
                            b.document_no AS workorder,
                            SUM(a.qty) AS qty_in,
                            COALESCE(c.direct_total, 0) AS price_in,
                            (SUM(a.qty) * COALESCE(c.direct_total, 0)) AS amount_in,
                            0 AS qty_out
                        FROM os_fg b
                        JOIN scan_item_receipts_fg a ON a.workorder = b.id
                        LEFT JOIN costing_products c ON b.item_fg_id = c.item_fg_id AND c.periode = '$periode_bf'
                        WHERE b.trans_date < '$filter_from'
                        GROUP BY b.id
                        UNION
                        SELECT
                            a.id,
                            b.item_fg_id,
                            'SCAN FG' AS trans_type,
                            a.created_by,
                            a.created_date AS trans_date,
                            a.checksheet_label AS workorder,
                            a.qty AS qty_in,
                            COALESCE(c.direct_total, 0) AS price_in,
                            (a.qty * COALESCE(c.direct_total, 0)) AS amount_in,
                            0 AS qty_out
                        FROM wip_receipts b
                        JOIN scan_item_receipts_fg a ON a.workorder = b.workorder AND a.checksheet_number = b.checksheet_number 
                        LEFT JOIN costing_products c ON b.item_fg_id = c.item_fg_id AND c.periode = '$periode_bf'
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') < '$filter_from'
                        UNION
                        SELECT
                            a.id,
                            a.item_fg_id,
                            'ADJUSTMENT IN' AS trans_type,
                            a.created_by,
                            a.trans_date,
                            a.document_no AS workorder,
                            a.qty AS qty_in,
                            0 AS price_in,
                            0 AS amount_in,
                            0 AS qty_out
                        FROM sto_fg a
                        WHERE a.type = 'IN' AND (a.approved_to = '' OR a.approved_to IS NULL) AND a.trans_date < '$filter_from'
                        UNION
                        SELECT
                            a.id,
                            a.item_fg_id,
                            'ADJUSTMENT OUT' AS trans_type,
                            a.created_by,
                            a.trans_date,
                            a.document_no AS workorder,
                            0 AS qty_in,
                            0 AS price_in,
                            0 AS amount_in,
                            a.qty AS qty_out
                        FROM sto_fg a
                        WHERE a.type = 'OUT' AND (a.approved_to = '' OR a.approved_to IS NULL) AND a.trans_date < '$filter_from'
                        UNION
                        SELECT
                            a.id,
                            b.id AS item_fg_id,
                            CONCAT(a.process_type, ' IN') AS trans_type,
                            a.created_by,
                            a.transaction_date AS trans_date,
                            a.number AS workorder,
                            a.qty AS qty_in,
                            a.price AS price_in,
                            (a.qty * a.price) AS amount_in,
                            0 AS qty_out
                        FROM rgp_transactions a
                        JOIN item_fg b ON a.item_id = b.id
                        JOIN scan_item_receipts_fg c ON a.rgp_id = c.checksheet_number AND a.id = c.workorder
                        WHERE a.type = 'IN' AND a.transaction_date < '$filter_from'
                        GROUP BY a.id
                        UNION
                        SELECT
                            a.id,
                            b.id AS item_fg_id,
                            CONCAT(a.process_type, ' IN') AS trans_type,
                            a.created_by,
                            a.transaction_date AS trans_date,
                            a.number AS workorder,
                            0 AS qty_in,
                            0 AS price_in,
                            0 AS amount_in,
                            a.qty AS qty_out
                        FROM rgp_transactions a
                        JOIN item_fg b ON a.item_id = b.id
                        JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id
                        WHERE a.type = 'OUT' AND a.transaction_date < '$filter_from'
                        GROUP BY a.id
                        UNION
                        SELECT
                            a.id,
                            a.item_fg_id,
                            'DELIVERY' AS trans_type,
                            a.created_by,
                            a.created_date AS trans_date,
                            a.delivery_note_no AS workorder,
                            0 AS qty_in,
                            0 AS price_in,
                            0 AS amount_in,
                            a.qty AS qty_out
                        FROM delivery_notes a
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') < '$filter_from' and a.status != '2'
                        ) a
                        ORDER BY a.item_fg_id, a.trans_date ASC
                    ) z,
                    (SELECT @no_urut := 0, @balance_qty := 0.0000, @balance_price := 0.0000, @balance_amount := 0.0000) AS zz
                    ORDER BY z.item_fg_id, z.trans_date ASC) zz ON c.id = zz.item_fg_id
                    LEFT JOIN inventory_fg a ON c.id = a.item_fg_id
                    WHERE c.id LIKE '%$filter_items%') bgin ON z.item_fg_id = bgin.item_fg_id
                JOIN item_fg c ON z.item_fg_id = c.id,
                (SELECT @no_urut := 0, @item_fg_id := 0) AS za
                WHERE z.item_fg_id LIKE '%$filter_items%'
                ORDER BY z.item_fg_id, z.trans_date, no_urut ASC");
    
                $html .= '<table id="customers" style="width: 100%">
                            <tr>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2">Product No</th>
                                <th rowspan="2">Product Name</th>
                                <th rowspan="2">Trans Type</th>
                                <th rowspan="2">Created By</th>
                                <th rowspan="2">Trans Date</th>
                                <th rowspan="2">Document No</th>
                                <th colspan="3">Begin</th>
                                <th colspan="3">In</th>
                                <th colspan="3">Out</th>
                                <th colspan="3">Ending</th>
                            </tr>
                            <tr>
                                <th width="50">Qty</th>
                                <th width="50">Price</th>
                                <th width="50">Amount</th>
                                <th width="50">Qty</th>
                                <th width="50">Price</th>
                                <th width="50">Amount</th>
                                <th width="50">Qty</th>
                                <th width="50">Price</th>
                                <th width="50">Amount</th>
                                <th width="50">Qty</th>
                                <th width="50">Price</th>
                                <th width="50">Amount</th>
                            </tr>';
                $nod = 1;
                $last_item_rm_id = "";
                foreach ($itemReceipts as $itemReceipt) {
                    $item_fg_id = $itemReceipt->item_fg_id;
                    if($item_fg_id != $last_item_rm_id){
    
                        $html .= '  <tr style="background: #aae9b5;">
                                        <td colspan="16" style="font-weight:bold;">BEGINING STOCK ('.$itemReceipt->item_fg_number.')</td>
                                        <td style="text-align:right; font-weight:bold;">' . $this->formatNo($itemReceipt->begin_qty) . '</td>
                                        <td style="text-align:right; font-weight:bold;">' . $this->formatNo($itemReceipt->begin_price) . '</td>
                                        <td colspan="2" style="text-align:right; font-weight:bold;">' . $this->formatNo($itemReceipt->begin_amount) . '</td>
                                    </tr>';
                    }
    
                    $html .= '  <tr>
                                    <td>' . $nod . '</td>
                                    <td style="mso-number-format:\@;">' . $itemReceipt->item_fg_number . '</td>
                                    <td style="mso-number-format:\@;">' . $itemReceipt->item_fg_name . '</td>
                                    <td>' . $itemReceipt->trans_type . '</td>
                                    <td>' . $itemReceipt->created_by . '</td>
                                    <td>' . $itemReceipt->trans_date . '</td>
                                    <td>' . $itemReceipt->workorder . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_qty) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_price) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_amount) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->qty_in) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->price_in) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->amount_in) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->qty_out) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->price_out) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->amount_out) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->balance_qty) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->balance_price) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($itemReceipt->balance_amount) . '</td>
                                </tr>';
    
                    $last_item_rm_id = $item_fg_id;
                    $nod++;
                }
            }

        $html .= '</table></body></html>';
        echo $html;
    }
}
