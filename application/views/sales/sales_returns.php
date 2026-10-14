<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'id',width:220,halign:'center'">ERP Document No</th>
        <th data-options="field:'status',width:80,halign:'center',align:'center',formatter:cellFormatterStatus,styler:cellStylerStatus">Status</th>
        <th data-options="field:'scan',width:80,halign:'center',align:'center',formatter:cellFormatterScan,styler:cellStylerScan">Scan</th>
        <th data-options="field:'return_no',width:150,halign:'center'">Sales Return No</th>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'sales_invoice_no',width:150,halign:'center'">Sales Invoice No</th>
            <th rowspan="2" data-options="field:'gl_no',width:100,halign:'center'">GL No</th>
            <th rowspan="2" data-options="field:'return_date',width:120,halign:'center', formatter: dateFormat">Trans Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'delivery_note_no',width:150,halign:'center'">Delivery Note No</th>
            <th rowspan="2" data-options="field:'sales_order_no',width:150,halign:'center'">Sales Order No</th>
            <th rowspan="2" data-options="field:'customer_order_no',width:150,halign:'center'">Customer Po No</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:150,halign:'center'">Product No EBWS</th>
            <th rowspan="2" data-options="field:'item_fg_customer',width:200,halign:'center'">Product No Customer</th>
            <th rowspan="2" data-options="field:'hsn_code',width:120,halign:'center'">HSN Code</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center',align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty_invoice',width:80,halign:'center',align:'right',formatter:numberformat">Qty Invoice</th>
            <th rowspan="2" data-options="field:'qty_return',width:80,halign:'center',align:'right',formatter:numberformat">Qty Return</th>
            <th rowspan="2" data-options="field:'qty_lot',width:80,halign:'center',align:'right',formatter:numberformat">Lot Box</th>
            <th rowspan="2" data-options="field:'qty_box',width:80,halign:'center',align:'right'">Qty Box</th>
            <th rowspan="2" data-options="field:'state',width:80,align:'center',formatter:BtnPrintLabel">Label</th>
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
<div id="toolbar" style="height: 230px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" id="filter_plant" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                    <input style="width:60%;" id="filter_sales_invoice_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Return No</span>
                    <input style="width:60%;" id="filter_return_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">ERP Document No</span>
                    <input style="width:60%;" id="filter_document_no" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 90%; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 45%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Return Date</span>
                    <input style="width:60%;" name="return_date" id="return_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                    <input style="width:60%;" name="sales_invoice_no" id="sales_invoice_no" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" name="document_no" id="document_no" required readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 45%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combobox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="customer_address_id" id="customer_address_id" required="" class="easyui-combobox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Return No</span>
                    <input style="width:60%;" name="return_no" id="return_no" required="" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Sales Return List" idField="item_rm_number">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'dn_number',width:120">Delivery Note No</th>
                    <th data-options="field:'so_number',width:150">SO No</th>
                    <th data-options="field:'customer_po',width:120">Customer Po No</th>
                    <th data-options="field:'item_fg_id',width:120,hidden:true">Product ID</th>
                    <th data-options="field:'item_no',width:120">Product No EBWS</th>
                    <th data-options="field:'item_no_customer',width:200">Product No Customer</th>
                    <th data-options="field:'hsn_code',width:120">HSN Code</th>
                    <th data-options="field:'uom',width:80">UoM</th>
                    <th data-options="field:'qty_invoice',width:80,editor:{type:'numberbox', options:{readonly:true}}">Qty Invoice</th>
                    <th data-options="field:'qty_return',width:80,editor:{type:'numberbox'}">Qty Return</th>
                    <th data-options="field:'qty_lot',width:80,editor:{type:'numberbox', options:{readonly:true}}">Lot Box</th>
                    <th data-options="field:'qty_box',width:80,editor:{type:'numberbox', options:{readonly:true}}">Box Qty</th>
                    <th data-options="field:'remark',width:150,editor:{type:'textbox'}">Remarks</th>
                </tr>
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
                <input style="width:60%;" name="sales_returns_id" id="sales_returns_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ERP Document No</span>
                <input style="width:60%;" name="document_no" id="document_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Return ID</span>
                <input style="width:60%;" name="return_id" id="return_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Return No</span>
                <input style="width:60%;" name="return_no" id="return_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" name="customer_name" id="customer_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No EBWS</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No Customer</span>
                <input style="width:60%;" name="item_fg_customer" id="item_fg_customer" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Lot Box</span>
                <input style="width:60%;" name="qty_lot" id="qty_lot" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty Return</span>
                <input style="width:60%;" name="qty_return" id="qty_return" class="easyui-numberbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Total</span>
                <input style="width:60%;" name="total_return" id="total_return" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Label</span>
                <input style="width:60%;" name="qty_box" id="qty_box" readonly class="easyui-numberbox">
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
        $('#return_date').datebox({
            onChange: function(return_date) {
                document_no("<?= $id_menu ?>", return_date);
            }
        });
        $('#return_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $("#sales_invoice_no").combobox({
            url: '<?= base_url('sales/sales_returns/readInvoiceNo') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Select Invoice No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(valinv, inv) {
                $("#customer_id").combobox('setValue', valinv.customer_id);
                $("#customer_address_id").combobox('setValue', valinv.customer_address_id);
            }
        });

        $("#customer_id").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 500,
            valueField: 'id',
            textField: 'name',
            mode: 'remote',
            prompt: "Choose Customer",
        });

        $("#customer_address_id").combobox({
            url: '<?= base_url('sales/sales_returns/readAddress') ?>',
            panelWidth: 500,
            valueField: 'id',
            textField: 'plant',
            mode: 'remote',
            prompt: "Choose Plant",
        });
    }

    function update() {
        $('#frm_update').form('clear');
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 1) {
            toastr.warning("Please select one of the data in the Detail!", "Information");
        } else {
            var row = $('#dg').treegrid('getSelected');
            if (row) {
                if (row.return_id == row.id) {
                    if (row.scan == 0) {
                        if (!row.total_label) {
                            $('#dlg_update').dialog('open');
                            $('#frm_update').form('load', row);
                            url_update = '<?= base_url('sales/sales_returns/update') ?>';
                        } else {
                            toastr.warning("Cannot Update data because the label already created");
                        }
                    } else {
                        toastr.warning("Cannot Update data because the label already scan in");
                    }
                } else {
                    toastr.warning("Please Select Detail in the table!");
                }

            } else {
                toastr.warning("Please select one of the data in the table first!", "Information");
            }

            $("#qty_return").numberbox({
                onChange: function(val) {
                    var qty_lot = $("#qty_lot").numberbox('getValue');
                    var box = Math.ceil(val / qty_lot);
                    $("#qty_box").numberbox('setValue', box);
                }
            });
        }
    }

    function document_no(id_menu, return_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('sales/sales_returns/document_no/') ?>" + id_menu + "/" + btoa(return_date),
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                    $("#document_no").textbox('clear');
                } else {
                    $("#document_no").textbox('setValue', result);
                }
            }
        });
    }

    function preview() {
        var sales_invoice_no = $("#sales_invoice_no").combobox('getValue');
        var return_no = $("#return_no").textbox('getValue');

        if (sales_invoice_no == "" || return_no == "") {
            toastr.warning('Please select Sales Invoice No and Sales Return No', 'Required');
        } else {
            $.ajax({
                type: "POST",
                async: true,
                url: "<?= base_url('sales/sales_returns/check_return_no') ?>",
                data: {
                    "return_no": return_no
                },
                cache: false,
                dataType: "json",
                success: function(check) {
                    if (check.total == 0) {
                        var lastIndex;
                        var dg = $('#dg_request').datagrid({
                            url: '<?= base_url('sales/sales_returns/datatablesTemp') ?>?sales_invoice_no=' + btoa(sales_invoice_no),
                            fitColumns: false,
                            onLoadSuccess: function(data, rows) {
                                if (data.total === 0) {
                                    toastr.warning("Data Not Found");
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
                                var qty_invoice = $(editors[0].target);
                                var qty_return = $(editors[1].target);
                                var qty_lot = $(editors[2].target);
                                var qty_box = $(editors[3].target);
                                qty_return.numberbox({
                                    onChange: function() {
                                        var f_qty_invoice = qty_invoice.numberbox('getValue');
                                        var f_qty_return = qty_return.numberbox('getValue');
                                        var f_qty_lot = qty_lot.numberbox('getValue');
                                        if (parseInt(f_qty_invoice) >= (parseInt(f_qty_return) + parseInt(row.total_return))) {
                                            var cost = Math.ceil(f_qty_return / f_qty_lot);
                                            qty_box.numberbox('setValue', cost);
                                        } else {
                                            qty_return.numberbox('setValue', 0);
                                            toastr.warning("Qty Return more than Qty Invoice", "Information");
                                        }
                                    }
                                })
                            }
                        });
                    } else {
                        toastr.error("Sales Return No. Can't duplicate");
                    }
                }
            });
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
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
                            if (row.return_id) {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('sales/sales_returns/delete') ?>',
                                    data: {
                                        id: row.id,
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
        var filter_customer = $("#filter_customer").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_sales_invoice_no = $("#filter_sales_invoice_no").combobox('getValue');
        var filter_return_no = $("#filter_return_no").combobox('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_customer=" + filter_customer + "&filter_plant=" + filter_plant + "&filter_sales_invoice_no=" + filter_sales_invoice_no + "&filter_return_no=" + filter_return_no + "&filter_document_no=" + filter_document_no;

        $('#dg').treegrid({
            url: '<?= base_url('sales/sales_returns/datatables') ?>' + url,
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
        $("#printout").attr('src', '<?= base_url('sales/sales_returns/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer = $("#filter_customer").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_sales_invoice_no = $("#filter_sales_invoice_no").combobox('getValue');
        var filter_return_no = $("#filter_return_no").combobox('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_customer=" + filter_customer + "&filter_plant=" + filter_plant + "&filter_sales_invoice_no=" + filter_sales_invoice_no + "&filter_return_no=" + filter_return_no + "&filter_document_no=" + filter_document_no;
        window.location.assign('<?= base_url('sales/sales_returns/print/excel') ?>' + url);
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
                    // document_no("<?= $id_menu ?>");

                    var return_date = $("#return_date").datebox('getValue');
                    var document_no = $("#document_no").textbox('getValue');
                    var sales_invoice_no = $("#sales_invoice_no").combobox('getValue');
                    var return_no = $("#return_no").textbox('getValue');
                    var customer_id = $("#customer_id").combobox('getValue');
                    var customer_address_id = $("#customer_address_id").combobox('getValue');

                    $('#dg_request').datagrid('acceptChanges');
                    var rows = $('#dg_request').datagrid('getSelections');
                    var totalrows = rows.length;
                    if (totalrows > 0) {
                        if (return_date != "" && document_no != "" && sales_invoice_no != "" && return_no != "") {

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
                            // endEditing();

                            requestData(totalrows, rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                var i = (number - 1);
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    if (json[i].qty_return > 0 && json[i].qty_return !== 'undefined') {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('sales/sales_returns/create') ?>',
                                            data: {
                                                return_date: return_date,
                                                document_no: document_no,
                                                sales_invoice_no: sales_invoice_no,
                                                return_no: return_no,
                                                customer_id: customer_id,
                                                customer_address_id: customer_address_id,
                                                delivery_note_no: json[i].dn_number,
                                                sales_order_no: json[i].so_number,
                                                customer_order_no: json[i].customer_po,
                                                item_fg_id: json[i].item_fg_id,
                                                item_fg_number: json[i].item_no,
                                                item_fg_customer: json[i].item_no_customer,
                                                hsn_code: json[i].hsn_code,
                                                uom: json[i].uom,
                                                qty_invoice: json[i].qty_invoice,
                                                qty_return: json[i].qty_return,
                                                qty_lot: json[i].qty_lot,
                                                qty_box: json[i].qty_box,
                                                remarks: json[i].remark,
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
                                                                window.location.reload();
                                                            }
                                                        });
                                                    } else {
                                                        requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                    }
                                                } else {
                                                    if (value >= 100) {
                                                        Swal.close();
                                                        toastr.error(result.message, result.title);
                                                    } else {
                                                        toastr.error(result.message, result.title);
                                                        requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                    }
                                                }
                                            }
                                        });
                                    } else {
                                        toastr.error("Qty return <= 0");
                                        swal.close();
                                    }
                                }
                            }

                        } else {
                            toastr.error("Please Complete Your input");
                        }
                    } else {
                        toastr.warning("No items were selected. Please tick an item to continue");
                    }
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

        $("#filter_customer").combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Customer",
            columns: [
                [{
                    field: 'id',
                    title: 'Customer ID',
                    width: 50
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 200
                }]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            onSelect: function(val, cust) {
                $("#filter_plant").combobox({
                    url: '<?= base_url('master/customers/readAddress/') ?>' + window.btoa(cust.id),
                    valueField: 'id',
                    textField: 'plant',
                    prompt: "Select Plant",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });


        $("#filter_sales_invoice_no").combobox({
            url: '<?= base_url('sales/sales_returns/readSalesInvoiceNo') ?>',
            valueField: 'sales_invoice_no',
            textField: 'sales_invoice_no',
            prompt: "Select Invoice No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_return_no").combobox({
            url: '<?= base_url('sales/sales_returns/readReturnNo') ?>',
            valueField: 'return_no',
            textField: 'return_no',
            prompt: "Select Return No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_document_no").combobox({
            url: '<?= base_url('sales/sales_returns/readDocumentNo') ?>',
            valueField: 'document_no',
            textField: 'document_no',
            prompt: "Select Document No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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

    function cellStylerScan(value, row, index) {
        if (value == 0) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterScan(value, row) {
        if (value == 0) {
            return 'Not Scan';
        } else {
            return 'Scanned';
        }
    };

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
            return 'CLOSED';
        }
    };

    function BtnPrintLabel(val, row) {
        if (row.state != 'closed') {
            var send = "print_label('" + row.document_no + "','" + row.return_id + "','" + row.qty_return + "','" + row.qty_lot + "','" + row.qty_box + "')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="' + send + '"><i class="fa fa-print"></i> Print</a>';
        }

    }

    function print_label(document_no, return_id, qty_return, qty_lot, qty_box) {
        if (qty_lot == 'null' || qty_lot == '') {
            toastr.error("Please check MPQ first!");
        } else {
            $.ajax({
                type: "POST",
                async: true,
                url: "<?= base_url('sales/sales_returns/create_check_label') ?>",
                data: {
                    "return_id": return_id
                },
                cache: false,
                dataType: "json",
                success: function(check) {
                    if (check.total == 0) {
                        $("#dlg_label").dialog('open');
                        requestDataBox(qty_box, qty_return);

                        function requestDataBox(total, qty, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                if (parseInt(qty) > parseInt(qty_lot)) {
                                    var qty_final = qty_lot;
                                } else {
                                    var qty_final = qty;
                                }

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('sales/sales_returns/create_label') ?>",
                                    data: {
                                        "return_id": return_id,
                                        "qty": qty_final
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.title != "OK") {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                var qty_balance = (parseInt(qty) - parseInt(qty_lot));
                                                requestDataBox(total, qty_balance, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                var qty_balance = (parseInt(qty) - parseInt(qty_lot));
                                                requestDataBox(total, qty_balance, number + 1, value, success + 0, failed + 1);
                                            }

                                            $("#p_remarks").append(title + "<br>");

                                            if (value == 100) {
                                                $("#dlg_label").dialog('close');
                                                $('#dg').treegrid('reload');
                                                toastr.success("Create Label Completed");
                                                window.open("<?= base_url('sales/sales_returns/print_label/') ?>" + window.btoa(return_id), '_blank');
                                            }
                                        } else {
                                            $("#dlg_label").dialog('close');
                                            window.open("<?= base_url('sales/sales_returns/print_label/') ?>" + window.btoa(return_id), '_blank');
                                        }
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    toastr.error("Connection Time Out, Please Wait");
                                    requestDataBox(total, qty, number, value, success, failed);
                                });
                            }
                        }
                    } else {
                        window.open("<?= base_url('sales/sales_returns/print_label/') ?>" + window.btoa(return_id), '_blank');
                    }
                }
            });
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

    function dateFormat(val, row) {
        if (val == "1899-11-30") {
            return "-";
        } else {
            return val;
        }
    }
</script>