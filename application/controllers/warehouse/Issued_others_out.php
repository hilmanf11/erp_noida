<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Issued_others_out extends CI_Controller
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
            $this->load->view('warehouse/issued_others_out');
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

    public function numberDocStore($location = "")
    {

        $format = $location . date("ym");
        $sql = $this->db->query("SELECT max(`document_in`) as kode From issued_others_store where `document_in` like '%$format%'");
        $row = $sql->row();
        if (empty($row->kode)) {
            $kode = substr($row->kode, 1);
            $autoid = $format . sprintf("%08s", $kode + 1);
        } else {
            $kode = substr($row->kode, -8);
            $autoid = $format . sprintf("%08s", $kode + 1);
        }
        echo $autoid;
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_part_no = $this->input->post('filter_part_no');
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, sum(a.qty) - sum(coalesce(b.qty,0)) as total_qty");
            $this->db->from('(SELECT * FROM issued_others_request WHERE status != 2 ORDER BY status ASC) a');
            $this->db->join("(select request_no, item_rm_id, sum(qty) as qty from issued_others where status <> '2' group by request_no, item_rm_id) b", 'a.request_no = b.request_no and a.item_rm_id=b.item_rm_id', 'left');
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "total_qty") {
                        $this->db->having("sum(a.qty) - sum(coalesce(b.qty,0)) = '$filter->value'");
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            if ($filter_part_no != "") {
                $this->db->where('a.item_rm_id', $filter_part_no);
            }
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_no', 'DESC');
            //$this->db->order_by('a.warehouse', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableDetails($request_no)
    {
        $request_no = base64_decode($request_no);
        $filter_from = date("Y-m-d");

        //Select Query
        $this->db->select("a.request_no,a.request_name,a.trans_date, a.item_rm_id,a.remarks,
            coalesce(d.ending_stock, 0) as ending_stock,
            a.qty - coalesce(c.qty,0) as stock, 
            a.qty - coalesce(c.qty,0) as qty, 
            (a.qty - coalesce(c.qty,0)) - (a.qty - coalesce(c.qty,0)) as balance, 
            coalesce(c.qty,0) as total_issued,
            b.number as item_rm_number, b.name as item_rm_name, b.uom");
        $this->db->from('issued_others_request a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join("(select request_no, item_rm_id, sum(qty) as qty from issued_others where status <> '2' group by request_no, item_rm_id) c", 'a.request_no = c.request_no and a.item_rm_id=c.item_rm_id', 'left');
        $this->db->join("(SELECT
            a.id,
            (COALESCE(c.qty_receipt,0) + COALESCE(e.qty_rgp_in,0) + COALESCE(g.sto_qty_in, 0) - COALESCE(i.sto_qty_out, 0) - COALESCE(f.qty_rgp_out,0) - COALESCE(d.qty,0)) AS ending_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE receipt_date <= '$filter_from' and store != 0 GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others WHERE trans_date <= '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date <= '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_id) e ON a.id = e.item_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date <= '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_id) f ON a.id = f.item_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_in FROM sto_ot WHERE type = 'IN' and trans_date <= '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_out FROM sto_ot WHERE type = 'OUT' and trans_date <= '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            GROUP BY a.id) d", 'a.item_rm_id = d.id', 'left');
        $this->db->where('a.request_no', $request_no);
        $this->db->where('a.status !=', 2);
        $this->db->order_by('b.number', 'DESC');

        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $item_rm_id   = $this->input->post('item_rm_id');
            $request_no   = $this->input->post('request_no');
            $trans_date   = $this->input->post('trans_date');
            $request_name   = $this->input->post('request_name');
            $location_other_number   = $this->input->post('location_other_number');
            $document_in   = $this->input->post('document_in');
            $stock   = $this->input->post('stock');
            $qty   = $this->input->post('qty');
            $balance   = $this->input->post('balance');
            $remarks   = $this->input->post('remarks');
            $out_by   = $this->input->post('out_by');
            $out_date   = $this->input->post('out_date');
            $status_store   = $this->input->post('status_store');

            if ($balance == 0) {
                $this->db->update('issued_others', ["status" => 1], ["item_rm_id" => $item_rm_id, "request_no" => $request_no]);
                $this->db->update('issued_others_request', ["status" => 1], ["item_rm_id" => $item_rm_id, "request_no" => $request_no]);
            }

            $postFinal = array(
                "request_no" => $request_no,
                "trans_date" => $trans_date,
                "item_rm_id" => $item_rm_id,
                "request_name" => $request_name,
                "location_other_number" => $location_other_number,
                "out_by" => $out_by,
                "out_date" => $out_date,
                "stock" => $stock,
                "qty" => $qty,
                "balance" => $balance,
                "remarks" => $remarks,
                "status" => 1,

            );

            $send = $this->crud->create('issued_others', $postFinal);

            $issued_others_request_no = $request_no;
            $request_no = str_replace("OUT", "ROU", $request_no);

            // Sebagai IN untuk transaksi store
            $dataInStore =  array(
                "issued_others_request_no" => $issued_others_request_no,
                "request_no" => $request_no,
                "trans_date" => $out_date, // out di issued other jadi transaksi in di issued other store
                "item_rm_id" => $item_rm_id,
                "request_name" => $request_name,
                "location_other_number" => $location_other_number,
                "document_in" => $document_in,
                "stock" => 0,
                "qty" => $qty,
                "balance" => $qty, // qty di issud other jadi balance di issued other store untuk data awalnya karena baru di IN
                "remarks" => $remarks,
                "status" => $status_store,
                "type" => "IN"
            );

            $send2 = $this->crud->create('issued_others_store', $dataInStore);

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function canceled()
    {
        if ($this->input->post()) {
            $request_no   = $this->input->post('request_no');
            $item_rm_id   = $this->input->post('item_rm_id');
            $status   = $this->input->post('status');
            $reason   = $this->input->post('reason');

            $send = $this->crud->update('issued_others_store', ["issued_others_request_no" => $request_no, "item_rm_id" => $item_rm_id], ["status" => $status, "reason" => $reason]);
            $send = $this->crud->update('issued_others', ["request_no" => $request_no, "item_rm_id" => $item_rm_id], ["status" => $status, "reason" => $reason]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send2 = $this->crud->delete('issued_others_store', ["issued_others_request_no" => $data['request_no']]);
        $send = $this->crud->delete('issued_others', $data);

        echo $send;
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

        $filter_part_no = base64_decode($this->input->get('filter_part_no'));

        $this->db->select("a.status,a.request_no,a.request_name,a.trans_date, a.item_rm_id,a.remarks,a.reason,
        a.qty - coalesce(c.qty,0) as stock, 
        a.qty - coalesce(c.qty,0) as qty, 
        (a.qty - coalesce(c.qty,0)) - (a.qty - coalesce(c.qty,0)) as balance, 
        b.number as item_rm_number, b.name as item_rm_name, b.uom");
        $this->db->from('issued_others_request a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join("(select request_no, item_rm_id, sum(qty) as qty from issued_others where status <> '2' group by request_no, item_rm_id) c", 'a.request_no = c.request_no and a.item_rm_id=c.item_rm_id', 'left');
        if ($filter_part_no != "") {
            $this->db->where('a.item_rm_id', $filter_part_no);
        }
        $this->db->order_by('a.request_no', 'DESC');
        $this->db->order_by('a.item_rm_id', 'ASC');
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
                            <small>ISSUED CONSUMABLE</small>
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
                <th>Request No</th>
                <th>Request Date</th>
                <th>Request Name</th>
                <th>Uom</th>
                <th>Stock</th>
                <th>Issued</th>
                <th>Balance</th>
                <th>Remarks</th>
                <th>Reason</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "OPEN";
            } elseif ($data['status'] == "1") {
                $status = "CLOSED";
            } else {
                $status = "CANCEL";
            }

            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $status . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['trans_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['stock'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['balance'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['reason'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
