<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 40%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Posting No</span>
                    <input style="width:60%;" id="filter_posting_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice No</span>
                    <input style="width:60%;" id="filter_invoice_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Clearing Adj No</span>
                    <input style="width:60%;" id="filter_cl_no" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" id="filter_currency" value="INR" class="easyui-combobox" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Display</span>
                    <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                        <option value="Summary">Summary</option>
                        <option value="Detail">Detail</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </div>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>
<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>
<script>
    function filter() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_supplier = $("#filter_supplier").combogrid("getValue");
        var filter_posting_no = $("#filter_posting_no").combogrid("getValue");
        var filter_document_no = $("#filter_document_no").combogrid("getValue");
        var filter_invoice_no = $("#filter_invoice_no").combogrid("getValue");
        var filter_cl_no = $("#filter_cl_no").combogrid("getValue");
        var filter_currency = $("#filter_currency").combobox("getValue");
        var filter_display = $("#filter_display").combobox("getValue");
        var filter_status = $("#filter_status").combobox("getValue");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + btoa(filter_supplier) +
            "&filter_posting_no=" + btoa(filter_posting_no) +
            "&filter_document_no=" + btoa(filter_document_no) +
            "&filter_invoice_no=" + btoa(filter_invoice_no) +
            "&filter_cl_no=" + btoa(filter_cl_no) +
            "&filter_currency=" + filter_currency +
            "&filter_display=" + filter_display +
            "&filter_status=" + filter_status;

        if (filter_from == "" && filter_to == "" && filter_supplier == "") {
            toastr.warning("Please select Trans Date or Supplier");
        } else {
            if(filter_display == "Summary"){
                $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
                $("#printout").attr('src', '<?= base_url('finance/report_ap2/print') ?>' + url);
            }else{
                $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
                $("#printout").attr('src', '<?= base_url('finance/report_ap/print') ?>' + url);
            }
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_supplier = $("#filter_supplier").combogrid("getValue");
        var filter_posting_no = $("#filter_posting_no").combogrid("getValue");
        var filter_document_no = $("#filter_document_no").combogrid("getValue");
        var filter_invoice_no = $("#filter_invoice_no").combogrid("getValue");
        var filter_cl_no = $("#filter_cl_no").combogrid("getValue");
        var filter_currency = $("#filter_currency").combobox("getValue");
        var filter_display = $("#filter_display").combobox("getValue");
        var filter_status = $("#filter_status").combobox("getValue");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + btoa(filter_supplier) +
            "&filter_posting_no=" + btoa(filter_posting_no) +
            "&filter_document_no=" + btoa(filter_document_no) +
            "&filter_invoice_no=" + btoa(filter_invoice_no) +
            "&filter_cl_no=" + btoa(filter_cl_no) +
            "&filter_currency=" + filter_currency +
            "&filter_display=" + filter_display +
            "&filter_status=" + filter_status;

        if (filter_from == "" && filter_to == "" && filter_supplier == "") {
            toastr.warning("Please select Trans Date or Supplier");
        } else {
            if(filter_display == "Summary"){
                window.location.assign('<?= base_url('finance/report_ap2/print/excel') ?>' + url);
            }else{
                window.location.assign('<?= base_url('finance/report_ap/print/excel') ?>' + url);
            }
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $("#filter_supplier").combogrid({
            url: '<?php echo base_url('finance/report_ap/readSuppliers'); ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 300
                }]
            ],
            onSelect: function(val, row){
                $('#filter_currency').combobox({
                    // url: '<?php echo base_url('master/currencies/reads'); ?>',
                    data: [{
                        "number" : "INR",
                    },{
                        "number" : row.currency,
                    }],
                    valueField: 'number',
                    textField: 'number',
                    prompt: 'Choose Currency',
                });
            }
        });

        $("#filter_posting_no").combogrid({
            url: '<?= base_url('finance/report_ap/readPostingNo') ?>',
            panelWidth: 300,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Posting No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Posting No',
                    width: 150
                }, {
                    field: 'journal_date',
                    title: 'Posting Date',
                    width: 150
                }]
            ],
        });

        $("#filter_document_no").combogrid({
            url: '<?= base_url('finance/report_ap/readDocumentNo') ?>',
            panelWidth: 300,
            idField: 'document_no',
            textField: 'document_no',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Document No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'document_no',
                    title: 'Document No',
                    width: 300
                }]
            ],
        });

        $("#filter_invoice_no").combogrid({
            url: '<?= base_url('finance/report_ap/readInvoiceNo') ?>',
            panelWidth: 300,
            idField: 'invoice_no',
            textField: 'invoice_no',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Invoice No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'invoice_no',
                    title: 'Invoice No',
                    width: 300
                }]
            ],
        });

        $("#filter_cl_no").combogrid({
            url: '<?= base_url('finance/report_ap/readClno') ?>',
            panelWidth: 200,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Clearing Adj No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Clearing No',
                    width: 200
                }]
            ],
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
</script>