<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <!-- <th field="ck" checkbox="true"></th> -->
            <th data-options="field:'id',width:120,halign:'center'">Part No EBWS</th>
            <th data-options="field:'number',width:200,halign:'center'">Part No Supplier</th>
            <th data-options="field:'item_family_name',width:120,halign:'center'">Product Family</th>
            <th data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th data-options="field:'scrap',width:120,halign:'center',align:'right', formatter:numberformat">Rejection<br>(Scrap Vendor)</th>
            <th data-options="field:'rejection',width:120,halign:'center',align:'right', formatter:numberformat">Scan Rejection<br>(Store)</th>
            <th data-options="field:'qty_return',width:120,halign:'center',align:'right', formatter:numberformat">Return to Supplier</th>
            <th data-options="field:'balance',width:120,halign:'center',align:'right', formatter:numberformat,styler:numberStyle">Balance Stock</th>
        </tr>
    </thead>
</table>

<div id="toolbar">
    <!-- <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 60%; float: left;">
            <div class="fitem">
                <span style="width:10%; display:inline-block;">Period</span>
                <input style="width:18%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:18%;" id="filter_to" class="easyui-datebox" value="<?= date('Y-m-d', strtotime('+14 days', strtotime(date('Y-m-01')))); ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton c1" id="generate" onclick="generate()"><i class="fa fa-database"></i> Calculate WIP</a>
            </div>
        </div>
        <div style="width: 40%; float: left;">
            <div class="alert alert-warning">
                It is recommended to calculate the data per week (maximum 2 weeks), the data will be saved and you will no longer need to calculate if the finished good quantity is correct.
            </div>
        </div>
    </fieldset> -->
    <?= $button ?>
</div>

<!-- <iframe id="printout" src="<?= base_url('warehouse/report_rejections/print') ?>" style="width: 100%;" hidden></iframe> -->
<script>
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to;

        $('#dg').datagrid('loading');

        $.ajax({
            url: '<?= base_url('warehouse/wip_reports/datatables') ?>',
            type: 'POST',
            data: {
                filter_from: filter_from,
                filter_to: filter_to
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
        $('#dg').datagrid('toExcel', 'report_rejections.xls');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/report_rejections/datatables') ?>',
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