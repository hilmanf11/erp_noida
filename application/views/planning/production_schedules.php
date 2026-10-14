<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status_visual',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status<br>Visual</th>
            <th rowspan="2" data-options="field:'status_rfg',width:80,align:'center',formatter:statusformat2,styler:statusStyle2">Status<br>RFG</th>
            <th rowspan="2" data-options="field:'precut_status',width:80,align:'center',formatter:statusprecut,styler:styleprecut">Production<br>Order</th>
            <th rowspan="2" data-options="field:'period',width:80,align:'center'">Period</th>
            <th rowspan="2" data-options="field:'wp',width:80,align:'center'">WP</th>
            <th rowspan="2" data-options="field:'trans_date',width:120,align:'center'">WP Date</th>
            <th rowspan="2" data-options="field:'workorder',width:150,align:'center'">Work Order</th>
            <th colspan="3">Customer</th>
            <th rowspan="2" data-options="field:'item_number',halign:'center',width:150">Product No</th>
            <th rowspan="2" data-options="field:'number_customer',halign:'center',width:150">Product Customer</th>
            <th rowspan="2" data-options="field:'item_name',halign:'center',width:200">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <!-- <th rowspan="2" data-options="field:'rework',width:80,halign:'center',align:'right',formatter:numberformat">Rework</th> -->
            <th rowspan="2" data-options="field:'total_wirecollect',width:80,halign:'center',align:'right',formatter:numberformat">Wire Collect<br>Qty</th>
            <th rowspan="2" data-options="field:'total_visual',width:80,halign:'center',align:'right',formatter:numberformat">Visual Qty</th>
            <th rowspan="2" data-options="field:'total_rfg',width:80,halign:'center',align:'right',formatter:numberformat">RFG Qty</th>
            <!-- <th rowspan="2" data-options="field:'so_number',width:150,align:'center'">SO No</th> -->
            <!-- <th rowspan="2" data-options="field:'so_date',width:80,align:'center'">SO Date</th> -->
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Precut</th>
        </tr>
        <tr>
            <th data-options="field:'customer_number',width:80, align:'center'">ID</th>
            <th data-options="field:'customer_name',width:200, halign:'center'">Name</th>
            <th data-options="field:'customer_type',width:100, align:'center'">Type</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'precut_by',width:100,align:'center'"> By</th>
            <th data-options="field:'precut_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 40%; float: left;">
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Period</span>
                <input style="width:20%;" id="filter_month" class="easyui-combobox" value="<?= date("m") ?>" data-options="prompt:'Select Month'">
                <input style="width:15%;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Select Year'" panelHeight="auto">
                <input style="width:20%;" id="filter_month2" class="easyui-combobox" value="<?= date("m") ?>" data-options="prompt:'Select Month'">
                <input style="width:15%;" id="filter_year2" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Select Year'" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Visual date</span>
                <input style="width:35%;" name="filter_date_from" id="filter_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                <input style="width:35%;" name="filter_date_to" id="filter_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customers" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
            </div>
        </div>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Workorder/WP</span>
                <input style="width:40%;" id="filter_workorder" class="easyui-combobox">
                <input style="width:20%;" id="filter_wp" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status RFG</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="0">OPEN</option>
                    <option value="1">CLOSED</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="formula()"><i class="fa fa-info"></i> Help</a>
    <?php 
        if(in_array($this->session->username, ["admin", "hariom_bhardwaj", "ITADMIN"])){
    ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="precut()"><i class="fa fa-cut"></i> Production Order</a>
    <?php 
        }
    ?>
    <!-- <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="print_job_order()"><i class="fa fa-print"></i> Print Job Order</a> -->
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>

            <div class="alert alert-warning">New Data for Outstanding Supply</div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="month" name="month" required class="easyui-combobox">
                <input style="width:30%;" id="year" name="year" required class="easyui-combobox" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="customer_id" required="" id="customer_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="item_fg_id" required="" id="item_fg_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP</span>
                <input style="width:60%;" name="wp" id="wp" required="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Quantity</span>
                <input style="width:30%;" name="qty" id="qty" class="easyui-numberbox" data-options="precision:2">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<!-- <iframe id="printout" src="<?= base_url('planning/production_schedules/print') ?>" style="width: 100%;" hidden></iframe> -->
