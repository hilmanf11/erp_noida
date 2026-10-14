<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_invoices extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/purchase_invoices');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $number = base64_decode($number);
        $this->db->select("a.*, a.item_no as item_rm_number, a.item_name as item_rm_name, a.uom, a.currency, a.account_number, d.name as item_family_name, f.hsn_code,
            c.account_name, 'INR' as currency_local, b.tds, k.individual,
            (CASE WHEN e.middle != '' THEN (e.middle * a.total) ELSE (a.rate * a.total) END) as total_local");
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_familys d', 'a.item_family_id = d.id', 'left');
        $this->db->join('account_coa c', 'a.account_number = c.account_number', 'left');
        $this->db->join('exchange_rates e', "e.start_date = DATE_FORMAT(a.trans_date, '%Y-%m-01') and e.currency_from = a.currency", 'left');
        $this->db->join('item_rm f', 'a.item_rm_id = f.id', 'left');
        $this->db->join('tds k', "b.tds = k.section", 'left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.number', $number);
        $this->db->order_by('a.item_no', 'asc');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        $total_igst = 0;
        $total_sgst = 0;
        $total_cgst = 0;
        foreach ($records as $record) {
            $total_sub += $record['total'];
            $total_igst += $record['igst'];
            $total_sgst += $record['sgst'];
            $total_cgst += $record['cgst'];
        }

        $datas['rows'] = $records;
        $datas['total_sub'] = $total_sub;
        $datas['total_igst'] = $total_igst;
        $datas['total_sgst'] = $total_sgst;
        $datas['total_cgst'] = $total_cgst;
        $datas['total_tcs'] = @$records[0]['total_tcs'];
        $datas['total_tds'] = @$records[0]['total_tds'];
        $datas['total_freight'] = @$records[0]['total_freight'];
        $datas['total_grand'] = ($total_sub + $total_igst + $total_sgst + $total_cgst + @$records[0]['total_tcs'] - @$records[0]['total_tds']);
        $datas['tds'] = @$records[0]['tds'];
        $datas['individual'] = @$records[0]['individual'];

        file_put_contents('purchase_invoicing.json', json_encode($records));
        die(json_encode($datas));
    }

    public function readSupplierCoa()
    {
        $supplier_id = $this->input->post('supplier_id');
        $journals = $this->crud->query("SELECT b.account_number, b.account_name
            FROM account_balance_suppliers a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.supplier_id = '$supplier_id'");

        echo json_encode($journals);
    }

    public function readJournal($journal_type_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $journal_type_id = base64_decode($journal_type_id);

        $journals = $this->crud->query("SELECT a.*, a.flag, b.account_name FROM journal_setups a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.account_number LIKE '%$post%' and a.journal_type_id = '$journal_type_id' ORDER BY a.flag ASC");

        $flag = 1;
        foreach ($journals as $journal) {
            $arr[] = array(
                "account_number" => $journal->account_number,
                "account_name" => $journal->account_name,
                "debit" => "0.00",
                "credit" => "0.00",
                "flag" => $flag,
            );

            $flag++;
        }

        echo json_encode($arr);
    }

    public function readJournals($number)
    {
        $number = base64_decode($number);
        $reads = $this->crud->reads("purchase_invoice_journals", [], ["number" => $number], "", "flag", "asc");
        echo json_encode($reads);
    }

    public function calculateJournal()
    {
        $journals = json_decode(file_get_contents("json/purchase_invoice_journals.json"), true);
        $jsonDatas = json_decode(file_get_contents("json/purchase_invoices.json"), true);

        if (count($journals) > 0) {
            $flag = 1;
            $mergedData = array();
            foreach ($jsonDatas as $jsonData) {
                $account_number = $jsonData["account_number"];
                $account_name = $jsonData["account_name"];
                $account_type = $jsonData["account_type"];
                $total = $jsonData["total"];

                if (isset($mergedData[$account_number])) {
                    // Jika nomor akun sudah ada dalam hasil penggabungan, tambahkan nilai total ke nomor akun tersebut
                    if ($jsonData['account_type'] == "DEBIT") {
                        $mergedData[$account_number]["debit"] += $total;
                    } elseif ($jsonData['account_type'] == "CREDIT") {
                        $mergedData[$account_number]["credit"] += $total;
                    }
                } else {
                    // Jika nomor akun belum ada dalam hasil penggabungan, tambahkan data baru
                    if ($jsonData['account_type'] == "DEBIT") {
                        $mergedData[$account_number] = array(
                            "account_number" => $account_number,
                            "account_name" => $account_name,
                            "account_type" => $account_type,
                            "debit" => $total,
                            "credit" => 0,
                            "flag" => $flag,
                        );
                    } elseif ($jsonData['account_type'] == "CREDIT") {
                        $mergedData[$account_number] = array(
                            "account_number" => $account_number,
                            "account_name" => $account_name,
                            "account_type" => $account_type,
                            "debit" => 0,
                            "credit" => $total,
                            "flag" => $flag,
                        );
                    }

                    $flag++;
                }
            }

            $array1 = array_values($mergedData);
            $flag = 1;
            foreach ($journals as $journal) {
                $array2[] = array(
                    "account_number" => $journal['account_number'],
                    "account_name" => $journal['account_name'],
                    "debit" => round(@$journal['debit'], 2),
                    "credit" => round(@$journal['credit'], 2),
                    "flag" => $flag,
                );

                $flag++;
            }

            $existingAccountNumbers = array_values(array_unique(array_column($array2, 'account_number')));
            foreach ($array1 as $item1) {
                if (!in_array($item1['account_number'], $existingAccountNumbers)) {
                    $array2[] = $item1;
                }
            }

            foreach ($array2 as $i => $item2) {
                foreach ($array1 as $item1) {
                    if ($item2['account_number'] === $item1['account_number']) {
                        $array2[$i] = array_merge($item2, $item1);
                        break;
                    }
                }
            }

            usort($array2, function ($a, $b) {
                return $a['flag'] <=> $b['flag']; // ascending
            });
        }

        echo json_encode($array2);
    }

    public function readDueDate($trans_date, $payment_term)
    {
        $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime(base64_decode($trans_date))));
        die($due_date);
    }

    public function readReceipt($type = "purchase")
    {
        $supplier_id = $this->input->get('supplier_id');

        if ($type == "purchase" || $type == "other") {
            $dp = "and d.total_dp = 0";
        } else if ($type == "dp") {
            $dp = "and d.total_dp > 0";
        }

        if ($type == "purchase") {
            $records = $this->crud->query("SELECT a.receipt_no, a.receipt_id, a.taxes, a.total_dp, a.bc_document, a.awb_no, a.journal_type_id, a.journal_type_name, SUM(a.qty_label) AS qty_label, SUM(a.total_label) AS total_label FROM (
                SELECT a.receipt_no, a.receipt_id, d.taxes, d.total_dp, a.bc_document, a.awb_no, f.journal_type_id, f.journal_type_name, a.qty_label, a.total_label
                FROM (SELECT a.*, b.total_label
                    FROM purchase_order_receipts a 
                    LEFT JOIN (SELECT receipt_id, COUNT(id) AS total_label FROM scan_item_receipts GROUP BY receipt_id) b ON a.receipt_id = b.receipt_id
                    WHERE a.status = 0 AND a.supplier_id = '$supplier_id'
                ) a
                JOIN item_rm b ON a.item_rm_id = b.id
                JOIN purchase_orders d ON a.po_no = d.po_no
                LEFT JOIN (SELECT a.name AS journal_type_name, a.id AS journal_type_id, b.account_number FROM journal_types a JOIN journal_setups b ON a.id = b.journal_type_id WHERE a.module LIKE '%PURCHASE INVOICING%' GROUP BY a.id, b.account_number) f ON b.account_number = f.account_number
                GROUP BY a.receipt_id 
                ORDER BY a.created_date DESC) a
                GROUP BY a.receipt_no
                HAVING SUM(a.qty_label) = SUM(a.total_label)");
        } else if ($type == "other") {
            $records = $this->crud->query("SELECT a.receipt_no, d.taxes, d.total_dp, a.bc_document, a.awb_no, (CASE WHEN e.journal_type_id is null THEN f.journal_type_id ELSE e.journal_type_id END) as journal_type_id, f.journal_type_name
                FROM purchase_order_receipt_others a
                JOIN item_rm b ON a.item_rm_id = b.id
                JOIN item_familys c ON b.item_family_id = c.id
                JOIN purchase_order_others d ON a.po_no = d.po_no
                LEFT JOIN (select a.number, b.id, b.name, c.account_number, c.account_name, d.journal_type_id from assets a join item_familys b ON a.item_family_id = b.id join account_coa c ON b.account_number = c.account_number join journal_setups d ON b.account_number = d.account_number group by a.number) e ON a.asset_code = e.number
                LEFT JOIN (select a.name as journal_type_name, a.id as journal_type_id, b.account_number from journal_types a JOIN journal_setups b ON a.id = b.journal_type_id WHERE a.module like '%PURCHASE INVOICING%' GROUP BY a.id, b.account_number) f ON b.account_number = f.account_number
                WHERE a.supplier_id = '$supplier_id' and a.status = '0' and a.store = '1' $dp
                GROUP BY a.receipt_no 
                ORDER BY a.created_date desc");
        } else {
            $records = $this->crud->query("SELECT po_no as receipt_no, taxes, total_dp
                FROM purchase_order_services
                WHERE supplier_id = '$supplier_id' and `status` = '0'
                GROUP BY po_no 
                ORDER BY po_no desc");
        }

        echo json_encode($records);
    }

    public function readPurchaseInvoice()
    {
        $data = $this->crud->query("SELECT DISTINCT `number` FROM purchase_invoices WHERE `status` = '0' ORDER BY `number` ASC");
        echo json_encode($data);
    }

    public function readPurchaseReceipt()
    {
        $data = $this->crud->query("SELECT DISTINCT `por_no` FROM purchase_invoices WHERE `status` = '0' ORDER BY `por_no` ASC");
        echo json_encode($data);
    }

    public function readPurchaseOrder()
    {
        $data = $this->crud->query("SELECT DISTINCT `po_no` FROM purchase_invoices WHERE `status` = '0' ORDER BY `po_no` ASC");
        echo json_encode($data);
    }

    public function readInvoice()
    {
        $data = $this->crud->query("SELECT DISTINCT `invoice_no` FROM purchase_invoices WHERE `status` = '0' ORDER BY `invoice_no` ASC");
        echo json_encode($data);
    }

    // public function number($id_menu)
    // {
    //     $datenow    = "PI-" . date("Ymd", strtotime(base64_decode($trans_date)));
    //     $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM purchase_invoices WHERE `number` like '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%04s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -4);
    //         $urutan++;
    //         $autoID = sprintf("%04s", $urutan);
    //     }
    //     echo $datenow . "-" . $autoID;
    // }

    public function number($id_menu, $receipt_date)
    {
        // $datenow = date("Y-m-d");
        $datenow = base64_decode($receipt_date);
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            //
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(`number`) as kode FROM purchase_invoices WHERE `number` like '%$whereDate%'");
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

    public function numberInvoice($type)
    {
        if ($type == "dp") {
            $datenow    = "INVWDP";
        } else {
            $datenow    = "INVTMP";
        }

        echo $datenow . "-" . time();
    }

    public function datatablesTemp()
    {
        $type = $this->input->get('type');
        $por_no = base64_decode($this->input->get('por_no'));
        $por_no_ex = explode(",", $por_no);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        if ($type == "purchase") {
            $this->db->select("a.receipt_no as por_no, a.po_no, '' as asset_code, c.id as item_rm_id, c.number as item_rm_number, c.name as item_rm_name, c.uom, c.hsn_code, b.currency, 
                a.qty_receipt as qty, f.price, f.discount, 'INR' as currency_local, h.account_number, i.account_name, h.id  as item_family_id, h.name as item_family_name,
                b.tds, b.state, k.individual, b.type as supplier_type,
                ((a.qty_receipt * f.price) * COALESCE(j.igst, 0) / 100) as igst,
                ((a.qty_receipt * f.price) * COALESCE(j.sgst, 0) / 100) as sgst,
                ((a.qty_receipt * f.price) * COALESCE(j.cgst, 0) / 100) as cgst,
                (a.qty_receipt * f.price) as total,
                (CASE WHEN g.middle is null THEN 1 ELSE g.middle END) as rate");
            $this->db->from('purchase_order_receipts a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id', 'left');
            $this->db->join('purchase_orders f', 'a.po_no = f.po_no and b.id = f.supplier_id and c.id = f.item_rm_id');
            $this->db->join('exchange_rates g', "g.start_date = DATE_FORMAT(a.receipt_date, '%Y-%m-01') and g.currency_from = b.currency", 'left');
            $this->db->join('item_familys h', "c.item_family_id = h.id", 'left');
            $this->db->join('account_coa i', "h.account_number = i.account_number", 'left');
            $this->db->join('hsn j', "c.hsn_code = j.number", 'left');
            $this->db->join('tds k', "b.tds = k.section", 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where_in('a.receipt_no', $por_no_ex);
            $this->db->group_by('a.id');
            $this->db->order_by('a.receipt_no', 'asc');
            $records = $this->db->get()->result_array();
        } elseif ($type == "others") {
            $this->db->select("a.receipt_no as por_no, a.po_no, a.asset_code, c.id as item_rm_id, c.number as item_rm_number, c.name as item_rm_name, c.uom, c.hsn_code, b.currency, 
                a.qty_receipt as qty, f.price, f.discount, 'INR' as currency_local, 
                (CASE WHEN l.account_number is null THEN h.account_number ELSE l.account_number END) as account_number, 
                (CASE WHEN l.account_name is null THEN i.account_name ELSE l.account_name END) as account_name,
                (CASE WHEN l.id is null THEN h.id ELSE l.id END) as item_family_id,
                (CASE WHEN l.name is null THEN h.name ELSE l.name END) as item_family_name,
                b.tds, b.state, k.individual, b.type as supplier_type,
                ((a.qty_receipt * f.price) * COALESCE(j.igst, 0) / 100) as igst,
                ((a.qty_receipt * f.price) * COALESCE(j.sgst, 0) / 100) as sgst,
                ((a.qty_receipt * f.price) * COALESCE(j.cgst, 0) / 100) as cgst,
                (a.qty_receipt * f.price) as total,
                (CASE WHEN g.middle is null THEN 1 ELSE g.middle END) as rate");
            $this->db->from('purchase_order_receipt_others a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id', 'left');
            $this->db->join('purchase_order_others f', 'a.po_no = f.po_no and b.id = f.supplier_id and c.id = f.item_rm_id');
            $this->db->join('exchange_rates g', "g.start_date = DATE_FORMAT(a.receipt_date, '%Y-%m-01') and g.currency_from = b.currency", 'left');
            $this->db->join('item_familys h', "c.item_family_id = h.id", 'left');
            $this->db->join('account_coa i', "h.account_number = i.account_number", 'left');
            $this->db->join('hsn j', "c.hsn_code = j.number", 'left');
            $this->db->join('tds k', "b.tds = k.section", 'left');
            $this->db->join("(select a.number, b.id, b.name, c.account_number, c.account_name from assets a join item_familys b ON a.item_family_id = b.id join account_coa c ON b.account_number = c.account_number group by a.number) l", "a.asset_code = l.number", 'left');
            $this->db->where('a.deleted', 0);
            // $this->db->where('a.status', 0);
            $this->db->where_in('a.receipt_no', $por_no_ex);
            $this->db->group_by('a.id');
            $this->db->order_by('a.receipt_no', 'asc');
            $records = $this->db->get()->result_array();
        } else {
            $this->db->select("a.po_no as por_no, a.po_no, '-' as asset_code, 'null' as item_rm_id, a.item_name as item_rm_number, a.item_name as item_rm_name, 'Pcs' as uom, a.hsn_code, b.currency, 
                a.qty, a.price, a.discount, 'INR' as currency_local, '' as account_number, '' as account_name, '' as item_family_id, '' as item_family_name,
                b.tds, b.state, k.individual, b.type as supplier_type,
                ((a.qty * a.price) * COALESCE(j.igst, 0) / 100) as igst,
                ((a.qty * a.price) * COALESCE(j.sgst, 0) / 100) as sgst,
                ((a.qty * a.price) * COALESCE(j.cgst, 0) / 100) as cgst,
                (a.qty * a.price) as total,
                (CASE WHEN g.middle is null THEN 1 ELSE g.middle END) as rate");
            $this->db->from('purchase_order_services a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('exchange_rates g', "g.start_date = DATE_FORMAT(a.po_no, '%Y-%m-01') and g.currency_from = b.currency", 'left');
            $this->db->join('hsn j', "a.hsn_code = j.number", 'left');
            $this->db->join('tds k', "b.tds = k.section", 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where_in('a.po_no', $por_no_ex);
            $this->db->group_by('a.id');
            $this->db->order_by('a.po_no', 'asc');
            $records = $this->db->get()->result_array();
        }

        $rate = 0;
        $total_price = 0;
        $total_sub = 0;
        $total_sub2 = 0;
        $total_sub3 = 0;
        $total_qty = 0;
        $total_igst = 0;
        $total_cgst = 0;
        $total_sgst = 0;
        $id = 1;
        $obj = array();
        foreach ($records as $record) {
            $total_sub += $record['total'];
            $total_qty += $record['qty'];

            if ($record['state'] == $config->state) {
                $igst = 0;
                $cgst = $record['cgst'];
                $sgst = $record['sgst'];
                $total2 = ($record['total'] + $record['cgst'] + $record['sgst']);
                $total2_local = ($total2 * $record['rate']);
                $total_igst += 0;
                $total_cgst += $record['cgst'];
                $total_sgst += $record['sgst'];
            } else if ($record['supplier_type'] == "LOCAL") {
                $igst = $record['igst'];
                $cgst = 0;
                $sgst = 0;
                $total2 = ($record['total'] + $record['igst']);
                $total2_local = ($total2 * $record['rate']);
                $total_igst += $record['igst'];
                $total_cgst += 0;
                $total_sgst += 0;
            } else {
                $igst = 0;
                $cgst = 0;
                $sgst = 0;
                $total2 = ($record['total']);
                $total2_local = ($total2 * $record['rate']);
                $total_igst += 0;
                $total_cgst += 0;
                $total_sgst += 0;
            }

            $obj[] = array(
                "no_id" => $id,
                "asset_code" => $record['asset_code'],
                "por_no" => $record['por_no'],
                "po_no" => $record['po_no'],
                "item_rm_id" => $record['item_rm_id'],
                "item_rm_number" => $record['item_rm_number'],
                "item_rm_name" => $record['item_rm_name'],
                "item_family_id" => $record['item_family_id'],
                "item_family_name" => $record['item_family_name'],
                "uom" => $record['uom'],
                "hsn_code" => $record['hsn_code'],
                "currency" => $record['currency'],
                "currency_local" => $record['currency_local'],
                "qty" => $record['qty'],
                "discount" => $record['discount'],
                "price" => $record['price'],
                "rate" => $record['rate'],
                "total" => $record['total'],
                "total2" => ($total2),
                "igst" => $igst,
                "cgst" => $cgst,
                "sgst" => $sgst,
                "total_local" => $total2_local,
                "account_number" => $record['account_number'],
                "account_name" => $record['account_name'],
                "account_type" => "DEBIT"
            );

            $rate = $record['rate'];
            $total_sub2 += $total2;
            $total_sub3 += $total2_local;
            $total_price += $record['price'];
            $id++;
        }

        $arr['footer'] = array([
            "action" => "0",
            "por_no" => "GRAND TOTAL",
            "qty" => $total_qty,
            "total" => $total_sub,
            "igst" => $total_igst,
            "cgst" => $total_cgst,
            "sgst" => $total_sgst,
            "price" => $total_price,
            "rate" => $rate,
            "total2" => $total_sub2,
            "total_local" => $total_sub3,
        ]);

        $arr['rows'] = $obj;
        $arr['total_sub'] = round($total_sub, 2);
        $arr['total_igst'] = round($total_igst, 2);
        $arr['total_cgst'] = round($total_cgst, 2);
        $arr['total_sgst'] = round($total_sgst, 2);
        $arr['total_tds'] = round((($total_sub * @$records[0]['individual']) / 100), 2);
        $arr['tds'] = @$records[0]['tds'];
        $arr['individual'] = @$records[0]['individual'];
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_family_id = base64_decode($this->input->get('filter_family_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");

        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, e.number as gl_no, b.name as supplier_name, d.name as journal_type_name, CONCAT('GRN', SUBSTRING(por_no, 4)) AS grn_no,
                a.invoice_no as status_invoice,
                a.total_sub2 as total_sub,
                (a.total_grand2 + a.total_tcs) as total_grand");
            $this->db->from("(SELECT *, SUM(total) as total_sub2, SUM(total2) as total_grand2 FROM purchase_invoices GROUP BY number) a");
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('journal_types d', 'a.journal_type_id = d.id', 'left');
            $this->db->join("(select distinct number, document_no from journal_postings) e", 'a.number = e.document_no', 'left');
            if ($filter_type == "PID") {
                $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
            } elseif ($filter_type == "PAY") {
                $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
            } else {
                $this->db->where("a.trans_date between '$date_from' and '$date_to'");
            }
            $this->db->where('a.flag', "PURCHASE");
            $this->db->like('a.number', $filter_purchase_invoice);
            $this->db->like('a.por_no', $filter_purchase_receipt);
            $this->db->like('a.po_no', $filter_purchase_order);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.invoice_no', $filter_status_supplier);
            $this->db->like('a.invoice_no', $filter_invoice_no);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.number');
            $this->db->order_by('a.number', 'DESC');
            // $this->db->order_by('a.trans_date', 'DESC');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
        } else {
            $number = base64_decode($this->input->get('number'));

            $this->db->select('a.*, c.name as item_family_name');
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_familys c', 'a.item_family_id = c.id', 'left');
            $this->db->where('a.number', $number);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $dataPurchaseInvoices = $post['dataPurchaseInvoices'];
            $combinedJournals = $post['combinedJournals'];

            $dataAssets = array();
            foreach ($dataPurchaseInvoices as $purchase_invoice) {
                if (!empty(@$purchase_invoice['id'])) {
                    $send = $this->crud->update('purchase_invoices', ["id" => $purchase_invoice['id']], $purchase_invoice);
                } else {
                    $send = $this->crud->create('purchase_invoices', $purchase_invoice, "logs");
                    if ($send) {
                        if ($purchase_invoice['por_no'] != "-") {
                            if ($purchase_invoice['type'] != "dp") {
                                if ($purchase_invoice['type'] == "purchase") {
                                    $this->db->update('purchase_order_receipts', ["status" => 1], ["receipt_no" => $purchase_invoice['por_no'], "po_no" => $purchase_invoice['po_no'], "item_rm_id" => $purchase_invoice['item_rm_id'], "supplier_id" => $purchase_invoice['supplier_id']]);
                                } else {
                                    if (@$purchase_invoice['asset_code'] != "" && @$purchase_invoice['asset_code'] != "-") {
                                        $asset_code = $purchase_invoice['asset_code'];

                                        $item_rm = $this->crud->read("item_rm", [], ["id" => $purchase_invoice['item_rm_id']]);
                                        $item_family = $this->crud->read("item_familys", [], ["id" => $item_rm->item_family_id]);
                                        $supplier = $this->crud->read('suppliers', [], ["id" => $purchase_invoice['supplier_id']]);
                                        $asset_fixeds = $this->crud->read('asset_fixeds', [], ["number" => $asset_code]);
                                        $asset = $this->crud->read('assets', [], ["number" => $asset_code]);

                                        if (isset($dataAssets[$asset_code])) {
                                            $amount += ($purchase_invoice['total'] * $purchase_invoice['rate']);
                                        } else {
                                            $amount = ($purchase_invoice['total'] * $purchase_invoice['rate']);
                                        }

                                        if (@$item_family->usefull > 0) {
                                            $estimate_year = $item_family->usefull;
                                        } else {
                                            $estimate_year = 1;
                                        }

                                        $depreciation = ($amount / ($estimate_year * 12));

                                        if (empty($asset_fixeds)) {
                                            $dataAssets[$asset_code] = array(
                                                "item_family_id" => @$item_family->id,
                                                "purchase_invoice_no" => $purchase_invoice['number'],
                                                "supplier_id" => $purchase_invoice['supplier_id'],
                                                "supplier_name" => @$supplier->name,
                                                // "item_rm_id" => $data['item_rm_id'],
                                                "number" => $asset_code,
                                                "name" => @$asset->name,
                                                "shift" => @$item_family->shift,
                                                "trans_date" => $purchase_invoice['invoice_date'],
                                                "uom" => $asset->uom,
                                                "qty" => $asset->qty,
                                                "currency" => @$supplier->currency,
                                                "cost" => $amount,
                                                "estimate_day" => ($estimate_year * 365),
                                                "estimate_month" => ($estimate_year * 12),
                                                "estimate_year" => $estimate_year,
                                                "expired_date" => date("Y-m-d", strtotime("+" . ($estimate_year * 12) . ' months', strtotime($purchase_invoice['trans_date']))),
                                                "depreciation" => $depreciation,
                                                "method" => "PURCHASE",
                                                "account_number" => $purchase_invoice['account_number'],
                                                "total" => $amount,
                                                "posting_date" => $purchase_invoice['trans_date'],
                                                "capitalization_date" => $purchase_invoice['trans_date'],
                                            );
                                        }
                                    }

                                    $this->db->update('purchase_order_receipt_others', ["status" => 1], ["receipt_no" => $purchase_invoice['por_no'], "po_no" => $purchase_invoice['po_no'], "item_rm_id" => $purchase_invoice['item_rm_id'], "supplier_id" => $purchase_invoice['supplier_id']]);
                                }
                            }
                        } else {
                            $this->db->update('purchase_order_others', ["status" => 1], ["po_no" => $purchase_invoice['po_no'], "item_rm_id" => $purchase_invoice['item_rm_id'], "supplier_id" => $purchase_invoice['supplier_id']]);
                        }
                    }
                }
            }

            foreach ($dataAssets as $code => $value) {
                $this->crud->create('asset_fixeds', $value);
            }

            foreach ($combinedJournals as $journal) {
                $purchase_invoice_journals = $this->crud->read('purchase_invoice_journals', [], ["number" => $journal['number'], "account_number" => $journal['account_number'], "flag" => $journal['flag']]);

                if (@$purchase_invoice_journals->id != "") {
                    $send = $this->crud->update('purchase_invoice_journals', ["number" => $journal['number'], "account_number" => $journal['account_number'], "flag" => $journal['flag']], $journal);
                } else {
                    $send = $this->crud->create('purchase_invoice_journals', $journal, "logs");
                }
            }

            die(json_encode(array("title" => "Good Job", "message" => "Data Save Successfully", "theme" => "success")));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createJson()
    {
        $jsonData = $this->input->post('jsonData');
        $jsonData2 = $this->input->post('jsonData2');

        // Simpan data JSON ke dalam file
        file_put_contents('json/purchase_invoices.json', $jsonData);
        file_put_contents('json/purchase_invoice_journals.json', $jsonData2);
    }

    // public function createJournals()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
    //         $purchase_invoice_journals = $this->crud->read('purchase_invoice_journals', [], ["number" => $post['number'], "account_number" => $post['account_number'], "flag" => $post['flag']]);

    //         if (@$purchase_invoice_journals->id != "") {
    //             $send = $this->crud->update('purchase_invoice_journals', ["number" => $post['number'], "account_number" => $post['account_number'], "flag" => $post['flag']], $post);
    //             echo $send;
    //         } else {
    //             $send = $this->crud->create('purchase_invoice_journals', $post);
    //             echo $send;
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function createPosting()
    {
        $document_no = $this->input->post('number');

        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '20240227000005' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if (@$row_menu->number == "") {
            $voucher_no = "";
        } else {
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number . $yearnow . $yearend . "1";

            $sqlGetID    = $this->db->query("SELECT max(`number`) as kode FROM journal_postings WHERE `number` like '%$whereDate%'");
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

            $voucher_no = $autoNo;
        }

        $this->db->select('a.number, b.trans_date, b.invoice_no, c.id as supplier_id, c.name as supplier_name, b.currency, b.item_no, b.item_name, a.account_number, a.account_name, a.debit, a.credit, b.rate, a.flag');
        $this->db->from('purchase_invoice_journals a');
        $this->db->join("(SELECT * FROM purchase_invoices GROUP BY number) b", "b.number = a.number");
        $this->db->join("suppliers c", "b.supplier_id = c.id");
        $this->db->where_in('a.number', $document_no);
        $this->db->order_by('a.number', 'asc');
        $this->db->order_by('a.flag', 'asc');
        $journals = $this->db->get()->result_array();

        $trans_date = "";
        $customer_name = "";
        $currency = "";

        $original_debit = 0;
        $original_credit = 0;
        $local_debit = 0;
        $local_credit = 0;

        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;

        $data = array();
        foreach ($journals as $journal) {
            $number = $journal['number'];
            $account_number = $journal['account_number'];
            $account_name = $journal['account_name'];
            $debit = $journal['debit'];
            $credit = $journal['credit'];
            $rate = $journal['rate'];

            $this->db->select('a.trans_date, a.journal_type_id, a.invoice_no, b.id as supplier_id, b.name as supplier_name, a.po_no, a.item_no, a.item_name, a.currency, a.account_number, c.account_name, a.account_type, a.rate, a.total');
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('account_coa c', 'a.account_number = c.account_number');
            $this->db->where('a.number', $number);
            $this->db->where('a.account_number', $account_number);
            $this->db->order_by('a.trans_date', 'asc');
            $purchase_invoices = $this->db->get()->result_array();

            if ($debit > 0 || $credit > 0) {
                if (count($purchase_invoices) > 0) {

                    $bal_original_debit = 0;
                    $bal_original_credit = 0;
                    $bal_local_debit = 0;
                    $bal_local_credit = 0;

                    foreach ($purchase_invoices as $purchase_invoice) {
                        $currency = $purchase_invoice['currency'];
                        $supplier_id = $purchase_invoice['supplier_id'];
                        $supplier_name = $purchase_invoice['supplier_name'];
                        $trans_date = $purchase_invoice['trans_date'];

                        // $transmonth = date('Y-m-01', strtotime($purchase_invoice['trans_date']));
                        // $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $currency, "currency_to" => "INR"]);

                        if ($currency != "INR") {
                            if ($purchase_invoice['account_type'] == "DEBIT") {
                                $original_debit = round($purchase_invoice['total'], 2);
                                $original_credit = 0;
                            } else {
                                $original_debit = 0;
                                $original_credit = round($purchase_invoice['total'], 2);
                            }

                            if ($purchase_invoice['account_type'] == "DEBIT") {
                                // $local_debit = round($purchase_invoice['total'] * @$exchange->middle, 2);
                                $local_debit = round($purchase_invoice['total'] * @$purchase_invoice['rate'], 2);
                                $local_credit = 0;
                            } else {
                                $local_debit = 0;
                                // $local_credit = round($purchase_invoice['total'] * @$exchange->middle, 2);
                                $local_credit = round($purchase_invoice['total'] * @$purchase_invoice['rate'], 2);
                            }

                            // $rates = @$exchange->middle;
                            $rates = @$purchase_invoice['rate'];
                        } else {
                            if ($purchase_invoice['account_type'] == "DEBIT") {
                                $original_debit = $purchase_invoice['total'];
                                $original_credit = 0;
                                $local_debit = $purchase_invoice['total'];
                                $local_credit = 0;
                            } else {
                                $original_debit = 0;
                                $original_credit = $purchase_invoice['total'];
                                $local_debit = 0;
                                $local_credit = $purchase_invoice['total'];
                            }

                            $rates = 1;
                        }

                        $data[] = array(
                            "journal_date" => $purchase_invoice['trans_date'],
                            "journal_type_id" => $purchase_invoice['journal_type_id'],
                            "number" => $voucher_no,
                            "trans_date" => $purchase_invoice['trans_date'],
                            "document_no" => $number,
                            "invoice_no" => $purchase_invoice['invoice_no'],
                            "company_id" => $supplier_id,
                            "company_name" => $supplier_name,
                            "modul" => "PURCHASE INVOICING",
                            "account_number" => $purchase_invoice['account_number'],
                            "account_name" => $purchase_invoice['account_name'],
                            "description" => $purchase_invoice['po_no'] . " | " . $purchase_invoice['invoice_no'] . " | " . $purchase_invoice['item_no'] . " | " . $purchase_invoice['item_name'],
                            "currency" => $purchase_invoice['currency'],
                            "original_debit" => $original_debit,
                            "original_credit" => $original_credit,
                            "rates" => $rates,
                            "local_debit" => $local_debit,
                            "local_credit" => $local_credit,
                        );

                        $bal_original_debit += $original_debit;
                        $bal_original_credit += $original_credit;
                        $bal_local_debit += $local_debit;
                        $bal_local_credit += $local_credit;

                        $grand_original_debit += $original_debit;
                        $grand_original_credit += $original_credit;
                        $grand_local_debit += $local_debit;
                        $grand_local_credit += $local_credit;
                    }
                } else {
                    // $transmonth = date('Y-m-01', strtotime($trans_date));
                    // $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $transmonth, "currency_from" => $currency, "currency_to" => "INR"]);

                    if ($currency != "INR") {
                        $original_debit = $debit;
                        $original_credit = $credit;
                        $local_debit = round($debit * @$rate, 2);
                        $local_credit = round($credit * @$rate, 2);

                        $rates = @$rate;
                    } else {
                        $original_debit = $debit;
                        $original_credit = $credit;
                        $local_debit = $debit;
                        $local_credit = $credit;

                        $rates = 1;
                    }

                    $data[] = array(
                        "journal_date" => $journal['trans_date'],
                        "journal_type_id" => $purchase_invoice['journal_type_id'],
                        "number" => $voucher_no,
                        "trans_date" => $journal['trans_date'],
                        "document_no" => $number,
                        "invoice_no" => $journal['invoice_no'],
                        "company_id" => $journal['supplier_id'],
                        "company_name" => $journal['supplier_name'],
                        "modul" => "PURCHASE INVOICING",
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "description" => $number . " | " . $journal['invoice_no'] . " | " . $journal['item_no'] . " | " . $journal['item_name'],
                        "currency" => $journal['currency'],
                        "original_debit" => $original_debit,
                        "original_credit" => $original_credit,
                        "rates" => $rates,
                        "local_debit" => $local_debit,
                        "local_credit" => $local_credit,
                    );

                    $grand_original_debit += $original_debit;
                    $grand_original_credit += $original_credit;
                    $grand_local_debit += $local_debit;
                    $grand_local_credit += $local_credit;
                }
            }
        }

        $checkPi = $this->crud->read("journal_postings", [], ["document_no" => "$number"]);
        if (!empty($checkPi)) {
            die(json_encode(array("title" => "Duplicate", "message" => "Invoice No " . $number . " already posting journal", "theme" => "error")));
        }

        if ($voucher_no == "") {
            die(json_encode(array("title" => "Configuration", "message" => "Please Check Configuration in Serial No (Journal Posting) First", "theme" => "error")));
        }

        if (round($grand_local_debit, 2) != round($grand_local_credit, 2)) {
            die(json_encode(array("title" => "Failed", "message" => "Balance Debit (" . $grand_local_debit . ") Cannot match on Balance Credit (" . $grand_local_credit . ")", "theme" => "error")));
        }

        foreach ($data as $dt) {
            $this->crud->create('journal_postings', $dt, "logs");
        }

        die(json_encode(array("title" => "Good Job", "message" => "Data Successfully Created to Journal Posting with Code GL No " . $voucher_no, "theme" => "success")));
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('purchase_invoices', ["number" => $post['number']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_invoices', $data);
        echo $send;
    }

    public function deleteJournal()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_invoice_journals', $data);
        echo $send;
    }

    public function delete()
    {
        $data = $this->input->post();

        $purchase_invoices = $this->crud->reads("purchase_invoices", [], ["number" => $data['number']]);
        foreach ($purchase_invoices as $purchase_invoice) {
            if ($purchase_invoice->por_no != "-") {
                $this->db->update("purchase_order_receipts", ["status" => 0], [
                    "receipt_no" => $purchase_invoice->por_no,
                    "po_no" => $purchase_invoice->po_no,
                    "item_rm_id" => $purchase_invoice->item_rm_id,
                    "supplier_id" => $purchase_invoice->supplier_id
                ]);

                $this->db->update("purchase_order_receipt_others", ["status" => 0], ["receipt_no" => $purchase_invoice->por_no]);
            }
        }

        $send = $this->crud->delete('purchase_invoices', $data);
        $this->db->delete('purchase_invoice_journals', $data);
        $this->db->delete('journal_postings', ["document_no" => $data['number']]);
        echo $send;
    }

    public function print_invoicing($invoice, $tb = "")
    {
        $invoice_no = base64_decode($invoice);
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.number', $invoice_no);
        $this->db->order_by('a.trans_date', 'DESC');
        //$this->db->group_by('a.number');
        $total_invoice = $this->db->get()->result_array();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 10;
        $page = ceil(count($total_invoice) / $rows);
        //Generate QRcode
        $this->createQrcode(@$invoice_no, "assets/image/qrcode/");

        if ($tb == "") {
            $media = "  @media screen {
                            .print {
                                display: none !important;
                            }
                        }
            
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }";
            $noprint = '<div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                                <p>Display pages for 8 rows</p>
                                <p>Paper Size A5, Layout Landscape</p>
                                <p>Margin Default, Scale 80</p>
                            </center>
                        </div>';
            $script = "<script>window.print()</script>";
        } else {
            $media = "";
            $noprint = "";
            $script = "";
        }

        $html = '<html>
                    <head>
                        <title>' . $invoice_no . '</title>
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
                        ' . $media . '
                    </style>
                    <body>
                    ' . $noprint . '
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $grand_total_all = 0;
        $total_igst = 0;
        $total_sgst = 0;
        $total_cgst = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as supplier_name, a.item_no, a.item_name, c.individual, b.tds');
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('tds c', 'b.tds = c.section', 'left');
            $this->db->like('a.number', $invoice_no);
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="450" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $invoice_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . @$config_iso->doc_purchase_invoice . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . @$config_iso->form_purchase_invoice . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u style="padding:5px;">PURCHASE INVOICING</u></h3>
                                </center>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Supplier Name</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Supplier Invoice No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['invoice_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Purchase Invoice No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$invoice_no . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Date</td>
                                            <td width="30">:</td>
                                            <td><b>' . @date("d F Y", strtotime(@$records[0]['trans_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Payment Term</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$records[0]['payment_term'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Payment Due</td>
                                            <td width="30">:</td>
                                            <td><b>' . @date("d F Y", strtotime(@$records[0]['due_date'])) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width:100%; text-align: right; font-size:12px;">Page ' . $hal . '/' . $page . '</div>
                                <table id="customers">
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">POR No</th>
                                        <th rowspan="2">PO No</th>
                                        <th rowspan="2">Part No EBWS</th>
                                        <th rowspan="2">Part No Supplier</th>
                                        <th rowspan="2">Uom</th>
                                        <th rowspan="2">Qty</th>
                                        <th colspan="7">Original Currency</th>
                                        <th colspan="3">Local Currency</th>
                                    </tr>
                                    <tr>
                                        <th>Currency</th>
                                        <th>Price</th>
                                        <th>Amount</th>
                                        <th>IGST</th>
                                        <th>CGST</th>
                                        <th>SGST</th>
                                        <th>Amount<br>After Tax</th>
                                        <th>Rate</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                    </tr>';
            $grand_total = 0;
            $grand_total_local = 0;
            foreach ($records as $record) {
                $amount = ($record['total_idr']);
                if ($record['account_type'] == "DEBIT") {
                    $grand_total += $record['total2'];
                    $grand_total_all += $record['total'];
                    $grand_total_local += $amount;
                } else {
                    $grand_total -= $record['total2'];
                    $grand_total_all -= $record['total'];
                    $grand_total_local -= $amount;
                }

                $total_igst += round($record['igst'], 2);
                $total_sgst += round($record['sgst'], 2);
                $total_cgst += round($record['cgst'], 2);

                if ($record['currency'] == "INR") {
                    $digits = 4;
                } else {
                    $digits = 5;
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['por_no'] . '</td>
                                <td>' . $record['po_no'] . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_no'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['qty']), 2) . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format($record['price'], $digits) . '</td>
                                <td style="text-align:right;">' . @number_format($record['total'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['igst'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['cgst'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['sgst'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['total2'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['rate'], $digits) . '</td>
                                <td>INR</td>
                                <td style="text-align:right;">' . @number_format($amount, 2) . '</td>
                            </tr>';
                $no++;
            }

            $html .= '  <tr>
                            <th colspan="13" style="text-align:right">TOTAL</th>
                            <th style="text-align:right">' . number_format($grand_total, 2) . '</th>
                            <th></th>
                            <th></th>
                            <th style="text-align:right">' . number_format($grand_total_local, 2) . '</th>
                        </tr>
                    </table>
                </div>';

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $hal++;
        }

        $journals = $this->crud->query("SELECT a.*, b.account_name 
            FROM purchase_invoice_journals a 
            JOIN account_coa b ON a.account_number = b.account_number
            WHERE a.number = '$invoice_no' ORDER BY a.flag ASC");

        $html .= '
                <div style="width:100%;">
                    <div style="width:50%; float:left; padding: 10px;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td style="font-weight:bold;">Account No</td>
                                <td style="font-weight:bold;">Account Name</td>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                            </tr>';
        $total_debit = 0;
        $total_credit = 0;
        foreach ($journals as $journal) {
            $total_debit += $journal->debit;
            $total_credit += $journal->credit;

            $html .= '  <tr>
                                <td style="mso-number-format:\@;">' . $journal->account_number . '</td>
                                <td>' . $journal->account_name . '</td>
                                <td style="text-align:right;">' . number_format($journal->debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->credit, 2) . '</td>
                            </tr>';
        }

        $html .= '      <tr>
                                <td colspan="2">Balance</td>
                                <td style="text-align:right;">' . @number_format($total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($total_credit, 2) . '</td>
                            </tr>
                        </table>
                    </div>
                    <div style="width:30%; float:left;">
                        &nbsp;
                    </div>
                    <div style="width:20%; float:right; padding: 10px;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td style="font-weight:bold;">TAXABLE VALUE</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format($grand_total_all, 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">IGST</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$total_igst, 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">SGST</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$total_sgst, 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">CGST</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$total_cgst, 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">TDS (' . @$records[0]['tds'] . ')</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$records[0]['total_tds'], 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">TCS</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format(@$records[0]['total_tcs'], 2) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">INVOICE VALUE</td>
                                <td style="font-weight:bold; text-align:right;">' . @number_format((@$grand_total_all + $total_igst + $total_sgst + $total_cgst + @$records[0]['total_tcs']), 2) . '</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <br><br>
                <table style="width:100%; margin-top: 150px; font-size:12px;">
                    <tr>
                        <td style="text-align:center; font-weight:bold;">Prepared By</td>
                        <td style="text-align:center; font-weight:bold;">Checked By</td>
                        <td style="text-align:center; font-weight:bold;">Approved By</td>
                        <td style="text-align:center; font-weight:bold;">Approved By</td>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Purchasing</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Accounting Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                    </tr>
                </table><br>';
        $html .= "</div></div>" . $script . "</body>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_invoices_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        // $filter_family_id = base64_decode($this->input->get('filter_family_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name, SUM(a.total) as total_sub, (SUM(a.total) + a.total_vat - a.total_pph) as total_grand');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        }
        // $this->db->like('a.family_id', $filter_family_id);
        $this->db->where('a.flag', "PURCHASE");
        $this->db->like('a.number', $filter_purchase_invoice);
        $this->db->like('a.por_no', $filter_purchase_receipt);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.invoice_no', $filter_status_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->group_by('a.number');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 150%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>REPORT PURCHASE INVOICING</small><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Purchase Invoice No</th>
                    <th>Invoice No</th>
                    <th>BOE No</th>
                    <th>BOE Date</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Due Date</th>
                    <th>Payment Term</th>
                    <th>Sub Total</th>
                    <th>VAT</th>
                    <th>PPH 23</th>
                    <th>Grand Total</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $number = $data['number'];

            $this->db->select("a.*, CONCAT('GRN', SUBSTRING(a.por_no, 4)) as grn_no");
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.por_no');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['boe_no'] . '</td>
                            <td>' . $data['boe_date'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td>' . $data['payment_term'] . '</td>
                            <td style="text-align:right;">' . number_format($data['total_sub'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_vat'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_pph'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_grand'], 4) . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="13" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['po_no'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>POR No</th>
                            <th>GRN No</th>
                            <th>PO No</th>
                            <th>Created By</th>
                            <th>Product No EBWS</th>
                            <th>Product No Supplier</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Currency</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['por_no'] . '</td>
                                <td>' . $detail['grn_no'] . '</td>
                                <td>' . $detail['po_no'] . '</td>
                                <td >' . $detail['created_by'] . '</td>
                                <td>' . $detail['item_rm_id'] . '</td>
                                <td>' . $detail['item_no'] . '</td>
                                <td style="text-align:right">' . number_format($detail['qty'], 2) . '</td>
                                <td>' . $detail['uom'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['price'], 4) . '</td>
                                <td style="text-align:right">' . number_format($detail['total'], 4)  . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table>';
        $html .= '  <table id="customers" style="margin-top:20px; width:50%;">
                        <tr>
                            <th width="200" style="text-align:center;">Approval By</th>
                            <th width="200" style="text-align:center;">Dept Manager</th>
                            <th width="200" style="text-align:center;">Created By</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                        </tr>
                    </table></body></html>';
        echo $html;
    }

    public function printDetail($option)
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_invoices_details_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_family_id = base64_decode($this->input->get('filter_family_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        if ($filter_type == "PID") {
            $periode =  $filter_trans_date_from . ' to ' . $filter_trans_date_to;
        } elseif ($filter_type == "PAY") {
            $periode =  $filter_due_date_from . ' to ' . $filter_due_date_to;
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name, b.state, b.gst, b.tds, b.pen, c.name as journal_type_name, d.receipt_date, e.account_name, 
            f.number as item_rm_number, f.hsn_code, g.igst as igst_rate, g.sgst as sgst_rate, g.cgst as cgst_rate');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('journal_types c', 'a.journal_type_id = c.id', 'left');
        $this->db->join('purchase_order_receipts d', 'a.por_no = d.receipt_no', 'left');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        $this->db->join('item_rm f', 'a.item_rm_id = f.id', 'left');
        $this->db->join('hsn g', 'f.hsn_code = g.number', 'left');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        }
        $this->db->like('a.item_family_id', $filter_family_id);
        $this->db->like('a.number', $filter_purchase_invoice);
        $this->db->like('a.por_no', $filter_purchase_receipt);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.invoice_no', $filter_status_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->like('a.status', $filter_status);
        $this->db->group_by('a.id');
        $this->db->order_by('a.supplier_id', 'ASC');
        $this->db->order_by('a.number', 'ASC');
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
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br>
            <center>
                <h2>PURCHASE INVOICE DETAIL REPORT</h2>
                <small>PERIOD : ' . $periode . '</small>
            </center>
            <br><br>
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Company GST No</th>
                    <th>Name of Supplier</th>
                    <th>Supplier GST No</th>
                    <th>State Code</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Posting Date</th>
                    <th>Part No EBWS</th>
                    <th>Asset Code</th>
                    <th>Part Description</th>
                    <th>Material Type</th>
                    <th>HSN / SAC</th>
                    <th>Quantity of Good</th>
                    <th>Currency</th>
                    <th>Rate</th>
                    <th>Taxable Value</th>
                    <th>IGST Rate</th>
                    <th>IGST Amount</th>
                    <th>CGST Rate</th>
                    <th>CGST Amount</th>
                    <th>SGST Rate</th>
                    <th>SGST Amount</th>
                    <th>TCS Amount</th>
                    <th>TDS Amount</th>
                    <th>TDS Section</th>
                    <th>Total Invoice</th>
                    <th>GRN No</th>
                    <th>GRN Date</th>
                    <th>COA No</th>
                    <th>COA Name</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['igst'] > 0) {
                $igst_rate = $data['igst_rate'];
            } else {
                $igst_rate = 0;
            }

            if ($data['sgst'] > 0) {
                $sgst_rate = $data['sgst_rate'];
            } else {
                $sgst_rate = 0;
            }

            if ($data['cgst'] > 0) {
                $cgst_rate = $data['cgst_rate'];
            } else {
                $cgst_rate = 0;
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $config->gst_no . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['gst'] . '</td>
                            <td>' . $data['state'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['invoice_date'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;"></td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td></td>
                            <td>' . $data['hsn_code'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td style="text-align:center;">' . $data['currency'] . '</td>
                            <td style="text-align:right;">' . $data['rate'] . '</td>
                            <td style="text-align:right;">' . $data['total'] . '</td>
                            <td style="text-align:right;">' . $igst_rate . '</td>
                            <td style="text-align:right;">' . $data['igst'] . '</td>
                            <td style="text-align:right;">' . $cgst_rate . '</td>
                            <td style="text-align:right;">' . $data['cgst'] . '</td>
                            <td style="text-align:right;">' . $sgst_rate . '</td>
                            <td style="text-align:right;">' . $data['sgst'] . '</td>
                            <td style="text-align:right;">' . $data['total2'] . '</td>
                            <td style="text-align:right;">' . $data['total_tds'] . '</td>
                            <td>' . $data['tds'] . '</td>
                            <td style="text-align:right;">' . ($data['total2'] + $data['total_tds']) . '</td>
                            <td>' . $data['por_no'] . '</td>
                            <td>' . $data['receipt_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function printJournal($option)
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_invoices_journals_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_family_id = base64_decode($this->input->get('filter_family_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        if ($filter_type == "PID") {
            $periode =  $filter_trans_date_from . ' to ' . $filter_trans_date_to;
            $period_due =  "-";
        } elseif ($filter_type == "PAY") {
            $period_due =  $filter_due_date_from . ' to ' . $filter_due_date_to;
            $periode =  "-";
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.invoice_no, b.trans_date, b.due_date, b.payment_term, b.currency, c.name as supplier_name, d.name as journal_type_name, e.account_name');
        $this->db->from('purchase_invoice_journals a');
        $this->db->join('purchase_invoices b', 'a.number = b.number');
        $this->db->join('suppliers c', 'b.supplier_id = c.id');
        $this->db->join('journal_types d', 'b.journal_type_id = d.id', 'left');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        if ($filter_type == "PID") {
            $this->db->where("b.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("b.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        }
        $this->db->like('b.family_id', $filter_family_id);
        $this->db->like('b.number', $filter_purchase_invoice);
        $this->db->like('b.por_no', $filter_purchase_receipt);
        $this->db->like('b.po_no', $filter_purchase_order);
        $this->db->like('b.supplier_id', $filter_supplier);
        $this->db->like('b.invoice_no', $filter_status_supplier);
        $this->db->like('b.invoice_no', $filter_invoice_no);
        $this->db->like('b.status', $filter_status);
        $this->db->group_by('a.number');
        $this->db->group_by('a.account_number');
        $this->db->order_by('b.supplier_id', 'ASC');
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('a.flag', 'ASC');
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
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br>
            <center>
                <h2>REPORT PURCHASE INVOICING JOURNALS</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $periode . '</td>
                </tr>
                <tr>
                    <td width="100">Payment Due</td>
                    <td width="20">:</td>
                    <td>' . $period_due . '</td>
                </tr>
            </table>
            <br><br>
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Journal Type</th>
                    <th>Purchase Invoice No</th>
                    <th>Invoice No</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Payment Due</th>
                    <th>Payment Term</th>
                    <th>Currency</th>
                    <th>Account No</th>
                    <th>Account Name</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td>' . $data['payment_term'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['debit'] . '</td>
                            <td>' . $data['credit'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
