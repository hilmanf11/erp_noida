<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_orders extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->library('Ciqrcode');
        $this->load->library('email');

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Part No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_orders');
        } else {
            redirect('error_access');
        }
    }

    public function readRevision()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');

        $this->db->select('max(revision) as revision');
        $this->db->from('generate_mrp_finals');
        $this->db->where('p_month', $p_month);
        $this->db->where('p_year', $p_year);
        // $this->db->group_by('revision');
        $rev = $this->db->get()->row();

        // die(json_encode($rev));

        $revision = @$rev->revision;

        echo json_encode(array("rev" => $revision));
    }

    function readPoSearch()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $datas = $this->crud->query("SELECT DISTINCT po_no FROM purchase_orders WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision='$filter_revision'");
        echo json_encode($datas);
    }

    function readPo($p_month, $p_year, $revision, $product_family)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $datas = $this->crud->query("SELECT DISTINCT a.supplier_id, a.po_no, b.number as supplier_no, b.name as supplier_name, d.name as item_family_name 
            FROM purchase_orders a
            JOIN suppliers b ON a.supplier_id = b.id 
            JOIN item_rm c ON a.item_rm_id = c.id
            JOIN item_familys d ON c.item_family_id = d.id
            WHERE a.p_month = '$p_month' and a.p_year = '$p_year' and a.revision = '$revision' and d.id = '$product_family'
            and (a.po_no like '%$post%' or b.name like '%$post%' or d.name like '%$post%')");
        echo json_encode($datas);
    }

    function readPono()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($this->input->get('supplier_id'));
        $datas = $this->crud->query("SELECT DISTINCT a.po_no, a.po_date FROM purchase_orders a WHERE a.supplier_id = '$supplier_id' and a.status = 0 and (a.approved_to = '' or a.approved_to is null) and (po_date like '%$post%' or po_no like '%$post%') ORDER BY a.po_date, a.po_no ASC");
        echo json_encode($datas);
    }

    public function readSupplier()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $data = $this->crud->query("SELECT DISTINCT b.id, b.number, b.name FROM purchase_orders a JOIN suppliers b ON a.supplier_id = b.id WHERE b.name like '%$post%' ORDER BY b.name asc");
        echo json_encode($data);
    }

    public function readTerm()
    {
        $po_no = $this->input->post('po_no');
        $send = $this->crud->read('purchase_orders', [], ["po_no" => $po_no]);
        echo json_encode($send);
    }

    function forecast($item_rm_id, $month, $year, $revision)
    {
        $q_forecast = $this->db->query("SELECT a.item_rm_id, c.number as item_rm_number, c.name as item_rm_name, SUM(a.composition) as qty, 
            (SUM(a.composition) * b.month_1) as m1, 
            (SUM(a.composition) * b.month_2) as m2, 
            (SUM(a.composition) * b.month_3) as m3,
            (SUM(a.composition) * b.month_4) as m4,
            (SUM(a.composition) * b.month_5) as m5,
            (SUM(a.composition) * b.month_6) as m6,
            (SUM(a.composition) * b.month_7) as m7,
            (SUM(a.composition) * b.month_8) as m8,
            (SUM(a.composition) * b.month_9) as m9,
            (SUM(a.composition) * b.month_10) as m10,
            (SUM(a.composition) * b.month_11) as m11,
            (SUM(a.composition) * b.month_12) as m12
            FROM bom a
            JOIN forecasts b ON a.item_fg_id = b.item_fg_id
            JOIN item_rm c ON a.item_rm_id = c.id
            WHERE b.p_month = '$month' and b.p_year = '$year' and b.revision = '$revision' 
            and (b.month_1 > 0 or b.month_2 > 0 or b.month_3 > 0) and a.item_rm_id = '$item_rm_id'
            GROUP BY a.item_rm_id");
        $forecast = $q_forecast->row();

        return $forecast;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));
            $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
            $filter_po = trim(base64_decode($this->input->get('filter_po')));
            $filter_type = trim(base64_decode($this->input->get('filter_type')));
            $filter_status = trim(base64_decode($this->input->get('filter_status')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, (CASE WHEN COUNT(a.status) = COALESCE(f.total_status_close, 0) THEN 1 ELSE 0 END) as status_po, SUM(a.qty) as total_qty, SUM(a.total) as total_amount, COUNT(c.po_no) as print_sd, d.number as supplier_no, d.name as supplier_name, d.type as supplier_type, d.supplier_po, e.name as item_family_name, d.state');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', "a.item_rm_id = b.id");
            $this->db->join('(select DISTINCT po_no from delivery_schedules) c', "a.po_no = c.po_no", 'left');
            $this->db->join('suppliers d', "a.supplier_id = d.id", 'left');
            $this->db->join('item_familys e', "b.item_family_id = e.id");
            $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) f', 'a.po_no = f.po_no', 'left');
            $this->db->where('a.type', "PO");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('b.item_family_id', $filter_item_family);
            $this->db->like('a.item_rm_id', $filter_item_rm);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.po_no', $filter_po);
            $this->db->like('d.type', $filter_type);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.po_no');
            $this->db->order_by('a.po_no');
            $this->db->order_by('a.status');
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

    public function datatableDetails($po_no, $revision)
    {
        $po_no = base64_decode($po_no);
        $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));

        $po = $this->crud->read("purchase_orders", [], ["po_no" => $po_no]);
        $start_date = date("Y-m-d");
        $valid_date = date("Y-m-t", strtotime(@$po->p_year . "-" . @$po->p_month . "-10"));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Select Query
        $this->db->select("a.*, g.purchase_order as mrp_qty, b.number as supplier_no, b.name as supplier_name, f.mpq, f.moq, b.state,
            b.email, d.number as item_rm_number, d.name as item_rm_name, d.item_family_id, e.name as product_family, b.type as supplier_type, 
            ((a.qty * a.price) * COALESCE(i.igst, 0) / 100) as digst,
            ((a.qty * a.price) * COALESCE(i.sgst, 0) / 100) as dsgst,
            ((a.qty * a.price) * COALESCE(i.cgst, 0) / 100) as dcgst,
            (CASE WHEN a.price = f.price THEN 'OK' ELSE 'SYNC' END) as sync, f.price as new_price");
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('purchase_requests c', 'a.request_no = c.request_no and a.item_rm_id = c.item_rm_id', 'left');
        $this->db->join('item_rm d', 'a.item_rm_id = d.id');
        $this->db->join('item_familys e', 'd.item_family_id = e.id');
        $this->db->join('supplier_items f', 'a.supplier_id = f.supplier_id and a.item_rm_id = f.item_rm_id', 'left');
        $this->db->join('generate_mrp_finals g', 'a.p_month = g.p_month and a.p_year = g.p_year and a.revision = g.revision and a.item_rm_id = g.item_rm_id and a.supplier_id = g.supplier_id', 'left');
        $this->db->join('purchase_requests h', 'a.request_no = h.request_no', 'left');
        $this->db->join('hsn i', "d.hsn_code = i.number", 'left');
        $this->db->where('a.type', "PO");
        $this->db->where('a.po_no', $po_no);
        $this->db->where('a.revision', $revision);
        $this->db->like('a.item_rm_id', $filter_item_rm);
        $this->db->group_by('a.item_rm_id');
        $this->db->order_by('a.item_rm_id');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $total_qty = 0;
        $total_amount = 0;
        $total_igst = 0;
        $total_cgst = 0;
        $total_sgst = 0;
        $total_amount_tax = 0;
        foreach ($records as $record) {

            if ($record['state'] == $config->state) {
                $total2 = ($record['total'] + $record['dcgst'] + $record['dsgst']);
                $total_igst += 0;
                $total_cgst += $record['dcgst'];
                $total_sgst += $record['dsgst'];
                $digst = 0;
                $dsgst = $record['dsgst'];
                $dcgst = $record['dcgst'];
            } else {
                if ($record['supplier_type'] == "LOCAL") {
                    $total2 = ($record['total'] + $record['digst']);
                    $total_igst += $record['digst'];
                    $total_cgst += 0;
                    $total_sgst += 0;
                    $digst = $record['digst'];
                    $dsgst = 0;
                    $dcgst = 0;
                } else {
                    $total2 = ($record['total']);
                    $total_igst += 0;
                    $total_cgst += 0;
                    $total_sgst += 0;
                    $digst = 0;
                    $dsgst = 0;
                    $dcgst = 0;
                }
            }

            $data_amount = array("total_amount" => $total2, "igst" => $digst, "cgst" => $dcgst, "sgst" => $dsgst);

            $total_qty += $record['qty'];
            $total_amount += $record['total'];
            $total_amount_tax += $total2;
            array_push($data, array_merge($record, $data_amount));
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $data,
            'footer' => array([
                "item_rm_id" => "Grand Total",
                "qty" => $total_qty,
                "price" => 0,
                "sync" => 0,
                "total" => $total_amount,
                "igst" => $total_igst,
                "sgst" => $total_sgst,
                "cgst" => $total_cgst,
                "total_amount" => $total_amount_tax,
            ])
        ]);
        echo json_encode($result);
    }

    public function datatableMrp()
    {
        $get = $this->input->get();
        $mrp_month = base64_decode($get['mrp_month']);
        $mrp_year = base64_decode($get['mrp_year']);
        $mrp_revision = base64_decode($get['mrp_revision']);
        $mrp_profam = base64_decode($get['mrp_profam']);
        $mrp_supplier = base64_decode($get['mrp_supplier']);
        $mrp_item_rm_id = base64_decode($get['mrp_item_rm_id']);

        //Select Query
        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, e.name as supplier_name, b.uom, e.currency, c.name as product_family, d.class, d.composition, d.safety, f.price');
        $this->db->from('generate_mrp_finals a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('generate_mrp_abcclass d', 'a.item_rm_id = d.item_rm_id and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
        $this->db->join('suppliers e', 'a.supplier_id = e.id');
        $this->db->join('supplier_items f', "a.item_rm_id = f.item_rm_id and a.supplier_id = f.supplier_id and (f.approved_to = '' or f.approved_to is null)");
        $this->db->where('a.status', 0);
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.p_month', $mrp_month);
        $this->db->where('a.p_year', $mrp_year);
        $this->db->where('a.revision', $mrp_revision);
        $this->db->where('a.supplier_id', $mrp_supplier);
        $this->db->like('b.item_family_id', $mrp_profam);
        $this->db->like('b.id', $mrp_item_rm_id);
        $this->db->group_by('a.item_rm_id');
        $this->db->group_by('a.supplier_id');
        $this->db->order_by('b.number', 'asc');
        // $this->db->limit(10);
        $totalRows = $this->db->count_all_results('', false);
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $finals = array();
        foreach ($records as $record) {
            $convert = $this->crud->read("convertions", [], ["item_rm_id" => $record['item_rm_id']]);
            if (empty($convert->uom_po)) {
                $uom = $record['uom'];
                $convertion = 1;
            } else {
                $uom = $convert->uom_po;
                $convertion = $convert->convertion;
            }

            $date = $record['p_year'] . "-" . $record['p_month'] . "-01";
            $item_rm_id = $record['item_rm_id'];
            $leadtime = $record['leadtime'];
            $revision = $record['revision'];

            $m1 = date('Y-m-01', strtotime($date, strtotime($leadtime)));
            $m2 = date('Y-m-01', strtotime($date, strtotime('+1 month', strtotime($leadtime))));
            $m3 = date('Y-m-01', strtotime($date, strtotime('+2 month', strtotime($leadtime))));
            $m11 = "m" . date('n', strtotime($date, strtotime($leadtime)));
            $m22 = "m" . date('n', strtotime($date, strtotime('+1 month', strtotime($leadtime))));
            $m33 = "m" . date('n', strtotime($date, strtotime('+2 month', strtotime($leadtime))));

            $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and item_rm_id = '$item_rm_id' and revision = '$revision'");
            $rpms1 = $qmps1->row();

            $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and item_rm_id = '$item_rm_id' and revision = '$revision'");
            $rpms2 = $qmps2->row();

            $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and item_rm_id = '$item_rm_id' and revision = '$revision'");
            $rpms3 = $qmps3->row();

            $forecast = $this->forecast($item_rm_id, $record['p_month'], $record['p_year'], @$revision);

            if (@$rpms1->qty > 0) {
                $prod1 = @$rpms1->qty;
            } else {
                $prod1 = @$forecast->$m11;
            }

            if (@$rpms2->qty > 0) {
                $prod2 = @$rpms2->qty;
            } else {
                $prod2 = @$forecast->$m22;
            }

            if (@$rpms3->qty > 0) {
                $prod3 = @$rpms3->qty;
            } else {
                $prod3 = @$forecast->$m33;
            }

            if (round($record['total_need']) > 0 && $record['moq'] > 0) {
                $final = ($record['total_need'] / $convertion);
                if ($final > $record['moq']) {
                    $mrp_result = (ceil($final / $record['mpq']) * $record['mpq']);
                } else {
                    $mrp_result = (ceil($final / $record['moq']) * $record['moq']);
                }
            } else {
                $mrp_result = 0;
            }

            $finals[] = array(
                "p_month" => $record['p_month'],
                "p_year" => $record['p_year'],
                "revision" => $record['revision'],
                "request_date" => date("Y-m-d"),
                "request_name" => $this->session->name,
                "item_rm_id" => $record['item_rm_id'],
                "item_rm_number" => $record['item_rm_number'],
                "item_rm_name" => $record['item_rm_name'],
                "product_family" => $record['product_family'],
                "supplier_id" => $record['supplier_id'],
                "supplier_name" => $record['supplier_name'],
                "leadtime" => $record['leadtime'],
                "uom" => $uom,
                "currency" => $record['currency'],
                "mpq" => $record['mpq'],
                "moq" => $record['moq'],
                "price" => $record['price'],
                "qty" => $mrp_result,
                "total" => ($mrp_result * $record['price']),
                "month_1" => $prod1,
                "month_2" => $prod2,
                "month_3" => $prod3,
            );
        }

        $result['rows'] = $finals;
        echo json_encode($result);
    }

    // public function datatableConfirm(){
    //     //Select Query
    //     $this->db->select('a.*, b.name as supplier_name, c.po_no, c.po_date, 
    //         SUM(a.qty) as total_pr, 
    //         SUM(c.qty) as total_po,
    //         SUM(c.total) as total_amount');
    //     $this->db->from('purchase_requests a');
    //     $this->db->join('suppliers b', 'a.supplier_id = b.id');
    //     $this->db->join('purchase_orders c', 'a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id and a.request_no = c.request_no');
    //     $this->db->where('a.confirm', 0);
    //     $this->db->group_by('a.request_no');
    //     $this->db->order_by('a.updated_date');
    //     $this->db->order_by('a.created_date');
    //     //Total Data
    //     $totalRows = $this->db->count_all_results('', false);
    //     //Get Data Array
    //     $records = $this->db->get()->result_array();

    //     //Mapping Data
    //     $result['total'] = $totalRows;
    //     $result = array_merge($result, [
    //         'rows' => $records
    //     ]);
    //     echo json_encode($result);
    // }

    public function purchaseOrderNo($id_menu, $add = "")
    {
        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(po_no) as kode FROM purchase_orders WHERE po_no like '%$whereDate%'");
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

            if ($add == "") {
                return $autoNo;
            } else {
                die(json_encode(array("code" => $autoNo)));
            }
        }
    }

    public function getData()
    {
        if ($this->input->get()) {
            //Filter Data
            $request_no = base64_decode($this->input->get('request_no'));
            $id_menu = $this->input->get('id_menu');

            $pr = $this->crud->read("purchase_requests", ["request_no" => $request_no]);
            $this->crud->delete("purchase_orders", ["request_no" => $request_no]);

            //Select Query
            $this->db->select('a.*, b.number as pfm_number, c.uom,
                d.item_supplier, d.price, d.currency, d.leadtime,
                f.name as supplier_name, f.number as supplier_no');
            $this->db->from('purchase_requests a');
            $this->db->join('item_familys b', 'a.item_family_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('supplier_items d', "c.id = d.item_rm_id and a.supplier_id = d.supplier_id and d.share_order > 0", 'left');
            $this->db->join('suppliers f', 'a.supplier_id = f.id');
            $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.request_no', $request_no);
            $this->db->where('a.qty >', 0);
            $this->db->order_by('d.currency', 'asc');
            $purchase_requests = $this->db->get()->result_array();

            $arr = array();
            $end_currency = "-";
            $end_po_no = "";
            foreach ($purchase_requests as $purchase_request) {
                $date = $purchase_request['p_year'] . "-" . $purchase_request['p_month'] . "-01";
                $item_rm_id = $purchase_request['item_rm_id'];
                $leadtime = $purchase_request['leadtime'];
                $revision = $purchase_request['revision'];

                $m1 = date('Y-m-01', strtotime($date, strtotime($leadtime)));
                $m2 = date('Y-m-01', strtotime($date, strtotime('+1 month', strtotime($leadtime))));
                $m3 = date('Y-m-01', strtotime($date, strtotime('+2 month', strtotime($leadtime))));
                $m11 = "m" . date('n', strtotime($date, strtotime($leadtime)));
                $m22 = "m" . date('n', strtotime($date, strtotime('+1 month', strtotime($leadtime))));
                $m33 = "m" . date('n', strtotime($date, strtotime('+2 month', strtotime($leadtime))));

                $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and item_rm_id = '$item_rm_id' and revision = '$revision'");
                $rpms1 = $qmps1->row();

                $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and item_rm_id = '$item_rm_id' and revision = '$revision'");
                $rpms2 = $qmps2->row();

                $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and item_rm_id = '$item_rm_id' and revision = '$revision'");
                $rpms3 = $qmps3->row();

                $forecast = $this->forecast($item_rm_id, $purchase_request['p_month'], $purchase_request['p_year'], @$revision);

                if (@$rpms1->qty > 0) {
                    $prod1 = @$rpms1->qty;
                } else {
                    $prod1 = @$forecast->$m11;
                }

                if (@$rpms2->qty > 0) {
                    $prod2 = @$rpms2->qty;
                } else {
                    $prod2 = @$forecast->$m22;
                }

                if (@$rpms3->qty > 0) {
                    $prod3 = @$rpms3->qty;
                } else {
                    $prod3 = @$forecast->$m33;
                }

                $supplier_no = $purchase_request['supplier_no'];
                $leadtime = $purchase_request['leadtime'];
                $eta = date("Y-m-d", strtotime("+" . $leadtime . " days", strtotime($purchase_request['request_date'])));
                $period = $purchase_request['p_month'] . "/" . $purchase_request['p_year'];
                $delivery_date = date("Y-m-d", strtotime("+" . $leadtime . " days"));

                if ($purchase_request['currency'] == $end_currency) {
                    $po_no = $end_po_no;
                } else {
                    // $po_no = $this->purchaseOrderNo("MC".@trim($supplier_no).$period. "-" .trim($purchase_request['pfm_number']));
                    $po_no = $this->purchaseOrderNo($id_menu);
                }

                $arr[] = array(
                    "supplier_id" => $purchase_request['supplier_id'],
                    "item_rm_id" => $purchase_request['item_rm_id'],
                    "p_month" => $purchase_request['p_month'],
                    "p_year" => $purchase_request['p_year'],
                    "revision" => $purchase_request['revision'],
                    "request_no" => $request_no,
                    "request_date" => $purchase_request['request_date'],
                    "request_name" => $purchase_request['request_name'],
                    "po_no" => $po_no,
                    "po_date" => date("Y-m-d"),
                    "po_name" => $this->session->name,
                    "delivery_date" => $delivery_date,
                    "uom" => $purchase_request['uom'],
                    "qty" => $purchase_request['qty'],
                    "price" => $purchase_request['price'],
                    "total" => ($purchase_request['qty'] * $purchase_request['price']),
                    "currency" => $purchase_request['currency'],
                    "leadtime" => $purchase_request['leadtime'],
                    "month_1" => $prod1,
                    "month_2" => $prod2,
                    "month_3" => $prod3,
                );

                $end_currency = $purchase_request['currency'];
                $end_po_no = $po_no;
            }

            if (count($arr) > 0) {
                $this->db->update("purchase_requests", ["create_po" => date("Y-m-d H:i:s"), "confirm" => 0, "confirm_date" => null], ["request_no" => $request_no]);
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $po_no = $this->input->post('po_no');
            $po_date = $this->input->post('po_date');
            $post = $this->input->post('data');

            // $records = $this->crud->reads('purchase_orders', [], [
            //     "p_month" => $post['p_month'],
            //     "p_year" => $post['p_year'],
            //     "revision" => $post['revision'],
            //     "item_rm_id" => $post['item_rm_id'],
            //     "supplier_id" => $post['supplier_id']
            // ]);

            // if (count($records) > 0) {
            //     $send = $this->db->update('purchase_orders', [
            //         "qty" => $post['qty'],
            //         "price" => $post['price'],
            //         "total" => $post['total'],
            //         "currency" => $post['currency'],
            //         "month_1" => $post['month_1'],
            //         "month_2" => $post['month_2'],
            //         "month_3" => $post['month_3']
            //     ],[
            //         "p_month" => $post['p_month'],
            //         "p_year" => $post['p_year'],
            //         "revision" => $post['revision'],
            //         "item_rm_id" => $post['item_rm_id'],
            //         "supplier_id" => $post['supplier_id']
            //     ]);

            //     echo $send;
            // } else {
            $postFinal = array(
                "supplier_id" => $post['supplier_id'],
                "item_rm_id" => $post['item_rm_id'],
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "request_date" => $post['request_date'],
                "request_name" => $post['request_name'],
                "po_no" => $po_no,
                "po_date" => $po_date,
                "delivery_date" => date("Y-m-d", strtotime("+" . $post['leadtime'] . " day", strtotime($po_date))),
                "uom" => $post['uom'],
                "qty" => $post['qty'],
                "price" => $post['price'],
                "total" => $post['total'],
                "currency" => $post['currency'],
                "leadtime" => $post['leadtime'],
                "month_1" => $post['month_1'],
                "month_2" => $post['month_2'],
                "month_3" => $post['month_3'],
            );

            $this->db->update('generate_mrp_finals', ["status" => 1], ["p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision'], "item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']]);
            $send = $this->crud->create('purchase_orders', $postFinal);
            $this->db->update('purchase_orders', ["approved_to" => ""], ["po_no" => $po_no, "item_rm_id" => $post['item_rm_id']]);
            echo $send;
            // }
        }
    }

    public function create2()
    {
        if ($this->input->post('data')) {
            $post = $this->input->post('data');

            $records = $this->crud->reads('purchase_orders', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_rm_id" => $post['item_rm_id'],
                "supplier_id" => $post['supplier_id']
            ]);

            if (count($records) > 0) {
                $send = $this->db->update('purchase_orders', [
                    "qty" => $post['qty'],
                    "price" => $post['price'],
                    "total" => $post['total'],
                    "currency" => $post['currency'],
                    "month_1" => $post['month_1'],
                    "month_2" => $post['month_2'],
                    "month_3" => $post['month_3']
                ], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $post['item_rm_id'],
                    "supplier_id" => $post['supplier_id']
                ]);

                echo $send;
            } else {
                $postFinal = array(
                    "supplier_id" => $post['supplier_id'],
                    "item_rm_id" => $post['item_rm_id'],
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "request_no" => $post['request_no'],
                    "request_date" => $post['request_date'],
                    "request_name" => $post['request_name'],
                    "po_no" => $post['po_no'],
                    "po_date" => $post['po_date'],
                    "po_name" => $post['po_name'],
                    "delivery_date" => date("Y-m-d", strtotime("+" . $post['leadtime'] . " day", strtotime($post['po_date']))),
                    "uom" => $post['uom'],
                    "qty" => $post['qty'],
                    "price" => $post['price'],
                    "total" => $post['total'],
                    "currency" => $post['currency'],
                    "leadtime" => $post['leadtime'],
                    "month_1" => $post['month_1'],
                    "month_2" => $post['month_2'],
                    "month_3" => $post['month_3'],
                );

                $send = $this->crud->create('purchase_orders', $postFinal);
                echo $send;
            }
        }
    }

    // public function updateConfirm(){
    //     if ($this->input->post()) {
    //         $request_no = $this->input->post('request_no');
    //         $this->db->update('purchase_requests', ["confirm" => 1, "confirm_date" => date("Y-m-d H:i:s")], ["request_no" => $request_no]);

    //         echo json_encode(array("message" => "success"));
    //     }
    // }

    public function completeMail()
    {
        if ($this->input->post()) {
            $po_no = $this->input->post('po_no');
            $email_date = $this->input->post('email_date');

            $this->db->update('purchase_orders', ["email_status" => "SENT", "confirm" => "Pending", "email_date" => $email_date], ["po_no" => $po_no]);

            echo json_encode(array("message" => "Complete Mail Success"));
        }
    }

    public function syncPrice()
    {
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $data = $this->crud->read("purchase_orders", [], ["id" => $id]);
            $po_no = @$data->po_no;
            $maxRev = $this->crud->query("SELECT max(rev) as rev FROM purchase_orders WHERE po_no = '$po_no'");

            $sync_date = date("Y-m-d H:i:s");
            $sync_by = $this->session->username;

            $this->db->select('a.price, a.currency');
            $this->db->from('supplier_items a');
            $this->db->where('a.supplier_id', $data->supplier_id);
            $this->db->where('a.item_rm_id', $data->item_rm_id);
            $supplier_item = $this->db->get()->row();

            if (!empty($supplier_item)) {
                $total = ($data->qty * $supplier_item->price);
                $update = $this->db->update('purchase_orders', ["price" => $supplier_item->price, "currency" => $supplier_item->currency, "total" => $total, "rev" => (@$maxRev->rev + 1), "sync_date" => $sync_date, "sync_by" => $sync_by], ["id" => $id]);
                if ($update) {
                    echo json_encode(array("title" => "Good Job", "message" => $data->item_rm_id . " | Sync Price Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $data->item_rm_id . " | Sync Price Failed", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Error", "message" => "Valid Date not match", "theme" => "error"));
            }
        }
    }

    // public function pushData(){
    //     if ($this->input->post()) {
    //         $this->dummy = $this->load->database('dummy', TRUE);

    //         $post = $this->input->post('data');

    //         $this->dummy->select('*');
    //         $this->dummy->from("mst_item");
    //         $this->dummy->where("item_id", $post['item_rm_number']);
    //         $mst_item = $this->dummy->get()->row();

    //         $this->dummy->select('*');
    //         $this->dummy->from("poc");
    //         $this->dummy->where("poc_id", $post['po_no']);
    //         $this->dummy->where("item_id", $post['item_rm_number']);
    //         $poc = $this->dummy->get()->row();

    //         // $gpai = $this->crudSupplier($post['supplier_no']);
    //         // $supplier = $gpai['data'];

    //         $postFinal = array(
    //             "poc_id" => $post['po_no'],
    //             "ven_id" => @$mst_item->ven_id,
    //             // "ven_id" => @$supplier[0]['alias_code'],
    //             "poc_date" => $post['po_date'],
    //             "poc_emp" => $this->session->name,
    //             "item_id" => $post['item_rm_number'],
    //             "poc_reqdate" => date("Y-m-d"),
    //             "poc_reqqty" => $post['qty'],
    //             "currency" => $post['currency'],
    //             "periode" => date("Ym"),
    //             // "poc_remark" => $post['reason'],
    //         );

    //         if($poc){
    //             $this->dummy->where("poc_id", $post['po_no']);
    //             $this->dummy->where("item_id", $post['item_rm_number']);
    //             if ($this->dummy->update('poc', ["poc_reqqty" => $post['qty']])) {
    //                 echo json_encode(array("title" => "Good Job", "message" => $post['item_rm_number'] . " | Data Update Successfully", "theme" => "success"));
    //             } else {
    //                 echo json_encode(array("title" => "Error", "message" => $post['item_rm_number'] . " | Data Unsaved", "theme" => "error"));
    //             }
    //         }else{
    //             if ($this->dummy->insert('poc', $postFinal)) {
    //                 echo json_encode(array("title" => "Good Job", "message" => $post['item_rm_number'] . " | Data Saved Successfully", "theme" => "success"));
    //             } else {
    //                 echo json_encode(array("title" => "Error", "message" => $post['item_rm_number'] . " | Data Unsaved", "theme" => "error"));
    //             }
    //         }

    //         $this->db->where("item_rm_id", $post['item_rm_id']);
    //         $this->db->where("po_no", $post['po_no']);
    //         $this->db->update('purchase_orders', ["push_date" => date("Y-m-d H:i:s"), "push" => "OK"]);
    //     }
    // }

    public function updateDetails()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));

            $post = $this->input->post();
            $total = round($post['qty'] * $post['price'], 4);
            $databf = $this->crud->read("purchase_orders", [], ["id" => $id]);

            $po_no = @$databf->po_no;
            $maxRev = $this->crud->query("SELECT max(rev) as rev FROM purchase_orders WHERE po_no = '$po_no'");

            $send = $this->crud->update(
                'purchase_orders',
                ["id" => $id],
                [
                    "po_no" => $post['po_no'],
                    "supplier_id" => $post['supplier_id'],
                    "qty" => $post['qty'],
                    "price" => $post['price'],
                    "currency" => $post['currency'],
                    "total" => $total,
                    "remarks" => $post['remarks'],
                    "status_edit" => 1,
                    "rev" => (@$maxRev->rev + 1),
                    "log" => json_encode(["qty" => $databf->qty, "total" => $databf->total, "revision" => $databf->revision, "log" => $databf->log, "remarks" => $databf->remarks])
                ]
            );

            if ($send) {
                echo json_encode(array("title" => "Good Job", "message" => "Update data Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Update data Failed", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updateNote()
    {
        if ($this->input->post()) {
            $po_no = $this->input->post('po_no');
            $note = $this->input->post('note');

            $send = $this->db->update('purchase_orders', [
                "note" => $note
            ], ["po_no" => $po_no]);

            if ($send) {
                echo json_encode(array("title" => "Good Job", "message" => "Update Note Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Update Note Failed", "theme" => "error"));
            }
        }
    }

    public function updateTerms()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->db->update('purchase_orders', ["terms" => $post['terms']], ["po_no" => $post['po_no']]);
            echo json_encode(array("title" => "Good Job", "message" => "Update Temrs & Conditions Successfully", "theme" => "success"));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updatePrice()
    {
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $price = $this->input->post('price');
            $price_old = $this->input->post('price_old');

            $sync_date = date("Y-m-d H:i:s");
            $sync_by = $this->session->username;

            $po = $this->crud->read("purchase_orders", [], ["id" => $id]);
            $total = ($po->qty * $price);

            $send = $this->db->update('purchase_orders', [
                "sync_by" => $sync_by,
                "sync_date" => $sync_date,
                "price_old" => $price_old,
                "price" => $price,
                "total" => $total,
            ], ["id" => $id]);

            if ($send) {
                echo json_encode(array("title" => "Good Job", "message" => "Sync Price Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Sync Price Failed", "theme" => "error"));
            }
        }
    }

    public function updateStatus()
    {
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $status = $this->input->post('status');

            if ($status == "0") {
                $status_f = 1;
            } else {
                $status_f = 0;
            }

            $send = $this->db->update('purchase_orders', [
                "status" => $status_f,
                "complete" => $status_f,
            ], ["id" => $id]);

            if ($send) {
                echo json_encode(array("title" => "Good Job", "message" => "Update Status Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Update Status Failed", "theme" => "error"));
            }
        }
    }

    public function delete()
    {
        $data = $this->input->post();

        $reads = $this->crud->reads('purchase_orders', ["po_no" => $data['po_no']]);
        $por = $this->crud->reads('purchase_confirm', ["po_no" => $data['po_no']]);

        if (empty($por)) {
            foreach ($reads as $read) {
                $this->db->update('generate_mrp_finals', ["status" => 0], [
                    "p_month" => $read->p_month,
                    "p_year" => $read->p_year,
                    "revision" => $read->revision,
                    "item_rm_id" => $read->item_rm_id,
                    "supplier_id" => $read->supplier_id,
                ]);
            }

            $send = $this->crud->delete('purchase_orders', ["po_no" => $data['po_no']]);
            echo $send;
        } else {
            echo json_encode(array("title" => "Failed", "message" => "this PO already created in Gate Entry", "theme" => "error"));
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

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'po_no' => $data->val($i, 2),
                'po_date' => $data->val($i, 3),
                'po_name' => $data->val($i, 4),
                'supplier_id' => $data->val($i, 5),
                'item_rm_id' => $data->val($i, 6),
                'delivery_date' => $data->val($i, 7),
                'qty' => $data->val($i, 8),
                'month_1' => $data->val($i, 9),
                'month_2' => $data->val($i, 10),
                'month_3' => $data->val($i, 11),
                'remarks' => $data->val($i, 12)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_orders.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_orders.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_orders.txt";
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
            $post = $this->input->post('data');

            $item_rm = $this->crud->read("item_rm", [], ["id" => $post['item_rm_id']]);
            $supplier = $this->crud->read("suppliers", [], ["id" => $post['supplier_id']]);
            $supplier_items = $this->crud->read("supplier_items", [], ["item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']]);
            $purchase_orders = $this->crud->read("purchase_orders", [], ["item_rm_id" => $post['item_rm_id'], "po_no" => $post['po_no'], "supplier_id" => $post['supplier_id']]);

            if (empty($item_rm)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $post['item_rm_id'] . " Not Found in Master Items", "theme" => "error"));
            } elseif (empty($supplier)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier ID " . $post['supplier_id'] . " Not Found in Master Suppliers", "theme" => "error"));
            } elseif (empty($supplier_items)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier and Item Not Found in Supplier Items", "theme" => "error"));
            } elseif ($supplier_items->approved_to != "") {
                echo json_encode(array("title" => "Not Approved", "message" => "Part No " . $post['item_rm_id'] . " in Supplier Item Not Approved", "theme" => "error"));
            } elseif (!empty($purchase_orders)) {
                echo json_encode(array("title" => "Duplicate", "message" => "Part No " . $post['item_rm_id'] . " in PO " . $post['po_no'] . " is Duplicate", "theme" => "error"));
            } else {
                $postFinal = array_merge($post, array(
                    "p_month" => date("m", strtotime($post['po_date'])),
                    "p_year" => date("Y", strtotime($post['po_date'])),
                    "uom" => $item_rm->uom,
                    "price" => $supplier_items->price,
                    "total" => ($post['qty'] * $supplier_items->price),
                    "currency" => $supplier_items->currency,
                    "leadtime" => $supplier_items->leadtime,
                    "remarks" => $post['remarks']
                ));

                $send = $this->crud->create('purchase_orders', $postFinal);
                echo $send;
            }
        }
    }

    public function print_po($po_no, $export = "")
    {
        $purchase_orders_total = $this->crud->reads('purchase_orders', [], ["po_no" => base64_decode($po_no)]);
        $purchase_orders = $this->crud->read('purchase_orders', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => @$purchase_orders->supplier_id]);
        $approval = $this->crud->read('approvals', [], ["table_name" => "purchase_orders"]);

        $user_3 = $this->crud->read('users', [], ["username" => @$approval->user_approval_2]);
        $user_4 = $this->crud->read('users', [], ["username" => @$approval->user_approval_3]);

        $qr_user_3 = "";
        $qr_user_4 = "";

        if ($purchase_orders->approved == 3) {
            $qr_user_3 = "src='" . base_url('assets/image/qrcode/' . @$user_3->name . '.png') . "'";
        } elseif ($purchase_orders->approved == 4) {
            $qr_user_3 = "src='" . base_url('assets/image/qrcode/' . @$user_3->name . '.png') . "'";
            $qr_user_4 = "src='" . base_url('assets/image/qrcode/' . @$user_4->name . '.png') . "'";
        }

        // $month_1 =  date('M Y', strtotime('+1 month', strtotime(@$purchase_orders->po_date)));
        // $month_2 =  date('M Y', strtotime('+2 month', strtotime(@$purchase_orders->po_date)));
        // $month_3 =  date('M Y', strtotime('+3 month', strtotime(@$purchase_orders->po_date)));

        $config = $this->db->get('config')->row();

        //Config Page
        $rows = 20;
        $page = ceil(count($purchase_orders_total) / $rows);
        //Generate QRcode
        // $this->createQrcode($purchase_orders->po_no, "assets/image/qrcode/");

        $this->createQrcode(@$user_3->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_4->name, "assets/image/qrcode/");

        $media = "@media screen {
                        .print {
                            display: none !important;
                        }
                    }
                    @media print {
                        .noprint {
                            display: none !important;
                        }
                    }";
        $media2 = ' <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                        </center>
                    </div>';


        $html = '<html>
                    <head>
                        <title>' . $purchase_orders->po_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        .customers {
                            border-collapse: collapse;
                            font-size: 12px;
                        }
                        .customers td, .customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        .customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        ' . $media . '
                    </style>
                    <body>
                        ' . $media2 . '
                        <div class="print">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*,
                b.number as item_rm_number, 
                b.name as item_rm_name,
                c.item_supplier,
                d.type,
                ((a.qty * a.price) * COALESCE(e.igst, 0) / 100) as digst,
                ((a.qty * a.price) * COALESCE(e.sgst, 0) / 100) as dsgst,
                ((a.qty * a.price) * COALESCE(e.cgst, 0) / 100) as dcgst,
                d.state');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('supplier_items c', 'a.supplier_id = c.supplier_id and a.item_rm_id = c.item_rm_id', 'left');
            $this->db->join('suppliers d', 'a.supplier_id = d.id', 'left');
            $this->db->join('hsn e', "b.hsn_code = e.number", 'left');
            $this->db->where('a.po_no', base64_decode($po_no));
            $this->db->where('a.qty >', 0);
            $this->db->order_by('a.item_rm_id');
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();

            if ($purchase_orders->updated_date != null) {
                $revision_date = $purchase_orders->updated_date;
            } else {
                $revision_date = $purchase_orders->created_date;
            }
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%; float:left;">
                            <div style="padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;">DELIVERY SCHEDULE</h3>
                                </center>
                                <br>
                                <div style="width:45%; float:left;">
                                    <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                        <tr>
                                            <td width="20" style="vertical-align: top;"><b>To</b></td>
                                            <td width="5" style="vertical-align: top;">:</td>
                                            <td><b>' . @$supplier->name . '</b><br>' . @$supplier->address . '<br>GST No : ' . @$supplier->gst . '</td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width:10%; float:left;"><br></div>
                                <div style="width:45%; float:left;">
                                    <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                        <tr>
                                            <td width="50" style="vertical-align: top;"></td>
                                            <td width="30" style="vertical-align: top;"></td>
                                            <td>PO No : ' . $supplier->supplier_po . '<br>
                                                Register No : ' . $purchase_orders->po_no . '<br>
                                                Date : ' . $purchase_orders->po_date . '<br>
                                                Revision : ' . $purchase_orders->revision . '<br><br>
                                                <b>ELCON BANSHU WIRING SYSTEM PVT. LTD.</b><br>
                                                Factory / Shipping Address : <br>
                                                ' . @$config->address2 . '<br>
                                                GST No : ' . @$config->gst_no . '
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <table class="customers" style="width:100%;">
                                    <tr>
                                        <th style="text-align:center;">No</th>
                                        <th style="text-align:center;">Part No EBWS</th>
                                        <th style="text-align:center;">Part No Supplier</th>
                                        <th style="text-align:center;">Part Name</th>
                                        <th style="text-align:center;">ETD</th>
                                        <th style="text-align:center;">Currency</th>
                                        <th style="text-align:center;">Price</th>
                                        <th style="text-align:center;">Qty</th>
                                        <th style="text-align:center;">UOM</th>
                                        <th style="text-align:center;">Total Price</th>
                                        <th style="text-align:center;">CGST</th>
                                        <th style="text-align:center;">SGST</th>
                                        <th style="text-align:center;">IGST</th>
                                        <th style="text-align:center;">Total Amount</th>
                                    </tr>';
            $row = 0;
            $total_igst = 0;
            $total_cgst = 0;
            $total_sgst = 0;
            foreach ($records as $record) {
                $subtotal += ($record['total']);

                if ($record['state'] == $config->state) {
                    $total2 = ($record['total'] + $record['dcgst'] + $record['dsgst']);
                    $total_igst += 0;
                    $total_cgst += $record['dcgst'];
                    $total_sgst += $record['dsgst'];
                    $digst = 0;
                    $dsgst = $record['dsgst'];
                    $dcgst = $record['dcgst'];
                } else {
                    if ($record['type'] == "LOCAL") {
                        $total2 = ($record['total'] + $record['digst']);
                        $total_igst += $record['digst'];
                        $total_cgst += 0;
                        $total_sgst += 0;
                        $digst = $record['digst'];
                        $dsgst = 0;
                        $dcgst = 0;
                    } else {
                        $total2 = ($record['total']);
                        $total_igst += 0;
                        $total_cgst += 0;
                        $total_sgst += 0;
                        $digst = 0;
                        $dsgst = 0;
                        $dcgst = 0;
                    }
                }

                if (!empty($record['reason'])) {
                    $styles = "background:#FFDD9E;";
                } else {
                    $styles = "";
                }

                if ($record['currency'] == "INR") {
                    $digit = 4;
                } else {
                    $digit = 5;
                }

                $html .= '  <tr style="' . $styles . '">
                                <td  style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_rm_number'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td>' . $record['delivery_date'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . number_format($record['price'], $digit) . '</td>
                                <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format($record['total'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($dcgst, 2) . '</td>
                                <td style="text-align:right;">' . number_format($dsgst, 2) . '</td>
                                <td style="text-align:right;">' . number_format($digst, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total2, 2) . '</td>
                            </tr>';
                $row++;
                $no++;
            }

            if (($i + 1) == $page) {
                $html .= '  <tr>
                                <td style="vertical-align: top; text-align:left;" colspan="10" rowspan="6">
                                    <b>Note :</b> <br>' . @$purchase_orders->note;
                $html .= '      </td>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">Sub Total</th>
                                <th style="text-align:right;">' . number_format($subtotal, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">CGST</th>
                                <th style="text-align:right;">' . number_format($total_cgst, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">SGST</th>
                                <th style="text-align:right;">' . number_format($total_sgst, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">IGST</th>
                                <th style="text-align:right;">' . number_format($total_igst, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">Total Amount</th>
                                <th style="text-align:right;">' . number_format(($subtotal + ($total_cgst + $total_sgst + $total_igst)), 2) . '</th>
                            </tr>
                        </table>';
            } else {
                $html .= '</table>';
            }

            if (($i + 1) == $page) {
                $html .= '  <div style="width:100%">
                                <div style="width:70%; float:left;">
                                    <br><br>
                                    <div style="font-size:12px !important;">
                                        <b style="font-size:16px !important;">Term & Condition</b><br><br>
                                        ' . $purchase_orders->terms . '
                                    </div>
                                </div>
                                <div style="width:30%; float:left; right: 50px;">
                                    <br><br><br>
                                    <table style="margin-top:20px; font-size:12px;">
                                        <tr>
                                            <th colspan="2" width="800" style="text-align:left;">' . $config->name . '</th>
                                        </tr>
                                        <tr>
                                            <th style="height:80px;"><img ' . $qr_user_3 . ' width="80"/></th>
                                        </tr>
                                        <tr>
                                            <th style="height:20px; text-align:left;">Authorized Signatory <br>Vishal Sharma</th>
                                        </tr>
                                    </table>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>';
            } else {
                $html .= "</div></div>";
            }

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;

            // <img '.$qr_user_4.' width="80"/>
        }

        $html .= '<script>window.print()</script>';
        die($html);
    }

    public function print_delivery($po_no)
    {
        $po_no = base64_decode($po_no);
        $purchase_order = $this->crud->read('purchase_orders', [], ["po_no" => $po_no], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => @$purchase_order->supplier_id]);

        $filter_year = $purchase_order->p_year;
        $filter_month = $purchase_order->p_month;
        $revision = $purchase_order->revision;

        $period_1 = date("F Y", strtotime($filter_year . "-" . $filter_month . "-01"));
        $period_2 = date("F Y",  strtotime("1 month", strtotime($filter_year . "-" . $filter_month . "-01")));
        $period_3 = date("F Y",  strtotime("2 month", strtotime($filter_year . "-" . $filter_month . "-01")));
        $period_4 = date("F Y",  strtotime("3 month", strtotime($filter_year . "-" . $filter_month . "-01")));
        $period_5 = date("F Y",  strtotime("4 month", strtotime($filter_year . "-" . $filter_month . "-01")));
        $period_6 = date("F Y",  strtotime("5 month", strtotime($filter_year . "-" . $filter_month . "-01")));

        $config = $this->db->get('config')->row();

        $html = '<html>
                    <head>
                        <title>Schedule Delivery ' . $po_no . '</title>
                        <link rel="icon" href="' . base_url('assets/image/config/' . $config->favicon) . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 10px;
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

        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, c.mpq, c.moq, c.leadtime');
        $this->db->from('delivery_schedules a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('supplier_items c', 'a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id');
        $this->db->where('a.po_no', $po_no);
        $this->db->order_by('a.item_rm_id');
        $records = $this->db->get()->result_array();

        $html .= '  <table style="width:100%;">
                        <tr>
                            <th width="10">
                                <img src="' . $config->favicon . '" width="40" />
                            </th>
                            <td width="300" style="padding:10px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                            <th width="100" style="text-align:right;"></th>
                        </tr>
                    </table>
                        <div style="padding:10px;">
                            <center>
                                <br>
                                <h3 style="margin:0;"><u>SCHEDULE DELIVERY</u></h3>
                                <small>NO : ' . @$po_no . '</small>
                            </center>
                            <table style="width:50%; font-size:12px; margin-bottom:10px;">
                                <tr>
                                    <td width="20">Period</td>
                                    <td width="10">:</td>
                                    <td width="100"><b>' . date("F Y", strtotime($purchase_order->p_year . "-" . $purchase_order->p_month . "-01")) . '</b></td>
                                </tr>
                                <tr>
                                    <td width="20">Revision</td>
                                    <td width="10">:</td>
                                    <td><b>' . $purchase_order->revision . '</b></td>
                                </tr>
                                <tr>
                                    <td width="20">Supplier</td>
                                    <td width="10">:</td>
                                    <td><b>' . @$supplier->name . '</b></td>
                                </tr>
                            </table>
                            <table id="customers">
                                <tr>
                                    <th rowspan="2" width="10" style="text-align:center;">No</th>
                                    <th rowspan="2" width="100" style="text-align:center;">Part No</th>
                                    <th rowspan="2" width="50" style="text-align:center;">Part Name</th>
                                    <th rowspan="2" width="30" style="text-align:center;">LT</th>
                                    <th rowspan="2" width="50" style="text-align:center;">MPQ</th>
                                    <th rowspan="2" width="50" style="text-align:center;">MOQ</th>
                                    <th rowspan="2" width="50" style="text-align:center;">Qty</th>
                                    <th colspan="3" width="50" style="text-align:center;">' . $period_1 . '</th>
                                    <th colspan="3" width="50" style="text-align:center;">' . $period_2 . '</th>
                                    <th colspan="3" width="50" style="text-align:center;">' . $period_3 . '</th>
                                    <th colspan="3" width="50" style="text-align:center;">' . $period_4 . '</th>
                                    <th colspan="3" width="50" style="text-align:center;">' . $period_5 . '</th>
                                    <th colspan="3" width="50" style="text-align:center;">' . $period_6 . '</th>
                                </tr>
                                <tr>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                </tr>';
        $no = 1;
        foreach ($records as $record) {
            if ($record['delivey_date_1'] == "0000-00-00") {
                $eta_1 = "-";
            } else {
                $eta_1 = $record['delivey_date_1'];
            }
            if ($record['delivey_date_2'] == "0000-00-00") {
                $eta_2 = "-";
            } else {
                $eta_2 = $record['delivey_date_2'];
            }
            if ($record['delivey_date_3'] == "0000-00-00") {
                $eta_3 = "-";
            } else {
                $eta_3 = $record['delivey_date_3'];
            }
            if ($record['delivey_date_4'] == "0000-00-00") {
                $eta_4 = "-";
            } else {
                $eta_4 = $record['delivey_date_4'];
            }
            if ($record['delivey_date_5'] == "0000-00-00") {
                $eta_5 = "-";
            } else {
                $eta_5 = $record['delivey_date_5'];
            }
            if ($record['delivey_date_6'] == "0000-00-00") {
                $eta_6 = "-";
            } else {
                $eta_6 = $record['delivey_date_6'];
            }
            if ($record['etd_1'] == "0000-00-00") {
                $etd_1 = "-";
            } else {
                $etd_1 = $record['etd_1'];
            }
            if ($record['etd_2'] == "0000-00-00") {
                $etd_2 = "-";
            } else {
                $etd_2 = $record['etd_2'];
            }
            if ($record['etd_3'] == "0000-00-00") {
                $etd_3 = "-";
            } else {
                $etd_3 = $record['etd_3'];
            }
            if ($record['etd_4'] == "0000-00-00") {
                $etd_4 = "-";
            } else {
                $etd_4 = $record['etd_4'];
            }
            if ($record['etd_5'] == "0000-00-00") {
                $etd_5 = "-";
            } else {
                $etd_5 = $record['etd_5'];
            }
            if ($record['etd_6'] == "0000-00-00") {
                $etd_6 = "-";
            } else {
                $etd_6 = $record['etd_6'];
            }

            $html .= '  <tr>
                            <td  style="text-align:center;">' . $no . '</td>
                            <td>' . $record['item_rm_number'] . '</td>
                            <td>' . $record['item_rm_name'] . '</td>
                            <td style="text-align:right;">' . number_format($record['leadtime']) . '</td>
                            <td style="text-align:right;">' . number_format($record['mpq']) . '</td>
                            <td style="text-align:right;">' . number_format($record['moq']) . '</td>
                            <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_1']) . '</td>
                            <td style="text-align:center;">' . $etd_1 . '</td>
                            <td style="text-align:center;">' . $eta_1 . '</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_2']) . '</td>
                            <td style="text-align:center;">' . $etd_2 . '</td>
                            <td style="text-align:center;">' . $eta_2 . '</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_3']) . '</td>
                            <td style="text-align:center;">' . $etd_3 . '</td>
                            <td style="text-align:center;">' . $eta_3 . '</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_4']) . '</td>
                            <td style="text-align:center;">' . $etd_4 . '</td>
                            <td style="text-align:center;">' . $eta_4 . '</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_5']) . '</td>
                            <td style="text-align:center;">' . $etd_5 . '</td>
                            <td style="text-align:center;">' . $eta_5 . '</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_6']) . '</td>
                            <td style="text-align:center;">' . $etd_6 . '</td>
                            <td style="text-align:center;">' . $eta_6 . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table>';
        $html .= '<script>window.print()</script>';
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("YmdHis");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = trim(base64_decode($this->input->get('filter_item_family')));
        $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));
        $filter_type = trim(base64_decode($this->input->get('filter_type')));
        $filter_status = trim(base64_decode($this->input->get('filter_status')));

        //Select Query
        $this->db->select('a.*, b.name as item_rm_name, b.number as item_rm_number, c.number as supplier_no, 
            c.name as supplier_name,
            c.supplier_po,
            c.type,
            c.state,
            ((a.qty * a.price) * COALESCE(e.igst, 0) / 100) as digst,
            ((a.qty * a.price) * COALESCE(e.sgst, 0) / 100) as dsgst,
            ((a.qty * a.price) * COALESCE(e.cgst, 0) / 100) as dcgst,
            d.name as item_family_name');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('item_familys d', 'b.item_family_id = d.id');
        $this->db->join('hsn e', "b.hsn_code = e.number", 'left');
        $this->db->where('a.type', "PO");
        $this->db->like('a.p_month', $filter_month);
        $this->db->like('a.p_year', $filter_year);
        $this->db->like('a.revision', $filter_revision);
        if ($filter_item_family != "") {
            $this->db->where('d.id', $filter_item_family);
        }
        if ($filter_item_rm != "") {
            $this->db->where('a.item_rm_id', $filter_item_rm);
        }
        if ($filter_supplier != "") {
            $this->db->where('a.supplier_id', $filter_supplier);
        }
        if ($filter_po != "") {
            $this->db->where('a.po_no', $filter_po);
        }
        if ($filter_type != "") {
            $this->db->where('c.type', $filter_type);
        }
        if ($filter_status != "") {
            $this->db->where('a.status', $filter_status);
        }
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('d.name', 'asc');
        $this->db->order_by('a.item_rm_id', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->logo . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>PURCHASE ORDER</small>
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
        <table style="font-size: 12px;">
            <tr>
                <td width="80">PERIOD</td>
                <td width="20">:</td>
                <td>' . $filter_month . '/' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="20">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        
        <table id="customers" border="1">
            <tr>
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">Approved</th>
                <th style="text-align:center;">Approved By</th>
                <th style="text-align:center;">Approved Date</th>
                <th style="text-align:center;">PO No</th>
                <th style="text-align:center;">Register No</th>
                <th style="text-align:center;">Register Date</th>
                <th style="text-align:center;">Part No EBWS</th>
                <th style="text-align:center;">Part No Supplier</th>
                <th style="text-align:center;">Part Name</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Supplier No</th>
                <th style="text-align:center;">Supplier Name</th>
                <th style="text-align:center;">Supplier Type</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Price</th>
                <th style="text-align:center;">Total</th>
                <th style="text-align:center;">IGST</th>
                <th style="text-align:center;">SGST</th>
                <th style="text-align:center;">CGST</th>
                <th style="text-align:center;">Total After Tax</th>
                <th style="text-align:center;">Currency</th>
                <th style="text-align:center;">Uom</th>
                <th style="text-align:center;">Leadtime</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Created By</th>
                <th style="text-align:center;">Created Date</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            if ($data['state'] == $config->state) {
                $digst = 0;
                $dsgst = round($data['dsgst'], 2);
                $dcgst = round($data['dcgst']);
            } else {
                if ($data['type'] == "LOCAL") {
                    $digst = round($data['digst'], 2);
                    $dsgst = 0;
                    $dcgst = 0;
                } else {
                    $digst = 0;
                    $dsgst = 0;
                    $dcgst = 0;
                }
            }

            $total2 = ($data['total'] + $digst + $dcgst + $dsgst);

            if ($data['status'] == "0") {
                $status = "OPEN";
            } else {
                $status = "CLOSE";
            }

            if ($data['complete'] == 1) {
                $status = "COMPLETE";
            }

            if ($data['approved_to'] == "") {
                $approved = "APPROVED";
            } else {
                $approved = "CHECKING";
            }

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $approved . '</td>
                            <td>' . $data['approved_by'] . '</td>
                            <td>' . $data['approved_date'] . '</td>
                            <td>' . $data['supplier_po'] . '</td>
                            <td>' . $data['po_no'] . '</td>
                            <td>' . $data['po_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td>' . $data['item_rm_name'] . '</td>
                            <td>' . $data['item_family_name'] . '</td>
                            <td>' . $data['supplier_id'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['type'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td style="text-align:right;">' . $data['price'] . '</td>
                            <td style="text-align:right;">' . $data['total'] . '</td>
                            <td style="text-align:right;">' . $digst . '</td>
                            <td style="text-align:right;">' . $dsgst . '</td>
                            <td style="text-align:right;">' . $dcgst . '</td>
                            <td style="text-align:right;">' . $total2 . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['leadtime'] . '</td>
                            <td>' . $status . '</td>
                            <td>' . $data['created_by'] . '</td>
                            <td>' . $data['created_date'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    // public function sendMail(){
    //     error_reporting(0);
    //     $post = $this->input->post();

    //     $mail_to = explode(";", $post['mail_to']);
    //     // $mail_to = "hilmanfadillah11@gmail.com";
    //     $mail_cc = explode(";", $post['mail_cc']);
    //     // $mail_cc = "aeconsys.dev@gmail.com";
    //     $mail_format = $post['mail_format'];
    //     $mail_po = $post['mail_po'];

    //     $message = $this->crud->read("config_mail", [], ["module" => "purchase_orders", "language" => $mail_format]);
    //     $po = $this->crud->read("purchase_orders", [], ["po_no" => $mail_po]);
    //     $supplier = $this->crud->read("suppliers", [], ["id" => @$po->supplier_id]);
    //     $contact_name = @$supplier->attention;
    //     $company = @$supplier->attention;
    //     $address = @$supplier->address;

    //     //Setting Upload Image
    //     $file = $_FILES["attachment"]["name"];
    //     $extension_explode = explode('.', $file);
    //     $extension_final = strtolower(end($extension_explode));
    //     $temporary = $_FILES["attachment"]['tmp_name'];

    //     $file2 = $_FILES["attachment2"]["name"];
    //     $extension_explode2 = explode('.', $file2);
    //     $extension_final2 = strtolower(end($extension_explode2));
    //     $temporary2 = $_FILES["attachment2"]['tmp_name'];

    //     $file3 = $_FILES["attachment3"]["name"];
    //     $extension_explode3 = explode('.', $file3);
    //     $extension_final3 = strtolower(end($extension_explode3));
    //     $temporary3 = $_FILES["attachment3"]['tmp_name'];

    //     $file4 = $_FILES["attachment4"]["name"];
    //     $extension_explode4 = explode('.', $file4);
    //     $extension_final4 = strtolower(end($extension_explode4));
    //     $temporary4 = $_FILES["attachment4"]['tmp_name'];

    //     $file5 = $_FILES["attachment5"]["name"];
    //     $extension_explode5 = explode('.', $file5);
    //     $extension_final5 = strtolower(end($extension_explode5));
    //     $temporary5 = $_FILES["attachment5"]['tmp_name'];

    //     if ((in_array($extension_final, ["pdf"]) === true || $file != "") && (in_array($extension_final2, ["pdf"]) === true || $file2 != "")) {
    //             @move_uploaded_file($temporary, "assets/po/" . $file);
    //             @move_uploaded_file($temporary2, "assets/po/" . $file2);
    //             @move_uploaded_file($temporary3, "assets/po/" . $file3);
    //             @move_uploaded_file($temporary4, "assets/po/" . $file4);
    //             @move_uploaded_file($temporary5, "assets/po/" . $file5);
    //             sleep(3);

    //             $config = array(
    //                 'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
    //                 'smtp_host' => 'mail.banshu-ind.com',
    //                 'smtp_port' => 465,
    //                 'smtp_user' => 'procurement01@banshu-ind.com',
    //                 'smtp_pass' => 'pRo!177$BeiAi',
    //                 'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
    //                 'mailtype' => 'html', //plaintext 'text' mails or 'html'
    //                 'smtp_timeout' => '4', //in seconds
    //                 'charset' => 'iso-8859-1',
    //                 'wordwrap' => TRUE
    //             );

    //             $description = $message->description;
    //             $description = str_replace("{{contact}}", $contact_name, $description);
    //             $description = str_replace("{{po_no}}", $mail_po, $description);
    //             $description = str_replace("{{company}}", $company, $description);
    //             $description = str_replace("{{address}}", $address, $description);

    //             $this->email->initialize($config);
    //             $this->email->from($post['mail_from'], 'BANSHU ELECTRIC INDONESIA');
    //             $this->email->to($mail_to);
    //             $this->email->cc($mail_cc);
    //             $this->email->subject($post['mail_subject']);
    //             $this->email->message($description);

    //             // Attach the PDF file
    //             $attachment = 'assets/po/'.$file; // Replace with the path to your PDF file
    //             $attachment2 = 'assets/po/'.$file2; // Replace with the path to your PDF file
    //             $attachment3 = 'assets/po/'.$file3; // Replace with the path to your PDF file
    //             $attachment4 = 'assets/po/'.$file4; // Replace with the path to your PDF file
    //             $attachment5 = 'assets/po/'.$file5; // Replace with the path to your PDF file

    //             $this->email->attach($attachment);
    //             $this->email->attach($attachment2);
    //             if($file3 != ""){
    //                 $this->email->attach($attachment3);
    //             }
    //             if($file4 != ""){
    //                 $this->email->attach($attachment4);
    //             }
    //             if($file5 != ""){
    //                 $this->email->attach($attachment5);
    //             }

    //             if ($this->email->send()) {
    //                 echo json_encode(array("title" => "Successs", "message" => "Email Sending Successfully", "theme" => "success"));

    //                 $this->db->where("po_no", $mail_po);
    //                 $this->db->update('purchase_orders', ["email_date" => date("Y-m-d H:i:s"), "email_status" => "SENT", "confirm" => "Pending"]);

    //             } else {
    //                 echo json_encode(array("title" => "Failed", "message" => "Email Sending Failed", "theme" => "error"));
    //             }
    //     } else {
    //         show_error("Your extension file is not recognized", 200, "File Upload Error");
    //     }
    // }
}