<script>
    function formula(){
        Swal.fire({
            width: 600,
            html: `<div style="text-align:left;"><center><b style="font-size:16px !important;">RELATION</b><hr></center>
                    <ul>
                        <li>The Data Customers is taken from <b>Master Data > Marketing > Customers</b></li>
                        <li>The Data Line Production is taken from <b>Master Data > General Master > Line Production</b></li>
                        <li>The Data Sales Order No is taken from the results of Customer selection and Get Data <b>Sales Order</b> Module</li>
                        <li>The Data Product No is taken from the results of Sales Order No selection</li>
                    </ul>
                    <center><hr><b style="font-size:16px !important;">CONDITION</b><hr></center>
                    <ul>
                        <li>If Status WO <b style="color: green">OPEN</b> then data new created in <b>Production Schedules</b></li>
                        <li>If Status WO <b style="color: orange">SUPPLY</b> then data created in <b>Supply Sheet</b> when qty balance = 0</li>
                        <li>If Status WO <b style="color: red">CLOSED</b> then data has been Scanned in <b>Scan Receipt FG</b></li>
                        <li>If Status RFG <b style="color: green">OPEN</b> then data new created in <b>Production Schedules</b></li>
                        <li>If Status RFG <b style="color: red">CLOSED</b> then data has been Scanned in <b>WIP Receipts</b></li>
                        <li>If Qty in Production Schedule > Qty in Sales Order then <b style="color: red">ERROR</b></li>
                    </ul>
                </div>`,
        });
    }

    //Add Data
    function add() {
        toastr.info("Create Data Production Schedule from MPP");
        // $('#dlg_insert').dialog('open');
        // url_save = '<?= base_url('planning/production_schedules/create') ?>';
        // $('#frm_insert').form('clear');
        // $("#wp").numberbox('enable');
        // $("#customer_id").combogrid('enable');
        // $("#item_fg_id").combogrid('enable');
        // $("#month").combobox('setValue', "<?= date("m") ?>");
        // $("#year").combobox('setValue', "<?= date("Y") ?>");
    }

    //Edit Data
    function update() {
        toastr.info("This Not Ready to this Function");
        // var row = $('#dg').datagrid('getSelected');
        // if (row) {
        //     $('#dlg_insert').dialog('open');
        //     $('#frm_insert').form('load', row);
        //     $("#wp").numberbox('disable');
        //     $("#customer_id").combogrid('disable');
        //     $("#item_fg_id").combogrid('disable');
        //     url_save = '<?= base_url('planning/production_schedules/update') ?>?id=' + btoa(row.id);
        // } else {
        //     toastr.warning("Please select one of the data in the table first!", "Information");
        // }
    }

    //Edit Status Precut
    function precut() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Precut Data',
                text: "Are you sure? You want to precut this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Do it!'
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

                    requestPrecut(rows.length, rows);
                    function requestPrecut(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            if(row.precut_status == "NO"){
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('planning/production_schedules/precut') ?>',
                                    data: {
                                        id: row.id,
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        requestPrecut(total, json, number + 1, value);

                                        if (number == total) {
                                            $('#dg').datagrid('reload');
                                            Swal.close();
                                            Swal.fire(
                                                'Precut Completed',
                                                'Precut Data has been completed, You cannot restore data that has been precut',
                                                'success'
                                            );
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        Swal.close();
                                    },
                                });
                            }else{
                                toastr.info("Precut has been Processed, Please select data precut = NO");
                                Swal.close();
                            }
                        }
                    }
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }
    //Delete Data
    function deleted() {
        // toastr.info("This Not Ready to this Function");
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
                                url: '<?= base_url('planning/production_schedules/delete') ?>',
                                data: {
                                    id: row.id,
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
                                    toastr.error("You cannot Delete, Because the data already relation in other Module");
                                    swal.close();
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
        var filter_date_from = $("#filter_date_from").datebox('getValue');
        var filter_date_to = $("#filter_date_to").datebox('getValue');
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_month2 = $("#filter_month2").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_year2 = $("#filter_year2").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_month=" + filter_month + "&filter_month2=" + filter_month2 + "&filter_year=" + filter_year + "&filter_year2=" + filter_year2 + "&filter_wp=" + filter_wp + "&filter_workorder=" + filter_workorder +
            "&filter_date_from=" + filter_date_from + "&filter_date_to=" + filter_date_to + 
            "&filter_customers=" + filter_customers + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('planning/production_schedules/datatables') ?>' + url,
            fit: true,
            pagination: true,
            rownumbers: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });

        // $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        // $("#printout").attr('src', '<?= base_url('planning/production_schedules/print') ?>' + url);
    }

    function pdf() {
        toastr.info("Print Not Allowed, Please Export Excel");
        // $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_date_from = $("#filter_date_from").datebox('getValue');
        var filter_date_to = $("#filter_date_to").datebox('getValue');
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_month2 = $("#filter_month2").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_year2 = $("#filter_year2").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_month=" + filter_month + "&filter_month2=" + filter_month2 + "&filter_year=" + filter_year + "&filter_year2=" + filter_year2 + "&filter_wp=" + filter_wp + "&filter_workorder=" + filter_workorder +
            "&filter_date_from=" + filter_date_from + "&filter_date_to=" + filter_date_to + 
            "&filter_customers=" + filter_customers + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status;

        $.messager.alert('Export to Excel','Please Wait...');
        window.location.assign('<?= base_url('planning/production_schedules/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        filter();

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
        
        $("#filter_month").combobox({
            url: '<?= base_url('planning/production_schedules/readMonth') ?>',
            valueField: 'number',
            textField: 'name',
            onSelect: function(month){
                var year = $("#filter_year").combobox('getValue');

                $("#filter_wp").combobox({
                    url: '<?= base_url('planning/production_schedules/filterWp/') ?>' + month.number + "/" + year,
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Choose WP"
                });

                $("#filter_workorder").combobox({
                    url: '<?= base_url('planning/production_schedules/filterWo/') ?>' + month.number + "/" + year,
                    valueField: 'workorder',
                    textField: 'workorder',
                    prompt: "Choose Workorder"
                });
            }
        });

        $("#filter_month2").combobox({
            url: '<?= base_url('planning/production_schedules/readMonth') ?>',
            valueField: 'number',
            textField: 'name'
        });

        $("#filter_year").combobox({
            url: '<?= base_url('planning/production_schedules/readYear') ?>',
            valueField: 'number',
            textField: 'number',
        });

        $("#filter_year2").combobox({
            url: '<?= base_url('planning/production_schedules/readYear') ?>',
            valueField: 'number',
            textField: 'number',
        });

        $("#filter_wp").combobox({
            url: '<?= base_url('planning/production_schedules/filterWp/'.date('m').'/'.date('Y')) ?>',
            valueField: 'wp',
            textField: 'wp',
            prompt: "Choose WP"
        });

        $("#filter_workorder").combobox({
            uurl: '<?= base_url('planning/production_schedules/filterWo/'.date('m').'/'.date('Y')) ?>',
            valueField: 'workorder',
            textField: 'workorder',
            prompt: "Choose Workorder"
        });

        $("#filter_month").combobox("setValue", "<?= date("m") ?>");

        $('#filter_customers').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });
        
        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads/001') ?>',
            panelWidth: 520,
            idField: 'id',
            textField: 'number',
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
                    title: 'Product No',
                    width: 150
                },{
                    field: 'number_customer',
                    title: 'Product Customer',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 150
                }]
            ],
        });
        
        $("#month").combobox({
            url: '<?= base_url('planning/production_schedules/readMonth') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Month",
            onSelect: function(){
                $("#customer_id").combogrid('clear');
                $("#item_fg_id").combogrid('clear');
            }
        });

        $("#year").combobox({
            url: '<?= base_url('planning/production_schedules/readYear') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Year",
            onSelect: function(){
                $("#customer_id").combogrid('clear');
                $("#item_fg_id").combogrid('clear');
            }
        });

        //Get Product
        $('#customer_id').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function(val, rowCust) {
                $('#item_fg_id').combogrid({
                    url: '<?= base_url('master/customer_items/reads/') ?>' + btoa(rowCust.id),
                    panelWidth: 500,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Item",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No',
                            width: 100
                        },{
                            field: 'number_customer',
                            title: 'Product Customer',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 150
                        }]
                    ],
                    onSelect: function(itemVal, itemRow) {
                        var month = $("#month").combobox('getValue');
                        var year = $("#year").combobox('getValue');

                        // $("#wp").combobox({
                        //     url: '<?= base_url('planning/production_schedules/getDataWp?p_month=') ?>' + month + "&p_year=" + year + "&customer_id=" + btoa(rowCust.id) + "&item_fg_id=" + btoa(itemRow.id),
                        //     valueField: 'wp',
                        //     textField: 'wp',
                        //     prompt: "Choose WP",
                        //     onSelect: function(wp){
                        //         $("#trans_date").datebox('setValue', wp.trans_date);
                        //         $("#qty").numberbox('setValue', wp.qty);
                        //     }
                        //  });
                    }
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

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if(value == 1){
            return "<b style='color:orange;'>SUPPLY</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if(value == 1){
            return 'background-color:#FFDFBD;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function statusformat2(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle2(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function statusprecut(value, row) {
        if (value == "YES") {
            return "<b style='color:green;'>YES</b>";
        } else {
            return "<b style='color:red;'>NO</b>";
        }
    }

    function styleprecut(value, row, index) {
        if (value == "YES") {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>