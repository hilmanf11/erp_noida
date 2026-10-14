<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Item_process extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Proces Code', 'required|min_length[1]|max_length[30]|is_unique[process.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/item_process');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads($process_sub_id)
    {
        $process_sub = $this->crud->reads("process", [], ["process_sub_id" => $process_sub_id]);
        die(json_encode($process_sub));
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
                b.number as process_main_number, 
                b.name as process_main_name,
                c.number as process_sub_number,
                c.name as process_sub_name');
            $this->db->from('process a');
            $this->db->join('process_main b', 'a.process_main_id = b.id');
            $this->db->join('process_main_sub c', 'a.process_sub_id = c.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "process_main_name"){
                        $this->db->like("b.name", $filter->value);
                    }elseif($filter->field == "process_sub_name"){
                        $this->db->like("c.name", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.flag', 'asc');
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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('process', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('process', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('process', $data);
        echo $send;
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
                'process_main_number' => $data->val($i, 2),
                'process_sub_number' => $data->val($i, 3),
                'number' => $data->val($i, 4),
                'name' => $data->val($i, 5),
                'efficiency' => $data->val($i, 6),
                'mp' => $data->val($i, 7),
                'index' => $data->val($i, 8),
                'description' => $data->val($i, 9)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/process.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/process.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/process.txt";
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
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            $process_main = $this->crud->read('process_main', [], ["number" => $data['process_main_number']]);
            $process_main_sub = $this->crud->read('process_main_sub', [], ["process_main_id" => @$process_main->id, "number" => $data['process_sub_number']]);
            $process = $this->crud->read('process', [], ["number" => $data['number']]);

            if (empty($process_main->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Main Code " . $data['process_main_number'] . " is Not Found", "theme" => "error"));
            }elseif(empty($process_main_sub->id)){
                echo json_encode(array("title" => "Not Found", "message" => "Process Sub Code " . $data['process_sub_number'] . " is Not Found", "theme" => "error"));
            }elseif(!empty($process->number)){
                echo json_encode(array("title" => "Duplicate", "message" => "Process Code " . $data['number'] . " is Duplicate", "theme" => "error"));
            } else {
                $dataFinal = array(
                    "process_main_id" => $process_main->id,
                    "process_sub_id" => $process_main_sub->id,
                    "number" => $data['number'],
                    "name" => $data['name'],
                    "efficiency" => $data['efficiency'],
                    "mp" => $data['mp'],
                    "flag" => $data['index'],
                    "description" => $data['description'],
                );

                $send = $this->crud->create('process', $dataFinal);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=process_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
            b.number as process_main_number, 
            b.name as process_main_name,
            c.number as process_sub_number,
            c.name as process_sub_name');
        $this->db->from('process a');
        $this->db->join('process_main b', 'a.process_main_id = b.id');
        $this->db->join('process_main_sub c', 'a.process_sub_id = c.id');
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
                <h3>MASTER PROCESS</h3>
            </div>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Process Main</th>
                <th>Process Main Sub</th>
                <th>Process Code</th>
                <th>Process Name</th>
                <th>Efficiency</th>
                <th>Man Power</th>
                <th>Index</th>
                <th>Description</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['process_main_name'] . '</td>
                    <td>' . $data['process_sub_name'] . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['efficiency'] . '</td>
                    <td>' . $data['mp'] . '</td>
                    <td>' . $data['flag'] . '</td>
                    <td>' . $data['description'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
