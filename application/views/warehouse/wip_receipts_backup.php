<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'scan',width:100,halign:'center', styler:cellStylerScan, formatter:cellFormatterScan">Scan</th>
            <th rowspan="2" data-options="field:'checksheet_number',width:150,align:'center'">Checksheet ID</th>
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'">Workorder</th>
            <th rowspan="2" data-options="field:'trans_date_from',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'period',width:80,align:'center'">Period</th>
            <th rowspan="2" data-options="field:'wp',width:80,align:'center'">WP</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:150">Product No EBWS</th>
            <th rowspan="2" data-options="field:'number_customer',width:200">Product No Customer</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200">Description</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <!-- <th rowspan="2" data-options="field:'lot_label',width:80,halign:'center',align:'right'">Lot Label</th> -->
            <th rowspan="2" data-options="field:'lot_box',width:80,halign:'center',align:'right'">Lot Box</th>
            <!-- <th rowspan="2" data-options="field:'label',width:80,halign:'center',align:'right'">Label Qty</th> -->
            <th rowspan="2" data-options="field:'label_box',width:80,halign:'center',align:'right'">Box Qty</th>
            <!-- <th rowspan="2" data-options="field:'print',width:80,align:'center',formatter:BtnPrint">Label</th> -->
            <th rowspan="2" data-options="field:'print_box',width:80,align:'center',formatter:BtnPrintBox">Box</th>
            <th rowspan="2" data-options="field:'repeat',width:80,align:'center',formatter:BtnRepeatBox">Repeat</th>
            <!-- <th rowspan="2" data-options="field:'print_strip',width:80,align:'center',formatter:BtnPrintStrip">Small</th> -->
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Checksheet</span>
                    <input style="width:60%;" id="filter_checksheet" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_rfg()"><i class="fa fa-print"></i> Print Data</a>
                </div>
            </div>
            <div style="width:30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" id="filter_workorder" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period / WP</span>
                    <input style="width:30%;" id="filter_period" class="easyui-combobox">
                    <input style="width:30%;" id="filter_wp" class="easyui-combobox">
                </div>
            </div>
            <div style="width:30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status Scan</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="0">Not Scan</option>
                        <option value="1">Scanned</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; height: 550px; padding:10px; top: 20px; left: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" name="trans_date_from" id="trans_date_from" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" name="trans_date_to" id="trans_date_to" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period / WP</span>
                    <input style="width:30%;" name="period" id="period" required="" class="easyui-combobox">
                    <input style="width:30%;" name="wp" id="wp" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">RFG ID</span>
                    <input style="width:60%;" name="checksheet_number" id="checksheet_number" readonly required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Receiving Finish Good List" idField="item_number">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th hidden data-options="field:'item_fg_id',width:250">Product No</th>
                    <th data-options="field:'workorder',width:250">Work Order</th>
                    <th data-options="field:'period',width:150">Period</th>
                    <th data-options="field:'wp',width:150">WP</th>
                    <th data-options="field:'qty_wo',width:100,editor:{type:'numberbox', options:{readonly:true}}">Qty WO</th>
                    <th data-options="field:'qty_rfg',width:100,editor:{type:'numberbox', options:{readonly:true}}">Qty RFG</th>
                    <th data-options="field:'qty_receipt',width:100,editor:{type:'numberbox', options:{readonly:true}}">Qty</th>
                    <th data-options="field:'qty',width:100,editor:{type:'numberbox', options:{required:true}}">Receipt Qty</th>
                    <th data-options="field:'qty_box',width:100,editor:{type:'numberbox', options:{readonly:true}}">Lot Box</th>
                    <th data-options="field:'label_box',width:100,editor:{type:'numberbox', options:{readonly:true}}">Label Box</th>
                </tr>
            </thead>
        </table>
    </form>
</div>

