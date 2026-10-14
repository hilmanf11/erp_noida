<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Assets extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('assets/assets');
        } else {
            redirect('error_access');
        }
    }

    public function autoid()
    {
        $post = $this->input->post();
        $item_family_number = $post['item_family_number'];

        $codes = $item_family_number."-".date("y");
        $sql = $this->db->query("SELECT max(number) as kode FROM assets WHERE number like '%$codes%'");
        $row = $sql->row();

        $sqla = $this->db->query("SELECT max(number) as kode FROM asset_fixeds WHERE number like '%$codes%'");
        $rowa = $sqla->row();
        
        if($rowa->kode == ""){
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = substr($row->kode, 5);
            }
        }else{
            $kode1 = substr($rowa->kode, 5);
            $kode2 = substr($row->kode, 5);

            if($kode1 >= $kode2){
                $kode = substr($rowa->kode, 5);
            }else{
                $kode = substr($row->kode, 5);
            }
        }

        $format = $codes . sprintf("%04s", $kode + 1);
        echo json_encode(array("code" => $format));
    }

    public function datatables()
    {
        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_item_familys = $this->input->get('filter_item_familys');

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('*, SUM(qty) as total');
            $this->db->from('assets');
            $this->db->like('item_family_id', $filter_item_familys);
            $this->db->like('item_rm_id', $filter_item_rm);
            $this->db->group_by('number');
            $this->db->order_by('created_by', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['number'],
                    "number" => $record['number'],
                    "name" => $record['name'],
                    "description" => $record['description'],
                    "uom" => $record['uom'],
                    "item_family_id" => $record['item_family_id'],
                    "qty" => $record['total'],
                    "status" => $record['status'],
                    "state" => "closed",
                    "datatable" => 1
                );
            }
            
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.id, b.id as number, b.number as name, b.name as description, b.uom, a.qty, a.created_by, a.created_date, a.updated_by, a.updated_date, a.status');
            $this->db->from('assets a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.number', $id);
            $this->db->like('a.item_family_id', $filter_item_familys);
            $this->db->like('a.item_rm_id', $filter_item_rm);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function datatable_updates(){
        $number = base64_decode($this->input->get('number'));
        
        $records = $this->crud->query("SELECT a.*, c.number as item_rm_number, c.name as item_rm_name
            FROM assets a
            JOIN item_rm c on a.item_rm_id = c.id
            WHERE a.number = '$number'
            GROUP BY c.number");
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                $assets = $this->crud->read('assets', [], ["item_rm_id" => $post['item_rm_id'], "number" => $post['number']]);
                if (empty($assets)) {
                    $send = $this->crud->create('assets', $post);
                    echo $send;
                }else{
                    $send = $this->crud->update('assets', ["number" => $post['number'], "item_rm_id" => $post['item_rm_id']], $post);
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
            $id   = $this->input->post('id');
            $post = $this->input->post();
            $send = $this->crud->update('assets', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('assets', $data);
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
        $category = $data->val(2, 3);
        
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_family_id' => $data->val($i, 2),
                'item_rm_id' => $data->val($i, 3),
                'number' => $data->val($i, 4),
                'name' => $data->val($i, 5),
                'description' => $data->val($i, 6)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/assets.txt');
    }
    
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/assets.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/assets.txt";
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
            $data = $this->input->post('data');
            //Cek Process Number
            $item = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);
            $assets = $this->crud->read('assets', [], ["number" => $data['number'], "item_rm_id" => $data['item_rm_id']]);

            if (empty($item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($assets->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part No " . $data['item_rm_id'] . " Duplicate Data", "theme" => "error"));
            } else {
                $send = $this->crud->create('assets', $data);
                echo $send;
            }
        }
    }
    
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=assets_$format.xls");
        }

        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_item_familys = $this->input->get('filter_item_familys');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name');
        $this->db->from('assets a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->like('a.item_family_id', $filter_item_familys);
        $this->db->like('a.item_rm_id', $filter_item_rm);
        $this->db->order_by('a.number', 'DESC');
        $records = $this->db->get()->result_array();
        
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' .  $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>MASTER ASSET</small>
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
                <th>Asset Code</th>
                <th>Asset Name</th>
                <th>Description</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Description</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['description'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_rm_number'] . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
