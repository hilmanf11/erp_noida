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
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

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

        // $account_coa = $this->crud->read("account_coa", [], ["account_number" => $filter_account]);

        $account = $this->crud->read('account_coa', [], ["account_number" => $filter_account]);

        $query_begin = $this->db->query("SELECT (SUM(local_debit) - SUM(local_credit)) AS local_begin, (SUM(original_debit) - SUM(original_credit)) AS original_begin FROM (
                SELECT original_debit, original_kredit AS original_credit, local_debit, local_kredit AS local_credit
                FROM account_coa WHERE account_number = '$filter_account'
            UNION ALL
            SELECT 
                SUM(a.debit) AS original_debit, SUM(a.credit) AS original_credit, SUM(a.local_debit) AS local_debit, SUM(a.local_credit) AS local_credit
                FROM ar_receipt_journals a 
                JOIN (select receipt_date, receipt_no, currency from ar_receipts GROUP BY receipt_no) b ON a.receipt_no = b.receipt_no 
                WHERE b.receipt_date < '$filter_from' AND a.account_number = '$filter_account' 
                GROUP BY a.receipt_no, a.account_number
            UNION ALL
                SELECT SUM(a.debit) AS original_debit, SUM(a.credit) AS original_credit, SUM(a.local_debit) AS local_debit, SUM(a.local_credit) AS local_credit
                FROM ap_payment_journals a 
                JOIN (select payment_date, payment_no, currency from ap_payments GROUP BY payment_no) b ON a.payment_no = b.payment_no 
                WHERE b.payment_date < '$filter_from' AND a.account_number = '$filter_account' 
                GROUP BY a.payment_no, a.account_number
            UNION ALL
                SELECT 
                (CASE WHEN SUM(original_debit) > 0 THEN SUM(original_debit) ELSE 0 END) AS original_debit,
                (CASE WHEN SUM(original_credit) > 0 THEN SUM(original_credit) ELSE 0 END) AS original_credit,
                (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) AS local_debit,
                (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) AS local_credit
                FROM journal_postings
                WHERE account_number = '$filter_account' AND modul = 'ADJUSTMENT' AND journal_date < '$filter_from'
                GROUP BY `number`
            UNION ALL
                SELECT
                (CASE WHEN SUM(original_credit) > 0 THEN SUM(original_credit) ELSE 0 END) AS original_debit,
                (CASE WHEN SUM(original_debit) > 0 THEN SUM(original_debit) ELSE 0 END) AS original_credit,
                (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) AS local_debit,
                (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) AS local_credit
                FROM journal_postings
                WHERE account_number = '$filter_account' AND modul = 'BANK STATEMENT' AND journal_date < '$filter_from'
                GROUP BY `number`
        ) z
        ");
        $data_begin = $query_begin->row_array();

        $query_posting = $this->db->query("SELECT number, document_no, description FROM journal_postings WHERE modul IN ('AP PAYMENT', 'AR RECEIPT') GROUP BY number, document_no");
        $data_postings = $query_posting->result_array();
        $arrPosting = [];
        foreach ($data_postings as $data_posting) {
            $arrPosting['number'][$data_posting['document_no']] = $data_posting['number'];
            $arrPosting['description'][$data_posting['document_no']] = $data_posting['description'];
        }

        $query_banks = $this->db->query("SELECT distinct number, journal_date, document_no, description FROM journal_postings WHERE modul IN ('BANK STATEMENT') and account_number = '$filter_account' and journal_date between '$filter_from' and '$filter_to'");
        $data_banks = $query_banks->result_array();
        $arrBank = [];
        $arrBank2 = [];
        $arrBankDate = [];
        $arrBankDate2 = [];
        foreach ($data_banks as $data_bank) {
            // $rawKey = str_replace(['"', "'"], '', $data_bank['description']);
            $explode = explode("|", $data_bank['description']);
            $rawKey = trim(@$explode[3]);
            
            $arrBank[$rawKey] = $data_bank['number'];
            $arrBankDate[$rawKey] = $data_bank['journal_date'];
            
            $arrBank2[trim($data_bank['document_no'])] = $data_bank['number'];
            $arrBankDate2[trim($data_bank['document_no'])] = $data_bank['journal_date'];
        }

        // die(json_encode($arrBank));

        $query_detail = $this->db->query("SELECT * FROM (
                SELECT 
                b.receipt_date as trans_date, b.receipt_no as document_no, 'TERPISAH' as gl_no, a.description, b.currency, SUM(a.debit) as original_debit, SUM(a.credit) as original_credit, SUM(a.local_debit) as local_debit, SUM(a.local_credit) as local_credit
                FROM ar_receipt_journals a 
                JOIN (select receipt_date, receipt_no, currency from ar_receipts GROUP BY receipt_no) b ON a.receipt_no = b.receipt_no
                WHERE b.receipt_date between '$filter_from' and '$filter_to' and a.account_number = '$filter_account' 
                GROUP BY a.receipt_no, a.account_number
            UNION ALL
                SELECT b.payment_date as trans_date, b.payment_no as document_no, 'TERPISAH' as gl_no, a.description, b.currency, SUM(a.debit) as original_debit, SUM(a.credit) as original_credit, SUM(a.local_debit) as local_debit, SUM(a.local_credit) as local_credit
                FROM ap_payment_journals a 
                JOIN (select payment_date, payment_no, currency from ap_payments GROUP BY payment_no) b ON a.payment_no = b.payment_no
                WHERE b.payment_date between '$filter_from' and '$filter_to' and a.account_number = '$filter_account' 
                GROUP BY a.payment_no, a.account_number
            UNION ALL
                SELECT journal_date as trans_date, document_no, `number` as gl_no, `description`, currency,
                (CASE WHEN SUM(original_debit) > 0 THEN SUM(original_debit) ELSE 0 END) as original_debit,
                (CASE WHEN SUM(original_credit) > 0 THEN SUM(original_credit) ELSE 0 END) as original_credit,
                (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) as local_debit,
                (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) as local_credit
                FROM journal_postings
                WHERE account_number = '$filter_account' and modul = 'ADJUSTMENT' and journal_date between '$filter_from' and '$filter_to'
                GROUP BY `number`
            UNION ALL
                SELECT journal_date as trans_date, document_no, `number` as gl_no, `description`, currency,
                (CASE WHEN SUM(original_credit) > 0 THEN SUM(original_credit) ELSE 0 END) AS original_debit,
                (CASE WHEN SUM(original_debit) > 0 THEN SUM(original_debit) ELSE 0 END) AS original_credit,
                (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) AS local_debit,
                (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) AS local_credit
                FROM journal_postings
                WHERE account_number = '$filter_account' and modul = 'BANK STATEMENT' and journal_date between '$filter_from' and '$filter_to'
                GROUP BY journal_date, `number`) z
            ORDER BY trans_date asc
        ");
        $data_details = $query_detail->result_array();

        $datas = [];
        foreach ($data_details as $data_detail) {
            $original_balance = ($data_detail['original_debit'] - $data_detail['original_credit']);
            $local_balance = ($data_detail['local_debit'] - $data_detail['local_credit']);

            if($data_detail['gl_no'] == "TERPISAH"){
                $gl_no = @$arrPosting['number'][$data_detail['document_no']];
                $description = @$arrPosting['description'][$data_detail['document_no']];
            }else{
                $gl_no = $data_detail['gl_no'];
                $description = $data_detail['description'];
            }

            if(empty(@$arrBank[trim($gl_no)])){
                $pb_no = @$arrBank2[trim($data_detail['document_no'])];
                $pb_date = @$arrBankDate2[trim($data_detail['document_no'])];
            }else{
                $pb_no = @$arrBank[trim($gl_no)];
                $pb_date = @$arrBankDate[trim($gl_no)];
            }

            if($pb_no == ""){
                $pb_status = "OPEN";
            }else{
                $pb_status = "CLOSE";
            }

            $datas[] = array(
                "trans_date" => $data_detail['trans_date'],
                "document_no" => $data_detail['document_no'],
                "gl_no" => $gl_no,
                "description" => $description,
                "pb_no" => $pb_no,
                "pb_date" => $pb_date,
                "pb_status" => $pb_status,
                "currency" => $data_detail['currency'],
                "original_debit" => $data_detail['original_debit'],
                "original_credit" => $data_detail['original_credit'],
                "original_balance" => $original_balance,
                // "rate" => (($data_detail['local_debit'] + $data_detail['local_credit']) / ($data_detail['original_debit'] + $data_detail['original_credit'])),
                "local_debit" => $data_detail['local_debit'],
                "local_credit" => $data_detail['local_credit'],
                "local_balance" => $local_balance,
            );
        }

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
                <th rowspan="2">PB No</th>
                <th rowspan="2">PB Date</th>
                <th rowspan="2">PB Status</th>
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
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>OPENING BALANCE</td>
                            <td></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data_begin['local_begin'], 2) . '</td>
                        </tr>';

        $no = 1;
        $grand_total_debit_original = 0;
        $grand_total_credit_original = 0;
        $grand_total_debit_local = 0;
        $grand_total_credit_local = 0;
        $local_balance = $data_begin['local_begin'];
        foreach ($datas as $data) {
            if($data['pb_status'] == "OPEN"){
                $pb_status = "<b style='color:green'>".$data['pb_status']."</b>";
            }else{
                $pb_status = "<b style='color:red'>".$data['pb_status']."</b>";
            }

            $local_balance += ($data['local_debit'] - $data['local_credit']);
            $html .= '  <tr>
                            <td style="text-align:center">'.$no.'</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['document_no'] . '</td>
                            <td>' . $data['gl_no'] . '</td>
                            <td>' . $data['pb_no'] . '</td>
                            <td>' . $data['pb_date'] . '</td>
                            <td>' . $pb_status . '</td>
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
