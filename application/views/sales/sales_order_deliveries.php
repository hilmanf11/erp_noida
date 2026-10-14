<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar" data-options="fit: true">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'item_fg_number',width:100,halign:'center'">Product No<br>EBWS</th>
            <th data-options="field:'number_customer',width:180,halign:'center'">Product No<br>Customer</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'item_fg_name',width:150,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'plant',width:80,halign:'center'">Plant</th>
            <th rowspan="2"data-options="field:'total_qty',width:80,halign:'center',align:'right', formatter:numberformat">Total Qty</th>
            <?php 
                if($this->input->get('filter_month')){
                    $filter_month = base64_decode($this->input->get('filter_month'));
                    $filter_year = base64_decode($this->input->get('filter_year'));
                    $filter_customer = base64_decode($this->input->get('filter_customer'));
                    $filter_plant = base64_decode($this->input->get('filter_plant'));
                    $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));
                }else{
                    $filter_month = date("m");
                    $filter_year = date("Y");
                    $filter_customer = "";
                    $filter_plant = "";
                    $filter_item_fg = "";
                }

                $firstDate = date("Y-m-01", strtotime(date("$filter_year-$filter_month-01")));
                $endDate = date("Y-m-t", strtotime(date("$filter_year-$filter_month")));
                $tgl = 1;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $working_date = date('d M', strtotime($firstDate));

                    if (date('w', strtotime($firstDate)) !== '0') {
                        $hstyle = "";
                    }else{
                        $hstyle = "background-color:#FFB0B0";
                    }
            ?>
            <th rowspan="2" data-options="field:'qty_<?= $tgl ?>',width:80,halign:'center', align:'right',hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }"><?= $working_date ?></th>
            <?php
                    $tgl++;
                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                }
            ?>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Created</th>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,halign:'center'">By</th>
            <th data-options="field:'created_date',width:150,halign:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,halign:'center'">By</th>
            <th data-options="field:'updated_date',width:150,halign:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 160px; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= $filter_month ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= $filter_year ?>" class="easyui-combobox" data-options="prompt:'Year'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer" value="<?= $filter_customer ?>" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 20%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" id="filter_plant" value="<?= $filter_plant ?>" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 20%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" value="<?= $filter_item_fg ?>" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 550px; padding:10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 25%;">
                <div class="fitem" hidden>
                    <span style="width:30%; display:inline-block;">ID</span>
                    <input style="width:60%;" name="id" id="id" required="" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="p_month" id="p_month" required class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="p_year" id="p_year" required class="easyui-combobox" data-options="prompt:'Year'">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="saveData()"><i class="fa fa-save"></i> Save Data</a>
                </div>
            </div>
            <div style="float: left; width: 25%;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required class="easyui-combobox">
                </div>
            </div>
            <div style="float: left; width: 20%;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="plant" id="plant" required class="easyui-combobox">
                </div>
            </div>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" required class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <div id="calendar">

        </div>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<iframe id="printout" src="" style="width: 100%; height:90%; border: 0;" hidden></iframe>

