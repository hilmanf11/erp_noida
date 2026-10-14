<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'wms',width:120,align:'center',formatter:BtnWms">WMS</th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:190">Kanban No</th>
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'">Request Date</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'">Request Name</th>
            <th rowspan="2" data-options="field:'workorder',width:120,halign:'center'">Workorder</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa">Qty</th>
            <th rowspan="2" data-options="field:'remarks',width:180,halign:'center'">Remarks</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
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
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Kanban ID</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>

        <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_kanban()"><i class="fa fa-print"></i> Print Kanban</a>
    </div>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" value="<?= date("Y-m-d") ?>" readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" required readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" name="workorder" id="workorder" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%; height: 70px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Request Kanban Material" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/supply_requests/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        request_no("<?= $id_menu ?>");

        $("#request_date").datebox('enable');
    }

    function request_no(id_menu = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/supply_requests/request_no') ?>/" + id_menu,
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                } else {
                    $("#request_no").textbox('setValue', result);
                }
            }
        });
    }

    //INSERT ADD ROW
    function addTable(item_fg_id, url = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: url,
            columns: [
                [{
                    field: 'item_rm_id',
                    width: 150,
                    halign: 'center',
                    title: "Part No EBWS",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('planning/supply_requests/readBom/') ?>' + btoa(item_fg_id),
                            required: true,
                            panelWidth: 600,
                            idField: 'item_rm_id',
                            textField: 'item_rm_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part No',
                            columns: [
                                [{
                                    field: 'item_rm_id',
                                    title: 'Part No EBWS',
                                    width: 100
                                }, {
                                    field: 'item_rm_number',
                                    title: 'Part No Supplier',
                                    width: 200
                                }, {
                                    field: 'item_rm_name',
                                    title: 'Description',
                                    width: 200
                                }, {
                                    field: 'mpq',
                                    title: 'MPQ',
                                    width: 80
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
                                    field: 'item_rm_number'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'composition'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'mpq'
                                });

                                var item_rm_id = $(ed.target).textbox('setValue', rows.item_rm_id);
                                var item_rm_number = $(ed2.target).textbox('setValue', rows.item_rm_number);
                                var uom = $(ed3.target).textbox('setValue', rows.uom);
                                var composition = $(ed4.target).textbox('setValue', rows.composition);
                                var mpq = $(ed5.target).textbox('setValue', rows.mpq);
                            }
                        }
                    }
                }, {
                    field: 'item_rm_number',
                    width: 200,
                    halign: 'center',
                    title: "Part No Supplier",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
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
                            readonly: true
                        }
                    }
                }, {
                    field: 'composition',
                    width: 80,
                    halign: 'center',
                    title: "QPA",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    align: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
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
        var item_fg_id = $("#item_fg_id").combogrid('getValue');
        var request_no = $("#request_no").textbox('getValue');

        if (item_fg_id == "" || request_no == "") {
            toastr.error("Please Complete your Input");
            return false;
        }

        if (endEditing()) {
            $('#dg2').datagrid('appendRow', {
                qty: '0'
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //Update Data
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.status == "0") {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#request_date").datebox('disable');

                // setTimeout(function() {
                //     $("#request_no").textbox('setValue', row.request_no);
                // }, 2000);

                addTable('<?= base_url('planning/supply_requests/datatableUpdate/') ?>' + window.btoa(row.request_no));
            } else {
                toastr.warning("Please Select Header of Table", "Information");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
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
                            var row = json[number - 1];
                            value = Math.floor((number / total) * 100);

                            if (row.status == 0) {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('planning/supply_requests/delete') ?>',
                                    data: {
                                        id: row.request_no,
                                        request_no: row.request_no,
                                        item_rm_id: row.item_rm_id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        requestDelete(total, json, number + 1, value);

                                        if (number == total) {
                                            $('#dg').treegrid('reload');
                                            Swal.close();
                                            Swal.fire(
                                                'Delete Completed',
                                                'Delete Data has been completed, You cannot restore data that has been deleted',
                                                'success'
                                            );
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        Swal.close();
                                        Swal.fire(
                                            'Delete Failed',
                                            'Cannot Process your Request to Delete',
                                            'error'
                                        );
                                    },
                                });
                            } else {
                                Swal.close();
                                toastr.info("Only header data can be deleted");
                            }
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
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        url = "?filter_request_no=" + filter_request_no + "&filter_product_family=" + filter_product_family + "&filter_product_no=" + btoa(filter_product_no) + "&filter_from=" + filter_from + "&filter_to=" + filter_to;

        $('#dg').treegrid({
            url: '<?= base_url('planning/supply_requests/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            singleSelect: false,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/supply_requests/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        url = "?filter_request_no=" + filter_request_no + "&filter_product_family=" + filter_product_family + "&filter_product_no=" + btoa(filter_product_no) + "&filter_from=" + filter_from + "&filter_to=" + filter_to;
        window.location.assign('<?= base_url('planning/supply_requests/print/excel') ?>' + url);
    }

    function print_kanban() {
        var row = $('#dg').treegrid('getSelected');

        if (row) {
            if (row.approved_to == "" || row.approved_to == null) {
                window.open("<?= base_url('planning/supply_requests/print_kanban/') ?>" + window.btoa(row.request_no));
            } else {
                toastr.error("Status Approval still Checking");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        filter();

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Send to Approval',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var item_fg_id = $("#item_fg_id").combogrid('getValue');
                    var workorder = $("#workorder").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    if (request_date == "" || request_no == "" || item_fg_id == "" || totalrows <= 0) {
                        toastr.error("please complete your input data");
                    } else {
                        $("#dg2").datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();

                        $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                            if (r) {
                                $('#dlg_insert').dialog('close');

                                Swal.fire({
                                    title: 'Please Wait. . .',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url('planning/supply_requests/request_no/' . $id_menu) ?>",
                                    dataType: "html",
                                    success: function(result) {
                                        if (result == "") {
                                            Swal.close();
                                            toastr.info("Please Check Configuration in Serial No First");
                                            return false;
                                        } else {
                                            var request_no = result;
                                        }

                                        requestData(totalrows, rows);

                                        function requestData(total, rows, number = 1, value = 0, success = 1, failed = 1) {
                                            var i = (number - 1);

                                            if (parseInt(rows[i].qty) >= 0) {
                                                if (value < 100) {
                                                    value = Math.floor((number / total) * 100);

                                                    $.ajax({
                                                        type: "post",
                                                        url: '<?= base_url('planning/supply_requests/create') ?>',
                                                        data: {
                                                            item_fg_id: item_fg_id,
                                                            workorder: workorder,
                                                            request_date: request_date,
                                                            request_no: request_no,
                                                            request_name: request_name,
                                                            remarks: remarks,
                                                            item_rm_id: rows[i].item_rm_id,
                                                            mpq: rows[i].mpq,
                                                            qty_req: rows[i].qty,
                                                            qty_act: rows[i].qty,
                                                            qty_issued: rows[i].qty,
                                                            qty_bal: 0,
                                                            type: 'REQ'
                                                        },
                                                        dataType: "json",
                                                        success: function(result) {
                                                            if (result.theme == "success") {
                                                                if (value >= 100) {
                                                                    Swal.close();
                                                                    Swal.fire({
                                                                        title: result.message,
                                                                        icon: result.theme,
                                                                        confirmButtonText: 'Ok',
                                                                        allowOutsideClick: false,
                                                                    }).then((result) => {
                                                                        if (result.isConfirmed) {
                                                                            $('#dg').treegrid('reload');
                                                                            // window.location.reload();
                                                                        }
                                                                    });
                                                                } else {
                                                                    requestData(total, rows, number + 1, value, success + 1, failed + 0);
                                                                }
                                                            } else {
                                                                Swal.close();
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
                                                        },
                                                    });
                                                }
                                            } else {
                                                Swal.close();
                                                toastr.error("Qty Cannot be 0");
                                                $('#dg').treegrid('reload');
                                            }
                                        }

                                    }
                                });
                            }
                        });
                    }
                }
            }]
        });

        $('#item_fg_id').combogrid({
            url: '<?= base_url('planning/supply_requests/readItem') ?>',
            panelWidth: 580,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No EBWS',
                    width: 100
                }, {
                    field: 'number_customer',
                    title: 'Product No Cusstomer',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 120
                }, {
                    field: 'workorder',
                    title: 'Workorder',
                    width: 120
                }, ]
            ],
            onSelect: function(val, item_fg) {
                addTable(item_fg.id);
                $('#workorder').textbox('setValue', item_fg.workorder);
            }
        });

        $("#filter_request_no").combobox({
            url: '<?= base_url('planning/supply_requests/readRequestNo') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Choose Kanban",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_product_family").combobox({
            url: '<?= base_url('master/item_familys/readNotFg/') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Family",
            onSelect: function(prodfam) {
                $('#filter_product_no').combogrid({
                    url: '<?= base_url('master/item_rm/reads/') ?>' + prodfam.id,
                    panelWidth: 480,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Part No",
                    columns: [
                        [{
                            field: 'id',
                            title: 'Part No EBWS',
                            width: 100
                        }, {
                            field: 'number',
                            title: 'Part No Cusstomer',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 150
                        }, ]
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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatQpa(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (val == "" || val == null) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function cellStylerApproval(value, row, index) {
        if (value == "" || value == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function BtnWms(val, row) {
        if (row.state == "closed") {
            if (row.request_date >= "2026-07-02") {
                var onClick = "sendWms('" + row.request_no + "')";
                return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-database"></i> Send</a>';
            } else {
                return '-';
            }
        }
    }

    function sendWms(request_no) {
        $.messager.confirm('Confirm', 'Are you sure you want to send data to WMS?', function(r) {
            if (r) {
                Swal.fire({
                    title: 'Please Wait for Sending Data',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                var burl = "<?= base_url("api/wms/kanbanRequest") ?>";

                $.ajax({
                    type: "post",
                    url: burl,
                    data: {
                        request_no: request_no
                    },
                    dataType: "json",
                    success: function(msg) {
                        if (msg.success) {
                            Swal.fire("", msg.message, "success");
                            // $.ajax({
                            //     type: "post",
                            //     url: '<?= base_url('api/wms/gateEntryClosing') ?>',
                            //     data: {receipt_no: receipt_no},
                            //     dataType: "json",
                            //     success: function (si) {
                            //         $('#dg').datagrid('reload');

                            //         Swal.fire(
                            //             "Info",
                            //             msg.message,
                            //             "success"
                            //         );
                            //     }
                            // });
                        } else {
                            Swal.fire("", msg.message, "error");
                        }
                    },
                    error: function() {
                        Swal.fire("", "Failed to Sending Data WMS", "error");
                    }
                });
            }
        });
    }
</script>