<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <form id="frm_search" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" name="filter_from" id="filter_from" value="<?= "2024-11-01" ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" name="filter_to" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="filter_supplier" id="filter_supplier" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" name="filter_currency" id="filter_currency" value="INR" class="easyui-combobox" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Aging Criteria</span>
                    <select style="width:60%;" name="filter_criteria[]" id="filter_criteria" class="easyui-combobox" panelHeight="auto" data-options="prompt:'Choose Criteria'" multiple="true">
                        <option value="1-30_Days">1-30 Days</option>
                        <option value="31-45_Days">31-45 Days</option>
                        <option value="46-60_Days">46-60 Days</option>
                        <option value="61-75_Days">61-75 Days</option>
                        <option value="76-90_Days">76-90 Days</option>
                        <option value="91-120_Days">91-120 Days</option>
                        <option value="121-180_Days">121-180 Days</option>
                        <option value="181-365_Days">181-365 Days</option>
                        <option value="Over_365_Days">Over 365 Days</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </form>
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
        var filter_supplier = $("#filter_supplier").combobox("getValue");
        var filter_currency = $("#filter_currency").combobox("getValue");
        var filter_criteria = $("#filter_criteria").combobox("getText");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_currency=" + window.btoa(filter_currency) +
            "&filter_criteria=" + window.btoa(filter_criteria);

        if (filter_from == "" || filter_to == "" || filter_criteria == "") {
            toastr.warning("Please select Trans Date & Criteria!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/ap_schedules/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_supplier = $("#filter_supplier").combobox("getValue");
        var filter_currency = $("#filter_currency").combobox("getValue");
        var filter_criteria = $("#filter_criteria").combobox("getText");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_currency=" + window.btoa(filter_currency) +
            "&filter_criteria=" + window.btoa(filter_criteria);

        if (filter_from == "" || filter_to == "" || filter_criteria == "") {
            toastr.warning("Please select Trans Date & Criteria!");
        } else {
            window.location.assign('<?= base_url('finance/ap_schedules/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#filter_supplier').combobox({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Supplier Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
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