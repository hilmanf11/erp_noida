<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= "2024-11-01" ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice No</span>
                    <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer PO</span>
                    <input style="width:60%;" id="filter_po" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_no" class="easyui-combogrid">
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
        var filter_customer = $("#filter_customer").combobox("getValue");
        var filter_invoice_no = $("#filter_invoice_no").combobox("getValue");
        var filter_po = $("#filter_po").combobox("getValue");
        var filter_item_no = $("#filter_item_no").combogrid("getValue");

        var url = "?filter_from=" + btoa(filter_from) +
            "&filter_to=" + btoa(filter_to) +
            "&filter_customer=" + btoa(filter_customer) +
            "&filter_invoice_no=" + btoa(filter_invoice_no) +
            "&filter_po=" + btoa(filter_po) +
            "&filter_item_no=" + btoa(filter_item_no);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/report_sales_registers/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_customer = $("#filter_customer").combobox("getValue");
        var filter_invoice_no = $("#filter_invoice_no").combobox("getValue");
        var filter_po = $("#filter_po").combobox("getValue");
        var filter_item_no = $("#filter_item_no").combogrid("getValue");

        var url = "?filter_from=" + btoa(filter_from) +
            "&filter_to=" + btoa(filter_to) +
            "&filter_customer=" + btoa(filter_customer) +
            "&filter_invoice_no=" + btoa(filter_invoice_no) +
            "&filter_po=" + btoa(filter_po) +
            "&filter_item_no=" + btoa(filter_item_no);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            window.location.assign('<?= base_url('finance/report_sales_registers/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $('#filter_customer').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Customer Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $("#filter_invoice_no").combobox({
                    url: '<?php echo base_url('finance/report_sales_registers/readInvoice/'); ?>' + btoa(row.id),
                    valueField: 'number',
                    textField: 'number',
                    prompt: 'Choose Purchase Invoice',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $("#filter_po").combobox({
                    url: '<?php echo base_url('finance/report_sales_registers/readPo/'); ?>' + btoa(row.id),
                    valueField: 'customer_po',
                    textField: 'customer_po',
                    prompt: 'Choose Customer PO',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_item_no').combogrid({
                    url: '<?= base_url('finance/report_sales_registers/readPartNo/') ?>' + btoa(row.id),
                    panelWidth: 520,
                    idField: 'item_fg_id',
                    textField: 'item_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Product No",
                    columns: [
                        [{
                            field: 'item_fg_number',
                            title: 'Part No EBWS',
                            width: 120
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            width: 250
                        }, {
                            field: 'item_fg_name',
                            title: 'Description',
                            width: 200
                        }]
                    ],
                });
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
</script>