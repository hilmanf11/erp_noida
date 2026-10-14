<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Supply_requests extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('planning/supply_requests');
        } else {
            redirect('error_access');
        }
    }

    public function readRequestNo()
    {
        $records = $this->crud->query("SELECT request_no FROM supply_sheets WHERE `type` = 'REQ' GROUP BY `request_no`");
        echo json_encode($records);
    }

    public function readItem(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT DISTINCT b.id, b.number, b.number_customer, b.name, a.workorder 
            FROM production_schedules a
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.status_wip = '0' and b.number_customer like '%$post%' or a.workorder like '%$post%'
            ORDER BY b.number_customer, a.workorder ASC");
        echo json_encode($records);
    }

    public function readBom($item_fg_id)
    {
        $item_fg_id = base64_decode($item_fg_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom, c.mpq
            FROM bom a 
            JOIN item_rm b ON a.item_rm_id = b.id
            JOIN item_mpq c ON a.item_rm_id = c.item_rm_id
            WHERE a.item_fg_id = '$item_fg_id' and b.number like '%$post%'
            ORDER BY b.number asc");
        echo json_encode($send);
    }

    public function request_no($id_menu)
    {
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

            $sqlGetID	= $this->db->query("SELECT max(request_no) as kode FROM supply_sheets WHERE request_no like '%$whereDate%'");
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

            echo $autoNo;
        }
    }

    public function datatableUpdate($request_no){
        $request_no = base64_decode($request_no);

        //Select Query
        $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom, d.composition');
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('bom d', 'd.item_fg_id = b.id and d.item_rm_id = c.id', 'left');
        $this->db->where('a.request_no', $request_no);

        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_request_no = $this->input->get('filter_request_no');
            $filter_product_family = $this->input->get('filter_product_family');
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            $id = $_POST['id'];
            if ($id === "0") {
                //Select Query
                $this->db->select('a.*, c.number as item_number, c.name as item_name, b.uom');
                $this->db->from('supply_sheets a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.type', 'REQ');
                $this->db->where("a.request_date >= '$filter_from'");
                $this->db->where("a.request_date <= '$filter_to'");
                if ($filter_request_no != "") {
                    $this->db->where('a.request_no', $filter_request_no);
                }
                if($filter_product_family != ""){
                    $this->db->where('b.item_family_id', $filter_product_family);
                }
                if($filter_product_no != ""){
                    $this->db->where('a.item_rm_id', $filter_product_no);
                }
                $this->db->group_by('a.request_no');
                $this->db->order_by('a.approved_to', 'DESC');
                $this->db->order_by('a.request_no', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['request_no'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "workorder" => $record['workorder'],
                        "item_fg_id" => $record['item_fg_id'],
                        "item_rm_id" => $record['item_rm_id'],
                        "item_number" => $record['item_number'],
                        "item_name" => $record['item_name'],
                        "approved_to" => $record['approved_to'],
                        "remarks" => $record['remarks'],
                        "state" => "closed",
                        "status" => 0
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                //Select Query
                $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
                $this->db->from('supply_sheets a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->where('a.request_no', $id);
                if($filter_product_family != ""){
                    $this->db->where('b.item_family_id', $filter_product_family);
                }
                if($filter_product_no != ""){
                    $this->db->where('a.item_rm_id', $filter_product_no);
                }
                $this->db->order_by('a.request_no', 'DESC');
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['id'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "item_rm_id" => $record['item_rm_id'],
                        "item_number" => $record['item_number'],
                        "item_name" => $record['item_name'],
                        "workorder" => $record['workorder'],
                        "qty" => $record['qty_act'],
                        "uom" => $record['uom'],
                        "status" => $record['status'],
                        "created_by" => $record['created_by'],
                        "created_date" => $record['created_date'],
                        "updated_by" => $record['updated_by'],
                        "updated_date" => $record['updated_date'],
                        "approved" => $record['approved'],
                        "approved_by" => $record['approved_by'],
                        "approved_date" => $record['approved_date'],
                        "approved_to" => $record['approved_to'],
                        "remarks" => $record['remarks'],
                        "status" => 1
                    );
                }
                $result = !empty($arr) ? $arr : [];
                echo json_encode($result);
            }
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                $request_no = $post['request_no'];
                $item_rm_id = $post['item_rm_id'];

                $datas = $this->crud->reads('supply_sheets', [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id]);
                if(count($datas) > 0){
                    $send = $this->crud->update('supply_sheets', ["request_no" => $request_no, "item_rm_id" => $item_rm_id], array_merge($post, array("type" => "REQ")));
                    echo $send;
                }else{
                    $send = $this->crud->create('supply_sheets', array_merge($post, array("type" => "REQ")));
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('supply_sheets', ["request_no" => $data['id']]);
        echo $send;
    }

    public function print_kanban($request_no)
    {
        $kanbans = $this->crud->reads('supply_sheets', [], ["request_no" => base64_decode($request_no)]);
        $kanban = $this->crud->read('supply_sheets', [], ["request_no" => base64_decode($request_no)]);
        $config = $this->db->get('config')->row();
        $rows = 8;
        $page = ceil(count($kanbans) / $rows);
        //Generate QRcode
        $this->createQrcode($kanban->request_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $kanban->request_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: left;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 8 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
            $this->db->from('supply_sheets a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.request_no', base64_decode($request_no));
            $this->db->group_by('a.item_rm_id');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();
            $html .= '<table>
                            <tr>
                                <th style="padding:10px;"><img src="' . $config->favicon . '" width="50" /></th>
                                <td width="700" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>REQUEST KANBAN MATERIAL</u></h3>
                                </center>
                                <div style="float:left; width:45%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Request No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Request Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($kanban->request_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Request Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->request_name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:45%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Workorder</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->workorder . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Print Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y") . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Print By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$this->session->name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:10%; text-align:right;">
                                    <img src="' . base_url('assets/image/qrcode/' . $kanban->request_no . '.png') . '" width="60"/>
                                </div> 
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Part No EBWS</th>
                                        <th>Part No Supplier</th>
                                        <th>Description</th>
                                        <th>Uom</th>
                                        <th>Qty</th>
                                    </tr>';
            $no = 1;
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_number'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . $record['qty_act'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>
                <br>
                <table id="customers">
                    <tr>
                        <th style="text-align:center;">Production</th>
                        <th style="text-align:center;">Warehouse</th>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <td style="height:20px;"></td>
                        <td style="height:20px;"></td>
                    </tr>
                </table>
                </div>
            </div>';
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }
        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=supply_requests_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_product_family = $this->input->get('filter_product_family');
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.number_customer, c.number as item_rm_number, c.name as item_rm_name, c.uom');
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.type', "REQ");
        $this->db->where("a.request_date >= '$filter_from'");
        $this->db->where("a.request_date <= '$filter_to'");
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        if($filter_product_family != ""){
            $this->db->where('c.item_family_id', $filter_product_family);
        }
        if($filter_product_no != ""){
            $this->db->where('a.item_rm_id', $filter_product_no);
        }
        $this->db->order_by('a.request_no', 'DESC');
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
                                <small>REQUEST KANBAN MATERIAL</small>
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
                <th>No</th>
                <th>Request No</th>
                <th>Request Date</th>
                <th>Requester</th>
                <th>Workorder</th>
                <th>Product No EBWS</th>
                <th>Product No Customer</th>
                <th>Product Name</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Part Name</th>
                <th>Qty</th>
                <th>Uom</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['workorder'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['number_customer'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_rm_number'] . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                        <td>' . $data['qty_act'] . '</td>
                        <td>' . $data['uom'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
