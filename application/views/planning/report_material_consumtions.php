<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="padding:10px;">
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:18%;" id="filter_p_month_from" value="<?= date("m") ?>" class="easyui-combobox">
                <input style="width:10%;" id="filter_p_year_from" value="<?= date("Y") ?>" class="easyui-combobox"> To
                <input style="width:18%;" id="filter_p_month_to" value="<?= date("m") ?>" class="easyui-combobox">
                <input style="width:10%;" id="filter_p_year_to" value="<?= date("Y") ?>" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a> -->
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:500px; border: 0;"></iframe>
</div>

<script>
    function filter() {
        var filter_p_year_from = $("#filter_p_year_from").datebox("getValue");
        var filter_p_month_from = $("#filter_p_month_from").datebox("getValue");
        var filter_p_year_to = $("#filter_p_year_to").datebox("getValue");
        var filter_p_month_to = $("#filter_p_month_to").datebox("getValue");
        var filter_customer = $("#filter_customer").combobox("getValue");
        var filter_item_family = $("#filter_item_family").combobox("getValue");
        var url = "?filter_p_year_from=" + window.btoa(filter_p_year_from) +
            "&filter_p_month_from=" + window.btoa(filter_p_month_from) +
            "&filter_p_year_to=" + window.btoa(filter_p_year_to) +
            "&filter_p_month_to=" + window.btoa(filter_p_month_to) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_family=" + window.btoa(filter_item_family);
        if (filter_p_year_from == "" || filter_p_month_from == "" || filter_p_year_to == "" || filter_p_month_to == "") {
            toastr.warning("Please select Periode!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/report_material_consumtions/print') ?>' + url);
        }
    }

    function excel() {
        var filter_p_year_from = $("#filter_p_year_from").datebox("getValue");
        var filter_p_month_from = $("#filter_p_month_from").datebox("getValue");
        var filter_p_year_to = $("#filter_p_year_to").datebox("getValue");
        var filter_p_month_to = $("#filter_p_month_to").datebox("getValue");
        var filter_customer = $("#filter_customer").combobox("getValue");
        var filter_item_family = $("#filter_item_family").combobox("getValue");
        var url = "?filter_p_year_from=" + window.btoa(filter_p_year_from) +
            "&filter_p_month_from=" + window.btoa(filter_p_month_from) +
            "&filter_p_year_to=" + window.btoa(filter_p_year_to) +
            "&filter_p_month_to=" + window.btoa(filter_p_month_to) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_family=" + window.btoa(filter_item_family);
        if (filter_p_year_from == "" || filter_p_month_from == "" || filter_p_year_to == "" || filter_p_month_to == "") {
            toastr.warning("Please select Periode!");
        } else {
            window.location.assign('<?= base_url('planning/report_material_consumtions/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $('#filter_p_year_from').combobox({
        url: '<?= base_url('planning/report_material_consumtions/readYears'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years'
    });

    $('#filter_p_month_from').combobox({
        url: '<?= base_url('planning/report_material_consumtions/readMonths'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months'
    });

    $('#filter_p_year_to').combobox({
        url: '<?= base_url('planning/report_material_consumtions/readYears'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years'
    });

    $('#filter_p_month_to').combobox({
        url: '<?= base_url('planning/report_material_consumtions/readMonths'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months'
    });

    $('#filter_customer').combobox({
        url: '<?php echo base_url('master/customers/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Customer',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }]
    });

    $('#filter_item_family').combobox({
        url: '<?php echo base_url('planning/report_material_consumtions/readItemFamily'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Select Product Family',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }]
    });

    $(function() {
        var filter_p_year_from = $("#filter_p_year_from").combobox('getValue');
        var filter_p_month_from = $("#filter_p_month_from").combobox('getValue');
        var filter_p_year_to = $("#filter_p_year_to").combobox('getValue');
        var filter_p_month_to = $("#filter_p_month_to").combobox('getValue');

    });
</script>