<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Rgp_scan_out extends CI_Controller
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
        $this->form_validation->set_rules('rgp_number', 'Number', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/rgp_scan_out');
        } else {
            redirect('error_access');
        }
    }

    public function readDocument()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("
        select * from (
            select a.number, COALESCE(sum(a.qty),0) as total_qty, COALESCE(b.total_scan,0) as total_scan, COALESCE(sum(a.qty),0)-COALESCE(b.total_scan,0) as balance
            from rgp_transactions a 
            left join (select rgp_number, COALESCE(sum(qty),0) as total_scan from scan_item_rgp group by rgp_number) b on a.number=b.rgp_number
            where a.type='OUT' and a.number LIKE '%$post%' group by a.number
        ) x where (x.total_qty -x.total_scan) <> 0");
        echo json_encode($send);
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

    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');

            // cek ke receipt RM
            $this->db->select('b.po_no, b.receipt_no, b.receipt_id, a.label_no, a.qty, b.item_rm_id as item_id');
            $this->db->from('scan_item_receipts a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('a.label_no', $label_no);
            $this->db->where('a.status', 0);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            if ($records == []) {
                // cek ke sto
                $this->db->select('b.label_no, a.item_rm_id as item_id, b.qty');
                $this->db->from('os_rm a');
                $this->db->join('scan_item_receipts b', 'a.id = b.receipt_id');
                $this->db->where('b.label_no', $label_no);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if ($records == []) {
                    $this->db->select('b.label_no, a.item_id, b.qty');
                    $this->db->from('rgp_transactions a');
                    $this->db->join('scan_item_receipts b', 'a.rgp_id = b.receipt_id');
                    $this->db->where('b.label_no', $label_no);
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();

                    if ($records == []) {
                        $this->db->select('b.label_no, a.item_rm_id as item_id, b.qty');
                        $this->db->from('return_materials a');
                        $this->db->join('scan_item_receipts b', 'a.return_id = b.receipt_id');
                        $this->db->where('b.label_no', $label_no);
                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();

                        if ($records == []) {
                            $this->db->select('a.label_divided as label_no, b.item_rm_id as item_id, a.qty');
                            $this->db->from('barcode_divides a');
                            $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                            $this->db->where('a.label_divided', $label_no);
                            $this->db->where('a.status', '0');
                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();

                            if ($records == []) {
                                $this->db->select('a.label_divided as label_no, b.item_rm_id as item_id, a.qty');
                                $this->db->from('barcode_divides a');
                                $this->db->join('os_rm b', 'a.reff = b.id');
                                $this->db->where('a.label_divided', $label_no);
                                $this->db->where('a.status', '0');
                                $totalRows = $this->db->count_all_results('', false);
                                $records = $this->db->get()->result_array();

                                if ($records == []) {
                                    // cek ke receipt FG
                                    $this->db->select("b.item_fg_id as item_id, a.checksheet_label as label_no, a.qty");
                                    $this->db->from('scan_item_receipts_fg a');
                                    $this->db->join('wip_receipts b', 'a.workorder = b.workorder and a.checksheet_number = b.checksheet_number');
                                    $this->db->where('a.checksheet_label', $label_no);
                                    $this->db->where('a.status', 0);
                                    $totalRows = $this->db->count_all_results('', false);
                                    $records = $this->db->get()->result_array();

                                    if ($records == []) {
                                        $this->db->select("a.item_fg_id as item_id, a.label_divided as label_no, a.qty");
                                        $this->db->from('barcode_divides_fg a');
                                        $this->db->where('a.label_divided', $label_no);
                                        $this->db->where('a.status', '0');
                                        // $this->db->where('a.type', 'SUPPLY');
                                        $this->db->group_by('a.label_divided');
                                        $totalRows = $this->db->count_all_results('', false);
                                        $records = $this->db->get()->result_array();

                                        if ($records == []) {
                                            $this->db->select("b.item_fg_id as item_id, a.checksheet_label as label_no, a.qty");
                                            $this->db->from('scan_item_receipts_fg a');
                                            $this->db->join('os_fg b', 'a.workorder = b.id');
                                            $this->db->where('a.checksheet_label', $label_no);
                                            $this->db->where('a.status', '0');
                                            $this->db->group_by('a.checksheet_label');
                                            $totalRows = $this->db->count_all_results('', false);
                                            $records = $this->db->get()->result_array();

                                            if ($records == []) {
                                                $this->db->select("b.item_id, a.checksheet_label as label_no, a.qty");
                                                $this->db->from('scan_item_receipts_fg a');
                                                $this->db->join('rgp_transactions b', 'a.checksheet_number = b.rgp_id');
                                                $this->db->where('a.checksheet_label', $label_no);
                                                $this->db->where('a.status', '0');
                                                $this->db->group_by('a.checksheet_label');
                                                $totalRows = $this->db->count_all_results('', false);
                                                $records = $this->db->get()->result_array();

                                                if ($records == []) {
                                                    $this->db->select("b.item_fg_id as item_id, a.checksheet_label as label_no, a.qty");
                                                    $this->db->from('scan_item_receipts_fg a');
                                                    $this->db->join('sales_returns b', 'a.checksheet_number = b.return_id');
                                                    $this->db->where('a.checksheet_label', $label_no);
                                                    $this->db->where('a.status', '0');
                                                    $this->db->group_by('a.checksheet_label');
                                                    $totalRows = $this->db->count_all_results('', false);
                                                    $records = $this->db->get()->result_array();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatables($label_no = "")
    {
        $date = date("Y-m-d");
        $rgp_transaction = $this->crud->read('scan_item_rgp', [], ["label_no" => base64_decode($label_no), "type" => "OUT"]);
        //Select Query
        $this->db->select("
        a.label_no, d.uom, a.qty,a.created_date,a.created_by,
        (CASE 
            WHEN b.id is not null THEN b.id
            ELSE c.number
        END) as item_id,
        (CASE 
            WHEN b.id is not null THEN b.number
            ELSE c.number_customer
        END) as item_number,
        (CASE 
            WHEN b.id is not null THEN b.name
            ELSE c.name
        END) as item_name,
        (CASE 
                WHEN b.id is not null THEN f.name
                ELSE null
            END) as item_family_name,
        e.name as item_category_name,
        ");
        $this->db->from('scan_item_rgp a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->join('rgp_transactions d', 'a.rgp_number = d.number');
        $this->db->join('item_categories e', 'd.item_category_id = e.id', 'left');
        $this->db->join('item_familys f', 'b.item_family_id = f.id', 'left');
        $this->db->where('d.number', @$rgp_transaction->rgp_number);
        $this->db->where('a.type', 'OUT');
        $this->db->group_by('a.label_no');
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
                $post   = $this->input->post();
                $scan_item_rgp = $this->crud->read("scan_item_rgp", [], ["label_no" => $post['label_no'], "type" => "OUT"]);
                $issued_materials = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);

                if(!empty($issued_materials)){
                    die(json_encode(array("title" => "Exist Issued", "message" => "This Label No already Scan Issued", "theme" => "error")));
                }

                if (!$scan_item_rgp) { // yg belum di scan
                    $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $post['rgp_number'], "item_id" => $post['item_id']]);
                    if (!empty(@$rgp_transactions->item_id)) {

                        if($rgp_transactions->process_type == "NRGP"){
                            die(json_encode(array("title" => "Error", "message" => "This Process is NRGP, only allow RGP and FOC", "theme" => "error")));
                        }

                        if (@$rgp_transactions->approved_to == '' || @$rgp_transactions->approved_to == null) { // cek document RGP sudah di approve
                            $cek_qty = $this->crud->query("
                            select a.number, a.item_id, COALESCE(sum(a.qty),0) as total_qty, COALESCE(b.total_scan,0) as total_scan, COALESCE(sum(a.qty),0)-COALESCE(b.total_scan,0) as balance
                            from rgp_transactions a 
                            left join (select rgp_number, item_id, COALESCE(sum(qty),0) as total_scan from scan_item_rgp where type = 'OUT' group by rgp_number, item_id) b on a.number=b.rgp_number and a.item_id=b.item_id
                            where a.type='OUT' and a.number = '" . $post['rgp_number'] . "' and a.item_id='" . $post['item_id'] . "' group by a.number, a.item_id
                            ");

                            $item_rm = $this->crud->read("item_rm", [], ["id" => $post['item_id']]);
                            if (!empty(@$item_rm->id)) { // jika RM
                                // $item_receipts = $this->crud->read("scan_item_receipts", [], ["label_no" => $post['label_no']]);
                                // if (!$item_receipts) {
                                //     $item_receipts = $this->crud->read("barcode_divides", [], ["label_divided" => $post['label_no']]);
                                // }

                                if(($this->readEndingStock($post['item_id']) - $post['qty']) < 0){
                                    die(json_encode(array("title" => "Stock Empty", "message" => "Stock In Warehouse is Empty", "theme" => "error")));
                                }

                                if ((@$cek_qty[0]->total_scan + $post['qty']) > @$cek_qty[0]->total_qty) {
                                    echo json_encode(array("title" => "Qty Label is Over", "message" => "Qty in Barcode more than with required Qty Item", "theme" => "error"));
                                } else {
                                    $send = $this->crud->create('scan_item_rgp', array_merge($post, ["type" => "OUT"]));

                                    if ($send) {
                                        $this->crud->update('scan_item_receipts', ["label_no" => $post['label_no']], ["status" => 1]);
                                        $this->crud->update('barcode_divides', ["label_divided" => $post['label_no']], ["status" => 1]);
                                        echo $send;
                                    }
                                }
                            } else { // jika FG
                                // $item_receipts_fg = $this->crud->read("scan_item_receipts_fg", [], ["checksheet_label" => $post['label_no']]);
                                // if (!$item_receipts_fg) {
                                //     $item_receipts_fg = $this->crud->read("barcode_divides_fg", [], ["label_divided" => $post['label_no']]);
                                // }

                                if ((@$cek_qty[0]->total_scan + $post['qty']) > @$cek_qty[0]->total_qty) {
                                    echo json_encode(array("title" => "Qty Label is Over", "message" => "Qty in Barcode more than with required Qty Item", "theme" => "error"));
                                } else {
                                    $send = $this->crud->create('scan_item_rgp', array_merge($post, ["type" => "OUT"]));

                                    if ($send) {
                                        $this->crud->update('scan_item_receipts_fg', ["checksheet_label" => $post['label_no']], ["status" => 1]);
                                        $this->crud->update('barcode_divides_fg', ["label_divided" => $post['label_no']], ["status" => 1]);
                                        echo $send;
                                    }
                                }
                            }
                        } else {
                            echo json_encode(array("title" => "Need Approve", "message" => "RGP Number Document Need to Approve", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Item Not Same", "message" => "Item in Barcode is Not Same with required Item", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Available", "message" => "Data Label No has been Scanned", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
