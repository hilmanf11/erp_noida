<?php
date_default_timezone_set("Asia/Kolkata");
defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_notes extends CI_Controller
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
            $this->load->view('sales/delivery_notes');
        } else {
            redirect('error_access');
        }
    }

    public function datatablesTemp($delivery_order_no, $delivery_note_date)
    {
        $delivery_order_no = explode(",", base64_decode($delivery_order_no));
        $delivery_note_date = base64_decode($delivery_note_date);

        $this->db->select("a.delivery_order_no, a.customer_order_no,
            a.sales_order_no,
            b.id as item_fg_id, 
            b.number_customer, 
            b.number as item_fg_number, 
            b.name as item_fg_name,
            c.shipping as qty,
            COALESCE(d.stock_fg, 0) as stock_fg,
            (CASE WHEN a.delivery_order_date >= '$delivery_note_date' THEN 0 ELSE 1 END) as status_delivery,
            b.uom");
        $this->db->from('delivery_orders a');
        $this->db->join("(SELECT item_fg_id, delivery_order_no, SUM(qty) AS shipping FROM shipping_orders GROUP BY item_fg_id, delivery_order_no) c", 'a.item_fg_id = c.item_fg_id and a.delivery_order_no = c.delivery_order_no');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
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
            ORDER BY a.number) d", 'a.item_fg_id = d.id', "LEFT");
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where_in('a.delivery_order_no', $delivery_order_no);
        $this->db->order_by('a.delivery_order_no');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readDo($customer_id, $plant)
    {
        $customer_id = base64_decode($customer_id);
        $plant = base64_decode($plant);
        $send = $this->crud->query("SELECT DISTINCT a.delivery_order_no 
            FROM delivery_orders a 
            JOIN shipping_orders b ON a.delivery_order_no = b.delivery_order_no 
            WHERE a.status = 0 and a.customer_id = '$customer_id' and a.plant = '$plant'");
        echo json_encode($send);
    }

    public function readDelivery_note_no($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT delivery_note_no, delivery_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readDelivery_order_no($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT delivery_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readSalesOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    // public function number($delivery_note_date)
    // {
    //     $customer_number = base64_decode($this->input->post('customer_number'));

    //     $numberCust = $customer_number;
    //     $divisions  = "DN";
    //     $datenow    = date("my", strtotime(base64_decode($delivery_note_date)));
    //     $dn_no      = $numberCust . "-" . $datenow;
    //     $sqlGetID   = $this->db->query("SELECT SUBSTR(delivery_note_no, 4, 4) as kode FROM delivery_notes WHERE `delivery_note_no` like '%$dn_no%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = @$rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%04s", $kode + 1);
    //     } else {
    //         $urutan = (int) $kode;
    //         $urutan++;
    //         $autoID = sprintf("%04s", $urutan);
    //     }
    //     echo $divisions. "-" . $autoID . "-" . $numberCust . "-" . $datenow;
    // }

    public function number($id_menu, $trans_date)
    {
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

            $sqlGetID    = $this->db->query("SELECT max(delivery_note_no) as kode FROM delivery_notes WHERE delivery_note_no like '%$whereDate%'");
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

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
            $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_status_delivery = @base64_decode($get['filter_status_delivery']);
            $filter_status = @base64_decode($get['filter_status']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name");
            $this->db->from('delivery_notes a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.delivery_note_date >=', $filter_from);
                $this->db->where('a.delivery_note_date <=', $filter_to);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
            $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            if ($filter_customer_order_no != "") {
                $this->db->like('a.customer_order_no', $filter_customer_order_no);
            }
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.status_delivery', $filter_status_delivery);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.delivery_note_no');
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
            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name');
            $this->db->from('delivery_notes a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            // $this->db->order_by('b.number', 'ASC');
            $this->db->order_by('a.delivery_order_no');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name');
            $this->db->from('delivery_notes a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
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

            $delivery_notes = $this->crud->read("delivery_notes", [], ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']]);

            if (@$delivery_notes->delivery_order_no != "") {
                $send = $this->crud->update('delivery_notes', ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], $post);
            } else {
                $send = $this->crud->create('delivery_notes', $post);

                //Ubah Status Sales Order Delivery
                $this->db->update("delivery_orders", ["status" => 1], ["item_fg_id" => $post['item_fg_id'], "delivery_order_no" => $post['delivery_order_no']]);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $delivery_notes = $this->crud->reads("delivery_notes", [], $data);
        foreach ($delivery_notes as $delivery_note) {
            $this->db->update("delivery_orders", ["status" => 0], ["item_fg_id" => $delivery_note->item_fg_id, "delivery_order_no" => $delivery_note->delivery_order_no]);
        }

        $send = $this->crud->delete('delivery_notes', $data);
        echo $send;
    }

    public function print_do($delivery_note_no)
    {
        $delivery_note_no = base64_decode($delivery_note_no);

        $delivery_notes = $this->crud->reads('delivery_notes', [], ["delivery_note_no" => $delivery_note_no]);
        $delivery_note = $this->crud->read('delivery_notes', [], ["delivery_note_no" => $delivery_note_no]);

        $approval = $this->crud->read('approvals', [], ["table_name" => "delivery_notes"]);
        $user_1 = $this->crud->read('users', [], ["username" => @$approval->user_approval_1]);

        if (!empty($approval->user_approval_2)) {
            $user_2 = $this->crud->read('users', [], ["username" => @$approval->user_approval_2]);
        } else {
            $user_2 = (object) ["name" => ""];
        }

        if (@$delivery_note->approved == 0) {
            $users_1 = '';
            $users_2 = '';
        } elseif (@$delivery_note->approved == 1) {
            $users_1 = '';
            $users_2 = '';
        } elseif (@$delivery_note->approved == 2) {
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . @$user_1->name . '.png') . '" width="100"/>';
            $users_2 = '';
        } else {
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . @$user_1->name . '.png') . '" width="100"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . @$user_2->name . '.png') . '" width="100"/>';
        }

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 25;
        $page = ceil(count($delivery_notes) / $rows);

        //Generate QRcode
        $this->createQrcode($delivery_note_no, "assets/image/qrcode/");
        $this->createQrcode(@$user_2->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_1->name, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $delivery_note_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
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
            $this->db->select('a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name, c.name as customer_name, 
            f.ship_address, f.bill_address, f.ship_telp, f.bill_telp, f.ship_contact, a.created_date as created_date');
            $this->db->from('delivery_notes a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('customer_address f', 'c.id = f.customer_id and a.plant = f.plant', 'left');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(25, ($i * 25));
            $records = $this->db->get()->result_array();

            $html .= '<div style="border: 1px solid black; width:100%; height:73%;">
            <div style="padding:10px; position:relative; top:10px;">
                <div style="text-align:center;">
                    <h3 style="margin:0;">DELIVERY NOTE</h3>
                </div>
                <br><br><br><br>
                <div style="position:absolute; top:10px; right:5px; display:flex; align-items:center;">
                    <div>
                        <img src="' . base_url('assets/image/qrcode/' . $delivery_note_no . '.png') . '" width="60" style="margin-right:5px;" />
                    </div>
                    <div>
                        <table style="width:50%; font-size:10px; text-align:left;">
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
                    </div>
                </div>
                <div style="float:left; width:50%;">
                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                        <tr>
                            <td width="100">Customer Name</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['customer_name'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Ship To</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['ship_address'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Bill To</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['bill_address'] . '</td>
                        </tr>
                            <tr>
                            <td width="100">Country Of Origin</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['ship_country'] . '</td>
                        </tr>
                        <tr>
                            <td width="150">Sailing On</td>
                            <td width="5">:</td>
                            <td><b>' . @$records[0]['sailing'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Attention</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['ship_contact'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Phone No.</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['ship_telp'] . '</td>
                        </tr>
                    </table>
                </div>
                <div style="float:right; width:40%;">
                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                        <tr>
                            <td width="100">Delivery Note No</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['delivery_note_no'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Ship By</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['ship_by'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Vehicle No</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['vehicle_no'] . '</td>
                        </tr>
                        <tr>
                            <td width="100">Incoterms</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['incoterm'] . '</b></td>
                        </tr>
                        <tr>
                            <td width="100">Delivery Date</td>
                            <td width="5">:</td>
                            <td>' . date("d F Y", strtotime(@$delivery_note->delivery_note_date)) . '</td>
                        </tr>
                        <tr>
                            <td width="100">Created Date</td>
                            <td width="5">:</td>
                            <td>' . date("d F Y", strtotime(@$records[0]['created_date'])) . '</td>
                        </tr>
                        <tr>
                            <td width="100">Note</td>
                            <td width="5">:</td>
                            <td>' . @$records[0]['note'] . '</td>
                        </tr>
                    </table>
                </div>
                <table id="customers">
                    <tr>
                        <th width="20">No</th>
                        <th>Delivery Order No</th>
                        <th>Product No EBWS</th>
                        <th>Product No Customer</th>
                        <th>Description</th>
                        <th>UoM</th>
                        <th width="60">Qty</th>
                        <th width="120">Customer Order No</th>
                        <th width="100">Note</th>
                    </tr>';

            $total_qty = 0;
            $no = 1;
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['delivery_order_no'] . '</td>
                                <td>' . $record['item_fg_number'] . '</td>
                                <td>' . $record['number_customer'] . '</td>
                                <td>' . $record['item_fg_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right">' . number_format($record['qty'], 2, ",", ".") . '</td>
                                <td>' . $record['customer_order_no'] . '</td>
                                <td>' . $record['note'] . '</td>
                            </tr>';
                $total_qty += $record['qty'];
                $no++;
            }
            $html .= '<tr>
            <td colspan="6" style="text-align:right; font-weight:bold;">Total</td>
            <td style="text-align:right; font-weight:bold;">' . number_format($total_qty, 2, ",", ".") . '</td>
            <td colspan="2"></td>
          </tr>';

            $html .= '</table>';

            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $html .= '</div></div>';
            if (($i + 1) == $page) {
                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                                <table id="customers" style="margin-top:10px;">
                                    <tr>
                                        <th width="200" style="text-align:center;">CUSTOMER STAMP & SIGNATURE By</th>
                                        <th width="200" style="text-align:center;">AUTHORISED SIGNATURES</th>
                                        <th width="200" style="text-align:center;">DELIVERY CONTROL</th>
                                    </tr>
                                    <tr>
                                        <th style="height:120px;"></th>
                                        <th style="height:120px;">' . $users_2 . '</th>
                                        <th style="height:120px;">' . $users_1 . '</th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;">' . @$user_2->name . '</th>
                                        <th style="height:20px; text-align:center;">' . @$user_1->name . '</th>
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
        $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_status = @base64_decode($get['filter_status']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, e.number_customer, e.name as item_fg_name");
        $this->db->from('delivery_notes a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('customer_address d', 'b.id = d.customer_id');
        $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id');
        $this->db->join('item_fg e', 'a.item_fg_id = e.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.delivery_note_date >=', $filter_from);
            $this->db->where('a.delivery_note_date <=', $filter_to);
        }
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
        $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('c.customer_order_no', $filter_customer_order_no);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->like('a.status', $filter_status);
        $this->db->group_by('a.delivery_note_no');
        $this->db->order_by('a.status', 'ASC');
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
                <h3>DELIVERY NOTE</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Delivery Note No</th>
                <th>Delivery Note Date</th>
                <th>Delivery Order No</th>
                <th>Product No EBWS</th>
                <th>Product No Customer</th>
                <th>Description</th>
                <th>Sales Order No</th>
                <th>Customer Order No</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Note</th>
                <th>Status</th>
              
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['delivery_note_no'] . '</td>
                        <td>' . $data['delivery_note_date'] . '</td>
                        <td>' . $data['delivery_order_no'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['note'] . '</td>
                        <td>' . $data['status'] . '</td>
                       
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
