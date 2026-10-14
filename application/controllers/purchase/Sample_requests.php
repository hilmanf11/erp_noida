<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sample_requests extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Part No EBWS', 'required|min_length[1]|max_length[100]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('purchase/sample_requests');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('purchase_orders', ["name" => $post]);
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_family_id = $this->input->get('item_family_id');
        $item_category_id = $this->input->get('item_category_id');
        if (!empty($item_category_id)) {
            $this->db->where('a.item_category_id', $item_category_id);
        }
        if (!empty($item_family_id)) {
            $this->db->where('a.item_family_id', $item_family_id);
        }

        $this->db->select('a.*, a.id as item_rm_id, a.number as item_number, a.name as item_name');
        $this->db->from('item_rm a');
        $this->db->join('item_categories b', 'a.item_category_id = b.id');
        $this->db->join('item_familys c', 'a.item_family_id = c.id');
        $this->db->where("a.id like '%$post%' or a.number like '%$post%'");
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        // die($this->db->last_query());

        echo json_encode($records);
    }


    public function autonumber($id_menu, $add = "")
    {
        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(number) as kode FROM sample_requests WHERE number like '%$whereDate%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;

            if ($kode == NULL) {
                $autoID = sprintf("%0" . $row_menu->sequence . "s", $kode + 1);
                $autoNo = $whereDate . $autoID;
            } else {
                $urutan = (int) substr($kode, -6);
                $urutan++;
                $autoID    = sprintf("%0" . $row_menu->sequence . "s", $urutan);
                $autoNo = $whereDate . $autoID;
            }

            if ($add == "") {
                return $autoNo;
            } else {
                die(json_encode(array("code" => $autoNo)));
            }
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_category = $this->input->get('filter_category');
            $filter_product_family = $this->input->get('filter_product_family');
            $filter_part_no = $this->input->get('filter_part_no');
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
                //Select Query
                $this->db->select('a.number, sum(a.qty) as qty, sum(a.price) as price, sum(a.amount) as amount,a.status');
                $this->db->from('sample_requests a');
                $this->db->join('item_rm b', 'a.item_rm_id=b.id');
                $this->db->where('a.deleted', 0);
                $this->db->like('b.item_category_id', $filter_category);
                $this->db->like('b.item_family_id', $filter_product_family);
                $this->db->like('a.item_rm_id', $filter_part_no);
                $this->db->group_by('a.number');
                $this->db->order_by('a.number', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                //Mapping Data
                foreach ($records as $record) {
                    // if ($record['total_status'] == $record['total_status_close']) {
                    //     $status = "1";
                    // } else {
                    //     $status = "0";
                    // }

                    $arr[] = array(
                        "id" => $record['number'],
                        "number" => $record['number'],
                        "status" => $record['status'],
                        "qty" => $record['qty'],
                        "price" => $record['price'],
                        "amount" => $record['amount'],
                        "state" => "closed",
                        "datatable" => 1
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                    b.number as item_rm_number, 
                    b.name as item_rm_name,
                    b.uom,
                    c.name as item_family_name, 
                    d.name as item_category_name,
                    a.currency,
                    a.qty, 
                    a.price, 
                    a.amount, 
                    a.status,
                    ');
                $this->db->from('sample_requests a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->join('item_categories d', 'b.item_category_id = d.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.number', $id);
                $this->db->like('b.item_category_id', $filter_category);
                $this->db->like('b.item_family_id', $filter_product_family);
                $this->db->like('a.item_rm_id', $filter_part_no);
                $this->db->order_by('a.item_rm_id', 'DESC');
                $records = $this->db->get()->result_array();
                echo json_encode($records);
            }
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $send = $this->crud->create('sample_requests', $post);
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
            $post = $this->input->post();
            $id = $this->input->post('id');

            if ($id != "") {
                $sample_requests = $this->crud->update('sample_requests', ["id" => $id], [
                    "qty" => $post['qty'],
                    "price" => $post['price'],
                    "amount" => $post['amount'],
                    "currency" => $post['currency'],
                    "remarks" => $post['remarks']
                ]);

                echo $sample_requests;
            } else {
                show_error("Cannot Process your request");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update_approval()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('signatures', [], $post);

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sample_requests', $data);
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
                'number' => $data->val($i, 2),
                'item_rm_id' => $data->val($i, 3),
                'qty' => $data->val($i, 4),
                'price' => $data->val($i, 5),
                'currency' => $data->val($i, 6),
                'amount' => $data->val($i, 7),
                'remarks' => $data->val($i, 8)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_sample_requests.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_sample_requests.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_sample_requests.txt";
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
            $sample_requests = $this->crud->read('sample_requests', [], ["number" => $data['number'], "item_rm_id" => $data['item_rm_id']]);

            if (empty($item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($sample_requests->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part No " . $data['item_rm_id'] . " Duplicate Data", "theme" => "error"));
            } else {
                $send = $this->crud->create('sample_requests', $data);
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_orders_$format.xls");
        }
        $filter_category = $this->input->get('filter_category');
        $filter_product_family = $this->input->get('filter_product_family');
        $filter_part_no = $this->input->get('filter_part_no');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
                    b.number as item_rm_number, 
                    b.name as item_rm_name,
                    c.name as item_family_name, 
                    d.name as item_category_name,
                    a.currency,
                    a.qty, 
                    a.price, 
                    a.amount, 
                    a.status,
                    ');
        $this->db->from('sample_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('item_categories d', 'b.item_category_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->like('b.item_category_id', $filter_category);
        $this->db->like('b.item_family_id', $filter_product_family);
        $this->db->like('a.item_rm_id', $filter_part_no);
        $this->db->order_by('a.item_rm_id', 'DESC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>SAMPLE REQUESTS</small>
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
                    <th>Sample No</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>Qty</th>
                    <th>Currency</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>Remarks</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_rm_number'] . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                        <td>' . $data['item_category_name'] . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['price'], 4, ",", ".") . '</td>
                        <td>' . number_format(($data['amount']), 2, ",", ".") . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['created_by'] . '</td>
                        <td>' . $data['created_date'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_sample_slip($number)
    {
        $number = base64_decode($number);
        $filter_category = base64_decode($this->input->get('filter_category'));
        $filter_product_family = base64_decode($this->input->get('filter_product_family'));
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
                    b.number as item_rm_number, 
                    b.name as item_rm_name,
                    b.uom,
                    c.name as item_family_name, 
                    d.name as item_category_name,
                    a.currency,
                    a.qty, 
                    a.price, 
                    a.amount, 
                    a.status,
                    ');
        $this->db->from('sample_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('item_categories d', 'b.item_category_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.number', $number);
        $this->db->order_by('a.item_rm_id', 'DESC');
        $records = $this->db->get()->result_array();

        if ($filter_category != '') {
            $item_categories = $this->crud->read('item_categories', [], ["id" => $filter_category]);
            $category = @$item_categories->name;
        } else {
            $category = "ALL";
        }

        if ($filter_product_family != '') {
            $item_familys = $this->crud->read('item_familys', [], ["id" => $filter_product_family]);
            $family = @$item_familys->name;
        } else {
            $family = "ALL";
        }
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>SAMPLE REQUESTS</small><br>
                                <small>Sample No :' . $number . '</small><br>
                                <small>Category : ' . $category . '</small><br>
                                <small>Family : ' . $family . '</small><br>
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
                    <th>Sample No</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Currency</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_rm_number'] . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                        <td>' . $data['item_category_name'] . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['price'], 4, ",", ".") . '</td>
                        <td>' . number_format(($data['amount']), 2, ",", ".") . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table>
        <br>
                    <div style="float:right; width:100%;">
                        <table align="right" id="customers" border="1" style="width:30%; !important">
                        <tr>
                            <td align="center">Created By</td>
                            <td align="center">Approved By</td>
                            </tr>
                        <tr>
                            <td style="height:50px"></td>
                            <td style="height:50px"></td>
                        </tr>
                        <tr>
                            <td align="center">' . $this->session->username . '</td>
                            <td align="center"></td>
                        </tr>
                        </table>
                    </div>
        
        </body></html>';
        $html .= '<script>window.print()</script>';
        die($html);
    }
}
