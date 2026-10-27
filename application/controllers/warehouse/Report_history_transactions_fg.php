<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_fg extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions_fg');
        } else {
            redirect('error_access');
        }
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
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        function compare_trans_date($a, $b) {
            return strtotime($a['created_date']) - strtotime($b['created_date']);
        }
        
        $records = $this->crud->query("SELECT
            a.id,
            a.number,
            a.number_customer,
            a.name, 
            a.uom, 
            COALESCE(i.begin_stock, 0) as begin_stock,
            (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in,0) + COALESCE(c.qty_wip, 0) + COALESCE(j.qty_return, 0) + COALESCE(e.qty_rgp_in, 0)) as qty_in,
            (COALESCE(g.qty, 0) + COALESCE(h.sto_qty_out, 0) + COALESCE(f.qty_rgp_out, 0)) as qty_out,
            (COALESCE((b.qty_sto + d.sto_qty_in + c.qty_wip + COALESCE(j.qty_return, 0)),0) + COALESCE(e.qty_rgp_in, 0) - COALESCE(g.qty, 0) - COALESCE(h.sto_qty_out, 0) - COALESCE(f.qty_rgp_out, 0)) as end_stock
        FROM item_fg a 
        LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_fg a JOIN scan_item_receipts_fg b ON a.id = b.workorder WHERE a.trans_date between '$filter_from' and '$filter_to' GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
        LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_fg a WHERE a.trans_date between '$filter_from' and '$filter_to' and a.type = 'IN' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) d ON a.id = d.item_fg_id
        LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_fg a WHERE a.trans_date between '$filter_from' and '$filter_to' and a.type = 'OUT' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) h ON a.id = h.item_fg_id
        LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_wip FROM wip_receipts a JOIN scan_item_receipts_fg b ON a.workorder = b.workorder and a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY a.item_fg_id) c ON a.id = c.item_fg_id
        LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_return FROM sales_returns a JOIN scan_item_receipts_fg b ON a.sales_invoice_no = b.workorder and a.return_id = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY a.item_fg_id) j ON a.id = j.item_fg_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id= b.item_id WHERE a.type = 'IN' and a.transaction_date between '$filter_from' and '$filter_to' GROUP BY a.item_id) e ON a.id = e.item_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date between '$filter_from' and '$filter_to' GROUP BY a.item_id) f ON a.id = f.item_id
        LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and `status` != '2' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
        LEFT JOIN ( SELECT
                        a.id,
                        (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(c.qty_wip, 0) + COALESCE(e.qty_rgp_in, 0) + COALESCE(i.qty_return, 0) - COALESCE(g.qty, 0) - COALESCE(h.sto_qty_out, 0) - COALESCE(f.qty_rgp_out, 0)) as begin_stock
                    FROM item_fg a 
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_fg a JOIN scan_item_receipts_fg b ON a.id = b.workorder WHERE a.trans_date < '$filter_from' GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_wip FROM wip_receipts a JOIN scan_item_receipts_fg b ON a.workorder = b.workorder and a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$filter_from' GROUP BY a.item_fg_id) c ON a.id = c.item_fg_id
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_return FROM sales_returns a JOIN scan_item_receipts_fg b ON a.sales_invoice_no = b.workorder and a.return_id = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$filter_from' GROUP BY a.item_fg_id) i ON a.id = i.item_fg_id
                    LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_fg a WHERE a.trans_date < '$filter_from' and a.type = 'IN' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) d ON a.id = d.item_fg_id
                    LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_fg a WHERE a.trans_date < '$filter_from' and a.type = 'OUT' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) h ON a.id = h.item_fg_id
                    LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date < '$filter_from' GROUP BY a.item_id) e ON a.id = e.item_id
                    LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date < '$filter_from' GROUP BY a.item_id) f ON a.id = f.item_id
                    LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' and `status` != '2' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
                    GROUP BY a.id
                    ORDER BY a.number) i ON a.id = i.id
        WHERE a.id like '%$filter_items%'
        GROUP BY a.id
        ORDER BY a.number");
        
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
            <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION (FG)</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th colspan="2">Product No EBWS</th>
                    <th>Product No Customer</th>
                    <th>Product Name</th>
                    <th>Uom</th>
                    <th>Product Family</th>
                    <th width="100">Begin<br>Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending<br>Stock</th>
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_fg_id = $record->id;
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="2">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->number_customer . '</td>
                            <td>' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>FINISH GOOD</td>
                            <td style="text-align:right;">' . number_format($record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->begin_stock + $record->qty_in - $record->qty_out), 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th>Trans Date</th>
                                <th>WO / DO</th>
                                <th>Doc. No</th>
                                <th>Begin</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Balance</th>
                            </tr>';
                $nod = 1;
                $begin = $record->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = $record->begin_stock;

                //STO BEGIN
                $q_os_fg = $this->db->query("SELECT
                    'STO' as trans_type,
                    a.created_by,
                    a.created_date,
                    a.document_no as document_no_1,
                    a.id as document_no_2,
                    SUM(b.qty) as qty_in,
                    '0' as qty_out
                FROM os_fg a 
                JOIN scan_item_receipts_fg b ON a.id = b.workorder
                WHERE a.item_fg_id = '$item_fg_id' and a.trans_date BETWEEN '$filter_from' and '$filter_to'
                GROUP BY a.id");
                $d_os_fg = $q_os_fg->result_array();

                //STO ADJUSTMENT IN
                $q_adj_in = $this->db->query("SELECT
                    'ADJUSTMENT IN' as trans_type,
                    a.created_by,
                    a.trans_date as created_date,
                    a.document_no as document_no_1,
                    a.id as document_no_2,
                    a.qty as qty_in,
                    '0' as qty_out
                FROM sto_fg a 
                WHERE a.item_fg_id = '$item_fg_id' and a.trans_date BETWEEN '$filter_from' and '$filter_to' and a.type = 'IN'");
                $d_adj_in = $q_adj_in->result_array();

                //STO ADJUSTMENT IN
                $q_adj_out = $this->db->query("SELECT
                    'ADJUSTMENT OUT' as trans_type,
                    a.created_by,
                    a.trans_date as created_date,
                    a.document_no as document_no_1,
                    a.id as document_no_2,
                    '0' as qty_in,
                    a.qty as qty_out
                FROM sto_fg a 
                WHERE a.item_fg_id = '$item_fg_id' and a.trans_date BETWEEN '$filter_from' and '$filter_to' and a.type = 'OUT'");
                $d_adj_out = $q_adj_out->result_array();

                //RECEIPT
                $q_receipt = $this->db->query("SELECT
                    'RECEIPT FG' as trans_type,
                    f.created_by,
                    f.created_date,
                    f.workorder as document_no_1,
                    f.checksheet_label as document_no_2,
                    f.qty as qty_in,
                    '0' as qty_out
                FROM wip_receipts e
                JOIN scan_item_receipts_fg f ON e.checksheet_number = f.checksheet_number and e.workorder = f.workorder
                WHERE e.item_fg_id = '$item_fg_id' 
                and DATE_FORMAT(f.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                $d_receipt = $q_receipt->result_array();

                //RETURN
                $q_return = $this->db->query("SELECT
                    'RETURN FG' as trans_type,
                    f.created_by,
                    f.created_date,
                    f.workorder as document_no_1,
                    f.checksheet_label as document_no_2,
                    f.qty as qty_in,
                    '0' as qty_out
                FROM sales_returns e
                JOIN scan_item_receipts_fg f ON e.return_id = f.checksheet_number and e.sales_invoice_no = f.workorder
                WHERE e.item_fg_id = '$item_fg_id' 
                and DATE_FORMAT(f.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                $d_return = $q_return->result_array();

                //DELIVERY
                $q_delivery = $this->db->query("SELECT
                    'DELIVERY NOTE' as trans_type,
                    a.created_by,
                    a.created_date,
                    a.delivery_order_no as document_no_1,
                    a.delivery_note_no as document_no_2,
                    '0' as qty_in,
                    a.qty as qty_out
                FROM delivery_notes a 
                WHERE a.item_fg_id = '$item_fg_id' and DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.status != '2'");
                $d_delivery = $q_delivery->result_array();

                //RGP TRANSACTION IN
                $q_rgp_in = $this->db->query("SELECT
                    'RGP IN' as trans_type,
                    b.created_by,
                    b.created_date,
                    a.number as document_no_1,
                    b.label_no as document_no_2,
                    b.qty as qty_in,
                    '0' as qty_out
                FROM rgp_transactions a 
                JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id
                WHERE a.item_id = '$item_fg_id' and DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.type = 'IN'
                GROUP BY b.id");
                $d_rgp_in = $q_rgp_in->result_array();

                //RGP TRANSACTION OUT
                $q_rgp_out = $this->db->query("SELECT
                    'RGP OUT' as trans_type,
                    b.created_by,
                    b.created_date,
                    a.number as document_no_1,
                    b.label_no as document_no_2,
                    '0' as qty_in,
                    b.qty as qty_out
                FROM rgp_transactions a 
                JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id
                WHERE a.item_id = '$item_fg_id' and DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.type = 'OUT'
                GROUP BY b.id");
                $d_rgp_out = $q_rgp_out->result_array();

                $records = array_merge($d_os_fg, $d_adj_in, $d_adj_out, $d_receipt, $d_return, $d_delivery, $d_rgp_in, $d_rgp_out);
                usort($records, 'compare_trans_date');

                foreach ($records as $record) {
                    $user = $this->crud->read("users", [], ["username" => $record['created_by']]);
                    $balance = ($begin + $record['qty_in'] - $record['qty_out']);
                    $html .= '  <tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $record['trans_type'] . '</td>
                                    <td>' . $user->name . '</td>
                                    <td>' . $record['created_date'] . '</td>
                                    <td>' . $record['document_no_1'] . '</td>
                                    <td>' . $record['document_no_2'] . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record['qty_in'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record['qty_out'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                </tr>';

                    $begin += ($record['qty_in'] - $record['qty_out']);
                    $nod++;
                }
            }
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
