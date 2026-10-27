<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_bank_statements extends CI_Controller
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
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('finance/report_bank_statements');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bank_statements_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_account = base64_decode($this->input->get("filter_account"));
        $filter_currency = base64_decode($this->input->get("filter_currency"));

        if($filter_from == "" && $filter_to == ""){
            $filter_from = "2024-11-01";
            $filter_to = date("Y-m-d");
        }
        
        $filter_from_1 = date("Y-m-d", strtotime("-1 day", strtotime($filter_from)));
        $time_start = new DateTime($filter_from);
        $time_to = new DateTime($filter_to);

        $account_coa = $this->crud->read("account_coa", [], ["account_number" => $filter_account]);

        $ar_receipt_begin = $this->crud->query("SELECT z.account_number, 
                    (SUM(z.local_debit) - SUM(z.local_credit)) as local_begin, 
                    (SUM(z.debit) - SUM(z.credit)) as original_begin 
            FROM (SELECT a.*, b.currency
            FROM ar_receipt_journals a 
            JOIN ar_receipts b ON a.receipt_no = b.receipt_no 
            WHERE b.receipt_date < '$filter_from' and a.account_number = '$filter_account' 
            GROUP BY a.receipt_no, a.account_number, a.description) z GROUP BY z.account_number");

        $ap_payment_begin = $this->crud->query("SELECT a.account_number, (SUM(a.local_debit) - SUM(a.local_credit)) as local_begin, (SUM(a.debit) - SUM(a.credit)) as original_begin
            FROM (
            SELECT a.*
            FROM ap_payment_journals a 
            JOIN ap_payments b ON a.payment_no = b.payment_no 
            WHERE b.payment_date between '2023-01-01' and '$filter_from_1' and a.account_number = '$filter_account' GROUP BY a.account_number, b.payment_no) a
            GROUP BY a.payment_no, a.account_number, a.description");
            
        $journal_adj_begin = $this->crud->query("SELECT `number`, document_no, `description`, currency, rates,
            (COALESCE(SUM(original_debit), 0) - COALESCE(SUM(original_credit), 0)) as original_begin,
            (COALESCE(SUM(local_debit), 0) - COALESCE(SUM(local_credit), 0)) as local_begin
        FROM journal_postings
        WHERE account_number = '$filter_account' and modul = 'ADJUSTMENT' and journal_date < '$filter_from'
        GROUP BY account_number");
        
        $journal_posting_begin = $this->crud->query("SELECT `number`, document_no, `description`, currency, rates,
            (COALESCE(SUM(original_credit), 0) - COALESCE(SUM(original_debit), 0)) as original_begin,
            (COALESCE(SUM(local_credit), 0) - COALESCE(SUM(local_debit), 0)) as local_begin
        FROM journal_postings
        WHERE account_number = '$filter_account' and modul = 'BANK STATEMENT' and journal_date < '$filter_from'
        GROUP BY account_number");

        $datas = array();
        $coa_original = (@$account_coa->original_debit - @$account_coa->original_kredit);
        $coa_local = (@$account_coa->local_debit - @$account_coa->local_kredit);
        $ar_begin_local = @$ar_receipt_begin[0]->local_begin;
        $ar_begin_original = @$ar_receipt_begin[0]->original_begin;
        $ap_begin_local = @$ap_payment_begin[0]->local_begin;
        $ap_begin_original = @$ap_payment_begin[0]->original_begin;
        $journal_begin_original = @$journal_posting_begin[0]->original_begin;
        $journal_begin_local = @$journal_posting_begin[0]->local_begin;
        $adj_begin_original = @$journal_adj_begin[0]->original_begin;
        $adj_begin_local = @$journal_adj_begin[0]->local_begin;

        while ($time_start <= $time_to) {
            $trans_date = $time_start->format('Y-m-d');
            $ar_receipts = $this->crud->query("SELECT a.*, c.description as description2, b.currency, c.number as gl_no
                FROM ar_receipt_journals a 
                JOIN ar_receipts b ON a.receipt_no = b.receipt_no 
                LEFT JOIN journal_postings c ON b.receipt_no = c.document_no
                WHERE b.receipt_date = '$trans_date' and a.account_number = '$filter_account' 
                GROUP BY a.receipt_no, a.account_number, a.description");

            $ap_payments = $this->crud->query("SELECT a.*, c.description, b.currency, c.number as gl_no
                FROM ap_payment_journals a 
                JOIN ap_payments b ON a.payment_no = b.payment_no 
                LEFT JOIN journal_postings c ON b.payment_no = c.document_no
                WHERE b.payment_date = '$trans_date' and a.account_number = '$filter_account' 
                GROUP BY a.payment_no, a.account_number");

            $journal_adjustments = $this->crud->query("SELECT `number`, document_no, `description`, currency, rates,
                    (CASE WHEN SUM(original_credit) > 0 THEN SUM(original_credit) ELSE 0 END) as original_credit,
                    (CASE WHEN SUM(original_debit) > 0 THEN SUM(original_debit) ELSE 0 END) as original_debit,
                    (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) as local_credit,
                    (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) as local_debit
                FROM journal_postings
                WHERE account_number = '$filter_account' and modul = 'ADJUSTMENT' and journal_date = '$trans_date'
                GROUP BY `number`");
                
            $journal_postings = $this->crud->query("SELECT `number`, document_no, `description`, currency, rates,
                    (CASE WHEN SUM(original_credit) > 0 THEN SUM(original_credit) ELSE 0 END) as original_debit,
                    (CASE WHEN SUM(original_debit) > 0 THEN SUM(original_debit) ELSE 0 END) as original_credit,
                    (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) as local_debit,
                    (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) as local_credit
                FROM journal_postings
                WHERE account_number = '$filter_account' and modul = 'BANK STATEMENT' and journal_date = '$trans_date'
                GROUP BY `number`");
            
            foreach ($ar_receipts as $ar_receipt) {
                $ar_original_balance = ($coa_original + $ar_begin_original + $ap_begin_original + $journal_begin_original + $adj_begin_original + $ar_receipt->debit - $ar_receipt->credit);
                $ar_local_balance = ($coa_local + $ar_begin_local + $ap_begin_local + $journal_begin_local + $adj_begin_local + $ar_receipt->local_debit - $ar_receipt->local_credit);

                $datas[] = array(
                    "trans_date" => $trans_date,
                    "document_no" => $ar_receipt->receipt_no,
                    "gl_no" => $ar_receipt->gl_no,
                    "description" => $ar_receipt->description . " | " . $ar_receipt->description2,
                    "currency" => $ar_receipt->currency,
                    "original_debit" => $ar_receipt->debit,
                    "original_credit" => $ar_receipt->credit,
                    "original_balance" => $ar_original_balance,
                    "rate" => (($ar_receipt->local_debit + $ar_receipt->local_credit) / ($ar_receipt->debit + $ar_receipt->credit)),
                    "local_debit" => $ar_receipt->local_debit,
                    "local_credit" => $ar_receipt->local_credit,
                    "local_balance" => $ar_local_balance,
                );

                $coa_original = 0;
                $coa_local = 0;
                $ap_begin_original = 0;
                $ap_begin_local = 0;
                $journal_begin_local = 0;
                $ar_begin_original = $ar_original_balance;
                $ar_begin_local = $ar_local_balance;
            }

            foreach ($ap_payments as $ap_payment) {
                $ap_original_balance = ($coa_original + $ar_begin_original + $ap_begin_original + $ap_payment->debit - $ap_payment->credit);
                $ap_local_balance = ($coa_local + $ar_begin_local + $ap_begin_local + $journal_begin_local + $adj_begin_local + $ap_payment->local_debit - $ap_payment->local_credit);

                $datas[] = array(
                    "trans_date" => $trans_date,
                    "document_no" => $ap_payment->payment_no,
                    "gl_no" => $ap_payment->gl_no,
                    "description" => $ap_payment->description,
                    "currency" => $ap_payment->currency,
                    "original_debit" => $ap_payment->debit,
                    "original_credit" => $ap_payment->credit,
                    "original_balance" => $ap_original_balance,
                    "rate" => (($ap_payment->local_debit + $ap_payment->local_credit) / ($ap_payment->debit + $ap_payment->credit)),
                    "local_debit" => $ap_payment->local_debit,
                    "local_credit" => $ap_payment->local_credit,
                    "local_balance" => $ap_local_balance,
                );

                $coa_original = 0;
                $coa_local = 0;
                $journal_begin_local = 0;
                $ap_begin_original = $ap_original_balance;
                $ap_begin_local = $ap_local_balance;
                $ar_begin_original = 0;
                $ar_begin_local = 0;
            }

            foreach ($journal_adjustments as $journal_adjustment) {
                $ap_original_balance = ($coa_original + $ar_begin_original + $ap_begin_original + $journal_adjustment->original_debit - $journal_adjustment->original_credit);
                $ap_local_balance = ($coa_local + $ar_begin_local + $ap_begin_local + $journal_begin_local + $adj_begin_local + $journal_adjustment->local_debit - $journal_adjustment->local_credit);

                $datas[] = array(
                    "trans_date" => $trans_date,
                    "document_no" => $journal_adjustment->document_no,
                    "gl_no" => $journal_adjustment->number,
                    "description" => $journal_adjustment->description,
                    "currency" => $journal_adjustment->currency,
                    "original_debit" => $journal_adjustment->original_debit,
                    "original_credit" => $journal_adjustment->original_credit,
                    "original_balance" => $ap_original_balance,
                    "rate" => 1,
                    "local_debit" => $journal_adjustment->local_debit,
                    "local_credit" => $journal_adjustment->local_credit,
                    "local_balance" => $ap_local_balance,
                );

                $coa_original = 0;
                $coa_local = 0;
                $ap_begin_original = $ap_original_balance;
                $ap_begin_local = $ap_local_balance;
                $ar_begin_original = 0;
                $ar_begin_local = 0;
            }

            foreach ($journal_postings as $journal_posting) {
                $ap_original_balance = ($coa_original + $ar_begin_original + $ap_begin_original + $journal_posting->original_debit - $journal_posting->original_credit);
                $ap_local_balance = ($coa_local + $ar_begin_local + $ap_begin_local + $journal_begin_local + $adj_begin_local + $journal_posting->local_debit - $journal_posting->local_credit);

                $datas[] = array(
                    "trans_date" => $trans_date,
                    "document_no" => $journal_posting->document_no,
                    "gl_no" => $journal_posting->number,
                    "description" => $journal_posting->description,
                    "currency" => $journal_posting->currency,
                    "original_debit" => $journal_posting->original_debit,
                    "original_credit" => $journal_posting->original_credit,
                    "original_balance" => $ap_original_balance,
                    "rate" => 1,
                    "local_debit" => $journal_posting->local_debit,
                    "local_credit" => $journal_posting->local_credit,
                    "local_balance" => $ap_local_balance,
                );

                $coa_original = 0;
                $coa_local = 0;
                $ap_begin_original = $ap_original_balance;
                $ap_begin_local = $ap_local_balance;
                $ar_begin_original = 0;
                $ar_begin_local = 0;
            }

            $ar_begin_original = $ar_begin_original;
            $ar_begin_local = $ar_begin_local;
            $ap_begin_original = $ap_begin_original;
            $ap_begin_local = $ap_begin_local;

            $time_start->modify('+1 day');
        }

        $account = $this->crud->read('account_coa', [], ["account_number" => $filter_account]);

        $coa_original = (@$account_coa->original_debit - @$account_coa->original_kredit);
        $coa_local = (@$account_coa->local_debit - @$account_coa->local_kredit);
        $ar_begin_local = @$ar_receipt_begin[0]->local_begin;
        $ar_begin_original = @$ar_receipt_begin[0]->original_begin;
        $ap_begin_local = @$ap_payment_begin[0]->local_begin;
        $ap_begin_original = @$ap_payment_begin[0]->original_begin;
        $journal_begin_original = @$journal_posting_begin[0]->original_begin;
        $journal_begin_local = @$journal_posting_begin[0]->local_begin;
        $adj_begin_original = @$journal_adj_begin[0]->original_begin;
        $adj_begin_local = @$journal_adj_begin[0]->local_begin;

        $opening_balance_original = ($coa_original + $ar_begin_original + $ap_begin_original + $journal_begin_original + $adj_begin_original);
        $opening_balance_local = ($coa_local + $ar_begin_local + $ap_begin_local + $journal_begin_local + $adj_begin_local);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
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
                <h3 style="margin:0;">BANK STATEMENT</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <table style="width: 50%; font-size:12px;">
                <tr>
                    <td width="150">Account No</td>
                    <td width="5">:</td>
                    <td>'.$account->account_number.'</td>
                </tr>
                <tr>
                    <td>Account Name</td>
                    <td>:</td>
                    <td>'.$account->account_name.'</td>
                </tr>
            </table>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Trans Date</th>
                <th rowspan="2">Document No</th>
                <th rowspan="2">GL No</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Currency</th>
                <th colspan="4">Local Currency</th>
            </tr>
            <tr>
                <th>Rate</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>';

            $html .= '  <tr>
                            <td style="text-align:center">#</td>
                            <td>' . $filter_from . '</td>
                            <td></td>
                            <td></td>
                            <td>OPENING BALANCE</td>
                            <td></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($opening_balance_local, 2) . '</td>
                        </tr>';

        $no = 1;
        $grand_total_debit_original = 0;
        $grand_total_credit_original = 0;
        $grand_total_debit_local = 0;
        $grand_total_credit_local = 0;
        $local_balance = $opening_balance_local;
        foreach ($datas as $data) {
            $local_balance += ($data['local_debit'] - $data['local_credit']);
            $html .= '  <tr>
                            <td style="text-align:center">'.$no.'</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['document_no'] . '</td>
                            <td>' . $data['gl_no'] . '</td>
                            <td>' . $data['description'] . '</td>
                            <td>INR</td>
                            <td style="text-align:right;font-weight:bold;">1.00</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['local_debit'], 2) . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['local_credit'], 2) . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($local_balance, 2) . '</td>
                        </tr>';
            $no++;
            $grand_total_debit_original += $data['original_debit'];
            $grand_total_credit_original += $data['original_credit'];
            $grand_total_debit_local += $data['local_debit'];
            $grand_total_credit_local += $data['local_credit'];
        }

        $html .= '  <tr style="background:#EBEBEB;">
                        <td></td>
                        <td><b>GRAND TOTAL</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_debit_local, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_credit_local, 2) . '</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                    </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }
}
