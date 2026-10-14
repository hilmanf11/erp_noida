<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ap2 extends CI_Controller
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
            $this->load->view('finance/report_ap');
        } else {
            redirect('error_access');
        }
    }

    public function formatNo($amount){
        if ($amount >= 0) {
            return number_format(@$amount, 2);
        } else {
            return "<span style='color:red;'>(" . number_format(abs($amount), 2) . ")</span>";
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ap_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        if($filter_from == "" && $filter_to == ""){
            $filter_from = "2024-11-01";
            $filter_to = date("Y-m-d");
        }

        $filter_supplier = base64_decode($this->input->get("filter_supplier"));
        $filter_currency = $this->input->get("filter_currency");
        $filter_display = $this->input->get("filter_display");

        if($filter_currency == "INR"){
            $filter_currency = "";
            $currency = "INR";
        }else{
            $filter_currency = $filter_currency;
            $currency = $filter_currency;
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $query = $this->db->query("SELECT a.id, a.name, b.account_number, '$currency' AS currency, 
            (CASE WHEN '$currency' = 'INR' THEN (COALESCE(b.balance_local, 0) + COALESCE(c.local_credit, 0) - COALESCE(c.local_debit, 0)) 
             ELSE (COALESCE(b.balance, 0) + COALESCE(c.original_credit, 0) - COALESCE(c.original_debit, 0)) END) AS amount
            FROM suppliers a
            LEFT JOIN account_balance_suppliers b ON a.id = b.supplier_id
            LEFT JOIN (SELECT company_id, SUM(original_debit) AS original_debit, SUM(original_credit) AS original_credit, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') AND journal_date <= '$filter_to' AND modul NOT IN ('CLEARING','BANK STATEMENT')
                GROUP BY company_id) c ON a.id = c.company_id
            WHERE a.id like '%$filter_supplier%'
            GROUP BY a.id
            ORDER BY a.name ASC");
        $suppliers = $query->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="font-size: 12px; text-align: left; margin-bottom: 20px;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <center style="margin-bottom: 20px;">
                <h3 style="margin:0;">ACCOUNT PAYABLE REPORT <i>(' . $filter_display . ')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>';

        $summary = '<table id="customers" border="1">
                        <tr>
                            <th width="20">No</th>
                            <th>Supplier ID</th>
                            <th>Supplier Name</th>
                            <th>Account No</th>
                            <th>Currency</th>
                            <th>Amount</th>
                        </tr>';

       
        $no = 1;
        $grand_local_balance = 0;
        foreach ($suppliers as $supplier) {
            $summary .= '   <tr>
                                <td>' . $no . '</td>
                                <td>' . $supplier['id'] . '</td>
                                <td>' . $supplier['name'] . '</td>
                                <td>' . $supplier['account_number'] . '</td>
                                <td>' . $currency . '</td>
                                <td style="text-align:right;">' . $this->formatNo($supplier['amount']) . '</td>
                            </tr>';
            $grand_local_balance += $supplier['amount'];
            $no++;
        }

        $summary .= '   <tr style="font-weight:bold;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>GRAND TOTAL</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_local_balance) . '</td>
                        </tr>';

        $htmlend = '</table></body></html>';
        echo $html . $summary . $htmlend;
    }
}
