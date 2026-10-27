<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print_pr',width:80,halign:'center',formatter:BtnPrint">Print</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusFormat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'request_no',width:150,halign:'center'">Requisition No</th>
            <th rowspan="2" data-options="field:'trans_date',width:120,halign:'center'">Requisition Date</th>
            <th rowspan="2" data-options="field:'request_name',width:150,halign:'center'">Requisition Name</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'stock',width:80,halign:'center',align:'right',formatter:numberformat">Stock</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'center',formatter:numberformat">Balance</th>
            <th rowspan="2" data-options="field:'location_other_name',width:120,halign:'center'">Location Store</th>
            <th rowspan="2" data-options="field:'remarks',width:200,halign:'center'">Remarks</th>
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
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 40%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Requisition Date</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Requisition No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Requisition Name</span>
                    <input style="width:60%;" id="filter_request_name" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Location Store</span>
                    <input style="width:60%;" id="filter_location" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">ALL</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <!-- <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_return()"><i class="fa fa-print"></i> Return No</a> -->
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 900px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Requisition No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Requisition Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Requisition Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Location Store</span>
                    <input style="width:60%;" name="location_other_number" id="location_other_number" required class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Requisition Item List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- UPDATE -->
<div id="dlg_update" class="easyui-dialog" title="Update Requisition" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" required class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No EBWS</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" name="item_rm_number" id="item_rm_number" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="item_rm_name" id="item_rm_name" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Stock</span>
                <input style="width:30%;" name="stock" id="stock" disabled class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" required="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Balance</span>
                <input style="width:30%;" name="balance" id="balance" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" id="remarks" required="" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/issued_others/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        addTable();
        request_no("<?= $id_menu ?>");
    }

    function request_no(id_menu) {
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/issued_others/request_no') ?>/" + id_menu,
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

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.state != "closed") {
                if (row.status == "0") {
                    $('#dlg_update').dialog('open');
                    $('#frm_update').form('load', row);
                } else {
                    toastr.error("You cannot update this data, because status Return is closed");
                }
            } else {
                toastr.warning("Please select detail of table first");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function addTable() {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            singleSelect: true,
            columns: [
                [{
                    field: 'item_rm_id',
                    width: 120,
                    halign: 'center',
                    title: "Part No EBWS",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('warehouse/issued_others/readItem') ?>',
                            required: true,
                            panelWidth: 650,
                            idField: 'item_rm_id',
                            textField: 'item_rm_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part No EBWS',
                            columns: [
                                [{
                                    field: 'item_rm_id',
                                    title: 'Part No EBWS',
                                    width: 150
                                }, {
                                    field: 'item_rm_number',
                                    title: 'Part No Supplier',
                                    width: 200
                                }, {
                                    field: 'item_rm_name',
                                    title: 'Description',
                                    width: 200
                                }, {
                                    field: 'stock',
                                    title: 'Stock',
                                    width: 80
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_number'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_name'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                $(ed.target).textbox('setValue', rows.item_rm_number);
                                $(ed2.target).textbox('setValue', rows.item_rm_name);
                                $(ed3.target).textbox('setValue', rows.stock);
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
                    field: 'item_rm_name',
                    width: 200,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'stock',
                    width: 80,
                    halign: 'center',
                    title: "Stock",
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
                            onChange: function(qty) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'balance'
                                });

                                var stock = $(ed.target).numberbox('getValue');
                                var balance = (parseInt(stock) - parseInt(qty));

                                if (balance >= 0) {
                                    $(ed2.target).numberbox('setValue', balance);
                                } else {
                                    toastr.error("Balance cannot < 0");
                                    $(ed2.target).numberbox('clear');
                                }
                            }
                        }
                    }
                }, {
                    field: 'balance',
                    width: 80,
                    halign: 'center',
                    title: "Balance",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            required: true
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 200,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
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
        if (endEditing()) {
            var location_other_number = $("#location_other_number").combobox('getValue');
            if (location_other_number != '') {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });

                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            } else {
                toastr.error("Please Complete Input", "Error");
            }
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    function BtnPrint(val, row) {
        if (row.status == 0) {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('warehouse/issued_others/print_request/') ?>' + btoa(row.request_no) + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/issued_others/delete') ?>',
                            data: {
                                id: row.id
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

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").combogrid('getValue');
        var filter_request_name = $("#filter_request_name").combobox('getValue');
        var filter_location = $("#filter_location").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_request_no=" + filter_request_no + "&filter_request_name=" + 
            filter_request_name + "&filter_location=" + filter_location + "&filter_part_no=" + filter_part_no + "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('warehouse/issued_others/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            singleSelect: false,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/issued_others/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").combogrid('getValue');
        var filter_request_name = $("#filter_request_name").combobox('getValue');
        var filter_location = $("#filter_location").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_request_no=" + filter_request_no + "&filter_request_name=" + 
            filter_request_name + "&filter_location=" + filter_location + "&filter_part_no=" + filter_part_no + "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('warehouse/issued_others/print/excel') ?>' + url);
    }

    function print_return() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            window.open("<?= base_url('warehouse/issued_others/print_return/') ?>" + window.btoa(row.request_no));
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
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    // request_no("<?= $id_menu ?>");

                    var request_no = $("#request_no").textbox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var location_other_number = $("#location_other_number").combobox('getValue');

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    if(location_other_number == ""){
                        toastr.error("Location Store cannot empty!");
                        return false;
                    }

                    for (let z = 0; z < totalrows; z++) {
                        if (rows[z].balance == null || rows[z].balance == "" || parseFloat(rows[z].balance) < 0) {
                            toastr.error("Balance cannt empty or minus");
                            return false;
                        }

                        if(rows[z].remarks == null || rows[z].remarks == ""){
                            toastr.error("Remarks still Empty!");
                            return false;
                        }
                    }

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_rm_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('warehouse/issued_others/create') ?>',
                                data: {
                                    request_no: request_no,
                                    trans_date: trans_date,
                                    request_name: request_name,
                                    location_other_number: location_other_number,
                                    item_rm_id: rows[i].item_rm_id,
                                    stock: rows[i].stock,
                                    qty: rows[i].qty,
                                    balance: rows[i].balance,
                                    remarks: rows[i].remarks
                                },
                                dataType: "json",
                                success: function(result) {
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
                            });
                        }
                    }

                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        //Update Data
        $('#dlg_update').dialog({
            buttons: [{
                text: 'Update Data',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
                        url: '<?= base_url('warehouse/issued_others/update') ?>',
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
                            $('#dlg_update').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#filter_part_no').combogrid({
            url: '<?= base_url('master/item_rm/readNotRm/'); ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part No",
            columns: [
                [{
                    field: 'id',
                    title: 'Part ID',
                    width: 100
                }, {
                    field: 'number',
                    title: 'Part No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Part Name',
                    width: 200
                }]
            ],
        });

        $('#filter_request_no').combogrid({
            url: '<?= base_url('warehouse/issued_others/readRequestno/'); ?>',
            panelWidth: 550,
            idField: 'request_no',
            textField: 'request_no',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Request No",
            columns: [
                [{
                    field: 'request_no',
                    title: 'Requisition No',
                    width: 150
                }, {
                    field: 'request_name',
                    title: 'Requisition Name',
                    width: 150
                }, {
                    field: 'trans_date',
                    title: 'Trans Date',
                    width: 100
                }]
            ],
        });

        $('#filter_request_name').combobox({
            url: '<?= base_url('warehouse/issued_others/readRequestname'); ?>',
            valueField: 'request_name',
            textField: 'request_name',
            prompt: 'Choose Request Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_location').combobox({
            url: '<?= base_url('master/location_others/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Choose Location',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#qty").numberbox({
            onChange: function(qty) {
                var stock = $("#stock").numberbox('getValue');
                var balance = (parseInt(stock) - parseInt(qty));

                if (balance >= 0) {
                    $("#balance").numberbox('setValue', balance);
                } else {
                    toastr.error("Balance Cannot > 0");
                    $("#qty").numberbox('clear');
                    $("#balance").numberbox('clear');
                }
            }
        });

        $('#location_other_number').combobox({
            url: '<?= base_url('master/location_others/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Choose Location',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(rgp) {
                //   
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
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusFormat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSE</b>";
        } else if (value == 2) {
            return "<b style='color:red;'>CANCEL</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#7285e9;';
        }
    }
</script>