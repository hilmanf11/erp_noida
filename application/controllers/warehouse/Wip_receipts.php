<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Wip_receipts extends CI_Controller
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
        $this->form_validation->set_rules('checksheet_number', 'Checksheet No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('warehouse/wip_receipts');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('wip_receipts', ["name" => $post]);
        echo json_encode($send);
    }

    public function readChecksheet()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.checksheet_number, c.number_customer, b.name as customer_name
            FROM wip_receipts a 
            JOIN customers b ON a.customer_id = b.id
            JOIN item_fg c ON a.item_fg_id = c.id
            WHERE a.status = '0' and a.checksheet_number like '%$post%'
            GROUP BY a.checksheet_number
            LIMIT 50");
        echo json_encode($send);
    }

    public function readWo()
    {
        $period = date("Y-m");
        $send = $this->crud->query("SELECT DISTINCT workorder FROM wip_receipts WHERE trans_date_from like '%$period%' and workorder != '' ORDER BY workorder");
        echo json_encode($send);
    }

    public function readCustomer($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $period = date("Ym", strtotime($trans_date));

        $send = $this->crud->query("SELECT a.customer_id, b.number as customer_number, b.name as customer_name 
            FROM production_schedules a
            JOIN customers b on a.customer_id = b.id
            WHERE a.period = '$period' and a.status_wip = '0'
            GROUP BY b.id
            ORDER BY b.name ASC");
        echo json_encode($send);
    }

    public function readItems($customer_id, $trans_date)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $trans_date = base64_decode($trans_date);
        $period = date("Ym", strtotime($trans_date));
        $customer_id = base64_decode($customer_id);

        $send = $this->crud->query("SELECT a.workorder, b.id as item_fg_id, b.number as item_fg_number, b.name as item_fg_name, b.number_customer, c.name as customer_name  
            FROM production_schedules a
            JOIN item_fg b on a.item_fg_id = b.id
            JOIN customers c on a.customer_id = c.id
            WHERE a.period = '$period' and a.customer_id = '$customer_id' and a.status_wip = '0' and (b.number_customer like '%$post%' or b.number like '%$post%')
            GROUP BY b.id
            ORDER BY b.number_customer ASC");
        echo json_encode($send);
    }

    public function number($id_menu)
    {
        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(checksheet_number) as kode FROM wip_receipts WHERE checksheet_number like '%$whereDate%'");
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

    public function label_no($trans_date)
    {
        $datenow = date("Y-m", strtotime($trans_date));
        $sqlGetID = $this->db->query("SELECT max(`number`) as kode FROM wip_receipts WHERE trans_date like '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%05s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%05s", $urutan);
        }
        $workOrderNo = "CS" . $datenow . "-" . $autoID;
        return $workOrderNo;
    }

    public function datatablesTemp($period, $wp, $customer_id, $item_fg_id = "")
    {
        $this->dummy = $this->load->database('dummy', TRUE);

        // $trans_date_from = base64_decode($trans_date_from);
        // $trans_date_to = base64_decode($trans_date_to);
        $customer_id = base64_decode($customer_id);
        $item_fg_id = base64_decode($item_fg_id);
        $period = base64_decode($period);
        $wp = base64_decode($wp);
        // $period = date("Ym", strtotime($trans_date_from));

        // $item_fg = $this->crud->read("item_fg", [], ["id" => $item_fg_id]);

        $this->db->select('a.*, c.qty_box, b.name as customer_name, c.number_customer');
        $this->db->from('production_schedules a');
        $this->db->join('customers b', "a.customer_id = b.id");
        $this->db->join('item_fg c', "a.item_fg_id = c.id");
        $this->db->where('a.period', $period);
        $this->db->where('a.wp', $wp);
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->order_by('a.workorder', 'asc');
        $records = $this->db->get()->result_array();

        $arr = array();
        foreach ($records as $record) {
            $assy_number = $record['number_customer'];
            $mpp = $record['period'];
            $wp = sprintf("%02s", $record['wp']) . "/" . substr($mpp, -2);

            $sqlQty = $this->dummy->query("SELECT COUNT(x.serial_number) as qty FROM (
                SELECT DISTINCT z.serial_number FROM (
                SELECT DISTINCT
                    TRIM(a.serial_number) AS serial_number,
                    TRIM(a.assy_number) AS assy_number,
                    TRIM(a.customer) AS customer,
                    TRIM(a.mpp) AS mpp,
                    TRIM(a.wp) AS wp,
                    TRIM(a.lot) AS lot,
                    DATE(a.tanggal_proses) AS tanggal,
                    COALESCE(a.jam_scan,'00:00:00') AS jam,
                    a.wds_qty
                FROM mst_scan_proses a
                WHERE kode_proses = '11' and assy_number = '$assy_number' and mpp = '$period' and wp = '$wp'
                and status_repair = 'false' 
                AND status_before IS NOT NULL
                AND status_repair = 'f'
                ORDER BY serial_number) z ) x");
            $rowQty = $sqlQty->row();
            $qty_scan = empty(@$rowQty->qty) ? 0 : @$rowQty->qty;

            $this->db->select('SUM(qty) as qty_rfg');
            $this->db->from('wip_receipts');
            $this->db->where('workorder', $record['workorder']);
            $this->db->where('item_fg_id', $item_fg_id);
            $rfg = $this->db->get()->row();

            if (($qty_scan - round($rfg->qty_rfg)) > 0) {
                $qty_scan2 = ($qty_scan - round($rfg->qty_rfg));
            } else {
                $qty_scan2 = 0;
            }

            $qty_balance = (round($record['qty']) - round($rfg->qty_rfg));

            if ($qty_scan2 > 0) {
                $arr[] = array(
                    "item_fg_id" => $record['item_fg_id'],
                    "workorder" => $record['workorder'],
                    "period" => $record['period'],
                    "wp" => $record['wp'],
                    "qty_wo" => round($record['qty']),
                    "qty_rfg" => round($rfg->qty_rfg),
                    "qty_receipt" => $qty_scan2,
                    "qty" => $qty_scan2,
                    "qty_box" => $record['qty_box'],
                    "label_box" => ceil($qty_scan2 / $record['qty_box']),
                );
            }
        }

        echo json_encode($arr);
    }

    public function datatablesBom($period, $wp, $customer_id, $item_fg_id = "", $qty_fg = 0)
    {
        $customer_id = base64_decode($customer_id);
        $item_fg_id = base64_decode($item_fg_id);
        $period = base64_decode($period);
        $wp = base64_decode($wp);
        $trans_date = date("Y-m-d");

        $this->db->select("a.*, (SUM(a.qty) - coalesce(d.receipt, 0)) as qty_prod, b.item_rm_id, b.composition, round((SUM(a.qty) - coalesce(d.receipt, 0)) * b.composition, 2) as consumed, c.number as item_rm_number, c.name as item_rm_name, c.uom, e.balance");
        $this->db->from('production_schedules a');
        $this->db->join('bom b', 'a.item_fg_id = b.item_fg_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join("(SELECT workorder, item_fg_id, SUM(qty) as receipt FROM wip_receipts GROUP BY workorder, item_fg_id) d", 'a.workorder = d.workorder and a.item_fg_id = d.item_fg_id', 'left');
        $this->db->join("(SELECT a.id, (COALESCE(c.issued, 0) + COALESCE(g.adjustment_in, 0) - COALESCE(abs(i.adjustment_out), 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS balance
                FROM item_rm a
                JOIN item_familys b ON a.item_family_id = b.id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date <= '$trans_date' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date <= '$trans_date' and source = 'Vendor' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date <= '$trans_date' and source = 'User' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_in FROM sto_wip WHERE trans_date <= '$trans_date' and qty > 0 GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_out FROM sto_wip WHERE trans_date <= '$trans_date' and qty < 0 GROUP BY item_rm_id) i ON a.id = i.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(a.consumed) AS rfg FROM wip_receipt_balance a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
                GROUP BY a.id
                ORDER BY a.number) e", 'b.item_rm_id = e.id');
        $this->db->where('a.period', $period);
        $this->db->where('a.wp', $wp);
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('b.item_rm_id');
        $this->db->order_by('b.item_rm_id', 'asc');
        $records = $this->db->get()->result_array();

        $arr = array();
        foreach ($records as $record) {
            // $item_rm_id = $record['item_rm_id'];
            // $sqlwip = $this->db->query("SELECT a.id, (COALESCE(c.issued, 0) + COALESCE(g.adjustment_in, 0) - COALESCE(abs(i.adjustment_out), 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS balance
            //     FROM item_rm a
            //     JOIN item_familys b ON a.item_family_id = b.id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date <= '$trans_date' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date <= '$trans_date' and source = 'Vendor' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date <= '$trans_date' and source = 'User' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_in FROM sto_wip WHERE trans_date <= '$trans_date' and qty > 0 GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_out FROM sto_wip WHERE trans_date <= '$trans_date' and qty < 0 GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            //     LEFT JOIN (SELECT item_rm_id, SUM(consumed) AS rfg FROM wip_receipt_balance WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            //     WHERE a.id = '$item_rm_id'
            //     GROUP BY a.id
            //     ORDER BY a.number");
            // $datawip = $sqlwip->row();
            $wip = empty(@$record['balance']) ? 0 : @$record['balance'];

            if ($qty_fg == 0) {
                $consumed = $record['consumed'];
                $rfg = $record['qty_prod'];
            } else {
                $consumed = ($qty_fg * $record['composition']);
                $rfg = $qty_fg;
            }

            $arr[] = array(
                "item_fg_id" => $record['item_fg_id'],
                "customer_id" => $record['customer_id'],
                "item_rm_id" => $record['item_rm_id'],
                "item_rm_number" => $record['item_rm_number'],
                "item_rm_name" => $record['item_rm_name'],
                "workorder" => $record['workorder'],
                "wp" => $record['wp'],
                "uom" => $record['uom'],
                "qpa" => $record['composition'],
                "rfg" => $rfg,
                "consumed" => round($consumed, 2),
                "issued" => @$wip
            );
        }

        echo json_encode($arr);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_checksheet = $this->input->get('filter_checksheet');
            $filter_workorder = $this->input->get('filter_workorder');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_wp = $this->input->get('filter_wp');
            $filter_period = $this->input->get('filter_period');
            $filter_status = $this->input->get('filter_status');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, d.number as item_fg_number, d.name as item_fg_name, d.number_customer, d.uom, COAlESCE(c.total_label, 0) as total_label, (CASE WHEN b.total_scan = a.label_box THEN 1 ELSE 0 END) as scan');
            $this->db->from('wip_receipts a');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->join("(SELECT workorder, checksheet_number, COUNT(id) as total_scan FROM scan_item_receipts_fg GROUP BY workorder, checksheet_number) b", 'b.workorder = a.workorder and a.checksheet_number = b.checksheet_number', 'left');
            $this->db->join("(SELECT checksheet_number, COUNT(id) as total_label FROM wip_receipt_boxs GROUP BY checksheet_number) c", 'a.checksheet_number = c.checksheet_number', 'left');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date_from >=', $filter_from);
                $this->db->where('a.trans_date_to <=', $filter_to);
            }
            $this->db->like('a.checksheet_number', $filter_checksheet);
            $this->db->like('a.workorder', $filter_workorder);
            $this->db->like('a.period', $filter_period);
            $this->db->like('a.wp', $filter_wp);
            $this->db->like('d.id', $filter_item_fg_id);
            if ($filter_status != "") {
                $this->db->where("(CASE WHEN b.total_scan = a.label_box THEN 1 ELSE 0 END) = '$filter_status'");
            }
            $this->db->order_by('a.trans_date_from', 'DESC');
            $this->db->order_by('a.checksheet_number', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
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
                $workorder = $post['workorder'];
                $dataBoms = $post['details'];
                unset($post['details']);

                $qProses = $this->db->query("SELECT a.workorder, a.qty, SUM(b.qty) AS receipt FROM production_schedules a 
                    JOIN wip_receipts b ON a.item_fg_id = b.item_fg_id AND a.workorder = b.workorder
                    WHERE a.workorder = '$workorder'");
                $dProses = $qProses->row();

                $trans_date = date("Y-m-d");
                $this->db->select("a.*, (SUM(a.qty) - coalesce(d.receipt, 0)) as qty_prod, b.item_rm_id, b.composition, round((SUM(a.qty) - coalesce(d.receipt, 0)) * b.composition, 2) as consumed, c.number as item_rm_number, c.name as item_rm_name, c.uom, e.balance");
                $this->db->from('production_schedules a');
                $this->db->join('bom b', 'a.item_fg_id = b.item_fg_id');
                $this->db->join('item_rm c', 'b.item_rm_id = c.id');
                $this->db->join("(SELECT workorder, item_fg_id, SUM(qty) as receipt FROM wip_receipts GROUP BY workorder, item_fg_id) d", 'a.workorder = d.workorder and a.item_fg_id = d.item_fg_id', 'left');
                $this->db->join("(SELECT a.id, (COALESCE(c.issued, 0) + COALESCE(g.adjustment_in, 0) - COALESCE(abs(i.adjustment_out), 0) - COALESCE(d.return, 0) - COALESCE(e.ng, 0) - COALESCE(f.scrap, 0) - COALESCE(h.rfg, 0)) AS balance
                    FROM item_rm a
                    JOIN item_familys b ON a.item_family_id = b.id
                    LEFT JOIN (SELECT item_rm_id, SUM(qty) AS issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$trans_date' and request_no NOT LIKE '%PR-%' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                    LEFT JOIN (SELECT item_rm_id, SUM(qty) AS `return` FROM return_materials WHERE return_date <= '$trans_date' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                    LEFT JOIN (SELECT item_rm_id, SUM(qty) AS ng FROM scraps WHERE trans_date <= '$trans_date' and source = 'Vendor' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                    LEFT JOIN (SELECT item_rm_id, SUM(qty) AS scrap FROM scraps WHERE trans_date <= '$trans_date' and source = 'User' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                    LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_in FROM sto_wip WHERE trans_date <= '$trans_date' and qty > 0 GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                    LEFT JOIN (SELECT item_rm_id, SUM(qty) AS adjustment_out FROM sto_wip WHERE trans_date <= '$trans_date' and qty < 0 GROUP BY item_rm_id) i ON a.id = i.item_rm_id
                    LEFT JOIN (SELECT a.item_rm_id, SUM(a.consumed) AS rfg FROM wip_receipt_balance a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$trans_date' GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
                    GROUP BY a.id
                    ORDER BY a.number) e", 'b.item_rm_id = e.id');
                $this->db->where('a.period', $post['period']);
                $this->db->where('a.wp', $post['wp']);
                $this->db->where('a.customer_id', $post['customer_id']);
                $this->db->where('a.item_fg_id', $post['item_fg_id']);
                $this->db->group_by('a.item_fg_id');
                $this->db->group_by('b.item_rm_id');
                $this->db->order_by('b.item_rm_id', 'asc');
                $records = $this->db->get()->result_array();

                $arr = array();
                foreach ($records as $record) {
                    $wip = empty(@$record['balance']) ? 0 : @$record['balance'];

                    $consumed = round($post['qty'] * $record['composition'], 2);
                    if ($consumed > $wip) {
                        die(json_encode(array("title" => "Failed", "message" => "RFG Consumed > WIP balance, Please Issued Material this Part No " . $item_rm_id, "theme" => "error")));
                    }
                }

                if (!empty($dProses->workorder)) {
                    if ($dProses->qty < ($dProses->receipt + $post['qty'])) {
                        die(json_encode(array("title" => "Error", "message" => "Qty Production < Qty RFG", "theme" => "error")));
                    }
                }

                foreach ($dataBoms as $dataBom) {
                    if ($post['workorder'] != $dataBom['workorder']) {
                        die(json_encode(array("title" => "Error", "message" => "Workorder not correct, please try again", "theme" => "error")));
                    }

                    if ($post['item_fg_id'] != $dataBom['item_fg_id']) {
                        die(json_encode(array("title" => "Error", "message" => "Product No is not correct, please try again", "theme" => "error")));
                    }
                }

                $this->db->delete('wip_receipt_balance', ["checksheet_number" => $post['checksheet_number']]);
                $this->db->trans_start();
                foreach ($dataBoms as $dataBom) {
                    $this->db->insert('wip_receipt_balance', $dataBom);
                }

                $read = $this->crud->reads("wip_receipts", [], ["checksheet_number" => $post['checksheet_number']]);
                if (count($read) > 0) {
                    die(json_encode(array("title" => "Error", "message" => "Duplicate Checksheet Number, Please try again", "theme" => "error")));
                } else {
                    $this->crud->create('wip_receipts', $post);
                    $this->db->update('production_schedules', ["status_wip" => 1], ["workorder" => $post['workorder']]);
                }

                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array("title" => "Error", "message" => "Data Saved Failed", "theme" => "error"));
                } else {
                    echo json_encode(array("title" => "Success", "message" => "Data Saved Successfully", "theme" => "success"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create_label_box()
    {
        $post = $this->input->post();
        $checksheet_number = $post['checksheet_number'];
        $workorder = $post['workorder'];
        $qty = $post['qty'];

        //Read Label ID
        $sqlGetID = $this->db->query("SELECT max(checksheet_label) as kode FROM wip_receipt_boxs WHERE checksheet_number = '$checksheet_number'");
        $rowID = $sqlGetID->row();
        $label = $rowID->kode;
        if ($label == NULL) {
            $autoID = "B" . $checksheet_number .  sprintf("%06s", $label + 1);
        } else {
            $urutan = (int) substr($label, -6);
            $autoID = "B" . $checksheet_number . sprintf("%06s", $urutan + 1);
        }

        //Simpan Label
        $arrLabel = [
            "checksheet_number" => $checksheet_number,
            "workorder" => $workorder,
            "checksheet_label" => $autoID,
            "qty" => $qty
        ];

        $send = $this->crud->create('wip_receipt_boxs', $arrLabel);
        die($send);
    }

    public function delete()
    {
        $data = $this->input->post();
        $read = $this->crud->read('wip_receipts', ["id" => $data['id']]);
        $checksheet_number = @$read->checksheet_number;

        $scan = $this->crud->reads('scan_item_receipts_fg', ["checksheet_number" => $checksheet_number, "status" => "1"]);

        if (count($scan) > 0) {
            die(json_encode(array("title" => "Error", "message" => "Cannot Delete because the label already Shipping Order", "theme" => "error")));
        }

        $send = $this->crud->delete('wip_receipt_boxs', ["checksheet_number" => $checksheet_number]);
        $this->crud->delete('scan_item_receipts_fg', ["checksheet_number" => $checksheet_number]);
        $this->db->delete('wip_receipt_balance', ["checksheet_number" => $checksheet_number]);


        if ($send) {
            $send = $this->crud->delete('wip_receipts', ["id" => $data['id']]);
        }

        $this->db->update('production_schedules', ["status_wip" => 0], ["workorder" => $data['workorder']]);
        echo $send;
    }

    public function deleteLabel()
    {
        $data = $this->input->post();
        $checksheet_number = $data['checksheet_number'];

        $scan = $this->crud->reads('shipping_orders', ["checksheet_number" => $checksheet_number]);

        if (count($scan) > 0) {
            die(json_encode(array("title" => "Error", "message" => "Cannot process because the label already Shipping Order", "theme" => "error")));
        }

        $send = $this->crud->delete('wip_receipt_boxs', ["checksheet_number" => $checksheet_number]);
        $this->crud->delete('scan_item_receipts_fg', ["checksheet_number" => $checksheet_number]);
        echo $send;
    }

    public function print_label_box($checksheet_number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number, d.name, d.number_customer, d.alias, a.qty, a.checksheet_label, f.trans_date_to, i.id as customer_number, i.name as customer_name'); //d.description,
        $this->db->from('wip_receipt_boxs a');
        // $this->db->join('production_schedules c', 'a.workorder = c.workorder');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        $this->db->join('item_fg d', 'f.item_fg_id = d.id');
        // $this->db->join('warehouse_location_items g', 'd.id = g.item_rm_id', 'left');
        $this->db->join('customer_items h', 'h.customer_id = f.customer_id and d.id = h.item_fg_id', 'left');
        $this->db->join('customers i', 'i.id = h.customer_id', 'left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.checksheet_number', $checksheet_number);
        $this->db->group_by('a.checksheet_label');
        $wip_receipt_labels = $this->db->get()->result_object();

        $this->createQrcode($checksheet_number, "assets/image/qrcode/");

        $html = '<html>
                    <head>
                        <title>' . $checksheet_number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        // if ($wip_receipt_labels) {
        //$html .= '<div style="width: 120mm;">';
        $no = 1;
        foreach ($wip_receipt_labels as $wip_receipt_label) {
            // $qrcodes = $wip_receipt_label->number . "|" . $wip_receipt_label->customer_number . "|" . $wip_receipt_label->alias . "|" . $wip_receipt_label->checksheet_label . "|" . $wip_receipt_label->qty . "|" . $wip_receipt_label->trans_date_to;
            $qrcodes = $wip_receipt_label->checksheet_label;
            $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
            $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1">
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $checksheet_number . '.png') . '" width="50"/>
                                        </td>
                                        <td colspan="2" style="font-size:15px; text-align:left;"><span style="font-size:10px;">Checksheet ID</span><br><b>' . $checksheet_number . '</b></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Name.</td>
                                        <td colspan="2"><small style="font-size:12px;"><span style="font-size:10px;">' . $wip_receipt_label->customer_number . '</span><br><b>' . $wip_receipt_label->customer_name . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">EBWS P/N</td>
                                        <td colspan="2"><small style="font-size:30px;"><b>' . $wip_receipt_label->number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->number_customer . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Label. No</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Quantity</td>
                                        <td style="width:25mm;"><small style="font-size:20px;"><b>' . number_format($wip_receipt_label->qty, 2) . '</b></small></td>
                                        <td style="text-align:center;" style="width:25mm" rowspan="3">
                                            <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="100"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Date</td>
                                        <td><small style="font-size:16px;"><b>' . $wip_receipt_label->trans_date_to . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:center;"><img src="' . $config->favicon . '" width="60"/></td>
                                    </tr>
                                </table>
                            </div>';
            $no++;
        }
        $html .= '</div><script>window.print()</script>';
        // } else {
        // $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        // }
        die($html);
    }

    public function print_checksheet($checksheet)
    {
        $checksheetData = base64_decode($checksheet);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, d.name as customer_name, c.number as item_fg_number, c.number_customer, c.name as item_fg_name, c.uom');
        $this->db->from('wip_receipts a');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('customers d', 'a.customer_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.checksheet_number', $checksheetData);
        $this->db->order_by('a.workorder', 'ASC');
        $checksheets = $this->db->get()->result_array();

        //Generate QRcode
        $this->createQrcode($checksheetData, "assets/image/qrcode/");

        $html = '<html>
            <head>
                <title>' . $checksheetData . '</title>
                <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
            </head>
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }
                #customers {
                    border-collapse: collapse;width: 100%;
                    font-size: 12px;
                }
                #customers td, #customers th {
                    border: 1px solid black;padding: 2px;
                }
                #customers th {
                    padding-top: 2px;
                    padding-bottom: 2px;
                    text-align: center;color: black;
                }
                @media screen {
                    .print {
                        display: none !important;
                    }
                }

                @media print {
                    .noprint {
                        display: none !important;
                    }
                }
            </style>
            <body>
                <div style="margin:20%;" class="noprint">
                    <center>
                        <h1>Press CTRL + P for Print</h1>
                        <p>Paper Size A5, Layout Potrait</p>
                        <p>Margin Default, Scale 98</p>
                    </center>
                </div>
                <div class="print">
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' . $config->favicon . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        <table style="width:100%; font-size:10px;">
                            <tr>
                                <td width="80">Doc No</td>
                                <td width="5">:</td>
                                <td width="100">' . $config_iso->doc_checksheet . '</td>
                            </tr>
                            <tr>
                                <td>Form</td>
                                <td>:</td>
                                <td>' . $config_iso->form_checksheet . '</td>
                            </tr>
                            <tr>
                                <td>Print Date</td>
                                <td>:</td>
                                <td>' . date("d M Y H:m:s") . '</td>
                            </tr>
                            <tr>
                                <td>Print By</td>
                                <td>:</td>
                                <td>' . $this->session->username . '</td>
                            </tr>
                        </table>
                    </div>

                    <br><br><br><br>
                    <center>
                        <h3 style="margin:0;"><u>RECEIVING FINISH GOOD</u></h3>
                        <b style="font-size:12px;">Doc. No ' . $checksheetData . '</b>
                    </center>
                    <br>
                    <div style="float:left; width:80%;">
                        <table style="width:100%; font-size:12px;">
                            <tr>
                                <td width="100" style="padding:5px;">Date</td>
                                <td width="20">:</td>
                                <td><b>' . date("d F Y", strtotime($checksheets[0]['trans_date_to'])) . '</b></td>

                                <td width="100" style="padding:5px;">Product No EBWS</td>
                                <td width="20">:</td>
                                <td><b>' . $checksheets[0]['item_fg_number'] . '</b></td>
                            </tr>
                            <tr>
                                <td style="padding:5px;">Customer</td>
                                <td>:</td>
                                <td><b>' . $checksheets[0]['customer_name'] . '</b></td>

                                <td width="200" style="padding:5px;">Product No Customer</td>
                                <td width="20">:</td>
                                <td><b>' . $checksheets[0]['number_customer'] . '</b></td>
                            </tr>
                        </table>
                        <table id="customers" style="margin-top:20px !important; float:left;">
                            <tr>
                                <th style="text-align:center;">Workorder No</th>
                                <th style="text-align:center;">Period</th>
                                <th style="text-align:center;">WP</th>
                                <th style="text-align:center;">Qty</th>
                            </tr>';
        foreach ($checksheets as $checksheet) {
            $period = date("Ym", strtotime($checksheet['trans_date_to']));
            $html .= '  <tr>
                                                <td>' . $checksheet['workorder'] . '</td>
                                                <td>' . $period . '</td>
                                                <td>' . sprintf("%02s", $checksheet['wp']) . '/' . substr($period, -2) . '</td>
                                                <td>' . $checksheet['qty'] . '</td>
                                            </tr>';
        }
        $html .= '  </table>
                    </div>
                    <div style="float:left; width:20%; text-align:center;">
                        <img src="' . base_url('assets/image/qrcode/' . $checksheetData . '.png') . '" width="150"/>
                    </div>

                    <table id="customers" style="margin-top:20px !important; float:left;">
                        <tr>
                            <th width="200" style="text-align:center;">Production</th>
                            <th width="200" style="text-align:center;">QC</th>
                            <th width="200" style="text-align:center;">DC</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                        </tr>
                    </table>
                </div>
                <script>window.print()</script>
            </body>
        </html>';
        echo $html;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=wip_receipts_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_checksheet = $this->input->get('filter_checksheet');
        $filter_workorder = $this->input->get('filter_workorder');
        $filter_wp = $this->input->get('filter_wp');
        $filter_period = $this->input->get('filter_period');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, d.number as item_fg_number, d.name as item_fg_name, d.number_customer, d.uom');
        $this->db->from('wip_receipts a');
        // $this->db->join('production_schedules c', 'a.workorder = c.workorder and a.wp = c.wp');
        $this->db->join('item_fg d', 'a.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') >=", $filter_from);
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') <=", $filter_to);
        }
        $this->db->like('a.checksheet_number', $filter_checksheet);
        $this->db->like('a.workorder', $filter_workorder);
        $this->db->like('a.period', $filter_period);
        $this->db->like('a.wp', $filter_wp);
        $this->db->like('d.id', $filter_item_fg_id);
        $this->db->order_by('a.created_date', 'DESC');
        $this->db->order_by('a.checksheet_number', 'DESC');
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
                                <small>WIP RECEIPT</small>
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
                    <th>Checksheet No</th>
                    <th>Workorder</th>
                    <th>Date<br>From</th>
                    <th>Date<br>To</th>
                    <th>WP</th>
                    <th>Product No EBWS</th>
                    <th>Product No Customer</th>
                    <th>Description</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Lot Box</th>
                    <th>Label Box</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['checksheet_number'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['trans_date_from'] . '</td>
                            <td>' . $data['trans_date_to'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['wp'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . number_format($data['lot_box']) . '</td>
                            <td>' . number_format($data['label_box']) . '</td>
                            <td>' . $data['created_by'] . '</td>
                            <td>' . $data['created_date'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
