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

    public function readChecksheet(){
        $send = $this->crud->query("SELECT a.checksheet_number, c.number_customer, b.name as customer_name
            FROM wip_receipts a 
            JOIN customers b ON a.customer_id = b.id
            JOIN item_fg c ON a.item_fg_id = c.id
            WHERE a.status = '0'
            GROUP BY a.checksheet_number");
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

        if(@$row_menu->number == ""){
            //
        }else{
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number.$yearnow.$yearend."1";

            $sqlGetID	= $this->db->query("SELECT max(checksheet_number) as kode FROM wip_receipts WHERE checksheet_number like '%$whereDate%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;
            
            if ($kode == NULL) {
                $autoID = sprintf("%0".$row_menu->sequence."s", $kode + 1);
                $autoNo = $whereDate.$autoID;
            } else {
                $urutan = (int) substr($kode, -6);
                $urutan++;
                $autoID	= sprintf("%0".$row_menu->sequence."s", $urutan);
                $autoNo = $whereDate.$autoID;
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

    public function datatablesTemp($trans_date, $customer_id, $item_fg_id)
    {
        $this->dummy = $this->load->database('dummy', TRUE);

        $trans_date = base64_decode($trans_date);
        $customer_id = base64_decode($customer_id);
        $item_fg_id = base64_decode($item_fg_id);
        $period = date("Ym", strtotime($trans_date));

        // $delivery = $this->crud->reads("delivery_orders", [], ["delivery_date" => $trans_date]);
        // $whereArr = array();
        // foreach ($delivery as $del) {
        //     $whereArr[] = $del->item_fg_id;
        // }

        $this->db->select('a.*, c.qty_box, b.name as customer_name, c.number_customer');
        $this->db->from('production_schedules a');
        $this->db->join('customers b', "a.customer_id = b.id");
        $this->db->join('item_fg c', "a.item_fg_id = c.id");
        $this->db->where('a.status_wip', 0);
        $this->db->where('a.period', $period);
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.item_fg_id', $item_fg_id);
        // if(!empty($whereArr)){
        //     $this->db->where_not_in('a.item_fg_id', $whereArr);
        // }
        $this->db->order_by('a.workorder', 'asc');
        $records = $this->db->get()->result_array();

        $arr = array();
        foreach ($records as $record) {
            $assy_number = $record['number_customer'];
            $mpp = $record['period'];
            $wp = sprintf("%02s", $record['wp'])."/".substr($mpp, -2);

            $sqlQty = $this->dummy->query("SELECT assy_number, mpp, wp, customer, COUNT(qty) as qty 
                from mst_scan_proses 
                where kode_proses = '6' and assy_number = '$assy_number' and mpp ='$mpp' and wp = '$wp'
                GROUP BY assy_number, mpp, wp, customer");
            $rowQty = $sqlQty->row();

            if(!empty($rowQty)){
                $arr[] = array(
                    "workorder" => $record['workorder'],
                    "period" => $record['period'],
                    "wp" => $record['wp'],
                    "qty_receipt" => empty(@$rowQty->qty) ? 0 : @$rowQty->qty,
                    "qty" => empty(@$rowQty->qty) ? 0 : @$rowQty->qty,
                    "qty_box" => $record['qty_box'],
                    "label_box" => round(empty(@$rowQty->qty) ? 0 : @$rowQty->qty / $record['qty_box']),
                );
            }
        }

        if(empty($arr)){
            show_error("Data Not Found");
        }else{
            echo json_encode($arr);
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_checksheet = $this->input->get('filter_checksheet');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, c.workorder, d.number as item_fg_number, d.name as item_fg_name, d.number_customer, d.uom');
            $this->db->from('wip_receipts a');
            $this->db->join('production_schedules c', 'a.workorder = c.workorder and a.wp = c.wp');
            $this->db->join('item_fg d', 'c.item_fg_id = d.id');
            // $this->db->join('uom e', 'd.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.checksheet_number', $filter_checksheet);
            $this->db->order_by('a.trans_date', 'DESC');
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
                $read = $this->crud->reads("wip_receipts", [], ["checksheet_number" => $post['checksheet_number'], "workorder" => $post['workorder']]);

                if (count($read) > 0) {
                    echo json_encode(array("title" => "Error", "message" => "Duplicate Workorder", "theme" => "error"));
                } else {
                    $send_wip_receipt = $this->crud->create('wip_receipts', $post);
                    $this->crud->update('production_schedules', ["workorder" => $post['workorder']], ["status_wip" => 1]);
                    die($send_wip_receipt);
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function create_label()
    // {
    //     $post = $this->input->post();
    //     $checksheet_number = $post['checksheet_number'];
    //     $qty = $post['qty'];

    //     //Read Label ID
    //     $sqlGetID = $this->db->query("SELECT max(checksheet_label) as kode FROM wip_receipt_labels WHERE checksheet_number = '$checksheet_number'");
    //     $rowID = $sqlGetID->row();
    //     $label = $rowID->kode;
    //     if ($label == NULL) {
    //         $autoID = $checksheet_number .  sprintf("%03s", $label + 1);
    //     } else {
    //         $urutan = (int) substr($label, -3);
    //         $autoID = $checksheet_number . sprintf("%03s", $urutan + 1);
    //     }

    //     //Simpan Label
    //     $arrLabel = [
    //         "checksheet_number" => $checksheet_number,
    //         "checksheet_label" => $autoID,
    //         "qty" => $qty
    //     ];

    //     $send = $this->crud->create('wip_receipt_labels', $arrLabel);
    //     die($send);
    // }

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
        $send = $this->crud->delete('wip_receipt_boxs', ["workorder" => $data['workorder']]);
        // $send = $this->crud->delete('wip_receipt_labels', ["checksheet_number" => $data['checksheet_number']]);
        $send = $this->crud->delete('wip_receipts', ["id" => $data['id']]);
        $update = $this->crud->update('production_schedules', ["workorder" => $data['workorder']], ["status_wip" => 0]);
        echo $send;
    }

    // public function print_label($checksheet_number)
    // {
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     $checksheet_number = base64_decode($checksheet_number);
    //     //Cek Label
    //     $this->db->select('d.number, d.name, d.alias, a.qty, a.checksheet_label, f.trans_date, g.location, c.so_number, i.number as customer_number, d.control_id, d.logo, h.item_cust, j.customer_order_no');// d.description,
    //     $this->db->from('wip_receipt_labels a');
    //     $this->db->join('checksheets b', 'a.checksheet_number = b.number');
    //     $this->db->join('production_schedules c', 'b.workorder = c.workorder');
    //     $this->db->join('item_fg d', 'c.item_fg_id = d.id');
    //     // $this->db->join('uom e', 'd.uom_id = e.id');
    //     $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
    //     // $this->db->join('warehouse_location_items g', 'd.id = g.item_rm_id', 'left');
    //     $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_fg_id', 'left');
    //     $this->db->join('customers i', 'i.id = h.customer_id', 'left');
    //     $this->db->join('sales_orders j', 'c.so_number = j.sales_order_no', 'left');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.status', 0);
    //     $this->db->where('a.checksheet_number', $checksheet_number);
    //     $wip_receipt_labels = $this->db->get()->result_object();

    //     $html = '<html>
    //                 <head>
    //                     <title>' . $checksheet_number . '</title>
    //                     <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
    //                 </head>
    //                 <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
    //     if ($wip_receipt_labels) {
    //         //$html .= '<div style="width: 120mm;">';
    //         $no = 1;
    //         foreach ($wip_receipt_labels as $wip_receipt_label) {
    //             // if ($no == 3) {
    //             //     $no = 1;
    //             // }
    //             // if ($no == 1) {
    //             //     $padding = "padding:0 3mm 1mm 0mm;";
    //             // } else {
    //             //     $padding = "padding:0 0mm 1mm 4mm;";
    //             // }

    //             if ($wip_receipt_label->logo == "0") {
    //                 $img_leoco = '<img style="width:100%;" src="' . base_url("assets/image/BPI.png") . '" />';
    //             } else {
    //                 $img_leoco = '';
    //             }

    //             //Generate QRcode
    //             $qrcodes = $wip_receipt_label->customer_order_no . "|" . $wip_receipt_label->item_cust . "|" . $wip_receipt_label->alias . "|" . $wip_receipt_label->checksheet_label . "|" . $wip_receipt_label->qty . "|" . $wip_receipt_label->trans_date;
    //             $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
    //             $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
    //                             <table id="customers" border="1">
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Code</td>
    //                                     <th colspan="2" style="font-size:15px; text-align:left;"><b>' . $wip_receipt_label->customer_number . '</b></th>
    //                                 </tr>
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">P/O No.</td>
    //                                     <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->customer_order_no . '</b></small></td>
    //                                 </tr>
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
    //                                     <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->item_cust . '</b></small></td>
    //                                 </tr>
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">P/N</td>
    //                                     <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->alias . '</b></small></td>
    //                                 </tr>
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Lot. No</td>
    //                                     <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small></td>
    //                                 </tr>
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Quantity</td>
    //                                     <td style="width:25mm;"><small style="font-size:20px;"><b>' . number_format($wip_receipt_label->qty, 2) . '</b></small></td>
    //                                     <td style="text-align:left;" style="width:25mm" rowspan="3">
    //                                         <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="100"/>
    //                                     </td>
    //                                 </tr>
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm; font-size:10px; text-align:center;">Control ID</td>
    //                                     <td><b style="font-size:10px;">' . $wip_receipt_label->control_id . '</b></td>
    //                                 </tr>
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Date</td>
    //                                     <td><small style="font-size:12px;"><b>' . $wip_receipt_label->trans_date . '</b></small></td>
    //                                 </tr>
    //                                 <tr>
    //                                     <td style="width:15mm; height: 9mm;">' . $img_leoco . '</td>
    //                                     <td colspan="2" style="text-align:center;"><small style="font-size:14px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
    //                                 </tr>
    //                             </table>
    //                         </div>';
    //             $no++;
    //         }
    //         $html .= '</div><script>window.print()</script>';
    //     } else {
    //         $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
    //     }
    //     die($html);
    // }

    public function print_label_box($checksheet_number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number, d.name, d.number_customer, d.alias, a.qty, a.checksheet_label, f.trans_date, i.id as customer_number, i.name as customer_name');//d.description,
        $this->db->from('wip_receipt_boxs a');
        $this->db->join('production_schedules c', 'a.workorder = c.workorder');
        $this->db->join('item_fg d', 'c.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        // $this->db->join('warehouse_location_items g', 'd.id = g.item_rm_id', 'left');
        $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_fg_id', 'left');
        $this->db->join('customers i', 'i.id = h.customer_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.checksheet_number', $checksheet_number);
        $this->db->group_by('a.checksheet_label');
        $wip_receipt_labels = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $checksheet_number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($wip_receipt_labels) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($wip_receipt_labels as $wip_receipt_label) {
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                //     $padding = "padding:0 3mm 1mm 0mm;";
                // } else {
                //     $padding = "padding:0 0mm 1mm 4mm;";
                // }
                //Generate QRcode

                // if ($wip_receipt_label->logo == "0") {
                //     $img_leoco = '<img style="width:100%;" src="' . base_url("assets/image/BPI.png") . '" />';
                // } else {
                //     $img_leoco = '';
                // }

                $qrcodes = $wip_receipt_label->number . "|" . $wip_receipt_label->customer_number . "|" . $wip_receipt_label->alias . "|" . $wip_receipt_label->checksheet_label . "|" . $wip_receipt_label->qty . "|" . $wip_receipt_label->trans_date;
                $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1">
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Code</td>
                                        <th colspan="2" style="font-size:15px; text-align:left;"><b>' . $wip_receipt_label->customer_number . '</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Name.</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->customer_name . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">EBWS P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->number_customer . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Lot. No</td>
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
                                        <td><small style="font-size:12px;"><b>' . $wip_receipt_label->trans_date . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:10px; text-align:center;">Control ID</td>
                                        <td><small style="font-size:12px;"><b></b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm;"></td>
                                        <td colspan="2" style="text-align:center;"><small style="font-size:14px;"><b>ELCON BANSHU</b></small></td>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    // public function print_label_strip($checksheet_number)
    // {
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     $checksheet_number = base64_decode($checksheet_number);
    //     //Cek Label
    //     $this->db->select('d.number, d.name, a.qty, a.checksheet_label, f.trans_date, i.number as customer_number');// d.description,
    //     $this->db->from('wip_receipt_labels a');
    //     $this->db->join('checksheets b', 'a.checksheet_number = b.number');
    //     $this->db->join('production_schedules c', 'b.workorder = c.workorder');
    //     $this->db->join('item_fg d', 'c.item_fg_id = d.id');
    //     // $this->db->join('uom e', 'd.uom_id = e.id');
    //     $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
    //     $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_fg_id', 'left');
    //     $this->db->join('customers i', 'i.id = h.customer_id', 'left');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.status', 0);
    //     $this->db->where('a.checksheet_number', $checksheet_number);
    //     $wip_receipt_labels = $this->db->get()->result_object();

    //     $html = '<html>
    //                 <head>
    //                     <title>' . $checksheet_number . '</title>
    //                     <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
    //                 </head>
    //                 <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 0px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
    //     if ($wip_receipt_labels) {
    //         $html .= '  <div style="width: 90mm; max-height:17mm;">';
    //         $no = 1;
    //         foreach ($wip_receipt_labels as $wip_receipt_label) {

    //             if ($no == 3) {
    //                 $no = 1;
    //             }
    //             if ($no == 1) {
    //                 $margin = "margin:2mm 1mm 2mm 0mm;";
    //             } else {
    //                 $margin = "margin:2mm 0mm 2mm 1mm;";
    //             }

    //             $qrcodes = "ELCON" . $wip_receipt_label->checksheet_label . "-" . $wip_receipt_label->trans_date . "-" . $wip_receipt_label->qty;
    //             $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
    //             $html .= '  <div style="float:left; width:48%; ' . $margin . '">
    //                             <table id="customers" style="border: 1px solid black;">
    //                                 <tr>
    //                                     <td style="width:10mm; text-align:center;" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="50"/></td>
    //                                     <th style="font-size:8px; text-align:left;">ELCON BANSHU</th>
    //                                 </tr>
    //                                 <tr>
    //                                     <th style="font-size:8px; text-align:left;">' . $wip_receipt_label->name . '</th>
    //                                 </tr>
    //                                 <tr>
    //                                     <th style="font-size:8px; text-align:left;">' . date("d-m-Y") . '</th>
    //                                 </tr>
    //                                 <tr>
    //                                     <th style="font-size:8px; text-align:left;">' . $wip_receipt_label->checksheet_label . '</th>
    //                                 </tr>
    //                             </table>
    //                         </div>';
    //             $no++;
    //         }
    //         $html .= '</div><script>window.print()</script>';
    //     } else {
    //         $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
    //     }
    //     die($html);
    // }

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
                                <td><b>' . date("d F Y", strtotime($checksheets[0]['trans_date'])) . '</b></td>

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
                                $period = date("Ym", strtotime($checksheet['trans_date']));
                                $html .= '  <tr>
                                                <td>'.$checksheet['workorder'].'</td>
                                                <td>'.$period.'</td>
                                                <td>'.sprintf("%02s", $checksheet['wp']).'/'.substr($period, -2).'</td>
                                                <td>'.$checksheet['qty'].'</td>
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

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, d.number as item_fg_number, d.name as item_fg_name, d.number_customer, d.uom');
        $this->db->from('wip_receipts a');
        $this->db->join('production_schedules c', 'a.workorder = c.workorder and a.wp = c.wp');
        $this->db->join('item_fg d', 'c.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.checksheet_number', $filter_checksheet);
        $this->db->order_by('a.trans_date', 'DESC');
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
                    <th>Trans Date</th>
                    <th>WP</th>
                    <th>Product No EBWS</th>
                    <th>Product No Customer</th>
                    <th>Description</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Lot Box</th>
                    <th>Label Box</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['checksheet_number'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['wp'] . '</td>
                            <td>' . $data['item_fg_number'] . '</td>
                            <td>' . $data['number_customer'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . number_format($data['lot_box']) . '</td>
                            <td>' . number_format($data['label_box']) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
