<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Stock_fg extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[30]|is_unique[stock_fg.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/stock_fg');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('stock_fg', ["name" => $post]);
        echo json_encode($send);
    }
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $trans_date = date("Y-m-d");

            $records = $this->crud->query("SELECT
                a.number,
                a.number_customer,
                a.name,
                a.uom,
                (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(e.qty_rgp_in, 0) + COALESCE(c.qty_wip, 0) + COALESCE(i.qty_return, 0) - COALESCE(g.qty, 0) - COALESCE(f.qty_rgp_out, 0) - COALESCE(h.sto_qty_out, 0)) as end_stock
            FROM item_fg a 
            LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_fg a JOIN scan_item_receipts_fg b ON a.id = b.workorder WHERE a.trans_date <= '$trans_date' GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
            LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_wip FROM wip_receipts a JOIN scan_item_receipts_fg b ON a.workorder = b.workorder and a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_fg_id) c ON a.id = c.item_fg_id
            LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_return FROM sales_returns a JOIN scan_item_receipts_fg b ON a.sales_invoice_no = b.workorder and a.return_id = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_fg_id) i ON a.id = i.item_fg_id
            LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_fg a WHERE a.trans_date <= '$trans_date' and a.type = 'IN' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) d ON a.id = d.item_fg_id
            LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_fg a WHERE a.trans_date <= '$trans_date' and a.type = 'OUT' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) h ON a.id = h.item_fg_id
            LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id= b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) e ON a.id = e.item_id
            LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) f ON a.id = f.item_id
            LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and `status` != '2' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
            GROUP BY a.id
            ORDER BY (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(c.qty_wip, 0) - COALESCE(g.qty, 0) - COALESCE(h.sto_qty_out, 0)) DESC");

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
            header("Content-Disposition: attachment; filename=stock_fg_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $trans_date = date("Y-m-d");

        $records = $this->crud->query("SELECT
            a.number,
            a.number_customer,
            a.name,
            a.uom,
            (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(e.qty_rgp_in, 0) + COALESCE(c.qty_wip, 0) + COALESCE(i.qty_return, 0) - COALESCE(g.qty, 0) - COALESCE(f.qty_rgp_out, 0) - COALESCE(h.sto_qty_out, 0)) as end_stock
        FROM item_fg a 
        LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_fg a JOIN scan_item_receipts_fg b ON a.id = b.workorder WHERE a.trans_date <= '$trans_date' GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
        LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_wip FROM wip_receipts a JOIN scan_item_receipts_fg b ON a.workorder = b.workorder and a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_fg_id) c ON a.id = c.item_fg_id
        LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_return FROM sales_returns a JOIN scan_item_receipts_fg b ON a.sales_invoice_no = b.workorder and a.return_id = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_fg_id) i ON a.id = i.item_fg_id
        LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_fg a WHERE a.trans_date <= '$trans_date' and a.type = 'IN' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) d ON a.id = d.item_fg_id
        LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_fg a WHERE a.trans_date <= '$trans_date' and a.type = 'OUT' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) h ON a.id = h.item_fg_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id= b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) e ON a.id = e.item_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$trans_date' GROUP BY a.item_id) f ON a.id = f.item_id
        LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and `status` != '2' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
        GROUP BY a.id
        ORDER BY (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(c.qty_wip, 0) - COALESCE(g.qty, 0) - COALESCE(h.sto_qty_out, 0)) DESC");

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
                <th>Product No EBWS</th>
                <th>Product No Customer</th>
                <th>Description</th>
                <th>Uom</th>
                <th>Current Stock</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td style="mso-number-format:\@;">' . $data->number . '</td>
                    <td style="mso-number-format:\@;">' . $data->number_customer . '</td>
                    <td style="mso-number-format:\@;">' . $data->name . '</td>
                    <td>' . $data->uom . '</td>
                    <td style="text-align:right;">' . $data->end_stock . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
