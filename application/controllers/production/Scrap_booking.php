<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scrap_booking extends CI_Controller
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
        //Validasi Form
        $this->form_validation->set_rules('item_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('production/scrap_booking');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('scrap_booking', ["name" => $post]);
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * 
            FROM item_rm 
            WHERE status = 0 and item_category_id='C02'
            and (id like '%$post%' or number like '%$post%' or name like '%$post%' or name like '%$post%')");
        echo json_encode($send);
    }

    public function autonumber()
    {

        $datenow = date("ymd");
        $sql = $this->db->query("SELECT max(`number`) as kode From scrap_booking where `number` like '%$datenow%'");
        $row = $sql->row();
        if (empty($row->kode)) {
            $kode = substr($row->kode, 1);
            $autoid = "SCP-" . $datenow . sprintf("%04s", $kode + 1);
        } else {
            $kode = substr($row->kode, -4);
            $autoid = "SCP-" . $datenow . sprintf("%04s", $kode + 1);
        }
        echo $autoid;
    }

    public function datatables()
    {
        //Select Query
        $this->db->select('a.*, COALESCE(b.qty_scrap, 0) as qty_scrap, COALESCE(c.qty_invoice, 0) as qty_invoice, (COALESCE(b.qty_scrap, 0) - COALESCE(c.qty_invoice, 0)) as qty_balance');
        $this->db->from('item_rm a');
        $this->db->join("(SELECT item_id, SUM(qty) as qty_scrap FROM scrap_booking WHERE approved_to = '' or approved_to is null GROUP BY item_id) b", 'a.id = b.item_id', 'left');
        $this->db->join("(SELECT item_fg_id, SUM(qty) as qty_invoice FROM sales_invoices GROUP BY item_fg_id) c", 'a.id = c.item_fg_id', 'left');
        $this->db->where('a.status', 0);
        $this->db->where('a.item_category_id', 'C02');
        $this->db->group_by('a.id');
        $this->db->order_by('a.number');
        $totalRows = $this->db->count_all_results('', false);
        $records = $this->db->get()->result_array();
        
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatableDetails()
    {
        $id = $this->input->post('id');

        //Select Query
        $this->db->select('b.*, a.number as item_number, a.name as item_name, a.uom');
        $this->db->from('item_rm a');
        $this->db->join('scrap_booking b', 'a.id = b.item_id', 'left');
        $this->db->where('a.id', $id);
        $this->db->group_by('b.id');
        $this->db->order_by('b.number');
        $totalRows = $this->db->count_all_results('', false);
        $records = $this->db->get()->result_array();
        
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('scrap_booking', $post);
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
            $send = $this->crud->update('scrap_booking', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('scrap_booking', ["id" => $data['id']]);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=scrap_booking_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('scrap_booking a');
        $this->db->join('item_rm b', 'a.item_id = b.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.item_id', $filter_items);
        $this->db->order_by('a.item_id');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>SCRAPS BOOKING</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Trans Date</th>
                    <th>Document No</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>UoM</th>
                    <th>Qty</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['item_id'] . '</td>
                            <td>' . $data['item_number'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
