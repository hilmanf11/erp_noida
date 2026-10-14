<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Issued_others extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('Ciqrcode');
        $this->load->library('session');
        $this->load->model('crud');

        //Validasi Form
        $this->form_validation->set_rules('item_fg_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('warehouse/issued_others');
        } else {
            redirect('error_access');
        }
    }

    public function readRequestno()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sql = $this->db->query("SELECT DISTINCT request_no, request_name, trans_date FROM issued_others_request WHERE request_no like '%$post%' and request_name like '%$post%'");
        $results = $sql->result_array();
        echo json_encode($results);
    }

    public function readRequestname()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sql = $this->db->query("SELECT DISTINCT request_name FROM issued_others_request ORDER BY request_name ASC");
        $results = $sql->result_array();
        echo json_encode($results);
    }

    public function readItem()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sql = $this->db->query("SELECT b.id as item_rm_id, b.number as item_rm_number, b.name as item_rm_name, d.qty as issued, 
            (COALESCE(c.qty_receipt,0) + COALESCE(e.qty_rgp_in,0) + COALESCE(g.sto_qty_in, 0) - COALESCE(i.sto_qty_out, 0) - COALESCE(f.qty_rgp_out,0) - COALESCE(d.qty,0)) as stock
            FROM item_rm b
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE store != 0 GROUP BY item_rm_id) c ON b.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others GROUP BY item_rm_id) d ON b.id = d.item_rm_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and (approved_to = '' or approved_to is null) GROUP BY item_id) e ON b.id = e.item_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and (approved_to = '' or approved_to is null) GROUP BY item_id) f ON b.id = f.item_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_in FROM sto_ot WHERE type = 'IN' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) g ON b.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_out FROM sto_ot WHERE type = 'OUT' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) i ON b.id = i.item_rm_id
            WHERE (b.id like '%$post%' or b.number like '%$post%') and b.item_family_id NOT IN ('P61','P67','P51','P69','P71') and (b.approved_to is null or b.approved_to = '')
            GROUP BY b.id
            HAVING stock > 0
            LIMIT 100");
        $results = $sql->result_array();

        echo json_encode($results);
    }

    public function request_no($id_menu)
    {
        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(request_no) as kode FROM issued_others_request WHERE request_no like '%$whereDate%'");
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

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_request_no = $this->input->get('filter_request_no');
            $filter_request_name = $this->input->get('filter_request_name');
            $filter_location = $this->input->get('filter_location');
            $filter_status = $this->input->get('filter_status');
            $filter_part_no = $this->input->get('filter_part_no');

            //Pagination 1-10
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 20;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom, d.name as location_other_name');
            $this->db->from('issued_others_request a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('warehouse_location_others d', 'a.location_other_number = d.number', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
            if ($filter_request_no != "") {
                $this->db->where('a.request_no', $filter_request_no);
            }
            if ($filter_request_name != "") {
                $this->db->where('a.request_name', $filter_request_name);
            }
            if ($filter_location != "") {
                $this->db->where('a.location_other_number', $filter_location);
            }
            if ($filter_part_no != "") {
                $this->db->where('c.id', $filter_part_no);
            }
            if ($filter_status != "") {
                $this->db->where('a.status', $filter_status);
            }
            $this->db->group_by('a.request_no');
            $this->db->group_by('c.id');
            $this->db->order_by('a.request_no', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $issued_others_request = $this->crud->read("issued_others_request", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);

            if (!$issued_others_request) {
                $send = $this->crud->create('issued_others_request', $post);
                echo $send;
            } else {
                $send = $this->crud->update('issued_others_request', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $id = $post['id'];
            $send = $this->crud->update('issued_others_request', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('issued_others_request', $data);
        echo $send;
    }

    public function print_request($request_no)
    {
        $kanbans = $this->crud->reads('issued_others_request', [], ["request_no" => base64_decode($request_no)]);
        $kanban = $this->crud->read('issued_others_request', [], ["request_no" => base64_decode($request_no)]);
        $warehouse_location_others = $this->crud->read('warehouse_location_others', [], ["number" => @$kanban->location_other_number]);
        $config = $this->db->get('config')->row();
        $rows = 8;
        $page = ceil(count($kanbans) / $rows);
        //Generate QRcode
        $this->createQrcode($kanban->request_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $kanban->request_no . '</title>
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
                            text-align: left;color: black;
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
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
            $this->db->from('issued_others_request a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.request_no', base64_decode($request_no));
            $this->db->group_by('a.item_rm_id');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();
            $html .= '<table>
                            <tr>
                                <th style="padding:10px;"><img src="' . $config->favicon . '" width="50" /></th>
                                <td width="700" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>REQUEST CONSUMABLE</u></h3>
                                </center>
                                <div style="float:left; width:45%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Request No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Request Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($kanban->trans_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Request Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->request_name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:45%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Location Store</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$warehouse_location_others->name . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Print Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y") . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Print By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$this->session->name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:10%; text-align:right;">
                                    <img src="' . base_url('assets/image/qrcode/' . $kanban->request_no . '.png') . '" width="60"/>
                                </div> 
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th width="120">Part No EBWS</th>
                                        <th width="150">Part No Supplier</th>
                                        <th>Description</th>
                                        <th width="50">Uom</th>
                                        <th width="50">Qty</th>
                                    </tr>';
            $no = 1;
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_number'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:center;">' . $record['qty'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>
                <br>
                <table id="customers">
                    <tr>
                        <th style="text-align:center;">Request By</th>
                        <th style="text-align:center;">Warehouse</th>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <td style="height:20px; text-align:center;">' . @$kanban->request_name . '</td>
                        <td style="height:20px;"></td>
                    </tr>
                </table>
                </div>
            </div>';
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }
        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=issued_others_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_request_name = $this->input->get('filter_request_name');
        $filter_location = $this->input->get('filter_location');
        $filter_status = $this->input->get('filter_status');
        $filter_part_no = $this->input->get('filter_part_no');

        //Select Query
        $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom, d.name as location_other_name');
        $this->db->from('issued_others_request a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('warehouse_location_others d', 'a.location_other_number = d.number', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.trans_date >=', $filter_from);
        $this->db->where('a.trans_date <=', $filter_to);
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        if ($filter_request_name != "") {
            $this->db->where('a.request_name', $filter_request_name);
        }
        if ($filter_location != "") {
            $this->db->where('a.location_other_number', $filter_location);
        }
        if ($filter_part_no != "") {
            $this->db->where('c.id', $filter_part_no);
        }
        if ($filter_status != "") {
            $this->db->where('a.status', $filter_status);
        }
        $this->db->group_by('a.request_no');
        $this->db->group_by('c.id');
        $this->db->order_by('a.request_no', 'DESC');
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
                    <h3>DATA REQUISITION ITEM</h3>
                </div>
            </center>
            
            <table id="sto_fg" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Requisition No</th>
                    <th>Requisition Date</th>
                    <th>Requisition Name</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Uom</th>
                    <th>Stock</th>
                    <th>Qty</th>
                    <th>Balance</th>
                    <th>Location Store</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $data['request_no'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['request_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['stock']) . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . number_format($data['balance']) . '</td>
                            <td>' . $data['location_other_name'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