<!-- INSERT LABEL -->
<div id="dlg_label" class="easyui-dialog" title="Create Data Label" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Create Label" class="easyui-panel" style="width:100%; height:300px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/wip_receipts/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('warehouse/wip_receipts/create') ?>';
        $('#frm_insert').form('clear');
        $("#dg_request").datagrid('loadData', []);

        $("#trans_date_from").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#trans_date_to").datebox({
            onChange: function(val){
                number("<?= $id_menu ?>");
            }
        });

        $("#trans_date_to").datebox('setValue', "<?= date("Y-m-d") ?>");

        $("#period").combobox({
            url: '<?= base_url('planning/production_schedules/readPeriod/period') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Select Periode No",
            onSelect: function(period){
                $("#wp").combobox({
                    url: '<?= base_url('planning/production_schedules/readWp?period=') ?>' + btoa(period.period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP",
                });
            }
        });

        $('#customer_id').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Customer',
            onSelect: function(row){
                $('#item_fg_id').combogrid({
                    url: '<?php echo base_url('master/customer_items/reads/'); ?>' + btoa(row.id),
                    panelWidth: 550,
                    idField: 'id',
                    textField: 'number_customer',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product No",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No EBWS',
                            width: 150
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 200
                        }]
                    ]
                });
            }
        });
    }

    function number(id_menu){
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/wip_receipts/number/') ?>" + id_menu,
            dataType: "html",
            success: function(result) {
                if(result == ""){
                    toastr.info("Please Check Configuration in Serial No First");
                }else{
                    $("#checksheet_number").textbox('setValue', result);
                }
            }
        });
    }

    function preview(url = "") {
        var period = $("#period").combobox('getValue');
        var wp = $("#wp").combobox('getValue');
        var customer_id = $("#customer_id").combobox('getValue');
        var item_fg_id = $("#item_fg_id").combogrid('getValue');

        if(url == ""){
            var urlGet = "<?= base_url('warehouse/wip_receipts/datatablesTemp/') ?>" + btoa(period) + "/" + btoa(wp) + "/" + btoa(customer_id) + "/" + btoa(item_fg_id);
        }else{
            var urlGet = url;
        }

        if (period == "" || wp == "" || customer_id == "" || item_fg_id == "") {
            toastr.warning('Please Select Period, WP, Customer and Product No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: urlGet,
                fitColumns: true,
                onLoadError: function(rowMessage){
                    toastr.error("Data Not Found in Old MRP Process Scan");
                },
                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                    var qty_wo = $(editors[0].target).numberbox('getValue');
                    var qty_rfg = $(editors[1].target).numberbox('getValue');
                    var qty_receipt = $(editors[2].target).numberbox('getValue');
                    var qty = $(editors[3].target);
                    var qty_box = $(editors[4].target).numberbox('getValue');
                    var label_box = $(editors[5].target);

                    qty.numberbox({
                        onChange: function(val){
                            if(parseInt(val) > parseInt(qty_receipt)){
                                toastr.info("Qty Receipt Cannot > Qty");
                                qty.numberbox('setValue', qty_receipt);
                                label_box.numberbox('setValue', 0);
                            }else{
                                label_box.numberbox('setValue', Math.ceil(parseInt(val) / parseInt(qty_box)));
                            }
                        }
                    });
                }
            });
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Delete Data',
                text: "Are you sure? You want to delete this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    requestDelete(rows.length, rows);
                    function requestDelete(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            // if(row.scan <= 0){
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('warehouse/wip_receipts/delete') ?>',
                                    data: {
                                        id: row.id,
                                        workorder: row.workorder,
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        
                                        if (result.theme == "error") {
                                            toastr.error(result.message, result.title);
                                            Swal.close();
                                            return false;
                                        }
                                        
                                        requestDelete(total, json, number + 1, value);

                                        if (number == total) {
                                            $('#dg').datagrid('reload');
                                            Swal.close();
                                            Swal.fire(
                                                'Delete Completed',
                                                'Delete Data has been completed, You cannot restore data that has been deleted',
                                                'success'
                                            );
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                    },
                                });
                            // }else{
                            //     Swal.close();
                            //     toastr.error("Cannot Delete Because The label Already Scan In FG")
                            // }
                        }
                    }
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_checksheet = $("#filter_checksheet").combogrid('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_checksheet=" + filter_checksheet + "&filter_wp=" + filter_wp + "&filter_period=" + filter_period +
            "&filter_workorder=" + filter_workorder + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('warehouse/wip_receipts/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            singleSelect: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });
        
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/wip_receipts/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_checksheet = $("#filter_checksheet").combogrid('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_checksheet=" + filter_checksheet + "&filter_wp=" + filter_wp + "&filter_period=" + filter_period +
            "&filter_workorder=" + filter_workorder + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('warehouse/wip_receipts/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();
        
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('warehouse/wip_receipts/number/' . $id_menu) ?>",
                        dataType: "html",
                        success: function(result) {
                            if(result == ""){
                                toastr.info("Please Check Configuration in Serial No First");
                                return false;
                            }else{
                                var customer_id = $("#customer_id").combobox('getValue');
                                var trans_date_from = $("#trans_date_from").datebox('getValue');
                                var trans_date_to = $("#trans_date_to").datebox('getValue');
                                var period = $("#period").combobox('getValue');
                                // var checksheet_number = $("#checksheet_number").textbox('getValue');
                                var checksheet_number = result;
                                var item_fg_id = $("#item_fg_id").combogrid('getValue');

                                $('#dg_request').datagrid('acceptChanges');
                                var rows = $('#dg_request').datagrid('getSelections');
                                var totalrows = rows.length;

                                if(totalrows > 0){
                                    if (customer_id != "" && trans_date_from != "" && trans_date_to != "" && item_fg_id != "") {
                                        $('#dlg_insert').dialog('close');

                                        requestData(totalrows, rows);
                                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                            Swal.fire({
                                                title: 'Please Wait. . .',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            var i = (number - 1);
                                            if (value < 100) {
                                                value = Math.floor((number / total) * 100);
                                                $.ajax({
                                                    type: "post",
                                                    url: '<?= base_url('warehouse/wip_receipts/create') ?>',
                                                    data: {
                                                        customer_id: customer_id,
                                                        trans_date_from: trans_date_from,
                                                        trans_date_to: trans_date_to,
                                                        checksheet_number: checksheet_number,
                                                        period: period,
                                                        item_fg_id: json[i].item_fg_id,
                                                        workorder: json[i].workorder,
                                                        wp: json[i].wp,
                                                        qty: json[i].qty,
                                                        lot_box: json[i].qty_box,
                                                        label_box: json[i].label_box,
                                                    },
                                                    dataType: "json",
                                                    success: function(result) {
                                                        if (result.theme == "success") {
                                                            if (value >= 100) {
                                                                create_label(checksheet_number, json[i].workorder, json[i].qty, json[i].qty_box, json[i].label_box, total, json, number, value, success, failed, 1);
                                                            }else{
                                                                create_label(checksheet_number, json[i].workorder, json[i].qty, json[i].qty_box, json[i].label_box, total, json, number, value, success, failed, 0);
                                                                // requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                            }
                                                        } else {
                                                            if (value >= 100) {
                                                                Swal.close();
                                                                toastr.error(result.message, result.title);
                                                                return false;
                                                            }else{
                                                                toastr.error(result.message, result.title);
                                                                return false;
                                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                            }
                                                        }
                                                    }
                                                });
                                            }
                                        }

                                        function create_label(checksheet_number, workorder, qty, lot_box, label_box, totalRows, jsonData, numberRows, valueRows, successRows, failedRows, finish){
                                            Swal.close();
                                            $("#dlg_label").dialog('open');

                                            requestDataBox(label_box, qty);
                                            function requestDataBox(total, qty, number = 1, value = 0, success = 1, failed = 1) {
                                                if (value < 100) {
                                                    value = Math.floor((number / total) * 100);
                                                    $('#p_upload').progressbar('setValue', value);
                                                    $('#p_start').html(number);
                                                    $('#p_finish').html(total);

                                                    if (parseInt(qty) > parseInt(lot_box)) {
                                                        var qty_final = lot_box;
                                                    } else {
                                                        var qty_final = qty;
                                                    }

                                                    $.ajax({
                                                        type: "POST",
                                                        async: true,
                                                        url: "<?= base_url('warehouse/wip_receipts/create_label_box') ?>",
                                                        data: {
                                                            "checksheet_number": checksheet_number,
                                                            "workorder": workorder,
                                                            "qty": qty_final,
                                                        },
                                                        cache: false,
                                                        dataType: "json",
                                                        success: function(result) {
                                                            if (result.theme == "success") {
                                                                $('#p_success').html(success);
                                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                                var qty_balance = (parseInt(qty) - parseInt(lot_box));
                                                                requestDataBox(total, qty_balance, number + 1, value, success + 1, failed + 0);
                                                            } else {
                                                                $('#p_failed').html(failed);
                                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                                var qty_balance = (parseInt(qty) - parseInt(lot_box));
                                                                requestDataBox(total, qty_balance, number + 1, value, success + 0, failed + 1);
                                                            }

                                                            $("#p_remarks").append(title + "<br>");

                                                            // if (value == 100) {
                                                            //     requestDataLabel(label, qty);
                                                            // }

                                                            if (value == 100) {
                                                                $("#dlg_label").dialog('close');
                                                                $('#dg').datagrid('reload');
                                                                toastr.success("Create Label Completed");

                                                                if(finish == 1){
                                                                    Swal.fire({
                                                                        title: "Create Data Successfully",
                                                                        icon: "success",
                                                                        confirmButtonText: 'Ok',
                                                                        allowOutsideClick: false,
                                                                    }).then((result) => {
                                                                        if (result.isConfirmed) {
                                                                            window.location.reload();
                                                                        }
                                                                    });
                                                                }else{
                                                                    requestData(totalRows, jsonData, numberRows + 1, valueRows, successRows + 1, failedRows + 0);
                                                                }
                                                                // filter_checksheet();
                                                            }
                                                        }
                                                    }).fail(function(jqXHR, textStatus) {
                                                        toastr.error("Connection Time Out, Please Wait");
                                                        requestDataBox(total, qty, number, value, success, failed);
                                                    });
                                                }
                                            }
                                        }

                                        $('#dg').datagrid('reload');
                                        $('#dlg_insert').dialog('close');
                                    } else {
                                        toastr.error("Please Completed your input");
                                    }
                                }else{
                                    toastr.warning("Please select one of the data in the table first!", "Information");
                                }
                            }
                        }
                    });
                }
            }]
        });

        filter_checksheet();

        $('#receipt').numberbox({
            onChange: function(value) {
                var qty = $("#qty").numberbox("getValue");
                var receipt = $("#receipt").numberbox('getValue');
                var result = parseInt(qty) - parseInt(receipt);
                var balance = $("#balance").numberbox('setValue', result);

                if (result < 0) {
                    toastr.warning("Receipt Qty not minus");
                    $("#receipt").numberbox('setValue', 0);
                } else {
                    return result;
                }
            }
        });
    });

    function create_label2(checksheet_number, workorder, qty, lot_box, label_box){
        $.messager.prompt('Repeat Label', 'Please input The Password', function(r){
            if (r == "ULANG"){

                $("#dlg_label").dialog('open');

                requestDataBox(label_box, qty);
                function requestDataBox(total, qty, number = 1, value = 0, success = 1, failed = 1) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_upload').progressbar('setValue', value);
                        $('#p_start').html(number);
                        $('#p_finish').html(total);

                        if (parseInt(qty) > parseInt(lot_box)) {
                            var qty_final = lot_box;
                        } else {
                            var qty_final = qty;
                        }

                        $.ajax({
                            type: "POST",
                            async: true,
                            url: "<?= base_url('warehouse/wip_receipts/create_label_box') ?>",
                            data: {
                                "checksheet_number": checksheet_number,
                                "workorder": workorder,
                                "qty": qty_final,
                            },
                            cache: false,
                            dataType: "json",
                            success: function(result) {
                                if (result.theme == "success") {
                                    $('#p_success').html(success);
                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                    var qty_balance = (parseInt(qty) - parseInt(lot_box));
                                    requestDataBox(total, qty_balance, number + 1, value, success + 1, failed + 0);
                                } else {
                                    $('#p_failed').html(failed);
                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                    var qty_balance = (parseInt(qty) - parseInt(lot_box));
                                    requestDataBox(total, qty_balance, number + 1, value, success + 0, failed + 1);
                                }

                                $("#p_remarks").append(title + "<br>");

                                // if (value == 100) {
                                //     requestDataLabel(label, qty);
                                // }

                                if (value == 100) {
                                    $("#dlg_label").dialog('close');
                                    $('#dg').datagrid('reload');
                                    toastr.success("Create Label Completed");

                                    Swal.fire({
                                        title: "Create Data Successfully",
                                        icon: "success",
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                    // filter_checksheet();
                                }
                            }
                        }).fail(function(jqXHR, textStatus) {
                            toastr.error("Connection Time Out, Please Wait");
                            requestDataBox(total, qty, number, value, success, failed);
                        });
                    }
                }
            }
        });
    }

    function filter_checksheet() {
        //Get Product
        $('#filter_checksheet').combogrid({
            url: '<?= base_url('warehouse/wip_receipts/readChecksheet') ?>',
            panelWidth: 650,
            idField: 'checksheet_number',
            textField: 'checksheet_number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Checksheet",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'checksheet_number',
                    title: 'Checksheet No',
                    width: 150
                }, {
                    field: 'number_customer',
                    title: 'Product No Customer',
                    width: 200,
                }, {
                    field: 'customer_name',
                    title: 'Customer',
                    width: 250,
                }]
            ],
        });

        $("#filter_workorder").combobox({
            url: '<?= base_url('warehouse/wip_receipts/readWo') ?>',
            valueField: 'workorder',
            textField: 'workorder',
            prompt: "Select Workorder No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_period").combobox({
            url: '<?= base_url('planning/production_schedules/readPeriod/period') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Select Periode",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(period){
                $("#filter_wp").combobox({
                    url: '<?= base_url('planning/production_schedules/readWp?period=') ?>' + btoa(period.period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads') ?>',
            panelWidth: 650,
            idField: 'id',
            textField: 'number_customer',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Product No EBWS',
                    width: 150
                }, {
                    field: 'number_customer',
                    title: 'Product No Customer',
                    width: 200,
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 250,
                }]
            ],
        });
    }

    function print_rfg(){
        var filter_checksheet = $("#filter_checksheet").combogrid('getValue');

        if(filter_checksheet == ""){
            toastr.info("Please Choose Checksheet No");
        }else{
            window.open("<?= base_url('warehouse/wip_receipts/print_checksheet/') ?>" + btoa(filter_checksheet), "_blank");
        }
    }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
    }

    function BtnPrintBox(val, row) {
        if (row.scan == 0) {
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label_box/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
        } else {
            var details = "printBoxR('"+window.btoa(row.checksheet_number)+"')";
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="'+details+'"><i class="fa fa-print"></i> Print</a>';
        }
    }

    function printBoxR(checksheet_number){
        $.messager.prompt('Print Label', 'Please input Password Repeat Print', function(r){
            if (r == "ULANG"){
                window.open('<?= base_url('warehouse/wip_receipts/print_label_box/') ?>' + checksheet_number);
            }
        });
    }

    function BtnRepeatBox(val, row) {
        if(row.total_label == 0){
            var details = "create_label2('"+row.checksheet_number+"','"+row.workorder+"','"+row.qty+"','"+row.lot_box+"','"+row.label_box+"')";
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="'+details+'">Repeat</a>';
        }else{
            return "-";
        }
    }

    function BtnPrintStrip(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label_strip/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
    }

    function cellStylerScan(value, row, index) {
        if (value == 0) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterScan(value, row) {
        if (value == 0) {
            return 'Not Scan';
        } else {
            return 'Scanned';
        }
    };

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

    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>