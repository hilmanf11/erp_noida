<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order_receipts extends CI_Controller
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
            $data['id_menu'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_order_receipts');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $receipt_no = base64_decode($this->input->get('receipt_no'));
        $supplier_id = base64_decode($this->input->get('supplier_id'));
        $item_rm_number = base64_decode($this->input->get('item_rm_number'));

        //Select Query
        $this->db->select('a.*, b.number, b.name, b.uom, c.name as item_family_name, SUM(a.qty_receipt) as qty_receipt');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        // $this->db->join('supplier_items d', 'a.item_rm_id = d.item_rm_id');
        // $this->db->join('suppliers e', 'd.supplier_id = e.id');
        // $this->db->join('item_mpq f', 'a.item_rm_id = f.item_rm_id and a.supplier_id = f.supplier_id');
        // $this->db->join("(select distinct receipt_id FROM purchase_order_labels) f", 'f.receipt_id != a.receipt_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        if ($receipt_no != "") {
            $this->db->where('a.receipt_no', $receipt_no);
        }
        if ($supplier_id != "") {
            $this->db->like('d.supplier_id', $supplier_id);
        }
        if ($item_rm_number != "") {
            $this->db->where('b.number', $item_rm_number);
        }
        $this->db->group_by('a.receipt_no');
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readPoNo($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT po_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readReceipt($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readDocno($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT bc_document FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' GROUP BY bc_document ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReceiptNo()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readSupplier()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT b.id, b.number, b.name FROM purchase_order_receipts a JOIN suppliers b ON a.supplier_id = b.id WHERE (b.name like '%$post%' or b.id like '%$post%') GROUP BY a.supplier_id ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function receipt_no($id_menu, $receipt_date)
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

            $sqlGetID    = $this->db->query("SELECT max(receipt_no) as kode FROM purchase_order_receipts WHERE receipt_no like '%$whereDate%'");
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

    public function receipt_id($receipt_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(receipt_id) as kode FROM purchase_order_receipts WHERE receipt_id like '%$receipt_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $receipt_no . "-" . $autoID;
    }

    public function checkLabel($receipt_no)
    {
        $receipt_no = base64_decode($receipt_no);
        $sqlReceipt = $this->db->query("SELECT sum(qty_label) as qty_label FROM purchase_order_receipts WHERE receipt_no ='$receipt_no'");
        $rowReceipt = $sqlReceipt->row();

        $sqlLabel = $this->db->query("SELECT count(label_no) as label_no FROM scan_item_receipts WHERE receipt_no ='$receipt_no'");
        $rowLabel = $sqlLabel->row();

        if (empty(@$rowLabel->label_no)) {
            $label_no = 0;
        } else {
            $label_no = $rowLabel->label_no;
        }

        echo json_encode(["qty_label" => $rowReceipt->qty_label, "label_no" => $label_no]);
    }

    public function checkQas()
    {
        $invoice_no = trim($this->input->post('bc_document'));
        $item_id = trim(base64_decode($this->input->post('item_rm_number')));

        $this->qas = $this->load->database('qas', TRUE);

        // die ("SELECT * FROM receiving_system WHERE no_sj ='$invoice_no' and item_id = '$item_id'");
        $sqlLabel = $this->qas->query("SELECT * FROM receiving_system WHERE no_sj ='$invoice_no' and item_id = '$item_id'");
        $rowLabel = $sqlLabel->row();

        die(json_encode($rowLabel));
    }

    public function checkQasReceiving()
    {
        $receipt_no = $this->input->post('bc_document');

        // $this->qas = $this->load->database('qas', TRUE);

        // die ("SELECT * FROM receiving_system WHERE no_sj ='$invoice_no' and item_id = '$item_id'");
        $sqlLabel = $this->db->query("SELECT * FROM scan_item_receipts WHERE receipt_no ='$receipt_no'");
        $rowLabel = $sqlLabel->row();

        die(json_encode($rowLabel));
    }

    public function datatables()
    {
        // $this->qas = $this->load->database('qas', TRUE);

        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_supplier = $this->input->get('filter_supplier');
            $filter_item_rm = $this->input->get('filter_item_rm');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_receipt = $this->input->get('filter_receipt');
            $filter_doc_no = $this->input->get('filter_doc_no');
            $filter_status = $this->input->get('filter_status');

            if ($filter_item_rm != "") {
                $where_item_rm_id = "WHERE item_rm_id = '$filter_item_rm'";
            } else {
                $where_item_rm_id = "";
            }

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $id = $_POST['id'];
            if ($id === "0") {
                $this->db->select('a.po_no, a.receipt_no, a.receipt_date, a.awb_no, a.awb_date, a.bc_kind, a.bc_document, a.bc_aju, a.bc_date, b.id as supplier_id, b.name as supplier_name, a.total_receipt as qty_receipt, a.total_label as qty_label, a.total_data, a.status');
                $this->db->from('(SELECT *, COUNT(id) as total_data, sum(qty_label) as total_label, sum(qty_receipt) as total_receipt FROM purchase_order_receipts ' . $where_item_rm_id . ' GROUP BY receipt_no ORDER BY status asc) a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('purchase_orders c', 'a.po_no = c.po_no and a.item_rm_id = c.item_rm_id');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                if ($filter_supplier != "") {
                    $this->db->where('a.supplier_id', $filter_supplier);
                }
                if ($filter_item_rm != "") {
                    $this->db->where('c.item_rm_id', $filter_item_rm);
                }
                if ($filter_po_no != "") {
                    $this->db->where('a.po_no', $filter_po_no);
                }
                if ($filter_receipt != "") {
                    $this->db->where('a.receipt_no', $filter_receipt);
                }
                if ($filter_doc_no != "") {
                    $this->db->where('a.bc_document', $filter_doc_no);
                }
                if ($filter_status != "") {
                    $this->db->where('a.status', $filter_status);
                }
                $this->db->group_by('a.receipt_no');
                $this->db->order_by('a.receipt_id', 'DESC');
                // $this->db->order_by('a.status', 'ASC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $receipt_no = $record['receipt_no'];
                    $invoice_no = $record['bc_document'];

                    if ($filter_item_rm != "") {
                        $where_item_rm_id = "WHERE item_rm_id = '$filter_item_rm'";
                    } else {
                        $where_item_rm_id = "";
                    }

                    $poritems = $this->db->query("SELECT a.receipt_id, b.receipt_no as qas_receipt_id, b.status, c.is_dol, c.id as dol_id
                        FROM purchase_order_receipts a 
                        LEFT JOIN purchase_order_receipt_qas b ON a.receipt_id = b.receipt_no
                        LEFT JOIN dol_items c ON a.supplier_id = c.supplier_id and a.item_rm_id = c.item_rm_id
                        WHERE a.receipt_no = '$receipt_no'
                        GROUP BY a.receipt_id")->result_array();

                    $total_open = 0;
                    $total_ng = 0;
                    $total_close = 0;
                    $total_dol = 0;
                    foreach ($poritems as $poritem) {
                        if (empty(@$poritem['dol_id'])) {
                            $total_open += 1;
                        } else {
                            if (@$poritem['is_dol'] == 0) {
                                $total_dol += 1;
                            } else {
                                if (empty(@$poritem['status'])) {
                                    $total_open += 1;
                                } elseif (@$poritem['status'] == "OK") {
                                    $total_close += 1;
                                } elseif (@$poritem['status'] == "NG") {
                                    $total_ng += 1;
                                }
                            }
                        }
                    }

                    if ($total_open > ($total_close + $total_ng)) {
                        $qas = "OPEN";
                    } else {
                        if ($total_ng > 0) {
                            $qas = "NG";
                        } elseif ($total_dol > 0) {
                            $qas = "PASS DOL";
                        } else {
                            $qas = "CLOSE";
                        }
                    }

                    $purchase_order_label = $this->crud->query("SELECT receipt_id, count(id) as total_scan FROM scan_item_receipts WHERE receipt_id like '%$receipt_no%'");
                    $por_status = $this->crud->query("SELECT COUNT(id) as total_cancel FROM purchase_order_receipts WHERE `status` = '2' and receipt_no = '$receipt_no'");
                    $status = $this->crud->query("SELECT status FROM purchase_order_receipts WHERE receipt_no = '$receipt_no' ORDER BY status asc");

                    if (empty(@$por_status[0]->total_cancel)) {
                        $total_scan = @$purchase_order_label[0]->total_scan;
                    } elseif (@$por_status[0]->total_cancel == $record['total_data']) {
                        $total_scan = 0;
                    } elseif (@$por_status[0]->total_cancel < $record['total_data'] && $qas == "CLOSE") {
                        $total_scan = $record['qty_label'];
                    } elseif (empty(@$purchase_order_label[0]->total_scan)) {
                        $total_scan = @$purchase_order_label[0]->total_scan;
                    } else {
                        $total_scan = $record['qty_label'];
                    }

                    // $grn_no = "";
                    // if($purchase_order_label[0]->total_scan == $record['qty_label']){
                    //     $grn_no = "GRN".substr(@$record['receipt_no'], 3);
                    // }

                    $arr[] = array(
                        "id" => $record['receipt_no'],
                        // "grn_no" => $grn_no,
                        "po_no" => $record['po_no'],
                        "bc_kind" => $record['bc_kind'],
                        "bc_document" => $record['bc_document'],
                        "bc_aju" => $record['bc_aju'],
                        "bc_date" => $record['bc_date'],
                        "receipt_no" => $record['receipt_no'],
                        "receipt_date" => $record['receipt_date'],
                        "awb_no" => $record['awb_no'],
                        "awb_date" => $record['awb_date'],
                        "supplier_id" => $record['supplier_id'],
                        "supplier_name" => $record['supplier_name'],
                        "qty_label" => $record['qty_label'],
                        "total_scan" => $total_scan,
                        // "status" => $record['status'],
                        "qas" => @$qas,
                        "status" => $status[0]->status,
                        "state" => "closed",
                    );
                }
                //Mapping Data
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                    a.id as purchase_order_receipts_id, 
                    a.receipt_id as id, 
                    b.name as supplier_name, 
                    c.number as item_rm_number, 
                    c.name as item_rm_name, 
                    d.name as item_family_name, 
                    b.currency,
                    e.price, 
                    c.uom,
                    f.mpq,
                    g.total_scan,
                    coalesce(h.total_label_create, 0) as total_label_create,
                    i.status as qas_status,
                    j.id as dol_id,
                    j.is_dol');
                $this->db->from('purchase_order_receipts a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->join('purchase_orders e', 'a.po_no = e.po_no and a.item_rm_id = e.item_rm_id', 'left');
                $this->db->join('item_mpq f', 'b.id = f.supplier_id and c.id = f.item_rm_id', 'left');
                $this->db->join('(SELECT receipt_id, COUNT(id) AS total_scan FROM scan_item_receipts GROUP BY receipt_id) g', 'g.receipt_id = a.receipt_id', 'left');
                $this->db->join('(SELECT receipt_id, COUNT(id) AS total_label_create FROM purchase_order_labels GROUP BY receipt_id) h', 'h.receipt_id = a.receipt_id', 'left');
                $this->db->join('purchase_order_receipt_qas i', 'i.receipt_no = a.receipt_id', 'left');
                $this->db->join('dol_items j', 'a.supplier_id = j.supplier_id and a.item_rm_id = j.item_rm_id', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                if ($filter_item_rm != "") {
                    $this->db->where('c.id', $filter_item_rm);
                }
                if ($filter_status != "") {
                    $this->db->where('a.status', $filter_status);
                }
                $this->db->where('a.receipt_no', $id);
                $this->db->group_by('a.receipt_id');
                $this->db->order_by('a.receipt_id', 'ASC');
                $records = $this->db->get()->result_array();

                $result = array();
                foreach ($records as $record) {
                    if (empty(@$record['dol_id'])) {
                        $qas = "OPEN";
                    } else {
                        if (@$record['is_dol'] == 0) {
                            $qas = "PASS DOL";
                        } else {
                            if (empty(@$record['qas_status'])) {
                                $qas = "OPEN";
                            } elseif (@$record['qas_status'] == "OK") {
                                $qas = "CLOSE";
                            } elseif (@$record['qas_status'] == "NG") {
                                $qas = "NG";
                            }
                        }
                    }

                    $result[] = array_merge($record, ["qas" => $qas]);
                }

                echo json_encode($result);
            }
        }
    }

    public function datatablesTemp()
    {
        $po_no = $this->input->get('po_no');
        $bc_document = base64_decode($this->input->get('bc_document'));
        $bc_date = base64_decode($this->input->get('bc_date'));

        //Select Query
        $this->db->select("a.po_no, 
            b.id as item_rm_id, 
            b.number as item_rm_number, 
            b.name as item_rm_name, 
            round(e.qty) as qty_po,
            f.mpq, 
            a.supplier_id, 
            (CASE WHEN a.uom = '' THEN b.uom ELSE a.uom END) as uom,
            round(a.delivery - COALESCE(d.qty_os, 0)) as qty_delivery,
            round(a.delivery - COALESCE(d.qty_os, 0)) as qty_receipt,
            round(a.delivery - COALESCE(d.qty_os, 0)) as qty_receipt2,
            '0' as qty_balance,
            c.currency,
            a.price,
            a.total,
            CEIL(((a.delivery - COALESCE(d.qty_os, 0)) / f.mpq)) as qty_label");
        $this->db->from('purchase_confirm a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('item_mpq f', 'a.item_rm_id = f.item_rm_id and a.supplier_id = f.supplier_id');
        $this->db->join('(SELECT sum(qty_receipt) as qty_os, item_rm_id, supplier_id, po_no, bc_document FROM purchase_order_receipts WHERE status = 0 GROUP BY item_rm_id, supplier_id, po_no, bc_document) d', 'a.item_rm_id = d.item_rm_id and a.supplier_id = d.supplier_id and a.po_no = d.po_no and a.invoice_no = d.bc_document', 'left');
        $this->db->join('purchase_orders e', 'a.item_rm_id = e.item_rm_id and a.po_no = e.po_no');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.invoice_no', $bc_document);
        $this->db->where('a.invoice_date', $bc_date);
        $this->db->like('a.po_no', $po_no);
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.item_rm_id');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('purchase_order_receipts', array_merge($post, ["receipt_id" => $this->receipt_id($post['receipt_no'])]));

                if ($post['qty_delivery'] > $post['qty_receipt']) {
                    $status = 0;
                } else {
                    $status = 1;
                }

                if ($send) {
                    $this->db->where('invoice_no', $post['bc_document']);
                    $this->db->where('invoice_date', $post['bc_date']);
                    $this->db->where('item_rm_id', $post['item_rm_id']);
                    $this->db->where('status', 0);
                    $this->db->update("purchase_confirm", ["status" => 1]);
                }

                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $reads = $this->crud->reads('scan_item_receipts', [], ["receipt_id" => $post['receipt_id']]);

            if (count($reads) > 0) {
                show_error("this receipt id already scan in");
                return false;
            }

            $this->db->delete('purchase_order_labels', ["receipt_id" => $post['receipt_id']]);
            $send = $this->crud->update("purchase_order_receipts", ['id' => $post['id']], [
                "qty_delivery" => $post['qty_delivery'],
                "qty_receipt" => $post['qty_receipt'],
                "qty_balance" => $post['qty_balance'],
                "qty_label" => $post['qty_label'],
                "confirm" => "OK",
                "confirm_date" => date("Y-m-d H:i:s")
            ]);

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create_check_label()
    {
        $post = $this->input->post();
        $po_receipt = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $post['receipt_id']]);
        die(json_encode(array("total" => count($po_receipt))));
    }

    public function create_label()
    {
        $post = $this->input->post();
        $receipt_id = $post['receipt_id'];
        $qty = $post['qty'];

        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_id" => $receipt_id]);
        $po_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);

        if ($po_receipt->qty_label >= count($po_label)) {
            $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
            $rowID = $sqlGetID->row();
            $label = $rowID->kode;
            if ($label == NULL) {
                $autoID = $receipt_id . sprintf("%04s", $label + 1);
            } else {
                $urutan = (int) substr($label, -4);
                $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
            }

            $arrLabel = [
                "receipt_id" => strtoupper($receipt_id),
                "label_no" => strtoupper($autoID),
                "qty" => $qty,
            ];

            $send = $this->crud->create('purchase_order_labels', $arrLabel, "log");
            die($send);
        } else {
            die(json_encode(array("title" => "Finish", "message" => "Already more Qty Label", "theme" => "error")));
        }
    }

    public function uploadAttachment($file_bf = "")
    {
        $file                = $_FILES['file']['name'];
        $extension            = explode('.', $file);
        $extension_end        = strtolower(end($extension));
        $size                = $_FILES['file']['size'];
        $temporary             = $_FILES['file']['tmp_name'];
        $fileName           = time() . "." . $extension_end;

        if (in_array($extension_end, array('pdf', 'jpg', 'png', 'jpeg')) === true) {
            if ($size < 1000000) {
                if ($file != "") {
                    @unlink("assets/documents/purchase_order_receipts/" . base64_decode($file_bf));
                    @move_uploaded_file($temporary, 'assets/documents/purchase_order_receipts/' . $fileName);
                    echo json_encode(array('msg' => 'Upload Success', "file" => $fileName));
                }
            } else {
                echo json_encode(array('msg' => 'Your file size is too large, a maximum of 1 MB', 'file' => base64_decode($file_bf)));
            }
        } else {
            echo json_encode(array('msg' => 'error', 'text' =>  'Your file format is not accepted', 'file' => base64_decode($file_bf)));
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $deletePurchaseOrderReceipts = $this->crud->delete('purchase_order_receipts', ["receipt_id" => $data['id']]);
        $deleteScanItemReceipts = $this->crud->delete('scan_item_receipts', ["receipt_id" => $data['receipt_id']]);
        $updatePurchaseOrders = $this->crud->update('purchase_confirm', ["po_no" => $data['po_no'], "invoice_no" => $data['bc_document'], "invoice_date" => $data['bc_date'], "item_rm_id" => $data['item_rm_id']], ["status" => 0]);
        echo $deletePurchaseOrderReceipts;
    }

    public function deleteLabel()
    {
        $data = $this->input->post();
        $scan = $this->crud->read("scan_item_receipts", [], ["receipt_id" => $data['receipt_id']]);

        if (!empty($scan)) {
            show_error("cannot syncronize bacause this Item already scanned in", 401);
            die();
        }

        $por = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $data['receipt_id']]);
        $item_mpq = $this->crud->read("item_mpq", [], ["supplier_id" => $por->supplier_id, "item_rm_id" => $por->item_rm_id]);

        // if($por->qty_mpq != $item_mpq->mpq){
        $deleteScanItemReceipts = $this->crud->delete('purchase_order_labels', ["receipt_id" => $data['receipt_id']]);
        $this->crud->update('purchase_order_receipts', ["receipt_id" => $data['receipt_id']], ["qty_mpq" => $item_mpq->mpq, "qty_label" => ceil($por->qty_receipt / $item_mpq->mpq)]);
        echo $deleteScanItemReceipts;
        // }else{
        //     echo json_encode(array("theme" => "error"));
        // }
    }

    public function cancelLabel()
    {
        $data = $this->input->post();
        $por = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $data['receipt_id']]);

        if ($por->status == 0) {
            $deleteScanItemReceipts = $this->crud->delete('purchase_order_labels', ["receipt_id" => $data['receipt_id']]);
            $this->db->update('purchase_order_receipts', ["status" => 2, "cancel_status" => date("Y-m-d H:i:s")], ["receipt_id" => $data['receipt_id']]);
            $this->db->update('purchase_confirm', ["status" => 2], ["po_no" => $por->po_no, "invoice_no" => $por->bc_document, "item_rm_id" => $por->item_rm_id]);
            echo $deleteScanItemReceipts;
        } else if ($por->status == 2) {
            $this->db->update('purchase_order_receipts', ["status" => 0, "cancel_status" => null], ["receipt_id" => $data['receipt_id']]);
            $this->db->update('purchase_confirm', ["status" => 1], ["po_no" => $por->po_no, "invoice_no" => $por->bc_document, "item_rm_id" => $por->item_rm_id]);
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function ngLabel()
    {
        $data = $this->input->post();
        $por = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $data['receipt_id']]);

        if ($por->status == 0) {
            $deleteScanItemReceipts = $this->crud->delete('purchase_order_labels', ["receipt_id" => $data['receipt_id']]);
            $this->db->update('purchase_order_receipts', ["status" => 3, "cancel_status" => date("Y-m-d H:i:s")], ["receipt_id" => $data['receipt_id']]);
            $this->db->update('purchase_confirm', ["status" => 3], ["po_no" => $por->po_no, "invoice_no" => $por->bc_document, "item_rm_id" => $por->item_rm_id]);
            echo $deleteScanItemReceipts;
        } else if ($por->status == 3) {
            $this->db->update('purchase_order_receipts', ["status" => 0, "cancel_status" => null], ["receipt_id" => $data['receipt_id']]);
            $this->db->update('purchase_confirm', ["status" => 1], ["po_no" => $por->po_no, "invoice_no" => $por->bc_document, "item_rm_id" => $por->item_rm_id]);
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function print_label($receipt_id)
    {
        // $this->qas = $this->load->database('qas', TRUE);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_id = base64_decode($receipt_id);
        //Cek Label
        $this->db->select('a.*, c.id as item_rm_id, b.receipt_date, b.bc_document, c.number, c.name, f.number as location, c.uom, d.is_dol, d.id as dol_id');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('dol_items d', 'b.supplier_id = d.supplier_id and b.item_rm_id = d.item_rm_id', 'left');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->join("(SELECT * FROM warehouse_locations ORDER BY rand()) f", 'e.id = f.item_family_id', 'left');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->group_by('a.label_no');
        $this->db->where('a.receipt_id', $receipt_id);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();

        $this->db->select('COALESCE(SUM(qty), 0) as total_qty_label');
        $this->db->from("purchase_order_labels");
        $this->db->where('receipt_id', $receipt_id);
        $label = $this->db->get()->row();

        $por = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $receipt_id]);

        // $por = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $receipt_id]);
        // $item_rm = $this->crud->read("item_rm", [], ["id" => $por->item_rm_id]);

        // $invoice_no = $por->bc_document;
        // $item_rm_number = $item_rm->number;

        // $sqlLabel = $this->qas->query("SELECT label FROM receiving_system WHERE no_sj ='$invoice_no' and item_id = '$item_rm_number'");
        // $rowLabel = $sqlLabel->row();

        // if(empty(@$rowLabel->label)){
        //     $qas = "<b style='color:orange; font-size:15px;'>CHECK</b>";
        // }else{
        //     if(@$rowLabel->label == "OK"){
        //         $qas = "<b style='color:green; font-size:15px;'>OK</b>";
        //     }elseif(@$rowLabel->label == "NG"){
        //         $qas = "<b style='color:red; fosnt-size:15px;'>NG</b>";
        //     }else{
        //         $qas = "<b style='color:green; font-size:15px;'>".@$rowLabel->label."</b>";
        //     }
        // }

        $html = '<html>
                    <head>
                        <title>' . $receipt_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';

        if ($label->total_qty_label != $por->qty_receipt) {
            $html .= "<br><br><br><center><h3>Qty Label and Qty GATE ENTRY not same, please sync again</h3></center>";
            die($html);
        }

        if ($records) {
            $html .= '<div style="width: 44mm;">';
            $no = 1;
            foreach ($records as $record) {
                if ($no == 3) {
                    $no = 1;
                }
                if ($no == 1) {
                    $padding = "padding:2mm 3mm 3mm 2mm;";
                } else {
                    $padding = "padding:2mm 3mm 3mm 2mm;";
                }

                if (empty(@$record->dol_id)) {
                    die("NOT AVAILABLE IN DOL MASTER");
                } else {
                    if (@$record->is_dol == "0") {
                        $dol = "<b style='color:green; font-size:15px;'>DOL</b>";
                    } else {
                        $dol = "<b style='color:red; font-size:15px;'>NON DOL</b>";
                    }
                }

                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");
                $html .= '  <div style="width: 44mm; max-height:40mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>
                                        <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="height:35px;">
                                            <div style="float:left;">
                                                <small style="font-size:7px;"><b>' . $record->number . '</b></small><br>
                                                <small style="font-size:12px;"><b>' . $record->item_rm_id . '</b></small>
                                                <br>
                                                <b style="font-size:7px;">' . $record->bc_document . '</b>
                                            </div>
                                            
                                            <div style="float:right;">
                                                <small style="font-size:15px;"><b>' . $record->p_month . '</b></small><small style="font-size:10px;"><b>' . " - " . $record->p_year . '</b></small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left">
                                            <small>Quantity</small><br><b style="font-size:20px;">' . number_format($record->qty, 2) . '</b></small><br>
                                            <small style="font-size:15px; float: right;"><b>' . $record->uom . '</b></small>
                                        </th>
                                        <th style="text-align:left">
                                            ' . $dol . '
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small>Date :</small><br> 
                                                <b style="font-size:8px;">' . $record->receipt_date . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>Label No :</small><br>
                                                <b style="font-size:8px;">' . $record->label_no . '</b>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50"/>
                                        </th>
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
    // <small style="float:right;">QC PASSED</small>
    // <small style="font-size: 10px; float:right;"><b>'. $this->session->name . '</b></small><br>

    public function print_receiving($receipt_no, $type = "")
    {
        $purchase_order_receipt_total = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 8;
        $page = ceil(count($purchase_order_receipt_total) / $rows);

        if ($type == "QAS") {
            $type_name = "MATERIAL";
            $grn_no = "-";
        } else {
            $type_name = "GOOD";
            $grn_no = "GRN" . substr(@$po_receipt->receipt_no, 3);
        }

        //Generate QRcode
        $this->createQrcode($po_receipt->receipt_no, "assets/image/qrcode/");
        $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_rm_id, c.name as item_name, c.uom, d.name as item_familys_name, e.mpq, b.currency');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('item_mpq e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
        // $this->db->join('warehouse_location_items g', 'a.item_rm_id = g.item_rm_id', 'left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.receipt_no', base64_decode($receipt_no));
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.supplier_id');
        $this->db->group_by('a.item_rm_id');
        $records = $this->db->get()->result_array();

        if ($records) {
            $html = '<html>
                        <head>
                            <title>' . $po_receipt->receipt_no . '</title>
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
                                    <p>Display pages for 8 rows</p>
                                    <p>Paper Size A5, Layout Landscape</p>
                                    <p>Margin Default, Scale 98</p>
                                </center>
                            </div>
                            <div class="print">';
            $no = 1;
            $hal = 1;
            $subtotal = 0;
            for ($i = 0; $i < $page; $i++) {

                if ($type == "QAS") {
                    $this->db->select('a.*, b.id as supplier_id, b.name as supplier_name, c.name as item_rm_name, c.number as item_rm_number, c.uom, d.name as item_familys_name, e.mpq, b.currency');
                    $this->db->from('purchase_order_receipts a');
                    $this->db->join('suppliers b', 'a.supplier_id = b.id');
                    $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                    $this->db->join('item_familys d', 'c.item_family_id = d.id');
                    $this->db->join('item_mpq e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
                    // $this->db->join('warehouse_location_items g', 'a.item_rm_id = g.item_rm_id', 'left');
                    $this->db->where('a.deleted', 0);
                    // $this->db->where('a.status', 0);
                    $this->db->where('a.receipt_no', base64_decode($receipt_no));
                    $this->db->group_by('a.po_no');
                    $this->db->group_by('a.supplier_id');
                    $this->db->group_by('a.item_rm_id');
                    $this->db->limit(8, ($i * 8));
                    $records = $this->db->get()->result_array();
                } else {
                    $this->db->select('a.*, b.id as supplier_id, b.name as supplier_name, c.name as item_rm_name, c.number as item_rm_number, c.uom, d.name as item_familys_name, e.mpq, b.currency, SUM(g.qty) as qty_receipt');
                    $this->db->from('purchase_order_receipts a');
                    $this->db->join('suppliers b', 'a.supplier_id = b.id');
                    $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                    $this->db->join('item_familys d', 'c.item_family_id = d.id');
                    $this->db->join('item_mpq e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
                    $this->db->join('scan_item_receipts g', 'a.receipt_id = g.receipt_id');
                    $this->db->where('a.deleted', 0);
                    // $this->db->where('a.status', 0);
                    $this->db->where('a.receipt_no', base64_decode($receipt_no));
                    $this->db->group_by('a.po_no');
                    $this->db->group_by('a.supplier_id');
                    $this->db->group_by('a.item_rm_id');
                    $this->db->limit(8, ($i * 8));
                    $records = $this->db->get()->result_array();
                }

                $html .= '  <table style="width:100%;">
                                    <tr>
                                        <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                        <td width="250" style="padding:10px;">
                                            <b style="font-size:14px;">' . $config->name . '</b><br>
                                            <span style="font-size:10px;">' . $config->address . '</span><br>
                                        </td>
                                        <th width="100" style="text-align:right;">
                                            <table style="width:100%; font-size:10px;">
                                                <tr>
                                                    <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $po_receipt->receipt_no . '.png') . '" width="60"/></td>
                                                    <td width="60">Doc No</td>
                                                    <td width="5">:</td>
                                                    <td width="100">' . $config_iso->doc_receiving_note . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Form</td>
                                                    <td>:</td>
                                                    <td>' . $config_iso->form_receiving_note . '</td>
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
                                        </th>
                                    </tr>
                                </table>
                                <div style="border: 1px solid black; width:100%;">
                                    <div style="padding:10px;">
                                        <center>
                                            <h3><u>' . $type_name . ' RECEIPT NOTE</u></h3>
                                        </center>
                                        <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="100">GRN No</td>
                                                <td width="10">:</td>
                                                <td><b>' . $grn_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="100">MRN No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">MRN Date</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_date . '</b></td>
                                            </tr>
                                        </table>
                                        <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">

                                            <tr>
                                                <td width="100">Supplier</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="100">Invoice. No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['bc_document'] . '</b></td>
                                            </tr>
                                        </table>
    
                                        <table id="customers">
                                            <tr>
                                                <th>No</th>
                                                <th>PO No</th>
                                                <th>Part No EBWS</th>
                                                <th>Part No Supplier</th>
                                                <th>Description</th>
                                                <th>Category</th>
                                                <th>Location</th>
                                                <th>MPQ</th>
                                                <th>Quantity</th>
                                                <th>Uom</th>
                                            </tr>';
                $no = 1;
                foreach ($records as $record) {
                    $html .= '  <tr>
                    <td style="text-align:center">' . $no . '</td>
                    <td>' . $record['po_no'] . '</td>
                    <td>' . $record['item_rm_id'] . '</td>
                    <td>' . $record['item_rm_number'] . '</td>
                    <td>' . $record['item_rm_name'] . '</td>
                    <td>' . $record['item_familys_name'] . '</td>
                    <td>' . $record['location'] . '</td>
                    <td style="text-align:right">' . number_format($record['mpq'], 2) . '</td>
                    <td style="text-align:right">' . number_format($record['qty_receipt'], 2) . '</td>
                    <td>' . $record['uom'] . '</td>
                </tr>';
                    $no++;
                }
                $html .= '  </table>
                            <table id="customers" style="margin-top:20px;">
                                <tr>
                                    <th rowspan="3" style="vertical-align:top; padding:20px; text-align:left;">Note : </th>
                                    <th width="200" style="text-align:center;">Approval QC</th>
                                    <th width="200" style="text-align:center;">Receipt By</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;"></th>
                                    <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                                </tr>
                            </table>
                        </div>
                    </div>
                <script>window.print()</script>';
                if (($i + 1) != $page) {
                    $html .= '<div style="page-break-after:always;"></div>';
                }
                $hal++;
            }
            $html .= "</div></div><script>window.print()</script>";
            die($html);
        }
    }

    public function print_discrepancy($receipt_no, $type = "")
    {
        $purchase_order_receipt_total = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);

        $config = $this->db->get('config')->row();

        //Config Page
        $rows = 20;
        $page = ceil(count($purchase_order_receipt_total) / $rows);

        $html = '<html>
                    <head>
                        <title>' . $po_receipt->receipt_no . '</title>
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
                                <p>Display pages for 20 rows</p>
                                <p>Paper Size A5, Layout Landscape</p>
                                <p>Margin Default, Scale 98</p>
                            </center>
                        </div>
                        <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.id as supplier_id, b.name as supplier_name, c.name as item_rm_name, c.number as item_rm_number, c.uom, d.name as item_familys_name, b.currency');
            $this->db->from('purchase_order_receipts a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys d', 'c.item_family_id = d.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.receipt_no', base64_decode($receipt_no));
            $this->db->group_by('a.id');
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();

            $html .= '  <table style="width:100%;">
                                <tr>
                                    <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                    <td width="300" style="padding:10px;">
                                        <b style="font-size:14px;">' . $config->name . '</b><br>
                                        <span style="font-size:10px;">' . $config->address . '</span><br>
                                    </td>
                                </tr>
                            </table>
                            <div style="border: 1px solid black; width:100%;">
                                <div style="padding:10px;">
                                    <center>
                                        <h3><u>DISCREPANCY NOTE (Gate Entry)</u></h3>
                                    </center>
                                    <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">
                                        <tr>
                                            <td width="100">Receipt No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$po_receipt->receipt_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Receipt Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['receipt_date'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Supplier Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                        </tr>
                                    </table>
                                    <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">
                                        <tr>
                                            <td width="100">PO. No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['po_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Invoice. No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['bc_document'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Invoice. Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['bc_date'] . '</b></td>
                                        </tr>
                                    </table>

                                    <table id="customers">
                                        <tr>
                                            <th>No</th>
                                            <th>Serial No</th>
                                            <th>Part No EBWS</th>
                                            <th>Part No Supplier</th>
                                            <th>Description</th>
                                            <th>Uom</th>
                                            <th>Invoice Qty</th>
                                            <th>Receive Qty</th>
                                            <th>Difference Qty</th>
                                            <th>Type</th>
                                            <th>Remarks</th>
                                        </tr>';
            $no = 1;
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['receipt_id'] . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_rm_number'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right">' . number_format($record['qty_delivery']) . '</td>
                                <td style="text-align:right">' . number_format($record['qty_receipt']) . '</td>
                                <td style="text-align:right">' . number_format($record['qty_balance']) . '</td>
                                <td></td>
                                <td></td>
                            </tr>';
                $no++;
            }
            $html .= '  </table>
                        <table id="customers" style="margin-top:20px;">
                            <tr>
                                <th rowspan="3" style="vertical-align:top; padding:20px; text-align:left;">Note : </th>
                                <th width="200" style="text-align:center;">Approval QC</th>
                                <th width="200" style="text-align:center;">Receipt By</th>
                            </tr>
                            <tr>
                                <th style="height:80px;"></th>
                                <th style="height:80px;"></th>
                            </tr>
                            <tr>
                                <th style="height:20px; text-align:center;"></th>
                                <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                            </tr>
                        </table>
                    </div>
                </div>
            <script>window.print()</script>';
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

        $html .= "</div></div><script>window.print()</script>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_receipts_$format.xls");
        }

        $this->qas = $this->load->database('qas', TRUE);

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_supplier = $this->input->get('filter_supplier');
        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_receipt = $this->input->get('filter_receipt');
        $filter_doc_no = $this->input->get('filter_doc_no');
        $filter_status = $this->input->get('filter_status');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
            a.id as purchase_order_receipts_id, 
            a.receipt_id as id, 
            b.number as supplier_id, 
            b.name as supplier_name, 
            b.pen,
            b.gst,
            c.number as item_rm_number, 
            c.name as item_rm_name, 
            d.name as item_family_name, 
            b.currency, 
            c.uom,
            e.mpq,
            COALESCE(g.total_scan, 0) as total_scan');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('item_mpq e', 'b.id = e.supplier_id and c.id = e.item_rm_id', 'left');
        $this->db->join("(SELECT COUNT(id) as total_scan, receipt_id FROM scan_item_receipts GROUP BY receipt_id) g", 'g.receipt_id = a.receipt_id', 'left');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where('a.receipt_date >=', $filter_from);
            $this->db->where('a.receipt_date <=', $filter_to);
        }
        if ($filter_item_rm != "") {
            $this->db->where('c.id', $filter_item_rm);
        }
        if ($filter_supplier != "") {
            $this->db->where('b.id', $filter_supplier);
        }
        if ($filter_po_no != "") {
            $this->db->where('a.po_no', $filter_po_no);
        }
        if ($filter_receipt != "") {
            $this->db->where('a.receipt_no', $filter_receipt);
        }
        if ($filter_doc_no != "") {
            $this->db->where('a.bc_document', $filter_doc_no);
        }
        if ($filter_status != "") {
            $this->db->where('a.status', $filter_status);
        }
        $this->db->group_by('a.receipt_id');
        $this->db->order_by('a.receipt_id', 'ASC');
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
                                <small>PURCHASE ORDER RECEIPT</small>
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
                <th rowspan="2" width="20">No</th>
                <th rowspan="2" width="80">Status<br>GRN</th>
                <th rowspan="2" width="80">Status<br>Invoice</th>
                <th rowspan="2" width="80">Status<br>QAS</th>
                <th rowspan="2">MRN No</th>
                <th rowspan="2">GRN No</th>
                <th rowspan="2">PO No</th>
                <th rowspan="2">Invoice No</th>
                <th rowspan="2">Invoice Date</th>
                <th rowspan="2">BOE No</th>
                <th rowspan="2">BOE Date</th>
                <th colspan="4" style="text-align:center;">Supplier</th>
                <th rowspan="2">Part No EBWS</th>
                <th rowspan="2">Part No Supplier</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">UoM</th>
                <th rowspan="2">Currency</th>
                <th rowspan="2">Qty PO</th>
                <th rowspan="2">Qty Delivery</th>
                <th rowspan="2">Qty Receipt</th>
                <th rowspan="2">Qty Confirm</th>
                <th rowspan="2">MPQ</th>
                <th rowspan="2">Label</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>GST No</th>
                <th>PEN No</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == "0") {
                $status = "OPEN";
                $style = "style='background: green; color:white;'";
            } elseif ($data['status'] == "2") {
                $status = "CANCEL";
                $style = "style='background: blue; color:white;'";
            } else {
                $status = "CLOSED";
                $style = "style='background: red; color:white;'";
            }

            if ($data['total_scan'] == $data['qty_label']) {
                $grn_no = "GRN" . substr(@$data['receipt_no'], 3);
                $status2 = "CLOSED";
                $style2 = "style='background: red; color:white;'";
            } elseif ($data['status'] == "2") {
                $grn_no = "";
                $status2 = "CANCEL";
                $style2 = "style='background: blue; color:white;'";
            } else {
                $grn_no = "";
                $status2 = "OPEN";
                $style2 = "style='background: green; color:white;'";
            }

            $invoice_no = trim($data['bc_document']);
            $item_rm_number = trim($data['item_rm_number']);

            $sqlLabel = $this->qas->query("SELECT label FROM receiving_system WHERE no_sj ='$invoice_no' and item_id = '$item_rm_number'");
            $rowLabel = $sqlLabel->row();

            if (empty(@$rowLabel->label)) {
                $qas = "OPEN";
                $style3 = "style='background: green; color:white;'";
            } else {
                if (@$rowLabel->label == "OK") {
                    $qas = "CLOSE";
                    $style3 = "style='background: red; color:white;'";
                } elseif (@$rowLabel->label == "NG") {
                    $qas = "NG";
                    $style3 = "style='background: red; color:white;'";
                } else {
                    $qas = "OPEN";
                    $style3 = "style='background: green; color:white;'";
                }
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td ' . $style2 . '>' . $status2 . '</td>
                        <td ' . $style . '>' . $status . '</td>
                        <td ' . $style3 . '>' . $qas . '</td>
                        <td>' . $data['receipt_no'] . '</td>
                        <td>' . $grn_no . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['bc_document'] . '</td>
                        <td>' . $data['bc_date'] . '</td>
                        <td>' . $data['awb_no'] . '</td>
                        <td></td>
                        <td>' . $data['supplier_id'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['gst'] . '</td>
                        <td>' . $data['pen'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['qty_po']) . '</td>
                        <td>' . number_format($data['qty_delivery']) . '</td>
                        <td>' . number_format($data['qty_delivery']) . '</td>
                        <td>' . number_format($data['qty_receipt']) . '</td>
                        <td>' . number_format($data['qty_mpq']) . '</td>
                        <td>' . number_format($data['qty_label']) . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
