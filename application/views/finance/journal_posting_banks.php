<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'details',width:90,align:'center', formatter:btnDetails">Detail</th>
            <th rowspan="2" data-options="field:'journal_date',width:100,align:'center'">Journal Date</th>
            <th rowspan="2" data-options="field:'number',width:140,align:'center'">GL No</th>
            <!-- <th rowspan="2" data-options="field:'journal_type_name',width:200,halign:'center'">Journal Type</th> -->
            <!-- <th rowspan="2" data-options="field:'modul',width:150,halign:'center'">Modul</th> -->
            <th hidden colspan="3" data-options="field:'',width:200,halign:'center'">Original Currency</th>
            <th colspan="3" data-options="field:'',width:200,halign:'center'">Local Currency</th>
            <th rowspan="2" data-options="field:'remarks',width:200,halign:'center'">Remarks</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approval</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th hidden data-options="field:'original_debit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
            <th hidden data-options="field:'original_credit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
            <th hidden data-options="field:'rates',width:80,halign:'center',align:'right',formatter:numberformatDefault">Rates</th>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'local_debit',width:120,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
            <th data-options="field:'local_credit',width:120,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
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

<div id="toolbar" style="height: 160px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Journal Type</span>
                    <input style="width:60%;" id="filter_journal_type" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_voucher()"><i class="fa fa-print"></i> Print Voucher</a>
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Voucher No</span>
                    <input style="width:60%;" id="filter_voucher" class="easyui-combobox" />
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New Posting Journal" data-options="closed: true,modal:true, fit:true" style="width: 1400px; padding:10px; top: 0; left: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:70%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;" id="fieldset">
            <legend><b>Form Data</b></legend>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Date</span>
                    <input style="width:30%;" name="journal_date" id="journal_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Modul</span>
                    <input style="width:60%;" name="modul" id="modul" class="easyui-textbox" required>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Journal Type</span>
                    <input style="width:60%;" name="journal_type_id" id="journal_type" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans type</span>
                    <select style="width:60%;" name="trans_type" id="trans_type" class="easyui-combobox" panelHeight="auto" required panelHeight="auto">
                        <option value="AP PAYMENT">AP PAYMENT</option>
                        <option value="AR RECEIPT">AR RECEIPT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Account</span>
                    <input style="width:60%;" name="bank_account" id="bank_account" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" name="document_no" id="document_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="preview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Posting To</span>
                    <input style="width:60%;" name="posting_to" id="posting_to" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vourcher No</span>
                    <input style="width:60%;" name="number" id="number" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>

                <!-- Buat Validasi -->
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Local Debit</span>
                    <input style="width:60%;" id="local_debit" disabled class="easyui-numberbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Local Credit</span>
                    <input style="width:60%;" id="local_credit" disabled class="easyui-numberbox">
                </div>
            </div>
        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Journal Posting List" data-options="singleSelect: false" rownumbers="true" , idField="number" showFooter="true">
            <thead>
                <tr>
                    <th field="ck" checkbox="true">Posting</th>
                    <!-- <th data-options="field:'remove',width:120, formatter:removebtn">Action</th> -->
                    <th hidden data-options="field:'flag',width:100,editor: {type: 'textbox'}">Flag</th>
                    <th hidden data-options="field:'id',width:150,editor: {type: 'textbox'}">ID</th>
                    <th data-options="field:'trans_date',width:100,editor: {type: 'datebox',options: {formatter: myformatter,parser: myparser}}">Trans Date</th>
                    <th data-options="field:'document_no',width:160,editor: {type: 'textbox', options: {required: true}}">Document No</th>
                    <th data-options="field:'invoice_no',width:120,editor: {type: 'textbox', options: {required: true}}">GL No</th>
                    <th hidden data-options="field:'company_id',width:100,editor: {type: 'textbox'}">Company ID</th>
                    <th data-options="field:'company_name',width:200,editor: {type: 'textbox', options: {required: true}}">Company Name</th>
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
                    <th data-options="field:'account_name',width:250, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th data-options="field:'description',width:500,editor: {type: 'textbox', options: {required: true}}">Description</th>
                    <th data-options="field:'currency',width:80,align:'cennter',editor: {type: 'textbox', options: {required: true}}">Currency</th>
                    <th data-options="field:'local_debit',width:120,halign:'center',align:'right',formatter:numberformatDefault,editor: {type: 'numberbox', options: {required: true, precision:2}}">Debit</th>
                    <th data-options="field:'local_credit',width:120,halign:'center',align:'right',formatter:numberformatDefault,editor: {type: 'numberbox', options: {required: true, precision:2}}">Credit</th>
                </tr>
            </thead>
        </table>
    </form>
