<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Material_calculation extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //$this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[mpp_realization.item_rm_id]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/material_calculation');
        } else {
            redirect('error_access');
        }
    }

    public function cutoffDate(){
        $year = $this->input->post('year');
        $month = $this->input->post('month');

        echo date("Y-m-d", strtotime("-1 days", strtotime($year."-".$month."-01")));
    }

    public function readItemFamily(){
        $prodfam = $this->crud->query("SELECT * FROM item_familys WHERE `item_category_id` = 'C01'");
        echo json_encode($prodfam);
    }

    public function readProducts($pfm_id = ""){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $items = $this->crud->query("SELECT * FROM item_rm WHERE item_family_id like '%$pfm_id%' and item_category_id = 'C01' and (`number` like '%$post%' or id like '%$post%')");
        echo json_encode($items);
    }

    public function getDataMpp(){
        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $this->db->select("*");
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $filter_revision);
        $this->db->group_by('item_rm_id');
        $records = $this->db->get()->result_array();

        echo json_decode($records);
    }

    public function datatables()
    {
        //Database Server MRP
        // $this->db = $this->load->database('db', TRUE);
        
        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_id = base64_decode($this->input->get('filter_item_id'));
            $filter_status = $this->input->get('filter_status');
            $filter_date = base64_decode($this->input->get('filter_date'));

            $this->db->select("a.*, c.number as item_rm_number, b.name as item_rm_name, b.name as item_family_name");
            $this->db->from('generate_item_calculation a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys b', 'c.item_family_id = b.id');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('c.item_family_id', $filter_item_family);
            $this->db->like('a.item_rm_id', $filter_item_id);
            $this->db->order_by('b.number');
            $this->db->group_by('a.item_rm_id');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            // $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $dataFinal = array();
            foreach ($records as $record) {
                $item_id = $record['item_rm_id'];
                $firstDate = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
                
                $balances = ($record['stock_hand'] + $record['eta'] - $record['total']);

                $day = 1;
                $balance = $record['stock_hand'];
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $working_date = date("Y-m-d", strtotime($firstDate));
                    
                    //ETA
                    $delivery_schedule = $this->db->query("SELECT item_rm_id, SUM(qty_receipt) as total FROM purchase_order_receipts WHERE item_rm_id = '$item_id' and receipt_date = '$working_date' GROUP BY item_rm_id");
                    $totalRow = $delivery_schedule->row();
                    $etaqty = empty(@$totalRow->total) ? 0 : @$totalRow->total;
                    
                    //Supply
                    $ithSupply = $this->db->query("SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as supply FROM issued_material_details a WHERE a.item_rm_id = '$item_id' and DATE_FORMAT(a.created_date, '%Y-%m-%d') = '$working_date' and a.request_no NOT LIKE '%PR-%' GROUP BY a.item_rm_id");
                    $ithSupplyRow = $ithSupply->row();
    
                    $date_qty = $record["date_".$day];
                    $balance += round($etaqty - $date_qty);
                    
                    $arr["eta_".$day] = (int) $etaqty;
                    $arr["date_".$day] = round($date_qty);
                    $arr["supply_".$day] = abs(round(@$ithSupplyRow->supply));
                    $arr["bal_".$day] = round($balance);
    
                    $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
                    $day++;
                }

                if($balances > 0){
                    $free_stock = $balances;
                }else{
                    $free_stock = 0;
                }

                $data = array_merge($arr, [
                    "item_rm_id" => $item_id,
                    "item_rm_number" => $record['item_rm_number'],
                    "item_rm_name" => $record['item_rm_name'],
                    "product_family_name" => $record['item_family_name'],
                    "stock_whs" => $record['stock_whs'],
                    "os_supply" => $record['os_supply'],
                    "os_wo" => $record['os_wo'],
                    "stock_hand" => $record['stock_hand'],
                    "eta" => $record['eta'],
                    "total" => round($record['total']),
                    "total_bal" => round($balances),
                    "free_stock" => round($free_stock),
                    "next_month_need1" => round($record['need_2'] / 4),
                    "next_month_need2" => round($record['need_2'] / 4),
                    "next_month_need3" => round($record['need_2'] / 4),
                    "next_month_need4" => round($record['need_2'] / 4),
                    "next_month_bal1" => ($balance - round($record['need_2'] / 4)),
                    "next_month_bal2" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "next_month_bal3" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "next_month_bal4" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "balance_wip" => $record['balance_wip'],
                    "os_po" => @$record['os_po'],
                ]);

                if($filter_status == "OK"){
                    if(round($free_stock) > 0){
                        $dataFinal[] = $data;
                    }
                }elseif($filter_status == "SHORTAGE"){
                    if(round($free_stock) == 0){
                        $dataFinal[] = $data;
                    }
                }else{
                    $dataFinal[] = $data;
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $dataFinal]);
            echo json_encode($result);
        }
    }

    public function getData(){
        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $query = $this->db->query("SELECT z.p_month, z.p_year, z.revision, z.item_rm_id, 
            SUM(total_date_1) as date_1,
            SUM(total_date_2) as date_2,
            SUM(total_date_3) as date_3,
            SUM(total_date_4) as date_4,
            SUM(total_date_5) as date_5,
            SUM(total_date_6) as date_6,
            SUM(total_date_7) as date_7,
            SUM(total_date_8) as date_8,
            SUM(total_date_9) as date_9,
            SUM(total_date_10) as date_10,
            SUM(total_date_11) as date_11,
            SUM(total_date_12) as date_12,
            SUM(total_date_13) as date_13,
            SUM(total_date_14) as date_14,
            SUM(total_date_15) as date_15,
            SUM(total_date_16) as date_16,
            SUM(total_date_17) as date_17,
            SUM(total_date_18) as date_18,
            SUM(total_date_19) as date_19,
            SUM(total_date_20) as date_20,
            SUM(total_date_21) as date_21,
            SUM(total_date_22) as date_22,
            SUM(total_date_23) as date_23,
            SUM(total_date_24) as date_24,
            SUM(total_date_25) as date_25,
            SUM(total_date_26) as date_26,
            SUM(total_date_27) as date_27,
            SUM(total_date_28) as date_28,
            SUM(total_date_29) as date_29,
            SUM(total_date_30) as date_30,
            SUM(total_date_31) as date_31
        FROM (
            SELECT a.p_month, a.p_year, a.revision, a.item_fg_id, b.item_rm_id,
            (a.date_1 * b.composition) as total_date_1,
            (a.date_2 * b.composition) as total_date_2,
            (a.date_3 * b.composition) as total_date_3,
            (a.date_4 * b.composition) as total_date_4,
            (a.date_5 * b.composition) as total_date_5,
            (a.date_6 * b.composition) as total_date_6,
            (a.date_7 * b.composition) as total_date_7,
            (a.date_8 * b.composition) as total_date_8,
            (a.date_9 * b.composition) as total_date_9,
            (a.date_10 * b.composition) as total_date_10,
            (a.date_11 * b.composition) as total_date_11,
            (a.date_12 * b.composition) as total_date_12,
            (a.date_13 * b.composition) as total_date_13,
            (a.date_14 * b.composition) as total_date_14,
            (a.date_15 * b.composition) as total_date_15,
            (a.date_16 * b.composition) as total_date_16,
            (a.date_17 * b.composition) as total_date_17,
            (a.date_18 * b.composition) as total_date_18,
            (a.date_19 * b.composition) as total_date_19,
            (a.date_20 * b.composition) as total_date_20,
            (a.date_21 * b.composition) as total_date_21,
            (a.date_22 * b.composition) as total_date_22,
            (a.date_23 * b.composition) as total_date_23,
            (a.date_24 * b.composition) as total_date_24,
            (a.date_25 * b.composition) as total_date_25,
            (a.date_26 * b.composition) as total_date_26,
            (a.date_27 * b.composition) as total_date_27,
            (a.date_28 * b.composition) as total_date_28,
            (a.date_29 * b.composition) as total_date_29,
            (a.date_30 * b.composition) as total_date_30,
            (a.date_31 * b.composition) as total_date_31
        FROM generate_mpp a
        JOIN bom b ON a.item_fg_id = b.item_fg_id
        WHERE a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision'
        GROUP BY a.item_fg_id, b.item_rm_id, a.customer_id
        ORDER BY a.item_fg_id, b.item_rm_id asc) z
        GROUP BY z.item_rm_id");
        $records = $query->result_array();

        $this->db->delete('generate_item_calculation', ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);
        echo json_encode($records);
    }

    public function create(){
        if ($this->input->post()) {
            $post = $this->input->post('data');
            
            $cutoff = $this->input->post('cutoff');
            $month1 = date("Y-m-01", strtotime("-1 month", strtotime($cutoff)));
            $end = date("Y-m-t", strtotime($post['p_year'] . "-" . $post['p_month'] . "-01"));
            $start = date("Y-m-01", strtotime($post['p_year'] . "-" . $post['p_month'] . "-01"));
            $next = date("Y-m-01", strtotime("+1 month", strtotime($start)));
            $item_rm_id = $post['item_rm_id'];
            
            $qmps = $this->db->query("SELECT a.id, a.number AS item_rm_number, a.name AS item_rm_name, b.total
                FROM item_rm a 
                JOIN (SELECT b.item_rm_id, SUM(b.composition * a.need) AS total FROM generate_mps_details a JOIN bom b ON a.item_fg_id = b.item_fg_id WHERE a.ltpp_month2 = '$next' AND a.p_month = '$post[p_month]' AND a.p_year = '$post[p_year]' AND a.revision = '$post[revision]' GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id 
                WHERE a.id = '$item_rm_id'
                GROUP BY a.id");
            $dmps = $qmps->row();

            $wip = $this->db->query("SELECT a.item_rm_id, (SUM(a.total) - COALESCE(b.issued, 0)) AS balance FROM (
                    SELECT b.item_rm_id, a.item_fg_id, SUM(a.qty), b.composition, (SUM(a.qty) * b.composition) AS total
                    FROM production_schedules a
                    JOIN bom b ON a.item_fg_id = b.item_fg_id
                    WHERE a.trans_date BETWEEN '$month1' AND '$cutoff' 
                    GROUP BY a.item_fg_id, b.item_rm_id) a
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$month1' AND '$cutoff' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) b ON a.item_rm_id = b.item_rm_id
                JOIN item_rm c ON a.item_rm_id = c.id
                JOIN item_familys d ON c.item_family_id = d.id
                WHERE a.item_rm_id = '$item_rm_id'
                GROUP BY a.item_rm_id
                ORDER BY c.item_family_id, c.id asc");
            $dwip = $wip->row();

            if(@$dwip->balance > 0){
                $qty_wip = 0;
                $os_supply = abs(@$dwip->balance);
                $os_wo = 0;
            }else{
                $qty_wip = @$dwip->balance;
                $os_supply = 0;
                $os_wo = 0;
            }

            $ospo = $this->db->query("SELECT a.id, (COALESCE(SUM(b.qty), 0) - COALESCE(c.qty_receipt, 0)) AS balance
                FROM item_rm a
                JOIN purchase_orders b ON a.id = b.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty_receipt) AS qty_receipt FROM purchase_order_receipts WHERE receipt_date <= '$cutoff' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                WHERE b.po_date <= '$cutoff' and a.id = '$item_rm_id' and b.status = 0
                GROUP BY a.id");
            $ospoRow = $ospo->row();

            $eta = $this->db->query("SELECT item_rm_id, SUM(qty_receipt) as total FROM purchase_order_receipts WHERE item_rm_id = '$item_rm_id' and receipt_date between '$start' and '$end' GROUP BY item_rm_id");
            $etaRow = $eta->row();

            $ith = $this->db->query("SELECT
                a.id,
                a.number,
                a.name,
                a.uom,
                b.name as product_family,
                ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS end_stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$cutoff' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date <= '$cutoff' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date <= '$cutoff' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date <= '$cutoff' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$cutoff' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$cutoff' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$cutoff' GROUP BY a.item_id) j ON a.id = j.item_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$cutoff' GROUP BY a.item_id) k ON a.id = k.item_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
                WHERE a.return_date <= '$cutoff'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.id = '$item_rm_id'
            GROUP BY a.id
            ORDER BY b.number, a.number");
            $ithRow = $ith->row();

            $stock_hand = round(@$ithRow->end_stock - $os_supply - $os_wo);

            $postFinal = array_merge($post, array(
                "created_date" => date("Y-m-d H:i:s"),
                "created_by" => $this->session->username,
                "os_supply" => $os_supply,
                "os_wo" => $os_wo,
                "balance_wip" => empty($qty_wip) ? 0 : $qty_wip,
                "eta" => empty(@$etaRow->total) ? 0 : @$etaRow->total,
                "stock_whs" => empty(@$ithRow->end_stock) ? 0 : @$ithRow->end_stock,
                "stock_hand" => $stock_hand,
                "os_po" => empty(@$ospoRow->balance) ? 0 : @$ospoRow->balance,
                "need_2" => empty(@$dmps->total) ? 0 : @$dmps->total,
                "total" => ($post['date_1'] + $post['date_2'] + $post['date_3'] + $post['date_4'] + $post['date_5'] + $post['date_6'] + 
                    $post['date_7'] + $post['date_8'] + $post['date_9'] + $post['date_10'] + $post['date_11'] + $post['date_12'] + 
                    $post['date_13'] + $post['date_14'] + $post['date_15'] + $post['date_16'] + $post['date_17'] + $post['date_18'] + 
                    $post['date_19'] + $post['date_20'] + $post['date_21'] + $post['date_22'] + $post['date_23'] + $post['date_24'] + 
                    $post['date_25'] + $post['date_26'] + $post['date_27'] + $post['date_28'] + $post['date_29'] + $post['date_30'] + 
                    $post['date_31']),
                "balance" => (($post['date_1'] + $post['date_2'] + $post['date_3'] + $post['date_4'] + $post['date_5'] + $post['date_6'] + 
                    $post['date_7'] + $post['date_8'] + $post['date_9'] + $post['date_10'] + $post['date_11'] + $post['date_12'] + 
                    $post['date_13'] + $post['date_14'] + $post['date_15'] + $post['date_16'] + $post['date_17'] + $post['date_18'] + 
                    $post['date_19'] + $post['date_20'] + $post['date_21'] + $post['date_22'] + $post['date_23'] + $post['date_24'] + 
                    $post['date_25'] + $post['date_26'] + $post['date_27'] + $post['date_28'] + $post['date_29'] + $post['date_30'] + 
                    $post['date_31']))
            ));

            if ($this->db->insert('generate_item_calculation', $postFinal)) {
                echo json_encode(array("title" => "Good Job", "message" => $post['item_rm_id'] . " | Data Saved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Error", "message" => $post['item_rm_id'] . " | Data Unsaved", "theme" => "error"));
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=material_calculation_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));
        $filter_status = $this->input->get('filter_status');
        $filter_date = base64_decode($this->input->get('filter_date'));

        $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $period_start = $filter_year."-".$filter_month."-01";
        $period_end = date("Y-m-t", strtotime($filter_year."-".$filter_month."-01"));
        
        $next_month = date("F", strtotime("+1 month", strtotime($filter_year."-".$filter_month."-01")));

        $this->db->select("a.*, c.number as item_rm_number, b.name as item_rm_name, b.name as item_family_name");
        $this->db->from('generate_item_calculation a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys b', 'c.item_family_id = b.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('c.item_family_id', $filter_item_family);
        $this->db->like('a.item_rm_id', $filter_item_id);
        $this->db->order_by('b.number');
        $this->db->group_by('a.item_rm_id');
        $records = $this->db->get()->result_array();

        //Setting Header
        $styles = "";
        $cut = '<tr>';
        $cut_no = 0;
        $cut_content = "";
        $date = '<tr>';
        $no = 1;
        $colspan = 0;

        while (strtotime($firstDate) <= strtotime($endDate)) {
            $working_date = date("Y-m-d", strtotime($firstDate));

            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', strtotime($firstDate)) !== '0') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $styles = 'background:#FFD974;';
                    $cut_no += 1;
                    $cut_content = "W/P ". $cut_no;
                } else{
                    $styles = "";
                    $cut_no += 1;
                    $cut_content = "W/P ". $cut_no;
                }
            } else {
                $styles = 'background:#FFD974;';
                $cut_no += 1;
                $cut_content = "W/P ". $cut_no;
            }

            //Setting Header
            $cut .= '<th width="50" colspan="4" style="text-align:center; ' . $styles . '">' . $cut_content . ' ('.$firstDate.')</th>';
            $date .= '<th width="50" style="text-align:center; ' . $styles . '">SCAN IN</th>
                      <th width="50" style="text-align:center; ' . $styles . '">NEED</th>
                      <th width="50" style="text-align:center; ' . $styles . '">SUPPLY</th>
                      <th width="50" style="text-align:center; ' . $styles . '">BAL</th>';

            $colspan++;
            $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        }

        $date .= '  <th width="50" style="text-align:center;">WEEK 1</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 2</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 3</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 4</th>
                    <th width="50" style="text-align:center;">BALANCE</th>';

        //Setting Header
        $cut .= '</tr>';
        $date .= '</tr>';

        $content2 = "";
        foreach ($records as $record) {
            $item_rm_id = $record['item_rm_id'];
            $content = '';
            $day = 1;
            $i = 0;
            $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
            $balances = ($record['stock_hand'] + $record['eta'] - $record['total']);

            if($balances > 0){
                $free_stock = $balances;
            }else{
                $free_stock = 0;
            }

            $content .= "<tr>
                        <td>" . $no . "</td>
                        <td style='mso-number-format:\@;'>" . $record['item_rm_id'] . "</td>
                        <td style='mso-number-format:\@;'>" . $record['item_rm_number'] . "</td>
                        <td style='mso-number-format:\@;'>" . $record['item_rm_name'] . "</td>
                        <td>" . $record['item_family_name'] . "</td>
                        <td style='text-align:right;'>" . round($record['stock_whs']) . "</td>
                        <td style='text-align:right;'>" . round($record['os_supply']) . "</td>
                        <td style='text-align:right;'>" . round($record['stock_hand']) . "</td>
                        <td style='text-align:right;'>" . round($record['eta']) . "</td>
                        <td style='text-align:right;'>" . round($record['total']) . "</td>
                        <td style='text-align:right;'>". number_format(@$balances) ."</td>";
            
            //ini di ambil dari total ETA yang harian
            //$record['eta']
            $wp = "";
            $styles2 = "";
            $balance = $record['stock_hand'];
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $working_date2 = date("Y-m-d", strtotime($firstDate2));

                //ETA
                $delivery_schedule = $this->db->query("SELECT item_rm_id, SUM(qty_receipt) as total FROM purchase_order_receipts WHERE item_rm_id = '$item_rm_id' and receipt_date = '$working_date2' GROUP BY item_rm_id");
                $totalRow = $delivery_schedule->row();
                $etaqty = empty(@$totalRow->total) ? 0 : @$totalRow->total;
                
                //Supply
                $ithSupply = $this->db->query("SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as supply FROM issued_material_details a WHERE a.item_rm_id = '$item_rm_id' and DATE_FORMAT(a.created_date, '%Y-%m-%d') = '$working_date2' and a.request_no NOT LIKE '%PR-%' GROUP BY a.item_rm_id");
                $ithSupplyRow = $ithSupply->row();

                $date_qty = $record["date_".$day];
                $balance += round($etaqty - $date_qty);

                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date2);
                $holiday = $this->db->get()->row();

                if (date('w', strtotime($firstDate2)) !== '0') {
                    if (@$holiday->remarks != null or @$holiday->remarks != ""){
                        $styles2 = 'background:#FFD974;';
                    }else{
                        if($balance < 0){
                            $styles2 = 'background:#FFC2C2;';
                        }else{
                            $styles2 = '';
                        }
                    }
                } else {
                    $styles2 = 'background:#FFD974;';
                }

                $content .= "<td style='text-align:right; ".$styles2."'>".@$etaqty."</td>
                            <td style='text-align:right; ".$styles2."'>".round($date_qty)."</td>
                            <td style='text-align:right; ".$styles2."'>".abs(round(@$ithSupplyRow->supply))."</td>
                            <td style='text-align:right; ".$styles2."'>".round($balance)."</td>";

                $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
                $day++;
            }

            $content .= "   <td style='text-align:right; ".$styles."'>" . round($free_stock) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                        </tr>";

            if($filter_status == "OK"){
                if($free_stock > 0){
                    $content2 .= $content;
                    $no++;
                }
            }elseif($filter_status == "SHORTAGE"){
                if($free_stock == 0){
                    $content2 .= $content;
                    $no++;
                }
            }else{
                $content2 .= $content;
                $no++;
            }
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MATERIAL CALCULATION</small>
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

        <table id="customers" border="1" style="width:150%;">
            <tr>
                <th style="text-align:center;" rowspan="3" width="20">NO</th>
                <th style="text-align:center;" width="150" rowspan="3">PART NO EBWS</th>
                <th style="text-align:center;" width="150" rowspan="3">PART NO SUPPLIER</th>
                <th style="text-align:center;" width="80" rowspan="3">DESCRIPTION</th>
                <th style="text-align:center;" width="80" rowspan="3">PRODUCT FAMILY</th>
                <th style="text-align:center;" width="50" rowspan="3">STOCK WHS</th>
                <th style="text-align:center;" width="50" rowspan="3">OS SUPPLY</th>
                <th style="text-align:center;" width="50" rowspan="3">ON HAND<br>STOCK</th>
                <th style="text-align:center;" width="50" rowspan="3">ETA</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL NEED</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL BAL</th>
                <th style="text-align:center;" width="50" colspan="'.($colspan * 4).'">QUANTITY NEED FROM WORKORDER</th>
                <th style="text-align:center;" width="50" rowspan="3">FREE STOCK</th>
                <th style="text-align:center;" width="50" colspan="8" rowspan="2">'.$next_month.'</th>
            </tr>' . $cut . $date . $content2;
        echo $html;
    }

    public function printOsPo(){
        $this->db = $this->load->database('db', TRUE);
        $item_rm_id = base64_decode($this->input->get("item_rm_id"));

        $ospo = $this->db->query("SELECT b.item_id, b.poc_id, b.poc_reqdate, COALESCE(b.request, 0) as request, COALESCE(a.receive, 0) as receive, (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) as balance
            FROM (select item_id, poc_id, poc_reqdate, sum(poc_reqqty) as request from poc WHERE item_id = '$item_rm_id' GROUP BY item_id, poc_id, poc_reqdate) b 
            LEFT JOIN (select item_id, por_pocid, SUM(por_receiveqty) as receive from por WHERE item_id = '$item_rm_id' GROUP BY por_pocid, item_id) a ON a.item_id = b.item_id and a.por_pocid = b.poc_id
            WHERE (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) > 0");
        $records = $ospo->result_array();

        $balance = 0;
        $request = 0;
        $receive = 0;
        foreach ($records as $record) {
            $request += $record['request'];
            $receive += $record['receive'];
            $balance += $record['balance'];
        }

        die(json_encode(array_merge(array("rows" => $records), ["footer" => [["item_id" => "Grand Total", "balance" => $balance, "receive" => $receive, "request" => $request, "plan" => 0]]])));
    }

    public function printBalance(){
        $item_rm_id = base64_decode($this->input->get("item_rm_id"));
        $filter_month = base64_decode($this->input->get("filter_month"));
        $filter_year = base64_decode($this->input->get("filter_year"));
        $filter_revision = base64_decode($this->input->get("filter_revision"));

        $ospo = $this->db->query("SELECT z.p_month, z.p_year, z.revision, z.item_fg_id, z.number_customer, z.item_rm_id, z.item_rm_number, z.item_rm_name, (
            SUM(total_date_1) +
            SUM(total_date_2) +
            SUM(total_date_3) +
            SUM(total_date_4) +
            SUM(total_date_5) +
            SUM(total_date_6) +
            SUM(total_date_7) +
            SUM(total_date_8) +
            SUM(total_date_9) +
            SUM(total_date_10) +
            SUM(total_date_11) +
            SUM(total_date_12) +
            SUM(total_date_13) +
            SUM(total_date_14) +
            SUM(total_date_15) +
            SUM(total_date_16) +
            SUM(total_date_17) +
            SUM(total_date_18) +
            SUM(total_date_19) +
            SUM(total_date_20) +
            SUM(total_date_21) +
            SUM(total_date_22) +
            SUM(total_date_23) +
            SUM(total_date_24) +
            SUM(total_date_25) +
            SUM(total_date_26) +
            SUM(total_date_27) +
            SUM(total_date_28) +
            SUM(total_date_29) +
            SUM(total_date_30) +
            SUM(total_date_31)) as total
        FROM (
            SELECT a.p_month, a.p_year, a.revision, a.item_fg_id, d.number_customer, b.item_rm_id, c.number as item_rm_number, c.name as item_rm_name,
            (a.date_1 * b.composition) as total_date_1,
            (a.date_2 * b.composition) as total_date_2,
            (a.date_3 * b.composition) as total_date_3,
            (a.date_4 * b.composition) as total_date_4,
            (a.date_5 * b.composition) as total_date_5,
            (a.date_6 * b.composition) as total_date_6,
            (a.date_7 * b.composition) as total_date_7,
            (a.date_8 * b.composition) as total_date_8,
            (a.date_9 * b.composition) as total_date_9,
            (a.date_10 * b.composition) as total_date_10,
            (a.date_11 * b.composition) as total_date_11,
            (a.date_12 * b.composition) as total_date_12,
            (a.date_13 * b.composition) as total_date_13,
            (a.date_14 * b.composition) as total_date_14,
            (a.date_15 * b.composition) as total_date_15,
            (a.date_16 * b.composition) as total_date_16,
            (a.date_17 * b.composition) as total_date_17,
            (a.date_18 * b.composition) as total_date_18,
            (a.date_19 * b.composition) as total_date_19,
            (a.date_20 * b.composition) as total_date_20,
            (a.date_21 * b.composition) as total_date_21,
            (a.date_22 * b.composition) as total_date_22,
            (a.date_23 * b.composition) as total_date_23,
            (a.date_24 * b.composition) as total_date_24,
            (a.date_25 * b.composition) as total_date_25,
            (a.date_26 * b.composition) as total_date_26,
            (a.date_27 * b.composition) as total_date_27,
            (a.date_28 * b.composition) as total_date_28,
            (a.date_29 * b.composition) as total_date_29,
            (a.date_30 * b.composition) as total_date_30,
            (a.date_31 * b.composition) as total_date_31
        FROM generate_mpp a
        JOIN bom b ON a.item_fg_id = b.item_fg_id
        JOIN item_rm c ON b.item_rm_id = c.id
        JOIN item_fg d ON a.item_fg_id = d.id
        WHERE a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision' and b.item_rm_id = '$item_rm_id'
        GROUP BY a.item_fg_id, b.item_rm_id
        ORDER BY a.item_fg_id, b.item_rm_id asc) z
        GROUP BY z.item_rm_id, z.item_fg_id");
        $records = $ospo->result_array();

        $balance = 0;
        foreach ($records as $record) {
            $balance += $record['total'];
        }

        die(json_encode(array_merge(array("rows" => $records), ["footer" => [["item_rm_id" => "Grand Total", "total" => $balance]]])));
    }
}
