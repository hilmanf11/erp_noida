<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th data-options="field:'status2',width:80,align:'center', formatter: statusformat, styler:statusStyle">Status</th>
            <th data-options="field:'id',width:150,halign:'center'">Part No EBWS</th>
            <th data-options="field:'number',width:150,halign:'center'">Part No Supplier</th>
            <th data-options="field:'name',width:200,halign:'center'">Description</th>
            <th data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th data-options="field:'qty_scrap',width:120,halign:'center',align:'right',formatter:numberformat">Total Qty</th>
            <th data-options="field:'qty_invoice',width:120,halign:'center',align:'right',formatter:numberformat">Total Invoice</th>
            <th data-options="field:'qty_balance',width:120,halign:'center',align:'right',formatter:numberformat">Balance</th>
        </tr>
    </thead>
</table>

<div id="toolbar">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-rotate-left"></i> Reload</a>
</div>

<div id="toolbar2">
    <?= $button ?>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Document No</span>
                <input style="width:60%;" name="number" id="number" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" name="item_id" id="item_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">UOM</span>
                <input style="width:30%;" id="uom" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" required="" class="easyui-numberbox" data-options="precision:2">
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_history" class="easyui-dialog" title="Detail for Scrap" data-options="closed: true, modal:true" style="width: 80%; height: 500px; top: 10px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead>
            <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center'">Trans Date</th>
                <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Document No</th>
                <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
                <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
                <th colspan="3" data-options="field:'',width:80,align:'center'">Approved</th>
                <th colspan="2" data-options="field:'',width:80,align:'center'">Created</th>
            </tr>
            <tr>
                <th data-options="field:'approved_to',width:100,align:'center', styler:styleApproved, formatter:formatApproved">Status</th>
                <th data-options="field:'approved_by',width:100,align:'center'">By</th>
                <th data-options="field:'approved_date',width:150,align:'center'">Date</th>
                <th data-options="field:'created_by',width:120,align:'center'">By</th>
                <th data-options="field:'created_date',width:160,align:'center'">Date</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('production/scrap_booking/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('production/scrap_booking/create') ?>';
        // $('#frm_insert').form('clear');
        autonumber();
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
    }

    function autonumber() {
        $.ajax({
            type: "post",
            url: "<?= base_url('production/scrap_booking/autonumber') ?>",
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                } else {
                    $("#number").textbox('setValue', result);
                }
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg_history').datagrid('getSelected');
        if(row.approved_to == ""){
            toastr.error("You Cannot Update because the status is Approved");
            return false;
        }

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#uom").textbox('setValue', row.uom);
            url_save = '<?= base_url('production/scrap_booking/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg_history').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        if(row.approved_to == ""){
                            toastr.error("You Cannot Delete because the status is Approved");
                            return false;
                        }

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('closing/locks/checkLock') ?>",
                            data: "period=" + row.trans_date + "&menus_id=<?= $id_menu ?>",
                            dataType: "json",
                            success: function (lock) {
                                if(lock.total > 0){
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('production/scrap_booking/delete') ?>',
                                    data: {
                                        id: row.id,
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error("This data is already used in other modules, You must delete it First", "Error");

                                    },
                                    complete: function(data) {
                                        $('#dg').datagrid('reload');
                                        details(row.item_id, row.uom);
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

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('production/scrap_booking/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.id+"','"+row.uom+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function details(id, uom) {
        $("#dlg_history").dialog('open');
        $('#dg_history').datagrid('loading');

        $('#item_id').combogrid('setValue', id);
        $('#uom').textbox('setValue', uom);

        $.ajax({
            url: '<?= base_url('production/scrap_booking/datatableDetails') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(result) {
                $('#dg_history').datagrid('loadData', result).datagrid('loaded');
            }
        });
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('production/scrap_booking/datatables') ?>',
            pagination: true,
            clientPaging: true,
            // remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        $('#dg_history').datagrid({
            pagination: true,
            clientPaging: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var trans_date = $("#trans_date").datebox('getValue');
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + trans_date + "&menus_id=<?= $id_menu ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }
                            
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

                                    $('#dlg_insert').dialog('close');
                                    $('#dg').datagrid('reload');
                                    
                                    var item_id = $("#item_id").combogrid('getValue');
                                    var uom = $("#uom").textbox('getValue');

                                    details(item_id, uom);
                                }
                            });
                        }
                    });
                }
            }]
        });

        $('#item_id').combogrid({
            url: '<?= base_url('production/scrap_booking/readItems') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            columns: [
                [{
                    field: 'id',
                    title: 'Part No EBWS',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }, ]
            ],
            onSelect: function(value, rows) {
                $("#uom").textbox('setValue', rows.uom);
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

    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function statusformat(value, row) {
        if (parseFloat(row.qty_scrap) > parseFloat(row.qty_invoice) || parseFloat(row.qty_scrap) == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (parseFloat(row.qty_scrap) > parseFloat(row.qty_invoice) || parseFloat(row.qty_scrap) == 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };
</script>