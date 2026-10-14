<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Issued_others_store_out extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[30]|is_unique[issued_others_store.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/issued_others_store_out');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('issued_others_store', ["name" => $post]);
        echo json_encode($send);
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
            $this->db->select("coalesce(d.status,0) as status,a.document_in, a.request_no,a.trans_date, a.request_name,a.remarks,a.location_other_number,a.created_by,a.created_date,a.updated_by,a.updated_date,(coalesce(b.qty_in,0) - coalesce(c.qty_out,0)) as total_qty");
            $this->db->from('issued_others_store a');
            $this->db->join("(select request_no, sum(qty) as qty_in from issued_others_store where type='IN' group by request_no) b", "a.request_no = b.request_no", "left");
            $this->db->join("(select request_no, sum(qty) as qty_out from issued_others_store where type='OUT' group by request_no) c", "a.request_no = c.request_no", "left");
            $this->db->join("(select status, request_no from issued_others_store GROUP BY request_no,status ORDER BY status ASC ) d", "a.request_no = d.request_no", "left");
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "status") {
                        $this->db->like("d.status", $filter->value);
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

        $query = $this->db->query("
            select a.item_rm_id, d.number as item_rm_number, d.name as item_rm_name, d.uom,
                    coalesce(e.ending_stock, 0) as ending_stock,
                    (coalesce(b.qty_in,0) - coalesce(c.qty_out,0)) as stock, 
                    '0' as qty,
                    ((coalesce(b.qty_in,0) - coalesce(c.qty_out,0))) as balance,
                    a.remarks
            from (select request_no, item_rm_id, remarks from issued_others_store group by request_no,item_rm_id) a
            left join (select request_no, item_rm_id, sum(qty) as qty_in from issued_others_store where type='IN' group by request_no, item_rm_id) b on a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
            left join (select request_no, item_rm_id, sum(qty) as qty_out from issued_others_store where type='OUT' group by request_no, item_rm_id) c on a.request_no = c.request_no AND a.item_rm_id = c.item_rm_id
            left join (SELECT
                    a.id,
                    ((COALESCE(c.qty_in,0) + COALESCE(e.sto_qty_in,0)) - (COALESCE(d.qty_out,0) + COALESCE(f.sto_qty_out,0))) AS ending_stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
                LEFT JOIN (select item_rm_id, sum(qty) as qty_in from issued_others_store where trans_date <= '$filter_from' and `type` = 'IN' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_out from issued_others_store where trans_date <= '$filter_from' and `type` = 'OUT' and status <> '2' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_in FROM sto_store WHERE type = 'IN' and trans_date <= '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_out FROM sto_store WHERE type = 'OUT' and trans_date <= '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                GROUP BY a.id) e on a.item_rm_id = e.id
            join item_rm d on a.item_rm_id = d.id
            where a.request_no = '" . $request_no . "'
            order by d.number desc
    ")->result_array();

        $totalRows = count($query);
        $records = $query;
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $request_no   = $this->input->post('request_no');
            $trans_date   = $this->input->post('trans_date');
            $request_name   = $this->input->post('request_name');
            $document_in   = $this->input->post('document_in');
            $location_other_number   = $this->input->post('location_other_number');
            $out_by   = $this->input->post('out_by');
            $out_date   = $this->input->post('out_date');
            $item_rm_id   = $this->input->post('item_rm_id');
            $stock   = $this->input->post('stock');
            $qty   = $this->input->post('qty');
            $balance   = $this->input->post('balance');
            $remarks   = $this->input->post('remarks');
            $balance   = $this->input->post('balance');

            // Sebagai OUT untuk transaksi store
            $dataInStore =  array(
                "request_no" => $request_no,
                "trans_date" => $trans_date,
                "request_name" => $request_name,
                "document_in" => $document_in,
                "location_other_number" => $location_other_number,
                "out_by" => $out_by,
                "out_date" => $out_date,
                "item_rm_id" => $item_rm_id,
                "stock" => $stock,
                "qty" => $qty,
                "balance" => $balance,
                "remarks" => $remarks,
                "type" => "OUT"
            );

            if ($qty == 0) {
                $send = json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                if ($balance != "0") {
                    $send = $this->crud->create('issued_others_store', $dataInStore);
                } else {
                    $issued_others_store = $this->crud->read("issued_others_store", [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id, "status" => 1]);
                    if (empty(@$issued_others_store->request_no)) {
                        $send = $this->crud->create('issued_others_store', $dataInStore);
                    }
                    $this->crud->update('issued_others_store', ["request_no" => $request_no, "item_rm_id" => $item_rm_id], ["status" => 1]);
                }
            }
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

            $send = $this->crud->update('issued_others_store', ["request_no" => $request_no, "item_rm_id" => $item_rm_id], ["status" => $status, "reason" => $reason]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $request_no = $data['request_no'];
        $send = $this->crud->delete('issued_others_store', ["request_no" => $request_no, "type" => "OUT"]);
        $send = $this->crud->update('issued_others_store', $data, ["status" => 0, "reason" => ""]);

        echo $send;
    }
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=issued_others_store_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_part_no = base64_decode($this->input->get('filter_part_no'));
        if ($filter_part_no != "") {
            $whereItem = " and a.item_rm_id = '" . $filter_part_no . "'";
        } else {
            $whereItem = "";
        }

        $records = $this->db->query("
        select a.request_no,a.trans_date,a.item_rm_id, d.number as item_rm_number, d.name as item_rm_name, d.uom, 
                (coalesce(b.qty_in,0) - coalesce(c.qty_out,0)) as stock, 
                (coalesce(b.qty_in,0) - coalesce(c.qty_out,0)) as qty,
                ((coalesce(b.qty_in,0) - coalesce(c.qty_out,0)) - (coalesce(b.qty_in,0) - coalesce(c.qty_out,0))) as balance,
                a.remarks,a.status,a.request_name,a.reason
        from (select request_no, item_rm_id, remarks, request_name,trans_date, status,out_by,out_date,reason from issued_others_store group by request_no,item_rm_id,status) a
        left join (select request_no, item_rm_id, sum(qty) as qty_in from issued_others_store where type='IN' group by request_no, item_rm_id) b on a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
        left join (select request_no, item_rm_id, sum(qty) as qty_out from issued_others_store where type='OUT' group by request_no, item_rm_id) c on a.request_no = c.request_no AND a.item_rm_id = c.item_rm_id
        join item_rm d on a.item_rm_id = d.id
        where  1=1 $whereItem
        order by a.request_no desc
        ")->result_array();

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
                            <small>ISSUED OTHER STORE</small>
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
                $style = "background:#c8ffcc; color:#2d8035";
            } elseif ($data['status'] == "1") {
                $status = "CLOSED";
                $style = "background:#ffc8c8; color:#ff1040";
            } else {
                $status = "CANCEL";
                $style = "background:#7285e9; color:#ffffff";
            }

            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td align="center" style="' . $style . '">' . $status . '</td>
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
