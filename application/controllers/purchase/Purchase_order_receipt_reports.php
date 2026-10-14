<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order_receipt_reports extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_order_receipt_reports');
        } else {
            redirect('error_access');
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {

            $filter_from = $this->input->post('filter_from');
            $filter_to = $this->input->post('filter_to');
            $not_balance = $this->input->post('not_balance');

            //Select Query
            $this->db->select("a.item_rm_id, c.number as item_rm_number, c.name as item_rm_name, c.uom, a.receipt_id, a.receipt_date, a.po_no, a.supplier_id, b.name AS supplier_name, a.bc_document, a.bc_date, a.qty_receipt, a.qty_mpq, a.qty_label,
                COALESCE(d.label, 0) as label, COALESCE(e.scan, 0) as scan, (a.qty_label - COALESCE(d.label, 0)) AS balance2, (d.label - e.scan) AS balance, (CASE WHEN COALESCE(d.label, 0) = 0 THEN 'OPEN' WHEN COALESCE(d.label, 0) != COALESCE(e.scan, 0) tHEN 'OPEN' ELSE 'CLOSE' END) as status");
            $this->db->from('purchase_order_receipts a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join("(SELECT receipt_id, COUNT(id) AS label FROM purchase_order_labels GROUP BY receipt_id) d", 'a.receipt_id = d.receipt_id', 'left');
            $this->db->join("(SELECT receipt_id, COUNT(id) AS scan FROM scan_item_receipts GROUP BY receipt_id) e", 'a.receipt_id = e.receipt_id', 'left');
            if($filter_from != "" && $filter_to != ""){
                $this->db->where('a.receipt_date >=', $filter_from);
                $this->db->where('a.receipt_date <=', $filter_to);
            }
            if($not_balance == 1){
                $this->db->where('(a.qty_label - COALESCE(d.label, 0)) <', 0);
            }
            $this->db->order_by('a.receipt_id', 'DESC');
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = count($records);
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
}
