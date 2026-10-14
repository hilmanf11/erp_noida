<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_credits extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/purchase_credits');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $number = base64_decode($number);
        $this->db->select('a.*, c.id as item_id, c.number as item_number, c.name as item_name, d.name as uom, b.currency, b.name as supplier_name');
        $this->db->from('purchase_credits a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('uom d', 'c.uom_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_id');
        $this->db->join('purchase_orders f', 'a.po_no = f.po_no and b.id = f.supplier_id and c.id = f.item_id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.number', $number);
        $this->db->group_by('a.item_id');
        $this->db->order_by('c.number', 'asc');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readReturnNo()
    {
        $data = $this->crud->query("SELECT DISTINCT pr_no FROM purchase_credits ORDER BY `pr_no` ASC");
        echo json_encode($data);
    }

    public function readPurchaseOrder()
    {
        $data = $this->crud->query("SELECT DISTINCT po_no FROM purchase_credits ORDER BY `po_no` ASC");
        echo json_encode($data);
    }

    public function readItems($po_no)
    {
        $purchase_order = base64_decode($po_no);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $data = $this->crud->query("SELECT DISTINCT b.id, b.number, b.name 
            FROM purchase_credits a 
            JOIN items b ON a.item_id = b.id 
            WHERE a.po_no = '$purchase_order' and b.number like '%$post%' ORDER BY b.name ASC");
        echo json_encode($data);
    }

    public function number($trans_date)
    {
        $datenow    = "PC-" . date("Ymd", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM purchase_credits WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . "-" . $autoID;
    }

    public function datatablesTemp()
    {
        $pr_no = base64_decode($this->input->get('pr_no'));

        $this->db->select("a.item_id, a.supplier_id, c.number as item_number, 
            c.name as item_name, f.qty, d.name as uom, f.price, b.currency, a.qty as returned, 
            ((SUM(a.qty) * f.price) - (SUM(a.qty) * f.price) * (f.discount / 100)) as total,
            (CASE WHEN g.selling is null THEN ((SUM(a.qty) * f.price) - (SUM(a.qty) * f.price) * (f.discount / 100)) ELSE
            ((SUM(a.qty) * (f.price * g.selling)) - (SUM(a.qty) * (f.price * g.selling)) * (f.discount / 100)) END) as total_local");
        $this->db->from('purchase_returns a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('uom d', 'c.uom_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_id');
        $this->db->join('purchase_orders f', 'a.po_no = f.po_no and b.id = f.supplier_id and c.id = f.item_id');
        $this->db->join('exchange_rates g', "b.currency = g.currency_from and g.currency_to = 'IDR'", 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.return_no', $pr_no);
        $this->db->group_by('a.item_id');
        $this->db->order_by('a.return_no', 'asc');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        foreach ($records as $record) {
            $total_sub += $record['total_local'];
            $obj[] = array(
                "item_id" => $record['item_id'],
                "supplier_id" => $record['supplier_id'],
                "item_number" => $record['item_number'],
                "item_name" => $record['item_name'],
                "uom" => $record['uom'],
                "currency" => $record['currency'],
                "qty" => $record['qty'],
                "returned" => $record['returned'],
                "price" => round($record['price'], 2),
                "total" => $record['total'],
                "total_local" => $record['total_local']
            );
        }

        $arr['rows'] = @$obj;
        $arr['total_sub'] = round($total_sub, 4);
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_from  = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_purchase_return = base64_decode($this->input->get('filter_purchase_return'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name');
            $this->db->from('purchase_credits a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.pr_no', $filter_purchase_return);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.po_no', $filter_purchase_order);
            $this->db->like('a.item_id', $filter_product_no);
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->group_by('a.number');
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
        } else {
            $number = base64_decode($this->input->get('number'));

            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('purchase_credits a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.item_id');
            $this->db->order_by('a.trans_date', 'DESC');
        }
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $purchase_credits = $this->crud->read('purchase_credits', [], ["pr_no" => $post['pr_no'], "item_id" => $post['item_id'], "supplier_id" => $post['supplier_id']]);

                if (@$purchase_credits->id != "") {
                    $send = $this->crud->update('purchase_credits', ["pr_no" => $post['pr_no'], "item_id" => $post['item_id'], "supplier_id" => $post['supplier_id']], $post);
                    echo $send;
                } else {
                    $send = $this->crud->create('purchase_credits', $post);
                    if ($send) {
                        $this->crud->update('purchase_returns', ["return_no" => $post['pr_no'], "item_id" => $post['item_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                    }
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();

        $purchase_credits = $this->crud->reads("purchase_credits", [], ["number" => $data['number']]);
        foreach ($purchase_credits as $purchase_credit) {
            $this->crud->update("purchase_returns", [
                "return_no" => $purchase_credit->pr_no,
                "po_no" => $purchase_credit->po_no,
                "item_id" => $purchase_credit->item_id,
                "supplier_id" => $purchase_credit->supplier_id
            ], ["status" => 0]);
        }

        $send = $this->crud->delete('purchase_credits', $data);
        echo $send;
    }

    public function print_debit_note($pr)
    {
        $pr_no = base64_decode($pr);
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_credits a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.pr_no', $pr_no);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->group_by('a.pr_no');
        $payment_total = $this->db->get()->result_array();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 8;
        $page = ceil(count($payment_total) / $rows);
        //Generate QRcode
        $this->createQrcode(@$pr_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $pr_no . '</title>
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
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as supplier_name, c.number as item_number, c.name as item_name');
            $this->db->from('purchase_credits a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('items c', 'a.item_id = c.id');
            $this->db->like('a.pr_no', $pr_no);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();

            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $pr_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_debit . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_debit . '</td>
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
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u style="padding:5px;">DEBIT NOTE</u></h3>
                                </center>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Supplier Name</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Purchase Return No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['pr_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Purchase Order No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['po_no'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Document No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$records[0]['number'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Date</td>
                                            <td width="30">:</td>
                                            <td><b>' . @date("d F Y", strtotime(@$records[0]['trans_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Remarks</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['remarks'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th>UoM</th>
                                        <th>Qty PO</th>
                                        <th>Returned</th>
                                        <th>Currency</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>';
            $grand_total = 0;
            foreach ($records as $record) {
                $grand_total += $record['total'];
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_number'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['qty']), 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['returned'], 2) . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format($record['price'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['total'], 2) . '</td>
                            </tr>';
                $no++;
            }

            $html .= '  <tr>
                            <th style="text-align:right" colspan="8">GRAND TOTAL</th>
                            <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
                        </tr>';

            $html .= '</table>
                <br>
                <table style="width:100%; font-size:12px;">
                    <tr>
                        <td style="text-align:center;">Prepared By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Approved By</td>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Accounting Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_debit_note_$format.xls");
        }

        $filter_from  = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_purchase_return = base64_decode($this->input->get('filter_purchase_return'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_credits a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.pr_no', $filter_purchase_return);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.item_id', $filter_product_no);
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->group_by('a.number');
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
                                <small>REPORT PURCHASE CREDIT NOTE</small><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Document No</th>
                    <th>Trans Date</th>
                    <th>PR No</th>
                    <th>PO No</th>
                    <th>Supplier Name</th>
                    <th>Grand Total</th>
                    <th colspan="2">Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $number = $data['number'];

            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('purchase_credits a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.item_id');
            $this->db->order_by('a.trans_date', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['pr_no'] . '</td>
                            <td>' . $data['po_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="text-align:right;">' . number_format($data['total_sub'], 4) . '</td>
                            <td colspan="2">' . $data['remarks'] . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="9" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['number'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>Qty PO</th>
                            <th>Qty Return</th>
                            <th>UoM</th>
                            <th>Currency</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['item_number'] . '</td>
                                <td>' . $detail['item_name'] . '</td>
                                <td style="text-align:right">' . number_format($detail['qty'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['returned'], 2) . '</td>
                                <td>' . $detail['uom'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['price'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['total'], 2)  . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table>';
        $html .= '  <table id="customers" style="margin-top:20px; width:50%;">
                        <tr>
                            <th width="200" style="text-align:center;">Approval By</th>
                            <th width="200" style="text-align:center;">Dept Manager</th>
                            <th width="200" style="text-align:center;">Created By</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                        </tr>
                    </table></body></html>';
        echo $html;
    }
}
