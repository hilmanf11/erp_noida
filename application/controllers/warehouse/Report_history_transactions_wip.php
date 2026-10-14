<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_wip extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions_wip');
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

        $records = $this->crud->query("SELECT c.id as item_rm_id, c.number AS item_rm_number, c.name AS item_rm_name, d.name AS item_family_name, c.uom, SUM(a.total) AS need, b.issued, (COALESCE(b.issued, 0) - COALESCE(SUM(a.total), 0)) AS balance 
            FROM item_rm c
            LEFT JOIN (
                SELECT b.item_rm_id, a.item_fg_id, SUM(a.qty), b.composition, (SUM(a.qty) * b.composition) AS total
                FROM production_schedules a
                JOIN bom b ON a.item_fg_id = b.item_fg_id
                WHERE a.trans_date BETWEEN '$filter_from' AND '$filter_to' 
                GROUP BY a.item_fg_id, b.item_rm_id
            ) a ON a.item_rm_id = c.id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) b ON c.id = b.item_rm_id
            JOIN item_familys d ON c.item_family_id = d.id
            WHERE c.item_family_id like '%$filter_item_family%' and c.id like '%$filter_items%'
            GROUP BY c.id
            ORDER BY c.item_family_id, c.id asc");

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
            <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION (KANBAN)</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Product Family</th>
                    <th>Uom</th>
                    <th width="100">Need</th>
                    <th width="100">Issued</th>
                    <th width="100">Balance</th>
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_rm_id = $record->item_rm_id;

            if ($record->balance > 0) {
                $style = "";
            } else {
                $style = "background: red; color:white;";
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record->item_rm_id . '</td>
                            <td style="mso-number-format:\@;">' . $record->item_rm_number . '</td>
                            <td>' . $record->item_rm_name . '</td>
                            <td>' . $record->item_family_name . '</td>
                            <td>' . $record->uom . '</td>
                            <td style="text-align:right;">' . number_format($record->need, 0) . '</td>
                            <td style="text-align:right;">' . number_format($record->issued, 0) . '</td>
                            <td style="text-align:right; ' . $style . '">' . number_format($record->balance, 0) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="9" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->item_rm_number . ' - ' . $record->item_rm_name . '</b></td>
                            </tr>
                            <tr>
                                <th width="20">No</th>
                                <th>Req No / Customer</th>
                                <th>Workorder</th>
                                <th>Product No</th>
                                <th>Product Name</th>
                                <th>Created By</th>
                                <th>Trans Date</th>
                                <th>Trans Type</th>
                                <th>Qty</th>
                            </tr>';

                $details = $this->crud->query("SELECT * FROM (
                    (SELECT 'NEED PROD' as trans_type, b.name AS request_no, a.created_by, a.workorder, c.number_customer, c.name, a.trans_date, (a.qty * d.composition) AS qty
                        FROM production_schedules a 
                        JOIN customers b ON a.customer_id = b.id
                        JOIN item_fg c ON a.item_fg_id = c.id
                        JOIN bom d ON a.item_fg_id = d.item_fg_id
                        WHERE a.trans_date BETWEEN '$filter_from' AND '$filter_to' AND d.item_rm_id = '$item_rm_id'
                        GROUP BY a.item_fg_id, d.item_rm_id, a.workorder)
                    UNION
                    (SELECT 'ISSUED' as trans_type, b.request_no, b.created_by, b.workorder, c.number_customer, c.name, DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date, SUM(a.qty) AS qty 
                        FROM issued_material_details a 
                        LEFT JOIN supply_sheets b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
                        LEFT JOIN item_fg c ON b.item_fg_id = c.id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND a.item_rm_id = '$item_rm_id' and a.request_no NOT LIKE '%PR-%'
                        GROUP BY a.request_no)) z 
                        ORDER BY trans_date ASC");

                $nod = 1;
                foreach ($details as $detail) {
                    $html .= '  <tr>
                                    <td style="text-align:center">' . $no . "." . $nod . '</td>
                                    <td>' . $detail->request_no . '</td>
                                    <td>' . $detail->workorder . '</td>
                                    <td>' . $detail->number_customer . '</td>
                                    <td>' . $detail->name . '</td>
                                    <td>' . $detail->created_by . '</td>
                                    <td>' . $detail->trans_date . '</td>
                                    <td>' . $detail->trans_type . '</td>
                                    <td style="text-align:right;">' . number_format($detail->qty, 2) . '</td>
                                </tr>';
                    $nod++;
                }
            }

            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
