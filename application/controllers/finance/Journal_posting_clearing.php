<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Journal_posting_clearing extends CI_Controller
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
            $this->load->view('finance/journal_posting_clearing');
            //$this->load->view('maintenance');
        } else {
            redirect('error_access');
        }
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

    public function readCompany($type)
    {
        $type = base64_decode($type);
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        if ($type == "CL - SUPPLIER") {
            $suppliers = $this->crud->query("SELECT * FROM suppliers WHERE id like '%$post%' or name like '%$post%' ORDER BY id ASC");
            echo json_encode($suppliers);
        } else {
            $customers = $this->crud->query("SELECT CONCAT(a.id, '|', b.plant) as id, a.number, a.name, b.plant FROM customers a JOIN customer_address b ON a.id = b.customer_id 
                WHERE a.id like '%$post%' or a.name like '%$post%' or b.plant like '%$post%'
                ORDER BY a.name, b.plant ASC");
            echo json_encode($customers);
        }
    }

    public function readCompany2()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $suppliers = $this->crud->query("SELECT id, number, name, '' as plant FROM suppliers WHERE id like '%$post%' or name like '%$post%' ORDER BY id ASC");

        $customers = $this->crud->query("SELECT CONCAT(a.id, '|', b.plant) as id, a.number, a.name, b.plant FROM customers a JOIN customer_address b ON a.id = b.customer_id 
            WHERE a.id like '%$post%' or a.name like '%$post%' or b.plant like '%$post%'
            ORDER BY a.name, b.plant ASC");

        $merges = array_merge($suppliers, $customers);
        echo json_encode($merges);
    }

    public function readDocuments($company_id)
    {
        $company_id = base64_decode($company_id);
        $explode = explode('|', $company_id);
        $company_id = $explode[0];
        $plant = @$explode[1];
        $customer = $this->crud->read("customers", [], ["id" => $company_id]);
        if ($customer) {
            $company_name = "(" . $plant . ") " . @$customer->name;
        } else {
            $company_name = "";
        }

        $sql_0 = $this->db->query("SELECT
                'BS' AS source,
                a.id as documents,
                a.document_no AS invoice_no,
                a.start_date AS invoice_date,
                '-' AS payment_due,
                a.id AS document_no,
                'BALANCE' as description,
                '-' AS voucher_date,
                '-' AS voucher_no,
                a.account_number,
                'INR' as currency,
                a.balance_local as amount
            FROM account_balance_suppliers a
            JOIN suppliers b ON a.supplier_id = b.id
            JOIN account_coa c ON a.account_number = c.account_number
            WHERE a.supplier_id = '$company_id' and a.status = '0'
            GROUP BY a.id");
        $balance_suppliers = $sql_0->result_array();

        $sql_00 = $this->db->query("SELECT
                'BC' AS source,
                a.id as documents,
                'BALANCE CUSTOMER' AS invoice_no,
                a.start_date AS invoice_date,
                '-' AS payment_due,
                a.id AS document_no,
                'BALANCE' as description,
                '-' AS voucher_date,
                '-' AS voucher_no,
                a.account_number,
                'INR' as currency,
                a.balance_local as amount
            FROM account_balance_customers a
            JOIN customers b ON a.customer_id = b.id
            JOIN account_coa c ON a.account_number = c.account_number
            WHERE a.customer_id = '$company_id' AND a.plant = '$plant' and a.status = '0'
            GROUP BY a.id");
        $balance_customers = $sql_00->result_array();

        $sql_1 = $this->db->query("SELECT
                'AR' AS source,
                a.receipt_no as documents,
                a.sales_invoice AS invoice_no,
                a.receipt_date AS invoice_date,
                '-' AS payment_due,
                a.receipt_no AS document_no,
                b.description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                a.currency,
                ((CASE WHEN a.account_type = 'DEBIT' THEN SUM(a.receipt) ELSE 0 END) - (CASE WHEN a.account_type = 'CREDIT' THEN SUM(a.receipt) ELSE 0 END)) as amount
            FROM ar_receipts a
            LEFT JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('27000001') 
                GROUP BY document_no, account_number) b ON a.receipt_no = b.document_no
            WHERE a.customer_id = '$company_id' AND a.plant = '$plant' AND a.sales_invoice NOT IN (SELECT DISTINCT number FROM sales_invoices WHERE `status` = 1) AND a.status_cl = '0'
            GROUP BY a.receipt_no");
        $ar_receipts = $sql_1->result_array();

        $sql_2 = $this->db->query("SELECT
                'AP' AS source,
                a.payment_no as documents,
                a.payment_date AS trans_date,
                '-' AS due_date,
                a.payment_no AS document_no,
                a.supplier_invoice AS invoice_no,
                a.remarks AS description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                'INR' as currency,
                ((b.local_credit - b.local_debit) + coalesce(c.payment_2, 0)) as amount
            FROM ap_payments a
            LEFT JOIN (SELECT journal_date, number, document_no, account_number, 
                SUM(original_debit) AS original_debit, 
                SUM(original_credit) AS original_credit,
                SUM(local_debit) AS local_debit, 
                SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
                GROUP BY document_no, account_number) b ON a.payment_no = b.document_no
            LEFT JOIN (SELECT payment_no, SUM(payment) as payment, SUM(payment * rate) as payment_2 FROM ap_payments WHERE purchase_invoice IN (SELECT DISTINCT number FROM purchase_invoices) GROUP BY payment_no) c ON a.payment_no = c.payment_no
            WHERE a.supplier_id = '$company_id' 
            AND a.status_cl = '0' 
            AND a.purchase_invoice NOT IN (SELECT DISTINCT number FROM purchase_invoices WHERE `status` = 1)
            AND ((b.original_credit - b.original_debit) + coalesce(c.payment, 0)) != 0
            GROUP BY a.payment_no");
        $ap_payments = $sql_2->result_array();

        $sql_3 = $this->db->query("SELECT
                'AD' AS source,
                a.number as documents,
                a.trans_date,
                '-' AS due_date,
                a.document_no,
                a.invoice_no,
                a.description,
                a.journal_date AS voucher_date,
                a.number AS voucher_no,
                a.account_number,
                'INR' as currency,
                (SUM(a.local_credit) - SUM(a.local_debit)) AS amount
            FROM journal_postings a
            WHERE a.company_id = '$company_id' and a.company_name like '%$company_name%'
            AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') AND a.status_cl = '0'
            AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006','28000004','28000007')
            GROUP BY a.number");
        $journal_ap = $sql_3->result_array();

        $sql_33 = $this->db->query("SELECT
                'AD' AS source,
                a.number as documents,
                a.trans_date,
                '-' AS due_date,
                a.document_no,
                a.invoice_no,
                a.description,
                a.journal_date AS voucher_date,
                a.number AS voucher_no,
                a.account_number,
                'INR' as currency,
                (SUM(a.local_debit) - SUM(a.local_credit)) AS amount
            FROM journal_postings a
            WHERE a.company_id = '$company_id' and a.company_name like '%$company_name%'
            AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') AND a.status_cl = '0'
            AND a.account_number IN ('27000001')
            GROUP BY a.number");
        $journal_ar = $sql_33->result_array();

        $sql_4 = $this->db->query("SELECT 
            'PI' AS source,
            a.number as documents,
            a.invoice_date as trans_date,
            a.due_date,
            a.number AS document_no,
            a.invoice_no,
            b.description,
            b.journal_date AS voucher_date,
            b.number AS voucher_no,
            b.account_number,
            'INR' as currency,
            ((b.local_credit - b.local_debit) - (COALESCE(c.payment, 0) - COALESCE(d.payment, 0))) as amount
        FROM purchase_invoices a
        JOIN (SELECT journal_date, number, company_id, document_no, account_number, description, 
                SUM(original_debit) AS original_debit, 
                SUM(original_credit) AS original_credit,
                SUM(local_debit) AS local_debit, 
                SUM(local_credit) AS local_credit 
            FROM journal_postings 
            WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
            GROUP BY document_no, account_number) b ON a.number = b.document_no
        LEFT JOIN (SELECT purchase_invoice, SUM(payment) as payment FROM ap_payments WHERE account_type = 'DEBIT' GROUP BY purchase_invoice) c ON a.number = c.purchase_invoice
        LEFT JOIN (SELECT purchase_invoice, SUM(payment) as payment FROM ap_payments WHERE account_type = 'CREDIT' GROUP BY purchase_invoice) d ON a.number = d.purchase_invoice
        LEFT JOIN journal_clearings e ON b.document_no = e.document_no AND b.company_id = e.company_id AND b.description = e.description
        WHERE a.supplier_id = '$company_id' 
        AND ((b.original_credit - b.original_debit) - (COALESCE(c.payment, 0) - COALESCE(d.payment, 0))) != 0
        AND e.number is null
        GROUP BY a.number
        ORDER BY b.journal_date asc");
        $purchase_invoices = $sql_4->result_array();

        $sql_5 = $this->db->query("SELECT 
            'SI' AS source,
            a.number as documents,
            a.trans_date,
            a.due_date,
            a.number AS document_no,
            a.dn_number AS invoice_no,
            b.description,
            b.journal_date AS voucher_date,
            b.number AS voucher_no,
            b.account_number,
            'INR' as currency,
            (b.local_debit - b.local_credit) as amount
        FROM sales_invoices a
        JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
            FROM journal_postings 
            WHERE account_number IN ('27000001') 
            GROUP BY document_no, account_number) b ON a.number = b.document_no
        WHERE a.customer_id = '$company_id' AND a.plant = '$plant' and a.status = 0
        GROUP BY a.number
        ORDER BY b.journal_date ASC");
        $sales_invoices = $sql_5->result_array();

        $sql_6 = $this->db->query("SELECT
            'CL' AS source,
            a.id as documents,
            a.trans_date,
            '-' AS due_date,
            a.id as document_no,
            a.number as invoice_no,
            a.description,
            a.journal_date AS voucher_date,
            a.number AS voucher_no,
            a.account_number,
            'INR' as currency,
            (CASE WHEN a.account_number = '27000001' THEN (SUM(a.local_debit) - SUM(a.local_credit)) ELSE (SUM(a.local_credit) - SUM(a.local_debit)) END) as amount
        FROM journal_postings a
        WHERE a.company_id = '$company_id' and a.company_name like '%$company_name%'
        AND a.modul IN ('CLEARING') AND a.status_cl = '0'
        AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006','27000001','28000004','28000007')
        GROUP BY a.id");
        $clearing = $sql_6->result_array();

        $datas = array_merge($balance_suppliers, $balance_customers, $ar_receipts, $ap_payments, $journal_ap, $journal_ar, $purchase_invoices, $sales_invoices, $clearing);
        die(json_encode($datas));
    }

    public function number($id_menu, $journal_date)
    {
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
        $company = explode("|", base64_decode($get['company_id']));
        $company_id = $company[0];
        $plant = @$company[1];

        $customer = $this->crud->read("customers", [], ["id" => $company_id]);
        if ($customer) {
            $company_name = "(" . $plant . ") " . @$customer->name;
        } else {
            $company_name = "";
        }

        $document_no = explode(",", base64_decode($get['document_no']));
        $document_no_str = implode("','", $document_no);
        $document_no_str = "'" . $document_no_str . "'";

        $sql_0 = $this->db->query("SELECT
                'BS' AS source,
                '20241120000001' as journal_type_id,
                a.id AS document_no,
                a.start_date AS document_date,
                a.document_no AS invoice_no,
                '-' AS voucher_no,
                a.supplier_id as company_id,
                b.name as company_name,
                a.start_date AS invoice_date,
                'BALANCE' as description,
                a.account_number,
                c.account_name,
                'INR' as currency,
                a.balance_local as amount
            FROM account_balance_suppliers a
            JOIN suppliers b ON a.supplier_id = b.id
            JOIN account_coa c ON a.account_number = c.account_number
            WHERE a.id IN ($document_no_str) and a.supplier_id = '$company_id' and a.status = '0'
            GROUP BY a.id");
        $balance_suppliers = $sql_0->result_array();

        $sql_00 = $this->db->query("SELECT
                'BC' AS source,
                '20241120000001' as journal_type_id,
                a.id AS document_no,
                a.start_date AS document_date,
                'BALANCE CUSTOMER' AS invoice_no,
                '-' AS voucher_no,
                a.customer_id as company_id,
                CONCAT('(', a.plant, ') ', b.name) as company_name,
                a.start_date AS invoice_date,
                'BALANCE' as description,
                a.account_number,
                c.account_name,
                'INR' as currency,
                a.balance_local as amount
            FROM account_balance_customers a
            JOIN customers b ON a.customer_id = b.id
            JOIN account_coa c ON a.account_number = c.account_number
            WHERE a.id IN ($document_no_str) and a.customer_id = '$company_id' and a.plant = '$plant' and a.status = '0'
            GROUP BY a.id");
        $balance_customers = $sql_00->result_array();

        $sql_1 = $this->db->query("SELECT
                'AR' AS source,
                b.journal_type_id,
                a.receipt_no AS document_no,
                a.receipt_date AS document_date,
                a.sales_invoice AS invoice_no,
                b.number as voucher_no,
                a.customer_id AS company_id,
                CONCAT('(', a.plant, ') ', c.name) AS company_name,
                b.trans_date AS invoice_date,
                b.description,
                b.account_number,
                b.account_name,
                a.currency,
                ((CASE WHEN a.account_type = 'DEBIT' THEN SUM(a.receipt) ELSE 0 END) - (CASE WHEN a.account_type = 'CREDIT' THEN SUM(a.receipt) ELSE 0 END)) as amount
            FROM ar_receipts a
            JOIN (SELECT trans_date, journal_type_id, number, document_no, account_number, account_name, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('27000001') 
                GROUP BY document_no, account_number) b ON a.receipt_no = b.document_no
            JOIN customers c ON a.customer_id = c.id
            WHERE a.receipt_no IN ($document_no_str) and a.customer_id = '$company_id' and a.plant = '$plant' AND a.sales_invoice NOT IN (SELECT DISTINCT number FROM sales_invoices WHERE `status` = 1) AND a.status_cl = '0'
            GROUP BY a.receipt_no");
        $ar_receipts = $sql_1->result_array();

        $sql_2 = $this->db->query("SELECT
                'AP' AS source,
                b.journal_type_id,
                a.payment_no AS document_no,
                a.payment_date AS document_date,
                a.supplier_invoice AS invoice_no,
                b.number as voucher_no,
                c.id as company_id,
                c.name as company_name,
                b.trans_date as invoice_date,
                b.description,
                b.account_number,
                b.account_name,
                'INR' as currency,
                ((b.local_credit - b.local_debit) + coalesce(d.payment_2, 0)) as amount
            FROM ap_payments a
            JOIN (SELECT trans_date, journal_type_id, number, description, document_no, account_number, account_name,
                SUM(original_debit) AS original_debit, 
                SUM(original_credit) AS original_credit,
                SUM(local_debit) AS local_debit, 
                SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
                GROUP BY document_no, account_number) b ON a.payment_no = b.document_no
            JOIN suppliers c ON a.supplier_id = c.id
            LEFT JOIN (SELECT payment_no, SUM(payment) as payment, SUM(payment * rate) as payment_2 FROM ap_payments WHERE purchase_invoice IN (SELECT DISTINCT number FROM purchase_invoices) GROUP BY payment_no) d ON a.payment_no = d.payment_no
            WHERE a.payment_no IN ($document_no_str) and a.supplier_id = '$company_id' AND a.status_cl = '0' 
            AND a.purchase_invoice NOT IN (SELECT DISTINCT number FROM purchase_invoices WHERE `status` = 1)
            GROUP BY a.payment_no");
        $ap_payments = $sql_2->result_array();

        $sql_3 = $this->db->query("SELECT
                'AD' AS source,
                a.journal_type_id,
                a.document_no,
                a.journal_date as document_date,
                a.invoice_no,
                a.number AS voucher_no,
                a.company_id,
                a.company_name,
                a.trans_date as invoice_date,
                a.description,
                a.account_number,
                a.account_name,
                'INR' as currency,
                (SUM(a.local_credit) - SUM(a.local_debit)) AS amount
            FROM journal_postings a
            LEFT JOIN (SELECT company_id, company_name, document_no, invoice_no, description, SUM(local_debit) as cl_debit, SUM(local_credit) as cl_credit 
                FROM journal_postings 
                WHERE modul = 'CLEARING' 
                GROUP BY company_id, company_name, document_no, invoice_no) b ON a.company_id = b.company_id and a.company_name = b.company_name and a.document_no = b.document_no and a.invoice_no = b.invoice_no and a.description = b.description
            WHERE a.number IN ($document_no_str) and a.company_id = '$company_id' and a.company_name like '%$company_name%'
            AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') AND a.status_cl = '0'
            AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006','28000004','28000007')
            GROUP BY a.number");
        $journal_ap = $sql_3->result_array();

        $sql_33 = $this->db->query("SELECT
                'AD' AS source,
                a.journal_type_id,
                a.document_no,
                a.journal_date as document_date,
                a.invoice_no,
                a.number AS voucher_no,
                a.company_id,
                a.company_name,
                a.trans_date as invoice_date,
                a.description,
                a.account_number,
                a.account_name,
                'INR' as currency,
                (SUM(a.local_debit) - SUM(a.local_credit)) AS amount
            FROM journal_postings a
            LEFT JOIN (SELECT company_id, company_name, document_no, invoice_no, description, SUM(local_debit) as cl_debit, SUM(local_credit) as cl_credit 
                FROM journal_postings 
                WHERE modul = 'CLEARING' 
                GROUP BY company_id, company_name, document_no, invoice_no) b ON a.company_id = b.company_id and a.company_name = b.company_name and a.document_no = b.document_no and a.invoice_no = b.invoice_no and a.description = b.description
            WHERE a.number IN ($document_no_str) and a.company_id = '$company_id' and a.company_name like '%$company_name%'
            AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') AND a.status_cl = '0'
            AND a.account_number IN ('27000001')
            GROUP BY a.number");
        $journal_ar = $sql_33->result_array();

        $sql_4 = $this->db->query("SELECT 
            'PI' AS source,
            a.journal_type_id,
            a.number AS document_no,
            a.trans_date AS document_date,
            a.invoice_no,
            b.number AS voucher_no,
            a.supplier_id as company_id,
            c.name as company_name,
            a.invoice_date,
            b.description,
            b.account_number,
            b.account_name,
            'INR' as currency,
            ((b.local_credit - b.local_debit) - (COALESCE(d.payment, 0) - COALESCE(e.payment, 0))) as amount
        FROM purchase_invoices a
        JOIN (SELECT journal_date, number, document_no, account_number, account_name, description, 
            SUM(original_debit) AS original_debit, 
            SUM(original_credit) AS original_credit, 
            SUM(local_debit) AS local_debit, 
            SUM(local_credit) AS local_credit 
            FROM journal_postings 
            WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
            GROUP BY document_no, account_number) b ON a.number = b.document_no
        JOIN suppliers c ON a.supplier_id = c.id
        LEFT JOIN (SELECT purchase_invoice, SUM(payment) as payment FROM ap_payments WHERE account_type = 'DEBIT' GROUP BY purchase_invoice) d ON a.number = d.purchase_invoice
        LEFT JOIN (SELECT purchase_invoice, SUM(payment) as payment FROM ap_payments WHERE account_type = 'CREDIT' GROUP BY purchase_invoice) e ON a.number = e.purchase_invoice
        WHERE a.number IN ($document_no_str) and a.supplier_id = '$company_id' 
        AND ((b.original_credit - b.original_debit) - (COALESCE(d.payment, 0) - COALESCE(e.payment, 0))) != 0
        GROUP BY a.number
        ORDER BY b.journal_date asc");
        $purchase_invoices = $sql_4->result_array();

        $sql_5 = $this->db->query("SELECT 
            'SI' AS source,
            a.journal_type_id,
            a.number AS document_no,
            a.trans_date AS document_date,
            a.dn_number AS invoice_no,
            b.number AS voucher_no,
            a.customer_id as company_id,
            b.company_name,
            b.trans_date as invoice_date,
            b.description,
            b.account_number,
            b.account_name,
            'INR' as currency,
            (b.local_debit - b.local_credit) as amount
        FROM sales_invoices a
        JOIN (SELECT trans_date, number, document_no, company_name, account_number, account_name, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
            FROM journal_postings 
            WHERE account_number IN ('27000001') 
            GROUP BY document_no, account_number) b ON a.number = b.document_no
        WHERE a.number IN ($document_no_str) and a.customer_id = '$company_id' AND a.plant = '$plant' and a.status = 0
        GROUP BY a.number
        ORDER BY b.trans_date ASC");
        $sales_invoices = $sql_5->result_array();

        $sql_6 = $this->db->query("SELECT
                'CL' AS source,
                a.journal_type_id,
                a.id as document_no,
                a.journal_date as document_date,
                a.number as invoice_no,
                a.number AS voucher_no,
                a.company_id,
                a.company_name,
                a.trans_date as invoice_date,
                a.description,
                a.account_number,
                a.account_name,
                'INR' as currency,
                (CASE WHEN a.account_number = '27000001' THEN (SUM(a.local_debit) - SUM(a.local_credit)) ELSE (SUM(a.local_credit) - SUM(a.local_debit)) END) as amount
            FROM journal_postings a
            LEFT JOIN (SELECT company_id, company_name, document_no, invoice_no, description, SUM(local_debit) as cl_debit, SUM(local_credit) as cl_credit 
                FROM journal_postings 
                WHERE modul = 'CLEARING' 
                GROUP BY company_id, company_name, document_no, invoice_no) b ON a.company_id = b.company_id and a.company_name = b.company_name and a.document_no = b.document_no and a.invoice_no = b.invoice_no and a.description = b.description
            WHERE a.id IN ($document_no_str) and a.company_id = '$company_id' and a.company_name like '%$company_name%'
            AND a.modul IN ('CLEARING') AND a.status_cl = '0'
            AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006','27000001','28000004','28000007')
            GROUP BY a.id");
        $clearing = $sql_6->result_array();

        $datas = array_merge($balance_customers, $balance_suppliers, $ar_receipts, $ap_payments, $journal_ap, $journal_ar, $purchase_invoices, $sales_invoices, $clearing);

        $balance = 0;
        $arr = array();
        foreach ($datas as $data) {
            $balance += $data['amount'];
            $arr[] = array_merge($data, ["adjustment" => 0, "balance" => $balance]);
        }

        $result['total'] = count($arr);
        $result = array_merge($result, ['rows' => $arr]);
        echo json_encode($result);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_journal_type_id = base64_decode($this->input->get('filter_journal_type_id'));
            $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
            $filter_company = base64_decode($this->input->get('filter_company'));
            $filter_voucher = base64_decode($this->input->get('filter_voucher'));

            $explode = explode("|", $filter_company);
            $company_id = @$explode[0];
            $plant = @$explode[1];

            $customer = $this->crud->read("customers", [], ["id" => $company_id]);
            if ($customer) {
                $company_name = "(" . $plant . ") " . @$customer->name;
            } else {
                $company_name = "";
            }

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, c.local_debit, c.local_credit');
            $this->db->from('journal_clearings a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            $this->db->join("(SELECT number, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit FROM journal_postings GROUP BY number) c", 'a.number = c.number');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
            }
            $this->db->like('a.journal_type_id', $filter_journal_type_id);
            $this->db->like('a.number', $filter_voucher);
            $this->db->like('a.company_id', $company_id);
            $this->db->like('a.company_name', $company_name);
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

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('*');
        $this->db->from('journal_clearings');
        $this->db->where('number', $number);
        $this->db->order_by('id', 'asc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatableDetailJournals()
    {
        $number = base64_decode($this->input->get('number'));
        //Select Query
        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('number', $number);
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $local_debit = 0;
        $local_credit = 0;
        foreach ($records as $record) {
            $local_debit += ($record['local_debit']);
            $local_credit += ($record['local_credit']);
        }

        $footer[] = array(
            "account_number" => "TOTAL",
            "local_debit" => $local_debit,
            "local_credit" => $local_credit
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
            // "original_debit" => $grand_original_debit,
            // "original_credit" => $grand_original_credit,
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
        );

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records, 'footer' => $footer]);
        echo json_encode($result);
    }

    public function calculates()
    {
        $jsonDatas = json_decode(file_get_contents("json/journal_posting_adjustments.json"), true);
        $balance = 0;
        foreach ($jsonDatas as $post) {
            $balance += ($post['amount'] + $post['adjustment']);

            unset($post['balance']);
            $arr[] = array_merge($post, ["balance" => $balance]);
        }

        echo json_encode(['rows' => $arr]);
    }

    public function calculateJournal($journal_type)
    {
        $jsonDatas = json_decode(file_get_contents("json/journal_posting_adjustments.json"), true);

        $local_debit = 0;
        $local_credit = 0;
        $arrTax = array();

        $total_adjustment = 0;
        $total_debit = 0;
        $total_credit = 0;
        foreach ($jsonDatas as $post) {
            $total_adjustment += $post['adjustment'];

            if ($journal_type == "CL - SUPPLIER") {
                if ($post['amount'] > 0) {
                    $credit = $post['amount'];
                    $debit = 0;
                } else {
                    $debit = $post['amount'];
                    $credit = 0;
                }
            } else {
                if ($post['amount'] > 0) {
                    $debit = $post['amount'];
                    $credit = 0;
                } else {
                    $credit = $post['amount'];
                    $debit = 0;
                }
            }

            $arr[] = array(
                "description" => "CLEARING ADJUSTMENT",
                "account_number" => $post['account_number'],
                "account_name" => $post['account_name'],
                "local_debit" => abs($debit),
                "local_credit" => abs($credit),
                "status_cl" => 1
            );

            $total_debit += abs($debit);
            $total_credit += abs($credit);
        }

        if ($total_adjustment != 0) {
            if ($journal_type == "CL - SUPPLIER") {
                if ($total_adjustment > 0) {
                    $local_debit = 0;
                    $local_credit = $total_adjustment;
                } else {
                    $local_debit = $total_adjustment;
                    $local_credit = 0;
                }
            } else {
                if ($total_adjustment > 0) {
                    $local_debit = $total_adjustment;
                    $local_credit = 0;
                } else {
                    $local_debit = 0;
                    $local_credit = $total_adjustment;
                }
            }

            $arr[] = array(
                "description" => "CLEARING ADJUSTMENT",
                "account_number" => $post['account_number'],
                "account_name" => $post['account_name'],
                "local_debit" => abs($local_debit),
                "local_credit" => abs($local_credit),
                "status_cl" => 0
            );
        }

        $footer[] = array(
            "account_number" => "TOTAL",
            "local_debit" => abs($local_debit + $total_debit),
            "local_credit" => abs($local_credit + $total_credit),
        );

        $rows = array_merge($arr, $arrTax);
        echo json_encode(['rows' => $rows, 'footer' => $footer]);
    }

    public function create()
    {
        if ($this->input->post()) {
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $dataClearings = $post['dataClearings'];
            $dataJournals = $post['dataJournals'];

            $this->db->trans_start();
            foreach ($dataClearings as $clearing) {
                $this->crud->create('journal_clearings', $clearing);

                $this->db->update("journal_postings", ["status_cl" => 1], ["id" => $clearing['document_no'], "company_id" => $clearing['company_id']]);
                $this->db->update("journal_postings", ["status_cl" => 1], ["document_no" => $clearing['document_no'], "company_id" => $clearing['company_id'], "company_name" => $clearing['company_name'], "description" => $clearing['description']]);
                $this->db->update("ar_receipts", ["status_cl" => 1], ["receipt_no" => $clearing['document_no'], "customer_id" => $clearing['company_id']]);
                $this->db->update("ap_payments", ["status_cl" => 1], ["payment_no" => $clearing['document_no'], "supplier_id" => $clearing['company_id']]);
                $this->db->update("purchase_invoices", ["status" => 1], ["number" => $clearing['document_no'], "supplier_id" => $clearing['company_id']]);
                $this->db->update("sales_invoices", ["status" => 1], ["number" => $clearing['document_no'], "customer_id" => $clearing['company_id']]);
                $this->db->update("account_balance_suppliers", ["status" => 1], ["id" => $clearing['document_no'], "supplier_id" => $clearing['company_id']]);
                $this->db->update("account_balance_customers", ["status" => 1], ["id" => $clearing['document_no'], "customer_id" => $clearing['company_id']]);
            }
            foreach ($dataJournals as $journal) {
                $this->crud->create('journal_postings', $journal);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                die(json_encode(array("title" => "Failed", "message" => "Failed to Save Data", "theme" => "error")));
            } else {
                die(json_encode(array("title" => "Good Job", "message" => "Data Save Successfully", "theme" => "success")));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createJson()
    {
        $jsonData = $this->input->post('jsonData');
        file_put_contents('json/journal_posting_adjustments.json', $jsonData);
    }

    public function delete()
    {
        $data = $this->input->post();
        $journals = $this->crud->reads('journal_clearings', [], $data);

        foreach ($journals as $journal) {
            $this->db->update("journal_postings", ["status_cl" => 0], ["id" => $journal->document_no, "company_id" => $journal->company_id]);
            $this->db->update("journal_postings", ["status_cl" => 0], ["document_no" => $journal->document_no, "company_id" => $journal->company_id, "description" => $journal->description]);
            $this->db->update("ar_receipts", ["status_cl" => 0], ["receipt_no" => $journal->document_no, "customer_id" => $journal->company_id]);
            $this->db->update("ap_payments", ["status_cl" => 0], ["payment_no" => $journal->document_no, "supplier_id" => $journal->company_id]);
            $this->db->update("purchase_invoices", ["status" => 0], ["number" => $journal->document_no, "supplier_id" => $journal->company_id]);
            $this->db->update("sales_invoices", ["status" => 0], ["number" => $journal->document_no, "customer_id" => $journal->company_id]);
            $this->db->update("account_balance_suppliers", ["status" => 0], ["id" => $journal->document_no, "supplier_id" => $journal->company_id]);
            $this->db->update("account_balance_customers", ["status" => 0], ["id" => $journal->document_no, "customer_id" => $journal->company_id]);
        }

        $send = $this->crud->delete('journal_postings', $data);
        $send = $this->crud->delete('journal_clearings', $data);

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
        $filter_journal_type_id = base64_decode($this->input->get('filter_journal_type_id'));
        $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
        $filter_modul = base64_decode($this->input->get('filter_modul'));
        $filter_voucher = base64_decode($this->input->get('filter_voucher'));

        $this->db->select('a.*, b.name as journal_type_name');
        $this->db->from('journal_postings a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id');
        $this->db->where('a.modul', 'CLEARING');
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
                <th width="20">No</th>
                <th>Voucher No</th>
                <th>Journal Date</th>
                <th>Journal Type</th>
                <th>Journal Category</th>
                <th>Modul</th>
                <th>Document No</th>
                <th>Invoice No</th>
                <th>Company Name</th>
                <th>Trans Date</th>
                <th>Description</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Currency</th>
                <th>Adjustment</th>
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
                    <td>' . number_format(($data['local_debit'] - $data['local_credit']), 4) . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
