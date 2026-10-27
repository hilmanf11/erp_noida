<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print_po',width:80,halign:'center',formatter:BtnPrint">Print</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStylerStatus, formatter:cellFormatterStatus">Status</th>
            <th rowspan="2" data-options="field:'return_no',width:200,halign:'center'">Return No</th>
            <th rowspan="2" data-options="field:'return_date',width:100,halign:'center'">Return Date</th>
            <th rowspan="2" data-options="field:'return_name',width:150,halign:'center'">Return Name</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'po_no',width:120,halign:'center'">Purchase Order</th>
            <th rowspan="2" data-options="field:'invoice_no',width:120,halign:'center'">Invoice No</th>
            <th rowspan="2" data-options="field:'receipt_id',width:150,halign:'center'">Receipt ID</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <!-- <th rowspan="2" data-options="field:'description',width:400,halign:'center'">Product Specification</th> -->
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformat">MPQ</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
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
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 80%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Return No</span>
                    <input style="width:60%;" id="filter_return_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier No</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_id" class="easyui-combogrid">
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 550px; padding:10px; top: 0px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Return No</span>
                    <input style="width:60%;" name="return_no" id="return_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Return Date</span>
                    <input style="width:60%;" name="return_date" id="return_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Return Name</span>
                    <input style="width:60%;" name="return_name" id="return_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Return List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- UPDATE -->
