<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_general_ledgers extends CI_Controller
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
            $this->load->view('finance/report_general_ledgers');
        } else {
            redirect('error_access');
        }
    }

    function linked($number, $amount){
        return '<a onclick="window.open(\'' . base_url('finance/journal_postings/print_voucher_tb/' . base64_encode($number)) . '\', \'_blank\', \'location=yes,height=650,width=1500,scrollbars=yes,status=yes\');" style="color: inherit;text-decoration: none;cursor: pointer;">' . number_format($amount, 2) . '</a>';
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_general_ledgers_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_status = base64_decode($this->input->get("filter_status"));
        $filter_account_category = base64_decode($this->input->get("filter_account_category"));
        $filter_account = base64_decode($this->input->get("filter_account"));

        $period = date("Ym", strtotime($filter_from));

        if ($filter_account == "") {
            $filter_account_explode = explode(",", $filter_account_category);
        } else {
            $filter_account_explode = explode(",", $filter_account);
        }

        if($filter_from != "" && $filter_to != ""){
            $where_period = "and a.journal_date between '$filter_from' and '$filter_to'";
            $where_period2 = "and journal_date between '$filter_from' and '$filter_to'";
        }else{
            $where_period = "";
            $where_period2 = "";
        }

        $filter_before = date("2023-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';

        for ($i = 0; $i < count($filter_account_explode); $i++) {
            $filter_account = $filter_account_explode[$i];

            $query_1 = $this->db->query("SELECT 
                a.journal_date, 
                a.number, 
                (CASE WHEN a.modul = 'ADJUSTMENT' THEN a.number ELSE a.document_no END) as document_no,  
                (CASE WHEN b.asset_code is not null THEN b.asset_code ELSE f.number END) as asset_code,
                (CASE WHEN b.invoice_no is not null THEN b.invoice_no WHEN c.customer_po is not null THEN c.customer_po WHEN d.supplier_invoice is not null then d.supplier_invoice WHEN e.sales_invoice is not null THEN e.sales_invoice ELSE a.invoice_no END) as invoice_no,
                (CASE WHEN b.invoice_date is not null THEN b.invoice_date WHEN c.trans_date is not null THEN c.trans_date WHEN d.payment_date is not null THEN d.payment_date WHEN e.receipt_date is not null THEN e.receipt_date ELSE a.trans_date END) as invoice_date, 
                a.company_name, 
                a.account_number, 
                a.account_name, 
                a.modul, 
                a.description, 
                a.currency, 
                a.local_debit, 
                a.local_credit
            FROM journal_postings a
            LEFT JOIN purchase_invoices b ON (a.document_no = b.number and (trim(b.item_no) = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.description, '|', 4), '|', -1)) or trim(b.item_no) = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.description, '|', 3), '|', -1))))
            LEFT JOIN sales_invoices c ON a.document_no = c.number
            LEFT JOIN ap_payments d ON a.document_no = d.payment_no
            LEFT JOIN ar_receipts e ON a.document_no = e.receipt_no
            LEFT JOIN asset_fixeds f ON (a.number = f.voucher_no or a.number = f.purchase_invoice_no) and a.company_id = f.number
            WHERE a.account_number = '$filter_account' and a.modul != 'BANK STATEMENT' $where_period
            GROUP BY a.id");
            $data_1 = $query_1->result_array();

            $query_2 = $this->db->query("SELECT 
                journal_date, 
                `number`, 
                document_no,
                '-' as asset_code,
                invoice_no, 
                trans_date as invoice_date, 
                company_name, 
                account_number,
                account_name,
                modul, 
                `description`, 
                currency, 
                (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) as local_debit,
                (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) as local_credit
            FROM journal_postings
            WHERE account_number = '$filter_account' and modul = 'BANK STATEMENT' $where_period2
            GROUP BY `number`");
            $data_2 = $query_2->result_array();

            $journals = array_merge($data_1, $data_2);

            $this->db->select('*');
            $this->db->from('account_coa');
            $this->db->where("account_number", $filter_account);
            $account_coa = $this->db->get()->row();

            if(@$account_coa->account_number != ""){
                $html .= '
                        <center>
                            <div style="font-size: 12px; text-align: left;">
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
                        <center>
                            <h3 style="margin:0;">GENERAL LEDGER</h3>
                            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                        </center>
                        <table style="width:30%; font-size:12px;">
                            <tr>
                                <td width="100">Account No</td>
                                <td>:</td>
                                <td style="mso-number-format:\@;">' . @$account_coa->account_number . '</td>
                            </tr>
                            <tr>
                                <td>Account Name</td>
                                <td>:</td>
                                <td>' . @$account_coa->account_name . '</td>
                            </tr>
                        </table>
                        
                        <table id="customers" border="1">
                            <tr>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2">Status</th>
                                <th rowspan="2">Posting Date</th>
                                <th rowspan="2">Posting No</th>
                                <th rowspan="2">Document No</th>
                                <th rowspan="2">Invoice No</th>
                                <th rowspan="2">Invoice Date</th>
                                <th rowspan="2">Company Name</th>
                                <th rowspan="2">Account No</th>
                                <th rowspan="2">Account Name</th>
                                <th rowspan="2">Description</th>
                                <th rowspan="2">Asset Code</th>
                                <th rowspan="2">Currency</th>
                                <th colspan="2">BANK STATEMENT</th>
                                <th colspan="3">LOCAL CURRENCY</th>
                            </tr>
                            <tr>
                                <th>Document No</th>
                                <th>Date</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                            </tr>';

                $this->db->select('b.account_number, b.account_name, (COALESCE(a.original_debit, 0) + b.original_debit) as begin_debit, (COALESCE(a.original_credit, 0) + b.original_kredit) as begin_credit');
                $this->db->from('account_coa b');
                $this->db->join("(SELECT account_number, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit FROM journal_postings WHERE journal_date < '$filter_from' GROUP BY account_number) a", "a.account_number = b.account_number", 'left');
                $this->db->where("b.account_number", $filter_account);
                $this->db->group_by('b.account_number');
                $begin_ori_tb = $this->db->get()->row();

                $this->db->select('account_number, account_name, header,
                        SUM(begin_debit) as begin_debit, 
                        SUM(begin_credit) as begin_credit');
                $this->db->from('trial_balances');
                $this->db->where('period =', $period);
                $this->db->where('account_number', $filter_account);
                $this->db->order_by('id', 'asc');
                $this->db->group_by('account_number', 'asc');
                $begin_tb = $this->db->get()->row();

                $no = 1;
                $ori_balance = abs(@$begin_ori_tb->begin_debit - @$begin_ori_tb->begin_credit);
                $ori_debit = 0;
                $ori_credit = 0;
                $local_balance = (@$begin_tb->begin_debit - @$begin_tb->begin_credit);
                $local_debit = 0;
                $local_credit = 0;

                if($local_balance > 0){
                    $hstyle = "color:green;";
                }else{
                    $hstyle = "color:red;";
                }

                $html .= '  <tr>
                                <td style="text-align:center">-</td>
                                <td></td>
                                <td>' . $filter_from . '</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>BALANCE</td>
                                <td></td>
                                <td style="text-align:center;">INR</td>
                                <td></td>
                                <td></td>
                                <td style="text-align:right;font-weight:bold;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;font-weight:bold;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;font-weight:bold;'.$hstyle.'">' . number_format(abs($local_balance), 2) . '</td>
                            </tr>';

                foreach ($journals as $journal) {
                    $document_no = $journal['document_no'];

                    $this->db->select('number, trans_date');
                    $this->db->from('journal_postings');
                    $this->db->where('document_no', $document_no);
                    $this->db->where('modul', "BANK STATEMENT");
                    $bank_statement = $this->db->get()->row();

                    if($journal['modul'] == "ADJUSTMENT"){
                        $this->db->select("a.company_name");
                        $this->db->from('journal_postings a');
                        $this->db->join('suppliers b', 'a.company_id = b.id');
                        $this->db->where('a.number', $journal['number']);
                        $j = $this->db->get()->row();
                        $company_name = @$j->company_name;

                        if(empty($j)){
                            $this->db->select("a.company_name");
                            $this->db->from('journal_postings a');
                            $this->db->join('customers b', 'a.company_id = b.id');
                            $this->db->where('a.number', $journal['number']);
                            $j = $this->db->get()->row();
                            $company_name = @$j->company_name;

                            if(empty($j)){
                                $company_name = $journal['company_name'];
                            }
                        }
                    }else{
                        $company_name = $journal['company_name'];
                    }

                    $explode = explode("|", $journal['description']);
                    if($journal['modul'] == "PURCHASE INVOICING"){
                        if(@$explode[4] == ""){
                            $desciption = @$explode[3];
                        }else{
                            $desciption = @$explode[4];
                        }
                    }elseif($journal['modul'] == "AP PAYMENT"){
                        $desciption = @$explode[3];
                    }elseif($journal['modul'] == "SALES INVOICING"){
                        $desciption = @$explode[3] ." | ". @$explode[4];
                    }elseif($journal['modul'] == "AR RECEIPT"){
                        $desciption = @$explode[3];
                    }else{
                        $desciption = $journal['description'];
                    }

                    if(@$bank_statement->number == ""){
                        $status = "OPEN";
                        $style = "background-color:#C8FFCC;";
                    }else{
                        $status = "CLOSE";
                        $style = "background-color:#FFC8C8;";
                    }

                    if($filter_status == "" || $filter_status == $status){
                        $local_balance += ($journal['local_debit'] - $journal['local_credit']);

                        if ($local_balance > 0) {
                            $local_style = "color:green;";
                        } else {
                            $local_style = "color:red;";
                        }

                        $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td style="text-align:center;' . $style . '">' . $status . '</td>
                                            <td>' . $journal['journal_date'] . '</td>
                                            <td>' . $journal['number'] . '</td>
                                            <td>' . $journal['document_no'] . '</td>
                                            <td>' . $journal['invoice_no'] . '</td>
                                            <td>' . $journal['invoice_date'] . '</td>
                                            <td>' . $company_name . '</td>
                                            <td style="mso-number-format:\@;">' . $journal['account_number'] . '</td>
                                            <td>' . $journal['account_name'] . '</td>
                                            <td>' . $desciption . '</td>
                                            <td>' . $journal['asset_code'] . '</td>
                                            <td style="text-align:center;">INR</td>
                                            <td>' . @$bank_statement->number . '</td>
                                            <td>' . @$bank_statement->trans_date . '</td>
                                            <td style="text-align:right;font-weight:bold;color:green;">' . $this->linked($journal['number'], $journal['local_debit']) . '</td>
                                            <td style="text-align:right;font-weight:bold;color:red;">' . $this->linked($journal['number'], $journal['local_credit']) . '</td>
                                            <td style="text-align:right;font-weight:bold;' . $local_style . '">' . $this->linked($journal['number'], $local_balance) . '</td>
                                        </tr>';

                        $local_debit += $journal['local_debit'];
                        $local_credit += $journal['local_credit'];
                        $no++;
                    }
                }

                $html .= '  <tr style="background:#DEDEDE;">
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:left;"><b>ENDING BALANCE</b></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <td></td>
                                    <td></td>
                                    <th style="text-align:right;">-</th>
                                    <th style="text-align:right;">-</th>
                                    <th style="text-align:right;">' . number_format(abs($local_balance), 2) . '</th>
                                </tr>';

                $html .= '  <tr style="background:#DEDEDE;">
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:left;"><b>GRAND TOTAL</b></th>
                                    <th style="text-align:right;"></th>
                                    <th style="text-align:right;"></th>
                                    <td></td>
                                    <td></td>
                                    <th style="text-align:right;">' . number_format(abs($local_debit), 2) . '</th>
                                    <th style="text-align:right;">' . number_format(abs($local_credit), 2) . '</th>
                                    <th style="text-align:right;"></th>
                                </tr>';

                $html .= '</table><br><br><div style="page-break-after:always;"></div>';
            }
        }

        echo $html . "</body></html>";
    }
}
