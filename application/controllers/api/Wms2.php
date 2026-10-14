<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Wms2 extends CI_Controller
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
    }

    public function index()
    {
        redirect('error_access');
    }

    public function formatDate($date)
    {
        $date = new DateTime($date, new DateTimeZone('UTC'));
        return $date->format('Y-m-d\TH:i:s.v\Z');
    }

    public function login($expired = "")
    {
        $url = 'http://192.100.10.61:6622/api/auth/login';

        $data = array(
            "username" => "admin",
            "password" => "Admin@123",
        );

        $ch = curl_init($url);
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);

        // if($response === false) {
        //     echo 'Curl error: ' . curl_error($ch);
        // } else {
        //     return $response;
        // }

        // $query = $this->db->query("SELECT * FROM token_wms WHERE expired > now() ORDER BY id DESC limit 1");
        // $record = $query->row_array();

        // if(empty($record)){
        $login = json_decode($response, true);

        if ($login['success']) {
            $token = $login['data']['token'];
            // $this->session->set_userdata(["token" => $token]);
            // $this->crud->create("token_wms", ["token" => $token, "expired" => date("Y-m-d H:i:s", strtotime("+".$login['data']['expiresIn']))]);
        } else {
            die(json_encode(array("success" => false, "message" => "Generate Token Failed, Check Username or Password")));
        }
        // }else{
        //     if($expired != ""){
        //         $login = json_decode($response, true);

        //         if($login['success']){
        //             $token = $login['data']['token'];
        //             // $this->session->set_userdata(["token" => $token]);
        //             $this->crud->create("token_wms", ["token" => $token, "expired" => date("Y-m-d H:i:s", strtotime("+".$login['data']['expiresIn']))]);
        //         } else {
        //             die(json_encode(array("success" => false, "message" => "Generate Token Failed, Check Username or Password")));
        //         }
        //     }else{
        //         $token = $record['token'];
        //     }
        // }

        return $token;
    }

    public function gateEntry()
    {
        $url = 'http://192.100.10.61:6622/api/wms/inbound/gate-entry';
        $token = $this->login();

        $receipt_no = $this->input->post('receipt_no');

        $this->db->select('a.receipt_no, a.receipt_date, a.supplier_id, b.name as supplier_name, a.bc_document, a.bc_date, b.currency, a.created_by, a.created_date');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.receipt_no', "POR-N262710001454");
        $this->db->where('a.status !=', 2);
        $this->db->group_by('a.receipt_no');
        $this->db->order_by('a.receipt_no', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('a.receipt_id, a.item_rm_id, a.po_no, c.number, c.name, a.qty_delivery, a.qty_receipt, c.uom, a.price, a.qty_mpq, a.qty_label');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.receipt_id', "POR-N262710001454-039");
        $this->db->where('a.status !=', 2);
        $this->db->group_by('a.id');
        $this->db->order_by('a.receipt_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $this->db->select('a.receipt_id, a.label_no, d.name as supplier_name, c.id, c.number, b.po_no, b.bc_document, b.receipt_date, b.qty_receipt, a.qty');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('suppliers d', 'b.supplier_id = d.id', 'left');
        $this->db->where('a.receipt_id', "POR-N262710001454-039");
        $this->db->where('b.status !=', 2);
        $this->db->group_by('a.id');
        $this->db->order_by('a.label_no', 'ASC');
        $Arlabels = $this->db->get()->result_array();

        $details = array();
        $lineNo = 1;
        $qtyLabelTotal = 0;
        foreach ($Ardetails as $detail) {
            $details[] = array(
                "line_number"               => $lineNo,
                "receipt_number"            => $detail['receipt_id'],
                "po_number"                 => $detail['po_no'],
                "ebws_part_number"          => $detail['item_rm_id'],
                "supplier_part_number"      => $detail['number'],
                "supplier_part_description" => $detail['name'],
                "gate_entry_qty"            => floatval($detail['qty_delivery']),
                "gate_entry_confirmed_qty"  => floatval($detail['qty_receipt']),
                "uom"                       => $detail['uom'],
                "price"                     => floatval($detail['price']),
                "mpq"                       => floatval($detail['qty_mpq']),
            );

            $lineNo++;
            $qtyLabelTotal += $detail['qty_label'];
        }

        $labels = array();
        $qtyLabel = 0;
        foreach ($Arlabels as $label) {
            $labels[] = array(
                "label_serial_number"       => $label['label_no'],
                "receipt_number"            => $label['receipt_id'],
                "supplier_name"             => $label['supplier_name'],
                "part_no_ebws"              => $label['id'],
                "part_no_supplier"          => $label['number'],
                "po_number"                 => $label['po_no'],
                "invoice_no"                => $label['bc_document'],
                "gate_entry_date"           => $this->formatDate($label['receipt_date']),
                "gate_entry_confirmed_qty"  => floatval($label['qty_receipt']),
                "label_qty"                 => floatval($label['qty']),
                "status"                    => "ACTIVE"
            );

            $qtyLabel++;
        }

        if ($qtyLabelTotal != $qtyLabel) {
            die(json_encode(array("success" => false, "message" => "Label not all created, Total (" . $qtyLabelTotal . ") Label (" . $qtyLabel . ")")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: ' . $token,
        ];

        $data = array(
            "gate_entry_number" => $header['receipt_no'],
            "gate_entry_date"   => $this->formatDate($header['receipt_date']),
            "supplier_id"       => $header['supplier_id'],
            "supplier_name"     => $header['supplier_name'],
            "invoice_number"    => $header['bc_document'],
            "invoice_date"      => $this->formatDate($header['bc_date']),
            "currency"          => $header['currency'],
            "created_by"        => $header['created_by'],
            "created_date"      => $this->formatDate($header['created_date']),
            "approved_by"       => "",
            "details"           => $details,
            "labels"            => $labels
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $result = (array) json_decode($response, true);
        die($response);
    }
}
