<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_material_consumtions extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/report_material_consumtions');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-10 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => $i, "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function readItemFamily()
    {
        $prodfam = $this->crud->query("SELECT * FROM item_familys WHERE `number` != '06'");
        echo json_encode($prodfam);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_material_consumtions_$format.xls");
        }

        $filter_p_year_from = base64_decode($this->input->get("filter_p_year_from"));
        $filter_p_month_from = base64_decode($this->input->get("filter_p_month_from"));
        $filter_p_year_to = base64_decode($this->input->get("filter_p_year_to"));
        $filter_p_month_to = base64_decode($this->input->get("filter_p_month_to"));
        $filter_customer = base64_decode($this->input->get("filter_customer"));
        $filter_item_family = base64_decode($this->input->get("filter_item_family"));

        $this->db->select('a.item_rm_id, b.number as item_rm_number, b.name as item_rm_name, b.uom');
        $this->db->from('bom a');
        $this->db->join('item_rm b', 'a.item_rm_id=b.id');
        $this->db->join('item_fg c', 'a.item_fg_id=c.id');
        if (!empty($filter_item_family)) {
            $this->db->where('b.item_family_id', $filter_item_family);
        }
        if (!empty($filter_customer)) {
            $this->db->where('c.customer_id', $filter_customer);
        }
        $this->db->group_by('a.item_rm_id');
        $this->db->order_by('a.item_rm_id', 'asc');
        $records = $this->db->get()->result_array();
        // die($this->db->last_query());

        $p_date_start = date("Y-m", strtotime($filter_p_year_from . "-" . $filter_p_month_from));
        $p_date_to = date("Y-m", strtotime($filter_p_year_to . "-" . $filter_p_month_to));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $param_dates[] = array(
                "yearDate" => date("Y-m", strtotime($p_date_start)),
                "year" => date("Y", strtotime($p_date_start)),
                "month" => date("m", strtotime($p_date_start)),
            );

            $p_date_start = date("Y-m", strtotime("+1 month", strtotime($p_date_start)));
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

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
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:m:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">MATERIAL CONSUMTIONS</h3>
                <small>PERIOD : <b>' . $this->convertMonthName($filter_p_month_from) . ' ' . $filter_p_year_from . ' </b> To <b>' . $this->convertMonthName(date("m", strtotime($p_date_to))) . ' ' . date("Y", strtotime($p_date_to)) . '</b></small>
            </center>
            <br>
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" style="text-align: center;">No</th>
                <th rowspan="2" style="text-align: center;">Part No EBWS</th>
                <th rowspan="2" style="text-align: center;">Part No Supplier</th>
                <th rowspan="2" style="text-align: center;">Description</th>
                <th rowspan="2" style="text-align: center;">UOM</th>
            ';
        // loop month
        for ($i = 0; $i < count($dates); $i++) {
            $html .= '<th colspan="4" style="text-align: center;">' . $dates[$i]['name'] . '</th>';
        }
        $html .= '</tr>';
        $html .= '<tr>';
        for ($i = 0; $i < count($dates); $i++) {
            $html .= '<th style="text-align: center;">Opening<br>Stock</th>';
            $html .= '<th style="text-align: center;">MPP</th>';
            $html .= '<th style="text-align: center;">Issued</th>';
            $html .= '<th style="text-align: center;">Balance</th>';
        }
        $html .= '</tr>';

        // loop data bom
        $no  = 1;
        foreach ($records as $record) {
            $html .= '<tr>
            <td style="text-align: center;">' . $no . '</td>
            <td style="mso-number-format:\@;">' . $record['item_rm_id'] . '</td>
            <td style="mso-number-format:\@;">' . $record['item_rm_number'] . '</td>
            <td style="mso-number-format:\@;">' . $record['item_rm_name'] . '</td>
            <td style="text-align: left;">' . $record['uom'] . '</td>';

            // loop data mpp dan issued
            for ($i = 0; $i < count($dates); $i++) {
                if (!empty($filter_customer)) {
                    $whereCustomer = " and a.customer_id='" . $filter_customer . "'";
                } else {
                    $whereCustomer = "";
                }


                $endDateLastMonth =  date("Y-m-t", strtotime($param_dates[$i]['yearDate']) - 1);

                $stock = $this->crud->query("select
                        a.id,
                        a.number,
                        a.name,
                        a.uom,
                        b.name as product_family,
                        ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS end_stock
                        FROM item_rm a 
                        JOIN item_familys b ON a.item_family_id = b.id
                        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$endDateLastMonth' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date <= '$endDateLastMonth' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date <= '$endDateLastMonth' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id WHERE a.receipt_date <= '$endDateLastMonth' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$endDateLastMonth' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$endDateLastMonth' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$endDateLastMonth' GROUP BY a.item_id) j ON a.id = j.item_id
                        LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$endDateLastMonth' GROUP BY a.item_id) k ON a.id = k.item_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
                                    FROM return_materials a 
                                    JOIN return_material_labels b ON a.return_id = b.return_id
                                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
                                    WHERE a.return_date <= '$endDateLastMonth'
                                    GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
                        WHERE a.item_category_id = 'C01' and a.id='" . $record['item_rm_id'] . "'
                        GROUP BY a.id
                        ORDER BY b.number, a.number
                ");


                $mpp = $this->crud->query("
                select 
                sum(
                        (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + 
                        a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + 
                        a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31
                        ) * b.composition
                    ) as mpp_bom
                from generate_mpp a 
                join bom b on a.item_fg_id = b.item_fg_id 
                where a.p_year ='" . $param_dates[$i]['year'] . "' and a.p_month='" . $param_dates[$i]['month'] . "' and b.item_rm_id='" . $record['item_rm_id'] . "' $whereCustomer
                ");

                $issued = $this->crud->query("
                select sum(qty) as qty from issued_material_details where substring(created_date, 1,7) ='" . $param_dates[$i]['yearDate'] . "' and item_rm_id='" . $record['item_rm_id'] . "'
                ");

                $end_stock = @$stock[0]->end_stock;
                if ($end_stock < 0) {
                    $end_stock = "(" . number_format(abs($end_stock), 2) . ")";
                    $style_end_stock = "color:red;";
                } else {
                    $end_stock = number_format($end_stock, 2);
                    $style_end_stock = "";
                }

                $balance = @$mpp[0]->mpp_bom - @$issued[0]->qty;
                if ($balance < 0) {
                    $balance = "(" . number_format(abs($balance), 2) . ")";
                    $style_balance = "color:red;";
                } else {
                    $balance = number_format($balance, 2);
                    $style_balance = "";
                }
                $html .= '<td style="text-align: right; ' . $style_end_stock . '">' . $end_stock . '</td>';
                $html .= '<td style="text-align: right;">' . number_format(@$mpp[0]->mpp_bom, 2) . '</td>';
                $html .= '<td style="text-align: right;">' . number_format(@$issued[0]->qty, 2) . '</td>';
                $html .= '<td style="text-align: right; ' . $style_balance . '">' . $balance . '</td>';
            }

            $html .= '</tr>';
            $no++;
        }

        $html .= '</table>';
        $html .= '</body></html>';
        echo $html;
    }

    public function convertMonthName($month)
    {
        if ($month == "01") {
            $month_name = "JANUARY";
        } elseif ($month == "02") {
            $month_name = "FEBRUARY";
        } elseif ($month == "03") {
            $month_name = "MARCH";
        } elseif ($month == "04") {
            $month_name = "APRIL";
        } elseif ($month == "05") {
            $month_name = "MAY";
        } elseif ($month == "06") {
            $month_name = "JUNE";
        } elseif ($month == "07") {
            $month_name = "JULY";
        } elseif ($month == "08") {
            $month_name = "AUGUST";
        } elseif ($month == "09") {
            $month_name = "SEPTEMBER";
        } elseif ($month == "10") {
            $month_name = "OCTOBER";
        } elseif ($month == "11") {
            $month_name = "NOVEMBER";
        } elseif ($month == "12") {
            $month_name = "DECEMBER";
        }
        return $month_name;
    }
}
