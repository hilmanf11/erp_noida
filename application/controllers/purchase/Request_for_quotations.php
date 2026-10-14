<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Request_for_quotations extends CI_Controller
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
            $this->load->view('purchase/request_for_quotations');
        } else {
            redirect('error_access');
        }
    }

    public function readPono($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT DISTINCT po_no FROM request_for_quotations WHERE `status` = '0' and supplier_id = '$supplier_id' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function reads()
    {
        // $rfq_no = base64_decode($this->input->get('rfq_no'));
        $supplier_id = base64_decode($this->input->get('supplier_id'));

        //Select Query
        $this->db->select("a.*, 
            b.number as item_rm_number, 
            b.name as item_rm_name, 
            b.uom,
            d.mpq,
            d.moq,
            e.id as supplier_id,
            e.number as supplier_number,
            e.name as supplier_name,
            c.name as category_name,
            f.name as maker,
            (CASE WHEN d.approved_to = '' THEN d.price ELSE 0 END) as price,
            (a.qty * (CASE WHEN d.approved_to = '' THEN d.price ELSE 0 END)) as total,
            DATE_FORMAT(now(), '%Y-%m-%d') as delivery_date");
        $this->db->from('request_for_quotations a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('supplier_items d', "a.item_rm_id = d.item_rm_id");
        $this->db->join('suppliers e', 'd.supplier_id = e.id');
        $this->db->join('makers f', 'd.maker_id = f.id', 'left');
        $this->db->where('a.status', 0);
        // $this->db->where("(a.asset_code = '' or a.asset_code is null or a.asset_code = '-')");
        $this->db->where('d.supplier_id', $supplier_id);
        $this->db->where("a.approved_to", "");
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readRm()
    {
        $supplier_id = base64_decode($this->input->get('supplier_id'));

        //Select Query
        $this->db->select("a.*, d.price");
        $this->db->from('item_rm a');
        $this->db->join('supplier_items d', "a.id = d.item_rm_id", 'left');
        $this->db->where('a.item_category_id !=', 'C01');
        $this->db->where('d.supplier_id', $supplier_id);
        $this->db->where("d.approved_to", "");
        $this->db->order_by('a.number', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readRequestno($supplier_id = "", $status = 0)
    {
        if ($supplier_id == "") {
            $where_supplier = "";
        } else {
            $where_supplier = "b.supplier_id = '$supplier_id' and";
        }

        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT a.rfq_no, a.rfq_date
            FROM request_for_quotations a 
            WHERE $where_supplier a.status = 0 and (a.approved_to = '' or a.approved_to is null) 
            GROUP BY a.rfq_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function rfq_no($id_menu, $add = "")
    {
        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            die(json_encode([]));
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(rfq_no) as kode FROM request_for_quotations WHERE rfq_no like '%$whereDate%'");
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
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_rfq_no = $this->input->get('filter_rfq_no');
        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_supplier = $this->input->get('filter_supplier');
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
            $this->db->select('a.rfq_no, a.rfq_date, a.status, a.supplier_id, d.name as supplier_name');
            $this->db->from('request_for_quotations a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', "b.item_family_id = c.id");
            $this->db->join('suppliers d', 'a.supplier_id=d.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.rfq_date >=', $filter_from);
                $this->db->where('a.rfq_date <=', $filter_to);
            }
            $this->db->like('a.rfq_no', $filter_rfq_no);
            $this->db->like('a.item_rm_id', $filter_item_rm);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('c.id', $filter_item_familys);
            $this->db->group_by('a.rfq_no');
            $this->db->order_by('a.rfq_no', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['rfq_no'],
                    "rfq_no" => $record['rfq_no'],
                    "supplier_id" => $record['supplier_id'],
                    "supplier_name" => $record['supplier_name'],
                    "rfq_date" => $record['rfq_date'],
                    "status" => $record['status'],
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
                d.name as supplier_name');
            $this->db->from('request_for_quotations a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', "b.item_family_id = c.id");
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.rfq_date >=', $filter_from);
                $this->db->where('a.rfq_date <=', $filter_to);
            }
            $this->db->where('a.rfq_no', $id);
            $this->db->like('b.id', $filter_item_rm);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('c.id', $filter_item_familys);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function datatable_updates()
    {
        $rfq_no = base64_decode($this->input->get('rfq_no'));

        $records = $this->crud->query("SELECT a.*, c.number as item_number, c.name as item_rm_name, c.uom, d.name as item_family_name
            FROM request_for_quotations a
            JOIN item_rm c on a.item_rm_id = c.id
            JOIN item_familys d ON c.item_family_id = d.id
            WHERE a.status = '0' and a.rfq_no = '$rfq_no'
            GROUP BY c.number");
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $request_for_quotations = $this->crud->read('request_for_quotations', [], ["rfq_no" => $post['rfq_no'], "item_rm_id" => $post['item_rm_id']]);
                if (@$request_for_quotations->id != "") {
                    $send = $this->crud->update('request_for_quotations', ["rfq_no" => $post['rfq_no'], "item_rm_id" => $post['item_rm_id']], $post);
                } else {
                    $send = $this->crud->create('request_for_quotations', $post);
                }

                echo $send;
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
            $send = $this->crud->update('request_for_quotations', ["id" => $id], ["qty" => $post['qty'], "remarks" => $post['remarks']]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('request_for_quotations', ["id" => $data['id']]);
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
                'supplier_id' => $data->val($i, 2),
                'rfq_no' => $data->val($i, 3),
                'rfq_date' => $data->val($i, 4),
                'item_rm_id' => $data->val($i, 5),
                'mpq' => $data->val($i, 6),
                'moq' => $data->val($i, 7),
                'leadtime' => $data->val($i, 8),
                'price' => $data->val($i, 9)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/request_for_quotations.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/request_for_quotations.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/request_for_quotations.txt";
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
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);
            $suppliers = $this->crud->read('suppliers', [], ["id" => $data['supplier_id']]);
            $request_for_quotations = $this->crud->read('request_for_quotations', [], ["rfq_no" => $data['rfq_no'], "supplier_id" => $data['supplier_id'], "item_rm_id" => $data['item_rm_id']]);

            if (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($suppliers->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier ID " . $data['supplier_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($request_for_quotations->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "RFQ No " . $data['rfq_no'] . " | Supplier " . $data['supplier_id'] . " | Part No " . $data['item_rm_id'] . " Duplicate Data", "theme" => "error"));
            } else {
                $send = $this->crud->create('request_for_quotations', $data);
                echo $send;
            }
        }
    }

    public function print_request($rfq_no)
    {
        $rfq_no = base64_decode($rfq_no);
        $request_for_quotation_total = $this->crud->reads('request_for_quotations', [], ["rfq_no" => $rfq_no]);
        $request_for_quotations = $this->crud->read('request_for_quotations', [], ["rfq_no" => $rfq_no]);
        $supplier = $this->crud->read('suppliers', [], ["id" => @$request_for_quotations->supplier_id]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        $approval = $this->crud->read("approvals", [], ["table_name" => "request_for_quotations"]);
        $user_1 = $this->crud->read("users", [], ["username" => @$approval->user_approval_1]);
        $user_2 = $this->crud->read("users", [], ["username" => @$approval->user_approval_2]);

        if ($request_for_quotations->approved_by == @$approval->user_approval_1) {
            $imge_1 = '<img src="' . base_url('assets/image/approved.png') . '" width="100"/>';
        } elseif ($request_for_quotations->approved_by == @$approval->user_approval_2) {
            $imge_1 = '<img src="' . base_url('assets/image/approved.png') . '" width="100"/>';
        } else {
            $imge_1 = "";
        }

        //Config Page
        $rows = 30;
        $page = ceil(count($request_for_quotation_total) / $rows);
        //Generate QRcode
        $this->createQrcode($rfq_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $request_for_quotations->rfq_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <!-- <p>Display pages for 15 rows</p>
                            <p>Paper Size A4, Layout Landscape</p>
                            <p>Margin Default, Scale 98</p> -->
                        </center>
                    </div>
                    <div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom');
            $this->db->from('request_for_quotations a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.rfq_no', $rfq_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(30, ($i * 30));
            $records = $this->db->get()->result_array();

            $html .= '  <div style="border: 1px solid black; width:100%; float:left;">
                            <div style="padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;">REQUEST FOR QUOTATION (RFQ)</h3>
                                    <small>RFQ No: ' . $rfq_no . '</small>
                                </center>
                                <img style="position: absolute; top: 20px; right: 20px;" src="' . base_url('assets/image/qrcode/' . $rfq_no . '.png') . '" width="80" border="1"/>
                                <br>
                                <br>
                                <div style="width:45%; float:left;">
                                    <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                        <tr>
                                            <td width="50" style="vertical-align: top;"><b>To</b></td>
                                            <td width="5" style="vertical-align: top;">:</td>
                                            <td><b>' . @$supplier->name . '</b><br>' . @$supplier->address . '<br>GST No : ' . @$supplier->gst . '</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3"><br></td>
                                        </tr>
                                        <tr>
                                            <td width="50" style="vertical-align: top;"><b>Attn to :</b></td>
                                            <td width="5" style="vertical-align: top;">:</td>
                                            <td><b>' . @$supplier->contact_person . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width:10%; float:left;"><br></div>
                                <div style="width:45%; float:left;">
                                    <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                        <tr>
                                            <td width="50" style="vertical-align: top;"></td>
                                            <td width="30" style="vertical-align: top;"></td>
                                            <td>
                                                Office Address : <br>
                                                <b>' . @$config->name . '</b><br>
                                                ' . @$config->address . '<br><br>
                                                Factory / Shipping Address : <br>
                                                ' . @$config->address2 . '<br>
                                                GST No : ' . @$config->gst_no . '
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <br>
                                <small>We are requesting a quotation for the following Item:</small>
                                <br>
                                <br>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="80">Part No EBWS</th>
                                        <th width="200">Part No Supplier</th>
                                        <th width="200">Description</th>
                                        <th width="50">Uom</th>
                                        <th width="50">MPQ</th>
                                        <th width="50">MOQ</th>
                                        <th width="50">Lead Time</th>
                                        <th width="50">Price</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_rm_number'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right">' . $record['mpq'] . '</td>
                                <td style="text-align:right">' . $record['moq'] . '</td>
                                <td style="text-align:right">' . $record['leadtime'] . '</td>
                                <td style="text-align:right">' . $record['price'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';

            if (($i + 1) == $page) {
                $html .= '  <div style="width:100%; margin-bottom:20px;margin-top:20px;">
                                <small>
                                    We Would appreciate it if you could provide your best offer based on the above spesifications.<br>
                                    if you need further information or clarification, feel free to contact us.<br>
                                    Thank your for your prompt attention to this requests.
                                </small>
                            </div>
                            <div style="width:30%; float:right; margin-bottom: 10px;">
                                <table style="margin-top:20px;" border="0">
                                    <tr>
                                        <th style="text-align:center; font-size:12px;">' . $config->name . '</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;">' . @$imge_1 . '</th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">' . @$user_1->name . '</th>
                                    </tr>
                                </table>
                            </div>';
            } else {
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
            header("Content-Disposition: attachment; filename=request_for_quotations_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_rfq_no = $this->input->get('filter_rfq_no');
        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_supplier = $this->input->get('filter_supplier');
        $filter_item_familys = $this->input->get('filter_item_familys');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom, d.name as supplier_name');
        $this->db->from('request_for_quotations a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', "b.item_family_id = c.id");
        $this->db->join('suppliers d', 'a.supplier_id=d.id', 'left');
        $this->db->where('a.deleted', 0);
        if (!in_array($this->session->username, ["PURCHASE-03", "Purchase-01", "PURCHASE-02", "admin", "hariom_bhardwaj", "ITADMIN"])) {
            $this->db->where('a.created_by', $this->session->username);
        }
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.rfq_date >=', $filter_from);
            $this->db->where('a.rfq_date <=', $filter_to);
        }
        $this->db->like('a.rfq_no', $filter_rfq_no);
        $this->db->like('a.item_rm_id', $filter_item_rm);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('c.id', $filter_item_familys);
        $this->db->order_by('a.rfq_date', 'DESC');
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
                                <small>REQUEST FOR QUOTATION</small>
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
                <th>Supplier ID</th>
                <th>Supplier Name</th>
                <th>RFQ No</th>
                <th>RFQ Date</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Description</th>
                <th>Uom</th>
                <th>MPQ</th>
                <th>MOQ</th>
                <th>Leadtime</th>
                <th>Price</th>
                <th>Created By</th>
                <th>Created Date</th>
                <th>Approved Next</th>
                <th>Approved By</th>
                <th>Approved Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            // if ($data['status'] == 0) {
            //     $status = "UN CONVERTED";
            // } else {
            //     $status = "CONVERTED";
            // }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['supplier_id'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['rfq_no'] . '</td>
                        <td>' . $data['rfq_date'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_rm_number'] . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['moq'] . '</td>
                        <td>' . $data['leadtime'] . '</td>
                        <td>' . $data['price'] . '</td>
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
