<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Dol_items extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');

        //VALIDASI FORM
        // $this->form_validation->set_rules('item_rm_id', 'Item RM', 'required|min_length[1]|max_length[30]|is_unique[dol_items.item_rm_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/dol_items');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('dol_items', ["item_rm_id" => $post]);
        echo json_encode($send);
    }

    //GET DATA
    public function readSupplier()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_rm_id = $this->input->get('item_rm_id');
        //Select Query
        $this->db->select('b.id, b.name, a.mpq');
        $this->db->from('item_mpq a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.item_rm_id', $item_rm_id);
        $this->db->like('b.name', $post);
        $this->db->group_by('b.id');
        $this->db->order_by('b.name', 'ASC');
        $suppliers = $this->db->get()->result_array();

        $this->db->select('b.id, b.name, a.mpq');
        $this->db->from('item_mpq a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->where('a.item_rm_id', $item_rm_id);
        $this->db->like('b.name', $post);
        $this->db->group_by('b.id');
        $this->db->order_by('b.name', 'ASC');
        $customers = $this->db->get()->result_array();

        echo json_encode(array_merge($suppliers, $customers));
    }
    
    //GET DATATABLES
    public function datatables()
    {
        //Select Query
        $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, c.name as category_name, d.name as product_family_name, (CASE WHEN e.name IS NULL THEN f.name ELSE e.name END) as supplier_name');
        $this->db->from('dol_items a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_categories c', 'b.item_category_id = c.id');
        $this->db->join('item_familys d', 'b.item_family_id = d.id');
        $this->db->join('suppliers e', 'a.supplier_id = e.id', 'left');
        $this->db->join('customers f', 'a.supplier_id = f.id', 'left');
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = count($records);
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatableHistories()
    {
        if ($this->input->get()) {
            $supplier_id = base64_decode($this->input->get('supplier_id'));
            $item_rm_id = base64_decode($this->input->get('item_rm_id'));

            $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, (CASE WHEN e.name IS NULL THEN f.name ELSE e.name END) as supplier_name');
            $this->db->from('dol_items_history a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers e', 'a.supplier_id = e.id', 'left');
            $this->db->join('customers f', 'a.supplier_id = f.id', 'left');
            $this->db->where('a.item_rm_id', $item_rm_id);
            $this->db->where('a.supplier_id', $supplier_id);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $dol_items = $this->crud->read('dol_items', [], ["item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']]);
            if(empty($dol_items->id)){
                $send = $this->crud->create('dol_items', $post);
                $this->crud->create('dol_items_history', $post);
                echo json_encode(array("title" => "Success", "message" => "Data Save Successfuly", "theme" => "success", "item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']));
            }else{
                echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Data", "theme" => "error", "item_rm_id" => "", "supplier_id" => ""));
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
            $send = $this->crud->update('dol_items', ["id" => $id], $post);
            $this->crud->create('dol_items_history', $post);
            $dol_items = $this->crud->read('dol_items', [], ["id" => $id]);

            if($send){
                echo json_encode(array("title" => "Success", "message" => "Data Updated Successfuly", "theme" => "success", "item_rm_id" => $dol_items->item_rm_id, "supplier_id" => $dol_items->supplier_id));
            }else{
                echo json_encode(array("title" => "Duplicate", "message" => "Failed Updated Data", "theme" => "error", "item_rm_id" => "", "supplier_id" => ""));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('dol_items', $data);
        $this->crud->delete('dol_items_history', $data);
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

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_rm_id' => $data->val($i, 2),
                'supplier_id' => $data->val($i, 3),
                'trans_date' => $data->val($i, 4),
                'reorder_point' => $data->val($i, 5),
                'is_dol' => $data->val($i, 6),
                'is_active' => $data->val($i, 7),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/dol_items.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/dol_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/dol_items.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            $dol_items = $this->crud->read('dol_items', [], ["item_rm_id" => $data['item_rm_id'], "supplier_id" => $data['supplier_id']]);
            $item_rm = $this->db->query("SELECT * FROM item_mpq WHERE item_rm_id = '$data[item_rm_id]' and (supplier_id = '$data[supplier_id]' or customer_id = '$data[supplier_id]')")->row_array();

            if (!empty($dol_items->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part No EBWS " . $data['item_rm_id'] . " is Already Exist", "theme" => "error"));
            }elseif(empty($item_rm['id'])){
                echo json_encode(array("title" => "Not Found", "message" => "Part No EBWS " . $data['item_rm_id'] . " and Supplier/Customer " . $data['supplier_id'] . " Not Founded in Item MPQ", "theme" => "error"));
            } else {
                $dataFinal = array_merge($data, 
                    array(
                        "mpq" => $item_rm['mpq'],
                        "lead_time_days" => 0
                    )
                );
                
                if(in_array($data['is_dol'], ["0","1"]) && in_array($data['is_active'], ["0","1"])){
                    $send = $this->crud->create('dol_items', $dataFinal);
                    $this->crud->create('dol_items_history', $dataFinal);
                    echo $send;
                }else{
                    echo json_encode(array("title" => "Not Found", "message" => "Part No EBWS " . $data['item_rm_id'] . " is dol or status only accept (0) or (1)", "theme" => "error"));
                }
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=dol_items_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, c.name as category_name, d.name as product_family_name, (CASE WHEN e.name IS NULL THEN f.name ELSE e.name END) as supplier_name');
        $this->db->from('dol_items a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_categories c', 'b.item_category_id = c.id');
        $this->db->join('item_familys d', 'b.item_family_id = d.id');
        $this->db->join('suppliers e', 'a.supplier_id = e.id', 'left');
        $this->db->join('customers f', 'a.supplier_id = f.id', 'left');
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#dol_items {border-collapse: collapse;width: 100%;font-size: 12px;}#dol_items td, #dol_items th {border: 1px solid #ddd;padding: 2px;}#dol_items tr:nth-child(even){background-color: #f2f2f2;}#dol_items tr:hover {background-color: #ddd;}#dol_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
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
                <div style="float: centet; font-size: 16px; text-align: center;">
                    <h3>DATA DOL ITEMS</h3>
                </div>
            </center>
            
            <table id="dol_items" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>Supplier ID</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>MPQ</th>
                    <th>Uom</th>
                    <th>DOL/NON DOL</th>
                    <th>Status</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                            <td>' . $data['category_name'] . '</td>
                            <td>' . $data['product_family_name'] . '</td>
                            <td>' . $data['supplier_id'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['mpq'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['is_dol'] . '</td>
                            <td>' . $data['is_active'] . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
    }
}
