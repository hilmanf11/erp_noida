<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_fg_number',width:150,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'number_customer',width:200,halign:'center'">Part No Customer</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'circuit_no',width:100,align:'center'">CCT</th>
            <th rowspan="2" data-options="field:'kind',width:80,halign:'center'">Kind</th>
            <th rowspan="2" data-options="field:'size',width:80,halign:'center'">Size</th>
            <th rowspan="2" data-options="field:'color',width:80,halign:'center'">Base Color</th>
            <th rowspan="2" data-options="field:'line_color',width:80,halign:'center'">Line 1 Color</th>
            <th rowspan="2" data-options="field:'cl',width:80,halign:'center'">C/L</th>
            <th rowspan="2" data-options="field:'jno',width:80,halign:'center'">J. No</th>
            <th rowspan="2" data-options="field:'twist',width:80,halign:'center'">Twist</th>
            <th rowspan="2" data-options="field:'mno',width:80,halign:'center'">M. No</th>
            <th rowspan="2" data-options="field:'dc_1',width:80,halign:'center'">D/C 1</th>
            <th rowspan="2" data-options="field:'terminal_1',width:100,halign:'center'">Terminal 1 (R)</th>
            <th rowspan="2" data-options="field:'strip_1',width:100,halign:'center'">Strip 1</th>
            <th rowspan="2" data-options="field:'chc_1',width:100,halign:'center'">CH/C 1</th>
            <th rowspan="2" data-options="field:'cwc_1',width:100,halign:'center'">CW/C 1</th>
            <th rowspan="2" data-options="field:'chi_1',width:100,halign:'center'">CH/I 1</th>
            <th rowspan="2" data-options="field:'cwi_1',width:100,halign:'center'">CW/I 1</th>
            <th rowspan="2" data-options="field:'dc_2',width:100,halign:'center'">D/C 2</th>
            <th rowspan="2" data-options="field:'terminal_2',width:100,halign:'center'">Terminal 2 (F)</th>
            <th rowspan="2" data-options="field:'strip_2',width:100,halign:'center'">Strip 2</th>
            <th rowspan="2" data-options="field:'chc_2',width:100,halign:'center'">CH/C 2</th>
            <th rowspan="2" data-options="field:'cwc_2',width:100,halign:'center'">CW/C 2</th>
            <th rowspan="2" data-options="field:'chi_2',width:100,halign:'center'">CH/I 2</th>
            <th rowspan="2" data-options="field:'cwi_2',width:100,halign:'center'">CW/I 2</th>
            <th rowspan="2" data-options="field:'accessories_1',width:100,halign:'center'">Accessories 1</th>
            <th rowspan="2" data-options="field:'accessories_2',width:100,halign:'center'">Accessories 2</th>
            <th rowspan="2" data-options="field:'mark_r',width:100,halign:'center'">Mark R</th>
            <th rowspan="2" data-options="field:'mark_f',width:100,halign:'center'">Mark F</th>
            <th rowspan="2" data-options="field:'tube_1',width:100,halign:'center'">Tube 1</th>
            <th rowspan="2" data-options="field:'tube_size_1',width:100,halign:'center'">Tube Size 1</th>
            <th rowspan="2" data-options="field:'tube_2',width:100,halign:'center'">Tube 2</th>
            <th rowspan="2" data-options="field:'tube_size_2',width:100,halign:'center'">Tube Size 2</th>
            <th rowspan="2" data-options="field:'end_1',width:100,halign:'center'">End 1</th>
            <th rowspan="2" data-options="field:'end_2',width:100,halign:'center'">End 2</th>
            <th rowspan="2" data-options="field:'note_r',width:100,halign:'center'">Note R</th>
            <th rowspan="2" data-options="field:'note_f',width:100,halign:'center'">Note F</th>
            <th rowspan="2" data-options="field:'cct_order',width:80,halign:'center'">CCT<br>Order</th>
            <th rowspan="2" data-options="field:'cct_range',width:80,halign:'center'">CCT<br>Range</th>
            <th rowspan="2" data-options="field:'mc',width:100,halign:'center'">MC No</th>
            <th rowspan="2" data-options="field:'order',width:100,halign:'center'">Process<br>Sequence</th>
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
<div id="toolbar" style="height: 35px;">
    <a href="javascript:;" class="easyui-linkbutton" id="sync" data-options="plain:true" onclick="sync()"><i class="fa fa-history"></i> Push Data</a>
    <?= $button ?>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 700px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" style="margin-bottom: 20px;">
                <span style="width:17.5%; display:inline-block;">Product No</span>
                <input style="width:80%;" name="item_fg_id" id="item_fg_id" required="true" class="easyui-combogrid">
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Circuit No</span>
                    <input style="width:60%;" name="circuit_no" id="circuit_no" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Kind</span>
                    <input style="width:60%;" name="kind" id="kind" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Size</span>
                    <input style="width:60%;" name="size" id="size" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Base Color</span>
                    <input style="width:60%;" name="color" id="color" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Line 1 Color</span>
                    <input style="width:60%;" name="line_color" id="line_color" class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">C/L</span>
                    <input style="width:60%;" name="cl" id="cl" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">J. No</span>
                    <input style="width:60%;" name="jno" id="jno" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Twist</span>
                    <input style="width:60%;" name="twist" id="twist" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">M. No</span>
                    <input style="width:60%;" name="mno" id="mno" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">CCT Range</span>
                    <input style="width:60%;" name="cct_range" id="cct_range" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <fieldset style="width:49%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Setting 1</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">D/C</span>
                <input style="width:60%;" name="dc_1" id="dc_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Terminal R</span>
                <input style="width:60%;" name="terminal_1" id="terminal_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Strip</span>
                <input style="width:60%;" name="strip_1" id="strip_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CH/C</span>
                <input style="width:60%;" name="chc_1" id="chc_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CW/C</span>
                <input style="width:60%;" name="cwc_1" id="cwc_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CH/I</span>
                <input style="width:60%;" name="chi_1" id="chi_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CW/I</span>
                <input style="width:60%;" name="cwi_1" id="cwi_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Accessories</span>
                <input style="width:60%;" name="accessories_1" id="accessories_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Mark R</span>
                <input style="width:60%;" name="mark_r" id="mark_r" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tube</span>
                <input style="width:60%;" name="tube_1" id="tube_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tube Size</span>
                <input style="width:60%;" name="tube_size_1" id="tube_size_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">End</span>
                <input style="width:60%;" name="end_1" id="end_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Note R</span>
                <input style="width:60%;" name="note_r" id="note_r" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">MC No</span>
                <input style="width:60%;" name="mc" id="mc" class="easyui-textbox">
            </div>
        </fieldset>
        <fieldset style="width:49%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Setting 2</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">D/C</span>
                <input style="width:60%;" name="dc_2" id="dc_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Terminal F</span>
                <input style="width:60%;" name="terminal_2" id="terminal_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Strip</span>
                <input style="width:60%;" name="strip_2" id="strip_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CH/C</span>
                <input style="width:60%;" name="chc_2" id="chc_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CW/C</span>
                <input style="width:60%;" name="cwc_2" id="cwc_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CH/I</span>
                <input style="width:60%;" name="chi_2" id="chi_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CW/I</span>
                <input style="width:60%;" name="cwi_2" id="cwi_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Accessories</span>
                <input style="width:60%;" name="accessories_2" id="accessories_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Mark F</span>
                <input style="width:60%;" name="mark_f" id="mark_f" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tube</span>
                <input style="width:60%;" name="tube_2" id="tube_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tube Size</span>
                <input style="width:60%;" name="tube_size_2" id="tube_size_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">End</span>
                <input style="width:60%;" name="end_2" id="end_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Note F</span>
                <input style="width:60%;" name="note_f" id="note_f" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Index</span>
                <input style="width:60%;" name="order" id="order" class="easyui-numberbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <p>History Upload</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- Sync -->
