<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status_bank',width:100,align:'center',formatter:statusformat,styler:statusStyle">Reconciliation<br>Status</th>
            <th rowspan="2" data-options="field:'payment_type',width:100,halign:'center'">Payment Type</th>
            <th rowspan="2" data-options="field:'payment_no',width:150,align:'center'">Payment No</th>
            <th rowspan="2" data-options="field:'payment_date',width:100,align:'center'">Payment Date</th>
            <th rowspan="2" data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'bank_account',width:150,halign:'center'">Bank Account</th>
            <th rowspan="2" data-options="field:'payment_by',width:100,align:'center'">Payment By</th>
            <th rowspan="2" data-options="field:'gl_no',width:120,align:'center'">GL No</th>
            <th rowspan="2" data-options="field:'note',width:200,halign:'center'">Note</th>
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
    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Date</span>
                <input style="width:30%;" id="filter_payment_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_payment_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Type</span>
                <select style="width:60%;" id="filter_payment_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="PURCHASE">PURCHASE</option>
                    <option value="OTHERS">OTHERS</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_voucher()"><i class="fa fa-print"></i> Print Voucher</a>
            </div>
        </div>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment No</span>
                <input style="width:60%;" id="filter_payment_no" class="easyui-combobox">
            </div>
        </div>
        <div style="width: 20%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Account</span>
                <input style="width:60%;" id="filter_bank_no" class="easyui-combobox">
            </div>
        </div>
        <div style="width: 20%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="0">PENDING</option>
                    <option value="1">DONE</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment By</span>
                <select style="width:60%;" id="filter_payment_by" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="TRANSFER">TRANSFER</option>
                    <option value="CASH">CASH</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelDetail()"><i class="fa fa-file"></i> Export Excel Detail</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelJournal()"><i class="fa fa-file"></i> Export Excel Journal</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="view_journal()"><i class="fa fa-print"></i> View Journal</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" id="reverse" data-options="plain:true" onclick="reverse()"><i class="fa fa-rotate-left"></i> Reversal</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 550px; padding:10px; top: 5px; left: 5px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Type</span>
                        <select style="width:60%;" id="payment_type" name="payment_type" required class="easyui-combobox" panelHeight="auto">
                            <option value="" selected disabled>Select Payment Type</option>
                            <option value="PURCHASE">PURCHASE</option>
                            <option value="IMPREST">IMPREST</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Date</span>
                        <input style="width:60%;" id="payment_date" name="payment_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment No</span>
                        <input style="width:60%;" readonly id="payment_no" name="payment_no" class="easyui-textbox" data-options="prompt:'Automatic From Payment Date'">
                    </div>
                    <div class="fitem" id="f_supplier">
                        <span style="width:35%; display:inline-block;">Supplier Name</span>
                        <input style="width:60%;" required="" id="supplier_id" name="supplier_id" class="easyui-combobox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Currency</span>
                        <input style="width:60%;" id="currency" name="currency" class="easyui-textbox">
                    </div>
                    <div class="fitem" id="f_imprest">
                        <span style="width:35%; display:inline-block;">Imprest No</span>
                        <input style="width:60%;" id="imprest_account" name="imprest_account" class="easyui-combogrid">
                    </div>
                    <div class="fitem" id="f_account">
                        <span style="width:35%; display:inline-block;">Account No</span>
                        <input style="width:60%;" id="other_account" name="other_account" class="easyui-combogrid">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Account No Name</span>
                        <input style="width:60%;" id="other_account_name" name="other_account_name" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                        <input style="width:60%;" id="purchase_invoice" name="purchase_invoice" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" required="" name="journal_type_id" id="journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Bank Account</span>
                        <input style="width:60%;" required="" id="bank_account" name="bank_account" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment By</span>
                        <select style="width:60%;" id="payment_by" name="payment_by" class="easyui-combobox" panelHeight="auto">
                            <option value="TRANSFER">TRANSFER</option>
                            <option value="CASH">CASH</option>
                            <option value="CHEQUE">CHEQUE</option>
                        </select>
                    </div>
                    <div class="fitem" id="f_cheque_no">
                        <span style="width:35%; display:inline-block;">Cheque No</span>
                        <input style="width:60%;" id="cheque_no" name="cheque_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Note</span>
                        <input style="width:60%;" id="note" name="note" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>
        <div id="toolbar2">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append_dp()"><i class="fa fa-eye"></i> Find Down Payment</a>
        </div>
        <div id="toolbar3">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit3()"><i class="fa fa-times"></i> Remove</a>
        </div>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="AP Payment Lists" toolbar="#toolbar2" data-options="singleSelect: true" idField="purchase_invoice">
            <thead>
                <tr>
                    <th data-options="field:'delete',width:120, formatter:removebtn">#</th>
                    <th hidden data-options="field:'id',width:150, editor: {type: 'textbox'}">ID</th>
                    <th hidden data-options="field:'trans_date',width:150, editor: {type: 'textbox', options: { readonly: true }}">Trans Date</th>
                    <th data-options="field:'purchase_invoice',width:150, editor: {type: 'textbox'}">Purchase Invoice</th>
                    <th data-options="field:'supplier_invoice',width:150, editor: {type: 'textbox'}">Description</th>
                    <th data-options="field:'currency',align:'center',width:80, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/currencies/reads') ?>',
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose Currency',
                            editable:false,
                            panelHeight: 'auto',
                            required: true,
                        }}">Currency</th>
                    <th data-options="field:'rate',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:4}}">Rate</th>
                    <th hidden data-options="field:'rate2',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:4}}">Rate 2</th>
                    <th data-options="field:'amount',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2, readonly:true}}">Amount</th>
                    <th data-options="field:'balance',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2, readonly:true}}">Balance</th>
                    <th data-options="field:'payment',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2}}">Payment</th>
                    <th data-options="field:'remarks',width:100, editor: {type: 'textbox'}">Remarks</th>
                    <th data-options="field:'account_number',width:100, halign:'center', editor: {
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
                    <th data-options="field:'account_name',width:150, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th data-options="field:'account_type',width:120, halign:'center', editor: {
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
            </thead>
        </table>
        <div style="width: 68%; float: left; margin-top:20px;">
            <div style="float: left; width: 30%; ">
                <a style="width: 90%; height: 50px; padding:10px;" class="easyui-linkbutton c2" onclick="addJournal()">Add to Journal</a>
            </div>
            <div style="float: left; width: 30%; border:4px solid green; padding:10px;" id="showExchange">
                <p style="font-size: 16px !important; margin:0;"><b style="font-size: 16px !important;" id="exchange"></b></p>
            </div>
        </div>
        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">Total Payment</b>
                        <input style="width:60%;" id="total_payment" name="total_payment" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
        <div style="width: 80%; float: left; margin-top:10px;">
            <table id="dg3" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbar3">
                <thead>
                    <tr>
                        <th rowspan="2" data-options="field:'account_number',halign:'center',width:100, editor: {
                            type: 'combogrid',
                            options: {
                                url: '<?= base_url('finance/account_coa/reads') ?>',
                                panelWidth: 320,
                                idField: 'account_number',
                                textField: 'account_number',
                                mode: 'remote',
                                fitColumns: true,
                                readonly: true,
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
                        }">Account No</th>
                        <th rowspan="2" data-options="field:'account_name',halign:'center',width:200, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                        <th rowspan="2" data-options="field:'description',halign:'center',width:200, editor: {type: 'textbox', options: {required: true}}">Description</th>
                        <th colspan="2" data-options="field:'',width:150">Original Currency</th>
                        <th colspan="2" data-options="field:'',width:150">Local Currency</th>
                        <th rowspan="2" data-options="field:'flag',width:50,halign:'center',editor: {type: 'numberbox', options: {required: true}}">Index</th>
                    </tr>
                    <tr>
                        <th data-options="field:'debit',width:120,halign:'center',align:'right',formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision:2}}">Debit</th>
                        <th data-options="field:'credit',width:120,halign:'center',align:'right',formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision:2}}">Credit</th>
                        <th data-options="field:'local_debit',width:120,halign:'center',align:'right',formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision:2}}">Debit</th>
                        <th data-options="field:'local_credit',width:120,halign:'center',align:'right',formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision:2}}">Credit</th>
                    </tr>
                </thead>
            </table>
            <div class="fitem">
                <b style="width:46%; display:inline-block; padding-left: 50px;">BALANCE TOTAL</b>
                <input style="width:11%;" id="balance_debit" name="balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:11%;" id="balance_credit" name="balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:11%;" id="local_balance_debit" name="local_balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:11%;" id="local_balance_credit" name="local_balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
            </div>
        </div>
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

<div id="dlg_dp" class="easyui-dialog" title="Data Down Payment" data-options="closed: true, modal:true" style="width: 700px; top: 20px; left: 20px;">
    <table id="dgdp" class="easyui-datagrid" style="width: 100%; height: 400px;" data-options="singleSelect: false">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'payment_no',halign:'center',width:150">Payment No</th>
                <th data-options="field:'purchase_invoice',halign:'center',width:150">Purchase Invoice No</th>
                <th data-options="field:'supplier_invoice',halign:'center',width:150">Invoice No</th>
                <th data-options="field:'payment',width:120,halign:'center',align:'right',formatter:numberformat">Payment</th>
            </tr>
        </thead>
    </table>
</div>
<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#dg3').datagrid('loadData', []);
        $("#showExchange").hide();
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
        $('#frm_insert').form('clear');
        $("#payment_date").datebox('enable');
        $("#payment_type").combobox('enable');
        $("#supplier_id").combogrid('enable');
        $("#purchase_invoice").combogrid('enable');
        $("#payment_by").combobox('setValue', "TRANSFER");
        $("#f_cheque_no").hide();
        $("#f_account").hide();
        $("#f_imprest").hide();

        $("#payment_date").datebox({
            onChange: function(val) {
                number("<?= $menus_id ?>", val);
            }
        });

        action = "ADD";
    }

    function reverse() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            if (row.gl_no == null) {
                toastr.error("Please Posting Journal first!");
                return false;
            }

            $.ajax({
                type: "post",
                url: "<?= base_url('finance/ap_payments/checkReversal') ?>",
                data: "payment_no=" + row.payment_no,
                dataType: "json",
                success: function(reverseData) {
                    if (parseInt(reverseData.total) > 0) {
                        toastr.error("This Payment already Reversal on " + reverseData.number);
                        return false;
                    }

                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);

                    setTimeout(function() {
                        balance_journal();
                        number("<?= $menus_id ?>", "<?= date("Y-m-d") ?>");
                    }, 2000);

                    $("#note").textbox('setValue', 'REVERSAL from ' + row.payment_no);
                    $("#total_payment").numberbox('setValue', row.total_ap);
                    $("#payment_date").datebox('enable');
                    $("#payment_type").combobox('enable');
                    $("#supplier_id").combogrid('enable');
                    $("#purchase_invoice").combogrid('enable');
                    $("#showExchange").hide();
                    var payment_by = $("#payment_by").combobox('getValue');
                    if (payment_by == "CHEQUE") {
                        $("#f_cheque_no").show();
                    } else {
                        $("#f_cheque_no").hide();
                    }
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
                        onLoadSuccess: function(load_supplier) {
                            $("#supplier_id").combogrid('setValue', row.supplier_id);
                            $("#journal_type").combobox('setValue', row.journal_type);
                        },
                        onSelect: function(supplier) {
                            $("#purchase_invoice").combogrid({
                                url: '<?= base_url('finance/ap_payments/readInvoices/') ?>' + btoa(supplier.id),
                                panelWidth: 500,
                                idField: 'purchase_invoice',
                                textField: 'purchase_invoice',
                                // mode: 'remote',
                                fitColumns: true,
                                prompt: "Choose Purchase invoice No",
                                columns: [
                                    [{
                                        field: 'purchase_invoice',
                                        title: 'Purchase Invoice No',
                                        width: 200
                                    }, {
                                        field: 'supplier_invoice',
                                        title: 'Supplier Invoice No',
                                        width: 250
                                    }, ]
                                ],
                                onLoadSuccess: function(load_invoice) {
                                    $("#purchase_invoice").combogrid('setValue', row.purchase_invoice);
                                },
                            });
                        }
                    });


                    preview('<?= base_url('finance/ap_payments/readReverse/') ?>' + window.btoa(row.payment_no));
                    addTable2('<?= base_url('finance/ap_payments/readJournalReverse/') ?>' + window.btoa(row.payment_no) + "/" + window.btoa(row.journal_type) + "/" + window.btoa(row.bank_account));
                }
            });

            action = "ADD";
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function addJournal() {
        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;
        var rows2 = $('#dg3').datagrid('getRows');
        var totalrows2 = rows2.length;
        endEditing2();
        var payment_no = $("#payment_no").textbox('getValue');
        var journal_type = $("#journal_type").combobox('getValue');
        var bank_account = $("#bank_account").combogrid('getValue');
        var payment_date = $("#payment_date").datebox('getValue');
        if (journal_type != "" && bank_account != "") {
            if (totalrows > 0) {
                var data_array = [];
                var data_array2 = [];
                var total_payment = 0;
                var total_currency = 1;
                var currency = "";
                var trans_date = "";
                for (let i = 0; i < totalrows; i++) {
                    //if(rows[i].balance >= rows[i].payment){
                    var data = {
                        account_number: rows[i].account_number,
                        account_name: rows[i].account_name,
                        account_type: rows[i].account_type,
                        purchase_invoice: rows[i].purchase_invoice,
                        description: rows[i].supplier_invoice,
                        currency: rows[i].currency,
                        payment_date: payment_date,
                        trans_date: rows[i].trans_date,
                        payment: rows[i].payment,
                        rate: rows[i].rate,
                        rate2: rows[i].rate2
                    }
                    if (currency == rows[i].currency) {
                        total_currency += 1;
                        currency = rows[i].currency;
                        rate = rows[i].rate;
                    } else {
                        total_currency += 0;
                        currency = rows[i].currency;
                        rate = rows[i].rate;
                    }

                    if (rows[i].account_type == "DEBIT") {
                        total_payment += parseFloat(rows[i].payment);
                    } else if (rows[i].account_type == "CREDIT") {
                        total_payment -= parseFloat(rows[i].payment);
                    }

                    data_array.push(data);
                    // }else{
                    //     toastr.error("Balance must >= Payment, Please check again your data");
                    //     return false;
                    // }
                    trans_date = rows[i].trans_date;
                }
                for (let z = 0; z < totalrows2; z++) {
                    var data2 = {
                        account_number: rows2[z].account_number,
                        account_name: rows2[z].account_name,
                        flag: rows2[z].flag,
                    }
                    data_array2.push(data2);
                }
                $("#total_payment").numberbox('setValue', total_payment);
                var jsonData = JSON.stringify(data_array);
                var jsonData2 = JSON.stringify(data_array2);
                if (totalrows == total_currency) {
                    if (currency != "INR") {
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/ap_payments/readExchangeRate') ?>",
                            data: "payment_date=" + payment_date + "&trans_date=" + trans_date + "&currency=" + currency,
                            dataType: "html",
                            success: function(exchange) {
                                $("#exchange").html(exchange);
                                $("#showExchange").show();
                            }
                        });
                    }
                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('finance/ap_payments/createJson') ?>",
                        data: {
                            jsonData: jsonData,
                            jsonData2: jsonData2,
                        },
                        success: function(response) {
                            addTable2('<?= base_url('finance/ap_payments/calculateJournal/') ?>' + window.btoa(journal_type) + "/" + window.btoa(bank_account) + "/" + btoa(rate));
                            setTimeout(function() {
                                balance_journal();
                            }, 2000);
                        },
                    });
                } else {
                    toastr.error("Please correct the currency is not the same");
                }
            } else {
                toastr.warning("please selections your data in table first");
            }
        } else {
            toastr.info("Please Select Journal Type & Bank Account");
        }
    }

    function addTable2(link = "") {
        var lastIndex;
        var dg = $('#dg3').datagrid({
            url: link,
            singleSelect: true,
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
            var local_debit = 0;
            var local_credit = 0;
            for (let i = 0; i < totalrows; i++) {
                debit += parseFloat(rows[i].debit);
                credit += parseFloat(rows[i].credit);
                local_debit += parseFloat(rows[i].local_debit);
                local_credit += parseFloat(rows[i].local_credit);
            }
            $("#balance_debit").numberbox('setValue', debit);
            $("#balance_credit").numberbox('setValue', credit);
            $("#local_balance_debit").numberbox('setValue', local_debit);
            $("#local_balance_credit").numberbox('setValue', local_credit);
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
            var supplier_id = $("#supplier_id").combogrid('getValue');
            var payment_type = $("#payment_type").combobox('getValue');
            var other_account = $("#other_account").combogrid('getValue');
            var imprest_account = $("#imprest_account").combogrid('getValue');
            var other_account_name = $("#other_account_name").textbox('getValue');
            var currency = $("#currency").textbox('getValue');
            if (currency == "") {
                var currencyF = "INR";
            } else {
                var currencyF = currency;
            }
            if (payment_type == "OTHERS" || payment_type == "IMPREST") {
                $('#dg2').datagrid('appendRow', {
                    amount: '0',
                    balance: '0',
                    currency: 'INR',
                    rate: 1,
                    account_number: other_account,
                    account_name: other_account_name,
                    account_type: 'DEBIT',
                });
            } else {
                if (supplier_id != "") {
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('finance/balance_suppliers/readCoa') ?>",
                        data: "q=" + supplier_id,
                        dataType: "json",
                        success: function(json) {
                            if (json.length > 0) {
                                $('#dg2').datagrid('appendRow', {
                                    amount: '0',
                                    balance: '0',
                                    currency: currencyF,
                                    rate: 1,
                                    account_number: json[0].account_number,
                                    account_name: json[0].account_name,
                                    account_type: 'DEBIT',
                                });
                            } else {
                                toastr.error("Plase Input Account No in Balance Supplier Module");
                            }
                        }
                    });
                } else {
                    toastr.error("Plase Select Supplier First!!");
                }
            }
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    function append_dp() {
        var supplier_id = $("#supplier_id").combogrid('getValue');
        var purchase_invoice = $("#purchase_invoice").combogrid('getValue');

        $("#dlg_dp").dialog('open');

        if (supplier_id != "") {
            $.ajax({
                url: '<?= base_url('finance/ap_payments/readDp') ?>',
                type: 'POST',
                data: {
                    supplier_id: supplier_id,
                },
                dataType: 'json',
                success: function(result) {
                    if (result.length > 0) {
                        $('#dgdp').datagrid('loadData', result).datagrid('loaded');
                    } else {
                        toastr.info("Data Down Payment Not Found");
                    }
                }
            });
        } else {
            toastr.info("Please Choose Supplier!!");
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

                $('#dg2').datagrid('deleteRow', getRowIndex(target));
                addJournal();
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
            if (row.status == 0) {
                if (row.gl_no == null) {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);
                    $("#total_payment").numberbox('setValue', row.total_ap);
                    $("#payment_date").datebox('disable');
                    $("#payment_type").combobox('disable');
                    $("#supplier_id").combogrid('disable');
                    $("#purchase_invoice").combogrid('disable');
                    $("#showExchange").hide();
                    var payment_by = $("#payment_by").combobox('getValue');
                    if (payment_by == "CHEQUE") {
                        $("#f_cheque_no").show();
                    } else {
                        $("#f_cheque_no").hide();
                    }
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
                        onLoadSuccess: function(load_supplier) {
                            $("#supplier_id").combogrid('setValue', row.supplier_id);
                        },
                        onSelect: function(supplier) {
                            $("#purchase_invoice").combogrid({
                                url: '<?= base_url('finance/ap_payments/readInvoices/') ?>' + btoa(supplier.id),
                                panelWidth: 300,
                                idField: 'purchase_invoice',
                                textField: 'purchase_invoice',
                                // mode: 'remote',
                                fitColumns: true,
                                prompt: "Choose Purchase invoice No",
                                columns: [
                                    [{
                                        field: 'purchase_invoice',
                                        title: 'Purchase Invoice No',
                                        width: 150
                                    }, {
                                        field: 'supplier_invoice',
                                        title: 'Supplier Invoice No',
                                        width: 150
                                    }]
                                ],
                                onLoadSuccess: function(load_invoice) {
                                    $("#purchase_invoice").combogrid('setValue', row.purchase_invoice);
                                    $("#journal_type").combobox('setValue', row.journal_type);
                                },
                            });
                        }
                    });
                    preview('<?= base_url('finance/ap_payments/reads/') ?>' + window.btoa(row.payment_no));
                    addTable2('<?= base_url('finance/ap_payments/readJournals/') ?>' + window.btoa(row.payment_no) + "/" + window.btoa(row.journal_type) + "/" + window.btoa(row.bank_account));
                    setTimeout(function() {
                        balance_journal();
                        $("#payment_no").textbox('setValue', row.payment_no);
                    }, 2000);

                    action = "EDIT";
                } else {
                    toastr.error("Cannot Update because this AP Payment has been created in Posting Journal");
                }
            } else {
                toastr.error("Cannot Update because payment status is closed");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    //NOMOR AUTOMATIC
    function number(id_menu, trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/ap_payments/number/') ?>" + id_menu + "/" + btoa(trans_date),
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                    $("#payment_no").textbox('clear');
                } else {
                    $("#payment_no").textbox('setValue', result);
                }
            }
        });
    }

    var editIndex = undefined;

    function preview(link = "") {
        var purchase_invoice = $("#purchase_invoice").combogrid('getText');
        if (link == "") {
            var linked = '<?= base_url('finance/ap_payments/datatablesTemp') ?>?purchase_invoice=' + window.btoa(purchase_invoice);
        } else {
            var linked = link;
        }
        if (purchase_invoice == "") {
            toastr.info('Please select purchase invoice');
        } else {
            var dg = $('#dg2').datagrid({
                url: linked,
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
                            data: "period=" + row.payment_date + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function(lock) {
                                if (lock.total > 0) {
                                    Swal.close();
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                // if (row.gl_no == null) {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/ap_payments/delete') ?>',
                                    data: {
                                        payment_no: row.payment_no
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        toastr.success(result.message);
                                        Swal.close();
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                    },
                                    complete: function(data) {
                                        $('#dg').datagrid('reload');
                                    }
                                });
                                // } else {
                                //     toastr.error("Cannot Delete because this AP Payment has been created in Posting Journal");
                                // }
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
        var filter_payment_type = $("#filter_payment_type").combobox('getValue');
        var filter_payment_date_from = $("#filter_payment_date_from").datebox('getValue');
        var filter_payment_date_to = $("#filter_payment_date_to").datebox('getValue');
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_payment_by = $("#filter_payment_by").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var url = "?filter_payment_type=" + window.btoa(filter_payment_type) +
            "&filter_payment_date_from=" + window.btoa(filter_payment_date_from) +
            "&filter_payment_date_to=" + window.btoa(filter_payment_date_to) +
            "&filter_payment_no=" + window.btoa(filter_payment_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_payment_by=" + window.btoa(filter_payment_by);
        $('#dg').datagrid({
            url: '<?= base_url('finance/ap_payments/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/ap_payments/print') ?>' + url);
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT TO EXCEL
    function excel() {
        var filter_payment_type = $("#filter_payment_type").combobox('getValue');
        var filter_payment_date_from = $("#filter_payment_date_from").datebox('getValue');
        var filter_payment_date_to = $("#filter_payment_date_to").datebox('getValue');
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_payment_by = $("#filter_payment_by").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var url = "?filter_payment_type=" + window.btoa(filter_payment_type) +
            "&filter_payment_date_from=" + window.btoa(filter_payment_date_from) +
            "&filter_payment_date_to=" + window.btoa(filter_payment_date_to) +
            "&filter_payment_no=" + window.btoa(filter_payment_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_payment_by=" + window.btoa(filter_payment_by);
        window.location.assign('<?= base_url('finance/ap_payments/print/excel') ?>' + url);
    }

    function excelDetail() {
        var filter_payment_type = $("#filter_payment_type").combobox('getValue');
        var filter_payment_date_from = $("#filter_payment_date_from").datebox('getValue');
        var filter_payment_date_to = $("#filter_payment_date_to").datebox('getValue');
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_payment_by = $("#filter_payment_by").combobox('getValue');
        var url = "?filter_payment_type=" + window.btoa(filter_payment_type) +
            "&filter_payment_date_from=" + window.btoa(filter_payment_date_from) +
            "&filter_payment_date_to=" + window.btoa(filter_payment_date_to) +
            "&filter_payment_no=" + window.btoa(filter_payment_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_payment_by=" + window.btoa(filter_payment_by);
        window.location.assign('<?= base_url('finance/ap_payments/printDetail/excel') ?>' + url);
    }

    function excelJournal() {
        var filter_payment_type = $("#filter_payment_type").combobox('getValue');
        var filter_payment_date_from = $("#filter_payment_date_from").datebox('getValue');
        var filter_payment_date_to = $("#filter_payment_date_to").datebox('getValue');
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_payment_by = $("#filter_payment_by").combobox('getValue');
        var url = "?filter_payment_type=" + window.btoa(filter_payment_type) +
            "&filter_payment_date_from=" + window.btoa(filter_payment_date_from) +
            "&filter_payment_date_to=" + window.btoa(filter_payment_date_to) +
            "&filter_payment_no=" + window.btoa(filter_payment_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_payment_by=" + window.btoa(filter_payment_by);
        window.location.assign('<?= base_url('finance/ap_payments/printJournal/excel') ?>' + url);
    }

    function view_journal() {
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');
        if (filter_payment_no == "") {
            toastr.warning("Please select Payment No!");
        } else {
            window.open("<?= base_url('finance/ap_payments/view_journal/') ?>" + window.btoa(filter_payment_no), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        }
    }

    function print_voucher() {
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');
        if (filter_payment_no == "") {
            toastr.warning("Please select Payment No!");
        } else {
            window.open("<?= base_url('finance/ap_payments/print_voucher/') ?>" + window.btoa(filter_payment_no), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        }
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_ap_payments.xls') ?>');
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
    $(function() {
        $('#dlg_dp').dialog({
            buttons: [{
                text: 'Add DP',
                handler: function() {
                    var rows = $('#dgdp').datagrid('getSelections');
                    var totalrows = rows.length;

                    if (totalrows == 0) {
                        toastr.warning("Please Select data Down Payment in table first");
                        return false;
                    }

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].amount == 0) {
                            var amount = rows[i].payment;
                        } else {
                            var amount = rows[i].amount;
                        }

                        if (rows[i].balance == 0) {
                            var balance = rows[i].payment;
                        } else {
                            var balance = (parseFloat(rows[i].balance) - parseFloat(rows[i].payment));
                        }

                        if (rows[i].account_type == "DEBIT") {
                            var account_type = "CREDIT";
                        } else {
                            var account_type = "DEBIT";
                        }

                        $('#dg2').datagrid('appendRow', {
                            purchase_invoice: rows[i].payment_no,
                            supplier_invoice: rows[i].supplier_invoice,
                            currency: rows[i].currency,
                            rate: 1,
                            amount: amount,
                            balance: balance,
                            payment: rows[i].payment,
                            remarks: rows[i].remarks,
                            account_number: rows[i].account_number,
                            account_name: rows[i].account_name,
                            account_type: account_type,
                        });
                    }

                    $("#dlg_dp").dialog('close');
                },
            }]
        });

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/ap_payments/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.payment_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('finance/ap_payments/datatables/details?payment_no=') ?>' + window.btoa(row.payment_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'purchase_invoice',
                            title: 'Purchase Invoice',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'supplier_invoice',
                            title: 'Supplier Invoice',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'bank_account',
                            title: 'Bank Account',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'amount',
                            title: 'Amount',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'balance',
                            title: 'Balance',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'payment',
                            title: 'Payment',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'account_number',
                            title: 'Account No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'account_type',
                            title: 'Debt/Credit',
                            align: 'center',
                            width: 80
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
                    var payment_type = $("#payment_type").combobox('getValue');
                    var payment_date = $("#payment_date").datebox('getValue');
                    var payment_no = $("#payment_no").textbox('getValue');
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    var journal_type_id = $("#journal_type").combobox('getValue');
                    var bank_account = $("#bank_account").combogrid('getValue');
                    var other_account = $("#other_account").combogrid('getValue');
                    var imprest_account = $("#imprest_account").combogrid('getValue');
                    var payment_by = $("#payment_by").combobox('getValue');
                    var cheque_no = $("#cheque_no").textbox('getValue');
                    var note = $("#note").textbox('getValue');
                    var total_payment = $("#total_payment").numberbox('getValue');
                    var balance_debit = $("#balance_debit").numberbox('getValue');
                    var balance_credit = $("#balance_credit").numberbox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('finance/ap_payments/number/' . $menus_id . '/') ?>" + btoa(payment_date),
                        dataType: "html",
                        success: function(resultNumber) {
                            if (resultNumber == "") {
                                toastr.info("Please Check Configuration in Serial No First");
                                return false;
                            } else {
                                if (action == "ADD") {
                                    var payment_no = resultNumber;
                                } else {
                                    var payment_no = $("#payment_no").textbox('getValue');
                                }

                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url('closing/locks/checkLock') ?>",
                                    data: "period=" + payment_date + "&menus_id=<?= $menus_id ?>",
                                    dataType: "json",
                                    success: function(lock) {
                                        if (lock.total > 0) {
                                            toastr.error("This period is not active by Accounting");
                                            return false;
                                        }

                                        if (parseFloat(balance_debit) == parseFloat(balance_credit)) {
                                            //if (parseFloat(balance_debit) == parseFloat(total_payment)) {
                                            if (purchase_invoice == "" || bank_account == "" || payment_date == "" || payment_by == "") {
                                                toastr.error("please complete your input data");
                                            } else {
                                                $('#dg2').datagrid('acceptChanges');
                                                var rows = $('#dg2').datagrid('getRows');
                                                var totalrows = rows.length;

                                                var rows2 = $('#dg3').datagrid('getRows');
                                                var totalrows2 = rows2.length;
                                                endEditing2();

                                                combinedAp = [];
                                                for (let i = 0; i < totalrows; i++) {
                                                    var json = rows[i];

                                                    if (json.account_number == "" || json.account_number == null) {
                                                        toastr.error("Account No Not valid, Please Check your data input and save again!!");
                                                        return false;
                                                    }

                                                    combinedAp.push({
                                                        payment_type: payment_type,
                                                        payment_date: payment_date,
                                                        payment_no: payment_no,
                                                        supplier_id: supplier_id,
                                                        journal_type_id: journal_type_id,
                                                        bank_account: bank_account,
                                                        other_account: other_account,
                                                        imprest_account: imprest_account,
                                                        payment_by: payment_by,
                                                        cheque_no: cheque_no,
                                                        note: note,
                                                        total_payment: total_payment,
                                                        id: json.id,
                                                        purchase_invoice: json.purchase_invoice,
                                                        supplier_invoice: json.supplier_invoice,
                                                        currency: json.currency,
                                                        amount: json.amount,
                                                        balance: json.balance,
                                                        payment: json.payment,
                                                        remarks: json.remarks,
                                                        rate: json.rate,
                                                        account_number: json.account_number,
                                                        account_type: json.account_type,
                                                    });
                                                }

                                                combinedJournals = [];
                                                var total_debit = 0;
                                                var total_credit = 0;
                                                for (let z = 0; z < totalrows2; z++) {
                                                    var json2 = rows2[z];

                                                    if (json2.account_number == "" || json2.account_number == null) {
                                                        toastr.error("Account No Not valid, Please Check your data input and save again!!");
                                                        return false;
                                                    }

                                                    combinedJournals.push({
                                                        payment_no: payment_no,
                                                        account_number: json2.account_number,
                                                        account_name: json2.account_name,
                                                        description: json2.description,
                                                        debit: json2.debit,
                                                        credit: json2.credit,
                                                        local_debit: json2.local_debit,
                                                        local_credit: json2.local_credit,
                                                        flag: json2.flag,
                                                    });

                                                    total_debit += parseFloat(json2.local_debit);
                                                    total_credit += parseFloat(json2.local_credit);
                                                }

                                                if (totalrows2 <= 0) {
                                                    toastr.error("Please calculate journal first!");
                                                    return false;
                                                }

                                                if (total_debit == 0 && total_credit == 0) {
                                                    toastr.error("Debit and Credit still 0, please click add to journal first");
                                                    return false;
                                                }

                                                if (totalrows > 0) {
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
                                                        url: "<?= base_url('finance/ap_payments/deleteJournal') ?>",
                                                        data: "payment_no=" + payment_no,
                                                        dataType: "json",
                                                        success: function(response) {
                                                            $.ajax({
                                                                type: "post",
                                                                url: '<?= base_url('finance/ap_payments/create') ?>',
                                                                data: JSON.stringify({
                                                                    combinedAp: combinedAp,
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
                                                                                url: '<?= base_url('finance/ap_payments/createPosting') ?>',
                                                                                data: {
                                                                                    payment_no: payment_no
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
                                                                                url: "<?= base_url('finance/ap_payments/delete') ?>",
                                                                                data: "payment_no=" + payment_no,
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

                                                    $('#dg').datagrid('reload');
                                                } else {
                                                    toastr.warning("please selections your data in table first");
                                                }
                                            }
                                            // } else {
                                            //     toastr.error("Balance Debit Cannot match on Grand Total");
                                            // }
                                        } else {
                                            toastr.error("Balance Debit Cannot match on Balance Credit");
                                        }
                                    }
                                });
                            }
                        }
                    });
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('finance/ap_payments/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/ap_payments/upload') ?>',
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
                                url: "<?= base_url('finance/ap_payments/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('finance/ap_payments/uploadCreate') ?>",
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
                                                    url: "<?= base_url('finance/ap_payments/uploadcreateFailed') ?>",
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
        $('#filter_supplier').combogrid({
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
            onSelect: function(val, supplier) {
                $("#filter_payment_no").combobox({
                    url: '<?= base_url('finance/ap_payments/readPayments/') ?>' + btoa(supplier.id),
                    valueField: 'payment_no',
                    textField: 'payment_no',
                    prompt: "Choose Payment No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
                $("#filter_invoice_no").combobox({
                    url: '<?= base_url('finance/ap_payments/readInvoices/') ?>' + btoa(supplier.id),
                    valueField: 'purchase_invoice',
                    textField: 'purchase_invoice',
                    prompt: "Choose Purchase Invoice",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });
        $("#filter_bank_no").combogrid({
            url: '<?= base_url('finance/account_banks/reads/supplier') ?>',
            panelWidth: 500,
            idField: 'account_number',
            textField: 'bank_name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Bank Account",
            columns: [
                [{
                    field: 'bank_account',
                    title: 'Bank Account',
                    width: 120
                }, {
                    field: 'bank_name',
                    title: 'Bank Name',
                    width: 250
                }, {
                    field: 'bank_code',
                    title: 'Bank Code',
                    width: 100
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });
        $("#payment_type").combobox({
            onChange: function(val) {
                if (val == "OTHERS") {
                    $("#supplier_id").combogrid('setValue', "SUP-03-17");
                    $("#f_account").show();
                    $("#f_supplier").hide();
                    $("#f_imprest").hide();
                } else if (val == "IMPREST") {
                    $("#supplier_id").combogrid('setValue', "SUP-03-17");
                    $("#f_account").hide();
                    $("#f_supplier").hide();
                    $("#f_imprest").show();
                } else {
                    $("#supplier_id").combogrid('clear');
                    $("#f_account").hide();
                    $("#f_supplier").show();
                    $("#f_imprest").hide();
                }
                $("#journal_type").combobox({
                    url: '<?= base_url('finance/journal_types/reads/' . base64_encode("AP PAYMENT") . '/') ?>' + btoa(val),
                    valueField: 'id',
                    textField: 'name',
                    prompt: "Choose Journal Types",
                    onLoadSuccess: function(row) {
                        if (val != "PURCHASE") {
                            $("#journal_type").combobox('setValue', row[0].id);
                        }
                    },
                    onSelect: function(payment) {
                        $("#other_account").combogrid('setValue', payment.account_number);
                        $("#other_account_name").textbox('setValue', payment.account_name);
                    }
                });
            }
        });
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
            onSelect: function(supp, supplier) {
                var payment_type = $("#payment_type").combobox('getValue');
                var payment_date = $("#payment_date").datebox('getValue');
                $("#currency").textbox('setValue', supplier.currency);
                if (payment_date != "" && payment_type != "") {
                    $("#purchase_invoice").combogrid({
                        url: '<?= base_url('finance/ap_payments/readInvoiceType?supplier_id=') ?>' + supplier.id + "&payment_type=" + payment_type + "&payment_date=" + payment_date,
                        panelWidth: 650,
                        idField: 'number',
                        textField: 'number',
                        mode: 'remote',
                        fitColumns: true,
                        multiple: true,
                        prompt: "Choose Purchase invoice No",
                        columns: [
                            [{
                                field: 'ck',
                                checkbox: true,
                            }, {
                                field: 'number',
                                title: 'Purchase Invoice No',
                                width: 150
                            }, {
                                field: 'invoice_no',
                                title: 'Supplier Invoice No',
                                width: 150
                            }, {
                                field: 'invoice_date',
                                title: 'Invoice Date',
                                width: 120
                            }, {
                                field: 'due_date',
                                title: 'Due Date',
                                width: 120
                            }],
                        ],
                        onLoadSuccess: function(rowData) {
                            if (rowData.total == 0) {
                                toastr.warning("No data found with due date " + payment_date);
                            }
                        },
                        onSelect: function(valpi, pi) {
                            if (pi.journal_type_id != null) {
                                $("#journal_type").combobox('setValue', pi.journal_type_id);
                            } else {
                                toastr.info("The journal type on the purchase invoice is still empty");
                                $("#journal_type").combobox('clear');
                            }
                        }
                    });
                } else {
                    toastr.error("Please Select Payment Type and Date First");
                }
            }
        });
        $("#other_account").combogrid({
            url: '<?= base_url('finance/account_coa/readOthers/AP') ?>',
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
            onSelect: function(val, row) {
                $("#other_account_name").textbox('setValue', row.account_name);
            }
        });
        $("#imprest_account").combogrid({
            url: '<?= base_url('finance/account_imprests/reads') ?>',
            panelWidth: 600,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Imprest No',
            columns: [
                [{
                    field: 'number',
                    title: 'Imprest Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Imprest Name',
                    width: 100
                }, {
                    field: 'account_number',
                    title: 'Account No',
                    width: 150
                }, {
                    field: 'account_name',
                    title: 'Account Name',
                    width: 200
                }, ]
            ],
            onSelect: function(val, row) {
                $("#other_account").textbox('setValue', row.account_number);
                $("#other_account_name").textbox('setValue', row.account_name);
            }
        });
        $("#bank_account").combogrid({
            url: '<?= base_url('finance/account_banks/reads/supplier') ?>',
            panelWidth: 500,
            idField: 'account_number',
            textField: 'bank_name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Bank Account",
            columns: [
                [{
                    field: 'bank_account',
                    title: 'Bank Account',
                    width: 120
                }, {
                    field: 'bank_name',
                    title: 'Bank Name',
                    width: 250
                }, {
                    field: 'bank_code',
                    title: 'Bank Code',
                    width: 100
                }, ]
            ],
        });
        $("#payment_by").combobox({
            onChange: function(val) {
                if (val == "CHEQUE") {
                    $("#f_cheque_no").show();
                } else {
                    $("#f_cheque_no").hide();
                    $("#cheque_no").textbox('clear');
                }
            }
        });
    });

    function priceformat(value, row) {
        if (row.currency == "USD") {
            var digits = 2;
            var currency = 'USD';
            var format = "en-IN";
        } else if (row.currency == "JPY") {
            var digits = 2;
            var currency = 'JPY';
            var format = "ja-JP";
        } else if (row.currency == "EUR") {
            var digits = 2;
            var currency = 'EUR';
            var format = "de-DE";
        } else {
            var digits = 0;
            var currency = 'INR';
            var format = "ja-JP";
        }
        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function priceformatlocal(value, row) {
        var digits = 0;
        var currency = 'INR';
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
        if (value >= 0) {
            return "<b>" + formatter.format(value) + "</b>";
        } else {
            return "<b>0,00</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 1) {
            return "<b style='color:green;'>DONE</b>";
        } else {
            return "<b style='color:orange;'>PENDING</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 1) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#ffd495;';
        }
    }
</script>