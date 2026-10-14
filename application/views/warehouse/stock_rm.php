<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'id',width:120,halign:'center'">Part No EBWS</th>
            <th data-options="field:'number',width:200,halign:'center'">Part No Supplier</th>
            <th data-options="field:'name',width:200,halign:'center'">Description</th>
            <th data-options="field:'product_family',width:150,align:'center'">Product Family</th>
            <th data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th data-options="field:'end_stock',width:120,halign:'center',align:'right', formatter:numberformat,styler:numberStyle">Current Stock</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<iframe id="printout" src="<?= base_url('warehouse/stock_rm/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('warehouse/stock_rm/print/excel') ?>');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/stock_rm/datatables') ?>',
            pagination: true,
            clientPaging: true,
            // remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');
    });

    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberStyle(value, row, index) {
        if (value <= 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }
</script>