<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'history',width:80,halign:'center',formatter: btnHistories">History</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:250,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'category_name',width:150,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'product_family_name',width:150,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'mpq',width:80,align:'center'">MPQ</th>
            <th rowspan="2" data-options="field:'reorder_point',width:80,align:'center'">Reorder<br>Point</th>
            <th rowspan="2" data-options="field:'lead_time_days',width:80,align:'center'">Leadtime</th>
            <th rowspan="2" data-options="field:'is_dol',width:80,align:'center'">DOL/NON DOL</th>
            <th rowspan="2" data-options="field:'is_active',width:80,align:'center'">Status</th>
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

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="sendWms()"><i class="fa fa-database"></i> Send to WMS</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date"  id="trans_date" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No EBWS</span>
                <input style="width:60%;" name="item_rm_id"  id="item_rm_id" required class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" name="supplier_id"  id="supplier_id" required class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">MPQ</span>
                <input style="width:30%;" name="mpq" id="mpq" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Reorder Point</span>
                <input style="width:30%;" name="reorder_point"  id="reorder_point" required class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Leadtime</span>
                <input style="width:30%;" name="lead_time_days"  id="lead_time_days" required class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">DOL/NON DOL</span>
                <select style="width:30%;" name="is_dol" id="is_dol" required class="easyui-combobox" panelHeight="auto">
                    <option value="0">DOL</option>
                    <option value="1">NON DOL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:30%;" name="is_active" id="is_active" required class="easyui-combobox" panelHeight="auto">
                    <option value="0">ACTIVE</option>
                    <option value="1">INACTIVE</option>
                </select>
            </div>
        </fieldset>
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

