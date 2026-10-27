<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_confirm extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        // $this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[2]|max_length[30]|is_unique[purchase_confirm.item_rm_id]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/delivery_confirm');
        } else {
            redirect('error_access');
        }
    }

    public function readSupplier(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $data = $this->crud->query("SELECT DISTINCT b.id, b.number, b.name FROM purchase_confirm a JOIN suppliers b ON a.supplier_id = b.id WHERE a.status = 0 and b.name like '%$post%' ORDER BY b.name asc");
        echo json_encode($data);
    }

    function readInvoice(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($this->input->get('supplier_id'));
        $datas = $this->crud->query("SELECT invoice_no, invoice_date, awb_no, awb_date FROM purchase_confirm WHERE status = 0 and supplier_id = '$supplier_id' and (invoice_date like '%$post%' or invoice_no like '%$post%') GROUP BY invoice_no, invoice_date");
        echo json_encode($datas);
    }


    function readPono(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $invoice_no = base64_decode($this->input->get('invoice_no'));
        $supplier_id = base64_decode($this->input->get('supplier_id'));
        $datas = $this->crud->query("SELECT a.po_no, a.po_date FROM purchase_confirm a WHERE a.status = 0 and a.invoice_no = '$invoice_no' and a.supplier_id = '$supplier_id' GROUP BY a.po_no");
        echo json_encode($datas);
    }

    public function readProducts($po_no){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $po_no = base64_decode($po_no);

        $data = $this->crud->query("SELECT b.id, b.number, b.name, a.qty as qty_po, 
                (a.qty - COALESCE(SUM(c.delivery), 0)) as qty, 
                b.uom,
                a.price,
                e.currency,
                a.delivery_date, 
                DATE_FORMAT(now(), '%Y-%m-%d') as today, 
                (a.qty - COALESCE(SUM(c.delivery), 0)) as delivery, 
                COALESCE(SUM(c.delivery), 0) as actual,
                (a.price * (a.qty - COALESCE(SUM(c.delivery), 0))) as total
            FROM purchase_orders a 
            JOIN item_rm b ON a.item_rm_id = b.id
            JOIN suppliers e ON a.supplier_id = e.id
            LEFT JOIN purchase_confirm c ON a.po_no = c.po_no and a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id and c.status IN (0,1)
            LEFT JOIN convertions d ON a.item_rm_id = d.item_rm_id
            WHERE a.po_no = '$po_no' and a.status = 0 and (b.number LIKE '%$post%' and b.id like '%$post%') and (a.approved_to = '' or a.approved_to is null)
            GROUP BY a.item_rm_id
            ORDER BY b.number asc");
        echo json_encode($data);
    }

    public function filterSupplier(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $data = $this->crud->query("SELECT DISTINCT b.id, b.number, b.name FROM purchase_confirm a JOIN suppliers b ON a.supplier_id = b.id WHERE b.name like '%$post%' ORDER BY b.name asc");
        echo json_encode($data);
    }

    public function filterProdfam($supplier_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($supplier_id);
        $data = $this->crud->query("SELECT DISTINCT c.id, c.number, c.name FROM purchase_confirm a JOIN item_rm b ON a.item_rm_id = b.id JOIN item_familys c ON b.item_family_id = c.id WHERE a.supplier_id = '$supplier_id' ORDER BY c.number asc");
        echo json_encode($data);
    }

    public function filterInvoiceNo($supplier_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($supplier_id);
        $data = $this->crud->query("SELECT DISTINCT invoice_no FROM purchase_confirm WHERE supplier_id = '$supplier_id' ORDER BY invoice_no asc");
        echo json_encode($data);
    }

    public function filterPo($supplier_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($supplier_id);
        $data = $this->crud->query("SELECT DISTINCT po_no FROM purchase_confirm WHERE supplier_id = '$supplier_id' ORDER BY po_no asc");
        echo json_encode($data);
    }

    public function checkInvoice(){
        $invoice_no = trim($this->input->post("invoice_no"));
        $supplier_id = trim($this->input->post("supplier_id"));
        $invoice_date = trim($this->input->post("invoice_date"));

        // $read = $this->crud->query("SELECT id FROM purchase_order_receipts WHERE bc_document = '$invoice_no' and supplier_id = '$supplier_id' and `status` in ('0','1')");
        // $read2 = $this->crud->reads("purchase_confirm", [], ["invoice_no" => $invoice_no, "supplier_id" => $supplier_id]);
        $read2 = $this->crud->query("SELECT * FROM purchase_confirm WHERE invoice_no = '$invoice_no' and supplier_id = '$supplier_id' and status in ('0','1') ORDER BY invoice_date desc");
        $read3 = $this->crud->query("SELECT DISTINCT start_date, end_date FROM menus_no WHERE menus_id = '20240111000001' AND start_date <= '$invoice_date' AND end_date >= '$invoice_date'");
        
        if(count($read2) > 0){
            if(count($read3) > 0){
                $invoice_date_bf = @$read2[0]->invoice_date;
                $read4 = $this->crud->query("SELECT DISTINCT start_date, end_date FROM menus_no WHERE menus_id = '20240111000001' AND start_date <= '$invoice_date_bf' AND end_date >= '$invoice_date_bf'");
                if(@$read3[0]->start_date == @$read4[0]->start_date){
                    die("NG");
                }else{
                    die("OK");
                }
            }else{
                die("NG");
            }
        }else{
            die("OK");
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_status = base64_decode($this->input->get('filter_status'));
            $filter_po_no = base64_decode($this->input->get('filter_po_no'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));
            $filter_po_date = base64_decode($this->input->get('filter_po_date'));
            $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            // $this->db->select("a.*, b.name as supplier_name, c.number as item_rm_number, c.name as item_rm_name, d.name as item_family_name, e.receipt");
            $this->db->select("a.*, (CASE WHEN a.uom = '' THEN c.uom ELSE a.uom END) as uom_confirm, b.name as supplier_name, c.number as item_rm_number, c.name as item_rm_name, d.name as item_family_name, f.qty_confirm, (e.qty + (f.qty_confirm - a.delivery)) as qty_remain, e.qty as qty_po, b.currency, (e.qty + (f.qty_confirm - a.delivery) - a.delivery) as balance");
            $this->db->from('purchase_confirm a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys d', 'c.item_family_id = d.id');
            $this->db->join('purchase_orders e', 'a.supplier_id = e.supplier_id and a.item_rm_id = e.item_rm_id and a.po_no = e.po_no');
            $this->db->join("(SELECT supplier_id, item_rm_id, po_no, SUM(delivery) as qty_confirm FROM purchase_confirm GROUP BY supplier_id, item_rm_id, po_no) f", 'a.supplier_id = f.supplier_id and a.item_rm_id = f.item_rm_id and a.po_no = f.po_no');
            // $this->db->join("(SELECT po_no, bc_document, item_rm_id, supplier_id, SUM(qty_receipt) as receipt FROM purchase_order_receipts GROUP BY po_no, bc_document, item_rm_id, supplier_id) e", 'a.po_no = e.po_no and a.invoice_no = e.bc_document and a.item_rm_id = e.item_id and a.supplier_id = e.supplier_id', 'left');            
            if ($filter_from != "" and $filter_to != "") {
                $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') >=", $filter_from);
                $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') <=", $filter_to);
            }
            if($filter_po_date != ""){
                $this->db->where('a.po_date', $filter_po_date);
            }
            if($filter_invoice_no != ""){
                $this->db->where('a.invoice_no', $filter_invoice_no);
            }
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.po_no', $filter_po_no);
            $this->db->like('c.item_family_id', $filter_product_family);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->group_by('a.id');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.supplier_id', 'ASC');
            $this->db->order_by('a.delivery_no', 'ASC');
            $this->db->order_by('a.item_rm_id', 'ASC');
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

    // UPDATE DATA
    // public function datatableUpdates()
    // {
    //     if ($this->input->get()) {
    //         $delivery_no = base64_decode($this->input->get('delivery_no'));

    //         $this->db->select("a.*, b.name as supplier_name, c.number as item_rm_number, '0' as actual, (a.qty - a.delivery) as balance, c.name as item_rm_name, d.name as item_family_name");
    //         $this->db->from('purchase_confirm a');
    //         $this->db->join('suppliers b', 'a.supplier_id = b.id');
    //         $this->db->join('item_rm c', 'a.item_rm_id = c.id');
    //         $this->db->join('item_familys d', 'c.item_family_id = d.id');
    //         $this->db->where('a.delivery_no', $delivery_no);
    //         $records = $this->db->get()->result_array();

    //         echo json_encode($records);
    //     }
    // }

    //UPLOAD IMAGE
    public function uploadAttachment($file_bf = ""){
        $file				= $_FILES['file']['name'];
        $extension			= explode('.', $file);
        $extension_end		= strtolower(end($extension));
        $size				= $_FILES['file']['size'];
        $temporary 			= $_FILES['file']['tmp_name'];
        $fileName           = time() . "." . $extension_end;

        if (in_array($extension_end, array('pdf','jpg','png','jpeg')) === true) {
            if ($size < 1000000) {
                if ($file != "") {
                    @unlink("assets/documents/delivery_confirm/" . base64_decode($file_bf));
                    @move_uploaded_file($temporary, 'assets/documents/delivery_confirm/' . $fileName);
                    echo json_encode(array('msg' => 'Upload Success', "file" => $fileName));
                }
            }else{
                echo json_encode(array('msg' => 'Your file size is too large, a maximum of 1 MB', 'file' => base64_decode($file_bf)));
            }
        }else{
            echo json_encode(array('msg' => 'error', 'text' =>  'Your file format is not accepted', 'file' => base64_decode($file_bf)));
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $id = $post['id'];
            $dataFinals = array(
                "supplier_id" => $post['supplier_id'],
                "delivery_no" => $post['delivery_no'],
                "invoice_no" => trim($post['invoice_no']),
                "invoice_date" => $post['invoice_date'],
                "awb_no" => $post['awb_no'],
                "awb_date" => $post['awb_date'],
                "attachment" => $post['attachment'],
                "po_no" => $post['po_no'],
                "po_date" => $post['po_date'],
                "item_rm_id" => $post['item_rm_id'],
                "uom" => $post['uom'],
                "eta" => $post['eta'],
                "etd" => $post['etd'],
                "qty" => $post['qty'],
                "price" => $post['price'],
                "total" => $post['total'],
                "delivery" => $post['delivery'],
            );
 
            if($id != ""){
                if($post['balance'] > 0){
                    $status = 0;
                }else{
                    $status = 1;
                }

                $send = $this->crud->update('purchase_confirm', ["id" => $id], ["delivery" => $post['delivery'], "invoice_date" => $post['invoice_date'], "awb_no" => $post['awb_no'], "awb_date" => $post['awb_date']]);
                $this->db->update('purchase_orders', ["status" => $status], ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']]);
                echo $send;
            }else{
                if($post['balance'] > 0){
                    $status = 0;
                }else{
                    $status = 1;
                }

                $this->db->update('purchase_orders', ["status" => $status], ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']]);
                $send = $this->crud->create('purchase_confirm', $dataFinals);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            // $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('purchase_confirm', ["id" => $post['id']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_confirm', $data);
        $this->db->update('purchase_orders', ["status" => 0], ["po_no" => $data['po_no'], "item_rm_id" => $data['item_rm_id']]);
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
                'delivery_no' => $data->val($i, 2),
                'invoice_no' => $data->val($i, 3),
                'invoice_date' => $data->val($i, 4),
                'awb_no' => $data->val($i, 5),
                'awb_date' => $data->val($i, 6),
                'po_no' => $data->val($i, 7),
                'supplier_id' => $data->val($i, 8),
                'item_rm_id' => $data->val($i, 9),
                'etd' => $data->val($i, 10),
                'delivery' => $data->val($i, 11)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/delivery_confirm.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/delivery_confirm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/delivery_confirm.txt";
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
            $post = $this->input->post('data');

            $item_rm = $this->crud->read("item_rm", [], ["id" => $post['item_rm_id']]);
            $supplier = $this->crud->read("suppliers", [], ["id" => $post['supplier_id']]);
            $purchase_order = $this->crud->read("purchase_orders", [], ["item_rm_id" => $post['item_rm_id'], "po_no" => $post['po_no'], "supplier_id" => $post['supplier_id']]);
            $purchase_confirm = $this->crud->read("purchase_confirm", [], ["item_rm_id" => $post['item_rm_id'], "po_no" => $post['po_no'], "supplier_id" => $post['supplier_id'], "invoice_no" => $post['invoice_no']]);

            if(empty($item_rm)){
                echo json_encode(array("title" => "Not Found", "message" => "Part No ".$post['item_rm_id']." Not Found in Master Items", "theme" => "error"));
            }elseif(empty($supplier)){
                echo json_encode(array("title" => "Not Found", "message" => "Supplier ID ".$post['supplier_id']." Not Found in Master Suppliers", "theme" => "error"));
            }elseif(empty($purchase_order)){
                echo json_encode(array("title" => "Not Found", "message" => "PO No ".$post['po_no']." Not Match", "theme" => "error"));
            }elseif(!empty($purchase_confirm)){
                echo json_encode(array("title" => "Duplicate", "message" => "Part No ".$post['item_rm_id']." and ".$post['po_no']." is already Exists", "theme" => "error"));
            }else{
                if(@$purchase_order->qty > $post['delivery']){
                    $status = "0";
                }elseif(@$purchase_order->qty == $post['delivery']){
                    $status = "1";
                }else{
                    die(json_encode(array("title" => "More Qty", "message" => "Part No ".$post['item_rm_id']." in the PO ".$post['po_no']." qty is less than qty your input", "theme" => "error")));
                }

                $postFinal = array(
                    "supplier_id" => $post['supplier_id'],
                    "delivery_no" => $post['delivery_no'],
                    "invoice_no" => $post['invoice_no'],
                    "invoice_date" => $post['invoice_date'],
                    "awb_no" => $post['awb_no'],
                    "awb_date" => $post['awb_date'],
                    "po_no" => $post['po_no'],
                    "po_date" => $purchase_order->po_date,
                    "item_rm_id" => $post['item_rm_id'],
                    "uom" => $item_rm->uom,
                    "eta" => $purchase_order->delivery_date,
                    "etd" => $post['etd'],
                    "qty" => $purchase_order->qty,
                    "price" => $purchase_order->price,
                    "total" => $purchase_order->total,
                    "delivery" => $post['delivery'],
                );
                
                $this->db->update('purchase_orders', ["status" => $status], ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']]);
                $send = $this->crud->create('purchase_confirm', $postFinal);
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=schedule_delivery_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status = base64_decode($this->input->get('filter_status'));
        $filter_po_no = base64_decode($this->input->get('filter_po_no'));
        $filter_product_family = base64_decode($this->input->get('filter_product_family'));
        $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));
        $filter_po_date = base64_decode($this->input->get('filter_po_date'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));

        $this->db->select('a.*, b.name as supplier_name, c.number as item_rm_number, c.name as item_rm_name, d.name as item_family_name');
        $this->db->from('purchase_confirm a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') >=", $filter_from);
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') <=", $filter_to);
        }
        if($filter_po_date != ""){
            $this->db->where('a.po_date', $filter_po_date);
        }
        if($filter_invoice_no != ""){
            $this->db->where('a.invoice_no', $filter_invoice_no);
        }
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('c.item_family_id', $filter_product_family);
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->group_by('a.id');
        $this->db->order_by('a.supplier_id', 'ASC');
        $this->db->order_by('a.delivery_no', 'ASC');
        $this->db->order_by('a.item_rm_id', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->logo . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>DELIVERY CONFIRMATION</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Status</th>
                <th>Supplier Name</th>
                <th>Delivery No</th>
                <th>Invoice No</th>
                <th>PO No</th>
                <th>PO Date</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Description</th>
                <th>Product Family</th>
                <th>Uom</th>
                <th>Qty PO</th>
                <th>Plan Delivery</th>
                <th>ETD</th>
                <th>ETA</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['status'] == "0"){
                $status = "OPEN";
            }else{
                $status = "CLOSE";
            }
            
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $status . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['delivery_no'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['po_no'] . '</td>
                            <td>' . $data['po_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td>' . $data['item_rm_name'] . '</td>
                            <td>' . $data['item_family_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['qty'] . '</td>
                            <td>' . $data['delivery'] . '</td>
                            <td>' . $data['etd'] . '</td>
                            <td>' . $data['eta'] . '</td>
                            <td>' . $data['created_by'] . '</td>
                            <td>' . $data['created_date'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}