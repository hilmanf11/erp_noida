<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
ini_set('memory_limit', '512M');

class Bom extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[bom.item_fg_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[bom.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/bom');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('bom', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    //GET DATA
    public function readBom($item_fg_id)
    {
        $item_fg_id = base64_decode($item_fg_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom FROM bom a JOIN item_rm b ON a.item_rm_id = b.id WHERE a.item_fg_id = '$item_fg_id' and b.number like '%$post%' ORDER BY b.number asc");
        echo json_encode($send);
    }

    //GET DATA
    public function readWeight()
    {
        $post = $this->input->post();
        $item_fg = $this->crud->read("item_fg", [], ["id" => $post['item_fg_id']]);
        echo json_encode($item_fg);
    }

    //GET DATA
    public function readRunner()
    {
        $post = $this->input->post();
        $item_fg_id = $post['item_fg_id'];
        $menu_loading = $this->crud->query("SELECT SUM(a.runner) as runner, b.cavity_standard
       FROM menu_loadings a JOIN molds b on a.mold_id = b.id
       WHERE a.item_fg_id = '$item_fg_id' group by a.item_fg_id");
        echo json_encode($menu_loading);
    }

    public function readProcessMain($item_fg_id)
    {
        $item_fg_id = base64_decode($item_fg_id);

        $bom = $this->crud->query("SELECT a.main_process FROM bom a JOIN process_main b ON a.main_process = b.name WHERE item_fg_id = '$item_fg_id' and b.type = 'HAV' GROUP BY a.main_process ORDER BY a.main_process ASC");
        echo json_encode($bom);
    }

    public function readProcessItems($item_fg_id, $main_process)
    {
        $item_fg_id = base64_decode($item_fg_id);
        $main_process = base64_decode($main_process);

        $bom = $this->crud->query("SELECT b.* FROM bom a JOIN item_rm b ON a.item_rm_id = b.id WHERE a.item_fg_id = '$item_fg_id' and a.main_process = '$main_process' GROUP BY b.id ORDER BY b.id ASC");
        echo json_encode($bom);
    }

    public function readRevision(){
        $item_fg_id = $this->input->post("item_fg_id");
        
        $datas = $this->crud->query("SELECT DISTINCT revision FROM bom_log WHERE item_fg_id = '$item_fg_id' ORDER BY revision DESC");
        die(json_encode(array(
            "rows" => $datas,
            "new_revision" => (@$datas[0]->revision + 1)
        )));
    }
    
    public function calculateBom(){
        $datas = $this->crud->query("SELECT item_fg_id, MAX(revision) as revision FROM bom GROUP BY item_fg_id ORDER BY item_fg_id");
        foreach ($datas as $data) {
            $item_fg_id = $data->item_fg_id;
            $details = $this->crud->query("SELECT * FROM bom WHERE item_fg_id = '$item_fg_id'");
            foreach ($details as $detail) {
                $dataFinal = array(
                    "created_by" => $detail->created_by,
                    "created_date" => $detail->created_date,
                    "item_fg_id" => $detail->item_fg_id,
                    "item_rm_id" => $detail->item_rm_id,
                    "main_process" => $detail->main_process,
                    "sub_process" => $detail->sub_process,
                    "type" => $detail->type,
                    "composition" => $detail->composition,
                    "revision" => $data->revision,
                );
                $this->db->insert("bom_log", $dataFinal);
            }
            $this->db->update("bom", ["revision" => $data->revision], ["item_fg_id" => $item_fg_id]);
            echo "OK<br>";
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('b.id as item_fg_id, b.number as item_fg_number, b.number_customer, b.name as item_fg_name, a.created_by, a.created_date, a.updated_by, a.updated_date, a.approved_to, a.approved_by, a.approved_date, a.revision');
            $this->db->from("(SELECT * FROM bom ORDER BY approved_to desc) a");
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->group_by('b.number');
            $this->db->order_by('b.number', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));
            $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as item_rm_number, c.name as item_rm_name, c.uom as uom, c.item_family_id as product_family, d.name as product_family_name, f.name as supplier_name, g.name as maker_name');
            $this->db->from('bom a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys d', 'c.item_family_id = d.id');
            $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id', 'left');
            $this->db->join('suppliers f', 'e.supplier_id = f.id', 'left');
            $this->db->join('makers g', 'c.maker_id = g.id', 'left');
            $this->db->where('b.number', $number);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->group_by('a.id');
            $this->db->order_by('d.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));
            $revision = $this->input->get('revision');

            if($revision != ""){
                $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom, d.name as item_family_name');
                $this->db->from('bom_log a');
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->where('a.item_fg_id', $item_fg_id);
                $this->db->where('a.revision', $revision);
                $this->db->where('a.remove', 0);
                $this->db->group_by('a.item_fg_id');
                $this->db->group_by('a.item_rm_id');
                $this->db->order_by('c.number', 'ASC');
                $records = $this->db->get()->result_array();
            }else{
                $this->db->select('a.*, c.number as item_rm_number, c.name as item_rm_name, c.uom, d.name as item_family_name');
                $this->db->from('bom a');
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->where('a.item_fg_id', $item_fg_id);
                $this->db->order_by('a.id', 'ASC');
                $records = $this->db->get()->result_array();
            }

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $datas = $post['dataBom'];

            foreach ($datas as $data) {
                $dataFinal = array(
                    "created_by" => $this->session->username,
                    "created_date" => date("Y-m-d H:i:s"),
                    "item_fg_id" => @$data['item_fg_id'],
                    "item_rm_id" => @$data['item_rm_id'],
                    "main_process" => @$data['main_process'],
                    "revision" => @$data['revision'],
                    "drawing" => @$data['drawing'],
                    "composition" => @$data['composition'],
                    "remove" => @$data['remove']
                );

                if(@$data['item_rm_id'] != "" && @$data['main_process'] != "" && $data['composition'] != ""){
                    if($data['revision'] == ""){
                        $bom = $this->crud->read("bom", [], ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);
                        if (@$bom->item_fg_id != "") {
                            $this->crud->update('bom', ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']], $data, "logs");
                            $this->db->update("bom_log", $dataFinal, ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);
                        } else {
                            $this->crud->create('bom', $data, "logs");
                            $this->db->insert("bom_log", $dataFinal);
                        }
                    }else{
                        $bom = $this->crud->read("bom", [], ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);
                        if (@$bom->item_fg_id != "") {
                            if(@$bom->approved_to != ""){
                                die(json_encode(array("title" => "Failed", "message" => "Data Cannot Save because the Data still Process Approvals", "theme" => "error")));
                            }

                            if($data['revision'] > 0){
                                $this->crud->update('bom', ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']], $data, "logs");
                            }else{
                                $this->db->update('bom', $data, ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);
                            }
                        } else {
                            $this->crud->create('bom', $data);
                        }

                        $this->db->insert("bom_log", $dataFinal);
                    }
                }
            }

            die(json_encode(array("title" => "Good Job", "message" => "Data Save Successfully", "theme" => "success")));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function copy()
    {
        if ($this->input->post()) {
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $datas = $post['dataBom'];

            $bom2 = $this->crud->read("bom", [], ["item_fg_id" => @$datas[0]['item_fg_id_copy_2']]);

            if(!empty($bom2)){
                die(json_encode(array("title" => "Exists", "message" => "Cannot Copy Data, Because Already Exist", "theme" => "error")));
            }

            foreach ($datas as $data) {
                // $bom = $this->crud->read("bom", [], ["item_fg_id" => $data['item_fg_id_copy_1'], "item_rm_id" => $data['item_rm_id']]);
                $revision = 0;

                // if(empty($bom2)){
                //     $revision = 0;
                // }else{
                //     $revision = ($bom2->revision + 1);
                // }

                // $postFinalSource = array(
                //     "item_fg_id" => $data['item_fg_id_copy_1'],
                //     "item_rm_id" => $data['item_rm_id'],
                //     "main_process" => $data['main_process'],
                //     "composition" => $data['composition'],
                //     "revision" => ($bom->revision + 1)
                // );
    
                $postFinalCopy = array(
                    "item_fg_id" => $data['item_fg_id_copy_2'],
                    "item_rm_id" => $data['item_rm_id'],
                    "main_process" => $data['main_process'],
                    "composition" => $data['composition'],
                    "revision" => $revision
                );
    
                // $dataFinal = array(
                //     "created_by" => $this->session->username,
                //     "created_date" => date("Y-m-d H:i:s"),
                //     "item_fg_id" => $data['item_fg_id_copy_1'],
                //     "item_rm_id" => $data['item_rm_id'],
                //     "main_process" => $data['main_process'],
                //     "composition" => $data['composition'],
                //     "revision" => ($bom->revision + 1)
                // );
    
                $dataFinal2 = array(
                    "created_by" => $this->session->username,
                    "created_date" => date("Y-m-d H:i:s"),
                    "item_fg_id" => $data['item_fg_id_copy_2'],
                    "item_rm_id" => $data['item_rm_id'],
                    "main_process" => $data['main_process'],
                    "composition" => $data['composition'],
                    "revision" => $revision
                );

                $bom_copy = $this->crud->read("bom", [], ["item_fg_id" => $data['item_fg_id_copy_2'], "item_rm_id" => $data['item_rm_id']]);
                if (@$bom_copy->item_fg_id != "") {
                    $send = $this->db->update('bom', $postFinalCopy, ["item_fg_id" => $data['item_fg_id_copy_2'], "item_rm_id" => $data['item_rm_id']]);
                } else {
                    $send = $this->crud->create('bom', $postFinalCopy);
                }
                
                $this->db->insert("bom_log", $dataFinal2);
                $this->db->update("bom", ["approved_to" => ""], ["item_fg_id" => $data['item_fg_id_copy_2'], "item_rm_id" => $data['item_rm_id']]);
    
                // if ($data["type"] == "1") { // update source
                //     if (@$bom->item_fg_id != "") {
                //         $send = $this->db->update('bom', $postFinalSource, ["item_fg_id" => $data['item_fg_id_copy_1'], "item_rm_id" => $data['item_rm_id']]);
                //     } else {
                //         $send = $this->crud->create('bom', $postFinalSource);
                //     }

                //     $this->db->insert("bom_log", $dataFinal);
                // } else if ($data["type"] == "2") { // copy to new
                //     $bom_copy = $this->crud->read("bom", [], ["item_fg_id" => $data['item_fg_id_copy_2'], "item_rm_id" => $data['item_rm_id']]);
                //     if (@$bom_copy->item_fg_id != "") {
                //         $send = $this->db->update('bom', $postFinalCopy, ["item_fg_id" => $data['item_fg_id_copy_2'], "item_rm_id" => $data['item_rm_id']]);
                //     } else {
                //         $send = $this->crud->create('bom', $postFinalCopy);
                //     }
                    
                //     $this->db->insert("bom_log", $dataFinal2);
                //     $this->db->update("bom", ["approved_to" => ""], ["item_fg_id" => $data['item_fg_id_copy_2'], "item_rm_id" => $data['item_rm_id']]);
                // } else if ($data["type"] == "3") { // update & copy to new
                //     $bom_copy = $this->crud->read("bom", [], ["item_fg_id" => $data['item_fg_id_copy_1'], "item_rm_id" => $data['item_rm_id']]);
    
                //     if (@$bom->item_fg_id != "") {
                //         $send = $this->db->update('bom', $postFinalSource, ["item_fg_id" => $data['item_fg_id_copy_1'], "item_rm_id" => $data['item_rm_id']]);
                //     } else {
                //         $send = $this->crud->create('bom', $postFinalSource);
                //     }
    
                //     $this->db->insert("bom_log", $dataFinal);
                    
                //     if (@$bom_copy->item_fg_id != "") {
                //         $send = $this->db->update('bom', $postFinalCopy, ["item_fg_id" => $data['item_fg_id_copy_2'], "item_rm_id" => $data['item_rm_id']]);
                //     } else {
                //         $send = $this->crud->create('bom', $postFinalCopy);
                //     }
    
                //     $this->db->insert("bom_log", $dataFinal2);
                //     $this->db->update("bom", ["approved_to" => ""], ["item_fg_id" => $data['item_fg_id_copy_2'], "item_rm_id" => $data['item_rm_id']]);
                // }
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('bom', $data);
        $send = $this->crud->delete('bom_log', $data);
        echo $send;
    }

    //UPLOAD IMAGE
    public function uploadDrawing($file_bf = "")
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
                    @unlink("assets/documents/bom/" . base64_decode($file_bf));
                    @move_uploaded_file($temporary, 'assets/documents/bom/' . $fileName);
                    echo json_encode(array('msg' => 'Upload Success', "file" => $fileName));
                }
            } else {
                echo json_encode(array('msg' => 'Your file size is too large, a maximum of 1 MB', 'file' => base64_decode($file_bf)));
            }
        } else {
            echo json_encode(array('msg' => 'error', 'text' =>  'Your file format is not accepted', 'file' => base64_decode($file_bf)));
        }
    }

    //UPLOAD DATA
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
                //excel
                'item_fg_number' => $data->val($i, 2),
                'item_rm_number' => $data->val($i, 3),
                'main_process' => $data->val($i, 4),
                'type' => $data->val($i, 5),
                'composition' => $data->val($i, 6)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/bom.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/bom.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/bom.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadCreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_number']]);
            $process_main = $this->crud->read('process_main', [], ["name" => $data['main_process']]);

            $bom = $this->crud->read('bom', [], ["item_fg_id" => @$item_fg->id, "item_rm_id" => @$item_rm->id]);
            $bomRev = $this->crud->read('bom', [], ["item_fg_id" => @$item_fg->id]);

            if(empty($bomRev->revision)){
                $revision = 0;
            }else{
                $revision = $bomRev->revision;
            }

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No EBWS " . $data['item_fg_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No EBWS " . $data['item_rm_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($process_main->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Main " . $data['main_process'] . " Not Found", "theme" => "error"));
            } elseif (!empty($bom->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part No " . $data['item_rm_number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "item_fg_id" => $item_fg->id,
                    "item_rm_id" => $item_rm->id,
                    "main_process" => $data['main_process'],
                    "type" => $data['type'],
                    "composition" => $data['composition'],
                );

                $dataFinal2 = array(
                    "created_by" => $this->session->username,
                    "created_date" => date("Y-m-d H:i:s"),
                    "item_fg_id" => $item_fg->id,
                    "item_rm_id" => $item_rm->id,
                    "main_process" => $data['main_process'],
                    "type" => $data['type'],
                    "revision" => $revision,
                    "composition" => $data['composition']
                );

                $this->db->insert("bom_log", $dataFinal2);

                $send = $this->crud->create('bom', $dataFinal);
                $this->db->update("bom", ["approved_to" => ""], ["item_fg_id" => @$item_fg->id, "item_rm_id" => @$item_rm->id]);
                echo $send;
            }
        }
    }

    // public function deleteBom(){
    //     $query = $this->db->query("SELECT a.item_fg_id, c.number_customer as item_fg_number, a.item_rm_id, b.number as item_rm_number, COUNT(a.id) as total 
    //         FROM bom a JOIN item_rm b ON a.item_rm_id = b.id 
    //         JOIN item_fg c ON a.item_fg_id = c.id
    //         GROUP BY a.item_fg_id, a.item_rm_id 
    //         HAVING COUNT(a.id) > 1
    //         LIMIT 1");
    //     $querys = $query->result_array();

    //     foreach ($querys as $query) {
    //         $item_fg_id = $query['item_fg_id'];
    //         $item_rm_id = $query['item_rm_id'];

    //         $bom = $this->db->query("SELECT a.id, a.item_fg_id, c.number_customer as item_fg_number, a.item_rm_id, b.number as item_rm_number
    //             FROM bom a JOIN item_rm b ON a.item_rm_id = b.id 
    //             JOIN item_fg c ON a.item_fg_id = c.id 
    //             WHERE item_fg_id = '$item_fg_id' and a.item_rm_id = '$item_rm_id'
    //             ORDER BY a.created_date desc");
    //         $boms = $bom->result_array();

    //         for ($i=1; $i < $query['total']; $i++) {
    //             echo "<p>".$boms[$i]['id']."</p>";

    //             $this->db->delete('bom', ["id" => $boms[$i]['id']]);
    //         }
    //     }
    // }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bom_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.number_customer, b.name as item_fg_name, c.number as item_rm_number, c.name as item_rm_name, c.item_family_id as product_family, c.uom as uom, , d.name as product_family_name, g.name as maker_name, b.status as status_fg');
        $this->db->from('bom a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        // $this->db->join('supplier_items f', 'a.item_rm_id = f.item_rm_id and f.share_order > 0', 'left');
        // $this->db->join('suppliers e', 'f.supplier_id = e.id', 'left');
        $this->db->join('makers g', 'c.maker_id = g.id', 'left');
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#bom {border-collapse: collapse;width: 100%;font-size: 12px;}#bom td, #bom th {border: 1px solid #ddd;padding: 2px;}#bom tr:nth-child(even){background-color: #f2f2f2;}#bom tr:hover {background-color: #ddd;}#bom th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>MASTER BILL OF MATERIAL</h3>
            </div>
        </center>
        
        <table id="bom" border="1">
            <tr>
                <th width="20">No</th>
                <th>Status</th>
                <th>Product No EBWS</th>
                <th>Product No Customer</th>
                <th>Description</th>
                <th>Part No EBWS</th>
                <th>Part No Supplier</th>
                <th>Description</th>
                <th>Maker</th>
                <th>Process Main</th>
                <th>Product Family</th>
                <th>Unit Of Measure</th>
                <th>QPA</th>
                <th>Revision</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['status_fg'] == 0){
                $status = "ACTIVE";
                $style = "color:green;";
            }else{
                $style = "color:red;";
                $status = "NOT ACTIVE";
            }
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td style='.$style.'>' . $status . '</td>
                    <td>' . $data['item_fg_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                    <td>' . $data['item_rm_name'] . '</td>
                    <td>' . $data['maker_name'] . '</td>
                    <td>' . $data['main_process'] . '</td>
                    <td>' . $data['product_family_name'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['composition'] . '</td>
                    <td>' . $data['revision'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_compare($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bom_compare_$format.xls");
        }
        $item_fg_id_source = base64_decode($this->input->get("item_fg_id_source"));
        $item_fg_id_compare = base64_decode($this->input->get("item_fg_id_compare"));
        $revision_source = base64_decode($this->input->get("revision_source"));
        $revision_compare = base64_decode($this->input->get("revision_compare"));

        // data head
        $item_fg_source = $this->crud->read('item_fg', ["id" => $item_fg_id_source]);
        $customer_source = $this->crud->read('customers', ["id" => @$item_fg_source->customer_id]);
        $circuit_source = $this->db->query("select count(*) as total_cct from wos where item_fg_id='$item_fg_id_source'")->row();
        $bom_source = $this->crud->read('bom_log', ["item_fg_id" => $item_fg_id_source, "revision" => $revision_source]);

        $item_fg_compare = $this->crud->read('item_fg', ["id" => $item_fg_id_compare]);
        $customer_compare = $this->crud->read('customers', ["id" => @$item_fg_compare->customer_id]);
        $circuit_compare = $this->db->query("select count(*) as total_cct from wos where item_fg_id='$item_fg_id_compare'")->row();
        $bom_compare = $this->crud->read('bom_log', ["item_fg_id" => $item_fg_id_compare, "revision" => $revision_compare]);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $records = $this->db->query("select item_rm_id from bom_log where item_fg_id in ('$item_fg_id_source','$item_fg_id_compare') and revision in ('$revision_source','$revision_compare') group by item_rm_id order by item_rm_id asc")->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <table id="customers" border="1">
            <tr>
                <td colspan="2"><b>Source</b></td>
                <td colspan="6" style="mso-number-format:\@;"><b>' . @$item_fg_source->number . ' | ' . @$item_fg_source->number_customer . '</b></td>
                <td colspan="2"><b>Compare</b></td>
                <td colspan="7" style="mso-number-format:\@;"><b>' . @$item_fg_compare->number . ' | ' . @$item_fg_compare->number_customer . '</b></td>
            </tr>
            <tr>
                <td colspan="2"><b>Customer</b></td>
                <td colspan="6"><b>' . @$customer_source->name . '</b></td>
                <td colspan="2"><b>Customer</b></td>
                <td colspan="7"><b>' . @$customer_compare->name . '</b></td>
            </tr>
            <tr>
                <td colspan="2"><b>Revision</b></td>
                <td colspan="6"><b>' . @$bom_source->revision . '</b></td>
                <td colspan="2"><b>Revision</b></td>
                <td colspan="7"><b>' . @$bom_compare->revision . '</b></td>
            </tr>
            <tr>
                <td colspan="2"><b>Last Rev</b></td>
                <td colspan="6"><b>' . @$bom_source->created_date . '</b></td>
                <td colspan="2"><b>Last Rev</b></td>
                <td colspan="7"><b>' . @$bom_compare->created_date . '</b></td>
            </tr>
            <tr>
                <td colspan="2"><b>Rev By</b></td>
                <td colspan="6"><b>' . @$bom_source->created_by . '</b></td>
                <td colspan="2"><b>Rev By</b></td>
                <td colspan="7"><b>' . @$bom_compare->created_by . '</b></td>
            </tr>
            <tr>
                <td colspan="2"><b>Circuit</b></td>
                <td colspan="6"><b>' . @$circuit_source->total_cct . '</b></td>
                <td colspan="2"><b>Circuit</b></td>
                <td colspan="7"><b>' . @$circuit_compare->total_cct . '</b></td>
            </tr>';
        $html .= '<tr>
        <th width="5">No</th>
        <th>Part No EBWS</th>
        <th>Part No Supplier</th>
        <th>Product Family</th>
        <th>Process Main</th>
        <th>Maker</th>
        <th>UoM</th>
        <th>QPA</th>
        <th width="5">No</th>
        <th>Part No EBWS</th>
        <th>Part No Supplier</th>
        <th>Product Family</th>
        <th>Process Main</th>
        <th>Maker</th>
        <th>UoM</th>
        <th>QPA</th>
        <th>Status</th>
        </tr>';

        $no = 1;
        foreach ($records as $record) {
            // assy source
            $bom_source = $this->db->query("
            select a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom,c.name as item_family_name, d.name as maker_name
            from bom_log a 
            join item_rm b on a.item_rm_id = b.id
            join item_familys c on b.item_family_id = c.id
            join makers d on b.maker_id = d.id
            where a.item_fg_id='$item_fg_id_source' and a.revision='$revision_source' and a.item_rm_id='" . $record['item_rm_id'] . "' and a.remove = 0
            ")->row();

            // assy compare
            $bom_compare = $this->db->query("
            select a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom,c.name as item_family_name, d.name as maker_name
            from bom_log a 
            join item_rm b on a.item_rm_id = b.id
            join item_familys c on b.item_family_id = c.id
            join makers d on b.maker_id = d.id
            where a.item_fg_id='$item_fg_id_compare' and a.revision='$revision_compare' and a.item_rm_id='" . $record['item_rm_id'] . "' and a.remove = 0
            ")->row();

            if (@$bom_source->item_rm_id == @$bom_compare->item_rm_id && @$bom_source->composition == @$bom_compare->composition) {
                $styleCell = "style='background:#53d636; color:white;'";
                $styleRow = "";
                $status = "<b>SAME</b>";
            } else {
                $styleCell = "style='background:#ff5f5f; color:white;'";
                $styleRow = "style='background:#FFF2AF;'";
                $status = "<b>DIFFERENT</b>";
            }

            $html .= '
            <tr ' . $styleRow . '>
                <td align="center">' . $no . '</td>
                <td style="mso-number-format:\@;">' . @$bom_source->item_rm_id . '</td>
                <td style="mso-number-format:\@;">' . @$bom_source->item_rm_number . '</td>
                <td>' . @$bom_source->item_family_name . '</td>
                <td>' . @$bom_source->main_process . '</td>
                <td>' . @$bom_source->maker_name . '</td>
                <td>' . @$bom_source->uom . '</td>
                <td align="right">' . @$bom_source->composition . '</td>
                
                <td align="center">' . $no . '</td>
                <td style="mso-number-format:\@;">' . @$bom_compare->item_rm_id . '</td>
                <td style="mso-number-format:\@;">' . @$bom_compare->item_rm_number . '</td>
                <td>' . @$bom_compare->item_family_name . '</td>
                <td>' . @$bom_compare->main_process . '</td>
                <td>' . @$bom_compare->maker_name . '</td>
                <td>' . @$bom_compare->uom . '</td>
                <td align="right">' . @$bom_compare->composition . '</td>
                <td align="center" ' . $styleCell . '>' . $status . '</td>
            </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
