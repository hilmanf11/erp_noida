<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sto_fg extends CI_Controller
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
        // $this->form_validation->set_rules('item_fg_id', 'Item RM', 'required|min_length[1]|max_length[30]|is_unique[sto_fg.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/sto_fg');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('sto_fg', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select('a.*, b.uom, b.number_customer as item_fg_number, b.name as item_fg_name');
            $this->db->from('sto_fg a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            if($filter_from != "" && $filter_to != ""){
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            if($filter_item_fg != ""){
                $this->db->like('b.id', $filter_item_fg);
            }
            $this->db->group_by('a.id');
            $this->db->order_by('b.number_customer', 'ASC');

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

            $datenow = date("ym", strtotime($post['trans_date']));
            $sqlGetID = $this->db->query("SELECT max(document_no) as kode FROM sto_fg WHERE document_no like '%$datenow%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;

            if ($kode == NULL) {
                $autoID = sprintf("%04s", $kode + 1);
            } else {
                $urutan = (int) substr($kode, -4);
                $urutan++;
                $autoID = sprintf("%04s", $urutan);
            }

            $document_no = "STO-" . $datenow ."-" . $post['type'] . "-" . $autoID;

            $send = $this->crud->create('sto_fg', array_merge($post, array("document_no" => $document_no)));
            echo $send;
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
            $send = $this->crud->update('sto_fg', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sto_fg', $data);
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
                'item_fg_number' => $data->val($i, 2),
                'type' => $data->val($i, 3),
                'qty' => $data->val($i, 4),
                'trans_date' => $data->val($i, 5),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/sto_fg.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/sto_fg.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/sto_fg.txt";
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

            $datenow = date("ym", strtotime($data['trans_date']));
            $sqlGetID = $this->db->query("SELECT max(document_no) as kode FROM sto_fg WHERE document_no like '%$datenow%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;

            if ($kode == NULL) {
                $autoID = sprintf("%04s", $kode + 1);
            } else {
                $urutan = (int) substr($kode, -4);
                $urutan++;
                $autoID = sprintf("%04s", $urutan);
            }

            $document_no = "STO-" . $datenow ."-" . $data['type'] . "-" . $autoID;

            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);

            if(empty($item_fg->id)){
                echo json_encode(array("title" => "Not Found", "message" => "Product No EBWS " . $data['item_fg_number'] . " is Not Found", "theme" => "error"));
            }elseif(!in_array($data['type'], array("IN","OUT"))){
                echo json_encode(array("title" => "Not Found", "message" => "Type " . $data['type'] . " is Not IN/OUT", "theme" => "error"));
            } else {
                $dataFinal = array(
                    "item_fg_id" => $item_fg->id,
                    "trans_date" => $data['trans_date'],
                    "type" => $data['type'],
                    "document_no" => $document_no,
                    "qty" => $data['qty']
                );

                $send = $this->crud->create('sto_fg', $dataFinal);
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
            header("Content-Disposition: attachment; filename=sto_fg_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.uom, b.number_customer as item_fg_number, b.name as item_fg_name');
        $this->db->from('sto_fg a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        if($filter_from != "" && $filter_to != ""){
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('b.id', $filter_item_fg);
        $this->db->order_by('a.trans_date', 'DESC');
        $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#sto_fg {border-collapse: collapse;width: 100%;font-size: 12px;}#sto_fg td, #sto_fg th {border: 1px solid #ddd;padding: 2px;}#sto_fg tr:nth-child(even){background-color: #f2f2f2;}#sto_fg tr:hover {background-color: #ddd;}#sto_fg th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>DATA STO FINISH GOOD</h3>
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
            
            <table id="sto_fg" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Document No</th>
                    <th>Product No EBWS</th>
                    <th>Product No Customer</th>
                    <th>Description</th>
                    <th>Trans Date</th>
                    <th>Type</th>
                    <th>Qty</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $data['document_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['type'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
    }   
}
