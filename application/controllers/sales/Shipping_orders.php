<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Shipping_orders extends CI_Controller
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
        $this->form_validation->set_rules('checksheet_label', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/shipping_orders');
        } else {
            redirect('error_access');
        }
    }

    public function getDeliveryOrders()
    {
        if ($this->input->get()) {
            $delivery_order_no = $this->input->get('delivery_order_no');

            $this->db->select('a.delivery_order_no, a.delivery_order_date, a.uom, a.sales_order_no, 
                a.trans_type, a.remarks, b.number_customer, b.number as item_fg_number, b.name as item_fg_name, 
                c.name as customer_name, COALESCE(a.qty_do, 0) as delivery, COALESCE(SUM(e.qty), 0) as shipping, a.created_by, a.created_date');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join("(SELECT z.* FROM (
                (SELECT a.checksheet_label, b.item_fg_id, a.workorder, a.checksheet_number FROM scan_item_receipts_fg a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number AND a.workorder = b.workorder WHERE b.item_fg_id IN (select item_fg_id from delivery_orders WHERE delivery_order_no = '$delivery_order_no'))
                UNION 
                (SELECT a.checksheet_label, b.item_fg_id, a.workorder, a.checksheet_number FROM scan_item_receipts_fg a JOIN os_fg b ON a.workorder = b.id WHERE b.item_fg_id IN (select item_fg_id from delivery_orders WHERE delivery_order_no = '$delivery_order_no'))
                UNION
                (SELECT a.checksheet_label, b.item_id as item_fg_id, a.workorder, a.checksheet_number FROM scan_item_receipts_fg a JOIN rgp_transactions b ON a.checksheet_number = b.rgp_id WHERE b.item_id IN (select item_fg_id from delivery_orders WHERE delivery_order_no = '$delivery_order_no'))
                UNION
                (SELECT a.checksheet_label, b.item_fg_id, a.workorder, a.checksheet_number FROM scan_item_receipts_fg a JOIN sales_returns b ON a.checksheet_number = b.return_id WHERE b.item_fg_id IN (select item_fg_id from delivery_orders WHERE delivery_order_no = '$delivery_order_no'))
                UNION 
                (SELECT b.label_divided as checksheet_label, b.item_fg_id, a.workorder, a.checksheet_number FROM barcode_divides_fg b JOIN scan_item_receipts_fg a ON (a.checksheet_number = b.reff or a.workorder = b.reff) WHERE b.item_fg_id IN (select item_fg_id from delivery_orders WHERE delivery_order_no = '$delivery_order_no'))
            ) z ) d", 'a.item_fg_id = d.item_fg_id');
            $this->db->join("(select * from shipping_orders where delivery_order_no = '$delivery_order_no') e", 'a.delivery_order_no = e.delivery_order_no AND d.checksheet_label = e.checksheet_label', 'left');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->where('a.status', 0);
            $this->db->group_by('b.number');

            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $checksheet_label = $this->input->post('checksheet_label');
            $product_label = $this->input->post('product_label');

            $this->db->select("d.*, a.id as item_fg_id");
            $this->db->from('item_fg a');
            $this->db->join('wip_receipts c', 'a.id = c.item_fg_id', 'left');
            $this->db->join('scan_item_receipts_fg d', '(c.checksheet_number = d.checksheet_number)');
            $this->db->where('d.checksheet_label', $checksheet_label);
            $this->db->where('a.number_customer', $product_label);
            $this->db->where('d.status', '0');
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            if (empty($records)) {
                $this->db->select("d.*, a.id as item_fg_id");
                $this->db->from('item_fg a');
                $this->db->join('rgp_transactions c', 'a.id = c.item_id', 'left');
                $this->db->join('scan_item_receipts_fg d', 'c.rgp_id = d.checksheet_number');
                $this->db->where('d.checksheet_label', $checksheet_label);
                $this->db->where('a.number_customer', $product_label);
                $this->db->where('d.status', '0');
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if (empty($records)) {
                    $this->db->select("d.*, a.id as item_fg_id");
                    $this->db->from('item_fg a');
                    $this->db->join('os_fg c', 'a.id = c.item_fg_id', 'left');
                    $this->db->join('scan_item_receipts_fg d', '(c.id = d.workorder)');
                    $this->db->where('d.checksheet_label', $checksheet_label);
                    $this->db->where('a.number_customer', $product_label);
                    $this->db->where('d.status', '0');
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();

                    if (empty($records)) {
                        $this->db->select("c.label_divided as checksheet_label, d.workorder, c.qty, c.reff as checksheet_number, a.id as item_fg_id");
                        $this->db->from('item_fg a');
                        $this->db->join('barcode_divides_fg c', 'a.id = c.item_fg_id', 'left');
                        $this->db->join('scan_item_receipts_fg d', '(c.reff = d.checksheet_number or c.reff = d.workorder)');
                        $this->db->where('c.label_divided', $checksheet_label);
                        $this->db->where('a.number_customer', $product_label);
                        $this->db->where('c.status', '0');
                        $this->db->group_by('c.label_divided');
                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();

                        if (empty($records)) {
                            $this->db->select("d.*, a.id as item_fg_id");
                            $this->db->from('item_fg a');
                            $this->db->join('sales_returns c', 'a.id = c.item_fg_id', 'left');
                            $this->db->join('scan_item_receipts_fg d', 'c.return_id = d.checksheet_number');
                            $this->db->where('d.checksheet_label', $checksheet_label);
                            $this->db->where('a.number_customer', $product_label);
                            $this->db->where('d.status', '0');
                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
                        }
                    }
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $delivery_order_no = $post['delivery_order_no'];
                $workorder = $post['workorder'];
                $item_fg_id = $post['item_fg_id'];
                $checksheet_number = $post['checksheet_number'];
                $shipping_orders = $this->crud->read("shipping_orders", [], ["delivery_order_no" => $post['delivery_order_no'], "checksheet_label" => $post['checksheet_label']]);
                $delivery_orders = $this->crud->read("delivery_orders", [], ["delivery_order_no" => $delivery_order_no, "item_fg_id" => $item_fg_id]);

                $shipping = $this->db->query("SELECT a.item_fg_id, a.qty_do, COALESCE(SUM(b.qty), 0) AS qty_shipping, (a.qty_do - COALESCE(SUM(b.qty), 0)) as balance 
                    FROM delivery_orders a 
                    LEFT JOIN shipping_orders b ON a.delivery_order_no = b.delivery_order_no AND a.item_fg_id = b.item_fg_id
                    WHERE a.delivery_order_no = '$delivery_order_no' and a.item_fg_id = '$item_fg_id'
                    GROUP BY a.item_fg_id");
                $record = $shipping->row();

                if (!empty($delivery_orders)) {
                    if (@$record->qty_do >= $post['qty'] || empty(@$record->item_fg_id)) {
                        // die(@$record->qty_do."-".@$record->shipping."-".$post['qty']);
                        if (!$shipping_orders) {
                            if ((@$record->balance - $post['qty']) >= 0 || empty(@$record->item_fg_id)) {
                                $this->db->trans_start();
                                $send = $this->crud->create('shipping_orders', $post, "logs");
                                $response = json_decode($send, true);
                                if ($response['theme'] == "success") {
                                    $this->db->update('scan_item_receipts_fg', ["status" => "1"], ["checksheet_label" => $post['checksheet_label']]);
                                    $this->db->update('barcode_divides_fg', ["status" => "1"], ["label_divided" => $post['checksheet_label']]);
                                }
                                $this->db->trans_complete();

                                if ($this->db->trans_status() === FALSE) {
                                    echo json_encode(array("title" => "Failed", "message" => "Data Saved Unsuccessfully ", "theme" => "error"));
                                } else {
                                    echo json_encode(array("title" => "Success", "message" => "Data Saved Successfully ", "theme" => "success"));
                                }
                            } else {
                                echo json_encode(array("title" => "Not Match", "message" => "Qty DO and Shipping already Done", "theme" => "error"));
                            }
                        } else {
                            echo json_encode(array("title" => "Available", "message" => "Data Shipping Orders has been Scanning", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Match", "message" => "Qty DO must bigger when Shipping Qty", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Not Match", "message" => "The Label Not Match in Delivery Order No", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
