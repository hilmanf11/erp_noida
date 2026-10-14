<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_request_services extends CI_Controller
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
        // $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_request_services');
        } else {
            redirect('error_access');
        }
    }

    public function readPono($supplier_id){
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT DISTINCT po_no FROM purchase_request_services WHERE `status` = '0' and supplier_id = '$supplier_id' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readItem(){
        $records = $this->crud->query("SELECT DISTINCT item_name FROM purchase_request_services WHERE `status` = '0' GROUP BY item_name ORDER BY item_name desc LIMIT 1000");
        echo json_encode($records);
    }

    public function reads()
    {
        $request_no = base64_decode($this->input->get('request_no'));
        // $supplier_id = base64_decode($this->input->get('supplier_id'));

        //Select Query
        $this->db->select("a.*, (CASE WHEN a.request_type = 'ASSET' THEN b.number ELSE '-' END) as asset_code, b.name as asset_name");
        $this->db->from('purchase_request_services a');
        $this->db->join('assets b', 'a.request_no = b.request_no', 'left');
        $this->db->where('a.status', 0);
        $this->db->where("((CASE WHEN a.request_type = 'ASSET' THEN b.number ELSE '-' END) is not null)");
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.request_no', $request_no);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        
        echo json_encode($records);
    }

    public function readRequestno($status = 0)
    {  
         $records = $this->crud->query("SELECT a.request_no, a.request_date, a.request_name 
            FROM purchase_request_services a 
            LEFT JOIN assets b ON  a.request_no = b.request_no
            WHERE a.status = 0 and (a.approved_to = '' or a.approved_to is null) 
            AND (CASE WHEN a.request_type = 'ASSET' THEN b.number ELSE '-' END) is not null
            GROUP BY a.request_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function readRequestname(){
        $records = $this->crud->query("SELECT a.request_name 
            FROM purchase_request_services a
            GROUP BY a.request_name ORDER BY a.request_name asc");
        echo json_encode($records);
    }

    public function request_no($id_menu, $add = ""){
        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if(@$row_menu->number == ""){
            //
        }else{
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number.$yearnow.$yearend."1";

            $sqlGetID	= $this->db->query("SELECT max(request_no) as kode FROM purchase_request_services WHERE request_no like '%$whereDate%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;
            
            if ($kode == NULL) {
                $autoID = sprintf("%0".$row_menu->sequence."s", $kode + 1);
                $autoNo = $whereDate.$autoID;
            } else {
                $urutan = (int) substr($kode, -6);
                $urutan++;
                $autoID	= sprintf("%0".$row_menu->sequence."s", $urutan);
                $autoNo = $whereDate.$autoID;
            }

            if($add == ""){
                return $autoNo;
            }else{
                die(json_encode(array("code" => $autoNo)));
            }
        }
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_request_name = $this->input->get('filter_request_name');
        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_status_po = $this->input->get('filter_status_po');
        $filter_status_approval = $this->input->get('filter_status_approval');

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
            $this->db->select('a.request_no, a.request_date, a.expected_date, a.request_name, sum(a.qty) as qty, COUNT(a.id) as total_pr, a.status');
            $this->db->from('purchase_request_services a');
            $this->db->where('a.deleted', 0);
            if(!in_array($this->session->username, ["PURCHASE-03", "Purchase-01", "PURCHASE-02", "admin", "hariom_bhardwaj", "ITADMIN", "Accounts01"])){
                $this->db->where('a.created_by', $this->session->username);
            }
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.request_date >=', $filter_from);
                $this->db->where('a.request_date <=', $filter_to);
            }
            if($filter_request_no != ""){
                $this->db->where('a.request_no', $filter_request_no);
            }
            if($filter_request_name != ""){
                $this->db->like('a.request_name', $filter_request_name);
            }
            if($filter_status_approval != ""){
                if($filter_status_approval == "CHECKING"){
                    $this->db->where("(a.approved_to != '' and a.approved_to is not null)");
                }else{
                    $this->db->where("(a.approved_to = '' or a.approved_to is null)");
                }
            }
            $this->db->like('a.item_name', $filter_item_rm);
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_no', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $request_no = $record['request_no'];
                $purchase_order_other = $this->crud->query("SELECT a.request_no, a.po_no, COUNT(a.id) as total_po FROM purchase_order_services a WHERE a.request_no='$request_no' and a.item_name like '%$filter_item_rm%' GROUP BY a.request_no");

                if(@$purchase_order_other[0]->total_po == $record['total_pr']){
                    $status = 1;
                }else{
                    $status = 0;
                }

                if($filter_status_po != "" && $filter_status_po != $status){
                    //
                }else{
                    $arr[] = array(
                        "id" => $record['request_no'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "expected_date" => $record['expected_date'],
                        "request_name" => $record['request_name'],
                        "po_no" => @$purchase_order_other[0]->po_no,
                        "qty" => $record['qty'],
                        "status" => $status,
                        "state" => "closed",
                        "datatable" => 1
                    );
                }
            }
            
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select("a.*, d.po_no, b.name as item_family_name, c.name as item_category_name, e.number as asset_code, e.created_date as asset_date, (CASE WHEN c.type = 'ASSET' THEN 'YES' ELSE 'NO' END) as asset_type");
            $this->db->from('purchase_request_services a');
            $this->db->join('item_familys b', 'a.item_family_id = b.id', 'left');
            $this->db->join('item_categories c', "b.item_category_id = c.id");
            $this->db->join('purchase_order_services d', 'a.request_no = d.request_no', 'left');
            $this->db->join('assets e', 'a.request_no = e.request_no', 'left');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.request_date >=', $filter_from);
                $this->db->where('a.request_date <=', $filter_to);
            }
            if($filter_status_approval != ""){
                if($filter_status_approval == "CHECKING"){
                    $this->db->where("(a.approved_to != '' and a.approved_to is not null)");
                }else{
                    $this->db->where("(a.approved_to = '' or a.approved_to is null)");
                }
            }
            if($filter_status_po != ""){
                if($filter_status_approval == "0"){
                    $this->db->where("d.po_no is null");
                }else{
                    $this->db->where("d.po_no is not null");
                }
            }
            $this->db->where('a.request_no', $id);
            $this->db->like('a.item_name', $filter_item_rm);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function datatable_updates(){
        $request_no = base64_decode($this->input->get('request_no'));
        
        $records = $this->crud->query("SELECT * FROM purchase_request_services a
            WHERE a.status = '0' and a.request_no = '$request_no'");
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $p_month = date("m", strtotime($post['request_date']));
            $p_year = date("Y", strtotime($post['request_date']));
            
            $item_family = $this->crud->read("item_familys", [], ["id" => $post['item_family_id']]);
            $item_category = $this->crud->read("item_categories", [], ["id" => $item_family->item_category_id]);

            $purchase_request_item = $this->crud->read('purchase_request_services', [], ["request_no" => $post['request_no'], "item_name" => $post['item_name']]);
            
            if (@$purchase_request_item->id != "") {
                $send = $this->crud->update('purchase_request_services', ["request_no" => $post['request_no'], "item_name" => $post['item_name']], array_merge($post, ["request_type" => @$item_category->type]));
            } else {
                $send = $this->crud->create('purchase_request_services', array_merge($post, ["request_type" => @$item_category->type, "p_month" => $p_month, "p_year" => $p_year]));
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = $this->input->post('id');
            $post = $this->input->post();
            $send = $this->crud->update('purchase_request_services', ["id" => $id], ["qty" => $post['qty'], "remarks" => $post['remarks']]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_request_services', ["id" => $data['id']]);
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
                'request_no' => $data->val($i, 2),
                'request_date' => $data->val($i, 3),
                'request_name' => $this->session->name,
                'request_type' => "UPLOAD",
                'p_month' => date("m", strtotime($data->val($i, 3))),
                'p_year' => date("Y", strtotime($data->val($i, 3))),
                'item_name' => $data->val($i, 4),
                'qty' => $data->val($i, 5),
                'expected_date' => $data->val($i, 6),
                'remarks' => $data->val($i, 7)
            );
        }
        
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_request_services.txt');
    }
    
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_request_services.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_request_services.txt";
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
            $purchase_request_services = $this->crud->read('purchase_request_services', [], ["request_no" => $data['request_no'], "item_name" => $data['item_name']]);

            if (!empty($purchase_request_services->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Service Name " . $data['item_name'] . " Duplicate Data", "theme" => "error"));
            } else {
                $send = $this->crud->create('purchase_request_services', $data);
                echo $send;
            }
        }
    }

    public function print_request($request_no)
    {
        $request_no = base64_decode($request_no);
        $purchase_request_total = $this->crud->reads('purchase_request_services', [], ["request_no" => $request_no]);
        $purchase_requests = $this->crud->read('purchase_request_services', [], ["request_no" => $request_no]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        $approval = $this->crud->read("approvals", [], ["table_name" => "purchase_request_services"]);
        $user_1 = $this->crud->read("users", [], ["username" => @$approval->user_approval_1]);
        $user_2 = $this->crud->read("users", [], ["username" => @$approval->user_approval_2]);
        
        if($purchase_requests->approved_by == @$approval->user_approval_1){
            $imge_1 = '<img src="'.base_url('assets/image/approved.png').'" width="100"/>';
            $imge_2 = "";
        }elseif($purchase_requests->approved_by == @$approval->user_approval_2){
            $imge_1 = '<img src="'.base_url('assets/image/approved.png').'" width="100"/>';
            $imge_2 = '<img src="'.base_url('assets/image/approved.png').'" width="100"/>';
        }else{
            $imge_1 = "";
            $imge_2 = "";
        }

        //Config Page
        $rows = 30;
        $page = ceil(count($purchase_request_total) / $rows);
        //Generate QRcode
        // $this->createQrcode($request_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $purchase_requests->request_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 15 rows</p>
                            <p>Paper Size A4, Layout Landscape</p>
                            <p>Margin Default, Scale 98</p>
                        </center>
                    </div>
                    <div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*');
            $this->db->from('purchase_request_services a');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $request_no);
            $this->db->order_by('a.id', 'asc');
            $this->db->limit(30, ($i * 30));
            $records = $this->db->get()->result_array();

            $html .= '  <div style="border: 1px solid black; width:100%; float:left;">
                            <div style="padding:10px;">
                                <table style="width:100%;" border="0">
                                    <tr>
                                        <td width="200" style="text-align:left; font-size:10px;">
                                            <b>Elcon Banshu Wiring System</b><br>
                                            <b>Office : </b> 1622, 16th Floor, World Trade Tower,<br>
                                            Sec-16, Noida-201301, UP, INDIA<br>
                                            GST No : 09AAFCE7558R1ZN, <br>
                                            CIN No: U35999UP2019PTC124726<br><br>
                                            <b>Factory</b> :  UNIT NO 3, PLOT NO- B-9, ECOTECH EXTENSION<br>
                                            GRATER NOIDA, KASNA, 201306<br>
                                            GST NO : 09AAFCE7558R1ZN
                                        </td>
                                        <th width="100" style="text-align:right;"><img src="' . $config->favicon . '" width="150"/></th>
                                    </tr>
                                </table>
                                <center>
                                    <h3><u>PURCHASE REQUISITION SERVICE</u></h3>
                                </center>
                                <div style="float:left; width:50%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Order No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$purchase_requests->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$purchase_requests->request_date . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Plant</td>
                                            <td width="10">:</td>
                                            <td><b>02 - Noida</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Revision</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$purchase_requests->revision . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="80">Description of Service</th>
                                        <th width="50">Qty</th>
                                        <th width="150">Remarks</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td style="text-align:right">' . number_format($record['qty'], 2) . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';

            if (($i + 1) == $page) {
                $html .= '  <div style="width:20%; float:left; margin-bottom:20px;">
                                <table id="customers" style="margin-top:20px;">
                                    <tr>
                                        <th width="200" style="text-align:center;">ACCEPTED</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">' . @$supplier->name . '</th>
                                    </tr>
                                </table>
                            </div>
                            <div style="width:30%; float:left;">
                                <br>
                            </div>
                            <div style="width:40%; float:right; margin-bottom: 10px;">
                                <table id="customers" style="margin-top:20px;">
                                    <tr>
                                        <th colspan="2" style="text-align:center;">'.$config->name.'</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;">'.@$imge_1.'</th>
                                        <th style="height:80px;">'.@$imge_2.'</th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">'.@$user_1->name.'</th>
                                        <th style="height:20px; text-align:center;">'.@$user_2->name.'</th>
                                    </tr>
                                </table>
                            </div>';
            }else{
                if (($i + 1) != $page) {
                    $html .= '<div style="page-break-after:always;"></div>';
                }

                // $html .= "</div></div>";
            }
        }

        $html .= '</div><script>window.print()</script>';
        die($html);
    }
    
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_request_services_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_request_name = $this->input->get('filter_request_name');
        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_status_po = $this->input->get('filter_status_po');
        $filter_status_approval = $this->input->get('filter_status_approval');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $this->db->select("a.*, e.po_no, b.name as item_family_name, c.name as item_category_name, d.number as asset_code, d.created_date as asset_date, (CASE WHEN c.type = 'ASSET' THEN 'YES' ELSE 'NO' END) as asset_type");
        $this->db->from('purchase_request_services a');
        $this->db->join('item_familys b', 'a.item_family_id = b.id', 'left');
        $this->db->join('item_categories c', "b.item_category_id = c.id");
        $this->db->join('assets d', 'a.request_no = d.request_no', 'left');
        $this->db->join('purchase_order_services e', 'a.request_no = e.request_no and a.item_name = e.item_name', 'left');
        $this->db->where('a.deleted', 0);
        if(!in_array($this->session->username, ["PURCHASE-03", "Purchase-01", "PURCHASE-02", "admin", "hariom_bhardwaj", "ITADMIN", "Accounts01"])){
            $this->db->where('a.created_by', $this->session->username);
        }
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.request_date >=', $filter_from);
            $this->db->where('a.request_date <=', $filter_to);
        }
        if($filter_request_no != ""){
            $this->db->like('a.request_no', $filter_request_no);
        }
        if($filter_request_name != ""){
            $this->db->like('a.request_name', $filter_request_name);
        }
        if($filter_status_approval != ""){
            if($filter_status_approval == "CHECKING"){
                $this->db->where("(a.approved_to != '' and a.approved_to is not null)");
            }else{
                $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            }
        }
        if($filter_status_po != ""){
            if($filter_status_approval == "0"){
                $this->db->where("e.po_no is null");
            }else{
                $this->db->where("e.po_no is not null");
            }
        }
        $this->db->like('a.item_name', $filter_item_rm);
        $this->db->order_by('a.request_no', 'DESC');
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
                                <small>PURCHASE REQUEST SERVICE</small>
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
                <th>Status PO</th>
                <th>Status Approval</th>
                <th>Request No</th>
                <th>Request Date</th>
                <th>Request Name</th>
                <th>Expected Date</th>
                <th>Description of Service</th>
                <th>Product Family</th>
                <th>Category</th>
                <th>Asset Type</th>
                <th>Asset Code</th>
                <th>Asset Date</th>
                <th>Qty</th>
                <th>Currency</th>
                <th>Est. Price</th>
                <th>Total</th>
                <th>PO No</th>
                <th>Remarks</th>
                <th>Created By</th>
                <th>Created Date</th>
                <th>Approved Next</th>
                <th>Approved By</th>
                <th>Approved Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "UN CONVERTED";
            } else {
                $status = "CONVERTED";
            }
            if (empty($data['approved_to'])) {
                $status_approval = "APPROVED";
            } else {
                $status_approval = "CHECKING";
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $status . '</td>
                        <td>' . $status_approval . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['expected_date'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . $data['item_category_name'] . '</td>
                        <td>' . $data['asset_type'] . '</td>
                        <td>' . $data['asset_code'] . '</td>
                        <td>' . $data['asset_date'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['price'] . '</td>
                        <td>' . $data['total'] . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['created_by'] . '</td>
                        <td>' . $data['created_date'] . '</td>
                        <td>' . $data['approved_to'] . '</td>
                        <td>' . $data['approved_by'] . '</td>
                        <td>' . $data['approved_date'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
