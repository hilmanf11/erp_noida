<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'document_no',width:120,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:150,halign:'center'">Product EBWS</th>
            <th rowspan="2" data-options="field:'number_customer',width:150,halign:'center'">Product Customer</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:150,halign:'center'">Description</th>
            <th colspan="7" data-options="field:'',width:100,halign:'center'"> Stock WIP</th>
            <th colspan="3" data-options="field:'',width:100,align:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'pp',width:80,align:'center',formatter:numberFormat"> PP</th>
            <th data-options="field:'wc',width:80,align:'center',formatter:numberFormat"> WC</th>
            <th data-options="field:'ha',width:80,align:'center',formatter:numberFormat"> HA</th>
            <th data-options="field:'c',width:80,align:'center',formatter:numberFormat"> C</th>
            <th data-options="field:'v',width:80,align:'center',formatter:numberFormat"> V</th>
            <th data-options="field:'p',width:80,align:'center',formatter:numberFormat"> P</th>
            <th data-options="field:'rfg',width:80,align:'center',formatter:numberFormat"> RFG</th>
            <th data-options="field:'approved_to',width:100,align:'center', styler:styleApproved, formatter:formatApproved"> Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'"> By</th>
            <th data-options="field:'approved_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox">
                    <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No.</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" name="p_month" id="p_month" required="" class="easyui-combobox">
                <input style="width:30%;" name="p_year" id="p_year" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <select style="width:60%;" name="revision" id="revision" required class="easyui-combobox" panelHeight="auto">
                    <option value="" selected disabled>Choose Revision</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Document No.</span>
                <input style="width:60%;" name="document_no" id="document_no" class="easyui-textbox">
            </div>
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="customer_id" id="customer_id" required class="easyui-combobox">
            </div> -->
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No.</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" required class="easyui-combogrid">
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Quantity Stock</b></legend>
            <div style="width:50%;float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PP</span>
                    <input style="width:40%;" name="pp" id="pp" required class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WC</span>
                    <input style="width:40%;" name="wc" id="wc" required class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">HA</span>
                    <input style="width:40%;" name="ha" id="ha" required class="easyui-numberbox">
                </div>
            </div>
            <div style="width:50%;float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">C</span>
                    <input style="width:40%;" name="c" id="c" required class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">V</span>
                    <input style="width:40%;" name="v" id="v" required class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">P</span>
                    <input style="width:40%;" name="p" id="p" required class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">RFG</span>
                    <input style="width:40%;" name="rfg" id="rfg" required class="easyui-numberbox">
                </div>
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

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/stock_wip/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    // function add() {
    //     var filter_month = $("#filter_month").combobox('getValue');
    //     var filter_year = $("#filter_year").textbox('getValue');
    //     var filter_cutoff = $("#filter_cutoff").datebox('getValue');
    //     var filter_revision = $("#filter_revision").combobox('getValue');
    //     var filter_product_family = $("#filter_product_family").combobox('getValue');
    //     var filter_product_no = $("#filter_product_no").combogrid('getValue');

    //     var rows = $('#dg').datagrid('getChecked');
    //     if (rows.length > 0) {
    //         $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
    //             if (r) {
    //                 $.ajax({
    //                     url: "<?= base_url('planning/os_po/uploadclearFailed') ?>"
    //                 });

    //                 requestData(rows.length, rows);

    //                 function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
    //                     if (value < 100) {
    //                         value = Math.floor((number / total) * 100);
    //                         $('#p_upload').progressbar('setValue', value);
    //                         $('#p_start').html(number);
    //                         $('#p_finish').html(total);

    //                         $.post('<?= base_url('planning/os_po/create') ?>', {
    //                             data: json[number - 1]
    //                         }, function(note) {
    //                             var result = eval('(' + note + ')');
    //                             if (result.theme == "success") {
    //                                 Swal.close();
    //                                 $('#p_success').html(success);
    //                                 var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
    //                                 requestData(total, json, number + 1, value, success + 1, failed + 0);
    //                             } else {
    //                                 $('#p_failed').html(failed);
    //                                 var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

    //                                 //Json Failed
    //                                 $.ajax({
    //                                     type: "POST",
    //                                     async: true,
    //                                     url: "<?= base_url('planning/os_po/uploadcreateFailed') ?>",
    //                                     data: {
    //                                         data: json[number - 1],
    //                                         message: result.message
    //                                     },
    //                                     cache: false
    //                                 });

    //                                 requestData(total, json, number + 1, value, success + 0, failed + 1);
    //                             }

    //                             if (value == 100) {
    //                                 Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
    //                             }

    //                             $("#p_remarks").append(title + "<br>");

    //                         }).fail(function(jqXHR, textStatus) {
    //                             if (textStatus == "error") {
    //                                 Swal.fire({
    //                                     title: 'Connection Time Out, Check Your Connection',
    //                                     showConfirmButton: false,
    //                                     allowOutsideClick: false,
    //                                     allowEscapeKey: false,
    //                                     didOpen: () => {
    //                                         Swal.showLoading();
    //                                     },
    //                                 });

    //                                 requestData(total, json, number, value, success + 0, failed + 0);
    //                             }
    //                         });
    //                     }
    //                 }
    //             }
    //         });
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!");
    //     }
    // }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('planning/stock_wip/create') ?>';
        $('#frm_insert').form('clear');
        // $("#customer_id").combogrid('enable');
        $("#item_fg_id").combogrid('enable');
        $("#p_month").combobox('enable');
        $("#p_year").combobox('enable');
        $("#revision").combogrid('enable');
        $("#document_no").combogrid('enable');

        $("#revision").combobox('setValue', '0');
        $("#p_month").combobox('setValue', '<?= date("m") ?>');
        $("#p_year").combobox('setValue', '<?= date("Y") ?>');
    }

    //EDIT DATA
    function update() {
        $("#item_fg_id").combogrid('disable');
        // $("#customer_id").combogrid('disable');
        $("#revision").combogrid('disable');
        $("#document_no").combogrid('disable');
        $("#p_month").combobox('disable');
        $("#p_year").combobox('disable');

        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            // $("#customer_id").combobox('setValue',row.customer_id);
            $("#item_fg_id").combogrid('setValue',row.item_fg_id);
            url_save = '<?= base_url('planning/stock_wip/update') ?>?id=' + btoa(row.id);
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
                                url: '<?= base_url('planning/stock_wip/delete') ?>',
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

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_stock_wip.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        // var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_revision=" + window.btoa(filter_revision);

        if(filter_period_month == "" || filter_period_year == "" || filter_revision == ""){
            toastr.info("Please Choose Period and Revision");
        }else{
            $('#dg').datagrid({
                url: '<?= base_url('planning/stock_wip/datatables') ?>' + url,
                pagination: true,
                clientPaging: false,
                remoteFilter: true,
                rownumbers: true,
                fit: true,
                pageList: [10, 50, 100, 500, 1000],
                pageSize: 10,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/stock_wip/print') ?>' + url);
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        // var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_revision=" + window.btoa(filter_revision);

        if(filter_period_month == "" || filter_period_year == "" || filter_revision == ""){
            toastr.info("Please Choose Period and Revision");
        }else{
            window.location.assign('<?= base_url('planning/stock_wip/print/excel') ?>' + url);
        }
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
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
            }]
        });
    });

    $('#p_month').combobox({
        url: '<?= base_url('planning/stock_wip/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
    });

    $('#p_year').combobox({
        url: '<?= base_url('planning/stock_wip/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
    });

    $('#item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads/'); ?>',
        panelWidth: 620,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'number',
                title: 'Product EBWS',
                width: 150
            },{
                field: 'number_customer',
                title: 'Product Customer',
                width: 200
            }, {
                field: 'name',
                title: 'Description',
                width: 250
            }, ]
        ]
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 650,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'number',
                title: 'Product EBWS',
                width: 150
            },{
                field: 'number_customer',
                title: 'Product Customer',
                width: 200
            }, {
                field: 'name',
                title: 'Description',
                width: 200
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_period_month').combobox({
        url: '<?= base_url('planning/stock_wip/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_period_year').combobox({
        url: '<?= base_url('planning/stock_wip/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('planning/stock_wip/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('planning/stock_wip/upload') ?>',
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
                            url: "<?= base_url('planning/stock_wip/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('planning/stock_wip/uploadCreate') ?>",
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
                                                url: "<?= base_url('planning/stock_wip/uploadcreateFailed') ?>",
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
</script>