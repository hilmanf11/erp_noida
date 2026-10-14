<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Item_rm extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Part No.', 'required|min_length[1]|max_length[100]|is_unique[item_rm.number]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/item_rm');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads($item_family_id = "")
    {
        //master/bom
        //planning/where_used
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name 
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN item_categories c ON a.item_category_id = c.id 
            WHERE b.id like '%$item_family_id%' and a.status = 0 and c.id IN ('C01')
            and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
            LIMIT 100");
        echo json_encode($send);
    }

    public function readNotHsn($item_family_id = "")
    {
        //master/bom
        //planning/where_used
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name 
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN item_categories c ON a.item_category_id = c.id 
            WHERE b.id like '%$item_family_id%' and a.status = 0 and c.id IN ('C01') and a.hsn_code != '-'
            and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
            LIMIT 100");
        echo json_encode($send);
    }

    public function readItemCategory($item_category = "")
    {
        // if($item_category == "ASSET"){
        //     $where_category = "and c.type IN ('OTHER','ASSET','SERVICE')";
        // }else{
        //     $where_category = "and c.type = '$item_category'";
        // }

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name 
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN item_categories c ON a.item_category_id = c.id 
            WHERE a.status = 0 and c.type = '$item_category' and a.approved_to = ''
            and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
            LIMIT 100");
        echo json_encode($send);
    }

    public function readNotRm($item_family_id = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name 
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id 
            JOIN item_categories c ON a.item_category_id = c.id 
            WHERE b.id like '%$item_family_id%' and c.id NOT IN ('C01','C08','C02') and a.status = 0 and a.approved_to = ''
            and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
            LIMIT 100");
        echo json_encode($send);
    }

    public function readNotAsset($item_family_id = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name 
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id 
            JOIN item_categories c ON a.item_category_id = c.id 
            WHERE b.id like '%$item_family_id%' and c.id NOT IN ('C01','C08','C02') 
            and a.status = 0 and a.approved_to = '' and a.type != 'ASSET'
            and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
            LIMIT 100");
        echo json_encode($send);
    }

    //GET DATA
    public function readAll($item_family_id = "")
    {
        //master/bom
        //planning/where_used
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name 
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN item_categories c ON a.item_category_id = c.id 
            WHERE b.id like '%$item_family_id%' and a.status = 0
            and a.approved_to = ''
            and (a.id like '%$post%' or a.number like '%$post%' or a.name like '%$post%')
            LIMIT 100");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name, d.number as hsn_number, d.name as hsn_name, d.igst, d.sgst, d.cgst, e.name as maker_name');
            $this->db->from('item_rm a');
            $this->db->join('item_categories b', 'a.item_category_id = b.id');
            $this->db->join('item_familys c', 'a.item_family_id = c.id');
            // $this->db->join('item_family_subs d', 'a.item_sub_family_id = d.id', 'left');
            $this->db->join('hsn d', 'a.hsn_code = d.number', 'left');
            $this->db->join('makers e', 'a.maker_id = e.id', 'left');
            // $this->db->where('b.number !=', 'FA');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "item_category_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "item_family_name") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "item_sub_family_name") {
                        $this->db->like("d.name", $filter->value);
                    } elseif ($filter->field == "maker_name") {
                        $this->db->like("e.name", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.id', 'ASC');
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
    //AUTO ID
    public function autoid()
    {
        $post = $this->input->post();
        $item_family_number = $post['item_family_number'];
        $kind = $post['kind'];
        $size = $post['size'];
        $color_1 = $post['color_1'];
        $color_2 = $post['color_2'];
        $male = $post['male'];
        $waterproof = $post['waterproof'];
        $platting = $post['platting'];
        $type = $post['type'];

        if($type == "LOCAL"){
            $type = 2;
        }elseif($type == "IMPORT"){
            $type = 1;
        }

        if($item_family_number == "1" || $item_family_number == "4"){
            $format = $item_family_number.$kind.$size.$color_1.$color_2.$type;
        }else if(in_array($item_family_number, ["7","8","9"])){
            $codes = $item_family_number.$kind.$color_1;
            $sql = $this->db->query("SELECT max(SUBSTRING(id, 6, 4)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 1) = '$item_family_number'");
            $row = $sql->row();
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = $row->kode;
            }
            $format = $codes . sprintf("%04s", $kode + 1) . $type;
        }else if(in_array($item_family_number, ["10","11","12","13","14","15","16","17","18","19"])){
            $codes = $item_family_number.$kind.$color_1;
            $sql = $this->db->query("SELECT max(SUBSTRING(id, 7, 3)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 2) = '$item_family_number'");
            $row = $sql->row();
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = $row->kode;
            }
            $format = $codes . sprintf("%03s", $kode + 1) . $type;
        }else if($item_family_number == "2"){
            $codes = $item_family_number.$male.$waterproof.$platting;
            $sql = $this->db->query("SELECT max(SUBSTRING(id, 6, 4)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 1) = '$item_family_number'");
            $row = $sql->row();
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = $row->kode;
            }
            $format = $codes . sprintf("%04s", $kode + 1) . $type;
        }else if($item_family_number == "3"){
            $codes = $item_family_number.$male.$waterproof.$color_1;
            $sql = $this->db->query("SELECT max(SUBSTRING(id, 6, 4)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 1) = '$item_family_number'");
            $row = $sql->row();
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = $row->kode;
            }
            $format = $codes . sprintf("%04s", $kode + 1) . $type;
        }else if($item_family_number == "5"){
            $format = $item_family_number.$kind."0".$size.$color_1."0".$type;
        }else if($item_family_number == "6"){
            $codes = $item_family_number.$kind.$size.$color_1;
            $sql = $this->db->query("SELECT max(SUBSTRING(id, 8, 2)) as kode FROM item_rm WHERE id like '%$codes%'");
            $row = $sql->row();
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = $row->kode;
            }
            $format = $codes . sprintf("%02s", $kode + 1) . $type;
        }else{
            $codes = $item_family_number;
            $sql = $this->db->query("SELECT max(SUBSTRING(id, 3, 7)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 2) = '$codes'");
            $row = $sql->row();
            
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = $row->kode;
            }

            $format = $codes . sprintf("%07s", $kode + 1) . $type;
            // $format = $codes ."000000". ($kode + 1) . $type;
        }

        echo json_encode(array("code" => $format));
    }

    // public function getSync()
    // {
    //     error_reporting(0);
        
    //     $items = $this->crud->reads('item_rm');
    //     $rowItem = array();
    //     foreach ($items as $item) {
    //         $rowItem[] = $item->number;
    //     }

    //     $this->pg = $this->load->database('dummy', TRUE);
    //     $this->pg->select("trim(a.item_id) as item_id, a.item_name, b.pfm_name, a.um_id");
    //     $this->pg->from("mst_item a");
    //     $this->pg->join("r_prodfam b", 'a.pfm_id = b.pfm_id');
    //     $this->pg->where("a.stscode_id", "01");
    //     $this->pg->where("a.pfm_id !=", "06");
    //     $this->pg->where_not_in("trim(a.item_id)", $rowItem);
    //     $totalRows = $this->pg->count_all_results('', false);
    //     $records = $this->pg->get()->result_array();

    //     //Mapping Data
    //     $result['total'] = $totalRows;
    //     $result = array_merge($result, $records);

    //     die(json_encode($result));
    // }

    public function createSync()
    {
        $data = $this->input->post('data');

        $supplier_item = $this->crud->read("supplier_items", [], ["item_rm_id" => $data['id']]);

        $moq = @$supplier_item->moq;
        $mpq = @$supplier_item->mpq;

        if($data['item_family_name'] == "WIRE"){
            $pfm_id = "01";
        }elseif($data['item_family_name'] == "TERMINAL"){
            $pfm_id = "02";
        }elseif($data['item_family_name'] == "NAME PLATE"){
            $pfm_id = "05";
        }elseif($data['item_family_name'] == "TUBE"){
            $pfm_id = "07";
        }else{
            $pfm_id = "04";
        }

        $this->pg = $this->load->database('dummy', TRUE);

        $this->pg->select("*");
        $this->pg->from("mst_item");
        $this->pg->where("item_id", $data['number']);
        $record = $this->pg->get()->row();

        if(empty($record)){
            $dataFinal = array(
                "item_id" => $data['number'],
                "um_id" => "01",
                "item_name" => $data['name'],
                "item_cct" => 0,
                "item_regno" => mt_rand(1000,9999),
                "item_regdate" => date("Y-m-d"),
                "stscode_id" => "01",
                "type_id" => "02",
                "pfm_id" => $pfm_id,
                "item_manldtm" => 0,
                "item_saftystock" => 0,
                "item_shrink" => 0,
                "item_minoq" => $moq,
                "item_muloq" => $mpq,
                "item_maxoq" => 1,
                "item_user" => "Vishal Sharma",
                "lotqty" => 0,
                "quick_recalculate" => 0,
                "ven_id" => "001",
                "st_sparepart" => false,
                "life_time" => "365",
            );

            $this->pg->insert("mst_item", $dataFinal);
            echo json_encode(array("title" => "Good Job", "message" => "Save Data Completed", "theme" => "success"));
        }else{
            echo json_encode(array("title" => "Duplicate", "message" => "Data Cannot Save in MRP Banshu", "theme" => "error"));
        }
    }
    
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $foto = $this->crud->upload('foto', ["jpg", "png", "jpeg"], 'assets/image/item_rm/');
                $postFinal = array_merge($post, ["foto" => $foto]);

                $send = $this->crud->create('item_rm', $postFinal);
                echo $send;
            } else {
                show_error(validation_errors());
            }
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
            $foto = $this->crud->upload('foto', ["jpg", "png", "jpeg"], 'assets/image/item_rm/', ["id" => $id], "item_rm", "foto");
            $postFinal = array_merge($post, ["foto" => $foto]);

            $send = $this->crud->update('item_rm', ["id" => $id], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('item_rm', $data);
        echo $send;
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
                'number' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'uom' => $data->val($i, 4),
                'item_category_number' => $data->val($i, 5),
                'item_family_number' => $data->val($i, 6),
                'kind' => $data->val($i, 7),
                'size' => $data->val($i, 8),
                'color_1' => $data->val($i, 9),
                'color_2' => $data->val($i, 10),
                'male' => $data->val($i, 11),
                'waterproof' => $data->val($i, 12),
                'platting' => $data->val($i, 13),
                'hsn_code' => $data->val($i, 14),
                // 'account_number' => $data->val($i, 15),
                'description' => $data->val($i, 16),
                'supply' => $data->val($i, 17),
                'type' => $data->val($i, 18),
                'maker_code' => $data->val($i, 19),
                'status' => $data->val($i, 20)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/item_rm.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_rm.txt";
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
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['number']]);
            $item_rm_name = $this->crud->read('item_rm', ["name" => $data['name']]);
            $category = $this->crud->read('item_categories', [], ["number" => $data['item_category_number']]);
            $product_family = $this->crud->read('item_familys', [], ["number" => $data['item_family_number']]);
            $kind = $this->crud->read('item_kinds', [], ["number" => $data['kind']]);
            $size = $this->crud->read('item_sizes', [], ["number" => $data['size']]);
            $color1 = $this->crud->read('item_colors', [], ["number" => $data['color_1']]);
            $color2 = $this->crud->read('item_colors', [], ["number" => $data['color_2']]);
            $platting = $this->crud->read('item_platting', [], ["number" => $data['platting']]);
            $hsn = $this->crud->read('hsn', [], ["number" => $data['hsn_code']]);
            $maker = $this->crud->read('makers', [], ["number" => $data['maker_code']]);
            $account_coa = $this->crud->read('account_coa', [], ["account_number" => @$product_family->account_number]);

            if (empty($category->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Category " . $data['item_category_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($product_family->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $data['item_family_number'] . " Not Found", "theme" => "error"));
            // } elseif ($data['item_category_number'] == "FA") {
            //     echo json_encode(array("title" => "Fixed Asset", "message" => "Category " . $data['item_category_number'] . " Cannot Create in Master RM", "theme" => "error"));
            } elseif (empty(@$account_coa->account_number) && $category->type != "ASSET") {
                echo json_encode(array("title" => "Not Found", "message" => "COA No " . @$data['account_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($hsn->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "HSN Code " . $data['hsn_code'] . " Not Found", "theme" => "error"));
            } elseif (empty($kind->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "Kind Code " . $data['kind'] . " Not Found", "theme" => "error"));
            } elseif (empty($size->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "Size Code " . $data['size'] . " Not Found", "theme" => "error"));
            } elseif (empty($color1->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "Color 1 Code " . $data['color_1'] . " Not Found", "theme" => "error"));
            } elseif (empty($color2->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "Color 2 Code " . $data['color_2'] . " Not Found", "theme" => "error"));
            } elseif (empty($platting->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "Platting Code " . $data['platting'] . " Not Found", "theme" => "error"));
            } elseif (empty($maker->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "Maker Code " . $data['maker_code'] . " Not Found", "theme" => "error"));
            } elseif (!empty($item_rm->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Part No. " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } elseif (!empty($item_rm_name->name)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Part Name " . $data['name'] . " Similar Data in Item master", "theme" => "error"));
            } else {
                $item_family_number = $product_family->number;
                $kind = $data['kind'];
                $size = $data['size'];
                $color_1 = $data['color_1'];
                $color_2 = $data['color_2'];
                $male = $data['male'];
                $waterproof = $data['waterproof'];
                $platting = $data['platting'];
                $type = $data['type'];

                if($type == "LOCAL"){
                    $type = 2;
                }else{
                    $type = 1;
                }

                if($item_family_number == "1" || $item_family_number == "4"){
                    $format = $item_family_number.$kind.$size.$color_1.$color_2.$type;
                }else if(in_array($item_family_number, ["7","8","9"])){
                    $codes = $item_family_number.$kind.$color_1;
                    $sql = $this->db->query("SELECT max(SUBSTRING(id, 6, 4)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 1) = '$item_family_number'");
                    $row = $sql->row();
                    if ($row->kode == "") {
                        $kode = 0;
                    } else {
                        $kode = $row->kode;
                    }
                    $format = $codes . sprintf("%04s", $kode + 1) . $type;
                }else if(in_array($item_family_number, ["10","11","12","13","14","15","16","17","18","19"])){
                    $codes = $item_family_number.$kind.$color_1;
                    $sql = $this->db->query("SELECT max(SUBSTRING(id, 7, 3)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 2) = '$item_family_number'");
                    $row = $sql->row();
                    if ($row->kode == "") {
                        $kode = 0;
                    } else {
                        $kode = $row->kode;
                    }
                    $format = $codes . sprintf("%03s", $kode + 1) . $type;
                }else if($item_family_number == "2"){
                    $codes = $item_family_number.$male.$waterproof.$platting;
                    $sql = $this->db->query("SELECT max(SUBSTRING(id, 6, 4)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 1) = '$item_family_number'");
                    $row = $sql->row();
                    if ($row->kode == "") {
                        $kode = 0;
                    } else {
                        $kode = $row->kode;
                    }
                    $format = $codes . sprintf("%04s", $kode + 1) . $type;
                }else if($item_family_number == "3"){
                    $codes = $item_family_number.$male.$waterproof.$color_1;
                    $sql = $this->db->query("SELECT max(SUBSTRING(id, 6, 4)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 1) = '$item_family_number'");
                    $row = $sql->row();
                    if ($row->kode == "") {
                        $kode = 0;
                    } else {
                        $kode = $row->kode;
                    }
                    $format = $codes . sprintf("%04s", $kode + 1) . $type;
                }else if($item_family_number == "5"){
                    $format = $item_family_number.$kind."0".$size.$color_1."0".$type;
                }else if($item_family_number == "6"){
                    $codes = $item_family_number.$kind.$size.$color_1;
                    $sql = $this->db->query("SELECT max(SUBSTRING(id, 8, 2)) as kode FROM item_rm WHERE id like '%$codes%'");
                    $row = $sql->row();
                    if ($row->kode == "") {
                        $kode = 0;
                    } else {
                        $kode = $row->kode;
                    }
                    $format = $codes . sprintf("%02s", $kode + 1) . $type;
                }else{
                    $codes = $item_family_number;
                    $sql = $this->db->query("SELECT max(SUBSTRING(id, 3, 7)) as kode FROM item_rm WHERE SUBSTRING(id, 1, 2) = '$codes'");
                    $row = $sql->row();
                    
                    if ($row->kode == "") {
                        $kode = 0;
                    } else {
                        $kode = $row->kode;
                    }

                    $format = $codes . sprintf("%07s", $kode + 1) . $type;
                }

                $dataFinal = array(
                    //field
                    "id" => $format,
                    "number" => $data['number'],
                    "name" => $data['name'],
                    "uom" => $data['uom'],
                    "item_category_id" => $category->id,
                    "item_family_id" => $product_family->id,
                    "maker_id" => $maker->id,
                    "kind" => $data['kind'],
                    "size" => $data['size'],
                    "color_1" => $data['color_1'],
                    "color_2" => $data['color_2'],
                    "male" => $data['male'],
                    "waterproof" => $data['waterproof'],
                    "platting" => $data['platting'],
                    "hsn_code" => $data['hsn_code'],
                    "account_number" => $product_family->account_number,
                    "account_name" => $product_family->account_name,
                    "description" => $data['description'],
                    "supply" => $data['supply'],
                    "type" => $data['type'],
                    "status" => $data['status'],
                );

                $send   = $this->crud->create('item_rm', $dataFinal);
                echo $send;
            }
        }
    }
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=item_rm_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name, d.number as hsn_number, d.name as hsn_name, d.igst, d.sgst, d.cgst, e.name as maker_name');
        $this->db->from('item_rm a');
        $this->db->join('item_categories b', 'a.item_category_id = b.id');
        $this->db->join('item_familys c', 'a.item_family_id = c.id');
        $this->db->join('hsn d', 'a.hsn_code = d.number', 'left');
        $this->db->join('makers e', 'a.maker_id = e.id', 'left');
        // $this->db->where('b.number !=', 'FA');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#item_rm {border-collapse: collapse;width: 100%;font-size: 12px;}#item_rm td, #item_rm th {border: 1px solid #ddd;padding: 2px;}#item_rm tr:nth-child(even){background-color: #f2f2f2;}#item_rm tr:hover {background-color: #ddd;}#item_rm th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER ITEM RAW MATERIAL</h3>
            </div>
        </center>
        
        <table id="item_rm" border="1">
            <tr>
                <th width="20">No</th>
                <th>Part ID</th>
                <th>Part No EBWS</th>
                <th>Description</th>
                <th>Maker</th>
                <th>Type</th>
                <th>UOM</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>HSN Code</th>
                <th>IGST %</th>
                <th>SGST %</th>
                <th>CGST %</th>
                <th>COA No</th>
                <th>COA Name</th>
                <th>Remark</th>
                <th>Supply</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['name'] . '</td>
                        <td>' . $data['maker_name'] . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['item_category_name'] . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . $data['hsn_code'] . '</td>
                        <td>' . $data['igst'] . '</td>
                        <td>' . $data['sgst'] . '</td>
                        <td>' . $data['cgst'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['account_name'] . '</td>
                        <td>' . $data['description'] . '</td>
                        <td>' . $data['supply'] . '</td>
                        <td>' . $data['status'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
