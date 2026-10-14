<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sales_returns extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('sales/sales_returns');
        } else {
            redirect('error_access');
        }
    }

    public function readInvoiceNo()
    {
        $records = $this->crud->query("select a.number, a.customer_id, a.plant, b.id as customer_address_id from sales_invoices a 
        join customer_address b on a.plant = b.plant 
        where a.cancel='0' group by a.number, a.customer_id, a.plant
        order by a.number");
        echo json_encode($records);
    }

    public function readAddress()
    {
        $send = $this->crud->query("SELECT * FROM customer_address");
        echo json_encode($send);
    }

    public function readSalesInvoiceNo()
    {
        $records = $this->crud->query("SELECT sales_invoice_no FROM sales_returns WHERE status = '0' GROUP BY sales_invoice_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReturnNo()
    {
        $records = $this->crud->query("SELECT return_no FROM sales_returns WHERE status = '0' GROUP BY return_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readDocumentNo()
    {
        $records = $this->crud->query("SELECT document_no FROM sales_returns WHERE status = '0' GROUP BY document_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function document_no($id_menu, $return_date)
    {
        // $datenow = date("Y-m-d");
        $datenow = base64_decode($return_date);
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(document_no) as kode FROM sales_returns WHERE document_no like '%$whereDate%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;

            if ($kode == NULL) {
                $autoID = sprintf("%0" . $row_menu->sequence . "s", $kode + 1);
                $autoNo = $whereDate . $autoID;
            } else {
                $urutan = (int) substr($kode, -6);
                $urutan++;
                $autoID    = sprintf("%0" . $row_menu->sequence . "s", $urutan);
                $autoNo = $whereDate . $autoID;
            }

            echo $autoNo;
        }
    }

    public function return_id($document_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(return_id) as kode FROM sales_returns WHERE return_id like '%$document_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $document_no . "-" . $autoID;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_customer = $this->input->get('filter_customer');
            $filter_plant = $this->input->get('filter_plant');
            $filter_sales_invoice_no = $this->input->get('filter_sales_invoice_no');
            $filter_return_no = $this->input->get('filter_return_no');
            $filter_document_no = $this->input->get('filter_document_no');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $id = $_POST['id'];
            if ($id === "0") {
                $this->db->select("a.document_no,a.return_no,a.sales_invoice_no,a.return_date, b.name as customer_name, c.plant, (CASE WHEN d.total_label = e.total_label_scan THEN 1 ELSE 0 END) as scan, a.status");
                $this->db->from('sales_returns a');
                $this->db->join('customers b', 'a.customer_id = b.id');
                $this->db->join('customer_address c', 'a.customer_address_id = c.id');
                $this->db->join("(SELECT return_id, COUNT(id) as total_label FROM sales_return_labels GROUP BY return_id) d", 'a.return_id = d.return_id', 'left');
                $this->db->join("(SELECT return_id, COUNT(id) as total_label_scan FROM sales_return_labels where status='1' GROUP BY return_id) e", 'a.return_id = e.return_id', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.return_date >=', $filter_from);
                    $this->db->where('a.return_date <=', $filter_to);
                }
                if ($filter_customer != "") {
                    $this->db->where('a.customer_id', $filter_customer);
                }
                if ($filter_plant != "") {
                    $this->db->where('a.customer_address_id', $filter_plant);
                }
                if ($filter_sales_invoice_no != "") {
                    $this->db->where('a.sales_invoice_no', $filter_sales_invoice_no);
                }
                if ($filter_return_no != "") {
                    $this->db->where('a.return_no', $filter_return_no);
                }
                if ($filter_document_no != "") {
                    $this->db->where('a.document_no', $filter_document_no);
                }
                $this->db->group_by('a.document_no');
                $this->db->order_by('a.document_no', 'DESC');
                // $this->db->order_by('a.status', 'ASC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['document_no'],
                        "document_no" => $record['document_no'],
                        "return_no" => $record['return_no'],
                        "sales_invoice_no" => $record['sales_invoice_no'],
                        "return_date" => $record['return_date'],
                        "customer_name" => $record['customer_name'],
                        "plant" => $record['plant'],
                        "scan" => $record['scan'],
                        "status" => $record['status'],
                        "state" => "closed",
                    );
                }
                //Mapping Data
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, a.id as sales_returns_id, a.return_id as id, b.name as customer_name, c.plant, d.total_label, e.total_label_scan, (CASE WHEN d.total_label = e.total_label_scan THEN 1 ELSE 0 END) as scan, coalesce(f.total_return,0) as total_return');
                $this->db->from('sales_returns a');
                $this->db->join('customers b', 'a.customer_id = b.id');
                $this->db->join('customer_address c', 'a.customer_address_id = c.id');
                $this->db->join("(SELECT return_id, COUNT(id) as total_label FROM sales_return_labels GROUP BY return_id) d", 'a.return_id = d.return_id', 'left');
                $this->db->join("(SELECT return_id, COUNT(id) as total_label_scan FROM sales_return_labels where status='1' GROUP BY return_id) e", 'a.return_id = e.return_id', 'left');
                $this->db->join("(select item_fg_id, sales_invoice_no, coalesce(sum(qty_return),0) as total_return from sales_returns group by item_fg_id, sales_invoice_no) f", "f.item_fg_id = a.item_fg_id and f.sales_invoice_no = a.sales_invoice_no", "left");
                $this->db->where('a.deleted', 0);
                $this->db->where('a.document_no', $id);
                $records = $this->db->get()->result_array();
                echo json_encode($records);
            }
        }
    }

    public function datatablesTemp()
    {
        $sales_invoice_no = base64_decode($this->input->get('sales_invoice_no'));

        //Select Query
        $this->db->select("a.*, a.qty as qty_invoice, b.number_customer as item_no_customer, b.qty_box as qty_lot, coalesce(c.total_return,0) as total_return");
        $this->db->from('sales_invoices a');
        $this->db->join('item_fg b', 'a.item_no = b.number');
        $this->db->join("(select item_fg_id, sales_invoice_no, coalesce(sum(qty_return),0) as total_return from sales_returns group by item_fg_id, sales_invoice_no) c", "c.item_fg_id = b.id and a.number = c.sales_invoice_no", "left");
        $this->db->where('a.number', $sales_invoice_no);
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $send = $this->crud->create('sales_returns', array_merge($post, ["return_id" => $this->return_id($post['document_no'])]));
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $sales_returns = $this->crud->read('sales_returns', [], ["id" => $post['sales_returns_id']]);
            $total_return = $this->db->query("SELECT sum(qty_return) as total FROM sales_returns WHERE sales_invoice_no = '" . @$sales_returns->sales_invoice_no . "' and item_fg_id='" . @$sales_returns->item_fg_id . "'")->row();
            if (((int)$post['qty_return'] + (int)@$total_return->total - (int)@$sales_returns->qty_return) <= @$sales_returns->qty_invoice) {
                $send =  $this->crud->update('sales_returns', ["id" => $post['sales_returns_id']], ["qty_return" => $post['qty_return'], "qty_box" => $post['qty_box']]);
            } else {
                $send = json_encode(array("title" => "Error", "message" => "Qty Return more than Qty Invoice", "theme" => "error"));
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function check_return_no()
    {
        $post = $this->input->post();
        $sales_invoice_no = $this->crud->reads('sales_returns', [], ["return_no" => $post['return_no']]);
        die(json_encode(array("total" => count($sales_invoice_no))));
    }

    public function create_check_label()
    {
        $post = $this->input->post();
        $sales_return_labels = $this->crud->reads('sales_return_labels', [], ["return_id" => $post['return_id']]);
        die(json_encode(array("total" => count($sales_return_labels))));
    }

    public function create_label()
    {
        $post = $this->input->post();
        $return_id = $post['return_id'];
        $qty = $post['qty'];

        $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM sales_return_labels WHERE return_id = '$return_id'");
        $rowID = $sqlGetID->row();
        $label = $rowID->kode;
        if ($label == NULL) {
            $autoID = $return_id . sprintf("%04s", $label + 1);
        } else {
            $urutan = (int) substr($label, -4);
            $autoID = $return_id . sprintf("%04s", $urutan + 1);
        }
        //Simpan Label
        $arrLabel = [
            "return_id" => strtoupper($return_id),
            "label_no" => strtoupper($autoID),
            "qty" => $qty,
        ];

        $send = $this->crud->create('sales_return_labels', $arrLabel);
        die($send);
    }

    public function delete()
    {
        $data = $this->input->post();
        $deleteSalesReturns = $this->crud->delete('sales_returns', ["return_id" => $data['id']]);
        $deleteLabels = $this->crud->delete('sales_return_labels', ["return_id" => $data['id']]);
        echo $deleteSalesReturns;
    }

    public function print_label($return_id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $return_id = base64_decode($return_id);
        //Cek Label
        $this->db->select('a.*, b.customer_id, d.number, d.number_customer,b.return_date, c.name as customer_name');
        $this->db->from('sales_return_labels a');
        $this->db->join('sales_returns b', 'a.return_id = b.return_id');
        $this->db->join('customers c', 'b.customer_id = c.id');
        $this->db->join('item_fg d', 'b.item_fg_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.return_id', $return_id);
        $this->db->order_by('a.id', 'ASC');
        $sales_return_labels = $this->db->get()->result_object();
        $this->createQrcode($return_id, "assets/image/qrcode/");

        $html = '<html>
                    <head>
                        <title>' . $return_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        $no = 1;
        foreach ($sales_return_labels as $sales_return_label) {
            $qrcodes = $sales_return_label->label_no;
            $this->createQrcode($qrcodes, "assets/image/qrcode/", $sales_return_label->label_no);
            $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1">
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $return_id . '.png') . '" width="50"/>
                                        </td>
                                        <td colspan="2" style="font-size:15px; text-align:left;"><span style="font-size:10px;">Checksheet ID</span><br><b>' . $return_id . '</b></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Name.</td>
                                        <td colspan="2"><small style="font-size:12px;"><span style="font-size:10px;">' . $sales_return_label->customer_id . '</span><br><b>' . $sales_return_label->customer_name . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">EBWS P/N</td>
                                        <td colspan="2"><small style="font-size:30px;"><b>' . $sales_return_label->number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $sales_return_label->number_customer . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Label. No</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $sales_return_label->label_no . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Quantity</td>
                                        <td style="width:25mm;"><small style="font-size:20px;"><b>' . number_format($sales_return_label->qty, 2) . '</b></small></td>
                                        <td style="text-align:center;" style="width:25mm" rowspan="3">
                                            <img src="' . base_url('assets/image/qrcode/' . $sales_return_label->label_no . '.png') . '" width="100"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Date</td>
                                        <td><small style="font-size:16px;"><b>' . $sales_return_label->return_date . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:center;"><img src="' . $config->favicon . '" width="60"/></td>
                                    </tr>
                                </table>
                            </div>';
            $no++;
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_returns_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_customer = $this->input->get('filter_customer');
        $filter_plant = $this->input->get('filter_plant');
        $filter_sales_invoice_no = $this->input->get('filter_sales_invoice_no');
        $filter_return_no = $this->input->get('filter_return_no');
        $filter_document_no = $this->input->get('filter_document_no');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, a.return_id as id, b.name as customer_name, c.plant, d.total_label, e.total_label_scan, (CASE WHEN d.total_label = e.total_label_scan THEN 1 ELSE 0 END) as scan');
        $this->db->from('sales_returns a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('customer_address c', 'a.customer_address_id = c.id');
        $this->db->join("(SELECT return_id, COUNT(id) as total_label FROM sales_return_labels GROUP BY return_id) d", 'a.return_id = d.return_id', 'left');
        $this->db->join("(SELECT return_id, COUNT(id) as total_label_scan FROM sales_return_labels where status='1' GROUP BY return_id) e", 'a.return_id = e.return_id', 'left');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where('a.return_date >=', $filter_from);
            $this->db->where('a.return_date <=', $filter_to);
        }
        if ($filter_customer != "") {
            $this->db->where('a.customer_id', $filter_customer);
        }
        if ($filter_plant != "") {
            $this->db->where('a.customer_address_id', $filter_plant);
        }
        if ($filter_sales_invoice_no != "") {
            $this->db->where('a.sales_invoice_no', $filter_sales_invoice_no);
        }
        if ($filter_return_no != "") {
            $this->db->where('a.return_no', $filter_return_no);
        }
        if ($filter_document_no != "") {
            $this->db->where('a.document_no', $filter_document_no);
        }
        $this->db->order_by('a.document_no', 'DESC');
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
                                <small>SALES RETURNS</small>
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
                <th>Scan</th>
                <th>ERP Document</th>
                <th>Sales Return ID</th>
                <th>Sales Return No</th>
                <th>Sales Invoice No</th>
                <th>GL No</th>
                <th>Trans Date</th>
                <th>Customer Name</th>
                <th>Plant</th>
                <th>Delivery Note</th>
                <th>Sales Order No</th>
                <th>Customer Po No</th>
                <th>Product No EBWS</th>
                <th>Product No Customer</th>
                <th>HSN Code</th>
                <th>UoM</th>
                <th>Qty Invoice</th>
                <th>Qty Return</th>
                <th>Lot Box</th>
                <th>Qty Box</th>
                <th>Total Label</th>
                <th>Total Scan</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['total_label'] == $data['total_label_scan']) {
                $statusScan = "Scanned";
                $styleScan = "style='background: green; color:white;'";
            } else {
                $statusScan = "Not Scan";
                $styleScan = "style='background: red; color:white;'";
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td ' . $styleScan . '>' . $statusScan . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['return_id'] . '</td>
                        <td>' . $data['return_no'] . '</td>
                        <td>' . $data['sales_invoice_no'] . '</td>
                        <td></td>
                        <td>' . $data['return_date'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['plant'] . '</td>
                        <td>' . $data['delivery_note_no'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_customer'] . '</td>
                        <td>' . $data['hsn_code'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . number_format($data['qty_invoice']) . '</td>
                        <td>' . number_format($data['qty_return']) . '</td>
                        <td>' . number_format($data['qty_lot']) . '</td>
                        <td>' . number_format($data['qty_box']) . '</td>
                        <td>' . number_format($data['total_label']) . '</td>
                        <td>' . number_format($data['total_label_scan']) . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
