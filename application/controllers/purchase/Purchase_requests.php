<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_requests extends CI_Controller
{
    public $menu_id;

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
        $this->form_validation->set_rules('item_rm_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_requests');
        } else {
            redirect('error_access');
        }
    }

    public function readprodfam($supplier_id){
        $supplier_id = base64_decode($supplier_id);
        $query = $this->db->query("SELECT DISTINCT a.id, a.number, a.name 
            FROM item_familys a 
            JOIN item_rm b ON a.id = b.item_family_id
            JOIN supplier_items c ON b.id = c.item_rm_id
            WHERE a.item_category_id IN ('C01') and c.supplier_id = '$supplier_id'");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readParts($supplier_id, $item_famiy_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $supplier_id = base64_decode($supplier_id);
        $item_famiy_id = base64_decode($item_famiy_id);
        
        $query = $this->db->query("SELECT b.id, b.number, b.name, a.mpq, a.moq, a.share_order, a.leadtime
            FROM supplier_items a 
            JOIN item_rm b ON a.item_rm_id = b.id
            WHERE a.supplier_id = '$supplier_id' and b.item_family_id = '$item_famiy_id' and a.approved_to = '' and (b.number like '%$post%' or b.id like '%$post%')
            ORDER BY b.number ASC
        ");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readPr($supplier_id, $item_family_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $supplier_id = base64_decode($supplier_id);
        $item_family_id = base64_decode($item_family_id);
        
        $query = $this->db->query("SELECT a.request_no, b.name as supplier_name
            FROM purchase_requests a 
            JOIN suppliers b ON a.supplier_id = b.id
            WHERE a.supplier_id = '$supplier_id' and a.item_family_id = '$item_family_id' and a.request_no like '%$post%'
            GROUP BY a.request_no
            ORDER BY a.request_no ASC
        ");
        $records = $query->result_array();

        echo json_encode($records);
    }

    function forecast($item_rm_id, $month, $year, $revision){
        $q_forecast = $this->db->query("SELECT a.item_rm_id, c.name as item_name, SUM(a.composition) as qty, 
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

    public function readMonths()
    {
        $month = $this->input->get('month');
        $year = $this->input->get('year');

        $start = strtotime($year . "-" . $month . "-01");
        $begin =  strtotime(date('Y-m-d', strtotime('+4 month', $start)));
        $end =  strtotime(date('Y-m-d', strtotime('-3 month', $start)));

        $monthName = array();
        while ($end < $begin) {
            $monthName[] = date('M Y', $end);
            $end = strtotime("+1 month", $end);
        }

        die(json_encode($monthName));
    }

    public function purchaseRequestNo($id_menu, $add = ""){
        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if(@$row_menu->number == ""){
            //
        }else{
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number.$yearnow.$yearend."1";

            $sqlGetID	= $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no like '%$whereDate%'");
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

            if($add == ""){
                return $autoNo;
            }else{
                echo $autoNo;
            }
        }
    }

    public function getDataMrp()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $id_menu = base64_decode($this->input->get('id_menu'));

            if($filter_item_family != ""){
                if($filter_item_rm != ""){
                    $array_pfm = array("item_family_id" => $filter_item_family, "item_rm_id" => $filter_item_rm);
                }else{
                    $array_pfm = array("item_family_id" => $filter_item_family);
                }
            }else{
                $array_pfm = array();
            }

            $this->crud->delete("purchase_requests", array_merge(["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision], $array_pfm));

            if(date('Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01"))) == $filter_year){
                $p_year_min = $filter_month;
                $p_month_min = $filter_year;
            }else{
                $p_year_min =  date('Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
                $p_month_min =  date('m', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            }

            $p1_min = "m".date('n', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p2_min = "m".date('n', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p3_min = "m".date('n', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p1_plus = "m".date('n', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p2_plus = "m".date('n', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p3_plus = "m".date('n', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $forecast_rev = $this->db->query("SELECT DISTINCT revision FROM `forecasts` WHERE p_month = '$filter_month' and p_year = '$filter_year' ORDER BY revision DESC LIMIT 1");
            $forecast_row = $forecast_rev->row();

            $forecast_rev_min = $this->db->query("SELECT DISTINCT revision FROM `forecasts` WHERE p_month = '$p_month_min' and p_year = '$p_year_min' ORDER BY revision DESC LIMIT 1");
            $forecast_row_min = $forecast_rev_min->row();

            //Select Query
            $this->db->select('a.item_rm_id, c.id as supplier_id, f.id as item_family_id, c.mpq, c.moq, c.leadtime, a.total_need, a.purchase_order, a.item_rm_id, b.item_family_id, f.number as item_family_number, c.share_order, c.supplier_id, e.number as supplier_no, d.class, d.composition, d.safety_stock, d.safety');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('supplier_items c', 'b.id = c.item_rm_id and c.share_order > 0');
            $this->db->join('generate_mrp_abcclass d', 'a.item_rm_id = d.item_rm_id and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
            $this->db->join('suppliers e', 'c.supplier_id = e.id');
            $this->db->join('item_familys f', 'b.item_family_id = f.id');
            $this->db->where('(a.purchase_order + d.safety_stock) >', 0);
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_item_family != ""){
                $this->db->where('b.item_family_id', $filter_item_family);
            }
            if($filter_item_rm != ""){
                $this->db->where('b.id', $filter_item_rm);
            }
            $this->db->group_by('c.supplier_id');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('e.name');
            $this->db->order_by('b.item_family_id');
            $mpsDetails = $this->db->get()->result_array();

            $arr = array();
            $end_supplier = "";
            $end_product_family = "";
            $end_request_no = "";
            $no = 0;
            foreach ($mpsDetails as $generate) {
                $supplier_no = $generate['supplier_no'];
                $item_rm_id = $generate['item_rm_id'];

                if($supplier_no == $end_supplier && $generate['item_family_id'] == $end_product_family){
                    $request_no = $end_request_no;
                    $no++;
                }else{
                    // $request_no = $this->purchaseRequestNo("PR".@trim($supplier_no.date("Ym")). "-" .trim($generate['item_family_number']));
                    $request_no = $this->purchaseRequestNo($id_menu);
                    $no = 1;
                }

                $forecast_min = $this->forecast($item_rm_id, $filter_month, $filter_year, @$forecast_row_min->revision);
                $forecast_plus = $this->forecast($item_rm_id, $filter_month, $filter_year, @$forecast_row->revision);
                
                if(round($generate['total_need'] + $generate['safety_stock']) > 0 && $generate['moq'] > 0){
                    $final = ($generate['total_need'] + $generate['safety_stock']);
                    if($final > $generate['moq']){
                        $purchase_order = (ceil($final / $generate['mpq']) * $generate['mpq']);
                    }else{
                        $purchase_order = (ceil($final / $generate['moq']) * $generate['moq']);
                    }
                }else{
                    $purchase_order = 0;
                }

                $split = ($purchase_order * ($generate['share_order'] / 100));
                if($split > 0){
                    $purchase_request = (ceil($split / $generate['moq']) * $generate['moq']);
                }else{
                    $purchase_request = 0;
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "item_rm_id" => $generate['item_rm_id'],
                    "supplier_id" => $generate['supplier_id'],
                    "item_family_id" => $generate['item_family_id'],
                    "request_no" => $request_no,
                    "request_date" => date("Y-m-d"),
                    "request_name" => $this->session->name,
                    "expected_date" => date("Y-m-d"),
                    "need" => ($generate['total_need'] + $generate['safety_stock']),
                    "qty" => $purchase_request,
                    "forecast_m1" => @$forecast_min->$p1_min,
                    "forecast_m2" => @$forecast_min->$p2_min,
                    "forecast_m3" => @$forecast_min->$p3_min,
                    "forecast_plus_1" => @$forecast_plus->$p1_plus,
                    "forecast_plus_2" => @$forecast_plus->$p2_plus,
                    "forecast_plus_3" => @$forecast_plus->$p3_plus,
                    "forecast_avg" => round((@$forecast_min->$p1_min + @$forecast_min->$p2_min + @$forecast_min->$p3_min + @$forecast_plus->$p1_plus + @$forecast_plus->$p2_plus + @$forecast_plus->$p3_plus) / 6),
                    "status_data" => "GENERATE",
                );

                $end_supplier = $supplier_no;
                $end_product_family = $generate['item_family_id'];
                $end_request_no = $request_no;
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name, c.name as item_family_name, SUM(a.qty) as total_pr');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_familys c', "a.item_family_id = c.id and c.item_category_id IN ('C01')");
            // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_item_family != ""){
                $this->db->like('a.item_family_id', $filter_item_family);
            }
            if($filter_item_rm != ""){
                $this->db->like('a.item_rm_id', $filter_item_rm);
            }
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_no', 'ASC');
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

    public function datatableDetails($request_no, $revision)
    {
        $request_no = base64_decode($request_no);
        $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));
        $filter_month = trim(base64_decode($this->input->get('filter_month')));
        $filter_year = trim(base64_decode($this->input->get('filter_year')));

        $ending = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        $po_date_1 = date('Y-m', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $po_date_2 = date('Y-m', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $po_date_3 = date('Y-m', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('a.*, d.number as item_rm_number, d.name as item_rm_name, b.name as supplier_name, c.share_order, c.moq, c.mpq, c.leadtime');
        $this->db->from('purchase_requests a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('supplier_items c', 'a.supplier_id = c.supplier_id and a.item_rm_id = c.item_rm_id');
        $this->db->join('item_rm d', 'a.item_rm_id = d.id');
        // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.revision', $revision);
        $this->db->where('a.request_no', $request_no);
        $this->db->like('a.item_rm_id', $filter_item_rm);
        $this->db->order_by('a.item_rm_id', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $productTotals = [];
        foreach ($records as $record) {
            $product = $record['item_rm_id'];
            $shareOrder = $record['share_order'];
            $productTotals[$product] = isset($productTotals[$product]) ? $productTotals[$product] + $shareOrder : $shareOrder;
        }

        $finals = array();
        $total_m1 = 0;
        $total_m2 = 0;
        $total_m3 = 0;
        $total_avg = 0;
        $total_prodplan_1 = 0;
        $total_prodplan_2 = 0;
        $total_prodplan_3 = 0;
        $total_forecast_m1 = 0;
        $total_forecast_m2 = 0;
        $total_forecast_m3 = 0;
        $total_forecast_plus_1 = 0;
        $total_forecast_plus_2 = 0;
        $total_forecast_plus_3 = 0;
        $total_avg_forecast = 0;
        $total_purchase_request = 0;
        $total_purchase_order = 0;
        $total_need = 0;
        foreach ($records as $data) {
            $item_id = $data['item_rm_id'];
            $leadtime = date('Y-m-01', strtotime('+'.$data['leadtime'].' days', strtotime($ending)));

            //Purchase History
            $qpo1 = $this->db->query("SELECT qty FROM purchase_orders WHERE `po_date` LIKE '%$po_date_1%' and item_rm_id = '$item_id' and revision = '$revision'");
            $rpo1 = $qpo1->row();

            $qpo2 = $this->db->query("SELECT qty FROM purchase_orders WHERE `po_date` LIKE '%$po_date_2%' and item_rm_id = '$item_id' and revision = '$revision'");
            $rpo2 = $qpo2->row();

            $qpo3 = $this->db->query("SELECT qty FROM purchase_orders WHERE `po_date` LIKE '%$po_date_3%' and item_rm_id = '$item_id' and revision = '$revision'");
            $rpo3 = $qpo3->row();
            
            $m1 = date('Y-m-01', strtotime($leadtime));
            $m2 = date('Y-m-01', strtotime('+1 month', strtotime($leadtime)));
            $m3 = date('Y-m-01', strtotime('+2 month', strtotime($leadtime)));
            $m11 = "m".date('n', strtotime($leadtime));
            $m22 = "m".date('n', strtotime('+1 month', strtotime($leadtime)));
            $m33 = "m".date('n', strtotime('+2 month', strtotime($leadtime)));

            $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and item_rm_id = '$item_id' and revision = '$revision'");
            $rpms1 = $qmps1->row();

            $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and item_rm_id = '$item_id' and revision = '$revision'");
            $rpms2 = $qmps2->row();

            $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and item_rm_id = '$item_id' and revision = '$revision'");
            $rpms3 = $qmps3->row();

            $forecast = $this->forecast($item_id, $filter_month, $filter_year, @$revision);

            if(@$rpms1->qty > 0){
                $prod1 = @$rpms1->qty;
            }else{
                $prod1 = @$forecast->$m11;
            }

            if(@$rpms2->qty > 0){
                $prod2 = @$rpms2->qty;
            }else{
                $prod2 = @$forecast->$m22;
            }

            if(@$rpms3->qty > 0){
                $prod3 = @$rpms3->qty;
            }else{
                $prod3 = @$forecast->$m33;
            }

            $class = ($productTotals[$data['item_rm_id']] >= 100) ? 'red' : '';
            $finals[] = array_merge($data, [
                "color" => $class,
                "m1" => empty(@$rpo1->qty) ? 0 : @$rpo1->qty,
                "m2" => empty(@$rpo2->qty) ? 0 : @$rpo2->qty,
                "m3" => empty(@$rpo3->qty) ? 0 : @$rpo3->qty,
                "avg" => empty((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty)) ? 0 : ((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty) / 3),
                "prodplan_1" => $prod1,
                "prodplan_2" => $prod2,
                "prodplan_3" => $prod3,
                "avg_forecast" => round(($data['forecast_m1'] + $data['forecast_m2'] + $data['forecast_m3'] + $data['forecast_plus_1'] + $data['forecast_plus_2'] + $data['forecast_plus_3'] + $prod1 + $prod2 + $prod3) / 9)
            ]);

            $total_m1 += empty(@$rpo1->qty) ? 0 : @$rpo1->qty;
            $total_m2 += empty(@$rpo2->qty) ? 0 : @$rpo2->qty;
            $total_m3 += empty(@$rpo3->qty) ? 0 : @$rpo3->qty;
            $total_avg += empty((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty)) ? 0 : ((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty) / 3);
            $total_prodplan_1 += $prod1;
            $total_prodplan_2 += $prod2;
            $total_prodplan_3 += $prod3;
            $total_forecast_m1 += $data['forecast_m1'];
            $total_forecast_m2 += $data['forecast_m2'];
            $total_forecast_m3 += $data['forecast_m3'];
            $total_forecast_plus_1 += $data['forecast_plus_1'];
            $total_forecast_plus_2 += $data['forecast_plus_2'];
            $total_forecast_plus_3 += $data['forecast_plus_3'];
            $total_avg_forecast += (($data['forecast_m1'] + $data['forecast_m2'] + $data['forecast_m3'] + $data['forecast_plus_1'] + $data['forecast_plus_2'] + $data['forecast_plus_3'] + $prod1 + $prod2 + $prod3) / 9);
            $total_purchase_request += $data['qty'];
            $total_need += $data['need'];
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
        'rows' => $finals, 
        'footer' => array([
                "item_rm_number" => "Grand Total",
                "qty" => $total_purchase_request,
                "need" => $total_need,
                "m1" => $total_m1,
                "m2" => $total_m2,
                "m3" => $total_m3,
                "avg" => $total_avg,
                "prodplan_1" => $total_prodplan_1,
                "prodplan_2" => $total_prodplan_2,
                "prodplan_3" => $total_prodplan_3,
                "forecast_m1" => $total_forecast_m1,
                "forecast_m2" => $total_forecast_m2,
                "forecast_m3" => $total_forecast_m3,
                "forecast_plus_1" => $total_forecast_plus_1,
                "forecast_plus_2" => $total_forecast_plus_2,
                "forecast_plus_3" => $total_forecast_plus_3,
                "avg_forecast" => $total_avg_forecast
            ])
        ]);
        echo json_encode($result);
    }

    public function datatableConfirm(){
        //Select Query
        $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, d.name as item_family_name, b.name as supplier_name, SUM(a.qty) as total_qty');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->where('a.status_edit', 1);
        $this->db->where('a.request_no !=', null);
        $this->db->group_by('a.request_no');
        $this->db->order_by('a.updated_date');
        $this->db->order_by('a.created_date');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        
        $arrFinal = array();
        foreach ($records as $record) {
            $json = json_decode($record['log'], true);
            $log = json_decode($json['log'], true);
            $arrFinal[] = array_merge($record, ["last_qty" => $log['qty']]);
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $arrFinal
        ]);
        echo json_encode($result);
    }

    public function createAdds()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $item_rm_id = $post['item_rm_id'];
            $filter_month = $post['p_month'];
            $filter_year = $post['p_year'];

            if(date('Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01"))) == $filter_year){
                $p_year_min = $filter_year;
                $p_month_min = $filter_month;
            }else{
                $p_year_min =  date('Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
                $p_month_min =  date('m', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            }

            $records = $this->crud->reads('purchase_requests', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_rm_id" => $post['item_rm_id'],
                "supplier_id" => $post['supplier_id']
            ]);

            if(count($records) > 0){
                show_error("Duplicate Item ID");
            }else{
                $forecast_rev = $this->db->query("SELECT DISTINCT revision FROM `forecasts` WHERE p_month = '$filter_month' and p_year = '$filter_year' ORDER BY revision DESC LIMIT 1");
                $forecast_row = $forecast_rev->row();

                $forecast_rev_min = $this->db->query("SELECT DISTINCT revision FROM `forecasts` WHERE p_month = '$p_month_min' and p_year = '$p_year_min' ORDER BY revision DESC LIMIT 1");
                $forecast_row_min = $forecast_rev_min->row();

                $forecast_min = $this->forecast($item_rm_id, $filter_month, $filter_year, @$forecast_row_min->revision);
                $forecast_plus = $this->forecast($item_rm_id, $filter_month, $filter_year, @$forecast_row->revision);

                $dataForecast = array(
                    "forecast_m1" => empty(@$forecast_min->$p1_min) ? 0 : @$forecast_min->$p1_min,
                    "forecast_m2" => empty(@$forecast_min->$p2_min) ? 0 : @$forecast_min->$p2_min,
                    "forecast_m3" => empty(@$forecast_min->$p3_min) ? 0 : @$forecast_min->$p3_min,
                    "forecast_plus_1" => empty(@$forecast_plus->$p1_plus) ? 0 : @$forecast_plus->$p1_plus,
                    "forecast_plus_2" => empty(@$forecast_plus->$p2_plus) ? 0 : @$forecast_plus->$p2_plus,
                    "forecast_plus_3" => empty(@$forecast_plus->$p3_plus) ? 0 : @$forecast_plus->$p3_plus,
                    "forecast_avg" => round((@$forecast_min->$p1_min + @$forecast_min->$p2_min + @$forecast_min->$p3_min + @$forecast_plus->$p1_plus + @$forecast_plus->$p2_plus + @$forecast_plus->$p3_plus) / 6),
                );

                $send = $this->crud->create('purchase_requests', array_merge($post, $dataForecast), "PR", "JUTA");
                echo $send;
            }
        }
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/documents/purchase_requests/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Pastikan ada file yang diunggah dari permintaan
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];

                // Validasi ekstensi file yang diunggah
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, or .png are allowed.']);
                    exit; // Menghentikan proses lebih lanjut jika ekstensi tidak valid
                }

                // Validasi ukuran file yang diunggah (maksimal 5MB)
                $maxFileSize = 2 * 1024 * 1024; // 5MB dalam bytes
                if ($file['size'] > $maxFileSize) {
                    echo json_encode(['success' => false, 'message' => 'Size File Maximal 2 Mb.']);
                    exit; // Menghentikan proses lebih lanjut jika ukuran terlalu besar
                }

                // Pastikan tidak ada error dalam proses upload
                if ($file['error'] === UPLOAD_ERR_OK) {
                    // Buat nama unik untuk file yang diunggah
                    $fileName = uniqid() . '_' . $file['name'];
                    $uploadPath = $uploadDir . $fileName;

                    // Pindahkan file dari temporary directory ke lokasi yang diinginkan
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        // File berhasil diunggah
                        echo json_encode(['success' => true, 'message' => 'File Upload Success.', 'filename' => $fileName]);
                    } else {
                        // Gagal menyimpan file
                        echo json_encode(['success' => false, 'message' => 'File Upload Failed.']);
                    }
                } else {
                    // Ada error dalam proses upload
                    echo json_encode(['success' => false, 'message' => 'Error while Upload.']);
                }
            } else {
                // File tidak ditemukan dalam permintaan
                echo json_encode(['success' => false, 'message' => 'File Not Found.']);
            }
        } else {
            // Metode request yang diperlukan adalah POST
            echo json_encode(['success' => false, 'message' => 'Metode request yang diperlukan adalah POST.']);
        }
    }

    public function updateConfirm(){
        if ($this->input->post()) {
            $request_no = $this->input->post('request_no');
            $item_rm_id = $this->input->post('item_rm_id');
            
            $this->db->update('purchase_orders', ["status_edit" => 0], ["request_no" => $request_no, "item_rm_id" => $item_rm_id]);
            
            echo json_encode(array("message" => "success"));
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $send = $this->crud->create('purchase_requests', $post);
            echo $send;
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));

            $post = $this->input->post();
            $postFinal = array(
                "qty" => $post['qty'],
                "request_no" => $post['request_no'],
                "remarks" => $post['remarks'],
                "status_data" => $post['status_data'],
            );

            $send = $this->crud->update('purchase_requests', ["id" => $id], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_requests', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=purchase_requests_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_3 =  date('M Y', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_2 =  date('M Y', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_1 =  date('M Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $this->db->select('a.*, b.name as supplier_name, c.name as item_family_name, d.number as item_rm_number, d.name as item_rm_name, e.share_order, e.mpq, e.moq, e.leadtime');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_familys c', "a.item_family_id = c.id and c.item_category_id IN ('C01')");
            $this->db->join('item_rm d', "a.item_rm_id = d.id");
            $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id and e.share_order > 0');
            // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('a.item_family_id', $filter_item_family);
            $this->db->like('a.item_rm_id', $filter_item_rm);
            $this->db->order_by('a.request_no', 'ASC');
            $this->db->order_by('d.number', 'ASC');
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 11px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} 
            .box-green{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #D6FFCF;
            }

            .box-red{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #FFCFCF;
            }
            </style>
            <body>
                <center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <small>PURCHASE REQUEST</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:m:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                    <br><br><br>
                </center>

                <table id="customers" border="1">
                    <tr>
                        <th rowspan="2" style="text-align:center;" width="20">No</th>
                        <th rowspan="2" style="text-align:center;">PERIOD</th>
                        <th rowspan="2" style="text-align:center;">REVISION</th>
                        <th rowspan="2" style="text-align:center;">VENDOR</th>
                        <th rowspan="2" style="text-align:center;">ITEM NO</th>
                        <th rowspan="2" style="text-align:center;">ITEM NAME</th>
                        <th rowspan="2" style="text-align:center;">PRODFAM</th>
                        <th rowspan="2" style="text-align:center;">SHARE<br>ORDER</th>
                        <th rowspan="2" style="text-align:center;">MPQ</th>
                        <th rowspan="2" style="text-align:center;">MOQ</th>
                        <th rowspan="2" style="text-align:center;">LEADTIME</th>
                        <th rowspan="2" style="text-align:center;">TOTAL NEED</th>
                        <th colspan="3" style="text-align:center;">PURCHASE HISTORY</th>
                        <th rowspan="2" style="text-align:center;">AVG</th>
                        <th colspan="6" style="text-align:center;">FORECAST SUPPLIER</th>
                        <th rowspan="2" style="text-align:center;">AVG</th>
                        <th rowspan="2" style="text-align:center;">REASON</th>
                    </tr>
                    <tr>
                        <th style="text-align:center;">'.$month_min_3.'</th>
                        <th style="text-align:center;">'.$month_min_2.'</th>
                        <th style="text-align:center;">'.$month_min_1.'</th>
                        <th style="text-align:center;">'.$month_min_3.'</th>
                        <th style="text-align:center;">'.$month_min_2.'</th>
                        <th style="text-align:center;">'.$month_min_1.'</th>
                        <th style="text-align:center;">'.$month_1.'</th>
                        <th style="text-align:center;">'.$month_2.'</th>
                        <th style="text-align:center;">'.$month_3.'</th>
                    </tr>';

                    $no = 1;

                    $productTotals = [];
                    foreach ($records as $record) {
                        $product = $record['item_rm_id'];
                        $shareOrder = $record['share_order'];
                        $productTotals[$product] = isset($productTotals[$product]) ? $productTotals[$product] + $shareOrder : $shareOrder;
                    }
                    foreach ($records as $record) {
                        $class = ($productTotals[$record['item_rm_id']] >= 100) ? 'red' : '';
                        if($class == ""){
                            $style = "style='background:#FFD2D2;'";
                        }else{
                            $style = "";
                        }

                        $html .= "  <tr ".$style.">
                                        <td>".$no."</td>
                                        <td>".$record['p_year'] . $record['p_month']."</td>
                                        <td>".$record['revision']."</td>
                                        <td>".$record['supplier_name']."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['item_rm_number'])."</td>
                                        <td>".$record['item_rm_name']."</td>
                                        <td>".$record['item_family_name']."</td>
                                        <td>".$record['share_order']."</td>
                                        <td>".$record['mpq']."</td>
                                        <td>".$record['moq']."</td>
                                        <td>".$record['leadtime']."</td>
                                        <td style='text-align:right;'>".round($record['qty'])."</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>".round($record['forecast_m3'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_m2'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_m1'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_1'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_2'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_3'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_avg'])."</td>
                                        <td style='text-align:right;'>".$record['remarks']."</td>
                                    </tr>";
                        $no++;
                    }

            $html .= '</table></body></html>';
            echo $html;
        }
    }

    public function print_request($request_no)
    {
        $request_no = base64_decode($request_no);
        $purchase_request_total = $this->crud->reads('purchase_requests', [], ["request_no" => $request_no]);
        $purchase_requests = $this->crud->read('purchase_requests', [], ["request_no" => $request_no]);
        $supplier = $this->crud->read('suppliers', [], ["id" => @$purchase_requests->supplier_id]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        $approval = $this->crud->read("approvals", [], ["table_name" => "purchase_request_others"]);
        $user_1 = $this->crud->read("users", [], ["username" => @$approval->user_approval_1]);
        $user_2 = $this->crud->read("users", [], ["username" => @$approval->user_approval_2]);

        if($purchase_requests->approved_by == @$approval->user_approval_1){
            $imge_1 = '<img src="'.base_url('assets/image/approved.png').'" width="100"/>';
            $imge_2 = "";
        }elseif($purchase_requests->approved_by == @$approval->user_approval_2){
            $imge_1 = '<img src="'.base_url('assets/image/approved.png').'" width="100"/>';
            $imge_2 = '<img src="'.base_url('assets/image/approved.png').'" width="100"/>';
        }else{
            $imge_1 = "";
            $imge_2 = "";
        }
        
        //Config Page
        $rows = 15;
        $page = ceil(count($purchase_request_total) / $rows);
        //Generate QRcode
        // $this->createQrcode($request_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $purchase_requests->request_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 15 rows</p>
                            <p>Paper Size A4, Layout Landscape</p>
                            <p>Margin Default, Scale 98</p>
                        </center>
                    </div>
                    <div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom, c.leadtime, c.price, currency');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('supplier_items c', 'a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $request_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(15, ($i * 15));
            $records = $this->db->get()->result_array();

            $html .= '  <div style="border: 1px solid black; width:100%; float:left;">
                            <div style="padding:10px;">
                                <table style="width:100%;" border="0">
                                    <tr>
                                        <td width="200" style="text-align:left; font-size:10px;">
                                            <b>Elcon Banshu Wiring System</b><br>
                                            <b>Office : </b> 1622, 16th Floor, World Trade Tower,<br>
                                            Sec-16, Noida-201301, UP, INDIA<br>
                                            GST No : 09AAFCE7558R1ZN, <br>
                                            CIN No: U35999UP2019PTC124726<br><br>
                                            <b>Factory</b> :  UNIT NO 3, PLOT NO- B-9, ECOTECH EXTENSION<br>
                                            GRATER NOIDA, KASNA, 201306<br>
                                            GST NO : 09AAFCE7558R1ZN
                                        </td>
                                        <th width="100" style="text-align:right;"><img src="' . $config->favicon . '" width="150"/></th>
                                    </tr>
                                </table>
                                <center>
                                    <h3><u>PURCHASE REQUISITION</u></h3>
                                </center>
                                <div style="float:left; width:50%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="50" style="vertical-align: top;"><b>To</b></td>
                                            <td width="30" style="vertical-align: top;">:</td>
                                            <td><b>' . @$supplier->name . '</b><br>'.@$supplier->address.'<br>GST No : '.@$supplier->gst.'</td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Order No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$purchase_requests->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$purchase_requests->request_date . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Plant</td>
                                            <td width="10">:</td>
                                            <td><b>02 - Noida</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Revision</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$purchase_requests->revision . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="120">Part No EBWS</th>
                                        <th width="120">Part No Supplier</th>
                                        <th width="100">Delivery Date <br>(ETA)</th>
                                        <th width="50">Qty</th>
                                        <th width="50">Uom</th>
                                        <th width="50">Currency</th>
                                        <th width="50">Unit Price<br>(INR)</th>
                                        <th width="50">Amount<br>(INR)</th>
                                        <th width="100">Remarks</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_rm_number'] . '</td>
                                <td>' . date("Y-m-d", strtotime("+".$record['leadtime']." day", strtotime($record['request_date']))) . '</td>
                                <td style="text-align:right">' . number_format($record['qty'], 2) . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td>' . $record['price'] . '</td>
                                <td>' . ($record['price'] * $record['qty']) . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            
            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            if (($i + 1) == $page) {
                $html .= '  <div style="width:20%; float:left; margin-bottom:20px;">
                                <table id="customers" style="margin-top:20px;">
                                    <tr>
                                        <th width="200" style="text-align:center;">ACCEPTED</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">' . @$supplier->name . '</th>
                                    </tr>
                                </table>
                            </div>
                            <div style="width:30%; float:left;">
                                <br>
                            </div>
                            <div style="width:40%; float:right;">
                                <table id="customers" style="margin-top:20px;">
                                    <tr>
                                        <th colspan="2" style="text-align:center;">'.$config->name.'</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;">'.@$imge_1.'</th>
                                        <th style="height:80px;">'.@$imge_2.'</th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">'.@$user_1->name.'</th>
                                        <th style="height:20px; text-align:center;">'.@$user_2->name.'</th>
                                    </tr>
                                </table>
                            </div>';
            }

            $html .= '</div></div>';
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }
}
