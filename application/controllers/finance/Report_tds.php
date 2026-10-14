<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_tds extends CI_Controller
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
            $this->load->view('finance/report_tds');
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
            header("Content-Disposition: attachment; filename=report_tds_$format.xls");
        }

        $filter_from  = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.supplier_id, b.name AS supplier_name, b.pen, c.section as tds,  
            (CASE WHEN SUBSTRING(b.pen, 4, 1) = 'P' THEN 'Non Company' ELSE 'Company' END) AS tds_status,
            c.individual, d.account_number, a.invoice_no, a.invoice_date, a.currency, SUM(a.total) as total, 
            a.number as posting_no, a.trans_date as posting_date,
            (d.credit - d.debit) AS tds_amount");
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join("(SELECT number, account_number, SUM(debit) as debit, SUM(credit) as credit FROM purchase_invoice_journals WHERE account_number IN ('17000001','17000002','17000003','17000004','17000005','17000006','17000028','17000029','17000034') GROUP BY number) d", 'a.number = d.number');
        $this->db->join('tds c', 'd.account_number = c.account_number', 'left');
        $this->db->join('(SELECT DISTINCT document_no, number, journal_date FROM journal_postings) e', 'a.number = e.document_no', 'left');
        $this->db->where("a.trans_date between '$filter_from' and '$filter_to'");
        $this->db->where('ABS(d.debit - d.credit) > 0');
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->group_by('a.number');
        $this->db->order_by('a.invoice_date', 'ASC');
        $data1 = $this->db->get()->result_array();

        // die(json_encode($data1));

        $this->db->select("a.company_id AS supplier_id, 
            a.supplier_name,
            a.pen,
            c.section as tds,
            (CASE WHEN SUBSTRING(a.pen, 4, 1) = 'P' THEN 'Non Company' ELSE 'Company' END) AS tds_status,
            c.individual, 
            b.account_number,
            b.account_name,
            b.invoice_no,
            b.trans_date AS invoice_date,
            b.currency,
            COALESCE((d.local_credit - d.local_debit), 0) AS total,
            a.number AS posting_no,
            a.journal_date AS posting_date,
            (b.credit - b.debit) AS tds_amount");
        $this->db->from("(SELECT a.*, b.name AS supplier_name, b.pen FROM journal_postings a JOIN suppliers b ON a.company_id = b.id GROUP BY a.number) a");
        $this->db->join("(SELECT number, account_number, account_name, invoice_no, trans_date, currency, SUM(local_debit) as debit, SUM(local_credit) as credit FROM journal_postings WHERE account_number IN ('17000001','17000002','17000003','17000004','17000005','17000006','17000028','17000029','17000034') GROUP BY number) b", "a.number = b.number");
        $this->db->join('tds c', "b.account_number = c.account_number", 'left');
        $this->db->join("(SELECT number, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings WHERE account_number NOT IN ('20100003','20100004','20100005','16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','17000001','17000002','17000003','17000004','17000005','17000006','17000008','17000009','17000010','17000011','17000012','17000013','17000028','17000029','17000034') GROUP BY number) d", "a.number = d.number", 'left');
        $this->db->where("a.journal_date between '$filter_from' and '$filter_to'");
        $this->db->where('a.modul', "ADJUSTMENT");
        $this->db->like('a.company_id', $filter_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->group_by('a.number');
        $this->db->order_by('a.company_id', 'ASC');
        $this->db->order_by('a.number', 'ASC');
        $data2 = $this->db->get()->result_array();

        $records = array_merge($data1, $data2);

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
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
                <h2 style="margin:0;">TDS REPORT</h2>
                <small>PERIOD : '.$filter_from.' To '.$filter_to.'</small>
            </center>
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Supplier No</th>
                    <th>Supplier Name</th>
                    <th>PAN No</th>
                    <th>TDS Section</th>
                    <th>Status of Supplier</th>
                    <th>TDS Rate</th>
                    <th>COA No</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Posting No</th>
                    <th>Posting Date</th>
                    <th>Currency</th>
                    <th>Taxable Amount</th>
                    <th>TDS Value</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['tds_amount'] < 0){
                $style = "color:red;";
                $tds_amount = (abs($data['tds_amount']) * -1);
                $total = (abs($data['total']) * -1);
            }else{
                $style = "";
                $tds_amount = abs($data['tds_amount']);
                $total = abs($data['total']);
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['supplier_id'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['pen'] . '</td>
                            <td>' . $data['tds'] . '</td>
                            <td>' . $data['tds_status'] . '</td>
                            <td>' . $data['individual'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['invoice_date'] . '</td>
                            <td>' . $data['posting_no'] . '</td>
                            <td>' . $data['posting_date'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td style="text-align:right;'.$style.'">' . $total . '</td>
                            <td style="text-align:right;'.$style.'">' . $tds_amount . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
