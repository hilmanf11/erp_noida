<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_sales_registers extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/report_sales_registers');
        } else {
            redirect('error_access');
        }
    }

    public function readInvoice($customer_id){
        $customer_id = base64_decode($customer_id);
        $query = $this->crud->query("SELECT DISTINCT `number` FROM sales_invoices WHERE customer_id = '$customer_id' ORDER BY `number` ASC");
        die(json_encode($query));
    }

    public function readPo($customer_id){
        $customer_id = base64_decode($customer_id);
        $query = $this->crud->query("SELECT DISTINCT `customer_po` FROM sales_invoices WHERE customer_id = '$customer_id' ORDER BY `customer_po` ASC");
        die(json_encode($query));
    }

    public function readPartNo($customer_id){
        $customer_id = base64_decode($customer_id);
        $query = $this->crud->query("SELECT DISTINCT a.item_fg_id, b.number as item_fg_number, b.number_customer, b.name as item_fg_name FROM sales_invoices a JOIN item_fg b ON a.item_fg_id = b.id WHERE a.customer_id = '$customer_id' ORDER BY a.item_fg_id ASC");
        die(json_encode($query));
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_registers_$format.xls");
        }

        $filter_from  = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_po = base64_decode($this->input->get('filter_po'));
        $filter_item_no = base64_decode($this->input->get('filter_item_no'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as customer_name,
            d.gst_no,
            d.vendor_code,
            d.bill_address,
            d.bill_state,
            d.ship_address,
            d.ship_state,
            (CASE WHEN c.number is null THEN a.item_no ELSE c.number END) as item_fg_number, 
            c.number_customer, 
            (CASE WHEN c.name is null THEN a.item_name ELSE c.name END) as item_fg_name,
            e.account_name,
            f.igst as igst_rate,
            f.sgst as sgst_rate,
            f.cgst as cgst_rate,
            g.number as voucher_no,
            g.remarks as voucher_remarks
        ');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
        $this->db->join('customer_address d', 'a.customer_id = d.customer_id and a.plant = d.plant', 'left');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        $this->db->join('hsn f', 'c.hsn_code = f.number', 'left');
        $this->db->join('journal_postings g', "a.number = g.document_no and g.modul = 'SALES INVOICING'");
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where("a.trans_date between '$filter_from' and '$filter_to'");
        }
        if($filter_invoice_no != ""){
            $this->db->where('a.number', $filter_invoice_no);
        }
        if($filter_po != ""){
            $this->db->where('a.customer_po', $filter_po);
        }
        if($filter_customer != ""){
            $this->db->where('a.customer_id', $filter_customer);
        }
        if($filter_item_no != ""){
            $this->db->where('a.item_fg_id', $filter_item_no);
        }
        $this->db->group_by('a.id');
        $this->db->group_by('g.remarks');
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.customer_id', 'ASC');
        $this->db->order_by('a.number', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 300%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="font-size: 12px; text-align: left; margin-bottom:20px;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <center style="margin-bottom:20px;">
                <h2>SALES REGISTER REPORT</h2>
                <small>PERIOD '.$filter_from.' To '.$filter_to.'</small>
            </center>
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>GST No</th>
                    <th>Customer Code</th>
                    <th>Customer Plant</th>
                    <th>Bill To Customer Name</th>
                    <th>Customer GST No</th>
                    <th>State</th>
                    <th>State Code</th>
                    <th>Ship To Customer Name</th>
                    <th>Ship To Location</th>
                    <th>Ship To GST No</th>
                    <th>Vehicle No</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>ORG Inv No</th>
                    <th>ORG Inv Date</th>
                    <th>Currency</th>
                    <th>Exchange Rate</th>
                    <th>Product No EBWS</th>
                    <th>Product No Customer</th>
                    <th>Description</th>
                    <th>Invoice Type</th>
                    <th>HSN Code</th>
                    <th>Quantity</th>
                    <th>Taxable Value</th>
                    <th>IGST Rate</th>
                    <th>IGST Amount</th>
                    <th>CGST Rate</th>
                    <th>CGST Amount</th>
                    <th>SGST Rate</th>
                    <th>SGST Amount</th>
                    <th>TCS Amount</th>
                    <th>Total Invoice Value</th>
                    <th>COA No</th>
                    <th>COA Name</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['igst'] > 0){
                $igst_rate = $data['igst_rate'];
            }else{
                $igst_rate = 0;
            }

            if($data['sgst'] > 0){
                $sgst_rate = $data['sgst_rate'];
            }else{
                $sgst_rate = 0;
            }

            if($data['cgst'] > 0){
                $cgst_rate = $data['cgst_rate'];
            }else{
                $cgst_rate = 0;
            }

            if($data['voucher_remarks'] == "CANCEL SALES INVOICE" || $data['type'] == "RETURN"){
                $style = "style='color:red;'";
                $minus = "-";
            }else{
                $style = "";
                $minus = "";
            }

            $html .= '  <tr '.$style.'>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $config->gst_no . '</td>
                            <td>' . $data['vendor_code'] . '</td>
                            <td>' . $data['plant'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['gst_no'] . '</td>
                            <td>' . $data['bill_state'] . '</td>
                            <td></td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['ship_address'] . '</td>
                            <td>' . $data['gst_no'] . '</td>
                            <td>' . $data['vehicle_no'] . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td></td>
                            <td></td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['rate'] . '</td>
                            <td>' . $data['item_fg_number'] . '</td>
                            <td>' . $data['number_customer'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['hsn_code'] . '</td>
                            <td style="text-align:right;">' . $minus.$data['qty'] . '</td>
                            <td style="text-align:right;">' . $minus.$data['total'] . '</td>
                            <td style="text-align:right;">' . $igst_rate . '</td>
                            <td style="text-align:right;">' . $minus.$data['igst'] . '</td>
                            <td style="text-align:right;">' . $cgst_rate . '</td>
                            <td style="text-align:right;">' . $minus.$data['cgst'] . '</td>
                            <td style="text-align:right;">' . $sgst_rate . '</td>
                            <td style="text-align:right;">' . $minus.$data['sgst'] . '</td>
                            <td>' . $minus.$data['tcs'] . '</td>
                            <td style="text-align:right;">' . $minus.$data['total2'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
