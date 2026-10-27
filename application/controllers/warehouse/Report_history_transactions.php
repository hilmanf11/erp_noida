<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions');
        } else {
            redirect('error_access');
        }
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
                ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS end_stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date <= '$date' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date <= '$date' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date <= '$date' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date <= '$date' GROUP BY item_id) j ON a.id = j.item_id
                LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date <= '$date' GROUP BY item_id) k ON a.id = k.item_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
                WHERE a.return_date <= '$date'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.id = '$item_rm_id'
            GROUP BY a.id
            ORDER BY b.number, a.number");

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
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

        if($filter_item_family != ""){
            $whereProdfam = "and b.number = '$filter_item_family'";
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
            (COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in,0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0) + COALESCE(j.qty_rgp_in, 0)) AS qty_in,
            (COALESCE(f.qty, 0) + COALESCE(i.qty_reject, 0) + COALESCE(k.qty_rgp_out, 0) + COALESCE(d.sto_qty_out, 0)) AS qty_out,
            ((COALESCE(h.begin_stock,0) + COALESCE(SUM(c.qty_sto),0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(SUM(dd.sto_qty_in),0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(i.qty_reject, 0) - COALESCE(k.qty_rgp_out, 0)) AS end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.item_category_id = 'C01'
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date between '$filter_from' and '$filter_to' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date between '$filter_from' and '$filter_to' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date between '$filter_from' and '$filter_to' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date between '$filter_from' and '$filter_to' GROUP BY a.item_id) j ON a.id = j.item_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date between '$filter_from' and '$filter_to' GROUP BY a.item_id) k ON a.id = k.item_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
            WHERE a.return_date between '$filter_from' and '$filter_to'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT
            a.id,
            ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS begin_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
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
                <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th colspan="2">Part No EBWS</th>
                    <th colspan="2">Part No Supplier</th>
                    <th colspan="2">Description</th>
                    <th>Uom</th>
                    <th>Product Family</th>
                    <th width="100">Begin<br>Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending<br>Stock</th>
                </tr>';
        $no = 1;
        $qty_begin = 0;
        $qty_in = 0;
        $qty_out = 0;
        foreach ($records as $record) {
            $item_rm_id = $record->id;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->id . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->name . '</td>
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

                // $detail = $this->db->query("SELECT z.* FROM (
                // SELECT 'STO' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, a.id AS invoice_no, COALESCE(SUM(b.qty), 0) AS qty_in, '0' AS qty_out FROM os_rm a JOIN scan_item_receipts b ON a.id = b.receipt_id JOIN users c ON a.created_by = c.username WHERE a.item_rm_id = '$item_rm_id' GROUP BY a.id
                // UNION
                // SELECT 'STO ADJUSTMENT IN' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, '-' AS invoice_no, a.qty AS qty_in, '0' AS qty_out FROM sto_rm a JOIN users c ON a.created_by = c.username WHERE a.type = 'IN' AND a.item_rm_id = '$item_rm_id' GROUP BY a.id
                // UNION
                // SELECT 'STO ADJUSTMENT OUT' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, '-' AS invoice_no, '0' AS qty_in, a.qty AS qty_out FROM sto_rm a JOIN users c ON a.created_by = c.username WHERE a.type = 'OUT' AND a.item_rm_id = '$item_rm_id' GROUP BY a.id
                // UNION
                // SELECT 'RECEIPT' AS trans_type, c.name AS created_by, DATE_FORMAT(b.created_date, '%Y-%m-%d') AS trans_date, d.id AS supplier_id, d.name AS supplier_name, a.receipt_no AS document_no, a.bc_document AS invoice_no, SUM(b.qty) AS qty_in, '0' AS qty_out FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id JOIN users c ON a.created_by = c.username JOIN suppliers d ON a.supplier_id = d.id WHERE a.item_rm_id = '$item_rm_id' GROUP BY a.receipt_no, a.bc_document
                // UNION
                // SELECT 'REJECTION' AS trans_type, c.name AS created_by, b.created_date AS trans_date, d.id AS supplier_id, d.name AS supplier_name, a.bc_document AS document_no, b.label_no AS invoice_no, '0' AS qty_in, SUM(b.qty) AS qty_out FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id = b.receipt_id JOIN users c ON b.created_by = c.username JOIN suppliers d ON a.supplier_id = d.id WHERE a.item_rm_id = '$item_rm_id' GROUP BY b.id
                // UNION
                // SELECT 'ISSUED' AS trans_type, created_by, created_date AS trans_date, '-' AS supplier_id, '-' AS supplier_name, request_no AS document_no, label_no AS invoice_no, '0' AS qty_in, qty AS qty_out FROM issued_material_details WHERE item_rm_id = '$item_rm_id'
                // UNION
                // SELECT 'RETURN MATERIAL' AS trans_type, d.name AS created_by, a.return_date AS trans_date, '-' AS supplier_id, '-' AS supplier_name, a.return_no AS document_no, b.label_no AS invoice_no, b.qty AS qty_in, '0' AS qty_out FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id JOIN users d ON a.created_by = d.username WHERE a.item_rm_id = '$item_rm_id' GROUP BY b.label_no
                // UNION 
                // SELECT 'RGP IN' AS trans_type, a.created_by, c.created_date AS trans_date, b.id AS supplier_id, b.name AS supplier_name, c.rgp_number AS document_no, c.label_no AS invoice_no, c.qty AS qty_in, '0' AS qty_out FROM rgp_transactions a LEFT JOIN suppliers b ON a.company_id = b.id JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id WHERE a.type = 'IN' AND a.item_id = '$item_rm_id' GROUP BY c.id
                // UNION
                // SELECT 'RGP OUT' AS trans_type, a.created_by, c.created_date AS trans_date, b.id AS supplier_id, b.name AS supplier_name, c.rgp_number AS document_no, c.label_no AS invoice_no, '0' AS qty_in, c.qty AS qty_out FROM rgp_transactions a LEFT JOIN suppliers b ON a.company_id = b.id JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id WHERE a.type = 'OUT' AND a.item_id = '$item_rm_id' GROUP BY c.id
                // ) z WHERE z.trans_date between '$filter_from' and '$filter_to' ORDER BY z.trans_date ASC");
                // $detail->result_array();

                //RECEIPT
                $sql = $this->db->query("SELECT 'RECEIPT' AS trans_type, c.name AS created_by, b.trans_date, d.id AS supplier_id, d.name AS supplier_name, a.receipt_no AS document_no, a.bc_document AS invoice_no, SUM(b.qty) AS qty_in, '0' AS qty_out 
                    FROM purchase_order_receipts a 
                    JOIN (select receipt_id, DATE_FORMAT(created_date, '%Y-%m-%d') as trans_date, SUM(qty) as qty FROM scan_item_receipts WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' GROUP BY receipt_id, DATE_FORMAT(created_date, '%Y-%m-%d')) b ON a.receipt_id = b.receipt_id 
                    JOIN users c ON a.created_by = c.username 
                    JOIN suppliers d ON a.supplier_id = d.id 
                    WHERE a.item_rm_id = '$item_rm_id'
                    GROUP BY a.receipt_no, a.bc_document, b.trans_date");
                $row = $sql->result_array();

                //STO
                $sql2 = $this->db->query("SELECT 'STO' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, a.id AS invoice_no, COALESCE(SUM(b.qty), 0) AS qty_in, '0' AS qty_out FROM os_rm a JOIN scan_item_receipts b ON a.id = b.receipt_id JOIN users c ON a.created_by = c.username WHERE a.item_rm_id = '$item_rm_id' and a.trans_date between '$filter_from' and '$filter_to' GROUP BY a.id");
                $row2 = $sql2->result_array();

                //STO ADJUSTMENT IN
                $sql3 = $this->db->query("SELECT 'STO ADJUSTMENT IN' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, '-' AS invoice_no, a.qty AS qty_in, '0' AS qty_out FROM sto_rm a JOIN users c ON a.created_by = c.username WHERE a.type = 'IN' AND a.item_rm_id = '$item_rm_id' and a.trans_date between '$filter_from' and '$filter_to' GROUP BY a.id");
                $row3 = $sql3->result_array();

                //STO ADJUSTMENT OUT
                $sql4 = $this->db->query("SELECT 'STO ADJUSTMENT OUT' AS trans_type, c.name AS created_by, a.trans_date, '-' AS supplier_id, '-' AS supplier_name, a.document_no, '-' AS invoice_no, '0' AS qty_in, a.qty AS qty_out FROM sto_rm a JOIN users c ON a.created_by = c.username WHERE a.type = 'OUT' AND a.item_rm_id = '$item_rm_id' and a.trans_date between '$filter_from' and '$filter_to' GROUP BY a.id");
                $row4 = $sql4->result_array();

                //REJECTION
                $sql5 = $this->db->query("SELECT 'REJECTION' AS trans_type, c.name AS created_by, b.created_date AS trans_date, d.id AS supplier_id, d.name AS supplier_name, a.bc_document AS document_no, b.label_no AS invoice_no, '0' AS qty_in, SUM(b.qty) AS qty_out FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id = b.receipt_id JOIN users c ON b.created_by = c.username JOIN suppliers d ON a.supplier_id = d.id WHERE a.item_rm_id = '$item_rm_id' and DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY b.id");
                $row5 = $sql5->result_array();
                
                //ISSUED
                $sql6 = $this->db->query("SELECT 'ISSUED' AS trans_type, created_by, created_date AS trans_date, '-' AS supplier_id, '-' AS supplier_name, request_no AS document_no, label_no AS invoice_no, '0' AS qty_in, qty AS qty_out FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
                $row6 = $sql6->result_array();
                
                //RETURN MATERIAL
                $sql7 = $this->db->query("SELECT 'RETURN MATERIAL' AS trans_type, d.name AS created_by, a.return_date AS trans_date, '-' AS supplier_id, '-' AS supplier_name, a.return_no AS document_no, b.label_no AS invoice_no, b.qty AS qty_in, '0' AS qty_out FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id JOIN users d ON a.created_by = d.username WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$filter_from' and '$filter_to' GROUP BY b.label_no");
                $row7 = $sql7->result_array();

                //RGP IN
                $sql8 = $this->db->query("SELECT 'RGP IN' AS trans_type, a.created_by, c.created_date AS trans_date, b.id AS supplier_id, b.name AS supplier_name, c.rgp_number AS document_no, c.label_no AS invoice_no, c.qty AS qty_in, '0' AS qty_out FROM rgp_transactions a LEFT JOIN suppliers b ON a.company_id = b.id JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id WHERE a.type = 'IN' AND a.item_id = '$item_rm_id' and DATE_FORMAT(c.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY c.id");
                $row8 = $sql8->result_array();

                //RGP OUT
                $sql9 = $this->db->query("SELECT 'RGP OUT' AS trans_type, a.created_by, c.created_date AS trans_date, b.id AS supplier_id, b.name AS supplier_name, c.rgp_number AS document_no, c.label_no AS invoice_no, '0' AS qty_in, c.qty AS qty_out FROM rgp_transactions a LEFT JOIN suppliers b ON a.company_id = b.id JOIN scan_item_rgp c ON a.number = c.rgp_number and a.item_id = c.item_id WHERE a.type = 'OUT' AND a.item_id = '$item_rm_id' and DATE_FORMAT(c.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY c.id");
                $row9 = $sql9->result_array();

                $details = array_merge($row, $row2, $row3, $row4, $row5, $row6, $row7, $row8, $row9);
                usort($details, function($a, $b) {
                    return strtotime($a['trans_date']) - strtotime($b['trans_date']);
                });
                
                $nod = 1;
                $begin = @$record->begin_stock;
                $in_qty = 0;
                $out_qty = 0;
                $balance = @$record->begin_stock;
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
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $detail['trans_type'] . '</td>
                                    <td>' . $detail['created_by'] . '</td>
                                    <td>' . $detail['trans_date'] . '</td>
                                    <td>' . $detail['supplier_id'] . '</td>
                                    <td>' . $detail['supplier_name'] . '</td>
                                    <td>' . $detail['document_no'] . '</td>
                                    <td>' . $detail['invoice_no'] . '</td>
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

            $qty_begin += @$record->begin_stock;
            $qty_in += $record->qty_in;
            $qty_out += $record->qty_out;
            $no++;
        }

        $html .= '  <tr>
                        <td style="text-align:right" colspan="9">GRAND TOTAL</td>
                        <td style="text-align:right;">' . number_format($qty_begin, 2) . '</td>
                        <td style="text-align:right;">' . number_format($qty_in, 2) . '</td>
                        <td style="text-align:right;">' . number_format($qty_out, 2) . '</td>
                        <td style="text-align:right;">' . number_format($qty_begin + $qty_in - $qty_out, 2) . '</td>
                    </tr>';
        
        $html .= '</table></body></html>';
        echo $html;
    }
}
