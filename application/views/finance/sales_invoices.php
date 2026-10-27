<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Sales Invoice No</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Receipt<br>Status</th>
            <th rowspan="2" data-options="field:'irn',width:100,align:'center',formatter:irnformat,styler:irnStyle">IRN<br>Status</th>
            <th rowspan="2" data-options="field:'gl_no',width:120,align:'center'">GL NO</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'taxes',width:80,halign:'center',align:'right'">Taxes %</th>
            <th rowspan="2" data-options="field:'payment_term',width:100,align:'center'">Payment Term <br>(Days)</th>
            <th rowspan="2" data-options="field:'due_date',width:100,align:'center'">Payment Due</th>
            <th rowspan="2" data-options="field:'currency',width:100,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:120,halign:'center',align:'right',formatter: priceformat">Sub Total</th>
            <!-- <th rowspan="2" data-options="field:'total_vat',width:120,halign:'center',align:'right',formatter: priceformat">VAT</th> -->
            <!-- <th rowspan="2" data-options="field:'total_pph',width:120,halign:'center',align:'right',formatter: priceformat">PPH</th> -->
            <th rowspan="2" data-options="field:'total_grand',width:120,halign:'center',align:'right',formatter: priceformat">Grand Total</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'cancel',width:100,halign:'center',formatter:BtnCancel">Cancel</th>
            <th rowspan="2" data-options="field:'cancel_date',width:150,halign:'center'">Cancel Date</th>
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
<div id="toolbar" style="height: 270px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type Date</span>
                <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="PID">Sales Invoice Date</option>
                    <option value="PAY">Payment Due</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Invoice Date</span>
                <input style="width:30%;" id="filter_trans_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_trans_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Due</span>
                <input style="width:30%;" id="filter_due_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_due_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="filter_customer" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_commercial()"><i class="fa fa-print"></i> Commercial Invoice</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_invoice()"><i class="fa fa-print"></i> Sales Invoice</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Plant</span>
                <input style="width:60%;" name="filter_plant" id="filter_plant" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Note</span>
                <input style="width:60%;" id="filter_dn_number" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                <input style="width:60%;" name="filter_sales_invoice" id="filter_sales_invoice" class="easyui-combobox">
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
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelDetail()"><i class="fa fa-file"></i> Sales Register Report</a> -->
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelJournal()"><i class="fa fa-file"></i> Export Excel Journal</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="irnSync()"><i class="fa fa-database"></i> Generate IRN</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="irnCancel()"><i class="fa fa-times"></i> Cancel IRN</a>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
</div>

