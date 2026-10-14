<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Rgp_in extends CI_Controller
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
            $this->load->view('warehouse/rgp_in');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('rgp_transactions', ["number" => $post], ["type" => "IN"]);
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
        select number from rgp_transactions where type='IN' and number LIKE '%$post%' group by number");
        echo json_encode($send);
    }

    public function readDocumentOut()
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

        if ($item_category_id == 'C09') { // jika other tidak perlu check status scan label
            $status_scan_label = "";
        } else {
            $status_scan_label = " and a.qty=c.qty_scan";
        }

        if ($process_type == 'NRGP') {
            $send = $this->crud->query("
            SELECT
                a.number, (COALESCE ( sum(a.qty), 0 ) - COALESCE ( sum(b.total), 0 )) as balance
            FROM
                rgp_transactions a
                LEFT JOIN ( SELECT rgp_number, item_id, sum( qty ) AS total FROM rgp_transactions WHERE type='IN' GROUP BY rgp_number,item_id ) b ON a.number = b.rgp_number and a.item_id=b.item_id
                LEFT JOIN (select rgp_number, item_id, sum(qty) as qty_scan from scan_item_rgp group by rgp_number, item_id) c on a.number=c.rgp_number and a.item_id=c.item_id
                WHERE
                a.type = 'OUT' 
                AND a.number LIKE '%$post%' 
                AND a.process_type = 'RGP' 
                AND (a.approved_to is null or a.approved_to = '')
                $status_scan_label
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

    public function readProcessType()
    {
        $send = array(
            array("id" => "RGP", "name" => "RGP IN"),
            array("id" => "NRGP", "name" => "RGP OUT TO NRGP IN"),
            array("id" => "FOC", "name" => "FOC IN"),
        );
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

            $sqlGetID    = $this->db->query("SELECT max(number) as kode FROM rgp_transactions WHERE type='IN' and number like '%$whereDate%'");
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

    public function autonumberfoc()
    {

        $datenow = date("ym");
        $sql = $this->db->query("SELECT max(`number`) as kode From rgp_transactions where `number` like '%FOC-$datenow%'");
        $row = $sql->row();
        if (empty($row->kode)) {
            $kode = substr($row->kode, 1);
            $autoid = "FOC-" . $datenow . sprintf("%08s", $kode + 1);
        } else {
            $kode = substr($row->kode, -8);
            $autoid = "FOC-" . $datenow . sprintf("%08s", $kode + 1);
        }
        echo $autoid;
    }

    public function autonumberNrgp()
    {

        $datenow = date("ym");
        $sql = $this->db->query("SELECT max(`number`) as kode From rgp_transactions where `number` like '%NRGP-$datenow%'");
        $row = $sql->row();
        if (empty($row->kode)) {
            $kode = substr($row->kode, 1);
            $autoid = "NRGP-" . $datenow . sprintf("%08s", $kode + 1);
        } else {
            $kode = substr($row->kode, -8);
            $autoid = "NRGP-" . $datenow . sprintf("%08s", $kode + 1);
        }
        echo $autoid;
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
                WHERE a.type = 'IN'
                GROUP BY a.number, a.item_id) z 
                GROUP BY z.number) j", 'a.number = j.number', 'left');
            $this->db->where('a.type', 'IN');
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

            a.company_id,a.uom, a.qty, a.currency, a.price, a.value, a.igst_value, a.sgst_value, a.cgst_value, a.total,a.number,a.rgp_id,
            
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
            a.item_category_id,
            b.id as item_rm_id,
            c.id as item_fg_id,
            c.qty_box,
            a.approved_to
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
            // $this->db->join('item_mpq k', 'a.item_id = k.item_rm_id and a.company_id=k.supplier_id', 'left');
            $this->db->where('a.number', $number);
            $this->db->order_by('a.item_id', 'ASC');
            $records = $this->db->get()->result_array();
            $result = [];
            foreach ($records as $record) {
                $item_mpq_supplier = $this->db->query("select mpq from item_mpq where item_rm_id='" . $record['item_rm_id'] . "' and supplier_id='" . $record['company_id'] . "'")->row();
                if (!empty($item_mpq_supplier)) {
                    $mpq = $item_mpq_supplier->mpq;
                    $qty_label = ceil($record['qty'] / $item_mpq_supplier->mpq);
                } else {
                    $item_mpq_customer = $this->db->query("select mpq from item_mpq where item_rm_id='" . $record['item_rm_id'] . "' and customer_id='" . $record['company_id'] . "'")->row();
                    if (!empty($item_mpq_customer)) {
                        $mpq = $item_mpq_customer->mpq;
                        $qty_label = ceil($record['qty'] / $item_mpq_customer->mpq);
                    } else if ($record['qty_box']) {
                        $mpq = $record['qty_box'];
                        $qty_label = ceil($record['qty'] / $record['qty_box']);
                    } else {
                        $mpq = "";
                        $qty_label = "";
                    }
                }

                $arr[] = array(
                    "item_id" => $record['item_id'],
                    "item_number" => $record['item_number'],
                    "item_name" => $record['item_name'],
                    "item_family_name" => $record['item_family_name'],
                    "uom" => $record['uom'],
                    "qty" => $record['qty'],
                    "currency" => $record['currency'],
                    "price" => $record['price'],
                    "value" => $record['value'],
                    "igst_value" => $record['igst_value'],
                    "sgst_value" => $record['sgst_value'],
                    "cgst_value" => $record['cgst_value'],
                    "total" => $record['total'],
                    "number" => $record['number'],
                    "rgp_id" => $record['rgp_id'],
                    "company_name" => $record['company_name'],
                    "plant_number" => $record['plant_number'],
                    "item_category_name" => $record['item_category_name'],
                    "departement_name" => $record['departement_name'],
                    "rgp_type_name" => $record['rgp_type_name'],
                    "item_category_id" => $record['item_category_id'],
                    "approved_to" => $record['approved_to'],

                    "mpq" => $mpq,
                    "qty_label" => $qty_label,
                );
            }
            $result = array_merge($result, @$arr);
            echo json_encode($result);
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
                $this->db->join("(select number, item_id, sum(qty) as qty_in from rgp_transactions where type='OUT' group by number, item_id) k", 'a.rgp_number=k.number and a.item_id=k.item_id', 'left');
                $this->db->join("(select rgp_number, item_id, sum(qty) as qty_out from rgp_transactions where type='IN' group by rgp_number, item_id) l", 'a.rgp_number=l.rgp_number and a.item_id=l.item_id', 'left');
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
                $this->db->join("(select rgp_number, item_id, qty as qty_out from rgp_transactions where type = 'IN') k", 'k.rgp_number=a.number and a.item_id=k.item_id', 'left');
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
                    select * from rgp_transactions a where a.number = '" . $post['number'] . "' and a.item_id='" . $item_id . "' and a.type='IN'
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
                // "return_date_estimation" => $post['return_date_estimation'],
                "rgp_number" => $post['rgp_number'],
                "notes" => $post['notes'],
                "item_id" => $item_id,
                "uom" => $post['uom'],
                // "stock" => $post['stock'],
                "qty" => $qty,
                "hsn_code" => $post['hsn_code'],
                "currency" => $post['currency'],
                "price" => $post['price'],
                "value" => $value,
                "igst_value" => $igst_value,
                "sgst_value" => $sgst_value,
                "cgst_value" => $cgst_value,
                "total" => $total,
                "type" => "IN",
                // "declaration" => $post['declaration'],
                "status" => $status,
            );
            $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $post['number'], "item_id" => $item_id, "type" => "IN"]);
            if (!empty(@$rgp_transactions->number)) {
                $send = $this->crud->update('rgp_transactions', ["number" => $post['number'], "item_id" => $item_id, "type" => "IN"], $postFinal);
            } else {
                $sqlGetID = $this->db->query("SELECT max(rgp_id) as kode FROM rgp_transactions WHERE `number` = '" . $post['number'] . "'");
                $rowID = $sqlGetID->row();
                $id = $rowID->kode;
                if ($id == NULL) {
                    $rgp_id = $post['number'] . "-" . sprintf("%03s", $id + 1);
                } else {
                    $urutan = (int) substr($id, -3);
                    $rgp_id = $post['number'] . "-" . sprintf("%03s", $urutan + 1);
                }

                $send = $this->crud->create('rgp_transactions', array_merge($postFinal, ["rgp_id" => $rgp_id]));

                $approval = $this->crud->read('approvals', [], ["table_name" => "rgp_transaction_in"]);
                if (!empty($approval)) {
                    $formApprove = [
                        "approved" => 1,
                        "approved_to" => $approval->user_approval_1,
                        "approved_by" => $this->session->username,
                    ];

                    $this->db->update('rgp_transactions', $formApprove, ["number" => $post['number'], "item_id" => $item_id, "type" => "IN"]);
                }
            }

            $rgp_out = $this->db->query("
            select sum(qty) as qty from rgp_transactions a where a.number = '" . $post['rgp_number'] . "' and a.item_id='" . $item_id . "' and a.type='OUT'
            ")->row();

            $rgp_in = $this->db->query("
            select sum(qty) as qty from rgp_transactions a where a.rgp_number = '" . $post['rgp_number'] . "' and a.item_id='" . $item_id . "' and a.type='IN'
            ")->row();

            // upddate semua status yg terkait dengan rgp in
            if (@$rgp_out->qty == @$rgp_in->qty) {
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
        $dataLabels = array("rgp_number" => $data['number']);
        $this->crud->delete('rgp_transaction_labels', $dataLabels);
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
                    @unlink("assets/documents/rgp_in/" . base64_decode($file_bf));
                    @move_uploaded_file($temporary, 'assets/documents/rgp_in/' . $fileName);
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
        @unlink('failed/rgp_in.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/rgp_in.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/rgp_in.txt";
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

            $rgp_in = $this->crud->read('rgp_in', [], ["item_fg_id" => @$item_fg->id, "item_rm_id" => @$item_rm->id]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No EBWS " . $data['item_fg_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No EBWS " . $data['item_rm_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($process_main->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Main " . $data['main_process'] . " Not Found", "theme" => "error"));
            } elseif (!empty($rgp_in->item_rm_id)) {
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

                $send   = $this->crud->create('rgp_in', $dataFinal);
                echo $send;
            }
        }
    }

    // public function deletergp_in(){
    //     $query = $this->db->query("SELECT a.item_fg_id, c.number_customer as item_fg_number, a.item_rm_id, b.number as item_rm_number, COUNT(a.id) as total 
    //         FROM rgp_in a JOIN item_rm b ON a.item_rm_id = b.id 
    //         JOIN item_fg c ON a.item_fg_id = c.id
    //         GROUP BY a.item_fg_id, a.item_rm_id 
    //         HAVING COUNT(a.id) > 1
    //         LIMIT 1");
    //     $querys = $query->result_array();

    //     foreach ($querys as $query) {
    //         $item_fg_id = $query['item_fg_id'];
    //         $item_rm_id = $query['item_rm_id'];

    //         $rgp_in = $this->db->query("SELECT a.id, a.item_fg_id, c.number_customer as item_fg_number, a.item_rm_id, b.number as item_rm_number
    //             FROM rgp_in a JOIN item_rm b ON a.item_rm_id = b.id 
    //             JOIN item_fg c ON a.item_fg_id = c.id 
    //             WHERE item_fg_id = '$item_fg_id' and a.item_rm_id = '$item_rm_id'
    //             ORDER BY a.created_date desc");
    //         $rgp_ins = $rgp_in->result_array();

    //         for ($i=1; $i < $query['total']; $i++) {
    //             echo "<p>".$rgp_ins[$i]['id']."</p>";

    //             $this->db->delete('rgp_in', ["id" => $rgp_ins[$i]['id']]);
    //         }
    //     }
    // }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=rgp_in_$format.xls");
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
        $this->db->where('a.type', 'IN');
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
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#rgp_in {border-collapse: collapse;width: 100%;font-size: 12px;}#rgp_in td, #rgp_in th {border: 1px solid #ddd;padding: 2px;}#rgp_in tr:nth-child(even){background-color: #f2f2f2;}#rgp_in tr:hover {background-color: #ddd;}#rgp_in th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>RGP/NRGP IN</h3>
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

    public function create_check_label()
    {
        $post = $this->input->post();
        $item_fg = $this->crud->read("item_fg", [], ["number" => $post['item_id']]);
        if (!empty(@$item_fg->id)) {
            $item_id = @$item_fg->id;
        } else {
            $item_id = $post['item_id'];
        }
        $rgp_transaction_labels = $this->crud->reads('rgp_transaction_labels', [], ["rgp_number" => $post['number'], "item_id" => $item_id]);
        die(json_encode(array("total" => count($rgp_transaction_labels))));
    }

    public function create_label()
    {
        $post = $this->input->post();
        $rgp_number = $post['rgp_number'];
        $qty = $post['qty'];

        $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM rgp_transaction_labels WHERE rgp_number = '$rgp_number'");
        $rowID = $sqlGetID->row();
        $label = $rowID->kode;
        if ($label == NULL) {
            $autoID = $rgp_number . sprintf("%04s", $label + 1);
        } else {
            $urutan = (int) substr($label, -4);
            $autoID = $rgp_number . sprintf("%04s", $urutan + 1);
        }

        $item_fg = $this->crud->read("item_fg", [], ["number" => $post['item_id']]);
        if (!empty(@$item_fg->id)) {
            $item_id = @$item_fg->id;
        } else {
            $item_id = $post['item_id'];
        }

        $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $post['rgp_number'], "item_id" => $item_id]);

        //Simpan Label
        $arrLabel = [
            "rgp_number" => strtoupper($rgp_number),
            "rgp_id" => strtoupper(@$rgp_transactions->rgp_id),
            "label_no" => strtoupper($autoID),
            "item_id" => $item_id,
            "qty" => $qty,
        ];

        $send = $this->crud->create('rgp_transaction_labels', $arrLabel);
        die($send);
    }

    public function print_label($rgp_number, $item_category_id, $item_id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $rgp_number = base64_decode($rgp_number);

        $item_fg = $this->crud->read("item_fg", [], ["number" => base64_decode($item_id)]);
        if (!empty(@$item_fg->id)) {
            $item_id = @$item_fg->id;
        } else {
            $item_id = base64_decode($item_id);
        }
        //Cek Label

        $this->db->select("
        (CASE 
                WHEN c.id is not null THEN c.id
                ELSE d.number
            END) as item_id,
            (CASE 
                WHEN c.id is not null THEN c.number
                ELSE d.number_customer
            END) as item_number,
            (CASE 
                WHEN c.id is not null THEN c.name
                ELSE d.name
            END) as item_name,
            b.uom, a.qty,a.label_no,b.transaction_date,b.company_id,
            (CASE 
                WHEN e.id is not null THEN e.name
                ELSE f.name
            END) as company_name,
            g.is_dol, g.id as dol_id
        ");
        $this->db->from('rgp_transaction_labels a');
        $this->db->join('rgp_transactions b', 'a.rgp_number = b.number');
        $this->db->join('item_rm c', 'a.item_id = c.id', 'left');
        $this->db->join('item_fg d', 'a.item_id = d.id', 'left');
        $this->db->join('suppliers e', 'b.company_id = e.id', 'left');
        $this->db->join('customers f', 'b.company_id = f.id', 'left');
        $this->db->join('dol_items g', 'b.company_id = g.supplier_id and a.item_id = g.item_rm_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.item_id', $item_id);
        //$this->db->where('a.status', 0);
        $this->db->group_by('a.label_no');
        $this->db->where('a.rgp_number', $rgp_number);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();

        // die($this->db->last_query());
        $html = '<html>
                    <head>
                        <title>' . $rgp_number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            if ($item_category_id != "C08") { // JIKA Bukan finish good
                $html .= '<div style="width: 44mm;">';
                $no = 1;
                foreach ($records as $record) {
                    if ($no == 3) {
                        $no = 1;
                    }
                    if ($no == 1) {
                        $padding = "padding:2mm 3mm 3mm 2mm;";
                    } else {
                        $padding = "padding:2mm 3mm 3mm 2mm;";
                    }

                    if(empty(@$record->dol_id)){
                        die("NOT AVAILABLE IN DOL MASTER");
                    }else{
                        if(@$record->is_dol == "0"){
                            $dol = "<b style='color:green; font-size:15px;'>DOL</b>";
                        }else{
                            $dol = "<b style='color:red; font-size:15px;'>NON DOL</b>";
                        }
                    }

                    //Generate QRcode
                    $this->createQrcode($record->label_no, "assets/image/qrcode/");
                    $html .= '  <div style="width: 44mm; max-height:40mm; float:left; ' . $padding . '">
                                    <table id="customers" border="1" style="margin-bottom:20px;">
                                        <tr>
                                            <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="height:35px;">
                                                <div style="float:left;">
                                                    <small style="font-size:7px;"><b>' . $record->item_number . '</b></small><br>
                                                    <small style="font-size:12px;"><b>' . $record->item_id . '</b></small>
                                                    <br>
                                                    <b style="font-size:7px;"></b>
                                                </div>
                                                
                                                <div style="float:right;">
                                                    <small style="font-size:15px;"><b></b></small><small style="font-size:10px;"><b></b></small>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left">
                                                <small>Quantity</small><br><b style="font-size:20px;">' . number_format($record->qty, 2) . '</b></small><br>
                                                <small style="font-size:15px; float: right;"><b>' . $record->uom . '</b></small>
                                            </th>
                                            <th style="text-align:left">
                                                '.$dol.'
                                            </th>
                                        </tr>
                                        
                                        <tr>
                                            <th style="text-align:left">
                                                <div style="display: inline-block;">
                                                    <small>Date :</small><br> 
                                                    <b style="font-size:8px;">' . $record->transaction_date . '</b>
                                                </div>
                                                <div style="display: inline-block;">
                                                    <small>Label No :</small><br>
                                                    <b style="font-size:8px;">' . $record->label_no . '</b>
                                                </div>
                                            </th>
                                            <th style="text-align:center;">
                                                <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50"/>
                                            </th>
                                        </tr>
                                    </table>
                                </div>';
                    $no++;
                }
                $html .= '</div><script>window.print()</script>';
            } else {
                $this->createQrcode($rgp_number, "assets/image/qrcode/");
                $no = 1;
                foreach ($records as $record) {
                    $qrcodes = $record->label_no;
                    $this->createQrcode($qrcodes, "assets/image/qrcode/", $record->label_no);
                    $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1">
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $rgp_number . '.png') . '" width="50"/>
                                        </td>
                                        <td colspan="2" style="font-size:15px; text-align:left;"><span style="font-size:10px;">Checksheet ID</span><br><b>' . $rgp_number . '</b></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Name.</td>
                                        <td colspan="2"><small style="font-size:12px;"><span style="font-size:10px;">' . $record->company_id . '</span><br><b>' . $record->company_name . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">EBWS P/N</td>
                                        <td colspan="2"><small style="font-size:30px;"><b>' . $record->item_id . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $record->item_number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Label. No</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $record->label_no . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Quantity</td>
                                        <td style="width:25mm;"><small style="font-size:20px;"><b>' . number_format($record->qty, 2) . '</b></small></td>
                                        <td style="text-align:center;" style="width:25mm" rowspan="3">
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="100"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Date</td>
                                        <td><small style="font-size:16px;"><b>' . $record->transaction_date . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:center;"><img src="' . $config->favicon . '" width="60"/></td>
                                    </tr>
                                </table>
                            </div>';
                    $no++;
                }
                $html .= '</div><script>window.print()</script>';
            }
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }
}
