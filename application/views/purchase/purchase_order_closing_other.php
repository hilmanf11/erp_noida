<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'po_no',width:220,halign:'center'">Del. Sch No</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'po_date',width:100,align:'center'">Del. Sch Date</th>
            <th rowspan="2" data-options="field:'supplier_po',width:150,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:250,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:250,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatDefault">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'total_price',width:120,halign:'center',align:'right',formatter:numberformatDefault">Total</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'delivery_date',width:100,align:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'terms',width:100,halign:'center',formatter:btnTerms">Terms<br>Condition</th>
            <!-- <th rowspan="2" data-options="field:'attachment',width:80,align:'center',formatter: btnDetails">Attachment</th> -->
            <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:120,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 190px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 35%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Del. Sch No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_po()"><i class="fa fa-print"></i> Purchase Order</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_suppliers" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <!-- <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="signatures()"><i class="fa fa-check"></i> Signature</a> -->
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add Data" data-options="closed: true,modal:true" style="width: 90%; height: 550px; padding:10px; top: 10px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Del. Sch Date</span>
                    <input style="width:60%;" name="po_date" id="po_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Del. Sch No</span>
                    <input style="width:60%;" name="po_no" id="po_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Suppliers</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" class="easyui-combogrid" required>
                </div>
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="supplier_po" id="supplier_po" readonly class="easyui-textbox">
                </div>
            </div>
            <!-- <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment_upload" id="attachment_upload" class="easyui-filebox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-textbox">
                </div>
            </div> -->
        </fieldset>

        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Order Closing" toolbar="#toolbar2" data-options="rownumbers: true">
        </table>
    </form>
</div>

<!-- Update -->
<div id="dlg_update" class="easyui-dialog" title="Update Purchase Order Others" data-options="closed: true, modal:true" style="width: 500px; height: 550px; padding:10px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Del. Sch No</span>
                <input style="width:60%;" name="po_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:60%;" name="supplier_po" disabled class="easyui-textbox">
            </div>
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Attachment</span>
                <input style="width:60%;" id="attachment_upload2" class="easyui-filebox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Attachment</span>
                <input style="width:60%;" name="attachment" id="attachment2" class="easyui-textbox">
            </div> -->
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No EBWS</span>
                <input style="width:60%;" name="item_rm_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No Supplier</span>
                <input style="width:60%;" name="item_rm_number" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Date</span>
                <input style="width:28%;" name="delivery_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Before Qty</span>
                <input style="width:40%;" name="bf_qty" id="bf_qty" class="easyui-numberbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">POR Qty</span>
                <input style="width:40%;" name="por_qty" id="por_qty" class="easyui-numberbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:40%;" name="qty" id="qty" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Price</span>
                <input style="width:40%;" name="price" id="price" class="easyui-numberbox" data-options="precision: 5">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Total</span>
                <input style="width:40%;" name="total" id="total" readonly class="easyui-numberbox" data-options="precision: 5">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:40%;" name="revision" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" class="easyui-textbox">
            </div>
        </fieldset>
    </form>

    <div class="alert alert-info" role="alert">
        If you change the data then approval will start from the beginning
    </div>
</div>

