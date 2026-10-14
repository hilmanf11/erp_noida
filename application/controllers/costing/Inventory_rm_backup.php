<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Inventory_rm extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('costing/inventory_rm');
        } else {
            redirect('error_access');
        }
    }

    public function readBalanceWip()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");

            echo json_encode($wip_balances);
        }
    }

    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        $this->db->where("item_category_id", "C01");
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function getData(){
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');
        $filter_item_family = $this->input->post('filter_item_family');
        $filter_items = $this->input->post('filter_items');
        $period = date("Ym", strtotime($filter_from));

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $this->db->query("DELETE FROM inventory_rm WHERE trans_date between '$filter_from' and '$filter_to'");

        //Item Receipts
        $itemReceipts = $this->crud->query("SELECT * FROM (SELECT
            DATE_FORMAT(a.receipt_date, '%Y%m') AS `period`,
            a.item_rm_id,
            f.item_family_id,
            'RECEIPT' AS trans_type,
            c.name AS created_name,
            d.po_date,
            a.receipt_date AS trans_date,
            a.receipt_id AS custom_kind, 
            a.po_no AS custom_no,
            a.bc_document AS document_no, 
            a.bc_date AS custom_date,
            f.uom, 
            SUM(b.qty) AS qty,
            d.currency,
            d.price,
            (SUM(b.qty) * d.price) AS amount
        FROM item_rm f
        JOIN purchase_order_receipts a ON a.item_rm_id = f.id
        JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
        JOIN users c ON a.created_by = c.username
        LEFT JOIN purchase_orders d ON a.item_rm_id = d.item_rm_id AND a.po_no = d.po_no
        LEFT JOIN suppliers e ON d.supplier_id = e.id
        GROUP BY a.id
        UNION
        SELECT 
            DATE_FORMAT(a.created_date, '%Y%m') AS `period`,
            a.item_rm_id, 
            g.item_family_id, 
            'ISSUED' AS trans_type,
            h.name AS created_name,
            d.po_date,
            DATE_FORMAT(a.created_date, '%Y-%m-%d') AS trans_date,
            b.receipt_id AS custom_kind,
            a.request_no AS custom_no,
            a.label_no AS document_no,
            DATE_FORMAT(a.created_date, '%Y-%m-%d') AS custom_date,
            g.uom,
            (a.qty * -1),
            d.currency,
            d.price,
            ((a.qty * price) * -1) AS amount
        FROM item_rm g
        JOIN issued_material_details a ON g.id = a.item_rm_id
        LEFT JOIN purchase_order_labels b ON a.label_no = b.label_no
        LEFT JOIN barcode_divides f ON a.label_no = f.label_divided
        LEFT JOIN purchase_order_receipts c ON a.item_rm_id = c.item_rm_id AND (b.receipt_id = c.receipt_id OR f.reff = c.receipt_id) 
        LEFT JOIN purchase_orders d ON a.item_rm_id = d.item_rm_id AND c.po_no = d.po_no
        LEFT JOIN suppliers e ON d.supplier_id = e.id
        JOIN users h ON a.created_by = h.username
        GROUP BY a.id) z
        WHERE z.trans_date BETWEEN '$filter_from' and '$filter_to' and z.item_family_id like '%$filter_item_family%' and z.item_rm_id like '%$filter_items%'
        ORDER BY z.item_rm_id, z.trans_date");

        $result['total'] = count($itemReceipts);
        $result = array_merge($result, ['rows' => $itemReceipts]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $data = array_merge($post, array("created_by" => $this->session->username, "created_date" => date("Y-m-d H:i:s")));
            $send = $this->db->insert('inventory_rm', $data);
            if($send){
                echo json_encode(array("title" => "Created", "message" => "Data Created Successfully", "theme" => "success"));
            }else{
                echo json_encode(array("title" => "Failed", "message" => "Data Updated Failed, Try again", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function formatNo($number){
        if($number >= 0){
            return "<span>".number_format($number, 2)."</span>";
        }else{
            return "<span style='color:red;'>(".number_format(abs($number), 2).")</span>";
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=stock_values_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

        $period = date("Y-m", strtotime($filter_from));

        // $inventory_rm = $this->crud->reads("inventory_rm", [], ['period' => $period]);

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>REPORT INVENTORY HISTORY TRANSACTION</small><br>
                                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>';

        if ($filter_display == "RECAP") {
            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT 
                a.id AS item_rm_id, 
                a.number AS item_rm_number, 
                a.name AS item_rm_name, 
                a.uom, 
                b.name AS item_family_name, 
                COALESCE(c.qty, 0) AS begin_qty, 
                COALESCE((c.amount / c.qty), 0) AS begin_price,
                COALESCE(c.amount, 0) AS begin_amount,
                COALESCE(d.qty, 0) AS in_qty,
                COALESCE((d.amount / d.qty), 0) AS in_price,
                COALESCE(d.amount, 0) AS in_amount,
                COALESCE(e.qty, 0) AS out_qty,
                COALESCE((e.amount / e.qty), 0) AS out_price,
                COALESCE(e.amount, 0) AS out_amount,
                COALESCE(f.qty, 0) AS bal_qty,
                COALESCE((f.amount / f.qty), 0) AS bal_price,
                COALESCE(f.amount, 0) AS bal_amount
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty, SUM(amount) AS amount FROM inventory_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty, SUM(amount) AS amount FROM inventory_rm WHERE trans_type = 'RECEIPT' AND trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty, SUM(amount) AS amount FROM inventory_rm WHERE trans_type = 'ISSUED' AND trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty, SUM(amount) AS amount FROM inventory_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            WHERE a.item_category_id = 'C01' and a.item_family_id like '%$filter_item_family%' and a.id like '%$filter_items%'
            ORDER BY b.number, a.number ASC");

            $html .= '<table id="customers" border="1" style="width: 100%">
                            <tr>
                                <th width="10" rowspan="2"width="20">No</th>
                                <th width="100" rowspan="2">Part No EBWS</th>
                                <th width="200" rowspan="2">Part No Supplier</th>
                                <th width="200" rowspan="2">Description</th>
                                <th width="50" rowspan="2">Uom</th>
                                <th width="100" rowspan="2">Product Family</th>
                                <th colspan="2">Begin</th>
                                <th colspan="2">In</th>
                                <th colspan="2">Out</th>
                                <th colspan="2">Ending</th>
                            </tr>
                            <tr>
                                <th width="50">Qty</th>
                                <th width="50">Amount</th>
                                <th width="50">Qty</th>
                                <th width="50">Amount</th>
                                <th width="50">Qty</th>
                                <th width="50">Amount</th>
                                <th width="50">Qty</th>
                                <th width="50">Amount</th>
                            </tr>';
            $noh = 1;
            $total_begin_qty = 0;
            $total_begin_amount = 0;
            $total_in_qty = 0;
            $total_in_amount = 0;
            $total_out_qty = 0;
            $total_out_amount = 0;
            $total_bal_qty = 0;
            $total_bal_amount = 0;
            foreach ($itemReceipts as $itemReceipt) {
                $html .= '  <tr>
                                <td>' . $noh . '</td>
                                <td>' . $itemReceipt->item_rm_id . '</td>
                                <td>' . $itemReceipt->item_rm_number . '</td>
                                <td>' . $itemReceipt->item_rm_name . '</td>
                                <td>' . $itemReceipt->uom . '</td>
                                <td>' . $itemReceipt->item_family_name . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_qty) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_amount) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->in_qty) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->in_amount) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->out_qty) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->out_amount) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_qty + $itemReceipt->bal_qty) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->begin_amount + $itemReceipt->bal_amount) . '</td>
                            </tr>';

                $total_begin_qty += $itemReceipt->begin_qty;
                $total_begin_amount += $itemReceipt->begin_amount;
                $total_in_qty += $itemReceipt->in_qty;
                $total_in_amount += $itemReceipt->in_amount;
                $total_out_qty += $itemReceipt->out_qty;
                $total_out_amount += $itemReceipt->out_amount;
                $total_bal_qty += ($itemReceipt->begin_qty + $itemReceipt->bal_qty);
                $total_bal_amount += ($itemReceipt->begin_amount + $itemReceipt->bal_amount);
                $noh++;
            }

            $html .= '  <tr>
                            <td colspan="6" style="text-align:right;"><b>GRAND TOTAL</b></td>
                            <td style="text-align:right;"><b>' . $this->formatNo($total_begin_qty, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . $this->formatNo($total_begin_amount, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . $this->formatNo($total_in_qty, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . $this->formatNo($total_in_amount, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . $this->formatNo($total_out_qty, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . $this->formatNo($total_out_amount, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . $this->formatNo($total_bal_qty, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . $this->formatNo($total_bal_amount, 2) . '</b></td>
                        </tr>';
            $html .= '</table></body></html>';
        } else {
            $itemReceipts = $this->crud->query("SELECT 
	            a.id AS item_rm_id, 
                a.number AS item_rm_number, 
                a.name AS item_rm_name, 
                a.uom, 
                b.name AS item_family_name, 
                d.*
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN inventory_rm d ON a.id = d.item_rm_id
            WHERE a.item_category_id = 'C01' and d.trans_date BETWEEN '$filter_from' AND '$filter_to' and a.item_family_id like '%$filter_item_family%' and a.id like '%$filter_items%'
            ORDER BY d.trans_date, b.number, a.number ASC");

            $html .= '<table id="customers" style="width: 100%">
                        <tr>
                            <th width="20">No</th>
                            <th>Part No EBWS</th>
                            <th>Part No Supplier</th>
                            <th>Description</th>
                            <th>Trans Type</th>
                            <th>Created By</th>
                            <th>PO Date</th>
                            <th>Trans Date</th>
                            <th>Receipt No</th>
                            <th>Kanban/PO No</th>
                            <th>Document No</th>
                            <th>Document Date</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Amount</th>
                            <th>Balance</th>
                        </tr>';
            $nod = 1;
            $last_item_rm_id = "";
            foreach ($itemReceipts as $itemReceipt) {
                $item_rm_id = $itemReceipt->item_rm_id;

                if($item_rm_id != $last_item_rm_id){
                    $begin = $this->crud->query("SELECT item_rm_id, SUM(qty) as begin_qty, SUM(amount) as begin_amount FROM inventory_rm WHERE item_rm_id = '$item_rm_id' and trans_date < '$filter_from'");
                    
                    if(@$begin[0]->begin_amount > 0){
                        $begin_price = (@$begin[0]->begin_amount / @$begin[0]->begin_qty);
                    }else{
                        $begin_price = 0;
                    }

                    $html .= '  <tr style="background: #aae9b5;">
                                    <td colspan="12" style="font-weight:bold;">BEGINING STOCK ('.$itemReceipt->item_rm_number.')</td>
                                    <td style="text-align:right; font-weight:bold;">' . $this->formatNo(@$begin[0]->begin_qty) . '</td>
                                    <td style="text-align:right; font-weight:bold;">' . $this->formatNo($begin_price) . '</td>
                                    <td colspan="2" style="text-align:right; font-weight:bold;">' . $this->formatNo(@$begin[0]->begin_amount) . '</td>
                                </tr>';

                    $balnce = @$begin[0]->begin_amount;
                }

                $balnce += $itemReceipt->amount;
                $html .= '  <tr>
                                <td>' . $nod . '</td>
                                <td>' . $itemReceipt->item_rm_id . '</td>
                                <td>' . $itemReceipt->item_rm_number . '</td>
                                <td>' . $itemReceipt->item_rm_name . '</td>
                                <td>' . $itemReceipt->trans_type . '</td>
                                <td>' . $itemReceipt->created_name . '</td>
                                <td>' . $itemReceipt->po_date . '</td>
                                <td>' . $itemReceipt->trans_date . '</td>
                                <td>' . $itemReceipt->custom_kind . '</td>
                                <td>' . $itemReceipt->custom_no . '</td>
                                <td>' . $itemReceipt->document_no . '</td>
                                <td>' . $itemReceipt->custom_date . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->qty) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->price) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($itemReceipt->amount) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balnce) . '</td>
                            </tr>';

                $last_item_rm_id = $item_rm_id;
                $nod++;
            }
        }
        echo $html;
    }
}
