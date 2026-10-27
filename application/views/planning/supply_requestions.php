<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:190">Kanban No</th>
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'">Kanban Date</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'">Requester</th>
            <!-- <th rowspan="2" data-options="field:'period',width:100,halign:'center'">Period</th> -->
            <!-- <th rowspan="2" data-options="field:'wp',width:50,halign:'center'">WP</th> -->
            <th rowspan="2" data-options="field:'workorder',width:120,halign:'center'">Workorder</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No EBWS</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Product No Customer</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa">Qty</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
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
                <input style="width:60%;" id="filter_period" class="easyui-combobox">
            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Request ID</span>
                <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_kanban()"><i class="fa fa-print"></i> Print Kanban</a>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 900px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>

            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Scrap No</span>
                    <input style="width:60%;" id="scrap_no" class="easyui-textbox">
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Rejection No</span>
                    <input style="width:60%;" id="rejection_no" class="easyui-textbox">
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="period" class="easyui-textbox">
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">WP</span>
                    <input style="width:60%;" id="wp" class="easyui-textbox">
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="item_fg_id" class="easyui-textbox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder No</span>
                    <input style="width:60%;" id="workorder" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Material Requestion" toolbar="#toolbar2" data-options="singleSelect: true">

        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/supply_requestions/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $("#request_date").datebox("setValue", "<?= date("Y-m-d") ?>");

        $('#workorder').combogrid({
            url: '<?= base_url('planning/scraps/readDocument') ?>',
            panelWidth: 550,
            idField: 'workorder',
            textField: 'workorder',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Workorder No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'workorder',
                    title: 'Workorder',
                    width: 150
                },{
                    field: 'rejection_no',
                    title: 'Rejection No',
                    width: 150
                },{
                    field: 'scrap_no',
                    title: 'Scrap No',
                    width: 150
                },{
                    field: 'number_customer',
                    title: 'Product No',
                    width: 200
                }]
            ],
            onSelect: function(val, row){
                $("#scrap_no").textbox('setValue', row.scrap_no);
                $("#rejection_no").textbox('setValue', row.rejection_no);
                $("#period").textbox('setValue', row.period);
                $("#wp").textbox('setValue', row.wp);
                $("#item_fg_id").textbox('setValue', row.item_fg_id);
            }
        });
    }

    function request_no(id_menu, trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/supply_requestions/request_no') ?>/" + id_menu + "/" + btoa(trans_date),
            dataType: "html",
            success: function(result) {
                if(result == ""){
                    toastr.info("Please Check Configuration in Serial No First");
                }else{
                    $("#request_no").textbox('setValue', result);
                }
            }
        });
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
                            url: '<?= base_url('planning/supply_requestions/delete') ?>',
                            data: {
                                id: row.id,
                                request_no: row.request_no,
                                item_rm_id: row.item_rm_id,
                                scrap_no: row.scrap_no,
                                workorder: row.workorder
                            },

                            success: function(result) {
                                var result = eval('(' + result + ')');
                                $('#dg').treegrid('reload');
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

    function preview() {
        var scrap_no = $("#scrap_no").textbox('getValue');
        var rejection_no = $("#rejection_no").textbox('getValue');

        if (scrap_no == "") {
            toastr.warning('Please Select Document', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg2').datagrid({
                url: '<?= base_url('planning/supply_requestions/datatablesTemp') ?>?scrap_no=' + scrap_no + "&rejection_no=" + rejection_no,
                singleSelect: false,
                idField: 'item_rm_id',
                columns: [[
                    {
                        field: 'ck',
                        checkbox: true,
                    },{
                        field: 'item_rm_id',
                        width: 120,
                        halign: 'center',
                        title: "Part No EBWS",
                        editor: {
                            type: 'textbox',
                            options: {
                                readonly: true
                            }
                        }
                    }, {
                        field: 'item_rm_number',
                        width: 200,
                        halign: 'center',
                        title: "Part No Supplier",
                    }, {
                        field: 'qty',
                        width: 80,
                        halign: 'center',
                        title: "Qty",
                        editor: {
                            type: 'numberbox',
                            options: {
                                required: true,
                                readonly: true,
                            }
                        }
                    }, {
                        field: 'uom',
                        width: 80,
                        halign: 'center',
                        title: "UoM",
                    }, {
                        field: 'description',
                        width: 200,
                        halign: 'center',
                        title: "Remarks",
                        editor: {
                            type: 'textbox',
                        }
                    }]
                ],
                onLoadSuccess: function(data){
                    if(data.total == 0){
                        toastr.info("Data Not Found or already created, please check data");
                    }
                },
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg2').datagrid('getEditors', rowIndex);
                },
                onEndEdit: function(index, row) {
                    var ed = $(this).datagrid('getEditor', {
                        index: index,
                        field: 'item_rm_id'
                    });
                },
                onBeforeEdit: function(index, row) {
                    row.editing = true;
                    $(this).datagrid('refreshRow', index);
                },
                onAfterEdit: function(index, row) {
                    row.editing = false;
                    $(this).datagrid('refreshRow', index);
                },
                onCancelEdit: function(index, row) {
                    row.editing = false;
                    $(this).datagrid('refreshRow', index);
                },
                // rowStyler:function(index, row){
                //     if (row.qty_req < 0){
                //         return 'background-color:#ffa07a;';
                //     }else if (row.qty_req == 0){
                //         return 'background-color:#ff1919; color:white;';
                //     }
                // }
            });
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg2').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                $('#dg2').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));
    }

    function cancelrow(target) {
        $('#dg2').datagrid('cancelEdit', getRowIndex(target));
    }

    function filter() {
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        url = "?filter_period=" + filter_period + "&filter_request_no=" + filter_request_no;

        $('#dg').treegrid({
            url: '<?= base_url('planning/supply_requestions/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/supply_requestions/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');

        url = "?filter_period=" + filter_period + "&filter_request_no=" + filter_request_no;

        window.location.assign('<?= base_url('planning/supply_requestions/print/excel') ?>' + url);
    }

    function print_kanban() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            window.open("<?= base_url('planning/supply_requestions/print_kanban/') ?>" + window.btoa(row.request_no));// + "/" + window.btoa(operation), "_blank"
        }else{
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').treegrid({
            url: '<?= base_url('planning/supply_requestions/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            singleSelect: false,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var period = $("#period").textbox('getValue');
                    var wp = $("#wp").textbox('getValue');
                    var workorder = $("#workorder").combogrid('getValue');
                    var rejection_no = $("#rejection_no").textbox('getValue');
                    var scrap_no = $("#scrap_no").textbox('getValue');
                    var item_fg_id = $("#item_fg_id").textbox('getValue');

                    if (workorder == "") {
                        toastr.error("please complete your input data");
                    } else {
                        $('#dg2').datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        // endEditing();
                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_rm_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/supply_requestions/create') ?>',
                                    data: {
                                        request_date: request_date,
                                        request_no: request_no,
                                        request_name: request_name,
                                        period: period,
                                        wp: wp,
                                        workorder: scrap_no,
                                        rejection_no: rejection_no,
                                        scrap_no: workorder,
                                        item_fg_id: item_fg_id,
                                        item_rm_id: rows[i].item_rm_id,
                                        qty: rows[i].qty
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "error") {
                                            toastr.warning(result.message, "Error");
                                        }
                                    }
                                });
                            }
                        }
                        Swal.fire({
                            title: "Data Saved Successfully",
                            icon: "success",
                            confirmButtonText: 'Ok',
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                        $('#dg').treegrid('reload');
                        $('#dlg_insert').dialog('close');
                    }
                }
            }]
        });

        $("#filter_period").combobox({
            url: '<?= base_url('planning/supply_requestions/readPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Choose period",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(period) {
                $("#filter_wp").combobox({
                    url: '<?= base_url('planning/supply_requestions/readWp/') ?>' + period.period,
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Choose WP",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(wp) {
                        $("#filter_request_no").combobox({
                            url: '<?= base_url('planning/supply_requestions/readRequestNo/') ?>' + period.period + '/' + window.btoa(wp.wp),
                            valueField: 'request_no',
                            textField: 'request_no',
                            prompt: "Choose WP",
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                        });
                    }
                });
            }
        });

        $("#request_date").datebox({
            onChange: function(val) {
                request_no("<?= $id_menu ?>", val);
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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatQpa(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
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
        return '<a style="text-decoration: none; font-weight:bold;" target="_blank" href="<?= base_url('planning/supply_requestions/print_label/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
    }

</script>