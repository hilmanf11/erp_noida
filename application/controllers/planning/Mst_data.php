<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_data extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        // $this->load->model('banshu');
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => "$i", "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function readRevisions()
    {
        $arr = array(
            ["id" => "0", "name" => "Revision 0"],
            ["id" => "1", "name" => "Revision 1"],
            ["id" => "2", "name" => "Revision 2"],
            ["id" => "3", "name" => "Revision 3"],
            ["id" => "4", "name" => "Revision 4"],
            ["id" => "5", "name" => "Revision 5"],
        );

        echo json_encode($arr);
    }

    // public function readProductFamily(){
    //     $this->dummy = $this->load->database('dummy', TRUE);
    //     $query = $this->dummy->query("SELECT * FROM r_prodfam WHERE prod_group = 'Raw Material' and pfm_id != '06' ORDER BY pfm_id ASC");
    //     $records = $query->result_array();

    //     echo json_encode($records);
    // }

    // public function readProducts($pfm_id)
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $this->dummy = $this->load->database('dummy', TRUE);
    //     $query = $this->dummy->query("SELECT * FROM mst_item WHERE pfm_id = '$pfm_id' and stscode_id = '01' and item_id LIKE '%$post%' ORDER BY item_id ASC");
    //     $records = $query->result_array();

    //     echo json_encode($records);
    // }
}
