<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order_deliveries extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/sales_order_deliveries');
        } else {
            redirect('error_access');
        }
    }

    public function readCustomer()
    {
        $send = $this->crud->query("SELECT b.id, b.number, b.name 
            FROM sales_order_deliveries a 
            JOIN customers b ON a.customer_id = b.id 
            WHERE a.status = 0
            GROUP BY a.customer_id ORDER BY b.name ASC");
        echo json_encode($send);
    }

    public function readTransdate($customer_id)
    {  
        $customer_id = base64_decode($customer_id);
        $send = $this->crud->query("SELECT a.trans_date
            FROM sales_order_deliveries a 
            JOIN customers b ON a.customer_id = b.id
            WHERE b.id = '$customer_id'
            AND a.status = 0
            GROUP BY a.trans_date ORDER BY a.trans_date DESC");
        echo json_encode($send);
    }

    public function readPlant($customer_id, $trans_date)
    {  
        $customer_id = base64_decode($customer_id);
        $trans_date = base64_decode($trans_date);
        $send = $this->crud->query("SELECT plant
            FROM sales_order_deliveries
            WHERE customer_id = '$customer_id'
            AND trans_date = '$trans_date'
            AND `status` = 0
            GROUP BY plant ORDER BY plant ASC");
        echo json_encode($send);
    }

    public function calendars()
    {
        $bulan = $this->input->post('month');
        $tahun = $this->input->post('year');
        $customer_id = $this->input->post('customer_id');
        $item_fg_id = $this->input->post('item_fg_id');
        $plant = $this->input->post('plant');

        if ($bulan == "" or $tahun == "") {
            $bulan = date('m');
            $tahun = date('Y');
        }

        $hari = "01";
        $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));

        $html = '<style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }

                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 10px;
                    }

                    #customers td,
                    #customers th {
                        border: 1px solid #ddd;
                        padding: 2px;
                        height:50px;
                    }

                    #customers tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }

                    #customers tr:hover {
                        background-color: #ddd;
                    }

                    #customers th {
                        padding-top: 2px;
                        padding-bottom: 2px;
                        text-align: left;
                        color: black;
                    }
                </style>
                <table id="customers" style="width: 100%;">
                    <tr>
                        <td align=center width="100">
                            <font color="#FF0000">Sunday</font>
                        </td>
                        <td align=center width="100">Monday</td>
                        <td align=center width="100">Tuesday</td>
                        <td align=center width="100">Wednesday</td>
                        <td align=center width="100">Thursday</td>
                        <td align=center width="100">Friday</td>
                        <td align=center width="100">Saturday</td>
                    </tr>';

        $s = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));

        for ($ds = 1; $ds <= $s; $ds++) {
            $html .= "<td></td>";
        }

        for ($d = 1; $d <= $jumlahhari; $d++) {
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 0) {
                $html .= "<tr>";
            }

            $tanggal = $tahun . "-" . $bulan . "-" . sprintf("%02s", $d);
            
            $this->db->select('*');
            $this->db->from('sales_order_deliveries');
            $this->db->where('customer_id', $customer_id);
            $this->db->where('item_fg_id', $item_fg_id);
            $this->db->where('trans_date', $tanggal);
            $this->db->where('plant', $plant);
            $data = $this->db->get()->result_array();

            if(@$data[0]['status'] == 1){
                $disable = "disabled";
            }else{
                $disable = "";
            }

            //Jika Hari Minggu
            $style = "background:white !important;";
            // $checkbox = "<input hidden checked class='checked' type='checkbox' value='" . $d . "' name='days[]' style='float: left; width: 20px;'/>";
            $note = "<input type='number' style='width:100px;' $disable name='qty[]' value='" . @$data[0]['qty'] . "'/>
                     <input hidden type='text' style='width:100px;' $disable name='trans_date[]' value='" . @$tanggal . "'/>";

            $html .= "  <td align=center style='" . $style . "' valign=middle>
                            <b style='font-size: 20px;'>$d</b><br>
                            $note
                        </td>";

            //Jika Sudah seminggu
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 6) {
                $html .= "</tr>";
            }
        }
        $html .= '</table>';

        echo $html;
    }

    public function datatables()
    {
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
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_plant = base64_decode($this->input->get('filter_plant'));
            $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

            //Select Query
            $this->db->select("a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name, c.name as customer_name");
            $this->db->from('sales_order_deliveries a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            if($filter_customer != ""){
                $this->db->where('a.customer_id', $filter_customer);
            }
            if($filter_plant != ""){
                $this->db->where('a.plant', $filter_plant);
            }
            if($filter_item_fg != ""){
                $this->db->where('b.id', $filter_item_fg);
            }
            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.customer_id');
            $this->db->group_by('a.plant');
            $this->db->order_by('c.name', 'asc');
            $this->db->order_by('b.number', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $dataFinal = array();
            foreach ($records as $record) {
                $firstDate = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

                $day = 1;
                $arr = array();
                $total_qty = 0;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $trans_date = date("Y-m-d", strtotime($firstDate));

                    $this->db->select('*');
                    $this->db->from('sales_order_deliveries');
                    $this->db->where('customer_id', $record['customer_id']);
                    $this->db->where('item_fg_id', $record['item_fg_id']);
                    $this->db->where('plant', $record['plant']);
                    $this->db->where('trans_date', $trans_date);
                    $row = $this->db->get()->row();

                    $actual_qty = @$row->qty;

                    $arr = array_merge($arr, [
                        "qty_".$day => $actual_qty,
                    ]);

                    $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
                    $day++;
                    $total_qty += $actual_qty;
                }

                $dataFinal[] = array_merge($arr, [
                    "id" => $record['id'],
                    "p_month" => $record['p_month'],
                    "p_year" => $record['p_year'],
                    "item_fg_id" => $record['item_fg_id'],
                    "customer_id" => $record['customer_id'],
                    "item_fg_number" => $record['item_fg_number'],
                    "number_customer" => $record['number_customer'],
                    "item_fg_name" => $record['item_fg_name'],
                    "customer_name" => $record['customer_name'],
                    "plant" => $record['plant'],
                    "total_qty" => $total_qty,
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date'],
                    "status" => $record['status'],
                ]);
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $dataFinal]);
            echo json_encode($result);
        }
    }

    public function create(){
        if ($this->input->post()) {
            $post = $this->input->post();

            $i = 0;
            foreach ($post['qty'] as $row => $val) {
                $check = $this->crud->read("sales_order_deliveries", [], [
                    "item_fg_id" => $post['item_fg_id'], 
                    "trans_date" => $post['trans_date'][$i], 
                    "customer_id" => $post['customer_id'],
                    "plant" => $post['plant'],
                ]);

                if(empty($check)){
                    if($val != "" || $val != 0){
                        $dataFinal = [
                            "p_month" => $post['p_month'],
                            "p_year" => $post['p_year'],
                            "customer_id" => $post['customer_id'],
                            "item_fg_id" => $post['item_fg_id'],
                            "qty" => $val,
                            "trans_date" => $post['trans_date'][$i],
                            "plant" => $post['plant'],
                        ];
    
                        $this->crud->create('sales_order_deliveries', $dataFinal);
                    }
                }

                $i++;
            }
            
            echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
        }
    }

    public function update(){
        if ($this->input->post()) {
            $post = $this->input->post();

            $i = 0;
            foreach ($post['qty'] as $row => $val) {
                $check = $this->crud->read("sales_order_deliveries", [], [
                    "item_fg_id" => $post['item_fg_id'], 
                    "trans_date" => $post['trans_date'][$i], 
                    "customer_id" => $post['customer_id'],
                    "plant" => $post['plant'],
                ]);

                if(!empty($check)){
                    if($val != "" || $val != 0){
                        $dataFinal = [
                            "p_month" => $post['p_month'],
                            "p_year" => $post['p_year'],
                            "customer_id" => $post['customer_id'],
                            "item_fg_id" => $post['item_fg_id'],
                            "qty" => $val,
                            "trans_date" => $post['trans_date'][$i],
                            "plant" => $post['plant'],
                        ];
    
                        $this->crud->update('sales_order_deliveries', [
                            "item_fg_id" => $post['item_fg_id'], 
                            "trans_date" => $post['trans_date'][$i], 
                            "customer_id" => $post['customer_id']
                        ], $dataFinal);
                    }
                }

                $i++;
            }
            
            echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sales_order_deliveries', $data);
        echo $send;
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
                'p_month' => $data->val($i, 2),
                'p_year' => $data->val($i, 3),
                'customer_id' => $data->val($i, 4),
                'item_fg_number' => $data->val($i, 5),
                'trans_date' => $data->val($i, 6),
                'qty' => $data->val($i, 7),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Data
            $item_fg = $this->crud->read("item_fg", [], ["number" => $data['item_fg_number']]);
            $customer = $this->crud->read("customers", [], ["id" => $data['customer_id']]);
            $sales_order_deliveries = $this->crud->read('sales_order_deliveries', [], [
                "p_month" => $data['p_month'], 
                "p_year" => $data['p_year'], 
                "customer_id" => $data['customer_id'], 
                "item_fg_id" => @$item_fg->id,
                "trans_date" => $data['trans_date']
            ]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No EBWS " . $data['item_fg_id'] . " Not Found", "theme" => "error"));
            } else if (empty($customer->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer ID " . $data['customer_id'] . " Not Found", "theme" => "error"));
            } else if(!empty($sales_order_deliveries)){
                echo json_encode(array("title" => "Duplicate", "message" => "Product No EBWS " . $data['item_fg_id'] . " and Customer ".$data['customer_id']." is Duplicate ", "theme" => "error"));
            } else {
                $dataFinal = [
                    "p_month" => $data['p_month'],
                    "p_year" => $data['p_year'],
                    "customer_id" => $data['customer_id'],
                    "item_fg_id" => @$item_fg->id,
                    "qty" => $data['qty'],
                    "trans_date" => $data['trans_date'],
                ];
                
                $this->crud->create('sales_order_deliveries', $dataFinal);
                echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/sales_order_deliveries.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/sales_order_deliveries.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/sales_order_deliveries.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_order_deliveries_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

        $firstDate = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));

        $this->db->select("a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name, c.name as customer_name");
        $this->db->from('sales_order_deliveries a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        if($filter_customer != ""){
            $this->db->where('a.customer_id', $filter_customer);
        }
        if($filter_plant != ""){
            $this->db->where('a.plant', $filter_plant);
        }
        if($filter_item_fg != ""){
            $this->db->where('b.id', $filter_item_fg);
        }
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.plant');
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();

        //Setting Header
        $styles = "";
        $date = '';
        $content = '';
        $no = 1;
        $colspan = 0;

        while (strtotime($firstDate) <= strtotime($endDate)) {
            $working_date = date("Y-m-d", strtotime($firstDate));
            $working_date2 = date("d F", strtotime($firstDate));

            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $styles = 'background:#FFD974;';
                } else{
                    $styles = "";
                }
            } else {
                $styles = 'background:#FFD974;';
            }

            //Setting Header
            $date .= '<th width="50" style="text-align:center; ' . $styles . '">'.$working_date2.'</th>';

            $colspan++;
            $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
        }

        foreach ($records as $record) {
            $day = 1;
            $firstDate2 = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

            $day = 1;
            $styles2 = "";
            $isi = "";
            $total_qty = 0;
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $trans_date = date("Y-m-d", strtotime($firstDate2));

                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $trans_date);
                $holiday = $this->db->get()->row();

                if (date('w', strtotime($firstDate2)) !== '0' && date('w', strtotime($firstDate2)) !== '6') {
                    if (@$holiday->remarks != null or @$holiday->remarks != ""){
                        $styles2 = 'background:#FFD974;';
                    }else{
                        if(@$row->qty < 0){
                            $styles2 = 'background:#FFC2C2;';
                        }else{
                            $styles2 = '';
                        }
                    }
                } else {
                    $styles2 = 'background:#FFD974;';
                }

                $this->db->select('*');
                $this->db->from('sales_order_deliveries');
                $this->db->where('customer_id', $record['customer_id']);
                $this->db->where('item_fg_id', $record['item_fg_id']);
                $this->db->where('plant', $record['plant']);
                $this->db->where('trans_date', $trans_date);
                $row = $this->db->get()->row();

                $actual_qty = @$row->qty;

                $isi .= "<td style='text-align:right; ".$styles2."'>".$actual_qty."</td>";

                $firstDate2 = date("d M Y", strtotime("+1 day", strtotime($firstDate2)));
                $day++;
                $total_qty += $actual_qty;
            }

            $content .= "<tr>
                            <td>" . $no . "</td>
                            <td style='mso-number-format:\@;'>" . $record['item_fg_number'] . "</td>
                            <td>" . $record['number_customer'] . "</td>
                            <td>" . $record['item_fg_name'] . "</td>
                            <td style='text-align:right;'>" . $total_qty . "</td>" . $isi;
            $content .= "</tr>";
            $no++;
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->logo . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>DELIVERY SCHEDULE OF '.$filter_year.' YEAR</small>
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

        <table id="customers" border="1" style="width:100%;">
            <tr>
                <th style="text-align:center;" rowspan="2" width="20">NO</th>
                <th style="text-align:center;" width="150" rowspan="2">Product No EBWS</th>
                <th style="text-align:center;" width="150" rowspan="2">Product No Customer</th>
                <th style="text-align:center;" width="200" rowspan="2">Description</th>
                <th style="text-align:center;" width="80" rowspan="2">Total QTY</th>
                <th style="text-align:center;" width="50" colspan="'.($colspan).'">Delivery Schedule Qty</th>
            </tr>' . $date . $content;
        echo $html;
    }
}
