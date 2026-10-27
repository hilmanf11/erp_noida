<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sales_orders extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[customer_items.customer_id]');
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[customer_items.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('sales/sales_orders');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFg($customer_id, $plant)
    {
        $customer_id = base64_decode($customer_id);
        $plant = base64_decode($plant);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price, c.currency, b.uom, COALESCE(SUM(d.qty_del), 0) as delivery
            FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customers c ON a.customer_id = c.id
            LEFT JOIN delivery_orders d ON b.id = d.item_fg_id and d.status = 0 and c.id = d.customer_id and a.plant = d.plant
            WHERE a.customer_id = '$customer_id' and a.plant = '$plant' and (b.number LIKE '%$post%' or b.name LIKE '%$post%') and (a.approved_to = '' or a.approved_to is null) GROUP BY b.number");
        echo json_encode($send);
    }

    public function readItems($customer_id, $sales_order_no)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, a.qty
            FROM sales_orders a 
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customers c ON a.customer_id = c.id
            WHERE a.customer_id = '$customer_id' and a.sales_order_no = '$sales_order_no' and a.status = 0 and (b.number LIKE '%$post%' or b.name LIKE '%$post%') ");
        echo json_encode($send);
    }

    public function readSalesOrder($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $send = $this->crud->query("SELECT DISTINCT sales_order_no, sales_order_date FROM sales_orders WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_orders WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function number($id_menu)
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

            $sqlGetID	= $this->db->query("SELECT max(sales_order_no) as kode FROM sales_orders WHERE sales_order_no like '%$whereDate%'");
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

    // public function number($customer_id, $sales_order_date)
    // {
    //     $datenow    = "SO" . base64_decode($customer_id) . date("ym", strtotime(base64_decode($sales_order_date)));
    //     $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%04s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -4);
    //         $urutan++;
    //         $autoID = sprintf("%04s", $urutan);
    //     }
    //     echo $datenow . $autoID;
    // }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_status = @base64_decode($get['filter_status']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name, COALESCE(SUM(c.qty_delivery), 0) as qty_delivery, SUM(a.qty) as qty_so, (SUM(a.qty) - COALESCE(SUM(c.qty_delivery), 0)) as qty_outstanding, SUM(total) as total_sub2");
            $this->db->from('sales_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join("(select item_fg_id, sales_order_no, SUM(qty) as qty_delivery from delivery_notes GROUP by sales_order_no, item_fg_id) c", 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id', 'left');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.sales_order_date >=', $filter_from);
                $this->db->where('a.sales_order_date <=', $filter_to);
            }
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.sales_order_no');
            if($filter_status == "0"){
                $this->db->having('(SUM(a.qty) - COALESCE(SUM(c.qty_delivery), 0)) > 0');
            }
            $this->db->order_by('a.status', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));
            $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.number_customer, COALESCE(c.qty_delivery, 0) as qty_delivery, (a.qty_so - COALESCE(c.qty_delivery, 0)) as qty_outstanding');
            $this->db->from('(select *, SUM(qty) as qty_so from sales_orders GROUP by sales_order_no, item_fg_id) a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join("(select sales_order_no, item_fg_id, SUM(qty) as qty_delivery from delivery_notes GROUP by sales_order_no, item_fg_id) c", 'a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id', 'left');
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->group_by('a.sales_order_no');
            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.customer_id');
            $this->db->group_by('a.plant');
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));

            $this->db->select('a.*, b.number_customer as item_fg_number, b.name as item_fg_name, SUM(a.qty) AS qty, COALESCE(c.qty_delivery, 0) AS delivery, (SUM(a.qty) - COALESCE(c.qty_delivery, 0)) as outstanding');
            $this->db->from('sales_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join("(select sales_order_no, item_fg_id, customer_id, plant, SUM(qty) as qty_delivery from delivery_notes GROUP by sales_order_no, item_fg_id) c", 'a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id AND a.customer_id = c.customer_id AND a.plant = c.plant', 'left');
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->group_by('a.sales_order_no');
            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.customer_id');
            $this->db->group_by('a.plant');
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']]);
            if (@$sales_orders->sales_order_no != "") {
                $send = $this->crud->update('sales_orders', ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']], $post);
                if(@$sales_orders->qty == $post['qty']){
                    $this->db->update('sales_orders', ["approved_to" => ""], ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']]);
                }
            } else {
                $send = $this->crud->create('sales_orders', $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function closeSo(){
        if ($this->input->post()) {
            $sales_order_no = $this->input->post('sales_order_no');
            $closing_reason = $this->input->post('closing_reason');

            $this->db->update('sales_orders', ["closing_reason" => $closing_reason, "closing_date" => date("Y-m-d"), "status" => 1], ["sales_order_no" => $sales_order_no]);
            echo json_encode(array("title" => "Close SO", "message" => "Success Closing this SO " . $sales_order_no, "theme" => "success"));
        }
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/image/sales_orders/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Pastikan ada file yang diunggah dari permintaan
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];

                // Validasi ekstensi file yang diunggah
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, or .png are allowed.']);
                    exit; // Menghentikan proses lebih lanjut jika ekstensi tidak valid
                }

                // Validasi ukuran file yang diunggah (maksimal 5MB)
                $maxFileSize = 2 * 1024 * 1024; // 5MB dalam bytes
                if ($file['size'] > $maxFileSize) {
                    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB yang diperbolehkan.']);
                    exit; // Menghentikan proses lebih lanjut jika ukuran terlalu besar
                }

                // Pastikan tidak ada error dalam proses upload
                if ($file['error'] === UPLOAD_ERR_OK) {
                    // Buat nama unik untuk file yang diunggah
                    $fileName = uniqid() . '_' . $file['name'];
                    $uploadPath = $uploadDir . $fileName;

                    // Pindahkan file dari temporary directory ke lokasi yang diinginkan
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        // File berhasil diunggah
                        echo json_encode(['success' => true, 'message' => 'File Upload Success.', 'filename' => $fileName]);
                    } else {
                        // Gagal menyimpan file
                        echo json_encode(['success' => false, 'message' => 'File Upload Failed.']);
                    }
                } else {
                    // Ada error dalam proses upload
                    echo json_encode(['success' => false, 'message' => 'Error while Upload.']);
                }
            } else {
                // File tidak ditemukan dalam permintaan
                echo json_encode(['success' => false, 'message' => 'File Not Found.']);
            }
        } else {
            // Metode request yang diperlukan adalah POST
            echo json_encode(['success' => false, 'message' => 'Metode request yang diperlukan adalah POST.']);
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sales_orders', $data);
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

        $total_sub = 0;
        $last_customer_order_no = "";
        $urutan_id = 0;
        $last_customer_id = "";
        $last_sales_order_no = "";
        $sales_order_date = date("Y-m-d");

        $datas = [];
        $total_per_sales_order = []; 

        for ($i = 4; $i <= $total_row; $i++) {
            $customer_id = $data->val($i, 2);
            $plant = $data->val($i, 3);
            $sales_order_date = $data->val($i, 4);
            $customer_order_no = $data->val($i, 5);
            $item_fg_number = $data->val($i, 6);
            $qty = $data->val($i, 7);
            $delivery_date = $data->val($i, 8);

            $item_fg = $this->crud->read('item_fg', [], ["number_customer" => $item_fg_number]);

            if (!empty($item_fg->number)) {
                // Skip baris kosong
                if (empty($customer_id) && empty($customer_order_no) && empty($item_fg_number) && empty($qty)) {
                    continue;
                }

                if ($last_customer_order_no == $customer_order_no) {
                    $customer_id = $last_customer_id;
                    $sales_order_no = $last_sales_order_no;
                    $urutan_id = $urutan_id;
                } else {
                    if ($last_customer_id != $customer_id) {
                        $datenow    = "SO" . $customer_id . date("ymd");
                        $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
                        $rowID      = $sqlGetID->row();
                        $kode       = $rowID->kode;
                        if ($kode == NULL) {
                            $autoID = sprintf("%03s", $kode + 1);
                            $urutan_id = 1;
                        } else {
                            $urutan = (int) substr($kode, -3);
                            $urutan++;
                            $autoID = sprintf("%03s", $urutan);
                            $urutan_id = $urutan;
                        }

                        $sales_order_no = $datenow . $autoID;
                    } else {
                        $datenow    = "SO" . $customer_id . date("ymd");
                        $sales_order_no = $datenow . sprintf("%03s", $urutan_id + 1);
                        $urutan_id++;
                    }
                }

                $cust_add = $this->crud->read('customer_address', [], ["customer_id" => $customer_id, "plant" => $plant]);
                $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id, "customer_id" => @$customer_id]);
                $price = $customer_items->price;
                $total = ($qty * $price);

                // Simpan total untuk setiap sales_order_no di array $total_per_sales_order
                if (isset($total_per_sales_order[$sales_order_no])) {
                    $total_per_sales_order[$sales_order_no] += $total;
                } else {
                    $total_per_sales_order[$sales_order_no] = $total;
                }

                $datas[] = array(
                    //excel
                    'customer_id' => $customer_id,
                    'customer_address_id' => @$cust_add->id,
                    'item_fg_id' => $item_fg->id,
                    'customer_order_no' => $customer_order_no,
                    'sales_order_no' => $sales_order_no,
                    'sales_order_date' => $sales_order_date,
                    'delivery_date' => $delivery_date,
                    'uom' => $item_fg->uom,
                    'qty' => $qty,
                    'outstanding' => $qty,
                    'price' => $customer_items->price,
                    "total" => $total,
                    'plant' => $plant,
                    'department' => $cust_add->department,
                    'attention_to' => $cust_add->contact_person,
                );

                $last_customer_order_no = $customer_order_no;
                $urutan_id = $urutan_id;
                $last_customer_id = $customer_id;
                $last_sales_order_no = $sales_order_no;
                
            }
        }

        // Tambahkan total_sub per sales_order_no ke array data
        foreach ($datas as &$data_row) {
            $sales_order_no = $data_row['sales_order_no'];
            if (isset($total_per_sales_order[$sales_order_no])) {
                $data_row['total_sub'] = $total_per_sales_order[$sales_order_no];
            }
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/sales_orders.txt');
    }
    
    public function uploadcreateFailed(){
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/sales_orders.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/sales_orders.txt";
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
            $data = $this->input->post('data'); //field excel

            $customer_id = $data['customer_id'];
            $item_fg_id = $data['item_fg_id'];

            $customers = $this->crud->read('customers', [], ["id" => $data['customer_id']]);
            $customer_item = $this->db->query("SELECT * FROM customer_items WHERE customer_id = '$customer_id' and item_fg_id = '$item_fg_id' and approved_to != ''");
            $row_customer_item = $customer_item->row();
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);

            // Check for duplicate sales order by customer_order_no AND delivery_date
            $sales_orders = $this->crud->read('sales_orders', [], [
                "customer_order_no" => $data['customer_order_no'],
                "item_fg_id" => $data['item_fg_id'],
                "delivery_date" => $data['delivery_date'] // Include delivery date in the check
            ]);

            if (empty($customers)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customers ID " . $data['customer_id'] . " Not Found", "theme" => "error"));
            } else if (empty($item_fg)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . @$item_fg->number_customer . " Not Found in Master Item", "theme" => "error"));
            } else if (empty($row_customer_item)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . @$item_fg->number_customer . " Not Found in Customer Contract", "theme" => "error"));
            } else if (!empty($sales_orders->sales_order_no)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product ID " . @$item_fg->number_customer . " and Customer Order No " . $data['customer_order_no'] . " with Delivery Date " . $data['delivery_date'] . " Duplicated", "theme" => "error"));
            } else if (empty($data['customer_id'])) {
                $this->uploadcreateFailed();
            } else {
                $dataFinal = array(
                    //field        //excel
                    "customer_id" => $data['customer_id'],
                    "sales_order_date" => $data['sales_order_date'],
                    "sales_order_no" => $data['sales_order_no'],
                    "delivery_date" => $data['delivery_date'],
                    "customer_address_id" => $data['customer_address_id'],
                    "plant" => $data['plant'],
                    "attention_to" => $data['attention_to'],
                    "department" => $data['department'],
                    "customer_order_no" => $data['customer_order_no'],
                    "item_fg_id" => $customer_items->item_fg_id,
                    "qty" => $data['qty'],
                    "uom" => $data['uom'],
                    "outstanding" => $data['qty'],
                    "currency" => $customers->currency,
                    "price" => $customer_items->price,
                    "total" => $data['total'],
                    "total_sub" => $data['total_sub'],
                    "total_grand" => $data['total_sub'],
                );
                $send = $this->crud->create('sales_orders', $dataFinal);
                echo json_encode(array("title" => "Success", "message" => "Data successfully created with Sales Order No: " . $data['sales_order_no'], "theme" => "success"));
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_status = @base64_decode($get['filter_status']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.number_customer, 
            d.name as customer_name,
            COALESCE(c.qty_delivery, 0) as qty_delivery, (a.qty_so - COALESCE(c.qty_delivery, 0)) as qty_outstanding');
        $this->db->from('(select *, SUM(qty) as qty_so from sales_orders GROUP by sales_order_no, item_fg_id) a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers d', 'a.customer_id = d.id');
        $this->db->join("(select sales_order_no, item_fg_id, SUM(qty) as qty_delivery from delivery_notes WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to' GROUP by sales_order_no, item_fg_id) c", 'a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id', 'left');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.sales_order_date >=', $filter_from);
            $this->db->where('a.sales_order_date <=', $filter_to);
        }
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('a.status', $filter_status);
        $this->db->group_by('a.sales_order_no');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.plant');
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('a.sales_order_no', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
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
                <h3>SALES ORDER</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Plant</th>
                <th>Customer Order No</th>
                <th>Forecast No</th>
                <th>Sales Order No</th>
                <th>Sales Order Date</th>
                <th>Delivery Date</th>
                <th>Remarks</th>
                <th>Product No EBWS</th>
                <th>Product No Customer</th>
                <th>Description</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Delivery</th>
                <th>Outstanding</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Total</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['status'] == 0){
                $status = "OPEN";
            }else{
                $status = "CLOSE";
            }

            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['plant'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['forecast_no'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['sales_order_date'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty_so'] . '</td>
                        <td>' . $data['qty_delivery'] . '</td>
                        <td>' . $data['qty_outstanding'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['price'] . '</td>
                        <td>' . $data['total'] . '</td>
                        <td>' . $status . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
