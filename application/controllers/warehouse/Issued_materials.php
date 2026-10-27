<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Issued_materials extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
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
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/issued_materials');
        } else {
            redirect('error_access');
        }
    }
    
    public function getSupplySheet()
    {
        if ($this->input->post()) {
            $request_no = $this->input->post('request_no');

            // $readIssued = $this->crud->reads("issued_materials", [], ["request_no" => $request_no]);
            $totalRows = 1;
            $records = array();

            $this->db->select('a.*, b.number as item_fg_number, c.id as item_rm_id, c.number as item_rm_number, c.name as item_rm_name, c.uom, d.period, d.wp');
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('production_schedules d', 'a.workorder = d.workorder and a.item_fg_id = d.item_fg_id');
            $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
            $this->db->where('a.request_no', $request_no);
            // $this->db->where('d.period !=', '202410');
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            if ($totalRows <= 0) {
                $this->db->select("a.*, '-' as workorder, a.qty as qty_act, b.number as item_fg_number, c.id as item_rm_id, c.number as item_rm_number, c.name as item_rm_name, b.uom");
                $this->db->from('supply_materials a');
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->where('a.approved_to', "");
                $this->db->where('a.request_no', $request_no);
                $totalRows = $this->db->count_all_results('', false);
                //Get Data Array
                $records = $this->db->get()->result_array();
                if ($totalRows <= 0) {
                    $this->db->select("a.*, a.qty as qty_act, b.number as item_fg_number, c.id as item_rm_id, c.number as item_rm_number, c.name as item_rm_name, b.uom");
                    $this->db->from('supply_requestions a');
                    $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                    $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                    // $this->db->where('a.approved_to', "");
                    $this->db->where('a.request_no', $request_no);
                    $totalRows = $this->db->count_all_results('', false);
                    //Get Data Array
                    $records = $this->db->get()->result_array();
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function readEndingStock($item_rm_id)
    {
        // if ($this->input->post()) {
            $date = date("Y-m-d");

            $records = $this->crud->query("SELECT
                a.id,
                ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS end_stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date <= '$date' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date <= '$date' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date <= '$date' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$date' GROUP BY a.item_id) j ON a.id = j.item_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$date' GROUP BY a.item_id) k ON a.id = k.item_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
                WHERE a.return_date <= '$date'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.id = '$item_rm_id'
            GROUP BY a.id
            ORDER BY b.number, a.number");

            return empty(@$records[0]->end_stock) ? 0 : @$records[0]->end_stock;
        // }
    }
    
    public function datatables()
    {
        if ($this->input->get()) {
            $request_no = base64_decode($this->input->get('request_no'));
            //Select Query
            $this->db->select('a.request_no, a.qty_act, a.workorder, a.mpq, a.request_name, a.request_date, b.number_customer as item_fg_number, a.item_rm_id, c.number as item_rm_number, c.name as item_rm_name, e.period, e.wp,
                c.uom, COALESCE(d.qty_req, 0) as qty_req, 
                (COALESCE(d.qty_req,0) - a.qty_act) as qty_bal');
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('production_schedules e', 'a.workorder = e.workorder');
            $this->db->join("(SELECT item_rm_id, request_no, SUM(qty) as qty_req FROM issued_material_details WHERE request_no = '$request_no' GROUP BY request_no, item_rm_id) d", 'd.request_no = a.request_no and d.item_rm_id = a.item_rm_id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where('a.request_no', $request_no);
            $this->db->group_by('a.request_no');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.item_rm_id', 'ASC');
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            if (!$records) {
                $this->db->select('a.request_no, a.qty as qty_act, a.workorder, a.request_name, a.request_date, b.number_customer as item_fg_number, a.item_rm_id, c.number as item_rm_number, c.name as item_rm_name,
                    c.uom, COALESCE(d.qty_req, 0) as qty_req, 
                    (COALESCE(d.qty_req,0) - a.qty) as qty_bal');
                $this->db->from('supply_materials a');
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join("(SELECT item_rm_id, request_no, SUM(qty) as qty_req FROM issued_material_details WHERE request_no = '$request_no' GROUP BY request_no, item_rm_id) d", 'd.request_no = a.request_no and d.item_rm_id = a.item_rm_id', 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->where('a.request_no', $request_no);
                $this->db->group_by('a.request_no');
                $this->db->group_by('a.item_rm_id');
                $this->db->order_by('a.item_rm_id', 'ASC');
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if (!$records) {
                    $this->db->select('a.request_no, a.qty as qty_act, a.workorder, a.request_name, a.request_date, b.number_customer as item_fg_number, a.item_rm_id, c.number as item_rm_number, c.name as item_rm_name,
                        c.uom, COALESCE(d.qty_req, 0) as qty_req, (COALESCE(d.qty_req,0) - a.qty) as qty_bal');
                    $this->db->from('supply_requestions a');
                    $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                    $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                    $this->db->join("(SELECT item_rm_id, request_no, SUM(qty) as qty_req FROM issued_material_details WHERE request_no = '$request_no' GROUP BY request_no, item_rm_id) d", 'd.request_no = a.request_no and d.item_rm_id = a.item_rm_id', 'left');
                    $this->db->where('a.deleted', 0);
                    $this->db->where('a.status', 0);
                    $this->db->where('a.request_no', $request_no);
                    $this->db->group_by('a.request_no');
                    $this->db->group_by('a.item_rm_id');
                    $this->db->order_by('a.item_rm_id', 'ASC');
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();
                }
            }
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    
    public function create_label()
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        
        if ($this->input->post()) {
            $post = $this->input->post();

            $request_no = $post['request_no'];
            $label_no = $this->input->post('label_no');
            
            $this->db->select('a.label_no, b.item_rm_id, a.qty');
            $this->db->from('scan_item_receipts a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('b.status != 2');
            $this->db->where('a.label_no', $label_no);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            // die(json_encode($totalRows));
            
            if (!$records) {
                $this->db->select('a.label_no, b.item_id as item_rm_id, a.qty');
                $this->db->from('scan_item_receipts a');
                $this->db->join('rgp_transactions b', 'a.receipt_id = b.rgp_id');
                $this->db->where('a.label_no', $label_no);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if (!$records) {
                    $this->db->select('b.label_no, a.item_rm_id, b.qty');
                    $this->db->from('os_rm a');
                    $this->db->join('scan_item_receipts b', 'a.id = b.receipt_id');
                    $this->db->where('b.label_no', $label_no);
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();
                    
                    if (!$records) {
                        $this->db->select('a.label_divided as label_no, b.item_rm_id, a.qty');
                        $this->db->from('barcode_divides a');
                        $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                        $this->db->where('a.label_divided', $label_no);
                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();

                        if (!$records) {
                            $this->db->select('a.label_divided as label_no, b.item_rm_id, a.qty');
                            $this->db->from('barcode_divides a');
                            $this->db->join('os_rm b', 'a.reff = b.id');
                            $this->db->where('a.label_divided', $label_no);
                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
                        }
                    }
                }
            }

            if($totalRows == 0){
                die(json_encode(array("title" => "Not Found", "message" => "Label Not Found", "theme" => "error")));
            }

            $item_rm_id = $records[0]['item_rm_id'];
            $qty_label = $records[0]['qty'];

            if((round($this->readEndingStock($item_rm_id)) - round($qty_label)) < 0){
                die(json_encode(array("title" => "Stock Empty", "message" => "Stock In Warehouse is Empty", "theme" => "error")));
            }

            $scan_rgp = $this->crud->read("scan_item_rgp", [], ["label_no" => $post['label_no'], "type" => "OUT"]);
            if(!empty($scan_rgp)){
                die(json_encode(array("title" => "Available", "message" => "The Label has been Scan Out in RGP", "theme" => "error")));
            }
            
            $issued_material_details = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);
            if($issued_material_details){
                die(json_encode(array("title" => "Available", "message" => "Data label has been Scanning", "theme" => "error")));
            }
            
            $barcode_dividel = $this->crud->read("barcode_divides", [], ["label_no" => $post['label_no']]);
            $scan_item_rejections = $this->crud->read("scan_item_rejections", [], ["label_no" => $post['label_no']]);
            if(!empty($scan_item_rejections) || !empty($barcode_dividel)){
                die(json_encode(array("title" => "Not Registered", "message" => "This label already Scan Rejection or Barcode Divided", "theme" => "error")));
            }

            $supply_sheets = $this->crud->read("supply_sheets", [], ["request_no" => $post['request_no'], "item_rm_id" => $item_rm_id]);
            $supply_materials = $this->crud->read("supply_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $item_rm_id]);
            $supply_requestions = $this->crud->read("supply_requestions", [], ["request_no" => $post['request_no'], "item_rm_id" => $item_rm_id]);

            if($supply_sheets){
                $qty = $supply_sheets->qty_act;
                $item_fg_id = @$supply_sheets->item_fg_id;
                $type = $supply_sheets->type;
            }elseif($supply_materials){
                $qty = $supply_materials->qty;
                $item_fg_id = @$supply_materials->item_fg_id;
                $type = "SMP";
            }elseif($supply_requestions){
                $qty = $supply_requestions->qty;
                $item_fg_id = @$supply_requestions->item_fg_id;
                $type = "REQ";
            }else{
                die(json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error")));
            }

            $barcode_divides = $this->crud->read("barcode_divides", [], ["label_divided" => $post['label_no'], "status" => 0]);
            $scan_item_receipts = $this->crud->read("scan_item_receipts", [], ["label_no" => $post['label_no']]);
            if ($scan_item_receipts) {
                $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id'");
                $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $scan_item_receipts->receipt_id]);
                $trans_date = @$purchase_order_receipts->receipt_date;
                $item_id_po = @$purchase_order_receipts->item_rm_id;
                if(empty($purchase_order_receipts)){
                    $purchase_order_receipts = $this->crud->read("os_rm", [], ["id" => $scan_item_receipts->receipt_id]);
                    $trans_date = "2020-01-01";
                    $item_id_po = @$purchase_order_receipts->item_rm_id;
                }

                $checkItems = $this->crud->query("SELECT a.receipt_date, a.receipt_id, e.label_no AS label_1, c.label_divided AS label_2
                    FROM purchase_order_receipts a 
                    JOIN scan_item_receipts e ON a.receipt_id = e.receipt_id AND e.status = 0 AND e.label_no NOT IN (SELECT DISTINCT label_no FROM issued_material_details)
                    LEFT JOIN barcode_divides c ON a.receipt_id = c.reff AND c.status = 0 AND c.label_divided NOT IN (SELECT DISTINCT label_no FROM issued_material_details)
                    WHERE a.item_rm_id = '$item_id_po' AND a.receipt_date < '$trans_date' AND a.status != 2
                    ORDER BY a.receipt_date ASC");

                if (($totalSupply[0]->qty + $qty_label) <= $qty || $qty == "0") {
                    if (count($checkItems) <= 0) {

                        if($type == "PP"){
                            // die(json_encode("-"));
                            $customer_item = $this->crud->read("customer_items", [], ["item_fg_id" => $item_fg_id]);
                            $customer = $this->crud->read("customers", [], ["id" => @$customer_item->customer_id]);
                            $item_rm = $this->crud->read("item_rm", [], ["id" => @$item_rm_id]);

                            $this->dummy->select('serialno');
                            $this->dummy->from('serial_barcode_incoming');
                            $this->dummy->where('serialno', strtoupper($post['label_no']));
                            $serialno = $this->dummy->get()->result_array();

                            if(count($serialno) == 0){
                                @$this->dummy->insert('serial_barcode_outgoing', [
                                    "serialno" => strtoupper($post['label_no']),
                                    "outscan" => date("Y-m-d H:i:s"),
                                    "outform" => "OU - ".$type." ".@$customer->number,
                                    "outdocno" => $request_no,
                                    "outqty" => round($qty_label),
                                    "itemid" => @$item_rm->number,
                                    "itemname" => @$item_rm->name,
                                    "outemployee" => $this->session->name,
                                    "outstatus" => "t",
                                ]);
                                
                                @$this->dummy->insert('serial_barcode_incoming', [
                                    "serialno" => strtoupper($post['label_no']),
                                    "serialdate" => date("Y-m-d"),
                                    "scantime" => date("Y-m-d H:i:s"),
                                    "serialpo" => "PO ASTEK",
                                    "serialform" => "OU - ".$type." ".@$customer->number,
                                    "serialdocno" => $request_no,
                                    "serialqtyreceipt" => round($qty_label),
                                    "itemid" => @$item_rm->number,
                                    "itemname" => @$item_rm->name,
                                    "statusscanincoming" => "t",
                                    "statusreceive" => "t",
                                    "por_urut_poc" => 1,
                                ]);
                            }
                        }
                        
                        $this->db->update('scan_item_receipts', ["status" => 1], ["label_no" => $post['label_no']]);
                        $send = $this->crud->create('issued_material_details', ["request_no" => $post['request_no'], "label_no" => $post['label_no'], "item_rm_id" => $item_rm_id, "qty" => $qty_label], "logs");
                        echo $send;
                    } else {
                        $fifo = "";

                        foreach ($checkItems as $checkItem) {
                            $fifo .= $checkItem->label_1.$checkItem->label_2." | ";
                        }
                        
                        echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially $fifo", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                }
            } elseif ($barcode_divides) {
                $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id'");
                $customer_item = $this->crud->read("customer_items", [], ["item_fg_id" => @$item_fg_id]);
                $customer = $this->crud->read("customers", [], ["id" => @$customer_item->customer_id]);
                $item_rm = $this->crud->read("item_rm", [], ["id" => @$item_rm_id]);

                $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $barcode_divides->reff]);
                $receipt_date = @$purchase_order_receipts->receipt_date;
                $item_id_po = @$purchase_order_receipts->item_rm_id;
                if(empty($purchase_order_receipts)){
                    $purchase_order_receipts = $this->crud->read("os_rm", [], ["id" => $barcode_divides->reff]);
                    $receipt_date = "2020-01-01";
                    $item_id_po = @$purchase_order_receipts->item_rm_id;
                }

                $checkItems = $this->crud->query("SELECT a.receipt_date, c.label_divided, c.label_no as label_1, a.receipt_id, b.receipt_id, d.label_no as label_2
                FROM purchase_order_receipts a
                LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                LEFT JOIN barcode_divides c ON b.label_no = c.label_no and c.type = 'SUPPLY'
                LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and (b.label_no = d.label_no or c.label_divided = d.label_no)
                JOIN scan_item_receipts e ON b.label_no = e.label_no AND e.status = 0
                WHERE a.item_rm_id = '$item_id_po' and a.receipt_date < '$receipt_date' AND c.status = 0 AND d.label_no is null and b.status = 1 and a.status != 2
                ORDER BY receipt_date ASC");

                if (($totalSupply[0]->qty + $qty_label) <= $qty || $qty == "0") {
                    if (count($checkItems) <= 0) {

                        $this->dummy->select('serialno');
                        $this->dummy->from('serial_barcode_incoming');
                        $this->dummy->where('serialno', strtoupper($post['label_no']));
                        $serialno = $this->dummy->get()->result_array();

                        if(count($serialno) == 0){
                            @$this->dummy->insert('serial_barcode_outgoing', [
                                "serialno" => strtoupper($post['label_no']),
                                "outscan" => date("Y-m-d H:i:s"),
                                "outform" => "OU - ".$type." ".@$customer->number,
                                "outdocno" => $request_no,
                                "outqty" => round($qty_label),
                                "itemid" => @$item_rm->number,
                                "itemname" => @$item_rm->name,
                                "outemployee" => $this->session->name,
                                "outstatus" => "t",
                            ]);

                            @$this->dummy->insert('serial_barcode_incoming', [
                                "serialno" => strtoupper($post['label_no']),
                                "serialdate" => date("Y-m-d"),
                                "scantime" => date("Y-m-d H:i:s"),
                                "serialpo" => "PO ASTEK",
                                "serialform" => "OU - ".$type." ".@$customer->number,
                                "serialdocno" => $request_no,
                                "serialqtyreceipt" => round($qty_label),
                                "itemid" => @$item_rm->number,
                                "itemname" => @$item_rm->name,
                                "statusscanincoming" => "t",
                                "statusreceive" => "t",
                                "por_urut_poc" => 1,
                            ]);
                        }
                        
                        $this->crud->update('barcode_divides', ["label_divided" => $post['label_no']], ["status" => 1]);
                        $send = $this->crud->create('issued_material_details', ["request_no" => $post['request_no'], "label_no" => $post['label_no'], "item_rm_id" => $item_rm_id, "qty" => $qty_label], "logs");
                        echo $send;
                    } else {
                        $fifo = "";
                        foreach ($checkItems as $checkItem) {
                            $fifo .= $checkItem->label_1.$checkItem->label_2." | ";
                        }
                        
                        echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan label Sequentially $fifo", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Not Scanned In", "message" => "This label has not been scanned in", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
