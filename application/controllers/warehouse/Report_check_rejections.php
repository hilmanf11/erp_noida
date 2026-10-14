<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_check_rejections extends CI_Controller
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
            $this->load->view('warehouse/report_check_rejections');
        } else {
            redirect('error_access');
        }
    }

    public function readReceiptNo()
    {
        $supplier = $this->input->get('supplier');
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE supplier_id = '$supplier' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_check_rejections_$format.xls");
        }
        
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_serial_no = base64_decode($this->input->get("filter_serial_no"));
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status = $this->input->get("filter_status");

        //Details
        $this->db->select("a.label_no, d.name as supplier_name, c.id as item_rm_id, c.number as item_rm_number, a.po_no, b.bc_document, a.receipt_id, b.receipt_date, b.qty_receipt, a.qty, a.created_date, a.created_by");
        $this->db->from('scan_item_rejections a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('suppliers d', 'b.supplier_id = d.id');
        $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
        $this->db->like('a.label_no', $filter_serial_no);
        $this->db->like('b.supplier_id', $filter_supplier);
        $this->db->like('c.id', $filter_product_no);
        if($filter_status != ""){
            $this->db->where("(CASE WHEN b.status = 2 THEN 2 ELSE a.status END) = '$filter_status'");
        }
        $this->db->group_by('a.label_no');
        $this->db->order_by('a.id', 'ASC');
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
                <h3 style="margin:0;">CHECK SERIAL SCAN REJECTIONS</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20" rowspan="2">No</th>
                <th rowspan="2">Serial No</th>
                <th rowspan="2">Supplier Name</th>
                <th rowspan="2">Part No EBWS</th>
                <th rowspan="2">Part No Supplier</th>
                <th rowspan="2">PO No</th>
                <th rowspan="2">Invoice No</th>
                <th rowspan="2">Receipt ID</th>
                <th rowspan="2">Receipt Date</th>
                <th rowspan="2">Qty<br>Receipt</th>
                <th colspan="3">Scan Rejections</th>
            </tr>
            <tr>
                <td>Quantity</td>
                <td>Date</td>
                <td>Scan By</td>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . strtoupper($data['label_no']) . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td>' . $data['po_no'] . '</td>
                            <td>' . $data['bc_document'] . '</td>
                            <td>' . $data['receipt_id'] . '</td>
                            <td>' . $data['receipt_date'] . '</td>
                            <td style="text-align:right">' . number_format($data['qty_receipt']) . '</td>
                            <td style="text-align:right">' . number_format($data['qty']) . '</td>
                            <td>' . $data['created_date'] . '</td>
                            <td>' . $data['created_by'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
