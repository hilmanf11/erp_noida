<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Os_wo extends CI_Controller
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
            $this->load->view('planning/os_wo');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->crud->reads('os_wo', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->crud->query("SELECT DISTINCT revision FROM os_wo WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
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

            $filter_start = date("Y-m-d", strtotime($filter_cutoff . "+1 days"));
            $filter_end = date("Y-m-t", strtotime($filter_cutoff));

            // $qmpp = $this->dummy->query("SELECT * FROM wip_trx_mpp WHERE wp_date BETWEEN '$filter_start' and '$filter_end'");
            // $dmpp = $qmpp->result_array();

            $itemq = $this->dummy->query("SELECT b.bom_com_item, c.item_name, d.pfm_name, SUM(a.total * b.bom_qty_perassy) as total from
                (select assy_no, SUM(qty) as total from wip_trx_mpp WHERE wp_date between '$filter_start' and '$filter_end' GROUP BY assy_no ORDER BY SUM(qty) asc) a
                join mst_bom b on a.assy_no = b.bom_par_item
                join mst_item c on b.bom_com_item = c.item_id
                join r_prodfam d on c.pfm_id = d.pfm_id
                where d.pfm_id like '%$filter_product_family%' and b.bom_com_item like '%$filter_product_no%'
                group by b.bom_com_item, c.item_name, d.pfm_name
                order by d.pfm_name asc, SUM(a.total * b.bom_qty_perassy) desc
            ");
            $items = $itemq->result_array();
            $arr = array();

            foreach ($items as $item) {
                $item_id = $item['bom_com_item'];
                $item_name = $item['item_name'];
                $pfm_name = $item['pfm_name'];
                $qty = abs($item['total']);

                $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
                $os_wo = $this->crud->read('os_wo', [], [
                    "item_rm_id" => @$item_rm->id,
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision
                ]);

                if ($os_wo) {
                    if($os_wo->status == "EMPTY"){
                        $status = "GENERATE";
                    }else{
                        $status = $os_wo->status;
                    }

                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $filter_cutoff,
                        "product_no" => $item_id,
                        "product_name" => $item_name,
                        "product_family" => $pfm_name,
                        "qty" => round($qty),
                        "actual" => round($os_wo->qty),
                        "status" => $status,
                        "created_by" => $os_wo->created_by,
                        "created_date" => $os_wo->created_date,
                        "updated_by" => $os_wo->updated_by,
                        "updated_date" => $os_wo->updated_date,
                    );
                } else {
                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $filter_cutoff,
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
                $os_wo = $this->crud->reads('os_wo', [], [
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

                if (count($os_wo) > 0) {
                    $this->crud->update('os_wo', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "item_rm_id" => $item_rm->id
                    ], $postFinal);

                    echo json_encode(array("title" => "Good Job", "message" => "Part No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $this->crud->create('os_wo', $postFinal);

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
        $send = $this->crud->delete('os_wo', $data);
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
                $os_wo = $this->crud->reads('os_wo', [], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $item_rm->id
                ]);
    
                $postFinal = array(
                    "p_month" => $p_month,
                    "p_year" => $p_year,
                    "revision" => $revision,
                    "item_rm_id" => $item_rm->id,
                    "actual" => $post['qty'],
                    "status" => $post['status']
                );
                if (count($os_wo) > 0) {
                    $this->crud->update('os_wo', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "item_rm_id" => $item_rm->id
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
            header("Content-Disposition: attachment; filename=os_wo_$format.xls");
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

        $filter_start = date("Y-m-d", strtotime($filter_cutoff . "+1 days"));
        $filter_end = date("Y-m-t", strtotime($filter_cutoff));

        $itemq = $this->dummy->query("SELECT b.bom_com_item, c.item_name, d.pfm_name, SUM(a.total * b.bom_qty_perassy) as total from
                (select assy_no, SUM(qty) as total from wip_trx_mpp WHERE wp_date between '$filter_start' and '$filter_end' GROUP BY assy_no ORDER BY SUM(qty) asc) a
                join mst_bom b on a.assy_no = b.bom_par_item
                join mst_item c on b.bom_com_item = c.item_id
                join r_prodfam d on c.pfm_id = d.pfm_id
                where d.pfm_id like '%$filter_product_family%' and b.bom_com_item like '%$filter_product_no%'
                group by b.bom_com_item, c.item_name, d.pfm_name
                order by d.pfm_name asc, SUM(a.total * b.bom_qty_perassy) desc
        ");
        $items = $itemq->result_array();
        $records = array();

        foreach ($items as $item) {
            $item_id = $item['bom_com_item'];
            $item_name = $item['item_name'];
            $pfm_name = $item['pfm_name'];
            $qty = abs($item['total']);

            $item_rm = $this->crud->read("item_rm", [], ["number" => $item_id]);
            $os_wo = $this->crud->read('os_wo', [], [
                "item_rm_id" => @$item_rm->id,
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision
            ]);

            if ($os_wo) {
                if($os_wo->status == "EMPTY"){
                    $status = "GENERATE";
                }else{
                    $status = $os_wo->status;
                }

                $records[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $filter_cutoff,
                    "product_no" => $item_id,
                    "product_name" => $item_name,
                    "product_family" => $pfm_name,
                    "qty" => $qty,
                    "actual" => @$os_wo->qty,
                    "status" => $status,
                );
            } else {
                $records[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $filter_cutoff,
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
                <th>OS Workorder</th>
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
