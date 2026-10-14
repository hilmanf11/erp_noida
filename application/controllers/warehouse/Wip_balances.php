<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Wip_balances extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[30]|is_unique[wip_balances.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/wip_balances');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('wip_balances', ["name" => $post]);
        echo json_encode($send);
    }
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $type = $this->input->post('type');
            $filter_from = $this->input->post('filter_from');
            $filter_to = $this->input->post('filter_to');
            
            //Select Query
            $this->db->select("a.*, 
                c.number as item_rm_number, 
                c.name as item_rm_name, 
                c.uom");
            $this->db->from('supply_sheets a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.type', $type);
            $this->db->where("a.created_date between '$filter_from' and '$filter_to'");
            $this->db->group_by('a.id');
            $this->db->order_by('a.created_date', 'DESC');
            $records = $this->db->get()->result_array();
            
            $datas = [];
            foreach ($records as $record) {
                $request_no = $record['request_no'];
                $item_rm_id = $record['item_rm_id'];
                $query = $this->db->query("SELECT SUM(qty) as issued FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id = '$item_rm_id'");
                $row = $query->row();

                if(@$row->issued <= 0){
                    $issued = 0;
                }else{
                    $issued = $row->issued;
                }

                if(($record['qty_act'] - $issued) > 0){
                    $status = "OPEN";
                }else{
                    $status = "CLOSED";
                }

                $balance = ($issued - $record['qty_act']);

                $datas[] = array(
                    "id" => $record['id'],
                    "supply_type" => $status,
                    "item_rm_id" => $record['item_rm_id'],
                    "item_rm_number" => $record['item_rm_number'],
                    "uom" => $record['uom'],
                    "request_no" => $record['request_no'],
                    "workorder" => $record['workorder'],
                    "qty_act" => $record['qty_act'],
                    "issued" => "$issued",
                    "qty_issued_bal" => "$balance",
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date'],
                );
            }

            $result['total'] = count($datas);
            $result = array_merge($result, ['rows' => $datas]);
            echo json_encode($result);

            // die(json_encode($records));
        }
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->create('wip_balances', $post);
            echo $send;
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
            $send = $this->crud->update('wip_balances', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('supply_sheets', ["id" => $data['id']]);
        // $this->db->update('production_schedules', ["workorder" => $data['workorder']], ["status" => 0]);
        echo $send;
    }

    public function deleteIssued(){
        $querys = $this->crud->query("SELECT * FROM supply_sheets WHERE type = 'HAV' ORDER BY created_date ASC");
        foreach ($querys as $query) {
            $id = $query->id;
            $request_no = $query->request_no;
            $item_rm_id = $query->item_rm_id;

            $querys2 = $this->crud->query("SELECT SUM(qty) as total FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id = '$item_rm_id'");
            if(@$querys2[0]->total <= 0){
                $this->db->delete('supply_sheets', ["id" => $id]);
                echo $request_no . " | " . $item_rm_id . " | Delete <br>";
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=wip_balances_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, 
            c.number as item_rm_number, 
            c.name as item_rm_name, 
            c.uom,
            (CASE WHEN (coalesce(i.qty_issued, 0) - a.qty_act) < 0 THEN 'OPEN' ELSE 'CLOSED' END) as supply_type,
            coalesce(i.qty_issued, 0) as issued,
            (coalesce(i.qty_issued, 0) - a.qty_act) as qty_issued_bal");
        $this->db->from('supply_sheets a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issued FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");
        $this->db->group_by('a.request_no');
        $this->db->group_by('a.item_rm_id');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER WIP BALANCE</small>
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
                <th>Status</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Description</th>
                <th>Kanban No</th>
                <th>Workorder</th>
                <th>Uom</th>
                <th>Need</th>
                <th>Issued</th>
                <th>Balance</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['supply_type'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                    <td>' . $data['request_no'] . '</td>
                    <td>' . $data['workorder'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['qty_act'] . '</td>
                    <td>' . $data['issued'] . '</td>
                    <td>' . $data['qty_issued_bal'] . '</td>
                    <td>' . $data['created_by'] . '</td>
                    <td>' . $data['created_date'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
