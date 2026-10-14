<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_rfg2 extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions_rfg2');
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
        $filter_to = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.id, 
            a.number, 
            a.name, 
            a.uom, 
            b.name AS item_family_name,
            COALESCE(i.begin, 0) AS begin,
            COALESCE(c.issued, 0) AS issued, 
            COALESCE(d.return, 0) AS `return`, 
            COALESCE(e.ng, 0) AS ng, 
            COALESCE(f.scrap, 0) AS scrap, 
            COALESCE(g.adjustment, 0) AS adjustment_in, 
            COALESCE(ABS(j.adjustment), 0) AS adjustment_out, 
            COALESCE(h.rfg, 0) AS rfg,
            (COALESCE(i.begin, 0) + COALESCE(c.issued, 0) + COALESCE(g.adjustment, 0) - COALESCE(ABS(j.adjustment), 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS balance");
        $this->db->from('item_rm a');
        $this->db->join('item_familys b', 'a.item_family_id = b.id');
        $this->db->join("(SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) c", 'a.id = c.item_rm_id', 'left');
        $this->db->join("(SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date BETWEEN '$filter_from' and '$filter_to' GROUP BY item_rm_id) d", 'a.id = d.item_rm_id', 'left');
        $this->db->join("(SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and source = 'Vendor' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) e", 'a.id = e.item_rm_id', 'left');
        $this->db->join("(SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and source = 'User' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) f", 'a.id = f.item_rm_id', 'left');
        $this->db->join("(SELECT item_rm_id, SUM(qty) AS adjustment FROM sto_wip WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and qty > 0 GROUP BY item_rm_id) g", 'a.id = g.item_rm_id', 'left');
        $this->db->join("(SELECT item_rm_id, SUM(qty) AS adjustment FROM sto_wip WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and qty < 0 GROUP BY item_rm_id) j", 'a.id = j.item_rm_id', 'left');
        $this->db->join("(SELECT a.item_rm_id, SUM(a.consumed) AS rfg FROM wip_receipt_balance a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY a.item_rm_id) h", 'a.id = h.item_rm_id', 'left');
        $this->db->join("(SELECT a.id, (COALESCE(c.issued, 0) + COALESCE(g.adjustment_in, 0) - COALESCE(abs(i.adjustment_out), 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS begin
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date < '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date < '$filter_from' and source = 'Vendor' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date < '$filter_from' and source = 'User' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_in FROM sto_wip WHERE trans_date < '$filter_from' and qty > 0 GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_out FROM sto_wip WHERE trans_date < '$filter_from' and qty < 0 GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.consumed) AS rfg FROM wip_receipt_balance a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$filter_from' GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
            WHERE a.item_category_id = 'C01'
            GROUP BY a.id
            ORDER BY a.number) i", 'a.id = i.id', 'left');
        $this->db->where('a.item_category_id', "C01");
        $this->db->like('a.item_family_id', $filter_item_family);
        $this->db->like('a.id', $filter_items);
        $this->db->group_by('a.id');
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('a.number', 'ASC');
        $records = $this->db->get()->result_object();
        
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
            <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION SUMMARY (WIP)</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th width="100">Part No EBWS</th>
                    <th width="150">Part No Supplier</th>
                    <th width="150">Description</th>
                    <th width="150">Product Family</th>
                    <th width="50">Uom</th>
                    <th width="50">Begin</th>
                    <th width="50">Kanban IN</th>
                    <th width="50">STO IN</th>
                    <th width="50">Total IN</th>
                    <th width="50">BOM OUT</th>
                    <th width="50">STO OUT</th>
                    <th width="50">Scrap</th>
                    <th width="50">NG</th>
                    <th width="50">Return</th>
                    <th width="50">Total OUT</th>
                    <th width="50">Balance</th>
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_rm_id = $record->id;

            if($record->balance >= 0){
                $style = "";
            }else{
                $style = "background: red; color:white;";
            }

            $in = ($record->issued + $record->adjustment_in);
            $out = ($record->rfg + $record->adjustment_out + $record->ng + $record->scrap + $record->return);
            $balance = ($record->begin + $in - $out);
            
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record->id . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td>' . $record->name . '</td>
                            <td>' . $record->item_family_name . '</td>
                            <td>' . $record->uom . '</td>
                            <td style="text-align:right;">' . number_format($record->begin, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->issued, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->adjustment_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->rfg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->adjustment_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->scrap, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->ng, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->return, 2) . '</td>
                            <td style="text-align:right;">' . number_format($out, 2) . '</td>
                            <td style="text-align:right; '.$style.'">' . number_format($balance, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="13" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th>Document No</th>
                                <th>Serial No</th>
                                <th>Trans Date</th>
                                <th>Begin</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Balance</th>
                            </tr>';

                $sql = $this->db->query("SELECT item_rm_id, 'ISSUED' AS trans_type, created_by, request_no AS document_no, label_no AS serial_no, DATE_FORMAT(created_date, '%Y-%m-%d') AS trans_date, qty AS qty_in, '0' AS qty_out
                    FROM issued_material_details 
                    WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and request_no NOT LIKE '%PR-%'
                    AND item_rm_id = '$item_rm_id' 
                    GROUP BY id");
                $row = $sql->result_array();

                $sql2 = $this->db->query("SELECT item_rm_id, 'RETURN' AS trans_type, created_by, return_no AS document_no, return_id AS serial_no, return_date AS trans_date, '0' AS qty_in, qty AS qty_out
                    FROM return_materials
                    WHERE return_date BETWEEN '$filter_from' AND '$filter_to'
                    AND item_rm_id = '$item_rm_id'
                    GROUP BY id");
                $row2 = $sql2->result_array();

                $sql3 = $this->db->query("SELECT item_rm_id, 'SCRAP' AS trans_type, created_by, document AS document_no, workorder AS serial_no, trans_date, '0' AS qty_in, qty AS qty_out
                    FROM scraps
                    WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                    AND item_rm_id = '$item_rm_id'
                    AND source = 'User'
                    and (approved_to = '' or approved_to is null)
                    GROUP BY id");
                $row3 = $sql3->result_array();

                $sql4 = $this->db->query("SELECT item_rm_id, 'NG' AS trans_type, created_by, document AS document_no, workorder AS serial_no, trans_date, '0' AS qty_in, qty AS qty_out
                    FROM scraps
                    WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                    AND item_rm_id = '$item_rm_id'
                    AND source = 'Vendor'
                    and (approved_to = '' or approved_to is null)
                    GROUP BY id");
                $row4 = $sql4->result_array();

                $sql5 = $this->db->query("SELECT item_rm_id, 'ADJUSTMENT IN' AS trans_type, created_by, document_no, '-' AS serial_no, trans_date, qty AS qty_in, '0' AS qty_out
                    FROM sto_wip
                    WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                    AND item_rm_id = '$item_rm_id'
                    AND qty > 0
                    GROUP BY id");
                $row5 = $sql5->result_array();

                $sql6 = $this->db->query("SELECT item_rm_id, 'ADJUSTMENT OUT' AS trans_type, created_by, document_no, '-' AS serial_no, trans_date, '0' AS qty_in, ABS(qty) AS qty_out
                    FROM sto_wip
                    WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                    AND item_rm_id = '$item_rm_id'
                    AND qty < 0
                    GROUP BY id");
                $row6 = $sql6->result_array();

                $sql7 = $this->db->query("SELECT a.item_rm_id, 'RFG' AS trans_type, b.created_by, a.checksheet_number AS document_no, b.workorder AS serial_no, DATE_FORMAT(b.created_date, '%Y-%m-%d') as trans_date, '0' AS qty_in, a.consumed AS qty_out
                    FROM wip_receipt_balance a
                    JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number
                    WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                    AND a.item_rm_id = '$item_rm_id'
                    GROUP BY a.item_rm_id, a.checksheet_number");
                $row7 = $sql7->result_array();

                $details = array_merge($row, $row2, $row3, $row4, $row5, $row6, $row7);
                usort($details, function($a, $b) {
                    return strtotime($a['trans_date']) - strtotime($b['trans_date']);
                });

                $nod = 1;
                $begin = @$record->begin;
                $in_qty = 0;
                $out_qty = 0;
                $balance = @$record->begin;
                foreach ($details as $detail) {
                    if($detail['qty_in'] > 0){
                        $in_qty = $detail['qty_in'];
                        $out_qty = 0;
                    }else{
                        $in_qty = 0;
                        $out_qty = $detail['qty_out'];
                    }

                    $balance = ($begin + $in_qty - $out_qty);

                    $html .= '  <tr>
                                    <td style="text-align:center">' . $no.".".$nod . '</td>
                                    <td>' . $detail['trans_type'] . '</td>
                                    <td>' . $detail['created_by'] . '</td>
                                    <td>' . $detail['document_no'] . '</td>
                                    <td>' . $detail['serial_no'] . '</td>
                                    <td>' . $detail['trans_date'] . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($in_qty, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($out_qty, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                </tr>';

                    if($detail['qty_in'] > 0){
                        $begin += ($in_qty);
                    }else{
                        $begin -= ($out_qty);
                    }
                    
                    $nod++;
                }
            }

            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
