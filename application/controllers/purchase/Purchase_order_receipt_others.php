<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order_receipt_others extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_order_receipt_others');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $receipt_no = base64_decode($this->input->get('receipt_no'));
        $supplier_id = base64_decode($this->input->get('supplier_id'));
        $item_rm_number = base64_decode($this->input->get('item_rm_number'));

        //Select Query
        $this->db->select('a.*, b.number, b.name, b.uom, c.name as item_family_name, f.mpq');
        $this->db->from('purchase_order_receipt_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        // $this->db->join('supplier_items d', 'a.item_rm_id = d.item_rm_id');
        // $this->db->join('suppliers e', 'd.supplier_id = e.id');
        $this->db->join('supplier_items f', 'a.item_rm_id = f.item_rm_id and a.supplier_id = f.supplier_id');
        // $this->db->join("(select distinct receipt_id FROM purchase_order_labels) f", 'f.receipt_id != a.receipt_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        if ($receipt_no != "") {
            $this->db->where('a.receipt_no', $receipt_no);
        }
        if ($supplier_id != "") {
            $this->db->like('d.supplier_id', $supplier_id);
        }
        if ($item_rm_number != "") {
            $this->db->where('b.number', $item_rm_number);
        }
        $this->db->group_by('a.receipt_no');
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readPoNo($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT po_no FROM purchase_order_receipt_others WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readReceipt($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipt_others WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readDocno($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT bc_document FROM purchase_order_receipt_others WHERE supplier_id = '$supplier_id' GROUP BY bc_document ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReceiptNo()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipt_others WHERE status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readSupplier()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT b.id, b.number, b.name FROM purchase_order_receipt_others a JOIN suppliers b ON a.supplier_id = b.id WHERE a.status = '0' and b.name like '%$post%' GROUP BY a.supplier_id ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function receipt_no($id_menu, $receipt_date)
    {
        // $datenow = date("Y-m-d");
        $datenow = base64_decode($receipt_date);
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(receipt_no) as kode FROM purchase_order_receipt_others WHERE receipt_no like '%$whereDate%'");
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

            echo $autoNo;
        }
    }

    public function receipt_id($receipt_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(receipt_id) as kode FROM purchase_order_receipt_others WHERE receipt_id like '%$receipt_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $receipt_no . "-" . $autoID;
    }

    public function checkData()
    {
        $receipt_no = $this->input->post('receipt_no');

        $sqlLabel = $this->db->query("SELECT * FROM purchase_order_receipt_others WHERE receipt_no ='$receipt_no'");
        $rowLabel = $sqlLabel->row();

        die(json_encode($rowLabel));
    }

    public function checkInvoice()
    {
        $invoice_no = trim($this->input->post("bc_document"));
        $supplier_id = trim($this->input->post("supplier_id"));
        $invoice_date = trim($this->input->post("bc_date"));

        $read2 = $this->crud->query("SELECT * FROM purchase_order_receipt_others WHERE bc_document = '$invoice_no' and supplier_id = '$supplier_id' and status != '2' and store = '1' ORDER BY bc_date desc");
        $read3 = $this->crud->query("SELECT DISTINCT start_date, end_date FROM menus_no WHERE menus_id = '20241128000002' AND start_date <= '$invoice_date' AND end_date >= '$invoice_date'");

        if (count($read2) > 0) {
            if (count($read3) > 0) {
                $invoice_date_bf = @$read2[0]->invoice_date;
                $read4 = $this->crud->query("SELECT DISTINCT start_date, end_date FROM menus_no WHERE menus_id = '20241128000002' AND start_date <= '$invoice_date_bf' AND end_date >= '$invoice_date_bf'");
                if (@$read3[0]->start_date == @$read4[0]->start_date) {
                    die(json_encode(array("theme" => "error")));
                } else {
                    die(json_encode(array("theme" => "success")));
                }
            } else {
                die(json_encode(array("theme" => "error")));
            }
        } else {
            die(json_encode(array("theme" => "success")));
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_supplier = $this->input->get('filter_supplier');
            $filter_item_rm = $this->input->get('filter_item_rm');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_receipt = $this->input->get('filter_receipt');
            $filter_doc_no = $this->input->get('filter_doc_no');

            if ($filter_item_rm != "") {
                $where_item_rm_id = "AND item_rm_id = '$filter_item_rm'";
            } else {
                $where_item_rm_id = "";
            }

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $id = $_POST['id'];
            if ($id === "0") {
                $this->db->select('a.po_no, a.total_store as store, a.receipt_no, a.receipt_date, a.awb_no, a.awb_date, a.bc_kind, a.bc_document, a.bc_aju, a.bc_date, b.id as supplier_id, b.name as supplier_name, a.total_receipt as qty_receipt, a.total_label as qty_label, a.status');
                $this->db->from("(SELECT *, SUM(store) as total_store, sum(qty_label) as total_label, sum(qty_receipt) as total_receipt FROM purchase_order_receipt_others WHERE receipt_date between '$filter_from' and '$filter_to' " . $where_item_rm_id . " GROUP BY receipt_no ORDER BY status asc) a");
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('purchase_order_others c', 'a.po_no = c.po_no and a.item_rm_id = c.item_rm_id');
                $this->db->where('a.deleted', 0);
                // if ($filter_from != "" and $filter_to != "") {
                //     $this->db->where('a.receipt_date >=', $filter_from);
                //     $this->db->where('a.receipt_date <=', $filter_to);
                // }
                if ($filter_supplier != "") {
                    $this->db->where('a.supplier_id', $filter_supplier);
                }
                if ($filter_item_rm != "") {
                    $this->db->where('c.item_rm_id', $filter_item_rm);
                }
                if ($filter_po_no != "") {
                    $this->db->where('a.po_no', $filter_po_no);
                }
                if ($filter_receipt != "") {
                    $this->db->where('a.receipt_no', $filter_receipt);
                }
                if ($filter_doc_no != "") {
                    $this->db->where('a.bc_document', $filter_doc_no);
                }
                $this->db->group_by('a.receipt_no');
                $this->db->order_by('a.receipt_no', 'DESC');
                // $this->db->order_by('a.status', 'ASC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    // $receipt_no = $record['receipt_no'];
                    // $purchase_order_label = $this->crud->query("SELECT receipt_id, count(id) as total_scan FROM scan_item_receipts WHERE receipt_id like '%$receipt_no%'");

                    $arr[] = array(
                        "id" => $record['receipt_no'],
                        // "grn_no" => $grn_no,
                        "store" => $record['store'],
                        "po_no" => $record['po_no'],
                        "bc_kind" => $record['bc_kind'],
                        "bc_document" => $record['bc_document'],
                        "bc_aju" => $record['bc_aju'],
                        "bc_date" => $record['bc_date'],
                        "receipt_no" => $record['receipt_no'],
                        "receipt_date" => $record['receipt_date'],
                        "awb_no" => $record['awb_no'],
                        "awb_date" => $record['awb_date'],
                        "supplier_id" => $record['supplier_id'],
                        "supplier_name" => $record['supplier_name'],
                        "qty_label" => $record['qty_label'],
                        // "total_scan" => $purchase_order_label[0]->total_scan,
                        "status" => $record['status'],
                        "state" => "closed",
                    );
                }
                //Mapping Data
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                    a.id as purchase_order_receipt_others_id, 
                    a.receipt_id as id, 
                    b.name as supplier_name, 
                    c.number as item_rm_number, 
                    c.name as item_rm_name, 
                    d.name as item_family_name, 
                    b.currency, 
                    c.uom,
                    f.mpq');
                $this->db->from('purchase_order_receipt_others a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                // $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
                $this->db->join('supplier_items f', 'b.id = f.supplier_id and c.id = f.item_rm_id', 'left');
                // $this->db->join('scan_item_receipts g', 'g.receipt_id = a.receipt_id', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                if ($filter_item_rm != "") {
                    $this->db->where('c.id', $filter_item_rm);
                }
                $this->db->where('a.receipt_no', $id);
                $this->db->group_by('a.receipt_id');
                $this->db->order_by('a.receipt_id', 'ASC');
                $records = $this->db->get()->result_array();
                echo json_encode($records);
            }
        }
    }

    public function datatablesTemp()
    {
        $po_no = $this->input->get('po_no');
        $bc_document = base64_decode($this->input->get('bc_document'));

        //Select Query
        $this->db->select("a.po_no,
            '' as id,
            b.id as item_rm_id, 
            b.number as item_rm_number, 
            b.name as item_rm_name, 
            round(e.qty) as qty_po,
            f.mpq, 
            a.supplier_id,
            e.asset_code,
            e.asset_code_sub,
            (CASE WHEN a.uom = '' THEN b.uom ELSE a.uom END) as uom,
            (SUM(a.delivery) - COALESCE(d.qty_os, 0)) as qty_delivery,
            (SUM(a.delivery) - COALESCE(d.qty_os, 0)) as qty_receipt,
            CEIL(((SUM(a.delivery) - COALESCE(d.qty_os, 0)) / f.mpq)) as qty_label");
        $this->db->from('purchase_confirm_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('supplier_items f', 'a.item_rm_id = f.item_rm_id and a.supplier_id = f.supplier_id', 'left');
        $this->db->join('(SELECT sum(qty_receipt) as qty_os, item_rm_id, supplier_id, po_no, bc_document FROM purchase_order_receipt_others WHERE status = 0 GROUP BY item_rm_id, supplier_id, po_no, bc_document) d', 'a.item_rm_id = d.item_rm_id and a.supplier_id = d.supplier_id and a.po_no = d.po_no and a.invoice_no = d.bc_document', 'left');
        $this->db->join('(SELECT SUM(qty) AS qty, item_rm_id, po_no, asset_code, asset_code_sub FROM purchase_order_others GROUP BY item_rm_id, po_no) e', 'a.item_rm_id = e.item_rm_id and a.po_no = e.po_no');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.invoice_no', $bc_document);
        $this->db->like('a.po_no', $po_no);
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.item_rm_id');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function datatablesUpdate()
    {
        $receipt_no = base64_decode($this->input->get('receipt_no'));

        //Select Query
        $this->db->select('a.*, 
            b.id as item_rm_id, 
            b.number as item_rm_number, 
            b.name as item_rm_name,
            b.uom,
            a.qty_mpq as mpq');
        $this->db->from('purchase_order_receipt_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.receipt_no', $receipt_no);
        $this->db->order_by('a.id', 'asc');

        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if (empty($post['id'])) {
                $receipt_no = $post['receipt_no'];
                $receipt = $this->crud->read("purchase_order_receipt_others", [], ["receipt_no" => $receipt_no]);

                if (!empty($receipt)) {
                    if ($receipt->supplier_id != $post['supplier_id']) {
                        die(json_encode(array('title' => 'Failed', 'message' => "Cannot Save data because Different Supplier in Receipt No, Please Try Again", 'theme' => "error")));
                    }
                }

                $send = $this->crud->create('purchase_order_receipt_others', array_merge($post, ["receipt_id" => $this->receipt_id($post['receipt_no'])]));

                if ($post['qty_delivery'] > $post['qty_receipt']) {
                    $status = 0;
                } else {
                    $status = 1;
                }

                if ($send) {
                    $this->db->where('invoice_no', $post['bc_document']);
                    $this->db->where('po_no', $post['po_no']);
                    $this->db->where('item_rm_id', $post['item_rm_id']);
                    $this->db->update("purchase_confirm_others", ["status" => $status]);
                }

                echo $send;
            } else {
                $send = $this->db->update('purchase_order_receipt_others', [
                    "store" => 1,
                    "store_by" => $this->session->username,
                    "store_date" => date("Y-m-d")
                ], ["id" => $post['id']]);
                echo json_encode(array('title' => 'Confirmed', 'message' => "Data Successfully Confirmed", 'theme' => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadAttachment($file_bf = "")
    {
        $file                = $_FILES['file']['name'];
        $extension            = explode('.', $file);
        $extension_end        = strtolower(end($extension));
        $size                = $_FILES['file']['size'];
        $temporary             = $_FILES['file']['tmp_name'];
        $fileName           = time() . "." . $extension_end;

        if (in_array($extension_end, array('pdf', 'jpg', 'png', 'jpeg')) === true) {
            if ($size < 1000000) {
                if ($file != "") {
                    @unlink("assets/documents/purchase_order_receipt_others/" . base64_decode($file_bf));
                    @move_uploaded_file($temporary, 'assets/documents/purchase_order_receipt_others/' . $fileName);
                    echo json_encode(array('msg' => 'Upload Success', "file" => $fileName));
                }
            } else {
                echo json_encode(array('msg' => 'Your file size is too large, a maximum of 1 MB', 'file' => base64_decode($file_bf)));
            }
        } else {
            echo json_encode(array('msg' => 'error', 'text' =>  'Your file format is not accepted', 'file' => base64_decode($file_bf)));
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $deletePurchaseOrderReceipts = $this->crud->delete('purchase_order_receipt_others', ["receipt_id" => $data['id']]);
        $updatePurchaseOrders = $this->crud->update('purchase_confirm_others', ["po_no" => $data['po_no'], "invoice_no" => $data['bc_document'], "item_rm_id" => $data['item_rm_id']], ["status" => 0]);
        echo $deletePurchaseOrderReceipts;
    }

    public function cancelLabel()
    {
        $data = $this->input->post();
        $por = $this->crud->read("purchase_order_receipt_others", [], ["receipt_id" => $data['receipt_id']]);

        if ($por->status == 0) {
            $this->db->update('purchase_order_receipt_others', ["status" => 2], ["receipt_id" => $data['receipt_id']]);
            $this->db->update('purchase_confirm_others', ["status" => 2], ["po_no" => $por->po_no, "item_rm_id" => $por->item_rm_id]);
            $this->db->update('purchase_order_others', ["status" => 0], ["po_no" => $por->po_no, "item_rm_id" => $por->item_rm_id]);
            echo json_encode(array("theme" => "success"));
        } else if ($por->status == 2) {
            $this->db->update('purchase_order_receipt_others', ["status" => 0], ["receipt_id" => $data['receipt_id']]);
            $this->db->update('purchase_confirm_others', ["status" => 1], ["po_no" => $por->po_no, "item_rm_id" => $por->item_rm_id]);
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function print_receiving($receipt_no, $type = "")
    {
        $purchase_order_receipt_total = $this->crud->reads('purchase_order_receipt_others', [], ["receipt_no" => base64_decode($receipt_no)]);
        $po_receipt = $this->crud->read('purchase_order_receipt_others', [], ["receipt_no" => base64_decode($receipt_no)]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 8;
        $page = ceil(count($purchase_order_receipt_total) / $rows);

        if ($type == "QAS") {
            $type_name = "MATERIAL";
            $grn_no = "-";
        } else {
            $type_name = "GOOD";
            $grn_no = "GRN" . substr(@$po_receipt->receipt_no, 3);
        }

        //Generate QRcode
        $this->createQrcode($po_receipt->receipt_no, "assets/image/qrcode/");
        $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_rm_id, c.name as item_name, c.uom, d.name as item_familys_name, e.mpq, b.currency');
        $this->db->from('purchase_order_receipt_others a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.receipt_no', base64_decode($receipt_no));
        $this->db->group_by('a.id');
        $records = $this->db->get()->result_array();

        if ($records) {
            $html = '<html>
                        <head>
                            <title>' . $po_receipt->receipt_no . '</title>
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
                                text-align: center;color: black;
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
                                    <p>Margin Default, Scale 98</p>
                                </center>
                            </div>
                            <div class="print">';
            $no = 1;
            $hal = 1;
            $subtotal = 0;
            for ($i = 0; $i < $page; $i++) {
                $this->db->select('a.*, b.id as supplier_id, b.name as supplier_name, c.name as item_rm_name, c.number as item_rm_number, c.uom, d.name as item_familys_name, e.mpq, b.currency');
                $this->db->from('purchase_order_receipt_others a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id', 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.receipt_no', base64_decode($receipt_no));
                $this->db->group_by('a.id');
                $this->db->limit(8, ($i * 8));
                $records = $this->db->get()->result_array();

                $html .= '  <table style="width:100%;">
                                    <tr>
                                        <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                        <td width="250" style="padding:10px;">
                                            <b style="font-size:14px;">' . $config->name . '</b><br>
                                            <span style="font-size:10px;">' . $config->address . '</span><br>
                                        </td>
                                        <th width="100" style="text-align:right;">
                                            <table style="width:100%; font-size:10px;">
                                                <tr>
                                                    <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $po_receipt->receipt_no . '.png') . '" width="60"/></td>
                                                    <td width="60">Doc No</td>
                                                    <td width="5">:</td>
                                                    <td width="100">' . $config_iso->doc_receiving_note . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Form</td>
                                                    <td>:</td>
                                                    <td>' . $config_iso->form_receiving_note . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Print Date</td>
                                                    <td>:</td>
                                                    <td>' . date("Y-m-d H:i") . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Print By</td>
                                                    <td>:</td>
                                                    <td>' . $this->session->name . '</td>
                                                </tr>
                                            </table>
                                        </th>
                                    </tr>
                                </table>
                                <div style="border: 1px solid black; width:100%;">
                                    <div style="padding:10px;">
                                        <center>
                                            <h3><u>' . $type_name . ' RECEIPT NOTE</u></h3>
                                        </center>
                                        <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="100">GRN No</td>
                                                <td width="10">:</td>
                                                <td><b>' . $grn_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="100">MRN No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">MRN Date</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_date . '</b></td>
                                            </tr>
                                        </table>
                                        <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">

                                            <tr>
                                                <td width="100">Supplier</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="100">Invoice. No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['bc_document'] . '</b></td>
                                            </tr>
                                        </table>
    
                                        <table id="customers">
                                            <tr>
                                                <th>No</th>
                                                <th>PO No</th>
                                                <th>Part No EBWS</th>
                                                <th>Part No Supplier</th>
                                                <th>Description</th>
                                                <th>Category</th>
                                                <th>Location</th>
                                                <th>MPQ</th>
                                                <th>Quantity</th>
                                                <th>Uom</th>
                                            </tr>';
                $no = 1;
                foreach ($records as $record) {
                    $html .= '  <tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $record['po_no'] . '</td>
                        <td>' . $record['item_rm_id'] . '</td>
                        <td>' . $record['item_rm_number'] . '</td>
                        <td>' . $record['item_rm_name'] . '</td>
                        <td>' . $record['item_familys_name'] . '</td>
                        <td>' . $record['location'] . '</td>
                        <td style="text-align:right">' . number_format($record['mpq'], 2) . '</td>
                        <td style="text-align:right">' . number_format($record['qty_receipt'], 2) . '</td>
                        <td>' . $record['uom'] . '</td>
                    </tr>';
                    $no++;
                }
                $html .= '  </table>
                            <table id="customers" style="margin-top:20px;">
                                <tr>
                                    <th rowspan="3" style="vertical-align:top; padding:20px; text-align:left;">Note : </th>
                                    <th width="200" style="text-align:center;">Approval QC</th>
                                    <th width="200" style="text-align:center;">Receipt By</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;"></th>
                                    <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                                </tr>
                            </table>
                        </div>
                    </div>
                <script>window.print()</script>';
                if (($i + 1) != $page) {
                    $html .= '<div style="page-break-after:always;"></div>';
                }
                $hal++;
            }
            $html .= "</div></div><script>window.print()</script>";
            die($html);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_receipt_others_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_supplier = $this->input->get('filter_supplier');
        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_receipt = $this->input->get('filter_receipt');
        $filter_doc_no = $this->input->get('filter_doc_no');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
            a.id as purchase_order_receipt_others_id, 
            a.receipt_id as id, 
            b.number as supplier_id, 
            b.name as supplier_name, 
            b.pen,
            b.gst,
            c.number as item_rm_number, 
            c.name as item_rm_name, 
            d.name as item_family_name, 
            b.currency, 
            c.uom,
            e.mpq');
        $this->db->from('purchase_order_receipt_others a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id', 'left');
        // $this->db->join('scan_item_receipts g', 'g.receipt_id = a.receipt_id', 'left');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where('a.receipt_date >=', $filter_from);
            $this->db->where('a.receipt_date <=', $filter_to);
        }
        if ($filter_item_rm != "") {
            $this->db->where('c.id', $filter_item_rm);
        }
        if ($filter_supplier != "") {
            $this->db->where('b.id', $filter_supplier);
        }
        if ($filter_po_no != "") {
            $this->db->where('a.po_no', $filter_po_no);
        }
        if ($filter_receipt != "") {
            $this->db->where('a.receipt_no', $filter_receipt);
        }
        if ($filter_doc_no != "") {
            $this->db->where('a.bc_document', $filter_doc_no);
        }
        $this->db->group_by('a.receipt_id');
        $this->db->order_by('a.receipt_id', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>PURCHASE ORDER RECEIPT</small>
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
                <th rowspan="2" width="20">No</th>
                <th rowspan="2" width="80">Status<br>Invoice</th>
                <th rowspan="2">Store</th>
                <th rowspan="2">POR No</th>
                <th rowspan="2">PO No</th>
                <th rowspan="2">Invoice No</th>
                <th rowspan="2">Invoice Date</th>
                <th rowspan="2">BOE No</th>
                <th rowspan="2">BOE Date</th>
                <th colspan="4" style="text-align:center;">Supplier</th>
                <th rowspan="2">Part No EBWS</th>
                <th rowspan="2">Part No Supplier</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">UoM</th>
                <th rowspan="2">Currency</th>
                <th rowspan="2">Qty PO</th>
                <th rowspan="2">Qty Delivery</th>
                <th rowspan="2">Qty Receipt</th>
                <th rowspan="2">MPQ</th>
                <th rowspan="2">Label</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>GST No</th>
                <th>PEN No</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == "0") {
                $status = "OPEN";
                $style = "style='background: green; color:white;'";
            } else {
                $status = "CLOSED";
                $style = "style='background: red; color:white;'";
            }

            if ($data['store'] > 0) {
                $status_store = "Confirmed";
                $style_store = "style='background: green; color:white;'";
            } else {
                $status_store = "Unconfirm";
                $style_store = "style='background: red; color:white;'";
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td ' . $style . '>' . $status . '</td>
                        <td ' . $style_store . '>' . $status_store . '</td>
                        <td>' . $data['receipt_no'] . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['bc_document'] . '</td>
                        <td>' . $data['bc_date'] . '</td>
                        <td>' . $data['awb_no'] . '</td>
                        <td>' . $data['awb_date'] . '</td>
                        <td>' . $data['supplier_id'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['gst'] . '</td>
                        <td>' . $data['pen'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['qty_po']) . '</td>
                        <td>' . number_format($data['qty_delivery']) . '</td>
                        <td>' . number_format($data['qty_receipt']) . '</td>
                        <td>' . number_format($data['qty_mpq']) . '</td>
                        <td>' . number_format($data['qty_label']) . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
