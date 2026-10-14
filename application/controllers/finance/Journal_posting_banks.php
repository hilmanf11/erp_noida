<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Journal_posting_banks extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_number', 'Product No', 'required|min_length[1]|max_length[100]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/journal_posting_banks');
            //$this->load->view('maintenance');
        } else {
            redirect('error_access');
        }
    }

    public function readModul()
    {
        $get = $this->input->get();
        $trans_type = base64_decode($get['trans_type']);
        $account_number = base64_decode($get['account_number']);
        $journal_date = base64_decode($get['journal_date']);
        $trans_date = strtotime("Y-m-01", strtotime($journal_date));

        $this->db->select('a.trans_date, a.number, a.document_no, a.journal_type_id, a.company_name, abs(a.local_debit - a.local_credit) as amount');
        $this->db->from('journal_postings a');
        // $this->db->where('a.modul', $trans_type);
        $this->db->where('a.modul !=', 'BANK STATEMENT');
        $this->db->where('a.status_bank', 0);
        $this->db->where('a.account_number', $account_number);
        $this->db->where("a.trans_date between '$trans_date' and '$journal_date'");
        $this->db->group_by('a.number');
        $this->db->order_by('a.trans_date', 'asc');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readVoucher()
    {
        $this->db->select('number');
        $this->db->from('journal_postings');
        $this->db->where("modul", 'BANK STATEMENT');
        $this->db->group_by('number');
        $this->db->order_by('number', 'desc');
        $this->db->limit(1000);
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function number($id_menu, $journal_date)
    {
        // $datenow = date("Y-m-d");
        $datenow = base64_decode($journal_date);
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(`number`) as kode FROM journal_postings WHERE `number` like '%$whereDate%'");
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
        $get = $this->input->get();
        $journal_date = @base64_decode($get['journal_date']);
        $modul = base64_decode($get['modul']);
        $bank_account = base64_decode($get['bank_account']);
        $posting_to = base64_decode($get['posting_to']);
        $trans_type = base64_decode($get['trans_type']);
        $number = base64_decode($get['number']);
        $document_no = explode(",", base64_decode($get['document_no']));

        $account_coa = $this->crud->read("account_coa", [], ["account_number" => $posting_to]);

        $this->db->select('a.*');
        $this->db->from('journal_postings a');
        // $this->db->where('a.modul', $trans_type);
        $this->db->where('a.status_bank', 0);
        $this->db->where('a.account_number', $bank_account);
        $this->db->where_in('a.number', $document_no);
        $this->db->order_by('a.number', 'asc');
        $journals = $this->db->get()->result_array();

        $data = array();
        $grand_balance_original = 0;
        $grand_balance_local = 0;
        foreach ($journals as $journal) {
            $data[] = array(
                "trans_date" => $journal_date,
                "document_no" => $journal['document_no'],
                "invoice_no" => $journal['invoice_no'],
                "company_id" => $journal['company_id'],
                "company_name" => $journal['company_name'],
                "modul" => $modul,
                "account_number" => $journal['account_number'],
                "account_name" => $journal['account_name'],
                "description" => $journal['company_name'] . " | " . $journal['document_no'] . " | " . $journal['invoice_no'] . " | " . $journal['number'] . " | " . $number,
                "currency" => $journal['currency'],
                "original_debit" => $journal['original_debit'],
                "original_credit" => $journal['original_credit'],
                "rates" => 1,
                "local_debit" => $journal['local_debit'],
                "local_credit" => $journal['local_credit'],
            );

            $grand_balance_original += ($journal['original_debit'] - $journal['original_credit']);
            $grand_balance_local += ($journal['local_debit'] - $journal['local_credit']);
        }

        if ($grand_balance_original > 0) {
            $grand_original_debit = abs($grand_balance_original);
            $grand_original_credit = 0;
        } else {
            $grand_original_debit = 0;
            $grand_original_credit = abs($grand_balance_original);
        }

        if ($grand_balance_local > 0) {
            $grand_local_debit = abs($grand_balance_local);
            $grand_local_credit = 0;
        } else {
            $grand_local_debit = 0;
            $grand_local_credit = abs($grand_balance_local);
        }

        $data[] = array(
            "trans_date" => $journal_date,
            "document_no" => $number,
            "invoice_no" => @$journals[0]['invoice_no'],
            "company_id" => @$journals[0]['company_id'],
            "company_name" => @$journals[0]['company_name'],
            "modul" => $modul,
            "account_number" => @$account_coa->account_number,
            "account_name" => @$account_coa->account_name,
            "description" => "Reconciliation Bank",
            "currency" => "INR",
            "original_debit" => $grand_original_credit,
            "original_credit" => $grand_original_debit,
            "rates" => 1,
            "local_debit" => $grand_local_credit,
            "local_credit" => $grand_local_debit,
        );

        $footer[] = array(
            "original_debit" => ($grand_original_debit + $grand_original_credit),
            "original_credit" => ($grand_original_credit + $grand_original_debit),
            "local_debit" => ($grand_local_debit + $grand_local_credit),
            "local_credit" => ($grand_local_credit + $grand_local_debit),
        );

        $result['total'] = count($data);
        $result = array_merge($result, ['rows' => $data], ["footer" => $footer]);
        echo json_encode($result);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
            $filter_modul = base64_decode($this->input->get('filter_modul'));
            $filter_voucher = base64_decode($this->input->get('filter_voucher'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.journal_date, a.number, a.journal_type_id, b.name as journal_type_name, a.modul, a.remarks, 'INR' as currency, a.rates, a.posting,
                a.created_by, a.created_date, a.updated_by, a.updated_date, a.approved, a.approved_to, a.approved_by, a.approved_date,
                SUM(a.original_debit) as original_debit, 
                SUM(a.original_credit) as original_credit, 
                SUM(a.local_debit) as local_debit, 
                SUM(a.local_credit) as local_credit");
            $this->db->from('journal_postings a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id', 'left');
            $this->db->where("a.modul", 'BANK STATEMENT');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
            }
            $this->db->like('a.journal_type_id', $filter_journal_type);
            $this->db->like('a.modul', $filter_modul);
            $this->db->like('a.number', $filter_voucher);
            $this->db->group_by('a.number');
            $this->db->order_by('a.journal_date', 'asc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatablesCheck()
    {
        $post = $this->input->post();

        $journal_date = $post['journal_date'];
        $transaction_to = date("Y-m-t", strtotime($journal_date));
        $modul = $post['modul'];

        if (!empty($post['document_no'])) {
            $document_no = explode(",", $post['document_no']);
        } else {
            $document_no = array();
        }

        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('journal_date', $transaction_to);
        $this->db->where('modul', $modul);
        if (count($document_no) > 0) {
            $this->db->where_in('document_no', $document_no);
        }
        $totalRows = $this->db->count_all_results('', false);

        if ($totalRows > 0) {
            echo 1;
        } else {
            echo 0;
        }
    }

    public function datatableDetails()
    {
        $number = base64_decode($this->input->get('number'));
        $filters = json_decode($this->input->post('filterRules'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('number', $number);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('document_no', 'asc');
        $this->db->order_by('journal_date', 'asc');
        $this->db->order_by('account_number', 'asc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        foreach ($records as $record) {
            $data[] = array(
                "trans_date" => $record['trans_date'],
                "document_no" => $record['document_no'],
                "invoice_no" => $record['invoice_no'],
                "company_name" => $record['company_name'],
                "description" => $record['description'],
                "account_number" => $record['account_number'],
                "account_name" => $record['account_name'],
                "currency" => "INR",
                "original_debit" => $record['original_debit'],
                "original_credit" => $record['original_credit'],
                "rates" => $record['rates'],
                "local_debit" => $record['local_debit'],
                "local_credit" => $record['local_credit'],
            );

            $grand_original_debit += $record['original_debit'];
            $grand_original_credit += $record['original_credit'];
            $grand_local_debit += $record['local_debit'];
            $grand_local_credit += $record['local_credit'];
        }

        $footer[] = array(
            "currency" => "TOTAL",
            "original_debit" => $grand_original_debit,
            "original_credit" => $grand_original_credit,
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
        );

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records, 'footer' => $footer]);
        echo json_encode($result);
    }

    public function datatableUpdates()
    {
        $number = base64_decode($this->input->get('number'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('number', $number);
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        foreach ($records as $record) {
            array_push($data, $record);

            $grand_original_debit += $record['original_debit'];
            $grand_original_credit += $record['original_credit'];
            $grand_local_debit += $record['local_debit'];
            $grand_local_credit += $record['local_credit'];
        }

        $footer[] = array(
            "currency" => "TOTAL",
            "original_debit" => $grand_original_debit,
            "original_credit" => $grand_original_credit,
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
        );

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records, 'footer' => $footer]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $dataJournals = $post['dataJournals'];

            foreach ($dataJournals as $dataJournal) {
                $data2 = explode(" | ", $dataJournal['description']);

                if (@$data2[3] != "") {
                    $check = $this->crud->reads("journal_postings", ["description" => @$data2[3]], ["modul" => "BANK STATEMENT"]);

                    if (count($check) > 0) {
                        die(json_encode(array("title" => "Failed", "message" => "GL No " . @$data2[3] . " has been posting bank", "theme" => "error")));
                    }
                }
            }

            $this->db->trans_start();
            foreach ($dataJournals as $dataJournal) {
                if (!empty(@$purchase_invoice['id'])) {
                    $send = $this->crud->update('journal_postings', ["id" => $purchase_invoice['id']], $dataJournal);
                } else {
                    $send = $this->crud->create('journal_postings', $dataJournal, "logs");
                    if ($send) {
                        $data = explode(" | ", $dataJournal['description']);
                        $this->db->update('journal_postings', ["status_bank" => 1], ["number" => @$data[3], "account_number" => $dataJournal['account_number']]);
                        $this->db->update('journal_postings', ["status_bank" => 1], ["number" => @$data[4], "account_number" => $dataJournal['account_number']]);
                    }
                }
            }
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                echo json_encode(array("title" => "Error", "message" => "Data Saved Failed", "theme" => "error"));
            } else {
                echo json_encode(array("title" => "Success", "message" => "Data Saved Successfully", "theme" => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $postings = $this->crud->reads("journal_postings", ["number" => $data['number']]);

        foreach ($postings as $posting) {
            $result = explode(" | ", $posting->description);
            $this->db->update('journal_postings', ["status_bank" => 0], ["number" => @$result[3], "account_number" => $posting->account_number]);
            $this->db->update('journal_postings', ["status_bank" => 0], ["number" => @$result[4], "account_number" => $posting->account_number]);
        }

        $send = $this->crud->delete('journal_postings', $data);
        echo $send;
    }

    public function print_voucher($number)
    {
        $number = base64_decode($number);
        $journal_total = $this->crud->reads('journal_postings', [], ["number" => $number]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 10;
        $page = ceil(count($journal_total) / $rows);
        //Generate QRcode
        $this->createQrcode(@$number, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $number . '</title>
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
                            <p>Display pages for 15 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as journal_type_name');
            $this->db->from('journal_postings a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            $this->db->like('a.number', $number);
            $this->db->order_by('a.trans_date', 'ASC');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

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
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $number . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . @$config_iso->doc_general_ledger . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . @$config_iso->form_general_ledger . '</td>
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
                                    <h3>GENERAL LEDGER VOUCHER</h3>
                                </center>
                                <div style="float:left; width:49%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">General Ledger No</td>
                                            <td width="10">:</td>
                                            <td><b>' . $number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Journal Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['journal_date'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Journal Type</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['journal_type_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Company Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['company_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Modul</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['modul'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">Document No</th>
                                        <th rowspan="2">Account No</th>
                                        <th rowspan="2">Account Name</th>
                                        <th rowspan="2">Description</th>
                                        <th colspan="3">Original Currency</th>
                                        <th colspan="3">Local Currency</th>
                                    </tr>
                                    <tr>
                                        <th>Currency</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Rates</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>';
            $original_debit = 0;
            $original_credit = 0;
            $rates = 0;
            $local_debit = 0;
            $local_credit = 0;
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['document_no'] . '</td>
                                <td>' . $record['account_number'] . '</td>
                                <td>' . $record['account_name'] . '</td>
                                <td>' . $record['description'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format($record['original_debit'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['original_credit'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['rates'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['local_debit'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['local_credit'], 2) . '</td>
                            </tr>';

                $original_debit += $record['original_debit'];
                $original_credit += $record['original_credit'];
                $rates += $record['rates'];
                $local_debit += $record['local_debit'];
                $local_credit += $record['local_credit'];
                $no++;
            }

            $html .= '  <tr>
                            <th style="text-align:right" colspan="6">SUB TOTAL</th>
                            <th style="text-align:right;">' . @number_format($original_debit, 2) . '</th>
                            <th style="text-align:right;">' . @number_format($original_credit, 2) . '</th>
                            <th style="text-align:right;">-</th>
                            <th style="text-align:right;">' . @number_format($local_debit, 2) . '</th>
                            <th style="text-align:right;">' . @number_format($local_credit, 2) . '</th>
                        </tr>
                    </table>';

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=posting_journals_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
        $filter_modul = base64_decode($this->input->get('filter_modul'));
        $filter_voucher = base64_decode($this->input->get('filter_voucher'));

        $this->db->select('a.*, b.name as journal_type_name');
        $this->db->from('journal_postings a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id');
        $this->db->where("a.modul", 'BANK STATEMENT');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
        }
        $this->db->like('a.journal_type_id', $filter_journal_type);
        $this->db->like('a.modul', $filter_modul);
        $this->db->like('a.number', $filter_voucher);
        $this->db->order_by('a.journal_date', 'asc');
        $this->db->order_by('a.number', 'asc');
        $this->db->order_by('a.document_no', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>POSTING JOURNAL</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Voucher No</th>
                <th rowspan="2">Journal Date</th>
                <th rowspan="2">Journal Type</th>
                <th rowspan="2">Modul</th>
                <th rowspan="2">Document No</th>
                <th rowspan="2">Invoice No</th>
                <th rowspan="2">Company Name</th>
                <th rowspan="2">Trans Date</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Account No</th>
                <th rowspan="2">Account Name</th>
                <th colspan="3">Original Debit</th>
                <th colspan="3">Local Debit</th>
            </tr>
            <tr>
                <th>Currency</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Rates</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['journal_date'] . '</td>
                    <td>' . $data['journal_type_name'] . '</td>
                    <td>' . $data['modul'] . '</td>
                    <td>' . $data['document_no'] . '</td>
                    <td>' . $data['invoice_no'] . '</td>
                    <td>' . $data['company_name'] . '</td>
                    <td>' . $data['trans_date'] . '</td>
                    <td>' . $data['description'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . number_format($data['original_debit'], 4) . '</td>
                    <td>' . number_format($data['original_credit'], 4) . '</td>
                    <td>' . number_format($data['rates'], 2) . '</td>
                    <td>' . number_format($data['local_debit'], 4) . '</td>
                    <td>' . number_format($data['local_credit'], 4) . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
