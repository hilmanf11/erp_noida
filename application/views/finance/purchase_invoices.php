<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Payment<br>Status</th>
            <th rowspan="2" data-options="field:'status_invoice',width:110,align:'center',formatter:statusformatInv,styler:statusStyleInv">Supplier<br>Invoice</th>
            <th rowspan="2" data-options="field:'grn_no',width:150,align:'center'">GRN No</th>
            <th rowspan="2" data-options="field:'gl_no',width:140,align:'center'">GL No</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <!-- <th rowspan="2" data-options="field:'item_family_name',width:150,halign:'center'">Sub Category</th> -->
            <th rowspan="2" data-options="field:'journal_type_name',width:200,halign:'center'">Journal Name</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <tfh rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Invoice No</tfh>
            <th rowspan="2" data-options="field:'invoice_date',width:120,halign:'center'">Invoice Date</th>
            <th rowspan="2" data-options="field:'boe_no',width:150,halign:'center'">BOE No</th>
            <th rowspan="2" data-options="field:'boe_date',width:120,halign:'center'">BOE Date</th>
            <th rowspan="2" data-options="field:'taxes',width:80,halign:'center',align:'right'">Taxes %</th>
            <th rowspan="2" data-options="field:'payment_term',width:100,align:'center'">Payment Term <br>(Days)</th>
            <th rowspan="2" data-options="field:'due_date',width:100,align:'center'">Payment Due</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:120,halign:'center',align:'right',formatter: numberformat">Taxable Value</th>
            <!-- <th rowspan="2" data-options="field:'total_vat',width:120,halign:'center',align:'right',formatter: numberformat">VAT</th> -->
            <!-- <th rowspan="2" data-options="field:'total_pph',width:120,halign:'center',align:'right',formatter: numberformat">PPH</th> -->
            <th rowspan="2" data-options="field:'total_grand',width:120,halign:'center',align:'right',formatter: numberformat">Invoice Value</th>
            <th rowspan="2" data-options="field:'total_dp',width:120,halign:'center',align:'right',formatter: numberformat">Down Payment</th>
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

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 230px; padding: 10px;">
    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 32%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type Date</span>
                <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose Type Date</option>
                    <option value="PID">Purchase Invoice Date</option>
                    <option value="PAY">Payment Due</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Invoice Date</span>
                <input style="width:30%;" id="filter_trans_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_trans_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Due</span>
                <input style="width:30%;" id="filter_due_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_due_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_invoicing()"><i class="fa fa-print"></i> Print Invoicing</a>
            </div>
        </div>
        <div style="width: 32%; float: left;">
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="filter_family_id" id="filter_family_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purhcase Invoice No</span>
                <input style="width:60%;" name="filter_purchase_invoice" id="filter_purchase_invoice" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" name="filter_supplier" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
        </div>
        <div style="width: 35%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Order</span>
                <input style="width:60%;" id="filter_purchase_order" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Order Receipt</span>
                <input style="width:60%;" id="filter_purchase_receipt" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Supplier Status</span>
                <select style="width:60%;" id="filter_status_supplier" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="INVWDP">DOWN PAYMENT</option>
                    <option value="INVTMP">TEMPORARY</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Status</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="0">OPEN</option>
                    <option value="1">CLOSE</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelDetail()"><i class="fa fa-file"></i> Purchase Register Report</a> -->
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelJournal()"><i class="fa fa-file"></i> Export Excel Journal</a> -->
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
</div>

