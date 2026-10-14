<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Os_po extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[os_po.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/os_po');
        } else {
            redirect('error_access');
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->crud->query("SELECT DISTINCT revision FROM os_po WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
        echo json_encode($data);
    }

    public function datatables()
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
            $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));

            $this->dummy->select("b.item_id, b.item_name, c.pfm_name, 
            (CASE WHEN (d.poc_reqqty - a.por_receiveqty) < 0 THEN 0 ELSE (d.poc_reqqty - a.por_receiveqty) END) as stock");
            $this->dummy->from("(SELECT item_id, SUM(por_receiveqty) as por_receiveqty FROM por WHERE por_date <= '$filter_cutoff' GROUP BY item_id) a");
            $this->dummy->join('mst_item b', "a.item_id = b.item_id and b.stscode_id = '01'");
            $this->dummy->join('r_prodfam c', "b.pfm_id = c.pfm_id and c.prod_group = 'Raw Material' and c.pfm_id != '05'");
            // $this->dummy->join("(SELECT DISTINCT por_pocid, item_id, por_pocreqqty FROM por WHERE por_date <= '$filter_cutoff' ORDER by item_id asc) d", "b.item_id = d.item_id");
            $this->dummy->join("(SELECT item_id, SUM(poc_reqqty) as poc_reqqty from poc where poc_date <= '$filter_cutoff' GROUP BY item_id) d", "b.item_id = d.item_id");
            $this->dummy->like('b.pfm_id', $filter_product_family);
            $this->dummy->like('b.item_id', $filter_product_no);
            $this->dummy->group_by('b.item_id');
            $this->dummy->group_by('b.item_name');
            $this->dummy->group_by('c.pfm_name');
            $this->dummy->group_by('a.por_receiveqty');
            $this->dummy->group_by('d.poc_reqqty');
            $this->dummy->order_by('c.pfm_name', 'asc');
            $this->dummy->order_by('(d.poc_reqqty  - a.por_receiveqty)', 'asc');
            $items = $this->dummy->get()->result_array();

            $arr = array();
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $item_name = $item['item_name'];
                $pfm_name = $item['pfm_name'];
                $qty = $item['stock'];

                $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
                $os_po = $this->crud->read('os_po', [], [
                    "item_rm_id" => @$item_rm->id,
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision
                ]);

                if ($os_po) {
                    if($os_po->status == "EMPTY"){
                        $status = "GENERATE";
                    }else{
                        $status = $os_po->status;
                    }

                    $arr[] = array(
                        "id" => $os_po->id,
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $cutoff,
                        "product_no" => $item_id,
                        "product_name" => $item_name,
                        "product_family" => $pfm_name,
                        "qty" => round($qty),
                        "actual" => round($os_po->actual),
                        "status" => $status,
                        "created_by" => $os_po->created_by,
                        "created_date" => $os_po->created_date,
                        "updated_by" => $os_po->updated_by,
                        "updated_date" => $os_po->updated_date,
                    );
                } else {
                    $arr[] = array(
                        "id" => "",
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $cutoff,
                        "product_no" => $item_id,
                        "product_name" => $item_name,
                        "product_family" => $pfm_name,
                        "qty" => round($qty),
                        "actual" => round($qty),
                        "status" => "EMPTY",
                    );
                }
            }

            echo json_encode($arr);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $item_id = $post['product_no'];

            $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
            if(!empty($item_rm->id)){
                $os_po = $this->crud->reads('os_po', [], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => @$item_rm->id
                ]);

                $postFinal = array(
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "cutoff" => $post['cutoff'],
                    "item_rm_id" => @$item_rm->id,
                    "qty" => $post['qty'],
                    "actual" => $post['actual'],
                    "status" => $post['status'],
                );

                if (count($os_po) > 0) {
                    $this->crud->update('os_po', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "item_rm_id" => @$item_rm->id
                    ], $postFinal);

                    echo json_encode(array("title" => "Good Job", "message" => "Part No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $this->crud->create('os_po', $postFinal);
                    echo json_encode(array("title" => "Good Job", "message" => "Part No " . $post['product_no'] . " Saved Successfully", "theme" => "success"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Part No " . $post['product_no'] . " Cannot Saved! because this part not available in master item RM", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('os_po', $data);
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

        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                "cutoff" => $data->val(4, 3),
                'product_no' => $data->val($i, 2),
                'product_name' => $data->val($i, 3),
                'qty' => $data->val($i, 4),
                'status' => "UPLOAD"
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $p_month = $post['p_month'];
            $p_year = $post['p_year'];
            $revision = $post['revision'];
            $cutoff = $post['cutoff'];
            $item_id = $post['product_no'];

            $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
            if(!empty($item_rm->id)){
                $os_po = $this->crud->reads('os_po', [], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $item_rm->id
                ]);
    
                $postFinal = array(
                    "p_month" => $p_month,
                    "p_year" => $p_year,
                    "revision" => $revision,
                    "cutoff" => $cutoff,
                    "item_rm_id" => @$item_rm->id,
                    "actual" => $post['qty'],
                    "status" => $post['status']
                );

                if (count($os_po) > 0) {
                    $this->crud->update('os_po', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "item_rm_id" => @$item_rm->id
                    ], $postFinal);

                    echo json_encode(array("title" => "Good Job", "message" => "Part No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Part No " . $post['product_no'] . " Not Generate", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Part No " . $post['product_no'] . " Cannot Saved! because this part not available in master item RM", "theme" => "error"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/os_po.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/os_po.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/os_po.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function print($option = "")
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=outstanding_po_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

        $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));

        $this->dummy->select("b.item_id, b.item_name, c.pfm_name, 
        (CASE WHEN (d.poc_reqqty - a.por_receiveqty) < 0 THEN 0 ELSE (d.poc_reqqty - a.por_receiveqty) END) as stock");
        $this->dummy->from("(SELECT item_id, SUM(por_receiveqty) as por_receiveqty FROM por WHERE por_date <= '$filter_cutoff' GROUP BY item_id) a");
        $this->dummy->join('mst_item b', "a.item_id = b.item_id and b.stscode_id = '01'");
        $this->dummy->join('r_prodfam c', "b.pfm_id = c.pfm_id and c.prod_group = 'Raw Material' and c.pfm_id != '05'");
        // $this->dummy->join("(SELECT DISTINCT por_pocid, item_id, por_pocreqqty FROM por WHERE por_date <= '$filter_cutoff' ORDER by item_id asc) d", "b.item_id = d.item_id");
        $this->dummy->join("(SELECT item_id, SUM(poc_reqqty) as poc_reqqty from poc where poc_date <= '$filter_cutoff' GROUP BY item_id) d", "b.item_id = d.item_id");
        $this->dummy->like('b.pfm_id', $filter_product_family);
        $this->dummy->like('b.item_id', $filter_product_no);
        $this->dummy->group_by('b.item_id');
        $this->dummy->group_by('b.item_name');
        $this->dummy->group_by('c.pfm_name');
        $this->dummy->group_by('a.por_receiveqty');
        $this->dummy->group_by('d.poc_reqqty');
        $this->dummy->order_by('c.pfm_name', 'asc');
        $this->dummy->order_by('(d.poc_reqqty  - a.por_receiveqty)', 'asc');
        $items = $this->dummy->get()->result_array();

        $records = array();
        foreach ($items as $item) {
            $item_id = $item['item_id'];
            $item_name = $item['item_name'];
            $pfm_name = $item['pfm_name'];
            $qty = $item['stock'];

            $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
            $os_po = $this->crud->read('os_po', [], [
                "item_rm_id" => @$item_rm->id,
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision
            ]);

            if ($os_po) {
                if($os_po->status == "EMPTY"){
                    $status = "GENERATE";
                }else{
                    $status = $os_po->status;
                }

                $records[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $cutoff,
                    "product_no" => $item_id,
                    "product_name" => $item_name,
                    "product_family" => $pfm_name,
                    "qty" => $qty,
                    "actual" => $os_po->actual,
                    "status" => $status,
                );
            } else {
                $records[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $cutoff,
                    "product_no" => $item_id,
                    "product_name" => $item_name,
                    "product_family" => $pfm_name,
                    "qty" => $qty,
                    "actual" => $qty,
                    "status" => "EMPTY",
                );
            }
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <table style="font-size: 12px;">
            <tr>
                <td width="80">MONTH</td>
                <td width="10">:</td>
                <td>' . $filter_month . '<td>
            </tr>
            <tr>
                <td width="80">YEAR</td>
                <td width="10">:</td>
                <td>' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="10">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Product Family</th>
                <th>Stock</th>
                <th>Actual</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['qty'] . '</td>
                            <td>' . $data['actual'] . '</td>
                            <td>' . $data['status'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
