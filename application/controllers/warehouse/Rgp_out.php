<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Rgp_out extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Documen No.', 'required|min_length[1]|max_length[50]|is_unique[rgp_in.number]');
        $this->form_validation->set_rules('item_id', 'Item.', 'required|min_length[1]|max_length[50]|is_unique[rgp_in.item_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/rgp_out');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('rgp_transactions', ["number" => $post], ["type" => "OUT"]);
        echo json_encode($send);
    }

    public function readItemCategories($type = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_categories WHERE name LIKE '%$post%' AND number in ('RM','FG','OT')");
        echo json_encode($send);
    }

    public function readDocument()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("
        select number from rgp_transactions where type='OUT' and number LIKE '%$post%' group by number");
        echo json_encode($send);
    }

    public function readProcessType()
    {
        $send = array(
            array("id" => "RGP", "name" => "RGP OUT"),
            array("id" => "NRGP", "name" => "RGP IN TO NRGP OUT"),
            array("id" => "FOC", "name" => "FOC OUT"),
        );
        echo json_encode($send);
    }

    public function readDocumentIn()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $process_type = isset($_GET['process_type']) ? $_GET['process_type'] : "";
        $company_id = isset($_GET['company_id']) ? $_GET['company_id'] : "";
        $item_category_id = isset($_GET['item_category_id']) ? $_GET['item_category_id'] : "";

        if (!empty($company_id)) {
            $company = " and a.company_id = '$company_id'";
        } else {
            $company = "";
        }

        if (!empty($item_category_id)) {
            $item_category = " and a.item_category_id = '$item_category_id'";
        } else {
            $item_category = "";
        }
        if ($process_type == 'NRGP') {
            $send = $this->crud->query("
            SELECT
                a.number, (COALESCE ( sum(a.qty), 0 ) - COALESCE ( sum(b.total), 0 )) as balance
            FROM
                rgp_transactions a
                LEFT JOIN ( SELECT rgp_number, item_id, sum( qty ) AS total FROM rgp_transactions WHERE type='OUT' GROUP BY rgp_number,item_id ) b ON a.number = b.rgp_number and a.item_id=b.item_id
            WHERE
                a.type = 'IN' 
                AND a.number LIKE '%$post%' 
                AND a.process_type = 'RGP' 
                AND (a.approved_to is null or a.approved_to = '')
                $company 
                $item_category
                group by a.number
                having (COALESCE ( sum(a.qty), 0 ) - COALESCE ( sum(b.total), 0 ))  > 0
            ");
        } else {
            $send = [];
        }

        echo json_encode($send);
    }

    public function readStockRgp()
    {
        $post = $this->input->post();
        $item_fg = $this->db->query("select a.id from item_fg a where a.number='" . $post['item_id'] . "'")->row();
        $item_rm = $this->db->query("select a.id from item_rm a where a.id='" . $post['item_id'] . "'")->row();

        if (!empty($item_fg->id)) {
            $item_id = @$item_fg->id;
        } else {
            $item_id = @$item_rm->id;
        }

        $rgp = $this->db->query("
            select coalesce(a.qty,0) as qty,COALESCE(b.total,0) as total, (coalesce(a.qty,0) - COALESCE(b.total,0)) as balance from rgp_transactions a 
            left join (select rgp_number, item_id, sum(qty) as total from rgp_transactions group by rgp_number, item_id) b on a.number=b.rgp_number and a.item_id=b.item_id
            where a.number = '" . $post['rgp_number'] . "' and a.item_id='" . $item_id . "'
        ")->row();
        $arr = array(
            "balance" => @$rgp->balance
        );
        echo json_encode(@$arr);
    }

    public function readCompany()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("
        select * from (
        select id, number, name, 'CUSTOMER' as type from customers
        union 
        select id, number, name, 'SUPPLIER' as type from suppliers
        ) a
        where a.name LIKE '%$post%' or a.id like '%$post%' or number like '%$post%'");
        echo json_encode($send);
    }

    public function readItemCategory($item_category = "", $company_id = "")
    {

        $trans_date = date("Y-m-d");
        //master/bom
        //planning/where_used
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        if ($item_category != 'C08') { // bukan finish good
            if ($item_category == 'C01') { // jika raw material
                $company_id = base64_decode($company_id);
                if ($company_id != '') {
                    $whereCompany = "and j.supplier_id='$company_id'";
                } else {
                    $whereCompany = "";
                }
                $send = $this->crud->query("SELECT a.*,b.name as item_family_name,
                ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id
                JOIN item_categories z ON a.item_category_id = z.id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$trans_date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date <= '$trans_date' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date <= '$trans_date' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date <= '$trans_date' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) j ON a.id = j.item_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) k ON a.id = k.item_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
                WHERE a.return_date <= '$trans_date'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
                WHERE a.status = 0 and z.id = '$item_category'
                and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
                GROUP BY a.id");
            }
            // else if ($item_category == 'C03') { // JIKA CONSUMABLE
            //     $company_id = base64_decode($company_id);
            //     if ($company_id != '') {
            //         $whereCompany = "and e.supplier_id='$company_id'";
            //     } else {
            //         $whereCompany = "";
            //     }
            //     $send = $this->crud->query("SELECT a.*,b.name as item_family_name,
            //     (COALESCE(f.begin_stock,0) + COALESCE(d.qty_receipt,0) - COALESCE(e.qty,0)) AS stock 
            //     FROM item_rm a 
            //     JOIN item_familys b ON a.item_family_id = b.id
            //     JOIN item_categories c ON a.item_category_id = c.id

            //     JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE receipt_date <= '$trans_date' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others WHERE trans_date <= '$trans_date' and `status` = '1' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            //     LEFT JOIN (SELECT
            //         a.id,
            //         (c.qty_receipt - d.qty) AS begin_stock
            //         FROM item_rm a 
            //         JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            //         JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE receipt_date <= '$trans_date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            //         LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others WHERE trans_date <= '$trans_date' and `status` = '1' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            //         GROUP BY a.id) f on a.id = f.id

            //     JOIN supplier_items e ON a.id = e.item_rm_id 
            //     WHERE a.status = 0 and c.id = '$item_category'
            //     and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
            //     GROUP BY a.id");
            // } 
            else { // JIKA CONSUMABL OTHER NON INV-
                $company_id = base64_decode($company_id);
                if ($company_id != '') {
                    $whereCompany = "and e.supplier_id='$company_id'";
                } else {
                    $whereCompany = "";
                }
                $send = $this->crud->query("SELECT a.*,b.name as item_family_name,
                    (COALESCE(c.qty_receipt,0) + COALESCE(g.sto_qty_in, 0) + COALESCE(e.qty_rgp_in,0) - COALESCE(i.sto_qty_out, 0) - COALESCE(f.qty_rgp_out,0) - COALESCE(d.qty,0)) AS stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id
                JOIN item_categories j ON a.item_category_id = j.id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE receipt_date <= '$trans_date' and store != 0 GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others WHERE trans_date <= '$trans_date' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date <= '$trans_date' and (approved_to = '' or approved_to is null) GROUP BY item_id) e ON a.id = e.item_id
                LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date <= '$trans_date' and (approved_to = '' or approved_to is null) GROUP BY item_id) f ON a.id = f.item_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_in FROM sto_ot WHERE type = 'IN' and trans_date <= '$trans_date' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_out FROM sto_ot WHERE type = 'OUT' and trans_date <= '$trans_date' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) i ON a.id = i.item_rm_id
                WHERE a.status = 0 and j.id not in ('C01','C08') and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
                GROUP BY a.id");

                // die($this->db->last_query());
            }
        } else { // FG
            $company_id = base64_decode($company_id);
            if ($company_id != '') {
                $whereCompany = "and a.customer_id='$company_id'";
            } else {
                $whereCompany = "";
            }
            $send = $this->crud->query("SELECT a.number as id, a.number_customer as number, a.name, a.uom, a.hsn_code,
            (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(e.qty_rgp_in, 0) + COALESCE(c.qty_wip, 0) + COALESCE(i.qty_return, 0) - COALESCE(g.qty, 0) - COALESCE(f.qty_rgp_out, 0) - COALESCE(h.sto_qty_out, 0)) as stock
            FROM item_fg a 
            LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_fg a JOIN scan_item_receipts_fg b ON a.id = b.workorder WHERE a.trans_date <= '$trans_date' GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
            LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_wip FROM wip_receipts a JOIN scan_item_receipts_fg b ON a.workorder = b.workorder and a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_fg_id) c ON a.id = c.item_fg_id
            LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_return FROM sales_returns a JOIN scan_item_receipts_fg b ON a.sales_invoice_no = b.workorder and a.return_id = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_fg_id) i ON a.id = i.item_fg_id
            LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_fg a WHERE a.trans_date <= '$trans_date' and a.type = 'IN' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) d ON a.id = d.item_fg_id
            LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_fg a WHERE a.trans_date <= '$trans_date' and a.type = 'OUT' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) h ON a.id = h.item_fg_id
            LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id= b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) e ON a.id = e.item_id
            LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) f ON a.id = f.item_id
            LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and `status` != '2' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
            WHERE (a.number like '%$post%' or a.number_customer like '%$post%' or a.name like '%$post%')
            GROUP BY a.id");

            // die($this->db->last_query());
        }
        echo json_encode($send);
    }

    public function autonumber($id_menu, $trans_date)
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

            $sqlGetID    = $this->db->query("SELECT max(number) as kode FROM rgp_transactions WHERE type='OUT' and number like '$whereDate%'");
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

    public function readIgst($item_category = "")
    {
        $post = $this->input->post();
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $customer_address = $this->crud->read("customer_address", [], ["plant" => $post['plant']]);
        $suppliers = $this->crud->read("suppliers", [], ["id" => $post['company_id']]);

        $item_fg = $this->db->query("select a.id, b.igst, b.sgst, b.cgst from item_fg a join hsn b on a.hsn_code=b.number where a.number='" . $post['item_id'] . "'")->row();
        $item_rm = $this->db->query("select a.id, b.igst, b.sgst, b.cgst from item_rm a join hsn b on a.hsn_code=b.number where a.id='" . $post['item_id'] . "'")->row();

        if (!empty($item_fg->id)) {
            $igst = @$item_fg->igst;
            $sgst = @$item_fg->sgst;
            $cgst = @$item_fg->cgst;
        } else {
            $igst = @$item_rm->igst;
            $sgst = @$item_rm->sgst;
            $cgst = @$item_rm->cgst;
        }

        if (!empty(@$customer_address->ship_state)) {
            if (strtoupper(@$config->state) == strtoupper(@$customer_address->ship_state)) {
                $arr = array(
                    "igst" => 0,
                    "sgst" => $sgst,
                    "cgst" => $cgst,
                    "state" => @$customer_address->ship_state,
                );
            } else {
                $arr = array(
                    "igst" => $igst,
                    "sgst" => 0,
                    "cgst" => 0,
                    "state" => @$customer_address->ship_state,

                );
            }
        } else if (!empty(@$suppliers->state)) {
            if (strtoupper(@$config->state) == strtoupper(@$suppliers->state)) {
                $arr = array(
                    "igst" => 0,
                    "sgst" => $sgst,
                    "cgst" => $cgst,
                    "state" => @$suppliers->state,
                );
            } else {
                $arr = array(
                    "igst" => $igst,
                    "sgst" => 0,
                    "cgst" => 0,
                    "state" => @$suppliers->state,
                );
            }
        } else {
            $arr = array(
                "igst" => 0,
                "sgst" => 0,
                "cgst" => 0,
                "state" => null,
            );
        }
        echo json_encode(@$arr);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_process_type = @base64_decode($get['filter_process_type']);
            $filter_number = @base64_decode($get['filter_number']);
            $filter_company = @base64_decode($get['filter_company']);
            $filter_plant = @base64_decode($get['filter_plant']);
            $filter_category = @base64_decode($get['filter_category']);
            $filter_departement = @base64_decode($get['filter_departement']);
            $filter_rgp_type = @base64_decode($get['filter_rgp_type']);
            $filter_status = @base64_decode($get['filter_status']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*,
            (CASE 
                WHEN b.id is not null THEN b.name
                ELSE c.name
            END) as item_name,
            (CASE 
                WHEN d.id is not null THEN d.name
                ELSE e.name
            END) as company_name,
            (CASE 
                WHEN f.plant is not null THEN f.plant
                ELSE NULL
            END) as plant_number,
            g.name as item_category_name,
            h.name as departement_name,
            i.name as rgp_type_name,
            (CASE WHEN a.item_category_id IN ('C01','C08') THEN (CASE WHEN j.balance > 0 THEN 1 ELSE 0 END) ELSE 0 END) as status_label
            ");
            $this->db->from('rgp_transactions a');
            $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
            $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
            $this->db->join('suppliers d', 'a.company_id = d.id', 'left');
            $this->db->join('customers e', 'a.company_id = e.id', 'left');
            $this->db->join('customer_address f', 'a.plant = f.plant and a.company_id=f.customer_id', 'left');
            $this->db->join('item_categories g', 'a.item_category_id = g.id', 'left');
            $this->db->join('departements h', 'a.departement_id = h.id', 'left');
            $this->db->join('rgp_types i', 'a.rgp_type_id = i.id', 'left');
            $this->db->join("(SELECT z.number, (SUM(z.qty) - SUM(z.label)) as balance FROM (
                SELECT a.number, a.item_id, a.qty, COALESCE(SUM(b.qty), 0) AS label
                FROM rgp_transactions a
                LEFT JOIN scan_item_rgp b ON a.item_id = b.item_id AND a.number = b.rgp_number
                WHERE a.type = 'OUT'
                GROUP BY a.number, a.item_id) z 
                GROUP BY z.number) j", 'a.number = j.number', 'left');
            $this->db->where('a.type', 'OUT');
            if ($filter_from != "" || $filter_to != "") {
                $this->db->where('a.transaction_date >=', $filter_from);
                $this->db->where('a.transaction_date <=', $filter_to);
            }
            if (!empty($filter_process_type)) {
                $this->db->where('a.process_type', $filter_process_type);
            }
            if (!empty($filter_number)) {
                $this->db->where('a.number', $filter_number);
            }
            if (!empty($filter_company)) {
                $this->db->where('a.company_id', $filter_company);
            }
            if (!empty($filter_plant)) {
                $this->db->where('a.plant', $filter_plant);
            }
            if (!empty($filter_category)) {
                $this->db->where('a.item_category_id', $filter_category);
            }
            if (!empty($filter_departement)) {
                $this->db->where('a.departement_id', $filter_departement);
            }
            if (!empty($filter_rgp_type)) {
                $this->db->where('a.rgp_type_id', $filter_rgp_type);
            }
            if (!empty($filter_status)) {
                $this->db->where('a.status', $filter_status);
            }

            $this->db->group_by('a.number');
            $this->db->order_by('a.number', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));

            $this->db->select("
            (CASE 
                WHEN b.id is not null THEN b.id
                ELSE c.number
            END) as item_id,
            (CASE 
                WHEN b.id is not null THEN b.number
                ELSE c.number_customer
            END) as item_number,
            (CASE 
                WHEN b.id is not null THEN b.name
                ELSE c.name
            END) as item_name,
            (CASE 
                WHEN b.id is not null THEN j.name
                ELSE null
            END) as item_family_name,

            a.uom, a.stock, a.qty, a.currency, a.price, a.value, a.igst_value, a.sgst_value, a.cgst_value, a.total, a.hsn_code,
            
            (CASE 
                WHEN d.id is not null THEN d.name
                ELSE e.name
            END) as company_name,
            (CASE 
                WHEN f.plant is not null THEN f.plant
                ELSE NULL
            END) as plant_number,
            g.name as item_category_name,
            h.name as departement_name,
            i.name as rgp_type_name,
            (CASE 
                WHEN a.qty=k.qty_scan THEN 1
                ELSE 0
            END) as status_scan,
            ");
            $this->db->from('rgp_transactions a');
            $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
            $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
            $this->db->join('suppliers d', 'a.company_id = d.id', 'left');
            $this->db->join('customers e', 'a.company_id = e.id', 'left');
            $this->db->join('customer_address f', 'a.plant = f.plant and a.company_id=f.customer_id', 'left');
            $this->db->join('item_categories g', 'a.item_category_id = g.id', 'left');
            $this->db->join('departements h', 'a.departement_id = h.id', 'left');
            $this->db->join('rgp_types i', 'a.rgp_type_id = i.id', 'left');
            $this->db->join('item_familys j', 'b.item_family_id = j.id', 'left');
            $this->db->join("(select rgp_number, item_id, sum(qty) as qty_scan from scan_item_rgp group by rgp_number, item_id) k", 'a.number=k.rgp_number and a.item_id=k.item_id', 'left');
            $this->db->where('a.number', $number);
            $this->db->order_by('a.item_id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $action = $this->input->get('action');
            $number = base64_decode($this->input->get('number'));
            $rgp_number = base64_decode($this->input->get('rgp_number'));

            if (!empty($rgp_number) && $rgp_number != "null" && $action == "update") {
                $this->db->select("
                    (CASE 
                        WHEN b.id is not null THEN b.id
                        ELSE c.number
                    END) as item_id,
                    (CASE 
                        WHEN b.id is not null THEN b.number
                        ELSE c.number_customer
                    END) as item_number,
                    (CASE 
                        WHEN b.id is not null THEN b.name
                        ELSE c.name
                    END) as item_name,
                    (CASE 
                        WHEN b.id is not null THEN j.name
                        ELSE null
                    END) as item_family_name,

                    a.uom, a.stock, ((coalesce(k.qty_in,0) - coalesce(l.qty_out,0))) as qty, a.currency, a.price, a.value, a.igst_value, a.sgst_value, a.cgst_value, a.total, a.hsn_code,
                    
                    (CASE 
                        WHEN d.id is not null THEN d.name
                        ELSE e.name
                    END) as company_name,
                    (CASE 
                        WHEN f.plant is not null THEN f.plant
                        ELSE NULL
                    END) as plant_number,
                    g.name as item_category_name,
                    h.name as departement_name,
                    i.name as rgp_type_name
                ");
                $this->db->from('rgp_transactions a');
                $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
                $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
                $this->db->join('suppliers d', 'a.company_id = d.id', 'left');
                $this->db->join('customers e', 'a.company_id = e.id', 'left');
                $this->db->join('customer_address f', 'a.plant = f.plant and a.company_id=f.customer_id', 'left');
                $this->db->join('item_categories g', 'a.item_category_id = g.id', 'left');
                $this->db->join('departements h', 'a.departement_id = h.id', 'left');
                $this->db->join('rgp_types i', 'a.rgp_type_id = i.id', 'left');
                $this->db->join('item_familys j', 'b.item_family_id = j.id', 'left');
                $this->db->join("(select number, item_id, sum(qty) as qty_in from rgp_transactions where type='IN' group by number, item_id) k", 'a.rgp_number=k.number and a.item_id=k.item_id', 'left');
                $this->db->join("(select rgp_number, item_id, sum(qty) as qty_out from rgp_transactions where type='OUT' group by rgp_number, item_id) l", 'a.rgp_number=l.rgp_number and a.item_id=l.item_id', 'left');
                $this->db->where('a.number', $number);
                $this->db->order_by('a.item_id', 'ASC');
            } else if (!empty($rgp_number) && $rgp_number != "null" && $action == "add") {
                $this->db->select("
                (CASE 
                    WHEN b.id is not null THEN b.id
                    ELSE c.number
                END) as item_id,
                (CASE 
                    WHEN b.id is not null THEN b.number
                    ELSE c.number_customer
                END) as item_number,
                (CASE 
                    WHEN b.id is not null THEN b.name
                    ELSE c.name
                END) as item_name,
                (CASE 
                    WHEN b.id is not null THEN j.name
                    ELSE null
                END) as item_family_name,

                a.uom, a.stock, ((coalesce(a.qty,0) - coalesce(k.qty_out,0))) as qty, a.currency, a.price, a.value, a.igst_value, a.sgst_value, a.cgst_value, a.total, a.hsn_code,
                
                (CASE 
                    WHEN d.id is not null THEN d.name
                    ELSE e.name
                END) as company_name,
                (CASE 
                    WHEN f.plant is not null THEN f.plant
                    ELSE NULL
                END) as plant_number,
                g.name as item_category_name,
                h.name as departement_name,
                i.name as rgp_type_name
            ");
                $this->db->from('rgp_transactions a');
                $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
                $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
                $this->db->join('suppliers d', 'a.company_id = d.id', 'left');
                $this->db->join('customers e', 'a.company_id = e.id', 'left');
                $this->db->join('customer_address f', 'a.plant = f.plant and a.company_id=f.customer_id', 'left');
                $this->db->join('item_categories g', 'a.item_category_id = g.id', 'left');
                $this->db->join('departements h', 'a.departement_id = h.id', 'left');
                $this->db->join('rgp_types i', 'a.rgp_type_id = i.id', 'left');
                $this->db->join('item_familys j', 'b.item_family_id = j.id', 'left');
                $this->db->join("(select rgp_number, item_id, qty as qty_out from rgp_transactions where type = 'OUT') k", 'k.rgp_number=a.number and a.item_id=k.item_id', 'left');
                $this->db->where('a.number', $rgp_number);
                $this->db->order_by('a.item_id', 'ASC');
            } else {
                $this->db->select("
                (CASE 
                    WHEN b.id is not null THEN b.id
                    ELSE c.number
                END) as item_id,
                (CASE 
                    WHEN b.id is not null THEN b.number
                    ELSE c.number_customer
                END) as item_number,
                (CASE 
                    WHEN b.id is not null THEN b.name
                    ELSE c.name
                END) as item_name,
                (CASE 
                    WHEN b.id is not null THEN j.name
                    ELSE null
                END) as item_family_name,
    
                a.uom, a.stock, a.qty, a.currency, a.price, a.value, a.igst_value, a.sgst_value, a.cgst_value, a.total, a.hsn_code,
                
                (CASE 
                    WHEN d.id is not null THEN d.name
                    ELSE e.name
                END) as company_name,
                (CASE 
                    WHEN f.plant is not null THEN f.plant
                    ELSE NULL
                END) as plant_number,
                g.name as item_category_name,
                h.name as departement_name,
                i.name as rgp_type_name
                ");
                $this->db->from('rgp_transactions a');
                $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
                $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
                $this->db->join('suppliers d', 'a.company_id = d.id', 'left');
                $this->db->join('customers e', 'a.company_id = e.id', 'left');
                $this->db->join('customer_address f', 'a.plant = f.plant and a.company_id=f.customer_id', 'left');
                $this->db->join('item_categories g', 'a.item_category_id = g.id', 'left');
                $this->db->join('departements h', 'a.departement_id = h.id', 'left');
                $this->db->join('rgp_types i', 'a.rgp_type_id = i.id', 'left');
                $this->db->join('item_familys j', 'b.item_family_id = j.id', 'left');
                $this->db->where('a.number', $number);
                $this->db->order_by('a.item_id', 'ASC');
            }
            $records = $this->db->get()->result_array();

            // die($this->db->last_query());
            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $item_fg = $this->crud->read("item_fg", [], ["number" => $post['item_id']]);
            if (!empty(@$item_fg->id)) {
                $item_id = @$item_fg->id;
            } else {
                $item_id = $post['item_id'];
            }

            if ($post['rgp_number'] !== "") {
                $rgp = $this->db->query("
                    select * from rgp_transactions a where a.number = '" . $post['number'] . "' and a.item_id='" . $item_id . "' and a.type='OUT'
                    ")->row();

                $rgpQty = $this->db->query("
                    select coalesce(a.qty,0) as qty,COALESCE(b.total,0) as total, (coalesce(a.qty,0) - COALESCE(b.total,0)) as balance from rgp_transactions a 
                    left join (select rgp_number, item_id, sum(qty) as total from rgp_transactions group by rgp_number, item_id) b on a.number=b.rgp_number and a.item_id=b.item_id
                    where a.number = '" . $post['rgp_number'] . "' and a.item_id='" . $item_id . "'
                    ")->row();

                if ($post['qty'] < @$rgpQty->balance) {
                    $status = 0;
                } else {
                    $status = 1;
                }
                $qty = $post['qty'] + @$rgp->qty;
                $value = $post['value'] + @$rgp->value;
                $igst_value = $post['igst_value'] + @$rgp->igst_value;
                $sgst_value = $post['sgst_value'] + @$rgp->sgst_value;
                $cgst_value = $post['cgst_value'] + @$rgp->cgst_value;
                $total = $post['total'] + @$rgp->total;
            } else {
                $status = 0;
                $qty = $post['qty'];
                $value = $post['value'];
                $igst_value = $post['igst_value'];
                $sgst_value = $post['sgst_value'];
                $cgst_value = $post['cgst_value'];
                $total = $post['total'];
            }

            $postFinal = array(
                "process_type" => $post['process_type'],
                "transaction_date" => $post['transaction_date'],
                "number" => $post['number'],
                "company_id" => $post['company_id'],
                "plant" => $post['plant'],
                "item_category_id" => $post['item_category_id'],
                "departement_id" => $post['departement_id'],
                "rgp_type_id" => $post['rgp_type_id'],
                "reference_number" => $post['reference_number'],
                "return_date_estimation" => $post['return_date_estimation'],
                "rgp_number" => $post['rgp_number'],
                "notes" => $post['notes'],
                "item_id" => $item_id,
                "uom" => $post['uom'],
                "stock" => $post['stock'],
                "qty" => $qty,
                "hsn_code" => $post['hsn_code'],
                "currency" => $post['currency'],
                "price" => $post['price'],
                "value" => $value,
                "igst_value" => $igst_value,
                "sgst_value" => $sgst_value,
                "cgst_value" => $cgst_value,
                "total" => $total,
                "type" => "OUT",
                "declaration" => $post['declaration'],
                "status" => $status,
            );
            $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $post['number'], "item_id" => $item_id, "type" => "OUT"]);
            if (!empty(@$rgp_transactions->number)) {
                $send = $this->crud->update('rgp_transactions', ["number" => $post['number'], "item_id" => $item_id, "type" => "OUT"], $postFinal);
            } else {
                $send = $this->crud->create('rgp_transactions', $postFinal);

                $approval = $this->crud->read('approvals', [], ["table_name" => "rgp_transaction_out"]);
                if (!empty($approval)) {
                    $formApprove = [
                        "approved" => 1,
                        "approved_to" => $approval->user_approval_1,
                        "approved_by" => $this->session->username,
                    ];

                    $this->db->update('rgp_transactions', $formApprove, ["number" => $post['number'], "item_id" => $item_id, "type" => "OUT"]);
                }
            }

            $rgp_in = $this->db->query("
            select sum(qty) as qty from rgp_transactions a where a.number = '" . $post['rgp_number'] . "' and a.item_id='" . $item_id . "' and a.type='IN'
            ")->row();

            $rgp_out = $this->db->query("
            select sum(qty) as qty from rgp_transactions a where a.rgp_number = '" . $post['rgp_number'] . "' and a.item_id='" . $item_id . "' and a.type='OUT'
            ")->row();

            // upddate semua status yg terkait dengan rgp in
            if (@$rgp_in->qty == @$rgp_out->qty) {
                $this->db->update('rgp_transactions', ["status" => 1], ["rgp_number" => $post['rgp_number'], "item_id" => $item_id]);
                $this->db->update('rgp_transactions', ["status" => 1], ["number" => $post['rgp_number'], "item_id" => $item_id]);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('rgp_transactions', $data);
        echo $send;
    }

    //UPLOAD IMAGE
    public function uploadDrawing($file_bf = "")
    {
        $file                = $_FILES['file']['name'];
        $extension            = explode('.', $file);
        $extension_end        = strtolower(end($extension));
        $size                = $_FILES['file']['size'];
        $temporary             = $_FILES['file']['tmp_name'];
        $fileName           = time() . "." . $extension_end;

        if (in_array($extension_end, array('pdf', 'jpg', 'png', 'jpeg')) === true) {
            if ($size < 1000000) {
                if ($file != "") {
                    @unlink("assets/documents/rgp_out/" . base64_decode($file_bf));
                    @move_uploaded_file($temporary, 'assets/documents/rgp_out/' . $fileName);
                    echo json_encode(array('msg' => 'Upload Success', "file" => $fileName));
                }
            } else {
                echo json_encode(array('msg' => 'Your file size is too large, a maximum of 1 MB', 'file' => base64_decode($file_bf)));
            }
        } else {
            echo json_encode(array('msg' => 'error', 'text' =>  'Your file format is not accepted', 'file' => base64_decode($file_bf)));
        }
    }

    //UPLOAD DATA
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'item_fg_number' => $data->val($i, 2),
                'item_rm_number' => $data->val($i, 3),
                'main_process' => $data->val($i, 4),
                'type' => $data->val($i, 5),
                'composition' => $data->val($i, 6)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/rgp_out.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/rgp_out.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/rgp_out.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadCreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_number']]);
            $process_main = $this->crud->read('process_main', [], ["name" => $data['main_process']]);

            $rgp_out = $this->crud->read('rgp_out', [], ["item_fg_id" => @$item_fg->id, "item_rm_id" => @$item_rm->id]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No EBWS " . $data['item_fg_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No EBWS " . $data['item_rm_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($process_main->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Main " . $data['main_process'] . " Not Found", "theme" => "error"));
            } elseif (!empty($rgp_out->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part No " . $data['item_rm_number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "item_fg_id" => $item_fg->id,
                    "item_rm_id" => $item_rm->id,
                    "main_process" => $data['main_process'],
                    "type" => $data['type'],
                    "composition" => $data['composition'],
                );

                $send   = $this->crud->create('rgp_out', $dataFinal);
                echo $send;
            }
        }
    }

    // public function deletergp_out(){
    //     $query = $this->db->query("SELECT a.item_fg_id, c.number_customer as item_fg_number, a.item_rm_id, b.number as item_rm_number, COUNT(a.id) as total 
    //         FROM rgp_out a JOIN item_rm b ON a.item_rm_id = b.id 
    //         JOIN item_fg c ON a.item_fg_id = c.id
    //         GROUP BY a.item_fg_id, a.item_rm_id 
    //         HAVING COUNT(a.id) > 1
    //         LIMIT 1");
    //     $querys = $query->result_array();

    //     foreach ($querys as $query) {
    //         $item_fg_id = $query['item_fg_id'];
    //         $item_rm_id = $query['item_rm_id'];

    //         $rgp_out = $this->db->query("SELECT a.id, a.item_fg_id, c.number_customer as item_fg_number, a.item_rm_id, b.number as item_rm_number
    //             FROM rgp_out a JOIN item_rm b ON a.item_rm_id = b.id 
    //             JOIN item_fg c ON a.item_fg_id = c.id 
    //             WHERE item_fg_id = '$item_fg_id' and a.item_rm_id = '$item_rm_id'
    //             ORDER BY a.created_date desc");
    //         $rgp_outs = $rgp_out->result_array();

    //         for ($i=1; $i < $query['total']; $i++) {
    //             echo "<p>".$rgp_outs[$i]['id']."</p>";

    //             $this->db->delete('rgp_out', ["id" => $rgp_outs[$i]['id']]);
    //         }
    //     }
    // }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=rgp_out_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_process_type = @base64_decode($get['filter_process_type']);
        $filter_number = @base64_decode($get['filter_number']);
        $filter_company = @base64_decode($get['filter_company']);
        $filter_plant = @base64_decode($get['filter_plant']);
        $filter_category = @base64_decode($get['filter_category']);
        $filter_departement = @base64_decode($get['filter_departement']);
        $filter_rgp_type = @base64_decode($get['filter_rgp_type']);
        $filter_status = @base64_decode($get['filter_status']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*,
            (CASE 
                WHEN b.id is not null THEN b.id
                ELSE c.number
            END) as item_id,
            (CASE 
                WHEN b.id is not null THEN b.number
                ELSE c.number_customer
            END) as item_number,
            (CASE 
                WHEN b.id is not null THEN b.name
                ELSE c.name
            END) as item_name,
            (CASE 
                WHEN d.id is not null THEN d.name
                ELSE e.name
            END) as company_name,
            (CASE 
                WHEN f.plant is not null THEN f.plant
                ELSE NULL
            END) as plant_number,
            g.name as item_category_name,
            h.name as departement_name,
            i.name as rgp_type_name
            ");
        $this->db->from('rgp_transactions a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->join('suppliers d', 'a.company_id = d.id', 'left');
        $this->db->join('customers e', 'a.company_id = e.id', 'left');
        $this->db->join('customer_address f', 'a.plant = f.plant and a.company_id=f.customer_id', 'left');
        $this->db->join('item_categories g', 'a.item_category_id = g.id', 'left');
        $this->db->join('departements h', 'a.departement_id = h.id', 'left');
        $this->db->join('rgp_types i', 'a.rgp_type_id = i.id', 'left');
        $this->db->where('a.type', 'OUT');
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.transaction_date >=', $filter_from);
            $this->db->where('a.transaction_date <=', $filter_to);
        }
        if (!empty($filter_process_type)) {
            $this->db->where('a.process_type', $filter_process_type);
        }
        if (!empty($filter_number)) {
            $this->db->where('a.number', $filter_number);
        }
        if (!empty($filter_company)) {
            $this->db->where('a.company_id', $filter_company);
        }
        if (!empty($filter_plant)) {
            $this->db->where('a.plant', $filter_plant);
        }
        if (!empty($filter_category)) {
            $this->db->where('a.item_category_id', $filter_category);
        }
        if (!empty($filter_departement)) {
            $this->db->where('a.departement_id', $filter_departement);
        }
        if (!empty($filter_rgp_type)) {
            $this->db->where('a.rgp_type_id', $filter_rgp_type);
        }
        if (!empty($filter_status)) {
            $this->db->where('a.status', $filter_status);
        }

        $this->db->group_by('a.id');
        $this->db->order_by('a.number', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#rgp_out {border-collapse: collapse;width: 100%;font-size: 12px;}#rgp_out td, #rgp_out th {border: 1px solid #ddd;padding: 2px;}#rgp_out tr:nth-child(even){background-color: #f2f2f2;}#rgp_out tr:hover {background-color: #ddd;}#rgp_out th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>RGP/NRGP OUT</h3>
            </div>
        </center>
        
        <table id="rgp_in" border="1">
            <tr>
                <th width="20">No</th>
                <th>Process Type</th>
                <th>EBWS Doc No</th>
                <th>Transaction <br>Date</th>
                <th>Company ID</th>
                <th>Company Name</th>
                <th>Plant</th>
                <th>Category</th>
                <th>Departement</th>
                <th>Reference</th>
                <th>RGP Out No.</th>
                <th>RGP ID</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Description</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Value</th>
                <th>IGST</th>
                <th>SGST</th>
                <th>CGST</th>
                <th>Total Value</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['process_type'] . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['transaction_date'] . '</td>
                    <td>' . $data['company_id'] . '</td>
                    <td>' . $data['company_name'] . '</td>
                    <td>' . $data['plant'] . '</td>
                    <td>' . $data['item_category_name'] . '</td>
                    <td>' . $data['departement_name'] . '</td>
                    <td>' . $data['reference_number'] . '</td>
                    <td>' . $data['rgp_number'] . '</td>
                    <td>' . $data['rgp_id'] . '</td>
                    <td>' . $data['item_id'] . '</td>
                    <td>' . $data['item_number'] . '</td>
                    <td>' . $data['item_name'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['value'] . '</td>
                    <td>' . $data['igst_value'] . '</td>
                    <td>' . $data['sgst_value'] . '</td>
                    <td>' . $data['cgst_value'] . '</td>
                    <td>' . $data['total'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_doc($number) //, $operation
    {
        $number = base64_decode($number);
        $this->db->select("
            a.uom, a.qty, a.currency, a.price, a.value, a.igst_value, a.sgst_value, a.cgst_value, a.total, a.hsn_code,
            (CASE 
                WHEN b.id is not null THEN b.id
                ELSE c.number
            END) as item_id,
            (CASE 
                WHEN b.id is not null THEN b.number
                ELSE c.number_customer
            END) as item_number,
            (CASE 
                WHEN b.id is not null THEN b.name
                ELSE c.name
            END) as item_name");
        $this->db->from('rgp_transactions a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->where('a.number', $number);
        $records = $this->db->get()->result_array();
        // die($this->db->last_query());
        $config = $this->db->get('config')->row();

        $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $number]);
        $customers = $this->crud->read("customers", [], ["id" => @$rgp_transactions->company_id]);
        $customer_address = $this->crud->read("customer_address", [], ["plant" => @$rgp_transactions->plant, "customer_id" => @$rgp_transactions->company_id]);
        if (!empty(@$customers->name)) {
            $company_name = @$customers->name;
            $company_address = @$customer_address->ship_address;
            $gstn = @$customer_address->gst_no;
        } else {
            $suppliers = $this->crud->read("suppliers", [], ["id" => @$rgp_transactions->company_id]);
            $company_name = @$suppliers->name;
            $company_address = @$suppliers->address;
            $gstn = @$suppliers->gst;
        }

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
                        </center>
                    </div>
                    <div class="print">';
        $html .= '
                            <table id="customers">
                            <tr>
                                <td colspan="13" style="font-size:15px;" align="center"><b>Delivery Challan</b><br><i><u>Under Rule-55 </u><b>(2)</b> of CGST Act 2017</i></td>
                            </tr>
                            <tr>
                                <td colspan="13" align="center"><b>' . @$config->name . ' PVT. LTD.</b><br> ' . @$config->address2 . '<br>GSTIN : ' . @$config->gst_no . '</td>
                            </tr>
                            <tr>
                                <td colspan="8">Consignee</td>
                                <td colspan="5"></td>
                            </tr>
                            <tr>
                                <td colspan="8" rowspan="3"><b>' . $company_name . '</b><br>' . $company_address . '<br>GSTN : ' . $gstn . '</td>
                                <td colspan="2"><b>Documen No</b></td>
                                <td colspan="3"><b>' . $number . '</b></td>
                            </tr>
                            <tr>
                                <td colspan="2"><b>Date</b></td>
                                <td colspan="3"><b>' . @$rgp_transactions->transaction_date . '</b></td>
                            </tr>
                            <tr>
                                <td colspan="2"><b>Note</b></td>
                                <td colspan="3">' . @$rgp_transactions->notes . '</td>
                            </tr>
                                <tr>
                                <th>NO</th>
                                <th>PART NO EBWS</th>
                                <th>PART NO SUPPLIER</th>
                                <th>DESCRIPTION</th>
                                <th>UOM</th>
                                <th>QTY</th>
                                <th>HSN</th>
                                <th>PRICE</th>
                                <th>VALUE</th>
                                <th>IGST</th>
                                <th>CGST</th>
                                <th>SGST</th>
                                <th>TOTAL VALUE</th>
                            </tr>';
        $no = 1;
        $total_qty = 0;
        $total_amount = 0;
        foreach ($records as $record) {
            $html .= ' 
            <tr>
                <td align="center">' . $no . '</td>
                <td>' . $record['item_id'] . '</td>
                <td>' . $record['item_number'] . '</td>
                <td>' . $record['item_name'] . '</td>
                <td>' . $record['uom'] . '</td>
                <td align="right">' . $record['qty'] . '</td>
                <td align="center">' . $record['hsn_code'] . '</td>
                <td align="right">' . @number_format(($record['price']), 2) . '</td>
                <td align="right">' . @number_format(($record['value']), 2) . '</td>
                <td align="right">' . @number_format(($record['igst_value']), 2) . '</td>
                <td align="right">' . @number_format(($record['cgst_value']), 2) . '</td>
                <td align="right">' . @number_format(($record['sgst_value']), 2) . '</td>
                <td align="right">' . @number_format(($record['total']), 2) . '</td>
            </tr>';
            $total_qty += $record['qty'];
            $total_amount += $record['total'];
            $no++;
        }
        $html .= '
                <tr>
                    <td colspan="5" align="right"><b>TOTAL</b></td>
                    <td align="right"><b>' . $total_qty . '</b></td>
                    <td colspan="3" align="right"><b>TOTAL AMOUNT</b></td>
                    <td colspan="4" align="right"><b>' . @number_format(($total_amount), 2) . '</b></td>
                </tr>
                <tr>
                    <td colspan="6" rowspan="2" style="vertical-align: text-top;">Declaration :<br>' . @$rgp_transactions->declaration . '</td>
                    <td colspan="7"><b>For ' . @$config->name . '</b></td>
                </tr>
                <tr>
                    <td colspan="7" style="height:100px; vertical-align: bottom; text-align:center;"><b>AUTHORISED SIGNATORY</b></td>
                </tr>
        </table>';

        $html .= "</div><script>window.print()</script></body>";
        die($html);
    }
}