<div id="toolbar3">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit3()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 550px; padding:10px; top: 10px; left:5px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Invoice Type</span>
                        <select style="width:60%;" required="" name="type" id="type" panelHeight="auto" class="easyui-combobox">
                            <option value="SALES">SALES INVOICE</option>
                            <option value="SUPPLEMENTARY">SUPPLEMENTARY INVOICE</option>
                            <option value="SCRAP">SCRAP INVOICE</option>
                            <option value="RM">RM / OTHERS</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Invoice Date</span>
                        <input style="width:60%;" id="trans_date" name="trans_date" required="" readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                        <input style="width:60%;" readonly id="number" name="number" class="easyui-textbox" data-options="prompt:'Automatic From Purchase Invoce Date & Customer'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" required="" name="journal_type_id" id="journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer Name</span>
                        <input style="width:60%;" required="" id="customer_id" name="customer_name" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Plant</span>
                        <input style="width:30%;" id="plant" name="plant" required class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Delivery Note</span>
                        <input style="width:60%;" required="" id="dn_number" name="dn_number" class="easyui-combobox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Taxes</span>
                        <input style="width:30%;" id="taxes" name="taxes" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Term</span>
                        <input style="width:30%;" name="payment_term" readonly="" id="payment_term" class="easyui-numberbox" data-options="buttonText:'Days',buttonAlign:'right'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Due</span>
                        <input style="width:60%;" id="due_date" name="due_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Voucher</span>
                        <input style="width:60%;" id="voucher" name="voucher" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Vehicle No</span>
                        <input style="width:60%;" id="vehicle_no" name="vehicle_no" required class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%;" id="remarks" name="remarks" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Sales Invoicing Lists" data-options="singleSelect: true" toolbar="#toolbar2" rownumbers="true" , idField="dn_number">
            <thead>
                <tr>
                    <th rowspan="2" data-options="field:'delete',width:120,formatter:removebtn">Action</th>
                    <th hidden rowspan="2" data-options="field:'id',width:150,editor: {type: 'textbox'}">ID</th>
                    <th rowspan="2" data-options="field:'dn_number',width:150,editor: {type: 'textbox', options: {required: true}}">Delivery Note</th>
                    <th rowspan="2" data-options="field:'so_number',width:160,editor: {type: 'textbox', options: {required: true}}">SO. No</th>
                    <th rowspan="2" data-options="field:'customer_po',width:120, editor: {type: 'textbox', options: {required: true}}">Customer PO</th>
                    <th rowspan="2" data-options="field:'item_fg_id',width:150, editor: {type: 'textbox', options: {required: true}}" hidden>Product Id</th>
                    <th rowspan="2" data-options="field:'item_fg_number',width:170,editor: {type: 'textbox', options: {required: true}}">Product No EBWS</th>
                    <th rowspan="2" data-options="field:'number_customer',width:200,editor: {type: 'textbox', options: {readonly: true}}">Product No Customer</th>
                    <th rowspan="2" data-options="field:'item_fg_name',width:150,editor: {type: 'textbox', options: {readonly: true}}">Description</th>
                    <th rowspan="2" data-options="field:'hsn_code',width:100,editor: {type: 'textbox', options: {readonly: true}}">HSN Code</th>
                    <th rowspan="2" data-options="field:'uom',width:80, editor: {
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
                            onChange: function(value, oldValue) {
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

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });

                                var type = $('#type').combobox('getValue');
                                if(type == 'SCRAP' && parseInt(value) > parseInt(oldValue)){
                                    toastr.error('Qty Scrap cannot more bigger from old qty');
                                    $(ed3.target).numberbox('setValue', oldValue);
                                    return false;
                                }

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
                    <th data-options="field:'currency',width:80, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/currencies/reads') ?>',
                            editable:false,
                            valueField: 'number',
                            textField: 'number',
                            prompt: 'Choose Currencies'
                        }}">Currency</th>
                    <th data-options="field:'price',width:80, halign:'center',align:'right', formatter:priceformat,editor: {type: 'numberbox', 
                        options: {
                            required: true,
                            precision: 4,
                            readonly: true,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var type = $('#type').combobox('getValue');

                                var ed = dg.datagrid('getEditor', {index: rowIndex,field: 'total'});
                                var ed2 = dg.datagrid('getEditor', {index: rowIndex,field: 'qty'});
                                var item_fg_id = dg.datagrid('getEditor', {index: rowIndex, field: 'item_fg_id'});
                                var hsn_code = dg.datagrid('getEditor', {index: rowIndex, field: 'hsn_code'});
                                var igst = dg.datagrid('getEditor', {index: rowIndex, field: 'igst'});
                                var sgst = dg.datagrid('getEditor', {index: rowIndex, field: 'sgst'});
                                var cgst = dg.datagrid('getEditor', {index: rowIndex, field: 'cgst'});
                                var total2 = dg.datagrid('getEditor', {index: rowIndex, field: 'total2'});
                                var rate = dg.datagrid('getEditor', {index: rowIndex, field: 'rate'});
                                var account_number = dg.datagrid('getEditor', {index: rowIndex, field: 'account_number'});
                                var account_name = dg.datagrid('getEditor', {index: rowIndex, field: 'account_name'});
                                var account_type = dg.datagrid('getEditor', {index: rowIndex, field: 'account_type'});

                                var item_fg_id = $(item_fg_id.target).textbox('getValue');
                                var qty = $(ed2.target).numberbox('getValue');
                                var total = (parseFloat(value) * parseFloat(qty));
                                var customer_id = $('#customer_id').combogrid('getValue');
                                var plant = $('#plant').combobox('getValue');

                                $(ed.target).textbox('setValue', total);

                                $.ajax({
                                    type: 'post',
                                    url: '<?= base_url("finance/sales_invoices/readGst/") ?>' + type,
                                    data: {
                                        customer_id: customer_id,
                                        plant: plant,
                                        item_fg_id: item_fg_id
                                    },
                                    dataType: 'json',
                                    success: function (gst) {
                                        $(igst.target).numberbox('setValue', (parseFloat(total) * parseFloat(gst.igst)));
                                        $(sgst.target).numberbox('setValue', (parseFloat(total) * parseFloat(gst.sgst)));
                                        $(cgst.target).numberbox('setValue', (parseFloat(total) * parseFloat(gst.cgst)));
                                        $(total2.target).numberbox('setValue', ((parseFloat(total) * parseFloat(gst.igst)) + (parseFloat(total) * parseFloat(gst.sgst)) + (parseFloat(total) * parseFloat(gst.cgst)) + parseFloat(total)));
                                        $(account_number.target).combogrid('setValue', gst.account_number);
                                        $(account_name.target).textbox('setValue', gst.account_name);
                                        $(account_type.target).combobox('setValue', 'CREDIT');
                                        $(rate.target).numberbox('setValue', 1);
                                    }
                                });
                            }
                        }}">Price</th>
                    <th data-options="field:'total',width:120, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">Amount</th>
                    <th data-options="field:'igst',width:100, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">IGST</th>
                    <th data-options="field:'sgst',width:100, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">SGST</th>
                    <th data-options="field:'cgst',width:100, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">CGST</th>
                    <th data-options="field:'total2',width:120, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">Amount<br>After Tax</th>
                    <th data-options="field:'rate',width:80, halign:'center',align:'right', formatter:priceformat,editor: {type: 'numberbox', 
                        options: {
                            required: true,
                            precision: 2,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total2'
                                });

                                var total2 = $(ed2.target).numberbox('getValue');
                                $(ed.target).textbox('setValue', (parseFloat(value) * parseFloat(total2)));
                            }
                        }}">Rate</th>
                    <th data-options="field:'currency_local',align:'center',width:80">Currency</th>
                    <th data-options="field:'total_local',width:120, formatter:priceformat, halign:'center', align:'right', editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">Amount</th>
                </tr>
            </thead>
        </table>

        <div style="width: 50%; float: left; margin-top:20px;">
            <a style="width: 100%;" class="easyui-linkbutton c2" onclick="addJournal()">Add to Journal</a>
            <br><br>
            <table id="dg3" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbar3"></table>

            <div class="fitem">
                <b style="width:45%; display:inline-block; padding-left: 50px;">BALANCE TOTAL</b>
                <input style="width:18%;" id="balance_debit" name="balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:18%;" id="balance_credit" name="balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
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
                        <select style="width:20%;" id="tcs_option" name="tcs_option" panelHeight="auto" class="easyui-combobox">
                            <option value="0">0%</option>
                            <option value="0.1">0.1%</option>
                            <option value="1">1%</option>
                        </select>
                        <input style="width:40%;" id="tcs" name="tcs" disabled required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">FREIGHT</b>
                        <input style="width:40%;" id="total_freight" name="total_freight" class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">INVOICE VALUE</b>
                        <input style="width:60%;" id="total_grand" name="total_grand" disabled required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem" hidden>
                        <b style="width:35%; display:inline-block;">CONVERT IDR</b>
                        <input style="width:60%;" id="total_grand_local" name="total_grand_local" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
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
        $('#frm_insert').form('clear');
        $("#type").combobox('setValue', 'SALES');

        $('#dg2').datagrid({
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

        proses = "ADD";

        $('#dg3').datagrid('loadData', []);

        $("#trans_date").datebox('enable');
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#customer_id").combobox('enable');
        $("#dn_number").combobox('enable');
        $("#preview").linkbutton('enable');

        $("#tcs_option").combobox('setValue', 0);
        $("#tcs").numberbox('setValue', 0);
        $("#total_freight").numberbox('setValue', 0);
        // $("#trans_date").datebox({
        //     onChange: function(val) {
        //         number(val);
        //     }
        // });

        $('#customer_id').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function(index, row) {
                var trans_date = $("#trans_date").datebox('getValue');
                // number(trans_date, row.number);
                number("<?= $menus_id ?>");

                $("#plant").combobox({
                    url: '<?= base_url('master/customers/readAddress/') ?>' + btoa(row.id),
                    valueField: 'plant',
                    textField: 'plant',
                    prompt: "Choose Plant",
                    editable: false,
                    onSelect: function(plant) {
                        $("#payment_term").numberbox("setValue", plant.payment_term);
                        var type = $('#type').combobox('getValue');

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/sales_invoices/readDueDate/') ?>" + window.btoa(trans_date) + "/" + plant.payment_term,
                            dataType: "text",
                            success: function(due_date) {
                                $("#due_date").datebox('setValue', due_date);
                            }
                        });

                        if (type == "SALES") {
                            $("#dn_number").combobox({
                                url: '<?= base_url('finance/sales_invoices/readDelivery?customer_id=') ?>' + btoa(row.id) + "&plant=" + btoa(plant.plant),
                                valueField: 'delivery_note_no',
                                textField: 'delivery_note_no',
                                prompt: "Choose Delivery Note",
                                editable: false,
                                onSelect: function(plant) {
                                    var trans_date = $("#trans_date").datebox('getValue');

                                    if (trans_date != plant.delivery_note_date) {
                                        toastr.error("Delivery note date (" + plant.delivery_note_date + ") dan Sales Invoice Date (" + trans_date + ") not same");
                                        $("#plant").textbox('clear');
                                        $("#vehicle_no").textbox('clear');
                                        $("#taxes").numberbox("clear");
                                        $("#dn_number").combobox('clear');
                                    } else {
                                        $("#plant").textbox('setValue', plant.plant);
                                        $("#vehicle_no").textbox('setValue', plant.vehicle_no);
                                        $("#taxes").numberbox("setValue", 0);
                                    }
                                }
                            });
                        } else if (type == "RM") {
                            $("#dn_number").combobox({
                                url: '<?= base_url('finance/sales_invoices/readFoc') ?>',
                                valueField: 'number',
                                textField: 'number',
                                prompt: "Choose FOC No",
                                onSelect: function(plant) {
                                    var trans_date = $("#trans_date").datebox('getValue');

                                    if (trans_date != plant.approved_date) {
                                        toastr.error("Approval date (FOC) (" + plant.approved_date + ") dan Sales Invoice Date (" + trans_date + ") not same");
                                        $("#plant").textbox('clear');
                                        $("#vehicle_no").textbox('clear');
                                        $("#taxes").numberbox("clear");
                                        $("#dn_number").combobox('clear');
                                    } else {
                                        $("#plant").textbox('setValue', plant.plant);
                                        $("#vehicle_no").textbox('setValue', plant.vehicle_no);
                                        $("#taxes").numberbox("setValue", 0);
                                    }
                                }
                            });
                        }
                    }
                });
            }
        });
    }

    function addJournal() {
        var customer_id = $("#customer_id").combogrid('getValue');

        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;

        var rows2 = $('#dg3').datagrid('getRows');
        var totalrows2 = rows2.length;
        endEditing2();

        if (totalrows > 0) {
            var data_array = [];
            var data_array2 = [];
            var total_sub = 0;
            var total_local = 0;
            var total_igst = 0;
            var total_sgst = 0;
            var total_cgst = 0;

            for (let i = 0; i < totalrows; i++) {
                var data = {
                    account_number: rows[i].account_number,
                    account_name: rows[i].account_name,
                    account_type: rows[i].account_type,
                    total: rows[i].total
                }

                total_igst += Math.abs(parseFloat(rows[i].igst));
                total_sgst += Math.abs(parseFloat(rows[i].sgst));
                total_cgst += Math.abs(parseFloat(rows[i].cgst));

                if (rows[i].account_type == "DEBIT") {
                    total_sub -= Math.abs(parseFloat(rows[i].total));
                    total_local -= Math.abs(parseFloat(rows[i].total_local));
                } else {
                    total_sub += Math.abs(parseFloat(rows[i].total));
                    total_local += Math.abs(parseFloat(rows[i].total_local));
                }

                data_array.push(data);
            }

            $("#total_sub").numberbox('setValue', Math.abs(total_sub));

            $("#total_igst").numberbox('setValue', total_igst);
            $("#total_cgst").numberbox('setValue', total_cgst);
            $("#total_sgst").numberbox('setValue', total_sgst);

            var total_tcs = $("#tcs").numberbox('getValue');
            var total_freight = $("#total_freight").numberbox('getValue');
            var total_grand = (parseFloat(total_sub) + parseFloat(total_igst) + parseFloat(total_cgst) + parseFloat(total_sgst) + parseFloat(total_tcs) - parseFloat(total_freight));

            $("#total_grand").numberbox('setValue', (total_grand));
            $("#total_grand_local").numberbox('setValue', Math.abs(total_local));

            var tds_val = 0;
            // var arr_tds = ["17000001","17000002","17000003","17000004","17000005","17000006"];
            var arr_ap = ["27000001"];

            for (let z = 0; z < totalrows2; z++) {
                var debit = rows2[z].debit;
                var credit = rows2[z].credit;

                if (rows2[z].account_number == "17000010") {
                    var debit = 0;
                    var credit = total_igst;
                }

                if (rows2[z].account_number == "17000009") {
                    var debit = 0;
                    var credit = total_cgst;
                }

                if (rows2[z].account_number == "17000008") {
                    var debit = 0;
                    var credit = total_sgst;
                }

                if (rows2[z].account_number == "46000001") {
                    var debit = total_freight;
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

                if (jQuery.inArray(rows2[z].account_number, arr_ap) >= 0) {
                    var debit = total_grand;
                    var credit = 0;
                }

                var data2 = {
                    account_number: rows2[z].account_number,
                    account_name: rows2[z].account_name,
                    debit: debit,
                    credit: credit,
                    flag: rows2[z].flag,
                }

                data_array2.push(data2);
            }

            // if (taxes > 0 && vat_val == 0) {
            //     var data2 = {
            //         account_number: "1154105",
            //         account_name: "TAX",
            //         debit: disc_tax,
            //         credit: 0,
            //         flag: "3",
            //     }

            //     data_array2.push(data2);
            // }

            var jsonData = JSON.stringify(data_array);
            var jsonData2 = JSON.stringify(data_array2);

            $.ajax({
                type: "POST",
                url: "<?= base_url('finance/sales_invoices/createJson') ?>",
                data: {
                    jsonData: jsonData,
                    jsonData2: jsonData2,
                },
                success: function(response) {
                    addTable2('<?= base_url('finance/sales_invoices/calculateJournal') ?>');

                    setTimeout(function() {
                        balance_journal();
                    }, 2000);
                },
            });
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
                    width: 110,
                    halign: 'center',
                    title: "Debit",
                    formatter: priceformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 4
                        }
                    }
                }, {
                    field: 'credit',
                    width: 110,
                    halign: 'center',
                    title: "Credit",
                    formatter: priceformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 4
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
            var customer_id = $("#customer_id").combogrid('getValue');
            var plant = $("#plant").combobox('getValue');
            var dn_numberVal = $("#dn_number").combobox('getValue');

            if (plant == "" || dn_numberVal == "" || customer_id == "" || type == "") {
                toastr.info("Please Select Plant, Customer dan Delivery Note");
                return false;
            }

            $('#dg2').datagrid('appendRow', {
                "action": 0
            });

            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

            var dg = $('#dg2');
            var row = dg.datagrid('getSelected');
            var rowIndex = dg.datagrid('getRowIndex', row);

            var dn_number = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'dn_number'
            });
            var so_number = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'so_number'
            });
            var customer_po = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'customer_po'
            });
            var item_fg_id = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'item_fg_id'
            });
            var item_fg_number = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'item_fg_number'
            });
            var number_customer = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'number_customer'
            });
            var item_fg_name = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'item_fg_name'
            });
            var hsn_code = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'hsn_code'
            });
            var currency = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'currency'
            });
            var uom = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'uom'
            });
            var qty = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'qty'
            });
            var price = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'price'
            });

            $(currency.target).combobox('setValue', "INR");

            if (type == "SUPPLEMENTARY") {
                $(qty.target).numberbox('readonly', false);
                $(price.target).numberbox('readonly', false);
                $(dn_number.target).textbox('setValue', '-');
                $(so_number.target).textbox('setValue', '-');
                $(customer_po.target).textbox('setValue', '-');

                $(item_fg_number.target).combogrid({
                    url: "<?= base_url("master/item_fg/readSupplement") ?>",
                    panelWidth: 550,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Product No',
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No EBWS',
                            width: 150
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            width: 250
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 150
                        }]
                    ],
                    onSelect: function(value, rows) {
                        $(item_fg_id.target).textbox('setValue', rows.id);
                        $(number_customer.target).textbox('setValue', rows.number_customer);
                        $(item_fg_name.target).textbox('setValue', rows.name);
                        $(hsn_code.target).textbox('setValue', rows.hsn_code);
                        $(uom.target).combobox('setValue', rows.uom);
                        $(qty.target).numberbox('setValue', 1);
                    }
                });
            } else if (type == "SCRAP") {
                $(qty.target).numberbox('readonly', false);
                $(price.target).numberbox('readonly', false);
                $(customer_po.target).textbox('setValue', '-');

                $(item_fg_number.target).combogrid({
                    url: "<?= base_url("finance/sales_invoices/readScrapItem") ?>",
                    panelWidth: 650,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Product No',
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No EBWS',
                            width: 150
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            width: 250
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 150
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 80
                        }]
                    ],
                    onSelect: function(value, rows) {
                        $(item_fg_id.target).textbox('setValue', rows.id);
                        $(number_customer.target).textbox('setValue', rows.number_customer);
                        $(item_fg_name.target).textbox('setValue', rows.name);
                        $(hsn_code.target).textbox('setValue', rows.hsn_code);
                        $(uom.target).combobox('setValue', rows.uom);
                        $(qty.target).numberbox('setValue', rows.qty);
                        // $(dn_number.target).textbox('setValue', rows.document_no);
                        // $(so_number.target).textbox('setValue', rows.document_no);
                    }
                });
            } else if (type == "RM") {
                $(dn_number.target).textbox('setValue', dn_numberVal);
                $(so_number.target).textbox('setValue', '-');
                $(customer_po.target).textbox('setValue', '-');

                $(item_fg_number.target).combogrid({
                    url: "<?= base_url("finance/sales_invoices/readFocItems?number=") ?>" + btoa(dn_numberVal),
                    panelWidth: 650,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Product No',
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No EBWS',
                            width: 150
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            width: 250
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 150
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 80
                        }]
                    ],
                    onSelect: function(value, rows) {
                        $(item_fg_id.target).textbox('setValue', rows.id);
                        $(number_customer.target).textbox('setValue', rows.number_customer);
                        $(item_fg_name.target).textbox('setValue', rows.name);
                        $(hsn_code.target).textbox('setValue', rows.hsn_code);
                        $(uom.target).combobox('setValue', rows.uom);
                        $(qty.target).numberbox('setValue', rows.qty);
                        $(price.target).numberbox('setValue', rows.price);
                    }
                });
            }
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

                if (row.id != undefined) {
                    $.ajax({
                        method: 'post',
                        url: '<?= base_url('finance/sales_invoices/deleteSingle') ?>',
                        data: {
                            id: row.id
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            toastr.success(result.message);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            //toastr.error("This data is already used in other modules, You must delete it First", "Error");
                        },
                        complete: function(data) {
                            $('#dg').datagrid('reload');
                        },
                    });
                }

                $('#dg2').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));
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
        // if (editIndex2 != index) {
        //     if (endEditing2()) {
        //         $('#dg3').datagrid('selectRow', index).datagrid('beginEdit', index);
        //         editIndex2 = index;
        //     } else {
        //         setTimeout(function() {
        //             $('#dg3').datagrid('selectRow', editIndex2);
        //         }, 0);
        //     }
        // }
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

    function irnSync() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.barcode == "" || row.barcode == null) {
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
                    url: "<?= base_url("api/onyx/login") ?>",
                    data: row,
                    dataType: "json",
                    success: function(j) {
                        Swal.close();

                        if (j.status == "FAILURE") {
                            Swal.fire({
                                title: j.message,
                                icon: 'error',
                                confirmButtonText: 'Ok',
                                allowOutsideClick: false,
                            });

                            return false;
                        }

                        Swal.fire({
                            title: 'Are Your Sure ?',
                            text: "Hai " + j.response.username + " in " + j.response.companyname + " You want Generate IRN Barcode ? with User Mail " + j.response.email,
                            icon: 'warning',
                            showCancelButton: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            confirmButtonText: 'Yes, Do it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url("api/onyx/addInvoice") ?>",
                                    data: {
                                        q: row,
                                        token: j.response.token,
                                        companyId: j.response.companyid
                                    },
                                    dataType: "json",
                                    success: function(msg) {
                                        var i;
                                        var results = "";
                                        if (msg.errors != undefined) {
                                            for (i = 0; i < msg.errors.length; ++i) {
                                                results += " | " + msg.errors[i].msg + " | ";
                                            }
                                        }

                                        if (msg.status == "SUCCESS") {
                                            $.ajax({
                                                type: "post",
                                                url: '<?= base_url('finance/sales_invoices/updateIrn') ?>',
                                                data: {
                                                    irn: msg.response.irn,
                                                    barcode: msg.response.signedQrCode,
                                                    ack_no: msg.response.ackNo,
                                                    ack_date: msg.response.ackDt,
                                                    number: msg.response.no
                                                },
                                                dataType: "json",
                                                success: function(si) {
                                                    $('#dg').datagrid('reload');

                                                    Swal.fire(
                                                        msg.status,
                                                        msg.message + "\n" + results,
                                                        "success"
                                                    );
                                                }
                                            });
                                        } else {
                                            Swal.fire(
                                                msg.status,
                                                msg.message + "\n" + results,
                                                "error"
                                            );
                                        }
                                    }
                                });
                            }
                        });
                    }
                });
            } else {
                toastr.warning("IRN Already Posted");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function irnCancel() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.barcode != "" || row.barcode != null) {
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
                    url: "<?= base_url("api/onyx/login") ?>",
                    data: row,
                    dataType: "json",
                    success: function(j) {
                        Swal.fire('close');
                        Swal.fire({
                            title: 'Are Your Sure ?',
                            text: "Hai " + j.response.username + " in " + j.response.companyname + " You want Cancel IRN Barcode ? with User Mail " + j.response.email,
                            icon: 'warning',
                            showCancelButton: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            confirmButtonText: 'Yes, Do it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url("api/onyx/cancelInvoice") ?>",
                                    data: {
                                        q: row,
                                        token: j.response.token,
                                        companyId: j.response.companyid
                                    },
                                    dataType: "json",
                                    success: function(msg) {
                                        var i;
                                        var results = "";
                                        if (msg.errors != undefined) {
                                            for (i = 0; i < msg.errors.length; ++i) {
                                                results += " | " + msg.errors[i].msg + " | ";
                                            }
                                        }

                                        if (msg.status == "SUCCESS") {
                                            $.ajax({
                                                type: "post",
                                                url: '<?= base_url('finance/sales_invoices/updateIrn') ?>',
                                                data: {
                                                    irn: '',
                                                    barcode: '',
                                                    number: row.number
                                                },
                                                dataType: "json",
                                                success: function(si) {
                                                    $('#dg').datagrid('reload');

                                                    Swal.fire(
                                                        msg.status,
                                                        msg.message + "\n" + results,
                                                        "success"
                                                    );
                                                }
                                            });
                                        } else {
                                            Swal.fire(
                                                msg.status,
                                                msg.message + "\n" + results,
                                                "error"
                                            );
                                        }
                                    }
                                });
                            }
                        });
                    }
                });
            } else {
                toastr.warning("IRN Not Posted");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.status == 0) {
                if (row.gl_no == null) {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);

                    $("#trans_date").datebox('disable');
                    $("#customer_id").combobox('disable');
                    $("#dn_number").combobox('disable');
                    $("#preview").linkbutton('disable');

                    proses = "EDIT";

                    $('#customer_id').combogrid({
                        url: '<?= base_url('master/customers/reads') ?>',
                        panelWidth: 420,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Customer",
                        columns: [
                            [{
                                field: 'number',
                                title: 'Customer No',
                                width: 120
                            }, {
                                field: 'name',
                                title: 'Customer Name',
                                width: 250
                            }, ]
                        ],
                        onLoadSuccess: function(customer_id) {
                            $("#customer_id").combogrid('setValue', row.customer_id);
                        },
                        onSelect: function(index, customer) {
                            var trans_date = $("#trans_date").datebox('getValue');
                            $("#payment_term").numberbox("setValue", customer.payment_term);

                            $("#dn_number").combobox({
                                url: '<?= base_url('finance/sales_invoices/readDelivery?customer_id=') ?>' + customer.id,
                                valueField: 'number',
                                textField: 'number',
                                prompt: "Choose Delivery Note",
                                onLoadSuccess: function(delivery_no) {
                                    $("#dn_number").combobox('setValue', row.dn_number);
                                },
                            });
                        }
                    });

                    var lastIndex;
                    var dg = $('#dg2').datagrid({
                        url: '<?= base_url('finance/sales_invoices/reads/') ?>' + window.btoa(row.number),
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
                        onLoadSuccess: function(rowzz) {
                            $("#total_sub").numberbox('setValue', rowzz.total_sub);
                            $("#total_igst").numberbox('setValue', rowzz.total_igst);
                            $("#total_sgst").numberbox('setValue', rowzz.total_sgst);
                            $("#total_cgst").numberbox('setValue', rowzz.total_cgst);
                            $("#tcs").numberbox('setValue', row.tcs);
                            $("#total_grand").numberbox('setValue', rowzz.total_grand);
                            $("#total_grand_local").numberbox('setValue', rowzz.total_grand_local);
                        }
                    });

                    addTable2('<?= base_url('finance/sales_invoices/readJournals/') ?>' + window.btoa(row.number));

                    setTimeout(function() {
                        balance_journal();
                        $("#number").textbox('setValue', row.number);
                    }, 2000);
                } else {
                    toastr.error("Cannot Update because this Sales Invoice has been created in Posting Journal");
                }
            } else {
                toastr.error("Cannot Update because AR Receipt status is closed");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(id_menu) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/sales_invoices/number/') ?>" + id_menu,
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                } else {
                    $("#number").textbox('setValue', result);
                }
            }
        });
    }

    function preview() {
        var customer_id = $("#customer_id").combogrid('getValue');
        var plant = $("#plant").textbox('getValue');
        var dn_number = $("#dn_number").combobox('getValue');
        var trans_date = $("#trans_date").datebox('getValue');
        var due_date = $("#due_date").datebox('getValue');
        var taxes = $("#taxes").numberbox('getValue');
        var journal_type_id = $("#journal_type").combobox('getValue');

        if (dn_number == "" || trans_date == "" || due_date == "" || plant == "") {
            toastr.info('Please completed your data');
        } else {
            // $("#pph").combobox('setValue', "0");

            var lastIndex;
            var dg = $('#dg2').datagrid({
                url: '<?= base_url('finance/sales_invoices/datatablesTemp') ?>?dn_number=' + window.btoa(dn_number),
                onLoadSuccess: function(row) {
                    $("#total_sub").numberbox('setValue', row.total_sub);
                    $("#total_igst").numberbox('setValue', row.total_igst);
                    $("#total_cgst").numberbox('setValue', row.total_cgst);
                    $("#total_sgst").numberbox('setValue', row.total_sgst);
                    $("#total_grand").numberbox('setValue', row.total_grand);
                    $("#total_grand_local").numberbox('setValue', row.total_local);

                    addTable2('<?= base_url('finance/sales_invoices/readJournal/') ?>' + window.btoa(journal_type_id));
                },
                // onClickRow: function(rowIndex) {
                //     if (lastIndex != rowIndex) {
                //         $(this).datagrid('endEdit', lastIndex);
                //         $(this).datagrid('beginEdit', rowIndex);
                //     }
                //     lastIndex = rowIndex;
                // },
            });
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('closing/locks/checkLock') ?>",
                            data: "period=" + row.trans_date + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function(lock) {
                                if (lock.total > 0) {
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                if (row.status != 0) {
                                    toastr.error("Cannot Delete because Sales Invoice status is closed");
                                    return false;
                                }

                                // if(row.gl_no != null){
                                //     toastr.error("Cannot Delete because this Sales Invoice has been created in Posting Journal");
                                //     return false;
                                // }

                                if (row.cancel != 0) {
                                    toastr.error("Cannot Delete because Sales Invoice status is Cancel");
                                    return false;
                                }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/sales_invoices/delete') ?>',
                                    data: {
                                        number: row.number,
                                        dn_number: row.dn_number,
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');

                                        if (result.theme == "success") {
                                            toastr.success(result.message);
                                        } else {
                                            toastr.error(result.message);
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                    },
                                    complete: function(data) {
                                        $('#dg').datagrid('reload');
                                    }
                                });
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
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('finance/sales_invoices/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/sales_invoices/print') ?>' + url);
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
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/sales_invoices/print/excel') ?>' + url);
    }

    function excelDetail() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_status=" + window.btoa(filter_status);


        if (filter_type == "") {
            toastr.info("Please Choose Filter Type Date");
        } else {
            window.location.assign('<?= base_url('finance/sales_invoices/printDetail/excel') ?>' + url);
        }
    }

    function excelJournal() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/sales_invoices/printJournal/excel') ?>' + url);
    }

    //PRINT INVOICE
    function print_invoice() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            if (row.cancel > 0) {
                toastr.error("Cannot Print Invoce because the status is cancel");
                return false;
            }

            window.open("<?= base_url('finance/sales_invoices/print_dn/') ?>" + window.btoa(row.number), '_blank', 'location=yes,height=570,width=1000,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please checklist in the table first!", "Information");
        }
    }

    //PRINT COMMERCIAL INVOICE
    function print_commercial() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            if (row.cancel > 0) {
                toastr.error("Cannot Print Invoce because the status is cancel");
                return false;
            }

            window.open("<?= base_url('finance/sales_invoices/print_commercial/') ?>" + window.btoa(row.number), '_blank', 'location=yes,height=570,width=1000,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please checklist in the table first!", "Information");
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

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/sales_invoices/datatables') ?>',
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
                    url: '<?= base_url('finance/sales_invoices/datatables/details?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'dn_number',
                            title: 'Delivery Note',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'so_number',
                            title: 'Sales Order',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'customer_po',
                            title: 'Customer PO',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No EBWS',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 300
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
                            title: 'Unit Price',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'total',
                            title: 'Total',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
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
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number_edit = $("#number").textbox('getValue');
                    var customer_id = $("#customer_id").combogrid('getValue');
                    var plant = $("#plant").combobox('getValue');
                    var taxes = $("#taxes").numberbox('getValue');
                    var payment_term = $("#payment_term").numberbox('getValue');
                    var due_date = $("#due_date").datebox('getValue');
                    var vehicle_no = $("#vehicle_no").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');
                    var journal_type_id = $("#journal_type").combobox('getValue');
                    var type = $("#type").combobox('getValue');

                    var balance_debit = $("#balance_debit").numberbox('getValue');
                    var balance_credit = $("#balance_credit").numberbox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');
                    // var total_vat = $("#total_vat").numberbox('getValue');
                    // var total_pph = $("#total_pph").numberbox('getValue');
                    var tcs_option = $("#tcs_option").combobox('getValue');
                    var tcs = $("#tcs").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');
                    var total_grand_local = $("#total_grand_local").numberbox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + trans_date + "&menus_id=<?= $menus_id ?>",
                        dataType: "json",
                        success: function(lock) {
                            if (lock.total > 0) {
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            if (parseFloat(balance_debit) == parseFloat(balance_credit)) {
                                if (due_date == "" || trans_date == "" || customer_id == "" || journal_type_id == "" || vehicle_no == "") {
                                    toastr.error("please complete your input data");
                                } else {
                                    $('#dg2').datagrid('acceptChanges');
                                    var rows = $('#dg2').datagrid('getRows');
                                    var totalrows = rows.length;

                                    var rows2 = $('#dg3').datagrid('getRows');
                                    var totalrows2 = rows2.length;
                                    endEditing2();

                                    for (let z = 0; z < totalrows2; z++) {
                                        var account_number = rows2[z].account_number;
                                        var account_letter = /[a-zA-Z]/.test(account_number);

                                        if (account_letter) {
                                            toastr.error("Account Number Unknown");
                                            return false;
                                        }

                                        if (rows2[z].item_fg_number == "") {
                                            toastr.error("Product No Customer still Empty!");
                                            return false;
                                        }

                                        if (account_number == "") {
                                            toastr.error("Account Number cannot empty!");
                                            return false;
                                        }
                                    }


                                    $.ajax({
                                        type: "post",
                                        url: "<?= base_url('finance/sales_invoices/number/' . $menus_id) ?>",
                                        dataType: "html",
                                        success: function(result) {
                                            if (result == "") {
                                                toastr.info("Please Check Configuration in Serial No First");
                                                return false;
                                            } else {
                                                if (proses == "ADD") {
                                                    var number = result;
                                                } else {
                                                    var number = number_edit;
                                                }

                                                combinedSalesInvoice = [];
                                                for (let i = 0; i < totalrows; i++) {
                                                    var json = rows[i];

                                                    combinedSalesInvoice.push({
                                                        trans_date: trans_date,
                                                        type: type,
                                                        number: number,
                                                        customer_id: customer_id,
                                                        plant: plant,
                                                        journal_type_id: journal_type_id,
                                                        taxes: taxes,
                                                        payment_term: payment_term,
                                                        due_date: due_date,
                                                        vehicle_no: vehicle_no,
                                                        remarks: remarks,
                                                        total_sub: total_sub,
                                                        tcs_option: tcs_option,
                                                        tcs: tcs,
                                                        total_grand: total_grand,
                                                        total_grand_local: total_grand_local,
                                                        id: json.id,
                                                        dn_number: json.dn_number,
                                                        so_number: json.so_number,
                                                        customer_po: json.customer_po,
                                                        item_fg_id: json.item_fg_id,
                                                        uom: json.uom,
                                                        hsn_code: json.hsn_code,
                                                        currency_local: json.currency_local,
                                                        currency: json.currency,
                                                        item_no: json.item_fg_number,
                                                        item_name: json.item_fg_name,
                                                        qty: json.qty,
                                                        igst: json.igst,
                                                        sgst: json.sgst,
                                                        cgst: json.cgst,
                                                        price: json.price,
                                                        rate: json.rate,
                                                        total: json.total,
                                                        total2: json.total2,
                                                        total_local: json.total_local,
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

                                                var total_debit = 0;
                                                var total_credit = 0;
                                                for (let jo = 0; jo < totalrows2; jo++) {
                                                    var dataJ = rows2[jo];

                                                    if (dataJ.account_number == "" || dataJ.account_name == "") {
                                                        toastr.error("Account Number Not Valid in Journals, Please Check your data input and save again!!");
                                                        return false;
                                                    }

                                                    total_debit += parseFloat(dataJ.debit);
                                                    total_credit += parseFloat(dataJ.credit);
                                                }

                                                if (total_debit == 0 && total_credit == 0) {
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
                                                    url: "<?= base_url('finance/sales_invoices/deleteJournal') ?>",
                                                    data: "number=" + number,
                                                    dataType: "json",
                                                    success: function(response) {
                                                        $.ajax({
                                                            type: "post",
                                                            url: '<?= base_url('finance/sales_invoices/create') ?>',
                                                            data: JSON.stringify({
                                                                combinedSalesInvoices: combinedSalesInvoice,
                                                                combinedJournals: combinedJournals
                                                            }),
                                                            dataType: "json",
                                                            success: function(result) {
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
                                                                            url: '<?= base_url('finance/sales_invoices/createPosting') ?>',
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
                                                            error: function(msg) {
                                                                Swal.fire({
                                                                    title: "Failed to Save Data, Please Try Again",
                                                                    icon: "error",
                                                                    confirmButtonText: 'Ok',
                                                                    allowOutsideClick: false,
                                                                }).then((result3) => {
                                                                    if (result3.isConfirmed) {
                                                                        $.ajax({
                                                                            type: "post",
                                                                            url: "<?= base_url('finance/sales_invoices/delete') ?>",
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

        $("#type").combobox({
            onChange: function(val) {
                if (val == "SUPPLEMENTARY") {
                    $("#dn_number").combobox('setValue', '-');
                    $("#dn_number").combobox('readonly', true);
                    $("#preview").linkbutton('disable');
                } else if (val == "RM") {
                    $("#preview").linkbutton('disable');
                } else if (val == "SCRAP") {
                    $("#preview").linkbutton('disable');
                    $("#dn_number").combobox('setValue', '-');
                    $("#dn_number").combobox('readonly', true);
                } else {
                    $("#dn_number").combobox('clear');
                    $("#dn_number").combobox('readonly', false);
                    $("#preview").linkbutton('enable');
                }
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

        $("#filter_customer").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Customers",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(customer) {
                $("#filter_plant").combobox({
                    url: '<?= base_url('master/customers/readAddress/') ?>' + btoa(customer.id),
                    valueField: 'plant',
                    textField: 'plant',
                    prompt: "Choose Plant",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#filter_sales_invoice").combobox({
            url: '<?= base_url('finance/sales_invoices/readSalesInvoices') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Sales Invoice No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_dn_number").combobox({
            url: '<?= base_url('finance/sales_invoices/readDeliveryNote') ?>',
            valueField: 'dn_number',
            textField: 'dn_number',
            prompt: "Choose Delivery Note",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#journal_type").combobox({
            url: '<?= base_url('finance/journal_types/reads/' . base64_encode("SALES INVOICING")) ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Journal Types",
            onSelect: function(row) {
                addTable2('<?= base_url('finance/purchase_invoices/readJournal/') ?>' + window.btoa(row.id));
            }
        });

        $("#tcs_option").combobox({
            onChange: function(tcs) {
                var total_sub = $("#total_sub").numberbox('getValue');
                var total_igst = $("#total_igst").numberbox('getValue');
                var total_cgst = $("#total_cgst").numberbox('getValue');
                var total_sgst = $("#total_sgst").numberbox('getValue');
                var grand_total = (parseFloat(total_sub) + parseFloat(total_igst) + parseFloat(total_cgst) + parseFloat(total_sgst));
                $("#tcs").numberbox('setValue', (parseFloat(grand_total) * parseFloat(tcs)) / 100);
            }
        });
    });

    function priceformat(value, row) {
        if (row.currency != "INR") {
            var digits = 4;
            var format = "ja-JP";
        } else {
            var digits = 2;
            var format = "ja-JP";
        }

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
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
        if (row.cancel == "0") {
            if (value == 0) {
                return "<b style='color:green;'>OPEN</b>";
            } else {
                return "<b style='color:red;'>CLOSED</b>";
            }
        } else {
            return "<b style='color:red;'>CANCEL</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (row.cancel == "0") {
            if (value == 0) {
                return 'background-color:#C8FFCC;';
            } else {
                return 'background-color:#FFC8C8;';
            }
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function irnformat(value, row) {
        if (row.cancel == "0") {
            if (value == "" || value == null) {
                return "<b style='color:orange;'>PENDING</b>";
            } else {
                return "<b style='color:green;'>POSTED</b>";
            }
        } else {
            return "<b style='color:red;'>CANCEL</b>";
        }
    }

    function irnStyle(value, row, index) {
        if (row.cancel == "0") {
            if (value == "" || value == null) {
                return 'background-color:#ffdcab;';
            } else {
                return 'background-color:#C8FFCC;';
            }
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function BtnCancel(val, row) {
        if (val == 0) {
            if (row.status == 0 && (row.irn == "" || row.irn == null)) {
                var onClick = "cancelSi('" + row.number + "')";
                return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-times"></i> Cancel</a>';
            } else {
                return '-';
            }
        } else {
            return 'CANCELED';
        }
    }

    function cancelSi(number) {
        $.messager.prompt('Cancel Sales Invoices', 'Are you sure you want to cancel the sales invoices? <br><br>' +
            '<ul>' +
            '<li>Delivery Note can no longer be used, Status Change to Cancel</li>' +
            '<li>Delivery Order can no longer be used, Status Change to Cancel</li>' +
            '<li>Delivery Schedules can be Opened Again</li>' +
            '<li>Shipping Order can Deleted, Serial No returned to Original</li>' +
            '<li>Journal Posting can Reversal, Please Confirm first to Finance</li>' +
            '</ul><br>Please input The Password for Process',
            function(r) {
                if (r == "CANCELSI") {
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
                        url: "<?= base_url("finance/sales_invoices/cancelInvoice") ?>",
                        data: "number=" + number,
                        dataType: "json",
                        success: function(label) {
                            swal.close();
                            $('#dg').datagrid('reload');

                            if (label.theme == "error") {
                                toastr.error("Cancel Invoices Failed because the status is close");
                            } else {
                                toastr.success("Cancel Sales Invices Success");
                            }
                        }
                    });
                }
            });
    }
</script>