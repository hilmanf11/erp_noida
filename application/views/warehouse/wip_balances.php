<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'supply_type',width:80,align:'center',formatter:issuedformat,styler:statusIssued">Status</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <!-- <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th> -->
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'request_no',width:150,align:'center'">Kanban No</th>
            <th rowspan="2" data-options="field:'workorder',width:150,align:'center'">Workorder No</th>
            <th rowspan="2" data-options="field:'qty_act',width:80,halign:'center',align:'right', formatter:numberformat">Need</th>
            <th rowspan="2" data-options="field:'issued',width:80,halign:'center',align:'right', formatter:numberformat">Issued</th>
            <th rowspan="2" data-options="field:'qty_issued_bal',width:80,halign:'center',align:'right', formatter:numberformat,styler:numberStyle">Balance</th>
            <!-- <th rowspan="2" data-options="field:'warehouse',width:80,halign:'center',align:'right', formatter:numberformat">Warehouse</th> -->
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
    <div style="float: right; text-align: right; width: 50%;">
        <div class="fitem">
            <span style="width:10%; display:inline-block;">Filter Data &nbsp;</span>
            <input style="width:20%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
            <input style="width:20%;" id="filter_to" class="easyui-datebox" value="<?= date('Y-m-t'); ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            <select style="width:10%;" id="type" class="easyui-combobox" panelHeight="auto">
                <option value="PP">PP</option>
                <option value="HAV">HAV</option>
            </select>
            <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i></a>
        </div>
    </div>
</div>

<!-- PDF -->
<!-- <iframe id="printout" src="<?= base_url('warehouse/wip_balances/print') ?>" style="width: 100%;" hidden></iframe> -->
<script>
    //ADD DATA
    function add() {
        // $('#dlg_insert').dialog('open');
        // url_save = '<?= base_url('warehouse/wip_balances/create') ?>';
        // $('#frm_insert').form('clear');
    }

    //EDIT DATA
    function update() {
        // var row = $('#dg').datagrid('getSelected');
        // if (row) {
        //     $('#dlg_insert').dialog('open');
        //     $('#frm_insert').form('load', row);
        //     url_save = '<?= base_url('warehouse/wip_balances/update') ?>?id=' + btoa(row.id);
        // } else {
        //     toastr.warning("Please select one of the data in the table first!", "Information");
        // }
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
                            method: 'post',
                            url: '<?= base_url('warehouse/wip_balances/delete') ?>',
                            data: {
                                id: row.id,
                                workorder: row.workorder,
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
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var type = $("#type").combobox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');

        $('#dg').datagrid('loading');
        $.ajax({
            url: '<?= base_url('warehouse/wip_balances/datatables') ?>',
            type: 'POST',
            data: {
                type: type,
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
        // $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        // var supply_type = $('#dg').datagrid('getFilterRule', 'supply_type');
        // var item_rm_id = $('#dg').datagrid('getFilterRule', 'item_rm_id');
        // var item_rm_number = $('#dg').datagrid('getFilterRule', 'item_rm_number');
        // var uom = $('#dg').datagrid('getFilterRule', 'uom');
        // var request_no = $('#dg').datagrid('getFilterRule', 'request_no');
        // var workorder = $('#dg').datagrid('getFilterRule', 'workorder');
        // var qty_act = $('#dg').datagrid('getFilterRule', 'qty_act');
        // var issued = $('#dg').datagrid('getFilterRule', 'issued');
        // var qty_issued_bal = $('#dg').datagrid('getFilterRule', 'qty_issued_bal');
        // var created_by = $('#dg').datagrid('getFilterRule', 'created_by');
        // var created_date = $('#dg').datagrid('getFilterRule', 'created_date');
        // var updated_by = $('#dg').datagrid('getFilterRule', 'updated_by');
        // var updated_date = $('#dg').datagrid('getFilterRule', 'updated_date');
        
        // var url = "?supply_type=" + window.btoa(supply_type.value) +
        //     "&item_rm_id=" + window.btoa(item_rm_id.value) +
            // "&item_rm_number=" + window.btoa(item_rm_number.value) +
            // "&uom=" + window.btoa(uom.value) +
            // "&request_no=" + window.btoa(request_no.value) +
            // "&workorder=" + window.btoa(workorder.value) +
            // "&qty_act=" + window.btoa(qty_act.value) +
            // "&issued=" + window.btoa(issued.value) +
            // "&qty_issued_bal=" + window.btoa(qty_issued_bal.value) +
            // "&created_by=" + window.btoa(created_by.value) +
            // "&created_date=" + window.btoa(created_date.value) +
            // "&updated_by=" + window.btoa(updated_by.value) +
            // "&updated_date=" + window.btoa(updated_date.value);

        $('#dg').datagrid('toExcel','wip_balances.xls');
        // window.location.assign('<?= base_url('warehouse/wip_balances/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            // url: '<?= base_url('warehouse/wip_balances/datatables') ?>',
            pagination: true,
            clientPaging: true,
            // remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        // $('#dg').datagrid('addFilterRule', {
        //     field: 'created_date',
        //     op: 'contains',
        //     value: "<?= date("Y-m-d") ?>"
        // });

        // $('#dg').datagrid('doFilter');

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        // $('#item_rm_id').combogrid({
        //     url: '<?= base_url('master/item_rm/reads') ?>',
        //     panelWidth: 400,
        //     idField: 'id',
        //     textField: 'number',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Choose Product",
        //     columns: [
        //         [{
        //             field: 'number',
        //             title: 'Product No',
        //             width: 200
        //         }, {
        //             field: 'name',
        //             title: 'Product Name',
        //             width: 200
        //         }]
        //     ],
        //     onSelect: function(valItem, rowItem) {
        //         $("#uom").textbox('setValue', rowItem.uom);
        //     }
        // });

        // $("#request_no").combobox({
        //     url: '<?= base_url('planning/supply_sheets/readRequestNo') ?>',
        //     valueField: 'request_no',
        //     textField: 'request_no',
        //     prompt: "Choose Supply Sheet"
        // });
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
        if (value < 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function statusIssued(value, row, index) {
        if (value == "OPEN") {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function issuedformat(value, row) {
        if (value == "OPEN") {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
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