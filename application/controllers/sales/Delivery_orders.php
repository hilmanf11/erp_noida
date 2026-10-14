<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_orders extends CI_Controller
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
            $this->load->view('sales/delivery_orders');
        } else {
            redirect('error_access');
        }
    }

    public function readSo($trans_date, $customer_id, $item_fg_id, $plant)
    {
        $trans_date = base64_decode($trans_date);
        $customer_id = base64_decode($customer_id);
        $item_fg_id = base64_decode($item_fg_id);
        $plant = base64_decode($plant);

        $this->db->select('b.sales_order_no, b.customer_order_no, b.qty, (b.qty - COALESCE(d.qty_do, 0)) as qty_remain, a.plant');
        $this->db->from('sales_order_deliveries a');
        $this->db->join('sales_orders b', 'a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id and a.plant = b.plant');
        $this->db->join("(SELECT customer_id, item_fg_id, sales_order_no, plant, SUM(qty_do) as qty_do FROM delivery_orders GROUP BY customer_id, item_fg_id, sales_order_no, plant) d", 'b.sales_order_no = d.sales_order_no and a.customer_id = d.customer_id and a.item_fg_id = d.item_fg_id and a.plant = d.plant', "LEFT");
        // $this->db->join('customer_address d', 'b.customer_address_id = d.id', 'left');
        $this->db->where('a.trans_date', $trans_date);
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->where('a.plant', $plant);
        $this->db->where('b.status', 0);
        $this->db->where("(b.approved_to = '' or b.approved_to is null)");
        $this->db->where("(b.qty - COALESCE(d.qty_do, 0)) > 0");
        $this->db->group_by('b.sales_order_no');
        $this->db->order_by('b.sales_order_no', 'asc');

        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function number($id_menu, $trans_date)
    {
        // $datenow = date("Y-m-d");
        $datenow = base64_decode($trans_date);
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(delivery_order_no) as kode FROM delivery_orders WHERE delivery_order_no like '%$whereDate%'");
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

    // public function number($delivery_order_date, $customer_id)
    // {
    //     $customer_id = base64_decode($customer_id);
    //     $datenow    = "DO" . $customer_id . date("ym", strtotime(base64_decode($delivery_order_date)));
    //     $sqlGetID   = $this->db->query("SELECT max(`delivery_order_no`) as kode FROM delivery_orders WHERE `delivery_order_no` like '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%04s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -3);
    //         $urutan++;
    //         $autoID = sprintf("%04s", $urutan);
    //     }
    //     echo $datenow . $autoID;
    // }

    public function datatablesTemp($delivery_date, $customer_id, $plant)
    {
        $delivery_date = base64_decode($delivery_date);
        $customer_id = base64_decode($customer_id);
        $plant = base64_decode($plant);

        $delivery = $this->crud->reads("delivery_orders", [], ["delivery_date" => $delivery_date]);
        $whereArr = array();
        foreach ($delivery as $del) {
            $whereArr[] = $del->item_fg_id;
        }

        $this->db->select('a.item_fg_id, a.customer_id, 
            a.trans_date, b.sales_order_no, b.customer_order_no,
            c.number as item_fg_number, 
            c.number_customer, 
            c.name as item_fg_name, 
            c.uom, 
            b.plant,
            (a.qty - COALESCE(d.qty_do, 0)) as qty_del,
            b.qty as qty_so,
            (b.qty - COALESCE(f.qty_do2, 0)) as qty_remain,
            (a.qty - COALESCE(d.qty_do, 0)) as qty_do,
            (COALESCE(e.stock_fg, 0)) as stock_fg,
            COALESCE(b.qty - COALESCE(f.qty_do2, 0) - (a.qty - COALESCE(d.qty_do, 0)), 0) as balance');
        $this->db->from('sales_order_deliveries a');
        $this->db->join('sales_orders b', 'a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id and a.plant = b.plant and b.status = 0');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join("(SELECT customer_id, item_fg_id, plant, sales_order_no, SUM(qty_do) as qty_do FROM delivery_orders WHERE delivery_date = '$delivery_date' and status != '2' GROUP BY customer_id, item_fg_id, sales_order_no, plant) d", 'b.sales_order_no = d.sales_order_no and a.customer_id = d.customer_id and a.item_fg_id = d.item_fg_id and a.plant = d.plant', "LEFT");
        $this->db->join("(SELECT customer_id, item_fg_id, plant, sales_order_no, SUM(qty_do) as qty_do2 FROM delivery_orders WHERE status != '2' GROUP BY customer_id, item_fg_id, sales_order_no, plant) f", 'b.sales_order_no = f.sales_order_no and a.customer_id = f.customer_id and a.item_fg_id = f.item_fg_id and a.plant = f.plant', "LEFT");
        $this->db->join("(SELECT
                a.id,
                (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(e.qty_rgp_in, 0) + COALESCE(c.qty_wip, 0) + COALESCE(i.qty_return, 0) - COALESCE(g.qty, 0) - COALESCE(f.qty_rgp_out, 0) - COALESCE(h.sto_qty_out, 0)) as stock_fg
                FROM item_fg a 
                LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_fg a JOIN scan_item_receipts_fg b ON a.id = b.workorder GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
                LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_wip FROM wip_receipts a JOIN scan_item_receipts_fg b ON a.workorder = b.workorder and a.checksheet_number = b.checksheet_number GROUP BY a.item_fg_id) c ON a.id = c.item_fg_id
                LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_return FROM sales_returns a JOIN scan_item_receipts_fg b ON a.sales_invoice_no = b.workorder and a.return_id = b.checksheet_number GROUP BY a.item_fg_id) i ON a.id = i.item_fg_id
                LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_fg a WHERE a.type = 'IN' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) d ON a.id = d.item_fg_id
                LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_fg a WHERE a.type = 'OUT' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) h ON a.id = h.item_fg_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id= b.item_id WHERE a.type = 'IN' GROUP BY a.item_id) e ON a.id = e.item_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' GROUP BY a.item_id) f ON a.id = f.item_id
                LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE `status` != '2' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
            GROUP BY a.id
            ORDER BY a.number) e", 'a.item_fg_id = e.id', "LEFT");
        $this->db->where('a.trans_date', $delivery_date);
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.plant', $plant);
        $this->db->where("(b.qty - COALESCE(f.qty_do2, 0)) > 0");
        // if(!empty($whereArr)){
        //     $this->db->where_not_in('a.item_fg_id', $whereArr);
        // }
        $this->db->group_by('c.id');
        $this->db->group_by('a.customer_id');
        $this->db->order_by('c.number_customer', 'asc');

        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_plant = @base64_decode($get['filter_plant']);
            $filter_status = @base64_decode($get['filter_status']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name, c.delivery_note_no");
            $this->db->from('delivery_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('delivery_notes c', 'a.delivery_order_no = c.delivery_order_no', 'left');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.delivery_order_date >=', $filter_from);
                $this->db->where('a.delivery_order_date <=', $filter_to);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.plant', $filter_plant);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.delivery_order_no');
            $this->db->order_by('a.delivery_order_no', 'DESC');
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
            $delivery_order_no = base64_decode($this->input->get('delivery_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.number_customer');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $delivery_order_no = base64_decode($this->input->get('delivery_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name, a.stock as stock_fg, a.stock_bal as balance');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
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
            $item_fg_id =  $post['item_fg_id'];

            $delivery_orders = $this->crud->read("delivery_orders", [], [
                "delivery_order_no" => $post['delivery_order_no'],
                "item_fg_id" => $post['item_fg_id'],
                "sales_order_no" => $post['sales_order_no']
            ]);

            $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $post['sales_order_no']]);
            if (@$sales_orders->approved_to != "") {
                die(json_encode(array("title" => "Failed", "message" => "Sales Order " . $post['sales_order_no'] . " is Checking, Please Approve First Sales Order", "theme" => "error")));
            }

            if (@$delivery_orders->delivery_order_no != "") {
                $send = $this->crud->update('delivery_orders', [
                    "delivery_order_no" => $post['delivery_order_no'],
                    "item_fg_id" => $post['item_fg_id'],
                    "sales_order_no" => $post['sales_order_no']
                ], ["remarks" => $post['remarks'], "qty_do" => $post['qty_do']]);

                echo $send;
            } else {
                $lastStock = $this->db->query("SELECT
                    a.id,
                    (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(e.qty_rgp_in, 0) + COALESCE(c.qty_wip, 0) + COALESCE(i.qty_return, 0) - COALESCE(g.qty, 0) - COALESCE(f.qty_rgp_out, 0) - COALESCE(h.sto_qty_out, 0)) as stock_fg
                    FROM item_fg a 
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_fg a JOIN scan_item_receipts_fg b ON a.id = b.workorder GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_wip FROM wip_receipts a JOIN scan_item_receipts_fg b ON a.workorder = b.workorder and a.checksheet_number = b.checksheet_number GROUP BY a.item_fg_id) c ON a.id = c.item_fg_id
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_return FROM sales_returns a JOIN scan_item_receipts_fg b ON a.sales_invoice_no = b.workorder and a.return_id = b.checksheet_number GROUP BY a.item_fg_id) i ON a.id = i.item_fg_id
                    LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_fg a WHERE a.type = 'IN' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) d ON a.id = d.item_fg_id
                    LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_fg a WHERE a.type = 'OUT' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) h ON a.id = h.item_fg_id
                    LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id= b.item_id WHERE a.type = 'IN' GROUP BY a.item_id) e ON a.id = e.item_id
                    LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' GROUP BY a.item_id) f ON a.id = f.item_id
                    LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE `status` != '2' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
                    WHERE a.id = '$item_fg_id'
                GROUP BY a.id
                ORDER BY a.number")->row_array();

                if (@$lastStock['stock_fg'] >= $post['qty_do']) {
                    if ($post['qty_remain'] >= $post['qty_do']) {
                        if ($post['stock_bal'] >= 0) {
                            if ($post['qty_del'] > $post['qty_do']) {
                                $send = $this->crud->create('delivery_orders', $post);

                                echo $send;
                            } else {
                                $send = $this->crud->create('delivery_orders', $post);
                                $this->db->update("sales_order_deliveries", ["status" => 1], [
                                    "item_fg_id" => $post['item_fg_id'],
                                    "customer_id" => $post['customer_id'],
                                    "trans_date" => $post['delivery_date'],
                                    "plant" => $post['plant'],
                                ]);
                                echo $send;
                            }
                        } else {
                            echo json_encode(array("title" => "Balance Minus", "message" => "Sales Order " . $post['sales_order_no'] . " on Product ID " . $post['item_fg_id'] . " Balance is Minus", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Big Delivery Qty", "message" => "Sales Order " . $post['sales_order_no'] . " on Product ID " . $post['item_fg_id'] . " Delivery qty is too big from SO qty", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Minimum Stock", "message" => "Sales Order " . $post['sales_order_no'] . " on Product ID " . $post['item_fg_id'] . " Minimum Stock from Qty Delivery", "theme" => "error"));
                }
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $delivery_orders = $this->crud->reads("delivery_orders", [], $data);
        $shipping_orders = $this->crud->reads("shipping_orders", [], $data);

        foreach ($shipping_orders as $shipping_order) {
            $this->db->update("scan_item_receipts_fg", [
                "status" => 0
            ], [
                "checksheet_label" => $shipping_order->checksheet_label
            ]);
        }

        foreach ($delivery_orders as $delivery_order) {
            $this->crud->update("sales_order_deliveries", [
                "item_fg_id" => $delivery_order->item_fg_id,
                "customer_id" => $delivery_order->customer_id,
                "trans_date" => $delivery_order->delivery_date
            ], [
                "status" => 0
            ]);
        }

        $this->db->delete('shipping_orders', $data);
        $send = $this->crud->delete('delivery_orders', $data);
        echo $send;
    }

    public function cancelDo()
    {
        $data = $this->input->post();
        $delivery_orders = $this->crud->reads("delivery_orders", [], $data);
        $shipping_orders = $this->crud->reads("shipping_orders", [], $data);
        $send = $this->db->update('delivery_orders', ["status" => 2], $data);

        foreach ($shipping_orders as $shipping_order) {
            $this->db->update("scan_item_receipts_fg", [
                "status" => 0
            ], [
                "checksheet_label" => $shipping_order->checksheet_label
            ]);
        }

        foreach ($delivery_orders as $delivery_order) {
            $this->crud->update("sales_order_deliveries", [
                "item_fg_id" => $delivery_order->item_fg_id,
                "customer_id" => $delivery_order->customer_id,
                "trans_date" => $delivery_order->delivery_date
            ], [
                "status" => 0
            ]);
        }

        $this->db->delete('shipping_orders', $data);
        echo $send;
    }

    public function print_do($delivery_order_no)
    {
        $delivery_order_no = base64_decode($delivery_order_no);

        $delivery_orders = $this->crud->reads('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);
        $delivery_order = $this->crud->read('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 10;
        $page = ceil(count($delivery_orders) / $rows);
        //Generate QRcode
        $this->createQrcode($delivery_order_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $delivery_order->delivery_order_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Display pages for 10 rows</p>
                    <p>Paper Size A4, Layout Landscape</p>
                    <p>Margin Default, Scale 98</p>
                </center></div><div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, b.number_customer');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="40" /></th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->description . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
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
                        <div style="border: 1px solid black; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>DELIVERY ORDER</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Delivery Order No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$delivery_order->delivery_order_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Delivery Order Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . date("d F Y", strtotime(@$delivery_order->delivery_order_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Delivery Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . date("d F Y", strtotime(@$delivery_order->delivery_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Customer Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['customer_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%; text-align:right;">
                                    <img style="margin-right:10px;" src="' . base_url('assets/image/qrcode/' . $delivery_order->delivery_order_no . '.png') . '" width="80"/><br>
                                    <small style="font-size:10px; margin-right:16px;">' . $delivery_order->delivery_order_no . '</small><br><br>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th>Product No EBWS</th>
                                        <th>Product No Customer</th>
                                        <th>Description</th>
                                        <th width="30">UoM</th>
                                        <th width="60">Qty</th>
                                        <th>Sales Order No</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_fg_number'] . '</td>
                                <td>' . $record['number_customer'] . '</td>
                                <td>' . $record['item_fg_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right">' . number_format($record['qty_del'], 2, ",", ".") . '</td>
                                <td>' . $record['sales_order_no'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $html .= '</div></div>';

            if (($i + 1) == $page) {
                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                                <table id="customers" style="margin-top:10px;">
                                    <tr>
                                        <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">Note.</th>
                                    </tr>
                                    <tr>
                                        <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                                        <th width="200" style="text-align:center;">DELIVER CONTROL</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;"></th>
                                    </tr>
                                </table>
                            </div>';
            }
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_orders_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_status = @base64_decode($get['filter_status']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name, c.number_customer");
        $this->db->from('delivery_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.delivery_order_date >=', $filter_from);
            $this->db->where('a.delivery_order_date <=', $filter_to);
        }
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
        $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.delivery_order_no', 'ASC');
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
                <h3>DELIVERY ORDER</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Delivery Order No</th>
                <th>Delivery Order Date</th>
                <th>Delivery Date</th>
                <th>Trans Type</th>
                <th>Sales Order No</th>
                <th>Customer Order No</th>
                <th>Remarks</th>
                <th>Product No EBWS</th>
                <th>Product No Customer</th>
                <th>Description</th>
                <th>Uom</th>
                <th>Qty SO</th>
                <th>Qty Remain</th>
                <th>Qty Delivery</th>
                <th>Qty DO</th>
                <th>Stock</th>
                <th>Balance</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['delivery_order_no'] . '</td>
                        <td>' . $data['delivery_order_date'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['trans_type'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['number_customer'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty_so'] . '</td>
                        <td>' . $data['qty_remain'] . '</td>
                        <td>' . $data['qty_del'] . '</td>
                        <td>' . $data['qty_do'] . '</td>
                        <td>' . $data['stock'] . '</td>
                        <td>' . $data['stock_bal'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
