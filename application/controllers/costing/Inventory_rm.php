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
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('costing/inventory_rm');
        } else {
            redirect('error_access');
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
        $this->db->where("item_category_id", "C01");
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
            header("Content-Disposition: attachment; filename=stock_values_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

        $period = date("Y-m", strtotime($filter_from));

        // $inventory_rm = $this->crud->reads("inventory_rm", [], ['period' => $period]);

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

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
                                <small>REPORT INVENTORY HISTORY TRANSACTION</small><br>
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
                    c.number AS item_rm_number, c.name AS item_rm_name, c.uom, d.name AS item_family_name,
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
                FROM (SELECT (@no_urut := @no_urut + 1) AS no_urut, z.receipt_date, 
                        (CASE WHEN @item_rm_id = a.id THEN '1' ELSE '0' END) AS typee,
                        @begin_qty := ROUND(CASE WHEN @item_rm_id = a.id THEN @balance_qty ELSE COALESCE(bgin.begin_qty, 0) END, 2) AS begin_qty,
                        @begin_price := ROUND(CASE WHEN @item_rm_id = a.id THEN @balance_price ELSE COALESCE(bgin.begin_price, 0) END, 4) AS begin_price,
                        @begin_amount := ROUND(CASE WHEN @item_rm_id = a.id THEN @balance_amount ELSE COALESCE(bgin.begin_amount, 0) END, 4) AS begin_amount,
                        @item_rm_id := a.id AS item_rm_id,
                        COALESCE(z.qty_in, 0) AS qty_in, 
                        COALESCE(z.price_in, 0) AS price_in, 
                        COALESCE(z.amount_in, 0) AS amount_in,
                        COALESCE(z.qty_out, 0) AS qty_out,
                        ROUND(CASE WHEN z.qty_in = 0 THEN @begin_price ELSE 0 END, 4) AS price_out, 
                        (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END) AS amount_out,
                        @balance_price := COALESCE(ROUND((@begin_amount + COALESCE(z.amount_in, 0) - (CASE WHEN COALESCE(z.qty_in, 0) = 0 THEN ROUND(COALESCE(z.qty_out, 0) * @begin_price, 4) ELSE 0 END)) / (@begin_qty + COALESCE(z.qty_in, 0) - COALESCE(z.qty_out, 0)), 2), 0) AS balance_price,
                        @balance_qty := COALESCE(ROUND(@begin_qty + COALESCE(z.qty_in, 0) - COALESCE(z.qty_out, 0), 2), 0) AS balance_qty,
                        @balance_amount := COALESCE(ROUND(@balance_qty * @balance_price, 4), 0) AS balance_amount
                    FROM item_rm a
                    LEFT JOIN (
                        SELECT a.* FROM (
                            SELECT
                                a.id,
                                a.item_rm_id,
                                a.receipt_date,
                                c.qty_receipt AS qty_in,
                                b.price AS price_in,
                                (c.qty_receipt * b.price) AS amount_in,
                                0 AS qty_out
                            FROM purchase_order_receipts a
                            LEFT JOIN purchase_orders b ON a.item_rm_id = b.item_rm_id AND a.po_no = b.po_no
                            JOIN (SELECT receipt_id, SUM(qty) as qty_receipt FROM scan_item_receipts GROUP BY receipt_id) c ON a.receipt_id = c.receipt_id
                            WHERE a.receipt_date BETWEEN '$filter_from' and '$filter_to'
                            GROUP BY a.id
                            UNION
                            SELECT
                                id,
                                item_rm_id,
                                DATE_FORMAT(created_date, '%Y-%m-%d') AS receipt_date,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                qty AS qty_out
                            FROM issued_material_details
                            WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'
                            GROUP BY id
                            UNION
                            SELECT 
                                a.id,
                                a.item_rm_id, 
                                a.trans_date,
                                COALESCE(SUM(a.qty), 0) AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                0 AS qty_out
                            FROM sto_rm a 
                            WHERE a.type = 'IN' AND a.trans_date BETWEEN '$filter_from' and '$filter_to' AND a.approved_to = ''
                            GROUP BY a.id
                            UNION
                            SELECT 
                                a.id,
                                a.item_rm_id, 
                                a.trans_date,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                COALESCE(SUM(a.qty), 0) AS qty_out
                            FROM sto_rm a 
                            WHERE a.type = 'OUT' AND a.trans_date BETWEEN '$filter_from' and '$filter_to' AND a.approved_to = ''
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                a.item_id AS item_rm_id,
                                a.transaction_date AS trans_date,
                                a.qty AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                0 AS qty_out 
                            FROM rgp_transactions a
                            WHERE a.type = 'IN' AND a.transaction_date BETWEEN '$filter_from' and '$filter_to'
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                a.item_id AS item_rm_id,
                                a.transaction_date AS trans_date,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                a.qty AS qty_out 
                            FROM rgp_transactions a
                            WHERE a.type = 'OUT' AND a.transaction_date BETWEEN '$filter_from' and '$filter_to'
                            GROUP BY a.id
                            UNION
                            SELECT 
                                b.id,
                                a.item_rm_id, 
                                DATE_FORMAT(b.created_date, '%Y-%m-%d') AS trans_date,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                COALESCE(SUM(b.qty), 0) AS qty_out 
                            FROM purchase_order_receipts a 
                            JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id 
                            WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' 
                            GROUP BY b.id
                            UNION
                            SELECT
                                a.id,
                                a.item_rm_id,
                                a.trans_date,
                                a.qty AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                0 AS qty_out
                            FROM os_rm a
                            WHERE a.trans_date BETWEEN '$filter_from' and '$filter_to' AND a.type = 'STO'
                            GROUP BY a.id
                            ) a
                            ORDER BY a.item_rm_id, a.receipt_date ASC
                        ) z ON a.id = z.item_rm_id
                    LEFT JOIN (
                        SELECT 
                            c.id as item_rm_id, 
                            (COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) AS begin_qty, 
                            (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END) AS begin_price, 
                            ((COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) * (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END)) AS begin_amount
                        FROM item_rm c
                        LEFT JOIN (
                            SELECT (@no_urut := @no_urut + 1) AS no_urut, z.receipt_date,
                                (CASE WHEN @item_rm_id = z.item_rm_id THEN '1' ELSE '0' END) AS typee,
                                @begin_qty := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_qty ELSE 0 END, 2) AS begin_qty,
                                @begin_price := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_price ELSE 0 END, 4) AS begin_price,
                                @begin_amount := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_amount ELSE 0 END, 4) AS begin_amount,
                                @item_rm_id := z.item_rm_id AS item_rm_id,
                                z.qty_in, 
                                z.price_in, 
                                z.amount_in,
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
                                    a.item_rm_id,
                                    a.receipt_date,
                                    c.qty_receipt AS qty_in,
                                    b.price AS price_in,
                                    (c.qty_receipt * b.price) AS amount_in,
                                    0 AS qty_out
                                FROM purchase_order_receipts a
                                LEFT JOIN purchase_orders b ON a.item_rm_id = b.item_rm_id AND a.po_no = b.po_no
                                JOIN (SELECT receipt_id, SUM(qty) as qty_receipt FROM scan_item_receipts GROUP BY receipt_id) c ON a.receipt_id = c.receipt_id
                                WHERE a.receipt_date < '$filter_from'
                                GROUP BY a.id
                                UNION
                                SELECT
                                    id,
                                    item_rm_id,
                                    DATE_FORMAT(created_date, '%Y-%m-%d') AS receipt_date,
                                    0 AS qty_in,
                                    0 AS price_in,
                                    0 AS amount_in,
                                    qty AS qty_out
                                FROM issued_material_details
                                WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from'
                                GROUP BY id
                                UNION
                                SELECT 
                                    a.id,
                                    a.item_rm_id, 
                                    a.trans_date,
                                    COALESCE(SUM(a.qty), 0) AS qty_in,
                                    0 AS price_in,
                                    0 AS amount_in,
                                    0 AS qty_out
                                FROM sto_rm a 
                                WHERE a.type = 'IN' AND a.trans_date < '$filter_from' AND a.approved_to = ''
                                GROUP BY a.id
                                UNION
                                SELECT 
                                    a.id,
                                    a.item_rm_id, 
                                    a.trans_date,
                                    0 AS qty_in,
                                    0 AS price_in,
                                    0 AS amount_in,
                                    COALESCE(SUM(a.qty), 0) AS qty_out
                                FROM sto_rm a 
                                WHERE a.type = 'OUT' AND a.trans_date < '$filter_from' AND a.approved_to = ''
                                GROUP BY a.id
                                UNION
                                SELECT
                                    a.id,
                                    a.item_id AS item_rm_id,
                                    a.transaction_date AS trans_date,
                                    a.qty AS qty_in,
                                    0 AS price_in,
                                    0 AS amount_in,
                                    0 AS qty_out 
                                FROM rgp_transactions a
                                WHERE a.type = 'IN' AND a.transaction_date < '$filter_from'
                                GROUP BY a.id
                                UNION
                                SELECT
                                    a.id,
                                    a.item_id AS item_rm_id,
                                    a.transaction_date AS trans_date,
                                    0 AS qty_in,
                                    0 AS price_in,
                                    0 AS amount_in,
                                    a.qty AS qty_out 
                                FROM rgp_transactions a
                                WHERE a.type = 'OUT' AND a.transaction_date < '$filter_from'
                                GROUP BY a.id
                                UNION
                                SELECT 
                                    b.id,
                                    a.item_rm_id, 
                                    DATE_FORMAT(b.created_date, '%Y-%m-%d') AS trans_date,
                                    0 AS qty_in,
                                    0 AS price_in,
                                    0 AS amount_in,
                                    COALESCE(SUM(b.qty), 0) AS qty_out 
                                FROM purchase_order_receipts a 
                                JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id 
                                WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$filter_from' 
                                GROUP BY b.id
                                UNION
                                SELECT
                                    a.id,
                                    a.item_rm_id,
                                    a.trans_date,
                                    a.qty AS qty_in,
                                    0 AS price_in,
                                    0 AS amount_in,
                                    0 AS qty_out
                                FROM os_rm a
                                WHERE a.trans_date < '$filter_from' AND a.type = 'STO'
                                GROUP BY a.id
                                ) a
                                ORDER BY a.item_rm_id, a.receipt_date ASC
                            ) z,
                        (SELECT @no_urut := 0, @balance_qty := 0.0000, @balance_price := 0.0000, @balance_amount := 0.0000) AS zz
                        ORDER BY z.item_rm_id, z.receipt_date ASC) zz ON c.id = zz.item_rm_id
                        LEFT JOIN inventory_rm a ON c.id = a.item_rm_id
                        WHERE c.id LIKE '%$filter_items%' AND c.item_family_id LIKE '%$filter_item_family%' AND c.item_category_id = 'C01'
                    GROUP BY c.id) bgin ON a.id = bgin.item_rm_id,
                    (SELECT @no_urut := 0, @item_rm_id := 0) AS za
                WHERE a.id LIKE '%$filter_items%' AND a.item_family_id LIKE '%$filter_item_family%' AND a.item_category_id = 'C01'
                ORDER BY z.item_rm_id, z.receipt_date, no_urut ASC) zz
                JOIN item_rm c ON zz.item_rm_id = c.id
                JOIN item_familys d ON c.item_family_id = d.id
                GROUP BY c.id
                ORDER BY d.number, c.number asc"
            );

            $html .= '<table id="customers" border="1" style="width: 100%">
                            <tr>
                                <th width="10" rowspan="2"width="20">No</th>
                                <th width="100" rowspan="2">Part No EBWS</th>
                                <th width="200" rowspan="2">Part No Supplier</th>
                                <th width="200" rowspan="2">Description</th>
                                <th width="50" rowspan="2">Uom</th>
                                <th width="100" rowspan="2">Product Family</th>
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
                                <td>' . $itemReceipt->item_rm_id . '</td>
                                <td style="mso-number-format:\@;">' . $itemReceipt->item_rm_number . '</td>
                                <td style="mso-number-format:\@;">' . $itemReceipt->item_rm_name . '</td>
                                <td>' . $itemReceipt->uom . '</td>
                                <td>' . $itemReceipt->item_family_name . '</td>
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
                            <td colspan="6" style="text-align:right;"><b>GRAND TOTAL</b></td>
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
                    c.number as item_rm_number, c.name as item_rm_name, c.uom, d.name as item_family_name, z.created_by,
                    z.trans_type, z.po_date, z.receipt_date, z.receipt_no, z.po_no, z.bc_document, z.bc_date,
                    (CASE WHEN @item_rm_id = z.item_rm_id THEN '1' ELSE '0' END) AS typee,
                    @begin_qty := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_qty ELSE COALESCE(bgin.begin_qty, 0) END, 2) AS begin_qty,
                    @begin_price := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_price ELSE COALESCE(bgin.begin_price, 0) END, 4) AS begin_price,
                    @begin_amount := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_amount ELSE COALESCE(bgin.begin_amount, 0) END, 4) AS begin_amount,
                    @item_rm_id := z.item_rm_id AS item_rm_id,
                    z.qty_in, 
                    ROUND(CASE WHEN z.price_in = 0 THEN @begin_price ELSE z.price_in END, 4) as price_in,
                    ROUND(CASE WHEN z.amount_in = 0 THEN (z.qty_in * @begin_price) ELSE z.amount_in END, 4) as amount_in,
                    z.qty_out, 
                    ROUND(CASE WHEN z.qty_in = 0 THEN @begin_price ELSE 0 END, 4) AS price_out, 
                    (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END) AS amount_out,
                    @balance_price := COALESCE(ROUND((@begin_amount + z.amount_in - (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END)) / (@begin_qty + z.qty_in - z.qty_out), 2), 0) AS balance_price,
                    @balance_qty := COALESCE(ROUND(@begin_qty + z.qty_in - z.qty_out, 2), 0) AS balance_qty,
                    @balance_amount := COALESCE(ROUND(@balance_qty * @balance_price, 4), 0) AS balance_amount
                FROM (SELECT a.* FROM (
                    SELECT
                        a.id,
                        a.item_rm_id,
                        a.receipt_date,
                        'RECEIPT' AS trans_type,
                        b.po_date,
                        a.receipt_no,
                        b.po_no,
                        a.bc_document,
                        a.created_by,
                        a.bc_date,
                        c.qty_receipt AS qty_in,
                        b.price AS price_in,
                        (c.qty_receipt * b.price) AS amount_in,
                        0 AS qty_out
                    FROM purchase_order_receipts a
                    LEFT JOIN purchase_orders b ON a.item_rm_id = b.item_rm_id AND a.po_no = b.po_no
                    JOIN (SELECT receipt_id, SUM(qty) as qty_receipt FROM scan_item_receipts GROUP BY receipt_id) c ON a.receipt_id = c.receipt_id
                    WHERE a.receipt_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY a.id
                    UNION
                    SELECT
                        id,
                        item_rm_id,
                        DATE_FORMAT(created_date, '%Y-%m-%d') AS receipt_date,
                        'ISSUED' AS trans_type,
                        '-' AS po_date,
                        '-' AS receipt_no,
                        request_no AS po_no,
                        label_no AS bc_document,
                        created_by,
                        '-' AS bc_date,
                        0 AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        qty AS qty_out
                    FROM issued_material_details
                    WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY id
                    UNION
                    SELECT 
                        a.id,
                        a.item_rm_id, 
                        a.trans_date AS receipt_date,
                        'ADJUSTMENT IN' AS trans_type,
                        '-' AS po_date,
                        '-' AS receipt_no,
                        '-' AS po_no,
                        a.document_no AS bc_document,
                        a.created_by,
                        '-' AS bc_date,
                        COALESCE(SUM(a.qty), 0) AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        0 AS qty_out
                    FROM sto_rm a 
                    WHERE a.type = 'IN' AND a.trans_date BETWEEN '$filter_from' AND '$filter_to' AND a.approved_to = ''
                    GROUP BY a.id
                    UNION
                    SELECT 
                        a.id,
                        a.item_rm_id, 
                        a.trans_date AS receipt_date,
                        'ADJUSTMENT OUT' AS trans_type,
                        '-' AS po_date,
                        '-' AS receipt_no,
                        '-' AS po_no,
                        a.document_no AS bc_document,
                        a.created_by,
                        '-' AS bc_date,
                        0 AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        COALESCE(SUM(a.qty), 0) AS qty_out
                    FROM sto_rm a 
                    WHERE a.type = 'OUT' AND a.trans_date BETWEEN '$filter_from' AND '$filter_to' AND a.approved_to = ''
                    GROUP BY a.id
                    UNION
                    SELECT
                        a.id,
                        a.item_id AS item_rm_id,
                        a.transaction_date AS receipt_date,
                        CONCAT(a.process_type, ' ', a.type) AS trans_type,
                        '-' AS po_date,
                        a.number AS receipt_no,
                        '-' AS po_no,
                        a.reference_number AS bc_document,
                        a.created_by,
                        '-' AS bc_date,
                        a.qty AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        0 AS qty_out 
                    FROM rgp_transactions a
                    WHERE a.type = 'IN' AND a.transaction_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY a.id
                    UNION
                    SELECT
                        a.id,
                        a.item_id AS item_rm_id,
                        a.transaction_date AS receipt_date,
                        CONCAT(a.process_type, ' ', a.type) AS trans_type,
                        '-' AS po_date,
                        a.number AS receipt_no,
                        '-' AS po_no,
                        a.reference_number AS bc_document,
                        a.created_by,
                        '-' AS bc_date,
                        0 AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        a.qty AS qty_out 
                    FROM rgp_transactions a
                    WHERE a.type = 'OUT' AND a.transaction_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY a.id
                    UNION
                    SELECT 
                        b.id,
                        a.item_rm_id, 
                        DATE_FORMAT(b.created_date, '%Y-%m-%d') AS trans_date,
                        'REJECTION' AS trans_type,
                        '-' AS po_date,
                        a.receipt_no,
                        a.po_no,
                        a.bc_document,
                        a.created_by,
                        a.bc_date,
                        0 AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        COALESCE(SUM(b.qty), 0) AS qty_out 
                    FROM purchase_order_receipts a 
                    JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id 
                    WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY b.id
                    UNION
                    SELECT
                        a.id,
                        a.item_rm_id,
                        a.trans_date as receipt_date,
                        'STO' as trans_type,
                        '-' AS po_date,
                        '-' AS receipt_no,
                        '-' AS po_no,
                        document_no AS bc_document,
                        created_by,
                        '-' AS bc_date,
                        a.qty AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        0 AS qty_out
                    FROM os_rm a
                    WHERE a.trans_date BETWEEN '$filter_from' AND '$filter_to' AND a.type = 'STO'
                    GROUP BY a.id) a
                    ORDER BY a.item_rm_id, a.receipt_date ASC) z
                LEFT JOIN (SELECT 
                        c.id as item_rm_id, 
                        (COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) AS begin_qty, 
                        (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END) as begin_price, 
                        ((COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) * (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END)) AS begin_amount
                    FROM item_rm c
                    LEFT JOIN (
                        SELECT (@no_urut := @no_urut + 1) AS no_urut, z.receipt_date,
                            (CASE WHEN @item_rm_id = z.item_rm_id THEN '1' ELSE '0' END) AS typee,
                            @begin_qty := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_qty ELSE 0 END, 2) AS begin_qty,
                            @begin_price := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_price ELSE 0 END, 4) AS begin_price,
                            @begin_amount := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_amount ELSE 0 END, 4) AS begin_amount,
                            @item_rm_id := z.item_rm_id AS item_rm_id,
                            z.qty_in, 
                            ROUND(CASE WHEN z.price_in = 0 THEN @begin_price ELSE z.price_in END, 4) as price_in,
                            ROUND(CASE WHEN z.amount_in = 0 THEN (z.qty_in * @begin_price) ELSE z.amount_in END, 4) as amount_in,
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
                                a.item_rm_id,
                                a.receipt_date,
                                c.qty_receipt AS qty_in,
                                b.price AS price_in,
                                (c.qty_receipt * b.price) AS amount_in,
                                0 AS qty_out
                            FROM purchase_order_receipts a
                            LEFT JOIN purchase_orders b ON a.item_rm_id = b.item_rm_id AND a.po_no = b.po_no
                            JOIN (SELECT receipt_id, SUM(qty) as qty_receipt FROM scan_item_receipts GROUP BY receipt_id) c ON a.receipt_id = c.receipt_id
                            WHERE a.receipt_date < '$filter_from'
                            GROUP BY a.id
                            UNION
                            SELECT
                                id,
                                item_rm_id,
                                DATE_FORMAT(created_date, '%Y-%m-%d') AS trans_date,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                qty AS qty_out
                            FROM issued_material_details
                            WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from'
                            GROUP BY id
                            UNION
                            SELECT 
                                a.id,
                                a.item_rm_id, 
                                a.trans_date,
                                COALESCE(SUM(a.qty), 0) AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                0 AS qty_out
                            FROM sto_rm a 
                            WHERE a.type = 'IN' AND a.trans_date < '$filter_from' AND a.approved_to = ''
                            GROUP BY a.id
                            UNION
                            SELECT 
                                a.id,
                                a.item_rm_id, 
                                a.trans_date,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                COALESCE(SUM(a.qty), 0) AS qty_out
                            FROM sto_rm a 
                            WHERE a.type = 'OUT' AND a.trans_date < '$filter_from' AND a.approved_to = ''
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                a.item_id AS item_rm_id,
                                a.transaction_date AS trans_date,
                                a.qty as qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                0 AS qty_out 
                            FROM rgp_transactions a
                            WHERE a.type = 'IN' AND a.transaction_date < '$filter_from'
                            GROUP BY a.id
                            UNION
                            SELECT
                                a.id,
                                a.item_id AS item_rm_id,
                                a.transaction_date AS trans_date,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                a.qty AS qty_out 
                            FROM rgp_transactions a
                            WHERE a.type = 'OUT' AND a.transaction_date < '$filter_from'
                            GROUP BY a.id
                            UNION
                            SELECT 
                                b.id,
                                a.item_rm_id, 
                                DATE_FORMAT(b.created_date, '%Y-%m-%d') AS trans_date,
                                0 AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                COALESCE(SUM(b.qty), 0) AS qty_out 
                            FROM purchase_order_receipts a 
                            JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id 
                            WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$filter_from' 
                            GROUP BY b.id
                            UNION
                            SELECT
                                a.id,
                                a.item_rm_id,
                                a.trans_date,
                                a.qty AS qty_in,
                                0 AS price_in,
                                0 AS amount_in,
                                0 AS qty_out
                            FROM os_rm a
                            WHERE a.trans_date < '$filter_from' AND a.type = 'STO'
                            GROUP BY a.id) a
                            ORDER BY a.item_rm_id, a.receipt_date ASC
                        ) z,
                    (SELECT @no_urut := 0, @balance_qty := 0.0000, @balance_price := 0.0000, @balance_amount := 0.0000) AS zz
                    ORDER BY z.item_rm_id, z.receipt_date ASC) zz ON c.id = zz.item_rm_id
                    LEFT JOIN inventory_rm a ON c.id = a.item_rm_id
                    WHERE c.id like '%$filter_items%' and c.item_family_id like '%$filter_item_family%' AND c.item_category_id = 'C01'
                    GROUP BY c.id) bgin ON z.item_rm_id = bgin.item_rm_id
                JOIN item_rm c ON z.item_rm_id = c.id
                JOIN item_familys d ON c.item_family_id = d.id,
                (SELECT @no_urut := 0, @item_rm_id := 0) AS za
                WHERE z.item_rm_id like '%$filter_items%' and d.id like '%$filter_item_family%' AND c.item_category_id = 'C01'
                ORDER BY z.item_rm_id, z.receipt_date, no_urut ASC");

            $html .= '<table id="customers" style="width: 100%">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2">Part No EBWS</th>
                            <th rowspan="2">Part No Supplier</th>
                            <th rowspan="2">Description</th>
                            <th rowspan="2">Trans Type</th>
                            <th rowspan="2">Created By</th>
                            <th rowspan="2">PO Date</th>
                            <th rowspan="2">Trans Date</th>
                            <th rowspan="2">Receipt No</th>
                            <th rowspan="2">Kanban/PO No</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Document Date</th>
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
                $item_rm_id = $itemReceipt->item_rm_id;
                if($item_rm_id != $last_item_rm_id){

                    $html .= '  <tr style="background: #aae9b5;">
                                    <td colspan="21" style="font-weight:bold;">BEGINING STOCK ('.$itemReceipt->item_rm_number.')</td>
                                    <td style="text-align:right; font-weight:bold;">' . $this->formatNo($itemReceipt->begin_qty) . '</td>
                                    <td style="text-align:right; font-weight:bold;">' . $this->formatNo($itemReceipt->begin_price) . '</td>
                                    <td colspan="2" style="text-align:right; font-weight:bold;">' . $this->formatNo($itemReceipt->begin_amount) . '</td>
                                </tr>';
                }

                $html .= '  <tr>
                                <td>' . $nod . '</td>
                                <td>' . $itemReceipt->item_rm_id . '</td>
                                <td style="mso-number-format:\@;">' . $itemReceipt->item_rm_number . '</td>
                                <td style="mso-number-format:\@;">' . $itemReceipt->item_rm_name . '</td>
                                <td>' . $itemReceipt->trans_type . '</td>
                                <td>' . $itemReceipt->created_by . '</td>
                                <td>' . $itemReceipt->po_date . '</td>
                                <td>' . $itemReceipt->receipt_date . '</td>
                                <td>' . $itemReceipt->receipt_no . '</td>
                                <td>' . $itemReceipt->po_no . '</td>
                                <td>' . $itemReceipt->bc_document . '</td>
                                <td>' . $itemReceipt->bc_date . '</td>
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

                $last_item_rm_id = $item_rm_id;
                $nod++;
            }
        }
        echo $html;
    }
}
