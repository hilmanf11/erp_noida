<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Barcode_divides_fg extends CI_Controller
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
        $this->form_validation->set_rules('label_no', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/barcode_divides_fg');
        } else {
            redirect('error_access');
        }
    }

    public function getSerial()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');

            $this->db->select('*');
            $this->db->from('shipping_orders');
            $this->db->where('status', 0);
            $this->db->where('checksheet_label', $label_no);
            $records = $this->db->get()->result_array();

            $barcode_divides = $this->crud->read("barcode_divides_fg", [], ["label_no" => $label_no]);
            $barcode_divides2 = $this->crud->read("barcode_divides_fg", [], ["label_divided" => $label_no]);

            if (!empty($barcode_divides)) {
                $result['total'] = 0;
                $result['message'] = "Barcode Divided has been created";
                $result = array_merge($result, ['rows' => []]);
                die(json_encode($result));
            }

            if (@$barcode_divides2->type == "SUPPLY") {
                $result['total'] = 0;
                $result['message'] = "Barcode Divided cannot scan because this label for SUPPLY";
                $result = array_merge($result, ['rows' => []]);
                die(json_encode($result));
            }

            if(empty($records)){
                $this->db->select("a.*, b.trans_date as receipt_date, b.id as reff, c.id as item_fg_id, c.number_customer as number, c.name, '' as customer_id");
                $this->db->from('scan_item_receipts_fg a');
                $this->db->join('os_fg b', 'b.id = a.workorder');
                $this->db->join('item_fg c', 'b.item_fg_id = c.id');
                $this->db->where('a.status', 0);
                $this->db->where('a.checksheet_label', $label_no);
                $records = $this->db->get()->result_array();
                if (!$records) {
                    $this->db->select("a.*, DATE_FORMAT(a.created_date, '%Y-%m-%d') as receipt_date, a.checksheet_number as reff, c.id as item_fg_id, c.number_customer as number, c.name, b.customer_id");
                    $this->db->from('scan_item_receipts_fg a');
                    $this->db->join('wip_receipts b', 'a.checksheet_number = b.checksheet_number and a.workorder = b.workorder');
                    $this->db->join('item_fg c', 'b.item_fg_id = c.id');
                    $this->db->where('a.status', 0);
                    $this->db->where('a.checksheet_label', $label_no);
                    $records = $this->db->get()->result_array();
                    if (!$records) {
                        $this->db->select("a.*, DATE_FORMAT(a.created_date, '%Y-%m-%d') as receipt_date, c.id as item_fg_id, c.number_customer as number, c.name");
                        $this->db->from('barcode_divides_fg a');
                        $this->db->join('scan_item_receipts_fg b', 'a.reff = b.checksheet_number', 'left');
                        $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
                        $this->db->join('os_fg d', 'a.reff = d.id', 'left');
                        $this->db->where('a.status', 0);
                        $this->db->where('a.label_divided', $label_no);
                        $this->db->group_by('a.label_divided');
                        $records = $this->db->get()->result_array();
                    }
                }

                //Mapping Data
                $result['total'] = count($records);
                $result['message'] = "Label not found!";
                $result = array_merge($result, ['rows' => $records]);
                echo json_encode($result);
            }else{
                //Mapping Data
                $result['total'] = 0;
                $result['message'] = "Barcode Already Out";
                $result = array_merge($result, ['rows' => []]);
                echo json_encode($result);
            }
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                if ($post['bal'] > 0) {
                    $barcode_divides = $this->crud->read("barcode_divides_fg", [], ["label_no" => $post['label_no']]);

                    $explode = @explode("D", $post['label_no']);
                    
                    if(empty($explode[0])){
                        $reff = $post['label_no'];
                        $label = $post['label_no'];
                    }else{
                        $reff = $explode[0];
                        $label = @$explode[1];
                    }

                    if(empty($explode[0])){
                        $autoID = $reff."D".sprintf("%02s", 1);
                    }else{
                        $autoID = $reff."D".sprintf("%02s", $label + 1);
                    }

                    for ($i = 0; $i < 2; $i++) {
                        $label_divided = $autoID;
                        
                        if ($i == 0) {
                            $qty = $post['qty'];
                            $type = "SUPPLY";
                        } else {
                            $qty = $post['bal'];
                            $type = "BALANCE";
                        }

                        $arrLabel = [
                            "customer_id" => $post['customer_id'],
                            "item_fg_id" => $post['item_fg_id'],
                            "reff" => $post['reff'],
                            "label_no" => $post['label_no'],
                            "label_divided" => $label_divided,
                            "qty" => $qty,
                            "type" => $type
                        ];

                        if (!$barcode_divides) {
                            $this->db->trans_start();
                                $this->crud->create('barcode_divides_fg', $arrLabel);
                                $this->db->update('barcode_divides_fg', ["status" => 1], ["label_divided" => $post['label_no']]);
                                $this->db->update('scan_item_receipts_fg', ["status" => 1], ["checksheet_label" => $post['label_no']]);
                            $this->db->trans_complete();
                            
                            if ($this->db->trans_status() === FALSE) {
                                $message = json_encode(array("title" => "Failed", "message" => "Data Saved Unsuccessfully ", "theme" => "error"));
                            } else {
                                $this->createQrcode($label_divided, "assets/image/qrcode/");
                                $message = json_encode(array("title" => "Success", "message" => "Data Saved Successfully ", "theme" => "success"));
                            }
                        } else {
                            $message = json_encode(array("title" => "Available", "message" => "Barcode Divided has been created ", "theme" => "error"));
                        }
                        
                        if(empty($explode[0])){
                            $autoID = $reff."D".sprintf("%02s", substr($label_divided, -2) + 1);
                        }else{
                            $autoID = $reff."D".sprintf("%02s", substr($label_divided, -2) + 1);
                        }
                    }
                } else {
                    $message = json_encode(array("title" => "Not Balance", "message" => "Qty Balance Cannot Minus or Zero ", "theme" => "error"));
                }

                echo $message;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print()
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $label_no = base64_decode($this->input->get('label_no'));

        $this->db->select("a.*, DATE_FORMAT(a.created_date, '%Y-%m-%d') as trans_date, b.number_customer, b.number as item_fg_number, b.name as item_fg_name, c.number as customer_number, c.name as customer_name");
        $this->db->from('barcode_divides_fg a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.label_no', $label_no);
        $this->db->group_by('a.label_divided');
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $label_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        $html .= '<div style="width: 120mm;">';
        foreach ($records as $record) {
            $checksheet_number = $record->reff;
            $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                            <table id="customers" border="1">
                                <tr>
                                    <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">
                                        <img src="' . base_url('assets/image/qrcode/' . $checksheet_number . '.png') . '" width="50"/>
                                    </td>
                                    <td colspan="2" style="font-size:15px; text-align:left;"><span style="font-size:10px;">Checksheet ID</span><br><b>'.$checksheet_number.'</b><br>
                                        <span style="color:blue;">DIVIDED FOR '.$record->type.'</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Name.</td>
                                    <td colspan="2"><small style="font-size:12px;"><span style="font-size:10px;">' . $record->customer_number . '</span><br><b>' . $record->customer_name . '</b></small></td>
                                </tr>
                                <tr>
                                    <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">EBWS P/N</td>
                                    <td colspan="2"><small style="font-size:30px;"><b>' . $record->item_fg_number . '</b></small></td>
                                </tr>
                                <tr>
                                    <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
                                    <td colspan="2"><small style="font-size:14px;"><b>' . $record->number_customer . '</b></small></td>
                                </tr>
                                <tr>
                                    <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Label. No</td>
                                    <td colspan="2"><small style="font-size:14px;"><b>' . $record->label_divided . '</b></small></td>
                                </tr>
                                <tr>
                                    <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Quantity</td>
                                    <td style="width:25mm;"><small style="font-size:20px;"><b>' . number_format($record->qty, 2) . '</b></small></td>
                                    <td style="text-align:center;" style="width:25mm" rowspan="3">
                                        <img src="' . base_url('assets/image/qrcode/' . $record->label_divided . '.png') . '" width="100"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Date</td>
                                    <td><small style="font-size:16px;"><b>' . $record->trans_date . '</b></small></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:center;"><img src="'.$config->favicon.'" width="60"/></td>
                                </tr>
                            </table>
                        </div>';
        }
        $html .= '</div></body>';
        die($html);
    }
}
