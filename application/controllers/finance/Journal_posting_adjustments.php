<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Journal_posting_adjustments extends CI_Controller
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
            $this->load->view('finance/journal_posting_adjustments');
            //$this->load->view('maintenance');
        } else {
            redirect('error_access');
        }
    }

    public function checkInvoice()
    {
        $invoice_no = trim($this->input->post("document_no"));
        $last_document_no = trim($this->input->post("last_document_no"));

        if ($last_document_no == "") {
            $journals = $this->crud->reads("journal_postings", [], ["document_no" => $invoice_no]);
            die(json_encode(array("total" => count($journals))));
        } elseif ($invoice_no == $last_document_no) {
            die(json_encode(array("total" => 0)));
        } elseif ($invoice_no != $last_document_no) {
            $journals = $this->crud->reads("journal_postings", [], ["document_no" => $invoice_no]);
            die(json_encode(array("total" => count($journals))));
        }
    }

    public function checkReversal()
    {
        $number = $this->input->post("number");
        $this->db->select('number');
        $this->db->from('journal_postings');
        $this->db->like('remarks', $number);
        $records = $this->db->get()->result_array();

        die(json_encode(array("total" => count($records), "number" => @$records[0]['number'])));
    }

    public function journalTypeManual()
    {
        // $sqls = $this->crud->query("SELECT a.number 
        //     FROM journal_postings a
        //     JOIN suppliers b ON a.company_id = b.id
        //     WHERE modul = 'ADJUSTMENT'
        //     GROUP BY a.number");

        // $sqls = $this->crud->query("SELECT a.number 
        //     FROM journal_postings a
        //     JOIN customers b ON a.company_id = b.id
        //     WHERE modul = 'ADJUSTMENT'
        //     GROUP BY a.number");

        // $sqls = $this->crud->query("SELECT a.number 
        //     FROM journal_postings a
        //     JOIN account_banks b ON a.account_number = b.account_number
        //     WHERE modul = 'ADJUSTMENT'
        //     GROUP BY a.number");

        $sqls = $this->crud->query("SELECT number 
            FROM journal_postings
            WHERE modul = 'ADJUSTMENT' AND journal_type IS NULL
            GROUP BY number");

        foreach ($sqls as $sql) {
            $this->db->update('journal_postings', ["journal_type" => "GL - GL"], ["number" => $sql->number]);
        }

        echo "OK";
    }

    public function readCoa()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $category = base64_decode($this->input->get("category"));
        $records = array();

        if ($category == "GL - GL") {
            $sql = $this->db->query("SELECT account_number, account_name, account_number as number, account_name as name, local_debit as debit, local_kredit as credit
                FROM account_coa 
                WHERE (account_number like '%$post%' or account_name like '%$post%') and account_number NOT IN (SELECT number FROM account_group_details) and account_number NOT IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','27000001') ORDER BY account_number");
            $records = $sql->result_array();
        } elseif ($category == "GL - SUPPLIER") {
            $sql = $this->db->query("SELECT a.* 
                FROM (
                    (SELECT account_number, account_name, account_number as number, account_name as name FROM account_coa WHERE account_number NOT IN (SELECT number FROM account_group_details) and account_number NOT IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','27000001') ORDER BY account_number ASC)
                UNION 
                    (SELECT b.account_number, b.account_name, c.id as number, c.name FROM account_balance_suppliers a JOIN account_coa b ON a.account_number = b.account_number JOIN suppliers c ON a.supplier_id = c.id ORDER BY c.name ASC)
                ) a WHERE a.account_number like '%$post%' or a.account_name like '%$post%' or a.number like '%$post%' or a.name like '%$post%' ORDER BY a.account_number");
            $records = $sql->result_array();
        } elseif ($category == "GL - CUSTOMER") {
            $sql = $this->db->query("SELECT a.* 
                FROM (
                    (SELECT account_number, account_name, account_number as number, account_name as name FROM account_coa WHERE account_number NOT IN (SELECT number FROM account_group_details) and account_number NOT IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','27000001') ORDER BY account_number ASC)
                UNION 
                    (SELECT b.account_number, b.account_name, c.id as number, CONCAT('(', a.plant, ') ', c.name) FROM account_balance_customers a JOIN account_coa b ON a.account_number = b.account_number JOIN customers c ON a.customer_id = c.id ORDER BY c.name ASC)
                ) a WHERE a.account_number like '%$post%' or a.account_name like '%$post%' or a.number like '%$post%' or a.name like '%$post%' ORDER BY a.account_number");
            $records = $sql->result_array();
        } elseif ($category == "GL - BANK") {
            $sql = $this->db->query("SELECT a.* 
                FROM (
                    (SELECT account_number, account_name, account_number as number, account_name as name FROM account_coa WHERE account_number NOT IN (SELECT number FROM account_group_details) and account_number NOT IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','27000001') ORDER BY account_number ASC)
                UNION 
                    (SELECT b.account_number, b.account_name, a.bank_account as number, a.bank_name as name FROM account_banks a JOIN account_coa b ON a.account_number = b.account_number ORDER BY a.bank_account ASC)
                ) a WHERE a.account_number like '%$post%' or a.account_name like '%$post%' or a.name like '%$post%' ORDER BY a.account_number");
            $records = $sql->result_array();
        } elseif ($category == "GL - ASSET") {
            $sql = $this->db->query("SELECT a.* 
                FROM (
                    (SELECT account_number, account_name, account_number as number, account_name as name FROM account_coa WHERE account_number IN (
                    '22000001','26000004','26000006','26000007','26000008','30000001','30000004','31000001','40000001','40000002',
                    '40000003','41000001','41000002','42000008','42000009','43000001','44000011','44000018','44000019','44000020',
                    '45000002','45000006','45000007','45000008','45000009','45000011','45000013','45000014','45000015','45000017',
                    '45000018','45000019','45000022','45000023','45000025','47000003','44000001','44000002') ORDER BY account_number ASC)
                UNION
                    (SELECT b.account_number, b.account_name, a.number, a.name FROM assets a JOIN item_familys b ON a.item_family_id = b.id JOIN account_coa c ON b.account_number = c.account_number ORDER BY a.number ASC)
                UNION
                    (SELECT c.account_number, c.account_name, a.number, a.name FROM asset_fixeds a JOIN item_familys b ON a.item_family_id = b.id JOIN account_coa c ON b.account_number = c.account_number WHERE a.status = 0 GROUP BY a.number ORDER BY a.number ASC)
                ) a WHERE a.account_number like '%$post%' or a.account_name like '%$post%' or a.number like '%$post%' or a.name like '%$post%' GROUP BY a.number ORDER BY a.account_number");
            $records = $sql->result_array();
        } elseif ($category == "ASSET - ASSET") {
            $sql = $this->db->query("SELECT a.* 
                FROM (
                    (SELECT c.account_number, c.account_name, a.number, a.name FROM asset_fixeds a JOIN item_familys b ON a.item_family_id = b.id JOIN account_coa c ON b.account_number = c.account_number WHERE a.status = 0 GROUP BY a.number ORDER BY a.number ASC)
                UNION 
                    (SELECT b.account_number, b.account_name, a.number, a.name FROM assets a JOIN item_familys b ON a.item_family_id = b.id JOIN account_coa c ON b.account_number = c.account_number WHERE a.request_no = 'Additional' and a.status = 0 ORDER BY a.number ASC)
                ) a WHERE a.number like '%$post%' or a.name like '%$post%' ORDER BY a.number");
            $records = $sql->result_array();
        } elseif ($category == "FAR - SUPPLIER") {
            $sql = $this->db->query("SELECT a.* 
                FROM (
                    (SELECT c.account_number, c.account_name, a.number, a.name FROM asset_fixeds a JOIN item_familys b ON a.item_family_id = b.id JOIN account_coa c ON b.account_number = c.account_number WHERE a.status = 0 GROUP BY a.number ORDER BY a.number ASC)
                UNION
                    (SELECT b.account_number, b.account_name, a.number, a.name FROM assets a JOIN item_familys b ON a.item_family_id = b.id JOIN account_coa c ON b.account_number = c.account_number ORDER BY a.number ASC)
                UNION 
                    (SELECT b.account_number, b.account_name, c.id as number, c.name FROM account_balance_suppliers a JOIN account_coa b ON a.account_number = b.account_number JOIN suppliers c ON a.supplier_id = c.id ORDER BY c.name ASC)
                ) a WHERE a.number like '%$post%' or a.name like '%$post%' ORDER BY a.number");
            $records = $sql->result_array();
        }

        echo json_encode($records);
    }

    public function readJournalType()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select('*');
        $this->db->from('journal_types');
        $this->db->like('name', $post);
        $this->db->order_by('name', 'asc');
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

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_journal_type_id = base64_decode($this->input->get('filter_journal_type_id'));
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
            $this->db->select('a.journal_date, a.journal_type, a.number, a.trans_date, a.journal_type_id, b.name as journal_type_name, a.modul, a.remarks, a.currency, a.rates, a.posting, a.status_bank, a.status_cl,
                a.created_by, a.created_date, a.updated_by, a.updated_date, a.approved, a.approved_to, a.approved_by, a.approved_date, a.document_no, a.trans_date as document_date, a.tds,
                SUM(a.original_debit) as original_debit, 
                SUM(a.original_credit) as original_credit, 
                SUM(a.local_debit) as local_debit, 
                SUM(a.local_credit) as local_credit');
            $this->db->from('journal_postings a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            $this->db->where('a.modul', "ADJUSTMENT");
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
            }
            $this->db->like('a.journal_type_id', $filter_journal_type_id);
            $this->db->like('a.journal_type', $filter_journal_type);
            $this->db->like('a.modul', $filter_modul);
            $this->db->like('a.number', $filter_voucher);
            $this->db->group_by('a.number');
            $this->db->order_by('a.journal_date', 'desc');

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
        $this->db->select('a.*, b.account_name');
        $this->db->from('journal_postings a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.number', $number);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('a.document_no', 'asc');
        $this->db->order_by('a.journal_date', 'asc');
        $this->db->order_by('a.account_number', 'asc');
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
                "currency" => $record['currency'],
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

        //Select Query
        $this->db->select("*,
            (CASE WHEN debitcredit = 'DEBIT' THEN (original_debit + taxes_amount) ELSE 0 END) as original_debit,
            (CASE WHEN debitcredit = 'CREDIT' THEN (original_credit + taxes_amount) ELSE 0 END) as original_credit,
            (CASE WHEN debitcredit = 'DEBIT' THEN (local_debit + (taxes_amount * rates)) ELSE 0 END) as local_debit, 
            (CASE WHEN debitcredit = 'CREDIT' THEN (local_credit + (taxes_amount * rates)) ELSE 0 END) as local_credit");
        $this->db->from('journal_postings');
        $this->db->where('number', $number);
        $this->db->group_start();
        $this->db->where_not_in('account_number', ["20100003", "20100004", "20100005", "17000008", "17000009", "17000010", "17000011", "17000012", "17000013"]);
        $this->db->or_where("taxes = ''");
        $this->db->group_end();

        //Total Data
        $totalRows = $this->db->count_all_results('', false);
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

    public function reverse()
    {
        $number = base64_decode($this->input->get('number'));
        $journals = $this->crud->reads("journal_postings", [], ["number" => $number]);

        $data = [];
        $total_debit = 0;
        $total_credit = 0;
        $total_ori_debit = 0;
        $total_ori_credit = 0;
        foreach ($journals as $journal) {
            if ($journal->local_debit > 0) {
                $original_debit = 0;
                $original_credit = $journal->original_debit;
                $local_debit = 0;
                $local_credit = $journal->local_debit;
            } else {
                $original_debit = $journal->original_credit;
                $original_credit = 0;
                $local_debit = $journal->local_credit;
                $local_credit = 0;
            }

            $data[] = array(
                // "id" => $journal->id,
                "description" => $journal->description,
                "account_number" => $journal->account_number,
                "account_name" => $journal->account_name,
                "company_id" => $journal->company_id,
                "company_name" => $journal->company_name,
                "currency" => $journal->currency,
                "amount" => $journal->amount,
                "taxes" => $journal->taxes,
                "qty" => $journal->qty,
                "rates" => $journal->rates,
                "taxes_amount" => $journal->taxes_amount,
                "debitcredit" => $journal->debitcredit,
                "original_debit" => $original_debit,
                "original_credit" => $original_credit,
                "local_debit" => $local_debit,
                "local_credit" => $local_credit,
            );

            $total_ori_debit += $original_debit;
            $total_ori_credit += $original_credit;
            $total_debit += $local_debit;
            $total_credit += $local_credit;
        }

        $result['rows'] = $data;
        $result['footer'] = [[
            "currency" => "TOTAL",
            "original_debit" => $total_ori_debit,
            "original_credit" => $total_ori_credit,
            "local_debit" => $total_debit,
            "local_credit" => $total_credit,
        ]];
        echo json_encode($result);
    }

    public function calculateJournal()
    {
        $jsonDatas = json_decode(file_get_contents("json/journal_posting_adjustments.json"), true);

        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $arrTax = array();

        foreach ($jsonDatas as $post) {
            $taxes = $this->crud->reads('taxes', [], ["number" => $post['taxes']]);

            $total_tax_ori_debit = 0;
            $total_tax_ori_credit = 0;
            $total_tax_debit = 0;
            $total_tax_credit = 0;
            foreach ($taxes as $tax) {
                $taxx = ($tax->cgst + $tax->sgst + $tax->igst);
                if ($tax->number != "D1") {
                    if ($tax->number == "C1" || $tax->number == "C2") {
                        if ($post['local_debit'] > 0) {
                            $original_debit = ((($post['original_debit'] * $taxx) / 100));
                            $original_credit = 0;
                            $local_debit = (($post['local_debit'] * $taxx) / 100);
                            $local_credit = 0;
                        } else {
                            $local_debit = 0;
                            $local_credit = (($post['local_credit'] * $taxx) / 100);
                            $original_debit = 0;
                            $original_credit = ((($post['original_credit'] * $taxx) / 100));
                        }
                    } else {
                        if ($post['local_debit'] > 0) {
                            $local_debit = (round(((($post['original_debit'] - $post['taxes_amount']) * $taxx) / 100), 2) * $post['rates']);
                            $local_credit = 0;
                            $original_debit = round(((($post['original_debit'] - $post['taxes_amount']) * $taxx) / 100), 2);
                            $original_credit = 0;
                        } else {
                            $local_debit = 0;
                            $local_credit = (round(((($post['original_credit'] - $post['taxes_amount']) * $taxx) / 100), 2) * $post['rates']);
                            $original_debit = 0;
                            $original_credit = round(((($post['original_credit'] - $post['taxes_amount']) * $taxx) / 100), 2);
                        }
                    }

                    if ($tax->account_number_1 != "") {
                        $arrTax[] = array(
                            "account_number" => $tax->account_number_1,
                            "account_name" => $tax->account_name_1,
                            "original_debit" => $original_credit,
                            "original_credit" => $original_debit,
                            "local_debit" => $local_credit,
                            "local_credit" => $local_debit,
                        );

                        $total_tax_ori_debit += $original_credit;
                        $total_tax_ori_credit += $original_debit;
                        $total_tax_debit += $local_credit;
                        $total_tax_credit += $local_debit;
                    }

                    $arrTax[] = array(
                        "account_number" => $tax->account_number,
                        "account_name" => $tax->account_name,
                        "original_debit" => $original_debit,
                        "original_credit" => $original_credit,
                        "local_debit" => $local_debit,
                        "local_credit" => $local_credit,
                    );

                    $total_tax_ori_debit += $original_debit;
                    $total_tax_ori_credit += $original_credit;
                    $total_tax_debit += $local_debit;
                    $total_tax_credit += $local_credit;
                }
            }

            if ($post['taxes'] == "C1" || $post['taxes'] == "C2") {
                if ($post['local_debit'] > 0) {
                    $original_debit = ($post['original_debit']);
                    $original_credit = 0;
                    $local_debit = ($post['local_debit']);
                    $local_credit = 0;
                } else {
                    $original_debit = 0;
                    $original_credit = ($post['original_credit']);
                    $local_debit = 0;
                    $local_credit = ($post['local_credit']);
                }
            } else {
                if ($post['local_debit'] > 0) {
                    $original_debit = round($post['original_debit'] - $post['taxes_amount'], 2);
                    $original_credit = 0;
                    $local_debit = round($post['local_debit'] - round($post['taxes_amount'] * $post['rates'], 2), 2);
                    $local_credit = 0;
                } else {
                    $original_debit = 0;
                    $original_credit = round($post['original_credit'] - $post['taxes_amount'], 2);
                    $local_debit = 0;
                    $local_credit = round($post['local_credit'] - round($post['taxes_amount'] * $post['rates'], 2), 2);
                }
            }

            $arr[] = array(
                "account_number" => $post['account_number'],
                "account_name" => $post['account_name'],
                "original_debit" => $original_debit,
                "original_credit" => $original_credit,
                "local_debit" => $local_debit,
                "local_credit" => $local_credit,
            );

            $grand_original_debit += $original_debit + $total_tax_ori_debit;
            $grand_original_credit += $original_credit + $total_tax_ori_credit;
            $grand_local_debit += $local_debit + $total_tax_debit;
            $grand_local_credit += $local_credit + $total_tax_credit;
        }

        $footer[] = array(
            "account_number" => "TOTAL",
            "original_debit" => $grand_original_debit,
            "original_credit" => $grand_original_credit,
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
        );

        $rows = array_merge($arr, $arrTax);
        echo json_encode(['rows' => $rows, 'footer' => $footer]);
    }

    public function create()
    {
        if ($this->input->post()) {
            $jsonData = file_get_contents("php://input");
            $data = json_decode($jsonData, true);
            $dataJournals = $data['dataJournals'];

            $dataAssets = array();
            $dataFinal = array();
            foreach ($dataJournals as $post) {
                $taxes = $this->crud->reads('taxes', [], ["number" => $post['taxes']]);

                if (empty($post['id'])) {
                    if ($post['proses'] != "REVERSE") {
                        foreach ($taxes as $tax) {
                            $taxx = ($tax->cgst + $tax->sgst + $tax->igst);
                            if ($tax->number != "D1") {
                                if ($tax->number == "C1" || $tax->number == "C2") {
                                    if ($post['local_debit'] > 0) {
                                        $original_debit = ((($post['original_debit'] * $taxx) / 100));
                                        $original_credit = 0;
                                        $local_debit = (($post['local_debit'] * $taxx) / 100);
                                        $local_credit = 0;
                                    } else {
                                        $original_debit = 0;
                                        $original_credit = ((($post['original_credit'] * $taxx) / 100));
                                        $local_debit = 0;
                                        $local_credit = (($post['local_credit'] * $taxx) / 100);
                                    }
                                } else {
                                    if ($post['local_debit'] > 0) {
                                        $local_debit = (round(((($post['original_debit'] - $post['taxes_amount']) * $taxx) / 100), 2) * $post['rates']);
                                        $local_credit = 0;
                                        $original_debit = (((($post['original_debit'] - $post['taxes_amount']) * $taxx) / 100));
                                        $original_credit = 0;
                                    } else {
                                        $local_debit = 0;
                                        $local_credit = (round(((($post['original_credit'] - $post['taxes_amount']) * $taxx) / 100), 2) * $post['rates']);
                                        $original_debit = 0;
                                        $original_credit = (((($post['original_credit'] - $post['taxes_amount']) * $taxx) / 100));
                                    }
                                }

                                if ($tax->account_number_1 != "") {
                                    $dataFinal[] = array(
                                        "journal_date" => $post['journal_date'],
                                        "modul" => $post['modul'],
                                        "journal_type_id" => $post['journal_type_id'],
                                        "journal_type" => $post['journal_type'],
                                        "number" => $post['number'],
                                        "remarks" => $post['remarks'],
                                        "trans_date" => $post['document_date'],
                                        "document_no" => $post['document_no'],
                                        "invoice_no" => $post['document_no'],
                                        "company_id" => $post['company_id'],
                                        "company_name" => $post['company_name'],
                                        "taxes" => $post['taxes'],
                                        "taxes_amount" => ($original_debit + $original_credit),
                                        "amount" => $post['amount'],
                                        "debitcredit" => $post['debitcredit'],
                                        "qty" => $post['qty'],
                                        "account_number" => $tax->account_number_1,
                                        "account_name" => $tax->account_name_1,
                                        "description" => $tax->account_name_1,
                                        "currency" => $post['currency'],
                                        "local_debit" => $local_credit,
                                        "local_credit" => $local_debit,
                                        "original_debit" => $original_credit,
                                        "original_credit" => $original_debit,
                                        "rates" => $post['rates'],
                                    );
                                }

                                $dataFinal[] = array(
                                    "journal_date" => $post['journal_date'],
                                    "modul" => $post['modul'],
                                    "journal_type_id" => $post['journal_type_id'],
                                    "journal_type" => $post['journal_type'],
                                    "number" => $post['number'],
                                    "remarks" => $post['remarks'],
                                    "trans_date" => $post['document_date'],
                                    "document_no" => $post['document_no'],
                                    "invoice_no" => $post['document_no'],
                                    "company_id" => $post['company_id'],
                                    "company_name" => $post['company_name'],
                                    "taxes" => $post['taxes'],
                                    "taxes_amount" => ($original_debit + $original_credit),
                                    "amount" => $post['amount'],
                                    "debitcredit" => $post['debitcredit'],
                                    "qty" => $post['qty'],
                                    "account_number" => $tax->account_number,
                                    "account_name" => $tax->account_name,
                                    "description" => $tax->account_name,
                                    "currency" => $post['currency'],
                                    "local_debit" => $local_debit,
                                    "local_credit" => $local_credit,
                                    "original_debit" => $original_debit,
                                    "original_credit" => $original_credit,
                                    "rates" => $post['rates'],
                                );
                            }
                        }
                    }
                }

                if (empty($post['id']) && $post['proses'] != "REVERSE") {
                    if ($post['taxes'] == "C1" || $post['taxes'] == "C2") {
                        if ($post['local_debit'] > 0) {
                            $local_debit = ($post['local_debit']);
                            $local_credit = 0;
                            $original_debit = ($post['original_debit']);
                            $original_credit = 0;
                        } else {
                            $local_debit = 0;
                            $local_credit = ($post['local_credit']);
                            $original_debit = 0;
                            $original_credit = ($post['original_credit']);
                        }
                    } else {
                        if ($post['local_debit'] > 0) {
                            $local_debit = ($post['local_debit'] - round($post['taxes_amount'] * $post['rates'], 2));
                            $local_credit = 0;
                            $original_debit = ($post['original_debit'] - $post['taxes_amount']);
                            $original_credit = 0;
                        } else {
                            $local_debit = 0;
                            $local_credit = ($post['local_credit'] - round($post['taxes_amount'] * $post['rates'], 2));
                            $original_debit = 0;
                            $original_credit = ($post['original_credit'] - $post['taxes_amount']);
                        }
                    }
                } else {
                    if ($post['local_debit'] > 0) {
                        $local_debit = ($post['local_debit']);
                        $local_credit = 0;
                        $original_debit = ($post['original_debit']);
                        $original_credit = 0;
                    } else {
                        $local_debit = 0;
                        $local_credit = ($post['local_credit']);
                        $original_debit = 0;
                        $original_credit = ($post['original_credit']);
                    }
                }

                $dataFinal[] = array(
                    "journal_date" => $post['journal_date'],
                    "modul" => $post['modul'],
                    "journal_type_id" => $post['journal_type_id'],
                    "journal_type" => $post['journal_type'],
                    "number" => $post['number'],
                    "remarks" => $post['remarks'],
                    "trans_date" => $post['document_date'],
                    "document_no" => $post['document_no'],
                    "invoice_no" => $post['document_no'],
                    "company_id" => $post['company_id'],
                    "company_name" => $post['company_name'],
                    "taxes" => $post['taxes'],
                    "taxes_amount" => $post['taxes_amount'],
                    "amount" => $post['amount'],
                    "debitcredit" => $post['debitcredit'],
                    "qty" => $post['qty'],
                    "account_number" => $post['account_number'],
                    "account_name" => $post['account_name'],
                    "description" => $post['description'],
                    "currency" => $post['currency'],
                    "local_debit" => $local_debit,
                    "local_credit" => $local_credit,
                    "original_debit" => $original_debit,
                    "original_credit" => $original_credit,
                    "rates" => $post['rates'],
                );

                // if(empty($post['id'])){

                if (isset($dataAssets[$post['company_id']])) {
                    $amount += ($local_debit - $local_credit);
                } else {
                    $amount = ($local_debit - $local_credit);
                }

                $dataAssets[$post['company_id']] = array(
                    "number" => $post['company_id'],
                    "name" => $post['company_name'],
                    "trans_date" => $post['document_date'],
                    "currency" => $post['currency'],
                    "price" => $amount,
                    "document_no" => $post['document_no'],
                    "gl_no" => $post['number'],
                    "qty" => $post['qty'],
                    "journal_date" => $post['journal_date']
                );
            }

            $this->db->trans_start();
            $this->delete(["number" => $dataJournals[0]['number']]);

            foreach ($dataFinal as $dataf) {
                $this->crud->create('journal_postings', $dataf);
            }

            if ($post['journal_type'] == "GL - ASSET") {
                $this->createAsset($dataAssets);
            }

            if ($post['journal_type'] == "ASSET - ASSET") {
                $this->createAsset($dataAssets);
            }

            if ($post['journal_type'] == "FAR - SUPPLIER") {
                $this->createAsset($dataAssets);
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

    public function createAsset($dataAssets)
    {
        foreach ($dataAssets as $dataAsset) {
            $number = $dataAsset['number'];
            $name = $dataAsset['name'];
            $number = $dataAsset['number'];
            $trans_date = $dataAsset['trans_date'];
            $currency = $dataAsset['currency'];
            $price = $dataAsset['price'];
            $document_no = $dataAsset['document_no'];
            $gl_no = $dataAsset['gl_no'];
            $qty = $dataAsset['qty'];
            $journal_date = $dataAsset['journal_date'];

            $asset = $this->crud->read("assets", [], ["number" => $number, "status" => 0]);
            if (!empty($asset)) {
                $item_family = $this->crud->read("item_familys", [], ["id" => $asset->item_family_id]);
                $estimate_year = $item_family->usefull;
                if (empty($item_family->shift)) {
                    $shift = 1;
                } else {
                    $shift = $item_family->shift;
                }
                $estimate_month = ($estimate_year * 12);
                // $depreciation = ($price / $estimate_month);
                $expired_date = date("Y-m-d", strtotime("+" . $estimate_year . " year", strtotime($trans_date)));
                $date_from  = date_create($trans_date);
                $date_to  = date_create($expired_date);
                $diff  = date_diff($date_from, $date_to);
                $estimate_day = $diff->d;
                if ($shift == "1") {
                    $factor = 1;
                } else {
                    $factor = 2;
                }

                $asset_fixeds = $this->crud->read("asset_fixeds", [], ["number" => $number, "purchase_invoice_no" => $document_no]);
                $dataAsset = array(
                    "item_family_id" => @$asset->item_family_id,
                    "purchase_invoice_no" => $document_no,
                    "voucher_no" => $gl_no,
                    "supplier_id" => "",
                    "supplier_name" => "",
                    "item_rm_id" => "",
                    "number" => $number,
                    "name" => $name,
                    "shift" => $shift,
                    "trans_date" => $trans_date,
                    "qty" => (@$asset->qty),
                    "currency" => @$currency,
                    "cost" => $price,
                    "estimate_month" => $estimate_month,
                    "estimate_year" => $estimate_year,
                    "residual" => (($price * 5) / 100),
                    "expired_date" => $expired_date,
                    // "depreciation" => $depreciation,
                    "acq_closing" => $price,
                    "remarks" => $document_no,
                    "method" => "JOURNAL ADJUSTMENT",
                    "departement" => "",
                    "total" => $price,
                    "uom" => @$asset->uom,
                    "posting_date" => $journal_date,
                    "capitalization_date" => $journal_date,
                    // "addition" => ($price - (($price * 5) / 100) / $estimate_year / 365 * $estimate_day * $factor),
                    "estimate_day" => $estimate_day,
                );

                if (empty($asset_fixeds)) {
                    $assets = $this->crud->create('asset_fixeds', $dataAsset);

                    if (@$asset->qty > 0) {
                        $status = 0;
                    } else {
                        $status = 1;
                    }

                    $this->db->update("assets", ["status" => $status], ["number" => $number]);
                    // die($assets);
                }
            } else {
                $asset_fixeds = $this->crud->read("asset_fixeds", [], ["number" => $number, "purchase_invoice_no" => $document_no]);
                if (!empty($asset_fixeds)) {
                    if ($price > 0) {
                        $qty = (@$asset_fixeds->qty + $qty);
                    } else {
                        $qty = (@$asset_fixeds->qty - $qty);
                    }

                    $item_family = $this->crud->read("item_familys", [], ["id" => $asset_fixeds->item_family_id]);
                    $estimate_year = @$item_family->usefull;
                    $estimate_month = ($estimate_year * 12);
                    $shift = @$item_family->shift;
                    $expired_date = date("Y-m-d", strtotime("+" . $estimate_year . " year", strtotime($trans_date)));
                    $date_from  = date_create($trans_date);
                    $date_to  = date_create($expired_date);
                    $diff  = date_diff($date_from, $date_to);
                    // $depreciation = ((@$asset_fixeds->cost + $price) / $estimate_month);
                    $estimate_day = $diff->d;
                    if ($shift == "1") {
                        $factor = 1;
                    } else {
                        $factor = 2;
                    }

                    if ($qty > 0) {
                        $status = 0;
                    } else {
                        $status = 1;
                    }

                    $dataAsset = array(
                        "qty" => $qty,
                        "cost" => (@$asset_fixeds->cost + $price),
                        "residual" => (((@$asset_fixeds->cost + $price) * 5) / 100),
                        "total" => (@$asset_fixeds->cost + $price),
                        "acq_closing" => (@$asset_fixeds->cost + $price),
                        "status" => $status
                    );

                    $this->db->update("asset_fixeds", $dataAsset, ["number" => $number, "purchase_invoice_no" => $document_no]);
                } else {
                    $asset_fixeds = $this->crud->read("asset_fixeds", [], ["number" => $number]);
                    if (!empty($asset_fixeds)) {
                        $dataAsset = array(
                            "item_family_id" => @$asset_fixeds->item_family_id,
                            "purchase_invoice_no" => $document_no,
                            "voucher_no" => $gl_no,
                            "supplier_id" => @$asset_fixeds->supplier_id,
                            "supplier_name" => @$asset_fixeds->supplier_name,
                            "item_rm_id" => "",
                            "number" => $number,
                            "name" => $name,
                            "shift" => @$asset_fixeds->shift,
                            "trans_date" => $trans_date,
                            "qty" => $qty,
                            "currency" => @$currency,
                            "cost" => $price,
                            "estimate_month" => @$asset_fixeds->estimate_month,
                            "estimate_year" => @$asset_fixeds->estimate_year,
                            "expired_date" => @$asset_fixeds->expired_date,
                            "remarks" => $document_no,
                            "method" => "JOURNAL ADJUSTMENT",
                            "departement" => "",
                            "uom" => @$asset_fixeds->uom,
                            "posting_date" => $journal_date,
                            "capitalization_date" => $journal_date,
                            "estimate_day" => @$asset_fixeds->estimate_day,
                        );

                        $this->crud->create('asset_fixeds', $dataAsset);
                    }
                }
            }
        }
    }

    public function createJson()
    {
        $jsonData = $this->input->post('jsonData');
        file_put_contents('json/journal_posting_adjustments.json', $jsonData);
    }

    public function delete($number = [])
    {
        if (empty($number)) {
            $data = $this->input->post();
        } else {
            $data = $number;
        }

        $journal = $this->crud->reads('journal_postings', [], $data);
        $send = $this->crud->delete('journal_postings', $data);

        foreach ($journal as $jr) {
            if ($jr->journal_type == "ASSET - ASSET" || $jr->journal_type == "FAR - SUPPLIER") {
                $asset_fixeds = $this->crud->read("asset_fixeds", [], ["number" => $jr->company_id, "purchase_invoice_no" => @$jr->document_no]);
                if (!empty($asset_fixeds)) {
                    $price = ($jr->local_credit - $jr->local_debit);

                    if ($price > 0) {
                        $qty = (@$asset_fixeds->qty + $jr->qty);
                    } else {
                        $qty = (@$asset_fixeds->qty - $jr->qty);
                    }

                    $item_family = $this->crud->read("item_familys", [], ["id" => $asset_fixeds->item_family_id]);
                    $estimate_year = @$item_family->usefull;
                    $estimate_month = ($estimate_year * 12);
                    $shift = @$item_family->shift;
                    $expired_date = date("Y-m-d", strtotime("+" . $estimate_year . " year", strtotime($jr->trans_date)));
                    $date_from  = date_create($jr->trans_date);
                    $date_to  = date_create($expired_date);
                    $diff  = date_diff($date_from, $date_to);
                    $depreciation = ((@$asset_fixeds->cost + $price) / $estimate_month);
                    $estimate_day = $diff->d;
                    if ($shift == "1") {
                        $factor = 1;
                    } else {
                        $factor = 2;
                    }

                    $dataAsset = array(
                        "qty" => $qty,
                        "cost" => (@$asset_fixeds->cost + $price),
                        "residual" => (((@$asset_fixeds->cost + $price) * 5) / 100),
                        "total" => (@$asset_fixeds->cost + $price),
                        "depreciation" => $depreciation,
                        "acq_closing" => (@$asset_fixeds->cost + $price),
                        "addition" => ((@$asset_fixeds->cost + $price) - (((@$asset_fixeds->cost + $price) * 5) / 100) / $estimate_year / 365 * $estimate_day * $factor),
                        "status" => 0
                    );

                    $this->db->update("asset_fixeds", $dataAsset, ["number" => @$jr->company_id, "purchase_invoice_no" => $jr->document_no]);
                }

                $asset = $this->crud->read("assets", [], ["number" => @$jr->company_id]);
                if (!empty($asset)) {
                    $this->db->delete("asset_fixeds", ["number" => @$jr->company_id, "purchase_invoice_no" => $jr->document_no]);
                    $this->db->update("assets", ["status" => 0], ["number" => @$jr->company_id]);
                }
            } else {
                $this->db->delete("asset_fixeds", ["number" => @$jr->company_id, "purchase_invoice_no" => $jr->document_no]);
                $this->db->update("assets", ["status" => 0], ["number" => @$jr->company_id]);
            }
        }

        if (empty($number)) {
            echo $send;
        }
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        $datenow    = "GL" . date("ym");
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM journal_postings WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }

        $number = $datenow . $autoID;

        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $number,
                'journal_date' => $data->val($i, 2),
                'journal_type_number' => $data->val($i, 3),
                'modul' => $data->val($i, 4),
                'document_no' => $data->val($i, 5),
                'invoice_no' => $data->val($i, 6),
                'company_name' => $data->val($i, 7),
                'trans_date' => $data->val($i, 8),
                'description' => $data->val($i, 9),
                'account_number' => $data->val($i, 10),
                'account_name' => $data->val($i, 11),
                'currency' => $data->val($i, 12),
                'original_debit' => $data->val($i, 13),
                'original_credit' => $data->val($i, 14),
                'rates' => $data->val($i, 15),
                'local_debit' => $data->val($i, 16),
                'local_credit' => $data->val($i, 17)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/journal_postings.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/journal_postings.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/journal_postings.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            //Cek Process Number
            $journal_types = $this->crud->read('journal_types', [], ["number" => $data['journal_type_number']]);

            if (empty($journal_types->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Journal Type Code " . $data['journal_type_number'] . " Not Found", "theme" => "error"));
            } else {
                $postFinal = array(
                    'number' => $data['number'],
                    'journal_date' => $data['journal_date'],
                    'journal_type_id' => $journal_types->id,
                    'modul' => $data['modul'],
                    'document_no' => $data['document_no'],
                    'invoice_no' => $data['invoice_no'],
                    'company_name' => $data['company_name'],
                    'trans_date' => $data['trans_date'],
                    'description' => $data['description'],
                    'account_number' => $data['account_number'],
                    'account_name' => $data['account_name'],
                    'currency' => $data['currency'],
                    'original_debit' => $data['original_debit'],
                    'original_credit' => $data['original_credit'],
                    'rates' => $data['rates'],
                    'local_debit' => $data['local_debit'],
                    'local_credit' => $data['local_credit']
                );

                $send   = $this->crud->create('journal_postings', $postFinal);
                echo $send;
            }
        }
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
        $filter_journal_type_id = base64_decode($this->input->get('filter_journal_type_id'));
        $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
        $filter_modul = base64_decode($this->input->get('filter_modul'));
        $filter_voucher = base64_decode($this->input->get('filter_voucher'));

        $this->db->select('a.*, b.name as journal_type_name');
        $this->db->from('journal_postings a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id');
        $this->db->where('a.modul', 'ADJUSTMENT');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
        }
        $this->db->like('a.journal_type_id', $filter_journal_type_id);
        $this->db->like('a.journal_type', $filter_journal_type);
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
                <th rowspan="2">Journal Category</th>
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
                    <td>' . $data['journal_type'] . '</td>
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
