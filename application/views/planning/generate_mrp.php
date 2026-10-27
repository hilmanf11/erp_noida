<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Menu" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <div style="width: 30%; float: left;">
                <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                    <legend><b>Form Filter Data</b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period</span>
                        <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                        <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Revision</span>
                        <input style="width:60%;" name="filter_revision" id="filter_revision" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Cut Off</span>
                        <input style="width:60%;" id="filter_cutoff" class="easyui-datebox" value="<?= date("Y-m-25") ?>" required data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Part No</span>
                        <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </fieldset>
            </div>
            <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>MRP Component Parameter</b></legend>
                <table style="width: 100%;">
                    <tr>
                        <td style="padding:10px;"><input value="on" readonly="true" class="easyui-checkbox" id="check_mps"> &nbsp; MPS Production Plan</td>
                        <td style="padding:10px;"><input value="on" readonly="true" class="easyui-checkbox" id="check_mpp"> &nbsp; OS Workorder</td>
                    </tr>
                    <tr>
                        <td style="padding:10px;"><input value="on" readonly="true" class="easyui-checkbox" id="check_ospo"> &nbsp; OS Purchase Order</td>
                        <td style="padding:10px;"><input value="on" readonly="true" class="easyui-checkbox" id="check_supply"> &nbsp; OS Supply</td>
                    </tr>
                    <tr>
                        <td style="padding:10px;"><input value="on" readonly="true" class="easyui-checkbox" id="check_wip"> &nbsp; Balance WIP</td>
                        <td style="padding:10px;"><input value="on" readonly="true" class="easyui-checkbox" id="check_rm"> &nbsp; Stock Raw Material</td>
                    </tr>
                    <tr>
                        <td style="padding:10px;"><input value="on" class="easyui-checkbox" id="check_bypass"> &nbsp; By Pass Breakdown</td>
                        <td style="padding:10px;"></td>
                    </tr>
                </table>
            </fieldset>
            <fieldset style="width: 29%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>Generating Process</b></legend>

                <b>Breakdown MPS & BOM | Part No : <span id="txt_assy_no"></span></b>
                <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>

                <b>Generate MRP</b>
                <div id="p_upload4" class="easyui-progressbar" style="width:100%;"></div>
                <center><b id="p_start4">0</b> Of <b id="p_finish4">0</b></center>

                <b>Generate ABC Class</b>
                <div id="p_upload2" class="easyui-progressbar" style="width:100%;"></div>
                <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
            </fieldset>
        </div>
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="formula()"><i class="fa fa-info"></i> Help</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="exceptions()"><i class="fa fa-file"></i> Exception Report</a>
    </div>
</div>

<div id="toolbar">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excel_exception()"><i class="fa fa-file"></i> Export Excel</a>
</div>

