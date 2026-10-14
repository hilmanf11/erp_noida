<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Loadcap_all extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.item_fg_id]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/loadcap_all');
        } else {
            redirect('error_access');
        }
    }

    public function checkCalendar($period)
    {
        $monthStart = strtotime("01 " . $period);
        $start = strtotime(date('Y-m-01', $monthStart));
        $finish = strtotime(date('Y-m-t', $monthStart));

        //HKW 1
        $hkw = 0;
        for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
            $working_date = date('Y-m-d', $z);

            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', $z) !== '0') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $hkw += 0;
                } else {
                    $hkw += 1;
                }
            } else {
                $hkw += 0;
            }
        }

        return $hkw;
    }

    public function getData()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));

            $period = $filter_year . "-" . $filter_month . "-01";

            // Select Query
            $this->db->select('id, number, name');
            $this->db->from('customers');
            $this->db->like('id', $filter_customer);
            $this->db->group_by('id');
            $this->db->order_by('name', 'asc');
            $records = $this->db->get()->result_array();

            $arr['total'] = @count($records);
            $array = array_merge($records, $arr);

            die(json_encode($array));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getData2()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));

            $this->db->select('a.customer_id, a.item_fg_id');
            $this->db->from('generate_mps a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('a.customer_id', $filter_customer);
            $this->db->group_by('a.item_fg_id');
            $this->db->order_by('a.item_fg_id', 'asc');
            $records2 = $this->db->get()->result_array();

            $customer = $this->crud->read("customers", [], ["id" => $filter_customer]);

            $m1 = 0;
            $m2 = 0;
            $m3 = 0;
            $m4 = 0;
            $m5 = 0;
            $m6 = 0;
            $status = "0";
            foreach ($records2 as $data2) {
                $item_fg_id = $data2['item_fg_id'];

                $query = $this->db->query("SELECT a.ltpp_month, a.ltpp_month2, a.prod_plan, (b.total_pp / 3600) as umh, (SUM(a.prod_plan) * (b.total_pp / 3600)) as total_umh
                FROM generate_mps_details a
                JOIN (SELECT item_fg_id, sum(cycle_time) as total_pp FROM umh GROUP BY item_fg_id) b on a.item_fg_id = b.item_fg_id
                WHERE a.item_fg_id = '$item_fg_id' and a.customer_id = '$filter_customer' and a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision'
                GROUP BY a.item_fg_id, a.ltpp_month2
                ORDER BY a.item_fg_id, a.ltpp_month2 asc");
                $records3 = $query->result_array();
                $rows = $query->num_rows();

                if ($rows > 0) {
                    $status = $status;
                } else {
                    $status = "1";
                }

                $m1 += @round($records3[0]['total_umh']);
                $m2 += @round($records3[1]['total_umh']);
                $m3 += @round($records3[2]['total_umh']);
                $m4 += @round($records3[3]['total_umh']);
                $m5 += @round($records3[4]['total_umh']);
                $m6 += @round($records3[5]['total_umh']);
            }

            $arr[] = array(
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision,
                "customer_id" => @$customer->id,
                "customer_name" => @$customer->name,
                "month_1" => $m1,
                "month_2" => $m2,
                "month_3" => $m3,
                "month_4" => $m4,
                "month_5" => $m4,
                "month_6" => $m5,
                "status" => @$status
            );

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $loadcap = $this->crud->reads('generate_loadcap', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "customer_id" => $post['customer_id']
            ]);

            if (count($loadcap) > 0) {
                $send   = $this->crud->update('generate_loadcap', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "customer_id" => $post['customer_id']
                ], $post);
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                $send   = $this->crud->create('generate_loadcap', $post);
                echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/generate_loadcap.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/generate_loadcap.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/generate_loadcap.txt";

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
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=generate_loadcap_all_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_shift = base64_decode($this->input->get('filter_shift'));
            $filter_overtime = base64_decode($this->input->get('filter_overtime'));

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_4 =  date('M Y', strtotime('+4 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_5 =  date('M Y', strtotime('+5 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            // Select Query
            $this->db->select('a.*, (a.month_1 + a.month_2 + a.month_3 + a.month_4 + a.month_5 + a.month_6) as total_umh');
            $this->db->from('generate_loadcap a');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->order_by('a.customer_id', 'desc');
            $records = $this->db->get()->result_array();

            // Select Query
            $this->db->select('SUM(station) as total');
            $this->db->from('process_main_sub');
            $machine = $this->db->get()->row();

            $hkw_1 = $this->checkCalendar($month_0);
            $hkw_2 = $this->checkCalendar($month_1);
            $hkw_3 = $this->checkCalendar($month_2);
            $hkw_4 = $this->checkCalendar($month_3);
            $hkw_5 = $this->checkCalendar($month_4);
            $hkw_6 = $this->checkCalendar($month_5);

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
                            <small>' . $config->description . '</small>
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
        <center>
            <h3 style="margin:0;">LOADING CAPACITY CUSTOMER</h3>
            PERIOD <b>' . $filter_year . '/' . $filter_month . '</b>
            REVISION <b>' . $filter_revision . '</b>
        </center>
        <br>
        <table id="customers" border="1">
            <tr>
                <th style="text-align:center;" rowspan="3" width="20">No</th>
                <th style="text-align:center;" rowspan="3">Customer</th>
                <th style="text-align:center;" colspan="6">Month</th>
                <th style="text-align:center;" rowspan="2">Average</th>
            </tr>
            <tr>
                <th style="text-align:center;">' . $month_0 . '</th>
                <th style="text-align:center;">' . $month_1 . '</th>
                <th style="text-align:center;">' . $month_2 . '</th>
                <th style="text-align:center;">' . $month_3 . '</th>
                <th style="text-align:center;">' . $month_4 . '</th>
                <th style="text-align:center;">' . $month_5 . '</th>
            </tr>
            <tr>
                <th style="text-align:center;">Prod Plan (UMH)</th>
                <th style="text-align:center;">Prod Plan (UMH)</th>
                <th style="text-align:center;">Prod Plan (UMH)</th>
                <th style="text-align:center;">Prod Plan (UMH)</th>
                <th style="text-align:center;">Prod Plan (UMH)</th>
                <th style="text-align:center;">Prod Plan (UMH)</th>
                <th style="text-align:center;">Prod Plan (UMH)</th>
            </tr>';

            $no = 1;
            $total_month_1 = 0;
            $total_persentase_month_1 = 0;
            $total_month_2 = 0;
            $total_month_3 = 0;
            $total_month_4 = 0;
            $total_month_5 = 0;
            $total_month_6 = 0;
            $total_average = 0;
            foreach ($records as $record) {
                $total_umh = $record['total_umh'];
                if ($record['status'] == "1") {
                    $background = "style='background:#FFCFCF'";
                } else {
                    $background = "style='background:#DBFFCF'";
                }
                $html .= '  <tr ' . $background . '>
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $record['customer_name'] . '</td>
                                <td style="text-align:right;">' . number_format($record['month_1']) . '</td>
                                <td style="text-align:right;">' . number_format($record['month_2']) . '</td>
                                <td style="text-align:right;">' . number_format($record['month_3']) . '</td>
                                <td style="text-align:right;">' . number_format($record['month_4']) . '</td>
                                <td style="text-align:right;">' . number_format($record['month_5']) . '</td>
                                <td style="text-align:right;">' . number_format($record['month_6']) . '</td>
                                <td style="text-align:right;">' . number_format(round($record['total_umh'] / 6)) . '</td>
                            </tr>';

                $total_month_1 += $record['month_1'];
                //$total_persentase_month_1 += @(($record['month_1'] / $total_umh) * 100);
                $total_month_2 += $record['month_2'];
                $total_month_3 += $record['month_3'];
                $total_month_4 += $record['month_4'];
                $total_month_5 += $record['month_5'];
                $total_month_6 += $record['month_6'];
                $total_average += ($record['total_umh'] / 6);
                $no++;
            }

            if ($filter_overtime != "0") {
                $overtime = ((($machine->total * $hkw_1) * $filter_shift) * $filter_overtime);
            } else {
                $overtime = 0;
            }

            $average_capacity = ((((($machine->total * $hkw_1 * 8) * $filter_shift) * 0.8) +
                ((($machine->total * $hkw_2 * 8) * $filter_shift) * 0.8) +
                ((($machine->total * $hkw_3 * 8) * $filter_shift) * 0.8) +
                ((($machine->total * $hkw_4 * 8) * $filter_shift) * 0.8) +
                ((($machine->total * $hkw_5 * 8) * $filter_shift) * 0.8) +
                ((($machine->total * $hkw_6 * 8) * $filter_shift) * 0.8)) / 6);

            $average_overtime = ((((($machine->total * $hkw_1) * $filter_shift) * $filter_overtime) +
                ((($machine->total * $hkw_2) * $filter_shift) * $filter_overtime) +
                ((($machine->total * $hkw_3) * $filter_shift) * $filter_overtime) +
                ((($machine->total * $hkw_4) * $filter_shift) * $filter_overtime) +
                ((($machine->total * $hkw_5) * $filter_shift) * $filter_overtime) +
                ((($machine->total * $hkw_6) * $filter_shift) * $filter_overtime)) / 6);

            $average_total_capacty = (((($machine->total * $hkw_1 * $filter_shift * 8 * 0.8) + ($machine->total * $hkw_1 * $filter_shift + $overtime * 0.8)) + (($machine->total * $hkw_2 * $filter_shift * 8 * 0.8) + ($machine->total * $hkw_2 * $filter_shift + $overtime * 0.8)) +
                (($machine->total * $hkw_3 * $filter_shift * 8 * 0.8) + ($machine->total * $hkw_3 * $filter_shift + $overtime * 0.8)) +
                (($machine->total * $hkw_4 * $filter_shift * 8 * 0.8) + ($machine->total * $hkw_4 * $filter_shift + $overtime * 0.8)) +
                (($machine->total * $hkw_5 * $filter_shift * 8 * 0.8) + ($machine->total * $hkw_5 * $filter_shift + $overtime * 0.8)) +
                (($machine->total * $hkw_6 * $filter_shift * 8 * 0.8) + ($machine->total * $hkw_6 * $filter_shift + $overtime * 0.8))) / 6);

            $average_balance = ((((((($machine->total * $hkw_1 * 8) * $filter_shift) * 0.8) + ((($machine->total * $hkw_1 * 8) * $filter_shift) * 0.8)) - $total_month_1) +
                ((((($machine->total * $hkw_2 * 8) * $filter_shift) * 0.8) + (($machine->total * $hkw_2 * $filter_shift + $overtime) * 0.8)) - $total_month_2) +
                ((((($machine->total * $hkw_3 * 8) * $filter_shift) * 0.8) + (($machine->total * $hkw_3 * $filter_shift + $overtime) * 0.8)) - $total_month_3) +
                ((((($machine->total * $hkw_4 * 8) * $filter_shift) * 0.8) + (($machine->total * $hkw_4 * $filter_shift + $overtime) * 0.8)) - $total_month_4) +
                ((((($machine->total * $hkw_5 * 8) * $filter_shift) * 0.8) + (($machine->total * $hkw_5 * $filter_shift + $overtime) * 0.8)) - $total_month_5) +
                ((((($machine->total * $hkw_6 * 8) * $filter_shift) * 0.8) + (($machine->total * $hkw_6 * $filter_shift + $overtime) * 0.8)) - $total_month_6)) / 6);

            $html .= '  <tr>
                            <td colspan="2"><b>TOTAL LOADING</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_month_1) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_month_2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_month_3) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_month_4) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_month_5) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_month_6) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_average) . '</b></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>CAPACITY (' . $filter_shift . ' SHIFT)</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_1 * 8) * $filter_shift) * 0.8) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_2 * 8) * $filter_shift) * 0.8) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_3 * 8) * $filter_shift) * 0.8) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_4 * 8) * $filter_shift) * 0.8) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_5 * 8) * $filter_shift) * 0.8) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_6 * 8) * $filter_shift) * 0.8) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($average_capacity) . '</b></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>OVERTIME (' . $filter_overtime . ' HOUR)</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_1) * $filter_shift) * $filter_overtime) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_2) * $filter_shift) * $filter_overtime) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_3) * $filter_shift) * $filter_overtime) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_4) * $filter_shift) * $filter_overtime) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_5) * $filter_shift) * $filter_overtime) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_6) * $filter_shift) * $filter_overtime) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($average_overtime) . '</b></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>TOTAL CAPACITY</b></td>
                            <td style="text-align:right;"><b>' . number_format(($machine->total * $hkw_1 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_1) * $filter_shift) * $filter_overtime)) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(($machine->total * $hkw_2 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_2) * $filter_shift) * $filter_overtime)) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(($machine->total * $hkw_3 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_3) * $filter_shift) * $filter_overtime)) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(($machine->total * $hkw_4 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_4) * $filter_shift) * $filter_overtime)) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(($machine->total * $hkw_5 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_5) * $filter_shift) * $filter_overtime)) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(($machine->total * $hkw_6 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_6) * $filter_shift) * $filter_overtime)) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($average_total_capacty) . '</b></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>BALANCE</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_1 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_1) * $filter_shift) * $filter_overtime)) - $total_month_1) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_2 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_2) * $filter_shift) * $filter_overtime)) - $total_month_2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_3 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_3) * $filter_shift) * $filter_overtime)) - $total_month_3) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_4 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_4) * $filter_shift) * $filter_overtime)) - $total_month_4) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_5 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_5) * $filter_shift) * $filter_overtime)) - $total_month_5) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format((($machine->total * $hkw_6 * $filter_shift * 8 * 0.8) + ((($machine->total * $hkw_6) * $filter_shift) * $filter_overtime)) - $total_month_6) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($average_balance) . '</b></td>
                        </tr>';

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
