<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Production_schedules extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/production_schedules');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('production_schedules', ["name" => $post]);
        echo json_encode($send);
    }
    public function readPeriodAll()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules ORDER BY `period` DESC");
        echo json_encode($send);
    }
    public function readWpAll()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp, workorder, item_fg_id FROM production_schedules WHERE `period` = '$period' and (workorder like '%$post%' or wp like '%$post%') ORDER BY workorder DESC");
        echo json_encode($send);
    }
    public function readPeriod($types = "")
    {
        if($types == "period"){
            $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules WHERE `status` in ('0','1') and precut_status = 'YES' and `period` > '202410' ORDER BY `period` DESC");
            echo json_encode($send);
        }else{
            $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules WHERE `status` in ('0','1') and precut_status = 'YES' ORDER BY `period` DESC");
            echo json_encode($send);
        }
    }

    public function readWp()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp FROM production_schedules WHERE `status` in ('0','1') and precut_status = 'YES' and `period` = '$period' ORDER BY CAST(wp AS SIGNED) DESC");
        echo json_encode($send);
    }

    public function readWorkorder()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $send = $this->crud->query("SELECT a.*, b.number_customer
            FROM production_schedules a
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.status in ('0','1') and a.precut_status = 'YES' 
            and a.period = '$period' and wp = '$wp' 
            ORDER BY workorder DESC");
        echo json_encode($send);
    }

    public function filterWp($month, $year)
    {   
        $period = $year.$month;
        $send = $this->crud->query("SELECT DISTINCT wp FROM production_schedules WHERE `period` = '$period' ORDER BY `wp` DESC");
        echo json_encode($send);
    }

    public function filterWo($month, $year)
    {   
        $period = $year.$month;
        $send = $this->crud->query("SELECT DISTINCT workorder FROM production_schedules WHERE `period` = '$period' ORDER BY `workorder` DESC");
        echo json_encode($send);
    }

    public function readMonth()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array(
                "number" => $key,
                "name" => $value
            );
        }
        die(json_encode($arr));
    }

    public function readYear()
    {
        $tahun_before = date('Y', strtotime('-5 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_before; $i <= $tahun_next; $i++) {
            $arr[] = array(
                "number" => "$i"
            );
        }
        die(json_encode($arr));
    }

    public function workorder($wp, $trans_date)
    {
        $datenow = date("ymd", strtotime($trans_date));
        $sqlGetID = $this->db->query("SELECT max(workorder) as kode FROM production_schedules WHERE workorder like '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%05s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%05s", $urutan);
        }
        $workOrderNo = "WO" . $datenow . "-" . $autoID;
        return $workOrderNo;
    }

    public function getDataWp()
    {
        $post = $this->input->get();
        $p_month = $post['p_month'];
        $p_year = $post['p_year'];
        $customer_id = base64_decode($post['customer_id']);
        $item_fg_id = base64_decode($post['item_fg_id']);

        $query_generate_mpp = $this->db->query("SELECT * FROM generate_mpp WHERE p_month='$p_month' and p_year='$p_year' and customer_id = '$customer_id' and item_fg_id = '$item_fg_id'");
        $generate_mpp = $query_generate_mpp->row_array();

        $period = $p_year.$p_month;
        
        $firstDate = date("Y-m-01", strtotime(date("$p_year-$p_month-01")));
        $endDate = date("Y-m-t", strtotime(date("$p_year-$p_month")));

        $wp = 0;
        $tgl = 1;
        $alfabet = "z";
        $arr = array();
        while (strtotime($firstDate) <= strtotime($endDate)) {
            $working_date = date('Y-m-d', strtotime($firstDate));

            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (@$holiday->remarks != null or @$holiday->remarks != "") {
                if($alfabet == "z"){
                    $alfabets = "A";
                }elseif($alfabet == "A"){
                    $alfabets = "B";
                }elseif($alfabet == "B"){
                    $alfabets = "C";
                }elseif($alfabet == "C"){
                    $alfabets = "D";
                }elseif($alfabet == "D"){
                    $alfabets = "E";
                }elseif($alfabet == "E"){
                    $alfabets = "F";
                }elseif($alfabet == "F"){
                    $alfabets = "G";
                }elseif($alfabet == "G"){
                    $alfabets = "H";
                }elseif($alfabet == "H"){
                    $alfabets = "I";
                }elseif($alfabet == "I"){
                    $alfabets = "J";
                }elseif($alfabet == "J"){
                    $alfabets = "K";
                }elseif($alfabet == "K"){
                    $alfabets = "L";
                }elseif($alfabet == "L"){
                    $alfabets = "M";
                }elseif($alfabet == "M"){
                    $alfabets = "N";
                }elseif($alfabet == "N"){
                    $alfabets = "O";
                }else{  
                    $alfabets = "";
                }

                $wpp = $wp.$alfabets;
                $alfabet = $alfabets;
                $firstDate_check = date("d M", strtotime("+1 day", strtotime($firstDate)));
                $working_date_check = date('Y-m-d', strtotime($firstDate_check));
                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date_check);
                $holiday_check = $this->db->get()->row();

                // if (date('w', strtotime($firstDate_check)) !== '0' && date('w', strtotime($firstDate_check)) !== '6') {
                    if (@$holiday_check->remarks == null or @$holiday_check->remarks == "") {
                        $wp++;
                    }
                // }
            }else{
                if($wp == 0){
                    $wp = 1;
                }

                $wpp = $wp;
                $alfabet = "z";
                $firstDate_check = date("d M", strtotime("+1 day", strtotime($firstDate)));
                $working_date_check = date('Y-m-d', strtotime($firstDate_check));
                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date_check);
                $holiday_check = $this->db->get()->row();

                // if (date('w', strtotime($firstDate_check)) !== '0' && date('w', strtotime($firstDate_check)) !== '6') {
                    if (@$holiday_check->remarks == null or @$holiday_check->remarks == "") {
                        $wp++;
                    }
                // }
            }

            $qty = $generate_mpp['date_'.$tgl];
            $trans_date = $p_year."-".$p_month."-".sprintf("%02s", $tgl);

            $dataFinal = array(
                "trans_date" => $trans_date,
                "period" => $period,
                "year" => $p_year,
                "month" => $p_month,
                "wp" => $wpp,
                "qty" => $qty,
            );

            $productionSchedule = $this->crud->read("production_schedules", [], [
                "customer_id" => $customer_id, 
                "item_fg_id" => $item_fg_id,
                "period" => $period,
                "year" => $p_year,
                "month" => $p_month,
                "wp" => $wpp,
            ]);

            if($qty > 0){
                if($productionSchedule){
                    //
                }else{
                    $arr[] = $dataFinal;
                }
            }else{
                //
            }

            $tgl++;
            $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        }

        die(json_encode($arr));
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);

            $filter_month = $this->input->get('filter_month');
            $filter_year = $this->input->get('filter_year');
            $filter_month2 = $this->input->get('filter_month2');
            $filter_year2 = $this->input->get('filter_year2');
            $filter_wp = $this->input->get('filter_wp');
            $filter_workorder = $this->input->get('filter_workorder');
            $filter_customers = $this->input->get('filter_customers');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_date_from = $this->input->get('filter_date_from');
            $filter_date_to = $this->input->get('filter_date_to');
            $filter_status = $this->input->get('filter_status');
            $period = $filter_year.$filter_month;
            $period2 = $filter_year2.$filter_month2;

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $this->db->select("a.*, b.number as customer_number, b.name as customer_name, b.type as customer_type, 
                c.number as item_number, c.name as item_name, c.number_customer, c.uom, COALESCE(d.total_rfg, 0) as total_rfg");
            $this->db->from('production_schedules a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join("(SELECT workorder, item_fg_id, SUM(qty) as total_rfg FROM wip_receipts GROUP BY workorder, item_fg_id) d", 'a.workorder = d.workorder and a.item_fg_id = d.item_fg_id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where("a.period between '$period' and '$period2'");
            if($filter_wp != ""){
                $this->db->where('a.wp', $filter_wp);
            }
            $this->db->like('a.workorder', $filter_workorder);
            $this->db->like('a.customer_id', $filter_customers);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->group_by('a.workorder');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('c.number', 'ASC');
            $this->db->order_by('a.id', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $arr = array();
            foreach ($records as $record) {
                $assy_number = $record['number_customer'];
                $mpp = $record['period'];
                $wp = sprintf("%02s", $record['wp'])."/".substr($mpp, -2);

                $sqlQty = $this->dummy->query("SELECT x.kode_proses, COUNT(x.serial_number) as qty FROM (
                    SELECT DISTINCT z.serial_number, z.kode_proses FROM (
                    SELECT DISTINCT
                        TRIM(a.serial_number) AS serial_number,
                        TRIM(a.assy_number) AS assy_number,
                        TRIM(a.customer) AS customer,
                        TRIM(a.mpp) AS mpp,
                        TRIM(a.wp) AS wp,
                        TRIM(a.lot) AS lot,
                        kode_proses,
                        DATE(a.tanggal_proses) AS tanggal,
                        COALESCE(a.jam_scan,'00:00:00') AS jam,
                        a.wds_qty
                    FROM mst_scan_proses a
                    WHERE kode_proses IN ('1','11') and assy_number = '$assy_number' and mpp = '$mpp' and wp = '$wp' and
                    tanggal_proses between '$filter_date_from' and '$filter_date_to' AND (status_repair = 'f' or status_repair is null)
                    ORDER BY serial_number) z 
                    ORDER BY z.kode_proses ASC) x
                    GROUP BY x.kode_proses
                    ORDER BY x.kode_proses ASC");
                $rowQty = $sqlQty->result_array();
                
                $qty_scan = empty(@$rowQty[1]['qty']) ? 0 : @$rowQty[1]['qty'];
                $qty_wirecollect = empty(@$rowQty[0]['qty']) ? 0 : @$rowQty[0]['qty'];

                if($record['qty'] > $record['total_rfg']){
                    $status_rfg = "0";
                }else{
                    $status_rfg = "1";
                }

                if($qty_scan >= $record['qty']){
                    $status_visual = "2";
                }else{
                    $status_visual = "0";
                }

                if($filter_status == $status_rfg || $filter_status == ""){
                    $arr[] = array_merge($record, [
                        "total_visual" => $qty_scan,
                        "total_wirecollect" => $qty_wirecollect,
                        "status_rfg" => $status_rfg,
                        "status_visual" => $status_visual,
                    ]);
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $arr]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $workorder = $this->workorder($post['wp'], $post['trans_date']);
                $production_schedules = $this->crud->read('production_schedules', [], ["customer_id" => $post['customer_id'], "item_fg_id" => $post['item_fg_id'], "wp" => $post['wp'], "trans_date" => $post['trans_date']]);

                if (@$production_schedules->id) {
                    show_error("Duplicate Data");
                } else {
                    $postFinal = array_merge($post, array("workorder" => $workorder, "period" => $post['year'] . $post['month'], "status_input" => "MANUAL"));
                    $send = $this->crud->create('production_schedules', $postFinal);
                }
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('production_schedules', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    
    public function precut()
    {
        if ($this->input->post()) {
            $id   = $this->input->post('id');
            $send = $this->crud->update('production_schedules', ["id" => $id], ["precut_status" => "YES", "precut_by" => $this->session->username, "precut_date" => date("Y-m-d H:i:s")]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('production_schedules', ["id" => $data['id']]);
        // $update = $this->crud->update('sales_orders', ["number" => $data['so_number'], "item_fg_id" => $data['item_fg_id']], ["status" => 0]);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=production_schedules_$format.xls");
        }

        $this->dummy = $this->load->database('dummy', TRUE);

        $filter_month = $this->input->get('filter_month');
        $filter_year = $this->input->get('filter_year');
        $filter_month2 = $this->input->get('filter_month2');
        $filter_year2 = $this->input->get('filter_year2');
        $filter_wp = $this->input->get('filter_wp');
        $filter_workorder = $this->input->get('filter_workorder');
        $filter_customers = $this->input->get('filter_customers');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_date_from = $this->input->get('filter_date_from');
        $filter_date_to = $this->input->get('filter_date_to');
        $filter_status = $this->input->get('filter_status');

        $period = $filter_year.$filter_month;
        $period2 = $filter_year2.$filter_month2;

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $this->db->select("a.*, b.number as customer_number, b.name as customer_name, b.type as customer_type, 
            c.number as item_number, c.name as item_name, c.number_customer, c.uom, COALESCE(d.total_rfg, 0) as total_rfg");
        $this->db->from('production_schedules a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join("(SELECT workorder, SUM(qty) as total_rfg FROM wip_receipts GROUP BY workorder) d", 'a.workorder = d.workorder', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.period between '$period' and '$period2'");
        if($filter_wp != ""){
            $this->db->where('a.wp', $filter_wp);
        }
        $this->db->like('a.workorder', $filter_workorder);
        $this->db->like('a.customer_id', $filter_customers);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->group_by('a.workorder');
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('c.number', 'ASC');
        $this->db->order_by('a.id', 'ASC');
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
                                <small>PRODUCTION SCHEDULE</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Status<br>Visual</th>
                    <th>Status<br>RFG</th>
                    <th>Production<br>Order</th>
                    <th>Period</th>
                    <th>WP</th>
                    <th>WP Date</th>
                    <th>Work Order</th>
                    <th>Customer No</th>
                    <th>Customer Name</th>
                    <th>Customer Type</th>
                    <th>Product No</th>
                    <th>Product Customer</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Wire Collect Qty</th>
                    <th>Visual Qty</th>
                    <th>RFG Qty</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                    <th>Updated By</th>
                    <th>Updated Date</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $assy_number = $data['number_customer'];
            $mpp = $data['period'];
            $wp = sprintf("%02s", $data['wp'])."/".substr($mpp, -2);

            $sqlQty = $this->dummy->query("SELECT x.kode_proses, COUNT(x.serial_number) as qty FROM (
                SELECT DISTINCT z.serial_number, z.kode_proses FROM (
                SELECT DISTINCT
                    TRIM(a.serial_number) AS serial_number,
                    TRIM(a.assy_number) AS assy_number,
                    TRIM(a.customer) AS customer,
                    TRIM(a.mpp) AS mpp,
                    TRIM(a.wp) AS wp,
                    TRIM(a.lot) AS lot,
                    kode_proses,
                    DATE(a.tanggal_proses) AS tanggal,
                    COALESCE(a.jam_scan,'00:00:00') AS jam,
                    a.wds_qty
                FROM mst_scan_proses a
                WHERE kode_proses IN ('1','11') and assy_number = '$assy_number' and mpp = '$mpp' and wp = '$wp' and
                tanggal_proses between '$filter_date_from' and '$filter_date_to' AND (status_repair = 'f' or status_repair is null)
                ORDER BY serial_number) z 
                ORDER BY z.kode_proses ASC) x
                GROUP BY x.kode_proses
                ORDER BY x.kode_proses ASC");
            $rowQty = $sqlQty->result_array();
            
            $qty_scan = empty(@$rowQty[1]['qty']) ? 0 : @$rowQty[1]['qty'];
            $qty_wirecollect = empty(@$rowQty[0]['qty']) ? 0 : @$rowQty[0]['qty'];

            if($data['qty'] > $data['total_rfg']){
                $status_rfg = "OPEN";
                $status_rfg2 = 0;
            }else{
                $status_rfg = "CLOSE";
                $status_rfg2 = 1;
            }

            if($qty_scan >= $data['qty']){
                $status_visual = "CLOSE";
            }else{
                $status_visual = "OPEN";
            }

            if($filter_status == $status_rfg2 || $filter_status == ""){
                $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $status_visual . '</td>
                            <td>' . $status_rfg . '</td>
                            <td>' . $data['precut_status'] . '</td>
                            <td>' . $data['period'] . '</td>
                            <td>' . $data['wp'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['customer_number'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['customer_type'] . '</td>
                            <td>' . $data['item_number'] . '</td>
                            <td>' . $data['number_customer'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                            <td>' . number_format($qty_wirecollect, 2) . '</td>
                            <td>' . number_format($qty_scan, 2) . '</td>
                            <td>' . number_format($data['total_rfg'], 2) . '</td>
                            <td>' . $data['created_by'] . '</td>
                            <td>' . $data['created_date'] . '</td>
                            <td>' . $data['updated_by'] . '</td>
                            <td>' . $data['updated_date'] . '</td>
                        </tr>';
                $no++;
            }
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
