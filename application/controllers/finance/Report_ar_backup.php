<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ar extends CI_Controller
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
            $this->load->view('finance/report_ar');
        } else {
            redirect('error_access');
        }
    }

    public function readPi($customer_id)
    {
        $query = $this->crud->query("SELECT DISTINCT `number` FROM sales_invoices WHERE customer_id = '$customer_id' ORDER BY `number` ASC");
        die(json_encode($query));
    }

    function readExchangeRate($currency, $receipt_date)
    {
        $search_date = date("d", strtotime($receipt_date));
        if ($search_date == "31") {
            $receipt_date = date("Y-m-d", strtotime('-10 days', strtotime($receipt_date)));
        }

        $monthBf = date('Y-m-01', strtotime($receipt_date));
        $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

        if ($exchange) {
            $amount = $exchange->middle;
        } else {
            $amount = 1;
        }

        return $amount;
    }

    public function begin_balance($customer_id, $plant, $filter_from, $filter_status = "", $balance_local){
        $customer = $this->crud->read("customers", [], ["id" => $customer_id]);
        $company_name = "(".$plant.") ".$customer->name;
        $company_name2 = $customer->name . " | " . $plant;

        $query_1 = $this->db->query("SELECT 
            'SI' AS source,
            a.number AS invoice_no,
            a.trans_date AS invoice_date,
            a.due_date AS payment_due,
            a.dn_number AS document_no,
            b.description,
            b.journal_date AS voucher_date,
            b.number AS voucher_no,
            b.account_number,
            c.number as cl_no,
            c.journal_date as cl_date,
            a.currency,
            b.local_debit,
            b.local_credit,
            (CASE WHEN d.number is null THEN a.status ELSE 1 END) as status
        FROM sales_invoices a
        JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
            FROM journal_postings 
            WHERE  modul NOT IN ('CLEARING','ADJUSTMENT') and account_number IN ('27000001') 
            GROUP BY number, document_no, account_number) b ON a.number = b.document_no
        LEFT JOIN journal_clearings c ON a.number = c.document_no
        LEFT JOIN sales_invoices d ON a.number = d.number and d.cancel = 1
        WHERE a.customer_id = '$customer_id' AND a.plant = '$plant' AND b.journal_date < '$filter_from'
        GROUP BY a.number, b.number
        ORDER BY b.journal_date ASC");
        $data_1 = $query_1->result_array();

        $query_2 = $this->db->query("SELECT
            'AR' AS source,
            a.receipt_no AS invoice_no,
            a.receipt_date AS invoice_date,
            '-' AS payment_due,
            a.sales_invoice AS document_no,
            b.description,
            b.journal_date AS voucher_date,
            b.number AS voucher_no,
            b.account_number,
            d.number as cl_no,
            d.journal_date as cl_date,
            a.currency,
            (CASE WHEN a.account_type = 'DEBIT' THEN b.local_debit ELSE 0 END) as local_debit,
            (CASE WHEN a.account_type = 'CREDIT' THEN b.local_credit ELSE 0 END) as local_credit,
            (CASE WHEN c.status IS NULL THEN a.status_cl ELSE c.status END) as status
        FROM ar_receipts a
        LEFT JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
            FROM journal_postings 
            WHERE modul != 'CLEARING' and account_number IN ('27000001') 
            GROUP BY number, document_no, account_number) b ON a.receipt_no = b.document_no
        LEFT JOIN sales_invoices c ON a.sales_invoice = c.number
        LEFT JOIN journal_clearings d ON a.receipt_no = d.document_no
        WHERE a.customer_id = '$customer_id' AND a.plant = '$plant' AND b.journal_date < '$filter_from'
        GROUP BY a.receipt_no, a.account_number
        ORDER BY b.journal_date");
        $data_2 = $query_2->result_array();

        $query_3 = $this->db->query("SELECT
            'AD' AS source,
            a.invoice_no,
            a.trans_date AS invoice_date,
            '-' AS payment_due,
            a.document_no,
            a.description,
            a.journal_date AS voucher_date,
            a.number AS voucher_no,
            a.account_number,
            b.number as cl_no,
            b.journal_date as cl_date,
            a.currency,
            SUM(a.local_debit) AS local_debit,
            SUM(a.local_credit) AS local_credit,
            a.status_cl as status
        FROM journal_postings a
        LEFT JOIN journal_clearings b ON a.document_no = b.document_no and a.company_id = b.company_id and a.description = b.description and b.company_name like '%$plant%'
        WHERE a.company_id = '$customer_id' and a.company_name like '%$company_name%'
        AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') 
        AND a.account_number IN ('27000001','28000004','28000007')
        AND a.journal_date < '$filter_from'
        GROUP BY a.number
        ORDER BY a.journal_date ASC");
        $data_3 = $query_3->result_array();

        $query_4 = $this->db->query("SELECT
            'CL' AS source,
            a.invoice_no,
            a.trans_date as invoice_date,
            '-' AS payment_due,
            a.document_no,
            a.description,
            a.journal_date AS voucher_date,
            a.number AS voucher_no,
            a.account_number,
            b.number as cl_no,
            b.journal_date as cl_date,
            a.currency,
            SUM(a.local_debit) AS local_debit,
            SUM(a.local_credit) AS local_credit,
            a.status_cl as status
        FROM journal_postings a
        LEFT JOIN journal_clearings b ON a.id = b.document_no and a.number = b.invoice_no and a.company_id = b.company_id
        WHERE a.company_id = '$customer_id' and (a.company_name like '%$company_name%' or a.company_name like '%$company_name2%')
        AND a.modul IN ('CLEARING') and a.account_number IN ('27000001') 
        AND a.journal_date < '$filter_from'
        GROUP BY a.id
        ORDER BY a.journal_date ASC");
        $data_4 = $query_4->result_array();

        $purchase_invoices = array_merge($data_1, $data_2, $data_3, $data_4);

        $local_debit = 0;
        $local_credit = 0;
        $no = 0;
        foreach ($purchase_invoices as $purchase_invoice) {
            // if($purchase_invoice['cl_no'] == ""){
                $local_debit += $purchase_invoice['local_debit'];
                $local_credit += $purchase_invoice['local_credit'];
            // }

            $no++;
        }

        $begin_balance = ($balance_local + $local_debit - $local_credit);
        return $begin_balance;
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
            header("Content-Disposition: attachment; filename=report_ar_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        if($filter_from == "" && $filter_to == ""){
            $filter_from = "2024-10-01";
            $filter_to = date("Y-m-d");
        }
        $filter_customer = $this->input->get("filter_customer");
        $filter_plant = $this->input->get("filter_plant");
        $filter_currency = $this->input->get("filter_currency");
        $filter_payment = $this->input->get("filter_payment");
        $filter_display = $this->input->get("filter_display");
        $filter_status = $this->input->get("filter_status");
        $filter_sales_invoice = base64_decode($this->input->get("filter_sales_invoice"));
        $period = date("Ym", strtotime($filter_to));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, c.plant, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->from('customers a');
        $this->db->join('customer_address c', 'a.id = c.customer_id', 'left');
        $this->db->join('account_balance_customers b', 'a.id = b.customer_id and b.plant = c.plant', 'left');
        if ($filter_customer != "") {
            $this->db->where("a.id", $filter_customer);
        }
        if ($filter_plant != "") {
            $this->db->where("c.plant", $filter_plant);
        }
        $this->db->group_by('c.plant');
        $this->db->group_by('c.customer_id');
        $this->db->order_by('a.name', 'asc');
        $customers = $this->db->get()->result_array();

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
                <h3 style="margin:0;">ACCOUNT RECEIVABLE REPORT <i>(' . $filter_display . ')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>';

        $summary = '<table id="customers" border="1">
                            <tr>
                                <th width="20">No</th>
                                <th>Customer ID</th>
                                <th>Customer Name</th>
                                <th>Plant</th>
                                <th>Currency</th>
                                <th>LOCAL CURRENCY<br><i>Balance</i></th>
                            </tr>';

        $invoice = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Customer ID</th>
                            <th rowspan="2">Customer Name</th>
                            <th rowspan="2">Plant</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Posting Date</th>
                            <th rowspan="2">Posting No</th>
                            <th rowspan="2">Clearing No</th>
                            <th rowspan="2">Clearing Date</th>
                            <th rowspan="2">Account No</th>
                            <th rowspan="2">Currency</th>
                            <th colspan="4">LOCAL CURRENCY</th>
                        </tr>
                        <tr>
                            <th>Invoice</th>
                            <th>Payment</th>
                            <th>Remain</th>
                            <th>Accumulated</th>
                        </tr>';
    
        $detail = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Customer ID</th>
                            <th rowspan="2">Customer Name</th>
                            <th rowspan="2">Plant</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Posting Date</th>
                            <th rowspan="2">Posting No</th>
                            <th rowspan="2">Clearing No</th>
                            <th rowspan="2">Clearing Date</th>
                            <th rowspan="2">Account No</th>
                            <th rowspan="2">Currency</th>
                            <th colspan="4">LOCAL CURRENCY</th>
                        </tr>
                        <tr>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                            <th>Accumulated</th>
                        </tr>';
        $no = 1;
        $noid = 1;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $grand_local_balance = 0;
        $grand_summary_total = 0;

        function compare_trans_date($a, $b) {
            return strtotime($a['voucher_date']) - strtotime($b['voucher_date']);
        }

        foreach ($customers as $customer) {
            $customer_id = $customer['id'];
            $customer_name = $customer['name'];
            $plant = $customer['plant'];
            $company_name = "(".$plant.") ".$customer_name;
            $company_name2 = $customer_name . " | " . $plant;

            if($filter_display != "Invoice"){
                $where_inv = "";
            }else{
                $where_inv = "AND a.sales_invoice NOT IN (SELECT DISTINCT number FROM sales_invoices)";
            }

            $query_1 = $this->db->query("SELECT 
                'SI' AS source,
                a.number AS invoice_no,
                a.trans_date AS invoice_date,
                a.due_date AS payment_due,
                a.dn_number AS document_no,
                b.description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                c.number as cl_no,
                c.journal_date as cl_date,
                a.currency,
                b.local_debit,
                b.local_credit,
                (CASE WHEN d.number is null THEN a.status ELSE 1 END) as status
            FROM sales_invoices a
            JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE  modul NOT IN ('CLEARING','ADJUSTMENT') and account_number IN ('27000001') 
                GROUP BY number, document_no, account_number) b ON a.number = b.document_no
            LEFT JOIN journal_clearings c ON a.number = c.document_no
            LEFT JOIN sales_invoices d ON a.number = d.number and d.cancel = 1
            WHERE a.customer_id = '$customer_id' AND a.plant = '$plant' AND b.journal_date BETWEEN '$filter_from' AND '$filter_to' AND a.status LIKE '%$filter_status%'
            GROUP BY a.number, b.number
            ORDER BY b.journal_date ASC");
            $data_1 = $query_1->result_array();

            $query_2 = $this->db->query("SELECT
                'AR' AS source,
                a.receipt_no AS invoice_no,
                a.receipt_date AS invoice_date,
                '-' AS payment_due,
                a.sales_invoice AS document_no,
                b.description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                d.number as cl_no,
                d.journal_date as cl_date,
                a.currency,
                (CASE WHEN a.account_type = 'DEBIT' THEN b.local_debit ELSE 0 END) as local_debit,
                (CASE WHEN a.account_type = 'CREDIT' THEN b.local_credit ELSE 0 END) as local_credit,
                (CASE WHEN c.status IS NULL THEN a.status_cl ELSE c.status END) as status
            FROM ar_receipts a
            LEFT JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE modul != 'CLEARING' and account_number IN ('27000001') 
                GROUP BY number, document_no, account_number) b ON a.receipt_no = b.document_no
            LEFT JOIN sales_invoices c ON a.sales_invoice = c.number
            LEFT JOIN journal_clearings d ON a.receipt_no = d.document_no
            WHERE a.customer_id = '$customer_id' AND a.plant = '$plant' $where_inv AND b.journal_date BETWEEN '$filter_from' AND '$filter_to' AND (CASE WHEN c.status IS NULL THEN a.status_cl ELSE c.status END) LIKE '%$filter_status%'
            GROUP BY a.receipt_no, a.account_number
            ORDER BY b.journal_date");
            $data_2 = $query_2->result_array();

            $query_3 = $this->db->query("SELECT
                'AD' AS source,
                a.invoice_no,
                a.trans_date AS invoice_date,
                '-' AS payment_due,
                a.document_no,
                a.description,
                a.journal_date AS voucher_date,
                a.number AS voucher_no,
                a.account_number,
                b.number as cl_no,
                b.journal_date as cl_date,
                a.currency,
                SUM(a.local_debit) AS local_debit,
                SUM(a.local_credit) AS local_credit,
                a.status_cl as status
            FROM journal_postings a
            LEFT JOIN journal_clearings b ON a.document_no = b.document_no and a.company_id = b.company_id and a.description = b.description and b.company_name like '%$plant%'
            WHERE a.company_id = '$customer_id' and a.company_name like '%$company_name%'
            AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') 
            AND a.account_number IN ('27000001','28000004','28000007')
            AND a.journal_date BETWEEN '$filter_from' and '$filter_to'
            AND a.status_cl LIKE '%$filter_status%'
            GROUP BY a.number
            ORDER BY a.journal_date ASC");
            $data_3 = $query_3->result_array();

            $query_4 = $this->db->query("SELECT
                'CL' AS source,
                a.invoice_no,
                a.trans_date as invoice_date,
                '-' AS payment_due,
                a.document_no,
                a.description,
                a.journal_date AS voucher_date,
                a.number AS voucher_no,
                a.account_number,
                b.number as cl_no,
                b.journal_date as cl_date,
                a.currency,
                SUM(a.local_debit) AS local_debit,
                SUM(a.local_credit) AS local_credit,
                a.status_cl as status
            FROM journal_postings a
            LEFT JOIN journal_clearings b ON a.id = b.document_no and a.number = b.invoice_no and a.company_id = b.company_id
            WHERE a.company_id = '$customer_id' and (a.company_name like '%$company_name%' or a.company_name like '%$company_name2%')
            AND a.modul IN ('CLEARING') and a.account_number IN ('27000001') 
            AND a.journal_date BETWEEN '$filter_from' and '$filter_to'
            GROUP BY a.id
            ORDER BY a.journal_date ASC");
            $data_4 = $query_4->result_array();

            $sales_invoices = array_merge($data_1, $data_2, $data_3, $data_4);

            usort($sales_invoices, 'compare_trans_date');

            $local_debit = 0;
            $local_credit = 0;

            $begin_balance_local = $this->begin_balance($customer_id, $plant, $filter_from, $filter_status, @$customer['balance_local']);

            // if (count($sales_invoices) > 0) {
                $detail .= '  <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="16">BEGINING BALANCE ('.$customer_name.')</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">' . number_format(@$begin_balance_local, 2) . '</td>
                                </tr>';

                $invoice .= '    <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="16">BEGINING BALANCE ('.$customer_name.')</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">' . number_format(@$begin_balance_local, 2) . '</td>
                                </tr>';
            // }

            $accumulated = $begin_balance_local;
            foreach ($sales_invoices as $sales_invoice) {
                $document_no = $sales_invoice['invoice_no'];
                if ($filter_display == "Invoice") {
                    $payments = $this->db->query("SELECT a.*, b.number as voucher_no, b.trans_date as voucher_date, a.account_number,
                        (CASE WHEN a.account_type = 'DEBIT' THEN (a.receipt) ELSE 0 END) AS local_debit,
                        (CASE WHEN a.account_type = 'CREDIT' THEN (a.receipt) ELSE 0 END) AS local_credit
                        FROM ar_receipts a
                        LEFT JOIN journal_postings b ON a.receipt_no = b.document_no
                        JOIN sales_invoices c ON a.sales_invoice = c.number
                        WHERE a.sales_invoice = '$document_no'
                        GROUP BY a.receipt_no")->result_array();
                }

                if ($sales_invoice['status'] == '0') {
                    $status_purchase = "OPEN";
                    $style_status_purchase = "background-color:#C8FFCC;";
                } else if ($sales_invoice['status'] == '1') {
                    $status_purchase = "CLOSE";
                    $style_status_purchase = "background-color:#FFC8C8;";
                } else {
                    $status_purchase = "-";
                    $style_status_purchase = "";
                }
                
                if($sales_invoice['cl_no'] == ""){
                    $balance_local = ($sales_invoice['local_debit'] - $sales_invoice['local_credit']);
                }else{
                    $balance_local = 0;
                }

                $accumulated += ($sales_invoice['local_debit'] - $sales_invoice['local_credit']);
                $detail .= '  <tr>
                                    <td>' . $no . '</td>
                                    <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                    <td>' . $customer_id . '</td>
                                    <td>' . $customer_name . '</td>
                                    <td>' . $plant . '</td>
                                    <td>' . $sales_invoice['source'] . '</td>
                                    <td>' . $sales_invoice['invoice_date'] . '</td>
                                    <td>' . $sales_invoice['payment_due'] . '</td>
                                    <td>' . $sales_invoice['document_no'] . '</td>
                                    <td>' . $sales_invoice['invoice_no'] . '</td>
                                    <td>' . $sales_invoice['voucher_date'] . '</td>
                                    <td>' . $sales_invoice['voucher_no'] . '</td>
                                    <td>' . $sales_invoice['cl_no'] . '</td>
                                    <td>' . $sales_invoice['cl_date'] . '</td>
                                    <td>' . $sales_invoice['account_number'] . '</td>
                                    <td>INR</td>
                                    <td style="text-align:right;">' . number_format($sales_invoice['local_debit'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($sales_invoice['local_credit'], 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>';

                if ($filter_display == "Invoice") {
                    $payment_total = 0;
                    foreach ($payments as $payment) {
                        $balance_local += ($payment['local_debit'] - $payment['local_credit']);
                        $accumulated += ($payment['local_debit'] - $payment['local_credit']);
                        $payment_total += $payment['local_credit'];
                        $local_debit += $payment['local_debit'];
                        $local_credit += $payment['local_credit'];
                    }

                    if($payment_total == 0){
                        $payment_total = $sales_invoice['local_credit'];
                    }

                    if($sales_invoice['cl_no'] == ""){
                        $balance_invoice = ($sales_invoice['local_debit'] - $payment_total);
                    }else{
                        $balance_invoice = 0;
                    }

                    $invoice .= '  <tr>
                                    <td>' . $no . '</td>
                                    <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                    <td>' . $customer_id . '</td>
                                    <td>' . $customer_name . '</td>
                                    <td>' . $plant . '</td>
                                    <td>' . $sales_invoice['source'] . '</td>
                                    <td>' . $sales_invoice['invoice_date'] . '</td>
                                    <td>' . $sales_invoice['payment_due'] . '</td>
                                    <td>' . $sales_invoice['document_no'] . '</td>
                                    <td>' . $sales_invoice['invoice_no'] . '</td>
                                    <td>' . $sales_invoice['voucher_date'] . '</td>
                                    <td>' . $sales_invoice['voucher_no'] . '</td>
                                    <td>' . $sales_invoice['cl_no'] . '</td>
                                    <td>' . $sales_invoice['cl_date'] . '</td>
                                    <td>' . $sales_invoice['account_number'] . '</td>
                                    <td>INR</td>
                                    <td style="text-align:right;">' . number_format($sales_invoice['local_debit'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($payment_total, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_invoice) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>';
                }

                $no++;
                $local_debit += $sales_invoice['local_debit'];
                $local_credit += $sales_invoice['local_credit'];
            }

            $balance_local = ($begin_balance_local + $local_debit - $local_credit);

            if (count($sales_invoices) > 0) {
                $detail .= '  <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>SUB TOTAL</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:right;">' . number_format($local_debit, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($local_credit, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>';

                $invoice .= '  <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>SUB TOTAL</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:right;">' . number_format($local_debit, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($local_credit, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>';
            }

            $summary .= '   <tr>
                                <td>' . $noid . '</td>
                                <td>' . $customer['id'] . '</td>
                                <td>' . $customer['name'] . '</td>
                                <td>' . $customer['plant'] . '</td>
                                <td style="text-align:center;">' . $customer['currency'] . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                            </tr>';
            $noid++;

            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_local_balance += ($begin_balance_local + $grand_local_debit - $grand_local_credit);
            $grand_summary_total += $balance_local;
        }

        $detail .= '    <tr style="background: #C3FFB4; font-weight:bold;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>GRAND TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right;">' . number_format($grand_local_debit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($grand_local_credit, 2) . '</td>
                            <td style="text-align:right;">' .$this->formatNo($grand_local_balance) . '</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_summary_total) . '</td>
                        </tr>';

        $invoice .= '   <tr style="background: #C3FFB4; font-weight:bold;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>GRAND TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right;">' . number_format($grand_local_debit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($grand_local_credit, 2) . '</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_local_balance) . '</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_summary_total) . '</td>
                        </tr>';

        $summary .= '   <tr style="font-weight:bold;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>GRAND TOTAL</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_summary_total) . '</td>
                        </tr>';

        $htmlend = '</table></body></html>';

        if ($filter_display == "Summary") {
            echo $html . $summary . $htmlend;
        }elseif ($filter_display == "Invoice") {
            echo $html . $invoice . $htmlend;
        } else {
            echo $html . $detail . $htmlend;
        }
    }
}
