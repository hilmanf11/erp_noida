<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_rejections extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[30]|is_unique[wip_report.item_rm_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/report_rejections');
        } else {
            redirect('error_access');
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        // if ($this->input->post()) {

        //     $filter_from = $this->input->post('filter_from');
        //     $filter_from2 = date("Y-m-d", strtotime("-1 day", strtotime($filter_from)));
        //     $filter_to = $this->input->post('filter_to');

            //Select Query
            $this->db->select("a.id, a.number, a.name, a.uom, 
                b.name as item_family_name,
                COALESCE(d.scrap, 0) as scrap,
                COALESCE(c.qty, 0) as rejection,
                COALESCE(e.qty_return, 0) as qty_return,
                ((COALESCE(c.qty, 0) + COALESCE(d.scrap, 0)) - COALESCE(e.qty_return, 0)) as balance
            ");
            $this->db->from('item_rm a');
            $this->db->join('item_familys b', 'a.item_family_id = b.id');
            $this->db->join("(SELECT b.item_rm_id, SUM(a.qty) AS qty 
                FROM scan_item_rejections a 
                JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id
                GROUP BY b.item_rm_id) c", 'a.id = c.item_rm_id', 'left');
            $this->db->join("(SELECT item_rm_id, SUM(qty) as scrap FROM scraps WHERE source = 'Vendor' GROUP BY item_rm_id) d", 'a.id = d.item_rm_id', 'left');
            $this->db->join("(SELECT item_rm_id, SUM(qty) as qty_return FROM purchase_returns WHERE source in ('SCRAP','REJECT') GROUP BY item_rm_id) e", 'a.id = e.item_rm_id', 'left');
            $this->db->where('a.item_category_id', "C01");
            $this->db->group_by('a.id');
            $this->db->order_by('b.number', 'ASC');
            $this->db->order_by('a.number', 'ASC');
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = count($records);
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        // }
    }
}