<script>
    function calendars(m, y, item_fg_id = "", customer_id = "", plant = ""){
        if(m == "" || y == ""){
            toastr.info("Please Choose Month and Year");
        }else{
            $.ajax({
                type: "post",
                url: "<?= base_url('sales/sales_order_deliveries/calendars') ?>",
                data: "month=" + m + "&year=" + y + "&customer_id=" + customer_id + "&item_fg_id=" + item_fg_id + "&plant=" + plant,
                dataType: "html",
                success: function(response) {
                    $("#calendar").html(response);
                }
            });
        }
    }
    
     //Add Data
    function add() {
        $("#dlg_insert").dialog('open');
        url_save = '<?= base_url('sales/sales_order_deliveries/create') ?>';
        $("#frm_insert").form('clear');
        $("#p_month").combobox('readonly', false);
        $("#p_year").combobox('readonly', false);
        $("#customer_id").combobox('readonly', false);
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#p_month").combobox('readonly', true);
            $("#p_year").combobox('readonly', true);
            $("#customer_id").combobox('readonly', true);
            $("#item_fg_id").combogrid('setValue', row.item_fg_id);

            calendars(row.p_month, row.p_year, row.item_fg_id, row.customer_id, row.plant);
            url_save = '<?= base_url('sales/sales_order_deliveries/update') ?>';
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function saveData() {
        var p_month = $("#p_month").combobox('getValue');
        var p_year = $("#p_year").combobox('getValue');
        var customer_id = $("#customer_id").combobox('getValue');
        var plant = $("#plant").combobox('getValue');
        var item_fg_id = $("#item_fg_id").combogrid('getValue');

        if(customer_id == "" || plant == "" || item_fg_id == "" || p_month == "" || p_year == ""){
            toastr.error("Please Completed your data");
        }else{
            $('#frm_insert').form('submit', {
                url: url_save,
                method: 'POST',
                onSubmit: function() {
                    if($(this).form('validate') === true){
                        $('#dlg_insert').dialog('close');
                        
                        Swal.fire({
                            title: 'Please Wait...',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });
                    }else{
                        return $(this).form('validate');
                    }
                },
                success: function(result) {
                    var result = eval('(' + result + ')');
                    if (result.theme == "success") {
                        toastr.success(result.message, result.title);
                    } else {
                        toastr.error(result.message, result.title);
                    }

                    $('#dg').datagrid('reload');
                    Swal.close();
                }
            });
        }
    }

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

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('sales/sales_order_deliveries/delete') ?>',
                                data: {
                                    item_fg_id: row.item_fg_id,
                                    p_month: row.p_month,
                                    p_year: row.p_year,
                                    customer_id: row.customer_id,
                                    plant: row.plant
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
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
                        }
                    }
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month == "" || filter_year == "") {
                toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign(url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month == "" || filter_year == "") {
                toastr.warning("Please select Period!", "Information");
        } else {
            $.messager.alert('Info','Please Wait to Export to Excel');
            window.location.assign('<?= base_url('sales/sales_order_deliveries/print/excel') ?>' + url);
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_sales_order_deliveries.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month != "" && filter_year != "") {
            $('#dg').datagrid({
                url: '<?= base_url('sales/sales_order_deliveries/datatables') ?>' + url,
                pagination: true,
                rownumbers: true,
                singleSelect: true,
                fit: true,
                pageList: [20, 50, 100, 500, 1000],
                pageSize: 20,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('sales/sales_order_deliveries/print') ?>' + url);
        }

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('sales/sales_order_deliveries/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('sales/sales_order_deliveries/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            $.messager.progress('close');
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('sales/sales_order_deliveries/uploadclearFailed') ?>"
                            });
                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('sales/sales_order_deliveries/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('sales/sales_order_deliveries/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }
                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

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
        });

        $('#filter_customer').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $('#filter_plant').combobox({
                    url: '<?= base_url('master/customers/readAddress/') ?>' + btoa(row.id),
                    valueField: 'plant',
                    textField: 'plant',
                    prompt: 'Select Plant',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_item_fg').combogrid({
                    url: '<?= base_url('master/customer_items/reads/') ?>' + btoa(row.id),
                    panelWidth: 550,
                    idField: 'id',
                    textField: 'number_customer',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product No",
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

        $('#p_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            onSelect: function(m){
                var y = $("#p_year").combobox('getValue');
                calendars(m.id, y);
            }
        });

        $('#p_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            onSelect: function(y){
                var m = $("#p_month").combobox('getValue');
                calendars(m, y.id);
            }
        });

        $('#customer_id').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Customer',
            onSelect: function(row){
                $('#plant').combobox({
                    url: '<?php echo base_url('master/customers/readAddress/'); ?>' + btoa(row.id),
                    valueField: 'plant',
                    textField: 'plant',
                    prompt: 'Select plant'
                });

                $('#item_fg_id').combogrid({
                    url: '<?= base_url('master/customer_items/reads/') ?>' + btoa(row.id),
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
    });

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function cellStyler(value, row, index) {
        if (value >= 0) {
            return 'font-weight:bold;';
        } else {
            return 'color:#FF5F5F; font-weight:bold;';
        }
    }

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
</script>