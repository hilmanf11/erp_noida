<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Fixeds extends CI_Controller
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
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $row = $this->crud->read("asset_fixeds", [], [], "1", "capitalization_date", "asc");

            $data['filter_from'] = @$row->capitalization_date;

            $this->load->view('template/header', $data);
            $this->load->view('assets/fixeds');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function readPi()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT a.number, b.name
            FROM purchase_invoices a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN account_statements d ON a.account_number = d.account_number
            -- JOIN asset_fixeds c ON a.item_no != c.number AND a.number != c.purchase_invoice_number
            WHERE d.category = 'Other Assets' AND a.number LIKE '%$post%'
            ORDER BY a.number asc");

        $finals = array();
        $item = array();
        foreach ($send as $row) {
            $number = $row->number;
            $invoices = $this->crud->query("SELECT a.*
                FROM purchase_invoices a 
                JOIN account_statements d ON a.account_number = d.account_number
                WHERE d.category = 'Other Assets' AND a.number = '$number' 
                ORDER BY a.item_no asc");

            foreach ($invoices as $invoice) {
                $item_no = $invoice->item_no;
                $fixed = $this->crud->query("SELECT distinct `number` FROM asset_fixeds WHERE `number` like '%$item_no%' and purchase_invoice_number = '$number'");

                if (empty($fixed)) {
                    $item[] = array("item_no" => $item_no);
                }
            }

            if (!empty($item)) {
                array_push($finals, $row);
            }
        }

        echo json_encode($finals);
    }

    //GET FIXED
    public function readProductPi($number)
    {
        $number = base64_decode($number);
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->query("SELECT a.*, c.name as supplier_name
            FROM purchase_invoices a 
            JOIN item_familys b ON a.item_family_id = b.id 
            JOIN suppliers c ON a.supplier_id = c.id
            JOIN account_statements d ON a.account_number = d.account_number
            WHERE b.number != '002'
            AND d.category = 'Other Assets'
            AND a.number = '$number'
            AND a.item_no LIKE '%$post%' GROUP BY a.item_no ORDER BY a.item_no ASC");

        $finals = array();
        foreach ($send as $row) {
            $item_no = $row->item_no;
            $send = $this->crud->query("SELECT distinct `number` FROM asset_fixeds WHERE `number` like '%$item_no%' and purchase_invoice_number = '$number'");
            if (empty($send)) {
                array_push($finals, $row);
            }
        }

        echo json_encode($finals);
    }

    public function readExchangeRates()
    {
        $trans_date = date("Y-m-01", strtotime("-1 month", strtotime($this->input->post('trans_date'))));
        $currency = $this->input->post('currency');

        $records = $this->crud->query("SELECT middle
            FROM exchange_rates
            WHERE start_date = '$trans_date'
            AND currency_from = '$currency'
            AND currency_to = 'IDR'");
        echo json_encode($records);
    }

    public function readExpired($month, $trans_date)
    {
        $trans_date = base64_decode($trans_date);
        echo date("Y-m-d", strtotime("+" . $month . ' months', strtotime($trans_date)));
    }

    //GET FILTER DATA
    public function readNumber($asset_category_number)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $asset_category_number = base64_decode($asset_category_number);
        $send = $this->crud->query("SELECT DISTINCT `number`, `name` FROM asset_fixeds WHERE asset_category_number = '$asset_category_number' and (`number` like '%$post%' or name like '%$post%')");
        echo json_encode($send);
    }

    public function readPurchaseInvoiceNumber()
    {
        $send = $this->crud->query("SELECT DISTINCT purchase_invoice_number FROM asset_fixeds");
        echo json_encode($send);
    }

    public function readSupplier()
    {
        $send = $this->crud->query("SELECT DISTINCT supplier_name FROM asset_fixeds");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $cutoff_from = base64_decode($this->input->get('cutoff_from'));
            $cutoff_to = base64_decode($this->input->get('cutoff_to'));

            $filter_period_from = date("Y-m", strtotime($filter_from));
            $filter_period_to = date("Y-m", strtotime($filter_to));

            //Select Query
            $this->db->select("a.*, 
                d.number as voucher_no,
                (CASE WHEN a.capitalization_date is null THEN a.posting_date WHEN a.capitalization_date = '0000-00-00' THEN '' ELSE a.capitalization_date END) as capitalization_date,
                (CASE WHEN a.account_number is null THEN b.account_number ELSE a.account_number END) as account_number,
                b.name as asset_category_name,
                b.usefull, b.shift as shift_factor,
                (CASE WHEN (a.cost - (COALESCE(c.depreciation_acc, 0))) > 0 THEN 0 ELSE 1 END) as status_expired");
            $this->db->from('asset_fixeds a');
            $this->db->join('item_familys b', 'a.item_family_id = b.id');
            $this->db->join("(SELECT asset_no, SUM(debit) as depreciation_acc FROM asset_journals WHERE periode BETWEEN '$filter_period_from' and '$filter_period_to' GROUP BY asset_no) c", 'a.number = c.asset_no', 'left');
            $this->db->join("(SELECT distinct number, document_no FROM journal_postings) d", 'a.purchase_invoice_no = d.document_no', 'left');
            $this->db->where("a.capitalization_date >=", $filter_from);
            $this->db->where("a.capitalization_date <=", $filter_to);
            $this->db->group_by('a.id');
            $records = $this->db->get()->result_array();

            $datas = [];
            $total_cost = 0;
            $total_acq_period = 0;
            $total_acq_closing = 0;
            $total_residual = 0;
            $total_addition_op = 0;
            $total_addition = 0;
            $total_addition_total = 0;
            $total_accum_op = 0;
            $total_accum_close = 0;
            $total_wdv = 0;
            $total_wdv_close = 0;
            $total_depreciation = 0;
            foreach ($records as $record) {
                $trans_date = date_create($record['capitalization_date']);
                $start_date = date_create($cutoff_from);
                $end_date = date_create($cutoff_to);

                if ($trans_date < $start_date) {
                    $total_day = (date_diff($start_date, $end_date)->days + 1);
                } else {
                    $total_day = (date_diff($trans_date, $end_date)->days + 1);
                }

                if ($record['usefull'] == 0) {
                    $estimate_day = 1;
                } else {
                    $estimate_day = ($record['usefull'] * 365);
                }

                if ($record['capitalization_date'] < $cutoff_from) {
                    $cost = $record['cost'];
                    $acq_period = 0;
                } elseif ($record['capitalization_date'] >= $cutoff_from) {
                    $cost = 0;
                    $acq_period = $record['cost'];
                } else {
                    $cost = 0;
                    $acq_period = 0;
                }

                if ($cost != 0 && $estimate_day > 1) {
                    $addition_op = (round(((($cost - (($cost + $acq_period) * (5 / 100))) / $estimate_day) * $total_day) * $record['shift_factor'], 2));
                } else {
                    $addition_op = 0;
                }

                if ($acq_period != 0 && $estimate_day > 1) {
                    $addition = (round(((($acq_period - (($cost + $acq_period) * (5 / 100))) / $estimate_day) * $total_day) * $record['shift_factor'], 2));
                } else {
                    $addition = 0;
                }

                $addition_total = ($addition_op + $addition);
                $datas[] = array(
                    "id" => $record['id'],
                    "number" => $record['number'],
                    "name" => $record['name'],
                    "asset_category_name" => $record['asset_category_name'],
                    "account_number" => $record['account_number'],
                    "purchase_invoice_no" => $record['purchase_invoice_no'],
                    "supplier_name" => $record['supplier_name'],
                    "capitalization_date" => $record['capitalization_date'],
                    "trans_date" => $record['trans_date'],
                    "shift" => $record['shift_factor'],
                    "uom" => $record['uom'],
                    "qty" => $record['qty'],
                    "cost" => $cost,
                    "acq_period" => $acq_period,
                    "acq_closing" => ($cost + $acq_period),
                    "residual" => round(($cost + $acq_period) * (5 / 100)),
                    "estimate_year" => ($record['usefull']),
                    "estimate_month" => ($record['usefull'] * 12),
                    "estimate_day" => ($record['usefull'] * 365),
                    "asset_period" => $total_day,
                    "addition_op" => $addition_op,
                    "addition" => $addition,
                    "addition_total" => $addition_total,
                    "accum_op" => $record['accum_op'],
                    "accum_close" => ($addition_total + $record['accum_op']),
                    "wdv" => ($cost - $record['accum_op']),
                    "wdv_close" => (($cost + $acq_period) - ($addition_total + $record['accum_op'])),
                    "posting_date" => $record['posting_date'],
                    "expired_date" => $record['expired_date'],
                    "depreciation" => $record['depreciation'],
                    "departement" => $record['departement'],
                    "voucher_no" => $record['voucher_no'],
                    "status" => $record['status'],
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date'],
                );

                $total_cost += $cost;
                $total_acq_period += $acq_period;
                $total_acq_closing += ($cost + $acq_period);
                $total_residual += round(($cost + $acq_period) * (5 / 100));
                $total_addition_op += round($addition_op, 2);
                $total_addition += round($addition, 2);
                $total_addition_total += round($addition_total, 2);
                $total_accum_op += round($record['accum_op'], 2);
                $total_accum_close += round(($addition_total + $record['accum_op']), 2);
                $total_wdv += round(($cost - $record['accum_op']), 2);
                $total_wdv_close += round((($cost + $acq_period) - ($addition_total + $record['accum_op'])), 2);
                $total_depreciation += round($record['depreciation'], 2);
            }

            //Mapping Data
            $result['footer'] = array([
                "number" => "GRAND TOTAL",
                "cost" => $total_cost,
                "acq_period" => $total_acq_period,
                "acq_closing" => $total_acq_closing,
                "residual" => $total_residual,
                "addition_op" => $total_addition_op,
                "addition" => $total_addition,
                "addition_total" => $total_addition_total,
                "accum_op" => $total_accum_op,
                "accum_close" => $total_accum_close,
                "wdv" => $total_wdv,
                "wdv_close" => $total_wdv_close,
                "depreciation" => $total_depreciation,
            ]);
            $result['total'] = count($datas);
            $result = array_merge($result, ['rows' => $datas]);
            echo json_encode($result);
        }
    }

    public function numberId($number)
    {
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM asset_fixeds WHERE `number` like '%$number%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }

        echo $datenow . "-" . $autoID;
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = "[]";
            for ($i = 0; $i < $post['qty']; $i++) {

                if ($post['qty'] > 1) {
                    $asset_no = $post['number'];
                    $sqlGetID = $this->db->query("SELECT max(`number`) as kode FROM asset_fixeds WHERE `number` like '%$asset_no%'");
                    $rowID = $sqlGetID->row();
                    $kode = $rowID->kode;

                    if ($kode == NULL) {
                        $number = $post['number'] . sprintf("%03d", ($i + 1));
                    } else {
                        $urutan = (int) substr($kode, -3);
                        $urutan++;
                        $number = $post['number'] . sprintf("%03s", $urutan);
                    }
                } else {
                    $number = $post['number'];
                }

                $data = array(
                    "asset_category_number" => $post['asset_category_number'],
                    "purchase_invoice_number" => $post['purchase_invoice_number'],
                    "supplier_name" => $post['supplier_name'],
                    "number" => $number,
                    "name" => $post['name'],
                    "trans_date" => $post['trans_date'],
                    "qty" => 1,
                    "currency" => $post['currency'],
                    "cost" => $post['cost'],
                    "estimate_month" => $post['estimate_month'],
                    "expired_date" => $post['expired_date'],
                    "estimate_year" => $post['estimate_year'],
                    "depreciation" => $post['depreciation'],
                    "remarks" => $post['remarks'],
                    "method" => $post['method'],
                    "departement" => $post['departement'],
                    "location" => $post['location'],
                    "total" => $post['total'],
                );

                $send   = $this->crud->create('asset_fixeds', $data);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('asset_fixeds', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('asset_fixeds', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_family_number' => $data->val($i, 2),
                'purchase_invoice_no' => $data->val($i, 3),
                'supplier_id' => $data->val($i, 4),
                'item_rm_id' => $data->val($i, 5),
                'number' => $data->val($i, 6),
                'name' => $data->val($i, 7),
                'trans_date' => $data->val($i, 8),
                'capitalization_date' => $data->val($i, 9),
                'estimate_year' => $data->val($i, 10),
                'uom' => $data->val($i, 11),
                'qty' => $data->val($i, 12),
                'cost' => $data->val($i, 13),
                'accum_op' => $data->val($i, 14),
                'posting_date' => $data->val($i, 15),
                'departement' => $data->val($i, 16),
                'plus' => $data->val($i, 17)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/asset_fixeds.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/asset_fixeds.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/asset_fixeds.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Product Family
            $item_family = $this->crud->read('item_familys', [], ["number" => $data['item_family_number']]);
            // $item_category = $this->crud->read('item_categories', [], ["id" => @$item_family->item_category_id]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);
            $supplier = $this->crud->read('suppliers', [], ["id" => $data['supplier_id']]);

            if ($item_family->number == "CW") {
                $fixed_assets = $this->crud->read('asset_fixeds', [], ["number" => $data['number'], "name" => $data['name'], "purchase_invoice_no" => $data['purchase_invoice_no']]);
            } else {
                $fixed_assets = $this->crud->read('asset_fixeds', [], ["number" => $data['number']]);
            }

            if ($data['plus'] == "MINUS") {
                $kali = -1;
            } else {
                $kali = 1;
            }

            if (empty($data['cost']) || empty($data['estimate_year'])) {
                $cost = ($data['cost'] * $kali);
                $depreciation = 0;
                $total = 0;
            } else {
                $cost = ($data['cost'] * $kali);
                $depreciation = ($data['cost'] / ($data['estimate_year'] * 12) * $kali);
                $total = (($data['qty'] * $data['cost']) * $kali);
            }

            $dataFinal = array(
                "item_family_id" => @$item_family->id,
                "purchase_invoice_no" => $data['purchase_invoice_no'],
                "supplier_id" => $data['supplier_id'],
                "supplier_name" => @$supplier->name,
                // "item_rm_id" => $data['item_rm_id'],
                "number" => $data['number'],
                "name" => $data['name'],
                "shift" => @$item_family->shift,
                "trans_date" => $data['trans_date'],
                "capitalization_date" => $data['capitalization_date'],
                "uom" => $data['uom'],
                "qty" => $data['qty'],
                "currency" => @$supplier->currency,
                "cost" => $cost,
                "estimate_day" => ($data['estimate_year'] * 365),
                "estimate_month" => ($data['estimate_year'] * 12),
                "estimate_year" => $data['estimate_year'],
                "expired_date" => date("Y-m-d", strtotime("+" . ($data['estimate_year'] * 12) . ' months', strtotime($data['trans_date']))),
                "depreciation" => $depreciation,
                "method" => "UPLOAD",
                "departement" => $data['departement'],
                "total" => $total,
                "accum_op" => ($data['accum_op'] * $kali),
                "posting_date" => $data['posting_date'],
            );

            if (empty($item_family->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $data['item_family_number'] . " Not Found", "theme" => "error"));
                // } elseif (empty($item_rm->id)) {
                //     echo json_encode(array("title" => "Not Found", "message" => "Part No EBWS " . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($supplier->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier ID " . $data['supplier_id'] . " Not Found", "theme" => "error"));
                // } elseif (!empty($fixed_assets->id)) {
                //     $send = $this->crud->update('asset_fixeds', ["id" => $fixed_assets->id], $dataFinal);
                //     echo $send;
            } else {
                $send = $this->crud->create('asset_fixeds', $dataFinal, "logs");
                echo $send;
            }
        }
    }

    public function print_label($number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $number = base64_decode($number);
        //Cek Label

        $this->db->select('*');
        $this->db->from('asset_fixeds');
        $this->db->where('number', $number);
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 44mm;">';
            $no = 1;
            foreach ($records as $record) {
                for ($i = 0; $i < $record->qty; $i++) {
                    if ($no == 1) {
                        $padding = "padding:2mm 3mm 3mm 2mm;";
                    } else {
                        $padding = "padding:2mm 3mm 3mm 2mm;";
                    }

                    //Generate QRcode
                    $this->createQrcode($record->number, "assets/image/qrcode/");
                    $html .= '  <div style="width: 44mm; max-height:40mm; float:left; ' . $padding . '">
                                    <table id="customers" border="1" style="margin-bottom:20px;">
                                        <tr>
                                            <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
                                        </tr>
                                        <tr>
                                            <th style="text-align:center;">
                                                <img src="' . base_url('assets/image/qrcode/' . $record->number . '.png') . '" width="50"/>
                                            </th>
                                            <th style="text-align:left">
                                                <small style="font-size:7px;">Asset Code</small><br>
                                                <small style="font-size:12px;"><b>' . $record->number . "/" . $no . '</b></small><br>
                                                <small style="font-size:7px;">Invoice No</small><br>
                                                <small style="font-size:10px;">' . $record->purchase_invoice_no . '</small>
                                            </th>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="height:35px;">
                                                <small style="font-size:7px;">Asset Name</small><br>
                                                <small style="font-size:12px;"><b>' . $record->name . '</b></small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:left" width="150">
                                                <small>Quantity</small><br>
                                                <center>
                                                    <b style="font-size:20px;">' . $no . "/" . number_format($record->qty, 0) . '</b></small><br>
                                                    <small style="font-size:10px;"><b>' . $record->uom . '</b></small>
                                                </center>
                                            </td>
                                            <td style="text-align:left" width="200">
                                                <small>Trans Date</small><br><b style="font-size:10px;">' . $record->trans_date . '</b><br>
                                            </td>
                                        </tr>
                                    </table>
                                </div>';
                    $no++;
                }
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=asset_fixeds_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_number = base64_decode($this->input->get('filter_number'));
        $filter_category = base64_decode($this->input->get('filter_category'));
        $filter_estimate = base64_decode($this->input->get('filter_estimate'));
        $filter_purchase_invoice_number = base64_decode($this->input->get('filter_purchase_invoice_number'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));

        $filter_period_from = date("Y-m", strtotime($filter_from));
        $filter_period_to = date("Y-m", strtotime($filter_to));

        //Select Query
        $this->db->select("a.*, 
            b.accout_number,
            b.name as asset_category_name, 
            b.type as asset_category_type, 
            PERIOD_DIFF(DATE_FORMAT('$filter_to', '%Y%m'), DATE_FORMAT(trans_date, '%Y%m')) AS qty_month,
            (CASE WHEN a.capitalization_date is null THEN a.posting_date WHEN a.capitalization_date = '0000-00-00' THEN '' ELSE a.capitalization_date END) as capitalization_date,
            (COALESCE(c.depreciation_acc, 0) + a.depreciation_accumulate) as depreciation_acc,
            (a.cost - (c.depreciation_acc + a.depreciation_accumulate)) as book_value,
            (CASE WHEN (a.cost - (c.depreciation_acc + a.depreciation_accumulate)) > 0 THEN 0 ELSE 1 END) as status_expired");
        $this->db->from('asset_fixeds a');
        $this->db->join('asset_categories b', 'a.asset_category_number = b.number');
        $this->db->join("(SELECT asset_no, SUM(debit) as depreciation_acc FROM asset_journals WHERE periode BETWEEN '$filter_period_from' and '$filter_period_to' GROUP BY asset_no) c", 'a.number = c.asset_no', 'left');
        $this->db->where("a.capitalization_date >=", $filter_from);
        $this->db->where("a.capitalization_date <=", $filter_to);
        if ($filter_category != "") {
            $this->db->where('a.asset_category_number', $filter_category);
        }
        if ($filter_number != "") {
            $this->db->where('a.number', $filter_number);
        }
        if ($filter_estimate != "") {
            $this->db->where('a.estimate_year', $filter_estimate);
        }
        if ($filter_purchase_invoice_number != "") {
            $this->db->where('a.purchase_invoice_number', $filter_purchase_invoice_number);
        }
        if ($filter_supplier != "") {
            $this->db->where('a.supplier_name', $filter_supplier);
        }
        $this->db->group_by('a.id');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' .  $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br>
                <br>
                <h3 style="margin:0;">ASSET FIXED</h3>
                <small>Period ' . $filter_from . ' to ' . $filter_to . '</small>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Asset No</th>
                <th>Asset Name</th>
                <th>Asset Category</th>
                <th>Account No</th>
                <th>Asset Type</th>
                <th>Purchase Invoice</th>
                <th>Supplier</th>
                <th>Invoice Date</th>
                <th>Capitalization Date</th>
                <th>Qty</th>
                <th>Cost</th>
                <th>EST. <br>Year</th>
                <th>EST. <br>Month</th>
                <th>Expired Date</th>
                <th>Depreciation</th>
                <th>Depreciation Accumulation</th>
                <th>Book Value</th>
                <th>Depreciation<br>Method</th>
                <th>Departement</th>
                <th>Location</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status_expired'] == 1) {
                $status = "EXPIRED";
            } else {
                $status = "ACTIVE";
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['asset_category_name'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['asset_category_type'] . '</td>
                        <td>' . $data['purchase_invoice_number'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['trans_date'] . '</td>
                        <td>' . $data['capitalization_date'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['cost'] . '</td>
                        <td>' . $data['estimate_year'] . '</td>
                        <td>' . $data['estimate_month'] . '</td>
                        <td>' . $data['expired_date'] . '</td>
                        <td>' . $data['depreciation'] . '</td>
                        <td>' . $data['depreciation_acc'] . '</td>
                        <td>' . $data['book_value'] . '</td>
                        <td>' . $data['method'] . '</td>
                        <td>' . $data['departement'] . '</td>
                        <td>' . $data['location'] . '</td>
                        <td>' . $status . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
