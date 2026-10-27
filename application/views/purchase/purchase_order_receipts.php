<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'id',width:230,halign:'center'">Receipt No</th>
        <th data-options="field:'total_scan',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status GRN<br>(Scan In)</th>
        <th data-options="field:'status',width:80,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status<br>Invoice</th>
        <th data-options="field:'qas',width:80,align:'center',formatter:statusformatQas,styler:statusStyleQas">QAS<br>Status</th>
    </thead>
    <thead>
        <tr>
            <!-- <th rowspan="2" data-options="field:'grn_no',width:120,halign:'center'">GRN No</th> -->
            <th rowspan="2" data-options="field:'po_no',width:120,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'receipt_date',width:100,halign:'center'">Receipt Date</th>
            <th colspan="2" data-options="field:'coslpan',halign:'center'">Supplier</th>
            <th colspan="2" data-options="field:'coslpan',halign:'center'">Invoice</th>
            <th rowspan="2" data-options="field:'awb_no',swidth:120,halign:'center'">BOE No</th>
            <th rowspan="2" data-options="field:'awb_date',width:120,halign:'center', formatter: dateFormat">BOE Date</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:150,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'qty_delivery',width:80,halign:'center',align:'right',formatter:numberformat">Qty<br>Receipt</th>
            <th rowspan="2" data-options="field:'qty_receipt',width:80,halign:'center',align:'right',formatter:numberformat">Qty<br>Confirm</th>
            <th rowspan="2" data-options="field:'qty_balance',width:80,halign:'center',align:'right',formatter:numberformat">Qty<br>Balance</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center',align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'currency',width:80,halign:'center',align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'price',width:80,halign:'center',align:'right',formatter:numberformatprice">Price</th>
            <th rowspan="2" data-options="field:'total',width:80,halign:'center',align:'right',formatter:numberformat">Total</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right'">MPQ</th>
            <th rowspan="2" data-options="field:'qty_label',width:80,halign:'center',align:'right'">Qty <br> Label</th>
            <th rowspan="2" data-options="field:'confirm',width:120,align:'center',formatter:BtnConfirmQty">Confirm</th>
            <th rowspan="2" data-options="field:'state',width:100,align:'center',formatter:BtnPrintLabel">Label</th>
            <th rowspan="2" data-options="field:'delabel',width:80,align:'center',formatter:BtnDeleteLabel">Sync</th>
            <th rowspan="2" data-options="field:'nglabel',width:100,align:'center',formatter:BtnNg">NG</th>
            <th rowspan="2" data-options="field:'calabel',width:100,align:'center',formatter:BtnCancel">Cancel</th>
            <!-- <th rowspan="2" data-options="field:'wms',width:120,align:'center',formatter:BtnWms">WMS</th> -->
            <th rowspan="2" data-options="field:'attachment',width:100,halign:'center', formatter:btnAttachment">Attachment</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'supplier_id',width:80,halign:'center'">ID</th>
            <th data-options="field:'supplier_name',width:250,halign:'center'">Name</th>
            <!-- <th data-options="field:'bc_kind',width:80,halign:'center'">Kind</th> -->
            <!-- <th data-options="field:'bc_aju',width:100,halign:'center'">AJU</th> -->
            <th data-options="field:'bc_document',width:150,halign:'center'">Document</th>
            <th data-options="field:'bc_date',width:100,halign:'center', formatter: dateFormat">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 230px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 65%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" id="filter_receipt" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice No</span>
                    <input style="width:60%;" id="filter_doc_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status Invoice</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Select Status</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSED</option>
                        <option value="2">CANCEL</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Print Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Material Receipt No</span>
                <input style="width:55%;" id="filter_receipt_no" class="easyui-combobox">
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_receiving_note()"><i class="fa fa-print"></i></a>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Good Receipt No</span>
                <input style="width:55%;" id="filter_good_receipt_no" class="easyui-combobox">
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_good_receiving_note()"><i class="fa fa-print"></i></a>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="discrepancy()"><i class="fa fa-file"></i> Discrepancy Note</a>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true,fit:true" style="width: 100%; padding:10px; top: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 25%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt Date</span>
                    <input style="width:60%;" name="receipt_date" id="receipt_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" name="receipt_no" id="receipt_no" required readonly class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="u_attachment" id="u_attachment" class="easyui-filebox" data-options="prompt:'Choose Attachment', accept:'.png, .jpg, .jpeg, .pdf'">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">BC Kind</span>
                    <input style="width:60%;" name="bc_kind" id="bc_kind" class="easyui-combobox">
                </div> -->
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">No AJU</span>
                    <input style="width:60%;" name="bc_aju" id="bc_aju" class="easyui-textbox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 25%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="po_no" id="po_no" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 25%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice No</span>
                    <input style="width:60%;" name="bc_document" id="bc_document" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice Date</span>
                    <input style="width:60%;" name="bc_date" id="bc_date" required="" readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 25%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BOE No</span>
                    <input style="width:60%;" name="awb_no" id="awb_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BOE Date</span>
                    <input style="width:60%;" name="awb_date" id="awb_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Order Receipt List" idField="item_rm_number">
            <thead>
                <tr>
                    <!-- <th field="ck" checkbox="true"></th> -->
                    <th data-options="field:'po_no',width:120">PO No</th>
                    <th data-options="field:'item_rm_id',width:120">Part No EBWS</th>
                    <th data-options="field:'item_rm_number',width:200">Part No Supplier</th>
                    <th data-options="field:'item_rm_name',width:200">Description</th>
                    <th data-options="field:'uom',width:80">UoM</th>
                    <th data-options="field:'qty_po',width:80,editor:{type:'numberbox', options:{readonly:true}}">PO</th>
                    <th data-options="field:'qty_delivery',width:80,editor:{type:'numberbox', options:{readonly:true}}">Delivery</th>
                    <th data-options="field:'qty_receipt',width:80,editor:{type:'numberbox', options:{readonly:true}}">Receipt</th>
                    <!-- <th data-options="field:'qty_receipt2',width:80,editor:{type:'numberbox', options:{required:true}}">Receipt</th> -->
                    <!-- <th data-options="field:'qty_balance',width:80,editor:{type:'numberbox', options:{readonly:true}}">Balance</th> -->
                    <th data-options="field:'currency',width:80,editor:{type:'textbox', options:{readonly:true}}">Currency</th>
                    <th data-options="field:'price',width:80,editor:{type:'numberbox', options:{readonly:true, precision:5}}">Price</th>
                    <th data-options="field:'total',width:80,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Total Price</th>
                    <th data-options="field:'mpq',width:80,editor:{type:'numberbox', options:{readonly:true}}">MPQ</th>
                    <th data-options="field:'qty_label',width:80,editor:{type:'numberbox', options:{readonly:true}}">Label</th>
                    <!-- <th colspan="3" data-options="field:'t',width:80,halign:'center'">Warehouse</th> -->
                </tr>
                <!-- <tr>
                    <th data-options="field:'location',width:100,editor:{type:'combogrid', options:{required:true, editable:false}}">Location</th>
                    <th data-options="field:'capacity',width:80,editor:{type:'numberbox', options:{readonly:true}}">Available</th>
                    <th data-options="field:'warehouse',width:80,editor:{type:'numberbox', options:{readonly:true}}">Balance</th>
                </tr> -->
            </thead>
        </table>
    </form>
