<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_family_name',width:150,halign:'center'">Asset Category</th>
            <th rowspan="2" data-options="field:'number',width:120,halign:'center'">Asset Code</th>
            <th rowspan="2" data-options="field:'name',width:250,halign:'center'">Asset Name</th>
            <th rowspan="2" data-options="field:'request_no',width:150,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:60,halign:'center', align:'right'">Qty</th>
            <!-- <th rowspan="2" data-options="field:'status',width:80, align:'center', styler:cellStyler, formatter:cellFormatter">Status</th> -->
            <th rowspan="2" data-options="field:'description',width:250,halign:'center'">Remarks</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar">
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; height: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Category</span>
                    <input style="width:60%;" name="item_family_id" id="item_family_id" class="easyui-combogrid" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Code</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Name</span>
                    <input style="width:60%;" name="name" id="name" class="easyui-textbox" required>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty</span>
                    <input style="width:30%;" name="qty" id="qty" class="easyui-numberbox" required>
                    <input style="width:30%;" name="uom" id="uom" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="description" id="description" class="easyui-textbox">
                </div>
            </div>
        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%; height: 240px;" title="Purchase Request Lists">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
                    <th data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
                    <th data-options="field:'item_rm_name',width:150,halign:'center'">Part Name</th>
                    <th data-options="field:'item_family_name',width:120,halign:'center'">Product Family</th>
                    <th data-options="field:'uom',width:80,align:'center'">Uom</th>
                    <th data-options="field:'qty',width:80,halign:'center', align:'right'">Qty</th>
                    <th data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
                    <th data-options="field:'created_by',width:120,halign:'center'">Created By</th>
                    <th data-options="field:'created_date',width:150,halign:'center'">Created Date</th>
                </tr>
            </thead>
        </table>
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

