<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'details',width:90,align:'center', formatter:btnDetails">Detail</th>
            <th rowspan="2" data-options="field:'journal_date',width:100,align:'center'">Posting Date</th>
            <th rowspan="2" data-options="field:'number',width:140,align:'center'">Voucher No</th>
            <th rowspan="2" data-options="field:'company_id',width:120,halign:'center'">Company ID</th>
            <th rowspan="2" data-options="field:'company_name',width:200,halign:'center'">Company Name</th>
            <th rowspan="2" data-options="field:'remarks',width:200,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Debit</th>
            <th rowspan="2" data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Credit</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approval</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_by',width:100,align:'center'"> By</th>
            <th data-options="field:'approved',width:100,align:'center', styler:approvedStyle, formatter:approvedFormat"> Status</th>
            <th data-options="field:'approved_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Posting Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Type</span>
                    <input style="width:60%;" id="filter_journal_type_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <!-- <a href="javascript:;" class="easyui-linkbutton" onclick="print_voucher()"><i class="fa fa-print"></i> Print Voucher</a> -->
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Company Name</span>
                    <input style="width:60%;" id="filter_company" class="easyui-combogrid" />
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Voucher No</span>
                    <input style="width:60%;" id="filter_voucher" class="easyui-textbox" />
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" id="append" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" id="preview" data-options="plain:true" onclick="preview()"><i class="fa fa-eye"></i> Preview Journal</a> -->
</div>

<div id="toolbarDocumentNo">
    &nbsp; Total Amount/Data : &nbsp;
    <input style="width:15%;" id="total_amount" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:',',decimalSeparator:'.'">
    <input style="width:5%;" id="total_data" readonly class="easyui-numberbox">
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New Posting Journal" data-options="closed: true,modal:true, fit:true" style="width: 100%; padding:10px; top: 0; left: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;" id="fieldset">
            <legend><b>Form Data</b></legend>
            <div style="width:30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Posting Date</span>
                    <input style="width:60%;" name="journal_date" id="journal_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Voucher No</span>
                    <input style="width:60%;" name="number" id="number" readonly class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Modul</span>
                    <input style="width:60%;" name="modul" id="modul" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <select style="width:60%;" name="journal_type" id="journal_type" required class="easyui-combobox" panelHeight="auto">
                        <option value="CL - SUPPLIER">SUPPLIER</option>
                        <option value="CL - CUSTOMER">CUSTOMER</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="preview" onclick="previewData()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width:30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Company Name</span>
                    <input style="width:60%;" name="company_id" id="company_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" name="document_no" required id="document_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
            </div>
            <div style="width:30%; float:left;">
                <!-- Buat Validasi -->
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Total Debit</span>
                    <input style="width:60%;" id="local_debit" disabled class="easyui-numberbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Total Credit</span>
                    <input style="width:60%;" id="local_credit" disabled class="easyui-numberbox">
                </div>
            </div>
        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Journal Posting List" data-options="singleSelect: true" toolbar="#toolbar2" rownumbers="true" , idField="number" showFooter="true">
            <thead>
                <tr>
                    <!-- <th field="ck" checkbox="true">Posting</th> -->
                    <th data-options="field:'remove',width:110, formatter:removebtn">Action</th>
                    <th hidden data-options="field:'id',width:150,editor: {type: 'textbox'}">ID</th>
                    <th hidden data-options="field:'journal_type_id',width:150,editor: {type: 'textbox'}">ID</th>
                    <th data-options="field:'source',width:80,editor: {type: 'textbox', options: {readonly: true}}">Source</th>
                    <th data-options="field:'document_no',width:150,editor: {type: 'textbox'}">Document No</th>
                    <th data-options="field:'document_date',width:120,editor: {type: 'datebox', options: {formatter:myformatter,parser:myparser,editable:false}}">Document Date</th>
                    <th data-options="field:'invoice_no',width:200,editor: {type: 'textbox'}">Invoice No</th>
                    <th data-options="field:'invoice_date',width:120,editor: {type: 'datebox', options: {formatter:myformatter,parser:myparser,editable:false}}">Invoice Date</th>
                    <th hidden data-options="field:'voucher_no',width:120,editor: {type: 'textbox', options: {readonly: true}}">Voucher No</th>
                    <th hidden data-options="field:'description',width:200,editor: {type: 'textbox', options: {readonly: true}}">Description</th>
                    <th data-options="field:'account_number',width:100, halign:'center', editor: {type: 'combogrid'}">Account No</th>
                    <th hidden data-options="field:'account_name',width:180, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th hidden data-options="field:'company_id',width:100,editor: {type: 'textbox', options: {readonly: true}}">Company ID</th>
                    <th hidden data-options="field:'company_name',width:200,editor: {type: 'textbox', options: {readonly: true}}">Company Name</th>
                    <th data-options="field:'currency',width:80,editor: {type: 'textbox', options: {required: true, readonly: true}}">Currency</th>
                    <th data-options="field:'amount',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr,editor: {type: 'numberbox', options: {readonly: true, precision:2}}">Amount</th>
                    <th data-options="field:'adjustment',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr,editor: {type: 'numberbox', options: {required: true, precision:2, 
                        onChange: function(value) {
                            var dg = $('#dg2');
                            var row = dg.datagrid('getSelected');
                            var rowIndex = dg.datagrid('getRowIndex', row);

                            var ed = dg.datagrid('getEditor', {
                                index: rowIndex,
                                field: 'amount'
                            });

                            var ed2 = dg.datagrid('getEditor', {
                                index: rowIndex,
                                field: 'balance'
                            });

                            if(rowIndex == 0){
                                var balance = 0;
                            }else{
                                var indexBal = (rowIndex-1);
                                var rowData = dg.datagrid('getRows')[indexBal];
                                var balance = rowData.balance.toFixed(2);
                            }

                            var amount = $(ed.target).numberbox('getValue');
                            $(ed2.target).numberbox('setValue', (parseFloat(amount) + parseFloat(balance) + parseFloat(value)));
                        }
                    }}">Adjustment</th>
                    <th data-options="field:'balance',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr,editor: {type: 'numberbox', options: {readonly: true, precision:2}}">Balance</th>
                </tr>
            </thead>
        </table>

        <div style="width: 60%; float: left; margin-top:20px;">
            <a style="width: 20%;" class="easyui-linkbutton c2" onclick="preview()">Calculate to Journal</a>

            <br><br>
            <table id="dg4" class="easyui-datagrid" style="width:100%;" showFooter="true">
                <thead>
                    <tr>
                        <th data-options="field:'account_number',width:100,halign:'center'">Account No</th>
                        <th data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
                        <th data-options="field:'description',width:250,halign:'center'">Description</th>
                        <th data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Debit</th>
                        <th data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Credit</th>
                        <th hidden data-options="field:'status_cl',width:80,halign:'center'">Status</th>
                    </tr>
                </thead>
            </table>
        </div>
    </form>
