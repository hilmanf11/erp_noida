<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Item_ng extends CI_Controller
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
        $this->form_validation->set_rules('workorder', 'Workorder No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('planning/item_ng');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('item_ng', ["name" => $post]);
        echo json_encode($send);
    }

    public function readDocument()
    {
        $send = $this->crud->query("SELECT DISTINCT document FROM item_ng order by document desc");
        echo json_encode($send);
    }

    public function item_ng_no($id_menu, $trans_date)
    {
        // $datenow = date("Y-m-d");
        $datenow = base64_decode($trans_date);
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if(@$row_menu->number == ""){
            //
        }else{
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number.$yearnow.$yearend."1";

            $sqlGetID	= $this->db->query("SELECT max(document) as kode FROM item_ng WHERE document like '%$whereDate%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;
            
            if ($kode == NULL) {
                $autoID = sprintf("%0".$row_menu->sequence."s", $kode + 1);
                $autoNo = $whereDate.$autoID;
            } else {
                $urutan = (int) substr($kode, -6);
                $urutan++;
                $autoID	= sprintf("%0".$row_menu->sequence."s", $urutan);
                $autoNo = $whereDate.$autoID;
            }

            echo $autoNo;
        }
    }

    public function readItemNg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT a.document, a.workorder
        FROM item_ng a
        WHERE a.status = '0' and (a.workorder like '%$post%')
        order by a.workorder desc");
        echo json_encode($send);
    }

    public function readWorkorders()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        // $send = $this->crud->query("SELECT DISTINCT a.workorder, b.number_customer 
        // FROM production_schedules a
        // JOIN item_fg b ON a.item_fg_id = b.id
        // WHERE a.precut_status = 'YES' and a.status = '0' and a.status_wip = '0' and (a.workorder like '%$post%' or b.number_customer like '%$post%')
        // order by a.workorder desc");

        $this->db->select("a.workorder, c.number_customer,
            (CASE WHEN f.id != '' THEN 2 ELSE a.status END) as status_wo");
        $this->db->from('production_schedules a');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('scan_item_receipts_fg f', 'a.workorder = f.workorder', 'left');
        // $this->db->where("f.id", null);
        $this->db->like('a.workorder', $post);
        $this->db->or_like('c.number_customer', $post);
        $this->db->group_by('a.workorder');
        $this->db->group_by('c.number_customer');
        $this->db->order_by('a.workorder', 'asc');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readItems($workorder)
    {
        $workorder = base64_decode($workorder);
        $trans_date = date("Y-m-d");

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $datas = $this->crud->query("SELECT b.id, b.number, b.name, a.qty_act as qty, b.uom, coalesce(c.issued, 0) as issued
        FROM supply_sheets a
        JOIN item_rm b ON a.item_rm_id = b.id
        LEFT JOIN (SELECT item_rm_id, request_no, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and request_no NOT LIKE '%PR-%' GROUP BY request_no, item_rm_id) c ON a.item_rm_id = c.item_rm_id and a.request_no = c.request_no
        WHERE a.workorder = '$workorder' and b.status = '0' and b.number like '%$post%'
        group by a.id
        order by b.number asc");

        $sqlwip = $this->db->query("SELECT a.id, (COALESCE(c.issued, 0) + COALESCE(g.adjustment_in, 0) - COALESCE(abs(i.adjustment_out), 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS balance
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date <= '$trans_date' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date <= '$trans_date' and source = 'Vendor' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date <= '$trans_date' and source = 'User' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_in FROM sto_wip WHERE trans_date <= '$trans_date' and qty > 0 GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_out FROM sto_wip WHERE trans_date <= '$trans_date' and qty < 0 GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.consumed) AS rfg FROM wip_receipt_balance a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
            GROUP BY a.id
            ORDER BY a.number");
        $datawip = $sqlwip->result_array();

        $arrWip = [];
        foreach ($datawip as $dataw) {
            $arrWip[$dataw['id']] = $dataw['balance'];
        }

        $arr = array();
        foreach ($datas as $data) {
            $item_rm_id = $data->id;
            // $sqlwip = $this->db->query("SELECT a.id, (COALESCE(c.issued, 0) + COALESCE(g.adjustment_in, 0) - COALESCE(abs(i.adjustment_out), 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS balance
            //     FROM item_rm a
            //     JOIN item_familys b ON a.item_family_id = b.id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date <= '$trans_date' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date <= '$trans_date' and source = 'Vendor' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date <= '$trans_date' and source = 'User' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_in FROM sto_wip WHERE trans_date <= '$trans_date' and qty > 0 GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_out FROM sto_wip WHERE trans_date <= '$trans_date' and qty < 0 GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            //     LEFT JOIN (SELECT a.item_rm_id, SUM(a.consumed) AS rfg FROM wip_receipt_balance a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
            //     WHERE a.id = '$item_rm_id'
            //     GROUP BY a.id
            //     ORDER BY a.number");
            // $datawip = $sqlwip->row();
            $wip = empty(@$arrWip[$item_rm_id]) ? 0 : @$arrWip[$item_rm_id];

            $arr[] = array(
                "id" => $data->id,
                "number" => $data->number,
                "name" => $data->name,
                "qty" => $data->qty,
                "uom" => $data->uom,
                "issued" => $data->issued,
                "wip" => $wip
            );
        }



        echo json_encode($arr);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_document = $this->input->get('filter_document');
            $filter_family_id = $this->input->get('filter_family_id');
            $filter_item_rm_id = $this->input->get('filter_item_rm_id');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name');
            $this->db->from('item_ng a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.document', $filter_document);
            $this->db->like('b.item_family_id', $filter_family_id);
            $this->db->like('b.id', $filter_item_rm_id);
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->order_by('a.document', 'DESC');
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

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $itemNg = $this->crud->reads("item_ng", [], ["item_rm_id" => $post['item_rm_id'], "document" => $post['document']]);

                if (count($itemNg) > 0) {
                    echo json_encode(array("title" => "Duplicate", "message" => "Data has been created", "theme" => "error"));
                } else {
                    $send = $this->crud->create('item_ng', $post);
                    echo $send;
                }
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
            $send = $this->crud->update('item_ng', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('item_ng', ["id" => $data['id']]);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=item_ng_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_document = $this->input->get('filter_document');
        $filter_family_id = $this->input->get('filter_family_id');
        $filter_item_rm_id = $this->input->get('filter_item_rm_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_number, b.name as item_name');
        $this->db->from('item_ng a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.document', $filter_document);
        $this->db->like('b.item_family_id', $filter_family_id);
        $this->db->like('b.id', $filter_item_rm_id);
        $this->db->order_by('a.trans_date', 'DESC');
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
                                <small>ITEMS NG TRANSACTION</small>
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
                    <th>Departement</th>
                    <th>Process</th>
                    <th>NG Type</th>
                    <th>Work Order</th>
                    <th>Part No EBWS</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Rejection</th>
                    <th>Uom</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['document'] . '</td>
                            <td>' . $data['departement'] . '</td>
                            <td>' . $data['process'] . '</td>
                            <td>' . $data['type'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['item_rm_id'] . '</td>
                            <td>' . $data['item_number'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
