<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_other extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions_other');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        $this->db->where('item_category_id !=', 'C01');
        $this->db->where_not_in('id', ['P51','P61','P67','P69','P71']);
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
        $filter_to = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

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
            (COALESCE(c.qty_receipt,0) + COALESCE(e.qty_rgp_in,0) + COALESCE(g.sto_qty_in, 0)) AS qty_in,
            (COALESCE(d.qty,0) + COALESCE(f.qty_rgp_out,0) + COALESCE(i.sto_qty_out, 0)) AS qty_out,
            (COALESCE(h.begin_stock,0) + COALESCE(c.qty_receipt,0) + COALESCE(g.sto_qty_in, 0) + COALESCE(e.qty_rgp_in,0) - COALESCE(i.sto_qty_out, 0) - COALESCE(f.qty_rgp_out,0) - COALESCE(d.qty,0)) AS end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE receipt_date between '$filter_from' and '$filter_to' and store != 0 GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others WHERE trans_date between '$filter_from' and '$filter_to' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date between '$filter_from' and '$filter_to' and (approved_to = '' or approved_to is null) GROUP BY item_id) e ON a.id = e.item_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date between '$filter_from' and '$filter_to' and (approved_to = '' or approved_to is null) GROUP BY item_id) f ON a.id = f.item_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_in FROM sto_ot WHERE type = 'IN' and trans_date between '$filter_from' and '$filter_to' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_out FROM sto_ot WHERE type = 'OUT' and trans_date between '$filter_from' and '$filter_to' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT
            a.id,
            (COALESCE(c.qty_receipt,0) + COALESCE(e.qty_rgp_in,0) + COALESCE(g.sto_qty_in, 0) - COALESCE(i.sto_qty_out, 0) - COALESCE(f.qty_rgp_out,0) - COALESCE(d.qty,0)) AS begin_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE receipt_date < '$filter_from' and store != 0 GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others WHERE trans_date < '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date < '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_id) e ON a.id = e.item_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date < '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_id) f ON a.id = f.item_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_in FROM sto_ot WHERE type = 'IN' and trans_date < '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_out FROM sto_ot WHERE type = 'OUT' and trans_date < '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            GROUP BY a.id) h on a.id = h.id
        JOIN item_categories j ON a.item_category_id = j.id 
        WHERE a.id like '%$filter_items%' and b.number like '%$filter_item_family%' and j.id NOT IN ('C01','C08','C02') and a.item_family_id NOT IN ('P51','P61','P67','P69','P71')
        GROUP BY a.id
        HAVING (begin_stock != 0 or qty_in != 0 or qty_out != 0 or end_stock != 0)
        ORDER BY a.number");

        // die($this->db->last_query());

        function compare_trans_date($a, $b) {
            return strtotime($a['created_date']) - strtotime($b['created_date']);
        }

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
                <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION OTHER</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th colspan="2">Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th colspan="3">Description</th>
                    <th>Uom</th>
                    <th>Product Family</th>
                    <th width="100">Begin<br>Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending<br>Stock</th>
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_rm_id = $record->id;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->id . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td colspan="3" style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->end_stock, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="13" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th>Trans Date</th>
                                <th>Supplier ID</th>
                                <th>Supplier Name</th>
                                <th>Doc No</th>
                                <th>Invoice No</th>
                                <th>Begin</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Balance</th>
                            </tr>';

                $nod = 1;
                $begin = @$record->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = @$record->begin_stock;

                //RECEIPT
                $q_receipt = $this->db->query("SELECT
                    'RECEIPT' as `type`,
                    c.name as created_by,
                    a.receipt_date as created_date, 
                    d.id as supplier_id,
                    d.name as supplier_name,
                    a.receipt_no as document_no,
                    a.bc_document as invoice_no, 
                    a.qty_receipt as qty_in,
                    '0' as qty_out
                FROM purchase_order_receipt_others a 
                LEFT JOIN users c ON a.created_by = c.username
                LEFT JOIN suppliers d ON a.supplier_id = d.id
                WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to' and a.store != 0
                GROUP BY a.id");
                $receipts = $q_receipt->result_array();

                //ISSUED
                $q_issued = $this->db->query("SELECT 
                        'ISSUED' as `type`,
                        request_name as created_by,
                        trans_date as created_date,
                        '-' as supplier_id,
                        '-' as supplier_name,
                        request_no as document_no,
                        '-' as invoice_no,
                        '0' as qty_in,
                        qty as qty_out
                    FROM issued_others 
                    WHERE item_rm_id = '$item_rm_id' 
                    and trans_date between '$filter_from' and '$filter_to'");
                $issueds = $q_issued->result_array();

                //STO IN
                $q_sto_in = $this->db->query("SELECT
                    'ADJUSTMENT IN' as `type`,
                    c.name as created_by,
                    trans_date as created_date,
                    '-' as supplier_id,
                    '-' as supplier_name,
                    document_no,
                    '-' as invoice_no,
                    qty as qty_in,
                    '0' as qty_out
                FROM sto_ot a 
                JOIN users c ON a.created_by = c.username
                WHERE a.item_rm_id = '$item_rm_id' 
                and a.type = 'IN'
                and a.trans_date between '$filter_from' and '$filter_to' 
                and (a.approved_to = '' or a.approved_to is null)");
                $sto_in = $q_sto_in->result_array();

                //STO IN
                $q_sto_out = $this->db->query("SELECT
                    'ADJUSTMENT OUT' as `type`,
                    c.name as created_by,
                    trans_date as created_date,
                    '-' as supplier_id,
                    '-' as supplier_name,
                    document_no,
                    '-' as invoice_no,
                    '0' as qty_in,
                    qty as qty_out
                FROM sto_ot a 
                JOIN users c ON a.created_by = c.username
                WHERE a.item_rm_id = '$item_rm_id' 
                and a.type = 'OUT'
                and a.trans_date between '$filter_from' and '$filter_to' 
                and (a.approved_to = '' or a.approved_to is null)");
                $sto_out = $q_sto_out->result_array();

                //RGP IN
                $q_rgp_in = $this->db->query("SELECT 
                    'RGP IN' as `type`,
                    a.created_by,
                    a.transaction_date as created_date,
                    a.company_id as supplier_id,
                    case when b.name <> null then b.name else c.name end as supplier_name,
                    a.number as document_no,
                    '-' as invoice_no,
                    a.qty as qty_in,
                    '0' as qty_out
                FROM rgp_transactions a 
                LEFT JOIN suppliers b ON a.company_id = b.id 
                LEFT JOIN customers c ON a.company_id = c.id 
                WHERE a.item_id = '$item_rm_id' 
                and a.type = 'IN'
                and a.transaction_date between '$filter_from' and '$filter_to' 
                and (a.approved_to = '' or a.approved_to is null)");
                $rgp_in = $q_rgp_in->result_array();

                //RGP OUT
                $q_rgp_out = $this->db->query("SELECT 
                    'RGP OUT' as `type`,
                    a.created_by,
                    a.transaction_date as created_date,
                    a.company_id as supplier_id,
                    case when b.name <> null then b.name else c.name end as supplier_name,
                    a.number as document_no,
                    '-' as invoice_no,
                    '0' as qty_in,
                    a.qty as qty_out
                FROM rgp_transactions a 
                LEFT JOIN suppliers b ON a.company_id = b.id 
                LEFT JOIN customers c ON a.company_id = c.id 
                WHERE a.item_id = '$item_rm_id' 
                and a.type = 'OUT'
                and a.transaction_date between '$filter_from' and '$filter_to' 
                and (a.approved_to = '' or a.approved_to is null)");
                $rgp_out = $q_rgp_out->result_array();

                $records = array_merge($receipts, $issueds, $sto_in, $sto_out, $rgp_in, $rgp_out);
                usort($records, 'compare_trans_date');

                foreach ($records as $record) {
                    $balance = ($begin + ($record['qty_in'] - $record['qty_out']));
                    $html .= '  <tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $record['type'] . '</td>
                                    <td>' . $record['created_by'] . '</td>
                                    <td>' . $record['created_date'] . '</td>
                                    <td>' . $record['supplier_id'] . '</td>
                                    <td>' . $record['supplier_name'] . '</td>
                                    <td>' . $record['document_no'] . '</td>
                                    <td>' . $record['invoice_no'] . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record['qty_in'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record['qty_out'])  . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                </tr>';
                    $begin = $balance;
                    $nod++;
                }
            }
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