<div id="dlg_terms" class="easyui-dialog" title="Update Terms & Conditions" data-options="closed: true,modal:true" style="width: 40%; height: 500px; top: 0px; padding:10px;">
    <form id="frm_terms" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; padding: 10px; float: left;">
            <legend><b>Setting Terms & Conditions</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Del. Sch NO</span>
                <input style="width:60%;" id="po_no_terms" name="po_no" class="easyui-textbox">
            </div>

            <div style="width:100%;">
                <textarea class="summernote" name="terms" id="terms"></textarea>
            </div>

            <div style="float: left; width: 100%;">
                <br><br>
                <center>
                    <a class="easyui-linkbutton c6" style="width: 200px;" onclick="saved()"><i class="fa fa-save"></i> Save Changes</a>
                </center>
            </div>
        </fieldset>

    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Menu ID</span>
                <input name="id_menu" style="width: 60%;" value="<?= $id_menu ?>" class="easyui-textbox">
            </div>
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
<iframe id="printout" src="<?= base_url('purchase/purchase_order_closing_other/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $('#supplier_po').textbox('clear');
        $('#po_no').textbox('clear');

        $("#po_date").datebox({
            onChange: function(po_date) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_order_closing_other/request_no/" . $id_menu . "/add") ?>/" + btoa(po_date),
                    dataType: "json",
                    success: function(response) {
                        if (response == "") {
                            toastr.info("Please Check Configuration in Serial No First");
                            $("#po_no").textbox('clear');
                        } else {
                            $("#po_no").textbox('setValue', response.code);
                        }
                    }
                });
            }
        });

        $("#po_date").datebox('setValue', "<?= date("Y-m-d") ?>");

        $("#supplier_id").combogrid({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier ID',
                    width: 50
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 200
                }]
            ],
            onSelect: function(val, supp) {
                addTable(supp.id);
                $("#supplier_po").textbox('setValue', supp.supplier_po);
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.datatable == "1") {
                toastr.error("Please Select Details of Del. Sch <br>" + row.po_no);
            } else {
                if (row.status == "0") {
                    $('#dlg_update').dialog('open');
                    $('#frm_update').form('load', row);
                } else {
                    toastr.error("You cannot update this data, because status Del. Sch is closed");
                }
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function readPo() {
        $("#filter_suppliers").combogrid({
            url: '<?php echo base_url('master/supplier_items/readSuppliers'); ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier ID',
                    width: 50
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 200
                }]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            onSelect: function(val, supp) {
                $("#filter_po_no").combobox({
                    url: '<?= base_url('purchase/purchase_order_closing_other/readPono?supplier_id=') ?>' + btoa(supp.id),
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: "Select Del. Sch No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#filter_item_rm").combogrid({
            url: '<?php echo base_url('master/item_rm/readNotRm'); ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part No",
            columns: [
                [{
                    field: 'id',
                    title: 'Part No EBWS',
                    width: 120
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
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $("#filter_po_no").combobox({
            url: '<?= base_url('purchase/purchase_order_closing_other/readPono') ?>',
            valueField: 'po_no',
            textField: 'po_no',
            prompt: "Select Del. Sch No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    function saved() {
        $('#frm_terms').form('submit', {
            url: '<?= base_url('purchase/purchase_order_closing_other/updateTerms') ?>',
            method: 'POST',
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
            }
        });
    }

    function addTable(supplier_id, link = "") {
        var lastIndex;
        $('#dg_request').datagrid({
            singleSelect: true,
            columns: [
                [{
                    field: 'delete',
                    width: 120,
                    readonly: true,
                    halign: 'center',
                    title: "Action",
                    formatter: removebtn
                }, {
                    field: 'item_rm_id',
                    width: 150,
                    halign: 'center',
                    title: "Part No EBWS",
                    editor: {
                        type: 'combogrid',
                        options: {
                            // url: '<?= base_url('master/supplier_items/readItem') ?>?supplier_id=' + supplier_id,
                            url: '<?= base_url('master/item_rm/readNotAsset') ?>',
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
                                var dg = $('#dg_request');
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

                                // var ed3 = dg.datagrid('getEditor', {
                                //     index: rowIndex,
                                //     field: 'price'
                                // });

                                // var ed4 = dg.datagrid('getEditor', {
                                //     index: rowIndex,
                                //     field: 'total'
                                // });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.name);
                                // $(ed3.target).numberbox('setValue', rows.price);
                                // $(ed4.target).numberbox('setValue', 0);
                            }
                        }
                    }
                }, {
                    field: 'item_rm_number',
                    width: 200,
                    readonly: true,
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
                    readonly: true,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
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
                            precision: 0,
                            onChange: function(value) {
                                var dg = $('#dg_request');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var price = $(ed.target).numberbox('getValue');
                                $(ed2.target).textbox('setValue', (parseFloat(value) * parseFloat(price)));
                            }
                        }
                    }
                }, {
                    field: 'price',
                    width: 100,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 5,
                            onChange: function(value) {
                                var dg = $('#dg_request');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var qty = $(ed.target).numberbox('getValue');
                                $(ed2.target).textbox('setValue', (parseFloat(qty) * parseFloat(value)));
                            }
                        }
                    },
                    formatter: numberformat
                }, {
                    field: 'total',
                    width: 100,
                    halign: 'center',
                    title: "Total",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                            precision: 2
                        }
                    },
                    formatter: numberformatDefault
                }, {
                    field: 'delivery_date',
                    width: 120,
                    halign: 'center',
                    title: "Delivery Date",
                    editor: {
                        type: 'datebox',
                        options: {
                            formatter: myformatter,
                            parser: myparser,
                            editable: false,
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
            onBeforeEdit: function(index, row) {
                row.editing = true;
                $(this).datagrid('refreshRow', index);
            },
            onAfterEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
            onCancelEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
            // onClickRow: function(rowIndex) {
            //     $(this).datagrid('beginEdit', rowIndex);
            // },
            // onBeginEdit: function(rowIndex, row) {
            //     var editors = $('#dg_request').datagrid('getEditors', rowIndex);
            //     var delivery_date = $(editors[6].target);
            //     var po_date = $("#po_date").datebox('getValue');

            //     delivery_date.add(delivery_date).datebox({
            //         onChange: function() {
            //             var f_delivery_date = delivery_date.datebox('getValue');
            //             if (f_delivery_date < po_date) {
            //                 delivery_date.datebox('clear');
            //                 toastr.warning("Po Date > Expected Date");
            //             }
            //         }
            //     });

            //     delivery_date.datebox('setValue', row.delivery_date);
            // }
        });
    }

    function removebtn(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
            return s + c;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
            var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';
            return e + d;
        }
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg_request').datagrid('validateRow', editIndex)) {
            $('#dg_request').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        if (endEditing()) {
            var supplier_id = $("#supplier_id").combogrid('getValue');

            if (supplier_id != "") {
                $('#dg_request').datagrid('appendRow', {
                    "action": 0,
                    "qty": 0,
                    "price": 0,
                    "total": 0,
                });

                editIndex = $('#dg_request').datagrid('getRows').length - 1;
                $('#dg_request').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            } else {
                toastr.error("Please Choose Supplier No");
            }
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg_request').datagrid('selectRow', getRowIndex(target));
        $('#dg_request').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                $('#dg_request').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg_request').datagrid('endEdit', getRowIndex(target));
    }

    function cancelrow(target) {
        $('#dg_request').datagrid('cancelEdit', getRowIndex(target));
    }

    function btnTerms(val, row) {
        var details = "terms('" + row.po_no + "')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="' + details + '"><i class="fa fa-edit"></i> Edit</a>';
    }

    function terms(po_no) {
        $("#dlg_terms").dialog('open');
        $("#po_no_terms").textbox('setValue', po_no);

        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_order_closing_other/readTerm') ?>",
            data: "po_no=" + po_no,
            dataType: "json",
            success: function(json) {
                if (json.terms == null) {
                    $(".summernote").summernote("code",
                        "<table>" +
                        "<tr>" +
                        "<td width='100'>Delivery</td>" +
                        "<td width='200'></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<td>Warranty</td>" +
                        "<td></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<td>Payment Term</td>" +
                        "<td></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<td>Note</td>" +
                        "<td></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<td>P & F</td>" +
                        "<td></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<td>Installation</td>" +
                        "<td></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<td>Machine Training</td>" +
                        "<td></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<td>Spare Part</td>" +
                        "<td></td>" +
                        "</tr>" +
                        "</table>"
                    );
                } else {
                    $(".summernote").summernote("code", json.terms);
                }
            }
        });
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
                            toastr.error("Please Select Detail of Del. Sch <br>" + row.po_no);
                        } else {
                            if (row.status == "0") {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('purchase/purchase_order_closing_other/delete') ?>',
                                    data: {
                                        id: row.id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        $('#dg').treegrid('reload');
                                    }
                                });
                            } else {
                                toastr.error("You cannot update this data, because status Del. Sch is closed");
                            }
                        }
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

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_onetime_purchase_order.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_suppliers=" + filter_suppliers + "&filter_item_rm=" + filter_item_rm;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_order_closing_other/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'po_no',
            singleSelect: false,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_order_closing_other/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_suppliers=" + filter_suppliers + "&filter_item_rm=" + filter_item_rm;

        window.location.assign('<?= base_url('purchase/purchase_order_closing_other/print/excel') ?>' + url);
    }

    function print_po() {
        var rows = $('#dg').treegrid('getSelected');
        if (rows) {
            if (rows.approved_to == "") {
                window.open("<?= base_url('purchase/purchase_order_closing_other/print_po/') ?>" + window.btoa(rows.po_no), "_blank");
            } else {
                toastr.error("Approval Still Checking!!");
            }
        } else {
            toastr.warning("Please select data in the table first!");
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        readPo();
        filter();

        $("#delivery_date").datebox({
            onChange: function() {
                var po_date = $("#po_date_update").datebox('getValue');
                var delivery_date = $("#delivery_date").datebox('getValue');
                if (delivery_date < po_date) {
                    $("#delivery_date").datebox('clear');
                    toastr.warning("Po Date > Delivery Date");
                }
            }
        });

        $('.summernote').summernote({
            height: 280,
            hintMode: 'words',
            hintSelect: 'next',
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']]
            ]
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var po_date = $("#po_date").datebox('getValue');
                    var po_no = $("#po_no").textbox('getValue');
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    // var attachment = $("#attachment").textbox('getValue');

                    if (po_date == "" || supplier_id == "") {
                        toastr.warning('Please select Po Date', 'Required');
                    } else {
                        $('#dg_request').datagrid('acceptChanges');
                        var rows = $('#dg_request').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();

                        if (totalrows > 0) {
                            $.messager.confirm('Warning', 'Are you sure you want to Save this data?', function(r) {
                                if (r) {

                                    $.ajax({
                                        type: "post",
                                        url: "<?= base_url("purchase/purchase_order_closing_other/request_no/" . $id_menu . "/add") ?>/" + btoa(po_date),
                                        dataType: "json",
                                        success: function(response) {
                                            if (response == "") {
                                                toastr.info("Please Check Configuration in Serial No First");
                                                return false;
                                            } else {
                                                var po_no = response.code;
                                            }

                                            for (var i = 0; i < totalrows; i++) {
                                                var row = rows[i];
                                                if (row.price > 0) {
                                                    $.ajax({
                                                        type: "post",
                                                        url: '<?= base_url('purchase/purchase_order_closing_other/create') ?>',
                                                        data: {
                                                            po_no: po_no,
                                                            po_date: po_date,
                                                            supplier_id: supplier_id,
                                                            // attachment: attachment,
                                                            item_rm_id: row.item_rm_id,
                                                            delivery_date: row.delivery_date,
                                                            type: 'CL',
                                                            qty: row.qty,
                                                            price: row.price,
                                                            total: row.total,
                                                            remarks: row.remarks,
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
                                                } else {
                                                    toastr.error("Price is 0, please Check in Supplier Contract");
                                                }
                                            }

                                            readPo();
                                            $('#dg').treegrid('reload');
                                            $('#dlg_insert').dialog('close');
                                        }
                                    });
                                }
                            });
                        } else {
                            toastr.warning("Please select one of the data in the table first!", "Information");
                        }
                    }
                }
            }]
        });

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('purchase/purchase_order_closing_other/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('purchase/purchase_order_closing_other/upload') ?>',
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
                                url: "<?= base_url('purchase/purchase_order_closing_other/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('purchase/purchase_order_closing_other/uploadCreate') ?>",
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
                                                    url: "<?= base_url('purchase/purchase_order_closing_other/uploadcreateFailed') ?>",
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

        //Update Data
        $('#dlg_update').dialog({
            buttons: [{
                text: 'Send to Approval',
                iconCls: 'icon-ok',
                handler: function() {
                    var por_qty = $("#por_qty").numberbox('getValue');
                    var qty = $("#qty").numberbox('getValue');

                    if (parseInt(por_qty) >= parseInt(qty)) {
                        toastr.error("POR qty > Qty PO");
                        return false;
                    }

                    $('#frm_update').form('submit', {
                        url: '<?= base_url('purchase/purchase_order_closing_other/update') ?>',
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
                        }
                    });
                }
            }]
        });

        $("#qty").numberbox({
            onChange: function(val) {
                var price = $("#price").numberbox('getValue');
                $("#total").numberbox('setValue', (price * val));
            }
        });

        $("#price").numberbox({
            onChange: function(val) {
                var qty = $("#qty").numberbox('getValue');
                $("#total").numberbox('setValue', (qty * val));
            }
        });

        // $('#attachment_upload').filebox({
        //     buttonText: 'Browse File',
        //     accept: '.jpg, .png, .pdf',
        //     onChange: function () {
        //         var files = $(this).filebox('files');
        //         var formData = new FormData();

        //         for (var i = 0; i < files.length; i++) {
        //             var file = files[i];
        //             formData.append('file', file, file.name);
        //         }

        //         $.ajax({
        //             url: '<?= base_url('purchase/purchase_order_closing_other/uploadatt') ?>',
        //             type: 'post',
        //             data: formData,
        //             contentType: false,
        //             processData: false,
        //             dataType: 'json',
        //             success: function (data) {
        //                 if (data.success == true) {
        //                     toastr.success(data.message);
        //                     $('#attachment').textbox('setValue', data.filename); // Mengatur nilai pada textbox
        //                 } else {
        //                     toastr.error(data.message);
        //                 }
        //             }
        //         });
        //     }
        // });

        // $('#attachment_upload2').filebox({
        //     buttonText: 'Browse File',
        //     accept: '.jpg, .png, .pdf',
        //     onChange: function () {
        //         var files = $(this).filebox('files');
        //         var formData = new FormData();

        //         for (var i = 0; i < files.length; i++) {
        //             var file = files[i];
        //             formData.append('file', file, file.name);
        //         }

        //         $.messager.progress({
        //             title:'Please waiting',
        //             msg:'Uploading data...'
        //         });

        //         $.ajax({
        //             url: '<?= base_url('purchase/purchase_order_closing_other/uploadatt') ?>',
        //             type: 'post',
        //             data: formData,
        //             contentType: false,
        //             processData: false,
        //             dataType: 'json',
        //             success: function (data) {
        //                 if (data.success == true) {
        //                     toastr.success(data.message);
        //                     $('#attachment2').textbox('setValue', data.filename); // Mengatur nilai pada textbox
        //                 } else {
        //                     toastr.error(data.message);
        //                 }

        //                 $.messager.progress('close');
        //             }
        //         });
        //     }
        // });
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
    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg_request').datagrid('validateRow', editIndex)) {
            $('#dg_request').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function numberformatDefault(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformat(value, row) {
        if (row.currency == "INR") {
            var digit = 4;
        } else {
            var digit = 5;
        }

        const formatter = new Intl.NumberFormat("ja-JP", {
            minimumFractionDigits: digit
        });
        return "<b>" + formatter.format(value) + "</b>";
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

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return '';
        } else if (row.approved_to == "") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == null) {
            return '';
        } else if (row.approved_to == "") {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function btnDetails(val, row, index) {
        var attachment = row.attachment;

        if (row.datatable == 1) {
            if (attachment != null && attachment != "") {
                return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('assets/documents/purchase_orders/') ?>' + row.attachment + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
            } else {
                return '-';
            }
        }
    }
</script>