<div id="toolbar3">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit3()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 550px; padding:10px; top: 5px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice Type</span>
                        <select style="width:60%;" id="type" name="type" required class="easyui-combobox" panelHeight="auto">
                            <option value="" selected disabled>Select Purchase Invoice Type</option>
                            <option value="purchase">Non Down Payment</option>
                            <option value="dp">Down Payment</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice Date</span>
                        <input style="width:60%;" id="trans_date" name="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Document No</span>
                        <input style="width:60%;" readonly id="number" name="number" class="easyui-textbox" data-options="prompt:'Automatic From Purchase Invoce Date'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Name</span>
                        <input style="width:60%;" required="" id="supplier_id" name="supplier_id" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Order Receipt</span>
                        <input style="width:60%;" required="" id="por_no" name="por_no" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" required="" name="journal_type_id" id="journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Invoice</span>
                        <input style="width:30%;" required="" data-options="prompt:'Invoice No'" id="invoice_no" name="invoice_no" class="easyui-textbox">
                        <input style="width:30%;" required="" data-options="prompt:'Invoice Date', formatter:myformatter, parser:myparser, editable:false" id="invoice_date" name="invoice_date" class="easyui-datebox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Taxes</span>
                        <input style="width:60%;" id="taxes" name="taxes" readonly class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Term / Due</span>
                        <input style="width:30%;" required="" readonly="" id="payment_term" name="payment_term" class="easyui-numberbox" data-options="buttonText:'Days',buttonAlign:'right'">
                        <input style="width:30%;" id="due_date" name="due_date" required="" readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">BOE No / Date</span>
                        <input style="width:30%;" id="boe_no" name="boe_no" class="easyui-textbox">
                        <input style="width:30%;" id="boe_date" name="boe_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Voucher</span>
                        <input style="width:60%;" id="voucher" name="voucher" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%;" id="remarks" name="remarks" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" showFooter="true" title="Purchase Invoice Lists" data-options="singleSelect: true" toolbar="#toolbar2">
            <thead>
                <tr>
                    <th rowspan="2" data-options="field:'action',width:120,formatter:buttonEdit">Action</th>
                    <th hidden rowspan="2" data-options="field:'id',width:150, editor: {type: 'textbox'}">ID</th>
                    <th rowspan="2" data-options="field:'por_no',width:150,editor: {type: 'textbox'}">POR. No</th>
                    <th rowspan="2" data-options="field:'po_no',width:180,editor: {type: 'textbox'}">PO. No</th>
                    <th rowspan="2" data-options="field:'item_rm_id',width:120,editor: {type: 'textbox', options: {readonly: true}}">Part No EBWS</th>
                    <th rowspan="2" data-options="field:'item_rm_number',width:150,editor: {type: 'textbox', options: {required: true}}">Part No Supplier</th>
                    <th rowspan="2" data-options="field:'item_family_name',width:120,editor: {type: 'textbox', options: {readonly: true}}">Product Family</th>
                    <th rowspan="2" data-options="field:'asset_code',width:150, editor: {type: 'textbox', options: {readonly: true}}">Asset Code</th>
                    <th rowspan="2" data-options="field:'hsn_code',width:100,editor: {type: 'textbox', options: {readonly: true}}">HSN Code</th>
                    <th hidden rowspan="2" data-options="field:'item_family_id',width:200,editor: {type: 'textbox'}">Product Family ID</th>
                    <th hidden rowspan="2" data-options="field:'item_rm_name',width:200,editor: {type: 'textbox'}">Description</th>
                    <th rowspan="2" data-options="field:'uom',align:'center',width:80, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/uom/reads') ?>',
                            editable:false,
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose Uom'
                        }}">UoM</th>
                    <th rowspan="2" data-options="field:'qty',width:80, formatter:numberformat,editor: {
                        type: 'numberbox', 
                        options: {
                            required: true,
                            readonly: true,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });

                                var price = $(ed2.target).numberbox('getValue');
                                $(ed.target).textbox('setValue', (parseFloat(value) * parseFloat(price)));
                            }
                        }
                    }">Qty</th>
                    <th colspan="7" data-options="field:'',align:'center'">Original Currency</th>
                    <th colspan="3" data-options="field:'',align:'center'">Local Currency</th>
                    <th rowspan="2" data-options="field:'account_number',width:100, halign:'center', editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            panelWidth: 320,
                            idField: 'account_number',
                            textField: 'account_number',
                            mode: 'remote', 
                            fitColumns: true,
                            prompt: 'Choose Account No',
                            columns: [
                                [{
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
                                }, {
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }, ]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }}">Account No</th>
                    <th rowspan="2" data-options="field:'account_name',width:150, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th rowspan="2" data-options="field:'account_type',width:100, halign:'center', editor: {
                    type: 'combobox',
                    options: {
                        data: [{
                            'id':'DEBIT'
                        },{
                            'id':'CREDIT'
                        }],
                        valueField: 'id',
                        textField: 'id',
                        prompt: 'Choose Debit/Credit',
                        panelHeight: 'auto'
                    }}">Debit/Credit</th>
                </tr>
                <tr>
                    <th data-options="field:'currency',align:'center',width:80, editor: {
                    type: 'combobox',
                    options: {
                        url: '<?= base_url('master/currencies/reads') ?>',
                        editable:false,
                        valueField: 'name',
                        textField: 'name',
                        prompt: 'Choose Currencies'
                    }}">Currency</th>
                    <th data-options="field:'price', width:80, halign:'center', align:'right', formatter:priceformat,editor: {type: 'numberbox', 
                        options: {
                            readonly: true,
                            required: true,
                            precision: 5,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total2'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var qty = $(ed2.target).numberbox('getValue');
                                $(ed.target).numberbox('setValue', (parseFloat(value) * parseFloat(qty)));
                                $(ed3.target).numberbox('setValue', (parseFloat(value) * parseFloat(qty)));
                                $(ed4.target).numberbox('setValue', (parseFloat(value) * parseFloat(qty)));
                            }
                        }}">Price</th>
                    <th data-options="field:'total',width:120, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 2}}">Amount</th>
                    <th data-options="field:'igst',width:80, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', 
                        options: {
                            required: true,
                            precision: 2,
                            onChange: function(igst) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'sgst'
                                });

                                var ed1 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'cgst'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total2'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var sgst = $(ed.target).numberbox('getValue');
                                var total = $(ed1.target).numberbox('getValue');
                                var cgst = $(ed2.target).numberbox('getValue');
                                $(ed3.target).numberbox('setValue', (parseFloat(igst) + parseFloat(sgst) + parseFloat(cgst) + parseFloat(total)));
                                $(ed4.target).numberbox('setValue', (parseFloat(igst) + parseFloat(sgst) + parseFloat(cgst) + parseFloat(total)));
                            }
                        }
                    }">IGST</th>
                    <th data-options="field:'sgst',width:80, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', 
                    options: {
                            required: true,
                            precision: 2,
                            onChange: function(sgst) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'igst'
                                });

                                var ed1 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'cgst'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total2'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var igst = $(ed.target).numberbox('getValue');
                                var total = $(ed1.target).numberbox('getValue');
                                var cgst = $(ed2.target).numberbox('getValue');
                                $(ed3.target).numberbox('setValue', (parseFloat(igst) + parseFloat(sgst) + parseFloat(cgst) + parseFloat(total)));
                                $(ed4.target).numberbox('setValue', (parseFloat(igst) + parseFloat(sgst) + parseFloat(cgst) + parseFloat(total)));
                            }
                        }
                    }">SGST</th>
                    <th data-options="field:'cgst',width:80, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', 
                    options: {
                            required: true,
                            precision: 2,
                            onChange: function(cgst) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'igst'
                                });

                                var ed1 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'sgst'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total2'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var igst = $(ed.target).numberbox('getValue');
                                var total = $(ed1.target).numberbox('getValue');
                                var sgst = $(ed2.target).numberbox('getValue');
                                $(ed3.target).numberbox('setValue', (parseFloat(igst) + parseFloat(sgst) + parseFloat(cgst) + parseFloat(total)));
                                $(ed4.target).numberbox('setValue', (parseFloat(igst) + parseFloat(sgst) + parseFloat(cgst) + parseFloat(total)));
                            }
                        }
                    }">CGST</th>
                    <th data-options="field:'total2',width:100, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 2}}">Amount<br>After Tax</th>
                    <th data-options="field:'rate',width:100, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', options: {
                            required: true,
                            precision: 4,
                            onChange: function(rate) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed1 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var total2 = $(ed.target).numberbox('getValue');
                                $(ed1.target).numberbox('setValue', (parseFloat(total2) * parseFloat(rate)));
                            }
                        }
                    }">Rate</th>
                    <th data-options="field:'currency_local',align:'center',width:80">Currency</th>
                    <th data-options="field:'total_local',width:120, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', options: {required: true, precision: 2}}">Amount</th>
                </tr>
            </thead>
        </table>

        <div style="width: 50%; float: left; margin-top:20px;">
            <a style="width: 100%;" class="easyui-linkbutton c2" onclick="addJournal()">Add to Journal</a>
            <br><br>
            <table id="dg3" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbar3"></table>

            <div class="fitem">
                <b style="width:50%; display:inline-block; padding-left: 50px;">BALANCE TOTAL</b>
                <input style="width:18%;" id="balance_debit" name="balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:',', decimalSeparator:'.'">
                <input style="width:18%;" id="balance_credit" name="balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:',', decimalSeparator:'.'">
            </div>
        </div>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">TAXABLE VALUE</b>
                        <input style="width:60%;" id="total_sub" name="total_sub" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">IGST</b>
                        <input style="width:40%;" id="total_igst" name="total_igst" class="easyui-numberbox" disabled data-options="precision:2,groupSeparator:','">
                        <!-- &nbsp; &nbsp; <input type="checkbox" class="easyui-checkbox" id="check_vat" data-options="onChange: check_vat" value="VAT"> -->
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SGST</b>
                        <input style="width:40%;" id="total_sgst" name="total_sgst" class="easyui-numberbox" disabled data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">CGST</b>
                        <input style="width:40%;" id="total_cgst" name="total_cgst" class="easyui-numberbox" disabled data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">TCS Amount</b>
                        <select style="width:20%;" id="tcs" name="tcs" panelHeight="auto" class="easyui-combobox">
                            <option value="0">0%</option>
                            <option value="0.1">0.1%</option>
                            <option value="1">1%</option>
                        </select>
                        <input style="width:40%;" id="total_tcs" name="total_tcs" disabled required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">TDS <span id="txt_tds"></span> <span id="nom_tds"></span></b>
                        <input style="width:40%;" id="total_tds" name="total_tds" class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">FREIGHT</b>
                        <input style="width:40%;" id="total_freight" name="total_freight" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">INVOICE VALUE</b>
                        <input style="width:60%;" id="total_grand" name="total_grand" disabled required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem" id="type_selection_dp">
                        <b style="width:35%; display:inline-block;">DOWN PAYMENT</b>
                        <input style="width:60%;" id="total_dp" name="total_dp" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);

        var dg = $('#dg2').datagrid({
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
        });

        $('#dg3').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        $("#type_selection_dp").hide();

        $("#type").combobox({
            readonly: false
        });
        // $("#trans_date").datebox('enable');
        $("#supplier_id").combobox('enable');
        $("#por_no").combobox('enable');
        // $("#po_no").combobox('enable');
        $("#account_purchase_name").textbox('setValue', "PURCHASE");
        $("#account_pay_name").textbox('setValue', "PAYABLE");
        $("#account_bal_name").textbox('setValue', "BALANCE");
        $("#preview").linkbutton('enable');

        $("#tcs").combobox('setValue', 0);
        $("#total_tcs").numberbox('setValue', 0);
        $("#total_tds").numberbox('setValue', 0);
        $("#total_freight").numberbox('setValue', 0);

        $("#invoice_date").datebox({
            onChange: function(val) {
                var trans_date = val;
                var payment_term = $("#payment_term").numberbox("getValue");

                if (payment_term != "") {
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('finance/purchase_invoices/readDueDate/') ?>" + window.btoa(trans_date) + "/" + payment_term,
                        dataType: "text",
                        success: function(due_date) {
                            $("#due_date").datebox('setValue', due_date);
                        }
                    });
                }
            }
        });

        $("#type").combobox({
            onChange: function(t) {
                var type = $("#type").combobox('getValue');

                if (type != "dp") {
                    $("#type_selection_dp").hide();
                    $("#total_dp").numberbox('clear');
                } else {
                    $("#type_selection_dp").show();
                    $("#total_dp").numberbox('setValue', 0);
                }

                //Supplier Invoice Auto
                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/purchase_invoices/numberInvoice/') ?>" + type,
                    dataType: "html",
                    success: function(invoice_no) {
                        $("#invoice_no").textbox('setValue', invoice_no);
                    }
                });
            }
        });

        $("#trans_date").datebox({
            onChange: function(trans_date){
                number("<?= $menus_id ?>", btoa(trans_date));
            }
        });

        $("#type").combobox('setValue', "purchase");
        $("#trans_date").datebox("setValue", "<?= date("Y-m-d") ?>");
        $("#invoice_date").datebox("setValue", "<?= date("Y-m-d") ?>");
    }

    function addJournal() {
        var rows = $('#dg2').datagrid('getRows');
        var supplier_id = $("#supplier_id").combogrid('getValue');
        // var taxes = $("#taxes").numberbox('getValue');

        var totalrows = rows.length;

        var rows2 = $('#dg3').datagrid('getRows');
        var totalrows2 = rows2.length;
        endEditing2();

        if (totalrows > 0) {
            var data_array = [];
            var data_array2 = [];
            var data_array3 = [];
            var total_sub = 0;
            var total_igst = 0;
            var total_cgst = 0;
            var total_sgst = 0;
            for (let i = 0; i < totalrows; i++) {
                var data = {
                    account_number: rows[i].account_number,
                    account_name: rows[i].account_name,
                    account_type: rows[i].account_type,
                    total: rows[i].total
                }

                if(rows[i].account_type == "DEBIT"){
                    total_sub += Math.abs(parseFloat(rows[i].total));
                }else{
                    total_sub -= Math.abs(parseFloat(rows[i].total));
                }

                total_igst += Math.abs(parseFloat(rows[i].igst));
                total_cgst += Math.abs(parseFloat(rows[i].cgst));
                total_sgst += Math.abs(parseFloat(rows[i].sgst));
                data_array.push(data);
            }

            $("#total_sub").numberbox('setValue', total_sub);
            $("#total_igst").numberbox('setValue', total_igst);
            $("#total_cgst").numberbox('setValue', total_cgst);
            $("#total_sgst").numberbox('setValue', total_sgst);

            var total_tds = $("#total_tds").numberbox('getValue');
            var total_tcs = $("#total_tcs").numberbox('getValue');
            var total_freight = $("#total_freight").numberbox('getValue');

            if(total_tds == ""){
                $("#total_tds").numberbox('setValue', 0);
                var total_tds = 0;
            }

            var total_grand = (parseFloat(total_sub.toFixed(2)) + parseFloat(total_igst.toFixed(2)) + parseFloat(total_cgst.toFixed(2)) + parseFloat(total_sgst.toFixed(2)) - parseFloat(total_tds) + parseFloat(total_tcs) + parseFloat(total_freight));
            $("#total_grand").numberbox('setValue', (total_grand));

            var tds_val = 0;
            var arr_tds = ["17000001","17000002","17000003","17000004","17000005","17000006","17000028","17000029"];
            var arr_ap = ['16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008'];
            var flag = 1;
            for (let z = 0; z < totalrows2; z++) {
                var debit = rows2[z].debit;
                var credit = rows2[z].credit;

                if (rows2[z].account_number == "20100003") {
                    var debit = total_igst;
                    var credit = 0;
                }

                if (rows2[z].account_number == "20100004") {
                    var debit = total_cgst;
                    var credit = 0;
                }

                if (rows2[z].account_number == "20100005") {
                    var debit = total_sgst;
                    var credit = 0;
                }

                if (rows2[z].account_number == "17000007") {
                    var debit = 0;
                    var credit = total_tcs;
                }

                if (rows2[z].account_number == "20100008") {
                    var debit = total_tcs;
                    var credit = 0;
                }

                if (rows2[z].account_number == "40000002") {
                    var debit = total_freight;
                    var credit = 0;
                }

                if (jQuery.inArray(rows2[z].account_number, arr_tds) >= 0) {
                    var debit = 0;
                    var credit = total_tds;
                    tds_val = 1;
                }

                if (jQuery.inArray(rows2[z].account_number, arr_ap) >= 0) {
                    var debit = 0;
                    var credit = total_grand;
                }

                var data2 = {
                    account_number: rows2[z].account_number,
                    account_name: rows2[z].account_name,
                    debit: debit,
                    credit: credit,
                    flag: rows2[z].flag,
                }

                data_array2.push(data2);
                data_array3.push(rows2[z].account_number);
                flag++;
            }

            let hasil = arr_ap.filter(function(item) {
                return data_array3.includes(item);
            });

            if (hasil.length == 0) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/purchase_invoices/readSupplierCoa') ?>",
                    data: "supplier_id=" + supplier_id,
                    dataType: "json",
                    success: function (response) {
                        var data3 = {
                            account_number: response[0].account_number,
                            account_name: response[0].account_name,
                            debit: 0,
                            credit: total_grand,
                            flag: flag,
                        }

                        data_array2.push(data3);
                        var jsonData = JSON.stringify(data_array);
                        var jsonData2 = JSON.stringify(data_array2);

                        $.ajax({
                            type: "POST",
                            url: "<?= base_url('finance/purchase_invoices/createJson') ?>",
                            data: {
                                jsonData: jsonData,
                                jsonData2: jsonData2,
                            },
                            success: function(response) {
                                addTable2('<?= base_url('finance/purchase_invoices/calculateJournal') ?>');

                                setTimeout(function() {
                                    balance_journal();
                                }, 2000);
                            },
                        });
                    }
                });
            }else{
                var jsonData = JSON.stringify(data_array);
                var jsonData2 = JSON.stringify(data_array2);

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('finance/purchase_invoices/createJson') ?>",
                    data: {
                        jsonData: jsonData,
                        jsonData2: jsonData2,
                    },
                    success: function(response) {
                        addTable2('<?= base_url('finance/purchase_invoices/calculateJournal') ?>');

                        setTimeout(function() {
                            balance_journal();
                        }, 2000);
                    },
                });
            }
        } else {
            toastr.warning("please selections your data in table first");
        }
    }

    function addTable2(link = "") {
        var lastIndex;
        var dg = $('#dg3').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'account_number',
                    width: 100,
                    halign: 'center',
                    title: "Account No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            panelWidth: 320,
                            idField: 'account_number',
                            textField: 'account_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Account No',
                            columns: [
                                [{
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
                                }, {
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg3');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }
                    }
                }, {
                    field: 'account_name',
                    width: 200,
                    halign: 'center',
                    title: "Account Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'debit',
                    width: 100,
                    halign: 'center',
                    title: "Debit",
                    formatter: numberformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'credit',
                    width: 100,
                    halign: 'center',
                    title: "Credit",
                    formatter: numberformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'flag',
                    width: 50,
                    halign: 'center',
                    title: "Order",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true
                        }
                    }
                }, ],
            ],
            onClickCell: onClickCell2,
            onBeginEdit: function(rowIndex, row) {
                balance_journal();
            }
        });
    }

    function balance_journal() {
        var rows = $('#dg3').datagrid('getRows');
        var totalrows = rows.length;
        endEditing2();

        if (totalrows > 0) {
            var debit = 0;
            var credit = 0;
            for (let i = 0; i < totalrows; i++) {
                debit += parseFloat(rows[i].debit);
                credit += parseFloat(rows[i].credit);
            }

            $("#balance_debit").numberbox('setValue', debit);
            $("#balance_credit").numberbox('setValue', credit);
        }
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
            var type = $("#type").combobox('getValue');

            // if(type == "others"){
                $('#dg2').datagrid('appendRow', {
                    "action": 0,
                    "currency": "INR",
                    "currency_local": "INR",
                    "igst": 0,
                    "sgst": 0,
                    "cgst": 0,
                    "rate": 1.00,
                });

                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

                var dg = $('#dg2');
                var row = dg.datagrid('getSelected');
                var rowIndex = dg.datagrid('getRowIndex', row);

                var qty = dg.datagrid('getEditor', {
                    index: rowIndex,
                    field: 'qty'
                });

                var price = dg.datagrid('getEditor', {
                    index: rowIndex,
                    field: 'price'
                });

                $(qty.target).numberbox('readonly', false);
                $(price.target).numberbox('readonly', false);
            // }else{
            //     toastr.info("Available only Other Type");
            // }
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg2').datagrid('selectRow', getRowIndex(target));
        $('#dg2').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                var dg = $('#dg2');
                var row = dg.datagrid('getSelected');
                var rowIndex = dg.datagrid('getRowIndex', row);

                var ed = dg.datagrid('getEditor', {
                    index: editIndex,
                    field: 'id'
                });

                if(row.id != undefined){
                    // $.ajax({
                    //     method: 'post',
                    //     url: '<?= base_url('finance/purchase_invoices/deleteSingle') ?>',
                    //     data: {
                    //         id: row.id,
                    //     },
                    //     success: function(result) {
                    //         var result = eval('(' + result + ')');
                    //         toastr.success(result.message);
                    //     },
                    //     error: function(jqXHR, textStatus, errorThrown) {
                    //         toastr.error("This data is already used in other modules, You must delete it First", "Error");
                            
                    //     },
                    //     complete: function(data) {
                    //         $('#dg').datagrid('reload');
                    //     }
                    // });
                }

                $('#dg2').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));

        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;

        var qty = 0;
        var price = 0;
        var total = 0;
        var total2 = 0;
        var rate = 0;
        var igst = 0;
        var sgst = 0;
        var cgst = 0;
        var total_local = 0;
        for (let i = 0; i < totalrows; i++) {
            qty += parseFloat(rows[i].qty);
            price += parseFloat(rows[i].price);
            total += parseFloat(rows[i].total);
            total2 += parseFloat(rows[i].total2);
            rate += parseFloat(rows[i].rate);
            igst += parseFloat(rows[i].igst);
            sgst += parseFloat(rows[i].sgst);
            cgst += parseFloat(rows[i].cgst);
            total_local += parseFloat(rows[i].total_local);
        }

        $('#dg2').datagrid('reloadFooter', [{
            currency: "TOTAL",
            price: price,
            total: total,
            total2: total2,
            rate:rate,
            igst:igst,
            sgst:sgst,
            cgst:cgst,
            total_local:total_local,
        }]);
    }

    function cancelrow(target) {
        $('#dg2').datagrid('cancelEdit', getRowIndex(target));
    }

    //DATAGRID JOURNAL
    var editIndex2 = undefined;

    function endEditing2() {
        if (editIndex2 == undefined) {
            return true
        }
        if ($('#dg3').datagrid('validateRow', editIndex2)) {
            $('#dg3').datagrid('endEdit', editIndex2);
            editIndex2 = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCell2(index, field) {
        if (editIndex2 != index) {
            if (endEditing2()) {
                $('#dg3').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex2 = index;
            } else {
                setTimeout(function() {
                    $('#dg3').datagrid('selectRow', editIndex2);
                }, 0);
            }
        }
    }

    function append2() {
        if (endEditing2()) {
            $('#dg3').datagrid('appendRow', {
                debit: '0',
                credit: '0',
            });
            editIndex2 = $('#dg3').datagrid('getRows').length - 1;
            $('#dg3').datagrid('selectRow', editIndex2).datagrid('beginEdit', editIndex2);
        }
    }

    function removeit3() {
        if (editIndex2 == undefined) {
            return true;
        }

        $('#dg3').datagrid('cancelEdit', editIndex2).datagrid('deleteRow', editIndex2);
        editIndex2 = undefined;
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if(row.status == 0){
                if(row.gl_no == null){
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);

                    $("#type_selection_dp").hide();

                    $("#taxes").numberbox('setValue', row.taxes);

                    $("#type").combobox({
                        readonly: true
                    });
                    // $("#trans_date").datebox('disable');
                    $("#supplier_id").combobox('disable');
                    $("#por_no").combobox('disable');
                    // $("#po_no").combobox('disable');
                    $("#preview").linkbutton('disable');

                    if (row.type != "dp") {
                        $("#type_selection_dp").hide();
                        $("#total_dp").numberbox('clear');
                    } else {
                        $("#type_selection_dp").show();
                        $("#total_dp").numberbox('setValue', 0);
                    }

                    $('#supplier_id').combogrid({
                        url: '<?= base_url('master/suppliers/reads') ?>',
                        panelWidth: 520,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Supplier",
                        columns: [
                            [{
                                field: 'id',
                                title: 'Supplier No',
                                width: 80
                            }, {
                                field: 'name',
                                title: 'Supplier Name',
                                width: 300
                            }, {
                                field: 'state',
                                title: 'State',
                                width: 120
                            }, ]
                        ],
                        onLoadSuccess: function(item_family_load) {
                            $("#supplier_id").combogrid('setValue', row.supplier_id);
                        },
                    });

                    var lastIndex;
                    var dg = $('#dg2').datagrid({
                        url: '<?= base_url('finance/purchase_invoices/reads/') ?>' + window.btoa(row.number),
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
                        onLoadSuccess: function(row){
                            $("#total_sub").numberbox('setValue', row.total_sub);
                            $("#total_igst").numberbox('setValue', row.total_igst);
                            $("#total_sgst").numberbox('setValue', row.total_sgst);
                            $("#total_cgst").numberbox('setValue', row.total_cgst);
                            $("#total_grand").numberbox('setValue', row.total_grand);
                            $("#total_tcs").numberbox('setValue', row.total_tcs);
                            $("#total_tds").numberbox('setValue', row.total_tds);
                            $("#total_freight").numberbox('setValue', row.total_freight);
                            $("#txt_tds").html("("+row.tds+")");
                            $("#nom_tds").html(row.individual + "%");
                        }
                    });

                    addTable2('<?= base_url('finance/purchase_invoices/readJournals/') ?>' + window.btoa(row.number));

                    setTimeout(function() {
                        balance_journal();
                        $("#number").textbox('setValue', row.number);
                    }, 2000);
                } else {
                    toastr.error("Cannot Update because this Purchase Invoice has been created in Posting Journal");
                }
            } else {
                toastr.error("AP Payment Status is closed");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(menus_id, receipt_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/purchase_invoices/number/') ?>" + menus_id + "/" + receipt_date,
            dataType: "html",
            success: function(result) {
                if(result == ""){
                    toastr.info("Please Check Configuration in Serial No First");
                    $("#number").textbox('clear');
                }else{
                    $("#number").textbox('setValue', result);
                }
            }
        });
    }

    function preview() {
        var por_no = $("#por_no").combobox('getText');
        var type = $("#type").combobox('getValue');
        // var po_no = $("#po_no").combobox('getValue');
        var trans_date = $("#trans_date").datebox('getValue');
        var invoice_no = $("#invoice_no").textbox('getValue');
        // var taxes = $("#taxes").numberbox('getValue');
        var journal_type_id = $("#journal_type").combobox('getValue');

        if (trans_date == "" || invoice_no == "" || journal_type_id == "") {
            toastr.info('Please completed your data');
        } else {
            var lastIndex;
            if (por_no != "") {
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('finance/purchase_invoices/datatablesTemp') ?>?por_no=' + window.btoa(por_no) + "&type=" + type,
                    onLoadSuccess: function(row) {
                        $("#total_sub").numberbox('setValue', row.total_sub);
                        $("#total_igst").numberbox('setValue', row.total_igst);
                        $("#total_cgst").numberbox('setValue', row.total_cgst);
                        $("#total_sgst").numberbox('setValue', row.total_sgst);
                        $("#total_tds").numberbox('setValue', row.total_tds);
                        $("#txt_tds").html("("+row.tds+")");
                        $("#nom_tds").html(row.individual + "%");

                        var total_grand = (parseFloat(row.total_sub) + parseFloat(row.total_igst) + parseFloat(row.total_cgst) + parseFloat(row.total_sgst) - parseFloat(row.total_tds));
                        $("#total_grand").numberbox('setValue', (total_grand));

                        addTable2('<?= base_url('finance/purchase_invoices/readJournal/') ?>' + window.btoa(journal_type_id));
                    }
                });
            } else {
                toastr.info('Please completed your data');
            }
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    Swal.fire({
                        title: 'Please Wait for Delete Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('closing/locks/checkLock') ?>",
                            data: "period=" + row.trans_date + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function (lock) {
                                if(lock.total > 0){
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                if(row.status == 0){
                                    // if(row.gl_no == null){
                                        $.ajax({
                                            method: 'post',
                                            url: '<?= base_url('finance/purchase_invoices/delete') ?>',
                                            data: {
                                                number: row.number
                                            },
                                            success: function(result) {
                                                var result = eval('(' + result + ')');
                                                toastr.success(result.message);
                                                swal.close();
                                            },
                                            error: function(jqXHR, textStatus, errorThrown) {
                                                toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                                swal.close();
                                                
                                            },
                                            complete: function(data) {
                                                $('#dg').datagrid('reload');
                                                swal.close();
                                            }
                                        });
                                    // }else{
                                    //     swal.close();
                                    //     toastr.error("Cannot Delete because this Purchase Invoice has been created in Posting Journal");
                                    // }
                                } else {
                                    swal.close();
                                    toastr.error("AP Payment Status is closed");
                                }
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_family_id=" + window.btoa(filter_family_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('finance/purchase_invoices/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/purchase_invoices/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_family_id=" + window.btoa(filter_family_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/purchase_invoices/print/excel') ?>' + url);
    }

    function excelDetail() {
        //EXPORT TO EXCEL
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_family_id=" + window.btoa(filter_family_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status);

        if(filter_type == ""){
            toastr.info("Please Choose Filter Type Date");
        }else{
            window.location.assign('<?= base_url('finance/purchase_invoices/printDetail/excel') ?>' + url);
        }
    }

    function excelJournal() {
        //EXPORT TO EXCEL
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_family_id=" + window.btoa(filter_family_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/purchase_invoices/printJournal/excel') ?>' + url);
    }

    function print_invoicing() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            window.open("<?= base_url('finance/purchase_invoices/print_invoicing/') ?>" + window.btoa(row.number), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please select data in the table first!");
        }

    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

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

    function buttonEdit(value, row, index) {
        if(row.por_no == "GRAND TOTAL"){
            return "-";
        }else{
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
    }

    $(function() {
        $("#total_pph").numberbox('disable');

        $("#total_tds").numberbox({
            onChange: function(total_tds){
                var total_sub = $("#total_sub").numberbox("getValue");
                var total_igst = $("#total_igst").numberbox("getValue");
                var total_cgst = $("#total_cgst").numberbox("getValue");
                var total_sgst = $("#total_sgst").numberbox("getValue");
                var total_grand = (parseFloat(total_sub) + parseFloat(total_igst) + parseFloat(total_cgst) + parseFloat(total_sgst) - parseFloat(total_tds));
                $("#total_grand").numberbox('setValue', (total_grand));
            }
        });

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/purchase_invoices/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('finance/purchase_invoices/datatables/details?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'por_no',
                            title: 'Purchase Receipt',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'po_no',
                            title: 'Purchase Order',
                            halign: 'center',
                            width: 180
                        }, {
                            field: 'item_rm_id',
                            title: 'Part No EBWS',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'item_no',
                            title: 'Part No Supplier',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_family_name',
                            title: 'Product Family',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'asset_code',
                            title: 'Asset Code',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Price',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'total',
                            title: 'Amount',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'igst',
                            title: 'IGST',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'cgst',
                            title: 'CGST',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'sgst',
                            title: 'SGST',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'total2',
                            title: 'Amount<br>After Tax',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var type = $("#type").combobox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number = $("#number").textbox('getValue');
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    var journal_type_id = $("#journal_type").combobox('getValue');
                    var invoice_no = $("#invoice_no").textbox('getValue');
                    var invoice_date = $("#invoice_date").datebox('getValue');
                    var boe_no = $("#boe_no").textbox('getValue');
                    var boe_date = $("#boe_date").datebox('getValue');
                    var taxes = $("#taxes").numberbox('getValue');
                    var payment_term = $("#payment_term").numberbox('getValue');
                    var due_date = $("#due_date").datebox('getValue');
                    var voucher = $("#voucher").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');
                    
                    var balance_debit = $("#balance_debit").numberbox('getValue');
                    var balance_credit = $("#balance_credit").numberbox('getValue');
                    
                    var tcs = $("#tcs").combobox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');
                    var total_tds = $("#total_tds").numberbox('getValue');
                    var total_tcs = $("#total_tcs").numberbox('getValue');
                    var total_freight = $("#total_freight").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');
                    var total_dp = $("#total_dp").numberbox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + trans_date + "&menus_id=<?= $menus_id ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            if (parseFloat(balance_debit) == parseFloat(balance_credit)) {
                                if (por_no == "" || invoice_no == "" || supplier_id == "" || total_grand == "") {
                                    toastr.error("please complete your input data");
                                } else {
                                    $('#dg2').datagrid('acceptChanges');

                                    var rows = $('#dg2').datagrid('getRows');;
                                    var totalrows = rows.length;

                                    var rows2 = $('#dg3').datagrid('getRows');
                                    var totalrows2 = rows2.length;
                                    endEditing2();

                                    for (let x = 0; x < totalrows; x++) {
                                        var check = rows[x];
                                        if(check.currency != "INR" && parseFloat(check.rate) == 1){
                                            toastr.error("Please Ensure The Rate is Correct");
                                            return false;
                                        }
                                    }

                                    combinedPurchaseInvoice = [];
                                    for (let i = 0; i < totalrows; i++) {
                                        var json = rows[i];

                                        combinedPurchaseInvoice.push({
                                            type: type,
                                            trans_date: trans_date,
                                            invoice_date: invoice_date,
                                            number: number,
                                            supplier_id: supplier_id,
                                            journal_type_id: journal_type_id,
                                            invoice_no: invoice_no,
                                            boe_no: boe_no,
                                            boe_date: boe_date,
                                            taxes: taxes,
                                            payment_term: payment_term,
                                            due_date: due_date,
                                            voucher: voucher,
                                            remarks: remarks,
                                            tcs: tcs,
                                            total_tds: total_tds,
                                            total_tcs: total_tcs,
                                            total_freight: total_freight,
                                            total_dp: total_dp,
                                            id: json.id,
                                            asset_code: json.asset_code,
                                            item_family_id: json.item_family_id,
                                            por_no: json.por_no,
                                            po_no: json.po_no,
                                            item_rm_id: json.item_rm_id,
                                            item_no: json.item_rm_number,
                                            item_name: json.item_rm_name,
                                            uom: json.uom,
                                            currency: json.currency,
                                            igst: json.igst,
                                            sgst: json.sgst,
                                            cgst: json.cgst,
                                            rate: json.rate,
                                            qty: json.qty,
                                            price: json.price,
                                            discount: json.discount,
                                            total: json.total,
                                            total2: json.total2,
                                            total_idr: json.total_local,
                                            account_number: json.account_number,
                                            account_type: json.account_type,
                                        });
                                    }

                                    combinedJournals = [];
                                    for (let z = 0; z < totalrows2; z++) {
                                        var json2 = rows2[z];

                                        combinedJournals.push({
                                            number: number,
                                            account_number: json2.account_number,
                                            account_name: json2.account_name,
                                            debit: json2.debit,
                                            credit: json2.credit,
                                            flag: json2.flag,
                                        });
                                    }

                                    var total_debit  = 0;
                                    var total_credit = 0;
                                    for (let jo = 0; jo < totalrows2; jo++) {
                                        var dataJ = rows2[jo];

                                        if(dataJ.account_number == "" || dataJ.account_name == ""){
                                            toastr.error("Account Number Not Valid in Journals, Please Check your data input and save again!!");
                                            return false;
                                        }

                                        total_debit += parseFloat(dataJ.debit);
                                        total_credit += parseFloat(dataJ.credit);
                                    }

                                    if(total_debit == 0 && total_credit == 0){
                                        toastr.error("Debit and Credit still 0, please click add to journal first");
                                        return false;
                                    }

                                    $("#dlg_insert").dialog('close');

                                    Swal.fire({
                                        title: 'Please Wait for Saving Data',
                                        showConfirmButton: false,
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        },
                                    });

                                    $.ajax({
                                        type: "post",
                                        url: "<?= base_url('finance/purchase_invoices/deleteJournal') ?>",
                                        data: "number=" + number,
                                        dataType: "json",
                                        success: function(response) {
                                            $.ajax({
                                                type: "post",
                                                url: '<?= base_url('finance/purchase_invoices/create') ?>',
                                                data: JSON.stringify({ dataPurchaseInvoices: combinedPurchaseInvoice, combinedJournals: combinedJournals}),
                                                dataType: "json",
                                                success: function (result) {
                                                    Swal.close();

                                                    Swal.fire({
                                                        title: 'Posting Journal',
                                                        text: result.message + ", Do you want to save journal posting to ?",
                                                        icon: result.theme,
                                                        showCancelButton: true,
                                                        allowOutsideClick: false,
                                                        allowEscapeKey: false,
                                                        confirmButtonText: 'Yes, Add to Journal!'
                                                    }).then((msg) => {
                                                        if (msg.isConfirmed) {
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
                                                                url: '<?= base_url('finance/purchase_invoices/createPosting') ?>',
                                                                data: {
                                                                    number: number
                                                                },
                                                                dataType: "json",
                                                                success: function(result3) {
                                                                    Swal.close();

                                                                    Swal.fire({
                                                                        title: result3.message,
                                                                        icon: result3.theme,
                                                                        confirmButtonText: 'Ok',
                                                                        allowOutsideClick: false,
                                                                    }).then((result3) => {
                                                                        if (result3.isConfirmed) {
                                                                            window.location.reload();
                                                                        }
                                                                    });

                                                                }
                                                            });
                                                        }
                                                    });
                                                },
                                                error: function(msg){
                                                    Swal.fire({
                                                        title: "Failed to Save Data, Please Try Again",
                                                        icon: "error",
                                                        confirmButtonText: 'Ok',
                                                        allowOutsideClick: false,
                                                    }).then((result3) => {
                                                        if (result3.isConfirmed) {
                                                            $.ajax({
                                                                type: "post",
                                                                url: "<?= base_url('finance/purchase_invoices/delete') ?>",
                                                                data: "number=" + number,
                                                                dataType: "json",
                                                                success: function(response) {
                                                                    window.location.reload();
                                                                }
                                                            });
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    });
                                }
                            } else {
                                toastr.error("Balance Debit Cannot match on Balance Credit");
                            }
                        }
                    });
                }
            }]
        });

        $("#tcs").combobox({
            onChange: function(tcs) {
                var total_sub = $("#total_sub").numberbox('getValue');
                var total_igst = $("#total_igst").numberbox('getValue');
                var total_cgst = $("#total_cgst").numberbox('getValue');
                var total_sgst = $("#total_sgst").numberbox('getValue');
                var grand_total = (parseFloat(total_sub) + parseFloat(total_igst) + parseFloat(total_cgst) + parseFloat(total_sgst));
                $("#total_tcs").numberbox('setValue', (parseFloat(grand_total) * parseFloat(tcs)) / 100);
            }
        });

        $("#filter_type").combobox({
            onChange: function(val) {
                if (val == "PID") {
                    $("#filter_trans_date_from").datebox('enable');
                    $("#filter_trans_date_to").datebox('enable');
                    $("#filter_due_date_from").datebox('disable');
                    $("#filter_due_date_to").datebox('disable');
                } else if (val == "PAY") {
                    $("#filter_trans_date_from").datebox('disable');
                    $("#filter_trans_date_to").datebox('disable');
                    $("#filter_due_date_from").datebox('enable');
                    $("#filter_due_date_to").datebox('enable');
                } else {
                    $("#filter_trans_date_from").datebox('enable');
                    $("#filter_trans_date_to").datebox('enable');
                    $("#filter_due_date_from").datebox('enable');
                    $("#filter_due_date_to").datebox('enable');
                }
            }
        });

        $("#filter_purchase_invoice").combobox({
            url: '<?= base_url('finance/purchase_invoices/readPurchaseInvoice') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Document No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_purchase_receipt").combobox({
            url: '<?= base_url('finance/purchase_invoices/readPurchaseReceipt') ?>',
            valueField: 'por_no',
            textField: 'por_no',
            prompt: "Choose Purchase Receipt",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_purchase_order").combobox({
            url: '<?= base_url('finance/purchase_invoices/readPurchaseOrder') ?>',
            valueField: 'po_no',
            textField: 'po_no',
            prompt: "Choose Purchase Order",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_invoice_no").combobox({
            url: '<?= base_url('finance/purchase_invoices/readInvoice') ?>',
            valueField: 'invoice_no',
            textField: 'invoice_no',
            prompt: "Choose Invoice No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_supplier").combobox({
            url: '<?= base_url('master/suppliers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        //GET SUPPLIER
        $('#supplier_id').combogrid({
            // url: '<?= base_url('master/supplier_items/readSuppliers') ?>',
            url: '<?= base_url('master/suppliers/reads') ?>',
            panelWidth: 520,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier No',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 300
                }, {
                    field: 'state',
                    title: 'State',
                    width: 120
                }]
            ],
            onSelect: function(index, row) {
                var trans_date = $("#trans_date").datebox('getValue');
                var type = $("#type").combobox('getValue');

                $("#payment_term").numberbox("setValue", row.payment_term);
                $("#taxes").numberbox("setValue", row.vat);

                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/purchase_invoices/readDueDate/') ?>" + window.btoa(trans_date) + "/" + row.payment_term,
                    dataType: "text",
                    success: function(due_date) {
                        $("#due_date").datebox('setValue', due_date);
                    }
                });

                if (type != "dp") {
                    if (type == "purchase") {
                        $("#por_no").combobox({
                            url: '<?= base_url('finance/purchase_invoices/readReceipt/purchase?supplier_id=') ?>' + row.id,
                            valueField: 'receipt_no',
                            textField: 'receipt_no',
                            // multiple: true,
                            prompt: "Choose Purchase Order Receipts",
                            onSelect: function(row) {
                                $("#invoice_no").textbox('setValue', row.bc_document);
                                $("#boe_no").textbox('setValue', row.awb_no);
                                $("#journal_type").combobox('setValue', row.journal_type_id);
                            }
                        });
                    }else if (type == "others") {
                        $("#por_no").combobox({
                            url: '<?= base_url('finance/purchase_invoices/readReceipt/other?supplier_id=') ?>' + row.id,
                            valueField: 'receipt_no',
                            textField: 'receipt_no',
                            // multiple: true,
                            prompt: "Choose Purchase Order Receipts",
                            onSelect: function(row) {
                                $("#invoice_no").textbox('setValue', row.bc_document);
                                $("#boe_no").textbox('setValue', row.awb_no);
                                if(row.journal_type_id == null){
                                    $("#journal_type").combobox('setValue', "20241120000001");
                                }else{
                                    $("#journal_type").combobox('setValue', row.journal_type_id);
                                }
                            }
                        });
                    }else{
                        $("#por_no").combobox({
                            url: '<?= base_url('finance/purchase_invoices/readReceipt/service?supplier_id=') ?>' + row.id,
                            valueField: 'receipt_no',
                            textField: 'receipt_no',
                            // multiple: true,
                            prompt: "Choose Purchase Order Receipts",
                            onSelect: function(row) {
                                $("#invoice_no").textbox('setValue', row.bc_document);
                                $("#boe_no").textbox('setValue', row.awb_no);
                                // $("#journal_type").combobox('setValue', row.journal_type_id);
                            }
                        });
                    }
                } else {
                    $("#por_no").combobox({
                        url: '<?= base_url('finance/purchase_invoices/readReceipt/dp?supplier_id=') ?>' + row.id,
                        valueField: 'receipt_no',
                        textField: 'receipt_no',
                        prompt: "Choose Purchase Order Receipts",
                        onSelect: function(row) {
                            $("#invoice_no").textbox('setValue', row.bc_document);
                            $("#total_dp").numberbox('setValue', row.total_dp);
                            $("#journal_type").combobox('setValue', row.journal_type_id);
                        }
                    });
                }
            }
        });

        $("#journal_type").combobox({
            url: '<?= base_url('finance/journal_types/reads/' . base64_encode("PURCHASE INVOICING")) ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Journal Types",
            onSelect: function(row) {
                addTable2('<?= base_url('finance/purchase_invoices/readJournal/') ?>' + window.btoa(row.id));
            }
        });
    });

    function priceformat(value, row) {
        var digits = 5;
        var currency = 'JPY';
        var format = "ja-JP";

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                // style: 'currency',
                // currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function priceformatlocal(value, row) {
        var digits = 2;
        var currency = 'JPY';
        var format = "ja-JP";

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function statusformatInv(value, row) {
        var invoice = value.split('-');
        if (invoice[0] == "INVTMP") {
            return "<b style='color:green;'>TEMPORARY</b>";
        } else if (invoice[0] == "INVWDP") {
            return "<b style='color:green;'>DOWN PAYMENT</b>";
        } else {
            return "<b style='color:red;'>CLOSE</b>";
        }
    }

    function statusStyleInv(value, row, index) {
        var invoice = value.split('-');
        if (invoice[0] == "INVTMP") {
            return 'background-color:#C8FFCC;';
        } else if (invoice[0] == "INVWDP") {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>