<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Ar_receipts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->library('Convertcurrency');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('sales_invoice', 'Sales Invoice', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/ar_receipts');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $receipt_no = base64_decode($number);
        $this->db->select('a.*, b.account_name');
        $this->db->from('ar_receipts a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.receipt_no', $receipt_no);
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readReverse($number)
    {
        $receipt_no = base64_decode($number);
        $this->db->select('a.*, b.account_name');
        $this->db->from('ar_receipts a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.receipt_no', $receipt_no);
        $records = $this->db->get()->result_array();

        $arr = [];
        foreach ($records as $read) {
            unset($read['id']);

            if ($read['account_type'] == "DEBIT") {
                $account_type = "CREDIT";
            } else {
                $account_type = "DEBIT";
            }

            $arr[] = array_merge($read, array(
                "account_type" => $account_type,
            ));
        }

        die(json_encode($arr));
    }

    public function checkReversal()
    {
        $receipt_no = $this->input->post("receipt_no");
        $this->db->select('receipt_no');
        $this->db->from('ar_receipts');
        $this->db->like('note', $receipt_no);
        $records = $this->db->get()->result_array();

        die(json_encode(array("total" => count($records), "number" => @$records[0]['receipt_no'])));
    }

    public function readJournals($number, $journal_type_id = "", $bank_account = "")
    {
        $number = base64_decode($number);
        $reads = $this->crud->reads("ar_receipt_journals", [], ["receipt_no" => $number], "", "flag", "asc");

        if (count($reads) > 0) {
            echo json_encode($reads);
        }
    }

    public function readJournalReverse($number, $journal_type_id = "", $bank_account = "")
    {
        $number = base64_decode($number);
        // $reads = $this->crud->reads("ar_receipt_journals", [], ["receipt_no" => $number], "", "flag", "asc");
        $sql = $this->db->query("SELECT * FROM ar_receipt_journals WHERE receipt_no = '$number' ORDER BY flag ASC");
        $reads = $sql->result_array();

        $arr = [];
        foreach ($reads as $read) {
            unset($read['id']);
            $arr[] = array_merge($read, array(
                "local_debit" => $read['local_credit'],
                "local_credit" => $read['local_debit'],
                "debit" => $read['credit'],
                "credit" => $read['debit'],
            ));
        }

        if (count($arr) > 0) {
            echo json_encode($arr);
        }
    }

    function readExchangeRate()
    {
        $receipt_date = $this->input->post('receipt_date');
        $trans_date = $this->input->post('trans_date');
        $currency = $this->input->post('currency');

        $search_date = date("d", strtotime($receipt_date));
        if ($search_date == "31") {
            $receipt_date = date("Y-m-d", strtotime('-1 days', strtotime($receipt_date)));
        }

        $monthNow = date('Y-m-01', strtotime($receipt_date));
        $monthbf = date('Y-m-01', strtotime($trans_date));

        if ($monthNow != $monthbf) {
            if (date("m", strtotime($receipt_date)) == "11") {
                $monthbf = date('Y-m-t', strtotime('-1 month', strtotime($receipt_date)));
            } else {
                $monthbf = date('Y-m-01', strtotime('-1 month', strtotime($receipt_date)));
            }
        }

        $exchangeNow = $this->crud->read('exchange_rates', [], ["start_date" => $monthNow, "currency_from" => $currency, "currency_to" => "INR"]);
        $exchangeBf = $this->crud->read('exchange_rates', [], ["start_date" => $monthbf, "currency_from" => $currency, "currency_to" => "INR"]);

        if ($exchangeNow) {
            $amount = $exchangeNow->middle;
        } else {
            $amount = 0;
        }

        if ($exchangeBf) {
            $amount2 = $exchangeBf->middle;
        } else {
            $amount2 = 0;
        }

        echo "Rate $currency to INR : <br> $monthbf | " . number_format($amount2, 4) . "<br> $monthNow | " . number_format($amount, 4);
    }

    public function calculateJournal($journal_type_id = "", $bank_account = "")
    {
        $journal_type_id = base64_decode($journal_type_id);
        $bank_account = base64_decode($bank_account);

        $banks = $this->crud->query("SELECT a.*, b.account_name FROM account_banks a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.account_number = '$bank_account'");

        $jsonDatas = json_decode(file_get_contents("json/ar_receipts.json"), true);
        $total = 0;
        $grand_total = 0;
        $grand_total_INR = 0;
        $flag = 1;
        $mergedData = array();
        foreach ($jsonDatas as $jsonData) {
            $account_number = $jsonData["account_number"];
            $account_name = $jsonData["account_name"];
            $account_type = $jsonData["account_type"];
            $description = $jsonData["description"];
            $total = $jsonData["receipt"];
            $currency = $jsonData['currency'];
            $rate = $jsonData['rate'];
            // $receipt_date = $jsonData['receipt_date'];
            // $trans_date = $jsonData['trans_date'];

            // $search_date = date("d", strtotime($receipt_date));
            // if ($search_date == "31") {
            //     $receipt_date = date("Y-m-d", strtotime('-10 days', strtotime($receipt_date)));
            // }

            // $monthBf = date('Y-m-01', strtotime($trans_date));
            // $monthNow = date('Y-m-01', strtotime($receipt_date));

            // if ($monthNow != $monthBf) {
            //     $monthBf = date('Y-m-01', strtotime("-1 month", strtotime($receipt_date)));
            // }

            // $exchangeBf = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "INR"]);
            // $exchangeNow = $this->crud->read('exchange_rates', [], ["start_date" => $monthNow, "currency_from" => $currency, "currency_to" => "INR"]);

            if ($currency != "INR") {
                $amount = ($total * $rate);
            } else {
                $amount = $total;
            }

            if (isset($mergedData[$account_number])) {
                // Jika nomor akun sudah ada dalam hasil penggabungan, tambahkan nilai total ke nomor akun tersebut
                if ($jsonData['account_number'] == $mergedData[$account_number] && $jsonData['account_type'] == "DEBIT") {
                    $mergedData[$account_number]["debit"] += $total;
                    $mergedData[$account_number]["local_debit"] += $amount;
                } elseif ($jsonData['account_number'] == $mergedData[$account_number] && $jsonData['account_type'] == "CREDIT") {
                    $mergedData[$account_number]["credit"] += $total;
                    $mergedData[$account_number]["local_credit"] += $amount;
                }
            } else {
                // Jika nomor akun belum ada dalam hasil penggabungan, tambahkan data baru
                if ($jsonData['account_type'] == "DEBIT") {
                    $mergedData[] = array(
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "account_type" => $account_type,
                        "description" => $description,
                        "debit" => $total,
                        "credit" => 0,
                        "rate" => empty($exchangeBf->middle) ? 1 : $exchangeBf->middle,
                        "local_debit" => round($amount, 2),
                        "local_credit" => 0,
                        "flag" => $flag,
                    );
                } elseif ($jsonData['account_type'] == "CREDIT") {
                    $mergedData[] = array(
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "account_type" => $account_type,
                        "description" => $description,
                        "debit" => 0,
                        "credit" => $total,
                        "rate" => empty($exchangeBf->middle) ? 1 : $exchangeBf->middle,
                        "local_debit" => 0,
                        "local_credit" => round($amount, 2),
                        "flag" => $flag,
                    );
                }
            }

            $flag++;
            $grand_total += round($total, 2);
            $grand_total_INR += round($amount, 2);
        }

        $arr = array_values($mergedData);

        foreach ($banks as $bank) {
            if ($currency != "INR") {
                if ($exchangeNow) {
                    $amount2 = round(($grand_total * $exchangeNow->middle), 2);
                } else {
                    $amount2 = 0;
                }
            } else {
                $amount2 = $grand_total;
            }

            $arr[] = array(
                "account_number" => $bank->account_number,
                "account_name" => $bank->account_name,
                "debit" => $grand_total,
                "credit" => 0,
                "rate" => empty($exchangeNow->middle) ? 1 : $exchangeNow->middle,
                "local_debit" => round($amount2, 2),
                "local_credit" => 0,
                "flag" => $flag,
            );

            if ($currency != "INR") {
                if ($amount2 > $grand_total_INR) {
                    $arr[] = array(
                        "account_number" => "31000002",
                        "account_name" => "Currency Fluction Gain A/c",
                        "debit" => 0,
                        "credit" => 0,
                        "rate" => 1,
                        "local_debit" => 0,
                        "local_credit" => abs($amount2 - $grand_total_INR),
                        "flag" => $flag + 1,
                    );
                } else {
                    $arr[] = array(
                        "account_number" => "49000001",
                        "account_name" => "Currency Fluction Loss A/c",
                        "debit" => 0,
                        "credit" => 0,
                        "rate" => 1,
                        "local_debit" => abs($amount2 - $grand_total_INR),
                        "local_credit" => 0,
                        "flag" => $flag + 1,
                    );
                }
            }
        }

        echo json_encode($arr);
    }

    public function readInvoiceType()
    {
        $customer_id = $this->input->get('customer_id');
        $receipt_type = $this->input->get('receipt_type');
        $plant = base64_decode($this->input->get('plant'));

        $records = $this->crud->query("SELECT `number`, journal_type_id FROM sales_invoices WHERE customer_id = '$customer_id' and plant = '$plant' and `status` = 0 and cancel = 0 GROUP BY `number` ORDER BY `number` ASC");
        echo json_encode($records);
    }

    public function readReceipts($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $data = $this->crud->query("SELECT DISTINCT receipt_no FROM ar_receipts WHERE customer_id = '$customer_id' ORDER BY `receipt_no` ASC");
        echo json_encode($data);
    }

    public function readInvoices($customer_id, $plant = "")
    {
        $plant = base64_decode($plant);
        $customer_id = base64_decode($customer_id);
        $data = $this->crud->query("SELECT DISTINCT `sales_invoice` FROM ar_receipts WHERE customer_id = '$customer_id' and plant like '%$plant%' ORDER BY `sales_invoice` ASC");
        echo json_encode($data);
    }

    public function readDp()
    {
        $customer_id = $this->input->post('customer_id');
        $sales_invoice = $this->input->post('sales_invoice');
        $plant = $this->input->post('plant');

        $this->db->select('a.*, b.account_name');
        $this->db->from('ar_receipts a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.sales_invoice', $sales_invoice);
        $this->db->where('a.plant', $plant);
        $this->db->where('a.journal_type_id', '20230823000001');
        $this->db->where('a.account_number', '2041101');
        //$this->db->where('a.status_dp', '0');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    // public function number($trans_date)
    // {
    //     $datenow    = "AR-" . date("Ymd", strtotime(base64_decode($trans_date)));
    //     $sqlGetID   = $this->db->query("SELECT max(`receipt_no`) as kode FROM ar_receipts WHERE `receipt_no` like '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%04s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -4);
    //         $urutan++;
    //         $autoID = sprintf("%04s", $urutan);
    //     }
    //     echo $datenow . "-" . $autoID;
    // }

    public function number($id_menu, $trans_date)
    {
        // $datenow = date("Y-m-d");
        $datenow = base64_decode($trans_date);
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(receipt_no) as kode FROM ar_receipts WHERE receipt_no like '%$whereDate%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;

            if ($kode == NULL) {
                $autoID = sprintf("%0" . $row_menu->sequence . "s", $kode + 1);
                $autoNo = $whereDate . $autoID;
            } else {
                $urutan = (int) substr($kode, -6);
                $urutan++;
                $autoID    = sprintf("%0" . $row_menu->sequence . "s", $urutan);
                $autoNo = $whereDate . $autoID;
            }

            echo $autoNo;
        }
    }

    public function datatablesTemp()
    {
        $sales_invoice = base64_decode($this->input->get('sales_invoice'));
        $sales_invoice_ex = explode(",", $sales_invoice);

        $this->db->select("a.*, a.total_grand as receipt");
        $this->db->from('sales_invoices a');
        $this->db->join('ar_receipts b', 'a.number = b.sales_invoice', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.cancel', 0);
        $this->db->where_in('a.number', $sales_invoice_ex);
        $this->db->group_by('a.number');
        $this->db->order_by('a.number', 'asc');
        $records = $this->db->get()->result_array();

        $total_receipt = 0;
        foreach ($records as $record) {
            $total_receipt += $record['receipt'];
            $journal_type_id = $record['journal_type_id'];

            $journal = $this->crud->query("SELECT a.*, a.flag, b.account_name FROM journal_setups a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.journal_type_id = '$journal_type_id' AND a.ar_receipt = 'YES'");

            $obj[] = array(
                "trans_date" => $record['trans_date'],
                "sales_invoice" => $record['number'],
                "so_number" => $record['so_number'],
                "description" => $record['customer_po'],
                "currency" => $record['currency'],
                "amount" => $record['receipt'],
                "balance" => $record['receipt'],
                "receipt" => $record['receipt'],
                "rate" => $record['rate'],
                "account_number" => @$journal[0]->account_number,
                "account_name" => @$journal[0]->account_name,
                "account_type" => "CREDIT",
            );
        }

        $arr['rows'] = $obj;
        $arr['total_receipt'] = round($total_receipt, 2);
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_receipt_type  = base64_decode($this->input->get('filter_receipt_type'));
        $filter_receipt_date_from = base64_decode($this->input->get('filter_receipt_date_from'));
        $filter_receipt_date_to = base64_decode($this->input->get('filter_receipt_date_to'));
        $filter_receipt_no = base64_decode($this->input->get('filter_receipt_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_receipt_by = base64_decode($this->input->get('filter_receipt_by'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");

        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, c.number as gl_no, c.status_bank, (CASE WHEN d.account_name is null THEN b.name ELSE CONCAT(b.name, ' | ', d.account_name) END) as customer_name");
            $this->db->from('ar_receipts a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('journal_postings c', "a.receipt_no = c.document_no and c.modul != 'BANK STATEMENT'", 'left');
            $this->db->join('account_coa d', 'a.other_account = d.account_number', 'left');
            $this->db->like('a.receipt_type', $filter_receipt_type);
            if ($filter_receipt_date_from != "" && $filter_receipt_date_to != "") {
                $this->db->where("a.receipt_date between '$filter_receipt_date_from' and '$filter_receipt_date_to'");
            } else {
                $this->db->where("a.receipt_date between '$date_from' and '$date_to'");
            }
            if ($filter_status != "") {
                $this->db->like('c.status_bank', $filter_status);
            }
            if ($filter_plant != "") {
                $this->db->where('a.plant', $filter_plant);
            }
            $this->db->like('a.receipt_no', $filter_receipt_no);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->like('a.sales_invoice', $filter_invoice_no);
            $this->db->like('a.bank_account', $filter_bank_no);
            $this->db->like('a.receipt_by', $filter_receipt_by);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.receipt_no', 'DESC');
            $this->db->group_by('a.receipt_no');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
        } else {
            $receipt_no = base64_decode($this->input->get('receipt_no'));

            $this->db->select('*');
            $this->db->from('ar_receipts');
            $this->db->where('receipt_no', $receipt_no);
            $this->db->order_by('status', 'ASC');
            $this->db->order_by('sales_invoice', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
        }
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $combinedAr = $post['combinedAr'];
            $combinedJournals = $post['combinedJournals'];

            foreach ($combinedAr as $ap) {
                if (!empty(@$ap['id'])) {
                    $send = $this->crud->update('ar_receipts', ["id" => $ap['id']], $ap);
                } else {
                    $send = $this->crud->create('ar_receipts', $ap, "logs");
                    if ($send) {
                        if ($ap['amount'] == $ap['receipt']) {
                            $this->crud->update('sales_invoices', ["number" => $ap['sales_invoice']], ["status" => 1]);
                        }

                        $this->crud->update('ar_receipts', ["sales_invoice" => $ap['sales_invoice'], "journal_type_id" => "20230823000001"], ["status_dp" => 1]);
                    }
                }
            }

            foreach ($combinedJournals as $journal) {
                $ar_receipt_journals = $this->crud->read('ar_receipt_journals', [], ["receipt_no" => $journal['receipt_no'], "account_number" => $journal['account_number'], "flag" => $journal['flag']]);

                if (@$ar_receipt_journals->id != "") {
                    $send = $this->crud->update('ar_receipt_journals', ["receipt_no" => $journal['receipt_no'], "account_number" => $journal['account_number'], "flag" => $journal['flag']], $journal);
                } else {
                    $send = $this->crud->create('ar_receipt_journals', $journal, "logs");
                }
            }

            die(json_encode(array("title" => "Good Job", "message" => "Data Save Successfully", "theme" => "success")));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createJson()
    {
        $jsonData = $this->input->post('jsonData');
        $jsonData2 = $this->input->post('jsonData2');

        // Simpan data JSON ke dalam file
        file_put_contents('json/ar_receipts.json', $jsonData);
        file_put_contents('json/ar_receipt_journals.json', $jsonData2);
    }

    public function createPosting(){
        $document_no = $this->input->post('receipt_no');

        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '20240227000005' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if(@$row_menu->number == ""){
            $voucher_no = "";
        }else{
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number.$yearnow.$yearend."1";

            $sqlGetID	= $this->db->query("SELECT max(`number`) as kode FROM journal_postings WHERE `number` like '%$whereDate%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;
            
            if ($kode == NULL) {
                $autoID = sprintf("%0".$row_menu->sequence."s", $kode + 1);
                $autoNo = $whereDate.$autoID;
            } else {
                $urutan = (int) substr($kode, -6);
                $urutan++;
                $autoID	= sprintf("%0".$row_menu->sequence."s", $urutan);
                $autoNo = $whereDate.$autoID;
            }

            $voucher_no = $autoNo;
        }

        $this->db->select("a.receipt_no, b.journal_type_id, b.receipt_date, b.sales_invoice, b.description as remarks, b.plant, c.id as customer_id, c.name as customer_name, d.account_name as other_account,
                b.currency, a.description, a.account_number, a.account_name, a.debit, a.credit, a.flag, a.local_debit, a.local_credit, b.rate");
        $this->db->from('ar_receipt_journals a');
        $this->db->join("(SELECT * FROM ar_receipts GROUP BY receipt_no) b", "b.receipt_no = a.receipt_no");
        $this->db->join("customers c", "b.customer_id = c.id");
        $this->db->join('account_coa d', 'b.other_account = d.account_number', 'left');
        $this->db->where_in('a.receipt_no', $document_no);
        $this->db->order_by('a.receipt_no', 'asc');
        $this->db->order_by('a.flag', 'asc');
        $journals = $this->db->get()->result_array();

        $trans_date = "";
        $customer_name = "";
        $currency = "";

        $original_debit = 0;
        $original_credit = 0;
        $local_debit = 0;
        $local_credit = 0;

        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;

        $data = array();
        foreach ($journals as $journal) {
            $number = $journal['receipt_no'];
            $account_number = $journal['account_number'];
            $account_name = $journal['account_name'];
            $debit = $journal['debit'];
            $credit = $journal['credit'];
            $description = $journal['description'];
            $explode = explode(" | ", $description);
            $purchase_invoice = @$explode[0];
            $supplier_invoice = @$explode[1];

            if ($journal['currency'] != "INR") {
                $original_debit = $debit;
                $original_credit = $credit;
                $local_debit = $journal['local_debit'];
                $local_credit = $journal['local_credit'];

                if(($original_debit + $original_credit) == 0){
                    $rates = 1;
                }else{
                    $rates = (($journal['local_debit'] + $journal['local_credit']) / ($original_debit + $original_credit));
                }
            } else {
                $original_debit = $debit;
                $original_credit = $credit;
                $local_debit = $journal['local_debit'];
                $local_credit = $journal['local_credit'];

                $rates = 1;
            }

            $data[] = array(
                "journal_date" => $journal['receipt_date'],
                "journal_type_id" => $journal['journal_type_id'],
                "number" => $voucher_no,
                "trans_date" => $journal['receipt_date'],
                "document_no" => $number,
                "invoice_no" => $purchase_invoice,
                "company_id" => $journal['customer_id'],
                "company_name" => $journal['customer_name'] . " | " . $journal['plant'],
                "modul" => "AR RECEIPT",
                "account_number" => $account_number,
                "account_name" => $account_name,
                "description" => "(" . $journal['other_account'] . ") | " . $number . " | " . $purchase_invoice . " | " . $supplier_invoice,
                "currency" => $journal['currency'],
                "original_debit" => $original_debit,
                "original_credit" => $original_credit,
                "rates" => $rates,
                "local_debit" => $local_debit,
                "local_credit" => $local_credit,
            );

            $grand_original_debit += $original_debit;
            $grand_original_credit += $original_credit;
            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
        }

        if($voucher_no == ""){
            die(json_encode(array("title" => "Configuration", "message" => "Please Check Configuration in Serial No (Journal Posting) First", "theme" => "error")));
        }

        if(round($grand_local_debit, 2) != round($grand_local_credit, 2)){
            die(json_encode(array("title" => "Failed", "message" => "Balance Debit (".$grand_local_debit.") Cannot match on Balance Credit (" . $grand_local_credit . ")", "theme" => "error")));
        }

        foreach ($data as $dt) {
            $this->crud->create('journal_postings', $dt);
        }

        die(json_encode(array("title" => "Good Job", "message" => "Data Successfully Created to Journal Posting with Code GL No " . $voucher_no, "theme" => "success")));
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('ar_receipts', ["receipt_no" => $post['receipt_no']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        // $send = $this->crud->delete('ar_receipts', array("id" => $data['id']));

        // $send = $this->crud->update('ar_receipts', ["receipt_no" => $data['sales_invoice']], ["status_dp" => 0]);
        // echo $send;
    }

    public function deleteJournal()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('ar_receipt_journals', $data);
        echo $send;
    }

    public function delete()
    {
        $data = $this->input->post();

        $ar_receipts = $this->crud->reads("ar_receipts", [], ["receipt_no" => $data['receipt_no']]);
        foreach ($ar_receipts as $ap_receipt) {
            $this->crud->update("sales_invoices", [
                "number" => $ap_receipt->sales_invoice,
            ], ["status" => 0]);

            $this->crud->update('ar_receipts', ["receipt_no" => $ap_receipt->sales_invoice], ["status_dp" => 0]);
        }

        $send = $this->crud->delete('ar_receipts', $data);
        $send = $this->crud->delete('ar_receipt_journals', ["receipt_no" => $data['receipt_no']]);
        echo $send;
    }

    public function view_journal($receipt)
    {
        $receipt_no = base64_decode($receipt);
        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('ar_receipts a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->like('a.receipt_no', $receipt_no);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.receipt_date', 'DESC');
        $this->db->group_by('a.receipt_no');
        $receipt_total = $this->db->get()->result_array();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 10;
        $page = ceil(count($receipt_total) / $rows);
        //Generate QRcode
        $this->createQrcode(@$receipt_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $receipt_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
            
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 10 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as customer_name');
            $this->db->from('ar_receipts a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->like('a.receipt_no', $receipt_no);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.receipt_date', 'DESC');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            //Exchange Rate
            $receipt_date = $records[0]['receipt_date'];
            $currency = $records[0]['currency'];
            // $monthBf = date('Y-m-01', strtotime($receipt_date));
            $monthBf = $receipt_date;
            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "INR"]);

            if ($exchange) {
                $amount = $exchange->middle;
                $hide = "";
            } else {
                $amount = 0;
                $hide = "hidden";
            }

            $exchangeName = "Rp. " . number_format($amount, 2);

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $receipt_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_ar_receipt . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_ar_receipt . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>RECEIPT VOUCHER</h3>
                                </center>
                                <div style="float:left; width:40%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Receipt From</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['customer_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Receipt By</td>
                                            <td>:</td>
                                            <td><b>' . @$records[0]['receipt_by'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Receipt No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['receipt_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Receipt Date</td>
                                            <td>:</td>
                                            <td><b>' . @date("d F Y", strtotime(@$records[0]['receipt_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Bank Account</td>
                                            <td>:</td>
                                            <td><b>' . @$records[0]['bank_account'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div ' . $hide . ' style="float:left; width:15%; border:2px solid black; padding:10px; font-size:12px; margin-left:20px;"> 
                                    <p style="margin:0;">Rate USD to INR : <b>' . $exchangeName . '</b></p>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Sales Invoice No</th>
                                        <th>Description</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Receipt</th>
                                        <th>Remarks</th>
                                    </tr>';
            $grand_total = 0;
            foreach ($records as $record) {
                if ($record['account_type'] == "DEBIT") {
                    $grand_total -= $record['receipt'];
                    $subtotal -= $record['receipt'];
                } else {
                    $grand_total += $record['receipt'];
                    $subtotal += $record['receipt'];
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['sales_invoice'] . '</td>
                                <td>' . $record['description'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['amount']), 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['balance'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['receipt'], 2) . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }

            if (($i + 1) == $page) {
                $html_grand_total = '<tr>
                                        <th style="text-align:right" colspan="6">GRAND TOTAL</th>
                                        <th style="text-align:right;">' . @number_format($subtotal, 2) . '</th>
                                        <td></td>
                                    </tr>';
            } else {
                $html_grand_total = "";
            }

            $html .= '  <tr>
                            <th style="text-align:right" colspan="6">SUB TOTAL</th>
                            <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
                            <td></td>
                        </tr>';

            $html .= $html_grand_total;

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $hal++;
        }

        $html .= '  <table id="customers" style="margin-top:10px;">
                        <tr>
                            <th style="text-align:center;">Amount in Words</th>
                            <td>' . $this->convertcurrency->convertCurrencyToWords($grand_total, $records[0]['currency']) . '</td>
                        </tr>
                    </table>
                    <p style="font-size:12px;"><i>*This Receipt Voucher was prepared by ' . $config->name . '</i></p>
                    <div style="width:100%; float:left; margin-bottom:20px;">
                    <table id="customers" style="width:100%; font-size:12px;">
                        <tr>
                            <td rowspan="2" style="font-weight:bold;">Account No</td>
                            <td rowspan="2" style="font-weight:bold;">Account Name</td>
                            <td rowspan="2" style="font-weight:bold;">Description</td>
                            <td colspan="2" style="font-weight:bold;">Original Currency</td>
                            <td colspan="2" style="font-weight:bold;">Local Currency</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;">Debit</td>
                            <td style="font-weight:bold;">Credit</td>
                            <td style="font-weight:bold;">Debit</td>
                            <td style="font-weight:bold;">Credit</td>
                        </tr>';
        $journals = $this->crud->query("SELECT a.*, b.account_name 
        FROM ar_receipt_journals a 
        JOIN account_coa b ON a.account_number = b.account_number
        WHERE a.receipt_no = '$receipt_no' ORDER BY a.flag ASC");

        $total_debit = 0;
        $total_credit = 0;
        $local_total_debit = 0;
        $local_total_credit = 0;
        foreach ($journals as $journal) {

            $total_debit += $journal->debit;
            $total_credit += $journal->credit;
            $local_total_debit += $journal->local_debit;
            $local_total_credit += $journal->local_credit;

            $html .= '  <tr>
                            <td>' . $journal->account_number . '</td>
                            <td>' . $journal->account_name . '</td>
                            <td>' . $journal->description . '</td>
                            <td style="text-align:right;">' . number_format($journal->debit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($journal->credit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($journal->local_debit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($journal->local_credit, 2) . '</td>
                        </tr>';
        }

        $html .= '      <tr>
                            <td colspan="3"><b>BALANCE TOTAL</b></td>
                            <td style="text-align:right;">' . @number_format($total_debit, 2) . '</td>
                            <td style="text-align:right;">' . @number_format($total_credit, 2) . '</td>
                            <td style="text-align:right;">' . @number_format($local_total_debit, 2) . '</td>
                            <td style="text-align:right;">' . @number_format($local_total_credit, 2) . '</td>
                        </tr>
                    </table>
                </div>';

        $html .= '</table>
            <br>
            <table style="width:100%; font-size:12px;">
                <tr>
                    <td style="text-align:center;">Prepared By</td>
                    <td style="text-align:center;">Checked By</td>
                    <td style="text-align:center;">Approved By</td>
                </tr>
                <tr>
                    <td style="height:60px;"></td>
                    <td style="height:60px;"></td>
                    <td style="height:60px;"></td>
                </tr>
                <tr>
                    <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                    <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Accounting Manager</th>
                    <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                </tr>
            </table>
            </div>
        </div>';

        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    function linked($document_no, $amount){
        if($document_no != "-" ){
            $html = '<a onclick="window.open(\'' . base_url('finance/sales_invoices/print_dn/' . base64_encode($document_no). "/tb") . '\', \'_blank\', \'location=yes,height=650,width=1500,scrollbars=yes,status=yes\');" style="color: inherit;text-decoration: none;cursor: pointer;">' . number_format($amount, 2) . '</a>';
            return $html;
        }else{
            return number_format($amount, 2);
        }
    }

    public function print_voucher($receipt, $tb = "")
    {
        $receipt_no = base64_decode($receipt);
        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('ar_receipts a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->like('a.receipt_no', $receipt_no);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.receipt_date', 'DESC');
        $this->db->group_by('a.receipt_no');
        $receipt_total = $this->db->get()->result_array();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 10;
        $page = ceil(count($receipt_total) / $rows);
        //Generate QRcode
        $this->createQrcode(@$receipt_no, "assets/image/qrcode/");

        if($tb == ""){
            $media = "  @media screen {
                            .print {
                                display: none !important;
                            }
                        }
            
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }";
            $noprint = '<div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                                <p>Display pages for 8 rows</p>
                                <p>Paper Size A5, Layout Landscape</p>
                                <p>Margin Default, Scale 80</p>
                            </center>
                        </div>';
            $script = "<script>window.print()</script>";
        }else{
            $media = "";
            $noprint = "";
            $script = "";
        }

        $html = '<html>
                    <head>
                        <title>' . $receipt_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        '.$media.'
                    </style>
                    <body>
                    '.$noprint.'
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as customer_name');
            $this->db->from('ar_receipts a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->like('a.receipt_no', $receipt_no);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.receipt_date', 'DESC');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            //Exchange Rate
            $receipt_date = $records[0]['receipt_date'];
            $currency = $records[0]['currency'];
            // $monthBf = date('Y-m-01', strtotime($receipt_date));
            $monthBf = $receipt_date;
            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "INR"]);

            if ($exchange) {
                $amount = $exchange->middle;
                $hide = "";
            } else {
                $amount = 0;
                $hide = "hidden";
            }

            $exchangeName = "Rp. " . number_format($amount, 2);

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $receipt_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_ar_receipt . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_ar_receipt . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>RECEIPT VOUCHER</h3>
                                </center>
                                <div style="float:left; width:40%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Receipt From</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['customer_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Receipt By</td>
                                            <td>:</td>
                                            <td><b>' . @$records[0]['receipt_by'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Receipt No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['receipt_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Receipt Date</td>
                                            <td>:</td>
                                            <td><b>' . @date("d F Y", strtotime(@$records[0]['receipt_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Bank Account</td>
                                            <td>:</td>
                                            <td><b>' . @$records[0]['bank_account'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div ' . $hide . ' style="float:left; width:15%; border:2px solid black; padding:10px; font-size:12px; margin-left:20px;"> 
                                    <p style="margin:0;">Rate USD to INR : <b>' . $exchangeName . '</b></p>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Sales Invoice No</th>
                                        <th>Description</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Receipt</th>
                                        <th>Remarks</th>
                                    </tr>';
            $grand_total = 0;
            foreach ($records as $record) {
                if ($record['account_type'] == "DEBIT") {
                    $grand_total -= $record['receipt'];
                    $subtotal -= $record['receipt'];
                } else {
                    $grand_total += $record['receipt'];
                    $subtotal += $record['receipt'];
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['sales_invoice'] . '</td>
                                <td>' . $record['description'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['amount']), 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['balance'], 2) . '</td>
                                <td style="text-align:right;">' . $this->linked($record['sales_invoice'], $record['receipt']) . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }

            if (($i + 1) == $page) {
                $html_grand_total = '<tr>
                                        <th style="text-align:right" colspan="6">GRAND TOTAL</th>
                                        <th style="text-align:right;">' . @number_format($subtotal, 2) . '</th>
                                        <td></td>
                                    </tr>';
            } else {
                $html_grand_total = "";
            }

            $html .= '  <tr>
                            <th style="text-align:right" colspan="6">SUB TOTAL</th>
                            <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
                            <td></td>
                        </tr>';

            $html .= $html_grand_total;

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $hal++;
        }

        $html .= '  <table id="customers" style="margin-top:10px;">
                        <tr>
                            <th style="text-align:center;">Amount in Words</th>
                            <td>' . $this->convertcurrency->convertCurrencyToWords($grand_total, $records[0]['currency']) . '</td>
                        </tr>
                    </table>
                    <p style="font-size:12px;"><i>*This Receipt Voucher was prepared by ' . $config->name . '</i></p>';

        $html .= '</table>
            <br>
            <table style="width:100%; font-size:12px;">
                <tr>
                    <td style="text-align:center;">Prepared By</td>
                    <td style="text-align:center;">Checked By</td>
                    <td style="text-align:center;">Approved By</td>
                </tr>
                <tr>
                    <td style="height:60px;"></td>
                    <td style="height:60px;"></td>
                    <td style="height:60px;"></td>
                </tr>
                <tr>
                    <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                    <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Accounting Manager</th>
                    <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                </tr>
            </table>
            </div>
        </div>';

        $html .= "</div></div>".$script."</body>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ar_receipt_$format.xls");
        }

        $filter_receipt_type  = base64_decode($this->input->get('filter_receipt_type'));
        $filter_receipt_date_from = base64_decode($this->input->get('filter_receipt_date_from'));
        $filter_receipt_date_to = base64_decode($this->input->get('filter_receipt_date_to'));
        $filter_receipt_no = base64_decode($this->input->get('filter_receipt_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_receipt_by = base64_decode($this->input->get('filter_receipt_by'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('ar_receipts a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('journal_postings c', 'a.receipt_no = c.document_no', 'left');
        $this->db->like('a.receipt_type', $filter_receipt_type);
        if ($filter_receipt_date_from != "" && $filter_receipt_date_to == "") {
            $this->db->where("a.receipt_date between '$filter_receipt_date_from' and '$filter_receipt_date_to'");
        }
        if ($filter_status != "") {
            $this->db->where("c.status_bank = '$filter_status'");
        }
        $this->db->like('a.receipt_no', $filter_receipt_no);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.plant', $filter_plant);
        $this->db->like('a.sales_invoice', $filter_invoice_no);
        $this->db->like('a.bank_account', $filter_bank_no);
        $this->db->like('a.receipt_by', $filter_receipt_by);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.receipt_date', 'DESC');
        $this->db->group_by('a.receipt_no');
        $records = $this->db->get()->result_array();

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
                                <small>REPORT CASH BANK RECEIPT</small><br>
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
                    <th width="20">No</th>
                    <th>Payment Type</th>
                    <th colspan="2">Payment No</th>
                    <th>Payment Date</th>
                    <th>Supplier Name</th>
                    <th>Bank Account</th>
                    <th>Payment By</th>
                    <th colspan="2">Note</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $receipt_no = $data['receipt_no'];

            $this->db->select('*');
            $this->db->from('ar_receipts');
            $this->db->where('receipt_no', $receipt_no);
            $this->db->order_by('status', 'ASC');
            $this->db->order_by('sales_invoice', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['receipt_type'] . '</td>
                            <td colspan="2">' . $data['receipt_no'] . '</td>
                            <td>' . $data['receipt_date'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['bank_account'] . '</td>
                            <td>' . $data['receipt_by'] . '</td>
                            <td colspan="2">' . $data['note'] . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['receipt_no'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>Purchase Invoice</th>
                            <th>Supplier Invoice</th>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Payment</th>
                            <th>Remarks</th>
                            <th>Account No</th>
                            <th>Debt/Credit</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['sales_invoice'] . '</td>
                                <td>' . $detail['description'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['amount'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['balance'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['receipt'], 2)  . '</td>
                                <td>' . $detail['remarks'] . '</td>
                                <td>' . $detail['account_number'] . '</td>
                                <td>' . $detail['account_type'] . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table>';
        $html .= '  <table id="customers" style="margin-top:20px; width:50%;">
                        <tr>
                            <th width="200" style="text-align:center;">Approval By</th>
                            <th width="200" style="text-align:center;">Dept Manager</th>
                            <th width="200" style="text-align:center;">Created By</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                        </tr>
                    </table></body></html>';
        echo $html;
    }

    public function printDetail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ar_receipt_detail_$format.xls");
        }

        $filter_receipt_type  = base64_decode($this->input->get('filter_receipt_type'));
        $filter_receipt_date_from = base64_decode($this->input->get('filter_receipt_date_from'));
        $filter_receipt_date_to = base64_decode($this->input->get('filter_receipt_date_to'));
        $filter_receipt_no = base64_decode($this->input->get('filter_receipt_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_receipt_by = base64_decode($this->input->get('filter_receipt_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as customer_name, c.name as journal_type_name, d.account_name, e.bank_name');
        $this->db->from('ar_receipts a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('journal_types c', 'a.journal_type_id = c.id');
        $this->db->join('account_coa d', 'a.account_number = d.account_number', 'left');
        $this->db->join('account_banks e', 'a.bank_account = e.bank_account', 'left');
        $this->db->like('a.receipt_type', $filter_receipt_type);
        if ($filter_receipt_date_from != "" && $filter_receipt_date_to == "") {
            $this->db->where("a.receipt_date between '$filter_receipt_date_from' and '$filter_receipt_date_to'");
        }
        $this->db->like('a.receipt_no', $filter_receipt_no);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.plant', $filter_plant);
        $this->db->like('a.sales_invoice', $filter_invoice_no);
        $this->db->like('a.bank_account', $filter_bank_no);
        $this->db->like('a.receipt_by', $filter_receipt_by);
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('a.receipt_no', 'ASC');
        $records = $this->db->get()->result_array();

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
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br>
            <center>
                <h2>REPORT AR RECEIPT DETAIL</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $filter_receipt_date_from . ' - ' . $filter_receipt_date_to . '</td>
                </tr>
            </table>
            <br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Journal Type</th>
                    <th>Receipt Type</th>
                    <th>Receipt No</th>
                    <th>Receipt Date</th>
                    <th>Customer Name</th>
                    <th>Plant</th>
                    <th>Bank Account</th>
                    <th>Receipt By</th>
                    <th>Note</th>
                    <th>Sales Invoice</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Receipt</th>
                    <th>Remarks</th>
                    <th>Account No</th>
                    <th>Account Name</th>
                    <th>Debit/Credit</th>
                    <th>Created By</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['receipt_type'] . '</td>
                            <td>' . $data['receipt_no'] . '</td>
                            <td>' . $data['receipt_date'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['plant'] . '</td>
                            <td>' . $data['bank_account'] . ' - ' . $data['bank_name'] . '</td>
                            <td>' . $data['receipt_by'] . '</td>
                            <td>' . $data['note'] . '</td>
                            <td>' . $data['sales_invoice'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['amount'] . '</td>
                            <td>' . $data['balance'] . '</td>
                            <td>' . $data['receipt'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['account_type'] . '</td>
                            <td>' . $data['created_by'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function printJournal($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ar_receipt_journal_$format.xls");
        }

        $filter_receipt_type  = base64_decode($this->input->get('filter_receipt_type'));
        $filter_receipt_date_from = base64_decode($this->input->get('filter_receipt_date_from'));
        $filter_receipt_date_to = base64_decode($this->input->get('filter_receipt_date_to'));
        $filter_receipt_no = base64_decode($this->input->get('filter_receipt_no'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_receipt_by = base64_decode($this->input->get('filter_receipt_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.bank_account, b.receipt_date, b.receipt_by, b.sales_invoice, b.note, b.currency, c.name as customer_name, d.name as journal_type_name, e.account_name, f.bank_name, b.plant');
        $this->db->from('ar_receipt_journals a');
        $this->db->join('ar_receipts b', 'a.receipt_no = b.receipt_no');
        $this->db->join('customers c', 'b.customer_id = c.id');
        $this->db->join('journal_types d', 'b.journal_type_id = d.id');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        $this->db->join('account_banks f', 'b.bank_account = f.bank_account', 'left');
        $this->db->like('b.receipt_type', $filter_receipt_type);
        if ($filter_receipt_date_from != "" && $filter_receipt_date_to == "") {
            $this->db->where("b.receipt_date between '$filter_receipt_date_from' and '$filter_receipt_date_to'");
        }
        $this->db->like('b.receipt_no', $filter_receipt_no);
        $this->db->like('b.customer_id', $filter_customer);
        $this->db->like('b.plant', $filter_plant);
        $this->db->like('b.sales_invoice', $filter_invoice_no);
        $this->db->like('b.bank_account', $filter_bank_no);
        $this->db->like('b.receipt_by', $filter_receipt_by);
        $this->db->group_by('a.id');
        $this->db->order_by('c.name', 'ASC');
        $this->db->order_by('a.receipt_no', 'ASC');
        $this->db->order_by('a.flag', 'ASC');
        $records = $this->db->get()->result_array();

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
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br>
            <center>
                <h2>REPORT AR RECEIPT JOURNAL</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $filter_receipt_date_from . ' - ' . $filter_receipt_date_to . '</td>
                </tr>
            </table>
            <br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Journal Type</th>
                    <th rowspan="2">Receipt No</th>
                    <th rowspan="2">Receipt Date</th>
                    <th rowspan="2">Receipt By</th>
                    <th rowspan="2">Sales Invoice</th>
                    <th rowspan="2">Customer Name</th>
                    <th rowspan="2">Plant</th>
                    <th rowspan="2">Bank Account</th>
                    <th rowspan="2">Note</th>
                    <th rowspan="2">Currency</th>
                    <th rowspan="2">Account No</th>
                    <th rowspan="2">Account Name</th>
                    <th rowspan="2">Description</th>
                    <th colspan="2">Orginal Currency</th>
                    <th colspan="2">Local Currency</th>
                </tr>
                <tr>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['receipt_no'] . '</td>
                            <td>' . $data['receipt_date'] . '</td>
                            <td>' . $data['receipt_by'] . '</td>
                            <td>' . $data['sales_invoice'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['plant'] . '</td>
                            <td>' . $data['bank_account'] . ' - ' . $data['bank_name'] . '</td>
                            <td>' . $data['note'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['description'] . '</td>
                            <td>' . $data['debit'] . '</td>
                            <td>' . $data['credit'] . '</td>
                            <td>' . $data['local_debit'] . '</td>
                            <td>' . $data['local_credit'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
