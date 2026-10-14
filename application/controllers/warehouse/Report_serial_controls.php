<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_serial_controls extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/report_serial_controls');
        } else {
            redirect('error_access');
        }
    }

    public function readDeliveryOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get("customer_id");
        $item_fg_id = $this->input->get("item_id");

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sales_orders = $this->crud->query("SELECT delivery_order_no as `number`
        FROM delivery_orders
        WHERE customer_id = '$customer_id' 
        AND item_fg_id = '$item_fg_id'
        GROUP BY delivery_order_no 
        ORDER BY delivery_order_no DESC");
        
        echo json_encode($sales_orders);
    }

    public function readDeliveryNote()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $do_number = base64_decode($this->input->get("do_number"));

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sales_orders = $this->crud->query("SELECT delivery_note_no as `number`
        FROM delivery_notes
        WHERE delivery_order_no = '$do_number'
        GROUP BY delivery_note_no 
        ORDER BY delivery_note_no DESC");
        
        echo json_encode($sales_orders);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_serial_controls_$format.xls");
        }

        $filter_month = $this->input->get("filter_month");
        $filter_year = $this->input->get("filter_year");
        $filter_customer = $this->input->get("filter_customer");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_do_no = base64_decode($this->input->get("filter_do_no"));
        $filter_dn_no = base64_decode($this->input->get("filter_dn_no"));
        $filter_serial_no = base64_decode($this->input->get("filter_serial_no"));
        $period = $filter_year . '-' .$filter_month;

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.checksheet_number, a.checksheet_label, a.qty, a.status, d.number_customer AS item_no, d.name AS item_name, e.delivery_order_no, e.delivery_order_date, e.sales_order_no, f.name AS customer_name, e.delivery_note_no');
        $this->db->from('scan_item_receipts_fg a');
        $this->db->join('wip_receipts b', 'a.checksheet_number = b.checksheet_number', 'left');
        $this->db->join('os_fg c', 'a.workorder = c.id', 'left');
        $this->db->join('rgp_transactions g', 'a.workorder = g.id', 'left');
        $this->db->join('sales_returns h', 'a.checksheet_number = h.return_id', 'left');
        $this->db->join('item_fg d', '(b.item_fg_id = d.id OR c.item_fg_id = d.id or g.item_id = d.id or h.item_fg_id = d.id)');
        $this->db->join("(SELECT a.checksheet_label, c.delivery_note_no, b.delivery_order_no, b.delivery_order_date, b.sales_order_no
            FROM shipping_orders a
            JOIN delivery_orders b ON a.delivery_order_no = b.delivery_order_no
            JOIN delivery_notes c ON b.delivery_order_no = c.delivery_order_no
            GROUP BY a.id) e", 'a.checksheet_label = e.checksheet_label', 'left');
        $this->db->join('customers f', '(b.customer_id = f.id or h.customer_id = f.id)', 'left');
        $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m') = '$period'");
        if($filter_customer != ""){
            $this->db->like('f.id', $filter_customer);
        }
        if($filter_product_no != ""){
            $this->db->like('d.id', $filter_product_no);
        }
        if($filter_do_no != ""){
            $this->db->like('e.delivery_order_no ', $filter_do_no);
        }
        if($filter_dn_no != ""){
            $this->db->like('e.delivery_note_no', $filter_dn_no);
        }
        if($filter_serial_no != ""){
            $this->db->like('a.checksheet_label', $filter_serial_no);
        }
        $this->db->group_by('a.checksheet_label');
        $this->db->order_by('e.delivery_order_no', 'ASC');
        $this->db->order_by('a.checksheet_label', 'ASC');
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
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '
                </div>
                <br><br><br>
                <h3 style="margin:0;">CHECK SERIAL NO (FG)</h3>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Status</th>
                <th>Checksheet No</th>
                <th>Delivery Note</th>
                <th>Delivery Order</th>
                <th>Trans Date</th>
                <th>Sales Order</th>
                <th>Customer</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Serial No</th>
                <th>Qty</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $barcode_divides_fg = $this->crud->reads("barcode_divides_fg", ["label_no" => $data['checksheet_label']]);
            if(empty($data['delivery_note_no']) && $data['status'] == "0"){
                $status = "OPEN";
                $style = "style='text-align: center; background: green; color:white;'";
            }elseif(count($barcode_divides_fg) > 0){
                $status = "DIVIDED";
                $style = "style='text-align: center; background: red; color:white;'";
            }else{
                $status = "CLOSED";
                $style = "style='text-align: center; background: red; color:white;'";
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td '.$style.'>' . $status . '</td>
                            <td>' . $data['checksheet_number'] . '</td>
                            <td>' . $data['delivery_note_no'] . '</td>
                            <td>' . $data['delivery_order_no'] . '</td>
                            <td>' . $data['delivery_order_date'] . '</td>
                            <td>' . $data['sales_order_no'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['item_no'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['checksheet_label'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                        </tr>';
            $no++;

            foreach ($barcode_divides_fg as $barcode) {

                $shipping = $this->db->query("SELECT a.checksheet_label, c.delivery_note_no, b.delivery_order_no, b.delivery_order_date, b.sales_order_no, d.name as customer_name
                    FROM shipping_orders a
                    JOIN delivery_orders b ON a.delivery_order_no = b.delivery_order_no
                    JOIN delivery_notes c ON b.delivery_order_no = c.delivery_order_no
                    JOIN customers d ON c.customer_id = d.id
                    WHERE a.checksheet_label = '$barcode->label_divided'
                    GROUP BY a.id");
                $dataship = $shipping->row_array();

                if($barcode->status == "0"){
                    $status = "OPEN";
                    $style = "style='text-align: center; background: green; color:white;'";
                }else{
                    $status = "CLOSED";
                    $style = "style='text-align: center; background: red; color:white;'";
                }

                $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td '.$style.'>' . $status . '</td>
                            <td>' . $data['checksheet_number'] . '</td>
                            <td>' . $dataship['delivery_note_no'] . '</td>
                            <td>' . $dataship['delivery_order_no'] . '</td>
                            <td>' . $dataship['delivery_order_date'] . '</td>
                            <td>' . $dataship['sales_order_no'] . '</td>
                            <td>' . $dataship['customer_name'] . '</td>
                            <td>' . $data['item_no'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $barcode->label_divided . '</td>
                            <td>' . number_format($barcode->qty, 2) . '</td>
                        </tr>';
                $no++;
            }
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
