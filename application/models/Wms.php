<?php
date_default_timezone_set("Asia/Kolkata");
defined('BASEPATH') or exit('No direct script access allowed');

class Wms extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->default = $this->load->database('default', TRUE);
        $this->load->library('uuid');
    }

    function index()
    {
        show_404();
    }

    function uuid_key()
    {
        $id = $this->uuid->v4();
        $id = str_replace('-', '', $id);
        return $id;
    }

    function autoid($table)
    {
        $date = date("Ymd");
        $sql = $this->db->query("SELECT max(`id`) as kode FROM $table WHERE id like '%$date%'");
        $row = $sql->row();
        $kode = $row->kode;

        if ($kode == NULL) {
            $autoid = $date . sprintf("%06s", $kode + 1);
        } else {
            $autoid = (int) $kode + 1;
        }

        return $autoid;
    }
    
    function create($table, $values, $username)
    {
        if ($username != "") {
            if(@empty($values['id'])){
                $id = $this->autoid($table);
            }else{
                $id = $values['id'];
            }

            $data = array_merge($values, [
                "id" => $id,
                "created_by" => $username,
                "created_date" => date('Y-m-d H:i:s')
            ]);

            $this->db->trans_start();
            $this->db->insert($table, $data);
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $error = $this->db->error();
                // if($logs == ""){
                //     $this->logs("Create", json_encode($data), $table);
                //     $this->approvals($table, $id);
                // }
                
                return array("title" => "Failed", "message" => 'Database Error: ' . $error['message'], "theme" => "error");
            } else {
                return array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success", "id" => $id);
            }
        } else {
            return array("title" => "Failed", "message" => 'Your Session has been Expired', "theme" => "error");
        }
    }

    function update($table, $where, $values, $logs = "")
    {
        if ($this->session->username != "") {
            $data = array_merge($values, [
                "updated_by" => $this->session->username,
                "updated_date" => date('Y-m-d H:i:s')
            ]);

            $dataBefore = $this->read($table, [], $where);

            $this->db->where($where);
            if ($this->db->update($table, $data)) {
                if($logs == ""){
                    $this->logs("Update Before", json_encode($dataBefore), $table);
                    $this->logs("Update New", json_encode($data), $table);
                }
                
                $read = $this->read($table, [], $where);
                $this->approvals($table, @$read->id, $dataBefore);

                return json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                return log_message('error', 'There is an error in your system or data');
            }
        } else {
            return log_message('error', 'Your Session has been Expired');
        }
    }

    function delete($table, $data)
    {
        if ($this->session->username != "") {
            $dataBefore = $this->read($table, [], $data);

            if ($this->db->delete($table, $data)) {
                $this->logs("Delete", json_encode($dataBefore), $table);
                return json_encode(array("title" => "Good Job", "message" => "Data Deleted Successfully", "theme" => "success"));
            } else {
                return log_message('error', 'There is an error in your system or data');
            }
        } else {
            return log_message('error', 'Your Session has been Expired');
        }
    }

    function logs($action, $records, $table)
    {
        $id = $this->autoid('logs');
        $data = array(
            "id" => $id,
            "created_by" => $this->session->username,
            "created_date" => date('Y-m-d H:i:s'),
            "ip_address" => $this->input->ip_address(),
            "action" => $action,
            "menu" => $table,
            "description" => $records
        );

        $this->db->insert('logs', $data);
    }

    function approvals($table, $table_id, $dataBefore = null)
    {
        $query = $this->db->query("DESCRIBE $table");
        $fields = $query->result_array();

        if($table == "purchase_order_others"){
            $data = $this->crud->read("purchase_order_others", [], ["id" => $table_id]);
            if($data->type == "CL"){
                $table_name = "onetime_po_others";
            }else{
                $table_name = $table;
            }
        }else{
            $table_name = $table;
        }

        $user = $this->read('users', [], ["username" => $this->session->username]);
        $approval = $this->read('approvals', [], ["table_name" => $table_name]);

        $fieldExists = false;
        foreach ($fields as $field) {
            if ($field['Field'] == "approved") {
                $fieldExists = true;
                break;
            }
        }

        if ($fieldExists) {
            if (!empty($approval)) {
                $formApprove = [
                    "approved" => 1,
                    "approved_to" => $approval->user_approval_1,
                    "approved_by" => $this->session->username,
                    "approved_data" => json_encode($dataBefore),
                ];

                $this->db->where(["id" => $table_id]);
                $this->db->update($table, $formApprove);
            }
        }
    }

    function connectionInfo()
    {
        switch (connection_status()) {
            case CONNECTION_NORMAL:
                $txt = json_encode(array("title" => "Normal", "message" => "Connection is in a normal state", "theme" => "conn_normal"));
                break;
            case CONNECTION_ABORTED:
                $txt = json_encode(array("title" => "Normal", "message" => "Connection is aborted", "theme" => "conn_aborted"));
                break;
            case CONNECTION_TIMEOUT:
                $txt = json_encode(array("title" => "Normal", "message" => "Connection is timed out", "theme" => "conn_timeout"));
                break;
            case (CONNECTION_ABORTED & CONNECTION_TIMEOUT):
                $txt = json_encode(array("title" => "Normal", "message" => "Connection is aborted and timed out", "theme" => "conn_aborted_timeout"));
                break;
            default:
                $txt = json_encode(array("title" => "Unknown", "message" => "UNKNOWN Connection", "theme" => "conn_unknown"));
                break;
        }

        echo $txt;
    }
}
