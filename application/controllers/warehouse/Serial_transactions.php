<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Serial_transactions extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Kind Code and Product Family', 'required|min_length[1]|max_length[20]|is_unique[item_sizes.number]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/serial_transactions');
        } else {
            redirect('error_access');
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $id = $_POST['id'];

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            b.name as prodfam, 
            a.uom, 
            COALESCE(0,0) as begin_stock,
            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0)) as qty_in,
            COALESCE(f.qty,0) as qty_out,
            (COALESCE(SUM(e.qty),0) - COALESCE(f.qty, 0) + COALESCE(g.return_qty, 0)) as end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
        LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
        LEFT JOIN os_rm h ON a.id = h.item_rm_id and h.trans_date between '$filter_from' and '$filter_to'
        LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id or h.id = e.receipt_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
            WHERE a.return_date between '$filter_from' and '$filter_to'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        WHERE b.number like '%$filter_item_family%' and a.id like '%$filter_items%'
        GROUP BY a.id
        ORDER BY a.number");
        
        if ($id === "0") {
            foreach ($records as $record) {
                $item_rm_id = $record->id;
                //Item Receipts
                $itemReceipts = $this->crud->query("SELECT
                    a.id,
                    (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty,0) - COALESCE(f.qty, 0)) as begin_stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
                LEFT JOIN os_rm h ON a.id = h.item_rm_id and h.trans_date < '$filter_from'
                LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date < '$filter_from'
                LEFT JOIN scan_item_receipts e ON (d.receipt_id = e.receipt_id or h.id = e.receipt_id)
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                    FROM return_materials a 
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                    WHERE a.return_date < '$filter_from'
                    GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
                WHERE a.id like '$item_rm_id'
                GROUP BY a.id
                ORDER BY a.number");

                $arr[] = array(
                    "id" => $record->id,
                    "number" => $record->number,
                    "name" => $record->name,
                    "uom" => $record->uom,
                    "prodfam" => $record->prodfam,
                    "begin" => @$itemReceipts[0]->begin_stock,
                    "qty_in" => $record->qty_in,
                    "qty_out" => $record->qty_out,
                    "end_stock" => (@$itemReceipts[0]->begin_stock + $record->end_stock),
                    "state" => "closed",
                    "datatable" => 1
                );
            }

            die(json_encode($arr));
        } else {
            $this->db->select('a.*, b.name as journal_name, c.account_name');
            $this->db->from('journal_setups a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            $this->db->join('account_coa c', 'a.account_number = c.account_number');
            $this->db->where('a.journal_type_id', $id);
            $this->db->group_by('a.account_number');
            $this->db->order_by('a.flag', 'asc');
            $arr = $this->db->get()->result_array();
        }

        //Mapping Data
        $result = !empty($arr) ? $arr : [];
        echo json_encode($result);
    }
    
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=item_sizes_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name');
        $this->db->from('item_sizes a');
        $this->db->join('item_categories b', 'a.item_category_id = b.id');
        $this->db->join('item_familys c', 'a.item_family_id = c.id');
        $this->db->where('a.deleted', 0);

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
                            <small>MASTER SIZE</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Size Code</th>
                <th>Size Name</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>Description</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['item_category_name'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['description'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