<!-- Detail Histories -->
<div id="dlg_history" class="easyui-dialog" title="Price Histories" data-options="closed: true,modal:true" style="width: 1100px; height: 300px; top: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
                <th data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
                <th data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
                <th data-options="field:'trans_date',width:100,halign:'center'">Trans Date</th>
                <th data-options="field:'mpq',width:80,align:'center'">MPQ</th>
                <th data-options="field:'reorder_point',width:80,align:'center'">Reorder<br>Point</th>
                <th data-options="field:'lead_time_days',width:80,align:'center'">Leadtime</th>
                <th data-options="field:'is_dol',width:80,align:'center'">DOL/NON DOL</th>
                <th data-options="field:'is_active',width:80,align:'center'">Status</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/dol_items/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('master/dol_items/create') ?>';
        $('#frm_insert').form('clear');
        $("#item_rm_id").combogrid({readonly:false});
        $("#supplier_id").combogrid({readonly:false});
        $("#trans_date").datebox('setValue', '<?= date("Y-m-d") ?>');
        metode = "ADD";
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $("#item_rm_id").combogrid({readonly:true});
            $('#frm_insert').form('load', row);

            $('#supplier_id').combogrid({
                url: '<?= base_url('master/dol_items/readSupplier?item_rm_id='); ?>' + row.item_rm_id,
                panelWidth: 420,
                idField: 'id',
                textField: 'number',
                mode: 'remote',
                fitColumns: true,
                prompt: "Choose Supplier",
                columns: [
                    [{
                        field: 'id',
                        title: 'Supplier ID',
                        width: 150
                    }, {
                        field: 'name',
                        title: 'Supplier Name',
                        width: 250
                    }]
                ],
                onSelect: function(val2, row2){
                    $("#mpq").numberbox('setValue', row2.mpq);
                },
                onLoadSuccess: function(){
                    $('#supplier_id').combogrid('setValue', row.supplier_id);
                },
            });

            // $("#supplier_id").combogrid({readonly:true});

            url_save = '<?= base_url('master/dol_items/update') ?>?id=' + btoa(row.id);
            metode = "EDIT";
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('master/dol_items/delete') ?>',
                            data: {
                                id: row.id,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_dol_items.xls') ?>');
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/dol_items/print/excel') ?>');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    //Send WMS Data
    function sendWms() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Send Data',
                text: "Are you sure? You want to send this data to WMS!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Send it!'
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

                    requestSend(rows.length, rows);
                    function requestSend(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            if(row.approved_to == ""){
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url("api/wms/dolMaster") ?>',
                                    data: {
                                        item_rm_id: row.item_rm_id,
                                        supplier_id: row.supplier_id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        requestSend(total, json, number + 1, value);

                                        if (number == total) {
                                            Swal.close();
                                            Swal.fire("", result.message, "success");
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error("Failed to Sending Data WMS");
                                        swal.close();
                                    },
                                });
                            }else{
                                toastr.error("This Part No " + row.item_rm_id + " on processing approval");
                                requestSend(total, json, number + 1, value);

                                if (number == total) {
                                    Swal.close();
                                }
                            }
                        }
                    }
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/dol_items/datatables') ?>',
            pagination: true,
            rownumbers: true,
            striped: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        //SAVE DATA
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

                                // if(metode == "ADD"){
                                    $.messager.confirm('Confirm','Are you sure you want to send data to WMS?', function(r){
                                        if (r){
                                            Swal.fire({
                                                title: 'Please Wait for Sending Data',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            var burl = "<?= base_url("api/wms/dolMaster") ?>";

                                            $.ajax({
                                                type: "post",
                                                url: burl,
                                                data: {
                                                    item_rm_id: result.item_rm_id,
                                                    supplier_id: result.supplier_id
                                                },
                                                dataType: "json",
                                                success: function (msg) {
                                                    if(msg.success){
                                                        Swal.fire("", msg.message, "success");
                                                    }else{
                                                        Swal.fire("", msg.message, "error");
                                                    }
                                                }, error: function(){
                                                    Swal.fire("", "Failed to Sending Data WMS", "error");
                                                }
                                            });
                                        }
                                    });
                                // }
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

         // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/dol_items/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/dol_items/upload') ?>',
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
                                url: "<?= base_url('master/dol_items/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/dol_items/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                var burl = "<?= base_url("api/wms/dolMaster") ?>";
                                                $.ajax({
                                                    type: "post",
                                                    url: burl,
                                                    data: {
                                                        item_rm_id: json[number - 1].item_rm_id,
                                                        supplier_id: json[number - 1].supplier_id
                                                    },
                                                    dataType: "json",
                                                    success: function (msg) {
                                                        if(msg.success){
                                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                        }else{
                                                            $("#p_remarks").append(msg.message + "<br>");
                                                        }
                                                    }, error: function(){
                                                        $("#p_remarks").append("Failed to Sending Data WMS <br>");
                                                    }
                                                });
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('master/dol_items/uploadcreateFailed') ?>",
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

        $('#item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads/'); ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part No",
            columns: [
                [{
                    field: 'id',
                    title: 'Part ID',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Part No',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Part Name',
                    width: 100
                }]
            ],
            onSelect: function(val, row){
                $('#supplier_id').combogrid({
                    url: '<?= base_url('master/dol_items/readSupplier?item_rm_id='); ?>' + row.id,
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Supplier",
                    columns: [
                        [{
                            field: 'id',
                            title: 'Supplier ID',
                            width: 150
                        }, {
                            field: 'name',
                            title: 'Supplier Name',
                            width: 250
                        }]
                    ],
                    onSelect: function(val2, row2){
                        $("#mpq").numberbox('setValue', row2.mpq);
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

    function btnHistories(val, row) {
        var history = "viewHistories('" + row.supplier_id + "','" + row.item_rm_id + "')";
        return '<a class="btn btn-primary w-100" onClick="' + history + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewHistories(supplier_id, item_rm_id) {
        $("#dlg_history").dialog('open');
        $('#dg_history').datagrid({
            url: '<?= base_url('master/dol_items/datatableHistories?supplier_id=') ?>' + btoa(supplier_id) + "&item_rm_id=" + btoa(item_rm_id),
            pagination: false,
            rownumbers: true,
        });
    }
</script>