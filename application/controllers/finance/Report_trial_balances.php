<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_trial_balances extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('finance/report_trial_balances');
        } else {
            redirect('error_access');
        }
    }

    public function begin_balance($filter_from, $display){
        if($display == "Before"){
            $where_modul = "and modul != 'CLOSING JOURNAL'";
        }else{
            $where_modul = "";
        }

        $query = $this->db->query("SELECT account_number, account_name, COALESCE(SUM(local_debit)) as ending_debit, COALESCE(SUM(local_credit)) as ending_credit
            FROM (
                (SELECT journal_date, `number`, account_number, account_name, modul, `description`, currency, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit
                FROM journal_postings
                WHERE journal_date < '$filter_from' and modul NOT IN ('BANK STATEMENT')
                GROUP BY `number`, account_number) 
            UNION ALL
                (SELECT journal_date, `number`, account_number, account_name, modul, `description`, currency, 
                (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) as local_debit,
                (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) as local_credit
                FROM journal_postings
                WHERE journal_date < '$filter_from' and modul = 'BANK STATEMENT'
                GROUP BY `number`, account_number)
            ) a 
            GROUP BY account_number");
        $journals = $query->result_array();

        $data = [];
        foreach ($journals as $journal) {
            $journal_debit = @$journal['ending_debit'];
            $journal_credit = @$journal['ending_credit'];

            if(($journal_debit - $journal_credit) > 0){
                $journal_end_debit_f = ($journal_debit - $journal_credit);
                $journal_end_credit_f = 0;
            }else{
                $journal_end_debit_f = 0;
                $journal_end_credit_f = abs($journal_debit - $journal_credit);
            }

            $data[@$journal['account_number']] = array(
                "local_debit" => @$journal_debit,
                "local_credit" => @$journal_credit,
                "ending_debit" => $journal_end_debit_f,
                "ending_credit" => $journal_end_credit_f,
                "header" => 1,
            );
        }

        return $data;
    }

    public function getData($filter_from, $filter_to, $display){
        $period = date("Ym", strtotime($filter_from));

        if($display == "Before"){
            $where_modul = "and modul != 'CLOSING JOURNAL'";
        }elseif($display == "Comparison"){
            $where_modul = "and modul != 'CLOSING JOURNAL'";
        }else{
            $where_modul = "";
        }
        
        $this->db->select('*');
        $this->db->from('account_group_details');
        // $this->db->where("number", "28000000");
        $this->db->order_by('number', 'asc');
        $account_groups = $this->db->get()->result_array();

        $query = $this->db->query("SELECT account_number, account_name, COALESCE(SUM(local_debit)) as local_debit, COALESCE(SUM(local_credit)) as local_credit
            FROM (
                (SELECT journal_date, `number`, account_number, account_name, modul, `description`, currency, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit
                FROM journal_postings
                WHERE journal_date between '$filter_from' and '$filter_to' and modul NOT IN ('BANK STATEMENT') $where_modul
                GROUP BY `number`, account_number) 
            UNION ALL 
                (SELECT journal_date, `number`, account_number, account_name, modul, `description`, currency, 
                (CASE WHEN SUM(local_credit) > 0 THEN SUM(local_credit) ELSE 0 END) as local_debit,
                (CASE WHEN SUM(local_debit) > 0 THEN SUM(local_debit) ELSE 0 END) as local_credit
                FROM journal_postings
                WHERE journal_date between '$filter_from' and '$filter_to' and modul = 'BANK STATEMENT' $where_modul
                GROUP BY `number`, account_number)
            ) a GROUP BY account_number");
        $journals = $query->result_array();

        $arrJournal = [];
        foreach ($journals as $journal) {
            $arrJournal[$journal['account_number']] = [
                "local_debit" => $journal['local_debit'],
                "local_credit" => $journal['local_credit']
            ];
        }

        $queryClosing = $this->db->query("SELECT journal_date, `number`, account_number, account_name, modul, `description`, currency, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit
            FROM journal_postings
            WHERE journal_date BETWEEN '$filter_from' AND '$filter_to' AND modul NOT IN ('BANK STATEMENT') AND modul = 'CLOSING JOURNAL'
            GROUP BY account_number");
        $journalClosing = $queryClosing->result_array();

        $arrClosing = [];
        foreach ($journalClosing as $closing) {
            $arrClosing[$closing['account_number']] = [
                "local_debit" => empty($closing['local_debit']) ? 0 : $closing['local_debit'],
                "local_credit" => empty($closing['local_credit']) ? 0 : $closing['local_credit'],
            ];
        }

        $journal2 = $this->begin_balance($filter_from, $display);

        $sort = 1;
        $data = [];
        foreach ($account_groups as $account_group) {
            $this->db->select('a.*');
            $this->db->from('account_coa a');
            $this->db->where('a.account_group_detail_id', $account_group['id']);
            // $this->db->where('a.account_number', "28000004");
            $this->db->order_by('a.account_number', 'asc');
            $accounts = $this->db->get()->result_array();

            $local_debit = 0;
            $local_credit = 0;
            $begin_debit = 0;
            $begin_credit = 0;
            $ending_debit = 0;
            $ending_credit = 0;
            $final_balance_total = 0;
            $closing_debit_total = 0;
            $closing_credit_total = 0;
            $closing_ending_debit_total = 0;
            $closing_ending_credit_total = 0;
            $closing_final_total = 0;
            $text = "";
            $sortsub = 0;
            $detail = [];
            foreach ($accounts as $account) {
                $account_number = $account['account_number'];

                if(($account['local_debit'] + @$journal2[$account_number]['ending_debit']) - abs($account['local_kredit'] + @$journal2[$account_number]['ending_credit']) > 0){
                    $journal_begin_debit = (($account['local_debit'] + @$journal2[$account_number]['ending_debit']) - abs($account['local_kredit'] + @$journal2[$account_number]['ending_credit']));
                    $journal_begin_credit = 0;
                }else{
                    $journal_begin_debit = 0;
                    $journal_begin_credit = abs(($account['local_debit'] + @$journal2[$account_number]['ending_debit']) - abs($account['local_kredit'] + @$journal2[$account_number]['ending_credit']));
                }

                // $journal_begin_debit = ($account['local_debit'] + @$journal2[$account_number]['ending_debit']);
                // $journal_begin_credit = abs($account['local_kredit'] + @$journal2[$account_number]['ending_credit']);

                $begin_debit += $journal_begin_debit;
                $begin_credit += $journal_begin_credit;

                $journal_debit = empty($arrJournal[$account_number]['local_debit']) ? 0 : $arrJournal[$account_number]['local_debit'];
                $journal_credit = empty($arrJournal[$account_number]['local_credit']) ? 0 : $arrJournal[$account_number]['local_credit'];
                
                if($account_group['number'] != $account_number){
                    $local_debit += $journal_debit;
                    $local_credit += $journal_credit;

                    $journal_end_debit = ($account['local_debit'] + @$journal2[$account_number]['ending_debit'] + $journal_debit);
                    $journal_end_credit = ($account['local_kredit'] + @$journal2[$account_number]['ending_credit'] + $journal_credit);

                    if(($journal_end_debit - $journal_end_credit) > 0){
                        $ending_debit += ($journal_end_debit - $journal_end_credit);
                        $journal_end_debit_f = ($journal_end_debit - $journal_end_credit);
                        $journal_end_credit_f = 0;
                    }else{
                        $ending_credit += abs($journal_end_debit - $journal_end_credit);
                        $journal_end_debit_f = 0;
                        $journal_end_credit_f = abs($journal_end_debit - $journal_end_credit);
                    }

                    $final_balance = ((($account['local_debit'] + @$journal2[$account_number]['ending_debit']) + $journal_debit) - (($account['local_kredit'] + @$journal2[$account_number]['ending_credit']) + $journal_credit));
                    $final_balance_total += $final_balance;

                    $closing_debit = @$arrClosing[$account_number]['local_debit'];
                    $closing_credit = @$arrClosing[$account_number]['local_credit'];
                    $closing_debit_total += @$arrClosing[$account_number]['local_debit'];
                    $closing_credit_total += @$arrClosing[$account_number]['local_credit'];

                    if((($closing_debit + $journal_end_debit_f) - ($closing_credit + $journal_end_credit_f)) > 0){
                        $closing_ending_debit = (($closing_debit + $journal_end_debit_f) - ($closing_credit + $journal_end_credit_f));
                        $closing_ending_credit = 0;
                    }else{
                        $closing_ending_debit = 0;
                        $closing_ending_credit = abs(($closing_debit + $journal_end_debit_f) - ($closing_credit + $journal_end_credit_f));
                    }

                    $closing_ending_debit_total += $closing_ending_debit;
                    $closing_ending_credit_total += $closing_ending_credit;

                    $closing_final = (($closing_debit + $journal_end_debit_f) - ($closing_credit + $journal_end_credit_f));
                    $closing_final_total += $closing_final;

                    $detail[] = array(
                        "period" => $period,
                        "account_number" => $account_number,
                        "account_name" => $account['account_name'],
                        "begin_debit" => $journal_begin_debit,
                        "begin_credit" => $journal_begin_credit,
                        "local_debit" => $journal_debit,
                        "local_credit" => $journal_credit,
                        "ending_debit" => $journal_end_debit_f,
                        "ending_credit" => $journal_end_credit_f,
                        "final_balance" => $final_balance,
                        "closing_debit" => $closing_debit,
                        "closing_credit" => $closing_credit,
                        "closing_tb_debit" => $closing_ending_debit,
                        "closing_tb_credit" => $closing_ending_credit,
                        "closing_final" => $closing_final,
                        "header" => 1,
                        "sort" => $sort,
                        "sortsub" => $sortsub
                    );
                }

                $sortsub++;
            }

            $data[] = array(
                "period" => $period,
                "account_number" => $account_group['number'],
                "account_name" => $account_group['name'],
                "begin_debit" => $begin_debit,
                "begin_credit" => $begin_credit,
                "local_debit" => $local_debit,
                "local_credit" => $local_credit,
                "ending_debit" => $ending_debit,
                "ending_credit" => $ending_credit,
                "final_balance" => $final_balance_total,
                "closing_debit" => $closing_debit_total,
                "closing_credit" => $closing_credit_total,
                "closing_tb_debit" => $closing_ending_debit_total,
                "closing_tb_credit" => $closing_ending_credit_total,
                "closing_final" => $closing_final_total,
                "header" => 0,
                "sort" => $sort,
                "sortsub" => "-",
                "details" => $detail
            );

            $sort++;
        }

        return $data;
    }

    function linked($filter_from, $filter_to, $account_number, $amount){
        return '<a onclick="window.open(\'' . base_url('finance/report_general_ledgers/print?filter_from=' . base64_encode($filter_from) . '&filter_to=' . base64_encode($filter_to) . '&filter_account=' . base64_encode($account_number)) . '\', \'_blank\', \'location=yes,height=650,width=1500,scrollbars=yes,status=yes\');" style="color: inherit;text-decoration: none;cursor: pointer;">' . number_format($amount, 2) . '</a>';
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=trial_balances_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));

        if($filter_from < '2024-11-01'){
            $filter_from = "2024-11-01";
        }

        $filter_to = base64_decode($this->input->get("filter_to"));
        $display = $this->input->get("display");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $trial_balances = $this->getData($filter_from, $filter_to, $display);

        if($display == "Comparison"){
            $html_com1 = '  <th rowspan="2" colspan="2">Closing Entries</th>
                            <th rowspan="2" colspan="2">Post Closing Trial Balance</th>
                            <th rowspan="3">Final Balance</th>';
            $html_com2 = '  <th>Debit</th>
                            <th>Credit</th>
                            <th>Debit</th>
                            <th>Credit</th>';
        }else{
            $html_com1 = "";
            $html_com2 = "";
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <center>
                <h3 style="margin:0;">TRIAL BALANCE</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <table id="customers" border="1">
            <tr>
                <th rowspan="3" width="20">No</th>
                <th rowspan="3">Account No</th>
                <th rowspan="3">Account Name</th>
                <th colspan="6">LOCAL CURRENCY</th>
                <th rowspan="3">Final Balance</th>
                '.$html_com1.'
            </tr>
            <tr>
                <th colspan="2">Begin Balance</th>
                <th colspan="2">Transaction</th>
                <th colspan="2">End Balance</th>
            </tr>
            <tr>
                <th>Debit</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Credit</th>
                '.$html_com2.'
            </tr>';

        $no = 1;
        $grand_total_begin_debit = 0;
        $grand_total_begin_credit = 0;
        $grand_total_local_debit = 0;
        $grand_total_local_credit = 0;
        $grand_total_ending_debit = 0;
        $grand_total_ending_credit = 0;
        $grand_total_final_balance = 0;

        $grand_closing_debit = 0;
        $grand_closing_credit = 0;
        $grand_closing_ending_debit = 0;
        $grand_closing_ending_credit = 0;
        $grand_total_final_balance2 = 0;
        foreach ($trial_balances as $trial_balance) {
            $style = 'style="background:#CAFFB3;"';
            $font = 'font-weight:bold;';

            if($display == "Comparison"){
                    $html_closing1 = '  <td style="text-align:right;'.$font.'">'.$this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['closing_debit']).'</td>
                                        <td style="text-align:right;'.$font.'">'.$this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['closing_credit']).'</td>
                                        <td style="text-align:right;'.$font.'">'.$this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['closing_tb_debit']).'</td>
                                        <td style="text-align:right;'.$font.'">'.$this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['closing_tb_credit']).'</td>
                                        <td style="text-align:right;'.$font.'">'.$this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['closing_final']).'</td>';
                }else{
                    $html_closing1 = "";
                }

            $html .= '  <tr '.$style.'>
                            <td style="'.$font.'">' . $no . '</td>
                            <td style="'.$font.'mso-number-format:\@;">' . $trial_balance['account_number'] . '</td>
                            <td style="'.$font.'">' . $trial_balance['account_name'] . '</td>
                            <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['begin_debit']) . '</td>
                            <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['begin_credit']) . '</td>
                            <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['local_debit']) . '</td>
                            <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['local_credit']) . '</td>
                            <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['ending_debit']) . '</td>
                            <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['ending_credit']) . '</td>
                            <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $trial_balance['account_number'], $trial_balance['final_balance']) . '</td>
                            '.$html_closing1.'
                        </tr>';
            $no++;
            foreach ($trial_balance['details'] as $detail) {
                $style = '';
                $font = '';

                if($display == "Comparison"){
                    $html_closing1 = '  <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['closing_debit']) . '</td>
                                        <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['closing_credit']) . '</td>
                                        <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['closing_tb_debit']) . '</td>
                                        <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['closing_tb_credit']) . '</td>
                                        <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['closing_final']) . '</td>';
                }else{
                    $html_closing1 = "";
                    $closing_debit = 0;
                    $closing_credit = 0;
                    $closing_ending_debit = 0;
                    $closing_ending_credit = 0;
                    $closing_final_balance = 0;
                }

                $html .= '  <tr '.$style.'>
                                <td style="'.$font.'">' . $no . '</td>
                                <td style="'.$font.'mso-number-format:\@;">' . $detail['account_number'] . '</td>
                                <td style="'.$font.'">' . $detail['account_name'] . '</td>
                                <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['begin_debit']) . '</td>
                                <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['begin_credit']) . '</td>
                                <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['local_debit']) . '</td>
                                <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['local_credit']) . '</td>
                                <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['ending_debit']) . '</td>
                                <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['ending_credit']) . '</td>
                                <td style="text-align:right;'.$font.'">' . $this->linked($filter_from, $filter_to, $detail['account_number'], $detail['final_balance']) . '</td>
                                '.$html_closing1.'
                            </tr>';

                $grand_total_begin_debit += $detail['begin_debit'];
                $grand_total_begin_credit += $detail['begin_credit'];
                $grand_total_local_debit += $detail['local_debit'];
                $grand_total_local_credit += $detail['local_credit'];
                $grand_total_ending_debit += $detail['ending_debit'];
                $grand_total_ending_credit += $detail['ending_credit'];
                $grand_total_final_balance += $detail['final_balance'];
                
                $grand_closing_debit += $detail['closing_debit'];
                $grand_closing_credit += $detail['closing_credit'];
                $grand_closing_ending_debit += $detail['closing_tb_debit'];
                $grand_closing_ending_credit += $detail['closing_tb_credit'];
                $grand_total_final_balance2 += $detail['closing_final'];
                $no++;
            }
        }

        if($display == "Comparison"){
            $html_closing1 = '  <td style="text-align:right;"><b>' . number_format(@$grand_closing_debit, 2) . '</b></td>
                                <td style="text-align:right;"><b>' . number_format(@$grand_closing_credit, 2) . '</b></td>
                                <td style="text-align:right;"><b>' . number_format(@$grand_closing_ending_debit, 2) . '</b></td>
                                <td style="text-align:right;"><b>' . number_format(@$grand_closing_ending_credit, 2) . '</b></td>
                                <td style="text-align:right;"><b>' . number_format(@$grand_total_final_balance2, 2) . '</b></td>';
        }else{
            $html_closing1 = "";
        }

        $html .= '  <tr style="background:#EBEBEB;">
                        <td></td>
                        <td></td>
                        <td><b>GRAND TOTAL</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_begin_debit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_begin_credit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_local_debit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_local_credit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_ending_debit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_ending_credit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_final_balance, 2) . '</b></td>
                        '.$html_closing1.'
                    </tr>';


        
        $html .= '</table></body></html>';
        echo $html;
    }
}
