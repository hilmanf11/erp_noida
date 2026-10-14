<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Item_rejections extends CI_Controller
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
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/item_rejections');
        } else {
            redirect('error_access');
        }
    }

    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');

            $this->db->select('b.po_no, b.receipt_no, b.receipt_id, a.label_no, a.qty, a.item_rm_id');
            $this->db->from('scan_item_receipts a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('a.label_no', $label_no);
            $this->db->where('a.status', 0);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

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
            ((COALESCE(SUM(c.qty_sto_in),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(SUM(cc.qty_sto_out),0) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS end_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto_in FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.category = 'IN' and a.trans_date < '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto_out FROM os_rm a JOIN os_rm_labels b ON a.id  = b.receipt_id WHERE a.category = 'OUT' and a.trans_date < '$date' GROUP BY item_rm_id) cc ON a.id = cc.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date < '$date' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date < '$date' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$date' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$date' GROUP BY a.item_id) j ON a.id = j.item_id
            LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$date' GROUP BY a.item_id) k ON a.id = k.item_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
            WHERE a.return_date < '$date'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.id = '$item_rm_id'
            GROUP BY a.id");

        return empty(@$records[0]->end_stock) ? 0 : @$records[0]->end_stock;
        // }
    }

    public function datatables($label_no = "")
    {
        $date = date("Y-m-d");
        $purchase_order_label = $this->crud->read('scan_item_receipts', [], ["label_no" => base64_decode($label_no)]);
        //Select Query
        $this->db->select('a.label_no, e.name as supplier_name, b.receipt_no, b.bc_kind, b.bc_document, b.bc_date, b.po_no, d.id as item_rm_id, d.number as item_rm_number, d.name as item_rm_name, d.uom, a.qty, a.created_by, a.created_date');
        $this->db->from('scan_item_rejections a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id and a.receipt_no = b.receipt_no and a.po_no = b.po_no');
        $this->db->join('item_rm d', 'b.item_rm_id = d.id');
        $this->db->join('suppliers e', 'b.supplier_id = e.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like('a.created_date', $date);
        $this->db->where('a.receipt_id', @$purchase_order_label->receipt_id);
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

                $issued_material_details = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);
                if ($issued_material_details) {
                    die(json_encode(array("title" => "Available", "message" => "Data label has been Scanning", "theme" => "error")));
                }

                $scan_rgp = $this->crud->read("scan_item_rgp", [], ["label_no" => $post['label_no'], "type" => "OUT"]);
                if (!empty($scan_rgp)) {
                    die(json_encode(array("title" => "Available", "message" => "The Label has been Scan Out in RGP", "theme" => "error")));
                }

                if ((round($this->readEndingStock($post['item_rm_id'])) - round($post['qty'])) < 0) {
                    die(json_encode(array("title" => "Stock Empty", "message" => "Stock In Warehouse is Empty", "theme" => "error")));
                }

                unset($post['item_rm_id']);
                $item_receipts = $this->crud->read("scan_item_rejections", [], ["label_no" => $post['label_no']]);
                if (!$item_receipts) {
                    $send   = $this->crud->create('scan_item_rejections', $post);
                    if ($send) {
                        $this->crud->update('scan_item_receipts', ["label_no" => $post['label_no']], ["status" => 1]);
                        $this->crud->update('purchase_order_labels', ["label_no" => $post['label_no']], ["status" => 2]);
                        // $update   = $this->crud->update('return_material_labels', ["label_no" => $post['label_no']], ["status" => 1]);
                        echo $send;
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
