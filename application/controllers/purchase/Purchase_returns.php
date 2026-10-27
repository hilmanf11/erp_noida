<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class purchase_returns extends CI_Controller
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
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_returns');
        } else {
            redirect('error_access');
        }
    }

    function readPono(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($this->input->get('supplier_id'));
        $source = $this->input->get('source');

        if($source == "QAS"){
            $datas = $this->crud->query("SELECT a.po_no 
                FROM purchase_order_receipts a 
                WHERE a.supplier_id = '$supplier_id' and a.status = 2
                GROUP BY a.po_no 
                ORDER BY a.po_no DESC");
            echo json_encode($datas);
        }elseif($source == "REJECT"){
            $datas = $this->crud->query("SELECT a.po_no 
                FROM scan_item_rejections a 
                JOIN purchase_order_receipts b ON a.po_no = b.po_no 
                WHERE b.supplier_id = '$supplier_id' and a.status = 0
                GROUP BY a.po_no 
                ORDER BY a.po_no DESC");
            echo json_encode($datas);
        }elseif($source == "SCRAP"){
            $datas = $this->crud->query("SELECT document as po_no FROM scraps a 
                JOIN supplier_items b ON a.item_rm_id = b.item_rm_id and b.share_order > 0 
                WHERE b.supplier_id = '$supplier_id' and a.source = 'Vendor' and a.status = 0
                GROUP BY a.document 
                ORDER BY a.document DESC");
            echo json_encode($datas);
        }
    }

    public function readProducts($po_no, $source, $supplier_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $po_no = base64_decode($po_no);
        $supplier_id = base64_decode($supplier_id);

        if($source == "QAS"){
            $data = $this->crud->query("SELECT b.id, b.number, b.name, a.receipt_id, round(a.qty_receipt - COALESCE(SUM(c.qty), 0)) as qty_receipt, b.uom, a.bc_document
                FROM purchase_order_receipts a 
                JOIN item_rm b ON a.item_rm_id = b.id
                LEFT JOIN purchase_returns c ON a.receipt_id = c.receipt_id and a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id
                WHERE a.po_no = '$po_no' and (b.number LIKE '%$post%' and b.id like '%$post%') and a.status = 2
                GROUP BY a.item_rm_id, a.receipt_id
                ORDER BY b.number asc");
            echo json_encode($data);
        }elseif($source == "REJECT"){
            $data = $this->crud->query("SELECT b.id, b.number, b.name, a.receipt_id, round(SUM(a.qty) - COALESCE(SUM(c.qty), 0)) as qty_receipt, b.uom, d.bc_document
                FROM scan_item_rejections a 
                JOIN purchase_order_receipts d ON a.receipt_id = d.receipt_id
                JOIN item_rm b ON d.item_rm_id = b.id
                LEFT JOIN purchase_returns c ON a.receipt_id = c.receipt_id and b.id = c.item_rm_id and d.supplier_id = c.supplier_id
                WHERE a.po_no = '$po_no' and (b.number LIKE '%$post%' and b.id like '%$post%') and a.status = 0
                GROUP BY d.id
                ORDER BY b.number asc");
            echo json_encode($data);
        }elseif($source == "SCRAP"){
            $data = $this->crud->query("SELECT b.id, b.number, b.name, a.document as receipt_id, round(SUM(a.qty) - COALESCE(SUM(d.qty), 0)) as qty_receipt, b.uom
                FROM scraps a 
                JOIN item_rm b ON a.item_rm_id = b.id
                JOIN supplier_items c ON a.item_rm_id = c.item_rm_id and c.share_order > 0
                LEFT JOIN purchase_returns d ON a.document = d.receipt_id and d.item_rm_id = a.item_rm_id and d.supplier_id = c.supplier_id
                WHERE a.document = '$po_no' and c.supplier_id = '$supplier_id' and (b.number LIKE '%$post%' and b.id like '%$post%') and a.status = 0
                GROUP BY a.item_rm_id
                ORDER BY b.number asc");
            echo json_encode($data);
        }
    }

    public function readReturnNo()
    {
        $records = $this->crud->query("SELECT a.return_no, a.return_date, a.return_name, a.po_no, a.supplier_id, b.name as supplier_name 
            FROM purchase_returns a
            JOIN suppliers b ON a.supplier_id = b.id
            WHERE a.status = 0 GROUP BY a.return_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function return_no()
    {
        $datenow    = date("ymd");
        $sqlGetID   = $this->db->query("SELECT max(return_no) as kode FROM purchase_returns WHERE return_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "PRTN-" . $datenow . "-" . $autoID;
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_return_no = $this->input->get('filter_return_no');
        $filter_supplier = $this->input->get('filter_supplier');
        $filter_item_id = $this->input->get('filter_item_id');

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
            $this->db->select('return_no, return_date, return_name, po_no, sum(a.qty) as qty, a.status, c.name as supplier_name');
            $this->db->from('purchase_returns a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.return_date >=', $filter_from);
                $this->db->where('a.return_date <=', $filter_to);
            }
            $this->db->like('a.return_no', $filter_return_no);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.item_rm_id', $filter_item_id);
            $this->db->group_by('return_no');
            $this->db->order_by('a.return_no', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['return_no'],
                    "return_no" => $record['return_no'],
                    "return_date" => $record['return_date'],
                    "supplier_name" => $record['supplier_name'],
                    "return_name" => $record['return_name'],
                    "qty" => $record['qty'],
                    "status" => $record['status'],
                    "state" => "closed",
                    "datatable" => 1,
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, 
                b.number as item_rm_number, 
                b.name as item_rm_name, 
                d.name as supplier_name,
                b.uom,
                c.mpq');
                // b.specification,
            $this->db->from('purchase_returns a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('supplier_items c', 'c.item_rm_id = b.id and a.supplier_id = c.supplier_id');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.return_date >=', $filter_from);
                $this->db->where('a.return_date <=', $filter_to);
            }
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.item_rm_id', $filter_item_id);
            $this->db->where('a.return_no', $id);
            $this->db->order_by('d.name', 'ASC');
            $records = $this->db->get()->result_array();

            $arr = array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['id'],
                    "return_no" => $record['return_no'],
                    "return_date" => $record['return_date'],
                    "return_name" => $record['return_name'],
                    "receipt_id" => $record['receipt_id'],
                    "item_rm_id" => $record['item_rm_id'],
                    "item_rm_number" => $record['item_rm_number'],
                    "item_rm_name" => $record['item_rm_name'],
                    "po_no" => $record['po_no'],
                    "invoice_no" => $record['invoice_no'],
                    "supplier_name" => $record['supplier_name'],
                    "remarks" => $record['remarks'],
                    "uom" => $record['uom'],
                    "qty" => $record['qty'],
                    "mpq" => $record['mpq'],
                    "status" => $record['status'],
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date'],
                );//"description" => $record['description'],
            }
            echo json_encode($arr);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $purchase_returns = $this->crud->read('purchase_returns', [], ["return_no" => $post['return_no'], "item_rm_id" => $post['item_rm_id']]);

                if (@$purchase_returns->id != "") {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $post['item_rm_id'] . " Data Duplicated", "theme" => "error"));
                } else {
                    $send = $this->crud->create('purchase_returns', $post);
                    $this->crud->update('scan_item_rejections', ["receipt_id" => $post['receipt_id']], ["status" => 1]);
                    $this->crud->update('scraps', ["document" => $post['receipt_id'], "item_rm_id" => $post['item_rm_id']], ["status" => 1]);
                    echo $send;
                }
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
            $po_no = $this->crud->read("purchase_orders", [], ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']]);

            if($po_no){
                $send = $this->crud->update('purchase_returns', ["id" => $id], $post);
                echo $send;
            }else{
                show_error("PO No and Part No Not Found");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_returns', $data);
        $this->db->update('scan_item_rejections', ["status" => 0], ["receipt_id" => $data['receipt_id']]);
        $this->db->update('scraps', ["status" => 0], ["document" => $data['receipt_id'], "item_rm_id" => $data['item_rm_id']]);
        echo $send;
    }

    public function print_return($return_no)
    {
        $return_no = base64_decode($return_no);

        $config = $this->db->get('config')->row();

        $this->db->select('a.*, 
            b.number as item_rm_number, 
            b.name as item_rm_name,  
            b.uom, 
            b.hsn_code,
            d.id as supplier_number, 
            d.name as supplier_name,
            d.type,
            d.gst, 
            d.address,
            d.state,
            (CASE WHEN e.price is null THEN g.price ELSE e.price END) as price,
            (a.qty * (CASE WHEN e.price is null THEN g.price ELSE e.price END)) as value,
            (((a.qty * (CASE WHEN e.price is null THEN g.price ELSE e.price END)) * c.igst) / 100) as igst,
            (((a.qty * (CASE WHEN e.price is null THEN g.price ELSE e.price END)) * c.sgst) / 100) as sgst,
            (((a.qty * (CASE WHEN e.price is null THEN g.price ELSE e.price END)) * c.cgst) / 100) as cgst');
            // b.specification,
        $this->db->from('purchase_returns a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('hsn c', 'b.hsn_code = c.number', 'left');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('purchase_orders e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id and a.po_no = e.po_no', 'left');
        $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id and a.supplier_id = g.supplier_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.return_no', $return_no);
        $this->db->group_by('a.id');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html>
                    <head>
                        <title>' . $return_no . '</title>
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
                        </center>
                    </div>
                    <div class="print">';
        $html .= '  <table id="customers">
                        <tr>
                            <td colspan="14" style="font-size:15px;" align="center"><b>Delivery Challan cum Debit Note</b><br><i><u>Under Rule-55 </u><b>(2)</b> of CGST Act 2017</i></td>
                        </tr>
                        <tr>
                            <td colspan="14" align="center"><b>' . @$config->name . ' PVT. LTD.</b><br> ' . @$config->address2 . '<br>GSTIN : ' . @$config->gst_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="9">Consignee</td>
                            <td colspan="6"></td>
                        </tr>
                        <tr>
                            <td colspan="9" rowspan="3"><b>' . @$records[0]['supplier_name'] . '</b><br>' . @$records[0]['address'] . '<br>GSTN : ' . @$records[0]['gst'] . '</td>
                            <td colspan="3"><b>Documen No</b></td>
                            <td colspan="4"><b>' . $return_no . '</b></td>
                        </tr>
                        <tr>
                            <td colspan="3"><b>Date</b></td>
                            <td colspan="4"><b>' . @$records[0]['return_date'] . '</b></td>
                        </tr>
                        <tr>
                            <td colspan="3"><b>Note</b></td>
                            <td colspan="4">' . @$records[0]['source'] . '</td>
                        </tr>
                            <tr>
                            <th>NO</th>
                            <th>PART NO EBWS</th>
                            <th>PART NO SUPPLIER</th>
                            <th>PO NO</th>
                            <th>INVOICE NO</th>
                            <th>UOM</th>
                            <th>QTY</th>
                            <th>HSN</th>
                            <th>PRICE</th>
                            <th>VALUE</th>
                            <th>IGST</th>
                            <th>CGST</th>
                            <th>SGST</th>
                            <th>TOTAL VALUE</th>
                        </tr>';
        $no = 1;
        $total_qty = 0;
        $total_amount = 0;
        foreach ($records as $record) {
            if($record['state'] == $config->state){
                $igst = 0;
                $sgst = $record['sgst'];
                $cgst = $record['cgst'];
            }else{
                if($record['type'] == "LOCAL"){
                    $igst = $record['igst'];
                    $sgst = 0;
                    $cgst = 0;
                }else{
                    $igst = 0;
                    $sgst = 0;
                    $cgst = 0;
                }
            }
            
            $html .= '
                <tr>
                    <td align="center">' . $no . '</td>
                    <td>' . $record['item_rm_id'] . '</td>
                    <td>' . $record['item_rm_number'] . '</td>
                    <td>' . $record['po_no'] . '</td>
                    <td>' . $record['invoice_no'] . '</td>
                    <td>' . $record['uom'] . '</td>
                    <td align="right">' . $record['qty'] . '</td>
                    <td align="center">' . $record['hsn_code'] . '</td>
                    <td align="right">' . @number_format(($record['price']), 4) . '</td>
                    <td align="right">' . @number_format(($record['value']), 2) . '</td>
                    <td align="right">' . @number_format(($igst), 2) . '</td>
                    <td align="right">' . @number_format(($cgst), 2) . '</td>
                    <td align="right">' . @number_format(($sgst), 2) . '</td>
                    <td align="right">' . @number_format(($record['value'] + $igst + $cgst + $sgst), 2) . '</td>
                </tr>';

            $total_qty += $record['qty'];
            $total_amount += ($record['value'] + $igst + $cgst + $sgst);
            $no++;
        }

        $html .= '
                <tr>
                    <td colspan="6" align="right"><b>TOTAL</b></td>
                    <td align="right"><b>' . $total_qty . '</b></td>
                    <td colspan="4" align="right"><b>TOTAL AMOUNT</b></td>
                    <td colspan="5" align="right"><b>' . @number_format(($total_amount), 2) . '</b></td>
                </tr>
                <tr>
                    <td colspan="7" rowspan="2" style="vertical-align: text-top;">Declaration :<br></td>
                    <td colspan="8"><b>For ' . @$config->name . '</b></td>
                </tr>
                <tr>
                    <td colspan="8" style="height:100px; vertical-align: bottom; text-align:center;"><b>AUTHORISED SIGNATORY</b></td>
                </tr>
        </table>';

        $html .= "</div><script>window.print()</script></body>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_returns_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_return_no = $this->input->get('filter_return_no');
        $filter_supplier = $this->input->get('filter_supplier');
        $filter_item_id = $this->input->get('filter_item_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_rm_id, b.name as item_name, b.uom, c.name as supplier_name');
        $this->db->from('purchase_returns a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.return_date >=', $filter_from);
            $this->db->where('a.return_date <=', $filter_to);
        }
        $this->db->like('a.return_no', $filter_return_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.item_rm_id', $filter_item_id);
        $this->db->order_by('a.return_no', 'ASC');
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
                                <small>PURCHASE RETURN TO SUPPILER</small>
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
                <th>Return No</th>
                <th>Return Date</th>
                <th>Return Name</th>
                <th>Supplier Name</th>
                <th>Purchase Order</th>
                <th>Invoice No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Uom</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "OPEN";
            } else {
                $status = "CLOSED";
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['return_no'] . '</td>
                        <td>' . $data['return_date'] . '</td>
                        <td>' . $data['return_name'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['invoice_no'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
