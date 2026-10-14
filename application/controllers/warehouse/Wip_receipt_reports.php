<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Wip_receipt_reports extends CI_Controller
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
            $this->load->view('warehouse/wip_receipt_reports');
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

            //Select Query
            $this->db->select("a.id, 
                a.number, 
                a.name, 
                a.uom, 
                b.name AS item_family_name, 
                COALESCE(i.begin, 0) AS begin,
                COALESCE(c.issued, 0) AS issued, 
                COALESCE(d.return, 0) AS `return`, 
                COALESCE(e.ng, 0) AS ng, 
                COALESCE(f.scrap, 0) AS scrap, 
                COALESCE(g.adjustment, 0) AS adjustment, 
                COALESCE(h.rfg, 0) AS rfg,
                (COALESCE(i.begin, 0) + COALESCE(c.issued, 0) + COALESCE(g.adjustment, 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS balance");
            $this->db->from('item_rm a');
            $this->db->join('item_familys b', 'a.item_family_id = b.id');
            $this->db->join("(SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' GROUP BY item_rm_id) c", 'a.id = c.item_rm_id', 'left');
            $this->db->join("(SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date BETWEEN '$filter_from' and '$filter_to' GROUP BY item_rm_id) d", 'a.id = d.item_rm_id', 'left');
            $this->db->join("(SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and source = 'Vendor' GROUP BY item_rm_id) e", 'a.id = e.item_rm_id', 'left');
            $this->db->join("(SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and source = 'User' GROUP BY item_rm_id) f", 'a.id = f.item_rm_id', 'left');
            $this->db->join("(SELECT item_rm_id, SUM(qty) AS adjustment FROM sto_wip WHERE trans_date BETWEEN '$filter_from' and '$filter_to' GROUP BY item_rm_id) g", 'a.id = g.item_rm_id', 'left');
            $this->db->join("(SELECT item_rm_id, SUM(qty) AS rfg FROM wip_receipt_details WHERE trans_date BETWEEN '$filter_from' and '$filter_to' GROUP BY item_rm_id) h", 'a.id = h.item_rm_id', 'left');
            $this->db->join("(SELECT a.id, (COALESCE(c.issued, 0) + COALESCE(g.adjustment, 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS begin
                FROM item_rm a
                JOIN item_familys b ON a.item_family_id = b.id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date < '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date < '$filter_from' and source = 'Vendor' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date < '$filter_from' and source = 'User' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment FROM sto_wip WHERE trans_date < '$filter_from' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS rfg FROM wip_receipt_details WHERE trans_date < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                WHERE a.item_category_id = 'C01'
                GROUP BY a.id
                ORDER BY a.number) i", 'a.id = i.id', 'left');
            $this->db->where('a.item_category_id', "C01");
            $this->db->group_by('a.id');
            $this->db->order_by('b.number', 'ASC');
            $this->db->order_by('a.number', 'ASC');
            $records = $this->db->get()->result_array();
            
            $datas = [];
            foreach ($records as $record) {
                $datas[] = array(
                    "id" => $record['id'],
                    "number" => $record['number'],
                    "name" => $record['name'],
                    "uom" => $record['uom'],
                    "item_family_name" => $record['item_family_name'],
                    "begin" => $record['begin'],
                    "issued" => $record['issued'],
                    "return" => $record['return'],
                    "ng" => $record['ng'],
                    "scrap" => $record['scrap'],
                    "adjustment" => $record['adjustment'],
                    "rfg" => $record['rfg'],
                    "balance" => $record['balance']
                );
            }

            //Mapping Data
            $result['total'] = count($datas);
            $result = array_merge($result, ['rows' => $datas]);
            echo json_encode($result);
        }
    }
}
