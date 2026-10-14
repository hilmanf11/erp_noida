<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
        <th data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
    </thead>
    <thead>
        <tr>
            <th data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th data-options="field:'supplier_name',width:300,halign:'center'">Supplier Name</th>
            <th data-options="field:'receipt_id',width:150,halign:'center'">Receipt ID</th>
            <th data-options="field:'receipt_date',width:100,halign:'center'">Receipt Date</th>
            <th data-options="field:'po_no',width:130,halign:'center'">PO No</th>
            <th data-options="field:'bc_document',width:150,halign:'center'">Invoice No</th>
            <th data-options="field:'bc_date',width:100,halign:'center'">Invoice Date</th>
            <th data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th data-options="field:'qty_receipt',width:100,halign:'center',align:'right', formatter:numberformat">Qty Receipt</th>
            <th data-options="field:'qty_mpq',width:100,halign:'center',align:'right', formatter:numberformat">MPQ</th>
            <th data-options="field:'qty_label',width:100,halign:'center',align:'right', formatter:numberformat">Label Est</th>
            <th data-options="field:'label',width:100,halign:'center',align:'right', formatter:numberformat">Label Created</th>
            <th data-options="field:'scan',width:100,halign:'center',align:'right', formatter:numberformat">Label Scan</th>
            <th data-options="field:'balance2',width:100,halign:'center',align:'right', formatter:numberformat">Balance<br>(Est - Create)</th>
            <th data-options="field:'balance',width:100,halign:'center',align:'right', formatter:numberformat">Balance<br>(Create - Scan)</th>
            <th data-options="field:'status',width:80,align:'center', styler:statusStyle">Status</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 120px; padding: 10px;">
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 60%; float: left;">
            <div class="fitem">
                <span style="width:10%; display:inline-block;">Period</span>
                <input style="width:18%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:18%;" id="filter_to" class="easyui-datebox" value="<?= date('Y-m-d', strtotime('+14 days', strtotime(date('Y-m-01')))); ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                &nbsp; &nbsp; <input value="on" class="easyui-checkbox" id="not_balance"> &nbsp; Not Balance &nbsp; &nbsp;
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>


<!-- <iframe id="printout" src="<?= base_url('purchase/purchase_order_receipt_reports/print') ?>" style="width: 100%;" hidden></iframe> -->
<script>
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var not_balance = $("#not_balance").checkbox('options');

        if(not_balance.checked == false){
            var balance = 0;
        }else{
            var balance = 1;
        }

        $('#dg').datagrid('loading');

        $.ajax({
            url: '<?= base_url('purchase/purchase_order_receipt_reports/datatables') ?>',
            type: 'POST',
            data: {
                filter_from: filter_from,
                filter_to: filter_to,
                not_balance: balance,
            },
            dataType: 'json',
            success: function(result) {
                $('#dg').datagrid('loadData', result).datagrid('loaded');
            }
        });
    }

    //PRINT PDF
    function pdf() {
        $('#dg').datagrid('print', 'DataGrid');
    }

    //PRINT EXCEL
    function excel() {
        $('#dg').datagrid('toExcel', 'gate_entry_reports.xls');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            // url: '<?= base_url('purchase/purchase_order_receipt_reports/datatables') ?>',
            pagination: true,
            clientPaging: true,
            // remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        filter();
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

    function statusStyle(value, row, index) {
        if (value == 'CLOSE') {
            return 'background-color:#FFC8C8; color: red; font-weight:bold;';
        } else {
            return 'background-color:#C8FFCC; color: green; font-weight:bold;';
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