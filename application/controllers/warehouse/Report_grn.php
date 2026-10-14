<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_grn extends CI_Controller
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
            $this->load->view('warehouse/report_grn');
        } else {
            redirect('error_access');
        }
    }

    public function readPoNo()
    {
        $supplier = $this->input->get('supplier');
        $records = $this->crud->query("SELECT po_no FROM purchase_order_receipts WHERE supplier_id = '$supplier' GROUP BY po_no ORDER BY po_no desc");
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=grn_report_$format.xls");
        }
        
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_po_no = base64_decode($this->input->get("filter_po_no"));
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status = $this->input->get("filter_status");

        //Details
        $this->db->select('a.*,
            b.name as supplier_name, 
            c.number as item_rm_number, 
            c.name as item_rm_name, 
            d.name as item_family_name, 
            c.uom,
            e.total_scan_qty,
            g.scan_qty,
            g.scan_date,
            g.total_scan');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join("(SELECT receipt_id, DATE_FORMAT(created_date, '%Y-%m-%d') as scan_date, SUM(qty) as scan_qty, COUNT(id) as total_scan FROM scan_item_receipts GROUP BY receipt_id, DATE_FORMAT(created_date, '%Y-%m-%d')) g", 'g.receipt_id = a.receipt_id', 'left');
        $this->db->join("(SELECT receipt_id, SUM(qty) as total_scan_qty FROM scan_item_receipts GROUP BY receipt_id) e", 'e.receipt_id = a.receipt_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.status != 2");
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where('a.receipt_date >=', $filter_from);
            $this->db->where('a.receipt_date <=', $filter_to);
        }
        if ($filter_supplier != "") {
            $this->db->where('b.id', $filter_supplier);
        }
        if ($filter_product_no != "") {
            $this->db->where('c.id', $filter_product_no);
        }
        if ($filter_po_no != "") {
            $this->db->where('a.po_no', $filter_po_no);
        }
        if ($filter_status == "0") {
            $this->db->where("a.qty_label > COALESCE(g.total_scan, 0)");
        }
        if ($filter_status == "1") {
            $this->db->where("a.qty_label = g.total_scan");
        }
        $this->db->group_by('a.receipt_id');
        $this->db->group_by('a.bc_document');
        $this->db->group_by('g.scan_date');
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('a.receipt_id', 'ASC');
        $records = $this->db->get()->result_array();
        
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
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
                <h3 style="margin:0;">GRN REPORT</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Receipt No</th>
                <th>Receipt ID</th>
                <th>Receipt Date</th>
                <th>Scan Date</th>
                <th>Supplier Name</th>
                <th>Invoice No</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Uom</th>
                <th>Qty Gate</th>
                <th>Qty Scan</th>
                <th>MPQ</th>
                <th>Label</th>
                <th>Scan In</th>
                <th>GRN No</th>
                <th>Status</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            if($data['qty_receipt'] <= $data['total_scan_qty']){
                if($data['qty_receipt'] <= $data['scan_qty']){
                    $qty_scan = $data['qty_receipt'];
                }else{
                    $qty_scan = $data['scan_qty'];
                }
                
                $grn_no = "GRN".substr(@$data['receipt_no'], 3);
                $status = "<b style='color:red;'>CLOSE</b>";
            }else{
                $qty_scan = $data['scan_qty'];
                $status = "<b style='color:green;'>OPEN</b>";
                $grn_no = "-";
            }
            
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['receipt_no'] . '</td>
                            <td>' . $data['receipt_id'] . '</td>
                            <td>' . $data['receipt_date'] . '</td>
                            <td>' . $data['scan_date'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['bc_document'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td style="text-align:right">' . number_format($data['qty_receipt']) . '</td>
                            <td style="text-align:right">' . number_format($qty_scan) . '</td>
                            <td style="text-align:right">' . number_format($data['qty_mpq']) . '</td>
                            <td style="text-align:right">' . number_format($data['qty_label']) . '</td>
                            <td style="text-align:right">' . number_format($data['total_scan']) . '</td>
                            <td>' . $grn_no . '</td>
                            <td>' . $status . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
