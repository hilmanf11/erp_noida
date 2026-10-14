<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Os_rm extends CI_Controller
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

        //VALIDASI FORM
        // $this->form_validation->set_rules('item_rm_id', 'Item RM', 'required|min_length[1]|max_length[30]|is_unique[os_rm.item_rm_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/os_rm');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('os_rm', ["item_rm_id" => $post]);
        echo json_encode($send);
    }

    public function readMpq($id, $type){
        $id = base64_decode($id);

        $records = $this->crud->query("SELECT a.mpq, c.name as supplier_name 
            from item_mpq a 
            join os_rm b on a.item_rm_id = b.item_rm_id 
            join suppliers c on a.supplier_id = c.id 
            where b.id = '$id'
            GROUP BY a.mpq");
            
        if($type == "rows"){
            echo json_encode($records);
        }else{
            echo json_encode(array("total" => count($records)));
        }
        
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_category = @base64_decode($get['filter_category']);
            $filter_product_family = @base64_decode($get['filter_product_family']);
            $filter_product_family_sub = @base64_decode($get['filter_product_family_sub']);
            $filter_item_rm = @base64_decode($get['filter_item_rm']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, e.mpq, c.name as category_name, d.name as product_family_name');
            $this->db->from('os_rm a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_categories c', 'b.item_category_id = c.id');
            $this->db->join('item_familys d', 'b.item_family_id = d.id');
            $this->db->join('item_mpq e', 'a.item_rm_id = e.item_rm_id', 'left');
            $this->db->where('a.type', "BEGIN");
            if($filter_from != "" && $filter_to != ""){
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            if($filter_category != ""){
                $this->db->like('c.id', $filter_category);
            }
            if($filter_product_family != ""){
                $this->db->like('d.id', $filter_product_family);
            }
            if($filter_item_rm != ""){
                $this->db->like('b.id', $filter_item_rm);
            }
            $this->db->group_by('a.id');
            $this->db->order_by('b.number', 'ASC');

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
            $post = $this->input->post();

            $whereDate = date("ymd", strtotime($post['trans_date']));
            $document_no = "STO-RM-".$whereDate."-1";

            $os_rm = $this->crud->read('os_rm', [], ["item_rm_id" => $post['item_rm_id'], "document_no" => $document_no]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $post['item_rm_id']]);
            $location = $this->crud->query("SELECT `number` from warehouse_locations where item_family_id = '$item_rm->item_family_id' and `location` = '$post[item_rm_id]' limit 1");

            if(empty($os_rm->id)){
                $send = $this->crud->create('os_rm', array_merge($post, array("document_no" => $document_no, "location" => @$location[0]->number)));
                echo $send;
            }else{
                echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Data for Product No and Document No", "theme" => "error"));
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
            $send = $this->crud->update('os_rm', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('os_rm', $data);
        $send = $this->crud->delete('scan_item_receipts', ["receipt_id" => $data['id']]);
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
                'item_rm_id' => $data->val($i, 2),
                'qty' => $data->val($i, 3),
                'trans_date' => $data->val($i, 4),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/os_rm.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/os_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/os_rm.txt";
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

            $whereDate = date("ymd", strtotime($data['trans_date']));
            $document_no = "STO-RM-".$whereDate."-1";

            $os_rm = $this->crud->read('os_rm', [], ["item_rm_id" => $data['item_rm_id'], "document_no" => $document_no]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);

            if (!empty($os_rm->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part No EBWS " . $data['item_rm_id'] . " is Already Exist", "theme" => "error"));
            }elseif(empty($item_rm->id)){
                echo json_encode(array("title" => "Not Found", "message" => "Part No EBWS " . $data['item_rm_id'] . " is Not Found", "theme" => "error"));
            } else {
                $location = $this->crud->query("SELECT `number` from warehouse_locations where item_family_id = '$item_rm->item_family_id' and `location` = '$data[item_rm_id]' limit 1");

                if(empty($location->number)){
                    $dataFinal = array(
                        "item_rm_id" => $data['item_rm_id'],
                        "trans_date" => $data['trans_date'],
                        "location" => @$location[0]->number,
                        "document_no" => $document_no,
                        "qty" => $data['qty']
                    );
    
                    $send = $this->crud->create('os_rm', $dataFinal);
                    echo $send;
                }else{
                    echo json_encode(array("title" => "Not Found", "message" => "Part No EBWS " . $data['item_rm_id'] . " Location Not Ready", "theme" => "error"));
                }
            }
        }
    }

    public function getLabel(){
        $post = $this->input->post();
        $id = $post['id'];
        $mpq = $post['mpq'];
        
        $po_receipt_label = $this->crud->reads('scan_item_receipts', [], ["receipt_id" => $id]);

        $os_rm = $this->crud->read('os_rm', [], ["id" => $id]);
        $qty_receipt = $os_rm->qty;
        $qty_label = ceil($qty_receipt / $mpq);

        $arrLabel = array();
        if (!$po_receipt_label) {
            for ($i = 0; $i < $qty_label; $i++) {
                if ($qty_receipt > $mpq) {
                    $qty = $mpq;
                } else {
                    $qty = $qty_receipt;
                }

                //Simpan Label
                $arrLabel[] = [
                    "receipt_id" => $id,
                    "qty" => $qty,
                    "warehouse" => $os_rm->location
                ];

                $qty_receipt = ($qty_receipt - $mpq);
            }
        }

        $this->db->update("os_rm", ["created_label" => date("Y-m-d H:i:s")], ["id" => $id]);
        $arrLabel['total'] = count($arrLabel);
        die(json_encode($arrLabel));
    }

    public function testingCreate(){
        $id = "20241111000001";

        for ($i=0; $i < 61; $i++) { 
           //Read Label ID
            $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM scan_item_receipts WHERE receipt_id = '$id'");
            $rowID = $sqlGetID->row();
            $label = $rowID->kode;

            if ($label == NULL) {
                $autoID = "STOBR".$id . sprintf("%04s", $label + 1);
            } else {
                $urutan = (int) substr($label, -4);
                $autoID = "STOBR".$id . sprintf("%04s", $urutan + 1);
            }

            $send = $this->crud->create('scan_item_receipts', array("receipt_id" => $id, "label_no" => $autoID, "qty" => 100, "warehouse" => "W01"));
        
        }

        echo $send;
    }

    public function createLabel(){
        $post = $this->input->post('data');
        $id = $post['receipt_id'];

        //Read Label ID
        $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM scan_item_receipts WHERE receipt_id = '$id'");
        $rowID = $sqlGetID->row();
        $label = $rowID->kode;

        if ($label == NULL) {
            $autoID = "STORM".$id . sprintf("%04s", $label + 1);
        } else {
            $urutan = (int) substr($label, -4);
            $autoID = "STORM".$id . sprintf("%04s", $urutan + 1);
        }

        $send = $this->crud->create('scan_item_receipts', array_merge($post, array("label_no" => $autoID)));
        echo $send;
    }

    public function print_label($id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $id = base64_decode($id);
        
        $this->db->select('a.*, c.id as item_rm_id, b.trans_date, c.number, c.name, b.location, c.uom');
        $this->db->from('scan_item_receipts a');
        $this->db->join('os_rm b', 'a.receipt_id = b.id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.receipt_id', $id);
        $this->db->group_by('a.id');
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 44mm;">';
            $no = 1;
            foreach ($records as $record) {
                if ($no == 3) {
                    $no = 1;
                }
                if ($no == 1) {
                    $padding = "padding:2mm 3mm 3mm 2mm;";
                } else {
                    $padding = "padding:2mm 3mm 3mm 2mm;";
                }
                //Generate QRcode
                $this->createQrcode(strtoupper($record->label_no), "assets/image/qrcode/");
                $html .= '  <div style="width: 44mm; max-height:40mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>
                                        <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="height:35px;">
                                            <div style="float:left; width:70%;">
                                                <small style="font-size:7px;"><b>' . $record->number. '</b></small><br>
                                                <small style="font-size:12px;"><b>' . $record->item_rm_id . '</b></small>
                                                <br>
                                                <b style="font-size:7px;">STO</b>
                                            </div>
                                            
                                            <div style="float:right; width:30%;">
                                                <small style="font-size:15px;"><b>' . date("m", strtotime($record->trans_date)) . '</b></small><small style="font-size:10px;"><b>' ." - ". date("Y", strtotime($record->trans_date)) . '</b></small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left">
                                            <small>Quantity</small><br><b style="font-size:20px;">' . number_format($record->qty, 2) . '</b></small><br>
                                            <small style="font-size:15px; float: right;"><b>'. $record->uom . '</b></small>
                                        </th>
                                        <th style="text-align:left">
                                            <small>Location</small><br><b style="font-size:14px;">'.$record->location.'</b><br>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small>Date :</small><br> 
                                                <b style="font-size:8px;">' . $record->trans_date . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>Label No :</small><br>
                                                <b style="font-size:8px;">' . $record->label_no . '</b>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . strtoupper($record->label_no) . '.png') . '" width="50"/>
                                        </th>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=os_rm_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_category = @base64_decode($get['filter_category']);
        $filter_product_family = @base64_decode($get['filter_product_family']);
        $filter_product_family_sub = @base64_decode($get['filter_product_family_sub']);
        $filter_item_rm = @base64_decode($get['filter_item_rm']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, c.name as category_name, d.name as product_family_name');
        $this->db->from('os_rm a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_categories c', 'b.item_category_id = c.id');
        $this->db->join('item_familys d', 'b.item_family_id = d.id');
        $this->db->where('a.type', "BEGIN");
        if($filter_from != "" && $filter_to != ""){
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('c.id', $filter_category);
        $this->db->like('d.id', $filter_product_family);
        // $this->db->like('e.id', $filter_product_family_sub);
        $this->db->like('b.id', $filter_item_rm);
        $this->db->order_by('a.trans_date', 'DESC');
        $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#os_rm {border-collapse: collapse;width: 100%;font-size: 12px;}#os_rm td, #os_rm th {border: 1px solid #ddd;padding: 2px;}#os_rm tr:nth-child(even){background-color: #f2f2f2;}#os_rm tr:hover {background-color: #ddd;}#os_rm th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>DATA OUTSTANDING RAW MATERIAL</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left; width:30%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>Cut Off</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $filter_from . ' to ' . $filter_to . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            
            <table id="os_rm" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>Cut Off</th>
                    <th>Location</th>
                    <th>Qty</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                            <td>' . $data['category_name'] . '</td>
                            <td>' . $data['product_family_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['location'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
    }   
}
