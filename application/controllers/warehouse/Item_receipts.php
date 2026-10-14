<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Item_receipts extends CI_Controller
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
            $this->load->view('warehouse/item_receipts');
        } else {
            redirect('error_access');
        }
    }
    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $label_no = strtoupper($this->input->post('label_no'));
            $this->db->select('b.po_no, b.receipt_no, b.receipt_id, a.label_no, a.qty');
            $this->db->from('purchase_order_labels a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('b.status != 2');
            $this->db->where('a.label_no', $label_no);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            if (!$records) {
                $this->db->select('c.po_no, c.receipt_no, c.receipt_id, a.label_divided as label_no, a.qty');
                $this->db->from('barcode_divides a');
                $this->db->join('purchase_order_labels b', 'a.reff = b.label_no');
                $this->db->join('purchase_order_receipts c', 'b.receipt_id = c.receipt_id');
                $this->db->where('a.label_divided', $label_no);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if (!$records) {
                    $this->db->select("('RETURN MATERIAL') as po_no, b.return_no as receipt_no, b.return_id as receipt_id, a.label_no, a.qty");
                    $this->db->from('return_material_labels a');
                    $this->db->join('return_materials b', 'a.return_id = b.return_id');
                    $this->db->where('a.label_no', $label_no);
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
    
    public function datatables($label_no = "")
    {
        // $date = date("Y-m-d");
        $purchase_order_label = $this->crud->read('purchase_order_labels', [], ["label_no" => base64_decode($label_no)]);
        //Select Query
        $this->db->select('a.label_no, e.name as supplier_name, b.receipt_no, b.bc_kind, b.bc_document, b.bc_date, b.po_no, d.id as item_rm_id, d.number as item_rm_number, d.name as item_rm_name, d.uom, a.qty, a.created_by, a.created_date');
        $this->db->from('scan_item_receipts a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id and a.receipt_no = b.receipt_no and a.po_no = b.po_no');
        $this->db->join('item_rm d', 'b.item_rm_id = d.id');
        $this->db->join('suppliers e', 'b.supplier_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.receipt_id', @$purchase_order_label->receipt_id);
        // $this->db->like('a.created_date', $date);
        $this->db->group_by('a.label_no');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        if (!$records) {
            $return_material_labels = $this->crud->read('return_material_labels', [], ["label_no" => base64_decode($label_no)]);
            $this->db->select('a.label_no, b.return_no as receipt_no, a.po_no, d.id as item_rm_id, d.number as item_rm_number, d.name as item_rm_name, a.qty, a.created_by, a.created_date');
            $this->db->from('scan_item_receipts a');
            $this->db->join('return_materials b', 'a.receipt_id = b.return_id and a.receipt_no = b.return_no');
            $this->db->join('return_material_labels c', 'a.label_no = c.label_no');
            $this->db->join('item_rm d', 'b.item_rm_id = d.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            // $this->db->like('a.created_date', $date);
            $this->db->where('a.receipt_id', @$return_material_labels->return_id);
            $this->db->group_by('a.label_no');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
        }

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
                $item_receipts = $this->crud->read("scan_item_receipts", [], ["label_no" => strtoupper($post['label_no'])]);
                $purchase_order_label = $this->crud->read("purchase_order_labels", [], ["label_no" => strtoupper($post['label_no'])]);

                $sql = $this->db->query("SELECT a.receipt_id, a.qty_label, COUNT(b.id) as jumlah 
                    FROM purchase_order_receipts a 
                    JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id 
                    WHERE a.receipt_id = '$purchase_order_label->receipt_id' 
                    GROUP BY a.receipt_id 
                    HAVING a.qty_label != COUNT(b.id)");
                $row = $sql->row();

                if(@$row->qty_label == @$row->jumlah){
                    if (!$item_receipts) {
                        $send   = $this->crud->create('scan_item_receipts', array_merge($post, array("label_no" => strtoupper($post['label_no']))), "logs");
                        if ($send) {
                            $this->db->update('purchase_order_labels', ["status" => 1], ["label_no" => strtoupper($post['label_no'])]);
                            $this->db->update('return_material_labels', ["status" => 1], ["label_no" => strtoupper($post['label_no'])]);
                            echo $send;
                        }
                    } else {
                        echo json_encode(array("title" => "Available", "message" => "Data Label No has been Scanned", "theme" => "error"));
                    }
                }else{
                    echo json_encode(array("title" => "Not Match", "message" => "Gate Entry qty label not match in the total label", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