</div>

<div id="dlg_detail" class="easyui-window" title="Journal Detail" data-options="closed: true,modal:true" style="width: 800px; height: 500px; top: 20px; left:10px;">
    <table id="dg3" class="easyui-datagrid" style="width:100%;" showFooter="true">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center'">Trans Date</th>
                <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No</th>
                <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Supplier Invoice</th>
                <th rowspan="2" data-options="field:'company_name',width:200,halign:'center'">Company Name</th>
                <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
                <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
                <th rowspan="2" data-options="field:'description',width:700,halign:'center'">Description</th>
                <th hidden colspan="3" data-options="field:'',width:100">Original Currency</th>
                <th colspan="3" data-options="field:'',width:100">Local Currency</th>
            </tr>
            <tr>
                <th hidden data-options="field:'original_debit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
                <th hidden data-options="field:'original_credit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
                <th hidden data-options="field:'rates',width:100,halign:'center',align:'right',formatter:numberformatDefault">Rates</th>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Debit</th>
                <th data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Credit</th>
            </tr>
        </thead>
    </table>
</div>

<div id="dlg_generate" class="easyui-dialog" title="Save Data" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <div class="alert alert-warning" role="alert">
        Please wait until the save process is complete
    </div>
    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">
        <p>History Save Data</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/journal_posting_banks/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $("#fieldset").show();
        $('#frm_insert').form('clear');
        $("#journal_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#journal_date").datebox('enable');
        $("#modul").textbox('setValue', "BANK STATEMENT");
        $("#journal_type").textbox('setValue', "20241120000001");
        $("#trans_type").combobox('enable');
        $("#document_no").combogrid('enable');
        $("#posting_to").combogrid('enable');
        $("#bank_account").combogrid('enable');
        $("#number").textbox('enable');
        $("#preview").linkbutton('enable');

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
            url: "<?= base_url('finance/journal_posting_banks/number/') ?>" + id_menu + "/" + btoa(journal_date),
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
        var journal_date = $("#journal_date").datebox('getValue');
        var modul = $("#modul").textbox('getValue');
        var trans_type = $("#trans_type").combobox('getValue');
        var posting_to = $("#posting_to").combogrid('getValue');
        var bank_account = $("#bank_account").combogrid('getValue');
        var number = $("#number").textbox('getValue');
        var document_no = $("#document_no").combogrid('getText');

        if (posting_to == "" || document_no == "") {
            toastr.info('Please Choose Document No and Posting To');
        } else {
            $.ajax({
                method: 'post',
                url: '<?= base_url('finance/journal_posting_banks/datatablesCheck') ?>',
                data: {
                    journal_date: journal_date,
                    modul: modul,
                    posting_to: posting_to,
                    document_no: document_no
                },
                success: function(result) {
                    if(result == 0){
                        var lastIndex;
                        var dg = $('#dg2').datagrid({
                            url: '<?= base_url('finance/journal_posting_banks/datatablesTemp') ?>?journal_date=' + window.btoa(journal_date) +
                                "&modul=" + window.btoa(modul) +
                                "&posting_to=" + window.btoa(posting_to) +
                                "&bank_account=" + window.btoa(bank_account) +
                                "&document_no=" + window.btoa(document_no) +
                                "&trans_type=" + window.btoa(trans_type) +
                                "&number=" + window.btoa(number),
                            onLoadSuccess: function(data){
                                $("#local_debit").numberbox('setValue', data['footer'][0].local_debit);
                                $("#local_credit").numberbox('setValue', data['footer'][0].local_credit);
                            }
                        });
                    }else{
                        toastr.error("This Modul " + modul + " has been created");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    toastr.error("This data is already used in other modules, You must delete it First", "Error");
                },
            });
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

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            // toastr.warning("Not Available");
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            $("#journal_date").datebox('disable');
            $("#trans_type").combobox('disable');
            $("#document_no").combogrid('disable');
            $("#posting_to").combogrid('disable');
            $("#bank_account").combogrid('disable');
            $("#number").textbox('disable');

            // $("#fieldset").hide();
            setTimeout(function() {
                $("#number").textbox('setValue', row.number);
                $("#modul").textbox('setValue', row.modul);
                $("#journal_type").textbox('setValue', row.journal_type_id);
            }, 2000);


            var lastIndex;
            $('#dg2').datagrid({
                url: '<?= base_url('finance/journal_posting_banks/datatableUpdates') ?>?number=' + window.btoa(row.number),
                onLoadSuccess: function(data){
                    $("#local_debit").numberbox('setValue', data['footer'][0].local_debit);
                    $("#local_credit").numberbox('setValue', data['footer'][0].local_credit);
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
            });

        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
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
                            data: "period=" + row.journal_date + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function (lock) {
                                if(lock.total > 0){
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/journal_posting_banks/delete') ?>',
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

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_journal_type = $("#filter_journal_type").combogrid('getValue');
        var filter_voucher = $("#filter_voucher").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_journal_type=" + window.btoa(filter_journal_type) +
            "&filter_voucher=" + window.btoa(filter_voucher);

        $('#dg').datagrid({
            url: '<?= base_url('finance/journal_posting_banks/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit:true,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/journal_posting_banks/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_journal_type = $("#filter_journal_type").combogrid('getValue');
        var filter_voucher = $("#filter_voucher").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_journal_type=" + window.btoa(filter_journal_type) +
            "&filter_voucher=" + window.btoa(filter_voucher);

        window.location.assign('<?= base_url('finance/journal_posting_banks/print/excel') ?>' + url);
    }

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_journal_posting_banks.xls') ?>');
    }

    function print_voucher() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            window.open("<?= base_url('finance/journal_posting_banks/print_voucher/') ?>" + window.btoa(row.number), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please select GL No in the table first!");
        }
    }

    //RELOAD
    function reload() {
        window.location.reload();
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
                    var journal_type = $("#journal_type").textbox('getValue');
                    var number = $("#number").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    var local_debit = $("#local_debit").numberbox('getValue');
                    var local_credit = $("#local_credit").numberbox('getValue');

                    endEditing();
                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + journal_date + "&menus_id=<?= $menus_id ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            if (rows.length > 0) {
                                if(local_debit == local_credit){
                                    $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                                        if (r) {
                                            Swal.fire({
                                                title: 'Please Wait for Saving Data',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            if (totalrows > 0) {
                                                requestData(totalrows, rows);
                                                $('#dlg_insert').dialog('close');
                                                $('#dlg_generate').dialog('open');

                                                function requestData(total, json, jml = 1, value = 0) {
                                                    if (value < 100) {
                                                        value = Math.floor((jml / total) * 100);
                                                        var i = (jml - 1);

                                                        $('#p_upload').progressbar('setValue', value);
                                                        $('#p_start').html(jml);
                                                        $('#p_finish').html(totalrows);

                                                        $.ajax({
                                                            type: "post",
                                                            url: '<?= base_url('finance/journal_posting_banks/create') ?>',
                                                            data: {
                                                                journal_date: journal_date,
                                                                modul: modul,
                                                                journal_type_id: journal_type,
                                                                number: number,
                                                                remarks: remarks,
                                                                id: json[i].id,
                                                                trans_date: json[i].trans_date,
                                                                document_no: json[i].document_no,
                                                                invoice_no: json[i].invoice_no,
                                                                company_id: json[i].company_id,
                                                                company_name: json[i].company_name,
                                                                account_number: json[i].account_number,
                                                                account_name: json[i].account_name,
                                                                description: json[i].description,
                                                                currency: json[i].currency,
                                                                original_debit: json[i].local_debit,
                                                                original_credit: json[i].local_credit,
                                                                rates: json[i].rates,
                                                                local_debit: json[i].local_debit,
                                                                local_credit: json[i].local_credit
                                                            },
                                                            dataType: "json",
                                                            success: function(result) {
                                                                requestData(total, json, jml + 1, value);

                                                                if (result.theme == "success") {
                                                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                                } else {
                                                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                                }

                                                                $("#p_remarks").append(title + "<br>");

                                                                if (i == (totalrows - 1)) {
                                                                    Swal.close();
                                                                    $('#dlg_generate').dialog('close');

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
                                                            }
                                                        });
                                                    }
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    toastr.error("Debit & Credit Not Balance");
                                }
                            } else {
                                toastr.info("Please Checklist Posting");
                            }
                        }
                    });
                }
            }]
        });

        $("#trans_type").combobox({
            onChange: function(trans_type) {
                if(trans_type == "AP PAYMENT"){
                    var urlDoc = '<?= base_url('finance/account_banks/reads/supplier') ?>';
                }else if(trans_type == "AR RECEIPT"){
                    var urlDoc = '<?= base_url('finance/account_banks/reads/customer') ?>';
                }

                $("#bank_account").combogrid({
                    url: urlDoc,
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
                    onSelect: function(index, bank){
                        var journal_date = $("#journal_date").datebox('getValue');
                        $("#posting_to").combogrid('setValue', bank.posting_to);

                        $("#document_no").combogrid({
                            url: '<?= base_url('finance/journal_posting_banks/readModul?trans_type=') ?>' + btoa(trans_type) + "&account_number=" + btoa(bank.account_number) + "&journal_date=" + btoa(journal_date),
                            panelWidth: 800,
                            idField: 'number',
                            textField: 'number',
                            multiple: true,
                            mode: 'remote',
                            fitColumns: true,
                            prompt: "Choose Document No",
                            columns: [
                                [{
                                    field:'ck',
                                    checkbox:true
                                },{
                                    field: 'trans_date',
                                    title: 'Trans Date',
                                    width: 80
                                },{
                                    field: 'company_name',
                                    title: 'Company Name',
                                    width: 250
                                },{
                                    field: 'document_no',
                                    title: 'Document No',
                                    width: 150
                                },{
                                    field: 'number',
                                    title: 'GL No',
                                    width: 120
                                },{
                                    field: 'amount',
                                    title: 'Amount',
                                    width: 100,
                                    formatter: numberformatDefault,
                                    halign: 'center',
                                    align: 'right'
                                }]
                            ],
                        });
                    }
                });
            }
        });
        
        $("#posting_to").combogrid({
            url: '<?= base_url('finance/account_banks/reads/journal') ?>',
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

        $("#journal_date").datebox({
            onChange: function(val) {
                number("<?= $menus_id ?>", val);
            }
        });

        $("#filter_voucher").combobox({
            url: '<?= base_url('finance/journal_posting_banks/readVoucher') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Voucher No"
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
            url: '<?= base_url('finance/journal_posting_banks/datatableDetails?number=') ?>' + btoa(number),
            pagination: false,
            rownumbers: true,
            remoteFilter: true,
        }).datagrid('enableFilter');
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

    function numberformatDefaultIdr(value, row){
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