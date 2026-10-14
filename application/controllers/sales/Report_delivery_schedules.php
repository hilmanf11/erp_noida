<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_delivery_schedules extends CI_Controller
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
            $this->load->view('sales/report_delivery_schedules');
        } else {
            redirect('error_access');
        }
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
                $arrSales = array();
                $arrDelivery = array();
                $arrBalance = array();

                $typesArr = ["PLAN", "ACTUAL", "BALANCE"];
                foreach ($typesArr as $type) {
                    $firstDate = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
                    $endDate = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

                    $total_qty = 0;
                    $total_del = 0;

                    $day = 1;
                    while (strtotime($firstDate) <= strtotime($endDate)) {
                        $trans_date = date("Y-m-d", strtotime($firstDate));

                        $this->db->select('*');
                        $this->db->from('sales_order_deliveries');
                        $this->db->where('customer_id', $record['customer_id']);
                        $this->db->where('item_fg_id', $record['item_fg_id']);
                        $this->db->where('plant', $record['plant']);
                        $this->db->where('trans_date', $trans_date);
                        $row = $this->db->get()->row();

                        $this->db->select('*');
                        $this->db->from('delivery_notes');
                        $this->db->where('customer_id', $record['customer_id']);
                        $this->db->where('item_fg_id', $record['item_fg_id']);
                        $this->db->where('plant', $record['plant']);
                        $this->db->where('delivery_note_date', $trans_date);
                        $row2 = $this->db->get()->row();

                        $actual_qty = empty(@$row->qty) ? 0 : @$row->qty;
                        $delivery_qty = empty(@$row2->qty) ? 0 : @$row2->qty;

                        $arrSales = array_merge($arrSales, [
                            "qty_".$day => $actual_qty,
                        ]);

                        $arrDelivery = array_merge($arrDelivery, [
                            "qty_".$day => $delivery_qty,
                        ]);

                        $arrBalance = array_merge($arrBalance, [
                            "qty_".$day => ($actual_qty - $delivery_qty),
                        ]);

                        $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
                        $day++;
                        $total_qty += $actual_qty;
                        $total_del += $delivery_qty;
                    }

                    if($type == "PLAN"){
                        $arr = array_merge($arrSales, ["total_qty" => $total_qty]);
                    }elseif($type == "ACTUAL"){
                        $arr = array_merge($arrDelivery, ["total_qty" => $total_del]);
                    }else{
                        $arr = array_merge($arrBalance, ["total_qty" => ($total_qty - $total_del)]);
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
                        "type" => $type,
                        // "total_qty" => $total_qty,
                        "created_by" => $record['created_by'],
                        "created_date" => $record['created_date'],
                        "updated_by" => $record['updated_by'],
                        "updated_date" => $record['updated_date'],
                        "status" => $record['status'],
                    ]);
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $dataFinal]);
            echo json_encode($result);
        }
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
