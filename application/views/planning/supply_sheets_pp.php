<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'state',width:80,align:'center',formatter:BtnPrint">Print</th>
            <th rowspan="2" data-options="field:'wms',width:120,align:'center',formatter:BtnWms">WMS</th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:200" sortable="true">Kanban No</th>
            <th rowspan="2" data-options="field:'supply_type',width:80,align:'center',formatter:issuedformat,styler:statusIssued" sortable="true">Kanban<br>Type</th>
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'" sortable="true">Kanban Date</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'" sortable="true">Requester</th>
            <th rowspan="2" data-options="field:'period',width:100,halign:'center'" sortable="true">Period</th>
            <th rowspan="2" data-options="field:'wp',width:50,halign:'center'" sortable="true">WP</th>
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'" sortable="true">Work Order</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'" sortable="true">Product/Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'" sortable="true">Product/Part Name</th>
            <th rowspan="2" data-options="field:'main_process',width:100,halign:'center'" sortable="true">Main Process</th>
            <!-- <th rowspan="2" data-options="field:'sub_process',width:150,halign:'center'" sortable="true">Sub Process</th> -->
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'" sortable="true">UoM</th>
            <th rowspan="2" data-options="field:'composition',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">QPA</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">MPQ</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">WO Qty</th>
            <!-- <th rowspan="2" data-options="field:'qty_req',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">Req Qty</th> -->
            <th rowspan="2" data-options="field:'qty_act',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">Req Qty</th>
            <th rowspan="2" data-options="field:'qty_issued',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">Issued</th>
            <th rowspan="2" data-options="field:'qty_issued_bal',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">O/S Qty</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'" sortable="true"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'" sortable="true"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Kanban Type</span>
                    <input style="width:60%;" id="filter_supply_type" value="OPEN" class="easyui-combobox" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="filter_period" value="<?= date("Ym") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP</span>
                    <input style="width:60%;" id="filter_wp" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Kanban No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="formula()"><i class="fa fa-info"></i> Help</a>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1250px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Kanban Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" value="<?= date("Y-m-d") ?>" readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Kanban No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="period" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP</span>
                    <input style="width:60%;" id="wp" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Work Order ID</span>
                    <input style="width:60%;" name="workorder" id="workorder" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="item_family_id" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Process Main</span>
                    <input style="width:60%;" id="main_process" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="item_rm_id" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%; height: 440px;" title="Kanban Sheet List" data-options="rownumbers: true, singleSelect: false" idField="component_number">

        </table>
    </form>
</div>

