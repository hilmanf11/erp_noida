<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_purchase_registers extends CI_Controller
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
            $this->load->view('finance/report_purchase_registers');
        } else {
            redirect('error_access');
        }
    }

    public function readInvoice($supplier_id){
        $supplier_id = base64_decode($supplier_id);
        $query = $this->crud->query("SELECT DISTINCT `invoice_no` FROM purchase_invoices WHERE supplier_id = '$supplier_id' ORDER BY `invoice_no` ASC");
        die(json_encode($query));
    }

    public function readPor($supplier_id){
        $supplier_id = base64_decode($supplier_id);
        $query = $this->crud->query("SELECT DISTINCT `por_no` FROM purchase_invoices WHERE supplier_id = '$supplier_id' ORDER BY `por_no` ASC");
        die(json_encode($query));
    }

    public function readPartNo($supplier_id){
        $supplier_id = base64_decode($supplier_id);
        $query = $this->crud->query("SELECT DISTINCT item_rm_id, item_no, item_name FROM purchase_invoices WHERE supplier_id = '$supplier_id' ORDER BY `item_rm_id` ASC");
        die(json_encode($query));
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_purchase_registers_$format.xls");
        }

        $filter_from  = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_por_no = base64_decode($this->input->get('filter_por_no'));
        $filter_item_familys = base64_decode($this->input->get('filter_item_familys'));
        $filter_item_no = base64_decode($this->input->get('filter_item_no'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, SUM(a.qty) as qty, SUM(a.igst) as igst, SUM(a.sgst) as sgst, SUM(a.cgst) as cgst,
            b.name as supplier_name, b.state, b.gst, i.section as tds, b.pen, c.name as journal_type_name, e.account_name, a.currency,
            f.number as item_rm_number, f.hsn_code, g.igst as igst_rate, g.sgst as sgst_rate, g.cgst as cgst_rate, h.name as product_family');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('journal_types c', 'a.journal_type_id = c.id', 'left');
        $this->db->join('purchase_invoice_journals d', "a.number = d.number and d.account_number IN ('17000001','17000002','17000003','17000004','17000005','17000006','17000028','17000029','17000034')", 'left');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        $this->db->join('item_rm f', 'a.item_rm_id = f.id', 'left');
        $this->db->join('hsn g', 'f.hsn_code = g.number', 'left');
        $this->db->join('item_familys h', 'f.item_family_id = h.id', 'left');
        $this->db->join('tds i', 'd.account_number = i.account_number', 'left');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where("a.trans_date between '$filter_from' and '$filter_to'");
        }
        if($filter_item_familys != ""){
            $this->db->like('h.id', $filter_item_familys);
        }
        if($filter_item_no != ""){
            $this->db->like('a.item_rm_id', $filter_item_no);
        }
        if($filter_por_no != ""){
            $this->db->like('a.por_no', $filter_por_no);
        }
        if($filter_supplier != ""){
            $this->db->like('a.supplier_id', $filter_supplier);
        }
        if($filter_invoice_no != ""){
            $this->db->like('a.invoice_no', $filter_invoice_no);
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.supplier_id', 'ASC');
        $this->db->order_by('a.number', 'ASC');
        $data1 = $this->db->get()->result_array();

        $this->db->select("a.supplier_name, '-' as por_no, 
            a.state,
            a.invoice_no,
            a.trans_date AS invoice_date,
            a.journal_date as trans_date,
            a.description AS item_rm_id,
            '-' AS asset_code,
            '-' AS item_rm_number,
            '-' AS product_family,
            '-' AS hsn_code,
            '1' AS qty,
            a.currency,
            a.rates AS rate,
            COALESCE((e.original_debit - e.original_credit), 0) AS price,
            COALESCE((b.local_debit - b.local_credit), 0) AS igst,
            COALESCE((c.local_debit - c.local_credit), 0) AS cgst,
            COALESCE((d.local_debit - d.local_credit), 0) AS sgst,
            abs(g.local_debit - g.local_credit) AS total_tcs,
            (f.local_credit - f.local_debit) AS total_tds,
            h.section AS tds,
            a.number,
            a.document_no,
            a.account_number,
            a.account_name,
            a.gst, 
            a.tds_sup, 
            a.pen,
            'JOURNAL' as flag");
        $this->db->from("(SELECT a.*, b.name as supplier_name, b.state, b.gst, b.tds as tds_sup, b.pen FROM journal_postings a JOIN suppliers b ON a.company_id = b.id WHERE a.journal_type = 'GL - SUPPLIER') a");
        $this->db->join("(SELECT number, account_number, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings WHERE account_number IN ('20100003','17000010','17000013') GROUP BY number) b", "a.number = b.number", 'left');
        $this->db->join("(SELECT number, account_number, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings WHERE account_number IN ('20100004','17000009','17000012') GROUP BY number) c", "a.number = c.number", 'left');
        $this->db->join("(SELECT number, account_number, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings WHERE account_number IN ('20100005','17000008','17000011') GROUP BY number) d", "a.number = d.number", 'left');
        $this->db->join("(SELECT number, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit FROM journal_postings WHERE account_number NOT IN ('20100003','20100004','20100005','16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','17000001','17000002','17000003','17000004','17000005','17000006','17000008','17000009','17000010','17000011','17000012','17000013','17000028','17000029','17000034') GROUP BY number) e", "a.number = e.number", 'left');
        $this->db->join("(SELECT number, account_number, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings WHERE account_number IN ('17000001','17000002','17000003','17000004','17000005','17000006','17000028','17000029','17000034') GROUP BY number) f", "a.number = f.number", 'left');
        $this->db->join("(SELECT number, account_number, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings WHERE account_number IN ('17000007') GROUP BY number) g", "a.number = g.number", 'left');
        $this->db->join('tds h', 'f.account_number = h.account_number', 'left');
        $this->db->where("a.journal_date between '$filter_from' and '$filter_to'");
        $this->db->where('a.modul', "ADJUSTMENT");
        $this->db->like('a.company_id', $filter_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->group_by('a.number');
        $this->db->order_by('a.company_id', 'ASC');
        $this->db->order_by('a.number', 'ASC');
        $data2 = $this->db->get()->result_array();

        $records = array_merge($data1, $data2);

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 250%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="font-size: 12px; text-align: left; margin-bottom:20px;">
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
            </center>
            <center style="margin-bottom:20px;">
                <h2 style="margin:0;">PURCHASE REGISTER REPORT</h2>
                <small>PERIOD : '.$filter_from.' To '.$filter_to.'</small>
            </center>
            <table id="customers" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Company GST No</th>
                    <th rowspan="2">Name of Supplier</th>
                    <th rowspan="2">Supplier GST No</th>
                    <th rowspan="2">State Code</th>
                    <th rowspan="2">Invoice No</th>
                    <th rowspan="2">Invoice Date</th>
                    <th rowspan="2">Posting Date</th>
                    <th rowspan="2">Part No EBWS</th>
                    <th rowspan="2">Asset Code</th>
                    <th rowspan="2">Part Description</th>
                    <th rowspan="2">Material Type</th>
                    <th rowspan="2">HSN / SAC</th>
                    <th rowspan="2">Quantity of Good</th>
                    <th rowspan="2">Currency</th>
                    <th rowspan="2">Unit Price</th>
                    <th rowspan="2">Exchange Rate</th>
                    <th rowspan="2">Taxable Value</th>
                    <th rowspan="2">IGST Rate</th>
                    <th rowspan="2">IGST Amount</th>
                    <th rowspan="2">CGST Rate</th>
                    <th rowspan="2">CGST Amount</th>
                    <th rowspan="2">SGST Rate</th>
                    <th rowspan="2">SGST Amount</th>
                    <th rowspan="2">TCS Amount</th>
                    <th rowspan="2">TDS Amount</th>
                    <th rowspan="2">TDS Section</th>
                    <th rowspan="2">Total Invoice</th>
                    <th rowspan="2">PI No</th>
                    <th rowspan="2">GRN No</th>
                    <th rowspan="2">GRN Date</th>
                    <th rowspan="2">COA No</th>
                    <th rowspan="2">COA Name</th>
                    <th colspan="2">Purchase Orders</th>
                </tr>
                <tr>
                    <th>PO No</th>
                    <th>Value</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $receipt_no = $data['por_no'];
            $item_rm_id = str_replace("'", '', $data['item_rm_id']);

            $por =  $this->db->query("SELECT a.receipt_date, b.po_no, b.currency, b.qty, b.total FROM purchase_order_receipts a
                    JOIN purchase_orders b ON a.po_no = b.po_no AND a.item_rm_id = b.item_rm_id
                    WHERE a.receipt_no = '$receipt_no' and a.item_rm_id = '$item_rm_id'");
            $porData = $por->row();

            if(empty($porData)){
                $por =  $this->db->query("SELECT a.item_rm_id, a.receipt_date, b.po_no, c.currency, b.qty, b.total FROM purchase_order_receipt_others a
                    JOIN purchase_order_others b ON a.po_no = b.po_no AND a.item_rm_id = b.item_rm_id
                    JOIN suppliers c ON b.supplier_id = c.id
                    WHERE a.receipt_no = '$receipt_no' and a.item_rm_id = '$item_rm_id'
                    GROUP BY a.id");
                $porData = $por->row();
            }

            if($data['igst'] > 0){
                $igst_rate = @$data['igst_rate'];
            }else{
                $igst_rate = 0;
            }

            if($data['sgst'] > 0){
                $sgst_rate = @$data['sgst_rate'];
            }else{
                $sgst_rate = 0;
            }

            if($data['cgst'] > 0){
                $cgst_rate = @$data['cgst_rate'];
            }else{
                $cgst_rate = 0;
            }

            $price = ($data['price'] * $data['rate']);
            $total = ($data['qty'] * $price);
            $total2 = ($total + $data['igst'] + $data['cgst'] + $data['sgst'] + $data['total_tcs']);

            if($total2 == 0){
                $total2 = $data['total_tds'];
            }

            if($data['currency'] == ""){
                $currency = "INR";
            }else{
                $currency = $data['currency'];
            }

            if($total2 < 0 || $data['flag'] == "RETURN"){
                $style = "style='color:red;'";
            }else{
                $style = "";
            }

            if($data['total_tds'] < 0){
                $style_tds = "color:red;";
            }else{
                $style_tds = "";
            }

            if($data['flag'] == "RETURN"){
                $minus = -1;
            }else{
                $minus = 1;
            }

            $html .= '  <tr '.$style.'>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $config->gst_no . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['gst'] . '</td>
                            <td>' . $data['state'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['invoice_date'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['asset_code'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['hsn_code'] . '</td>
                            <td style="text-align:right;">' . ($data['qty'] * $minus) . '</td>
                            <td style="text-align:center;">' . $currency . '</td>
                            <td style="text-align:right;">' . $data['price'] . '</td>
                            <td style="text-align:right;">' . $data['rate'] . '</td>
                            <td style="text-align:right;">' . ($total * $minus) . '</td>
                            <td style="text-align:right;">' . $igst_rate . '</td>
                            <td style="text-align:right;">' . ($data['igst'] * $minus) . '</td>
                            <td style="text-align:right;">' . $cgst_rate . '</td>
                            <td style="text-align:right;">' . ($data['cgst'] * $minus) . '</td>
                            <td style="text-align:right;">' . $sgst_rate . '</td>
                            <td style="text-align:right;">' . ($data['sgst'] * $minus) . '</td>
                            <td style="text-align:right;">' . ($data['total_tcs'] * $minus) . '</td>
                            <td style="text-align:right;'.$style_tds.'">' . ($data['total_tds'] * $minus) . '</td>
                            <td>' . $data['tds'] . '</td>
                            <td style="text-align:right;">' . ($total2 * $minus) . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['por_no'] . '</td>
                            <td>' . @$porData->receipt_date . '</td>
                            <td style="mso-number-format:\@;">' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td style="text-align:left;">' . @$porData->po_no . '</td>
                            <td style="text-align:right;">' . (($data['qty'] * $data['price']) * $minus) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
