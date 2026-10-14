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

    public function updateSto(){
        // $this->dummy = $this->load->database('dummy', TRUE);
        // $sto = $this->dummy->query("SELECT serialno from serial_barcode_outgoing where itemid is null ORDER BY serialno asc limit 40");
        // $dataSto = $sto->result_array();

        // foreach ($dataSto as $to) {
        //     $id = $to['serialno'];

        //     $this->db->select("(CASE WHEN b.item_rm_id is null THEN c.item_rm_id ELSE b.item_rm_id END) as item_rm_id");
        //     $this->db->from('barcode_divides a');
        //     $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id', 'left');
        //     $this->db->join('os_rm c', 'a.reff = c.id', 'left');
        //     $this->db->where('a.label_divided', $id);
        //     $row = $this->db->get()->row();

        //     $item_rm = $this->crud->read("item_rm", [], ["id" => $row->item_rm_id]);
            
        //     $this->dummy->update('serial_barcode_outgoing', ['itemid' => $item_rm->number, 'itemname' => $item_rm->name], ["serialno" => $id]);
        //     echo $id." | ".$item_rm->number."<br>";
        // }
    }
    
    public function getSupplySheet()
    {
        if ($this->input->post()) {
            $request_no = $this->input->post('request_no');

            $readIssued = $this->crud->reads("issued_materials", [], ["request_no" => $request_no]);
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

            if(count($records) != count($readIssued)){
                foreach ($records as $record) {
                    $item_fg_id = $record['item_fg_id'];
                    $item_rm_id = $record['item_rm_id'];
                    $request_no = $record['request_no'];
                    $period = $record['period'];
                    $wp = $record['wp'];
                    $workorder = $record['workorder'];
                    $qty = $record['qty_act'];
                    
                    $dataFinal = array(
                        "item_fg_id" => $item_fg_id,
                        "item_rm_id" => $item_rm_id,
                        "request_no" => $request_no,
                        "period" => $period,
                        "wp" => $wp,
                        "workorder" => $workorder,
                        "qty" => $qty,
                    );
    
                    $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $request_no, "workorder" => $workorder, "item_fg_id" => $item_fg_id, "item_rm_id" => $item_rm_id]);
                    if (!$issued_materials) {
                        $send = $this->crud->create('issued_materials', $dataFinal);
                        $this->db->update('supply_requestions', ["status" => 0], ["request_no" => $request_no, "item_rm_id" => $item_rm_id]);
                        // echo $send;
                    } else {
                        $send = $this->crud->update('issued_materials', ["request_no" => $request_no, "workorder" => $workorder, "item_fg_id" => $item_fg_id, "item_rm_id" => $item_rm_id], $dataFinal);
                        // echo $send;
                    }
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $receipt_id = $this->input->post('receipt_id');

            $this->db->select('a.label_no, b.item_rm_id, a.qty');
            $this->db->from('scan_item_receipts a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('b.status != 2');
            $this->db->where('a.label_no', $receipt_id);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            if (!$records) {
                $this->db->select('b.label_no, a.item_rm_id, b.qty');
                $this->db->from('os_rm a');
                $this->db->join('scan_item_receipts b', 'a.id = b.receipt_id');
                $this->db->where('b.label_no', $receipt_id);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();
                
                if (!$records) {
                    $this->db->select('a.label_divided as label_no, b.item_rm_id, a.qty');
                    $this->db->from('barcode_divides a');
                    $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                    $this->db->where('a.label_divided', $receipt_id);
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();

                    if (!$records) {
                        $this->db->select('a.label_divided as label_no, b.item_rm_id, a.qty');
                        $this->db->from('barcode_divides a');
                        $this->db->join('os_rm b', 'a.reff = b.id');
                        $this->db->where('a.label_divided', $receipt_id);
                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();
                    }
                }
            }
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    
    public function datatables()
    {
        if ($this->input->get()) {
            $request_no = base64_decode($this->input->get('request_no'));
            //Select Query
            $this->db->select('a.*, b.number_customer as item_fg_number, c.number as item_rm_number, c.name as item_rm_name, 
                c.uom, COALESCE(d.qty_req, 0) as qty_req, 
                (COALESCE(d.qty_req,0) - a.qty) as qty_bal');
            $this->db->from('issued_materials a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('(SELECT item_rm_id, request_no, SUM(qty) as qty_req FROM issued_material_details GROUP BY request_no, item_rm_id) d', 'd.request_no = a.request_no and d.item_rm_id = a.item_rm_id', 'left');
            // $this->db->join('uom e', 'c.uom_id = e.id');
            // $this->db->join('wip_balances f', 'a.item_rm_id = f.item_rm_id and a.request_no = f.request_no', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            // if ($request_no != "") {
            $this->db->where('a.request_no', $request_no);
            // }
            $this->db->group_by('a.request_no');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.item_rm_id', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
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
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
                if (!$issued_materials) {
                    $send = $this->crud->create('issued_materials', $post);
                    echo $send;
                } else {
                    $send = $this->crud->update('issued_materials', ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    
    public function create_label()
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        
        if ($this->input->post()) {
            $post = $this->input->post();
            $request_no = $post['request_no'];
            $item_rm_id = $post['item_rm_id'];
            $issued_material_details = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);

            if (!$issued_material_details) {
                $barcode_divides = $this->crud->read("barcode_divides", [], ["label_divided" => $post['label_no'], "status" => 0]);
                $scan_item_receipts = $this->crud->read("scan_item_receipts", [], ["label_no" => $post['label_no']]);
                $scan_item_rejections = $this->crud->read("scan_item_rejections", [], ["label_no" => $post['label_no']]);
                $barcode_dividel = $this->crud->read("barcode_divides", [], ["label_no" => $post['label_no']]);
                $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);

                if(empty($scan_item_rejections) && empty($barcode_dividel)){
                    if ($scan_item_receipts) {
                        if ($issued_materials) {

                            $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id'");

                            $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $scan_item_receipts->receipt_id]);
                            $trans_date = @$purchase_order_receipts->receipt_date;
                            if(empty($purchase_order_receipts)){
                                $purchase_order_receipts = $this->crud->read("os_rm", [], ["id" => $scan_item_receipts->receipt_id]);
                                $trans_date = @$purchase_order_receipts->trans_date;
                            }

                            $checkItems = $this->crud->query("SELECT a.receipt_date, b.label_no as label_1, a.receipt_id, b.receipt_id, c.label_no as label_2, d.label_no as label_3
                            FROM purchase_order_receipts a 
                            LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                            LEFT JOIN barcode_divides c ON b.label_no = c.label_no
                            LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and b.label_no = d.label_no
                            WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$trans_date' AND d.label_no is null and c.label_no is null and b.status = 1 and a.status != 2
                            ORDER BY receipt_date ASC");

                            if (($totalSupply[0]->qty + $post['qty']) <= $issued_materials->qty || $issued_materials->qty == "0") {
                                if (count($checkItems) <= 0) {
                                    $supply_sheet = $this->crud->read("supply_sheets", [], ["request_no" => $request_no]);
                                    $customer_item = $this->crud->read("customer_items", [], ["item_fg_id" => @$issued_materials->item_fg_id]);
                                    $customer = $this->crud->read("customers", [], ["id" => @$customer_item->customer_id]);
                                    $item_rm = $this->crud->read("item_rm", [], ["id" => @$item_rm_id]);

                                    $send = $this->crud->create('issued_material_details', $post);
                                    @$this->dummy->insert('serial_barcode_outgoing', [
                                        "serialno" => strtoupper($post['label_no']),
                                        "outscan" => date("Y-m-d H:i:s"),
                                        "outform" => "OU - ".@$supply_sheet->type." ".@$customer->number,
                                        "outdocno" => $request_no,
                                        "outqty" => round($post['qty']),
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
                                        "serialform" => "OU - ".@$supply_sheet->type." ".@$customer->number,
                                        "serialdocno" => $request_no,
                                        "serialqtyreceipt" => round($post['qty']),
                                        "itemid" => @$item_rm->number,
                                        "itemname" => @$item_rm->name,
                                        "statusscanincoming" => "t",
                                        "statusreceive" => "t",
                                        "por_urut_poc" => 1,
                                    ]);
                                    
                                    $this->db->update('scan_item_receipts', ["status" => 1], ["label_no" => $post['label_no']]);
                                    echo $send;
                                } else {
                                    $fifo = "";

                                    foreach ($checkItems as $checkItem) {
                                        $fifo .= $checkItem->label_1.$checkItem->label_2.$checkItem->label_3." | ";
                                    }
                                    
                                    echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially $fifo", "theme" => "error"));
                                }
                            } else {
                                echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                            }
                        } else {
                            echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                        }
                    } elseif ($barcode_divides) {
                        if ($issued_materials) {
                            $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id'");
                            $supply_sheet = $this->crud->read("supply_sheets", [], ["request_no" => $request_no]);
                            $customer_item = $this->crud->read("customer_items", [], ["item_fg_id" => @$issued_materials->item_fg_id]);
                            $customer = $this->crud->read("customers", [], ["id" => @$customer_item->customer_id]);
                            $item_rm = $this->crud->read("item_rm", [], ["id" => @$item_rm_id]);

                            $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $barcode_divides->reff]);
                            $receipt_date = @$purchase_order_receipts->receipt_date;
                            if(empty($purchase_order_receipts)){
                                $purchase_order_receipts = $this->crud->read("os_rm", [], ["id" => $barcode_divides->reff]);
                                $receipt_date = @$purchase_order_receipts->trans_date;
                            }

                            $checkItems = $this->crud->query("SELECT a.receipt_date, c.label_divided, c.label_no as label_1, a.receipt_id, b.receipt_id, d.label_no as label_2
                            FROM purchase_order_receipts a
                            LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                            LEFT JOIN barcode_divides c ON b.label_no = c.label_no and c.type = 'SUPPLY'
                            LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and (b.label_no = d.label_no or c.label_divided = d.label_no)
                            WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$receipt_date' AND c.status = 0 AND d.label_no is null and b.status = 1 and a.status != 2
                            ORDER BY receipt_date ASC");

                            if (($totalSupply[0]->qty + $post['qty']) <= $issued_materials->qty || $issued_materials->qty == "0") {
                                if (count($checkItems) <= 0) {
                                    $send = $this->crud->create('issued_material_details', $post);
                                    @$this->dummy->insert('serial_barcode_outgoing', [
                                        "serialno" => strtoupper($post['label_no']),
                                        "outscan" => date("Y-m-d H:i:s"),
                                        "outform" => "OU - ".@$supply_sheet->type." ".@$customer->number,
                                        "outdocno" => $request_no,
                                        "outqty" => round($post['qty']),
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
                                        "serialform" => "OU - ".@$supply_sheet->type." ".@$customer->number,
                                        "serialdocno" => $request_no,
                                        "serialqtyreceipt" => round($post['qty']),
                                        "itemid" => @$item_rm->number,
                                        "itemname" => @$item_rm->name,
                                        "statusscanincoming" => "t",
                                        "statusreceive" => "t",
                                        "por_urut_poc" => 1,
                                    ]);

                                    $this->crud->update('barcode_divides', ["label_divided" => $post['label_no']], ["status" => 1]);
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
                            echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Scanned In", "message" => "This label has not been scanned in", "theme" => "error"));
                    }
                }else{
                    echo json_encode(array("title" => "Not Registered", "message" => "This label already Scan Rejection or Barcode Divided", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Available", "message" => "Data label has been Scanning", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
