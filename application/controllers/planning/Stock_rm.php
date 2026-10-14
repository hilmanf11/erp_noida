<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stock_rm extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[fg_stock.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/stock_rm');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->crud->reads('stock_rm', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->crud->query("SELECT DISTINCT revision FROM stock_rm WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
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

            // $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));
            $cutoff = $filter_cutoff;

            $itemq = $this->dummy->query("SELECT b.item_id, b.item_name, c.pfm_name, coalesce(round(sum(a.ith_qty)),0) as stock
                from ith a
                JOIN mst_item b ON a.ith_item_id = b.item_id and b.stscode_id = '01'
                JOIN r_prodfam c ON b.pfm_id = c.pfm_id and c.prod_group = 'Raw Material' and c.pfm_id != '05'
                where a.ith_date <= '$cutoff'
                and a.ith_item_id LIKE '%$filter_product_no%'
                and b.pfm_id LIKE '%$filter_product_family%'
                group by b.item_id, b.item_name, c.pfm_name
                order by c.pfm_name, coalesce(round(sum(a.ith_qty)),0) desc
            ");
            $items = $itemq->result_array();
            $arr = array();

            foreach ($items as $item) {
                $item_id = trim($item['item_id']);
                $item_name = $item['item_name'];
                $pfm_name = $item['pfm_name'];
                $qty = $item['stock'];

                $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
                $stock_rm = $this->crud->read('stock_rm', [], [
                    "item_rm_id" => @$item_rm->id,
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision
                ]);

                if ($stock_rm) {
                    if($stock_rm->status == "EMPTY"){
                        $status = "GENERATE";
                    }else{
                        $status = $stock_rm->status;
                    }

                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $cutoff,
                        "product_no" => $item_id,
                        "product_name" => $item_name,
                        "product_family" => $pfm_name,
                        "qty" => round($qty),
                        "actual" => round($stock_rm->actual),
                        "status" => $status,
                        "created_by" => $stock_rm->created_by,
                        "created_date" => $stock_rm->created_date,
                        "updated_by" => $stock_rm->updated_by,
                        "updated_date" => $stock_rm->updated_date,
                    );
                } else {
                    $arr[] = array(
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
            $item_id = trim($post['product_no']);

            $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);

            if(!empty($item_rm->id)){
                $stock_rm = $this->crud->reads('stock_rm', [], [
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

                if (count($stock_rm) > 0) {
                    $this->crud->update('stock_rm', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "item_rm_id" => @$item_rm->id
                    ], $postFinal);

                    echo json_encode(array("title" => "Good Job", "message" => "Part No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $this->crud->create('stock_rm', $postFinal);
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
        $send = $this->crud->delete('stock_rm', $data);
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
            $item_id = $post['product_no'];

            $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
            if(!empty($item_rm->id)){
                $stock_rm = $this->crud->reads('stock_rm', [], [
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
                    "actual" => $post['qty'],
                    "status" => $post['status'],
                );

                if (count($stock_rm) > 0) {
                    $this->crud->update('stock_rm', [
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
        @unlink('excel/failed/fg_stock.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/fg_stock.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/fg_stock.txt";

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
            header("Content-Disposition: attachment; filename=stock_rm_$format.xls");
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

        // $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));
        $cutoff = $filter_cutoff;

        $itemq = $this->dummy->query("SELECT b.item_id, b.item_name, c.pfm_name, coalesce(round(sum(a.ith_qty)),0) as stock
            from ith a
            JOIN mst_item b ON a.ith_item_id = b.item_id and b.stscode_id = '01'
            JOIN r_prodfam c ON b.pfm_id = c.pfm_id and c.prod_group = 'Raw Material' and c.pfm_id != '05'
            where a.ith_date <= '$cutoff'
            and a.ith_item_id LIKE '%$filter_product_no%'
            and b.pfm_id LIKE '%$filter_product_family%'
            group by b.item_id, b.item_name, c.pfm_name
            order by c.pfm_name, coalesce(round(sum(a.ith_qty)),0) desc
        ");
        $items = $itemq->result_array();
        $records = array();

        foreach ($items as $item) {
            $item_id = $item['item_id'];
            $item_name = $item['item_name'];
            $pfm_name = $item['pfm_name'];
            $qty = $item['stock'];

            $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
            $stock_rm = $this->crud->read('stock_rm', [], [
                "item_rm_id" => @$item_rm->id,
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision
            ]);

            if ($stock_rm) {
                if($stock_rm->status == "EMPTY"){
                    $status = "GENERATE";
                }else{
                    $status = $stock_rm->status;
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
                    "actual" => @$stock_rm->actual,
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
