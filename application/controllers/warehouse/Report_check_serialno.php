<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_check_serialno extends CI_Controller
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
            $this->load->view('warehouse/report_check_serialno');
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
        $filter_receipt = base64_decode($this->input->get("filter_receipt"));
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status = $this->input->get("filter_status");
        $filter_type = $this->input->get("filter_type");

        if($filter_type == "STO"){
            //Details
            $this->db->select("a.label_no,
                '-' as supplier_name,
                c.item_rm_id,
                d.number as item_rm_number,
                d.name as item_rm_name,
                c.id as receipt_id,
                c.trans_date as receipt_date,
                c.qty as qty_receipt,
                a.warehouse,
                a.qty as scan_in, 
                a.created_date as scan_in_date,
                a.created_by as scan_in_by, 
                f.request_no,
                f.qty as scan_out,
                f.created_date as scan_out_date,
                f.created_by as scan_out_by,
                (CASE WHEN g.id is not null THEN 3 WHEN e.id is not null THEN 2 WHEN f.request_no is null THEN 0 ELSE 1 END) as status 
            ");
            $this->db->from('scan_item_receipts a');
            $this->db->join('os_rm c', 'a.receipt_id = c.id');
            $this->db->join('item_rm d', 'c.item_rm_id = d.id');
            $this->db->join('scan_item_rejections e', 'a.label_no = e.label_no', 'left');
            $this->db->join('issued_material_details f', 'a.label_no = f.label_no', 'left');
            $this->db->join('barcode_divides g', 'a.label_no = g.label_no', 'left');
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
            $this->db->like('a.label_no', $filter_serial_no);
            $this->db->like('c.id', $filter_receipt);
            $this->db->like('c.item_rm_id', $filter_product_no);
            if ($filter_status == "0") {
                $this->db->where('f.request_no is null');
                $this->db->where('e.id is null');
                $this->db->where('g.id is null');
            }elseif($filter_status == "1"){
                $this->db->where('f.request_no is not null');
            }elseif($filter_status == "2"){
                $this->db->where('e.id is not null');
            }elseif($filter_status == "3"){
                $this->db->where('g.id is not null');
            }
            $this->db->group_by('a.label_no');
            $this->db->order_by('a.id', 'ASC');
            // $this->db->order_by('a.created_date', 'ASC');
            $records = $this->db->get()->result_array();
            
        }else if($filter_type == "POR"){
            //Details
            $this->db->select('a.label_no,
                e.name as supplier_name,
                c.item_rm_id,
                d.number as item_rm_number,
                d.name as item_rm_name,
                c.receipt_id,
                c.receipt_date,
                c.qty_receipt,
                a.warehouse,
                a.qty as scan_in, 
                a.created_date as scan_in_date,
                a.created_by as scan_in_by, 
                f.request_no,
                f.qty as scan_out,
                f.created_date as scan_out_date,
                f.created_by as scan_out_by,
                (CASE WHEN h.id is not null THEN 3 WHEN g.id is not null THEN 2 WHEN a.status = 1 THEN 1 WHEN f.request_no is null THEN 0 ELSE 1 END) as status
            ');
            $this->db->from('scan_item_receipts a');
            $this->db->join('purchase_order_labels b', 'a.label_no = b.label_no');
            $this->db->join('purchase_order_receipts c', 'b.receipt_id = c.receipt_id');
            $this->db->join('item_rm d', 'c.item_rm_id = d.id');
            $this->db->join('suppliers e', 'c.supplier_id = e.id');
            $this->db->join('issued_material_details f', 'a.label_no = f.label_no', 'left');
            $this->db->join('scan_item_rejections g', 'a.label_no = g.label_no', 'left');
            $this->db->join('barcode_divides h', 'a.label_no = h.label_no', 'left');
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
            $this->db->like('a.label_no', $filter_serial_no);
            $this->db->like('c.supplier_id', $filter_supplier);
            $this->db->like('c.receipt_no', $filter_receipt);
            $this->db->like('c.item_rm_id', $filter_product_no);
            if ($filter_status == "0") {
                $this->db->where('f.request_no is null');
                $this->db->where('g.id is null');
                $this->db->where('h.id is null');
            }elseif($filter_status == "1"){
                $this->db->where('f.request_no is not null');
            }elseif($filter_status == "2"){
                $this->db->where('g.id is not null');
            }elseif($filter_status == "3"){
                $this->db->where('h.id is not null');
            }
            $this->db->group_by('a.label_no');
            $this->db->order_by('a.id', 'ASC');
            // $this->db->order_by('a.created_date', 'ASC');
            $records = $this->db->get()->result_array();
        }else if($filter_type == "DIV"){
            //Details
            $this->db->select("a.label_divided as label_no,
                e.name as supplier_name,
                d.id as item_rm_id,
                d.number as item_rm_number,
                d.name as item_rm_name,
                (CASE WHEN c.id is null THEN ba.receipt_id ELSE c.id END) as receipt_id,
                (CASE WHEN c.trans_date is null THEN ba.receipt_date ELSE c.trans_date END) as receipt_date,
                (CASE WHEN c.qty is null THEN ba.qty_receipt ELSE c.qty END) as qty_receipt,
                a.type as warehouse,
                a.qty as scan_in, 
                a.created_date as scan_in_date,
                a.created_by as scan_in_by, 
                (CASE WHEN f.request_no is not null THEN f.request_no ELSE i.rgp_number END) as request_no,
                (CASE WHEN f.qty is not null THEN f.qty ELSE i.qty END) as scan_out,
                (CASE WHEN f.created_date is not null THEN f.created_date ELSE i.created_date END) as scan_out_date,
                (CASE WHEN f.created_by is not null THEN f.created_by ELSE i.created_by END) as scan_out_by,
                (CASE WHEN h.id is not null THEN 3 WHEN g.id is not null THEN 2 WHEN i.id is not null THEN 4 WHEN f.request_no is null THEN 0 ELSE 1 END) as status
            ");
            $this->db->from('barcode_divides a');
            $this->db->join('purchase_order_labels b', 'a.reff = b.receipt_id', 'left');
            $this->db->join('purchase_order_receipts ba', 'b.receipt_id = ba.receipt_id', 'left');
            $this->db->join('os_rm c', 'a.reff = c.id', 'left');
            $this->db->join('item_rm d', '(ba.item_rm_id = d.id or c.item_rm_id = d.id)');
            $this->db->join('suppliers e', 'ba.supplier_id = e.id', 'left');
            $this->db->join('issued_material_details f', 'a.label_divided = f.label_no', 'left');
            $this->db->join('scan_item_rejections g', 'a.label_no = g.label_no', 'left');
            $this->db->join('barcode_divides h', 'a.label_divided = h.label_no', 'left');
            $this->db->join('scan_item_rgp i', 'a.label_divided = i.label_no', 'left');
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
            // $this->db->where('a.type', "SUPPLY");
            if($filter_serial_no != ""){
                $this->db->like('a.label_divided', $filter_serial_no);
                $this->db->or_like('a.label_no', $filter_serial_no);
            }
            // $this->db->like('e.id', $filter_supplier);
            if($filter_receipt != ""){
                $this->db->where('c.id', $filter_receipt);
            }
            if($filter_product_no != ""){
                $this->db->where('d.id', $filter_product_no);
            }
            if ($filter_status == "0") {
                $this->db->where('f.request_no is null');
                $this->db->where('g.id is null');
                $this->db->where('h.id is null');
            }elseif($filter_status == "1"){
                $this->db->where('f.request_no is not null');
            }elseif($filter_status == "2"){
                $this->db->where('g.id is not null');
            }elseif($filter_status == "3"){
                $this->db->where('h.id is not null');
            }
            $this->db->group_by('a.label_divided');
            $this->db->order_by('a.id', 'ASC');
            // $this->db->order_by('a.created_date', 'ASC');
            $records = $this->db->get()->result_array();
        }else if($filter_type == "REJ"){
            //Details
            $this->db->select("a.label_no,
                d.name as supplier_name,
                b.item_rm_id,
                c.number as item_rm_number,
                c.name as item_rm_name,
                b.receipt_id,
                b.receipt_date,
                b.qty_receipt,
                '-' as warehouse,
                a.qty as scan_in, 
                a.created_date as scan_in_date,
                a.created_by as scan_in_by, 
                e.return_no as request_no,
                a.qty as scan_out,
                e.return_date as scan_out_date,
                e.return_name scan_out_by,
                a.status
            ");
            $this->db->from('scan_item_rejections a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id', 'left');
            $this->db->join('item_rm c', 'b.item_rm_id = c.id');
            $this->db->join('suppliers d', 'b.supplier_id = d.id', 'left');
            $this->db->join('purchase_returns e', 'b.receipt_id = e.receipt_id', 'left');
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
            $this->db->like('a.label_no', $filter_serial_no);
            if($filter_receipt != ""){
                $this->db->where('b.receipt_id', $filter_receipt);
            }
            if($filter_product_no != ""){
                $this->db->where('b.item_rm_id', $filter_product_no);
            }
            if($filter_status == "0"){
                $this->db->where('a.status', 0);
            }elseif($filter_status == "1"){
                $this->db->where('a.status', 1);
            }
            $this->db->group_by('a.label_no');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
        }else if($filter_type == "RGP"){
            //Details
            $this->db->select('a.label_no,
                (CASE WHEN e.name is null THEN ec.name ELSE e.name END) as supplier_name,
                d.id as item_rm_id,
                d.number as item_rm_number,
                d.name as item_rm_name,
                c.rgp_id as receipt_id,
                c.transaction_date as receipt_date,
                c.qty as qty_receipt,
                a.warehouse,
                a.qty as scan_in, 
                a.created_date as scan_in_date,
                a.created_by as scan_in_by, 
                (CASE WHEN f.request_no is not null THEN f.request_no WHEN i.rgp_number is not null THEN i.rgp_number ELSE null END) as request_no,
                (CASE WHEN f.qty is not null THEN f.qty WHEN i.qty is not null THEN i.qty ELSE null END) as scan_out,
                (CASE WHEN f.created_date is not null THEN f.created_date WHEN i.created_date is not null THEN i.created_date ELSE null END) as scan_out_date,
                (CASE WHEN f.created_by is not null THEN f.created_by WHEN i.created_by is not null THEN i.created_by ELSE null END) as scan_out_by,
                (CASE WHEN h.id is not null THEN 3 WHEN g.id is not null THEN 2 WHEN a.status = 1 THEN 1 WHEN f.request_no is null THEN 0 ELSE 1 END) as status
            ');
            $this->db->from('scan_item_receipts a');
            $this->db->join('rgp_transactions c', 'a.receipt_id = c.rgp_id');
            $this->db->join('item_rm d', 'c.item_id = d.id');
            $this->db->join('suppliers e', 'c.company_id = e.id', 'left');
            $this->db->join('customers ec', 'c.company_id = ec.id', 'left');
            $this->db->join('issued_material_details f', 'a.label_no = f.label_no', 'left');
            $this->db->join('scan_item_rejections g', 'a.label_no = g.label_no', 'left');
            $this->db->join('barcode_divides h', 'a.label_no = h.label_no', 'left');
            $this->db->join('scan_item_rgp i', "a.label_no = i.label_no and i.type = 'OUT'", 'left');
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
            $this->db->like('a.label_no', $filter_serial_no);
            $this->db->like('c.company_id', $filter_supplier);
            $this->db->like('c.number', $filter_receipt);
            $this->db->like('c.item_id', $filter_product_no);
            if ($filter_status == "0") {
                $this->db->where('f.request_no is null');
                $this->db->where('g.id is null');
                $this->db->where('h.id is null');
            }elseif($filter_status == "1"){
                $this->db->where('f.request_no is not null');
            }elseif($filter_status == "2"){
                $this->db->where('g.id is not null');
            }elseif($filter_status == "3"){
                $this->db->where('h.id is not null');
            }
            $this->db->group_by('a.label_no');
            $this->db->order_by('a.id', 'ASC');
            // $this->db->order_by('a.created_date', 'ASC');
            $records = $this->db->get()->result_array();
        }
        
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
                <h3 style="margin:0;">CHECK SERIAL NO (RM)</h3>
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
                <th>Receipt No</th>
                <th>Receipt Date</th>
                <th>Qty<br>Receipt</th>
                <th>Warehouse</th>
                <th>Scan In</th>
                <th>Scan In Date</th>
                <th>Scan In By</th>
                <th>Issued No</th>
                <th>Scan Out</th>
                <th>Scan Out Date</th>
                <th>Scan Out By</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            // $barcodeDivided = $this->crud->reads("barcode_divides", [], ["label_no" => $data['label_no']]);
            
            if ($data['status'] == 0) {
                $status = "<b style='color:green;'>OPEN</b>";
            } else if ($data['status'] == 1) {
                if($data['request_no'] == ""){
                    $status = "<b style='color:red;'>CLOSE<br>MANUAL</br>";
                }else{
                    $status = "<b style='color:red;'>CLOSE</b>";
                }
            } else if ($data['status'] == 2) {
                $status = "<b style='color:red;'>CLOSE<br>REJECTION</br>";
            } else if ($data['status'] == 3) {
                $status = "<b style='color:red;'>CLOSE <br> DIVIDED</br>";
            } else if ($data['status'] == 4) {
                $status = "<b style='color:red;'>CLOSE <br> RGP/NRGP</br>";
            }
            
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . strtoupper($data['label_no']) . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td>' . $data['receipt_id'] . '</td>
                            <td>' . $data['receipt_date'] . '</td>
                            <td style="text-align:right">' . number_format($data['qty_receipt']) . '</td>
                            <td>' . $data['warehouse'] . '</td>
                            <td style="text-align:right">' . number_format($data['scan_in']) . '</td>
                            <td>' . $data['scan_in_date'] . '</td>
                            <td>' . $data['scan_in_by'] . '</td>
                            <td>' . $data['request_no'] . '</td>
                            <td style="text-align:right">' . number_format($data['scan_out']) . '</td>
                            <td>' . $data['scan_out_date'] . '</td>
                            <td>' . $data['scan_out_by'] . '</td>
                            <td>' . $status . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
