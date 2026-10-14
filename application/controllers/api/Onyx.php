<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Onyx extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
    }

    public function index()
    {
        redirect('error_access');
    }

    public function login(){
        // $url = 'https://stage-api.irisgst.com/irisgst/mgmt/login';
        $url = 'https://api.irisgst.com/irisgst/mgmt/login';

        $config = $this->crud->read("config");

        $data = array(
            // "email" => "bhawani.shankar@ebwsindia.com",
            // "password" => "Damien@2128"
            "email" => $config->iris_email,
            "password" => $config->iris_password,
        );

        $ch = curl_init($url);
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);

        if($response === false) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            die($response);
        }

        curl_close($ch);
    }

    public function cancelInvoice(){
        // $url = 'https://stage-api.irisgst.com/irisgst/onyx/irn/cancel';
        $url = 'https://api.irisgst.com/irisgst/onyx/irn/cancel';

        $post = $this->input->post('q');
        $token = $this->input->post('token');
        $companyId = $this->input->post('companyId');
        $config = $this->crud->read("config");

        $headers = [
            'Content-type: application/json',
            'companyId: ' . $companyId,
            'X-Auth-Token: '. $token,
            'product: ONYX',
        ];

        $data = array(
            "irn" => $post['irn'],
            "cnlRsn" => "1",
            "cnlRem" => "Wrong entry",
            "userGstin" => $config->gst_no,
        );

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);

        die($response);
        curl_close($ch);
    }

    public function addInvoice(){
        // $url = 'https://stage-api.irisgst.com/irisgst/onyx/irn/addInvoice';
        $url = 'https://api.irisgst.com/irisgst/onyx/irn/addInvoice';


        $config = $this->crud->read("config");
        $post = $this->input->post('q');
        $token = $this->input->post('token');
        $companyId = $this->input->post('companyId');
        $customer = $this->crud->read("customers", [], ["id" => $post['customer_id']]);
        $customer_address = $this->crud->read("customer_address", [], ["customer_id" => $post['customer_id'], "plant" => $post['plant']]);
        $state = $this->crud->read("states", [], ["name" => @$customer_address->ship_state]);
        $state2 = $this->crud->read("states", [], ["name" => @$config->state]);
        $sales_invoices = $this->crud->reads("sales_invoices", [], ["number" => $post['number']]);

        $shipEx = explode(",", @$customer_address->bill_address);
        
        $total_igst = 0;
        $total_cgst = 0;
        $total_sgst = 0;
        $no = 1;
        $itemLists = array();
        $total_tcs = 0;
        foreach ($sales_invoices as $sales_invoice) {
            $item_fg = $this->crud->read("item_fg", [], ["id" => @$sales_invoice->item_fg_id]);
            $alias = @$item_fg->alias;
            if(!$item_fg){
                $item_fg = $this->crud->read("item_rm", [], ["id" => @$sales_invoice->item_fg_id]);
                $alias = "";
            }

            $total_tcs = $sales_invoice->tcs;
            $hsn = $this->crud->read("hsn", [], ["number" => @$sales_invoice->hsn_code]);

            if($customer_address->ship_state == $config->state){
            // if("BENGALURU" == $config->state){
                $igst = 0;
                $cgst = $sales_invoice->cgst;
                $sgst = $sales_invoice->sgst;
                $hsn_igst = 0;
                $hsn_cgst = @$hsn->cgst;
                $hsn_sgst = @$hsn->sgst;
                $total_igst += 0;
                $total_cgst += $sales_invoice->cgst;
                $total_sgst += $sales_invoice->sgst;
                $ntr = "INTRA";
            }else{
                if($customer->type == "LOCAL"){
                    $igst = $sales_invoice->igst;
                    $cgst = 0;
                    $sgst = 0;
                    $hsn_igst = @$hsn->igst;
                    $hsn_cgst = 0;
                    $hsn_sgst = 0;
                    $total_igst += $sales_invoice->igst;
                    $total_cgst += 0;
                    $total_sgst += 0;
                    $ntr = "INTER";
                }else{
                    $igst = 0;
                    $cgst = 0;
                    $sgst = 0;
                    $total_igst += 0;
                    $total_cgst += 0;
                    $total_sgst += 0;
                    $ntr = "INTER";
                }
            }

            $itemLists[] = [
                "barcde" => null,
                "bchExpDt" => null,
                "bchWrDt" => null,
                "bchnm" => null,
                "camt" => $cgst,
                "cesNonAdval" => 0,
                "stCesNonAdvl" => 0,
                "crt" => @$hsn_cgst,
                "csamt" => 0,
                "csrt" => 0,
                "disc" => 0,
                "freeQty" => 0,
                "hsnCd" => $item_fg->hsn_code,
                "iamt" => $igst,
                "irt" => @$hsn_igst,
                "isServc" => "N",
                "itmgen1" => null,
                "itmgen2" => null,
                "itmgen3" => null,
                "itmgen4" => null,
                "itmgen5" => null,
                "itmgen6" => null,
                "itmgen7" => null,
                "itmgen8" => null,
                "itmgen9" => null,
                "itmgen10" => null,
                "itmVal" => $sales_invoice->total2,
                "num" => sprintf("%06s", $no),
                "ordLineRef" => null,
                "orgCntry" => null,
                "othchrg" => 0,
                "prdDesc" => trim($item_fg->name),
                "prdNm" => trim($item_fg->name) . " " . $alias,
                "prdSlNo" => null,
                "preTaxVal" => 0,
                "qty" => $sales_invoice->qty,
                "rt" => $sales_invoice->rate,
                "samt" => $sgst,
                "srt" => $hsn_sgst,
                "stcsamt" => 0,
                "stcsrt" => 0,
                "sval" => $sales_invoice->total,
                "txp" => null,
                "txval" => $sales_invoice->total,
                "unit" => $sales_invoice->uom,
                "unitPrice" => round($sales_invoice->price, 3),
                "invItmOtherDtls" => [
                        [
                            "attNm" => "aaa",
                            "attVal" => "aaa"
                        ],[
                            "attNm" => "xyz",
                            "attVal" => "2300"
                        ]
                    ]
            ];

            $no++;
        }

        $total_tax = round($post['total_grand'] - ($total_igst + $total_cgst + $total_sgst), 2);

        $data = array(
            "userGstin" => $config->gst_no,
            // "userGstin" => "01AAACI9260R002",
            "pobCode" => null,
            "supplyType" => "O",
            "ntr" => $ntr,
            "docType" => "RI",
            "catg" => "B2B",
            "dst" => "O", 
            "trnTyp" => "REG",
            "no" => $post['number'], 
            "dt" => date("d-m-Y", strtotime($post['trans_date'])), 
            "refinum" => null,
            "refidt" => null,
            "pos" => $state->irn_code,
            // "pos" => "01",
            "diffprcnt" => null,
            "etin" => null,
            "rchrg" => "N",
            "sgstin" => $config->gst_no,
            // "sgstin" => "01AAACI9260R002",
            "strdNm" => "ELC",
            "slglNm" => @$config->name,
            "sbnm" => @$config->address2,
            "sflno" => null,
            // "sloc" => @$config->state,
            "sloc" => "BHIWADI",
            "sdst" => @$config->state,
            // "sdst" => "BENGALURU",
            // "sstcd" => "01",
            "sstcd" => $state2->irn_code,
            "spin" => @$config->pin_code,
            // "spin" => "192233",
            "sph" => null, //No Telp EBWS
            "sem" => "sales@ebwsindia.com",
            "bgstin" => @$customer_address->gst_no,
            "btrdNm" => @$customer->name,
            "blglNm" => @$customer->name,
            // "bbnm" => @$customer_address->ship_address,
            "bbnm" => @$shipEx[0],
            "bflno" => null,
            "bloc" => @$customer_address->bill_state,
            "bdst" => @$customer_address->bill_state,
            "bstcd" => @$state->irn_code,
            "bpin" => @$customer_address->bill_pin,
            "bph" => null, // Telp Customer
            // "bph" => @$customer_address->ship_telp, // Telp Customer
            // "bem" => @$customer_address->ship_email,
            "bem" => null,
            "dgstin" => null,
            "dtrdNm" => null,
            "dlglNm" => null,
            "dbnm" => null,
            "dflno" => null,
            "dloc" => null,
            "ddst" => null,
            "dstcd" => null,
            "dpin" => null,
            "dph" => null,
            "dem" => null,
            "togstin" => null,
            "totrdNm" => null,
            "tolglNm" => null,
            "tobnm" => null,
            "toflno" => null,
            "toloc" => null,
            "todst" => null,
            "tostcd" => null,
            "topin" => null,
            "toph" => null,
            "toem" => null,
            "sbnum" => null,
            "sbdt" => null,
            "port" => null,
            "expduty" => 1234,
            "cntcd" => null,
            "forCur" => $customer->currency,
            "invForCur" => null,
            "taxSch" => "GST",
            "totinvval" => round($post['total_grand'] + round($total_tcs, 2), 2),
            "totdisc" => 0,
            "totfrt" => 0,
            "totins" => 0,
            "totpkg" => 0,
            "totothchrg" => round($total_tcs, 2),
            "tottxval" => $total_tax,
            "totiamt" => round($total_igst, 2),
            "totcamt" => round($total_cgst, 2),
            "totsamt" => round($total_sgst, 2),
            "totcsamt" => 0,
            "totstcsamt" => 0,
            "rndOffAmt" => 0,
            "sec7act" => "N",
            "invStDt" => null,
            "invEndDt" => null,
            "invRmk" => null,
            "omon" => null,
            "odty" => null,
            "oinvtyp" => "B2CL",
            "octin" => null,
            "userIRN" => null,
            "payNm" => null,
            "acctdet" => null,
            "pa" =>null,
            "mode" => null,
            "ifsc" => null,
            "payTerm" => null,
            "payInstr" => null,
            "crTrn" => null,
            "dirDr" => null,
            "crDay" => null,
            "balAmt" => null,
            "paidAmt" => null,
            "payDueDt" => null,
            "transId" => null,
            "subSplyTyp" => "Supply",
            "subSplyDes" => null,
            "kdrefinum" => null,
            "kdrefidt" => null,
            "transMode" => null,
            "vehTyp" => null,
            "transDist" => "",
            "transName" => null,
            "transDocNo" => null,
            "transDocDate" => null,
            "vehNo" => null,
            "clmrfnd" => null,
            "rfndelg" => null,
            "boef" => null,
            "fy" => null,
            "refnum" => null,
            "pdt" => null,
            "ivst" => null,
            "cptycde" => null,
            "gen1" => "abcd",
            "gen2" => "abcd",
            "gen3" => "abcd",
            "gen4" => "abcd",
            "gen5" => "abcd",
            "gen6" => "abcd",
            "gen7" => "abcd",
            "gen8" => "abcd",
            "gen9" => "abcd",
            "gen10" => "abcd",
            "gen11" => "abcd",
            "gen12" => "abcd",
            "gen13" => "abcd",
            "gen14" => "abcd",
            "gen15" => "abcd",
            "gen16" => "abcd",
            "gen17" => "abcd",
            "gen18" => "abcd",
            "gen19" => "abcd",
            "gen20" => "abcd",
            "gen21" => "abcd",
            "gen22" => "abcd",
            "gen23" => "abcd",
            "gen24" => "abcd",
            "gen25" => "abcd",
            "gen26" => "abcd",
            "gen27" => "abcd",
            "gen28" => "abcd",
            "gen29" => "abcd",
            "gen30" => "abcd",
            "pobewb" => "Null",
            "pobret" => "Null",
            "tcsrt" => "null",
            "tcsamt" => 0,
            "pretcs" => 0,
            "genIrn" => true,
            "genewb" => "N",
            "signedDataReq" => true,
            "itemList" => $itemLists,
            "invOthDocDtls" => [
                [
                "url" => "www.google.com",
                "docs" => "This is the url",
                "infoDtls" => "abcd"
                ]
            ],
            "invRefPreDtls" => [
                [
                "oinum" => null,
                "oidt" => null,
                "othRefNo" => null
                ]
            ],
            "invRefContDtls" => [
                [
                "raref" => null,
                "radt" => null,
                "tendref" => null,
                "contref" => null,
                "extref" => null,
                "projref" => null,
                "poref" => null,
                "porefdt" => null
                ]
            ]
        );

        $headers = [
            'Content-type: application/json',
            'companyId: ' . $companyId,
            'X-Auth-Token: '. $token,
            'product: ONYX',
        ];

        $ch = curl_init($url);
        $payload = json_encode($data);

        // die($payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);

        if($response === false) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            die($response);
        }

        curl_close($ch);
    }
}
