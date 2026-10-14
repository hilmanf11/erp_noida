<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Costing_products extends CI_Controller
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
            $this->load->view('costing/costing_products');
        } else {
            redirect('error_access');
        }
    }

    public function readProduct(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        // $item_rm = $this->crud->query("SELECT id, number, name FROM item_rm WHERE item_category_id = 'C01'");
        $item_fg = $this->crud->query("SELECT id, number_customer as number, name FROM item_fg WHERE number like '%$post%' or number_customer like '%$post%'");

        // $arr = array_merge($item_rm, $item_fg);
        die(json_encode($item_fg));
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-10 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => $i, "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function dataSupplySheets($filter_from, $filter_to, $item_fg_id, $table){
        $supply_sheets = $this->crud->query("SELECT a.item_rm_id, a.receipt_date, a.po_no, SUM(a.qty_out) AS qty, a.price_out, SUM(a.amount_out) AS amount FROM (
            SELECT (@no_urut := @no_urut + 1) AS no_urut, 
                c.number AS item_rm_number, c.name AS item_rm_name, c.uom, d.name AS item_family_name, z.created_by,
                z.trans_type, z.po_date, z.receipt_date, z.receipt_no, z.po_no, z.bc_document, z.bc_date,
                (CASE WHEN @item_rm_id = z.item_rm_id THEN '1' ELSE '0' END) AS typee,
                @begin_qty := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_qty ELSE COALESCE(bgin.begin_qty, 0) END, 2) AS begin_qty,
                @begin_price := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_price ELSE COALESCE(bgin.begin_price, 0) END, 4) AS begin_price,
                @begin_amount := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_amount ELSE COALESCE(bgin.begin_amount, 0) END, 4) AS begin_amount,
                @item_rm_id := z.item_rm_id AS item_rm_id,
                z.qty_in, 
                ROUND(CASE WHEN z.price_in = 0 THEN @begin_price ELSE z.price_in END, 4) AS price_in,
                ROUND(CASE WHEN z.amount_in = 0 THEN (z.qty_in * @begin_price) ELSE z.amount_in END, 4) AS amount_in,
                z.qty_out, 
                ROUND(CASE WHEN z.qty_in = 0 THEN @begin_price ELSE 0 END, 4) AS price_out, 
                (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END) AS amount_out,
                @balance_price := COALESCE(ROUND((@begin_amount + z.amount_in - (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END)) / (@begin_qty + z.qty_in - z.qty_out), 2), 0) AS balance_price,
                @balance_qty := COALESCE(ROUND(@begin_qty + z.qty_in - z.qty_out, 2), 0) AS balance_qty,
                @balance_amount := COALESCE(ROUND(@balance_qty * @balance_price, 4), 0) AS balance_amount
            FROM (SELECT a.* FROM (
                SELECT
                    a.id,
                    a.item_rm_id,
                    a.receipt_date,
                    'RECEIPT' AS trans_type,
                    b.po_date,
                    a.receipt_no,
                    b.po_no,
                    a.bc_document,
                    a.created_by,
                    a.bc_date,
                    c.qty_receipt AS qty_in,
                    b.price AS price_in,
                    (c.qty_receipt * b.price) AS amount_in,
                    0 AS qty_out
                FROM purchase_order_receipts a
                LEFT JOIN purchase_orders b ON a.item_rm_id = b.item_rm_id AND a.po_no = b.po_no
                JOIN (SELECT receipt_id, SUM(qty) AS qty_receipt FROM scan_item_receipts GROUP BY receipt_id) c ON a.receipt_id = c.receipt_id
                WHERE a.receipt_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.id
                UNION
                SELECT
                    a.id,
                    a.item_rm_id,
                    DATE_FORMAT(a.created_date, '%Y-%m-%d') AS receipt_date,
                    'ISSUED' AS trans_type,
                    '-' AS po_date,
                    '-' AS receipt_no,
                    a.request_no AS po_no,
                    a.label_no AS bc_document,
                    a.created_by,
                    '-' AS bc_date,
                    0 AS qty_in,
                    0 AS price_in,
                    0 AS amount_in,
                    a.qty AS qty_out
                FROM issued_material_details a
                JOIN $table b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
                WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND b.item_fg_id = '$item_fg_id'
                GROUP BY a.id) a
                ORDER BY a.item_rm_id, a.receipt_date ASC) z
            LEFT JOIN (SELECT 
                c.id AS item_rm_id, 
                (COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) AS begin_qty, 
                (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END) AS begin_price, 
                ((COALESCE(a.qty, 0) + COALESCE(SUM(zz.qty_in), 0) - COALESCE(SUM(zz.qty_out), 0)) * (CASE WHEN zz.balance_price IS NULL OR zz.balance_price = 0 THEN a.price ELSE zz.balance_price END)) AS begin_amount
                FROM item_rm c
                LEFT JOIN (
                SELECT (@no_urut := @no_urut + 1) AS no_urut, z.receipt_date,
                    (CASE WHEN @item_rm_id = z.item_rm_id THEN '1' ELSE '0' END) AS typee,
                    @begin_qty := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_qty ELSE 0 END, 2) AS begin_qty,
                    @begin_price := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_price ELSE 0 END, 4) AS begin_price,
                    @begin_amount := ROUND(CASE WHEN @item_rm_id = z.item_rm_id THEN @balance_amount ELSE 0 END, 4) AS begin_amount,
                    @item_rm_id := z.item_rm_id AS item_rm_id,
                    z.qty_in, 
                    ROUND(CASE WHEN z.price_in = 0 THEN @begin_price ELSE z.price_in END, 4) AS price_in,
                    ROUND(CASE WHEN z.amount_in = 0 THEN (z.qty_in * @begin_price) ELSE z.amount_in END, 4) AS amount_in,
                    z.qty_out, 
                    ROUND(CASE WHEN z.qty_in = 0 THEN @begin_price ELSE 0 END, 4) AS price_out, 
                    (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END) AS amount_out,
                    @balance_price := COALESCE(ROUND((@begin_amount + z.amount_in - (CASE WHEN z.qty_in = 0 THEN ROUND(z.qty_out * @begin_price, 4) ELSE 0 END)) / (@begin_qty + z.qty_in - z.qty_out), 4), 0) AS balance_price,
                    @balance_qty := COALESCE(ROUND(@begin_qty + z.qty_in - z.qty_out, 2), 0) AS balance_qty,
                    @balance_amount := COALESCE(ROUND(@balance_qty * @balance_price, 4), 0) AS balance_amount
                FROM (
                SELECT a.* FROM (
                    SELECT
                        a.id,
                        a.item_rm_id,
                        a.receipt_date,
                        c.qty_receipt AS qty_in,
                        b.price AS price_in,
                        (c.qty_receipt * b.price) AS amount_in,
                        0 AS qty_out
                    FROM purchase_order_receipts a
                    LEFT JOIN purchase_orders b ON a.item_rm_id = b.item_rm_id AND a.po_no = b.po_no
                    JOIN (SELECT receipt_id, SUM(qty) AS qty_receipt FROM scan_item_receipts GROUP BY receipt_id) c ON a.receipt_id = c.receipt_id
                    WHERE a.receipt_date < '$filter_from'
                    GROUP BY a.id
                    UNION
                    SELECT
                        a.id,
                        a.item_rm_id,
                        DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date,
                        0 AS qty_in,
                        0 AS price_in,
                        0 AS amount_in,
                        a.qty AS qty_out
                    FROM issued_material_details a
                    JOIN $table b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
                    WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') < '$filter_from' AND b.item_fg_id = '$item_fg_id'
                    GROUP BY a.id) a
                    ORDER BY a.item_rm_id, a.receipt_date ASC
                ) z,
                (SELECT @no_urut := 0, @balance_qty := 0.0000, @balance_price := 0.0000, @balance_amount := 0.0000) AS zz
                ORDER BY z.item_rm_id, z.receipt_date ASC) zz ON c.id = zz.item_rm_id
                LEFT JOIN inventory_rm a ON c.id = a.item_rm_id
                WHERE c.item_category_id = 'C01'
                GROUP BY c.id) bgin ON z.item_rm_id = bgin.item_rm_id
            JOIN item_rm c ON z.item_rm_id = c.id
            JOIN item_familys d ON c.item_family_id = d.id,
            (SELECT @no_urut := 0, @item_rm_id := 0) AS za
            WHERE c.item_category_id = 'C01'
            ORDER BY z.item_rm_id, z.receipt_date, no_urut ASC) a
            WHERE a.trans_type = 'ISSUED'
            GROUP BY a.item_rm_id, a.po_no");
        return $supply_sheets;
    }

    public function getData()
    {
        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $item_fg_id = $this->input->post('item_fg_id');
            $periode = $year . $month;

            $filter_from = date("Y-m-01", strtotime($periode));
            $filter_to = date("Y-m-t", strtotime($periode));

            $this->db->select('c.item_fg_id, a.number_customer, c.period, SUM(c.qty) AS qty');
            $this->db->from('item_fg a');
            $this->db->join('production_schedules c', "a.id = c.item_fg_id and c.month = '$month' and c.year = '$year'");
            $this->db->like('a.id', $item_fg_id);
            $this->db->group_by('c.item_fg_id');
            $this->db->order_by('a.number', 'desc');
            $this->db->limit(1);
            $items = $this->db->get()->result_array();

            $result['total'] = count($items);
            $result = array_merge($result, ['rows' => $items]);
            echo json_encode($result);

        }
    }

    public function getData2()
    {
        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $item_fg_id = $this->input->post('item_fg_id');
            $periode = $this->input->post('period');
            $qty = $this->input->post('qty');
            $periode2 = $year . "-" . $month . "-01";

            $filter_from = date("Y-m-01", strtotime($periode2));
            $filter_to = date("Y-m-t", strtotime($periode2));

            
            $supply_sheets = $this->dataSupplySheets($filter_from, $filter_to, $item_fg_id, "supply_sheets");

            $direct_material = 0;
            $details = array();
            foreach ($supply_sheets as $supply_sheet) {
                $details[] = array(
                    "item_fg_id" => $item_fg_id,
                    "item_rm_id" => $supply_sheet->item_rm_id,
                    "periode" => $periode,
                    "supply_type" => "KANBAN MATERIAL",
                    "supply_date" => $supply_sheet->receipt_date,
                    "supply_no" => $supply_sheet->po_no,
                    "issued" => abs($supply_sheet->qty),
                    "price" => abs($supply_sheet->price_out),
                    "amount" => abs($supply_sheet->amount),
                    "created_by" => $this->session->username,
                    "created_date" => date("Y-m-d H:i:s"),
                );

                $direct_material += abs($supply_sheet->amount);
            }

            $supply_materials = $this->dataSupplySheets($filter_from, $filter_to, $item_fg_id, "supply_materials");

            $non_supply = 0;
            foreach ($supply_materials as $supply_material) {
                $details[] = array(
                    "item_fg_id" => $item_fg_id,
                    "item_rm_id" => $supply_material->item_rm_id,
                    "periode" => $periode,
                    "supply_type" => "SAMPLE REQUISITION",
                    "supply_date" => $supply_material->receipt_date,
                    "supply_no" => $supply_material->po_no,
                    "issued" => abs($supply_material->qty),
                    "price" => abs($supply_material->price_out),
                    "amount" => abs($supply_material->amount),
                    "created_by" => $this->session->username,
                    "created_date" => date("Y-m-d H:i:s"),
                );

                $non_supply += abs($supply_material->amount);
            }

            $dataFinals = array(
                "created_by" => $this->session->username,
                "created_date" => date("Y-m-d H:i:s"),
                "item_fg_id" => $item_fg_id,
                "periode" => $periode,
                "qty" => $qty,
                "umh" => 0,
                "total_umh" => 0,
                "direct_labor" => 0,
                "direct_foh" => 0,
                "direct_overtime" => 0,
                "direct_material" => $direct_material,
                "direct_requestion" => $non_supply
            );

            $this->db->delete('costing_product_details', ["item_fg_id" => $item_fg_id, "periode" => $periode]);
            $this->db->delete('costing_product_workorders', ["item_fg_id" => $item_fg_id, "periode" => $periode]);
            $this->db->insert('costing_product_workorders', $dataFinals);

            $result['total'] = count($details);
            $result = array_merge($result, ['rows' => $details]);
            echo json_encode($result);
        }
    }

    public function getDataProducts(){
        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $item_fg_id = $this->input->post('item_fg_id');
            $periode = $year . $month;

            $this->db->select('item_fg_id,
                SUM(qty) as total_qty, 
                SUM(total_umh) as total_umh, 
                SUM(direct_labor) as direct_labor, 
                SUM(direct_foh) as direct_foh, 
                SUM(direct_material) as direct_material,
                SUM(direct_overtime) as direct_overtime,
                SUM(direct_requestion) as direct_requestion');
            $this->db->from('costing_product_workorders');
            $this->db->where('periode', $periode);
            $this->db->like('item_fg_id', $item_fg_id);
            $this->db->group_by('item_fg_id');
            $this->db->order_by('item_fg_id', 'asc');
            $items = $this->db->get()->result_array();

            $dataFinals = [];
            foreach ($items as $item) {
                $direct_total = ((($item['direct_material'] + $item['direct_requestion']) / $item['total_qty']) + (($item['direct_labor'] + $item['direct_overtime']) / $item['total_qty']) + ($item['direct_foh'] / $item['total_qty']));

                $dataFinals[] = array(
                    "item_fg_id" => $item['item_fg_id'],
                    "periode" => $periode,
                    "umh" => $item['total_umh'],
                    "total_qty" => $item['total_qty'],
                    "direct_material" => $item['direct_material'],
                    "direct_requestion" => $item['direct_requestion'],
                    "direct_material_total" => ($item['direct_material'] + $item['direct_requestion']),
                    "direct_material_pcs" => (($item['direct_material'] + $item['direct_requestion']) / $item['total_qty']),
                    "direct_labor" => $item['direct_labor'],
                    "direct_overtime" => $item['direct_overtime'],
                    "direct_labor_total" => ($item['direct_labor'] + $item['direct_overtime']),
                    "direct_labor_pcs" => (($item['direct_labor'] + $item['direct_overtime']) / $item['total_qty']),
                    "direct_foh" => $item['direct_foh'],
                    "direct_foh_pcs" => ($item['direct_foh'] / $item['total_qty']),
                    "direct_total" => $direct_total,
                );
            }

            $result['total'] = count($dataFinals);
            $result = array_merge($result, ['rows' => $dataFinals]);
            echo json_encode($result);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_item = base64_decode($this->input->get('filter_item'));

            $filter_period = $filter_year . $filter_month;

            //Select Query
            $this->db->select('a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name');
            $this->db->from('costing_products a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.periode', $filter_period);
            $this->db->like('a.item_fg_id', $filter_item);
            $this->db->order_by('a.total_qty', 'desc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();

            //Select Query
            $this->db->select('item_fg_id, 
                SUM(total_qty) as total_qty, 
                SUM(umh) as total_umh, 
                SUM(direct_material) as direct_material,
                SUM(direct_requestion) as direct_requestion,
                SUM(direct_material_total) as direct_material_total,
                SUM(direct_material_pcs) as direct_material_pcs,
                SUM(direct_labor) as direct_labor,
                SUM(direct_overtime) as direct_overtime,
                SUM(direct_labor_total) as direct_labor_total,
                SUM(direct_labor_pcs) as direct_labor_pcs,
                SUM(direct_foh) as direct_foh,
                SUM(direct_foh_pcs) as direct_foh_pcs,
                SUM(direct_total) as direct_total');
            $this->db->from('costing_products');
            $this->db->where('periode', $filter_period);
            $this->db->like('item_fg_id', $filter_item);
            $this->db->group_by('periode');
            $total = $this->db->get()->row();
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result['footer'] = [array(
                "history" => "Grand Total",
                "total_qty" => @$total->total_qty,
                "umh" => @$total->total_umh,
                "direct_material" => @$total->direct_material,
                "direct_requestion" => @$total->direct_requestion,
                "direct_material_total" => @$total->direct_material_total,
                "direct_material_pcs" => @$total->direct_material_pcs,
                "direct_labor" => @$total->direct_labor,
                "direct_overtime" => @$total->direct_overtime,
                "direct_labor_total" => @$total->direct_labor_total,
                "direct_labor_pcs" => @$total->direct_labor_pcs,
                "direct_foh" => @$total->direct_foh,
                "direct_foh_pcs" => @$total->direct_foh_pcs,
                "direct_total" => @$total->direct_total,
            )];
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableWorkorders(){
        if ($this->input->get()) {
            $periode = base64_decode($this->input->get('periode'));
            $item_id = base64_decode($this->input->get('item_id'));

            //Select Query
            $this->db->select('a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name');
            $this->db->from('costing_product_workorders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.periode', $periode);
            $this->db->like('a.item_fg_id', $item_id);
            $this->db->order_by('a.workorder', 'asc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableDetails(){
        if ($this->input->post()) {
            $periode = $this->input->post('periode');
            $item_fg_id = $this->input->post('item_fg_id');

            //Select Query
            $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom');
            $this->db->from('costing_product_details a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.periode', $periode);
            $this->db->like('a.item_fg_id', $item_fg_id);
            $this->db->order_by('a.amount', 'desc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $sql = $this->db->insert('costing_product_details', $post);
            if($sql){
                die(json_encode(array("title" => "Good Job", "message" => "Data Save Already Successfully", "theme" => "success")));
            }else{
                die(json_encode(array("title" => "Failed", "message" => "Data Failed to Save Data", "theme" => "error")));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createProducts()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $costing_products = $this->crud->read('costing_products', [], ["periode" => $post['periode'], "item_fg_id" => $post['item_fg_id']]);

            if (@$costing_products->id != "") {
                $send = $this->crud->update('costing_products', ["periode" => $post['periode'], "item_fg_id" => $post['item_fg_id']], $post);
            } else {
                $send = $this->crud->create('costing_products', $post);
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
        $costing_product = $this->crud->read('costing_products', [], ["id" => $data['id']]);
        $costing_product_workorders = $this->crud->reads('costing_product_workorders', [], [
            "item_fg_id" => $costing_product->item_fg_id,
            "periode" => $costing_product->periode
        ]);

        foreach ($costing_product_workorders as $costing_product_workorder) {
            $this->crud->delete('costing_product_workorders', ["id" => $costing_product_workorder->id]);
            $this->crud->delete('costing_product_details', [
                "periode" => $costing_product_workorder->periode,
                "workorder" => $costing_product_workorder->workorder,
            ]);
        }

        $send = $this->crud->delete('costing_products', $data);
        echo $send;
    }

    public function uploadclearFailed()
    {
        @unlink('failed/costing_products.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/costing_products.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/costing_products.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=costing_products_$format.xls");
        }

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_item = base64_decode($this->input->get('filter_item'));

        $filter_period = $filter_year . $filter_month;

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_no, b.number_customer, b.name as item_name');
        $this->db->from('costing_products a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.periode', $filter_period);
        $this->db->like('a.item_fg_id', $filter_item);
        $this->db->order_by('a.total_qty', 'desc');
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
                            <small>GENERATE COSTING PRODUCTS PERIOD ' . $filter_period . '</small>
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
                <th rowspan="2">Product No</th>
                <th rowspan="2">Product Name</th>
                <th rowspan="2">Quantity</th>
                <th rowspan="2">UMH</th>
                <th colspan="4">Direct Material</th>
                <th rowspan="2">Cost Product</th>
            </tr>
            <tr>
                <th>Supply</th>
                <th>Material<br>Requestion</th>
                <th>Total</th>
                <th>Pcs</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['number_customer'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['total_qty'] . '</td>
                            <td>' . $data['umh'] . '</td>
                            <td>' . $data['direct_material'] . '</td>
                            <td>' . $data['direct_requestion'] . '</td>
                            <td>' . $data['direct_material_total'] . '</td>
                            <td>' . $data['direct_material_pcs'] . '</td>
                            <td>' . $data['direct_total'] . '</td>
                        </tr>';

            $html .= '  <tr>
                            <th colspan="16" style="background:#A3FF73;">Detail of Product No '.$data['item_no'].' - '.$data['item_name'].'</th>
                        </tr>';
                
                    $html .= '  <tr>
                                    <th colspan="16" style="background:#FFDB73;">Detail of Workorder No '.$workorder['workorder'].'</th>
                                </tr>
                                <tr>
                                    <th width="20">No</th>
                                    <th>Part No EBWS</th>
                                    <th>Part No Supplier</th>
                                    <th>Uom</th>
                                    <th>Supply Type</th>
                                    <th>Supply Date</th>
                                    <th>Supply No</th>
                                    <th>Issued</th>
                                    <th>Price</th>
                                    <th>Amount</th>
                                </tr>';

                $this->db->select('a.*, b.id as item_number, b.number as item_name, b.uom');
                $this->db->from('costing_product_details a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.periode', $filter_period);
                $this->db->like('a.item_fg_id', $data['item_fg_id']);
                $this->db->order_by('a.amount', 'desc');
                $details = $this->db->get()->result_array();

                $noc = 1;
                foreach ($details as $detail) {
                    $html .= '  <tr>
                                    <td>' . $no.'.'.$nod.'.'.$noc.'</td>
                                    <td>' . $detail['item_number'] . '</td>
                                    <td>' . $detail['item_name'] . '</td>
                                    <td>' . $detail['uom'] . '</td>
                                    <td>' . $detail['supply_type'] . '</td>
                                    <td>' . $detail['supply_date'] . '</td>
                                    <td>' . $detail['supply_no'] . '</td>
                                    <td>' . $detail['issued'] . '</td>
                                    <td>' . $detail['price'] . '</td>
                                    <td>' . $detail['amount'] . '</td>
                                </tr>';
                    $noc++;
                }
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
