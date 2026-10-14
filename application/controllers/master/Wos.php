<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Wos extends CI_Controller
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
        $this->form_validation->set_rules('circuit_no', 'Circuit No', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/wos');
        } else {
            redirect('error_access');
        }
    }

    public function readProducts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id as item_fg_id, b.number as item_fg_number, b.name as item_fg_name
            FROM wos a 
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE b.number like '%$post%' or b.name like '$post'
            GROUP BY a.item_fg_id
            ORDER BY b.number asc");
        echo json_encode($send);
    }

    public function readCircuits($item_fg_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.circuit_no
            FROM wos a 
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.item_fg_id = '$item_fg_id' and b.number like '%$post%' or b.name like '$post'
            GROUP BY a.circuit_no
            ORDER BY a.circuit_no asc");
        echo json_encode($send);
    }

    function validateInput($input) {
        $hasLetter = preg_match('/[a-zA-Z]/', $input);
        $hasNumber = preg_match('/[0-9]/', $input);

        if ($hasLetter && !$hasNumber) {
            return false;
        }else{
            return true;
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            // Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
                b.number as item_fg_number,
                b.number,
                b.number_customer, 
                b.name as item_fg_name');
            $this->db->from('wos a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_fg_number"){
                        $this->db->like("b.number", $filter->value);
                    }elseif($filter->field == "item_fg_name"){
                        $this->db->like("b.name", $filter->value);
                    }elseif($filter->field == "number_customer"){
                        $this->db->like("b.number_customer", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.item_fg_id', 'asc');
            $this->db->order_by('CAST(a.circuit_no AS UNSIGNED)', 'asc');
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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $wos = $this->crud->read("wos", [], ["circuit_no" => $post['circuit_no'], "item_fg_id" => $post['item_fg_id']]);

                if(empty($wos)){
                    $send   = $this->crud->create('wos', $post);
                    echo $send;
                }else{
                    show_error("Duplicate Data");
                }
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
            $send = $this->crud->update('wos', ["id" => $id], $post);

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('wos', $data);
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
                'item_fg_number' => $data->val($i, 2),
                'circuit_no' => $data->val($i, 3),
                'kind' => $data->val($i, 4),
                'size' => $data->val($i, 5),
                'color' => $data->val($i, 6),
                'line_color' => $data->val($i, 7),
                'cl' => $data->val($i, 8),
                'jno' => $data->val($i, 9),
                'twist' => $data->val($i, 10),
                'mno' => $data->val($i, 11),
                'dc_1' => $data->val($i, 12),
                'terminal_1' => $data->val($i, 13),
                'strip_1' => $data->val($i, 14),
                'chc_1' => $data->val($i, 15),
                'chi_1' => $data->val($i, 16),
                'cwc_1' => $data->val($i, 17),
                'cwi_1' => $data->val($i, 18),
                'dc_2' => $data->val($i, 19),
                'terminal_2' => $data->val($i, 20),
                'strip_2' => $data->val($i, 21),
                'chc_2' => $data->val($i, 22),
                'chi_2' => $data->val($i, 23),
                'cwc_2' => $data->val($i, 24),
                'cwi_2' => $data->val($i, 25),
                'accessories_1' => $data->val($i, 26),
                'accessories_2' => $data->val($i, 27),
                'mark_r' => $data->val($i, 28),
                'mark_f' => $data->val($i, 29),
                'tube_1' => $data->val($i, 30),
                'tube_size_1' => $data->val($i, 31),
                'end_1' => $data->val($i, 32),
                'tube_2' => $data->val($i, 33),
                'tube_size_2' => $data->val($i, 34),
                'end_2' => $data->val($i, 35),
                'note_r' => $data->val($i, 36),
                'note_f' => $data->val($i, 37),
                'cct_order' => $data->val($i, 38),
                'cct_range' => $data->val($i, 39),
                'mc' => $data->val($i, 40),
                'order' => $data->val($i, 41),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/wos.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/wos.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/wos.txt";
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

            $item_fg = $this->crud->read('item_fg', [], ["number_customer" => $data['item_fg_number']]);
            $wos = $this->crud->read('wos', [], ["item_fg_id" => @$item_fg->id, "circuit_no" => $data['circuit_no']]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_fg_number'] . " is Not Found", "theme" => "error"));
            }elseif ($this->validateInput($data['circuit_no']) === false){
                echo json_encode(array("title" => "Not Allowed", "message" => "Circuit No " . $data['circuit_no'] . " should not contain only letters", "theme" => "error"));
            }elseif(empty($data['cct_range']) && empty($data['cct_order'])){
                echo json_encode(array("title" => "Empty", "message" => "Product No " . $data['item_fg_number'] . " CCT Range and CCT Order still Empty", "theme" => "error"));
            }elseif(!empty($wos->item_fg_id)){
                $send = $this->crud->update('wos', ["item_fg_id" => @$item_fg->id, "circuit_no" => $data['circuit_no']], $data);
                echo $send;
            } else {
                $data["item_fg_id"] = $item_fg->id;
                unset($data["item_fg_number"]);

                $send = $this->crud->create('wos', $data);
                echo $send;
            }
        }
    }

    public function createSync()
    {
        $data = $this->input->post('data');
        $this->dummy = $this->load->database('dummy', TRUE);

        $this->dummy->select("mstwos_assyno");
        $this->dummy->from("wip_mst_wos");
        $this->dummy->where("mstwos_assyno", $data['number_customer']);
        $this->dummy->where("mstwos_circuitno", $data['circuit_no']);
        $read = $this->dummy->get()->result_array();

        $this->dummy->select("cd_machine");
        $this->dummy->from("wip_mst_machine");
        $this->dummy->where("cd_machine", $data['mc']);
        $machine = $this->dummy->get()->result_array();

        $whereDate = date("ym");
        $sqlGetID	= $this->dummy->query("SELECT max(mstwos_idwos) as kode FROM wip_mst_wos");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        $getDate = substr($rowID->kode, 0, 4);
        
        if($whereDate == $getDate){
            $urutan = (int) substr($kode, 4);
            $urutan++;
            $autoID	= sprintf("%05s", $urutan);
            $autoNo = $whereDate.$autoID;
        }else{
            $urutan = (int) substr($kode, 4);
            $autoID = sprintf("%05s", $urutan + 1);
            $autoNo = $whereDate.$autoID;
        }

        if(count($machine) == 0 && $data['mc'] != ""){
            die(json_encode(array("title" => "Failed", "message" => @$data['number_customer'] . " | ".@$data['circuit_no']." | Cannot Process Because Machine Code not same with Banshu ERP", "theme" => "error")));
        }


        $dataFinal = array(
            "mstwos_idwos" => $autoNo,
            "mstwos_assyno" => @$data['number_customer'],
            "mstwos_circuitno" => @$data['circuit_no'],
            "kind" => @$data['kind'],
            "size" => @$data['size'],
            "color" => @$data['color'],
            "line_colour" => @$data['line_color'],
            "tube_size_r" => @$data['tube_size_1'],
            "tube_size_f" => @$data['tube_size_2'],
            "length" => @$data['cl'],
            "joint_no" => @$data['jno'],
            "twist" => @$data['twist'],
            "mach1" => @$data['mno'],
            "double_crimping_r" => @$data['dc_1'],
            "double_crimping_f" => @$data['dc_2'],
            "terminal_name_r" => @$data['terminal_1'],
            "terminal_name_f" => @$data['terminal_2'],
            "stripping_r" => @$data['strip_1'],
            "stripping_f" => @$data['strip_2'],
            "chc_r" => @$data['chc_1'],
            "chc_f" => @$data['chc_2'],
            "cwc_r" => @$data['cwc_1'],
            "cwc_f" => @$data['cwc_2'],
            "chi_r" => @$data['chi_1'],
            "chi_f" => @$data['chi_2'],
            "cwi_r" => @$data['cwi_1'],
            "cwi_f" => @$data['cwi_2'],
            "standard_conductor_r" => @$data['chc_1'],
            "standard_conductor_f" => @$data['chc_2'],
            "standard_insulation_r" => @$data['chi_1'],
            "standard_insulation_f" => @$data['chi_2'],
            "ass_number_r" => @$data['accessories_1'],
            "ass_number_f" => @$data['accessories_2'],
            "ass_number_r2" => "",
            "ass_number_f2" => "",
            "ass_code_r" => @$data['accessories_1'],
            "ass_code_f" => @$data['accessories_2'],
            "mark_r" => @$data['mark_r'],
            "mark_f" => @$data['mark_f'],
            "tube_r" => @$data['tube_1'],
            "tube_f" => @$data['tube_2'],
            "drawing_r" => @$data['end_1'],
            "drawing_f" => @$data['end_2'],
            "mstwos_note1" => @$data['note_r'],
            "mstwos_note2" => @$data['note_f'],
            "cct_order" => @$data['cct_order'],
            "cct_range" => @$data['cct_range'],
            "no_mc" => @$data['mc'],
            "no_urut_proses" => @$data['order'],
            "ebws_partno" => @$data['number'],
        );

        if (empty($read)) {
            $this->dummy->insert('wip_mst_wos', $dataFinal);
            echo json_encode(array("title" => "Success", "message" => @$data['number_customer'] . " | ".@$data['circuit_no']." | Create Data Successfully", "theme" => "success"));
        } else {
            $this->dummy->update('wip_mst_wos', [
                "kind" => @$data['kind'],
                "size" => @$data['size'],
                "color" => @$data['color'],
                "line_colour" => @$data['line_color'],
                "tube_size_r" => @$data['tube_size_1'],
                "tube_size_f" => @$data['tube_size_2'],
                "length" => @$data['cl'],
                "joint_no" => @$data['jno'],
                "twist" => @$data['twist'],
                "mach1" => @$data['mno'],
                "double_crimping_r" => @$data['dc_1'],
                "double_crimping_f" => @$data['dc_2'],
                "terminal_name_r" => @$data['terminal_1'],
                "terminal_name_f" => @$data['terminal_2'],
                "stripping_r" => @$data['strip_1'],
                "stripping_f" => @$data['strip_2'],
                "chc_r" => @$data['chc_1'],
                "chc_f" => @$data['chc_2'],
                "cwc_r" => @$data['cwc_1'],
                "cwc_f" => @$data['cwc_2'],
                "chi_r" => @$data['chi_1'],
                "chi_f" => @$data['chi_2'],
                "cwi_r" => @$data['cwi_1'],
                "cwi_f" => @$data['cwi_2'],
                "standard_conductor_r" => @$data['chc_1'],
                "standard_conductor_f" => @$data['chc_2'],
                "standard_insulation_r" => @$data['chi_1'],
                "standard_insulation_f" => @$data['chi_2'],
                "ass_number_r" => @$data['accessories_1'],
                "ass_number_f" => @$data['accessories_2'],
                "ass_number_r2" => "",
                "ass_number_f2" => "",
                "ass_code_r" => @$data['accessories_1'],
                "ass_code_f" => @$data['accessories_2'],
                "mark_r" => @$data['mark_r'],
                "mark_f" => @$data['mark_f'],
                "tube_r" => @$data['tube_1'],
                "tube_f" => @$data['tube_2'],
                "drawing_r" => @$data['end_1'],
                "drawing_f" => @$data['end_2'],
                "mstwos_note1" => @$data['note_r'],
                "mstwos_note2" => @$data['note_f'],
                "cct_order" => @$data['cct_order'],
                "cct_range" => @$data['cct_range'],
                "no_mc" => @$data['mc'],
                "no_urut_proses" => @$data['order'],
                "ebws_partno" => @$data['number']
            ], ["mstwos_assyno" => $data['number_customer'], "mstwos_circuitno" => $data['circuit_no']]);

            echo json_encode(array("title" => "Updated", "message" => @$data['number_customer'] . " | ".@$data['circuit_no']." | Updated Data Successfully", "theme" => "success"));
        }
    }

    //PRINT & EXCEL DATA
    // public function print($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=wos_$format.xls");
    //     }
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     $this->db->select('a.*, 
    //         b.number as item_fg_number,
    //         b.number_customer, 
    //         b.name as item_fg_name');
    //     $this->db->from('wos a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->order_by('a.item_fg_id', 'asc');
    //     $this->db->order_by('a.circuit_no', 'asc');
    //     $records = $this->db->get()->result_array();

    //     $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
    //     <center>
    //         <div style="float: left; font-size: 12px; text-align: left;">
    //             <table style="width: 100%;">
    //                 <tr>
    //                     <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
    //                         <img src="' . $config->favicon . '" width="30">
    //                     </td>
    //                     <td style="font-size: 14px; text-align: left; margin:2px;">
    //                         <b>' . $config->name . '</b><br>
    //                         <small>'.$config->description.'</small>
    //                     </td>
    //                 </tr>
    //             </table>
    //         </div>
    //         <div style="float: right; font-size: 12px; text-align: right;">
    //             Print Date ' . date("d M Y H:m:s") . ' <br>
    //             Print By ' . $this->session->username . '  
    //         </div>
    //         <br><br>
    //         <div style="float: centet; font-size: 16px; text-align: center;">
    //             <h3>MASTER WOS</h3>
    //         </div>
    //     </center>
        
    //     <table id="customers" border="1">
    //         <tr>
    //             <th width="20">No</th>
    //             <th>Product No EBWS</th>
    //             <th>Part No Customer</th>
    //             <th>Description</th>
    //             <th>Circuit No</th>
    //             <th>Kind</th>
    //             <th>Size</th>
    //             <th>Base Color</th>
    //             <th>Line 1 Color</th>
    //             <th>C/L</th>
    //             <th>J. No</th>
    //             <th>Twist</th>
    //             <th>M. No</th>
    //             <th>D/C 1</th>
    //             <th>Terminal 1</th>
    //             <th>Strip 1</th>
    //             <th>CH/C 1</th>
    //             <th>CW/C 1</th>
    //             <th>CH/I 1</th>
    //             <th>CW/I 1</th>
    //             <th>D/C 2</th>
    //             <th>Terminal 2</th>
    //             <th>Strip 2</th>
    //             <th>CH/C 2</th>
    //             <th>CW/C 2</th>
    //             <th>CH/I 2</th>
    //             <th>CW/I 2</th>
    //             <th>Accessories 1</th>
    //             <th>Accessories 2</th>
    //             <th>Mark R</th>
    //             <th>Mark F</th>
    //             <th>Tube 1</th>
    //             <th>Tube Size 1</th>
    //             <th>Tube 2</th>
    //             <th>Tube Size 2</th>
    //             <th>End 1</th>
    //             <th>End 2</th>
    //             <th>Note R</th>
    //             <th>Note F</th>
    //             <th>CCT Order</th>
    //             <th>CCT Range</th>
    //             <th>MC No</th>
    //             <th>Process<br>Sequence 1</th>
    //         </tr>';
    //     $no = 1;
    //     foreach ($records as $data) {
    //         $html .= '<tr>
    //                 <td>' . $no . '</td>
    //                 <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
    //                 <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
    //                 <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
    //                 <td>' . $data['circuit_no'] . '</td>
    //                 <td>' . $data['kind'] . '</td>
    //                 <td>' . $data['size'] . '</td>
    //                 <td>' . $data['color'] . '</td>
    //                 <td>' . $data['line_color'] . '</td>
    //                 <td>' . $data['cl'] . '</td>
    //                 <td>' . $data['jno'] . '</td>
    //                 <td>' . $data['twist'] . '</td>
    //                 <td>' . $data['mno'] . '</td>
    //                 <td>' . $data['dc_1'] . '</td>
    //                 <td>' . $data['terminal_1'] . '</td>
    //                 <td>' . $data['strip_1'] . '</td>
    //                 <td>' . $data['chc_1'] . '</td>
    //                 <td>' . $data['cwc_1'] . '</td>
    //                 <td>' . $data['chi_1'] . '</td>
    //                 <td>' . $data['cwi_1'] . '</td>
    //                 <td>' . $data['dc_2'] . '</td>
    //                 <td>' . $data['terminal_2'] . '</td>
    //                 <td>' . $data['strip_2'] . '</td>
    //                 <td>' . $data['chc_2'] . '</td>
    //                 <td>' . $data['cwc_2'] . '</td>
    //                 <td>' . $data['chi_2'] . '</td>
    //                 <td>' . $data['cwi_2'] . '</td>
    //                 <td>' . $data['accessories_1'] . '</td>
    //                 <td>' . $data['accessories_2'] . '</td>
    //                 <td>' . $data['mark_r'] . '</td>
    //                 <td>' . $data['mark_f'] . '</td>
    //                 <td>' . $data['tube_1'] . '</td>
    //                 <td>' . $data['tube_size_1'] . '</td>
    //                 <td>' . $data['tube_2'] . '</td>
    //                 <td>' . $data['tube_size_2'] . '</td>
    //                 <td>' . $data['end_1'] . '</td>
    //                 <td>' . $data['end_2'] . '</td>
    //                 <td>' . $data['note_r'] . '</td>
    //                 <td>' . $data['note_f'] . '</td>
    //                 <td>' . $data['cct_order'] . '</td>
    //                 <td>' . $data['cct_range'] . '</td>
    //                 <td>' . $data['mc'] . '</td>
    //                 <td>' . $data['order'] . '</td>';
    //         $no++;
    //     }
    //     $html .= '</table></body></html>';
    //     echo $html;
    // }
}
