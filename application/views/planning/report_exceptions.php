<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
            <tr>
                <th data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
                <th data-options="field:'item_rm_number',width:250,halign:'center'">Part No Supplier</th>
                <th data-options="field:'item_rm_name',width:250,halign:'center'">Description</th>
                <th data-options="field:'mpq',width:80,align:'center', styler:numberStyle">MPQ</th>
                <th data-options="field:'moq',width:80,align:'center', styler:numberStyle">MOQ</th>
                <th data-options="field:'leadtime',width:80,align:'center', styler:numberStyle">Leadtime <br>(Days)</th>
                <th data-options="field:'share_order',width:80,align:'center', styler:numberStyle">Share Order %</th>
                <th data-options="field:'hsn_code',width:100,align:'center', styler:hsnStyle">HSN</th>
                <!-- <th data-options="field:'status_supplier',width:100,align:'center', styler:statusStyle">Status</th> -->
            </tr>
        </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <?= $button ?>
</div>

<iframe id="printout" src="<?= base_url('planning/report_exceptions/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //PRINT PDF
    function pdf() {
        $('#dg').datagrid('print', 'DataGrid');
    }

    //PRINT EXCEL
    function excel() {
        $('#dg').datagrid('toExcel', 'report_exceptions.xls');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/report_exceptions/datatables') ?>',
            pagination: true,
            clientPaging: true,
            // remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');
    });

    function numberStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function hsnStyle(value, row, index) {
        if (value == "-") {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function statusStyle(value, row, index) {
        if (value == "CHECKING") {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
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
</script>