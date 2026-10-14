<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ap extends CI_Controller
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

    public function readSuppliers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('suppliers', ["name" => $post]);
        echo json_encode($send);
    }

    public function readPostingNo()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT number, journal_date FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') and number like '%$post%' ORDER BY number DESC LIMIT 500");
        echo json_encode($send);
    }

    public function readDocumentNo()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT document_no FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') and document_no like '%$post%' ORDER BY document_no DESC LIMIT 500");
        echo json_encode($send);
    }

    public function readInvoiceNo()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT invoice_no FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') and invoice_no like '%$post%' ORDER BY invoice_no DESC LIMIT 500");
        echo json_encode($send);
    }

    public function readClno()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT number FROM journal_clearings WHERE number like '%$post%' ORDER BY number DESC LIMIT 500");
        echo json_encode($send);
    }

    public function begin_balance($supplier_id, $filter_from, $filter_to, $filter_currency, $filter_status = "", $balance_local)
    {
        if ($filter_currency == "INR" || $filter_currency == "") {
            $filter_currency = "";
            $currency = "INR";
        } else {
            $filter_currency = $filter_currency;
            $currency = $filter_currency;
        }

        $query = $this->db->query("SELECT number, trans_date, currency, document_no, account_number,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_debit) ELSE SUM(original_debit) END) as local_debit,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_credit) ELSE SUM(original_credit) END) as local_credit
            FROM journal_postings
            WHERE company_id = '$supplier_id' and modul NOT IN ('CLEARING','BANK STATEMENT') and journal_date < '$filter_from'
            AND account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008')
            GROUP BY number, document_no, account_number");
        $purchase_invoices = $query->result_array();

        $local_debit = 0;
        $local_credit = 0;
        foreach ($purchase_invoices as $purchase_invoice) {
            if ($currency != "INR") {
                $ex_local_debit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_debit']);
                $ex_local_credit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_credit']);
            } else {
                $ex_local_debit = $purchase_invoice['local_debit'];
                $ex_local_credit = $purchase_invoice['local_credit'];
            }

            $local_debit += $ex_local_debit;
            $local_credit += $ex_local_credit;
        }

        // die($balance_local ." + ". $local_credit . " - " . $local_debit);
        $begin_balance = ($balance_local + $local_credit - $local_debit);
        return $begin_balance;
    }

    public function formatNo($amount)
    {
        if ($amount >= 0) {
            return number_format(@$amount, 2);
        } else {
            return "<span style='color:red;'>(" . number_format(abs($amount), 2) . ")</span>";
        }
    }

    public function exchangeRates($trans_date, $currency_from, $currency_to, $amount = 0)
    {
        $query_exchange = $this->db->query("SELECT * FROM exchange_rates WHERE start_date <= '$trans_date' and end_date >= '$trans_date' and currency_from = '$currency_to' and currency_to = '$currency_from'");
        $row_exchange = $query_exchange->row();

        if ($currency_to == "INR") {
            return $amount;
        } else {
            if ($row_exchange) {
                $total = ($amount / $row_exchange->middle);
            } else {
                $total = $amount;
            }

            return $total;
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
        if ($filter_from == "" && $filter_to == "") {
            $filter_from = "2024-11-01";
            $filter_to = date("Y-m-d");
        }

        $filter_supplier = base64_decode($this->input->get("filter_supplier"));
        $filter_posting_no = base64_decode($this->input->get("filter_posting_no"));
        $filter_document_no = base64_decode($this->input->get("filter_document_no"));
        $filter_invoice_no = base64_decode($this->input->get("filter_invoice_no"));
        $filter_cl_no = base64_decode($this->input->get("filter_cl_no"));
        $filter_currency = $this->input->get("filter_currency");
        $filter_display = $this->input->get("filter_display");
        $filter_status = $this->input->get("filter_status");
        $period = date("Ym", strtotime($filter_to));

        if ($filter_currency == "INR") {
            $filter_currency = "";
            $currency = "INR";
        } else {
            $filter_currency = $filter_currency;
            $currency = $filter_currency;
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.account_number, c.account_name, (CASE WHEN '$currency' = 'INR' THEN b.balance_local ELSE b.balance END) as balance_local");
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
        $this->db->like("a.id", $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();

        //Purchase invoicing
        $query_1 = $this->db->query("SELECT 
            (CASE 
                WHEN a.modul = 'PURCHASE INVOICING' THEN 'PI'
                WHEN a.modul = 'AP PAYMENT' THEN 'AP'
                WHEN a.modul = 'ADJUSTMENT' THEN 'AD'
                WHEN a.modul = 'CLEARING' THEN 'CL'
                ELSE 'NA' END
            ) AS source,
            a.company_id,
            a.document_no,
            a.description,
            a.invoice_no,
            a.trans_date,
            a.journal_date AS posting_date,
            a.number AS voucher_no,
            a.account_number,
            a.currency,
            a.original_debit,
            a.original_credit,
            a.local_debit,
            a.local_credit,
            a.status
            FROM (SELECT number, modul, document_no, invoice_no, journal_date, account_number, currency, company_id, description, status, trans_date,
                    SUM(original_debit) AS original_debit,
                    SUM(original_credit) AS original_credit,
                    SUM(local_debit) AS local_debit,
                    SUM(local_credit) AS local_credit 
                FROM journal_postings
                WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008')
                AND journal_date BETWEEN '$filter_from' and '$filter_to'
                AND `number` like '%$filter_posting_no%' 
                AND document_no like '%$filter_document_no%' 
                AND invoice_no like '%$filter_invoice_no%'
                GROUP BY number, document_no, account_number, modul, company_id
            ) a
            GROUP BY a.number, a.document_no, a.account_number, a.company_id
            ORDER BY a.journal_date ASC");
        $data_purchase_invoices = $query_1->result_array();

        //Journal Clearing
        $query_2 = $this->db->query("SELECT `number`, journal_date, document_no, company_id, `description` FROM journal_clearings WHERE number like '%$filter_cl_no%' GROUP BY document_no, company_id, `description`");
        $journal_clearings = $query_2->result_array();

        $arrJournalClearings = [];
        foreach ($journal_clearings as $journal_clearing) {
            $arrJournalClearings[$journal_clearing['document_no'] . $journal_clearing['company_id'] . $journal_clearing['description']] = $journal_clearing;
        }

        $arrPurchaseInvoices = [];
        foreach ($data_purchase_invoices as $data_purchase_invoice) {
            $cl_no = @$arrJournalClearings[$data_purchase_invoice['document_no'] . $data_purchase_invoice['company_id'] . $data_purchase_invoice['description']]['number'];
            $cl_date = @$arrJournalClearings[$data_purchase_invoice['document_no'] . $data_purchase_invoice['company_id'] . $data_purchase_invoice['description']]['journal_date'];

            $arrPurchaseInvoices[$data_purchase_invoice['company_id']][] = array_merge($data_purchase_invoice, ["cl_no" => $cl_no, "cl_date" => $cl_date]);
        }

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

        $detail = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Supplier ID</th>
                            <th rowspan="2">Supplier Name</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Receipt Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Number of<br>Days</th>
                            <th rowspan="2">Aging</th>
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
        $grand_balance_local = 0;
        foreach ($suppliers as $supplier) {
            $supplier_id = $supplier['id'];
            $supplier_name = $supplier['name'];

            $purchase_invoices = empty($arrPurchaseInvoices[$supplier_id]) ? [] : $arrPurchaseInvoices[$supplier_id];

            $local_debit = 0;
            $local_credit = 0;
            $total_balance_local = 0;

            if ($supplier['currency'] == $filter_currency || $filter_currency == "") {
                $begin_balance_local = $this->begin_balance($supplier_id, $filter_from, $filter_to, $filter_currency, $filter_status, @$supplier['balance_local']);
            } else {
                $begin_balance_local = $this->begin_balance($supplier_id, $filter_from, $filter_to, $filter_currency, $filter_status, 0);
            }

            // if (count($purchase_invoices) > 0) {
            $detail .= '  <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="18">BEGINING BALANCE - ' . $supplier['name'] . '</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">' . number_format(@$begin_balance_local, 2) . '</td>
                                </tr>';
            // }

            $accumulated = $begin_balance_local;
            foreach ($purchase_invoices as $purchase_invoice) {
                $document_no = $purchase_invoice['document_no'];
                $invoice_no = $purchase_invoice['invoice_no'];

                $cl_no = $purchase_invoice['cl_no'];
                $cl_date = $purchase_invoice['cl_date'];
                $invoice_date = $purchase_invoice['trans_date'];
                $receipt_date = $purchase_invoice['posting_date'];
                $payment_due = $purchase_invoice['trans_date'];

                $total_payment = 0;
                $cl_no_array = array();
                $cl_date_array = array();
                if ($purchase_invoice['source'] == "PI") {
                    $payments = $this->db->query("SELECT payment_no, payment_date, payment, account_type FROM ap_payments WHERE purchase_invoice = '$document_no'")->result_array();
                    $purchase = $this->db->query("SELECT * FROM purchase_invoices WHERE number = '$document_no'")->row_array();
                    $por = $this->db->query("SELECT DISTINCT receipt_date FROM purchase_order_receipts WHERE bc_document = '$invoice_no'")->row_array();
                    $poro = $this->db->query("SELECT DISTINCT receipt_date FROM purchase_order_receipt_others WHERE bc_document = '$invoice_no'")->row_array();

                    $invoice_date = $purchase['invoice_date'];
                    $receipt_date = empty($por['receipt_date']) ? @$poro['receipt_date'] : $por['receipt_date'];

                    $payment_due = date('Y-m-d', strtotime('+' . $purchase['payment_term'] . ' days', strtotime($receipt_date)));

                    if ($cl_no != "") {
                        $cl_no_array[] = $cl_no;
                        $cl_date_array[] = $cl_date;
                    }

                    foreach ($payments as $payment) {
                        $cl_no_array[] = $payment['payment_no'];
                        $cl_date_array[] = $payment['payment_date'];

                        if ($cl_no == "") {
                            if ($payment['account_type'] == "DEBIT") {
                                $total_payment += $payment['payment'];
                            } else {
                                $total_payment -= $payment['payment'];
                            }
                        }
                    }

                    $cl_no = implode(", ", $cl_no_array);
                    $cl_date = implode(", ", $cl_date_array);
                } elseif ($purchase_invoice['source'] == "AP") {
                    $qpayment = $this->db->query("SELECT * FROM ap_payments WHERE payment_no = '$document_no' and purchase_invoice IN (SELECT DISTINCT number FROM purchase_invoices)")->result_array();

                    if ($cl_no != "") {
                        $cl_no_array[] = $cl_no;
                        $cl_date_array[] = $cl_date;
                    }

                    foreach ($qpayment as $payment) {
                        $cl_no_array[] = $payment['payment_no'];
                        $cl_date_array[] = $payment['payment_date'];

                        if ($payment['account_type'] == "DEBIT") {
                            $total_payment -= $payment['payment'];
                        } else {
                            $total_payment += $payment['payment'];
                        }
                    }

                    $cl_no = implode(", ", array_unique($cl_no_array));
                    $cl_date = implode(", ", array_unique($cl_date_array));
                }

                $date1 = new DateTime($payment_due);
                $date2 = new DateTime($filter_to);
                $aging = $date1->diff($date2);

                if ($currency != "INR") {
                    $ex_local_debit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['original_debit']);
                    $ex_local_credit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['original_credit']);
                } else {
                    $ex_local_debit = $purchase_invoice['local_debit'];
                    $ex_local_credit = $purchase_invoice['local_credit'];
                }

                if ($cl_no == "") {
                    $balance_local = ($purchase_invoice['original_credit'] - $purchase_invoice['original_debit']);
                } elseif ($total_payment != 0) {
                    $balance_local = ($purchase_invoice['original_credit'] - $purchase_invoice['original_debit'] - $total_payment);
                } else {
                    $balance_local = 0;
                }

                if ($cl_no == "" || round($balance_local, 2) != 0) {
                    $status_purchase = "OPEN";
                    $style_status_purchase = "background-color:#C8FFCC;";
                } else {
                    $status_purchase = "CLOSE";
                    $style_status_purchase = "background-color:#FFC8C8;";
                }

                $accumulated += ($ex_local_credit - $ex_local_debit);

                if ($aging->days <= 0) {
                    $aging_due = "< 0";
                } elseif ($aging->days >= 1 && $aging->days <= 30) {
                    $aging_due = "1-30";
                } elseif ($aging->days >= 31 && $aging->days <= 45) {
                    $aging_due = "31-45";
                } elseif ($aging->days >= 46 && $aging->days <= 60) {
                    $aging_due = "46-60";
                } elseif ($aging->days >= 61 && $aging->days <= 75) {
                    $aging_due = "61-75";
                } elseif ($aging->days >= 76 && $aging->days <= 90) {
                    $aging_due = "76-90";
                } elseif ($aging->days >= 91 && $aging->days <= 120) {
                    $aging_due = "91-120";
                } elseif ($aging->days >= 121 && $aging->days <= 180) {
                    $aging_due = "121-180";
                } elseif ($aging->days >= 181 && $aging->days <= 365) {
                    $aging_due = "181-365";
                } else {
                    $aging_due = "> 365";
                }

                $content = '  <tr>
                                <td>' . $no . '</td>
                                <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                <td>' . $supplier['id'] . '</td>
                                <td>' . $supplier['name'] . '</td>
                                <td>' . $purchase_invoice['source'] . '</td>
                                <td>' . $invoice_date . '</td>
                                <td>' . $receipt_date . '</td>
                                <td>' . $payment_due . '</td>
                                <td>' . $aging->days . '</td>
                                <td>' . $aging_due . '</td>
                                <td>' . $purchase_invoice['document_no'] . '</td>
                                <td>' . $purchase_invoice['invoice_no'] . '</td>
                                <td>' . $purchase_invoice['posting_date'] . '</td>
                                <td>' . $purchase_invoice['voucher_no'] . '</td>
                                <td>' . $cl_no . '</td>
                                <td>' . $cl_date . '</td>
                                <td>' . $purchase_invoice['account_number'] . '</td>
                                <td>' . $currency . '</td>
                                <td style="text-align:right;">' . number_format($ex_local_debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($ex_local_credit, 2) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                            </tr>';

                if ($filter_status == "0" && $status_purchase == "OPEN") {
                    $detail .= $content;
                }

                if ($filter_status == "1" && $status_purchase == "CLOSE") {
                    $detail .= $content;
                }

                if ($filter_status == "") {
                    $detail .= $content;
                }

                $no++;
                $local_debit += $ex_local_debit;
                $local_credit += $ex_local_credit;
                $total_balance_local += $balance_local;
            }

            $balance_local = ($begin_balance_local + $local_credit - $local_debit);

            if (count($purchase_invoices) > 0) {
                $detail .= '    <tr style="background: #E5E5E5; font-weight:bold;">
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
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:right;">' . number_format($local_debit, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($local_credit, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($total_balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>
                                <tr>
                                    <td style="height:20px;"></td>
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
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>';
            }

            $noid++;

            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_balance_local += $total_balance_local;
            $grand_local_balance += ($begin_balance_local + $local_credit - $local_debit);
        }

        $detail .= '    <tr style="background: #C3FFB4; font-weight:bold;">
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
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right;">' . number_format($grand_local_debit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($grand_local_credit, 2) . '</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_balance_local) . '</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_local_balance) . '</td>
                        </tr>';

        $htmlend = '</table></body></html>';

        echo $html . $detail . $htmlend;
    }
}
