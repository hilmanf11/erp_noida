<?php
error_reporting(0);
defined('BASEPATH') or exit('No direct script access allowed');

class Report_exceptions extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        // $this->dummy = $this->load->database('dummy', TRUE);
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/report_exceptions');
        } else {
            redirect('error_access');
        }
    }

    public function datatables(){
        $this->db->select("b.*, 
            COALESCE(c.mpq, 0) AS mpq, 
            COALESCE(c.moq, 0) AS moq, 
            COALESCE(c.leadtime, 0) AS leadtime, 
            COALESCE(c.share_order, 0) AS share_order, 
            a.number AS item_rm_number, 
            a.name AS item_rm_name,
            a.hsn_code");
        $this->db->from('item_rm a');
        $this->db->join('bom b', 'a.id = b.item_rm_id');
        $this->db->join('supplier_items c', "a.id = c.item_rm_id", "left");
        $this->db->where("a.item_category_id = 'C01' AND (COALESCE(c.mpq, 0) = 0 
            OR COALESCE(c.moq, 0) = 0 
            OR COALESCE(c.leadtime, 0) = 0 
            OR COALESCE(c.share_order, 0) = 0 
            OR a.hsn_code IN ('-',''))");
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'asc');
        $records = $this->db->get()->result_array();

        $result['total'] = count($records);
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }
}
