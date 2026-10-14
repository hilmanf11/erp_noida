<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Approvals extends CI_Controller
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

    public function approveAll()
    {
        $period = $this->input->post('period');
        $p_year = substr($period, 0, 4);
        $p_month = substr($period, 4);
        $supplier_id = $this->input->post('supplier_id');
        $revision = $this->input->post('revision');
        $tablename = $this->input->post('tablename');
        $message = $this->input->post('message');
        $approval = $this->crud->read('approvals', [], ["table_name" => $tablename]);
        $data = $this->crud->read($tablename, [], ["supplier_id" => $supplier_id, "revision" => $revision, "p_year" => $p_year, "p_month" => $p_month]);

        if ($data->approved == 1) {
            $users_id = @$approval->user_approval_2;
            $approved = 2;
        } elseif ($data->approved == 2) {
            $users_id = @$approval->user_approval_3;
            $approved = 3;
        } elseif ($data->approved == 3) {
            $users_id = @$approval->user_approval_4;
            $approved = 4;
        } elseif ($data->approved == 4) {
            $users_id = @$approval->user_approval_5;
            $approved = 5;
        } else {
            $users_id = "";
            $approved = 0;
        }

        $values = array(
            "approved_by" => $this->session->username,
            "approved_date" => date('Y-m-d H:i:s'),
            "approved_to" => $users_id,
            "approved" => $approved,
        );

        $send = $this->db->update($tablename, $values, ["supplier_id" => $supplier_id, "revision" => $revision, "p_year" => $p_year, "p_month" => $p_month]);

        if ($send) {
            echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
        } else {
            echo log_message('error', 'There is an error in your system or data');
        }
    }

    public function approve()
    {
        if ($this->session->username != "") {
            $id = $this->input->post('id');
            $tablename = $this->input->post('tablename');
            $message = $this->input->post('message');

            if ($tablename == "bom") {
                $data = $this->crud->read($tablename, [], ["item_fg_id" => $id]);
            } else {
                $data = $this->crud->read($tablename, [], ["id" => $id]);
            }

            if ($tablename == "purchase_order_others") {
                if ($data->type == "CL") {
                    $approval = $this->crud->read('approvals', [], ["table_name" => "onetime_po_others"]);
                } else {
                    $approval = $this->crud->read('approvals', [], ["table_name" => $tablename]);
                }
            } else {
                $approval = $this->crud->read('approvals', [], ["table_name" => $tablename]);
            }

            $remarks1 = "";
            $remarks2 = "";
            $remarks3 = "";
            $remarks4 = "";

            if ($data->approved == 1) {
                $users_id = @$approval->user_approval_2;
                $approved = 2;
                $remarks1 = $message;
            } elseif ($data->approved == 2) {
                $users_id = @$approval->user_approval_3;
                $approved = 3;
                $remarks2 = $message;
            } elseif ($data->approved == 3) {
                $users_id = @$approval->user_approval_4;
                $approved = 4;
                $remarks3 = $message;
            } elseif ($data->approved == 4) {
                $users_id = @$approval->user_approval_5;
                $approved = 5;
                $remarks4 = $message;
            } else {
                $users_id = "";
                $approved = 0;
            }

            if ($tablename == "supplier_items") {
                $values = array(
                    "approved_by" => $this->session->username,
                    "approved_date" => date('Y-m-d H:i:s'),
                    "approved_to" => $users_id,
                    "approved" => $approved,
                    "remarks1" => $remarks1,
                    "remarks2" => $remarks2,
                    "remarks3" => $remarks3,
                    "remarks4" => $remarks4
                );
            } else {
                $values = array(
                    "approved_by" => $this->session->username,
                    "approved_date" => date('Y-m-d H:i:s'),
                    "approved_to" => $users_id,
                    "approved" => $approved,
                );
            }

            if ($tablename == "bom") {
                $boms = $this->crud->reads("bom", [], ["item_fg_id" => $id]);
                foreach ($boms as $bom) {
                    if ($users_id == "" && $bom->remove == "1") {
                        $send = $this->db->delete($tablename, ["item_fg_id" => $id, "remove" => 1]);
                    } else {
                        $send = $this->db->update($tablename, $values, ["item_fg_id" => $id]);
                    }
                }
            } else {
                $send = $this->db->update($tablename, $values, ["id" => $id]);
            }

            if ($send) {
                echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
            } else {
                echo log_message('error', 'There is an error in your system or data');
            }
        } else {
            echo json_encode(array("title" => "Expired", "message" => "Session has been Expired, please re-login", "theme" => "error"));
        }
    }

    public function disapproveAll()
    {
        $period = $this->input->post('period');
        $p_year = substr($period, 0, 4);
        $p_month = substr($period, 4);
        $revision = $this->input->post('revision');
        $supplier_id = $this->input->post('supplier_id');
        $tablename = $this->input->post('tablename');
        $message = $this->input->post('message');

        $send = $this->db->delete($tablename, ["supplier_id" => @$supplier_id, "p_year" => $p_year, "p_month" => $p_month, "revision" => $revision]);

        if ($send) {
            echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
        } else {
            echo log_message('error', 'There is an error in your system or data');
        }
    }

    public function disapprove()
    {
        if ($this->session->username != "") {
            $id = $this->input->post('id');
            $message = $this->input->post('message');
            $tablename = $this->input->post('tablename');

            if ($tablename == "bom") {
                $reads = $this->crud->reads($tablename, [], ["item_fg_id" => $id]);
                foreach ($reads as $read) {
                    $data = json_decode($read->approved_data, false);

                    if (empty($data)) {
                        $this->db->delete("bom_log", ["item_fg_id" => $read->item_fg_id, "item_rm_id" => $read->item_rm_id, "revision" => $read->revision]);
                        $this->db->delete($tablename, ["id" => $read->id]);
                    } else {
                        $send = $this->db->update($tablename, $data, ["id" => $read->id]);
                        if ($tablename == "bom") {
                            $this->db->delete("bom_log", ["item_fg_id" => $read->item_fg_id, "item_rm_id" => $read->item_rm_id, "revision" => $read->revision]);
                        }
                    }
                }
            } else {
                $read = $this->crud->read($tablename, [], ["id" => $id]);
                $data = json_decode($read->approved_data, false);

                if ($read->updated_by != "") {
                    $users_id_to = $read->updated_by;
                } else {
                    $users_id_to = $read->created_by;
                }

                $notificationData = [
                    "created_by" => $this->session->username,
                    "created_date" => date("Y-m-d H:i:s"),
                    "users_id_from" => $this->session->username,
                    "users_id_to" => $users_id_to,
                    "table_id" => $id,
                    "table_name" => $tablename,
                    "reason" => $message,
                    "name" => "Disapproved",
                    "description" => 'The data in <b>' . strtoupper(str_replace("_", " ", $tablename)) . '</b> has been disapproved',
                    "logs" => json_encode($read),
                ];

                /* Default */
                if (empty($data)) {
                    $this->db->insert('notifications', $notificationData);

                    if ($tablename == "supplier_items") {
                        $po_other = $this->crud->reads("purchase_order_others", [], ["supplier_id" => @$read->supplier_id, "item_rm_id" => @$read->item_rm_id]);
                        $po = $this->crud->reads("purchase_orders", [], ["supplier_id" => @$read->supplier_id, "item_rm_id" => @$read->item_rm_id]);

                        if (empty($po) && empty($po_other)) {
                            $this->db->delete($tablename, ["id" => $id]);
                            $this->db->delete("supplier_item_histories", ["supplier_id" => @$read->supplier_id, "item_rm_id" => @$read->item_rm_id, "price" => @$read->price]);
                        } else {
                            die(json_encode(array("title" => "Already In PO", "message" => "Data Cannot Disaapprove", "theme" => "error")));
                        }
                    } else {
                        $this->db->delete($tablename, ["id" => $id]);
                    }
                } else {
                    $send = $this->db->update($tablename, $data, ["id" => $id]);
                    $this->db->insert('notifications', $notificationData);

                    if ($tablename == "supplier_items") {
                        $this->db->delete("supplier_item_histories", ["supplier_id" => @$read->supplier_id, "item_rm_id" => @$read->item_rm_id, "price" => @$read->price]);
                    }
                }
            }

            echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
        } else {
            echo json_encode(array("title" => "Expired", "message" => "Session has been Expired, please re-login", "theme" => "error"));
        }
    }

    public function approvalCount()
    {
        $username = $this->session->username;
        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_order_others = $this->crud->reads('purchase_order_others', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_order_services = $this->crud->reads('purchase_order_services', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $bom = $this->crud->reads('bom', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $customer_items = $this->crud->reads('customer_items', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $supplier_items = $this->crud->reads('supplier_items', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $supplier_item_prices = $this->crud->reads('supplier_item_prices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $item_rm = $this->crud->reads('item_rm', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $item_fg = $this->crud->reads('item_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_fg = $this->crud->reads('stock_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $stock_wip = $this->crud->reads('stock_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_so = $this->crud->reads('stock_so', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_so = $this->crud->reads('os_so', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_mpp = $this->crud->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $forecasts = $this->crud->reads('forecasts', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        // $generate_mrp_finals = $this->crud->reads('generate_mrp_finals', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $generate_mrp_finals = $this->crud->query("SELECT * FROM generate_mrp_finals WHERE purchase_order > 0 and approved_to = '$username' GROUP BY approved_to, approved_by");

        $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_request_others = $this->crud->reads('purchase_request_others', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_request_services = $this->crud->reads('purchase_request_services', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supply_materials = $this->crud->reads('supply_materials', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supply_sheets = $this->crud->reads('supply_sheets', [], ["approved_to" => $this->session->username, "type" => "REQ"], "", "", "", ["approved_to", "approved_by"]);
        $item_ng = $this->crud->reads('item_ng', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $scraps = $this->crud->reads('scraps', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $item_mpq = $this->crud->reads('item_mpq', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sto_rm = $this->crud->reads('sto_rm', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sto_fg = $this->crud->reads('sto_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_orders = $this->crud->reads('sales_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sample_requests = $this->crud->reads('sample_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $rgp_transaction_in = $this->crud->reads('rgp_transactions', [], ["approved_to" => $this->session->username, "type" => "IN"], "", "", "", ["approved_to", "approved_by"]);
        $rgp_transaction_out = $this->crud->reads('rgp_transactions', [], ["approved_to" => $this->session->username, "type" => "OUT"], "", "", "", ["approved_to", "approved_by"]);
        $scrap_booking = $this->crud->reads('scrap_booking', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        $totalRows = (count($users) +
            count($purchase_orders) +
            count($sales_orders) +
            count($purchase_order_others) +
            count($purchase_order_services) +
            // count($bom) + 
            // count($customer_items) + 
            // count($supplier_items) + 
            // count($supplier_item_prices) + 
            // count($item_rm) + 
            // count($item_fg) +
            // count($stock_fg) +
            count($stock_wip) +
            count($sample_requests) +
            count($sto_rm) +
            count($sto_fg) +
            count($purchase_requests) +
            count($purchase_request_others) +
            count($purchase_request_services) +
            count($supply_materials) +
            count($supply_sheets) +
            count($item_ng) +
            count($scraps) +
            count($item_mpq) +
            count($generate_mrp_finals) +
            count($rgp_transaction_in) +
            count($rgp_transaction_out) +
            count($scrap_booking) +
            count($forecasts)
        );

        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function approvalList()
    {
        $username = $this->session->username;
        //Users
        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_order_others = $this->crud->reads('purchase_order_others', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_order_services = $this->crud->reads('purchase_order_services', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $boms = $this->crud->reads('bom', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $customer_items = $this->crud->reads('customer_items', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $supplier_items = $this->crud->reads('supplier_items', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $supplier_item_prices = $this->crud->reads('supplier_item_prices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $item_rm = $this->crud->reads('item_rm', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $item_fg = $this->crud->reads('item_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_fg = $this->crud->reads('stock_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $stock_wip = $this->crud->reads('stock_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_so = $this->crud->reads('stock_so', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_so = $this->crud->reads('os_so', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_mpp = $this->crud->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        $generate_mrp_finals = $this->crud->query("SELECT * FROM generate_mrp_finals WHERE purchase_order > 0 and approved_to = '$username' GROUP BY approved_to, approved_by");

        $forecasts = $this->crud->reads('forecasts', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_request_others = $this->crud->reads('purchase_request_others', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_request_services = $this->crud->reads('purchase_request_services', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supply_materials = $this->crud->reads('supply_materials', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supply_sheets = $this->crud->reads('supply_sheets', [], ["approved_to" => $this->session->username, "type" => "REQ"], "", "", "", ["approved_to", "approved_by"]);
        $item_ng = $this->crud->reads('item_ng', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $scraps = $this->crud->reads('scraps', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $item_mpq = $this->crud->reads('item_mpq', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sto_rms = $this->crud->reads('sto_rm', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sto_fgs = $this->crud->reads('sto_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_orders = $this->crud->reads('sales_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sample_requests = $this->crud->reads('sample_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $rgp_transaction_in = $this->crud->reads('rgp_transactions', [], ["approved_to" => $this->session->username, "type" => "IN"], "", "", "", ["approved_to", "approved_by"]);
        $rgp_transaction_out = $this->crud->reads('rgp_transactions', [], ["approved_to" => $this->session->username, "type" => "OUT"], "", "", "", ["approved_to", "approved_by"]);
        $scrap_booking = $this->crud->reads('scrap_booking', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        foreach ($users as $user) {
            $this->approvalMessage($user->approved_by, $user->approved_to, $user->created_by, "users");
        }

        foreach ($purchase_orders as $po) {
            $this->approvalMessage($po->approved_by, $po->approved_to, $po->created_by, "purchase_orders");
        }

        foreach ($purchase_order_others as $poo) {
            $this->approvalMessage($poo->approved_by, $poo->approved_to, $poo->created_by, "purchase_order_others");
        }

        foreach ($purchase_order_services as $pos) {
            $this->approvalMessage($pos->approved_by, $pos->approved_to, $pos->created_by, "purchase_order_services");
        }

        // foreach ($customer_items as $customer_item) {
        //     $this->approvalMessage($customer_item->approved_by, $customer_item->approved_to, $customer_item->created_by, "customer_items");
        // }

        // foreach ($supplier_items as $supplier_item) {
        //     $this->approvalMessage($supplier_item->approved_by, $supplier_item->approved_to, $supplier_item->created_by, "supplier_items");
        // }

        // foreach ($supplier_item_prices as $supplier_item_price) {
        //     $this->approvalMessage($supplier_item_price->approved_by, $supplier_item_price->approved_to, $supplier_item_price->created_by, "supplier_item_prices");
        // }

        // foreach ($boms as $bom) {
        //     $this->approvalMessage($bom->approved_by, $bom->approved_to, $bom->created_by, "bom");
        // }

        // foreach ($item_rm as $rm) {
        //     $this->approvalMessage($rm->approved_by, $rm->approved_to, $rm->created_by, "item_rm");
        // }

        // foreach ($item_fg as $fg) {
        //     $this->approvalMessage($fg->approved_by, $fg->approved_to, $fg->created_by, "item_fg");
        // }

        foreach ($forecasts as $forecast) {
            $this->approvalMessage($forecast->approved_by, $forecast->approved_to, $forecast->created_by, "forecasts");
        }

        foreach ($generate_mrp_finals as $generate_mrp_final) {
            $this->approvalMessage($generate_mrp_final->approved_by, $generate_mrp_final->approved_to, $generate_mrp_final->created_by, "generate_mrp_finals");
        }

        foreach ($stock_wip as $wip) {
            $this->approvalMessage($wip->approved_by, $wip->approved_to, $wip->created_by, "stock_wip");
        }

        foreach ($purchase_requests as $purchase_request) {
            $this->approvalMessage($purchase_request->approved_by, $purchase_request->approved_to, $purchase_request->created_by, "purchase_requests");
        }

        foreach ($purchase_request_others as $purchase_request_other) {
            $this->approvalMessage($purchase_request_other->approved_by, $purchase_request_other->approved_to, $purchase_request_other->created_by, "purchase_request_others");
        }

        foreach ($purchase_request_services as $purchase_request_service) {
            $this->approvalMessage($purchase_request_service->approved_by, $purchase_request_service->approved_to, $purchase_request_service->created_by, "purchase_request_services");
        }

        foreach ($supply_materials as $supply_material) {
            $this->approvalMessage($supply_material->approved_by, $supply_material->approved_to, $supply_material->created_by, "supply_materials");
        }

        foreach ($supply_sheets as $supply_sheet) {
            $this->approvalMessage($supply_sheet->approved_by, $supply_sheet->approved_to, $supply_sheet->created_by, "supply_sheets");
        }

        foreach ($item_ng as $ng) {
            $this->approvalMessage($ng->approved_by, $ng->approved_to, $ng->created_by, "item_ng");
        }

        foreach ($scraps as $scrap) {
            $this->approvalMessage($scrap->approved_by, $scrap->approved_to, $scrap->created_by, "scraps");
        }

        foreach ($item_mpq as $mpq) {
            $this->approvalMessage($mpq->approved_by, $mpq->approved_to, $mpq->created_by, "item_mpq");
        }

        foreach ($sto_rms as $sto_rm) {
            $this->approvalMessage($sto_rm->approved_by, $sto_rm->approved_to, $sto_rm->created_by, "sto_rm");
        }

        foreach ($sto_fgs as $sto_fg) {
            $this->approvalMessage($sto_fg->approved_by, $sto_fg->approved_to, $sto_fg->created_by, "sto_fg");
        }

        foreach ($sales_orders as $sales_order) {
            $this->approvalMessage($sales_order->approved_by, $sales_order->approved_to, $sales_order->created_by, "sales_orders");
        }

        foreach ($sample_requests as $sample_request) {
            $this->approvalMessage($sample_request->approved_by, $sample_request->approved_to, $sample_request->created_by, "sample_requests");
        }

        foreach ($rgp_transaction_in as $rgp_in) {
            $this->approvalMessage($rgp_in->approved_by, $rgp_in->approved_to, $rgp_in->created_by, "rgp_transaction_in");
        }

        foreach ($rgp_transaction_out as $rgp_out) {
            $this->approvalMessage($rgp_out->approved_by, $rgp_out->approved_to, $rgp_out->created_by, "rgp_transaction_out");
        }

        foreach ($scrap_booking as $scrap_b) {
            $this->approvalMessage($scrap_b->approved_by, $scrap_b->approved_to, $scrap_b->created_by, "scrap_booking");
        }
    }

    public function approvalMessage($approved_by, $approved_to, $created_by, $table)
    {
        $user = $this->crud->read('users', [], ["username" => $approved_by]);

        if (empty($user->avatar)) {
            $avatar = base_url('assets/image/users/default.png');
        } else {
            $avatar = $user->avatar;
        }

        if ($table == "supplier_items") {
            $tablename = "supplier_contracts";
        } elseif ($table == "item_rm") {
            $tablename = "master_items";
        } elseif ($table == "supply_materials") {
            $tablename = "sample_requisition";
        } elseif ($table == "supply_sheets") {
            $tablename = "kanban_requests";
        } else {
            $tablename = $table;
        }

        $title = "APPROVAL " . strtoupper(str_replace('_', ' ', $tablename));

        $link = "approvalDetail('$table', '$approved_to', '$approved_by', '$title')";
        echo '  <li class="list-isi">
                    <a onclick="' . $link . '">
                        <table style="width: 100%;">
                            <tr>
                                <td width="40">
                                    <div class="icon-container">
                                        <img src="' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td style="padding-left: 10px;">
                                    <b>' . @$user->name . '</b><br>
                                    <small>Sent a request to approve data <b>' . strtoupper(str_replace("_", " ", $tablename)) . '</b></small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
    }

    public function users($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "users";
            $this->load->view('template/header', $data);
            $this->load->view('approval/users');
        }
    }

    // public function stock_fg($approved_to, $approved_by){
    //     if (empty($this->session->username)) {
    //         redirect('error_session');
    //     } else {
    //         $data['approved_to'] = base64_decode($approved_to);
    //         $data['approved_by'] = base64_decode($approved_by);
    //         $data['table'] = "stock_fg";
    //         $this->load->view('template/header', $data);
    //         $this->load->view('approval/stock_fg');
    //     }
    // }

    public function stock_wip($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "stock_wip";
            $this->load->view('template/header', $data);
            $this->load->view('approval/stock_wip');
        }
    }

    // public function stock_so($approved_to, $approved_by){
    //     if (empty($this->session->username)) {
    //         redirect('error_session');
    //     } else {
    //         $data['approved_to'] = base64_decode($approved_to);
    //         $data['approved_by'] = base64_decode($approved_by);
    //         $data['table'] = "stock_so";
    //         $this->load->view('template/header', $data);
    //         $this->load->view('approval/stock_so');
    //     }
    // }

    // public function os_so($approved_to, $approved_by){
    //     if (empty($this->session->username)) {
    //         redirect('error_session');
    //     } else {
    //         $data['approved_to'] = base64_decode($approved_to);
    //         $data['approved_by'] = base64_decode($approved_by);
    //         $data['table'] = "os_so";
    //         $this->load->view('template/header', $data);
    //         $this->load->view('approval/os_so');
    //     }
    // }

    public function sales_orders($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "sales_orders";
            $this->load->view('template/header', $data);
            $this->load->view('approval/sales_orders');
        }
    }

    public function forecasts($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "forecasts";
            $this->load->view('template/header', $data);
            $this->load->view('approval/forecasts');
        }
    }

    public function generate_mrp_finals($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $m = date("m");
            $y = date("Y");
            $mrp = $this->crud->query("SELECT max(revision) as revision FROM generate_mrp_finals WHERE p_month = '$m' and p_year='$y'");

            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "generate_mrp_finals";
            $data['revision'] = empty($mrp) ? 0 : @$mrp[0]->revision;
            $this->load->view('template/header', $data);
            $this->load->view('approval/generate_mrp_finals');
        }
    }

    public function purchase_requests($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_requests";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_requests');
        }
    }

    public function purchase_request_services($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_request_services";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_request_services');
        }
    }

    public function purchase_request_others($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_request_others";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_request_others');
        }
    }

    public function purchase_orders($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_orders";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_orders');
        }
    }

    public function purchase_order_others($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_order_others";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_order_others');
        }
    }

    public function purchase_order_services($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_order_services";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_order_services');
        }
    }

    public function bom($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "bom";
            $this->load->view('template/header', $data);
            $this->load->view('approval/bom');
        }
    }

    public function customer_items($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "customer_items";
            $this->load->view('template/header', $data);
            $this->load->view('approval/customer_items');
        }
    }

    public function supplier_items($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supplier_items";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supplier_items');
        }
    }

    public function item_rm($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "item_rm";
            $this->load->view('template/header', $data);
            $this->load->view('approval/item_rm');
        }
    }

    public function item_fg($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "item_fg";
            $this->load->view('template/header', $data);
            $this->load->view('approval/item_fg');
        }
    }

    public function supplier_item_prices($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supplier_item_prices";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supplier_item_prices');
        }
    }

    public function supply_materials($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supply_materials";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supply_materials');
        }
    }

    public function supply_sheets($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supply_sheets";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supply_sheets');
        }
    }

    public function item_ng($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "item_ng";
            $this->load->view('template/header', $data);
            $this->load->view('approval/item_ng');
        }
    }

    public function scraps($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "scraps";
            $this->load->view('template/header', $data);
            $this->load->view('approval/scraps');
        }
    }

    public function item_mpq($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "item_mpq";
            $this->load->view('template/header', $data);
            $this->load->view('approval/item_mpq');
        }
    }

    public function sto_rm($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "sto_rm";
            $this->load->view('template/header', $data);
            $this->load->view('approval/sto_rm');
        }
    }

    public function sto_fg($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "sto_fg";
            $this->load->view('template/header', $data);
            $this->load->view('approval/sto_fg');
        }
    }

    public function sample_requests($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "sample_requests";
            $this->load->view('template/header', $data);
            $this->load->view('approval/sample_requests');
        }
    }

    public function rgp_transaction_in($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "rgp_transactions";
            $this->load->view('template/header', $data);
            $this->load->view('approval/rgp_transaction_in');
        }
    }

    public function rgp_transaction_out($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "rgp_transactions";
            $this->load->view('template/header', $data);
            $this->load->view('approval/rgp_transaction_out');
        }
    }

    public function scrap_booking($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "scrap_booking";
            $this->load->view('template/header', $data);
            $this->load->view('approval/scrap_booking');
        }
    }

    public function approvalUsers($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('approved_by', $approved_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalStockFg($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
        $this->db->from('stock_fg a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalStockWip($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
        $this->db->from('stock_wip a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalStockSo($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
        $this->db->from('stock_so a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalOsSo($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
        $this->db->from('os_so a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalOsMpp($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
        $this->db->from('os_mpp a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalForecasts($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
        $this->db->from('forecasts a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.p_month');
        $this->db->group_by('a.p_year');
        $this->db->group_by('a.revision');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalForecastDetail($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        $customer_id = base64_decode($this->input->get('customer_id'));
        $p_month = ($this->input->get('p_month'));
        $p_year = ($this->input->get('p_year'));
        $revision = ($this->input->get('revision'));

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.number_customer, c.name as customer_name');
        $this->db->from('forecasts a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.p_month', $p_month);
        $this->db->where('a.p_year', $p_year);
        $this->db->where('a.revision', $revision);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalGenerateMrpFinals($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $filter_month = $this->input->post('filter_month');
        $filter_year = $this->input->post('filter_year');
        $filter_revision = $this->input->post('filter_revision');

        if ($filter_month != "") {
            $where_month = "AND a.p_month='$filter_month'";
        } else {
            $where_month = "";
        }

        if ($filter_year != "") {
            $where_year = "AND a.p_year='$filter_year'";
        } else {
            $where_year = "";
        }

        if ($filter_revision != "") {
            $where_rev = "AND a.revision='$filter_revision'";
        } else {
            $where_rev = "";
        }

        $this->db->select('a.period, a.revision, a.supplier_id, e.supplier_po, e.number as supplier_number, e.name as supplier_name, e.type, e.state, e.currency, SUM(a.purchase_order) as total_qty, SUM(a.amount) as total_amount');
        $this->db->from("(SELECT CONCAT(a.p_year, a.p_month) as period, a.revision, a.id, a.supplier_id, a.purchase_order, b.price, (a.purchase_order * b.price) AS amount 
            FROM generate_mrp_finals a 
            JOIN supplier_items b ON a.supplier_id = b.supplier_id AND a.item_rm_id = b.item_rm_id 
            WHERE a.approved_to = '$approved_to' and a.approved_by = '$approved_by' $where_month $where_year $where_rev) a");
        $this->db->join('suppliers e', 'a.supplier_id = e.id');
        $this->db->group_by('e.id');
        $this->db->group_by('a.period');
        $this->db->group_by('a.revision');
        $this->db->having('SUM(a.purchase_order) > 0');
        $this->db->order_by('e.name', 'asc');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalGenerateMrpFinalDetails($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        $supplier_id = base64_decode($this->input->get("supplier_id"));
        $period = base64_decode($this->input->get("period"));
        $revision = base64_decode($this->input->get("revision"));

        $this->db->select('a.*, concat(a.p_year, a.p_month) as period, b.number as item_rm_number, b.name as item_rm_name, e.id as supplier_number, e.name as supplier_name, f.leadtime as leadtime2, f.transit, c.name as product_family, d.class, d.composition, d.safety, f.currency, f.price');
        $this->db->from('generate_mrp_finals a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('generate_mrp_abcclass d', 'a.item_rm_id = d.item_rm_id and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
        $this->db->join('suppliers e', 'a.supplier_id = e.id');
        $this->db->join('supplier_items f', 'a.supplier_id = f.supplier_id and a.item_rm_id = f.item_rm_id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('concat(a.p_year, a.p_month) =', $period);
        $this->db->where('a.revision', $revision);
        $this->db->where('a.purchase_order > 0');
        $this->db->group_by('a.id');
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('d.class', 'asc');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();

        $arr = [];
        foreach ($records as $record) {
            if (round($record['total_need']) > 0 && $record['moq'] > 0) {
                $final = ($record['total_need']);
                if ($final > $record['moq']) {
                    $mrp_result = (ceil($final / $record['mpq']) * $record['mpq']);
                } else {
                    $mrp_result = (ceil($final / $record['moq']) * $record['moq']);
                }
            } else {
                $mrp_result = 0;
            }

            $arr[] = array(
                "id" => $record['id'],
                "period" => $record['p_year'] . $record['p_month'],
                "supplier_number" => $record['supplier_number'],
                "supplier_name" => $record['supplier_name'],
                "item_rm_id" => $record['item_rm_id'],
                "item_rm_number" => $record['item_rm_number'],
                "item_rm_name" => $record['item_rm_name'],
                "product_family" => $record['product_family'],
                "price" => $record['price'],
                "share_order" => $record['share_order'],
                "leadtime" => $record['leadtime'],
                "class" => $record['class'],
                "qty_rm" => $record['qty_rm'],
                "qty_wip" => $record['qty_wip'],
                "qty_vendor" => $record['qty_vendor'],
                "qty_supply" => $record['qty_supply'],
                "qty_wo" => $record['qty_wo'],
                "balance_1" => abs($record['balance_1']),
                "balance_2" => abs($record['balance_2']),
                "balance_3" => abs($record['balance_3']),
                "balance_4" => abs($record['balance_4']),
                "balance_5" => abs($record['balance_5']),
                "balance_6" => abs($record['balance_6']),
                "safety_stock" => $record['safety_stock'],
                "safety_stock_persen" => $record['safety_stock_persen'],
                "share_order_qty" => $record['share_order_qty'],
                "total_need" => $record['total_need'],
                "currency" => $record['currency'],
                "price" => $record['price'],
                "mpq" => $record['mpq'],
                "moq" => $record['moq'],
                "mrp_result" => $mrp_result,
                "total_amount" => ($mrp_result * $record['price']),
                "created_by" => $record['created_by'],
                "created_date" => $record['created_date'],
            );
        }

        die(json_encode($arr));
    }

    public function approvalPurchaseRequests($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, c.name as supplier_name, d.name as item_family_name');
        $this->db->from('purchase_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('item_familys d', 'b.item_family_id = d.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseRequestServices($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('*');
        $this->db->from('purchase_request_services');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('approved_by', $approved_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseRequestOthers($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.uom, b.number as item_rm_number, b.name as item_rm_name, c.name as item_family_name');
        $this->db->from('purchase_request_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPO($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.po_no, a.attachment, a.po_date, a.supplier_id, b.number as supplier_number, b.name as supplier_name, b.type, b.state, b.currency, a.note, SUM(a.total) as grandtotal');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPODetails($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        $po_no = base64_decode($this->input->get('po_no'));

        $this->db->select('a.id,a.po_no, a.request_no, a.total_dp,
                    a.po_date,
                    a.remarks,
                    b.number as item_number,
                    b.name as item_name,
                    c.name as item_family_name, 
                    d.name as supplier_name, 
                    d.currency, e.mpq, 
                    e.moq,
                    b.uom,
                    a.month_1,
                    a.month_2,
                    a.month_3,
                    a.discount,
                    d.currency, 
                    a.qty as qty, 
                    a.price as price, 
                    a.total as total,
                    a.total_sub,
                    a.created_by,
                    a.created_date');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.po_no', $po_no);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPOther($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.po_no, a.po_date, a.supplier_id, b.number as supplier_number, b.name as supplier_name, b.type, b.state, b.currency, a.remarks, SUM(a.total) as grandtotal');
        $this->db->from('purchase_order_others a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPOtherDetails($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        $po_no = base64_decode($this->input->get('po_no'));

        $this->db->select('a.id,a.po_no, a.request_no, a.total_dp,
            a.po_date,
            b.number as item_number,
            b.name as item_name,
            c.name as item_family_name, 
            d.name as supplier_name, 
            d.currency, e.mpq, 
            e.moq,
            b.uom,
            a.discount,
            d.currency, 
            a.qty as qty, 
            a.price as price, 
            a.total as total,
            a.total,
            a.created_by,
            a.created_date');
        $this->db->from('purchase_order_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id', 'left');
        // $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_order_others WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.po_no', $po_no);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseOrderServices($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.name as supplier_name, b.currency');
        $this->db->from('purchase_order_services a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        // $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalBom($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.item_fg_id as id, a.item_fg_id, b.number as item_fg_number, b.number_customer, b.name as item_fg_name, b.uom, a.revision, a.created_by, a.created_date');
        $this->db->from('bom a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.item_fg_id');
        $this->db->order_by('b.number_customer', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalBomDetails($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        $item_fg_id = base64_decode($this->input->get('item_fg_id'));

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as item_rm_number, c.name as item_rm_name, c.item_family_id as product_family, c.uom as uom, , d.name as product_family_name, e.composition as composition_old');
        $this->db->from('bom a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join("(SELECT * FROM bom_log ORDER BY revision DESC) e", "a.item_fg_id = e.item_fg_id and a.item_rm_id = e.item_rm_id and e.revision = (a.revision - 1)", 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->where('(a.composition != e.composition or e.composition is null or a.remove = 1)');
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalCustomerItems($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.currency, c.number as item_fg_number, c.name as item_fg_name, c.number_customer as item_fg_customer, d.price as price_old');
        $this->db->from('customer_items a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join("(SELECT * FROM customer_item_histories ORDER BY created_date DESC) d", 'a.item_fg_id = d.item_fg_id and a.plant = d.plant and a.price != d.price', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSupplierItems($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.supplier_id, b.supplier_po, b.number as supplier_number, b.name as supplier_name, b.type, b.state, b.currency, a.remarks1, a.remarks2, a.remarks3, a.remarks4, COUNT(a.id) as total_item');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('b.id');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSupplierItemDetails($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        $supplier_id = base64_decode($this->input->get("supplier_id"));

        $this->db->select('a.*, b.number as supplier_number, b.name as supplier_name, b.currency as supplier_currency, c.number as item_rm_number, c.name as item_rm_name, c.item_family_id as item_rm_family, c.uom, d.name as item_family_name, e.price as price_before');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join("(SELECT * FROM supplier_item_histories ORDER BY id DESC LIMIT 1) e", "a.supplier_id = e.supplier_id and a.item_rm_id = e.item_rm_id", "left");
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSupplierItemPrices($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as supplier_number, b.name as supplier_name, b.currency as supplier_currency, c.number as item_rm_number, c.name as item_rm_name, c.item_family_id as item_rm_family, d.name as item_family_name');
        $this->db->from('supplier_item_prices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalItemRm($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name, d.number as hsn_number, d.name as hsn_name');
        $this->db->from('item_rm a');
        $this->db->join('item_categories b', 'a.item_category_id = b.id');
        $this->db->join('item_familys c', 'a.item_family_id = c.id');
        $this->db->join('hsn d', 'a.hsn_code = d.number', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalItemFg($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as hsn_number, b.name as hsn_name, b.igst, b.sgst, b.cgst, c.number as customer_number, c.name as customer_name, d.name as harnes_type_name, e.total_cct');
        $this->db->from('item_fg a');
        $this->db->join('hsn b', 'a.hsn_code = b.number', 'left');
        $this->db->join('customers c', 'a.customer_id = c.id', 'left');
        $this->db->join('harnes_types d', 'd.number = a.harnes_type', 'left');
        $this->db->join("(SELECT item_fg_id, COUNT(id) as total_cct FROM wos GROUP BY item_fg_id) e", 'e.item_fg_id = a.id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSupplyMaterials($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom');
        $this->db->from('supply_materials a');
        // $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        // $this->db->join('customers d', 'a.customer_id = d.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSupplySheets($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom, b.number_customer, b.name as item_fg_name');
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.type', "REQ");
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalItemNg($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom');
        $this->db->from('item_ng a');
        // $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        // $this->db->join('customers d', 'a.customer_id = d.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalScraps($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        $filter_from = date("Y-m-d");

        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom, c.stock_wip');
        $this->db->from('scraps a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join("(SELECT a.id, (COALESCE(c.issued, 0) + COALESCE(g.adjustment_in, 0) - COALESCE(abs(i.adjustment_out), 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS stock_wip
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$filter_from' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date <= '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date <= '$filter_from' and source = 'Vendor' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date <= '$filter_from' and source = 'User' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_in FROM sto_wip WHERE trans_date <= '$filter_from' and qty > 0 GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_out FROM sto_wip WHERE trans_date <= '$filter_from' and qty < 0 GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.consumed) AS rfg FROM wip_receipt_balance a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$filter_from' GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
            WHERE a.item_category_id = 'C01'
            GROUP BY a.id
            ORDER BY a.number) c", 'a.item_rm_id = c.id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalItemMpq($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom');
        $this->db->from('item_mpq a');
        // $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        // $this->db->join('customers d', 'a.customer_id = d.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalStoRm($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom');
        $this->db->from('sto_rm a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalStoFg($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.number_customer as item_fg_number, c.name as item_fg_name, c.uom');
        $this->db->from('sto_fg a');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSalesOrders($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.name as customer_name, c.number as item_fg_number, c.number_customer, c.name as item_fg_name, c.uom');
        $this->db->from('sales_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSampleRequest($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, 
            b.number as item_rm_number, 
            b.name as item_rm_name,
            c.name as item_family_name, 
            d.name as item_category_name,
            a.currency,
            a.qty, 
            a.price, 
            a.amount, 
            a.status');
        $this->db->from('sample_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('item_categories d', 'b.item_category_id = d.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.item_rm_id', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalScrapBooking($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom');
        $this->db->from('scrap_booking a');
        $this->db->join('item_rm b', 'a.item_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalRgpTransactionsIn($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("
            (CASE 
                WHEN b.id is not null THEN b.id
                ELSE c.number
            END) as item_id,
            (CASE 
                WHEN b.id is not null THEN b.number
                ELSE c.number_customer
            END) as item_number,
            (CASE 
                WHEN b.id is not null THEN b.name
                ELSE c.name
            END) as item_name,
            (CASE 
                WHEN b.id is not null THEN j.name
                ELSE null
            END) as item_family_name,

            a.*,
            
            (CASE 
                WHEN d.id is not null THEN d.name
                ELSE e.name
            END) as company_name,
            (CASE 
                WHEN f.plant is not null THEN f.plant
                ELSE NULL
            END) as plant_number,
            g.name as item_category_name,
            h.name as departement_name,
            i.name as rgp_type_name,
            a.item_category_id,
            (CASE 
                WHEN b.id  is not null THEN k.mpq
                ELSE c.qty_box
            END) as mpq,
            (CASE 
                WHEN b.id is not null THEN ceil(a.qty/k.mpq)
                ELSE ceil(a.qty/c.qty_box)
            END) as qty_label
        ");
        $this->db->from('rgp_transactions a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->join('suppliers d', 'a.company_id = d.id', 'left');
        $this->db->join('customers e', 'a.company_id = e.id', 'left');
        $this->db->join('customer_address f', 'a.plant = f.plant', 'left');
        $this->db->join('item_categories g', 'a.item_category_id = g.id', 'left');
        $this->db->join('departements h', 'a.departement_id = h.id', 'left');
        $this->db->join('rgp_types i', 'a.rgp_type_id = i.id', 'left');
        $this->db->join('item_familys j', 'b.item_family_id = j.id', 'left');
        $this->db->join('item_mpq k', 'a.item_id = k.item_rm_id and a.company_id=k.supplier_id', 'left');
        $this->db->where('a.type', "IN");
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalRgpTransactionsOut($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("
            (CASE 
                WHEN b.id is not null THEN b.id
                ELSE c.number
            END) as item_id,
            (CASE 
                WHEN b.id is not null THEN b.number
                ELSE c.number_customer
            END) as item_number,
            (CASE 
                WHEN b.id is not null THEN b.name
                ELSE c.name
            END) as item_name,
            (CASE 
                WHEN b.id is not null THEN j.name
                ELSE null
            END) as item_family_name,

            a.*,
            
            (CASE 
                WHEN d.id is not null THEN d.name
                ELSE e.name
            END) as company_name,
            (CASE 
                WHEN f.plant is not null THEN f.plant
                ELSE NULL
            END) as plant_number,
            g.name as item_category_name,
            h.name as departement_name,
            i.name as rgp_type_name,
            a.item_category_id,
            (CASE 
                WHEN b.id  is not null THEN k.mpq
                ELSE c.qty_box
            END) as mpq,
            (CASE 
                WHEN b.id is not null THEN ceil(a.qty/k.mpq)
                ELSE ceil(a.qty/c.qty_box)
            END) as qty_label
        ");
        $this->db->from('rgp_transactions a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->join('suppliers d', 'a.company_id = d.id', 'left');
        $this->db->join('customers e', 'a.company_id = e.id', 'left');
        $this->db->join('customer_address f', 'a.plant = f.plant', 'left');
        $this->db->join('item_categories g', 'a.item_category_id = g.id', 'left');
        $this->db->join('departements h', 'a.departement_id = h.id', 'left');
        $this->db->join('rgp_types i', 'a.rgp_type_id = i.id', 'left');
        $this->db->join('item_familys j', 'b.item_family_id = j.id', 'left');
        $this->db->join('item_mpq k', 'a.item_id = k.item_rm_id and a.company_id=k.supplier_id', 'left');
        $this->db->where('a.type', "OUT");
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.id');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
}
