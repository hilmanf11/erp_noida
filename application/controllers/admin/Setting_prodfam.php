<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Setting_prodfam extends CI_Controller
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

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $this->load->view('template/header');
            $this->load->view('admin/setting_prodfam');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $username = $this->session->username;
        $send = $this->crud->query("SELECT b.id, b.name FROM setting_prodfam a JOIN item_familys b ON a.item_family_id = b.id WHERE a.username = '$username' and a.status = '1'");
        echo json_encode($send);
    }

    public function getUsers()
    {
        $post = isset($_POST['q']) ? array("name" => $_POST['q']) : $this->input->get();
        $send = $this->crud->reads('users', $post, ["deleted" => 0, "actived" => 0]);
        echo json_encode($send);
    }

    public function datatables()
    {
        if ($this->input->get('username')) {
            $username = $this->input->get('username');

            $this->db->select('a.*, b.name as prodfam, c.name as category');
            $this->db->from('setting_prodfam a');
            $this->db->join('item_familys b', 'a.item_family_id = b.id');
            $this->db->join('item_categories c', 'b.item_category_id = c.id');
            $this->db->like('a.username', $username);
            $this->db->group_by('a.item_family_id');
            $this->db->order_by('c.name', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function create()
    {
        if (!empty($this->session->username)) {
            if ($this->input->post()) {
                $post = $this->input->post();
                $sql = $this->db->query("SELECT * from item_familys order by `name` asc");
                foreach ($sql->result_array() as $data) {

                    //Ambil data yang belum terdaftar
                    $id = $data['id'];
                    $item_category_id = $data['item_category_id'];
                    $sql_cek2 = $this->db->query("SELECT * FROM setting_prodfam WHERE item_family_id = '$id' and username = '$post[username]'");
                    $row_cek2 = $sql_cek2->num_rows();

                    if ($row_cek2 > 0) {
                        //jika data sudah ada maka tidak ada proses
                    } else {
                        //Jika data belum ada maka terjadi insert data
                        $value  = array(
                            'username' => $post['username'],
                            'item_family_id' => $id,
                            'item_category_id' => $item_category_id
                        );

                        $this->crud->create('setting_prodfam', $value);
                    }
                }
            } else {
                show_error("Cannot Process your request");
            }
        } else {
            show_error("Your Session has been Expired");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id     = base64_decode($this->input->get('id'));
            $post   = $this->input->post();
            $send   = $this->crud->update('setting_prodfam', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
}
