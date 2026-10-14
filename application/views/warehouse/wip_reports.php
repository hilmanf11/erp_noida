<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'id',width:120,halign:'center'">Part No EBWS</th>
        <th data-options="field:'number',width:200,halign:'center'">Part No Supplier</th>
    </thead>
    <thead>
        <tr>
            <th data-options="field:'item_family_name',width:120,halign:'center'">Product Family</th>
            <th data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th data-options="field:'main_process',width:100,halign:'center'">Process</th>
            <th data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th data-options="field:'begin',width:120,halign:'center',align:'right', formatter:numberformat">WIP Opening</th>
            <th data-options="field:'wip',width:120,halign:'center',align:'right', formatter:numberformat">Adjustment</th>
            <th data-options="field:'receipt',width:120,halign:'center',align:'right', formatter:numberformat">Received</th>
            <th data-options="field:'scrap',width:100,halign:'center',align:'right', formatter:numberformat">Scrap</th>
            <th data-options="field:'ng',width:100,halign:'center',align:'right', formatter:numberformat">NG</th>
            <th data-options="field:'fg',width:120,halign:'center',align:'right', formatter:numberformat">Use in FG</th>
            <th data-options="field:'balance',width:120,halign:'center',align:'right', formatter:numberformat,styler:numberStyle">Balance</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 150px; padding: 10px;">
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 60%; float: left;">
            <div class="fitem">
                <span style="width:10%; display:inline-block;">Period</span>
                <input style="width:18%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:18%;" id="filter_to" class="easyui-datebox" value="<?= date('Y-m-d', strtotime('+14 days', strtotime(date('Y-m-01')))); ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <!-- <a href="javascript:;" class="easyui-linkbutton c1" id="generate" onclick="generate()"><i class="fa fa-database"></i> Calculate WIP</a> -->
            </div>
        </div>
        <div style="width: 40%; float: left;">
            <div class="alert alert-warning">
                The system takes data begin balance from date 2024-12-30 at the time of STO
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- UPLOAD DATA -->
<div id="dlg_save" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_save" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start_save">0</b> Of <b id="p_finish_save">0</b></center>
    <div id="p_remark_save" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
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


<iframe id="printout" src="<?= base_url('warehouse/wip_reports/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function generate() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');

        if (filter_from != "" && filter_to != "") {
            const start = new Date(filter_from).getTime();
            const end = new Date(filter_to).getTime();
            const daysDifference = Math.abs(end - start) / (1000 * 3600 * 24);
            const weeks = Math.ceil(daysDifference / 7);

            if (weeks > 2) {
                toastr.warning("Data is too long, please select the date of periode maximum for 2 weeks", "Information");
            } else {
                $.messager.prompt('Calculate WIP', 'Please input "YES" to starting Calculate', function(r) {
                    if (r == "YES") {
                        Swal.fire({
                            title: 'Checking Data. . ',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('warehouse/wip_reports/getdata') ?>",
                            data: "filter_from=" + filter_from +
                                "&filter_to=" + filter_to,
                            dataType: "json",
                            success: function(rows) {
                                Swal.close();
                                if (rows['total'] > 0) {
                                    $("#dlg_save").dialog('open');
                                    requestData(rows['total'], rows);

                                    function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                        if (value < 100) {
                                            value = Math.floor((number / total) * 100);
                                            $('#p_save').progressbar('setValue', value);
                                            $('#p_start_save').html(number);
                                            $('#p_finish_save').html(total);

                                            $.post('<?= base_url('warehouse/wip_reports/create') ?>', {
                                                data: json[number - 1],
                                                filter_from: filter_from,
                                                filter_to: filter_to
                                            }, function(note) {
                                                var result = eval('(' + note + ')');
                                                if (result.theme == "success") {
                                                    Swal.close();
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
                                                        url: "<?= base_url('warehouse/wip_reports/uploadcreateFailed') ?>",
                                                        data: {
                                                            data: json[number - 1],
                                                            message: result.message
                                                        },
                                                        cache: false
                                                    });

                                                    requestData(total, json, number + 1, value, success + 0, failed + 1);
                                                }

                                                if (value == 100) {
                                                    $("#dlg_save").dialog('close');
                                                    Swal.fire('Good job!', 'Process Calculate Data Completed!', 'success');
                                                }

                                                $("#p_remark_save").append(title + "<br>");
                                            }).fail(function(jqXHR, textStatus) {
                                                if (textStatus == "error") {
                                                    toastr.error("Something Wrong in System. .");
                                                    // requestData(total, json, number, value, success + 0, failed + 0);
                                                }
                                            });
                                        }
                                    }
                                } else {
                                    toastr.error("Data Not Found");
                                }
                            },
                            error: function() {
                                Swal.fire('Failed!', 'Process Calculating Data is Failed, Please Try Again!', 'error');
                            }
                        });
                    }
                });
            }
        } else {
            toastr.warning("Component Check Not Complete ", "Information");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to;

        $('#dg').datagrid('loading');

        $.ajax({
            url: '<?= base_url('warehouse/wip_reports/datatables') ?>',
            type: 'POST',
            data: {
                filter_from: filter_from,
                filter_to: filter_to
            },
            dataType: 'json',
            success: function(result) {
                $('#dg').datagrid('loadData', result).datagrid('loaded');
            }
        });
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_sto_wip.xls') ?>');
    }

    //PRINT PDF
    function pdf() {
        $('#dg').datagrid('print', 'DataGrid');
    }

    //PRINT EXCEL
    function excel() {
        $('#dg').datagrid('toExcel', 'wip_reports.xls');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            // url: '<?= base_url('warehouse/wip_reports/datatables') ?>',
            pagination: true,
            clientPaging: true,
            // remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        $("#filter_from").datebox({
            onChange: function(val){
                var selectedDate = new Date(val); 
                var minDate = new Date("2024-12-30"); 
                if (selectedDate < minDate) { 
                    toastr.error("Error: The selected date must be after December 30, 2024."); 
                    $("#filter_from").datebox('setValue', "2024-12-30");
                }
            }
        });

        // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('warehouse/sto_wip/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('warehouse/sto_wip/upload') ?>',
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
                                url: "<?= base_url('warehouse/sto_wip/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('warehouse/sto_wip/uploadCreate') ?>",
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
                                                    url: "<?= base_url('warehouse/sto_wip/uploadcreateFailed') ?>",
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
    });

    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberStyle(value, row, index) {
        if (value <= 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
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