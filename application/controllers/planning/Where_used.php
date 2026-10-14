<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Where_used extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/where_used');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=where_used_$format.xls");
        }

        if ($this->input->get()) {
            $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));

            $query = $this->db->query("SELECT d.id as item_rm_id, d.number as item_rm_number, d.name as item_rm_name,
                b.id as item_fg_id, b.number as item_fg_number, b.number_customer, b.name as item_fg_name, a.composition, e.name as customer_name
                FROM bom a
                LEFT JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN customer_items c ON b.id = c.item_fg_id
                LEFT JOIN item_rm d ON a.item_rm_id = d.id
                LEFT JOIN customers e ON c.customer_id = e.id
                WHERE a.item_rm_id like '%$filter_item_rm_id%' and b.status = 0
                GROUP BY a.item_fg_id, a.item_rm_id
                ORDER BY a.item_rm_id ASC");
            $records = $query->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
                    <style> .str{ mso-number-format:\@; } </style>
                    <body>
                    <center>
                        <div style="float: left; font-size: 12px; text-align: left;">
                            <table style="width: 100%;">
                                <tr>
                                    <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                        <img src="' .  $config->logo . '" width="30">
                                    </td>
                                    <td style="font-size: 14px; text-align: left; margin:2px;">
                                        <b>' . $config->name . '</b><br>
                                        <small>WHERE USED</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div style="float: right; font-size: 12px; text-align: right;">
                            Print Date ' . date("d M Y H:m:s") . ' <br>
                            Print By ' . $this->session->username . '  
                        </div>
                    </center>
                    <br><br><br>
                    <center>
                        <b style="font-size:20px;">WHERE USED</b><br><small>Part No EBWS '.$filter_item_rm_id.'</small><br><br>
                    </center>
                    <table id="customers" border="1">
                        <tr>
                            <th width="30">No</th>
                            <th style="text-align:center;">Part No EBWS</th>
                            <th style="text-align:center;">Part No Supplier</th>
                            <th style="text-align:center;">Description</th>
                            <th style="text-align:center;">Product No EBWS</th>
                            <th style="text-align:center;">Product No Supplier</th>
                            <th style="text-align:center;">Description</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:center;">Customer</th>
                        </tr>';
            $no = 1;
            foreach ($records as $record) {
                $html .= '<tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_rm_number'] . '</td>
                            <td>' . $record['item_rm_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_fg_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['number_customer'] . '</td>
                            <td>' . $record['item_fg_name'] . '</td>
                            <td>' . $record['composition'] . '</td>
                            <td>' . $record['customer_name'] . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';
        echo $html;
    }
}
