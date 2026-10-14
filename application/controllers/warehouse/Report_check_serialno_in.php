<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_check_serialno_in extends CI_Controller
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
            $this->load->view('warehouse/report_check_serialno_in');
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
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }
        
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_serial_no = base64_decode($this->input->get("filter_serial_no"));
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status = $this->input->get("filter_status");

        //Details
        $this->db->select("a.label_no, d.name AS supplier_name, b.item_rm_id, c.number AS item_rm_number, b.po_no, b.bc_document, a.receipt_id, b.receipt_date, b.qty_receipt, e.warehouse, e.qty, e.created_date, e.created_by, (CASE WHEN b.status = 2 THEN 2 ELSE a.status END) as status");
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('suppliers d', 'b.supplier_id = d.id');
        $this->db->join('scan_item_receipts e', 'a.label_no = e.label_no AND a.receipt_id = e.receipt_id', 'left');
        $this->db->where("b.receipt_date between '$filter_from' and '$filter_to'");
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
                <h3 style="margin:0;">CHECK SERIAL NO SCAN IN (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Serial No</th>
                <th>Supplier Name</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>PO No</th>
                <th>Invoice No</th>
                <th>Receipt ID</th>
                <th>Receipt Date</th>
                <th>Qty<br>Receipt</th>
                <th>Warehouse</th>
                <th>Scan In</th>
                <th>Scan In Date</th>
                <th>Scan In By</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            $barcodeDivided = $this->crud->reads("barcode_divides", [], ["label_no" => $data['label_no']]);

            if ($data['status'] == 0) {
                $status = "<b style='color:red;'>NOT SCAN</b>";
            } else if($data['status'] == 2){
                $status = "<b style='color:blue;'>CANCEL</b>";
            } else if(count($barcodeDivided) > 0){
                $status = "<b style='color:red;'>DIVIDED</b>";
            } else {
                $status = "<b style='color:green;'>SCAN IN</b>";
            }
            
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
                            <td>' . $data['warehouse'] . '</td>
                            <td style="text-align:right">' . number_format($data['qty']) . '</td>
                            <td>' . $data['created_date'] . '</td>
                            <td>' . $data['created_by'] . '</td>
                            <td>' . $status . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
