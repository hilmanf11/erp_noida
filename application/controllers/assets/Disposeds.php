<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Disposeds extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('assets/disposeds');
        } else {
            redirect('error_access');
        }
    }

    public function readFixeds()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $cutoff_from = date("Y-04-01", strtotime("-1 year"));
        $cutoff_to = date("Y-03-t");

        //Select Query
        $this->db->select("a.*, b.name as asset_category_name");
        $this->db->from('asset_fixeds a');
        $this->db->join('item_familys b', 'a.item_family_id = b.id');
        $this->db->where('a.status', 0);
        $this->db->group_start();
        $this->db->like('a.number', $post);
        $this->db->or_like('a.name', $post);
        $this->db->group_end();
        $this->db->group_by('a.number');
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
            $trans_date = date_create($record['trans_date']);
            $start_date = date_create($cutoff_from);
            $end_date = date_create($cutoff_to);

            if($trans_date < $start_date){
                $total_day = (date_diff($start_date, $end_date)->days + 1);
            }else{
                $total_day = (date_diff($trans_date, $end_date)->days + 1);
            }

            if($record['estimate_year'] == 0){
                $estimate_day = 1;
            }else{
                $estimate_day = ($record['estimate_year'] * 365);
            }

            if($record['trans_date'] < $cutoff_from){
                $cost = $record['cost'];
                $acq_period = 0;
            }elseif($record['trans_date'] >= $cutoff_from){
                $cost = 0;
                $acq_period = $record['cost'];
            }else{
                $cost = 0;
                $acq_period = 0;
            }

            if($cost != 0 && $estimate_day > 1){
                $addition_op = (round(((($cost - (($cost + $acq_period) * (5 / 100))) / $estimate_day) * $total_day) * $record['shift'], 2));
            }else{
                $addition_op = 0;
            }

            if($acq_period != 0 && $estimate_day > 1){
                $addition = (round(((($acq_period - (($cost + $acq_period) * (5 / 100))) / $estimate_day) * $total_day) * $record['shift'], 2));
            }else{
                $addition = 0;
            }

            $addition_total = ($addition_op + $addition);
            $datas[] = array(
                "id" => $record['id'],
                "number" => $record['number'],
                "name" => $record['name'],
                "asset_category_name" => $record['asset_category_name'],
                "purchase_invoice_no" => $record['purchase_invoice_no'],
                "supplier_name" => $record['supplier_name'],
                "trans_date" => $record['trans_date'],
                "shift" => $record['shift'],
                "uom" => $record['uom'],
                "qty" => $record['qty'],
                "cost" => $cost,
                "acq_period" => $acq_period,
                "acq_closing" => ($cost + $acq_period),
                "residual" => round(($cost + $acq_period) * (5 / 100)),
                "estimate_year" => ($record['estimate_year']),
                "estimate_month" => ($record['estimate_year'] * 12),
                "estimate_day" => ($record['estimate_year'] * 365),
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

        echo json_encode($datas);
    }

    public function readAssets()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.id, a.number, a.name
            FROM asset_fixeds a JOIN asset_disposals b ON a.id = b.asset_fixed_id
            WHERE (a.number LIKE '%$post%' or a.name LIKE '%$post%') 
            ORDER BY a.name asc");

        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page = isset($page) ? intval($page) : 1;
        $rows = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));

        //Select Query
        $this->db->select('a.*, b.number, b.name, b.cost, b.residual, c.name as product_family');
        $this->db->from('asset_disposals a');
        $this->db->join('asset_fixeds b', 'a.asset_fixed_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->where('a.disposal_date >=', $filter_from);
        $this->db->where('a.disposal_date <=', $filter_to);
        $this->db->order_by('a.disposal_date', 'asc');

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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->create('asset_disposals', $post);
            if ($send) {
                $this->crud->update('asset_fixeds', ["id" => $post['asset_fixed_id']], ["status" => 1]);
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
            $send = $this->crud->update('asset_disposals', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('asset_disposals', $data);
        $this->db->update('asset_fixeds', ["status" => 0], ["asset_fixed_id" => $post['asset_fixed_id']]);
        echo $send;
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

        //Select Query
        $this->db->select('a.*, b.number, b.name, b.cost, b.residual, c.name as product_family');
        $this->db->from('asset_disposals a');
        $this->db->join('asset_fixeds b', 'a.asset_fixed_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->where('a.disposal_date >=', $filter_from);
        $this->db->where('a.disposal_date <=', $filter_to);
        $this->db->order_by('a.disposal_date', 'asc');
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
                                <small>ASSET DISPOSED</small>
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
                <th>Disposal Date</th>
                <th>Asset Code</th>
                <th>Asset Name</th>
                <th>Asset Category</th>
                <th>Asset Cost</th>
                <th>Residual Value</th>
                <th>Disposal Value</th>
                <th>Net Book Value</th>
                <th>Gain/Loss</th>
                <th>Gain/Loss Value</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['disposal_date'] . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['product_family'] . '</td>
                        <td>' . $data['cost'] . '</td>
                        <td>' . $data['residual'] . '</td>
                        <td>' . $data['disposal'] . '</td>
                        <td>' . $data['book_value'] . '</td>
                        <td>' . $data['gainloss_type'] . '</td>
                        <td>' . $data['gainloss_value'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
