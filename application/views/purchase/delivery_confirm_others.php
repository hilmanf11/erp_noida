<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'delivery_no',width:150,halign:'center'">Delivery No</th>
            <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Invoice No</th>
            <th rowspan="2" data-options="field:'invoice_date',width:100,halign:'center'">Invoice Date</th>
            <th rowspan="2" data-options="field:'po_no',width:120,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'po_date',width:100,halign:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:100,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'item_family_name',width:100,halign:'center'">Product<br>Family</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty PO</th>
            <th rowspan="2" data-options="field:'delivery',width:100,halign:'center',align:'right',formatter:numberformat">Plan Delivery</th>
            <!-- <th rowspan="2" data-options="field:'receipt',width:100,halign:'center',align:'right',formatter:numberformat">Receipt</th> -->
            <th rowspan="2" data-options="field:'etd',width:100,align:'center'">ETD</th>
            <th rowspan="2" data-options="field:'eta',width:100,align:'center'">ETA</th>
            <th rowspan="2" data-options="field:'attachment',width:100,halign:'center', formatter:btnAttachment">Attahment</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,halign:'center'">By</th>
            <th data-options="field:'created_date',width:150,align:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,halign:'center'">By</th>
            <th data-options="field:'updated_date',width:150,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="toolbar" style="height: 200px; padding: 10px;">
    <fieldset style="width:100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        
    <legend><b>Filter Data</b></legend>
        <div style="width:35%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Create Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 30%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">PO Date</span>
                <input style="width:60%;" id="filter_po_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">PO Status</span>
                <select style="width:30%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">All</option>
                    <option value="0">Open</option>
                    <option value="1">Close</option>
                </select>
            </div>
        </div>
        <div style="width: 30%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_product_family" class="easyui-combobox" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 98%; height: 550px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier name</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note</span>
                    <input style="width:60%;" name="delivery_no" id="delivery_no" required class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="u_attachment" id="u_attachment" class="easyui-filebox" data-options="prompt:'Choose Attachment', accept:'.png, .jpg, .jpeg, .pdf'">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice No</span>
                    <input style="width:60%;" name="invoice_no" id="invoice_no" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice Date</span>
                    <input style="width:60%;" name="invoice_date" id="invoice_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">AWB No</span>
                    <input style="width:60%;" name="awb_no" id="awb_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">AWB Date</span>
                    <input style="width:60%;" name="awb_date" id="awb_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Delivery Confirmation Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 60%; max-height: 550px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_update" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">ID</span>
                    <input style="width:60%;" name="id" id="id" readonly class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Supplier ID</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier name</span>
                    <input style="width:60%;" name="supplier_name" id="supplier_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Part No EBWS</span>
                    <input style="width:60%;" name="item_rm_id" id="item_rm_id" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No Supplier</span>
                    <input style="width:60%;" name="item_rm_number" id="item_rm_number" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice No</span>
                    <input style="width:60%;" name="invoice_no" id="invoice_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice Date</span>
                    <input style="width:60%;" name="invoice_date" id="invoice_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="po_no" id="po_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO Date</span>
                    <input style="width:60%;" name="po_date" id="invoice_date" readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery No</span>
                    <input style="width:60%;" name="delivery_no" id="delivery_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BOE No</span>
                    <input style="width:60%;" name="awb_no" id="awb_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BOE Date</span>
                    <input style="width:60%;" name="awb_date" id="awb_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Uom</span>
                    <input style="width:60%;" name="uom" id="uom" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty PO</span>
                    <input style="width:60%;" name="qty_po" id="qty_po" class="easyui-numberbox" disabled data-options="precision: 2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty Remain</span>
                    <input style="width:60%;" name="qty_remain" id="qty_remain" class="easyui-numberbox" disabled data-options="precision: 2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice Qty</span>
                    <input style="width:60%;" name="delivery" id="delivery" class="easyui-numberbox" data-options="precision: 2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Balance Qty</span>
                    <input style="width:60%;" name="balance" id="balance" class="easyui-numberbox" disabled data-options="precision: 2">
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
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
        $('#dg2').datagrid('loadData', []);
        $("#supplier_id").combogrid('enable');
        $("#delivery_no").textbox('enable');
        $("#awb_date").datebox("setValue", "<?= date("Y-m-d") ?>");
        // $("#invoice_no").textbox('enable');
        // $("#invoice_date").datebox('enable');
    }

    function addTable(supplier_id, link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'po_no',
                    width: 200,
                    halign: 'center',
                    title: "PO No",
                    editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('purchase/purchase_orders_2/readPono?supplier_id='); ?>' + btoa(supplier_id),
                            valueField: 'po_no',
                            textField: 'po_no',
                            prompt: 'Choose Purchase Order',
                            required: true,
                            onSelect: function (po) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {index: rowIndex, field: 'item_rm_id'});
                                var ed2 = dg.datagrid('getEditor', {index: rowIndex, field: 'item_rm_number'});
                                var ed3 = dg.datagrid('getEditor', {index: rowIndex, field: 'item_rm_name'});
                                var ed4 = dg.datagrid('getEditor', {index: rowIndex, field: 'po_date'});
                                var ed5 = dg.datagrid('getEditor', {index: rowIndex, field: 'uom'});
                                var ed6 = dg.datagrid('getEditor', {index: rowIndex, field: 'eta'});
                                var ed7 = dg.datagrid('getEditor', {index: rowIndex, field: 'etd'});
                                var ed8 = dg.datagrid('getEditor', {index: rowIndex, field: 'qty'});
                                var ed9 = dg.datagrid('getEditor', {index: rowIndex, field: 'delivery'});
                                var ed10 = dg.datagrid('getEditor', {index: rowIndex, field: 'actual'});
                                var ed11 = dg.datagrid('getEditor', {index: rowIndex, field: 'balance'});
                                var ed12 = dg.datagrid('getEditor', {index: rowIndex, field: 'qty_po'});

                                $(ed4.target).textbox('setValue', po.po_date);
                                $(ed.target).combogrid({
                                    url: '<?= base_url('purchase/delivery_confirm_others/readProducts/'); ?>' + btoa(po.po_no),
                                    required: true,
                                    panelWidth: 600,
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
                                            field: 'qty',
                                            title: 'Remain',
                                            width: 80
                                        }]
                                    ],
                                    onSelect: function (value, item) {
                                        $(ed2.target).textbox('setValue', item.number);
                                        $(ed3.target).textbox('setValue', item.name);
                                        $(ed5.target).textbox('setValue', item.uom);
                                        $(ed6.target).textbox('setValue', item.delivery_date);
                                        $(ed7.target).datebox('setValue', item.today);
                                        $(ed8.target).numberbox('setValue', item.qty);
                                        $(ed9.target).numberbox('setValue', item.delivery);
                                        $(ed10.target).numberbox('setValue', item.actual);
                                        $(ed11.target).numberbox('setValue', (parseInt(item.qty) - parseInt(item.delivery)));
                                        $(ed12.target).numberbox('setValue', item.qty_po);
                                        $(ed9.target).numberbox({
                                            onChange: function(val){
                                                if((parseInt(item.qty) - val) <= 0){
                                                    toastr.info("You cannot exceed qty and delivery");
                                                    $(ed11.target).numberbox('setValue', 0);
                                                }else{
                                                    $(ed11.target).numberbox('setValue', (parseInt(item.qty) - val));
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    }
                }, {
                    field: 'po_date',
                    width: 100,
                    halign: 'center',
                    title: "PO Date",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                },{
                    field: 'item_rm_id',
                    width: 200,
                    halign: 'center',
                    title: "Part No",
                    editor: {
                        type: 'combogrid',
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
                    hidden: true,
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                },{
                    field: 'eta',
                    width: 100,
                    halign: 'center',
                    title: "ETA",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'etd',
                    width: 120,
                    halign: 'center',
                    title: "ETD",
                    editor: {
                        type: 'datebox',
                        options: {
                            formatter:myformatter,
                            parser:myparser,
                            required: true
                        }
                    }
                }, {
                    field: 'qty_po',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "PO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'actual',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "Receipt",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "Remain",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                },{
                    field: 'delivery',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    title: "Invoice Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            onChange: function(val){
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed1 = dg.datagrid('getEditor', {index: rowIndex, field: 'qty'});
                                var ed2 = dg.datagrid('getEditor', {index: rowIndex, field: 'balance'});
                                var ed3 = dg.datagrid('getEditor', {index: rowIndex, field: 'delivery'});

                                var item = $(ed1.target).numberbox('getValue');

                                if((parseInt(item) - val) <= 0){
                                    toastr.info("You cannot exceed qty remain and qty invoice");
                                    $(ed2.target).numberbox('setValue', 0);
                                    // $(ed3.target).numberbox('setValue', 0);
                                }else{
                                    $(ed2.target).numberbox('setValue', (parseInt(item) - val));
                                }
                            }
                        }
                    }
                },{
                    field: 'balance',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "Balance",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                },{
                    field: 'id',
                    width: 80,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox',
                    }
                }]
            ],
            onClickCell: onClickCell
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

    function onClickCell(index, field) {
        if (editIndex != index) {
            if (endEditing()) {
                $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    $('#dg2').datagrid('selectRow', editIndex);
                }, 0);
            }
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
            toastr.error("Please Choose Supplier No first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if(row.status == "1"){
                toastr.error("Cannot Update for this data because the status is CLOSED");
            }else{
                // $('#dlg_insert').dialog('open');
                // $('#frm_insert').form('load', row);
                // $("#supplier_id").combogrid('disable');
                // $("#delivery_no").textbox('disable');
                // $("#invoice_no").textbox('disable');
                // $("#invoice_date").datebox('disable');

                // addTable(row.supplier_id, '<?= base_url('purchase/delivery_confirm_others/datatableUpdates?delivery_no=') ?>' + window.btoa(row.delivery_no));

                $('#dlg_update').dialog('open');
                $('#frm_update').form('load', row);

                $("#delivery").numberbox({
                    onChange: function(val){
                        var qty_remain = $("#qty_remain").numberbox('getValue');

                        if((parseFloat(qty_remain) - parseFloat(val)) >= 0){
                            $("#balance").numberbox("setValue", (parseFloat(qty_remain) - parseFloat(val)));
                        }else{
                            toastr.error("Qty Cannot > Qty Remain");
                            $("#delivery").numberbox('setValue', 0);
                        }
                    }
                });
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
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/delivery_confirm_others/delete') ?>',
                                data: {
                                    id: row.id,
                                    po_no: row.po_no,
                                    item_rm_id: row.item_rm_id
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
                                    toastr.error("Delete Failed, Please Check Data Relations");
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

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_delivery_confirm_others.xls') ?>');
    }
    
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_po_date = $("#filter_po_date").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_po_date=" + window.btoa(filter_po_date) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        $('#dg').datagrid({
            url: '<?= base_url('purchase/delivery_confirm_others/datatables') ?>' + url,
        });

        $("#printout").attr('src', '<?= base_url('purchase/delivery_confirm_others/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_po_date = $("#filter_po_date").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_po_date=" + window.btoa(filter_po_date) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        window.location.assign('<?= base_url('purchase/delivery_confirm_others/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            // url: '<?= base_url('purchase/delivery_confirm_others/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true
        });

        filter();

        $("#u_attachment").filebox({
            onChange: function () {
                var attachment = $("#attachment").textbox('getValue');
                var files = $(this).filebox('files')[0];

                var formData = new FormData();
                formData.append('file', files);

                $.ajax({
                    url: "<?= base_url('purchase/delivery_confirm_others/uploadAttachment/') ?>" + btoa(attachment),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        var data = JSON.parse(response);
                        toastr.info(data.msg);
                        $("#attachment").textbox('setValue', data.file);
                    }
                });
            }
        })

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    var delivery_no = $("#delivery_no").textbox('getValue');
                    var invoice_no = $("#invoice_no").textbox('getValue');
                    var invoice_date = $("#invoice_date").datebox('getValue');
                    var awb_no = $("#awb_no").textbox('getValue');
                    var awb_date = $("#awb_date").datebox('getValue');
                    var attachment = $("#attachment").textbox('getValue');

                    if(supplier_id != "" && delivery_no != "" && invoice_no != "" && invoice_date != ""){
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('purchase/delivery_confirm_others/checkInvoice') ?>",
                            data: "invoice_no=" + invoice_no + "&supplier_id=" + supplier_id,
                            dataType: "html",
                            success: function (data) {
                                if(data == "NG"){
                                    toastr.error("Invoice No already created, Please check again");
                                    return false;
                                }
                                
                                $('#dg2').datagrid('acceptChanges');
                                var rows = $('#dg2').datagrid('getRows');
                                var totalrows = rows.length;
                                endEditing();

                                for (let z = 0; z < totalrows; z++) {
                                    var jsonCheck = rows[z];
                                    if((parseFloat(jsonCheck.qty) - parseFloat(jsonCheck.delivery)) < 0){
                                        toastr.error("Please Check Invoice Qty cannot > Remain");
                                        return false;
                                    }

                                    if(parseFloat(jsonCheck.delivery) == 0){
                                        toastr.error("Invoice Qty Still 0");
                                        return false;
                                    }
                                }
                                
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

                                requestData(totalrows, rows);
                                function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                    var i = (number - 1);
                                    if (value < 100) {
                                        value = Math.floor((number / total) * 100);

                                        if(json[i].balance >= 0){
                                            $.ajax({
                                                type: "post",
                                                url: '<?= base_url('purchase/delivery_confirm_others/create') ?>',
                                                data: {
                                                    supplier_id: supplier_id,
                                                    delivery_no: delivery_no,
                                                    invoice_no: invoice_no,
                                                    invoice_date: invoice_date,
                                                    awb_no: awb_no,
                                                    awb_date: awb_date,
                                                    attachment: attachment,
                                                    po_no: json[i].po_no,
                                                    po_date: json[i].po_date,
                                                    item_rm_id: json[i].item_rm_id,
                                                    uom: json[i].uom,
                                                    eta: json[i].eta,
                                                    etd: json[i].etd,
                                                    qty: json[i].qty,
                                                    delivery: json[i].delivery,
                                                    balance: json[i].balance,
                                                    id: json[i].id,
                                                },
                                                dataType: "json",
                                                success: function(result) {
                                                    if (i == (total - 1)) {
                                                        Swal.close();
                                                        Swal.fire({
                                                            title: result.message,
                                                            icon: result.theme,
                                                            confirmButtonText: 'Ok',
                                                            allowOutsideClick: false,
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                // window.location.reload();
                                                                $('#dg').datagrid('reload');
                                                            }
                                                        });
                                                    }

                                                    requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                }
                                            });
                                        }else{
                                            toastr.error("Balance Cannot Minus"); 
                                        }
                                    }
                                }
                            }
                        });
                    }else{
                        toastr.error("Please Complete Your input");    
                    }
                }
            }]
        });

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Update Data',
                iconCls: 'icon-ok',
                handler: function() {
                    var delivery = $("#delivery").numberbox("getValue");

                    if(delivery > 0){
                        $('#frm_update').form('submit', {
                            url: '<?= base_url('purchase/delivery_confirm_others/update') ?>',
                            onSubmit: function() {
                                if($(this).form('validate') === true){
                                    $('#dlg_update').dialog('close');
                                    
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
                    }else{
                        toastr.error("Qty Delivery Cannot 0");
                    }
                }
            }]
        });

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('purchase/delivery_confirm_others/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('purchase/delivery_confirm_others/upload') ?>',
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
                                url: "<?= base_url('purchase/delivery_confirm_others/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('purchase/delivery_confirm_others/uploadCreate') ?>",
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
                                                    url: "<?= base_url('purchase/delivery_confirm_others/uploadcreateFailed') ?>",
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

        $('#filter_item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/readNotRm') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part No EBWS",
            columns: [
                [{
                    field: 'id',
                    title: 'Part No EBWS',
                    width: 150
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

        $("#filter_supplier").combogrid({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            panelWidth: 300,
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
                    width: 200
                }]
            ],
            onSelect: function(val, supp){
                $('#filter_invoice_no').combobox({
                    url: '<?php echo base_url('purchase/delivery_confirm_others/filterInvoiceNo/'); ?>' + btoa(supp.id),
                    valueField: 'invoice_no',
                    textField: 'invoice_no',
                    prompt: 'Choose Invoice No',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_product_family').combobox({
                    url: '<?php echo base_url('purchase/delivery_confirm_others/filterProdfam/'); ?>' + btoa(supp.id),
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Choose Product Family',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_po_no').combobox({
                    url: '<?php echo base_url('purchase/delivery_confirm_others/filterPo/'); ?>' + btoa(supp.id),
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: 'Choose PO No',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#supplier_id").combogrid({
            url: '<?php echo base_url('purchase/purchase_order_others/readSupplier'); ?>',
            panelWidth: 300,
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
                    width: 200
                }]
            ],
            onSelect: function(val, supp){
                addTable(supp.id);
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

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSED';
        }
    };

    function btnAttachment(val){
        if(val == null || val == ""){
            return "-";
        }else{
            return "<a class='btn btn-primary btn-sm' style='pointer-events: auto; opacity:1; width:100%;' target='_blank' href='<?= base_url('assets/documents/delivery_confirm_others/') ?>"+val+"'><i class='fa fa-eye'></i> View</a>";
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }
</script>