<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mps extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mps');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
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

    public function readRevisions()
    {
        $arr = array(
            ["id" => "0", "name" => "Revision 0"],
            ["id" => "1", "name" => "Revision 1"],
            ["id" => "2", "name" => "Revision 2"],
            ["id" => "3", "name" => "Revision 3"],
            ["id" => "4", "name" => "Revision 4"],
            ["id" => "5", "name" => "Revision 5"],
        );

        echo json_encode($arr);
    }

    public function getData()
    {
        $this->dummy = $this->load->database('dummy', TRUE);

        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_product_no = base64_decode($this->input->get('filter_item_fg'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            $monthBack = date('F Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $period = date("Y-m-t", strtotime("-1 month", strtotime($filter_cutoff)));
            $period2 = $filter_year.$filter_month;

            //Configuration Planning
            $this->db->select('*');
            $this->db->from("config");
            $config = $this->db->get()->row();

            //Select Query
            $this->db->select('a.id, a.number, a.name, a.leadtime, b.customer_id, a.number_customer, a.safety_stock,
                COALESCE(c.qty, 0) as total_wip,
                (CASE WHEN COALESCE(f.outstanding, 0) > 0 THEN COALESCE(f.outstanding, 0) ELSE 0 END) as os_so');
            $this->db->from('item_fg a');
            $this->db->join('customer_items b', 'a.id = b.item_fg_id');
            $this->db->join("(SELECT z.item_fg_id, SUM(z.qty) as qty, SUM(z.qty_scan) as qty_scan, (SUM(z.qty) - SUM(z.qty_scan)) as balance FROM (
                SELECT a.item_fg_id, a.workorder, a.qty, COALESCE(SUM(c.qty), 0) AS qty_scan, (a.qty - COALESCE(SUM(c.qty), 0)) AS balance
                FROM production_schedules a
                JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN scan_item_receipts_fg c ON a.workorder = c.workorder
                WHERE a.trans_date < '$filter_cutoff'
                GROUP BY a.item_fg_id, a.workorder) z
                GROUP BY z.item_fg_id) c", "a.id = c.item_fg_id", "left");
            // $this->db->join("(SELECT DATE_FORMAT(a.created_date, '%Y%m') as period, b.item_fg_id, b.customer_id, SUM(a.qty) as qty FROM scan_item_receipts_fg a JOIN wip_receipts b ON a.checksheet_number = b.checksheet_number WHERE a.status = 0 GROUP BY b.period, b.item_fg_id) d", "a.id = d.item_fg_id and b.customer_id = d.customer_id and d.period = '$period2'", "left");
            // $this->db->join("(SELECT b.item_fg_id, SUM(a.qty) as qty FROM scan_item_receipts_fg a JOIN os_fg b ON a.workorder = b.id WHERE a.status = 0 GROUP BY b.item_fg_id) g", "a.id = g.item_fg_id", "left");
            // $this->db->join("(SELECT DATE_FORMAT(a.trans_date_from, '%Y%m') as period, item_fg_id, customer_id, SUM(qty) as qty FROM wip_receipts a GROUP BY DATE_FORMAT(a.trans_date_from, '%Y%m')) e", "a.id = e.item_fg_id and b.customer_id = e.customer_id and e.period = '$period2'", "left");
            // $this->db->join("(SELECT c.id as item_fg_id, c.number_customer, COALESCE(a.qty_so, 0) as qty_so, COALESCE(b.qty_delivery, 0) as qty_delivery, (COALESCE(a.qty_so, 0) - COALESCE(b.qty_delivery, 0)) as outstanding
            //     FROM item_fg c
            //     JOIN (SELECT item_fg_id, SUM(qty) as qty_so FROM `sales_orders` WHERE delivery_date < '$filter_cutoff' and status = 0 GROUP BY item_fg_id) a ON a.item_fg_id = c.id
            //     JOIN (SELECT item_fg_id, SUM(qty) as qty_delivery FROM delivery_notes WHERE delivery_note_date < '$filter_cutoff' GROUP BY item_fg_id) b ON b.item_fg_id = c.id 
            //     GROUP BY c.id) f", "a.id = f.item_fg_id", "left");
            $this->db->join("(SELECT z.id, SUM(z.qty_outstanding) AS outstanding FROM (
                SELECT b.id, b.number_customer, a.qty_so, COALESCE(c.qty_delivery, 0) AS qty_delivery, (a.qty_so - COALESCE(c.qty_delivery, 0)) AS qty_outstanding
                FROM (SELECT *, SUM(qty) AS qty_so FROM sales_orders WHERE `status` = 0 AND sales_order_no != '' GROUP BY sales_order_no, item_fg_id) a
                JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN (SELECT sales_order_no, item_fg_id, SUM(qty) AS qty_delivery FROM delivery_notes WHERE `status` != '2' AND delivery_note_date < '$period' GROUP BY sales_order_no, item_fg_id) c ON a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id
                WHERE a.sales_order_date < '$period'
                GROUP BY a.sales_order_no, a.item_fg_id) z
                GROUP BY z.id) f", "a.id = f.id", "left");
            if ($filter_customer != "") {
                $this->db->where('b.customer_id', $filter_customer);
            }
            if ($filter_product_no != "") {
                $this->db->where('a.id', $filter_product_no);
            }
            $this->db->group_by('b.customer_id');
            $this->db->group_by('b.item_fg_id');
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            foreach ($records as $data) {
                $filter_cutoff_start = date("Y-m-01", strtotime($filter_cutoff));
                $filter_cutoff_start2 = date("Y-m-01", strtotime("-1 month", strtotime($filter_cutoff)));
                $filter_cutoff_end = date("Y-m-d", strtotime("-1 day", strtotime($filter_cutoff)));
                $filter_cutoff_end2 = date("Y-m-t", strtotime($filter_cutoff));
                $mpp_1 = date("Ym", strtotime("-1 month", strtotime($filter_cutoff)));
                $mpp_2 = date("Ym", strtotime($filter_cutoff));
                $item_fg_id = $data['id'];
                $assy_number = $data['number_customer'];
                
                $prod = $this->crud->query("SELECT item_fg_id, SUM(qty) as qty FROM production_schedules 
                WHERE trans_date between '$filter_cutoff_start2' and '$filter_cutoff_end' and item_fg_id = '$item_fg_id'
                GROUP BY item_fg_id");
                $qty_prod = empty(@$prod[0]->qty) ? 0 : @$prod[0]->qty;

                $sqlQty = $this->dummy->query("SELECT COUNT(x.serial_number) as qty FROM (
                    SELECT DISTINCT z.serial_number FROM (
                    SELECT DISTINCT
                        TRIM(a.serial_number) AS serial_number,
                        TRIM(a.assy_number) AS assy_number,
                        TRIM(a.customer) AS customer,
                        TRIM(a.mpp) AS mpp,
                        TRIM(a.wp) AS wp,
                        TRIM(a.lot) AS lot,
                        DATE(a.tanggal_proses) AS tanggal,
                        COALESCE(a.jam_scan,'00:00:00') AS jam,
                        a.wds_qty
                    FROM mst_scan_proses a
                    WHERE kode_proses = '11' and assy_number = '$assy_number' and mpp in ('$mpp_1','$mpp_2') and 
                    tanggal_proses <= '$filter_cutoff_end' and status_repair = 'false' 
                    AND status_before IS NOT NULL
                    AND status_repair = 'f'
                    ORDER BY serial_number) z ) x");
                $rowQty = $sqlQty->row();
                $qty_scan = empty(@$rowQty->qty) ? 0 : @$rowQty->qty;

                if(($qty_prod - $qty_scan) > 0){
                    $balance_wip = ($prod[0]->qty - $qty_scan);
                }else{
                    $balance_wip = 0;
                }

                $i = 1;
                $beginBalance = 0;
                $soData = 0;
                $forecastData = 0;
                $deliveryRate = 0;
                $ito = 0;
                $safetyStock = 0;
                $prodPlan = 0;
                $arrMonth = array();

                $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
                $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

                //Cek Forecasts
                $item_fg_id = $data['id'];
                $customer_id = $data['customer_id'];
                $qForecast = $this->db->query("SELECT SUM(month_1) as month_1, SUM(month_2) as month_2, SUM(month_3) as month_3, SUM(month_4) as month_4, SUM(month_5) as month_5, SUM(month_6) as month_6 FROM forecasts WHERE item_fg_id = '$item_fg_id' and customer_id = '$customer_id' and p_year = '$filter_year' and p_month = '$filter_month' and revision = '$filter_revision'");
                $forecast = $qForecast->row();
                // $forecast = $this->crud->read('forecasts', [], ["item_fg_id" => $data['id'], "customer_id" => $data['customer_id'], "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

                $stockFg = $this->crud->query("SELECT
                    a.id,
                    (COALESCE(b.qty_sto, 0) + COALESCE(d.sto_qty_in, 0) + COALESCE(e.qty_rgp_in, 0) + COALESCE(c.qty_wip, 0) + COALESCE(i.qty_return, 0) - COALESCE(g.qty, 0) - COALESCE(f.qty_rgp_out, 0) - COALESCE(h.sto_qty_out, 0)) as end_stock
                    FROM item_fg a 
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_fg a JOIN scan_item_receipts_fg b ON a.id = b.workorder WHERE a.trans_date <= '$filter_cutoff' GROUP BY a.item_fg_id) b ON a.id = b.item_fg_id
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_wip FROM wip_receipts a JOIN scan_item_receipts_fg b ON a.workorder = b.workorder and a.checksheet_number = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$filter_cutoff' GROUP BY a.item_fg_id) c ON a.id = c.item_fg_id
                    LEFT JOIN (select a.item_fg_id, COALESCE(SUM(b.qty), 0) as qty_return FROM sales_returns a JOIN scan_item_receipts_fg b ON a.sales_invoice_no = b.workorder and a.return_id = b.checksheet_number WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') <= '$filter_cutoff' GROUP BY a.item_fg_id) i ON a.id = i.item_fg_id
                    LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_fg a WHERE a.trans_date <= '$filter_cutoff' and a.type = 'IN' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) d ON a.id = d.item_fg_id
                    LEFT JOIN (SELECT a.item_fg_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_fg a WHERE a.trans_date <= '$filter_cutoff' and a.type = 'OUT' and (a.approved_to = '' or a.approved_to is null) GROUP BY item_fg_id) h ON a.id = h.item_fg_id
                    LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_in FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id= b.item_id WHERE a.type = 'IN' and a.transaction_date <= '$filter_cutoff' GROUP BY a.item_id) e ON a.id = e.item_id
                    LEFT JOIN (SELECT a.item_id, COALESCE(SUM(b.qty), 0) as qty_rgp_out FROM rgp_transactions a JOIN scan_item_rgp b ON a.number = b.rgp_number and a.item_id = b.item_id WHERE a.type = 'OUT' and a.transaction_date <= '$filter_cutoff' GROUP BY a.item_id) f ON a.id = f.item_id
                    LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$filter_cutoff' and `status` != '2' GROUP BY item_fg_id) g ON a.id = g.item_fg_id
                WHERE a.id = '$item_fg_id'
                GROUP BY a.id
                ORDER BY a.number");

                $p_month = date("m", strtotime($filter_cutoff));
                $p_year = date("Y", strtotime($filter_cutoff));
                $osMpp = $this->crud->query("SELECT
                    a.id,
                    ((c.date_1 + c.date_2 + c.date_3 + c.date_4 + c.date_5 + c.date_6 + c.date_7 + c.date_8 + c.date_9 + c.date_10 + c.date_11 + c.date_12 + c.date_13 + c.date_14 + c.date_15 + c.date_16 + c.date_17 + c.date_18 + c.date_19 + c.date_20 + c.date_21 + c.date_22 + c.date_23 + c.date_24 + c.date_25 + c.date_26 + c.date_27 + c.date_28 + c.date_29 + c.date_30 + c.date_31) - COALESCE(SUM(b.qty), 0)) as os_mpp
                FROM item_fg a 
                JOIN generate_mpp c ON a.id = c.item_fg_id and c.p_month = '$p_month' and c.p_year = '$p_year'
                LEFT JOIN production_schedules b ON a.id = b.item_fg_id and b.trans_date between '$filter_cutoff_start' and '$filter_cutoff_end2'
                WHERE a.id = '$item_fg_id'
                GROUP BY a.id");

                if(@$osMpp[0]->os_mpp > 0){
                    $os_mpp = @$osMpp[0]->os_mpp;
                }else{
                    $os_mpp = 0;
                }

                $stock_fg = (@$stockFg[0]->end_stock);

                // $totalStock = ($data['total_wip'] + @$stock_fg + $os_mpp);
                $totalStock = ($balance_wip + @$stock_fg + $os_mpp);
                if (@$stock_fg == null) {
                    $fg = "0";
                } else {
                    $fg = @$stock_fg;
                }

                while ($monthStart < $monthEnd) {
                    $monthName = date('F Y', $monthStart);
                    $monthName2 = date('Y-m-01', $monthStart);
                    $monthName3 = date("Y-m", $monthStart);

                    // $monthsPlus = strtolower(date('F', strtotime('+1 month', $monthStart)));
                    $start = strtotime(date('Y-m-01', $monthStart));
                    $finish = strtotime(date('Y-m-t', $monthStart));

                    $start2 = strtotime(date('Y-m-01', strtotime('+1 month', $monthStart)));
                    $finish2 = strtotime(date('Y-m-t', strtotime('+1 month', $monthStart)));

                    //Sales Order
                    $sales_order = $this->crud->query("SELECT SUM(qty) as qty FROM sales_orders WHERE item_fg_id = '$data[id]' and `status` = 0 and customer_id = '$data[customer_id]' and sales_order_date like '%$monthName3%'");

                    //HKW 1
                    $hkw = 0;
                    for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                        $working_date = date('Y-m-d', $z);

                        $this->db->select('remarks');
                        $this->db->from('calendars');
                        $this->db->where('working_date', $working_date);
                        $holiday = $this->db->get()->row();

                        // if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                            if (@$holiday->remarks != null or @$holiday->remarks != "") {
                                $hkw += 0;
                            } else {
                                $hkw += 1;
                            }
                        // } else {
                            // $hkw += 0;
                        // }
                    }

                    //HKW 2
                    $hkw2 = 0;
                    for ($x = $start2; $x <= $finish2; $x += (60 * 60 * 24)) {
                        $working_date2 = date('Y-m-d', $x);

                        $this->db->select('remarks');
                        $this->db->from('calendars');
                        $this->db->where('working_date', $working_date2);
                        $holiday2 = $this->db->get()->row();

                        // if (date('w', $x) !== '0') {
                            if (@$holiday2->remarks != null or @$holiday2->remarks != "") {
                                $hkw2 += 0;
                            } else {
                                $hkw2 += 1;
                            }
                        // } else {
                        //     $hkw2 += 0;
                        // }
                    }

                    //Bulan Pertama - Keenam
                    if ($i == 1) {
                        $beginBalance = $totalStock - $data['os_so'];
                        $soData = @round($sales_order[0]->qty);
                        $forecastData = @round($forecast->month_1);
                        if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                        $deliveryRate = @round($qtySoFc / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        // $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_2);
                        $safetyStock = @round($forecast->month_1 * (@$data['safety_stock'] / 100));
                        $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    } else if ($i == 2) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecastData - $safetyStock);
                        $soData = @round($sales_order[0]->qty);
                        $forecastData = @round($forecast->month_2);
                        if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                        $deliveryRate = @round($qtySoFc / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        // $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_3);
                        $safetyStock = @round($forecast->month_2 * (@$data['safety_stock'] / 100));
                        $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    } elseif ($i == 3) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecastData - $safetyStock);
                        $soData = @round($sales_order[0]->qty);
                        $forecastData = @round($forecast->month_3);
                        if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                        $deliveryRate = @round($qtySoFc / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        // $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_4);
                        $safetyStock = @round($forecast->month_3 * (@$data['safety_stock'] / 100));
                        $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    } elseif ($i == 4) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecastData - $safetyStock);
                        $soData = @round($sales_order[0]->qty);
                        $forecastData = @round($forecast->month_4);
                        if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                        $deliveryRate = @round($qtySoFc / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        // $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_5);
                        $safetyStock = @round($forecast->month_4 * (@$data['safety_stock'] / 100));
                        $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    } elseif ($i == 5) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecastData - $safetyStock);
                        $soData = @round($sales_order[0]->qty);
                        $forecastData = @round($forecast->month_5);
                        if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                        $deliveryRate = @round($qtySoFc / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        // $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_6);
                        $safetyStock = @round($forecast->month_5 * (@$data['safety_stock'] / 100));
                        $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    } elseif ($i == 6) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecastData - $safetyStock);
                        $soData = @round($sales_order[0]->qty);
                        $forecastData = @round($forecast->month_6);
                        if($soData > $forecastData){ $qtySoFc = $soData; }else{ $qtySoFc = $forecastData; }
                        $deliveryRate = @round($qtySoFc / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        // $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_6);
                        $safetyStock = @round($forecast->month_6 * (@$data['safety_stock'] / 100));
                        $prodPlan = @round(($qtySoFc + $safetyStock) - $beginBalance);
                    }

                    if ($prodPlan <= 0) {
                        $prodPlanFinal = 0;
                    } else {
                        $prodPlanFinal = $prodPlan;
                    }

                    $arrMonth[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "customer_id" => $data['customer_id'],
                        "item_fg_id" => $data['id'],
                        "ltpp_month" => strtoupper($monthName),
                        "ltpp_month2" => $monthName2,
                        "hkw" => "$hkw2",
                        "begin_balance" => "$beginBalance",
                        "ito" => "$ito",
                        "so" => "$soData",
                        "forecast" => "$forecastData",
                        "delivery_rate" => "$deliveryRate",
                        "safety_stock" => "$safetyStock",
                        "prod_plan" => "$prodPlanFinal"
                    );

                    $monthStart = strtotime("+1 month", $monthStart);
                    $i++;

                    $beginBalance = $beginBalance;
                    $forecastData = $qtySoFc;
                    $deliveryRate = $deliveryRate;
                    $ito = $ito;
                    $safetyStock = $safetyStock;
                    $prodPlan = $prodPlanFinal;
                    $balance = (($prodPlan + $beginBalance) - $forecastData);
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $filter_cutoff . " " . date("H:i:s"),
                    "customer_id" => $data['customer_id'],
                    "item_fg_id" => $data['id'],
                    "wip_month" => strtoupper($monthBack),
                    // "pp" => $data['total_wip'],
                    "pp" => $balance_wip,
                    "fg" => $fg,
                    "os_mpp" => $os_mpp,
                    "total_stock" => "$totalStock",
                    "os_so" => $data['os_so'],
                    "balance" => "$balance",
                    "details" => $arrMonth
                );
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function revision()
    {
        $filter_month = $this->input->post('filter_month');
        $filter_year = $this->input->post('filter_year');

        $this->db->select('revision');
        $this->db->from('generate_mps');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->where('deleted', 0);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $this->db->limit(1);
        $record = $this->db->get()->row();
        echo @$record->revision ? $record->revision : 0;
    }

    public function checkForecast()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('forecasts');
        $this->db->where("approved_to = ''");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();
        
        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkFg()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        // $this->db->select('*');
        // $this->db->from('stock_fg');
        // $this->db->where("approved_to = ''");
        // if ($filter_month != "" or $filter_year != "") {
        //     $this->db->where('p_month', $filter_month);
        //     $this->db->where('p_year', $filter_year);
        // }
        // $this->db->like('revision', $filter_revision);
        // $records = $this->db->get()->result_array();

        // if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        // } else {
            // echo json_encode(array("theme" => "error"));
        // }
    }

    public function checkOs()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $period = $filter_year . "-" . $filter_month;

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_orders');
        $this->db->where("status", 0);
        $this->db->like("sales_order_date", $period);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOstSo()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $period = date("Y-m-t", strtotime("-1 month", strtotime($filter_year . "-" . $filter_month."-01")));

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_orders');
        $this->db->where("status", 0);
        $this->db->where("sales_order_date <=", $period);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkStockWip()
    {
        // $filter_month = base64_decode($this->input->get('filter_month'));
        // $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        // $this->db->select('*');
        // $this->db->from('stock_wip');
        // $this->db->where("approved_to = ''");
        // if ($filter_month != "" or $filter_year != "") {
        //     $this->db->where('p_month', $filter_month);
        //     $this->db->where('p_year', $filter_year);
        // }
        // $this->db->like('revision', $filter_revision);
        // $records = $this->db->get()->result_array();

        // if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        // } else {
        //     echo json_encode(array("theme" => "error"));
        // }
    }

    public function checkOstMpp()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        // $this->db->select('*');
        // $this->db->from('os_mpp');
        // $this->db->where("approved_to = ''");
        // if ($filter_month != "" or $filter_year != "") {
        //     $this->db->where('p_month', $filter_month);
        //     $this->db->where('p_year', $filter_year);
        // }
        // $this->db->like('revision', $filter_revision);
        // $records = $this->db->get()->result_array();

        // if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        // } else {
        //     echo json_encode(array("theme" => "error"));
        // }
    }

    public function checkCalendar()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

        $html = "";
        $no = 1;
        while ($monthStart < $monthEnd) {
            $monthName = date('m/Y', $monthStart);
            $start = strtotime(date('Y-m-01', $monthStart));
            $finish = strtotime(date('Y-m-t', $monthStart));

            //HKW 1
            $hkw = 0;
            for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                $working_date = date('Y-m-d', $z);

                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                // if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                    if (@$holiday->remarks != null or @$holiday->remarks != "") {
                        $hkw += 0;
                    } else {
                        $hkw += 1;
                    }
                // } else {
                //     $hkw += 0;
                // }
            }

            $html .= '  <div style="margin:15px;">
                            HKW ' . $no . ' : ' . $monthName . ' : <b>' . $hkw . '</b>
                        </div>';

            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        echo $html;
    }

    public function create()
    {   
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $postDetails = $post['details'];

            foreach ($postDetails as $postDetail) {
                $generateMpsDetails = $this->crud->reads('generate_mps_details', [], [
                    "p_month" => $postDetail['p_month'],
                    "p_year" => $postDetail['p_year'],
                    "revision" => $postDetail['revision'],
                    "customer_id" => $postDetail['customer_id'],
                    "item_fg_id" => $postDetail['item_fg_id'],
                    "ltpp_month" => $postDetail['ltpp_month']
                ]);

                $postFinalDetail = array(
                    "p_month" => $postDetail['p_month'],
                    "p_year" => $postDetail['p_year'],
                    "revision" => $postDetail['revision'],
                    "customer_id" => $postDetail['customer_id'],
                    "item_fg_id" => $postDetail['item_fg_id'],
                    "ltpp_month" => $postDetail['ltpp_month'],
                    "ltpp_month2" => $postDetail['ltpp_month2'],
                    "hkw" => $postDetail['hkw'],
                    "begin_balance" => $postDetail['begin_balance'],
                    "ito" => $postDetail['ito'],
                    "so" => $postDetail['so'],
                    "forecast" => $postDetail['forecast'],
                    "delivery_rate" => $postDetail['delivery_rate'],
                    "safety_stock" => $postDetail['safety_stock'],
                    "need" => $postDetail['prod_plan'],
                    "prod_plan" => $postDetail['prod_plan']
                );

                if (count($generateMpsDetails) > 0) {
                    $send = $this->crud->update('generate_mps_details', [
                        "p_month" => $postDetail['p_month'],
                        "p_year" => $postDetail['p_year'],
                        "revision" => $postDetail['revision'],
                        "customer_id" => $postDetail['customer_id'],
                        "item_fg_id" => $postDetail['item_fg_id'],
                        "ltpp_month" => $postDetail['ltpp_month']
                    ], $postFinalDetail, "pass");
                } else {
                    $this->crud->create('generate_mps_details', $postFinalDetail, "pass");
                }
            }

            $generateMps = $this->crud->reads('generate_mps', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "customer_id" => $post['customer_id'],
                "item_fg_id" => $post['item_fg_id'],
                "wip_month" => $post['wip_month']
            ]);

            $postFinal = array(
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "cutoff" => $post['cutoff'],
                "customer_id" => $post['customer_id'],
                "item_fg_id" => $post['item_fg_id'],
                "wip_month" => $post['wip_month'],
                "pp" => $post['pp'],
                "fg" => $post['fg'],
                "os_mpp" => $post['os_mpp'],
                "os_so" => $post['os_so'],
                "total_stock" => $post['total_stock'],
                "balance" => $post['balance']
            );

            if (count($generateMps) > 0) {
                $send   = $this->crud->update('generate_mps', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "customer_id" => $post['customer_id'],
                    "item_fg_id" => $post['item_fg_id'],
                    "wip_month" => $post['wip_month']
                ], $postFinal, "pass");
                echo $send;
            } else {
                $send = $this->crud->create('generate_mps', $postFinal, "pass");
                echo $send;
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('failed/generate_mps.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/generate_mps.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/generate_mps.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function monthName($id)
    {
        if ($id == "01") {
            return "JANUARY";
        } elseif ($id == "02") {
            return "FEBRUARY";
        } elseif ($id == "03") {
            return "MARCH";
        } elseif ($id == "04") {
            return "APRIL";
        } elseif ($id == "05") {
            return "MAY";
        } elseif ($id == "06") {
            return "JUNE";
        } elseif ($id == "07") {
            return "JULY";
        } elseif ($id == "08") {
            return "AUGUST";
        } elseif ($id == "09") {
            return "SEPTEMBER";
        } elseif ($id == "10") {
            return "OCTOBER";
        } elseif ($id == "11") {
            return "NOVEMBER";
        } elseif ($id == "12") {
            return "DECEMBER";
        }
    }

    public function ltpp_no($p_month, $p_year)
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        $datenow    = $p_year. $p_month;
        $sqlGetID   = $this->dummy->query("SELECT max(ltpp_doc) as kode FROM ltpp_header 
            WHERE ltpp_doc like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }

        return $datenow . "/LTPP/" . $autoID;
    }

    public function push_data_check(){
    	//Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Line No
        $this->db->select('*');
        $this->db->from('generate_mps');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $this->db->where('deleted', 0);
        $records = $this->db->get()->result_array();

        if(count($records) > 0){
        	echo json_encode(array("title" => "Success", "message" => "Data Already", "theme" => "success"));
        }else{
        	echo json_encode(array("title" => "Not Ready", "message" => "Data MPS Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_header(){
        $this->dummy = $this->load->database('dummy', TRUE);

        if($this->input->get()){
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            //Perhitungan HKW
            $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
            $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

            $no = 1;
            while ($monthStart < $monthEnd) {
                $start = strtotime(date('Y-m-01', $monthStart));
                $finish = strtotime(date('Y-m-t', $monthStart));

                //HKW 1
                $hkw = 0;
                for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $z);

                    $this->db->select('remarks');
                    $this->db->from('calendars');
                    $this->db->where('working_date', $working_date);
                    $holiday = $this->db->get()->row();

                    // if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                        if (@$holiday->remarks != null or @$holiday->remarks != "") {
                            $hkw += 0;
                        } else {
                            $hkw += 1;
                        }
                    // } else {
                    //     $hkw += 0;
                    // }
                }

                $arrHkw[] = array(
                    "hkw_".$no => $hkw
                );

                $no++;
                $monthStart = strtotime("+1 month", $monthStart);
            }

            $this->dummy->select('*');
            $this->dummy->from("ltpp_header");
            $this->dummy->where("period", $filter_year . $filter_month);
            $this->dummy->where("rev", $filter_revision);
            $ltpp_header = $this->dummy->get()->result_array();

            $arrData = array(
                "ltpp_doc" => $this->ltpp_no($filter_month, $filter_year),
                "period" => $filter_year . $filter_month,
                "date_period" => $filter_year ."-".$filter_month."-01",
                "lt" => "8",
                "hkw_1" => $arrHkw[0]['hkw_1'],
                "hkw_2" => $arrHkw[1]['hkw_2'],
                "hkw_3" => $arrHkw[2]['hkw_3'],
                "hkw_4" => $arrHkw[3]['hkw_4'],
                "hkw_5" => $arrHkw[4]['hkw_5'],
                "hkw_6" => $arrHkw[5]['hkw_6'],
                "rev" => $filter_revision,
                "fc_m4" => "0",
                "upload_user" => $this->session->username,
                "upload_time" => date("Y-m-d H:i:s"),
                "status" => "",
                "approval_sts" => "",
                "remark" => "",
            );

            if(count($ltpp_header) == 0){
                $this->dummy->insert("ltpp_header", $arrData);
                die(json_encode($arrData));
            }else{
                die(json_encode($ltpp_header));
            }
        }
    }

    public function push_data(){
        if($this->input->get()){
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            $this->db->select('a.*, c.number_customer as product_no, c.name as product_name, d.circuit_no');
            $this->db->from('generate_mps a');
            $this->db->join('generate_mps_details b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.item_fg_id = b.item_fg_id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join("(SELECT z.item_fg_id, COUNT(z.circuit_no) as circuit_no FROM (SELECT distinct item_fg_id, circuit_no FROM `wos`) z GROUP BY z.item_fg_id) d", 'a.item_fg_id = d.item_fg_id');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
            // $this->db->where("(a.pp > 0 or a.wc > 0 or a.ha > 0 or a.c > 0 or a.v > 0 or a.p > 0 or a.fg > 0 or a.os_mpp > 0 or a.os_so > 0)");
            $this->db->where('a.deleted', 0);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->group_by('a.item_fg_id');
            $records = $this->db->get()->result_array();

            $jsonData = array();
            foreach ($records as $data) {
                $this->db->select('a.*');
                $this->db->from('generate_mps_details a');
                $this->db->where('a.p_month', $data['p_month']);
                $this->db->where('a.p_year', $data['p_year']);
                $this->db->where('a.revision', $data['revision']);
                $this->db->where('a.customer_id', $data['customer_id']);
                $this->db->where('a.item_fg_id', $data['item_fg_id']);
                $this->db->where('a.deleted', 0);
                $this->db->group_by('a.ltpp_month');
                $this->db->order_by('a.id');
                $details2 = $this->db->get()->result_array();

                $nodetail = 1;
                $jsonDataDetail = array();
                foreach ($details2 as $detail2) {
                    $safety_stock = $detail2['safety_stock'];

                    $jsonDataDetail[] = array(
                        "begin_balance_".$nodetail => $detail2['begin_balance'],
                        "ito_".$nodetail => $detail2['ito'],
                        "xbar_".$nodetail => 0,
                        "forecast_".$nodetail => $detail2['forecast'],
                        "delivery_rate_".$nodetail => $detail2['delivery_rate'],
                        "safety_stock_".$nodetail => $safety_stock,
                        "prod_plan_".$nodetail => $detail2['prod_plan']
                    );

                    $nodetail++;
                }

                $jsonData[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "line_no" => "",
                    "product_no" => trim($data['product_no']),
                    "product_name" => trim($data['product_name']),
                    "circuit_no" => $data['circuit_no'],
                    "pp" => $data['pp'],
                    "p1" => $data['wc'],
                    "p2" => $data['ha'],
                    "p3" => $data['c'],
                    "p4" => $data['v'],
                    "p5" => $data['p'],
                    "p6" => 0,
                    "p7" => 0,
                    "p8" => 0,
                    "p9" => 0,
                    "p10" => 0,
                    "stock_tf" => ($data['wc'] + $data['ha'] + $data['c'] + $data['v'] + $data['p']),
                    "rqa" => 0,
                    "hav" => 0,
                    "fg" => $data['fg'],
                    "os_mpp" => $data['os_mpp'],
                    "total_stock" => $data['total_stock'],
                    "os_sales_order" => $data['os_so'],
                    "balance" => $data['balance'],
                    "details" => $jsonDataDetail
                );
            }

            $jsonData['total'] = count($jsonData);
            die(json_encode($jsonData));
        }
    }

    public function push_data_create(){
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $header = $this->input->post('header');

            // $sales_order = $this->crud->read('sales_order', [], [
            //     "p_month" => $data['p_month'],
            //     "p_year" => $data['p_year'],
            //     "revision" => $data['revision'],
            //     "product_no" => $data['product_no']
            // ]);

            $this->dummy = $this->load->database('dummy', TRUE);

            $this->dummy->select('COALESCE(lotqty, 0) as lotqty');
            $this->dummy->from("mst_item");
            $this->dummy->where("item_id", trim($data['product_no']));
            $mst_item = $this->dummy->get()->row();

            $this->dummy->select('*');
            $this->dummy->from("ltpp_generate");
            $this->dummy->where("period", $header[0]['period']);
            $this->dummy->where("assy_no", $data['product_no']);
            $this->dummy->where("rev", $header[0]['rev']);
            $ltpp_generate = $this->dummy->get()->result_array();

            if(count($ltpp_generate) > 0){
                $ltpp_doc = $ltpp_generate[0]['ltpp_doc'];
            }else{
                $ltpp_doc = $header[0]['ltpp_doc'];
            }

            if(@is_nan(ceil($data['details'][0]['prod_plan_1'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_1 = 0;
            }else{
            	$prod_plan_1 = @(ceil($data['details'][0]['prod_plan_1'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][1]['prod_plan_2'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_2 = 0;
            }else{
            	$prod_plan_2 = @(ceil($data['details'][1]['prod_plan_2'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][2]['prod_plan_3'] / $mst_item->lotqty) * $mst_item->lotqty) ){
            	$prod_plan_3 = 0;
            }else{
            	$prod_plan_3 = @(ceil($data['details'][2]['prod_plan_3'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][3]['prod_plan_4'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_4 = 0;
            }else{
            	$prod_plan_4 = @(ceil($data['details'][3]['prod_plan_4'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][4]['prod_plan_5'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_5 = 0;
            }else{
            	$prod_plan_5 = @(ceil($data['details'][4]['prod_plan_5'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][5]['prod_plan_6'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_6 = 0;
            }else{
            	$prod_plan_6 = @(ceil($data['details'][5]['prod_plan_6'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            $postFinal = array(
                "ltpp_doc" => $ltpp_doc,
                "period" => $header[0]['period'],
                "date_period" => $header[0]['date_period'],
                "lt" => $header[0]['lt'],
                "hkw_1" => $header[0]['hkw_1'],
                "hkw_2" => $header[0]['hkw_2'],
                "hkw_3" => $header[0]['hkw_3'],
                "hkw_4" => $header[0]['hkw_4'],
                "hkw_5" => $header[0]['hkw_5'],
                "hkw_6" => $header[0]['hkw_6'],
                "fc_m4" => $header[0]['fc_m4'],
                "rev" => $header[0]['rev'],
                "assy_no" => trim($data['product_no']),
                "item_name" => trim($data['product_name']),
                "cct" => $data['circuit_no'],
                "pp" => $data['pp'],
                "p1" => $data['p1'],
                "p2" => $data['p2'],
                "p3" => $data['p3'],
                "p4" => $data['p4'],
                "p5" => $data['p5'],
                "p6" => $data['p6'],
                "p7" => $data['p7'],
                "p8" => $data['p8'],
                "p9" => $data['p9'],
                "p10" => $data['p10'],
                "rep_qa" => $data['rqa'],
                "hav" => $data['hav'],
                "fg" => $data['fg'],
                "ost_mpp" => $data['os_mpp'],
                "t_stock" => $data['total_stock'],
                "fc1" => $data['details'][0]['forecast_1'],
                "fc2" => $data['details'][1]['forecast_2'],
                "fc3" => $data['details'][2]['forecast_3'],
                "fc4" => $data['details'][3]['forecast_4'],
                "fc5" => $data['details'][4]['forecast_5'],
                "fc6" => $data['details'][5]['forecast_6'],
                "t_fc" => ($data['details'][0]['forecast_1'] + $data['details'][1]['forecast_2'] + $data['details'][2]['forecast_3'] + $data['details'][3]['forecast_4'] + $data['details'][4]['forecast_5'] + $data['details'][5]['forecast_6']),
                // "so_0" => @$sales_order->qty_1,
                // "so_1" => @$sales_order->qty_2,
                // "so_2" => @$sales_order->qty_3,
                // "so_3" => @$sales_order->qty_4,
                // "so_4" => @$sales_order->qty_5,
                // "so_5" => @$sales_order->qty_6,
                "so_0" => "0.0000",
                "so_1" => "0.0000",
                "so_2" => "0.0000",
                "so_3" => "0.0000",
                "so_4" => "0.0000",
                "so_5" => "0.0000",
                "so_6" => "0.0000",
                "bal_1" => $data['details'][0]['begin_balance_1'],
                "bal_2" => $data['details'][1]['begin_balance_2'],
                "bal_3" => $data['details'][2]['begin_balance_3'],
                "bal_4" => $data['details'][3]['begin_balance_4'],
                "bal_5" => $data['details'][4]['begin_balance_5'],
                "bal_6" => $data['details'][5]['begin_balance_6'],
                "ito_1" => $data['details'][0]['ito_1'],
                "ito_2" => $data['details'][1]['ito_2'],
                "ito_3" => $data['details'][2]['ito_3'],
                "ito_4" => $data['details'][3]['ito_4'],
                "ito_5" => $data['details'][4]['ito_5'],
                "ito_6" => $data['details'][5]['ito_6'],
                "del_rate_1" => $data['details'][0]['delivery_rate_1'],
                "del_rate_2" => $data['details'][1]['delivery_rate_2'],
                "del_rate_3" => $data['details'][2]['delivery_rate_3'],
                "del_rate_4" => $data['details'][3]['delivery_rate_4'],
                "del_rate_5" => $data['details'][4]['delivery_rate_5'],
                "del_rate_6" => $data['details'][5]['delivery_rate_6'],
                "s_stock_1" => $data['details'][0]['safety_stock_1'],
                "s_stock_2" => $data['details'][1]['safety_stock_2'],
                "s_stock_3" => $data['details'][2]['safety_stock_3'],
                "s_stock_4" => $data['details'][3]['safety_stock_4'],
                "s_stock_5" => $data['details'][4]['safety_stock_5'],
                "s_stock_6" => $data['details'][5]['safety_stock_6'],
                // "prod_plan_1" => $prod_plan_1,
                // "prod_plan_2" => $prod_plan_2,
                // "prod_plan_3" => $prod_plan_3,
                // "prod_plan_4" => $prod_plan_4,
                // "prod_plan_5" => $prod_plan_5,
                // "prod_plan_6" => $prod_plan_6,
                "prod_plan_1" => $data['details'][0]['prod_plan_1'],
                "prod_plan_2" => $data['details'][1]['prod_plan_2'],
                "prod_plan_3" => $data['details'][2]['prod_plan_3'],
                "prod_plan_4" => $data['details'][3]['prod_plan_4'],
                "prod_plan_5" => $data['details'][4]['prod_plan_5'],
                "prod_plan_6" => $data['details'][5]['prod_plan_6'],
                "need_1" => $data['details'][0]['prod_plan_1'],
                "need_2" => $data['details'][1]['prod_plan_2'],
                "need_3" => $data['details'][2]['prod_plan_3'],
                "need_4" => $data['details'][3]['prod_plan_4'],
                "need_5" => $data['details'][4]['prod_plan_5'],
                "need_6" => $data['details'][5]['prod_plan_6'],
                "bal_end" => $data['balance'],
                "ltpp_date" => date("Y-m-d"),
                "notes" => "",
            );

            $postFinal_rev = array(
                "ltpp_doc" => $ltpp_doc,
                "period" => $header[0]['period'],
                "date_period" => $header[0]['date_period'],
                "lt" => $header[0]['lt'],
                "hkw_1" => $header[0]['hkw_1'],
                "hkw_2" => $header[0]['hkw_2'],
                "hkw_3" => $header[0]['hkw_3'],
                "hkw_4" => $header[0]['hkw_4'],
                "hkw_5" => $header[0]['hkw_5'],
                "hkw_6" => $header[0]['hkw_6'],
                "fc_m4" => $header[0]['fc_m4'],
                "rev" => $header[0]['rev'],
                "assy_no" => trim($data['product_no']),
                "item_name" => trim($data['product_name']),
                "cct" => $data['circuit_no'],
                "pp" => $data['pp'],
                "p1" => $data['p1'],
                "p2" => $data['p2'],
                "p3" => $data['p3'],
                "p4" => $data['p4'],
                "p5" => $data['p5'],
                "p6" => $data['p6'],
                "p7" => $data['p7'],
                "p8" => $data['p8'],
                "p9" => $data['p9'],
                "p10" => $data['p10'],
                "rep_qa" => $data['rqa'],
                "hav" => $data['hav'],
                "fg" => $data['fg'],
                "ost_mpp" => $data['os_mpp'],
                "t_stock" => $data['total_stock'],
                "fc1" => $data['details'][0]['forecast_1'],
                "fc2" => $data['details'][1]['forecast_2'],
                "fc3" => $data['details'][2]['forecast_3'],
                "fc4" => $data['details'][3]['forecast_4'],
                "fc5" => $data['details'][4]['forecast_5'],
                "fc6" => $data['details'][5]['forecast_6'],
                "t_fc" => ($data['details'][0]['forecast_1'] + $data['details'][1]['forecast_2'] + $data['details'][2]['forecast_3'] + $data['details'][3]['forecast_4'] + $data['details'][4]['forecast_5'] + $data['details'][5]['forecast_6']),
                "so_0" => @$sales_order->qty_1,
                "so_1" => @$sales_order->qty_2,
                "so_2" => @$sales_order->qty_3,
                "so_3" => @$sales_order->qty_4,
                "so_4" => @$sales_order->qty_5,
                "so_5" => @$sales_order->qty_6,
                "so_6" => "0.0000",
                "bal_1" => $data['details'][0]['begin_balance_1'],
                "bal_2" => $data['details'][1]['begin_balance_2'],
                "bal_3" => $data['details'][2]['begin_balance_3'],
                "bal_4" => $data['details'][3]['begin_balance_4'],
                "bal_5" => $data['details'][4]['begin_balance_5'],
                "bal_6" => $data['details'][5]['begin_balance_6'],
                "ito_1" => $data['details'][0]['ito_1'],
                "ito_2" => $data['details'][1]['ito_2'],
                "ito_3" => $data['details'][2]['ito_3'],
                "ito_4" => $data['details'][3]['ito_4'],
                "ito_5" => $data['details'][4]['ito_5'],
                "ito_6" => $data['details'][5]['ito_6'],
                "del_rate_1" => $data['details'][0]['delivery_rate_1'],
                "del_rate_2" => $data['details'][1]['delivery_rate_2'],
                "del_rate_3" => $data['details'][2]['delivery_rate_3'],
                "del_rate_4" => $data['details'][3]['delivery_rate_4'],
                "del_rate_5" => $data['details'][4]['delivery_rate_5'],
                "del_rate_6" => $data['details'][5]['delivery_rate_6'],
                "s_stock_1" => $data['details'][0]['safety_stock_1'],
                "s_stock_2" => $data['details'][1]['safety_stock_2'],
                "s_stock_3" => $data['details'][2]['safety_stock_3'],
                "s_stock_4" => $data['details'][3]['safety_stock_4'],
                "s_stock_5" => $data['details'][4]['safety_stock_5'],
                "s_stock_6" => $data['details'][5]['safety_stock_6'],
                // "prod_plan_1" => $prod_plan_1,
                // "prod_plan_2" => $prod_plan_2,
                // "prod_plan_3" => $prod_plan_3,
                // "prod_plan_4" => $prod_plan_4,
                // "prod_plan_5" => $prod_plan_5,
                // "prod_plan_6" => $prod_plan_6,
                "prod_plan_1" => $data['details'][0]['prod_plan_1'],
                "prod_plan_2" => $data['details'][1]['prod_plan_2'],
                "prod_plan_3" => $data['details'][2]['prod_plan_3'],
                "prod_plan_4" => $data['details'][3]['prod_plan_4'],
                "prod_plan_5" => $data['details'][4]['prod_plan_5'],
                "prod_plan_6" => $data['details'][5]['prod_plan_6'],
                "need_1" => $data['details'][0]['prod_plan_1'],
                "need_2" => $data['details'][1]['prod_plan_2'],
                "need_3" => $data['details'][2]['prod_plan_3'],
                "need_4" => $data['details'][3]['prod_plan_4'],
                "need_5" => $data['details'][4]['prod_plan_5'],
                "need_6" => $data['details'][5]['prod_plan_6'],
                "bal_end" => $data['balance'],
                "ltpp_date" => date("Y-m-d"),
                "notes" => "",
                "revisi_update" => "1",
                "update_date" => date("Y-m-d H:i:s"),
                "update_user" => $this->session->username,
            );

            if (count($ltpp_generate) > 0) {
                $this->dummy->where([
                    "period" => $header[0]['period'],
                    "assy_no" => $data['product_no'],
                    "rev" => $header[0]['rev']
                ]);

                if($this->dummy->update('ltpp_generate', $postFinal)){
                    $this->dummy->update('ltpp_generate_rev', $postFinal_rev, [
                        "period" => $header[0]['period'],
                        "assy_no" => $data['product_no'],
                        "rev" => $header[0]['rev']
                    ]);
                    echo json_encode(array("title" => "Good Job", "message" => $data['product_no'] . " | " . $data['product_name'] . " | Data Updated Successfully", "theme" => "success"));
                }else{
                    echo json_encode(array("title" => "Error", "message" => $data['product_no'] . " | " . $data['product_name'] . " | Data Unupdate", "theme" => "error"));
                }
            } else {
                if ($this->dummy->insert('ltpp_generate', $postFinal)) {
                    $this->dummy->insert('ltpp_generate_rev', $postFinal_rev);
                    echo json_encode(array("title" => "Good Job", "message" => $data['product_no'] . " | " . $data['product_name'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $data['product_no'] . " | " . $data['product_name'] . " | Data Unsaved", "theme" => "error"));
                }
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=generate_mps_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
        $filter_product_no = base64_decode($this->input->get('filter_item_fg'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $period = $filter_year . "-" . $filter_month;

        //Select Customer
        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('generate_mps a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
        }
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.revision', $filter_revision);
        $this->db->like('a.item_fg_id', $filter_product_no);
        $this->db->group_by('a.customer_id');
        $this->db->order_by('b.name', 'asc');
        $customers = $this->db->get()->result_array();

        $header = "";
        $headerDetails = "";

        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));
        $monthNameStart = date('F Y', $monthStart);

        $no = 1;
        while ($monthStart < $monthEnd) {
            $monthName = date('F Y', $monthStart);

            // if ($no == 1) {
                $xbar = '<th style="text-align:center;">SO</th>';
                $colspan = '6';
            // } else {
            //     $xbar = "";
            //     $colspan = '8';
            // }

            $header .= '<th style="text-align:center;" colspan="' . $colspan . '" width="50">' . strtoupper($monthName) . '</th>';
            $headerDetails .= ' <th style="text-align:center;">BEGIN <br> BALANCE</th>
                                    ' . $xbar . '
                                    <th style="text-align:center;">FC</th>
                                    <th style="text-align:center;">SAFETY <br> STOCK</th>
                                    <th style="text-align:center;">NEED</th>
                                    <th style="text-align:center;">PROD <br> PLAN</th>';
            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <div style="width:2500px;">
        <table style="width: 100%;">
            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                <img src="' . $config->logo . '" width="30">
            </td>
            <td style="font-size: 14px; text-align: left; margin:2px;">
                <b>' . $config->name . '</b><br>
                <small>PPC DEPARTEMENT</small>
            </td>
        </table>
        <center>
            <b style="font-size:18px;">MASTER PRODUCTION SCHEDULES</b>
        </center>
        <p style="font-size:12px; margin:0;">PERIOD ' . $this->monthName($filter_month) . ' ' . $filter_year . '</p>
        <p style="font-size:12px; margin:0;">REVISION ' . $filter_revision . '</p>
        <p style="font-size:12px; margin:0;">CUTOFF ' . @$customers[0]['cutoff'] . '</p>
        <p style="font-size:12px; margin:0;">CREATED DATE ' . @$customers[0]['created_date'] . '</p>
        <p style="font-size:12px; margin:0;">UPDATED DATE ' . @$customers[0]['updated_date'] . '</p>
        <br>
        <table id="customers" border="1">';
        $html .= '<tr>
                <th style="text-align:center;" rowspan="2" width="20">No</th>
                <th style="text-align:center;" rowspan="2" width="100">PRODUCT EBWS</th>
                <th style="text-align:center;" rowspan="2" width="150">PRODUCT CUSTOMER</th>
                <th style="text-align:center;" rowspan="2" width="100">DESCRIPTION</th>
                <th style="text-align:center;" rowspan="2" width="50">WIP</th>
                <th style="text-align:center;" rowspan="2" width="50">FG</th>
                <th style="text-align:center;" rowspan="2" width="50">OST<br>MPP</th>
                <th style="text-align:center;" rowspan="2" width="50">TOTAL<br>STOCK</th>
                <th style="text-align:center;" rowspan="2" width="50">OST<br>SO</th>';
        $html .= $header;
        $html .= '
                <th style="text-align:center;" rowspan="2" width="50">BAL</th>
            </tr>
            <tr>';
        $html .= $headerDetails;
        $html .= '</tr>';

        foreach ($customers as $customer) {
            if ($customer['customer_name'] == "") {
                $customer_name = "No Customer";
            } else {
                $customer_name = $customer['customer_name'];
            }

            $html .= '  <tr>
                            <th colspan="100" style="text-align:left;"><b>' . $customer_name . '</b></th>
                        </tr>';
            //Select Full
            $this->db->select('a.*, c.name as customer_name, e.number as item_fg_number, e.name as item_fg_name, e.number_customer');
            $this->db->from('generate_mps a');
            $this->db->join('generate_mps_details b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.item_fg_id = b.item_fg_id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('customer_items d', 'a.customer_id = d.customer_id and a.item_fg_id = d.item_fg_id', 'left');
            $this->db->join('item_fg e', 'a.item_fg_id = e.id');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
            $this->db->where('a.customer_id', $customer['customer_id']);
            $this->db->where("(a.pp > 0 or a.wc > 0 or a.ha > 0 or a.c > 0 or a.v > 0 or a.p > 0 or a.rfg or a.fg > 0 or a.os_mpp > 0 or a.os_so > 0 or a.total_stock > 0 or a.balance > 0 or 
                b.begin_balance > 0 or b.ito > 0 or b.forecast > 0 or b.delivery_rate > 0 or b.safety_stock > 0 or b.prod_plan > 0)");
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.item_fg_id', $filter_product_no);
            $this->db->group_by('a.item_fg_id');
            $records = $this->db->get()->result_array();

            $no = 1;
            foreach ($records as $data) {
                $html .= '  <tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td style="text-align:right;">' . $data['pp'] . '</td>
                            <td style="text-align:right;">' . $data['fg'] . '</td>
                            <td style="text-align:right;">' . $data['os_mpp'] . '</td>
                            <td style="text-align:right;">' . $data['total_stock'] . '</td>
                            <td style="text-align:right;">' . $data['os_so'] . '</td>';

                $this->db->select('a.*');
                $this->db->from('generate_mps_details a');
                $this->db->where('a.p_month', $data['p_month']);
                $this->db->where('a.p_year', $data['p_year']);
                $this->db->where('a.revision', $data['revision']);
                $this->db->where('a.customer_id', $data['customer_id']);
                $this->db->where('a.item_fg_id', $data['item_fg_id']);
                $this->db->where('a.deleted', 0);
                $this->db->group_by('a.ltpp_month');
                $this->db->order_by('a.id');
                $details2 = $this->db->get()->result_array();

                $nodetail = 1;
                foreach ($details2 as $detail2) {
                    // if ($nodetail == 1) {
                        // if ($detail2['qty'] == "") {
                        //     $xbarQty = 0;
                        // } else {
                        //     $xbarQty = $detail2['qty'];
                        // }

                        // $xbar2 = '<td style="text-align:right;">' . $xbarQty . '</td>';
                    // } else {
                    //     $xbar2 = "";
                    // }

                    $html .= '  <td style="text-align:right;">' . $detail2['begin_balance'] . '</td>
                                <td style="text-align:right;">' . $detail2['so'] . '</td>
                                <td style="text-align:right;">' . $detail2['forecast'] . '</td>
                                <td style="text-align:right;">' . $detail2['safety_stock'] . '</td>
                                <td style="text-align:right;">' . $detail2['need'] . '</td>
                                <td style="text-align:right;">' . $detail2['prod_plan'] . '</td>';
                    $nodetail++;
                }
                $html .= '<td style="text-align:right;">' . $data['balance'] . '</td>
            </tr>';
                $no++;
            }
        }

        $html .= '</table></div></body></html>';
        echo $html;
    }
}
