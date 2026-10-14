<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Os_sto_fg extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Item FG', 'required|min_length[1]|max_length[30]|is_unique[os_fg.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/os_sto_fg');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('os_fg', ["item_fg_id" => $post]);
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
            $this->db->select('a.*, b.uom, b.number as item_fg_number, b.qty_box, b.number_customer, b.name as item_fg_name');
            $this->db->from('os_fg a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.type', "STO");
            if($filter_from != "" && $filter_to != ""){
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('b.id', $filter_item_fg);
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
            $post   = $this->input->post();
            $whereDate = date("ymd", strtotime($post['trans_date']));
            $document_no = "STO-FG-".$whereDate."-1";

            $os_fg = $this->crud->read('os_fg', [], ["item_fg_id" => $post['item_fg_id'], "document_no" => $document_no]);

            if(empty($os_fg->id)){
                $send = $this->crud->create('os_fg', array_merge($post, array("document_no" => $document_no)));
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
            $send = $this->crud->update('os_fg', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('os_fg', $data);
        $send = $this->crud->delete('scan_item_receipts_fg', ["workorder" => $data['id']]);
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
        @unlink('failed/os_fg.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/os_fg.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/os_fg.txt";
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

            $item_fg = $this->crud->read('item_fg', [], ["number_customer" => $data['item_fg_number']]);

            $os_fg = $this->crud->read('os_fg', [], [
                "item_fg_id" => @$item_fg->id, 
                "document_no" => $document_no
            ]);

            if (!empty($os_fg->item_fg_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No EBWS " . $data['item_fg_number'] . " is Duplicate Data", "theme" => "error"));
            }elseif(empty($item_fg->id)){
                echo json_encode(array("title" => "Not Found", "message" => "Product No EBWS " . $data['item_fg_number'] . " is Not Found", "theme" => "error"));
            } else {
                $location = $this->crud->query("SELECT `number` from warehouse_locations where `type` = 'FG' order by RAND() limit 1");

                if(empty($location->number)){
                    $dataFinal = array(
                        "item_fg_id" => $item_fg->id,
                        "trans_date" => $data['trans_date'],
                        "location" => @$location[0]->number,
                        "document_no" => $document_no,
                        "type" => "STO",
                        "qty" => $data['qty']
                    );
    
                    $send = $this->crud->create('os_fg', $dataFinal);
                    echo $send;
                }else{
                    echo json_encode(array("title" => "Not Found", "message" => "Product No EBWS " . $data['item_fg_id'] . " Location Not Ready", "theme" => "error"));
                }
            }
        }
    }

    public function createLabel(){
        $post = $this->input->post();
        $id = $post['id'];
        $mpq = $post['mpq'];
        
        $po_receipt_label = $this->crud->reads('scan_item_receipts_fg', [], ["workorder" => $id]);

        $os_rm = $this->crud->read('os_fg', [], ["id" => $id]);
        $qty_receipt = $os_rm->qty;
        $qty_label = ceil($qty_receipt / $mpq);

        if (!$po_receipt_label) {
            for ($i = 0; $i < $qty_label; $i++) {
                //Read Label ID
                $sqlGetID = $this->db->query("SELECT max(checksheet_label) as kode FROM scan_item_receipts_fg WHERE workorder = '$id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;

                if ($label == NULL) {
                    $autoID = "STOFG".$id . sprintf("%04s", $label + 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = "STOFG".$id . sprintf("%04s", $urutan + 1);
                }

                if ($qty_receipt > $mpq) {
                    $qty = $mpq;
                } else {
                    $qty = $qty_receipt;
                }   

                //Simpan Label
                $arrLabel = [
                    "workorder" => $id,
                    "checksheet_label" => $autoID,
                    "qty" => $qty,
                    "warehouse" => $os_rm->location
                ];

                $this->crud->create('scan_item_receipts_fg', $arrLabel);
                $qty_receipt = ($qty_receipt - $mpq);
            }

            $this->db->update("os_fg", ["created_label" => date("Y-m-d H:i:s")], ["id" => $id]);
            echo "OK";
        }
    }

    public function print_label($id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $id = base64_decode($id);
        
        //Cek Label
        $this->db->select('d.number, d.name, d.number_customer, d.alias, a.qty, a.checksheet_label, b.trans_date, i.id as customer_number, i.name as customer_name');//d.description,
        $this->db->from('scan_item_receipts_fg a');
        $this->db->join('os_fg b', 'a.workorder = b.id');
        $this->db->join('item_fg d', 'b.item_fg_id = d.id');
        $this->db->join('customer_items h', 'd.id = h.item_fg_id', 'left');
        $this->db->join('customers i', 'i.id = h.customer_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.workorder', $id);
        $this->db->group_by('a.checksheet_label');
        $wip_receipt_labels = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($wip_receipt_labels) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($wip_receipt_labels as $wip_receipt_label) {
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                //     $padding = "padding:0 3mm 1mm 0mm;";
                // } else {
                //     $padding = "padding:0 0mm 1mm 4mm;";
                // }
                //Generate QRcode

                // if ($wip_receipt_label->logo == "0") {
                //     $img_leoco = '<img style="width:100%;" src="' . base_url("assets/image/BPI.png") . '" />';
                // } else {
                //     $img_leoco = '';
                // }

                $qrcodes = $wip_receipt_label->checksheet_label;
                $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1">
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Code</td>
                                        <th colspan="2" style="font-size:15px; text-align:left;"><b>' . $wip_receipt_label->customer_number . '</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Name.</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->customer_name . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">EBWS P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->number_customer . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Lot. No</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Quantity</td>
                                        <td style="width:25mm;"><small style="font-size:20px;"><b>' . number_format($wip_receipt_label->qty, 2) . '</b></small></td>
                                        <td style="text-align:center;" style="width:25mm" rowspan="3">
                                            <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="100"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Date</td>
                                        <td><small style="font-size:12px;"><b>' . $wip_receipt_label->trans_date . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:10px; text-align:center;">Control ID</td>
                                        <td><small style="font-size:12px;"><b></b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm;"></td>
                                        <td colspan="2" style="text-align:center;"><small style="font-size:14px;"><b>ELCON BANSHU</b></small></td>
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

        $this->db->select('a.*, b.uom, b.number as item_fg_number, b.number_customer, b.name as item_fg_name');
        $this->db->from('os_fg a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.type', "STO");
        if($filter_from != "" && $filter_to != ""){
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('b.id', $filter_item_fg);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#os_fg {border-collapse: collapse;width: 100%;font-size: 12px;}#os_fg td, #os_fg th {border: 1px solid #ddd;padding: 2px;}#os_fg tr:nth-child(even){background-color: #f2f2f2;}#os_fg tr:hover {background-color: #ddd;}#os_fg th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>DATA OUTSTANDING FINISH GOOD</h3>
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
            
            <table id="os_fg" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Product No EBWS</th>
                    <th>Product No Customer</th>
                    <th>Description</th>
                    <th>Cut Off</th>
                    <th>Location</th>
                    <th>Qty</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
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
