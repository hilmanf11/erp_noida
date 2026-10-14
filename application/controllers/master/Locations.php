<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Locations extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[20]|is_unique[warehouse_locations.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/locations');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads($type)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('warehouse_locations', ["location" => $post], ["type" => $type], "", "number", "asc");
        echo json_encode($send);
    }

    public function readNotRm($type)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as product_family_name
            FROM warehouse_locations a 
            LEFT JOIN item_familys b ON a.item_family_id = b.id
            WHERE a.type = '$type' and b.item_category_id != 'C01' and (a.number like '%$post%' or a.location like '%$post%' or a.area like '%$post%' or a.rack like '%$post%') 
            GROUP BY a.number
            ORDER BY a.number ASC");
        echo json_encode($send);
    }

    public function readLocations()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $type = $this->input->get('type');
        
        $send = $this->crud->query("SELECT a.*, COALESCE(SUM(b.qty), 0) as qty_receipt, (a.capacity - COALESCE(SUM(b.qty), 0)) as available
            FROM warehouse_locations a 
            LEFT JOIN scan_item_receipts b ON a.number = b.warehouse and b.status = 0
            -- LEFT JOIN scan_item_receipts c ON a.number = c.warehouse and b.status = 1
            WHERE a.type = '$type' and (a.number like '%$post%' or a.location like '%$post%' or a.area like '%$post%' or a.rack like '%$post%') 
            GROUP BY a.number 
            -- HAVING (a.capacity - COALESCE(SUM(b.qty), 0) + COALESCE(SUM(c.qty), 0)) > 0
            ORDER BY a.number ASC");
        echo json_encode($send);
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
            $this->db->select('a.*, e.name as item_family_name, round(COALESCE(SUM(b.qty_receipt), 0) - COALESCE(SUM(d.qty), 0)) as filled, round(a.capacity - COALESCE(SUM(b.qty_receipt), 0) + COALESCE(SUM(d.qty), 0)) as available');
            $this->db->from('warehouse_locations a');
            $this->db->join('purchase_order_receipts b', 'a.number = b.location', 'left');
            $this->db->join('purchase_order_labels c', 'b.receipt_id = c.receipt_id', 'left');
            $this->db->join('issued_material_details d', 'c.label_no = d.label_no and b.item_rm_id = d.item_rm_id', 'left');
            $this->db->join('item_familys e', 'a.item_family_id = e.id', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_family_name"){
                        $this->db->like("e.name", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->group_by('a.id');
            $this->db->order_by('a.number', 'ASC');
            $this->db->order_by('a.location', 'ASC');
            $this->db->order_by('a.area', 'ASC');
            $this->db->order_by('a.rack', 'ASC');
            $this->db->order_by('a.level', 'ASC');
            $this->db->order_by('a.level_sub', 'ASC');
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
                $send   = $this->crud->create('warehouse_locations', $post);
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
            $send = $this->crud->update('warehouse_locations', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('warehouse_locations', $data);
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
                'item_family_number' => $data->val($i, 2),
                'number' => $data->val($i, 3),
                'location' => $data->val($i, 4),
                'area' => $data->val($i, 5),
                'rack' => $data->val($i, 6),
                'level' => $data->val($i, 7),
                'level_sub' => $data->val($i, 8),
                'capacity' => $data->val($i, 9),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/warehouse_locations.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/warehouse_locations.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/warehouse_locations.txt";
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
            $item_family = $this->crud->read('item_familys', [], ["number" => $data['item_family_number']]);
            $warehouse_locations = $this->crud->read('warehouse_locations', [], ["number" => $data['number'], "item_family_id" => @$item_family->id]);

            if (!empty($warehouse_locations)) {
                echo json_encode(array("title" => "Duplicate", "message" => "Code " . $data['number'] . " Duplicate", "theme" => "error"));
            }elseif(empty($item_family)){
                echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $data['item_family_number'] . " is Not Found", "theme" => "error"));
            } else {
                if(@$item_family->number != "FG"){
                    $type = "RM";
                }else{
                    $type = "FG";
                }
                
                $send   = $this->crud->create('warehouse_locations', [
                    "type" => $type,
                    "number" => $data['number'],
                    "location" => $data['location'],
                    "area" => $data['area'],
                    "rack" => $data['rack'],
                    "level" => $data['level'],
                    "level_sub" => $data['level_sub'],
                    "capacity" => $data['capacity'],
                    "item_family_id" => $item_family->id,
                ]);
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
            header("Content-Disposition: attachment; filename=warehouse_locations_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('*');
        
        $this->db->select('a.*, e.name as item_family_name, round(COALESCE(SUM(b.qty_receipt), 0) - COALESCE(SUM(d.qty), 0)) as filled, round(a.capacity - COALESCE(SUM(b.qty_receipt), 0) + COALESCE(SUM(d.qty), 0)) as available');
        $this->db->from('warehouse_locations a');
        $this->db->join('purchase_order_receipts b', 'a.number = b.location', 'left');
        $this->db->join('purchase_order_labels c', 'b.receipt_id = c.receipt_id', 'left');
        $this->db->join('issued_material_details d', 'c.label_no = d.label_no and b.item_rm_id = d.item_rm_id', 'left');
        $this->db->join('item_familys e', 'a.item_family_id = e.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->group_by('a.id');
        $this->db->order_by('a.number', 'ASC');
        $this->db->order_by('a.location', 'ASC');
        $this->db->order_by('a.area', 'ASC');
        $this->db->order_by('a.rack', 'ASC');
        $this->db->order_by('a.level', 'ASC');
        $this->db->order_by('a.level_sub', 'ASC');
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
                            <small>WAREHOUSE RM LOCATION</small>
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
                <th>Code</th>
                <th>Product Family</th>
                <th>Location</th>
                <th>Area</th>
                <th>Rack</th>
                <th>Level</th>
                <th>Level Sub</th>
                <th>Capacity</th>
                <th>Filled</th>
                <th>Balance</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['location'] . '</td>
                    <td>' . $data['area'] . '</td>
                    <td>' . $data['rack'] . '</td>
                    <td>' . $data['level'] . '</td>
                    <td>' . $data['level_sub'] . '</td>
                    <td>' . $data['capacity'] . '</td>
                    <td>' . $data['filled'] . '</td>
                    <td>' . $data['available'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
