<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Stock_rm extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[30]|is_unique[stock_rm.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/stock_rm');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('stock_rm', ["name" => $post]);
        echo json_encode($send);
    }
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $trans_date = date("Y-m-d");

            $records = $this->crud->query("SELECT
                a.id,
                a.number,
                a.name,
                a.uom,
                b.name as product_family,
                ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS end_stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$trans_date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date <= '$trans_date' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date <= '$trans_date' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id WHERE a.receipt_date <= '$trans_date' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) j ON a.id = j.item_id
                LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) k ON a.id = k.item_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
                WHERE a.return_date <= '$trans_date'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.item_category_id = 'C01'
            GROUP BY a.id
            ORDER BY b.number, a.number");

            //Mapping Data
            $result['total'] = count($records);
            $result = array_merge($result, ["rows" => $records]);
            echo json_encode($result);
        }
    }
   
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=stock_rm_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $trans_date = date("Y-m-d");

        $records = $this->crud->query("SELECT
            a.id,
            a.number,
            a.name,
            a.uom,
            b.name AS product_family,
            ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(h.qty_reject, 0) - COALESCE(k.qty_rgp_out, 0)) AS end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$trans_date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date <= '$trans_date' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date <= '$trans_date' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date <= '$trans_date' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) j ON a.id = j.item_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) k ON a.id = k.item_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
        FROM return_materials a 
        JOIN return_material_labels b ON a.return_id = b.return_id
        JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
        WHERE a.return_date <= '$trans_date'
        GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        WHERE a.item_category_id = 'C01'
        GROUP BY a.id
        ORDER BY b.number, a.number");

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
                            <small>REPORT INVENTORY</small>
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
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Description</th>
                <th>Product Family</th>
                <th>Uom</th>
                <th>Current Stock</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td style="mso-number-format:\@;">' . $data->id . '</td>
                    <td style="mso-number-format:\@;">' . $data->number . '</td>
                    <td style="mso-number-format:\@;">' . $data->name . '</td>
                    <td>' . $data->product_family . '</td>
                    <td>' . $data->uom . '</td>
                    <td style="text-align:right;">' . $data->end_stock . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
