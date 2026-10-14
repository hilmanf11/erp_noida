<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class item_fg extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[item_fg.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/item_fg');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads($status = "")
    {
        //master/bom
        //master/wos
        //master/customers
        //master/customer_items
        if($status == ""){
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->crud->query("SELECT * FROM item_fg WHERE (`number` like '%$post%' or number_customer like '%$post%' or `name` like '%$post%') and approved_to = '' and status = 0");
            echo json_encode($send);
        }else{
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->crud->query("SELECT * FROM item_fg WHERE (`number` like '%$post%' or number_customer like '%$post%' or `name` like '%$post%')");
            echo json_encode($send);
        }
    }

    public function readSupplement()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_fg WHERE customer_id = 'CUS-01-06' and (`number` like '%$post%' or number_customer like '%$post%' or `name` like '%$post%')");
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
            $this->db->select('a.*, b.number as hsn_number, b.name as hsn_name, b.igst, b.sgst, b.cgst, c.number as customer_number, c.name as customer_name, d.name as harnes_type_name, e.total_cct');
            $this->db->from('item_fg a');
            $this->db->join('hsn b', 'a.hsn_code = b.number', 'left');
            $this->db->join('customers c', 'a.customer_id = c.id', 'left');
            $this->db->join('harnes_types d', 'd.number = a.harnes_type', 'left');
            $this->db->join("(SELECT item_fg_id, COUNT(id) as total_cct FROM wos GROUP BY item_fg_id) e", 'e.item_fg_id = a.id', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "hsn_number") {
                        $this->db->like("b.number", $filter->value);
                    } elseif ($filter->field == "hsn_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "customer_name") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "harnes_type_name") {
                        $this->db->like("d.name", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
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

    //AUTO ID
    public function autoid()
    {
        $post = $this->input->post();
        $customer_number = $post['customer_number'];
        $harnes_type = $post['harnes_type'];
        $revision = $post['revision'];

        $combine = $customer_number . $harnes_type;
        $sql = $this->db->query("SELECT max(SUBSTRING(`number`, 5, 4)) as kode FROM item_fg WHERE SUBSTRING(`number`, 1, 4) = '$combine' and SUBSTRING(`number`, 9, 2) = '$revision'");
        $row = $sql->row();
        if ($row->kode == "") {
            $kode = 0;
        } else {
            $kode = $row->kode;
        }
        $autoid = $combine . sprintf("%04s", $kode + 1) . $revision;
        echo json_encode(array("code" => $autoid));
    }

    public function getSync()
    {
        error_reporting(0);
        
        $this->pg = $this->load->database('dummy', TRUE);
        $this->pg->select("item_id");
        $this->pg->from("mst_item");
        $this->pg->where("stscode_id", "01");
        $this->pg->where("pfm_id", "06");
        $items = $this->pg->get()->result_array();

        $rowItem = array();
        foreach ($items as $item) {
            $rowItem[] = $item['item_id'];
        }

        $this->db->select("*");
        $this->db->from("item_fg");
        $this->db->where('status', 0);
        $this->db->where_not_in('number_customer', $rowItem);
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = count($records);
        $result = array_merge($result, $records);

        die(json_encode($result));
    }

    public function createSync()
    {
        $data = $this->input->post('data');

        $this->dummy = $this->load->database('dummy', TRUE);
        $this->dummy->select("item_id");
        $this->dummy->from("mst_item");
        $this->dummy->where("stscode_id", "01");
        $this->dummy->where("item_id", $data['number']);
        $read = $this->dummy->get()->result_array();

        if($data['uom'] == "METER"){
            $uom = "01";
        }elseif($data['uom'] == "PIECES"){
            $uom = "02";
        }elseif($data['uom'] == "SET"){
            $uom = "03";
        }elseif($data['uom'] == "GRAM"){
            $uom = "04";
        }elseif($data['uom'] == "LITER"){
            $uom = "05";
        }elseif($data['uom'] == "KILOGRAM"){
            $uom = "06";
        }else{
            $uom = "02";
        }

        $dataFinal = array(
            "item_id" => @$data['number_customer'],
            "um_id" => @$uom,
            "item_name" => @$data['name'],
            "item_cct" => @$data['cct'],
            "item_regdate" => date("Y-m-d"),
            "stscode_id" => "01",
            "type_id" => "01",
            "pfm_id" => "06",
            "cust_id" => "1",
            "item_minoq" => $data['moq'],
            "item_muloq" => $data['mpq'],
            "item_user" => $this->session->name,
            "lotqty" => $data['qty_box'],
            "type_item" => "Domestic",
            "harness_type" => 1,
        );

        // if(empty($item_family)){}
        if (empty($read)) {
            $this->dummy->insert('mst_item', $dataFinal);
            echo json_encode(array("title" => "Success", "message" => "Create Data Successfully", "theme" => "success"));
        } else {
            echo json_encode(array("title" => "Duplicate", "message" => "Duplicated", "theme" => "error"));
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                $attachment = $this->crud->upload('attachment', ["pdf"], 'assets/documents/item_fg/');
                $foto = $this->crud->upload('foto', ["jpg","png","jpeg"], 'assets/image/item_fg/');
                $postFinal = array_merge($post, ["attachment" => $attachment, "foto" => $foto]);

                $send   = $this->crud->create('item_fg', $postFinal);
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
            $attachment = $this->crud->upload('attachment', ["pdf"], 'assets/documents/item_fg/', ["id" => $id], "item_fg", "attachment");
            $foto = $this->crud->upload('foto', ["jpg","png","jpeg"], 'assets/image/item_fg/', ["id" => $id], "item_fg", "foto");
            $postFinal = array_merge($post, ["attachment" => $attachment, "foto" => $foto]);
            $send = $this->crud->update('item_fg', ["id" => $id], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('item_fg', $data);
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
                //excel
                'number_customer' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'alias' => $data->val($i, 4),
                'customer_number' => $data->val($i, 5),
                'harnes_type' => $data->val($i, 6),
                'control_id' => $data->val($i, 7),
                // 'boxs' => $data->val($i, 9),
                'lot' => $data->val($i, 8),
                'polybag' => $data->val($i, 9),
                'box_label' => $data->val($i, 10),
                'ng_ration' => $data->val($i, 11),
                'is_no' => $data->val($i, 12),
                'weight' => $data->val($i, 13),
                // 'color' => $data->val($i, 16),
                'leadtime' => $data->val($i, 14),
                'mpq' => $data->val($i, 15),
                'moq' => $data->val($i, 16),
                'uom' => $data->val($i, 17),
                'qty_box' => $data->val($i, 18),
                'box_sub' => $data->val($i, 19),
                'hsn_code' => $data->val($i, 20),
                'account_number' => $data->val($i, 21),
                // 'account_name' => $data->val($i, 22),
                'logo' => $data->val($i, 22),
                'type' => $data->val($i, 23),
                'revision' => $data->val($i, 24),
                'status' => $data->val($i, 25)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/item_fg.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_fg.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_fg.txt";
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

            //Cek Process Number          //table       //field        //field excel
            $item_fg = $this->crud->read('item_fg', [], ["number_customer" => $data['number_customer']]);
            $customer = $this->crud->read('customers', [], ["number" => $data['customer_number']]);
            $harnes_type = $this->crud->read('harnes_types', [], ["number" => $data['harnes_type']]);
            $hsn = $this->crud->read('hsn', [], ["number" => $data['hsn_code']]);
            $account_coa = $this->crud->read('account_coa', [], ["account_number" => $data['account_number']]);
            $revision = $data['revision'];

            if (empty($hsn->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " HSN Code " . $data['hsn_code'] . " Not Found", "theme" => "error"));
            } elseif (empty($customer->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer Code " . $data['customer_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($harnes_type->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Harnes Type Code " . $data['harnes_type'] . " Not Found", "theme" => "error"));
            } elseif (empty($account_coa->account_name)) {
                echo json_encode(array("title" => "Not Found", "message" => "COA No " . $data['account_number'] . " Not Found", "theme" => "error"));
            } elseif (in_array($data['revision'], ["01","02","03","04","05","06","07","08","09","10"])) {
                echo json_encode(array("title" => "Failed", "message" => "Revision Format " . $data['revision'] . " Is Wrong", "theme" => "error"));
            } elseif (!empty($item_fg->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $combine = $data['customer_number'] . $data['harnes_type'];
                $sql = $this->db->query("SELECT max(SUBSTRING(`number`, 5, 4)) as kode FROM item_fg WHERE SUBSTRING(`number`, 1, 4) = '$combine' and SUBSTRING(`number`, 9, 2) = '$revision'");
                $row = $sql->row();
                if ($row->kode == "") {
                    $kode = 0;
                } else {
                    $kode = $row->kode;
                }
                $autoid = $combine . sprintf("%04s", $kode + 1) . $data['revision'];

                $dataFinal = array(
                    //field
                    "number" => $autoid,
                    "name" => $data['name'],
                    "number_customer" => $data['number_customer'],
                    "alias"=> $data['alias'],
                    "customer_id" => $customer->id,
                    "harnes_type" => $data['harnes_type'],
                    "control_id" => $data['control_id'],
                    "lot" => $data['lot'],
                    "polybag" => $data['polybag'],
                    "box_label" => $data['box_label'],
                    "ng_ration" => $data['ng_ration'],
                    "is_no" => $data['is_no'],
                    "weight" => $data['weight'],
                    "leadtime" => $data['leadtime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "uom" => $data['uom'],
                    "qty_box" => $data['qty_box'],
                    "box_sub" => $data['box_sub'],
                    "hsn_code" => $data['hsn_code'],
                    // "gst_rate" => @$hsn->gst_rate,
                    "account_number" => $data['account_number'],
                    "account_name" => $account_coa->account_name,
                    "logo" => $data['logo'],
                    "type" => $data['type'],
                    "revision" => $data['revision'],
                    "status" => $data['status'],
                );

                $send   = $this->crud->create('item_fg', $dataFinal);
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
            header("Content-Disposition: attachment; filename=item_fg_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as hsn_number, b.name as hsn_name, b.igst, b.sgst, b.cgst, c.number as customer_number, c.name as customer_name, d.name as harnes_type_name, e.total_cct');
        $this->db->from('item_fg a');
        $this->db->join('hsn b', 'a.hsn_code = b.number', 'left');
        $this->db->join('customers c', 'a.customer_id = c.id', 'left');
        $this->db->join('harnes_types d', 'd.number = a.harnes_type', 'left');
        $this->db->join("(SELECT item_fg_id, COUNT(id) as total_cct FROM wos GROUP BY item_fg_id) e", 'e.item_fg_id = a.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#item_fg {border-collapse: collapse;width: 100%;font-size: 12px;}#item_fg td, #item_fg th {border: 1px solid #ddd;padding: 2px;}#item_fg tr:nth-child(even){background-color: #f2f2f2;}#item_fg tr:hover {background-color: #ddd;}#item_fg th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER ITEM FINISH GOOD</h3>
            </div>
        </center>
        
        <table id="item_fg" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No EBWS</th>
                <th>Product No Customer</th>
                <th>Description</th>
                <th>Product No Invoice</th>
                <th>CCT Qty</th>
                <th>Customer</th>
                <th>Harnes Type</th>
                <th>Control No</th>
                <th>Polybag Label</th>
                <th>Box Label</th>
                <th>Lot<br>Production</th>
                <th>Weight (Gram)</th>
                <th>Prod. Leadtime (Day)</th>
                <th>MPQ</th>
                <th>MOQ</th>
                <th>Uom</th>
                <th>Qty/Box</th>
                <th>Safety Stock %</th>
                <th>HSN No</th>
                <th>Type</th>
                <th>COA No</th>
                <th>COA Name</th>
                <th>Revision</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td style="mso-number-format:\@;">' . $data['number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['name'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                        <td>' . $data['alias'] . '</td>
                        <td>' . $data['cct'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['harnes_type_name'] . '</td>
                        <td>' . $data['control_id'] . '</td>
                        <td>' . $data['polybag'] . '</td>
                        <td>' . $data['box_label'] . '</td>
                        <td>' . $data['lot'] . '</td>
                        <td>' . $data['weight'] . '</td>
                        <td>' . $data['leadtime'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['moq'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty_box'] . '</td>
                        <td>' . $data['safety_stock'] . '</td>
                        <td>' . $data['hsn_code'] . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['account_name'] . '</td>
                        <td>' . $data['revision'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
