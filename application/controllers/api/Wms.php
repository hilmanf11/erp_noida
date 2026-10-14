<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Wms extends CI_Controller
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

    public function formatDate($date){
        $date = new DateTime($date, new DateTimeZone('UTC'));
        return $date->format('Y-m-d\TH:i:s.v\Z');
    }

    public function login($expired = ""){
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
            
            if($login['success']){
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

    public function gateEntry(){
        $url = 'http://192.100.10.61:6622/api/wms/inbound/gate-entry';
        $token = $this->login();

        $receipt_no = $this->input->post('receipt_no');

        $this->db->select('a.receipt_no, a.receipt_date, a.supplier_id, b.name as supplier_name, a.bc_document, a.bc_date, b.currency, a.created_by, a.created_date');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.receipt_no', $receipt_no);
        $this->db->where('a.status !=', 2);
        $this->db->group_by('a.receipt_no');
        $this->db->order_by('a.receipt_no', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('a.receipt_id, a.item_rm_id, a.po_no, c.number, c.name, a.qty_delivery, a.qty_receipt, c.uom, a.price, a.qty_mpq, a.qty_label');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.receipt_no', $receipt_no);
        $this->db->where('a.status !=', 2);
        $this->db->group_by('a.id');
        $this->db->order_by('a.receipt_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $this->db->select('a.receipt_id, a.label_no, d.name as supplier_name, c.id, c.number, b.po_no, b.bc_document, b.receipt_date, b.qty_receipt, a.qty');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('suppliers d', 'b.supplier_id = d.id', 'left');
        $this->db->where('b.receipt_no', $receipt_no);
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

        if($qtyLabelTotal != $qtyLabel){
            die(json_encode(array("success" => false, "message" => "Label not all created, Total (".$qtyLabelTotal.") Label (".$qtyLabel.")")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
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

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $result = (array) json_decode($response, true);
        die($response);
    }

    public function focIn(){
        $url = 'http://192.100.10.61:6622/api/wms/inbound/foc-in';
        $token = $this->login();

        $receipt_no = $this->input->post('receipt_no');

        $this->db->select("a.number as ebws_foc_document_number,
                a.process_type,
                a.transaction_date,
                (CASE WHEN b.id is null THEN c.id ELSE b.id END) as company_id,
                (CASE WHEN b.name is null THEN c.name ELSE b.name END) as company_name,
                a.plant,
                'RM' as category,
                e.name as department,
                a.reference_number,
                a.approved_by,
                a.approved_date,
                a.created_by,
                a.created_date");
        $this->db->from('rgp_transactions a');
        $this->db->join('customers b', 'a.company_id = b.id', 'left');
        $this->db->join('suppliers c', 'a.company_id = c.id', 'left');
        $this->db->join('item_categories d', 'a.item_category_id = d.id', 'left');
        $this->db->join('departements e', 'a.departement_id = e.id', 'left');
        $this->db->where('a.process_type', 'FOC');
        $this->db->where('a.type', 'IN');
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.item_category_id', 'C01');
        $this->db->where('a.number', $receipt_no);
        $this->db->group_by('a.number');
        $this->db->order_by('a.number', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('a.rgp_id, 
            (CASE WHEN b.id is null THEN c.number ELSE b.id END) as ebws_part_number,
            (CASE WHEN b.number is null THEN c.number_customer ELSE b.number END) as customer_supplier_part_number,
            (CASE WHEN b.name is null THEN c.name ELSE b.name END) as customer_supplier_part_description,
            a.uom,
            a.qty as foc_qty,
            COALESCE(d.mpq, 0) as mpq,
            a.price,
            a.currency,
            a.value as total_value,');
        $this->db->from('rgp_transactions a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->join("item_mpq d", 'a.item_id = d.item_rm_id and (a.company_id = d.supplier_id or a.company_id = d.customer_id)', 'left');
        $this->db->where('a.process_type', 'FOC');
        $this->db->where('a.type', 'IN');
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.item_category_id', 'C01');
        $this->db->where('a.number', $receipt_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.rgp_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $this->db->select('a.label_no, a.rgp_id, b.item_id,
            (CASE WHEN c.id is null THEN d.id ELSE c.id END) as company_id,
            (CASE WHEN c.name is null THEN d.name ELSE c.name END) as company_name,
            a.qty
        ');
        $this->db->from('rgp_transaction_labels a');
        $this->db->join('rgp_transactions b', 'a.rgp_id = b.rgp_id');
        $this->db->join('customers c', 'b.company_id = c.id', 'left');
        $this->db->join('suppliers d', 'b.company_id = d.id', 'left');
        $this->db->where('b.process_type', 'FOC');
        $this->db->where('b.type', 'IN');
        $this->db->where("(b.approved_to = '' or b.approved_to is null)");
        $this->db->where('b.item_category_id', 'C01');
        $this->db->where('b.number', $receipt_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.label_no', 'ASC');
        $Arlabels = $this->db->get()->result_array();

        $details = array();
        $qtyLabelTotal = 0;
        foreach ($Ardetails as $detail) {
            if($detail['mpq'] > 0){
                $qty_label = ceil($detail['foc_qty'] / $detail['mpq']);
            }else{
                $qty_label = 0;
            }

            if($detail['mpq'] == 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['ebws_part_number'] ." MPQ still 0, please check in master MPQ")));
            }

            $details[] = array(
                "receipt_number" => $detail['rgp_id'],
                "ebws_part_number" => $detail['ebws_part_number'],
                "customer_supplier_part_number" => $detail['customer_supplier_part_number'],
                "customer_supplier_part_description" => $detail['customer_supplier_part_description'],
                "mpq" => $detail['mpq'],
                "uom" => $detail['uom'],
                "foc_qty" => $detail['foc_qty'],
                "label_qty" => $qty_label,
                "price" => $detail['price'],
                "currency" => $detail['currency'],
                "total_value" => $detail['total_value'],
            );

            $qtyLabelTotal += $qty_label;
        }

        $labels = array();
        $qtyLabel = 0;
        foreach ($Arlabels as $label) {
            $labels[] = array(
                "foc_serial_number" => $label['label_no'],
                "receipt_number" => $label['rgp_id'],
                "ebws_part_number" => $label['item_id'],
                "customer_supplier_id" => $label['company_id'],
                "customer_supplier_name" => $label['company_name'],
                "label_qty" => $label['qty'],
            );

            $qtyLabel++;
        }

        if(empty($header)){
            die(json_encode(array("success" => false, "message" => "cannot send this data, Please check category and approval")));
        }
        
        if($qtyLabelTotal != $qtyLabel){
            die(json_encode(array("success" => false, "message" => "Label not all created, Total (".$qtyLabelTotal.") Label (".$qtyLabel.")")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        if(empty($header['approved_by'])){
            $approved_by = $header['created_by'];
            $approved_date = $this->formatDate($header['created_date']);
        }else{
            $approved_by = $header['approved_by'];
            $approved_date = $this->formatDate($header['approved_date']);
        }

        $data = array(
            "ebws_foc_document_number" => $header['ebws_foc_document_number'],
            "process_type" => $header['process_type'],
            "transaction_date" => $header['transaction_date'],
            "customer_supplier_id" => $header['company_id'],
            "customer_supplier_name" => $header['company_name'],
            "plant" => $header['plant'],
            "category" => $header['category'],
            "department" => $header['department'],
            "reference_doc_number" => $header['reference_number'],
            "approved_by" => $approved_by,
            "approved_date" => $approved_date,
            "created_by" => $header['created_by'],
            "created_date" => $this->formatDate($header['created_date']),
            "details" => $details,
            "labels" => $labels
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function nrgpIn(){
        $url = 'http://192.100.10.61:6622/api/wms/inbound/rgp-to-nrgp';
        $token = $this->login();

        $receipt_no = $this->input->post('receipt_no');

        $this->db->select("a.number as ebws_foc_document_number,
                a.process_type,
                a.transaction_date,
                (CASE WHEN b.id is null THEN c.id ELSE b.id END) as company_id,
                (CASE WHEN b.name is null THEN c.name ELSE b.name END) as company_name,
                a.plant,
                'RM' as category,
                e.name as department,
                a.reference_number,
                a.approved_by,
                a.approved_date,
                a.created_by,
                a.created_date");
        $this->db->from('rgp_transactions a');
        $this->db->join('customers b', 'a.company_id = b.id', 'left');
        $this->db->join('suppliers c', 'a.company_id = c.id', 'left');
        $this->db->join('item_categories d', 'a.item_category_id = d.id', 'left');
        $this->db->join('departements e', 'a.departement_id = e.id', 'left');
        $this->db->where_in('a.process_type', ['RGP', 'NRGP']);
        $this->db->where('a.type', 'IN');
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.item_category_id', 'C01');
        $this->db->where('a.number', $receipt_no);
        $this->db->group_by('a.number');
        $this->db->order_by('a.number', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('a.rgp_id, 
            (CASE WHEN b.id is null THEN c.number ELSE b.id END) as ebws_part_number,
            (CASE WHEN b.number is null THEN c.number_customer ELSE b.number END) as customer_supplier_part_number,
            (CASE WHEN b.name is null THEN c.name ELSE b.name END) as customer_supplier_part_description,
            a.uom,
            a.qty as foc_qty,
            COALESCE(d.mpq, 0) as mpq,
            a.price,
            a.currency,
            a.value as total_value,');
        $this->db->from('rgp_transactions a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->join("item_mpq d", 'a.item_id = d.item_rm_id and (a.company_id = d.supplier_id or a.company_id = d.customer_id)', 'left');
        $this->db->where_in('a.process_type', ['RGP', 'NRGP']);
        $this->db->where('a.type', 'IN');
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.item_category_id', 'C01');
        $this->db->where('a.number', $receipt_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.rgp_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $this->db->select('a.label_no, a.rgp_id, b.item_id,
            (CASE WHEN c.id is null THEN d.id ELSE c.id END) as company_id,
            (CASE WHEN c.name is null THEN d.name ELSE c.name END) as company_name,
            a.qty
        ');
        $this->db->from('rgp_transaction_labels a');
        $this->db->join('rgp_transactions b', 'a.rgp_id = b.rgp_id');
        $this->db->join('customers c', 'b.company_id = c.id', 'left');
        $this->db->join('suppliers d', 'b.company_id = d.id', 'left');
        $this->db->where_in('b.process_type', ['RGP', 'NRGP']);
        $this->db->where('b.type', 'IN');
        $this->db->where("(b.approved_to = '' or b.approved_to is null)");
        $this->db->where('b.item_category_id', 'C01');
        $this->db->where('b.number', $receipt_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.label_no', 'ASC');
        $Arlabels = $this->db->get()->result_array();

        $details = array();
        $qtyLabelTotal = 0;
        foreach ($Ardetails as $detail) {
            if($detail['mpq'] > 0){
                $qty_label = ceil($detail['foc_qty'] / $detail['mpq']);
            }else{
                $qty_label = 0;
            }

            if($detail['mpq'] == 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['ebws_part_number'] ." MPQ still 0, please check in master MPQ")));
            }

            $details[] = array(
                "receipt_number" => $detail['rgp_id'],
                "ebws_part_number" => $detail['ebws_part_number'],
                "customer_supplier_part_number" => $detail['customer_supplier_part_number'],
                "customer_supplier_part_description" => $detail['customer_supplier_part_description'],
                "mpq" => $detail['mpq'],
                "uom" => $detail['uom'],
                "nrgp_qty" => $detail['foc_qty'],
                "label_qty" => $qty_label,
                "price" => $detail['price'],
                "currency" => $detail['currency'],
                "total_value" => $detail['total_value'],
            );

            $qtyLabelTotal += $qty_label;
        }

        $labels = array();
        $qtyLabel = 0;
        foreach ($Arlabels as $label) {
            $labels[] = array(
                "nrgp_serial_number" => $label['label_no'],
                "receipt_number" => $label['rgp_id'],
                "ebws_part_number" => $label['item_id'],
                "customer_supplier_id" => $label['company_id'],
                "customer_supplier_name" => $label['company_name'],
                "label_qty" => $label['qty'],
            );

            $qtyLabel++;
        }

        if(empty($header)){
            die(json_encode(array("success" => false, "message" => "cannot send this data, Please check category and approval")));
        }
        
        if($qtyLabelTotal != $qtyLabel){
            die(json_encode(array("success" => false, "message" => "Label not all created, Total (".$qtyLabelTotal.") Label (".$qtyLabel.")")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        if(empty($header['approved_by'])){
            $approved_by = $header['created_by'];
            $approved_date = $this->formatDate($header['created_date']);
        }else{
            $approved_by = $header['approved_by'];
            $approved_date = $this->formatDate($header['approved_date']);
        }

        $data = array(
            "ebws_nrgp_document_number" => $header['ebws_foc_document_number'],
            "process_type" => $header['process_type'],
            "transaction_date" => $header['transaction_date'],
            "customer_supplier_id" => $header['company_id'],
            "customer_supplier_name" => $header['company_name'],
            "plant" => $header['plant'],
            "category" => $header['category'],
            "department" => $header['department'],
            "reference_doc_number" => $header['reference_number'],
            "approved_by" => $approved_by,
            "approved_date" => $approved_date,
            "created_by" => $header['created_by'],
            "created_date" => $this->formatDate($header['created_date']),
            "details" => $details,
            "labels" => $labels
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function materialReturn(){
        $url = 'http://192.100.10.61:6622/api/wms/inbound/material-return';
        $token = $this->login();

        $receipt_no = $this->input->post('receipt_no');

        $this->db->select("a.return_no as return_doc_number,
            a.return_date,
            a.return_name,
            a.workorder,
            a.remarks
        ");
        $this->db->from('return_materials a');
        $this->db->where('a.return_no', $receipt_no);
        $this->db->group_by('a.return_no');
        $this->db->order_by('a.return_no', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select("a.return_id as receipt_number,
            b.id as ebws_part_number,
            b.number as supplier_part_number,
            b.name as supplier_part_description,
            b.uom,
            a.mpq,
            a.qty,
            a.price
        ");
        $this->db->from('return_materials a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.return_no', $receipt_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.return_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $this->db->select('a.label_no, 
            a.return_id,
            b.return_date,
            c.id as ebws_part_number,
            c.number as supplier_part_number,
            c.uom,
            a.qty
        ');
        $this->db->from('return_material_labels a');
        $this->db->join('return_materials b', 'a.return_id = b.return_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->where('b.return_no', $receipt_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.label_no', 'ASC');
        $Arlabels = $this->db->get()->result_array();

        $details = array();
        $qtyLabelTotal = 0;
        foreach ($Ardetails as $detail) {
            if($detail['mpq'] > 0){
                $qty_label = ceil($detail['qty'] / $detail['mpq']);
            }else{
                $qty_label = 0;
            }

            if($detail['mpq'] == 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['ebws_part_number'] ." MPQ is 0")));
            }

            $details[] = array(
                "receipt_number" => $detail['receipt_number'],
                "ebws_part_number" => $detail['ebws_part_number'],
                "supplier_part_number" => $detail['supplier_part_number'],
                "supplier_part_description" => $detail['supplier_part_description'],
                "uom" => $detail['uom'],
                "mpq" => $detail['mpq'],
                "qty" => $detail['qty'],
                "price" => $detail['price'],
            );

            $qtyLabelTotal += $qty_label;
        }

        $labels = array();
        $qtyLabel = 0;
        foreach ($Arlabels as $label) {
            $labels[] = array(
                "material_return_serial_label_number" => $label['label_no'],
                "receipt_number" => $label['return_id'],
                "return_date" => $label['return_date'],
                "ebws_part_number" => $label['ebws_part_number'],
                "supplier_part_number" => $label['supplier_part_number'],
                "uom" => $label['uom'],
                "qty" => $label['qty'],
            );

            $qtyLabel++;
        }

        if(empty($header)){
            die(json_encode(array("success" => false, "message" => "Data Not Found")));
        }
        
        if($qtyLabelTotal != $qtyLabel){
            die(json_encode(array("success" => false, "message" => "Label not all created, Total (".$qtyLabelTotal.") Label (".$qtyLabel.")")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        $data = array(
            "return_doc_number" => $header['return_doc_number'],
            "return_date" => $header['return_date'],
            "return_by_name" => $header['return_name'],
            "work_order_number" => $header['workorder'],
            "remarks" => $header['remarks'],
            "details" => $details,
            "labels" => $labels
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function kanbanPP(){
        $url = 'http://192.100.10.61:6622/api/wms/material-issue/kanban-pp';
        $token = $this->login();

        $request_no = $this->input->post('request_no');

        $this->db->select('a.request_no as kanbanNumber,
            a.type as kanbanType,
            a.request_date as kanbanDate,
            d.trans_date as period,
            a.workorder as workOrderNumber,
            d.qty as workOrderQuantity,
            b.number as productPartNumber,
            b.number_customer as productCustomerCode,
            a.created_by as requestedBy
        ');
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('production_schedules d', 'a.item_fg_id = d.item_fg_id and a.workorder = d.workorder');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.request_no');
        $this->db->order_by('a.request_no', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('c.id as ebwsPartNumber,
            c.number as supplierPartNumber,
            b.main_process as mainProcess,
            c.uom,
            b.composition as qpa,
            a.mpq,
            a.qty_act as requestQuantity,
        ');
        $this->db->from('supply_sheets a');
        $this->db->join('bom b', 'a.item_fg_id = b.item_fg_id and a.item_rm_id = b.item_rm_id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('production_schedules d', 'a.item_fg_id = d.item_fg_id and a.workorder = d.workorder');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.item_rm_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $details = array();
        foreach ($Ardetails as $detail) {
            if($detail['mpq'] == 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartNumber'] ." MPQ is 0")));
            }

            if($detail['requestQuantity'] <= 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartNumber'] ." Quantity is 0")));
            }

            $details[] = array(
                "ebws_part_number" => $detail['ebwsPartNumber'],
                "supplier_part_number" => $detail['supplierPartNumber'],
                "main_process" => $detail['mainProcess'],
                "uom" => $detail['uom'],
                "qpa" => $detail['qpa'],
                "mpq" => $detail['mpq'],
                "request_quantity" => $detail['requestQuantity']
            );
        }

        if(empty($Ardetails)){
            die(json_encode(array("success" => false, "message" => "Data Not Found")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        $data = array(
            "kanban_number" => $header['kanbanNumber'],
            "kanban_type" => $header['kanbanType'],
            "kanban_date" => $header['kanbanDate'],
            "period" => $header['period'],
            "work_order_number" => $header['workOrderNumber'],
            "work_period" => date("Y-m", strtotime($header['period'])),
            "work_order_quantity" => $header['workOrderQuantity'],
            "product_part_number" => $header['productPartNumber'],
            "product_customer_code" => $header['productCustomerCode'],
            "requested_by" => $header['requestedBy'],
            "details" => $details
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function kanbanHAV(){
        $url = 'http://192.100.10.61:6622/api/wms/material-issue/kanban-hav';
        $token = $this->login();

        $request_no = $this->input->post('request_no');

        $this->db->select('a.request_no as kanbanNumber,
            a.type as kanbanType,
            a.request_date as kanbanDate,
            d.trans_date as period,
            a.workorder as workOrderNumber,
            a.qty_wo as workOrderQuantity,
            b.number as productPartNumber,
            b.number_customer as productCustomerCode,
            a.created_by as requestedBy
        ');
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('production_schedules d', 'a.item_fg_id = d.item_fg_id and a.workorder = d.workorder');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.request_no');
        $this->db->order_by('a.request_no', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('c.id as ebwsPartNumber,
            c.number as supplierPartNumber,
            b.main_process as mainProcess,
            c.uom,
            b.composition as qpa,
            a.mpq,
            a.qty_act as requestQuantity,
        ');
        $this->db->from('supply_sheets a');
        $this->db->join('bom b', 'a.item_fg_id = b.item_fg_id and a.item_rm_id = b.item_rm_id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('production_schedules d', 'a.item_fg_id = d.item_fg_id and a.workorder = d.workorder');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.item_rm_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $details = array();
        foreach ($Ardetails as $detail) {
            if($detail['mpq'] == 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartNumber'] ." MPQ is 0")));
            }

            if($detail['requestQuantity'] <= 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartNumber'] ." Quantity is 0")));
            }

            $details[] = array(
                "ebws_part_number" => $detail['ebwsPartNumber'],
                "supplier_part_number" => $detail['supplierPartNumber'],
                "main_process" => $detail['mainProcess'],
                "uom" => $detail['uom'],
                "qpa" => $detail['qpa'],
                "mpq" => $detail['mpq'],
                "request_quantity" => $detail['requestQuantity']
            );
        }

        if(empty($Ardetails)){
            die(json_encode(array("success" => false, "message" => "Data Not Found")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        $data = array(
            "kanban_number" => $header['kanbanNumber'],
            "kanban_type" => $header['kanbanType'],
            "kanban_date" => $header['kanbanDate'],
            "period" => $header['period'],
            "work_order_number" => $header['workOrderNumber'],
            "work_period" => date("Y-m", strtotime($header['period'])),
            "work_order_quantity" => $header['workOrderQuantity'],
            "product_part_number" => $header['productPartNumber'],
            "product_customer_code" => $header['productCustomerCode'],
            "requested_by" => $header['requestedBy'],
            "details" => $details
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function unplannedKanban(){
        $url = 'http://192.100.10.61:6622/api/wms/material-issue/unplanned-kanban';
        $token = $this->login();

        $request_no = $this->input->post('request_no');

        $this->db->select('a.request_no as kanbanNumber,
            a.request_date as kanbanDate,
            a.scrap_no as scrapNoteNumber,
            a.created_by as requestedBy
        ');
        $this->db->from('supply_requestions a');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.request_no');
        $this->db->order_by('a.request_no', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('c.id as ebwsPartNumber,
            c.number as supplierPartNumber,
            b.main_process as mainProcess,
            c.uom,
            a.qty as requestQuantity,
        ');
        $this->db->from('supply_requestions a');
        $this->db->join('bom b', 'a.item_fg_id = b.item_fg_id and a.item_rm_id = b.item_rm_id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.item_rm_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $details = array();
        foreach ($Ardetails as $detail) {
            if($detail['requestQuantity'] <= 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartNumber'] ." Quantity is 0")));
            }

            $details[] = array(
                "ebws_part_number" => $detail['ebwsPartNumber'],
                "supplier_part_number" => $detail['supplierPartNumber'],
                "main_process" => $detail['mainProcess'],
                "uom" => $detail['uom'],
                "request_quantity" => $detail['requestQuantity']
            );
        }

        if(empty($Ardetails)){
            die(json_encode(array("success" => false, "message" => "Data Not Found")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        $data = array(
            "kanban_number" => $header['kanbanNumber'],
            "kanban_date" => $header['kanbanDate'],
            "scrap_note_number" => $header['scrapNoteNumber'],
            "requested_by" => $header['requestedBy'],
            "details" => $details
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function partRequest(){
        $url = 'http://192.100.10.61:6622/api/wms/material-issue/part-request';
        $token = $this->login();

        $request_no = $this->input->post('request_no');

        $this->db->select("a.request_no as kanbanNumber,
            a.request_date as kanbanDate,
            a.created_by as requestedBy,
            a.request_date as period,
            'APPROVED' as status,
        ");
        $this->db->from('supply_materials a');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.request_no');
        $this->db->order_by('a.request_no', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('c.id as ebwsPartNumber,
            c.number as supplierPartCode,
            c.uom,
            a.qty as quantity,
            a.approved_to
        ');
        $this->db->from('supply_materials a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.item_rm_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $details = array();
        foreach ($Ardetails as $detail) {
            if($detail['quantity'] <= 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartCode'] ." Quantity is 0")));
            }

            if(!empty($detail['approved_to'])){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartCode'] ." still Checking in Approval")));
            }

            $details[] = array(
                "ebws_part_number" => $detail['ebwsPartNumber'],
                "supplier_part_code" => $detail['supplierPartCode'],
                "uom" => $detail['uom'],
                "quantity" => $detail['quantity']
            );
        }

        if(empty($Ardetails)){
            die(json_encode(array("success" => false, "message" => "Data Not Found")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        $data = array(
            "kanban_number" => $header['kanbanNumber'],
            "kanban_date" => $header['kanbanDate'],
            "requested_by" => $header['requestedBy'],
            "period" => $header['period'],
            "status" => $header['status'],
            "details" => $details
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function kanbanRequest(){
        $url = 'http://192.100.10.61:6622/api/wms/material-issue/kanban-request';
        $token = $this->login();

        $request_no = $this->input->post('request_no');

        $this->db->select("a.request_no as kanbanNumber,
            a.request_date as kanbanDate,
            a.created_by as requestedBy,
            a.workorder as workOrderNumber,
            d.trans_date as period,
            'APPROVED' as status
        ");
        $this->db->from('supply_sheets a');
        $this->db->join('production_schedules d', 'a.item_fg_id = d.item_fg_id and a.workorder = d.workorder');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.request_no');
        $this->db->order_by('a.request_no', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('c.id as ebwsPartNumber,
            c.number as supplierPartCode,
            c.uom,
            a.qty_act as quantity,
            a.approved_to
        ');
        $this->db->from('supply_sheets a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('production_schedules d', 'a.item_fg_id = d.item_fg_id and a.workorder = d.workorder');
        $this->db->where('a.request_no', $request_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.item_rm_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $details = array();
        foreach ($Ardetails as $detail) {
            if(!empty($detail['approved_to'])){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartCode'] ." still Checking in Approval")));
            }

            if($detail['quantity'] <= 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['supplierPartCode'] ." Quantity is 0")));
            }

            $details[] = array(
                "ebws_part_number" => $detail['ebwsPartNumber'],
                "supplier_part_code" => $detail['supplierPartCode'],
                "uom" => $detail['uom'],
                "quantity" => $detail['quantity']
            );
        }

        if(empty($Ardetails)){
            die(json_encode(array("success" => false, "message" => "Data Not Found")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        $data = array(
            "kanban_number" => $header['kanbanNumber'],
            "kanban_date" => $header['kanbanDate'],
            "requested_by" => $header['requestedBy'],
            "work_order_number" => $header['workOrderNumber'],
            "period" => $header['period'],
            "status" => $header['status'],
            "details" => $details
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function focOut(){
        $url = 'http://192.100.10.61:6622/api/wms/material-issue/foc-out';
        $token = $this->login();

        $receipt_no = $this->input->post('receipt_no');

        $this->db->select("a.number as ebwsDocumentNumber,
                a.transaction_date as transactionDate,
                (CASE WHEN b.id is null THEN c.id ELSE b.id END) as customerSupplierId,
                (CASE WHEN b.name is null THEN c.name ELSE b.name END) as customerSupplierName,
                a.plant,
                'RM' as category,
                'APPROVED' as status");
        $this->db->from('rgp_transactions a');
        $this->db->join('customers b', 'a.company_id = b.id', 'left');
        $this->db->join('suppliers c', 'a.company_id = c.id', 'left');
        $this->db->join('item_categories d', 'a.item_category_id = d.id', 'left');
        $this->db->join('departements e', 'a.departement_id = e.id', 'left');
        $this->db->where('a.process_type', 'FOC');
        $this->db->where('a.type', 'OUT');
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.item_category_id', 'C01');
        $this->db->where('a.number', $receipt_no);
        $this->db->group_by('a.number');
        $this->db->order_by('a.number', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('(CASE WHEN b.id is null THEN c.number ELSE b.id END) as partNumberEbws,
            (CASE WHEN b.number is null THEN c.number_customer ELSE b.number END) as partNumberSupplierCustomer,
            (CASE WHEN b.name is null THEN c.name ELSE b.name END) as partDescriptionSupplierCustomer,
            a.uom,
            a.qty as quantity,
            a.approved_to');
        $this->db->from('rgp_transactions a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->where('a.process_type', 'FOC');
        $this->db->where('a.type', 'OUT');
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.item_category_id', 'C01');
        $this->db->where('a.number', $receipt_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $details = array();
        foreach ($Ardetails as $detail) {
            if(!empty($detail['approved_to'])){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['partNumberSupplierCustomer'] ." still Checking in Approval")));
            }

            if($detail['quantity'] <= 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['partNumberSupplierCustomer'] ." Quantity is 0")));
            }

            $details[] = array(
                "part_number_ebws" => $detail['partNumberEbws'],
                "part_number_supplier_customer" => $detail['partNumberSupplierCustomer'],
                "part_description_supplier_customer" => $detail['partDescriptionSupplierCustomer'],
                "uom" => $detail['uom'],
                "quantity" => $detail['quantity']
            );
        }

        if(empty($Ardetails)){
            die(json_encode(array("success" => false, "message" => "cannot send this data, Please check category and approval")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        $data = array(
            "ebws_document_number" => $header['ebwsDocumentNumber'],
            "transaction_date" => $header['transactionDate'],
            "customer_supplier_id" => $header['customerSupplierId'],
            "plant" => $header['plant'],
            "category" => $header['category'],
            "status" => $header['status'],
            "details" => $details
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function nrgpOut(){
        $url = 'http://192.100.10.61:6622/api/wms/material-issue/rgp-out';
        $token = $this->login();

        $receipt_no = $this->input->post('receipt_no');

        $this->db->select("a.number as ebwsDocumentNumber,
                a.transaction_date as transactionDate,
                (CASE WHEN b.id is null THEN c.id ELSE b.id END) as customerSupplierId,
                (CASE WHEN b.name is null THEN c.name ELSE b.name END) as customerSupplierName,
                a.plant,
                'RM' as category,
                e.name as department,
                'APPROVED' as status");
        $this->db->from('rgp_transactions a');
        $this->db->join('customers b', 'a.company_id = b.id', 'left');
        $this->db->join('suppliers c', 'a.company_id = c.id', 'left');
        $this->db->join('item_categories d', 'a.item_category_id = d.id', 'left');
        $this->db->join('departements e', 'a.departement_id = e.id', 'left');
        $this->db->where_in('a.process_type', ['RGP']);
        $this->db->where('a.type', 'OUT');
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.item_category_id', 'C01');
        $this->db->where('a.number', $receipt_no);
        $this->db->group_by('a.number');
        $this->db->order_by('a.number', 'ASC');
        $header = $this->db->get()->row_array();

        $this->db->select('(CASE WHEN b.id is null THEN c.number ELSE b.id END) as partNumberEbws,
            (CASE WHEN b.number is null THEN c.number_customer ELSE b.number END) as partNumberSupplierCustomer,
            (CASE WHEN b.name is null THEN c.name ELSE b.name END) as partDescriptionSupplierCustomer,
            a.uom,
            a.qty as quantity,
            a.approved_to');
        $this->db->from('rgp_transactions a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->where_in('a.process_type', ['RGP']);
        $this->db->where('a.type', 'OUT');
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.item_category_id', 'C01');
        $this->db->where('a.number', $receipt_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.rgp_id', 'ASC');
        $Ardetails = $this->db->get()->result_array();

        $details = array();
        foreach ($Ardetails as $detail) {
            if(!empty($detail['approved_to'])){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['partNumberSupplierCustomer'] ." still Checking in Approval")));
            }

            if($detail['quantity'] <= 0){
                die(json_encode(array("success" => false, "message" => "Part No EBWS ". $detail['partNumberSupplierCustomer'] ." Quantity is 0")));
            }

            $details[] = array(
                "part_number_ebws" => $detail['partNumberEbws'],
                "part_number_supplier_customer" => $detail['partNumberSupplierCustomer'],
                "part_description_supplier_customer" => $detail['partDescriptionSupplierCustomer'],
                "uom" => $detail['uom'],
                "quantity" => $detail['quantity']
            );
        }

        if(empty($details)){
            die(json_encode(array("success" => false, "message" => "cannot send this data, Please check category and approval")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        $data = array(
            "ebws_document_number" => $header['ebwsDocumentNumber'],
            "transaction_date" => $header['transactionDate'],
            "return_date" => $header['transactionDate'],
            "customer_supplier_id" => $header['customerSupplierId'],
            "plant" => $header['plant'],
            "category" => $header['category'],
            "department" => $header['department'],
            "status" => $header['status'],
            "details" => $details
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }

    public function rejection(){
        $url = 'http://192.100.10.61:6622/api/wms/rejection';
        $token = $this->login();

        $return_no = $this->input->post('return_no');

        $this->db->select('a.*, 
            b.number as item_rm_number, 
            b.name as item_rm_name, 
            d.name as supplier_name,
            b.uom,
            c.mpq');
        $this->db->from('purchase_returns a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('supplier_items c', 'c.item_rm_id = b.id and a.supplier_id = c.supplier_id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->where('a.return_no', $return_no);
        $this->db->order_by('d.name', 'ASC');
        $records = $this->db->get()->result_array();

        if(empty($records)){
            die(json_encode(array("success" => false, "message" => "cannot send this data, Data still empty")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        foreach ($records as $record) {
            $data = array(
                "source" => $record['source'],
                "po_no" => $record['po_no'],
                "supplier_id" => $record['supplier_id'],
                "supplier_name" => $record['supplier_name'],
                "receipt_id" => $record['receipt_id'],
                "invoice_no" => $record['invoice_no'],
                "qty_receipt_invoice" => $record['qty'],
                "qty_return" => $record['qty'],
                "date" => $this->formatDate($record['created_date']),
                "return_date" => $this->formatDate($record['return_date']),
                "return_no" => $record['return_no']
            );

            $ch = curl_init($url);
            $payload = json_encode($data);

            // die($payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $response = curl_exec($ch);
        }

        die($response);
    }

    public function otw(){
        $url = 'http://192.100.10.61:6622/api/wms/otw';
        $token = $this->login();

        $id = $this->input->post('id');
        $invoice_no = $this->input->post('invoice_no');
        $supplier_id = $this->input->post('supplier_id');

        $this->db->select("a.*, 
            (CASE WHEN a.uom = '' THEN c.uom ELSE a.uom END) as uom, 
            b.name as supplier_name, 
            c.number as item_rm_number, 
            c.name as item_rm_name, 
            d.name as item_family_name, 
            f.qty_confirm, 
            (e.qty + (f.qty_confirm - a.delivery)) as qty_remain, 
            e.qty as qty_po, 
            b.currency, 
            (e.qty + (f.qty_confirm - a.delivery) - a.delivery) as balance");
        $this->db->from('purchase_confirm a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('purchase_orders e', 'a.supplier_id = e.supplier_id and a.item_rm_id = e.item_rm_id and a.po_no = e.po_no');
        $this->db->join("(SELECT supplier_id, item_rm_id, po_no, SUM(delivery) as qty_confirm FROM purchase_confirm GROUP BY supplier_id, item_rm_id, po_no) f", 'a.supplier_id = f.supplier_id and a.item_rm_id = f.item_rm_id and a.po_no = f.po_no');
        if($id != ""){
            $this->db->where('a.id', $id);
        }else{
            $this->db->where('a.invoice_no', $invoice_no);
            $this->db->where('a.supplier_id', $supplier_id);
        }
        
        $records = $this->db->get()->result_array();

        $response = json_encode(array("success" => false, "message" => "Data Not Found"));
        // $data = [];
        $htmlData = "Status of Send Data with Invoice No ".$invoice_no.", Supplier ID ".$supplier_id."  : <ul>";
        foreach ($records as $record) {
            if(empty($record)){
                die(json_encode(array("success" => false, "message" => "cannot send this data, Data still empty")));
            }

            $headers = [
                'Content-type: application/json',
                'X-WMS-Token: '. $token,
            ];

            $data = array(
                "supplier_id" => $record['supplier_id'],
                "supplier_name" => $record['supplier_name'],
                "po_no" => $record['po_no'],
                "invoice_no" => $record['invoice_no'],
                "invoice_date" => $record['invoice_date'],
                "ebws_part_number" => $record['item_rm_id'],
                "supplier_part_number" => $record['item_rm_number'],
                "plan_delivery_qty" => $record['delivery'],
                "eta" => $record['eta']
            );

            $ch = curl_init($url);
            $payload = json_encode($data);

            // die($payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $response = curl_exec($ch);
        
            $jsonDecode = json_decode($response, true);

            if(isset($jsonDecode['success'])){
                if($jsonDecode['success']){
                    $htmlData .= "<li>PO No ".$record['po_no']." and Part No ".$record['item_rm_id']." is <b style='color:green;'>".$jsonDecode['data']['status']."</b></li>";
                }else{
                    $htmlData .= "<li>PO No ".$record['po_no']." and Part No ".$record['item_rm_id']." is <b style='color:red;'>".$jsonDecode['data']['status']."</b></li>";
                }
            }else{
                $htmlData .= "<li>PO No ".$record['po_no']." and Part No ".$record['item_rm_id']." is <b style='color:red;'>ERROR</b></li>";
            }
        }

        // $htmlData .= "</ul>";
        die(json_encode(array("success" => true, "message" => $htmlData)));
        // die(json_encode($data));
        // die($response);
    }
    
    public function supplierItems(){
        $url = 'http://192.100.10.61:6622/api/wms/master/supplier-contract';
        $token = $this->login();

        $jsonData = file_get_contents("php://input");
        $post = json_decode($jsonData, true);
        $datas = $post['data'];
        
        $response = array();
        foreach ($datas as $data) {
            $id = $data['id'];
            
            $this->db->select('a.*, 
                c.number as item_rm_number, 
                c.name as item_rm_name, 
                c.uom,
                a.item_supplier, 
                b.number as supplier_number, 
                b.name as supplier_name,
                b.type, 
                b.supplier_po, 
                b.origin,
                d.name as item_family_name, 
                e.name as maker_name, 
                e.type as maker_type');
            $this->db->from('supplier_items a');
            $this->db->join('suppliers b', 'b.id = a.supplier_id');
            $this->db->join('item_rm c', 'c.id = a.item_rm_id');
            $this->db->join('item_familys d', "c.item_family_id = d.id and d.item_category_id IN ('C01')");
            $this->db->join('makers e', 'c.maker_id = e.id', 'left');
            $this->db->where('a.id', $id);
            $record = $this->db->get()->row_array();

            if(empty($record)){
                die(json_encode(array("success" => false, "message" => "cannot send this data, Data still empty")));
            }

            $headers = [
                'Content-type: application/json',
                'X-WMS-Token: '. $token,
            ];

            if($record['calculate'] == "YES"){
                $calculate_mpq = true;
            }else{
                $calculate_mpq = false;
            }

            $data = array(
                "supplier_id" => $record['supplier_id'],
                "supplier_name" => $record['supplier_name'],
                "ebws_part_number" => $record['item_rm_id'],
                "supplier_part_number" => $record['item_rm_number'],
                "supplier_origin" => $record['origin'],
                "product_family" => $record['item_family_name'],
                "maker" => $record['maker_name'],
                "maker_type" => $record['maker_type'],
                "uom" => $record['uom'],
                "currency" => $record['currency'],
                "price" => $record['price'],
                "mpq" => $record['mpq'],
                "moq" => $record['moq'],
                "mfg_lead_time_days" => $record['leadtime'],
                "transit_days" => $record['transit'],
                "share_order" => $record['share_order'],
                "safety" => $record['safety_stock'],
                "stock_percent" => 0,
                "calculate_mpq" => $calculate_mpq,
                "supplier_type" => $record['type'],
                "is_active" => true,
                "created_by" => $record['created_by'],
            );

            $ch = curl_init($url);
            $payload = json_encode($data);

            // die($payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $response = curl_exec($ch);
        }

        die($response);
    }

    public function supplierItemSingle(){
        $url = 'http://192.100.10.61:6622/api/wms/master/supplier-contract';
        $token = $this->login();

        $id = $this->input->post('id');

        $this->db->select('a.*, 
            c.number as item_rm_number, 
            c.name as item_rm_name, 
            c.uom,
            a.item_supplier, 
            b.number as supplier_number, 
            b.name as supplier_name,
            b.type, 
            b.supplier_po, 
            b.origin,
            d.name as item_family_name, 
            e.name as maker_name, 
            e.type as maker_type');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('item_rm c', 'c.id = a.item_rm_id');
        $this->db->join('item_familys d', "c.item_family_id = d.id and d.item_category_id IN ('C01')");
        $this->db->join('makers e', 'c.maker_id = e.id', 'left');
        $this->db->where('a.id', $id);
        $record = $this->db->get()->row_array();

        if(empty($record)){
            die(json_encode(array("success" => false, "message" => "cannot send this data, Data still empty")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        if($record['calculate'] == "YES"){
            $calculate_mpq = true;
        }else{
            $calculate_mpq = false;
        }

        $data = array(
            "supplier_id" => $record['supplier_id'],
            "supplier_name" => $record['supplier_name'],
            "ebws_part_number" => $record['item_rm_id'],
            "supplier_part_number" => $record['item_rm_number'],
            "supplier_origin" => $record['origin'],
            "product_family" => $record['item_family_name'],
            "maker" => $record['maker_name'],
            "maker_type" => $record['maker_type'],
            "uom" => $record['uom'],
            "currency" => $record['currency'],
            "price" => $record['price'],
            "mpq" => $record['mpq'],
            "moq" => $record['moq'],
            "mfg_lead_time_days" => $record['leadtime'],
            "transit_days" => $record['transit'],
            "share_order" => $record['share_order'],
            "safety" => $record['safety_stock'],
            "stock_percent" => 0,
            "calculate_mpq" => $calculate_mpq,
            "supplier_type" => $record['type'],
            "is_active" => true,
            "created_by" => $record['created_by'],
        );

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);

        die($response);
    }

    public function dolMaster(){
        $url = 'http://192.100.10.61:6622/api/wms/master/part-master';
        $token = $this->login();

        $item_rm_id = $this->input->post('item_rm_id');
        $supplier_id = $this->input->post('supplier_id');

        $this->db->select("a.*, b.number as supplier_part_number, b.name as part_description, b.uom, (CASE WHEN c.name IS NULL THEN d.name ELSE c.name END) as supplier_name");
        $this->db->from('dol_items a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id', 'left');
        $this->db->join('customers d', 'a.supplier_id = d.id', 'left');
        $this->db->where('a.item_rm_id', $item_rm_id);
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->group_by('b.number');
        $this->db->order_by('b.number', 'ASC');
        $header = $this->db->get()->row_array();

        if(empty($header)){
            die(json_encode(array("success" => false, "message" => "cannot send this data, Data still empty")));
        }

        $headers = [
            'Content-type: application/json',
            'X-WMS-Token: '. $token,
        ];

        if($header['is_dol'] == "0"){
            $is_dol = true;
        }else{
            $is_dol = false;
        }

        if($header['is_active'] == "0"){
            $is_active = true;
        }else{
            $is_active = false;
        }

        $data = array(
            "ebws_part_number" => $header['item_rm_id'],
            "supplier_part_number" => $header['supplier_part_number'],
            "part_description" => $header['part_description'],
            "uom" => $header['uom'],
            "mpq" => $header['mpq'],
            "category" => $header['category'],
            "is_dol" => $is_dol,
            "reorder_point" => $header['reorder_point'],
            "lead_time_days" => $header['lead_time_days'],
            "is_active" => $is_active,
            "created_by" => $header['created_by'],
            "supplier_id" => $header['supplier_id'],
            "supplier_name" => $header['supplier_name']
        );

        // die(json_encode($data));

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        die($response);
    }
}
