<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Rgp_scan_in extends CI_Controller
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
        $this->form_validation->set_rules('rgp_number', 'Number', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/rgp_scan_in');
        } else {
            redirect('error_access');
        }
    }

    public function readDocument()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("
        select * from (
            select a.number, COALESCE(sum(a.qty),0) as total_qty, COALESCE(b.total_scan,0) as total_scan, COALESCE(sum(a.qty),0)-COALESCE(b.total_scan,0) as balance
            from rgp_transactions a 
            left join (select rgp_number, COALESCE(sum(qty),0) as total_scan from scan_item_rgp group by rgp_number) b on a.number=b.rgp_number
            where a.type='IN' and a.number LIKE '%$post%' group by a.number
        ) x where (x.total_qty -x.total_scan) <> 0");
        echo json_encode($send);
    }

    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');

            $this->db->select('a.label_no,a.rgp_number,b.item_id,a.qty,a.rgp_id');
            $this->db->from('rgp_transaction_labels a');
            $this->db->join("(select number, item_id from rgp_transactions where type='IN' group by number, item_id) b", 'a.rgp_number = b.number and a.item_id = b.item_id', 'left');
            $this->db->where('a.label_no', $label_no);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatables($label_no = "")
    {
        $date = date("Y-m-d");
        $rgp_transaction = $this->crud->read('scan_item_rgp', [], ["label_no" => base64_decode($label_no)]);
        //Select Query
        $this->db->select("
        a.label_no, d.uom, a.qty,a.created_date,a.created_by,
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
                WHEN b.id is not null THEN f.name
                ELSE null
            END) as item_family_name,
        e.name as item_category_name,
        ");
        $this->db->from('scan_item_rgp a');
        $this->db->join('item_rm b', 'a.item_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_id = c.id', 'left');
        $this->db->join('rgp_transactions d', 'a.rgp_number = d.number and a.item_id = d.item_id', 'left');
        $this->db->join('item_categories e', 'd.item_category_id = e.id', 'left');
        $this->db->join('item_familys f', 'b.item_family_id = f.id', 'left');
        $this->db->where('d.number', @$rgp_transaction->rgp_number);
        $this->db->where('a.type', 'IN');
        $this->db->group_by('a.label_no');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $scan_item_rgp = $this->crud->read("scan_item_rgp", [], ["label_no" => $post['label_no'], "type" => "IN"]);

                if (!$scan_item_rgp) {
                    $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $post['rgp_number'], "item_id" => $post['item_id']]);
                    $item_rm = $this->crud->read("item_rm", [], ["id" => $post['item_id']]);
                    if (@$rgp_transactions->approved_to == '' || @$rgp_transactions->approved_to == null) { // cek document RGP sudah di approve
                        if (!empty(@$item_rm->id)) { //jika RM
                            $send   = $this->crud->create('scan_item_rgp', array_merge($post, ["type" => "IN"]));
                            if ($send) {
                                $postItemReceipts = array(
                                    "receipt_id" => $post['rgp_id'],
                                    "label_no" => $post['label_no'],
                                    "qty" => $post['qty'],
                                    "status" => 0,
                                );
                                $this->crud->update('rgp_transaction_labels', ["label_no" => $post['label_no']], ["status" => 1]);
                                $this->crud->create('scan_item_receipts', $postItemReceipts);
                                echo $send;
                            }
                        } else { // jika FG
                            $send   = $this->crud->create('scan_item_rgp', array_merge($post, ["type" => "IN"]));
                            // $rgp_transactions = $this->crud->read("rgp_transactions", [], ["number" => $post['rgp_number'], "item_id" => $post['item_id']]);
                            if ($send) {
                                $postItemReceipts = array(
                                    "checksheet_number" => $post['rgp_id'],
                                    "checksheet_label" => $post['label_no'],
                                    // "workorder" => @$rgp_transactions->id,
                                    "qty" => $post['qty'],
                                    "status" => 0,
                                );
                                $this->crud->update('rgp_transaction_labels', ["label_no" => $post['label_no']], ["status" => 1]);
                                $this->crud->create('scan_item_receipts_fg', $postItemReceipts);
                                echo $send;
                            }
                        }
                    } else {
                        echo json_encode(array("title" => "Need Approve", "message" => "RGP Number Document Need to Approve", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Available", "message" => "Data Label No has been Scanned", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