<!-- INSERT LABEL -->
<div id="dlg_label" class="easyui-dialog" title="Create Data Label" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Create Label" class="easyui-panel" style="width:100%; height:300px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" style="width: 100%;" hidden></iframe>
<script>
    function formula() {
        Swal.fire({
            width: 600,
            html: `<div style="text-align:left;"><center><b style="font-size:16px !important;">RELATION</b><hr></center>
                    <ul>
                        <li>All of Data taken from <b>Production Schedules</b> if Precut = YES</li>
                        <li>Preview of the Data taken from breakdown <b>Bill of Material</b></li>
                        <li>This data is only for inputting the main process type = <b>PP</b></li>
                    </ul>
                    <center><hr><b style="font-size:16px !important;">CONDITION</b><hr></center>
                    <ul>
                        <li>If Status Type <b style="color: green">OPEN</b> then data new created in <b>Kanban Sheet</b></li>
                        <li>If Status Type <b style="color: red">CLOSED</b> then data has been created in <b>Issued Material</b></li>
                        <li>If data created or Req Qty = Actual Qty then you cannot input it again</li>
                        <li>If the data you input has several part numbers or Actual Qty < Req Qty, then you can input remainder for the WO.</li>
                    </ul>
                    <center><hr><b style="font-size:16px !important;">FORMULA</b><hr></center>
                    <ul>
                        <li>Req Qty = Qty (<b>Production Schedule</b>) x QPA (<b>Bill of Material</b>)</li>
                    </ul>
                </div>`,
        });
    }

    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        request_no("<?= $id_menu ?>");

        $("#period").combobox({
            url: '<?= base_url('planning/production_schedules/readPeriod/period') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Select Period",
            onSelect: function(rowPeriod) {
                $("#wp").combobox({
                    url: '<?= base_url('planning/production_schedules/readWp?period=') ?>' + window.btoa(rowPeriod.period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP",
                    onSelect: function(rowWP) {
                        $("#workorder").combobox({
                            url: '<?= base_url('planning/production_schedules/readWorkorder?period=') ?>' + window.btoa(rowPeriod.period) + '&wp=' + window.btoa(rowWP.wp),
                            valueField: 'workorder',
                            textField: 'workorder',
                            prompt: "Select Workorder",
                            formatter: function(row) {
                                return row.workorder + ' | ' + row.number_customer;
                            },
                            onSelect: function(rowWorkorder) {
                                $("#customer_id").textbox("setValue", rowWorkorder.customer_id);
                                $("#item_fg_id").textbox("setValue", rowWorkorder.item_fg_id);

                                $('#item_family_id').combobox({
                                    url: '<?php echo base_url('planning/supply_sheets_pp/readProdfam?item_fg_id='); ?>' + btoa(rowWorkorder.item_fg_id),
                                    valueField: 'id',
                                    textField: 'name',
                                    prompt: 'Select Product Family',
                                    icons: [{
                                        iconCls: 'icon-clear',
                                        handler: function(e) {
                                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                                        }
                                    }],
                                    onSelect: function(rowProdfam) {
                                        $('#item_rm_id').combogrid({
                                            url: '<?= base_url('planning/supply_sheets_pp/readItems?item_fg_id=') ?>' + btoa(rowWorkorder.item_fg_id) + "&item_family_id=" + btoa(rowProdfam.id),
                                            panelWidth: 550,
                                            idField: 'id',
                                            textField: 'number',
                                            mode: 'remote',
                                            fitColumns: true,
                                            prompt: "Select Part No",
                                            icons: [{
                                                iconCls: 'icon-clear',
                                                handler: function(e) {
                                                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                                                }
                                            }],
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
                                                }]
                                            ]
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    }

    function request_no(id_menu) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/supply_sheets_pp/request_no') ?>/" + id_menu,
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                } else {
                    $("#request_no").textbox('setValue', result);
                }
            }
        });
    }

    function preview() {
        var item_fg_id = $("#item_fg_id").textbox('getValue');
        var workorder = $("#workorder").textbox('getValue');
        var main_process = $("#main_process").combobox('getValue');
        var item_family_id = $("#item_family_id").combobox('getValue');
        var item_rm_id = $("#item_rm_id").combogrid('getValue');

        if (workorder == "" || item_fg_id == "") {
            toastr.warning('Please select Product No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: '<?= base_url('planning/supply_sheets_pp/datatablesTemp') ?>?workorder=' + workorder + '&item_fg_id=' + item_fg_id + "&main_process=" + main_process + "&item_rm_id=" + item_rm_id + "&item_family_id=" + item_family_id,
                singleSelect: false,
                idField: 'item_rm_id',
                columns: [
                    [{
                        field: 'ck',
                        checkbox: true,
                    }, {
                        field: 'item_rm_id',
                        width: 150,
                        // hidden: true,
                        halign: 'center',
                        title: "Part No EBWS",
                        editor: {
                            type: 'textbox',
                            options: {
                                readonly: true
                            }
                        }
                    }, {
                        field: 'item_rm_no',
                        width: 200,
                        halign: 'center',
                        title: "Part No Supplier",
                    }, {
                        field: 'item_rm_name',
                        width: 200,
                        halign: 'center',
                        title: "Description",
                    }, {
                        field: 'item_family_name',
                        width: 100,
                        halign: 'center',
                        title: "Product Family",
                    }, {
                        field: 'main_process',
                        width: 100,
                        halign: 'center',
                        title: "Main Process",
                    }, {
                        field: 'qpa',
                        width: 80,
                        halign: 'center',
                        title: "QPA",
                    }, {
                        field: 'calculate',
                        width: 80,
                        halign: 'center',
                        title: "Round MPQ",
                    }, {
                        field: 'mpq',
                        width: 80,
                        halign: 'center',
                        title: "MPQ",
                        editor: {
                            type: 'combogrid',
                            options: {
                                required: true
                            }
                        }
                    }, {
                        field: 'stock',
                        width: 80,
                        halign: 'center',
                        title: "Stock",
                    }, {
                        field: 'qty',
                        width: 80,
                        halign: 'center',
                        title: "WO Qty",
                    }, {
                        field: 'qty_req2',
                        width: 80,
                        halign: 'center',
                        title: "Need",
                    }, {
                        field: 'qty_req3',
                        width: 80,
                        halign: 'center',
                        title: "Req Qty",
                    }, {
                        field: 'qty_req',
                        hidden: true,
                        width: 80,
                        halign: 'center',
                        title: "Qty Need",
                    }, {
                        field: 'qty_act',
                        width: 100,
                        halign: 'center',
                        title: "Qty Issued",
                        editor: {
                            type: 'numberbox',
                            options: {
                                required: true,
                                precision: 2
                            }
                        }
                    }, {
                        field: 'uom',
                        width: 80,
                        halign: 'center',
                        title: "Uom",
                    }, {
                        field: 'action',
                        title: 'Action',
                        width: 120,
                        align: 'center',
                        formatter: function(value, row, index) {
                            if (row.editing) {
                                var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
                                var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
                                return s + c;
                            } else {
                                var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
                                var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';
                                return e + d;
                            }
                        }
                    }]
                ],
                onLoadSuccess: function(data) {
                    if (data.total == 0) {
                        toastr.info("Data Not Found or already Created, please change your data");
                    }
                },
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);

                    var item_fg_id = $(editors[0].target).textbox('getValue');
                    var qty_act = $(editors[2].target);

                    $(editors[2].target).numberbox({
                        onChange: function(val) {
                            if (row.calculate == "YES") {
                                toastr.error("Qty Issued Cannot Edit because Round to MPQ is 'YES'");
                                $(editors[2].target).numberbox('clear');
                            } else {
                                if (val > parseInt(row.qty_req)) {
                                    toastr.error("Qty Issued Cannot >  Qty Need, please Change the qty");
                                    $(editors[2].target).numberbox('clear');
                                }
                            }
                        }
                    });

                    // qty_act.numberbox({
                    //     onChange: function() {
                    //         var f_qty_act = qty_act.numberbox('getValue');

                    //         if(f_qty_act != 1){
                    //             var cost = (Math.ceil(f_qty_act / row.mpq) * row.mpq);
                    //             qty_act.numberbox('setValue', cost);
                    //         }
                    //     }
                    // });

                    $(editors[1].target).combogrid({
                        url: '<?= base_url('planning/supply_sheets_pp/readMPQ/') ?>' + btoa(item_fg_id),
                        panelWidth: 420,
                        idField: 'mpq',
                        textField: 'mpq',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Select MPQ",
                        onLoadSuccess: function(data) {
                            $(editors[1].target).combogrid('setValue', row.mpq);
                        },
                        columns: [
                            [{
                                field: 'name',
                                title: 'Supplier Name',
                                width: 250
                            }, {
                                field: 'mpq',
                                title: 'MPQ',
                                width: 100
                            }, ]
                        ],
                        onSelect: function(valMpq, rowMpq) {
                            qty_act.numberbox('setValue', rowMpq.mpq);
                        }
                    });
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
                rowStyler: function(index, row) {
                    if (row.qty_req < 0) {
                        return 'background-color:#ffa07a;';
                    } else if (parseInt(row.qty_req) == 0) {
                        return 'background-color:#ff1919; color:white;';
                    }
                }
            });

            // dg.datagrid('enableFilter');
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg_request').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                $('#dg_request').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg_request').datagrid('endEdit', getRowIndex(target));
    }

    function cancelrow(target) {
        $('#dg_request').datagrid('cancelEdit', getRowIndex(target));
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Delete Data',
                text: "Are you sure? You want to delete this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    requestDelete(rows.length, rows);

                    function requestDelete(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number - 1];
                            value = Math.floor((number / total) * 100);

                            if (row.status == 1) {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('planning/supply_sheets_pp/delete') ?>',
                                    data: {
                                        id: row.id,
                                        workorder: row.workorder,
                                        item_fg_id: row.item_fg_id,
                                        request_no: row.request_no,
                                        item_rm_id: row.item_rm_id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        requestDelete(total, json, number + 1, value);

                                        if (number == total) {
                                            $('#dg').treegrid('reload');
                                            Swal.close();
                                            Swal.fire(
                                                'Delete Completed',
                                                'Delete Data has been completed, You cannot restore data that has been deleted',
                                                'success'
                                            );
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        Swal.close();
                                        Swal.fire(
                                            'Delete Failed',
                                            'Cannot Process your Request to Delete',
                                            'error'
                                        );
                                    },
                                });
                            } else {
                                Swal.close();
                                toastr.info("Only Detail Data can be deleted");
                            }
                        }
                    }
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    function filter() {
        var filter_supply_type = $("#filter_supply_type").combobox('getValue');
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        url = "?filter_period=" + filter_period + "&filter_wp=" + filter_wp + "&filter_request_no=" + filter_request_no + "&filter_supply_type=" + filter_supply_type + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_item_rm_id=" + filter_item_rm_id;

        $('#dg').treegrid({
            url: '<?= base_url('planning/supply_sheets_pp/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        // $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        // $("#printout").attr('src', '<?= base_url('planning/supply_sheets_pp/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_supply_type = $("#filter_supply_type").combobox('getValue');
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        url = "?filter_period=" + filter_period + "&filter_wp=" + filter_wp + "&filter_request_no=" + filter_request_no + "&filter_supply_type=" + filter_supply_type + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_item_rm_id=" + filter_item_rm_id;

        window.location.assign('<?= base_url('planning/supply_sheets_pp/print/excel') ?>' + url);
    }

    function print_kanban(request_no) {
        window.open("<?= base_url('planning/supply_sheets_pp/print_kanban/') ?>" + window.btoa(request_no));
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var workorder = $("#workorder").textbox('getValue');
                    var item_fg_id = $("#item_fg_id").textbox('getValue');

                    if (request_no == "") {
                        toastr.error("Kanban No still empty!!");
                        return false;
                    }

                    // $('#dg_request').datagrid('acceptChanges');
                    // var rows = $('#dg_request').datagrid('getRows');
                    var rows = $('#dg_request').datagrid('getSelections');
                    var totalrows = rows.length;
                    if (totalrows > 0) {
                        $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                            if (r) {
                                $('#dlg_insert').dialog('close');
                                $("#dlg_label").dialog('open');

                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url('planning/supply_sheets_pp/request_no/' . $id_menu) ?>",
                                    dataType: "html",
                                    success: function(result) {
                                        if (result == "") {
                                            toastr.info("Please Check Configuration in Serial No First");
                                            return false;
                                        } else {
                                            var request_no = result;

                                            requestData(totalrows, rows);

                                            function requestData(total, rows, number = 1, value = 0, success = 1, failed = 1, status = 0) {
                                                var i = (number - 1);
                                                if (value < 100) {
                                                    value = Math.floor((number / total) * 100);
                                                    $('#p_upload').progressbar('setValue', value);
                                                    $('#p_start').html(number);
                                                    $('#p_finish').html(total);

                                                    if (parseInt(rows[i].qty_req) > 0) {
                                                        if (rows[i].qty_req != rows[i].qty_act) {
                                                            var status = 1;
                                                        }

                                                        $.ajax({
                                                            type: "post",
                                                            url: '<?= base_url('planning/supply_sheets_pp/create') ?>',
                                                            data: {
                                                                item_fg_id: item_fg_id,
                                                                item_rm_id: rows[i].item_rm_id,
                                                                workorder: workorder,
                                                                request_date: request_date,
                                                                request_no: request_no,
                                                                request_name: request_name,
                                                                qty_req: rows[i].qty_req,
                                                                qty_act: rows[i].qty_act,
                                                                mpq: rows[i].mpq,
                                                                qty_bal: (parseInt(rows[i].qty_req) - parseInt(rows[i].qty_act)),
                                                                type: "PP",
                                                                status: status
                                                            },
                                                            dataType: "json",
                                                            success: function(result) {
                                                                if (result.theme == "success") {
                                                                    if (value >= 100) {
                                                                        // Swal.close();
                                                                        $("#dlg_label").dialog('close');

                                                                        Swal.fire({
                                                                            title: result.message,
                                                                            icon: result.theme,
                                                                            confirmButtonText: 'Ok',
                                                                            allowOutsideClick: false,
                                                                        }).then((result) => {
                                                                            if (result.isConfirmed) {
                                                                                // $('#dg').treegrid('reload');
                                                                                window.location.reload();
                                                                            }
                                                                        });
                                                                    } else {
                                                                        requestData(total, rows, number + 1, value, success + 1, failed + 0, status);
                                                                    }
                                                                } else {
                                                                    if (value >= 100) {
                                                                        // Swal.close();
                                                                        $("#dlg_label").dialog('close');

                                                                        toastr.error(result.message);
                                                                        requestData(total, rows, number + 1, value, success + 1, failed + 0, status);
                                                                    } else {
                                                                        toastr.error(result.message);
                                                                        requestData(total, rows, number + 1, value, success + 1, failed + 0, status);
                                                                    }
                                                                }

                                                                $("#p_remarks").append(title + "<br>");
                                                            },
                                                        });
                                                    } else {
                                                        toastr.error("Cannot Create " + rows[i].item_rm_no + " Because The Qty Still Outstanding");
                                                        requestData(total, rows, number + 1, value, success + 1, failed + 0, status);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    } else {
                        toastr.warning("Please select one of the data in the table first!", "Information");
                    }
                }
            }]
        });

        $("#filter_supply_type").combobox({
            data: [{
                "text": "OPEN",
            }, {
                "text": "CLOSE",
            }],
            valueField: 'text',
            textField: 'text',
            prompt: "Select Kanban Type",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_period").combobox({
            url: '<?= base_url('planning/supply_sheets_pp/readPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Select Period",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(rowPeriod) {
                $("#filter_wp").combobox({
                    url: '<?= base_url('planning/supply_sheets_pp/readWp?period=') ?>' + window.btoa(rowPeriod.period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(rowWp) {
                        $("#filter_request_no").combobox({
                            url: '<?= base_url('planning/supply_sheets_pp/readRequestNo?period=') ?>' + window.btoa(rowPeriod.period) + "&wp=" + window.btoa(rowWp.wp),
                            valueField: 'request_no',
                            textField: 'request_no',
                            prompt: "Select Kanban No",
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

        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads'); ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No EBWS',
                    width: 150
                }, {
                    field: 'number_customer',
                    title: 'Product No Customer',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $('#filter_item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads'); ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part No",
            columns: [
                [{
                    field: 'id',
                    title: 'Part EBWS',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Part Supplier',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 150
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });
    });

    function BtnPrint(val, row) {
        if (val == "closed") {
            var send = "print_kanban('" + row.request_no + "')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="' + send + '"><i class="fa fa-print"></i> Print</a>';
        } else {
            return '-';
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

    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatQpa(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
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

    function BtnWms(val, row) {
        if (row.state == "closed") {
            if (row.request_date >= "2026-07-02") {
                var onClick = "sendWms('" + row.request_no + "')";
                return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="' + onClick + '"><i class="fa fa-database"></i> Send</a>';
            } else {
                return '-';
            }
        }
    }

    function sendWms(request_no) {
        $.messager.confirm('Confirm', 'Are you sure you want to send data to WMS?', function(r) {
            if (r) {
                Swal.fire({
                    title: 'Please Wait for Sending Data',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                var burl = "<?= base_url("api/wms/kanbanPP") ?>";

                $.ajax({
                    type: "post",
                    url: burl,
                    data: {
                        request_no: request_no
                    },
                    dataType: "json",
                    success: function(msg) {
                        if (msg.success) {
                            Swal.fire("", msg.message, "success");
                            // $.ajax({
                            //     type: "post",
                            //     url: '<?= base_url('api/wms/gateEntryClosing') ?>',
                            //     data: {receipt_no: receipt_no},
                            //     dataType: "json",
                            //     success: function (si) {
                            //         $('#dg').datagrid('reload');

                            //         Swal.fire(
                            //             "Info",
                            //             msg.message,
                            //             "success"
                            //         );
                            //     }
                            // });
                        } else {
                            Swal.fire("", msg.message, "error");
                        }
                    },
                    error: function() {
                        Swal.fire("", "Failed to Sending Data WMS", "error");
                    }
                });
            }
        });
    }
</script>