<!-- EXCEPTION REPORT -->
<div id="dlg_exception" class="easyui-dialog" title="Exception Report (Material Requirements Planning)" data-options="closed: true,modal:true,maximizable:true" style="width: 95%; height: 600px; top: 20px;">
    <table id="dg_exception" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'item_rm_id',width:110,halign:'center'">Part No EBWS</th>
                <th rowspan="2" data-options="field:'item_rm_number',width:140,halign:'center'">Part No Supplier</th>
                <th rowspan="2" data-options="field:'item_rm_name',width:180,halign:'center'">Description</th>
                <th rowspan="2" data-options="field:'product_family',width:110,halign:'center'">Product Family</th>
                <th rowspan="2" data-options="field:'supplier_number',width:90,align:'center',halign:'center'">Supplier Code</th>
                <th rowspan="2" data-options="field:'supplier_name',width:140,halign:'center'">Supplier Name</th>
                <th rowspan="2" data-options="field:'price',width:80,align:'right',halign:'center',formatter:numberformatPrice">Price</th>
                <th rowspan="2" data-options="field:'leadtime',width:60,align:'center',halign:'center',formatter:numberformat,styler:numberStyle">PO L/T</th>
                <th rowspan="2" data-options="field:'mpq',width:70,align:'right',halign:'center',formatter:numberformat,styler:numberStyle">MPQ</th>
                <th rowspan="2" data-options="field:'moq',width:70,align:'right',halign:'center',formatter:numberformat,styler:numberStyle">MOQ</th>
                <th rowspan="2" data-options="field:'share_order',width:60,align:'center',halign:'center',formatter:numberformat,styler:numberStyle">Share %</th>
                <th rowspan="2" data-options="field:'hsn_code',width:80,align:'center',halign:'center',styler:hsnStyle">HSN</th>
                <th colspan="7" data-options="halign:'center'">STOCK RAW MATERIAL</th>
                <th colspan="2" data-options="halign:'center'"><b id="exp_th_m1">Month 1</b></th>
                <th colspan="2" data-options="halign:'center'"><b id="exp_th_m2">Month 2</b></th>
                <th colspan="2" data-options="halign:'center'"><b id="exp_th_m3">Month 3</b></th>
                <th colspan="2" data-options="halign:'center'"><b id="exp_th_m4">Month 4</b></th>
                <th colspan="2" data-options="halign:'center'"><b id="exp_th_m5">Month 5</b></th>
                <th colspan="2" data-options="halign:'center'"><b id="exp_th_m6">Month 6</b></th>
                <th colspan="2" data-options="halign:'center'">6 > Month</th>
                <th rowspan="2" data-options="field:'total_need',width:90,align:'right',halign:'center',formatter:numberformat,styler:totalNeedStyle">TOTAL NEED</th>
                <th rowspan="2" data-options="field:'exception_remark',width:240,halign:'center',styler:exceptionStyle">Exception Note</th>
            </tr>
            <tr>
                <th data-options="field:'qty_rm',width:75,align:'right',halign:'center',formatter:numberformat">WHS</th>
                <th data-options="field:'qty_wip',width:70,align:'right',halign:'center',formatter:numberformat">WIP</th>
                <th data-options="field:'qty_vendor',width:85,align:'right',halign:'center',formatter:numberformat">OS SUPPLIER</th>
                <th data-options="field:'qty_gate',width:70,align:'right',halign:'center',formatter:numberformat">GRN</th>
                <th data-options="field:'total_stock',width:85,align:'right',halign:'center',formatter:numberformat,styler:boldStyle">TOTAL</th>
                <th data-options="field:'qty_supply',width:75,align:'right',halign:'center',formatter:numberformat">OS SUPPLY</th>
                <th data-options="field:'total_wo',width:75,align:'right',halign:'center',formatter:numberformat">TOTAL OS</th>

                <th data-options="field:'need_1',width:70,align:'right',halign:'center',formatter:numberformat">NEED</th>
                <th data-options="field:'balance_1',width:75,align:'right',halign:'center',formatter:numberformat,styler:balanceStyle">BAL</th>

                <th data-options="field:'need_2',width:70,align:'right',halign:'center',formatter:numberformat">NEED</th>
                <th data-options="field:'balance_2',width:75,align:'right',halign:'center',formatter:numberformat,styler:balanceStyle">BAL</th>

                <th data-options="field:'need_3',width:70,align:'right',halign:'center',formatter:numberformat">NEED</th>
                <th data-options="field:'balance_3',width:75,align:'right',halign:'center',formatter:numberformat,styler:balanceStyle">BAL</th>

                <th data-options="field:'need_4',width:70,align:'right',halign:'center',formatter:numberformat">NEED</th>
                <th data-options="field:'balance_4',width:75,align:'right',halign:'center',formatter:numberformat,styler:balanceStyle">BAL</th>

                <th data-options="field:'need_5',width:70,align:'right',halign:'center',formatter:numberformat">NEED</th>
                <th data-options="field:'balance_5',width:75,align:'right',halign:'center',formatter:numberformat,styler:balanceStyle">BAL</th>

                <th data-options="field:'need_6',width:70,align:'right',halign:'center',formatter:numberformat">NEED</th>
                <th data-options="field:'balance_6',width:75,align:'right',halign:'center',formatter:numberformat,styler:balanceStyle">BAL</th>

                <th data-options="field:'avg_need',width:70,align:'right',halign:'center',formatter:numberformat">NEED</th>
                <th data-options="field:'avg_balance',width:75,align:'right',halign:'center',formatter:numberformat,styler:balanceStyle">BAL</th>
            </tr>
        </thead>
    </table>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height:95%; border: 0;"></iframe>
