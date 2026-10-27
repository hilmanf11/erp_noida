<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class purchase_orders_2 extends CI_Controller
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
            $this->load->view('purchase/purchase_orders_2');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('purchase_order_others', ["name" => $post]);
        echo json_encode($send);
    }

    public function readSupplier()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select('b.id, b.name');
        $this->db->from('purchase_order_others a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.status', 0);
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->like('b.name', $post);
        $this->db->group_by('b.id');
        $this->db->order_by('b.name', 'asc');
        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readPono()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($this->input->get('supplier_id'));

        $this->db->select('a.po_no, a.po_date, d.name as po_name');
        $this->db->from('purchase_order_others a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('users d', 'a.created_by = d.username');
        $this->db->where('a.status', 0);
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->like('a.supplier_id', $supplier_id);
        $this->db->like('a.po_no', $post);
        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'desc');
        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readPrno()
    {
        $supplier_id = base64_decode($this->input->get('supplier_id'));

        $this->db->select('a.request_no');
        $this->db->from('purchase_order_others a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('users d', 'a.created_by = d.username');
        $this->db->like('a.supplier_id', $supplier_id);
        $this->db->group_by('a.request_no');
        $this->db->order_by('a.request_no', 'desc');
        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readTotalPo()
    {
        $item_rm_id = $this->input->post('item_rm_id');
        $this->db->select('item_rm_id, SUM(qty) as qty');
        $this->db->from('purchase_order_others');
        $this->db->where('deleted', 0);
        $this->db->where('status', 0);
        $this->db->where('item_rm_id', $item_rm_id);
        $this->db->group_by('item_rm_id');
        $records = $this->db->get()->row();

        echo json_encode($records);
    }

    public function request_no($id_menu, $add = "", $trans_date = "")
    {
        if ($trans_date == "") {
            $datenow = date("Y-m-d");
        } else {
            $datenow = base64_decode($trans_date);
        }

        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(po_no) as kode FROM purchase_order_others WHERE po_no like '%$whereDate%'");
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

    public function readTerm()
    {
        $po_no = $this->input->post('po_no');
        $send = $this->crud->read('purchase_order_others', [], ["po_no" => $po_no]);
        echo json_encode($send);
    }

    public function checkSupplier()
    {
        if ($this->input->post()) {
            $supplier_id = $this->input->post('supplier_id');
            $supplier = $this->crud->read("suppliers", [], ["id" => $supplier_id]);

            if (($supplier->approved_to != "" || $supplier->approved_to != null)) {
                die(json_encode(array("title" => "Failed", "message" => "Supplier still on process approvals", "theme" => "error")));
            } elseif ($supplier->incoterm == "") {
                die(json_encode(array("title" => "Failed", "message" => "Incoterm empty, please check in master supplier", "theme" => "error")));
            } elseif ($supplier->terms == "") {
                die(json_encode(array("title" => "Failed", "message" => "Terms Condition empty, please check in master supplier", "theme" => "error")));
            } else {
                die(json_encode(array("title" => "Ok", "message" => "Completed", "theme" => "success")));
            }
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_pr_no = $this->input->get('filter_pr_no');
            $filter_item_no = $this->input->get('filter_item_no');
            $filter_suppliers = $this->input->get('filter_suppliers');
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
                $this->db->select('a.po_no, a.request_no, a.total_dp,
                    a.po_date, 
                    d.currency, 
                    b.uom,
                    d.name as supplier_name,
                    d.supplier_po,
                    SUM(a.qty) as qty, 
                    SUM(a.price) as price, 
                    SUM(a.total) as total_price,
                    (CASE WHEN h.po_no is not null THEN 1 ELSE 0 END) as status_pi,
                    a.status as status_gate,
                    g.total_status_close,
                    a.approved_by,
                    a.approved_date,
                    a.approved_to,
                    a.created_by,
                    a.created_date,
                    a.updated_by,
                    a.updated_date');
                $this->db->from('purchase_order_others a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', "a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id and e.approved_to = ''", "left");
                $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_order_others WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
                $this->db->join('purchase_invoices h', 'a.item_rm_id = h.item_rm_id and a.po_no = h.po_no', 'left');
                $this->db->join('purchase_order_receipt_others i', 'a.item_rm_id = i.item_rm_id and a.po_no = i.po_no', 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.type', "PO");
                if ($filter_from != "" or $filter_to != "") {
                    $this->db->where('a.po_date >=', $filter_from);
                    $this->db->where('a.po_date <=', $filter_to);
                }
                if ($filter_po_no != "") {
                    $this->db->where('a.po_no', $filter_po_no);
                }
                if ($filter_pr_no != "") {
                    $this->db->where('a.request_no', $filter_pr_no);
                }
                if ($filter_suppliers != "") {
                    $this->db->where('d.id', $filter_suppliers);
                }
                if ($filter_item_no != "") {
                    $this->db->where('b.id', $filter_item_no);
                }
                $this->db->group_by('a.po_no');
                // $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.po_no', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                //Mapping Data
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['po_no'],
                        "po_no" => $record['po_no'],
                        "request_no" => $record['request_no'],
                        "po_date" => $record['po_date'],
                        "uom" => $record['uom'],
                        "currency" => $record['currency'],
                        "supplier_name" => $record['supplier_name'],
                        "supplier_po" => $record['supplier_po'],
                        "status" => $record['status_gate'],
                        "status_pi" => $record['status_pi'],
                        "qty" => $record['qty'],
                        "price" => $record['price'],
                        "total_price" => $record['total_price'],
                        "approved_by" => $record['approved_by'],
                        "approved_to" => $record['approved_to'],
                        "approved_date" => $record['approved_date'],
                        "created_by" => $record['created_by'],
                        "created_date" => $record['created_date'],
                        "updated_by" => $record['updated_by'],
                        "updated_date" => $record['updated_date'],
                        "state" => "closed",
                        "datatable" => 1
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*,
                    a.po_no as supplier_po,
                    b.number as item_rm_number, 
                    b.name as item_rm_name,
                    c.name as item_family_name, 
                    d.name as supplier_name, 
                    d.currency,
                    b.uom,
                    a.price, 
                    (CASE WHEN h.po_no is not null THEN 1 ELSE 0 END) as status_pi,
                    a.status,
                    COALESCE(j.qty_receipt, 0) as por_qty,
                    a.qty as bf_qty,
                    (a.qty * a.price) as total_price');
                $this->db->from('purchase_order_others a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
                $this->db->join('purchase_invoices h', 'a.item_rm_id = h.item_rm_id and a.po_no = h.po_no', 'left');
                $this->db->join('purchase_order_receipt_others i', 'a.item_rm_id = i.item_rm_id and a.po_no = i.po_no', 'left');
                $this->db->join("(SELECT item_rm_id, po_no, SUM(delivery) AS qty_receipt FROM purchase_confirm_others WHERE status != '2' GROUP BY item_rm_id, po_no) j", 'a.po_no = j.po_no and a.item_rm_id = j.item_rm_id', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" or $filter_to != "") {
                    $this->db->where('a.po_date >=', $filter_from);
                    $this->db->where('a.po_date <=', $filter_to);
                }
                if ($filter_pr_no != "") {
                    $this->db->where('a.request_no', $filter_pr_no);
                }
                if ($filter_item_no != "") {
                    $this->db->where('b.id', $filter_item_no);
                }
                $this->db->like('a.po_no', $id);
                $this->db->like('d.id', $filter_suppliers);
                $this->db->group_by('a.id');
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.po_no', 'DESC');
                $records = $this->db->get()->result_array();
                echo json_encode($records);
            }
        }
    }

    public function changePrice()
    {
        $id = $this->input->post("id");
        $po = $this->crud->read("purchase_order_others", [], ["id" => $id]);

        $sql = $this->db->query("SELECT * FROM supplier_items WHERE supplier_id = '$po->supplier_id' and item_rm_id = '$po->item_rm_id' and approved_to = ''");
        $row = $sql->row_array();
        echo json_encode(array("price" => @$row['price']));
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $purchaseOrder = $this->crud->read('purchase_order_others', [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);
                $supplier = $this->crud->read("suppliers", [], ["id" => $post['supplier_id']]);

                if (empty($purchaseOrder)) {
                    $send = $this->crud->create('purchase_order_others', array_merge($post, ["terms" => $supplier->terms, "incoterm" => $supplier->incoterm, "payment_term" => $supplier->payment_term]));

                    $this->db->where('request_no', $post['request_no']);
                    $this->db->where('item_rm_id', $post['item_rm_id']);
                    $this->db->update("purchase_request_others", ["status" => 1]);
                    echo $send;
                } else {
                    show_error('Duplicate');
                }
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
                if ($post['qty'] > $post['bf_qty']) {
                    $this->crud->update('purchase_order_others', ["id" => $id], [
                        "qty" => $post['qty'],
                        "price" => $post['price'],
                        "total" => $post['total'],
                        "delivery_date" => $post['delivery_date'],
                        "remarks" => $post['remarks'],
                        "revision" => ($post['revision'] + 1)
                    ]);

                    echo json_encode(array("title" => "Good Job", "message" => "Update Prices Successfully", "theme" => "success"));
                } else {
                    $this->db->update('purchase_order_others', [
                        "qty" => $post['qty'],
                        "price" => $post['price'],
                        "total" => $post['total'],
                        "delivery_date" => $post['delivery_date'],
                        "remarks" => $post['remarks'],
                        "revision" => ($post['revision'] + 1)
                    ], ["id" => $id]);

                    echo json_encode(array("title" => "Good Job", "message" => "Update Prices Successfully", "theme" => "success"));
                }
            } else {
                show_error("Cannot Process your request");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updateTerms()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->db->update('purchase_order_others', ["terms" => $post['terms']], ["po_no" => $post['po_no']]);
            echo json_encode(array("title" => "Good Job", "message" => "Update Temrs & Conditions Successfully", "theme" => "success"));
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
        $send = $this->crud->delete('purchase_order_others', $data);
        $update = $this->crud->update('purchase_requests', ["request_no" => $data['request_no'], "item_rm_id" => $data['item_rm_id']], ["status" => 0]);
        echo $send;
    }

    public function print_po($po_no, $export = "")
    {
        $purchase_order_others_total = $this->crud->reads('purchase_order_others', [], ["po_no" => base64_decode($po_no)]);
        $purchase_order_others = $this->crud->read('purchase_order_others', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => @$purchase_order_others->supplier_id]);
        $approval = $this->crud->read('approvals', [], ["table_name" => "purchase_order_others"]);

        $user_3 = $this->crud->read('users', [], ["username" => @$approval->user_approval_1]);

        $qr_user_3 = "";

        if ($purchase_order_others->approved == 2) {
            $qr_user_3 = "src='" . base_url('assets/image/qrcode/' . @$user_3->name . '.png') . "'";
        }

        // $month_1 =  date('M Y', strtotime('+1 month', strtotime(@$purchase_order_others->po_date)));
        // $month_2 =  date('M Y', strtotime('+2 month', strtotime(@$purchase_order_others->po_date)));
        // $month_3 =  date('M Y', strtotime('+3 month', strtotime(@$purchase_order_others->po_date)));

        $config = $this->db->get('config')->row();

        //Config Page
        $rows = 20;
        $page = ceil(count($purchase_order_others_total) / $rows);
        //Generate QRcode
        // $this->createQrcode($purchase_order_others->po_no, "assets/image/qrcode/");

        $this->createQrcode(@$user_3->name, "assets/image/qrcode/");

        $media = "@media screen {
                        .print {
                            display: none !important;
                        }
                    }
                    @media print {
                        .noprint {
                            display: none !important;
                        }
                    }";
        $media2 = ' <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                        </center>
                    </div>';


        $html = '<html>
                    <head>
                        <title>' . $purchase_order_others->po_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        .customers {
                            border-collapse: collapse;
                            font-size: 12px;
                        }
                        .customers td, .customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        .customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        ' . $media . '
                    </style>
                    <body>
                        ' . $media2 . '
                        <div class="print">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $total_igst = 0;
        $total_cgst = 0;
        $total_sgst = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*,
                b.number as item_rm_number, 
                b.name as item_rm_name,
                c.item_supplier,
                d.currency,
                b.uom,
                d.type,
                ((a.qty * a.price) * COALESCE(e.igst, 0) / 100) as digst,
                ((a.qty * a.price) * COALESCE(e.sgst, 0) / 100) as dsgst,
                ((a.qty * a.price) * COALESCE(e.cgst, 0) / 100) as dcgst,
                d.state');
            $this->db->from('purchase_order_others a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('supplier_items c', 'a.supplier_id = c.supplier_id and a.item_rm_id = c.item_rm_id');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->join('hsn e', "b.hsn_code = e.number", 'left');
            $this->db->where('a.po_no', base64_decode($po_no));
            $this->db->where('a.qty >', 0);
            $this->db->order_by('a.item_rm_id');
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();

            if ($purchase_order_others->updated_date != null) {
                $revision_date = $purchase_order_others->updated_date;
            } else {
                $revision_date = $purchase_order_others->created_date;
            }
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%; float:left;">
                            <div style="padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;">PURCHASE ORDER</h3>
                                </center>
                                <br>
                                <div style="width:45%; float:left;">
                                    <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                        <tr>
                                            <td width="20" style="vertical-align: top;"><b>To</b></td>
                                            <td width="5" style="vertical-align: top;">:</td>
                                            <td><b>' . @$supplier->name . '</b><br>' . @$supplier->address . '<br>GST No : ' . @$supplier->gst . '</td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width:10%; float:left;"><br></div>
                                <div style="width:45%; float:left;">
                                    <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                        <tr>
                                            <td width="50" style="vertical-align: top;"></td>
                                            <td width="30" style="vertical-align: top;"></td>
                                            <td>PO No : ' . $supplier->supplier_po . '<br>
                                                Del. Sch No : ' . $purchase_order_others->po_no . '<br>
                                                Date : ' . $purchase_order_others->po_date . '<br>
                                                Revision : ' . $purchase_order_others->revision . '<br><br>
                                                <b>ELCON BANSHU WIRING SYSTEM PVT. LTD.</b><br>
                                                Factory / Shipping Address : <br>
                                                ' . @$config->address2 . '<br>
                                                GST No : ' . @$config->gst_no . '
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <table class="customers" style="width:100%;">
                                    <tr>
                                        <th style="text-align:center;">No</th>
                                        <th style="text-align:center;">Part No EBWS</th>
                                        <th style="text-align:center;">Part No Supplier</th>
                                        <th style="text-align:center;">Part Name</th>
                                        <th style="text-align:center;">Maker</th>
                                        <th style="text-align:center;">ETA</th>
                                        <th style="text-align:center;">Currency</th>
                                        <th style="text-align:center;">Price</th>
                                        <th style="text-align:center;">Qty</th>
                                        <th style="text-align:center;">UOM</th>
                                        <th style="text-align:center;">Total Price</th>
                                        <th style="text-align:center;">CGST</th>
                                        <th style="text-align:center;">SGST</th>
                                        <th style="text-align:center;">IGST</th>
                                        <th style="text-align:center;">Total Amount</th>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                $subtotal += ($record['total']);

                if ($record['state'] == $config->state) {
                    $total2 = ($record['total'] + $record['dcgst'] + $record['dsgst']);
                    $total_igst += 0;
                    $total_cgst += $record['dcgst'];
                    $total_sgst += $record['dsgst'];
                    $digst = 0;
                    $dsgst = $record['dsgst'];
                    $dcgst = $record['dcgst'];
                } else {
                    if ($record['type'] == "LOCAL") {
                        $total2 = ($record['total'] + $record['digst']);
                        $total_igst += $record['digst'];
                        $total_cgst += 0;
                        $total_sgst += 0;
                        $digst = $record['digst'];
                        $dsgst = 0;
                        $dcgst = 0;
                    } else {
                        $total2 = ($record['total']);
                        $total_igst += 0;
                        $total_cgst += 0;
                        $total_sgst += 0;
                        $digst = 0;
                        $dsgst = 0;
                        $dcgst = 0;
                    }
                }

                if (!empty($record['reason'])) {
                    $styles = "background:#FFDD9E;";
                } else {
                    $styles = "";
                }

                if ($record['currency'] == "INR") {
                    $digit = 4;
                } else {
                    $digit = 5;
                }

                $html .= '  <tr style="' . $styles . '">
                                <td  style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_rm_number'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td>' . $record['maker'] . '</td>
                                <td>' . $record['delivery_date'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . number_format($record['price'], $digit) . '</td>
                                <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format($record['total'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($dcgst, 2) . '</td>
                                <td style="text-align:right;">' . number_format($dsgst, 2) . '</td>
                                <td style="text-align:right;">' . number_format($digst, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total2, 2) . '</td>
                            </tr>';
                $row++;
                $no++;
            }

            if (($i + 1) == $page) {
                $html .= '  <tr>
                                <td style="vertical-align: top; text-align:left;" colspan="11" rowspan="6">
                                    <b>Note :</b> <br>' . @$purchase_order_others->note;
                $html .= '      </td>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">Sub Total</th>
                                <th style="text-align:right;">' . number_format($subtotal, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">CGST</th>
                                <th style="text-align:right;">' . number_format($total_cgst, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">SGST</th>
                                <th style="text-align:right;">' . number_format($total_sgst, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">IGST</th>
                                <th style="text-align:right;">' . number_format($total_igst, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="3">Total Amount</th>
                                <th style="text-align:right;">' . number_format(($subtotal + ($total_cgst + $total_sgst + $total_igst)), 2) . '</th>
                            </tr>
                        </table>';
            } else {
                $html .= '</table>';
            }

            if (($i + 1) == $page) {
                $html .= '  <div style="width:100%">
                                <div style="width:70%; float:left;">
                                    <br><br>
                                    <div style="font-size:12px !important;">
                                        <b style="font-size:16px !important;">Term & Condition</b><br><br>
                                        <b style="font-size:16px !important;">Payment Term : ' . $purchase_order_others->payment_term . ' Days</b><br> 
                                        <b style="font-size:16px !important;">Incoterm : ' . $purchase_order_others->incoterm . '</b><br><br>
                                        ' . $purchase_order_others->terms . '
                                    </div>
                                </div>
                                <div style="width:30%; float:left; right: 50px;">
                                    <br><br><br>
                                    <table style="margin-top:20px; font-size:12px;">
                                        <tr>
                                            <th colspan="2" width="800" style="text-align:left;">' . $config->name . '</th>
                                        </tr>
                                        <tr>
                                            <th style="height:80px; text-align:left;"><img ' . $qr_user_3 . ' width="80"/></th>
                                        </tr>
                                        <tr>
                                            <th style="height:20px; text-align:left;">Authorized Signatory <br>' . $user_3->name . '</th>
                                        </tr>
                                    </table>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>';
            } else {
                $html .= "</div></div>";
            }

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

        $html .= '<script>window.print()</script>';
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_others_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_suppliers = $this->input->get('filter_suppliers');
        $filter_pr_no = $this->input->get('filter_pr_no');
        $filter_item_no = $this->input->get('filter_item_no');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
            b.number as item_rm_number, 
            b.name as item_rm_name,
            b.uom,
            c.name as item_family_name, 
            d.name as supplier_name, 
            (CASE WHEN f.po_no is not null THEN 1 ELSE 0 END) as status_gate,
            d.supplier_po,
            d.currency');
        $this->db->from('purchase_order_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
        $this->db->join('purchase_order_receipt_others f', 'a.item_rm_id = f.item_rm_id and a.po_no = f.po_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.type', "PO");
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.po_date >=', $filter_from);
            $this->db->where('a.po_date <=', $filter_to);
        }
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('d.id', $filter_suppliers);
        $this->db->like('a.request_no', $filter_pr_no);
        $this->db->like('a.item_rm_id', $filter_item_no);
        $this->db->order_by('a.po_date', 'DESC');
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
                                <small>PURCHASE ORDER OTHERS</small>
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
                    <th>Approved</th>
                    <th>Approved By</th>
                    <th>Approved Date</th>
                    <th>Del. Sch No</th>
                    <th>Del. Sch Period</th>
                    <th>PO No</th>
                    <th>Supplier</th>
                    <th>Maker</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total Price</th>
                    <th>Currency</th>
                    <th>Uom</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status_gate'] == 0) {
                $status = "OPEN";
            } else {
                $status = "CLOSED";
            }

            if ($data['approved_to'] == "") {
                $approved = "APPROVED";
            } else {
                $approved = "CHECKING";
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $approved . '</td>
                        <td>' . $data['approved_by'] . '</td>
                        <td>' . $data['approved_date'] . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['po_date'] . '</td>
                        <td>' . $data['supplier_po'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['maker'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_rm_number'] . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['price'] . '</td>
                        <td>' . $data['total'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['created_by'] . '</td>
                        <td>' . $data['created_date'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
