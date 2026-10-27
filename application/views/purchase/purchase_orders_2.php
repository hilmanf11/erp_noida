<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'po_no',width:180,halign:'center'">Del. Sch No</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'request_no',width:150,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'po_date',width:100,align:'center'">Del. Sch Date</th>
            <th rowspan="2" data-options="field:'supplier_po',width:150,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:100,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:150,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'asset_code',width:100,halign:'center'">Asset Code</th>
            <th rowspan="2" data-options="field:'maker',width:160,halign:'center'">Maker</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformatDefault">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'total_price',width:120,halign:'center',align:'right',formatter:numberformatPrice2">Total</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'delivery_date',width:100,align:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'terms',width:100,halign:'center',formatter:btnTerms">Terms<br>Condition</th>
            <th colspan="4" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'status_approved',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:120,align:'center'">By</th>
            <th data-options="field:'approved_to',width:120,align:'center'">Next</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 195px; padding:10px;">
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
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_suppliers" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_po()"><i class="fa fa-print"></i> Purchase Order</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" id="filter_pr_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Del. Sch No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_no" class="easyui-combogrid">
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
<div id="dlg_insert" class="easyui-dialog" title="Convert Purchase Request to Purchase Order" data-options="closed: true,modal:true" style="width: 90%; height: 550px; padding:10px; top: 10px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Del. Sch Date</span>
                    <input style="width:60%;" name="po_date" id="po_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Del. Sch No</span>
                    <input style="width:60%;" name="po_no" id="po_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:25%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Suppliers</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" class="easyui-combobox" required>
                </div>
            </div>
        </fieldset>

        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Request Data" toolbar="#toolbar2" data-options="rownumbers: true">
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
                <span style="width:35%; display:inline-block;">PR No</span>
                <input style="width:60%;" name="request_no" readonly class="easyui-textbox">
            </div>
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
                <span style="width:35%; display:inline-block;">Maker</span>
                <input style="width:60%;" name="maker" class="easyui-textbox">
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
                <input style="width:40%;" name="price" id="price" readonly class="easyui-numberbox" data-options="precision: 5">
                <a href="javascript:;" class="easyui-linkbutton" onclick="changePrice()"><i class="fa fa-rotate-left"></i> Sync</a>
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
        If you change the qty then the qty in the <b>PURCHASE REQUESTS</b> Will change
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