</div>
<script>
    function formula(){
        Swal.fire({
            width: 800,
            html: `<div style="text-align:left;">
                        <center><b style="font-size:16px !important;">MRP COMPONENT PARAMETER</b><hr></center>
                        <ul>
                            <li><b>Breakdown LTTP & BOM</b> is taken from (Generate LTTP x Bom)</li>
                            <li><b>WHS</b> is taken from Ending Stock in Store of Cut off</li>
                            <li><b>WIP</b> is taken from Generate MPP - Issued If result Minus</li>
                            <li><b>OS SUPPLIER</b> is taken from Purchase Order - Gate Entry of Cut Off</li>
                            <li><b>TOTAL STOCK</b> is taken from (WHS + WIP + OS SUPPLIER)</li>
                            <li><b>OS SUPPLY</b> Generate MPP - Issued If result Plus</li>
                        </ul>
                        <center><b style="font-size:16px !important;">FORMULA</b><hr></center>
                        <ul>
                            <li><b>NEED M1 to M6</b> is taken from Generate LTTP (Prodplan) * BOM (Qpa) according to every month</li>
                            <li><b>BAL M1</b> is taken from (TOTAL STOCK - OS WO + NEED M1)</li>
                            <li><b>BAL M2</b> is taken from (BAL M1 - NEED M2)</li>
                            <li><b>BAL M3</b> is taken from (BAL M2 - NEED M3)</li>
                            <li><b>BAL M4</b> is taken from (BAL M3 - NEED M4)</li>
                            <li><b>BAL M5</b> is taken from (BAL M4 - NEED M5)</li>
                            <li><b>BAL M6</b> is taken from (BAL M5 - NEED M6)</li>
                            <li><b>SAFETY STOCK</b> is taken from (Supplier Items (safety stock) * TOTAL NEED)</li>
                            <li><b>TOTAL NEED</b> Calculate Leadtime if Supplier (type) = "LOCAL" then Current Month from BAL else Next Month from BAL + SAFETY STOCK</li>
                            <li><b>MRP RESULT</b> is taken from Round Up from TOTAL NEED</li>
                        </ul>
                    </div>`,
        });
    }

    function exceptions(){
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.warning("Please Choose Filter Date and Revision!");
            return false;
        }

        $('#dlg_exception').dialog('open');
        $('#dg_exception').datagrid('loading');
        
        $.ajax({
            url: "<?= base_url('planning/generate_mrp/datatableException') ?>",
            type: 'POST',
            data: {
                filter_month: filter_month,
                filter_year: filter_year,
                filter_revision: filter_revision,
                filter_cutoff: filter_cutoff
            },
            dataType: 'json',
            success: function(result) {
                if (result.periods) {
                    $('#exp_th_m1').html(result.periods.p1);
                    $('#exp_th_m2').html(result.periods.p2);
                    $('#exp_th_m3').html(result.periods.p3);
                    $('#exp_th_m4').html(result.periods.p4);
                    $('#exp_th_m5').html(result.periods.p5);
                    $('#exp_th_m6').html(result.periods.p6);
                }
                $('#dg_exception').datagrid('loadData', result).datagrid('loaded');
            },
            error: function() {
                $('#dg_exception').datagrid('loaded');
                toastr.error("Failed to load exception report");
            }
        });
    }

    function generateDataMrp(filter_month, filter_year, filter_revision, filter_product_family, filter_part_no, filter_cutoff){
        Swal.fire({
            title: 'Please Wait for Calculating MRP',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mrp/getDataMrp') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_product_family=" + window.btoa(filter_product_family) +
                "&filter_part_no=" + window.btoa(filter_part_no) +
                "&filter_cutoff=" + window.btoa(filter_cutoff),
            dataType: "json",
            success: function(rows4) {
                Swal.close();

                requestData4(rows4['total'], rows4);
                function requestData4(total4, json4, number4 = 1, value4 = 0, success4 = 1, failed4 = 1) {
                    if (value4 < 100) {
                        value4 = Math.floor((number4 / total4) * 100);
                        $('#p_upload4').progressbar('setValue', value4);
                        $('#p_start4').html(number4);
                        $('#p_finish4').html(total4);

                        $.post('<?= base_url('planning/generate_mrp/createMrp') ?>', {
                            data: json4[number4 - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            if (result.theme == "success") {
                                requestData4(total4, json4, number4 + 1, value4, success4 + 1, failed4 + 0);
                            } else {
                                requestData4(total4, json4, number4 + 1, value4, success4 + 0, failed4 + 1);
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                toastr.error("Connection time out");
                                requestData4(total4, json4, number4 + 1, value4, success4 + 0, failed4 + 1);
                            }
                        });
                    }else{
                        Swal.fire({
                            title: 'Please Wait for Generating ABC Class',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: "get",
                            url: "<?= base_url('planning/generate_mrp/getDataMrpFinals') ?>",
                            data: "filter_month=" + window.btoa(filter_month) +
                                "&filter_year=" + window.btoa(filter_year) +
                                "&filter_revision=" + window.btoa(filter_revision) +
                                "&filter_product_family=" + window.btoa(filter_product_family) +
                                "&filter_part_no=" + window.btoa(filter_part_no) +
                                "&filter_cutoff=" + window.btoa(filter_cutoff),
                            dataType: "json",
                            success: function(rows2) {
                                Swal.close();

                                requestData2(rows2['total'], rows2);
                                function requestData2(total2, json2, number2 = 1, value2 = 0, success2 = 1, failed2 = 1) {
                                    if (value2 < 100) {
                                        value2 = Math.floor((number2 / total2) * 100);
                                        $('#p_upload2').progressbar('setValue', value2);
                                        $('#p_start2').html(number2);
                                        $('#p_finish2').html(total2);

                                        $.post('<?= base_url('planning/generate_mrp/createAbc') ?>', {
                                            data: json2[number2 - 1]
                                        }, function(note) {
                                            var result = eval('(' + note + ')');
                                            if (result.theme == "success") {
                                                requestData2(total2, json2, number2 + 1, value2, success2 + 1, failed2 + 0);
                                            } else {
                                                requestData2(total2, json2, number2 + 1, value2, success2 + 0, failed2 + 1);
                                            }

                                            if (value2 == 100) {
                                                Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                            }
                                        }).fail(function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                toastr.error("Connection time out");
                                                requestData2(total2, json2, number2 + 1, value2, success2 + 0, failed2 + 1);
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    }
                }
            }
        });
    }
    
    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');

        var check_mps = $("#check_mps").checkbox('options');
        var check_ospo = $("#check_ospo").checkbox('options');
        var check_wip = $("#check_wip").checkbox('options');
        var check_mpp = $("#check_mpp").checkbox('options');
        var check_rm = $("#check_rm").checkbox('options');
        var check_supply = $("#check_supply").checkbox('options');
        var check_bypass = $("#check_bypass").checkbox('options');

        if (check_mps.checked == true && 
            check_ospo.checked == true && 
            check_wip.checked == true && 
            check_mpp.checked == true && 
            check_rm.checked == true &&
            check_supply.checked && 
            filter_cutoff != "") {

            $.messager.prompt('Generate MRP', 'Please input Password Generate', function(r){
                if (r == "GENERATEMRP"){
                    $.ajax({
                        type: "get",
                        url: "<?= base_url('planning/generate_mrp/checkPurchaseOrder') ?>",
                        data: "filter_month=" + window.btoa(filter_month) +
                            "&filter_year=" + window.btoa(filter_year) +
                            "&filter_revision=" + window.btoa(filter_revision) +
                            "&filter_product_family=" + window.btoa(filter_product_family) +
                            "&filter_part_no=" + window.btoa(filter_part_no),
                        dataType: "json",
                        success: function(check) {
                            if(check.status == "OK"){
                                if(check_bypass.checked == false){
                                    Swal.fire({
                                        title: 'Please Wait for Generating Data',
                                        showConfirmButton: false,
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        },
                                    });

                                    $.ajax({
                                        type: "get",
                                        url: "<?= base_url('planning/generate_mrp/getDataMps') ?>",
                                        data: "filter_month=" + window.btoa(filter_month) +
                                            "&filter_year=" + window.btoa(filter_year) +
                                            "&filter_revision=" + window.btoa(filter_revision) +
                                            "&filter_product_family=" + window.btoa(filter_product_family) +
                                            "&filter_part_no=" + window.btoa(filter_part_no),
                                        dataType: "json",
                                        success: function(rows) {
                                            Swal.close();

                                            if(rows['total'] > 0){
                                                requestData(rows['total'], rows);

                                                function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                                    if (value < 100) {
                                                        value = Math.floor((number / total) * 100);
                                                        $('#p_upload').progressbar('setValue', value);
                                                        $('#p_start').html(number);
                                                        $('#p_finish').html(total);
                                                        $('#txt_assy_no').html(json[number - 1].product_no);

                                                        $.post('<?= base_url('planning/generate_mrp/create') ?>', {
                                                            data: json[number - 1]
                                                        }, function(note) {
                                                            var result = eval('(' + note + ')');

                                                            if (result.theme == "success") {
                                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                            } else {
                                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                                            }
                                                        }).fail(function(jqXHR, textStatus) {
                                                            if (textStatus == "error") {
                                                                toastr.error("Connection time out");

                                                                requestData(total, json, number, value, success + 0, failed + 1);
                                                            }
                                                        });
                                                    } else {
                                                        generateDataMrp(filter_month, filter_year, filter_revision, filter_product_family, filter_part_no, filter_cutoff);
                                                    }
                                                }
                                            }else{
                                                toastr.warning("Data MPS Not Found");
                                            }
                                        }
                                    });
                                    
                                }else{
                                    generateDataMrp(filter_month, filter_year, filter_revision, filter_product_family, filter_part_no, filter_cutoff);
                                }
                            }else{
                                Swal.close();
                                Swal.fire('Failed!', 'Purchase order already Created, please change the revision!', 'error');
                            }
                        }
                    });
                }
            });
        } else {
            toastr.warning("MRP Component Parameter Not Complete ", "Information");
        }
    }

    function componentCheck(filter_month, filter_year) {
        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mrp/checkMps') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year),
                // "&filter_revision=" + window.btoa(filter_revision) +
                // "&filter_cutoff=" + window.btoa(filter_cutoff),
            dataType: "json",
            success: function(mps) {
                if (mps.theme == "success") {
                    $('#check_mps').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_mps').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mrp/checkOspo') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year),
                // "&filter_revision=" + window.btoa(filter_revision) +
                // "&filter_cutoff=" + window.btoa(filter_cutoff),
            dataType: "json",
            success: function(ospo) {
                if (ospo.theme == "success") {
                    $('#check_ospo').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_ospo').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mrp/checkWip') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year),
                // "&filter_revision=" + window.btoa(filter_revision) +
                // "&filter_cutoff=" + window.btoa(filter_cutoff),
            dataType: "json",
            success: function(wip) {
                if (wip.theme == "success") {
                    $('#check_wip').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_wip').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mrp/checkRm') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year),
                // "&filter_revision=" + window.btoa(filter_revision) +
                // "&filter_cutoff=" + window.btoa(filter_cutoff),
            dataType: "json",
            success: function(rm) {
                if (rm.theme == "success") {
                    $('#check_rm').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_rm').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mrp/checkMpp') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year),
                // "&filter_revision=" + window.btoa(filter_revision) +
                // "&filter_cutoff=" + window.btoa(filter_cutoff),
            dataType: "json",
            success: function(mpp) {
                if (mpp.theme == "success") {
                    $('#check_mpp').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_mpp').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mrp/checkSupply') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year),
                // "&filter_revision=" + window.btoa(filter_revision) +
                // "&filter_cutoff=" + window.btoa(filter_cutoff),
            dataType: "json",
            success: function(supply) {
                if (supply.theme == "success") {
                    $('#check_supply').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_supply').checkbox({
                        checked: false
                    });
                }
            }
        });
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please Choose Filter Date and Revision!", "Information");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/generate_mrp/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please Choose Filter Date and Revision!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/generate_mrp/print/excel') ?>' + url);
        }
    }

    function excel_exception() {
        $('#dg_exception').datagrid('toExcel', 'report_exceptions.xls');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
    	$("#add").html("Generate");

        $('#dg_exception').datagrid({
            pagination: true,
            clientPaging: true,
            rownumbers: true,
            fit: true,
            fitColumns: false,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(month){
                $("#filter_cutoff").datebox('clear');
                var filter_year = $("#filter_year").combobox('getValue');
                
                componentCheck(month.id, filter_year);

                $('#filter_revision').combobox({
                    url: '<?php echo base_url('planning/generate_mrp/readRevisions?filter_month='); ?>' + btoa(month.id) + "&filter_year=" + btoa(filter_year),
                    valueField: 'revision',
                    textField: 'revision',
                    prompt: 'Select Revision',
                    formatter: function(val){
                        return "Revision " + val.revision; 
                    },
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(year){
                $("#filter_cutoff").datebox('clear');
                var filter_month = $("#filter_month").combobox('getValue');
                
                componentCheck(filter_month, year.id);

                $('#filter_revision').combobox({
                    url: '<?php echo base_url('planning/generate_mrp/readRevisions?filter_month='); ?>' + btoa(filter_month) + "&filter_year=" + btoa(year.id),
                    valueField: 'revision',
                    textField: 'revision',
                    prompt: 'Select Revision',
                    formatter: function(val){
                        return "Revision " + val.revision; 
                    },
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#filter_cutoff').datebox({
            onChange: function(cutoff){
                var filter_month = $("#filter_month").combobox('getValue');
                var filter_year = $("#filter_year").combobox('getValue');
                var filter_revision = $("#filter_revision").combobox('getValue');

                if(filter_month != "" && filter_year != ""){
                    componentCheck(filter_month, filter_year, filter_revision, cutoff);
                }
            }
        });

        $('#filter_product_family').combobox({
            url: '<?php echo base_url('master/item_familys/readss/C01'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $('#filter_part_no').combogrid({
                    url: '<?= base_url('master/item_rm/reads/') ?>' + row.id,
                    panelWidth: 550,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Part No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'id',
                            title: 'Part No EBWS',
                            width: 150,
                            halign: 'center'
                        },{
                            field: 'number',
                            title: 'Part No Supplier',
                            width: 200,
                            halign: 'center'
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 200,
                            halign: 'center'
                        }]
                    ]
                });
            }
        });
    });

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    //Format Datepicker
    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }

    function numberformat(value, row, index) {
        if (value === null || value === undefined || value === "") {
            return "0";
        }
        var num = (typeof value === 'string') ? parseFloat(value.replace(/,/g, '')) : parseFloat(value);
        if (isNaN(num)) {
            return value;
        }
        return new Intl.NumberFormat('en-US').format(num);
    }

    function numberformatPrice(value, row, index) {
        if (value === null || value === undefined || value === "") {
            return "0.00";
        }
        var num = (typeof value === 'string') ? parseFloat(value.replace(/,/g, '')) : parseFloat(value);
        if (isNaN(num)) {
            return value;
        }
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(num);
    }

    function numberStyle(value, row, index) {
        var num = (typeof value === 'string') ? parseFloat(value.replace(/,/g, '')) : parseFloat(value);
        if (num == 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function hsnStyle(value, row, index) {
        if (value == "-" || value == "" || value === null) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function balanceStyle(value, row, index) {
        var num = (typeof value === 'string') ? parseFloat(value.replace(/,/g, '')) : parseFloat(value);
        if (num < 0) {
            return 'color: #d9534f; font-weight: bold;';
        }
        return '';
    }

    function totalNeedStyle(value, row, index) {
        var num = (typeof value === 'string') ? parseFloat(value.replace(/,/g, '')) : parseFloat(value);
        if (num > 0) {
            return 'background-color: #FFECEC; color: #c9302c; font-weight: bold;';
        }
        return '';
    }

    function boldStyle(value, row, index) {
        return 'font-weight: bold;';
    }

    function exceptionStyle(value, row, index) {
        return 'background-color: #FFF3CD; color: #856404; font-weight: bold;';
    }
</script>