<!-- PDF -->
<iframe id="printout" src="<?= base_url('assets/assets/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('assets/assets/create') ?>';
        $('#frm_insert').form('clear');
        $('#dg2').datagrid('loadData', []);
        $("#item_family_id").combogrid('enable');
        $("#number").textbox('enable');
        $("#name").textbox('enable');
        // $("#request_no").combobox('enable');

        $("#request_no").combobox({
            url: '<?= base_url('assets/assets/readPr') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Choose PR No",
            onSelect: function(request){
                $('#dg2').datagrid({
                    url: '<?= base_url('assets/assets/readPrDetails?request_no=') ?>' + request.request_no,
                    pagination: false,
                    clientPaging: false,
                    // remoteFilter: true,
                    rownumbers: true,
                });

                if(request.type_pr == "SERVICE"){ 
                    $("#number").textbox('readonly', false); 
                } else { 
                    $("#number").textbox('readonly', true); 
                }
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        
        if (row) {
            if(row.datatable == 1){
                toastr.error("Please Select Detail in the table first!");
            }else{
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#item_family_id").combogrid('disable');
                $("#number").textbox('disable');
                $("#name").textbox('disable');
                // $("#request_no").combobox('disable');

                url_save = '<?= base_url('assets/assets/update') ?>?id=' + btoa(row.id);

                $('#dg2').datagrid({
                    url: '<?= base_url('assets/assets/readPrDetailUpdate?request_no=') ?>' + row.request_no,
                    pagination: false,
                    clientPaging: false,
                    // remoteFilter: true,
                    rownumbers: true,
                });
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function number(item_family_number){
        $.ajax({
            type: "post",
            url: "<?= base_url("assets/assets/autoid") ?>",
            data: {item_family_number: item_family_number},
            dataType: "json",
            success: function (response) {
                $("#number").textbox('setValue', response.code);
            }
        });
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of Asset Code <br>" + row.id);
                        // } else if (row.status == "1") {
                        //     toastr.error("Status False Cannot Delete this data");
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('assets/assets/delete') ?>',
                                data: {
                                    id: row.id,
                                    number: row.number,
                                    request_no: row.request_no
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                    $.messager.alert("Error", jqXHR.statusText, 'error');
                                },
                                complete: function(data) {
                                    $('#dg').datagrid('reload');
                                }
                            });
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_assets.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('assets/assets/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if(row.datatable != 1){
            if (value == 0) {
                return 'background: #53D636; color:white;';
            } else {
                return 'background: #FF5F5F; color:white;';
            }
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value, row) {
        if(row.datatable != 1){
            if (value == 0) {
                return 'TRUE';
            } else {
                return 'FALSE';
            }
        }
    };

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('assets/assets/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var item_family_id = $("#item_family_id").combogrid('getValue');
                    var number = $("#number").textbox('getValue');
                    var name = $("#name").textbox('getValue');
                    var qty = $("#qty").numberbox('getValue');
                    var uom = $("#uom").combobox('getValue');
                    var request_no = $("#request_no").combobox('getValue');
                    var description = $("#description").textbox('getValue');

                    var rows = $('#dg2').datagrid('getSelections');
                    var totalrows = rows.length;

                    if(number != "" && name != "" && qty != "" && uom != ""){
                        if (rows.length > 0) {
                            $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                                if (r) {
                                    Swal.fire({
                                        title: 'Please Wait for Saving Data',
                                        showConfirmButton: false,
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        },
                                    });

                                    if (totalrows > 0) {
                                        requestData(totalrows, rows);
                                        $('#dlg_insert').dialog('close');

                                        function requestData(total, json, jml = 1, value = 0) {
                                            if (value < 100) {
                                                value = Math.floor((jml / total) * 100);
                                                var i = (jml - 1);

                                                $.ajax({
                                                    type: "post",
                                                    url: url_save,
                                                    data: {
                                                        item_family_id: item_family_id,
                                                        number: number,
                                                        name: name,
                                                        qty: qty,
                                                        uom: uom,
                                                        request_no: request_no,
                                                        description: description,
                                                        item_rm_id: json[i].item_rm_id
                                                    },
                                                    dataType: "json",
                                                    success: function(result) {
                                                        requestData(total, json, jml + 1, value);

                                                        if (i == (totalrows - 1)) {
                                                            Swal.close();

                                                            Swal.fire({
                                                                title: result.message,
                                                                icon: result.theme,
                                                                confirmButtonText: 'Ok',
                                                                allowOutsideClick: false,
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    // window.location.reload();
                                                                    $("#dg").datagrid('reload');
                                                                }
                                                            });
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }
                                }
                            });
                        } else {
                            if(request_no == "Additional" || request_no.includes("PRS")){
                                $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                                    if (r) {
                                        $('#dlg_insert').dialog('close');
                                        
                                        Swal.fire({
                                            title: 'Please Wait for Saving Data',
                                            showConfirmButton: false,
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            },
                                        });

                                        $.ajax({
                                            type: "post",
                                            url: url_save,
                                            data: {
                                                item_family_id: item_family_id,
                                                number: number,
                                                name: name,
                                                qty: qty,
                                                uom: uom,
                                                request_no: request_no,
                                                description: description
                                            },
                                            dataType: "json",
                                            success: function(result) {
                                                Swal.close();

                                                Swal.fire({
                                                    title: result.message,
                                                    icon: result.theme,
                                                    confirmButtonText: 'Ok',
                                                    allowOutsideClick: false,
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        // window.location.reload();
                                                        $("#dg").datagrid('reload');
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }else{
                                toastr.info("Request No must be Additional");
                            }
                        }
                    }else{
                        toastr.info("Please Completed Your Data Asset");
                    }
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('assets/assets/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('assets/assets/upload') ?>',
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
                                url: "<?= base_url('assets/assets/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('assets/assets/uploadCreate') ?>",
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
                                                    url: "<?= base_url('assets/assets/uploadcreateFailed') ?>",
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

        $("#uom").combobox({
            url: '<?= base_url('master/uom/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Choose Uom",
        });

        $('#item_family_id').combogrid({
            url: '<?= base_url('master/item_familys/reads/C06') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Asset Category",
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 250
                }, ]
            ],
            onSelect: function(val, row){
                if(row.number == "CW"){ 
                    $("#number").textbox('readonly', false); 
                } else { 
                    $("#number").textbox('readonly', true); 
                }

                number(row.number);
            }
        });
    });
</script>