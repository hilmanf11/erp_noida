<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'id',width:230,halign:'center'">Receipt No</th>
        <th data-options="field:'store',width:110,halign:'center',formatter:btnStore">Store</th>
        <!-- <th data-options="field:'total_scan',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status<br>POR</th> -->
        <th data-options="field:'status',width:80,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status<br>Invoice</th>
    </thead>
    <thead>
        <tr>
            <!-- <th rowspan="2" data-options="field:'grn_no',width:120,halign:'center'">GRN No</th> -->
            <th rowspan="2" data-options="field:'po_no',width:120,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'receipt_date',width:100,halign:'center'">Receipt Date</th>
            <th colspan="2" data-options="field:'coslpan',halign:'center'">Supplier</th>
            <th colspan="2" data-options="field:'coslpan',halign:'center'">Invoice</th>
            <th rowspan="2" data-options="field:'awb_no',width:120,halign:'center'">BOE No</th>
            <th rowspan="2" data-options="field:'awb_date',width:120,halign:'center', formatter: dateFormat">BOE Date</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:150,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'asset_code',width:120,halign:'center'">Asset Code</th>
            <th rowspan="2" data-options="field:'asset_code_sub',width:120,halign:'center'">Asset Code Sub</th>
            <th rowspan="2" data-options="field:'qty_receipt',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center',align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'currency',width:80,halign:'center',align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right'">MPQ</th>
            <!-- <th rowspan="2" data-options="field:'qty_label',width:80,halign:'center',align:'right'">Qty <br> Label</th> -->
            <!-- <th rowspan="2" data-options="field:'state',width:80,align:'center',formatter:BtnPrintLabel">Label</th> -->
            <!-- <th rowspan="2" data-options="field:'delabel',width:80,align:'center',formatter:BtnDeleteLabel">Sync</th> -->
            <th rowspan="2" data-options="field:'calabel',width:100,align:'center',formatter:BtnCancel">Cancel</th>
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
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Print Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Good Receipt No</span>
                <input style="width:55%;" id="filter_receipt_no" class="easyui-combobox">
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_receiving_note()"><i class="fa fa-print"></i></a>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 90%; height: 550px; padding:10px; top: 20px;">
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
                    <input style="width:60%;" name="bc_date" id="bc_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
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
                    <th hidden data-options="field:'id',width:100,editor:{type:'numberbox', options:{readonly:true}}">ID</th>
                    <th data-options="field:'po_no',width:120">PO No</th>
                    <th data-options="field:'item_rm_id',width:120">Part No EBWS</th>
                    <th data-options="field:'item_rm_number',width:200">Part No Supplier</th>
                    <th data-options="field:'item_rm_name',width:200">Description</th>
                    <th hidden data-options="field:'asset_code_sub',width:150">Asset Code Sub</th>
                    <th data-options="field:'asset_code',width:150">Asset Code</th>
                    <th data-options="field:'uom',width:80">UoM</th>
                    <th data-options="field:'qty_po',width:80,editor:{type:'numberbox', options:{readonly:true, precision: 2}}">PO</th>
                    <th data-options="field:'qty_delivery',width:80,editor:{type:'numberbox', options:{readonly:true, precision: 2}}">Delivery</th>
                    <th data-options="field:'qty_receipt',width:80,editor:{type:'numberbox', options:{readonly:true, precision: 2}}">Receipt</th>
                    <th data-options="field:'mpq',width:80,editor:{type:'numberbox', options:{readonly:true}}">MPQ</th>
                    <th hidden data-options="field:'qty_label',width:80,editor:{type:'numberbox', options:{readonly:true}}">Label</th>
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
        $('#awb_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $("#bc_document").textbox('enable');
        $("#bc_date").datebox('enable');
        $("#awb_no").textbox('enable');
        $("#awb_date").datebox('enable');
        $("#receipt_date").datebox('enable');
        $("#po_no").combobox('enable');
        // $("#supplier_id").combogrid('disable');

        receipt_no("<?= $id_menu ?>");

        $("#supplier_id").combogrid({
            url: '<?= base_url('purchase/delivery_confirm_others/readSupplier') ?>',
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
                    url: '<?= base_url('purchase/delivery_confirm_others/readInvoice?supplier_id=') ?>' + btoa(supp.id),
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
                        $("#awb_date").datebox('setValue', "<?= date("Y-m-d") ?>");

                        $("#po_no").combobox({
                            url: '<?= base_url('purchase/delivery_confirm_others/readPono?invoice_no=') ?>' + btoa(row.invoice_no) + "&supplier_id=" + btoa(supp.id),
                            valueField: 'po_no',
                            textField: 'po_no',
                            prompt: "Select PO No",
                        });
                    }
                });
            }
        });
    }

    function receipt_no(id_menu, receipt_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_order_receipt_others/receipt_no/') ?>" + id_menu + "/" + btoa(receipt_date),
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                } else {
                    $("#receipt_no").textbox('setValue', result);
                }
            }
        });
    }

    function preview() {
        var po_no = $("#po_no").combobox('getValue');
        var bc_document = $("#bc_document").combogrid('getValue');

        if (bc_document == "") {
            toastr.warning('Please select Invoice No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: '<?= base_url('purchase/purchase_order_receipt_others/datatablesTemp') ?>?po_no=' + po_no + "&bc_document=" + btoa(bc_document),
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
                    var qty_mpq = $(editors[3].target);
                    var qty_label = $(editors[4].target);
                    // var location = $(editors[5].target);
                    // var capacity = $(editors[6].target);
                    // var warehouse = $(editors[7].target);

                    qty_whs = 0;

                    qty_receipt.add(qty_mpq).numberbox({
                        onChange: function() {
                            var f_qty_po = qty_po.numberbox('getValue');
                            var f_qty_delivery = qty_delivery.numberbox('getValue');
                            var f_qty_receipt = qty_receipt.numberbox('getValue');
                            var f_qty_mpq = qty_mpq.numberbox('getValue');

                            if (parseInt(f_qty_delivery) == 0) {
                                if (parseInt(f_qty_po) >= parseInt(f_qty_receipt)) {
                                    var cost = Math.ceil(f_qty_receipt / f_qty_mpq);
                                    qty_label.numberbox('setValue', cost);
                                    // warehouse.numberbox('setValue', (qty_whs - f_qty_receipt));
                                } else {
                                    qty_receipt.numberbox('setValue', 0);
                                    toastr.warning("Qty Receipt > Qty PO", "Information");
                                    // warehouse.numberbox('setValue', (qty_whs - 0));
                                }
                            } else {
                                if (parseInt(f_qty_delivery) >= parseInt(f_qty_receipt)) {
                                    var cost = Math.ceil(f_qty_receipt / f_qty_mpq);
                                    qty_label.numberbox('setValue', cost);
                                    // warehouse.numberbox('setValue', (qty_whs - f_qty_receipt));
                                } else {
                                    qty_receipt.numberbox('setValue', 0);
                                    toastr.warning("Qty Receipt > Qty OS PO", "Information");
                                    // warehouse.numberbox('setValue', (qty_whs - 0));
                                }
                            }
                        }
                    })
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
                                            url: '<?= base_url('purchase/purchase_order_receipt_others/delete') ?>',
                                            data: {
                                                id: row.id,
                                                receipt_id: row.receipt_id,
                                                po_no: row.po_no,
                                                bc_document: row.bc_document,
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
                            });
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

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_supplier=" + filter_supplier + "&filter_receipt=" + filter_receipt + "&filter_doc_no=" + filter_doc_no + "&filter_item_rm=" + filter_item_rm;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_order_receipt_others/datatables') ?>' + url,
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

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_order_receipt_others/print') ?>' + url);
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

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_supplier=" + filter_supplier + "&filter_receipt=" + filter_receipt + "&filter_doc_no=" + filter_doc_no + "&filter_item_rm=" + filter_item_rm;
        window.location.assign('<?= base_url('purchase/purchase_order_receipt_others/print/excel') ?>' + url);
    }

    function print_receiving_note() {
        var receipt_no = $("#filter_receipt_no").combobox('getValue');
        if (receipt_no == "") {
            toastr.warning("Please select Receipt No!", "Information");
        } else {
            window.open("<?= base_url('purchase/purchase_order_receipt_others/print_receiving/') ?>" + window.btoa(receipt_no) + "/GOOD", "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }

    function readReceiptNo() {
        $("#filter_receipt_no").combobox({
            url: '<?= base_url('purchase/purchase_order_receipt_others/readReceiptNo') ?>',
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
            url: '<?= base_url('purchase/purchase_order_receipt_others/readReceiptNo') ?>',
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
                    url: "<?= base_url('purchase/purchase_order_receipt_others/uploadAttachment/') ?>" + btoa(attachment),
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
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    // var bc_aju = $("#bc_aju").textbox('getValue');
                    var attachment = $("#attachment").textbox('getValue');
                    var bc_document = $("#bc_document").combogrid('getValue');
                    var bc_date = $("#bc_date").datebox('getValue');
                    var awb_no = $("#awb_no").textbox('getValue');
                    var awb_date = $("#awb_date").datebox('getValue');

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

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('purchase/purchase_order_receipt_others/receipt_no/' . $id_menu) ?>/" + btoa(receipt_date),
                            dataType: "html",
                            success: function(result) {
                                if (result == "") {
                                    toastr.info("Please Check Configuration in Serial No First");
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

                                        $.post("<?= base_url('purchase/purchase_order_receipt_others/checkInvoice') ?>", {
                                            bc_document: bc_document,
                                            supplier_id: supplier_id,
                                            bc_date: bc_date
                                        }, function(checkInv) {
                                            if (checkInv.theme == "error") {
                                                Swal.close();
                                                toastr.error("Invoice No already Created, Please Check!!");
                                                return false;
                                            } else {
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
                                                                url: '<?= base_url('purchase/purchase_order_receipt_others/create') ?>',
                                                                data: 'item_rm_id=' + json[i].item_rm_id +
                                                                    '&supplier_id=' + json[i].supplier_id +
                                                                    '&asset_code=' + json[i].asset_code +
                                                                    '&asset_code_sub=' + json[i].asset_code_sub +
                                                                    '&receipt_date=' + receipt_date +
                                                                    '&receipt_no=' + receipt_no +
                                                                    '&po_no=' + json[i].po_no +
                                                                    '&id=' + json[i].id +
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
                                                                    '&qty_mpq=' + json[i].mpq +
                                                                    '&qty_label=' + json[i].qty_label,
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
                                            }
                                        }, 'json');
                                    }
                                });
                            }
                        });
                    } else {
                        toastr.error("Please Complete Your input");
                    }
                }
            }]
        });

        readReceiptNo();
        $("#filter_supplier").combogrid({
            url: '<?= base_url('master/suppliers/reads') ?>',
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
                    url: '<?= base_url('purchase/purchase_order_receipt_others/readReceipt/') ?>' + window.btoa(supp.id),
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
                    url: '<?= base_url('purchase/purchase_order_receipt_others/readDocno/') ?>' + window.btoa(supp.id),
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
                    url: '<?= base_url('purchase/purchase_order_receipt_others/readPoNo/') ?>' + window.btoa(supp.id),
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
            url: '<?= base_url('master/item_rm/readNotRm') ?>',
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

    function statusformat(value, row) {
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return "<b style='color:red;'>CLOSED</b>";
            } else {
                if (row.status == "2") {
                    return "<b style='color:blue;'>CANCEL</b>";
                } else {
                    return "<b style='color:green;'>OPEN</b>";
                }
            }
        } else {
            if (row.status == "2") {
                return "<b style='color:blue;'>CANCEL</b>";
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
                } else {
                    return 'background-color:#C8FFCC;';
                }
            }
        } else {
            if (row.status == "2") {
                return 'background-color:#b2c2ff;';
            }
        }
    }

    function statusformatFinance(value, row) {
        if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else if (value == 2) {
            return "<b style='color:blue;'>CANCEL</b>";
        } else {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function statusStyleFinance(value, row, index) {
        if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#b2c2ff;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function btnAttachment(val) {
        if (val == null || val == "") {
            return "-";
        } else {
            return "<a class='btn btn-primary btn-sm' style='pointer-events: auto; opacity:1; width:100%;' target='_blank' href='<?= base_url('assets/documents/purchase_order_receipt_others/') ?>" + val + "'><i class='fa fa-eye'></i> View</a>";
        }
    }

    function BtnCancel(val, row) {
        if (row.state != "closed") {
            if (row.total_scan == row.qty_label) {
                return "-";
            } else if (row.status == "2") {
                var onClick = "cancelLabel('" + row.receipt_id + "')";
                return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-check"></i> Open</a>';
            } else {
                var onClick = "cancelLabel('" + row.receipt_id + "')";
                return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-times"></i> Cancel</a>';
            }
        }
    }

    function dateFormat(val, row) {
        if (val == "1899-11-30") {
            return "-";
        } else {
            return val;
        }
    }

    function btnStore(val, row) {
        if (row.state == "closed") {
            if (row.status == 2) {
                return "<a class='btn btn-primary btn-sm' style='pointer-events: auto; opacity:1; width:100%;'><i class='fa fa-times'></i> Cancel</a>";
            } else {
                if (val > 0) {
                    return "<a class='btn btn-success btn-sm' style='pointer-events: auto; opacity:1; width:100%;'><i class='fa fa-check'></i> Confirmed</a>";
                } else {
                    var onClick = 'viewDetail("' + row.receipt_no + '")';
                    return "<a class='btn btn-danger btn-sm' style='pointer-events: auto; opacity:1; width:100%;' onClick='" + onClick + "'><i class='fa fa-edit'></i> Unconfirm</a>";
                }
            }
        }
    }

    function viewDetail(receipt_no) {
        $.messager.prompt('Store Key', 'Please Input Password for Confirm the Gate Entry Data', function(r) {
            if (r == "STOREGATE") {
                $('#dlg_insert').dialog('open');

                $("#bc_document").textbox('disable');
                $("#bc_date").datebox('disable');
                $("#awb_no").textbox('disable');
                $("#awb_date").datebox('disable');
                $("#receipt_date").datebox('disable');
                $("#po_no").combobox('disable');
                // $("#supplier_id").combogrid('disable');

                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_order_receipt_others/checkData"); ?>",
                    data: "receipt_no=" + receipt_no,
                    dataType: "json",
                    success: function(json) {
                        var lastIndex;
                        var dg = $('#dg_request').datagrid({
                            url: '<?= base_url('purchase/purchase_order_receipt_others/datatablesUpdate') ?>?receipt_no=' + btoa(receipt_no),
                            fitColumns: true,
                            singleSelect: true,
                            onClickRow: function(rowIndex) {
                                if (lastIndex != rowIndex) {
                                    $(this).datagrid('endEdit', lastIndex);
                                    $(this).datagrid('beginEdit', rowIndex);
                                }
                                lastIndex = rowIndex;
                            },
                            onBeginEdit: function(rowIndex, row) {
                                var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                                var qty_mpq = $(editors[1].target);
                                var qty_po = $(editors[2].target);
                                var qty_delivery = $(editors[3].target);
                                var qty_receipt = $(editors[4].target);
                                var qty_label = $(editors[5].target);
                                var location = $(editors[6].target);
                                // var capacity = $(editors[6].target);
                                // var warehouse = $(editors[5].target);

                                location.combogrid({
                                    url: '<?= base_url('master/locations/readNotRm/RM') ?>',
                                    panelWidth: 750,
                                    idField: 'number',
                                    textField: 'number',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: "Choose Warehouse",
                                    columns: [
                                        [{
                                            field: 'number',
                                            title: 'Code',
                                            width: 80
                                        }, {
                                            field: 'location',
                                            title: 'Location',
                                            width: 120
                                        }, {
                                            field: 'product_family_name',
                                            title: 'Product Family',
                                            width: 150
                                        }, {
                                            field: 'area',
                                            title: 'Area',
                                            width: 100
                                        }, {
                                            field: 'rack',
                                            title: 'Rack',
                                            width: 80
                                        }, {
                                            field: 'level',
                                            title: 'Level',
                                            width: 80
                                        }, {
                                            field: 'level_sub',
                                            title: 'Level Sub',
                                            width: 80
                                        }]
                                    ],
                                });

                                qty_receipt.add(qty_mpq).numberbox({
                                    onChange: function() {
                                        var f_qty_po = qty_po.numberbox('getValue');
                                        var f_qty_delivery = qty_delivery.numberbox('getValue');
                                        var f_qty_receipt = qty_receipt.numberbox('getValue');
                                        var f_qty_mpq = qty_mpq.numberbox('getValue');

                                        if (parseInt(f_qty_delivery) == 0) {
                                            if (parseInt(f_qty_po) >= parseInt(f_qty_receipt)) {
                                                var cost = Math.ceil(f_qty_receipt / f_qty_mpq);
                                                qty_label.numberbox('setValue', cost);
                                                // warehouse.numberbox('setValue', (qty_whs - f_qty_receipt));
                                            } else {
                                                qty_receipt.numberbox('setValue', 0);
                                                toastr.warning("Qty Receipt > Qty PO", "Information");
                                                // warehouse.numberbox('setValue', (qty_whs - 0));
                                            }
                                        } else {
                                            if (parseInt(f_qty_delivery) >= parseInt(f_qty_receipt)) {
                                                var cost = Math.ceil(f_qty_receipt / f_qty_mpq);
                                                qty_label.numberbox('setValue', cost);
                                                // warehouse.numberbox('setValue', (qty_whs - f_qty_receipt));
                                            } else {
                                                qty_receipt.numberbox('setValue', 0);
                                                toastr.warning("Qty Receipt > Qty OS PO", "Information");
                                                // warehouse.numberbox('setValue', (qty_whs - 0));
                                            }
                                        }
                                    }
                                })
                            }
                        });

                        $("#bc_document").textbox('setValue', json.bc_document);
                        $("#bc_date").datebox('setValue', json.bc_date);
                        $("#awb_no").textbox('setValue', json.awb_no);
                        $("#awb_date").datebox('setValue', json.awb_date);
                        $("#receipt_date").datebox('setValue', json.receipt_date);
                        $("#po_no").combobox('setValue', json.po_no);
                        $("#supplier_id").combogrid('setValue', json.supplier_id);
                    }
                });

                setTimeout(function() {
                    $('#receipt_no').textbox('setValue', receipt_no);
                }, 2000);
            } else {
                toastr.error("Access Denied!!");
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
                    url: "<?= base_url("purchase/purchase_order_receipt_others/cancelLabel") ?>",
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
</script>