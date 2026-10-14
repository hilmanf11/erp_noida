<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class purchase_invoice_upload extends CI_Controller
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
        $this->form_validation->set_rules('account_number', 'code', 'required|min_length[1]|max_length[20]|is_unique[account_coa.account_number]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $this->load->view('template/header');
            $this->load->view('finance/purchase_invoice_upload');
        }
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        $datas = array();
        for ($i = 2; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $data->val($i, 1),
                'asset_code' => $data->val($i, 2),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            $purchase_invoices = $this->crud->reads('purchase_invoices', [], ["number" => $data['number']]);
            if(empty($purchase_invoices)){
                echo json_encode(array("title" => "Error", "message" => "Invoice No ".$data['number']." Not Found ", "theme" => "error"));
            }else{
                foreach ($purchase_invoices as $purchase_invoice) {
                    $this->db->select('a.*, c.type as category_type');
                    $this->db->from('purchase_invoices a');
                    $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                    $this->db->join('item_categories c', 'b.item_category_id = c.id');
                    $this->db->where('a.id', $purchase_invoice->id);
                    $row = $this->db->get()->row_array();

                    if($row['category_type'] == "ASSET"){
                        $send = $this->db->update('purchase_invoices', ["asset_code" => $data['asset_code']], ["id" => $purchase_invoice->id]);
                    }
                }

                echo json_encode(array("title" => "Success", "message" => "Data Update Successfully", "theme" => "success"));
            }
        }
    }
}
