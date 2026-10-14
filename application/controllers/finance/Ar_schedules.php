<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Ar_schedules extends CI_Controller
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
            $this->load->view('finance/ar_schedules');
        } else {
            redirect('error_access');
        }
    }

    function readExchangeRate($currency, $receipt_date)
    {
        $search_date = date("d", strtotime($receipt_date));
        if($search_date == "31"){
          $receipt_date = date("Y-m-d", strtotime('-10 days', strtotime($receipt_date)));
        }
        
        $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($receipt_date)));
        $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "INR"]);

        if ($exchange) {
            $amount = $exchange->middle;
        } else {
            $amount = 1;
        }

        return $amount;
    }

    public function begin_balance($customer_id, $plant, $filter_from, $filter_to, $filter_currency, $filter_status = "", $balance_local){
        if($filter_currency == "INR" || $filter_currency == ""){
            $filter_currency = "";
            $currency = "INR";
        }else{
            $filter_currency = $filter_currency;
            $currency = $filter_currency;
        }

        $query = $this->db->query("SELECT number, trans_date, currency, document_no, account_number,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_debit) ELSE SUM(original_debit) END) as local_debit,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_credit) ELSE SUM(original_credit) END) as local_credit
            FROM journal_postings
            WHERE company_id = '$customer_id' and company_name like '%$plant%' and modul NOT IN ('CLEARING','BANK STATEMENT') and journal_date < '$filter_from'
            AND account_number IN ('27000001')
            GROUP BY number, document_no, account_number");
        $sales_invoices = $query->result_array();

        $local_debit = 0;
        $local_credit = 0;
        foreach ($sales_invoices as $sales_invoice) {
            if($currency != "INR"){
                $ex_local_debit = $this->exchangeRates($sales_invoice['trans_date'], $sales_invoice['currency'], $currency, $sales_invoice['local_debit']);
                $ex_local_credit = $this->exchangeRates($sales_invoice['trans_date'], $sales_invoice['currency'], $currency, $sales_invoice['local_credit']);
            }else{
                $ex_local_debit = $sales_invoice['local_debit'];
                $ex_local_credit = $sales_invoice['local_credit'];
            }

            $local_debit += $ex_local_debit;
            $local_credit += $ex_local_credit;
        }

        // die($balance_local ." + ". $local_credit . " - " . $local_debit);
        $begin_balance = ($balance_local + $local_credit - $local_debit);
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
            header("Content-Disposition: attachment; filename=ap_aging_schedules_$format.xls");
        }
        
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_customer = base64_decode($this->input->get("filter_customers"));
        $filter_criteria = base64_decode($this->input->get("filter_criteria"));
        $criteria = explode(",", $filter_criteria);
        $period = date("Ym", strtotime($filter_to));

        $start  = new DateTime($filter_from);
        $end = new DateTime($filter_to);
        $diff  = $start->diff($end);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.plant, b.account_number, c.account_name, b.balance_local");
        $this->db->from('customers a');
        $this->db->join('account_balance_customers b', 'a.id = b.customer_id', 'left');
        $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
        $this->db->like("a.id", $filter_customer);
        $this->db->group_by('a.id');
        $this->db->group_by('b.plant');
        $this->db->order_by('a.name', 'asc');
        $customers = $this->db->get()->result_array();

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
                                <small>REPORT ACCOUNT RECEIVABLE AGING</small><br>
                                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Customer Name</th>
                <th rowspan="2">Plant</th>
                <th rowspan="2">Currency</th>
                <th rowspan="2">Total<br>Outstanding</th>
                <th colspan="'.count($criteria).'">Amount Due</th>
                <th rowspan="2">Differences</th>
                <th rowspan="2">Grand Total</th>
            </tr>
            <tr>';
            for ($i = 0; $i < count($criteria); $i++) {
                $html .= "<th>" . strtr($criteria[$i], "_", " ") . "</th>";
            }
            $html .= '</tr>';

        $no = 1;
        $grand_total_begin = 0;
        $grand_total_outstanding = 0;
        $grand_total_current = 0;
        $grand_total_week_1 = 0;
        $grand_total_week_2 = 0;
        $grand_total_week_3 = 0;
        $grand_total_week_4 = 0;
        $grand_total_week_5 = 0;
        $grand_total_week_6 = 0;
        $grand_total_week_7 = 0;
        $grand_total_week_8 = 0;
        $grand_total_week_9 = 0;
        $grand_total_all = 0;
        $grand_diferences = 0;
        $header = "";

        foreach ($customers as $customer) {
            $customer_id = $customer['id'];
            $customer_name = $customer['name'];
            $plant = $customer['plant'];
            $company_name = "(".$plant.") ".$customer_name;
            $company_name2 = $customer_name . " | " . $plant;

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
            WHERE a.customer_id = '$customer_id' AND a.plant = '$plant' AND b.journal_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY a.number, b.number
            ORDER BY b.journal_date ASC");
            $data_1 = $query_1->result_array();

            $query_2 = $this->db->query("SELECT
                'AR' AS source,
                a.receipt_no AS invoice_no,
                a.receipt_date AS invoice_date,
                a.receipt_date AS payment_due,
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
            WHERE a.customer_id = '$customer_id' AND a.plant = '$plant' AND b.journal_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY a.receipt_no, a.account_number
            ORDER BY b.journal_date");
            $data_2 = $query_2->result_array();

            $query_3 = $this->db->query("SELECT
                'AD' AS source,
                a.invoice_no,
                a.trans_date AS invoice_date,
                a.trans_date AS payment_due,
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
            GROUP BY a.number
            ORDER BY a.journal_date ASC");
            $data_3 = $query_3->result_array();

            $query_4 = $this->db->query("SELECT
                'CL' AS source,
                a.invoice_no,
                a.trans_date as invoice_date,
                a.trans_date AS payment_due,
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

            // if($supplier['currency'] == $filter_currency || $filter_currency == ""){
                $begin_balance_local = $this->begin_balance($customer_id, $plant, $filter_from, $filter_to, "INR", "", @$customer['balance_local']);
            // }else{
            //     $begin_balance_local = $this->begin_balance($customer_id, $plant, $filter_from, $filter_to, $filter_currency, "", 0);
            // }

            $current = @$begin_balance_local;
            $week_1 = 0;
            $week_2 = 0;
            $week_3 = 0;
            $week_4 = 0;
            $week_5 = 0;
            $week_6 = 0;
            $week_7 = 0;
            $week_8 = 0;
            $week_9 = 0;
            $total_outstanding = $begin_balance_local;
            $total_diference = 0;
            $arr = [];

            foreach ($sales_invoices as $sales_invoice) {
                $document_no = $sales_invoice['document_no'];

                $cl_no = $sales_invoice['cl_no'];
                $receipt_due = $sales_invoice['payment_due'];
                $total_receipt = 0;
                // if($currency != "INR"){
                //     $ex_local_debit = $this->exchangeRates($sales_invoice['trans_date'], $sales_invoice['currency'], $currency, $sales_invoice['original_debit']);
                //     $ex_local_credit = $this->exchangeRates($sales_invoice['trans_date'], $sales_invoice['currency'], $currency, $sales_invoice['original_credit']);
                // }else{
                    $ex_local_debit = $sales_invoice['local_debit'];
                    $ex_local_credit = $sales_invoice['local_credit'];
                // }

                if($cl_no == ""){
                    $balance_local = ($sales_invoice['local_debit'] - $sales_invoice['local_credit']);
                }else{
                    $balance_local = 0;
                }
                
                if ($cl_no == "" || round($balance_local, 2) != 0) {
                    $total = $balance_local;
                } else {
                    $total = 0;
                }

                $date1 = new DateTime($receipt_due);
                $date2 = new DateTime($filter_to);
                $diviation = $date1->diff($date2);

                for ($y = 0; $y < count($criteria); $y++) {
                    if($criteria[$y] == "1-30 Days"){
                        if ($diviation->days >= 1 && $diviation->days <= 30) {
                            $week_1 += $total;
                        }
                    }elseif($criteria[$y] == "31-45 Days"){
                        if ($diviation->days >= 31 && $diviation->days <= 45) {
                            $week_2 += $total;
                        }
                    }elseif($criteria[$y] == "46-60 Days"){
                        if ($diviation->days >= 46 && $diviation->days <= 60) {
                            $week_3 += $total;
                        }
                    }elseif($criteria[$y] == "61-75 Days"){
                        if ($diviation->days >= 61 && $diviation->days <= 75) {
                            $week_4 += $total;
                        }
                    }elseif($criteria[$y] == "76-90 Days"){
                        if ($diviation->days >= 76 && $diviation->days <= 90) {
                            $week_5 += $total;
                        }
                    }elseif($criteria[$y] == "91-120 Days"){
                        if ($diviation->days >= 91 && $diviation->days <= 120) {
                            $week_6 += $total;
                        }
                    }elseif($criteria[$y] == "121-180 Days"){
                        if ($diviation->days >= 121 && $diviation->days <= 180) {
                            $week_7 += $total;
                        }
                    }elseif($criteria[$y] == "181-365 Days"){
                        if ($diviation->days >= 181 && $diviation->days <= 365) {
                            $week_8 += $total;  
                        }
                    }elseif($criteria[$y] == "Over 365 Days"){
                        if ($diviation->days >= 366) {
                            $week_9 += $total;
                        }
                    }
                }

                $arr[] = array(
                    "document_no" => $document_no,
                    "total" => $total
                );

                $total_outstanding += ($ex_local_debit - $ex_local_credit);
            }
            
            // die(json_encode($arr));

            $content = "";
            $week_11 = 0;
            $week_22 = 0;
            $week_33 = 0;
            $week_44 = 0;
            $week_55 = 0;
            $week_66 = 0;
            $week_77 = 0;
            $week_88 = 0;
            $week_99 = 0;
            for ($z = 0; $z < count($criteria); $z++) {
                if(@$criteria[$z] == "1-30 Days"){
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_1) . '</td>';
                    $week_11 = $week_1;
                }elseif(@$criteria[$z] == "31-45 Days"){
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_2) . '</td>';
                    $week_22 = $week_2;
                }elseif(@$criteria[$z] == "46-60 Days"){
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_3) . '</td>';
                    $week_33 = $week_3;
                }elseif(@$criteria[$z] == "61-75 Days"){
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_4) . '</td>';
                    $week_44 = $week_4;
                }elseif(@$criteria[$z] == "76-90 Days"){
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_5) . '</td>';
                    $week_55 = $week_5;
                }elseif(@$criteria[$z] == "91-120 Days"){
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_6) . '</td>';
                    $week_66 = $week_6;
                }elseif(@$criteria[$z] == "121-180 Days"){
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_7) . '</td>';
                    $week_77 = $week_7;
                }elseif(@$criteria[$z] == "181-365 Days"){
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_8) . '</td>';
                    $week_88 = $week_8;
                }else {
                    $content .= '<td style="text-align:right;">' . $this->formatNo($week_9) . '</td>';
                    $week_99 = $week_9;
                }
            }

            $total_diference = ($week_11 + $week_22 + $week_33 + $week_44 + $week_55 + $week_66 + $week_77 + $week_88 + $week_99);

            if(abs($current + $week_1 + $week_2 + $week_3 + $week_4 + $week_5 + $week_6 + $week_7 + $week_8 + $week_9) > 0){
                $header .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $customer_name. '</td>
                                <td>' . $plant . '</td>
                                <td style="text-align:center;">INR</td>
                                <td style="text-align:right;">' . $this->formatNo($total_outstanding) . '</td>
                                '.$content.'
                                <td style="text-align:right;">' . $this->formatNo(($total_outstanding - $total_diference)) . '</td>
                                <td style="text-align:right;">' . $this->formatNo(($total_outstanding)) . '</td>
                            </tr>';
                $no++;
            }

            $grand_total_outstanding += $total_outstanding;
            $grand_total_week_1 += $week_1;
            $grand_total_week_2 += $week_2;
            $grand_total_week_3 += $week_3;
            $grand_total_week_4 += $week_4;
            $grand_total_week_5 += $week_5;
            $grand_total_week_6 += $week_6;
            $grand_total_week_7 += $week_7;
            $grand_total_week_8 += $week_8;
            $grand_total_week_9 += $week_9;
            $grand_diferences += $total_diference;
            $grand_total_all += ($total_outstanding);
        }

        $contentTotal = "";
        for ($x = 0; $x < count($criteria); $x++) {
            if($criteria[$x] == "1-30 Days"){
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_1) . '</td>';
            }elseif($criteria[$x] == "31-45 Days"){
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_2) . '</td>';
            }elseif($criteria[$x] == "46-60 Days"){
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_3) . '</td>';
            }elseif($criteria[$x] == "61-75 Days"){
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_4) . '</td>';
            }elseif($criteria[$x] == "76-90 Days"){
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_5) . '</td>';
            }elseif($criteria[$x] == "91-120 Days"){
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_6) . '</td>';
            }elseif($criteria[$x] == "121-180 Days"){
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_7) . '</td>';
            }elseif($criteria[$x] == "181-365 Days"){
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_8) . '</td>';
            }else {
                $contentTotal .= '<td style="text-align:right;">' . $this->formatNo($grand_total_week_9) . '</td>';
            }
        }

        $header .= '  <tr>
                        <td style="text-align:right;" colspan="4"><b>GRAND TOTAL</b></td>
                        <td style="text-align:right;">' . $this->formatNo($grand_total_outstanding) . '</td>
                        '.$contentTotal.'
                        <td style="text-align:right;">' . $this->formatNo(($grand_total_outstanding - $grand_diferences)) . '</td>
                        <td style="text-align:right;">' . $this->formatNo($grand_total_outstanding) . '</td>
                    </tr>';

        $htmlend = '</table></body></html>';
        echo $html . $header . $htmlend;
    }
}