</div>

<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding: 10px; top: 10px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Id</span>
                <input style="width:60%;" name="receipt_id" id="receipt_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:60%;" name="supplier_name" id="supplier_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No EBWS</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No Supplier</span>
                <input style="width:60%;" name="item_rm_number" id="item_rm_number" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">MPQ</span>
                <input style="width:60%;" name="qty_mpq" id="qty_mpq" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty Receipt</span>
                <input style="width:60%;" name="qty_delivery" id="qty_delivery" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty Confirm</span>
                <input style="width:60%;" name="qty_receipt" id="qty_receipt" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty Balance</span>
                <input style="width:60%;" name="qty_balance" id="qty_balance" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Label</span>
                <input style="width:60%;" name="qty_label" id="qty_label" readonly class="easyui-numberbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- INSERT LABEL -->
<div id="dlg_label" class="easyui-dialog" title="Create Data Label" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Create Label" class="easyui-panel" style="width:100%; height:300px; padding:10px; margin-top: 10px;">
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
        $('#dg_request').datagrid('loadData', []);
        $('#frm_insert').form('clear');
        $('#receipt_date').datebox({
            onChange: function(receipt_date) {
                receipt_no("<?= $id_menu ?>", receipt_date);
            }
        });

        $('#receipt_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $("#supplier_id").combogrid({
            url: '<?= base_url('purchase/delivery_confirm/readSupplier') ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier No',
                    width: 50
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 200
                }]
            ],
            onSelect: function(valsupp, supp) {
                $('#bc_document').combogrid({
                    url: '<?= base_url('purchase/delivery_confirm/readInvoice?supplier_id=') ?>' + btoa(supp.id),
                    panelWidth: 250,
                    idField: 'invoice_no',
                    textField: 'invoice_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Invoice No",
                    columns: [
                        [{
                            field: 'invoice_no',
                            title: 'Invoice No',
                            width: 120
                        }, {
                            field: 'invoice_date',
                            title: 'Invoice Date',
                            width: 110
                        }]
                    ],
                    onSelect: function(val, row) {
                        $("#bc_date").datebox('setValue', row.invoice_date);
                        $("#awb_no").textbox('setValue', row.awb_no);

                        if (row.awb_date == "1899-11-30") {
                            $("#awb_date").datebox('setValue', "<?= date("Y-m-d") ?>");
                        } else {
                            $("#awb_date").datebox('setValue', row.awb_date);
                        }

                        $("#po_no").combobox({
                            url: '<?= base_url('purchase/delivery_confirm/readPono?invoice_no=') ?>' + btoa(row.invoice_no) + "&supplier_id=" + btoa(supp.id),
                            valueField: 'po_no',
                            textField: 'po_no',
                            prompt: "Select PO No",
                        });
                    }
                });
            }
        });
    }

    function confirmQty(id, receipt_id, supplier_name, item_rm_id, item_rm_number, qty_mpq, qty_delivery, qty_receipt, qty_balance, qty_label, confirm) {
        if (confirm == "OK") {
            $.messager.prompt('Confirm Gate Entry', 'Please input The Password', function(r) {
                if (r == "CONFIRMPOR") {
                    $('#dlg_update').dialog('open');
                    return true;
                } else {
                    toastr.error("Password not Match");
                }
            });
        } else {
            $('#dlg_update').dialog('open');
        }

        $("#id").textbox('setValue', id);
        $("#receipt_id").textbox('setValue', receipt_id);
        $("#supplier_name").textbox('setValue', supplier_name);
        $("#item_rm_id").textbox('setValue', item_rm_id);
        $("#item_rm_number").textbox('setValue', item_rm_number);
        $("#qty_mpq").numberbox('setValue', qty_mpq);
        $("#qty_delivery").numberbox('setValue', qty_delivery);
        $("#qty_receipt").numberbox('setValue', qty_receipt);
        $("#qty_balance").numberbox('setValue', qty_balance);
        $("#qty_label").numberbox('setValue', qty_label);

        url_update = '<?= base_url('purchase/purchase_order_receipts/update') ?>';

        $("#qty_receipt").numberbox({
            onChange: function(val) {
                var qty_delivery = $("#qty_delivery").numberbox('getValue');
                var qty_mpq = $("#qty_mpq").numberbox('getValue');
                var label = Math.ceil(val / qty_mpq);
                var balance = (parseFloat(qty_delivery) - parseFloat(val));
                $("#qty_label").numberbox('setValue', label);

                if (parseFloat(balance) > 0) {
                    $("#qty_balance").numberbox('setValue', balance);
                } else {
                    $("#qty_balance").numberbox('setValue', 0);
                    $("#qty_receipt").numberbox('setValue', qty_delivery);
                }
            }
        });
    }

    // function update() {
    // var row = $('#dg').treegrid('getSelected');
    // if (row) {
    //     if(row.receipt_no != row.id){
    //         $('#dlg_update').dialog('open');
    //         $('#frm_update').form('load', row);
    //         url_update = '<?= base_url('purchase/purchase_order_receipts/update') ?>';
    //     }else{
    //         toastr.warning("Please Select Detail in the table!");
    //     }
    // } else {
    //     toastr.warning("Please select one of the data in the table first!", "Information");
    // }

    // $("#qty_receipt").numberbox({
    //     onChange: function(val){
    //         var qty_mpq = $("#qty_mpq").numberbox('getValue');
    //         var label = Math.ceil(val / qty_mpq);
    //         $("#qty_label").numberbox('setValue', label);
    //     }
    // });
    // }

    function discrepancy() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.receipt_no == row.id) {
                window.open("<?= base_url('purchase/purchase_order_receipts/print_discrepancy/') ?>" + window.btoa(row.receipt_no), "_blank");
            } else {
                toastr.warning("Please Select Header in the table!");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function receipt_no(id_menu, receipt_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_order_receipts/receipt_no/') ?>" + id_menu + "/" + btoa(receipt_date),
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                    $("#receipt_no").textbox('clear');
                } else {
                    $("#receipt_no").textbox('setValue', result);
                }
            }
        });
    }

    function preview() {
        var po_no = $("#po_no").combobox('getValue');
        var bc_document = $("#bc_document").combogrid('getValue');
        var bc_date = $("#bc_date").datebox('getValue');

        if (bc_document == "") {
            toastr.warning('Please select Invoice No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: '<?= base_url('purchase/purchase_order_receipts/datatablesTemp') ?>?po_no=' + po_no + "&bc_document=" + btoa(bc_document) + "&bc_date=" + btoa(bc_date),
                fitColumns: true,
                singleSelect: true,
                onLoadSuccess: function(data, rows) {
                    if (data.total === 0) {
                        toastr.warning("Data Not Found, Please Check Data in Item MPQ, PO or OTW");
                    }
                },
                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                    var qty_po = $(editors[0].target);
                    var qty_delivery = $(editors[1].target);
                    var qty_receipt = $(editors[2].target);
                    var qty_receipt2 = $(editors[3].target);
                    var qty_balance = $(editors[4].target);
                    var qty_mpq = $(editors[8].target);
                    var qty_label = $(editors[9].target);

                    qty_whs = 0;

                    // location.combogrid({
                    //     url: '<?= base_url('master/locations/readLocations?type=RM') ?>',
                    //     panelWidth: 460,
                    //     idField: 'number',
                    //     textField: 'number',
                    //     mode: 'remote',
                    //     fitColumns: true,
                    //     prompt: "Choose Location",
                    //     columns: [
                    //         [{
                    //             field: 'number',
                    //             title: 'Code',
                    //             width: 80
                    //         }, {
                    //             field: 'location',
                    //             title: 'Location',
                    //             width: 150
                    //         },{
                    //             field: 'area',
                    //             title: 'Area',
                    //             width: 80
                    //         },{
                    //             field: 'rack',
                    //             title: 'Rack',
                    //             width: 80
                    //         },{
                    //             field: 'available',
                    //             title: 'Available',
                    //             width: 80
                    //         }, ]
                    //     ],
                    //     onSelect: function(val, loc){
                    //         var f_qty_receipt = qty_receipt.numberbox('getValue');

                    //         capacity.numberbox('setValue', loc.available);
                    //         warehouse.numberbox('setValue', (loc.available - f_qty_receipt));
                    //         qty_whs = (loc.available - f_qty_receipt);
                    //     }
                    // });

                    // qty_receipt2.add(qty_mpq).numberbox({
                    //     onChange: function(value) {
                    //         var f_qty_po = qty_po.numberbox('getValue');
                    //         var f_qty_delivery = qty_delivery.numberbox('getValue');
                    //         var f_qty_receipt = qty_receipt.numberbox('getValue');
                    //         var f_qty_mpq = qty_mpq.numberbox('getValue');

                    //         qty_balance.numberbox('setValue', (f_qty_receipt - value));

                    // if (parseInt(f_qty_delivery) == 0) {
                    //     if (parseInt(f_qty_po) >= parseInt(f_qty_receipt)) {
                    //         var cost = Math.ceil(f_qty_receipt / f_qty_mpq);
                    //         qty_label.numberbox('setValue', cost);
                    //         // warehouse.numberbox('setValue', (qty_whs - f_qty_receipt));
                    //     } else {
                    //         qty_receipt.numberbox('setValue', 0);
                    //         toastr.warning("Qty Receipt > Qty PO", "Information");
                    //         // warehouse.numberbox('setValue', (qty_whs - 0));
                    //     }
                    // } else {
                    //     if (parseInt(f_qty_delivery) >= parseInt(f_qty_receipt)) {
                    //         var cost = Math.ceil(f_qty_receipt / f_qty_mpq);
                    //         qty_label.numberbox('setValue', cost);
                    //         // warehouse.numberbox('setValue', (qty_whs - f_qty_receipt));
                    //     } else {
                    //         qty_receipt.numberbox('setValue', 0);
                    //         toastr.warning("Qty Receipt > Qty OS PO", "Information");
                    //         // warehouse.numberbox('setValue', (qty_whs - 0));
                    //     }
                    // }
                    // }
                    // });
                }
            });
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

                            $.ajax({
                                type: "post",
                                url: "<?= base_url('closing/locks/checkLock') ?>",
                                data: "period=" + row.receipt_date + "&menus_id=<?= $id_menu ?>",
                                dataType: "json",
                                success: function(lock) {
                                    if (lock.total > 0) {
                                        toastr.error("This period is not active by Accounting");
                                        Swal.close();
                                        return false;
                                    }

                                    if (row.receipt_id) {
                                        $.ajax({
                                            method: 'post',
                                            url: '<?= base_url('purchase/purchase_order_receipts/delete') ?>',
                                            data: {
                                                id: row.id,
                                                receipt_id: row.receipt_id,
                                                po_no: row.po_no,
                                                bc_document: row.bc_document,
                                                bc_date: row.bc_date,
                                                item_rm_id: row.item_rm_id,
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
                                                toastr.error("Delete Failed, Please Check Data Relations");
                                                swal.close();
                                            },
                                        });
                                    } else {
                                        toastr.error("Please Delete Detail of the data");
                                        Swal.close();
                                    }
                                }
                            })
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
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_receipt = $("#filter_receipt").combobox('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_supplier=" + filter_supplier + "&filter_receipt=" + filter_receipt + "&filter_doc_no=" + filter_doc_no + "&filter_item_rm=" + filter_item_rm + "&filter_status=" + filter_status;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_order_receipts/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'id',
            fit: true,
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        // $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        // $("#printout").attr('src', '<?= base_url('purchase/purchase_order_receipts/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_receipt = $("#filter_receipt").combobox('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_supplier=" + filter_supplier + "&filter_receipt=" + filter_receipt + "&filter_doc_no=" + filter_doc_no + "&filter_item_rm=" + filter_item_rm + "&filter_status=" + filter_status;
        window.location.assign('<?= base_url('purchase/purchase_order_receipts/print/excel') ?>' + url);
    }

    function print_receiving_note() {
        var receipt_no = $("#filter_receipt_no").combobox('getValue');
        if (receipt_no == "") {
            toastr.warning("Please select Receipt No!", "Information");
        } else {
            window.open("<?= base_url('purchase/purchase_order_receipts/print_receiving/') ?>" + window.btoa(receipt_no) + "/QAS", "_blank");
        }
    }

    function print_good_receiving_note() {
        var receipt_no = $("#filter_good_receipt_no").combobox('getValue');
        if (receipt_no == "") {
            toastr.warning("Please select Receipt No!", "Information");
        } else {
            checkQasReceiving(receipt_no);
        }
    }

    function reload() {
        window.location.reload();
    }

    function readReceiptNo() {
        $("#filter_receipt_no").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readReceiptNo') ?>',
            valueField: 'receipt_no',
            textField: 'receipt_no',
            prompt: "Select Receipt No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_good_receipt_no").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readReceiptNo') ?>',
            valueField: 'receipt_no',
            textField: 'receipt_no',
            prompt: "Select Receipt No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    $(function() {
        filter();

        $("#u_attachment").filebox({
            onChange: function() {
                var attachment = $("#attachment").textbox('getValue');
                var files = $(this).filebox('files')[0];

                var formData = new FormData();
                formData.append('file', files);

                $.ajax({
                    url: "<?= base_url('purchase/purchase_order_receipts/uploadAttachment/') ?>" + btoa(attachment),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
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
                    // receipt_no("<?= $id_menu ?>");

                    var receipt_date = $("#receipt_date").datebox('getValue');
                    var receipt_no = $("#receipt_no").textbox('getValue');
                    // var bc_kind = $("#bc_kind").textbox('getValue');
                    // var bc_aju = $("#bc_aju").textbox('getValue');
                    var attachment = $("#attachment").textbox('getValue');
                    var bc_document = $("#bc_document").combogrid('getValue');
                    var bc_date = $("#bc_date").datebox('getValue');
                    var awb_no = $("#awb_no").textbox('getValue');
                    var awb_date = $("#awb_date").datebox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('purchase/purchase_order_receipts/receipt_no/' . $id_menu) ?>/" + btoa(receipt_date),
                        dataType: "html",
                        success: function(result) {
                            if (result == "") {
                                toastr.info("Please Check Configuration in Serial No First");
                                return false;
                            } else {
                                var receipt_no = result;
                            }

                            $.ajax({
                                type: "post",
                                url: "<?= base_url('closing/locks/checkLock') ?>",
                                data: "period=" + receipt_date + "&menus_id=<?= $id_menu ?>",
                                dataType: "json",
                                success: function(lock) {
                                    if (lock.total > 0) {
                                        toastr.error("This period is not active by Accounting");
                                        Swal.close();
                                        return false;
                                    }

                                    if (receipt_date != "" && receipt_no != "" && bc_document != "" && bc_date != "") {
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

                                        $('#dg_request').datagrid('acceptChanges');
                                        var rows = $('#dg_request').datagrid('getRows');
                                        var totalrows = rows.length;
                                        // endEditing();

                                        requestData(totalrows, rows);

                                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                            var i = (number - 1);
                                            if (value < 100) {
                                                value = Math.floor((number / total) * 100);

                                                if (json[i].qty_receipt > 0) {
                                                    $.ajax({
                                                        type: "post",
                                                        url: '<?= base_url('purchase/purchase_order_receipts/create') ?>',
                                                        data: 'item_rm_id=' + json[i].item_rm_id +
                                                            '&supplier_id=' + json[i].supplier_id +
                                                            '&receipt_date=' + receipt_date +
                                                            '&receipt_no=' + receipt_no +
                                                            '&po_no=' + json[i].po_no +
                                                            // '&bc_kind=' + bc_kind +
                                                            // '&bc_aju=' + bc_aju +
                                                            '&attachment=' + attachment +
                                                            '&bc_document=' + bc_document +
                                                            '&bc_date=' + bc_date +
                                                            '&awb_no=' + awb_no +
                                                            '&awb_date=' + awb_date +
                                                            // '&location=' + json[i].location +
                                                            '&qty_po=' + json[i].qty_po +
                                                            '&qty_delivery=' + json[i].qty_delivery +
                                                            '&qty_receipt=' + json[i].qty_receipt +
                                                            '&price=' + json[i].price +
                                                            '&total=' + json[i].total +
                                                            '&qty_mpq=' + json[i].mpq +
                                                            '&qty_label=' + json[i].qty_label +
                                                            "&confirm=PENDING",
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
                                                                        $('#dg').treegrid('reload');
                                                                        readReceiptNo();
                                                                    }
                                                                });
                                                            }

                                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                        },
                                                        error: function() {
                                                            toastr.error("Cannot Process your Request");
                                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                        }
                                                    });
                                                } else {
                                                    toastr.error("Qty Receipt for Part No " + json[i].item_rm_number + " <= 0");
                                                    swal.close();
                                                }

                                            }
                                        }
                                    } else {
                                        toastr.error("Please Complete Your input");
                                    }
                                }
                            });
                        }
                    });
                }
            }]
        });

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Update Data',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
                        url: url_update,
                        onSubmit: function() {
                            if ($(this).form('validate') === true) {
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
                            } else {
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

                            $('#dg').treegrid('reload');
                            Swal.close();
                        }
                    });
                }
            }]
        });

        readReceiptNo();
        $("#filter_supplier").combogrid({
            url: '<?= base_url('purchase/purchase_order_receipts/readSupplier') ?>',
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
                $("#filter_receipt").combobox({
                    url: '<?= base_url('purchase/purchase_order_receipts/readReceipt/') ?>' + window.btoa(supp.id),
                    valueField: 'receipt_no',
                    textField: 'receipt_no',
                    prompt: "Select Receipt No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
                $("#filter_doc_no").combobox({
                    url: '<?= base_url('purchase/purchase_order_receipts/readDocno/') ?>' + window.btoa(supp.id),
                    valueField: 'bc_document',
                    textField: 'bc_document',
                    prompt: "Select Document No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
                $("#filter_po_no").combobox({
                    url: '<?= base_url('purchase/purchase_order_receipts/readPoNo/') ?>' + window.btoa(supp.id),
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: "Select PO No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#filter_item_rm').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
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
            ]
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
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatprice(value, row) {
        if (value != null) {
            if (row.currency == "INR") {
                var digit = 2;
            } else {
                var digit = 5;
            }

            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: digit
            });

            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return "<b style='color:red;'>CLOSED</b>";
            } else {
                if (row.status == "2") {
                    return "<b style='color:blue;'>CANCEL</b>";
                } else if (row.status == "3") {
                    return "<b style='color:blue;'>NG</b>";
                } else {
                    return "<b style='color:green;'>OPEN</b>";
                }
            }
        } else {
            if (row.status == "2") {
                return "<b style='color:blue;'>CANCEL</b>";
            } else if (row.status == "3") {
                return "<b style='color:blue;'>NG</b>";
            } else {
                return "<b style='color:green;'>OPEN</b>";
            }
        }
    }

    function statusStyle(value, row, index) {
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return 'background-color:#FFC8C8;';
            } else {
                if (row.status == "2") {
                    return 'background-color:#b2c2ff;';
                } else if (row.status == "3") {
                    return 'background-color:#b2c2ff;';
                } else {
                    return 'background-color:#C8FFCC;';
                }
            }
        } else {
            if (row.status == "2") {
                return 'background-color:#b2c2ff;';
            } else if (row.status == "3") {
                return 'background-color:#b2c2ff;';
            } else {
                return 'background-color:#C8FFCC;';
            }
        }
    }

    function statusformatFinance(value, row) {
        if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else if (value == 2) {
            return "<b style='color:blue;'>CANCEL</b>";
        } else if (value == 3) {
            return "<b style='color:blue;'>NG</b>";
        } else {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function statusformatQas(value, row) {
        if (row.status == "2") {
            return "<b style='color:blue;'>CANCEL</b>";
        } else if (row.status == "3") {
            return "<b style='color:blue;'>NG</b>";
        } else {
            if (value == "OPEN") {
                return "<b style='color:green;'>OPEN</b>";
            } else if (value == "NG") {
                return "<b style='color:red;'>NG</b>";
            } else if (value == "PASS DOL") {
                return "<b style='color:red;'>PASS DOL</b>";
            } else {
                return "<b style='color:red;'>CLOSED</b>";
            }
        }
    }

    function statusStyleFinance(value, row, index) {
        if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#b2c2ff;';
        } else if (value == 3) {
            return 'background-color:#b2c2ff;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function statusStyleQas(value, row, index) {
        if (row.status == "2") {
            return 'background-color:#b2c2ff;';
        } else if (row.status == "3") {
            return 'background-color:#b2c2ff;';
        } else {
            if (value == "OPEN") {
                return 'background-color:#C8FFCC; font-weight:bold; color:green;';
            } else {
                return 'background-color:#FFC8C8; font-weight:bold; color:red;';
            }
        }
    }

    function btnAttachment(val) {
        if (val == null || val == "") {
            return "-";
        } else {
            return "<a class='btn btn-primary btn-sm' style='pointer-events: auto; opacity:1; width:100%;' target='_blank' href='<?= base_url('assets/documents/purchase_order_receipts/') ?>" + val + "'><i class='fa fa-eye'></i> View</a>";
        }
    }

    function BtnPrintLabel(val, row) {
        if (val != "closed") {
            if (row.total_scan == row.qty_label) {
                return "-";
            } else if (row.status == '0') {
                if (row.confirm == "PENDING") {
                    return "-";
                } else {
                    var onClick = "checkQas('" + row.id + "','" + row.bc_document + "','" + row.item_rm_number + "','" + row.qty_label + "','" + row.qty_receipt + "','" + row.qty_mpq + "','" + row.receipt_id + "')";
                    if (row.total_label_create > 0) {
                        return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-print"></i> Reprint</a>';
                    } else {
                        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-print"></i> Print</a>';
                    }
                }
            } else if (row.status == "2") {
                return "-";
            } else {
                if (row.status == '1') {
                    var onClick = "checkQas('" + row.id + "','" + row.bc_document + "','" + row.item_rm_number + "','" + row.qty_label + "','" + row.qty_receipt + "','" + row.qty_mpq + "','" + row.receipt_id + "')";
                    if (row.total_label_create > 0) {
                        return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-print"></i> Reprint</a>';
                    } else {
                        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-print"></i> Print</a>';
                    }
                }
            }
        }
    }

    function BtnConfirmQty(val, row) {
        if (row.state != "closed") {
            if (row.total_scan == row.qty_label) {
                return "-";
            } else if (row.status == '0') {
                var onClick = "confirmQty('" + row.purchase_order_receipts_id + "','" + row.receipt_id + "','" + row.supplier_name + "','" + row.item_rm_id + "','" + row.item_rm_number + "','" + row.qty_mpq + "','" + row.qty_delivery + "','" + row.qty_receipt + "','" + row.qty_balance + "','" + row.qty_label + "','" + row.confirm + "')";
                if (val == "OK") {
                    return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-plus"></i> Reconfirm</a>';
                } else {
                    return '<a class="btn btn-warning w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-plus"></i> Confirm</a>';
                }
            } else {
                return "-";
            }
        }
    }

    function BtnDeleteLabel(val, row) {
        if (row.state != "closed") {
            if (parseInt(row.qty_mpq) == parseInt(row.mpq)) {
                if (row.total_scan > 0) {
                    return "-";
                } else {
                    var onClick = "deleteLabel('" + row.receipt_id + "')";
                    return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-rotate-left"></i> Sync</a>';
                }
            } else {
                if (row.total_scan > 0) {
                    return "-";
                } else {
                    var onClick = "deleteLabel('" + row.receipt_id + "')";
                    return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-rotate-left"></i> Sync</a>';
                }
            }
        }
    }

    function BtnCancel(val, row) {
        if (row.state != "closed") {
            if (row.total_scan > 0) {
                return "-";
            } else if (row.status == "2") {
                var onClick = "cancelLabel('" + row.receipt_id + "')";
                return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-check"></i> Open</a>';
            } else if (row.status == "3") {
                return '-';
            } else {
                var onClick = "cancelLabel('" + row.receipt_id + "')";
                return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-times"></i> Cancel</a>';
            }
        }
    }

    function BtnNg(val, row) {
        if (row.state != "closed") {
            if (row.total_scan > 0) {
                return "-";
            } else if (row.qas == "PASS DOL") {
                return "-";
            } else if (row.status == "3") {
                var onClick = "ngLabel('" + row.receipt_id + "')";
                return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-check"></i> Open</a>';
            } else if (row.status == "2") {
                return '-';
            } else {
                var onClick = "ngLabel('" + row.receipt_id + "')";
                return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-times"></i> NG</a>';
            }
        }
    }

    function BtnWms(val, row) {
        if (row.state == "closed") {
            if (row.receipt_date >= "2026-07-03") {
                var onClick = "sendWms('" + row.receipt_no + "')";
                return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-database"></i> Send Data</a>';
            } else {
                return '-';
            }
        } else {
            return "-";
        }
    }

    function dateFormat(val, row) {
        if (val == "1899-11-30") {
            return "-";
        } else {
            return val;
        }
    }

    function checkQas(id, bc_document, item_rm_number, label_box, qty, lot_box) {
        $.messager.confirm('Confirm', 'Are you sure you want to create/print label?', function(r) {
            if (r) {
                Swal.fire({
                    title: 'Please Wait...',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_order_receipts/checkQas") ?>",
                    data: "bc_document=" + bc_document + "&item_rm_number=" + btoa(item_rm_number),
                    dataType: "json",
                    success: function(qas) {
                        swal.close();

                        // if(qas == null){
                        //     toastr.error("Receipt No and Part No in QAS not Found");
                        // }else{
                        // if(qas.label == "OK"){
                        $.ajax({
                            type: "POST",
                            async: true,
                            url: "<?= base_url('purchase/purchase_order_receipts/create_check_label') ?>",
                            data: {
                                "receipt_id": id,
                            },
                            cache: false,
                            dataType: "json",
                            success: function(check) {
                                if (check.total == 0) {
                                    $("#dlg_label").dialog('open');
                                    requestDataBox(label_box, qty);

                                    function requestDataBox(total, qty, number = 1, value = 0, success = 1, failed = 1) {
                                        if (value < 100) {
                                            value = Math.floor((number / total) * 100);
                                            $('#p_upload').progressbar('setValue', value);
                                            $('#p_start').html(number);
                                            $('#p_finish').html(total);

                                            if (parseInt(qty) > parseInt(lot_box)) {
                                                var qty_final = lot_box;
                                            } else {
                                                var qty_final = qty;
                                            }

                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('purchase/purchase_order_receipts/create_label') ?>",
                                                data: {
                                                    "receipt_id": id,
                                                    "qty": qty_final,
                                                },
                                                cache: false,
                                                dataType: "json",
                                                success: function(result) {
                                                    if (result.title != "OK") {
                                                        if (result.theme == "success") {
                                                            $('#p_success').html(success);
                                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                            var qty_balance = (parseInt(qty) - parseInt(lot_box));
                                                            requestDataBox(total, qty_balance, number + 1, value, success + 1, failed + 0);
                                                        } else {
                                                            $('#p_failed').html(failed);
                                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                            var qty_balance = (parseInt(qty) - parseInt(lot_box));
                                                            requestDataBox(total, qty_balance, number + 1, value, success + 0, failed + 1);
                                                        }

                                                        $("#p_remarks").append(title + "<br>");

                                                        if (value == 100) {
                                                            $("#dlg_label").dialog('close');
                                                            $('#dg').datagrid('reload');
                                                            toastr.success("Create Label Completed");
                                                            window.open("<?= base_url('purchase/purchase_order_receipts/print_label/') ?>" + window.btoa(id), '_blank');
                                                        }
                                                    } else {
                                                        $("#dlg_label").dialog('close');
                                                        window.open("<?= base_url('purchase/purchase_order_receipts/print_label/') ?>" + window.btoa(id), '_blank');
                                                    }
                                                }
                                            }).fail(function(jqXHR, textStatus) {
                                                toastr.error("Connection Time Out, Please Wait");
                                                requestDataBox(total, qty, number + 1, value, success, failed);
                                            });
                                        }
                                    }
                                } else if (parseInt(check.total) != parseInt(label_box)) {
                                    toastr.error("Label Qty and Total Label Create not same, please Recreate or Click Sync Button");
                                } else {
                                    window.open("<?= base_url('purchase/purchase_order_receipts/print_label/') ?>" + window.btoa(id), '_blank');
                                }
                            }
                        });
                        //     }else{
                        //         toastr.error("Receipt No and Part No is NG");
                        //     }
                        // }
                    }
                });
            }
        });
    }

    function checkQasReceiving(receipt_no) {
        Swal.fire({
            title: 'Please Wait...',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "post",
            url: "<?= base_url("purchase/purchase_order_receipts/checkQasReceiving") ?>",
            data: "bc_document=" + receipt_no,
            dataType: "json",
            success: function(qas) {
                swal.close();

                if (qas == null) {
                    toastr.error("This Receipt No is Not Found, Please Scan First");
                } else {
                    window.open("<?= base_url('purchase/purchase_order_receipts/print_receiving/') ?>" + window.btoa(receipt_no), "_blank");
                }
            }
        });
    }

    function deleteLabel(receipt_id) {
        $.messager.prompt('Repeat Label', 'Please input The Password', function(r) {
            if (r == "ULANG") {
                Swal.fire({
                    title: 'Please Wait...',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_order_receipts/deleteLabel") ?>",
                    data: "receipt_id=" + receipt_id,
                    dataType: "json",
                    success: function(label) {
                        swal.close();
                        $('#dg').treegrid('reload');

                        if (label.theme == "error") {
                            toastr.error("Sync Label Not Direffent MPQ");
                        } else {
                            toastr.success("Sync Label Success");
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.close();
                        let errorMessage = "Some thing wrong in server (Error " + jqXHR.status + ")";

                        try {
                            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                errorMessage = jqXHR.responseJSON.message;
                            } else if (jqXHR.responseText) {
                                let res = JSON.parse(jqXHR.responseText);
                                if (res.message) {
                                    errorMessage = res.message;
                                }
                            }
                        } catch (e) {
                            errorMessage = jqXHR.statusText;
                        }

                        toastr.error(errorMessage);
                    },
                });
            }
        });
    }

    function cancelLabel(receipt_id) {
        $.messager.prompt('Cancel Label', 'Please input The Password', function(r) {
            if (r == "CANCELPOR") {
                Swal.fire({
                    title: 'Please Wait...',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_order_receipts/cancelLabel") ?>",
                    data: "receipt_id=" + receipt_id,
                    dataType: "json",
                    success: function(label) {
                        swal.close();
                        $('#dg').treegrid('reload');

                        if (label.theme == "error") {
                            toastr.error("Cancel Label Failed because the status is close");
                        } else {
                            toastr.success("Cancel Label Success");
                        }
                    }
                });
            }
        });
    }

    function ngLabel(receipt_id) {
        $.messager.prompt('NG Label', 'Please input The Password', function(r) {
            if (r == "NGPOR") {
                Swal.fire({
                    title: 'Please Wait...',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_order_receipts/ngLabel") ?>",
                    data: "receipt_id=" + receipt_id,
                    dataType: "json",
                    success: function(label) {
                        swal.close();
                        $('#dg').treegrid('reload');

                        if (label.theme == "error") {
                            toastr.error("NG Label Failed because the status is close");
                        } else {
                            toastr.success("NG Label Success");
                        }
                    }
                });
            }
        });
    }

    function sendWms(receipt_no) {
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

                $.ajax({
                    type: "post",
                    url: "<?= base_url("api/wms/gateEntry") ?>",
                    data: {
                        receipt_no: receipt_no
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