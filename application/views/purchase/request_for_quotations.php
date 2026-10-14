<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print_rfq',width:80,halign:'center',formatter:BtnPrint">Print RFQ</th>
            <th rowspan="2" data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <!-- <th rowspan="2" data-options="field:'status',width:120,align:'center',formatter:statusformat,styler:statusStyle">Status</th> -->
            <th rowspan="2" data-options="field:'rfq_no',width:200,halign:'center'">RFQ No</th>
            <th rowspan="2" data-options="field:'rfq_date',width:100,halign:'center'">RFQ Date</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'mpq',width:100,align:'center'">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:100,align:'center'">MOQ</th>
            <th rowspan="2" data-options="field:'leadtime',width:100,align:'center'">Leadtime</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th colspan="4" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'status_approved',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:120,align:'center'">By</th>
            <th data-options="field:'approved_to',width:120,align:'center'">Next</th>
            <th data-options="field:'approved_date',width:150,align:'center'">Date</th>
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
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">RFQ No</span>
                    <input style="width:60%;" id="filter_rfq_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_familys" class="easyui-combogrid">
                </div>
            </div>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 550px; padding:10px; left: 10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" class="easyui-combogrid" required>
                </div>
            </div>
            <div style="width: 35%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">RFQ No</span>
                    <input style="width:60%;" name="rfq_no" id="rfq_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">RFQ Date</span>
                    <input style="width:60%;" name="rfq_date" id="rfq_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Request for Quotation Lists" toolbar="#toolbar2"></table>
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
<iframe id="printout" src="<?= base_url('purchase/request_for_quotations/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#rfq_no').textbox('clear');
        $('#supplier_id').combogrid('clear');
        $('#supplier_id').combogrid('enable');

        flag = "add";

        rfq_no();
    }

    function rfq_no() {
        $.ajax({
            type: "post",
            url: "<?= base_url("purchase/request_for_quotations/rfq_no/" . $id_menu . "/add") ?>",
            dataType: "json",
            success: function(response) {
                if (response == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                } else {
                    $("#rfq_no").textbox('setValue', response.code);
                }
            }
        });
    }

    function addTable(link = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_rm_id',
                    width: 150,
                    halign: 'center',
                    title: "Part No EBWS",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/item_rm/reads/') ?>',
                            required: true,
                            panelWidth: 550,
                            idField: 'id',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part',
                            columns: [
                                [{
                                    field: 'id',
                                    title: 'Part No EBWS',
                                    width: 100
                                }, {
                                    field: 'number',
                                    title: 'Part No Supplier',
                                    width: 200
                                }, {
                                    field: 'name',
                                    title: 'Description',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_id'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_number'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_name'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });

                                $(ed.target).textbox('setValue', rows.id);
                                $(ed2.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.uom);
                            }
                        }
                    }
                }, {
                    field: 'item_number',
                    width: 200,
                    readonly: true,
                    halign: 'center',
                    title: "Part No Supplier",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'item_rm_name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    readonly: true,
                    halign: 'center',
                    title: "UoM",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'mpq',
                    width: 80,
                    halign: 'center',
                    title: "MPQ",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'moq',
                    width: 80,
                    halign: 'center',
                    title: "MOQ",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'leadtime',
                    width: 80,
                    halign: 'center',
                    title: "Leadtime",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'price',
                    width: 80,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 4,
                        }
                    }
                }, ]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                editIndex = rowIndex;
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);
            }
        });
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        var supplier_id = $("#supplier_id").combogrid('getValue');
        var rfq_no = $("#rfq_no").textbox('getValue');
        if (supplier_id != "" && rfq_no != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    price: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Supplier & RFQ No first");
        }
        // }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.datatable == "1") {
                if (row.status == "0") {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);
                    $('#supplier_id').combogrid('disable');

                    flag = "update";

                    setTimeout(function() {
                        $('#rfq_no').textbox('setValue', row.rfq_no);
                    }, 2000);

                    addTable('<?= base_url('purchase/request_for_quotations/datatable_updates?rfq_no=') ?>' + window.btoa(row.rfq_no));
                } else {
                    toastr.error("You cannot update this data, because status Request is 1");
                }
            } else {
                toastr.error("Please Select Header of RFQ <br>" + row.rfq_no);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of RFQ <br>" + row.id);
                        } else if (row.status != "0") {
                            toastr.error("Data cannot deleted, because the status already 1");
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/request_for_quotations/delete') ?>',
                                data: {
                                    id: row.id,
                                },
                                success: function(result) {
                                    // readRequestno();
                                    var result = eval('(' + result + ')');
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                    $.messager.alert("Error", jqXHR.statusText, 'error');
                                },
                                complete: function(data) {
                                    toastr.success("Delete Data Success");
                                    $('#dg').treegrid('reload');
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
        window.location.assign('<?= base_url('template/tmp_request_for_quotations.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_rfq_no = $("#filter_rfq_no").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_familys = $("#filter_item_familys").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_rfq_no=" + filter_rfq_no + "&filter_item_rm=" + filter_item_rm + "&filter_supplier=" + filter_supplier + "&filter_item_familys=" + filter_item_familys;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/request_for_quotations/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'rfq_no',
            fit: true,
            singleSelect: false,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/request_for_quotations/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_rfq_no = $("#filter_rfq_no").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_familys = $("#filter_item_familys").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_rfq_no=" + filter_rfq_no + "&filter_item_rm=" + filter_item_rm + "&filter_supplier=" + filter_supplier + "&filter_item_familys=" + filter_item_familys;
        window.location.assign('<?= base_url('purchase/request_for_quotations/print/excel') ?>' + url);
    }

    function print_pr() {
        var rows = $('#dg').treegrid('getSelected');
        if (rows.length > 0) {
            window.open("<?= base_url('purchase/request_for_quotations/print_request/') ?>" + window.btoa(rows.rfq_no), "_blank");
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }

    function readRequestno() {
        $("#filter_rfq_no").combobox({
            url: '<?= base_url('purchase/request_for_quotations/readRequestno') ?>',
            valueField: 'rfq_no',
            textField: 'rfq_no',
            prompt: "Select RFQ No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    function BtnPrint(val, row) {
        if (row.datatable == 1) {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('purchase/request_for_quotations/print_request/') ?>' + btoa(row.rfq_no) + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
        }
    }

    $(function() {
        addTable();
        filter();
        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    $.ajax({
                        type: "post",
                        url: "<?= base_url("purchase/request_for_quotations/rfq_no/" . $id_menu . "/add") ?>",
                        dataType: "json",
                        success: function(response) {
                            // $("#rfq_no").textbox('setValue', response.code);
                            if (flag == "add") {
                                var rfq_no = response.code;
                            } else {
                                var rfq_no = $("#rfq_no").textbox('getValue');
                            }

                            var rfq_date = $("#rfq_date").datebox('getValue');
                            var supplier_id = $("#supplier_id").combogrid('getValue');

                            $("#dg2").datagrid('acceptChanges');
                            var rows = $('#dg2').datagrid('getRows');
                            var totalrows = rows.length;
                            endEditing();

                            for (let i = 0; i < totalrows; i++) {
                                if (rows[i].item_rm_id) {
                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('purchase/request_for_quotations/create') ?>',
                                        data: {
                                            rfq_no: rfq_no,
                                            rfq_date: rfq_date,
                                            supplier_id: supplier_id,
                                            item_rm_id: rows[i].item_rm_id,
                                            mpq: rows[i].mpq,
                                            moq: rows[i].moq,
                                            leadtime: rows[i].leadtime,
                                            price: rows[i].price,

                                        },
                                        dataType: "json",
                                        success: function(result) {
                                            if ((i + 1) == totalrows) {
                                                Swal.fire({
                                                    title: result.message,
                                                    icon: result.theme,
                                                    confirmButtonText: 'Ok',
                                                    allowOutsideClick: false,
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        window.location.reload();
                                                    }
                                                });
                                            }
                                        }
                                    });
                                }
                            }

                            readRequestno();
                            $('#dg').treegrid('reload');
                            $('#dlg_insert').dialog('close');
                        }
                    });
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('purchase/request_for_quotations/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('purchase/request_for_quotations/upload') ?>',
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
                                url: "<?= base_url('purchase/request_for_quotations/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('purchase/request_for_quotations/uploadCreate') ?>",
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
                                                    url: "<?= base_url('purchase/request_for_quotations/uploadcreateFailed') ?>",
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

        $('#supplier_id').combogrid({
            url: '<?= base_url('master/suppliers/reads') ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'ID',
                    width: 80
                }, {
                    field: 'number',
                    title: 'Code',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 250
                }]
            ]
        });

        readRequestno();
    });

    $('#filter_supplier').combogrid({
        url: '<?= base_url('master/suppliers/reads') ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Supplier",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'id',
                title: 'ID',
                width: 80
            }, {
                field: 'number',
                title: 'Code',
                width: 80
            }, {
                field: 'name',
                title: 'Name',
                width: 250
            }]
        ]
    });

    $('#filter_item_familys').combogrid({
        url: '<?= base_url('master/item_familys/readNotRm') ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Product Family",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'number',
                title: 'Product Family ID',
                width: 120
            }, {
                field: 'name',
                title: 'Product Family Name',
                width: 250
            }, ]
        ]
    });

    $('#filter_item_rm').combogrid({
        url: '<?= base_url('master/item_rm/readNotRm') ?>',
        panelWidth: 600,
        idField: 'id',
        mode: 'remote',
        fitColumns: true,
        prompt: 'Choose Part No',
        columns: [
            [{
                field: 'id',
                title: 'Part No EBWS',
                width: 100
            }, {
                field: 'number',
                title: 'Part No Supplier',
                width: 200
            }, {
                field: 'name',
                title: 'Description',
                width: 200
            }]
        ],
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

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:red;'>UNCONVERTED</b>";
        } else if (value == 1) {
            return "<b style='color:green;'>CONVERTED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#FFC8C8;';
        } else if (value == 1) {
            return 'background-color:#C8FFCC;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == "") {
            return 'Approved';
        } else if (row.approved_to == null) {
            return '-';
        } else {
            return 'Checking';
        }
    };

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == "") {
            return 'background: #53D636; color:white;';
        } else if (row.approved_to == null) {
            return '';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
</script>