</div>

<div id="dlg_detail" class="easyui-window" title="Journal Detail" data-options="closed: true,modal:true" style="width: 1200px; height: 500px; top: 20px; left:10px;">
    <table id="dg3" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'journal_date',width:100,halign:'center'">Posting Date</th>
                <th data-options="field:'invoice_no',width:120,halign:'center'">Invoice No</th>
                <th data-options="field:'document_no',width:220,halign:'center'">Document No</th>
                <th data-options="field:'company_name',width:200,halign:'center'">Company Name</th>
                <th data-options="field:'account_number',width:100,halign:'center'">Account No</th>
                <th data-options="field:'account_name',width:250,halign:'center'">Account Name</th>
                <th data-options="field:'description',width:150,halign:'center'">Description</th>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'amount',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Amount</th>
                <th data-options="field:'adjustment',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Residual Value</th>
                <th data-options="field:'balance',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Balance</th>
            </tr>
        </thead>
    </table>

    <div style="width: 60%; float: left; margin-top:20px;">
        <br>
        <table id="dg5" title="Journal Postings" class="easyui-datagrid" style="width:100%;" showFooter="true">
            <thead>
                <tr>
                    <th data-options="field:'account_number',width:100,halign:'center'">Account No</th>
                    <th data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
                    <th data-options="field:'description',width:250,halign:'center'">Description</th>
                    <th data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Debit</th>
                    <th data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Credit</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/journal_posting_clearing/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $("#fieldset").show();
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        proses = "ADD";

        $("#journal_date").datebox('enable');
        $("#modul").textbox('enable');
        $("#journal_type").combobox('enable');
        $("#company_id").combogrid('enable');
        $("#document_no").combogrid('enable');
        $("#number").textbox('enable');

        $("#modul").textbox('setValue', "CLEARING");
        $("#journal_date").datebox('setValue', "<?= date("Y-m-d") ?>");

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
    }

    //NOMOR AUTOMATIC
    function number(id_menu, journal_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/journal_posting_clearing/number/') ?>" + id_menu + "/" + btoa(journal_date),
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                    $("#number").textbox('clear');
                } else {
                    $("#number").textbox('setValue', result);
                }
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
        var document_no = $("#document_no").combogrid('getValue');

        if (document_no != "") {
            if (endEditing()) {
                var rows = $('#dg2').datagrid('getRows');
                var totalrows = rows.length;

                if (totalrows == 0) {
                    toastr.error("No Data for the Clearing Adjustment");
                    return false;
                }

                var amount = 0;
                for (let i = 0; i < totalrows; i++) {
                    var data = rows[i];
                    company_id = data.company_id;
                    company_name = data.company_name;
                    account_number = data.account_number;
                    account_name = data.account_name;
                    amount += (parseFloat(data.amount) + parseFloat(data.adjustment));
                }

                $('#dg2').datagrid('appendRow', {
                    "source": 'MN',
                    "document_date": "<?= date("Y-m-d") ?>",
                    "invoice_date": "<?= date("Y-m-d") ?>",
                    "currency": 'INR',
                    "company_id": company_id,
                    "company_name": company_name,
                    "account_name": account_name,
                    "amount": (amount * -1),
                    "adjustment": 0,
                    "balance": 0,
                });

                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

                var urls = '<?= base_url('finance/account_coa/reads') ?>';

                var ed = $('#dg2').datagrid('getEditor', {
                    index: editIndex,
                    field: 'account_number'
                });

                $(ed.target).combogrid({
                    url: urls,
                    panelWidth: 450,
                    panelHeight: 200,
                    idField: 'account_number',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Account No',
                    columns: [
                        [{
                            field: 'account_number',
                            title: 'Code',
                            width: 100
                        }, {
                            field: 'account_name',
                            title: 'Name',
                            width: 250
                        }]
                    ],
                    onLoadSuccess: function() {
                        $(ed.target).combogrid('setValue', account_number);
                    },
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
                });
            }
        } else {
            toastr.warning("Please Select Document No");
        }
    }

    function removebtn(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
            return s + c;
        } else {
            if (row.currency != "TOTAL") {
                var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
                var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this, ' + row.id + ')">Delete</a>';

                return e + d;
            }
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        if (editIndex !== undefined) {
            $('#dg2').datagrid('endEdit', editIndex);
        }

        var rowIndex = getRowIndex(target);
        $('#dg2').datagrid('selectRow', rowIndex).datagrid('beginEdit', rowIndex);
        editIndex = rowIndex;

        var row = $('#dg2').datagrid('getSelected');

        if (row.source != "MN") {
            var ed = $('#dg2').datagrid('getEditor', {
                index: rowIndex,
                field: 'document_no'
            });

            var ed2 = $('#dg2').datagrid('getEditor', {
                index: rowIndex,
                field: 'document_date'
            });

            var ed3 = $('#dg2').datagrid('getEditor', {
                index: rowIndex,
                field: 'invoice_no'
            });

            var ed4 = $('#dg2').datagrid('getEditor', {
                index: rowIndex,
                field: 'invoice_date'
            });

            var ed5 = $('#dg2').datagrid('getEditor', {
                index: rowIndex,
                field: 'account_number'
            });

            $(ed.target).textbox({
                "readonly": true
            });
            $(ed2.target).textbox({
                "readonly": true
            });
            $(ed3.target).textbox({
                "readonly": true
            });
            $(ed4.target).textbox({
                "readonly": true
            });
            $(ed5.target).combogrid({
                "readonly": true
            });
            $(ed5.target).combogrid('setValue', row.account_number);
        } else {
            var ed = $('#dg2').datagrid('getEditor', {
                index: editIndex,
                field: 'account_number'
            });

            var urls = '<?= base_url('finance/account_coa/reads') ?>';
            $(ed.target).combogrid({
                url: urls,
                panelWidth: 450,
                panelHeight: 200,
                idField: 'account_number',
                textField: 'name',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Account No',
                columns: [
                    [{
                        field: 'account_number',
                        title: 'Code',
                        width: 100
                    }, {
                        field: 'account_name',
                        title: 'Name',
                        width: 250
                    }]
                ],
                onLoadSuccess: function() {
                    $(ed.target).combogrid('setValue', row.account_number);
                },
                onSelect: function(value, rows) {
                    var ed2 = $('#dg2').datagrid('getEditor', {
                        index: editIndex,
                        field: 'account_name'
                    });

                    $(ed2.target).textbox('setValue', rows.account_name);
                }
            });
        }
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));

        $('#dg2').datagrid('acceptChanges');
        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;
        endEditing();
        var jsonData = JSON.stringify(rows);

        $.ajax({
            type: "POST",
            url: "<?= base_url('finance/journal_posting_clearing/createJson') ?>",
            data: {
                jsonData: jsonData,
            },
            success: function(response) {
                $('#dg2').datagrid({
                    url: '<?= base_url('finance/journal_posting_clearing/calculates') ?>',
                    pagination: false,
                    rownumbers: true,
                });
            },
        });
    }

    function cancelrow(target) {
        $('#dg2').datagrid('cancelEdit', getRowIndex(target));
    }

    function deleterow(targetnya, id) {
        var row = $('#dg2').datagrid('getSelected');

        if (row.source == "MN") {
            $.messager.confirm('Confirm', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    $('#dg2').datagrid('deleteRow', getRowIndex(targetnya));
                }
            });
        } else {
            toastr.error("Only Source = 'MN' for delete");
        }

    }

    //EDIT DATA
    function update() {
        toastr.info("Not Yet Available");
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
                            data: "period=" + row.journal_date + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function(lock) {
                                if (lock.total > 0) {
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/journal_posting_clearing/delete') ?>',
                                    data: {
                                        number: row.number
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
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

    function preview() {
        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;
        endEditing();

        var journal_type = $("#journal_type").combobox('getValue');

        if (totalrows > 0) {
            var jsonData = JSON.stringify(rows);

            $.ajax({
                type: "POST",
                url: "<?= base_url('finance/journal_posting_clearing/createJson') ?>",
                data: {
                    jsonData: jsonData,
                },
                success: function(response) {
                    $('#dg4').datagrid({
                        url: '<?= base_url('finance/journal_posting_clearing/calculateJournal/') ?>' + btoa(journal_type),
                        pagination: false,
                        rownumbers: true,
                    });
                },
            });
        } else {
            toastr.warning("No Data to Preview");
        }
    }

    function previewData() {
        var journal_date = $("#journal_date").datebox('getValue');
        var company_id = $("#company_id").combogrid('getValue');
        var document_no = $("#document_no").combogrid('getText');

        if (document_no == "" || company_id == "") {
            toastr.info('Please Choose Document No and Company Name');
        } else {
            $('#dg2').datagrid({
                url: '<?= base_url('finance/journal_posting_clearing/datatablesTemp') ?>?journal_date=' + window.btoa(journal_date) + "&document_no=" + window.btoa(document_no) + "&company_id=" + window.btoa(company_id),
            });
        }
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_journal_type_id = $("#filter_journal_type_id").combogrid('getValue');
        var filter_voucher = $("#filter_voucher").textbox('getValue');
        var filter_company = $("#filter_company").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_journal_type_id=" + window.btoa(filter_journal_type_id) +
            "&filter_company=" + window.btoa(filter_company) +
            "&filter_voucher=" + window.btoa(filter_voucher);

        $('#dg').datagrid({
            url: '<?= base_url('finance/journal_posting_clearing/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/journal_posting_clearing/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_journal_type_id = $("#filter_journal_type_id").combogrid('getValue');
        var filter_voucher = $("#filter_voucher").textbox('getValue');
        var filter_company = $("#filter_company").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_journal_type_id=" + window.btoa(filter_journal_type_id) +
            "&filter_company=" + window.btoa(filter_company) +
            "&filter_voucher=" + window.btoa(filter_voucher);

        window.location.assign('<?= base_url('finance/journal_posting_clearing/print/excel') ?>' + url);
    }

    function print_voucher() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            window.open("<?= base_url('finance/journal_posting_clearing/print_voucher/') ?>" + window.btoa(row.number), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please select GL No in the table first!");
        }
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function updateTotal() {
        var rows = $('#document_no').combogrid('grid').datagrid('getSelections');
        var total = 0;
        for (var i = 0; i < rows.length; i++) {
            total += parseFloat(rows[i].amount);
        }
        $('#total_amount').numberbox('setValue', total.toFixed(2));
        $('#total_data').numberbox('setValue', rows.length);
    }

    $(function() {
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var btn = $(this);
                    btn.linkbutton('disable');
                    setTimeout(function() {
                        btn.linkbutton('enable');
                    }, 3000);

                    var journal_date = $("#journal_date").datebox('getValue');
                    var modul = $("#modul").textbox('getValue');
                    var journal_type = $("#journal_type").combobox('getValue');
                    var number = $("#number").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    // var local_debit = $("#local_debit").numberbox('getValue');
                    // var local_credit = $("#local_credit").numberbox('getValue');

                    endEditing();
                    //var rows = $('#dg2').datagrid('getSelections');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;

                    var rows2 = $('#dg4').datagrid('getRows');
                    var totalrows2 = rows2.length;

                    total_local_debit = 0;
                    total_local_credit = 0;
                    for (let i = 0; i < totalrows2; i++) {
                        var data = rows2[i];
                        total_local_debit += data.local_debit;
                        total_local_credit += data.local_credit;
                    }

                    if (parseFloat(total_local_debit).toFixed(2) != parseFloat(total_local_credit).toFixed(2)) {
                        toastr.error("Journal Not Balance " + parseFloat(total_local_debit).toFixed(2) + " != " + parseFloat(total_local_credit).toFixed(2));
                        return false;
                    }

                    if (totalrows2 == 0) {
                        toastr.error("Calculate to Journal still empty!");
                        return false;
                    }

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + journal_date + "&menus_id=<?= $menus_id ?>",
                        dataType: "json",
                        success: function(lock) {
                            if (lock.total > 0) {
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            if (rows.length > 0) {
                                $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                                    if (r) {
                                        $.ajax({
                                            type: "post",
                                            url: "<?= base_url('finance/journal_posting_clearing/number/' . $menus_id) ?>/" + btoa(journal_date),
                                            dataType: "html",
                                            success: function(numberValue) {
                                                if (numberValue == "") {
                                                    toastr.info("Please Check Configuration in Serial No First");
                                                    return false;
                                                }

                                                combinedClearing = [];
                                                for (let i = 0; i < totalrows; i++) {
                                                    var json = rows[i];

                                                    combinedClearing.push({
                                                        journal_date: journal_date,
                                                        journal_type_id: json.journal_type_id,
                                                        document_no: json.document_no,
                                                        document_date: json.document_date,
                                                        invoice_no: json.invoice_no,
                                                        invoice_date: json.invoice_date,
                                                        number: numberValue,
                                                        company_id: json.company_id,
                                                        company_name: json.company_name,
                                                        trans_date: journal_date,
                                                        description: json.description,
                                                        account_number: json.account_number,
                                                        account_name: json.account_name,
                                                        currency: json.currency,
                                                        remarks: remarks,
                                                        source: json.source,
                                                        amount: json.amount,
                                                        adjustment: json.adjustment,
                                                        balance: json.balance,
                                                    });
                                                }

                                                combinedJournals = [];
                                                var total_debit = 0;
                                                var total_credit = 0;
                                                for (let x = 0; x < totalrows2; x++) {
                                                    var json2 = rows2[x];

                                                    combinedJournals.push({
                                                        number: number,
                                                        journal_date: journal_date,
                                                        journal_type_id: "20241120000002",
                                                        modul: modul,
                                                        journal_type: journal_type,
                                                        number: numberValue,
                                                        remarks: remarks,
                                                        company_id: rows[0].company_id,
                                                        company_name: rows[0].company_name,
                                                        account_number: json2.account_number,
                                                        account_name: json2.account_name,
                                                        description: json2.description,
                                                        currency: "INR",
                                                        original_debit: json2.local_debit,
                                                        original_credit: json2.local_credit,
                                                        rates: 1,
                                                        local_debit: json2.local_debit,
                                                        local_credit: json2.local_credit,
                                                        status_cl: json2.status_cl,
                                                    });

                                                    total_debit += parseFloat(json2.local_debit);
                                                    total_credit += parseFloat(json2.local_credit);
                                                }

                                                if (Math.round(total_debit) != Math.round(total_credit)) {
                                                    toastr.error("Debit & Credit Not Balance (Debit : " + Math.round(total_debit) + ") and (Credit : " + Math.round(total_credit) + ")");
                                                    return false;
                                                }

                                                $('#dlg_insert').dialog('close');

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
                                                    url: '<?= base_url('finance/journal_posting_clearing/create') ?>',
                                                    data: JSON.stringify({
                                                        dataClearings: combinedClearing,
                                                        dataJournals: combinedJournals
                                                    }),
                                                    dataType: "json",
                                                    success: function(result) {
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
                                                    },
                                                    error: function(msg) {
                                                        Swal.close();

                                                        Swal.fire({
                                                            title: "Failed to Save Data, Please Try Again",
                                                            icon: "error",
                                                            confirmButtonText: 'Ok',
                                                            allowOutsideClick: false,
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            } else {
                                toastr.info("Please Checklist Posting");
                            }
                        }
                    });
                }
            }]
        });

        $("#filter_company").combogrid({
            url: '<?= base_url('finance/journal_posting_clearing/readCompany2') ?>',
            panelWidth: 480,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Company Name",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 250
                }, {
                    field: 'plant',
                    title: 'Plant',
                    width: 120
                }]
            ],
        });

        $("#filter_journal_type_id").combogrid({
            url: '<?= base_url('finance/journal_posting_clearing/readJournalType') ?>',
            panelWidth: 360,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Journal Type",
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 250
                }]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $("#journal_type").combobox({
            onChange: function(val) {
                if (val == "") {
                    return false;
                }

                $("#company_id").combogrid({
                    url: '<?= base_url('finance/journal_posting_clearing/readCompany/') ?>' + btoa(val),
                    panelWidth: 480,
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Company Name",
                    columns: [
                        [{
                            field: 'id',
                            title: 'Code',
                            width: 100
                        }, {
                            field: 'name',
                            title: 'Name',
                            width: 250
                        }, {
                            field: 'plant',
                            title: 'Plant',
                            width: 120
                        }]
                    ],
                    onSelect: function(valSup, rowSup) {
                        $("#document_no").combogrid({
                            url: '<?= base_url('finance/journal_posting_clearing/readDocuments/') ?>' + btoa(rowSup.id),
                            panelWidth: 850,
                            idField: 'documents',
                            textField: 'documents',
                            mode: 'remote',
                            fitColumns: true,
                            multiple: true,
                            toolbar: "#toolbarDocumentNo",
                            prompt: "Choose Document No",
                            loadMsg: 'Please Wait..',
                            columns: [
                                [{
                                    field: 'ck',
                                    checkbox: true
                                }, {
                                    field: 'source',
                                    title: 'Type',
                                    align: 'center',
                                    width: 50
                                }, {
                                    field: 'documents',
                                    title: 'Document No',
                                    width: 150,
                                    hidden: true
                                }, {
                                    field: 'document_no',
                                    title: 'Document No',
                                    width: 150
                                }, {
                                    field: 'invoice_no',
                                    title: 'Invoice No',
                                    width: 200
                                }, {
                                    field: 'voucher_no',
                                    title: 'Voucher No',
                                    width: 130
                                }, {
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
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
                                    formatter: numberformatDefaultIdr
                                }]
                            ],
                            onCheck: updateTotal,
                            onUncheck: updateTotal,
                            onCheckAll: updateTotal,
                            onUncheckAll: updateTotal
                        });
                    }
                });
            }
        });

        $("#journal_date").datebox({
            onChange: function(val) {
                number("<?= $menus_id ?>", val);
            }
        });
    });

    function btnDetails(val, row) {
        var details = "viewDetails('" + row.number + "')";
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewDetails(number) {
        $("#dlg_detail").window('open');
        $("#dlg_detail").window('setTitle', "Detail of " + number);

        $('#dg3').datagrid({
            url: '<?= base_url('finance/journal_posting_clearing/datatableDetails?number=') ?>' + btoa(number),
            pagination: false,
            rownumbers: true,
            remoteFilter: true,
        });

        $('#dg5').datagrid({
            url: '<?= base_url('finance/journal_posting_clearing/datatableDetailJournals?number=') ?>' + btoa(number),
            pagination: false,
            rownumbers: true,
            remoteFilter: true,
        });
    }

    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

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

    function numberformatDefault(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatDefaultIdr(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function approvedFormat(value, row) {
        if (row.approved_to == "" || row.approved_to == null) {
            return "<b style='color:green;'>APPROVED</b>";
        } else {
            return "<b style='color:red;'>CHECKING</b>";
        }
    }

    function approvedStyle(value, row, index) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>