<?php
error_reporting(0);
defined('BASEPATH') or exit('No direct script access allowed');

class Umh extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        // $this->load->model('banshu');

        //Validasi Form
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[2]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            // Process Main Number
            $this->db->select('COUNT(a.number) as total, a.number, a.name');
            $this->db->from('process_main a');
            $this->db->join('process_main_sub b', 'a.id = b.process_main_id');
            // $this->db->join('process c', 'b.id = c.process_sub_id');
            // $this->db->where('a.name', 'PP');
            $this->db->group_by('a.number');
            $this->db->order_by('b.flag', 'asc');
            $processMainNumber = $this->db->get()->result_array();

            //Process Sub Number
            $this->db->select('COUNT(b.number) as total, b.number, b.name');
            $this->db->from('process_main a');
            $this->db->join('process_main_sub b', 'a.id = b.process_main_id');
            // $this->db->join('process c', 'b.id = c.process_sub_id');
            // $this->db->where('a.name', 'PP');
            $this->db->group_by('b.number');
            $this->db->order_by('b.flag', 'asc');
            $processSubNumber = $this->db->get()->result_array();

            //Process Main Number
            // $this->db->select('c.number, c.name');
            // $this->db->from('process_main a');
            // $this->db->join('process_main_sub b', 'a.id = b.process_main_id');
            // $this->db->join('process c', 'b.id = c.process_sub_id');
            // $this->db->where('a.name', 'PP');
            // $this->db->group_by('c.number');
            // $this->db->order_by('b.flag', 'asc');
            // $processNumber = $this->db->get()->result_array();

            $no = 1;
            foreach ($processSubNumber as $process) {
                $arrProcess[] = array(
                    "number" => $process['number'],
                    "name" => $process['name']
                );
                $no++;
            }

            $data['mainProcess'] = @$processMainNumber;
            $data['subProcess'] = !empty($arrProcess) ? $arrProcess : [];
            // $data['process'] = !empty($arrProcess) ? $arrProcess : [];

            $this->load->view('template/header', $data);
            $this->load->view('planning/umh');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->crud->reads('umh', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function datatables()
    {
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $filter_item_fg_id = trim(base64_decode($this->input->get('filter_item_fg_id')));
        $filter_customer_id = trim(base64_decode($this->input->get('filter_customer_id')));

        if ($id == 0) {
            //Select Query
            $query = $this->db->query("SELECT a.item_fg_id, b.number as item_fg_number, b.name as item_fg_name, b.number_customer, SUM(a.total_time) as total_umh, COUNT(a.circuit_no) as circuit_no
            FROM (select *, SUM(cycle_time) as total_time from umh group by item_fg_id, circuit_no) a 
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customer_items c ON b.id = c.item_fg_id
            WHERE a.item_fg_id LIKE '%$filter_item_fg_id%' and c.customer_id LIKE '%$filter_customer_id%'
            GROUP BY a.item_fg_id
            ORDER BY b.number ASC");
            $records = $query->result_array();

            foreach ($records as $record) {
                $item_fg_id = $record['item_fg_id'];
                $arr = array(
                    "id" => $item_fg_id,
                    "state" => "closed",
                    "item_fg_number" => $record['item_fg_number'],
                    "number_customer" => $record['number_customer'],
                    "item_fg_name" => $record['item_fg_name'],
                    "circuit_no" => $record['circuit_no'],
                    "total" => $record['total_umh'],
                    "total_umh" => round($record['total_umh'] / 3600, 6),
                );

                //Process Number
                $sql2 = $this->db->query("SELECT process_sub_number, SUM(cycle_time) as cycle_time FROM umh WHERE item_fg_id = '$item_fg_id' GROUP BY process_sub_number ORDER BY process_sub_number asc");
                $processNumber = $sql2->result_array();

                $arrProcess = array();
                foreach ($processNumber as $process) {
                    $arrProcess += array(
                        $process['process_sub_number'] => $process['cycle_time']
                    );
                }
                $arrFinal[] = array_merge($arr, $arrProcess);
            }
        } else {
            //Select Query
            $sql = $this->db->query("SELECT a.id, a.item_fg_id, b.number as item_fg_number, b.name as item_fg_name, b.number_customer, a.circuit_no, SUM(a.cycle_time) as total_umh 
                FROM umh a JOIN item_fg b ON a.item_fg_id = b.id WHERE a.item_fg_id = '$id' 
                GROUP BY a.circuit_no
                ORDER BY id ASC");
            $records = $sql->result_array();

            foreach ($records as $record) {
                $item_fg_id = $record['item_fg_id'];
                $circuit_no = $record['circuit_no'];

                $arr = array(
                    "id" => $record['item_fg_id']."_".$record['circuit_no'],
                    "item_fg_id" => $record['item_fg_id'],
                    "item_fg_number" => $record['item_fg_number'],
                    "number_customer" => $record['number_customer'],
                    "item_fg_name" => $record['item_fg_name'],
                    "circuit_no" => $record['circuit_no'],
                    "total" => $record['total_umh'],
                    "total_umh" => round($record['total_umh'] / 3600, 6),
                );

                $sql2 = $this->db->query("SELECT process_sub_number, cycle_time FROM umh WHERE item_fg_id = '$item_fg_id' and circuit_no = '$circuit_no' ORDER BY process_sub_number asc");
                $processNumber = $sql2->result_array();

                $arrProcess = array();
                foreach ($processNumber as $process) {
                    $arrProcess += array(
                        $process['process_sub_number'] => $process['cycle_time']
                    );
                }

                $arrFinal[] = array_merge($arr, $arrProcess);
            }
        }

        //Mapping Data
        // $result['total'] = $totalRows;
        $result = !empty($arrFinal) ? $arrFinal : [];
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                $umh = $this->crud->reads('umh', [], [
                    "item_fg_id" => $post['item_fg_id'],
                    "circuit_no" => $post['circuit_no'],
                    "process_main_number" => $post['process_main_number'],
                    "process_sub_number" => $post['process_sub_number']
                ]);

                if (count($umh) > 0) {
                    echo json_encode(array("title" => "Available", "message" => "Unit Man Hour is duplicate", "theme" => "error"));
                } else {
                    $send = $this->crud->create('umh', $post);
                    echo $send;
                }
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
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));
            $circuit_no = $this->input->get('circuit_no');

            $post = $this->input->post();
            $send = $this->crud->update('umh', [
                "item_fg_id" => $item_fg_id,
                "circuit_no" => $circuit_no,
                "process_main_number" => $post['process_main_number'],
                "process_sub_number" => $post['process_sub_number']
            ], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('umh', $data);
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

        $this->db->select('a.number as process_main_number, b.number as process_sub_number');
        $this->db->from('process_main a');
        $this->db->join('process_main_sub b', 'a.id = b.process_main_id');
        // $this->db->where('a.name', 'PP');
        $this->db->group_by('c.number');
        $this->db->order_by('b.flag', 'asc');
        $processNumber = $this->db->get()->result_array();

        for ($i = 5; $i <= $total_row; $i++) {
            $datas = array(
                'item_fg_number' => $data->val($i, 2),
                'circuit_no' => $data->val($i, 3)
            );

            $arrProcess = array();
            $no = 0;
            foreach ($processNumber as $process) {
                $arrProcess = array(
                    "process_main_number" => $process['process_main_number'],
                    "process_sub_number" => $process['process_sub_number'],
                    "cycle_time" => $data->val($i, 4 + $no)
                );
                $no++;

                $arrFinal[] = array_merge($datas, $arrProcess);
            }
        }

        $arrFinal['total'] = count($arrFinal);
        echo json_encode($arrFinal);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);
            $process_main_sub = $this->crud->read('process_main_sub', [], ["number" => $data['process_sub_number']]);
            
            $umh = $this->crud->reads('umh', [], [
                "item_fg_id" => @$item_fg->id,
                "circuit_no" => $data['circuit_no'],
                "process_main_number" => $data['process_main_number'],
                "process_sub_number" => $data['process_sub_number']
            ]);

            $dataFinal = array(
                "item_fg_id" => @$item_fg->id,
                "circuit_no" => $data['circuit_no'],
                "process_main_number" => $data['process_main_number'],
                "process_sub_number" => $data['process_sub_number'],
                "cycle_time" => $data['cycle_time'],
            );

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_fg_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($process_main_sub->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Code " . $data['process_sub_number'] . " Not Found", "theme" => "error"));
            } elseif (count($umh) > 0) {
                $delete = $this->crud->delete('umh', [
                    "item_fg_id" => @$item_fg->id,
                    "circuit_no" => $data['circuit_no'],
                    "process_main_number" => $data['process_main_number'],
                    "process_sub_number" => $data['process_sub_number']]);

                if($delete){
                    $send = $this->crud->create('umh', $dataFinal);
                    echo $send;
                }
            } else {
                $send = $this->crud->create('umh', $dataFinal);
                echo $send;
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/umh.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/umh.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/umh.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function downloadTemplate()
    {
        $format  = date("Ymd");
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=tmp_umh.xls");

        // Process Main Number
        $this->db->select('COUNT(a.number) as total, a.number, a.name');
        $this->db->from('process_main a');
        $this->db->join('process_main_sub b', 'a.id = b.process_main_id');
        // $this->db->join('process c', 'b.id = c.process_sub_id');
        // $this->db->where('a.name', 'PP');
        $this->db->group_by('a.number');
        $this->db->order_by('b.flag', 'asc');
        $processMainNumber = $this->db->get()->result_array();

        //Process Sub Number
        $this->db->select('COUNT(b.number) as total, b.number, b.name');
        $this->db->from('process_main a');
        $this->db->join('process_main_sub b', 'a.id = b.process_main_id');
        // $this->db->join('process c', 'b.id = c.process_sub_id');
        // $this->db->where('a.name', 'PP');
        $this->db->group_by('b.number');
        $this->db->order_by('b.flag', 'asc');
        $processSubNumber = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center style="margin:0;">
            <b style="font-size:20px;">TEMPLATE UPLOAD UNIT MAN HOUR (PP)</b>
        </center>
        <i>if an error occurs when uploading please SAVE AS, or save file new</i>
        <table id="customers" border="1">
            <tr>
                <th style="text-align:center;" rowspan="2" width="50">No</th>
                <th style="text-align:center;" rowspan="2"><b>Product No</b></th>
                <th style="text-align:center;" rowspan="2">Circuit No</th>';
        foreach ($processMainNumber as $main) {
            $html .= '<th style="text-align:center;" colspan="' . $main['total'] . '"> ' . $main['name'] . '</th>';
        }

        $html .= '</tr><tr>';

        foreach ($processSubNumber as $sub) {
            $html .= '<th style="text-align:center;"> ' . $sub['name'] . '</th>';
        }

        $html .= '</tr></table>';

        echo $html;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=umh_$format.xls");
        }

        $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));
        $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Process Main Number
        $this->db->select('COUNT(a.number) as total, a.number, a.name');
        $this->db->from('process_main a');
        $this->db->join('process_main_sub b', 'a.id = b.process_main_id');
        // $this->db->join('process c', 'b.id = c.process_sub_id');
        // $this->db->where('a.name', 'PP');
        $this->db->group_by('a.number');
        $this->db->order_by('b.flag', 'asc');
        $processMainNumber = $this->db->get()->result_array();

        //Process Sub Number
        $this->db->select('COUNT(b.number) as total, b.number, b.name');
        $this->db->from('process_main a');
        $this->db->join('process_main_sub b', 'a.id = b.process_main_id');
        // $this->db->join('process c', 'b.id = c.process_sub_id');
        // $this->db->where('a.name', 'PP');
        $this->db->group_by('b.number');
        $this->db->order_by('b.flag', 'asc');
        $processSubNumber = $this->db->get()->result_array();

        //Select Query
        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.number_customer, SUM(a.cycle_time) as total_umh');
        $this->db->from('umh a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customer_items c', 'b.id = c.item_fg_id');
        $this->db->like('a.item_fg_id', trim($filter_item_fg_id));
        $this->db->like('c.customer_id', trim($filter_customer_id));
        $this->db->order_by('b.number', 'asc');
        $this->db->group_by('b.number');
        $this->db->group_by('a.circuit_no');
        $this->db->order_by('b.number', 'asc');
        $this->db->order_by('a.id', 'asc');
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
                            <small>'.$config->description.'</small>
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
                <h3>UNIT MAN HOUR</h3>
            </div>
        </center>
    
    <table id="customers" border="1">
        <tr>
            <th rowspan="2" style="text-align:center;"width="20">No</th>
            <th rowspan="2" style="text-align:center;">Product No EBWS</th>
            <th rowspan="2" style="text-align:center;">Part No Customer</th>
            <th rowspan="2" style="text-align:center;">Description</th>
            <th rowspan="2" style="text-align:center;">Circuit No</th>';
        foreach ($processMainNumber as $main) {
            $html .= '<th style="text-align:center;" colspan="' . $main['total'] . '"> ' . $main['name'] . '</th>';
        }

        $html .= ' <th style="text-align:center;" rowspan="2">Total</th>
                   <th style="text-align:center;" rowspan="2">UMH</th>
            </tr><tr>';

        foreach ($processSubNumber as $sub) {
            $html .= '<th style="text-align:center;"> ' . $sub['name'] . '</th>';
        }

        $html .= '</tr>';

        $no = 1;
        $arrFinal = array();
        foreach ($records as $record) {
            $item_fg_id = $record['item_fg_id'];
            $circuit_no = $record['circuit_no'];

            $arr = array(
                "item_fg_number" => $record['item_fg_number'],
                "item_fg_name" => $record['item_fg_name'],
                "number_customer" => $record['number_customer'],
                "circuit_no" => $record['circuit_no'],
                "remarks" => $record['remarks'],
                "total_umh" => $record['total_umh'],
            );

            //Process Main Number
            $this->db->select('process_sub_number, SUM(cycle_time) as cycle_time');
            $this->db->from('umh');
            $this->db->where('item_fg_id', $item_fg_id);
            $this->db->where('circuit_no', $circuit_no);
            $this->db->group_by('process_sub_number');
            $this->db->order_by('process_sub_number', 'asc');
            $processNumber2 = $this->db->get()->result_array();

            $arrProcess = array();
            foreach ($processNumber2 as $process) {
                $arrProcess += array(
                    $process['process_sub_number'] => $process['cycle_time']
                );
            }
            $arrFinal[] = array_merge($arr, $arrProcess);
        }

        foreach ($arrFinal as $final) {
            $html .= '  <tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $final['item_fg_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $final['number_customer'] . '</td>
                            <td style="mso-number-format:\@;">' . $final['item_fg_name'] . '</td>
                            <td style="text-align:center;">' . $final['circuit_no'] . '</td>';
            foreach ($processSubNumber as $proses) {
                $number = $proses['number'];
                $html .= '<td style="text-align:center;"> ' . @$final[$number] . '</td>';
            }
            $html .= '  <td style="text-align:center;">' . $final['total_umh'] . '</td>
                        <td style="text-align:center;">' . number_format($final['total_umh'] / 3600, 5) . '</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
