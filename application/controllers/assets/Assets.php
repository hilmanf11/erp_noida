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
        $this->form_validation->set_rules('number', 'Asset No', 'required|min_length[1]|max_length[30]');
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

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('assets', ["number" => $post, "name" => $post], ["status" => 0]);
        echo json_encode($send);
    }

    public function readPr()
    {
        $sql = $this->db->query("SELECT a.request_no, 'OTHER' as type_pr
            FROM purchase_request_others a
            WHERE a.item_category_id = 'ASSET' and (a.approved_to = '' or a.approved_to is null)
            AND (a.asset_code = '' or a.asset_code is null or a.asset_code = '-')
            GROUP BY a.request_no 
            ORDER BY a.request_no");
        $datas = $sql->result_array();

        $sql2 = $this->db->query("SELECT a.request_no, 'SERVICE' as type_pr
            FROM purchase_request_services a 
            WHERE a.request_type = 'ASSET' AND a.request_no NOT IN (SELECT DISTINCT request_no FROM assets)
            GROUP BY a.request_no 
            ORDER BY a.request_no");
        $datas2 = $sql2->result_array();

        $additional[] = array("request_no" => "Additional", "type_pr" => "ADITIONAL");
        
        echo json_encode(array_merge($additional, $datas, $datas2));
    }

    public function readPrDetails()
    {
        $request_no = $this->input->get('request_no');
        $sql = $this->db->query("SELECT a.*, b.number as item_rm_number, b.name as item_rm_name, c.name as item_family_name, b.uom
            FROM purchase_request_others a
            JOIN item_rm b ON a.item_rm_id = b.id
            JOIN item_familys c ON b.item_family_id = c.id
            WHERE a.request_no = '$request_no' and (a.asset_code = '' or a.asset_code is null or a.asset_code = '-')
            ORDER BY a.request_no");
        $datas = $sql->result_array();
        echo json_encode($datas);
    }

    public function readPrDetailUpdate()
    {
        $request_no = $this->input->get('request_no');
        $sql = $this->db->query("SELECT a.*, b.number as item_rm_number, b.name as item_rm_name, c.name as item_family_name, b.uom
            FROM purchase_request_others a
            JOIN item_rm b ON a.item_rm_id = b.id
            JOIN item_familys c ON b.item_family_id = c.id
            WHERE a.request_no = '$request_no'
            ORDER BY a.request_no");
        $datas = $sql->result_array();
        echo json_encode($datas);
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
            $this->db->select('a.*, b.name as item_family_name');
            $this->db->from('assets a');
            $this->db->join('item_familys b', 'a.item_family_id = b.id');
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_family_name"){
                        $this->db->like("b.name", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.id', 'desc');
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

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $reads = $this->crud->reads("assets", [], ["number" => $post['number'], "request_no" => $post['request_no']]);

                if($post['request_no'] == "Additional" || strpos($post['request_no'], "PRS") !== false){
                    $send = $this->crud->create('assets', $post);
                    echo $send;
                }else{
                    if(count($reads) == 0){
                        $send = $this->crud->create('assets', $post);
                    }
    
                    $this->db->update('purchase_request_others', ["asset_code" => $post['number']], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);
                    echo json_encode(array("title" => "Good Job", "message" => "Asset Code Save Data", "theme" => "success"));
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
            $id = $this->input->post('id');
            $post = $this->input->post();
            $send = $this->crud->update('assets', ["request_no" => $post['request_no'], "number" => $post['number'], "item_rm_id" => $post['item_rm_id']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('assets', $data);
        $this->db->update('purchase_request_others', ["asset_code" => ""], ["request_no" => $data['request_no'], "asset_code" => $data['number']]);
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
                'item_rm_id' => $data->val($i, 2),
                'number' => $data->val($i, 3),
                'name' => $data->val($i, 4),
                'description' => $data->val($i, 5)
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
            $assets = $this->crud->read('assets', [], ["number" => $data['number']]);

            if (empty($item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($assets->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Asset Code " . $data['number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $send = $this->crud->create('assets', array_merge($data, array("item_family_id" => @$item->item_family_id)));
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

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $this->db->select('a.*, b.name as item_family_name');
        $this->db->from('assets a');
        $this->db->join('item_familys b', 'a.item_family_id = b.id');
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
                <th>Asset Category</th>
                <th>Asset Code</th>
                <th>Asset Name</th>
                <th>Uom</th>
                <th>Qty</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['status'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
