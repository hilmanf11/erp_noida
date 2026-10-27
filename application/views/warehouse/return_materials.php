<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'return_no',width:200,halign:'center'">Return No</th>
            <th rowspan="2" data-options="field:'return_date',width:100,halign:'center'">Return Date</th>
            <th rowspan="2" data-options="field:'return_name',width:150,halign:'center'">Return Name</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:150,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier </th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'workorder',width:150,align:'center'">Workorder</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'mpq',width:100,halign:'center',align:'right',formatter:numberformat">MPQ</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'state',width:100,halign:'center',align:'center',formatter:BtnPrintLabel">Label</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
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
<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Return No</span>
                <input style="width:60%;" id="filter_return_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_return()"><i class="fa fa-print"></i> Return No</a>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 900px; height: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Return No</span>
                    <input style="width:60%;" name="return_no" id="return_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Return Date</span>
                    <input style="width:60%;" name="return_date" id="return_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Return Name</span>
                    <input style="width:60%;" name="return_name" id="return_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="month" name="month" required class="easyui-combobox">
                    <input style="width:30%;" id="year" name="year" required class="easyui-combobox" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" name="workorder" id="workorder" required class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Return Material List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- UPDATE -->
<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" required class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No EBWS</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No Supplier</span>
                <input style="width:60%;" name="item_rm_number" id="item_rm_number" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Workorder</span>
                <input style="width:60%;" name="workorder" id="workorder" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" required="" class="easyui-numberbox" data-options="precision:2">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/return_materials/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $("#year").combobox("setValue", "<?= date("Y") ?>");
        $("#month").combobox("setValue", "<?= date("m") ?>");
        // addTable();

        $.ajax({
            url: "<?= base_url('warehouse/return_materials/return_no') ?>",
            success: function(return_no) {
                $("#return_no").textbox('setValue', return_no);
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if(row.state != "closed"){
                if (row.status == "0") {
                    $('#dlg_update').dialog('open');
                    $('#frm_update').form('load', row);
                } else {
                    toastr.error("You cannot update this data, because status Return is closed");
                }
            }else{
                toastr.warning("Please select detail of table first");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function addTable(workorder) {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            singleSelect: true,
            columns: [
                [{
                    field: 'item_rm_id',
                    width: 150,
                    halign: 'center',
                    title: "Part No EBWS",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('warehouse/return_materials/readItems/') ?>' + btoa(workorder),
                            required: true,
                            panelWidth: 650,
                            idField: 'id',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part No',
                            columns: [
                                [{
                                    field: 'id',
                                    title: 'Part No EBWS',
                                    width: 150
                                },{
                                    field: 'number',
                                    title: 'Part No Supplier',
                                    width: 200
                                },{
                                    field: 'name',
                                    title: 'Description',
                                    width: 200
                                },{
                                    field: 'mpq',
                                    title: 'MPQ',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_number'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'mpq'
                                });
                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.mpq);
                            }
                        }
                    }
                }, {
                    field: 'item_rm_number',
                    width: 250,
                    halign: 'center',
                    title: "Part No Supplier",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'mpq',
                    width: 80,
                    halign: 'center',
                    title: "MPQ",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 200,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);
            }
        });
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        if (endEditing()) {
            var workorder = $("#workorder").combobox("getValue");

            if(workorder != ""){
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });

                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }else{
                toastr.error("Choose Workorder First!");
            }
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }
    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/return_materials/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                readRequestno();
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                
                            },
                            complete: function(data) {
                                $('#dg').treegrid('reload');
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
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_return_no = $("#filter_return_no").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_return_no=" + filter_return_no;
        $('#dg').treegrid({
            url: '<?= base_url('warehouse/return_materials/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'return_no',
            singleSelect: false,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/return_materials/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_return_no = $("#filter_return_no").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_return_no=" + filter_return_no;
        window.location.assign('<?= base_url('warehouse/return_materials/print/excel') ?>' + url);
    }

    function print_return() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            window.open("<?= base_url('warehouse/return_materials/print_return/') ?>" + window.btoa(row.return_no));// + "/" + window.btoa(operation), "_blank"
        }else{
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }

    function readRequestno() {
        $("#filter_return_no").combobox({
            url: '<?= base_url('warehouse/return_materials/readRequestno') ?>',
            valueField: 'return_no',
            textField: 'return_no',
            prompt: "Select Return No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    function readWorkorder(month, year) {
        $("#workorder").combobox({
            url: '<?= base_url('planning/production_schedules/filterWo/') ?>' + month + "/" + year,
            valueField: 'workorder',
            textField: 'workorder',
            prompt: "Select Workorder",
            onSelect: function(wo){
                addTable(wo.workorder);
            }
        });
    }

    $(function() {
        filter();

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var return_no = $("#return_no").textbox('getValue');
                    var return_date = $("#return_date").datebox('getValue');
                    var return_name = $("#return_name").textbox('getValue');
                    var p_month = $("#month").combobox('getValue');
                    var p_year = $("#year").combobox('getValue');
                    var workorder = $("#workorder").combobox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].qty > 0) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('warehouse/return_materials/create') ?>',
                                data: {
                                    return_no: return_no,
                                    return_date: return_date,
                                    return_name: return_name,
                                    workorder: workorder,
                                    p_month: p_month,
                                    p_year: p_year,
                                    item_rm_id: rows[i].item_rm_id,
                                    qty: rows[i].qty,
                                    mpq: rows[i].mpq,
                                    remarks: rows[i].remarks
                                },
                                dataType: "json",
                                success: function(result) {
                                    Swal.fire({
                                        title: result.message,
                                        icon: result.theme,
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            });
                        }
                    }

                    readRequestno();
                    $('#dg').treegrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        //Update Data
        $('#dlg_update').dialog({
            buttons: [{
                text: 'Update Data',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
                        url: '<?= base_url('warehouse/return_materials/update') ?>',
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
                            $('#dlg_update').dialog('close');
                            $('#dg').treegrid('reload');
                        }
                    });
                }
            }]
        });

        $("#month").combobox({
            url: '<?= base_url('planning/production_schedules/readMonth') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Month",
            onSelect: function(month){
                var year = $("#year").combobox('getValue');
                readWorkorder(month.number, year);
            }
        });

        $("#year").combobox({
            url: '<?= base_url('planning/production_schedules/readYear') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Year",
            onSelect: function(year){
                var month = $("#month").combobox('getValue');
                readWorkorder(month, year.number);
            }
        });

        readRequestno();
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

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSE</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }

    function BtnPrintLabel(val, row) {
        if (val != "closed") {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('warehouse/return_materials/print_label/') ?>' + window.btoa(row.return_no) + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
        }
    }
</script>