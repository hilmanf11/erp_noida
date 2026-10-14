<?php
header('Content-Type: application/json; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';
require APPPATH . 'libraries/Format.php';

class Send extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->helper('jwt_helper');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('wms');
    }

    //HALAMAN UTAMA
    public function index()
    {
        $this->response(['message' => 'Cannot Process your request'], 400);
    }

    public function login_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');

        $user = $this->crud->read("users", [], ["username" => $username, "password" => $password, "status" => 0]);

        if (!empty($user)) {
            $key = "KUNCIKEY";
            $payload = [
                'username' => $username,
                'expired' => time() + (24 * 60 * 60) //24 Jam
            ];

            $token = JWT::encode($payload, $key);
            $this->response([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    "token" => $token,
                    "expiresIn" => "24 hours",
                    "header" => "X-WMS-Token"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 200);
        } else {
            $this->response([
                'success' => false,
                'message' => "Invalid username or password, or account is inactive.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function returnQas_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('purchase_order_receipt_qas');
            $this->db->where('receipt_no', $post['receipt_no']);
            $this->db->where('receipt_id', $post['receipt_id']);
            $this->db->where('item_rm_id', $post['item_rm_id']);
            $this->db->where('supplier_id', $post['supplier_id']);
            $records = $this->db->get()->result_array();

            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Return QAS already exists",
                    'data' => [
                        "receipt_no" => $post['receipt_no'],
                        "receipt_id" => $post['receipt_id'],
                        "item_rm_id" => $post['item_rm_id'],
                        "supplier_id" => $post['supplier_id'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            if (round($post['qty'] * $post['price'], 2) != $post['total']) {
                $this->response([
                    'success' => false,
                    'message' => "Total not correct '" . $post['total'] . "' multiply price and qty",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if (!in_array($post['status'], ["OK", "NG"])) {
                $this->response([
                    'success' => false,
                    'message' => "Status Not Exists '" . $post['status'] . "' Accepted only (OK or NG)",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            $send = $this->wms->create("purchase_order_receipt_qas", $post, $username);
            if ($send['theme'] == "success") {
                $this->response([
                    'success' => true,
                    'message' => "Return QAS saved successfully",
                    'data' => [
                        "receipt_no" => $post['receipt_no'],
                        "receipt_id" => $post['receipt_id'],
                        "item_rm_id" => $post['item_rm_id'],
                        "supplier_id" => $post['supplier_id'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            } else {
                $this->response([
                    'success' => false,
                    'message' => "Return QAS Failed to saved data",
                    'data' => [
                        "remarks" => $send['message']
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function returnQas_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        // if(empty($receipt_no)){
        //     $this->response([
        //         'success' => false,
        //         'message' => "Parameter Receipt Number is Required.",
        //         'data' => null,
        //         'timestamp' => date("Y-m-d H:i:s")
        //     ], 401);
        //     die();
        // }

        if ($decoded) {
            $this->db->select('receipt_no, receipt_date, supplier_id, invoice_no, invoice_date');
            $this->db->from('purchase_order_receipt_qas');
            $this->db->where('receipt_no', $get['receipt_no']);
            $this->db->group_by('receipt_no');
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->db->select('receipt_id, trans_date, po_no, item_rm_id, currency, uom, qty, price, total, status');
                $this->db->from('purchase_order_receipt_qas');
                $this->db->where('receipt_no', $get['receipt_no']);
                $details = $this->db->get()->result_array();

                $datas = $records;
                $datas['details'] = $details;

                $this->response([
                    'success' => true,
                    'message' => "Return QAS Found.",
                    'data' => $datas,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Return QAS '" . $receipt_no . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function returnRejections_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if (empty($post['po_no']) || empty($post['receipt_no']) || empty($post['receipt_id'])) {
            $this->response([
                'success' => false,
                'message' => "PO No, Receipt No or Receipt ID Cannot Empty",
                'data' => [
                    "label_no" => $post['label_no'],
                    "status" => "FAILED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('scan_item_rejections');
            $this->db->where('label_no', $post['label_no']);
            $records = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('scan_item_receipts');
            $this->db->where('label_no', $post['label_no']);
            $labels = $this->db->get()->result_array();

            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Rejection Label No '" . $post['label_no'] . "' already exists",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            if (empty($labels)) {
                $this->response([
                    'success' => false,
                    'message' => "Label No '" . $post['label_no'] . "' Not Found in Database",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "FAILED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            $send = $this->wms->create("scan_item_rejections", $post, $username);
            if ($send['theme'] == "success") {
                $this->response([
                    'success' => true,
                    'message' => "Scan Rejection Label No '" . $post['label_no'] . "' saved successfully",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            } else {
                $this->response([
                    'success' => false,
                    'message' => "Scan Rejection failed to saved data",
                    'data' => [
                        "remarks" => $send['message']
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function returnRejections_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('po_no, receipt_no, receipt_id, label_no, qty, price, round(qty * price, 2) as total');
            $this->db->from('scan_item_rejections');
            $this->db->where('label_no', $get['label_no']);
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->response([
                    'success' => true,
                    'message' => "Scan Rejection Label No '" . $get['label_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Scan Rejection Label No '" . $get['label_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemIn_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if (empty($post['po_no']) || empty($post['receipt_no']) || empty($post['receipt_id']) || empty($post['label_no'])) {
            $this->response([
                'success' => false,
                'message' => "PO No, Label No, Receipt No or Receipt ID Cannot Empty",
                'data' => [
                    "label_no" => $post['label_no'],
                    "status" => "FAILED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('scan_item_receipts');
            $this->db->where('label_no', $post['label_no']);
            $records = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('purchase_order_labels');
            $this->db->where('label_no', $post['label_no']);
            $records2 = $this->db->get()->result_array();

            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Gate Entry In Label No '" . $post['label_no'] . "' already exists",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            if (empty($records2)) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Gate Entry Label No '" . $post['label_no'] . "' not founded",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "NOT FOUND"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }

            $send = $this->wms->create("scan_item_receipts", $post, $username);
            if ($send['theme'] == "success") {
                $this->response([
                    'success' => true,
                    'message' => "Scan Gate Entry In Label No '" . $post['label_no'] . "' saved successfully",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            } else {
                $this->response([
                    'success' => false,
                    'message' => "Scan Gate Entry In failed to saved data",
                    'data' => [
                        "remarks" => $send['message']
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemIn_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('po_no, receipt_no, receipt_id, label_no, qty, price, label_from, warehouse');
            $this->db->from('scan_item_receipts');
            $this->db->where('label_no', $get['label_no']);
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->response([
                    'success' => true,
                    'message' => "Scan In Label No '" . $get['label_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Scan In Label No '" . $get['label_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemFocIn_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if (empty($post['receipt_no']) || empty($post['label_no']) || empty($post['item_id']) || empty($post['qty'])) {
            $this->response([
                'success' => false,
                'message' => "FOC No, Label No, Item ID and Qty Cannot Empty",
                'data' => [
                    "label_no" => $post['label_no'],
                    "status" => "FAILED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('scan_item_rgp');
            $this->db->where('label_no', $post['label_no']);
            $records = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('rgp_transaction_labels');
            $this->db->where('label_no', $post['label_no']);
            $records2 = $this->db->get()->result_array();

            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Scan FOC In Label No '" . $post['label_no'] . "' already exists",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            if (empty($records2)) {
                $this->response([
                    'success' => false,
                    'message' => "Scan FOC In Label No '" . $post['label_no'] . "' not founded",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "NOT FOUND"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }

            $data = [
                "rgp_number" => $post['receipt_no'],
                "rgp_id" => $post['receipt_id'],
                "label_no" => $post['label_no'],
                "item_id" => $post['item_id'],
                "qty" => $post['qty'],
                "price" => $post['price'],
                "type" => "IN",
            ];

            $data2 = [
                "receipt_no" => $post['receipt_no'],
                "receipt_id" => $post['receipt_id'],
                "label_no" => $post['label_no'],
                "qty" => $post['qty'],
                "price" => $post['price'],
                "label_from" => "FOC"
            ];

            $send = $this->wms->create("scan_item_rgp", $data, $username);
            if ($send['theme'] == "success") {
                $this->db->update('rgp_transaction_labels', ["status" => 1], ["label_no" => $post['label_no']]);
                $this->wms->create('scan_item_receipts', $data2, $username);

                $this->response([
                    'success' => true,
                    'message' => "Scan FOC In Label No '" . $post['label_no'] . "' saved successfully",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            } else {
                $this->response([
                    'success' => false,
                    'message' => "Scan FOC In failed to saved data",
                    'data' => [
                        "remarks" => $send['message']
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemFocIn_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('rgp_number, rgp_id, item_id, label_no, qty, type');
            $this->db->from('scan_item_rgp');
            $this->db->where('label_no', $get['label_no']);
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->response([
                    'success' => true,
                    'message' => "Scan In Label No '" . $get['label_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Scan In Label No '" . $get['label_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemRgpIn_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if (empty($post['receipt_no']) || empty($post['label_no']) || empty($post['item_id']) || empty($post['qty'])) {
            $this->response([
                'success' => false,
                'message' => "RGP No, Label No, Item ID and Qty Cannot Empty",
                'data' => [
                    "label_no" => $post['label_no'],
                    "status" => "FAILED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('scan_item_rgp');
            $this->db->where('label_no', $post['label_no']);
            $records = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('rgp_transaction_labels');
            $this->db->where('label_no', $post['label_no']);
            $records2 = $this->db->get()->result_array();

            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Scan RGP In Label No '" . $post['label_no'] . "' already exists",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            if (empty($records2)) {
                $this->response([
                    'success' => false,
                    'message' => "Scan RGP In Label No '" . $post['label_no'] . "' not founded",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "NOT FOUND"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }

            $data = [
                "rgp_number" => $post['receipt_no'],
                "rgp_id" => $post['receipt_id'],
                "label_no" => $post['label_no'],
                "item_id" => $post['item_id'],
                "qty" => $post['qty'],
                "price" => $post['price'],
                "type" => "IN",
            ];

            $data2 = [
                "receipt_no" => $post['receipt_no'],
                "receipt_id" => $post['receipt_id'],
                "label_no" => $post['label_no'],
                "qty" => $post['qty'],
                "price" => $post['price'],
                "label_from" => "RGP"
            ];

            $send = $this->wms->create("scan_item_rgp", $data, $username);
            if ($send['theme'] == "success") {
                $this->db->update('rgp_transaction_labels', ["status" => 1], ["label_no" => $post['label_no']]);
                $this->wms->create('scan_item_receipts', $data2, $username);

                $this->response([
                    'success' => true,
                    'message' => "Scan RGP In Label No '" . $post['label_no'] . "' saved successfully",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            } else {
                $this->response([
                    'success' => false,
                    'message' => "Scan RGP In failed to saved data",
                    'data' => [
                        "remarks" => $send['message']
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemRgpIn_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('rgp_number, rgp_id, item_id, label_no, qty, type');
            $this->db->from('scan_item_rgp');
            $this->db->where('label_no', $get['label_no']);
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->response([
                    'success' => true,
                    'message' => "Scan In Label No '" . $get['label_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Scan In Label No '" . $get['label_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function stoIn_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();
        $labels = $post['labels'];
        unset($post['labels']);

        $totalLabel = array_sum(array_column($labels, 'qty'));
        $labelNo = array_column($labels, 'label_no');
        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if (empty($post['item_rm_id']) || empty($post['trans_date']) || empty($post['document_no']) || empty($post['qty'])) {
            $this->response([
                'success' => false,
                'message' => "Part No EBWS, Trans Date, Document No or Qty Cannot Empty",
                'data' => [
                    "document_no" => $post['document_no'],
                    "status" => "FAILED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('os_rm');
            $this->db->where('document_no', $post['document_no']);
            $records = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('item_rm');
            $this->db->where('id', $post['item_rm_id']);
            $records2 = $this->db->get()->result_array();

            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Document No '" . $post['document_no'] . "' already exists",
                    'data' => [
                        "document_no" => $post['document_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            if (empty($records2)) {
                $this->response([
                    'success' => false,
                    'message' => "Part No EBWS '" . $post['item_rm_id'] . "' Not Found",
                    'data' => [
                        "item_rm_id" => $post['item_rm_id'],
                        "status" => "NOT FOUND"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }

            if (!in_array($post['type'], ["STO"])) {
                $this->response([
                    'success' => false,
                    'message' => "Type Not Exists '" . $post['type'] . "' Accepted only (STO)",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if (!in_array($post['category'], ["IN"])) {
                $this->response([
                    'success' => false,
                    'message' => "Category Not Exists '" . $post['category'] . "' Accepted only (IN)",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if (empty($labels)) {
                $this->response([
                    'success' => false,
                    'message' => "Labels from Document No '" . $post['document_no'] . "' still empty",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if ($post['qty'] != $totalLabel) {
                $this->response([
                    'success' => false,
                    'message' => "Total Qty Document No '" . $post['document_no'] . "' differences with labels",
                    'data' => [
                        "qty_header" => $post['qty'],
                        "qty_label" => $totalLabel,
                        "difference" => ($post['qty'] - $totalLabel)
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            $this->db->select('label_no');
            $this->db->from('scan_item_receipts');
            $this->db->where_in('label_no', $labelNo);
            $querys = $this->db->get()->result_array();
            $existing_labels = array_column($querys, 'label_no');

            if (!empty($existing_labels)) {
                $this->response([
                    'success' => false,
                    'message' => "Document No '" . $post['document_no'] . "' Label No already exists",
                    'data' => [
                        "label_no" => $existing_labels,
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            $this->db->trans_start();
            $os_rm = $this->wms->create("os_rm", array_merge($post, ["created_label" => date("Y-m-d H:i:s")]), $username);
            $receipt_id = @$os_rm['id'];
            foreach ($labels as $label) {
                $receipt_no = $label['document_no'];
                unset($label['document_no']);
                $this->wms->create("scan_item_receipts", array_merge($label, ["receipt_no" => $receipt_no, "receipt_id" => $receipt_id]), $username);
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $error = $this->db->error();

                $this->response([
                    'success' => false,
                    'message' => "STO In '" . $post['document_no'] . "' failed to saved data",
                    'data' => [
                        "result" => "Database Error. " . $error['message']
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            } else {
                $this->response([
                    'success' => true,
                    'message' => "STO In '" . $post['document_no'] . "' saved successfully with (" . count($labels) . ") labels",
                    'data' => [
                        "document_no" => $post['document_no'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function stoIn_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('id, document_no, trans_date, item_rm_id, location, qty, type, category');
            $this->db->from('os_rm');
            $this->db->where('document_no', $get['document_no']);
            $records = $this->db->get()->row_array();

            $this->db->select('label_no, qty, price, label_from, warehouse');
            $this->db->from('scan_item_receipts');
            $this->db->where('receipt_id', $records['id']);
            $labels = $this->db->get()->result_array();

            $records['labels'] = $labels;

            if (!empty($labels)) {
                $this->response([
                    'success' => true,
                    'message' => "STO (In) Document No '" . $get['document_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "STO (In) Document No '" . $get['document_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function stoOut_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();
        $labels = $post['labels'];
        unset($post['labels']);

        $totalLabel = array_sum(array_column($labels, 'qty'));
        $labelNo = array_column($labels, 'label_no');
        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if (empty($post['item_rm_id']) || empty($post['trans_date']) || empty($post['document_no']) || empty($post['qty'])) {
            $this->response([
                'success' => false,
                'message' => "Part No EBWS, Trans Date, Document No or Qty Cannot Empty",
                'data' => [
                    "document_no" => $post['document_no'],
                    "status" => "FAILED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('os_rm');
            $this->db->where('document_no', $post['document_no']);
            // $this->db->where('item_rm_id', $post['item_rm_id']);
            $records = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('item_rm');
            $this->db->where('id', $post['item_rm_id']);
            $records2 = $this->db->get()->result_array();

            if (empty($records2)) {
                $this->response([
                    'success' => false,
                    'message' => "Part No EBWS '" . $post['item_rm_id'] . "' Not Found",
                    'data' => [
                        "item_rm_id" => $post['item_rm_id'],
                        "status" => "NOT FOUND"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }

            if (!in_array($post['type'], ["STO"])) {
                $this->response([
                    'success' => false,
                    'message' => "Type Not Exists '" . $post['type'] . "' Accepted only (STO)",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if (!in_array($post['category'], ["OUT"])) {
                $this->response([
                    'success' => false,
                    'message' => "Category Not Exists '" . $post['category'] . "' Accepted only (OUT)",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if (empty($labels)) {
                $this->response([
                    'success' => false,
                    'message' => "Labels from Document No '" . $post['document_no'] . "' still empty",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if ($post['qty'] != $totalLabel) {
                $this->response([
                    'success' => false,
                    'message' => "Total Qty Document No '" . $post['document_no'] . "' differences with labels",
                    'data' => [
                        "qty_header" => $post['qty'],
                        "qty_label" => $totalLabel,
                        "difference" => ($post['qty'] - $totalLabel)
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            // $this->db->select('label_no');
            // $this->db->from('scan_item_receipts');
            // $this->db->where_in('label_no', $labelNo);
            // $sql = $this->db->get()->result_array();

            // $this->db->select('label_divided as label_no');
            // $this->db->from('barcode_divides');
            // $this->db->where_in('label_divided', $labelNo);
            // $sql2 = $this->db->get()->result_array();
            // $querys = array_merge($sql, $sql2);

            // $existing_labels = array_map('strtolower', array_column($querys, 'label_no'));
            // $labelNoLower = array_map('strtolower', $labelNo);
            // $missing_labels = array_diff($labelNoLower, $existing_labels);

            // if(!empty($missing_labels)){
            //     $this->response([
            //         'success' => false,
            //         'message' => "Document No '" . $post['document_no'] ."' Several Label Not Found",
            //         'data' => [
            //             "result" => $missing_labels
            //         ],
            //         'timestamp' => date("Y-m-d H:i:s")
            //     ], 404);
            //     die();
            // }

            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Document No '" . $post['document_no'] . "' Document No already exists",
                    'data' => [
                        "document_no" => $post['document_no'],
                        "item_rm_id" => $post['item_rm_id'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            $this->db->select('label_no');
            $this->db->from('os_rm_labels');
            $this->db->where_in('label_no', $labelNo);
            $querys = $this->db->get()->result_array();
            $existing_labels = array_column($querys, 'label_no');

            if (!empty($existing_labels)) {
                $this->response([
                    'success' => false,
                    'message' => "Document No '" . $post['document_no'] . "' Label No already exists",
                    'data' => [
                        "label_no" => $existing_labels,
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            $this->db->trans_start();
            $os_rm = $this->wms->create("os_rm", array_merge($post, ["created_label" => date("Y-m-d H:i:s")]), $username);
            $receipt_id = @$os_rm['id'];
            foreach ($labels as $label) {
                $receipt_no = $label['document_no'];
                unset($label['document_no']);
                $this->wms->create("os_rm_labels", array_merge($label, ["receipt_no" => $receipt_no, "receipt_id" => $receipt_id]), $username);
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $error = $this->db->error();

                $this->response([
                    'success' => false,
                    'message' => "STO Out '" . $post['document_no'] . "' failed to saved data",
                    'data' => [
                        "result" => "Database Error. " . $error['message']
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            } else {
                $this->response([
                    'success' => true,
                    'message' => "STO Out '" . $post['document_no'] . "' saved successfully with (" . count($labels) . ") labels",
                    'data' => [
                        "document_no" => $post['document_no'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function stoOut_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('id, document_no, trans_date, item_rm_id, location, qty, type, category');
            $this->db->from('os_rm');
            $this->db->where('document_no', $get['document_no']);
            $records = $this->db->get()->row_array();

            $this->db->select('label_no, qty, price, label_from, warehouse');
            $this->db->from('os_rm_labels');
            $this->db->where('receipt_id', $records['id']);
            $labels = $this->db->get()->result_array();

            $records['labels'] = $labels;

            if (!empty($labels)) {
                $this->response([
                    'success' => true,
                    'message' => "STO (Out) Document No '" . $get['document_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "STO (Out) Document No '" . $get['document_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function readEndingStock($item_rm_id)
    {
        $filter_from = date("Y-m-d");

        $records = $this->crud->query("SELECT
            a.id,
            ((COALESCE(SUM(c.qty_sto_in),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(SUM(cc.qty_sto_out),0) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto_in FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.category = 'IN' and a.trans_date <= '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto_out FROM os_rm a JOIN os_rm_labels b ON a.id  = b.receipt_id WHERE a.category = 'OUT' and a.trans_date <= '$filter_from' GROUP BY item_rm_id) cc ON a.id = cc.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date <= '$filter_from' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date <= '$filter_from' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$filter_from' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$filter_from' GROUP BY a.item_id) j ON a.id = j.item_id
        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$filter_from' GROUP BY a.item_id) k ON a.id = k.item_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
            WHERE a.return_date <= '$filter_from'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        WHERE a.id = '$item_rm_id'  
        GROUP BY a.id");

        return empty(@$records[0]->end_stock) ? 0 : @$records[0]->end_stock;
    }

    public function scanItemOut_post()
    {
        $this->dummy = $this->load->database('dummy', TRUE);

        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();
        $request_no = $post['kanban_no'];

        $now = time();

        //Validasi Apakah token masih aktif
        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        //Validasi kanban, item, dan label tidak boleh kosong
        if (empty($post['kanban_no']) || empty($post['label_no']) || empty($post['item_rm_id']) || empty($post['qty'])) {
            $this->response([
                'success' => false,
                'message' => "Kanban No, Label No, Qty or Part No EBWS Cannot Empty",
                'data' => [
                    "label_no" => $post['label_no'],
                    "status" => "REJECTED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('issued_material_details');
            $this->db->where('label_no', $post['label_no']);
            $records = $this->db->get()->result_array();

            $scan_rgp = $this->crud->read("scan_item_rgp", [], ["label_no" => $post['label_no'], "type" => "OUT"]);
            if (!empty($scan_rgp)) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Out Label No '" . $post['label_no'] . "' already exists in RGP Out",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            $scan_item_rejections = $this->crud->read("scan_item_rejections", [], ["label_no" => $post['label_no']]);
            if (!empty($scan_item_rejections)) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Out Label No '" . $post['label_no'] . "' already exists in Scan Rejection",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            //Validasi apakah label sudah di scan atau belum
            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Out Label No '" . $post['label_no'] . "' already exists",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            $supply_sheets = $this->crud->read("supply_sheets", [], ["request_no" => $request_no, "item_rm_id" => $post['item_rm_id']]);
            $supply_materials = $this->crud->read("supply_materials", [], ["request_no" => $request_no, "item_rm_id" => $post['item_rm_id']]);
            $supply_requestions = $this->crud->read("supply_requestions", [], ["request_no" => $request_no, "item_rm_id" => $post['item_rm_id']]);

            $this->db->select('SUM(qty) as total_qty');
            $this->db->from('issued_material_details');
            $this->db->where('request_no', $request_no);
            $this->db->where('item_rm_id', $post['item_rm_id']);
            $issued = $this->db->get()->row_array();

            //Validasi kanban no terdaftar di supply_sheets atau tidak
            if ($supply_sheets) {
                $qty = $supply_sheets->qty_act;
                $item_fg_id = @$supply_sheets->item_fg_id;
                $type = $supply_sheets->type;
            } elseif ($supply_materials) {
                $qty = $supply_materials->qty;
                $item_fg_id = @$supply_materials->item_fg_id;
                $type = "SMP";
            } elseif ($supply_requestions) {
                $qty = $supply_requestions->qty;
                $item_fg_id = @$supply_requestions->item_fg_id;
                $type = "REQ";
            } else {
                $this->response([
                    'success' => false,
                    'message' => "Scan Out Kanban No '" . $request_no . "' This Kanban No has not been registered in Kanban Sheet",
                    'data' => [
                        "kanban_no" => $request_no,
                        "item_rm_id" => $post['item_rm_id'],
                        "status" => "NOT FOUND"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }

            //Validasi qty label tidak boleh melebihi qty kanban
            if (!empty($issued) && (($issued['total_qty'] + $post['qty']) > $qty)) {
                $this->response([
                    'success' => false,
                    'message' => "Label quantity has exceeded kanban quantity",
                    'data' => [
                        "kanban_no" => $request_no,
                        "item_rm_id" => $post['item_rm_id'],
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);

                die();
            }

            if (($this->readEndingStock($post['item_rm_id']) - $post['qty']) < 0) {
                $this->response([
                    'success' => false,
                    'message' => "Stock this item '" . $post['item_rm_id'] . "' in the warehouse is finished",
                    'data' => [
                        "item_id" => $post['item_rm_id'],
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if ($type == "PP") {
                // die(json_encode("-"));
                $customer_item = $this->crud->read("customer_items", [], ["item_fg_id" => $item_fg_id]);
                $customer = $this->crud->read("customers", [], ["id" => @$customer_item->customer_id]);
                $item_rm = $this->crud->read("item_rm", [], ["id" => @$post['item_rm_id']]);

                $this->dummy->select('serialno');
                $this->dummy->from('serial_barcode_incoming');
                $this->dummy->where('serialno', strtoupper($post['label_no']));
                $serialno = $this->dummy->get()->result_array();

                if (count($serialno) == 0) {
                    $this->dummy->trans_start();
                    @$this->dummy->insert('serial_barcode_outgoing', [
                        "serialno" => strtoupper($post['label_no']),
                        "outscan" => date("Y-m-d H:i:s"),
                        "outform" => "OU - " . $type . " " . @$customer->number,
                        "outdocno" => $request_no,
                        "outqty" => round($post['qty']),
                        "itemid" => @$item_rm->number,
                        "itemname" => @$item_rm->name,
                        "outemployee" => $username,
                        "outstatus" => "t",
                    ]);

                    @$this->dummy->insert('serial_barcode_incoming', [
                        "serialno" => strtoupper($post['label_no']),
                        "serialdate" => date("Y-m-d"),
                        "scantime" => date("Y-m-d H:i:s"),
                        "serialpo" => "PO ASTEK",
                        "serialform" => "OU - " . $type . " " . @$customer->number,
                        "serialdocno" => $request_no,
                        "serialqtyreceipt" => round($post['qty']),
                        "itemid" => @$item_rm->number,
                        "itemname" => @$item_rm->name,
                        "statusscanincoming" => "t",
                        "statusreceive" => "t",
                        "por_urut_poc" => 1,
                    ]);
                    $this->dummy->trans_complete();

                    if ($this->dummy->trans_status() === FALSE) {
                        $error = $this->dummy->error();
                        $this->response([
                            'success' => false,
                            'message' => "Failed to create data in Banshu System",
                            'data' => [
                                "code" => $error['code'],
                                "remarks" => $error['message'],
                                "status" => "REJECTED"
                            ],
                            'timestamp' => date("Y-m-d H:i:s")
                        ], 401);

                        die();
                    }
                }
            }

            unset($post['kanban_no']);
            $send = $this->wms->create("issued_material_details", array_merge($post, ["request_no" => $request_no]), $username);
            if ($send['theme'] == "success") {
                $this->response([
                    'success' => true,
                    'message' => "Scan Out Label No '" . $post['label_no'] . "' saved successfully",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            } else {
                $this->response([
                    'success' => false,
                    'message' => "Scan Out failed to saved data",
                    'data' => [
                        "remarks" => $send['message'],
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemOut_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('request_no as kanban_no, label_no, item_rm_id, qty, price');
            $this->db->from('issued_material_details');
            $this->db->where('label_no', $get['label_no']);
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->response([
                    'success' => true,
                    'message' => "Scan Out Label No '" . $get['label_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Scan Out Label No '" . $get['label_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemFocOut_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();
        $rgp_number = $post['rgp_number'];
        $item_id = $post['item_id'];

        $now = time();

        //Validasi Apakah token masih aktif
        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        //Validasi Rgp No, item, dan label tidak boleh kosong
        if (empty($post['rgp_number']) || empty($post['label_no']) || empty($post['item_id']) || empty($post['qty'])) {
            $this->response([
                'success' => false,
                'message' => "Foc/Rgp No, Label No, Qty or Part No EBWS Cannot Empty",
                'data' => [
                    "label_no" => $post['label_no'],
                    "status" => "REJECTED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('scan_item_rgp');
            $this->db->where('label_no', $post['label_no']);
            $this->db->where('type', 'OUT');
            $records = $this->db->get()->result_array();

            $issued_materials = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);
            $scan_item_rejections = $this->crud->read("scan_item_rejections", [], ["label_no" => $post['label_no']]);
            $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $rgp_number, "item_id" => $item_id]);

            if (!empty($scan_item_rejections)) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Out Label No '" . $post['label_no'] . "' already exists in Scan Rejection",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            //Validasi apakah label sudah di scan di shop floor atau belum
            if (!empty($issued_materials)) {
                $this->response([
                    'success' => false,
                    'message' => "Label no '" . $post['label_no'] . "' already scan out in shop floor",
                    'data' => [
                        "kanban_no" => $issued_materials->request_no,
                        "item_rm_id" => $item_id,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);

                die();
            }

            //Validasi apakah label sudah di scan atau belum
            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Foc Out Label No '" . $post['label_no'] . "' already exists",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            //Validasi apakah rgp number dan item number sama
            if (empty($rgp_transactions) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Item '" . $item_id . "' in barcode is not same with required Item",
                    'data' => [
                        "rgp_number" => $rgp_number,
                        "item_id" => $item_id,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if ($rgp_transactions->process_type == "NRGP") {
                $this->response([
                    'success' => false,
                    'message' => "This Process is NRGP, only allow RGP and FOC",
                    'data' => [
                        "rgp_number" => $rgp_number,
                        "item_id" => $item_id,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if (($this->readEndingStock($item_id) - $post['qty']) < 0) {
                $this->response([
                    'success' => false,
                    'message' => "Stock this item '" . $item_id . "' in the warehouse is finished",
                    'data' => [
                        "item_id" => $item_id,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            $cek_qty = $this->crud->query("
                select a.number, a.item_id, COALESCE(sum(a.qty),0) as total_qty, COALESCE(b.total_scan,0) as total_scan, COALESCE(sum(a.qty),0)-COALESCE(b.total_scan,0) as balance
                from rgp_transactions a 
                left join (select rgp_number, item_id, COALESCE(sum(qty),0) as total_scan from scan_item_rgp where type = 'OUT' group by rgp_number, item_id) b on a.number=b.rgp_number and a.item_id=b.item_id
                where a.type='OUT' and a.number = '" . $post['rgp_number'] . "' and a.item_id='" . $post['item_id'] . "' group by a.number, a.item_id
            ");

            if ((@$cek_qty[0]->total_scan + $post['qty']) > @$cek_qty[0]->total_qty) {
                $this->response([
                    'success' => false,
                    'message' => "Qty label in barcode more than with required qty Item",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "total_label_qty" => (@$cek_qty[0]->total_scan + $post['qty']),
                        "required_qty" => intval(@$cek_qty[0]->total_qty),
                        "balance" => (@$cek_qty[0]->total_qty - (@$cek_qty[0]->total_scan + $post['qty'])),
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            } else {
                $send = $this->wms->create("scan_item_rgp", array_merge($post, ["type" => "OUT"]), $username);
                if ($send['theme'] == "success") {
                    $this->db->update('scan_item_receipts', ["status" => 1], ["label_no" => $post['label_no']]);
                    $this->db->update('barcode_divides', ["status" => 1], ["label_divided" => $post['label_no']]);

                    $this->response([
                        'success' => true,
                        'message' => "Scan FOC Out Label No '" . $post['label_no'] . "' saved successfully",
                        'data' => [
                            "label_no" => $post['label_no'],
                            "status" => "ACCEPTED"
                        ],
                        'timestamp' => date("Y-m-d H:i:s")
                    ], 200);
                } else {
                    $this->response([
                        'success' => false,
                        'message' => "Scan FOC Out failed to saved data",
                        'data' => [
                            "remarks" => $send['message'],
                            "status" => "REJECTED"
                        ],
                        'timestamp' => date("Y-m-d H:i:s")
                    ], 401);
                }
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemFocOut_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('rgp_number, rgp_id, label_no, item_id, qty, price');
            $this->db->from('scan_item_rgp');
            $this->db->where('label_no', $get['label_no']);
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->response([
                    'success' => true,
                    'message' => "Scan FOC Out Label No '" . $get['label_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Scan FOC Out Label No '" . $get['label_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemRgpOut_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();
        $rgp_number = $post['rgp_number'];
        $item_id = $post['item_id'];

        $now = time();

        //Validasi Apakah token masih aktif
        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        //Validasi Rgp No, item, dan label tidak boleh kosong
        if (empty($post['rgp_number']) || empty($post['label_no']) || empty($post['item_id']) || empty($post['qty'])) {
            $this->response([
                'success' => false,
                'message' => "Foc/Rgp No, Label No, Qty or Part No EBWS Cannot Empty",
                'data' => [
                    "label_no" => $post['label_no'],
                    "status" => "REJECTED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('scan_item_rgp');
            $this->db->where('label_no', $post['label_no']);
            $this->db->where('type', 'OUT');
            $records = $this->db->get()->result_array();

            $issued_materials = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);
            $scan_item_rejections = $this->crud->read("scan_item_rejections", [], ["label_no" => $post['label_no']]);
            $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $rgp_number, "item_id" => $item_id]);

            if (!empty($scan_item_rejections)) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Out Label No '" . $post['label_no'] . "' already exists in Scan Rejection",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            //Validasi apakah label sudah di scan di shop floor atau belum
            if (!empty($issued_materials)) {
                $this->response([
                    'success' => false,
                    'message' => "Label no '" . $post['label_no'] . "' already scan out in shop floor",
                    'data' => [
                        "kanban_no" => $issued_materials->request_no,
                        "item_rm_id" => $item_id,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);

                die();
            }

            //Validasi apakah label sudah di scan atau belum
            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Scan RGP Out Label No '" . $post['label_no'] . "' already exists",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            //Validasi apakah rgp number dan item number sama
            if (empty($rgp_transactions) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Item '" . $item_id . "' in barcode is not same with required Item",
                    'data' => [
                        "rgp_number" => $rgp_number,
                        "item_id" => $item_id,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if ($rgp_transactions->process_type == "NRGP") {
                $this->response([
                    'success' => false,
                    'message' => "This Process is NRGP, only allow RGP and FOC",
                    'data' => [
                        "rgp_number" => $rgp_number,
                        "item_id" => $item_id,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            if (($this->readEndingStock($item_id) - $post['qty']) < 0) {
                $this->response([
                    'success' => false,
                    'message' => "Stock this item '" . $item_id . "' in the warehouse is finished",
                    'data' => [
                        "item_id" => $item_id,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
                die();
            }

            $cek_qty = $this->crud->query("
                select a.number, a.item_id, COALESCE(sum(a.qty),0) as total_qty, COALESCE(b.total_scan,0) as total_scan, COALESCE(sum(a.qty),0)-COALESCE(b.total_scan,0) as balance
                from rgp_transactions a 
                left join (select rgp_number, item_id, COALESCE(sum(qty),0) as total_scan from scan_item_rgp where type = 'OUT' group by rgp_number, item_id) b on a.number=b.rgp_number and a.item_id=b.item_id
                where a.type='OUT' and a.number = '" . $post['rgp_number'] . "' and a.item_id='" . $post['item_id'] . "' group by a.number, a.item_id
            ");

            if ((@$cek_qty[0]->total_scan + $post['qty']) > @$cek_qty[0]->total_qty) {
                $this->response([
                    'success' => false,
                    'message' => "Qty label in barcode more than with required qty Item",
                    'data' => [
                        "label_no" => $label_no,
                        "status" => "REJECTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            } else {
                $send = $this->wms->create("scan_item_rgp", array_merge($post, ["type" => "OUT"]), $username);
                if ($send['theme'] == "success") {
                    $this->db->update('scan_item_receipts', ["status" => 1], ["label_no" => $post['label_no']]);
                    $this->db->update('barcode_divides', ["status" => 1], ["label_divided" => $post['label_no']]);

                    $this->response([
                        'success' => true,
                        'message' => "Scan RGP Out Label No '" . $post['label_no'] . "' saved successfully",
                        'data' => [
                            "label_no" => $post['label_no'],
                            "status" => "ACCEPTED"
                        ],
                        'timestamp' => date("Y-m-d H:i:s")
                    ], 200);
                } else {
                    $this->response([
                        'success' => false,
                        'message' => "Scan RGP Out failed to saved data",
                        'data' => [
                            "remarks" => $send['message'],
                            "status" => "REJECTED"
                        ],
                        'timestamp' => date("Y-m-d H:i:s")
                    ], 401);
                }
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemRgpOut_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('rgp_number, rgp_id, label_no, item_id, qty, price');
            $this->db->from('scan_item_rgp');
            $this->db->where('label_no', $get['label_no']);
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->response([
                    'success' => true,
                    'message' => "Scan RGP Out Label No '" . $get['label_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Scan RGP Out Label No '" . $get['label_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemReturnMaterial_post()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $post = $this->post();
        $receipt_no = $post['return_no'];
        $receipt_id = $post['return_id'];
        unset($post['return_no']);
        unset($post['return_id']);

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if (empty($receipt_no) || empty($receipt_id) || empty($post['label_no'])) {
            $this->response([
                'success' => false,
                'message' => "Label No, Return No or Return ID Cannot Empty",
                'data' => [
                    "label_no" => $post['label_no'],
                    "status" => "FAILED"
                ],
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('*');
            $this->db->from('scan_item_receipts');
            $this->db->where('label_no', $post['label_no']);
            $records = $this->db->get()->result_array();

            $this->db->select('*');
            $this->db->from('return_material_labels');
            $this->db->where('label_no', $post['label_no']);
            $records2 = $this->db->get()->result_array();

            if (count($records) > 0) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Return Material Label No '" . $post['label_no'] . "' already exists",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "DUPLICATE"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 409);
                die();
            }

            if (empty($records2)) {
                $this->response([
                    'success' => false,
                    'message' => "Scan Return Material Label No '" . $post['label_no'] . "' not founded",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "NOT FOUND"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }

            $send = $this->wms->create("scan_item_receipts", array_merge($post, array("po_no" => "RETURN MATERIAL", "receipt_no" => $receipt_no, "receipt_id" => $receipt_id)), $username);
            if ($send['theme'] == "success") {
                $this->response([
                    'success' => true,
                    'message' => "Scan Return Material Label No '" . $post['label_no'] . "' saved successfully",
                    'data' => [
                        "label_no" => $post['label_no'],
                        "status" => "ACCEPTED"
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
            } else {
                $this->response([
                    'success' => false,
                    'message' => "Scan Return Material failed to saved data",
                    'data' => [
                        "remarks" => $send['message']
                    ],
                    'timestamp' => date("Y-m-d H:i:s")
                ], 401);
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    public function scanItemReturnMaterial_get()
    {
        $token = $this->input->get_request_header('X-WMS-Token');
        $decoded = JWT::decode($token, "KUNCIKEY");
        $username = $decoded['username'];
        $get = $this->get();

        $now = time();

        if ($decoded['expired'] < $now) {
            $this->response([
                'success' => false,
                'message' => "X-WMS-Token Expired.",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
            die();
        }

        if ($decoded) {
            $this->db->select('receipt_no as return_no, receipt_id as return_id, label_no, qty, price, label_from, warehouse');
            $this->db->from('scan_item_receipts');
            $this->db->where('label_no', $get['label_no']);
            $records = $this->db->get()->row_array();

            if (!empty($records)) {
                $this->response([
                    'success' => true,
                    'message' => "Scan Return Material Label No '" . $get['label_no'] . "' Found.",
                    'data' => $records,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 200);
                die();
            } else {
                $this->response([
                    'success' => true,
                    'message' => "Scan Return Material Label No '" . $get['label_no'] . "' Not Found.",
                    'data' => null,
                    'timestamp' => date("Y-m-d H:i:s")
                ], 404);
                die();
            }
        } else {
            $this->response([
                'success' => false,
                'message' => "Token Invalid",
                'data' => null,
                'timestamp' => date("Y-m-d H:i:s")
            ], 401);
        }
    }

    // public function returnMaterial_post() {
    //     $token = $this->input->get_request_header('X-WMS-Token');
    //     $decoded = JWT::decode($token, "KUNCIKEY");
    //     $username = $decoded['username'];
    //     $post = $this->post();

    //     $details = $post['details'];
    //     unset($post['details']);

    //     $returnId = array_column($details, 'return_id');
    //     $now = time();

    //     if($decoded['expired'] < $now){
    //         $this->response([
    //             'success' => false,
    //             'message' => "X-WMS-Token Expired.",
    //             'data' => null,
    //             'timestamp' => date("Y-m-d H:i:s")
    //         ], 401);
    //         die();
    //     }

    //     if(empty($post['return_no']) || empty($post['workorder'])){
    //         $this->response([
    //             'success' => false,
    //             'message' => "Return No, and Workorder still empty",
    //             'data' => [
    //                 "return_no" => $post['return_no'],
    //                 "status" => "FAILED"
    //             ],
    //             'timestamp' => date("Y-m-d H:i:s")
    //         ], 401);
    //         die();
    //     }

    //     if ($decoded) {
    //         $this->db->select('*');
    //         $this->db->from('return_materials');
    //         $this->db->where('return_no', $post['return_no']);
    //         $records = $this->db->get()->result_array();

    //         $error_detail_1 = "";
    //         $item_rm_id = "";
    //         foreach ($details as $detail) {
    //             $this->db->select('*');
    //             $this->db->from('item_rm');
    //             $this->db->where('id', $detail['item_rm_id']);
    //             $records2 = $this->db->get()->result_array();

    //             if(empty($records2)){
    //                 $error_detail_1 = "ITEM";
    //             }

    //             $this->db->select('c.id, c.number, c.name, a.mpq');
    //             $this->db->from('supply_sheets a');
    //             $this->db->join('item_rm c', 'a.item_rm_id = c.id');
    //             $this->db->where('a.workorder', $post['workorder']);
    //             $this->db->like('c.id', $detail['item_rm_id']);
    //             $this->db->group_by('a.id');
    //             $this->db->order_by('a.id', 'ASC');
    //             $records3 = $this->db->get()->row_array();

    //             if(empty($records3)){
    //                 $error_detail_1 = "KANBAN";
    //             }

    //             $item_rm_id = $detail['item_rm_id'];
    //         }

    //         if(count($records) > 0){
    //             $this->response([
    //                 'success' => false,
    //                 'message' => "Material Return No '".$post['return_no']."' already exists",
    //                 'data' => [
    //                     "return_no" => $post['return_no'],
    //                     "status" => "DUPLICATE"
    //                 ],
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 409);
    //             die();
    //         }

    //         if($error_detail_1 == "ITEM"){
    //             $this->response([
    //                 'success' => false,
    //                 'message' => "Part No EBWS '".$item_rm_id."' Not Found",
    //                 'data' => [
    //                     "item_rm_id" => $post['item_rm_id'],
    //                     "status" => "NOT FOUND"
    //                 ],
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 404);
    //             die();
    //         }

    //         if($error_detail_1 == "KANBAN"){
    //             $this->response([
    //                 'success' => false,
    //                 'message' => "Workorder '".$post['workorder']."' and Part No EBWS '".$item_rm_id."' Not Found from Kanban Sheet",
    //                 'data' => [
    //                     "item_rm_id" => $item_rm_id,
    //                     "workorder" => $post['workorder'],
    //                     "status" => "NOT FOUND"
    //                 ],
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 404);
    //             die();
    //         }

    //         if(empty($returnId)){
    //             $this->response([
    //                 'success' => false,
    //                 'message' => "Details in Material Return No '" . $post['return_no'] ."' still empty",
    //                 'data' => null,
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 401);
    //             die();
    //         }

    //         $this->db->select('return_id');
    //         $this->db->from('return_material_labels');
    //         $this->db->where_in('return_id', $returnId);
    //         $querys = $this->db->get()->result_array();
    //         $existing_labels = array_column($querys, 'return_id');

    //         if(!empty($existing_labels)){
    //             $this->response([
    //                 'success' => false,
    //                 'message' => "Material Return ID '".$post['return_id']."' already exists",
    //                 'data' => [
    //                     "label_no" => $existing_labels,
    //                     "status" => "DUPLICATE"
    //                 ],
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 409);
    //             die();
    //         }

    //         $this->db->trans_start();
    //         foreach ($details as $detail) {
    //             $dataFinal = array_merge($detail, [
    //                 "p_month" => date("m"),
    //                 "p_year" => date("Y"),
    //                 "return_no" => $post['return_no'],
    //                 "return_name" => $post['return_name'],
    //                 "return_date" => $post['return_date'],
    //                 "workorder" => $post['workorder'],
    //                 "remarks" => $post['remarks'],
    //             ]);

    //             $this->wms->create("return_materials", $dataFinal, $username);
    //         }
    //         $this->db->trans_complete();

    //         if ($this->db->trans_status() === FALSE) {
    //             $error = $this->db->error();

    //             $this->response([
    //                 'success' => false,
    //                 'message' => "Material Return No '".$post['return_no']."' failed to saved data",
    //                 'data' => [
    //                     "result" => "Database Error. ". $error['message']
    //                 ],
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 401);
    //         } else {
    //             $this->response([
    //                 'success' => true,
    //                 'message' => "Material Return No '".$post['return_no']."' saved successfully with (".count($details).") Part No EBWS",
    //                 'data' => [
    //                     "return_no" => $post['return_no'],
    //                     "status" => "ACCEPTED"
    //                 ],
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 200);
    //         }
    //     } else {
    //         $this->response([
    //             'success' => false,
    //             'message' => "Token Invalid",
    //             'data' => null,
    //             'timestamp' => date("Y-m-d H:i:s")
    //         ], 401);
    //     }
    // }

    // public function returnMaterial_get() {
    //     $token = $this->input->get_request_header('X-WMS-Token');
    //     $decoded = JWT::decode($token, "KUNCIKEY");
    //     $username = $decoded['username'];
    //     $get = $this->get();

    //     $now = time();

    //     if($decoded['expired'] < $now){
    //         $this->response([
    //             'success' => false,
    //             'message' => "X-WMS-Token Expired.",
    //             'data' => null,
    //             'timestamp' => date("Y-m-d H:i:s")
    //         ], 401);
    //         die();
    //     }

    //     if ($decoded) {
    //         $this->db->select('return_no, return_date, return_name, workorder, remarks');
    //         $this->db->from('return_materials');
    //         $this->db->where('return_no', $get['return_no']);
    //         $records = $this->db->get()->row_array();

    //         $this->db->select('return_id, item_rm_id, mpq, qty');
    //         $this->db->from('return_materials');
    //         $this->db->where('return_no', $get['return_no']);
    //         $details = $this->db->get()->result_array();

    //         $records['details'] = $details;

    //         if(!empty($details)){
    //             $this->response([
    //                 'success' => true,
    //                 'message' => "Material Return No '".$get['return_no']."' Found.",
    //                 'data' => $records,
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 200);
    //             die();
    //         }else{
    //             $this->response([
    //                 'success' => true,
    //                 'message' => "Material Return No '".$get['return_no']."' Not Found.",
    //                 'data' => null,
    //                 'timestamp' => date("Y-m-d H:i:s")
    //             ], 404);
    //             die();
    //         }
    //     } else {
    //         $this->response([
    //             'success' => false,
    //             'message' => "Token Invalid",
    //             'data' => null,
    //             'timestamp' => date("Y-m-d H:i:s")
    //         ], 401);
    //     }
    // }
}
