<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class account_imprests extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Imprest No', 'required|min_length[1]|max_length[30]|is_unique[account_imprests.number]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/account_imprests');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.account_name FROM account_imprests a JOIN account_coa b ON a.account_number = b.account_number WHERE (a.number LIKE '%$post%' or a.name LIKE '%$post%')");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.account_name');
            $this->db->from('account_imprests a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "account_name"){
                        $this->db->like("b.account_name", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }

            $this->db->group_by('a.number');
            $this->db->order_by('a.number', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);

            // Menampilkan hasil dalam format JSON
            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('account_imprests', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('account_imprests', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('account_imprests', $data);
        echo $send;
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
        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'account_number' => $data->val($i, 4),
                'currency_original' => $data->val($i, 5),
                'amount_original' => $data->val($i, 6),
                'currency_local' => $data->val($i, 7),
                'amount_local' => $data->val($i, 8),
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

            $account_coa = $this->crud->read('account_coa', [], ["account_number" => $data['account_number']]);
            $account_imprests = $this->crud->read('account_imprests', [], ["number" => $data['number']]);

            if (empty($account_coa->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Account COA " . $data['account_number'] . " Not Found", "theme" => "error"));
            } else if(!empty($account_imprests->id)){
                echo json_encode(array("title" => "Duplicate", "message" => "Imprest No " . $data['number'] . " Duplicate", "theme" => "error"));
            } else {
                $send = $this->crud->create('account_imprests', $data);
                echo $send;
            }
        }
    }


    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=account_imprests_$format.xls");
        }

        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Fetch data using JOIN for account COA
        $this->db->select('a.*, b.account_name');
        $this->db->from('account_imprests a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->order_by('a.number', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
    <center>
        <div style="float: left; font-size: 12px; text-align: left;">
            <table style="width: 100%;">
                <tr>
                    <td rowspan="2" width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                        <img src="' . $config->favicon . '" width="30">
                    </td>
                    <td colspan="2" style="font-size: 14px; text-align: left; margin:2px;">
                        <b>' . $config->name . '</b>
                    </td>
                </tr>
            </table>
        </div>
        <div style="float: right; font-size: 12px; text-align: right;">
            Print Date ' . date("d M Y H:m:s") . ' <br>
            Print By ' . $this->session->username . '  
        </div>
        <br><br>
        <div style="float: center; font-size: 16px; text-align: center;">
            <h3>IMPREST DATA</h3>
        </div>
    </center>
    
    <table id="customers" border="1">
        <tr>
            <th rowspan="2" width="20">No</th>
            <th rowspan="2">Imprest Np</th>
            <th rowspan="2">Imprest Name</th>
            <th rowspan="2">Account Number</th>
            <th rowspan="2">Account Name</th>
            <th colspan="2" style="text-align: center;"> Original Currency</th>
            <th colspan="2" style="text-align: center;"> Local Currency</th>
        </tr>
        <tr>
            <th>Currency</th>
            <th>Amount</th>
            <th>Currency</th>
            <th>Amount</th>x
        </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['account_name'] . '</td>
                        <td>' . $data['currency_original'] . '</td>
                        <td>' . $data['amount_original'] . '</td>
                        <td>' . $data['currency_local'] . '</td>
                        <td>' . $data['amount_local'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