<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" required class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No EBWS</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" name="item_rm_number" id="item_rm_number" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Name</span>
                <input style="width:60%;" name="item_rm_name" id="item_rm_name" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" required="" class="easyui-numberbox" data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:60%;" name="po_no" id="po_no" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" name="invoice_no" id="invoice_no" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/purchase_returns/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);

        $.ajax({
            url: "<?= base_url('purchase/purchase_returns/return_no') ?>",
            success: function(return_no) {
                $("#return_no").textbox('setValue', return_no);
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');

        if (row) {
            if (row.datatable != 1) {
                $('#dlg_update').dialog('open');
                $('#frm_update').form('load', row);
            } else {
                toastr.error("Please Select Detail in the Table");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function addTable(supplier_id) {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            singleSelect: true,
            columns: [
                [{
                    field: 'source',
                    width: 120,
                    halign: 'center',
                    title: "Source",
                    editor: {
                        type: 'combobox',
                        options: {
                            required: true,
                            panelHeight: 'auto',
                            valueField: 'id',
                            textField: 'id',
                            data: [{
                                "id": "QAS"
                            },{
                                "id": "REJECT"
                            },{
                                "id": "SCRAP"
                            }],
                            onSelect: function (source) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                
                                var edpo = dg.datagrid('getEditor', {index: rowIndex, field: 'po_no'});
                                var ed = dg.datagrid('getEditor', {index: rowIndex, field: 'item_rm_id'});
                                var ed2 = dg.datagrid('getEditor', {index: rowIndex, field: 'item_rm_number'});
                                var ed3 = dg.datagrid('getEditor', {index: rowIndex, field: 'receipt_id'});
                                var ed4 = dg.datagrid('getEditor', {index: rowIndex, field: 'qty_receipt'});
                                var ed5 = dg.datagrid('getEditor', {index: rowIndex, field: 'qty'});
                                var ed6 = dg.datagrid('getEditor', {index: rowIndex, field: 'invoice_no'});

                                $(edpo.target).combobox({
                                    url: '<?= base_url('purchase/purchase_returns/readPono?supplier_id='); ?>' + btoa(supplier_id) + "&source=" + source.id,
                                    valueField: 'po_no',
                                    textField: 'po_no',
                                    prompt: 'Choose Purchase Order',
                                    required: true,
                                    onSelect: function (po) {
                                        $(ed.target).combogrid({
                                            url: '<?= base_url('purchase/purchase_returns/readProducts/'); ?>' + btoa(po.po_no) + "/" + source.id + "/" + btoa(supplier_id),
                                            required: true,
                                            panelWidth: 800,
                                            idField: 'id',
                                            textField: 'number',
                                            mode: 'remote',
                                            fitColumns: true,
                                            prompt: 'Choose Part No EBWS',
                                            columns: [
                                                [{
                                                    field: 'id',
                                                    title: 'Part No EBWS',
                                                    width: 150
                                                },{
                                                    field: 'number',
                                                    title: 'Part No Supplier',
                                                    width: 150
                                                }, {
                                                    field: 'name',
                                                    title: 'Description',
                                                    width: 200
                                                }, {
                                                    field: 'receipt_id',
                                                    title: 'Receipt ID',
                                                    width: 150
                                                }, {
                                                    field: 'qty_receipt',
                                                    title: 'Receipt',
                                                    width: 80
                                                }]
                                            ],
                                            onSelect: function (value, item) {
                                                $(ed2.target).textbox('setValue', item.number);
                                                $(ed3.target).textbox('setValue', item.receipt_id);
                                                $(ed4.target).textbox('setValue', item.qty_receipt);
                                                $(ed6.target).textbox('setValue', item.bc_document);
                                                $(ed5.target).numberbox({
                                                    onChange: function(val){
                                                        if((parseInt(item.qty_receipt) - val) < 0){
                                                            toastr.info("You cannot exceed qty Receipt");
                                                            $(ed5.target).numberbox('setValue', 0);
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    }
                                })
                            }
                        }
                    }
                }, {
                    field: 'po_no',
                    width: 150,
                    halign: 'center',
                    title: "PO No",
                    editor: {
                        type: 'combobox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'item_rm_id',
                    width: 150,
                    halign: 'center',
                    title: "Part No EBWS",
                    editor: {
                        type: 'combogrid',
                        options: {
                            required: true
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
                    field: 'receipt_id',
                    width: 150,
                    halign: 'center',
                    title: "Receipt ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'invoice_no',
                    width: 150,
                    halign: 'center',
                    title: "Invoice No",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'qty_receipt',
                    width: 100,
                    halign: 'center',
                    title: "Qty Receipt",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty',
                    width: 100,
                    halign: 'center',
                    title: "Qty Return",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 150,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
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
        var supplier_id = $("#supplier_id").combogrid('getValue');
        if (supplier_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose PO No first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }
    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        if(row.receipt_id != null){
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/purchase_returns/delete') ?>',
                                data: {
                                    id: row.id,
                                    receipt_id: row.receipt_id,
                                    item_rm_id: row.item_rm_id
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                    
                                },
                                complete: function(data) {
                                    $('#dg').treegrid('reload');
                                }
                            });
                        }else{
                            toastr.error("Please Select Detail Data in the table");
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function BtnPrint(val, row) {
        if(row.datatable == 1){
            var detail = "printReturn('"+row.return_no+"','"+row.status+"')";
            return '<a class="btn btn-primary w-100" onClick="'+detail+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
        }
    }

    function printReturn(return_no, status){
        if(status == "1"){
            $.messager.prompt('Print', 'Please input the Password', function(r){
                if (r == "FNC"){
                    window.open('<?= base_url('purchase/purchase_returns/print_return/') ?>'+btoa(return_no));
                }
            });
        }else{
            toastr.info("Please Create Purchase Invoice Return First.");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_return_no = $("#filter_return_no").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_return_no=" + filter_return_no + "&filter_item_id=" + filter_item_id + "&filter_supplier=" + filter_supplier;
        
        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_returns/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'return_no',
            fit: true,
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_returns/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_return_no = $("#filter_return_no").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_return_no=" + filter_return_no + "&filter_item_id=" + filter_item_id + "&filter_supplier=" + filter_supplier;
        window.location.assign('<?= base_url('purchase/purchase_returns/print/excel') ?>' + url);
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
                    var return_no = $("#return_no").textbox('getValue');
                    var return_date = $("#return_date").datebox('getValue');
                    var return_name = $("#return_name").textbox('getValue');
                    var supplier_id = $("#supplier_id").combogrid('getValue');

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();
                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].qty > 0) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('purchase/purchase_returns/create') ?>',
                                data: {
                                    return_no: return_no,
                                    return_date: return_date,
                                    return_name: return_name,
                                    supplier_id: supplier_id,
                                    source: rows[i].source,
                                    po_no: rows[i].po_no,
                                    invoice_no: rows[i].invoice_no,
                                    receipt_id: rows[i].receipt_id,
                                    item_rm_id: rows[i].item_rm_id,
                                    qty: rows[i].qty,
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
                        }else{
                            toastr.error("Qty Cannot 0");
                        }
                    }

                    $('#dg').treegrid('reload');
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
                        url: '<?= base_url('purchase/purchase_returns/update') ?>',
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
                            $('#dg').treegrid('reload');
                        }
                    });
                }
            }]
        });

        $("#filter_return_no").combobox({
            url: '<?= base_url('purchase/purchase_returns/readReturnNo') ?>',
            valueField: 'return_no',
            textField: 'return_no',
            prompt: "Select Request No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#supplier_id").combogrid({
            url: '<?php echo base_url('purchase/purchase_orders/readSupplier'); ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 300
                }]
            ],
            onSelect: function(val, supp){
                addTable(supp.id);
            }
        });

        $("#filter_supplier").combogrid({
            url: '<?php echo base_url('purchase/purchase_orders/readSupplier'); ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 300
                }]
            ],
            onSelect: function(val, supp){
                $("#filter_item_id").combogrid({
                    url: '<?php echo base_url('master/supplier_items/readItem?supplier_id='); ?>' + supp.id,
                    panelWidth: 550,
                    idField: 'item_rm_id',
                    textField: 'item_number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Part No",
                    columns: [
                        [{
                            field: 'item_rm_id',
                            title: 'Part No EBWS',
                            width: 150
                        }, {
                            field: 'item_number',
                            title: 'Part No Supplier',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Description',
                            width: 200
                        }]
                    ],
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
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSE</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }

    function BtnPrintLabel(val, row) {
        if (val != "closed") {
            return '<a style="text-decoration: none; font-weight:bold;" target="_blank" href="<?= base_url('purchase/purchase_returns/print_label/') ?>' + window.btoa(row.return_no) + '"><i class="fa fa-print"></i> Print</a>';
        }
    }

    function cellStylerStatus(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterStatus(value, row) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSE';
        }
    };
</script>