<div id="dlg_sync" class="easyui-dialog" title="Sync Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks2" title="History Sync" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks2">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" style="width: 100%;" hidden></iframe>
<script>
    function sync() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to push this data?', function(r) {
                if (r) {
                    $("#dlg_sync").dialog('open');
                    $("#sync").linkbutton("disable");

                    requestData(rows.length, rows);
                    function requestData(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload2').progressbar('setValue', value);
                            $('#p_start2').html(number);
                            $('#p_finish2').html(total);

                            $.post('<?= base_url('master/wos/createSync') ?>', {
                                data: json[number - 1]
                            }, function(note) {
                                var result = eval('(' + note + ')');

                                if (result.theme == "success") {
                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                } else {
                                    $("#dlg_sync").dialog('close');
                                    Swal.fire(result.title, result.message, 'error');
                                    return false;
                                }

                                requestData(total, json, number + 1, value);
                                $("#p_remarks2").append(title + "<br>");

                                if (value == 100) {
                                    $("#dlg_sync").dialog('close');
                                    Swal.fire('Completed', 'Push Data WOS has been completed', 'success');
                                    $("#sync").linkbutton("enable");
                                }
                            }).fail(function(jqXHR, textStatus) {
                                if (textStatus == "error") {
                                    $("#dlg_sync").dialog('close');
                                    Swal.fire('Not Connected', 'Check Connection Database ERP Banshu', 'error');
                                }
                            });
                        }
                    }
                }
            });
        }else{
            toastr.info("Please select one of the data in the table first");
        }
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/wos/create') ?>';
        $('#frm_insert').form('clear');
        $("#item_fg_id").combogrid('enable');
        $("#circuit_no").textbox('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/wos/update') ?>?id=' + btoa(row.id);

            $("#item_fg_id").combogrid('disable');
            $("#circuit_no").textbox('disable');
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
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
                                url: '<?= base_url('master/wos/delete') ?>',
                                data: {
                                    id: row.id
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

    //PRINT PDF
    function pdf() {
        // $("#printout").get(0).contentWindow.print();
        $('#dg').datagrid('print','DataGrid');
    }
    
    //PRINT EXCEL
    function excel() {
        // window.location.assign('<?= base_url('master/wos/print/excel') ?>');
        $('#dg').datagrid('toExcel','wos_<?= time() ?>.xls');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    //UPLOAD
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    //DOWNLOAD TEMPLATE
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_wos.xls') ?>');
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/wos/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000, 10000],
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
                            var circuit_no = $("#circuit_no").textbox('getValue');
                            var hasLetter = /[a-zA-Z]/.test(circuit_no);
                            var hasNumber = /[0-9]/.test(circuit_no);

                            if (hasLetter && !hasNumber) {
                                toastr.error("Circuit No should not contain only letters!!");
                                return false;
                            }

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
            }]
        });

        // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/wos/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/wos/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                Swal.fire({
                                    title: 'Please Wait while fetching Excel Data',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });
                            }
                        },
                        success: function(result) {
                            Swal.close();
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('master/wos/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/wos/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/wos/uploadcreateFailed') ?>",
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

        $('#item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads'); ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No EBWS',
                    width: 100
                },{
                    field: 'number_customer',
                    title: 'Product No Customer',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }]
            ],
        });
    });
</script>