<!-- UPDATE SIGNATURE -->
<div id="dlg_approval" class="easyui-dialog" title="Edit Signature" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_approval" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Prepared By</span>
                <input style="width:60%;" name="po_prepared" id="po_prepared" value="<?= $approval->po_prepared ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Checked By</span>
                <input style="width:60%;" name="po_checked" id="po_checked" value="<?= $approval->po_checked ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approved By</span>
                <input style="width:60%;" name="po_approved" id="po_approved" value="<?= $approval->po_approved ?>" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/purchase_orders_2/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $('#request_no').combobox('clear');
        $('#po_no').textbox('clear');

        $("#po_date").datebox({
            onChange: function(val) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_orders_2/request_no/" . $id_menu . "/add") ?>/" + btoa(val),
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

        $('#supplier_id').combobox({
            url: '<?php echo base_url('master/supplier_item_others/readSuppliers'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Supplier',
            onSelect: function(supp) {
                $("#request_no").combobox({
                    url: '<?= base_url('purchase/purchase_request_others/readRequestno/') ?>' + btoa(supp.id),
                    valueField: 'request_no',
                    textField: 'request_no',
                    prompt: "Select Purchase Request No"
                });
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

    function changePrice() {
        var id = $("#id").textbox('getValue');
        var qty = $("#qty").numberbox('getValue');

        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_orders_2/changePrice') ?>",
            data: "id=" + id,
            dataType: "json",
            success: function(json) {
                if (parseFloat(json.price) > 0) {
                    toastr.success("Price Changed!");
                    $("#price").numberbox('setValue', json.price);
                    $("#total").numberbox('setValue', (parseFloat(qty) * parseFloat(json.price)));
                } else {
                    toastr.error("Price Cannot Change, Please cek data in Master Supplier Contract");
                }
            }
        });
    }

    //Add Signature
    function signatures() {
        $('#dlg_approval').dialog('open');
    }

    function readPo() {
        $("#filter_suppliers").combobox({
            url: '<?php echo base_url('master/supplier_item_others/readSuppliers'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(supp) {
                $("#filter_po_no").combobox({
                    url: '<?= base_url('purchase/purchase_orders_2/readPono?supplier_id=') ?>' + supp.id,
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: "Select Purchase Order No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $("#filter_pr_no").combobox({
                    url: '<?= base_url('purchase/purchase_orders_2/readPrno?supplier_id=') ?>' + supp.id,
                    valueField: 'request_no',
                    textField: 'request_no',
                    prompt: "Select Request No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#filter_po_no").combobox({
            url: '<?= base_url('purchase/purchase_orders_2/readPono') ?>',
            valueField: 'po_no',
            textField: 'po_no',
            prompt: "Select Purchase Order No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_pr_no").combobox({
            url: '<?= base_url('purchase/purchase_orders_2/readPrno') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Select Request No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_item_no').combogrid({
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
    }

    function saved() {
        $('#frm_terms').form('submit', {
            url: '<?= base_url('purchase/purchase_orders_2/updateTerms') ?>',
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

    function preview() {
        var supplier_id = $("#supplier_id").combobox('getValue');

        if (supplier_id == "") {
            toastr.warning('Please select Supplier', 'Required');
        } else {
            $.ajax({
                type: "post",
                url: "<?= base_url("purchase/purchase_orders_2/checkSupplier") ?>",
                data: "supplier_id=" + mrp_supplier,
                dataType: "json",
                success: function(response) {
                    if (response.theme == "success") {
                        addTable(supplier_id);
                    } else {
                        toastr.error(response.message);
                        return false;
                    }
                }
            });

            function addTable(supplier_id, link = "") {
                var lastIndex;
                $('#dg_request').datagrid({
                    singleSelect: true,
                    url: '<?= base_url('purchase/purchase_request_others/reads') ?>?supplier_id=' + btoa(supplier_id),
                    columns: [
                        [{
                            field: 'delete',
                            width: 120,
                            readonly: true,
                            halign: 'center',
                            title: "Action",
                            formatter: removebtn
                        }, {
                            field: 'request_no',
                            width: 150,
                            readonly: true,
                            halign: 'center',
                            title: "Request No",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
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
                                    url: '<?= base_url('purchase/purchase_request_others/readRm') ?>?supplier_id=' + btoa(supplier_id),
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

                                        var ed3 = dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'price'
                                        });

                                        var ed4 = dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'total'
                                        });

                                        $(ed.target).textbox('setValue', rows.number);
                                        $(ed2.target).textbox('setValue', rows.name);
                                        $(ed3.target).numberbox('setValue', rows.price);
                                        $(ed4.target).numberbox('setValue', 0);
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
                            field: 'maker',
                            width: 150,
                            readonly: true,
                            halign: 'center',
                            title: "Mker",
                            editor: {
                                type: 'textbox',
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
                                    readonly: true,
                                    precision: 5
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
                            field: 'asset_code',
                            width: 120,
                            halign: 'center',
                            title: "Asset Code",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'asset_code_sub',
                            width: 120,
                            halign: 'center',
                            hidden: true,
                            title: "Asset Code Sub",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
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
                    onLoadSuccess: function(row) {
                        // console.log(rowIndex);
                        if (row.total == 0) {
                            toastr.error("Data Purchase Request Not Found, Please Check Data Supplier Contract, Approval Purchase Request or Asset Code still Empty");
                        }
                    },
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
        }
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
            var request_no = $("#request_no").combobox('getValue');

            if (request_no != "") {
                $('#dg_request').datagrid('appendRow', {
                    "action": 0,
                    "qty": 0,
                    "price": 0,
                    "total": 0,
                });

                editIndex = $('#dg_request').datagrid('getRows').length - 1;
                $('#dg_request').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            } else {
                toastr.error("Please Choose Purchase Request No");
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
        if (row.datatable == 1) {
            var details = "terms('" + row.po_no + "')";
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="' + details + '"><i class="fa fa-edit"></i> Edit</a>';
        }
    }

    function terms(po_no) {
        $("#dlg_terms").dialog('open');
        $("#po_no_terms").textbox('setValue', po_no);

        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_orders_2/readTerm') ?>",
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
                                    url: '<?= base_url('purchase/purchase_orders_2/delete') ?>',
                                    data: {
                                        id: row.id,
                                        request_no: row.request_no,
                                        item_rm_id: row.item_rm_id
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

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_pr_no = $("#filter_pr_no").combobox('getValue');
        var filter_item_no = $("#filter_item_no").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_suppliers=" + filter_suppliers + "&filter_pr_no=" + filter_pr_no + "&filter_item_no=" + filter_item_no;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_orders_2/datatables') ?>' + url,
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
        $("#printout").attr('src', '<?= base_url('purchase/purchase_orders_2/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_pr_no = $("#filter_pr_no").combobox('getValue');
        var filter_item_no = $("#filter_item_no").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_suppliers=" + filter_suppliers + "&filter_pr_no=" + filter_pr_no + "&filter_item_no=" + filter_item_no;

        window.location.assign('<?= base_url('purchase/purchase_orders_2/print/excel') ?>' + url);
    }

    function print_po() {
        var rows = $('#dg').treegrid('getSelected');
        if (rows) {
            if (rows.approved_to == "") {
                window.open("<?= base_url('purchase/purchase_orders_2/print_po/') ?>" + window.btoa(rows.po_no), "_blank");
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

        $("#add").html("Convert PR to PO");
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
                text: 'Save & Send to Approval',
                iconCls: 'icon-ok',
                handler: function() {
                    var po_date = $("#po_date").datebox('getValue');
                    // var request_no = $("#request_no").combobox('getValue');
                    var po_no = $("#po_no").textbox('getValue');
                    var supplier_id = $("#supplier_id").combobox('getValue');

                    if (po_date == "") {
                        toastr.warning('Please select Po Date', 'Required');
                    } else {
                        $('#dg_request').datagrid('acceptChanges');
                        var rows = $('#dg_request').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();

                        if (totalrows > 0) {
                            $.messager.confirm('Warning', 'Are you sure you want to Convert and Save PR to PO?', function(r) {
                                if (r) {
                                    for (var i = 0; i < totalrows; i++) {
                                        var row = rows[i];
                                        if (row.price <= 0) {
                                            toastr.error("Price is 0, please Check in Supplier Contract");
                                            return false;
                                        }
                                    }

                                    for (var i = 0; i < totalrows; i++) {
                                        var row = rows[i];
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('purchase/purchase_orders_2/create') ?>',
                                            data: {
                                                po_no: po_no,
                                                po_date: po_date,
                                                supplier_id: supplier_id,
                                                request_no: row.request_no,
                                                item_rm_id: row.item_rm_id,
                                                maker: row.maker,
                                                delivery_date: row.delivery_date,
                                                qty: row.qty,
                                                price: row.price,
                                                total: row.total,
                                                asset_code: row.asset_code,
                                                asset_code_sub: row.asset_code_sub,
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
                                    }

                                    // readPo();
                                    $('#dg').treegrid('reload');
                                    $('#dlg_insert').dialog('close');
                                }
                            });
                        } else {
                            toastr.warning("Please select one of the data in the table first!", "Information");
                        }
                    }
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
                    var bf_qty = $("#bf_qty").numberbox('getValue');
                    var qty = $("#qty").numberbox('getValue');

                    if (parseInt(por_qty) >= parseInt(qty)) {
                        toastr.error("POR qty >= Qty PO");
                        return false;
                    }

                    $('#frm_update').form('submit', {
                        url: '<?= base_url('purchase/purchase_orders_2/update') ?>',
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
                            $('#dlg_approval').dialog('close');
                        }
                    });
                }
            }]
        });

        $('#dlg_approval').dialog({
            buttons: [{
                text: 'Update Data',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_approval').form('submit', {
                        url: '<?= base_url('purchase/purchase_orders_2/update_approval') ?>',
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
                            $('#dlg_approval').dialog('close');
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
    });

    // function getRowIndex(target) {
    //     var tr = $(target).closest('tr.datagrid-row');
    //     return parseInt(tr.attr('datagrid-row-index'));
    // }

    // function editrow(target) {
    //     $('#dg_request_update').datagrid('beginEdit', getRowIndex(target));
    // }

    // function saverow(target) {
    //     $('#dg_request_update').datagrid('endEdit', getRowIndex(target));
    // }

    // function buttonEdit(value, row, index) {
    //     if (row.editing) {
    //         var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a>';
    //         return s;
    //     } else {
    //         var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a>';
    //         return e;
    //     }
    // }

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

    function numberformatPrice2(value, row) {
        if (row.supplier_type == "LOCAL") {
            var decimal = 2;
        } else {
            var decimal = 4;
        }

        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: decimal
        });

        return "<b>" + formatter.format(value) + "</b>";
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
</script>