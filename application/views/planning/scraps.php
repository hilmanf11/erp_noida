<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <!-- <th rowspan="2" data-options="field:'type',width:80,align:'center',formatter:typeformat,styler:typeStyle">Type</th> -->
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'document',width:120,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:100,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:150,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'workorder',width:120,halign:'center'">Rejection No</th>
            <th rowspan="2" data-options="field:'source',width:100,halign:'center'">Rejection By</th>
            <th rowspan="2" data-options="field:'ng',width:100,halign:'center',align:'right',formatter:numberformat">Reject</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Accepted</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
            <th colspan="3" data-options="field:'',width:100,align:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:100,align:'center', styler:styleApproved, formatter:formatApproved"> Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'"> By</th>
            <th data-options="field:'approved_date',width:150,align:'center'"> Date</th>
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
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 40%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_family_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
                </div>
            </div>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Rejection By</span>
                    <select style="width:60%;" id="filter_source" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="User">User</option>
                        <option value="Vendor">Vendor</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="0">Open</option>
                        <option value="1">Close</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- <div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div> -->

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 900px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" required readonly data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" name="document" id="document" class="easyui-textbox" readonly required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:30%;" name="type" id="type" class="easyui-combobox" panelHeight="auto">
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Rejection No</span>
                    <input style="width:60%;" name="workorder" id="workorder" class="easyui-combogrid" required>
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Request List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Update -->
<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" class="easyui-numberbox" data-options="precision:2" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%; height: 50px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
            </div>
        </fieldset>
    </form>
</div>


<!-- UPLOAD DATA -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/scraps/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
    }

    function preview() {
        var trans_date = $("#trans_date").datebox('getValue');
        var workorder = $("#workorder").combogrid('getValue');
        var type = $("#type").combobox('getValue');
        var document = $("#document").textbox('getValue');

        if (trans_date == "" || workorder == "") {
            toastr.warning('Please select Product No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg2').datagrid({
                url: '<?= base_url('planning/scraps/datatablesTemp') ?>?workorder=' + workorder,
                singleSelect: false,
                idField: 'item_rm_id',
                columns: [
                    [{
                        field: 'ck',
                        checkbox: true,
                    }, {
                        field: 'action',
                        title: 'Action',
                        width: 100,
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
                    }, {
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
                        field: 'stock_wip',
                        width: 80,
                        halign: 'center',
                        title: "Wip",
                        editor: {
                            type: 'numberbox',
                            options: {
                                required: true,
                                readonly: true,
                            }
                        }
                    }, {
                        field: 'qty_reject',
                        width: 80,
                        halign: 'center',
                        title: "Reject",
                        editor: {
                            type: 'numberbox',
                            options: {
                                required: true,
                                readonly: true,
                            }
                        }
                    }, {
                        field: 'qty',
                        width: 80,
                        halign: 'center',
                        title: "Accepted",
                        editor: {
                            type: 'numberbox',
                            options: {
                                required: true,
                                precision: 2,
                                onChange: function(valQty) {
                                    var row = $('#dg2').datagrid('getSelected');
                                    var rowIndex = $('#dg2').datagrid('getRowIndex', row);

                                    var ed = $('#dg2').datagrid('getEditor', {
                                        index: rowIndex,
                                        field: 'stock'
                                    });

                                    var ed2 = $('#dg2').datagrid('getEditor', {
                                        index: rowIndex,
                                        field: 'qty'
                                    });

                                    var qty_ng = $(ed.target).numberbox('getValue');
                                    var qty = $(ed2.target).numberbox('getValue');

                                    if (parseInt(qty_ng) < parseInt(qty)) {
                                        toastr.error("Qty Reject Cannot < Qty Accepted");
                                        $(ed2.target).numberbox('clear');
                                    }
                                }
                            }
                        }
                    }, {
                        field: 'uom',
                        width: 80,
                        halign: 'center',
                        title: "UoM",
                    }, {
                        field: 'source',
                        width: 120,
                        halign: 'center',
                        title: "Source Reject",
                        editor: {
                            type: 'combobox',
                            options: {
                                valueField: 'name',
                                textField: 'name',
                                panelHeight: 'auto',
                                required: true,
                                data: [{
                                        name: "User"
                                    },
                                    {
                                        name: "Vendor"
                                    }
                                ],
                                prompt: "Choose Source Reject",
                            }
                        }
                    }, {
                        field: 'remarks',
                        width: 200,
                        halign: 'center',
                        title: "Remarks",
                        editor: {
                            type: 'textbox',
                        }
                    }]
                ],
                onLoadSuccess: function(data) {
                    if (data.total == 0) {
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

    // function addTable(type, workorder) {
    //     if (type == "IN") {
    //         var url = '<?= base_url('planning/item_ng/readItems/') ?>' + window.btoa(workorder);
    //     } else {
    //         var url = '<?= base_url('planning/scraps/readItem') ?>';
    //     }

    //     var lastIndex;
    //     var dg = $('#dg2').datagrid({
    //         singleSelect: true,
    //         columns: [
    //             [{
    //                 field: 'item_rm_id',
    //                 width: 250,
    //                 halign: 'center',
    //                 title: "Part No EBWS",
    //                 editor: {
    //                     type: 'combogrid',
    //                     options: {
    //                         url: url,
    //                         required: true,
    //                         panelWidth: 550,
    //                         idField: 'id',
    //                         textField: 'number',
    //                         mode: 'remote',
    //                         fitColumns: true,
    //                         prompt: 'Choose Product',
    //                         columns: [
    //                             [{
    //                                 field: 'id',
    //                                 title: 'Part No EBWS',
    //                                 width: 100
    //                             }, {
    //                                 field: 'number',
    //                                 title: 'Part No Supplier',
    //                                 width: 200
    //                             }, {
    //                                 field: 'name',
    //                                 title: 'Description',
    //                                 width: 200
    //                             }]
    //                         ],
    //                         onSelect: function(value, rows) {
    //                             var dg = $('#dg2');
    //                             var row = dg.datagrid('getSelected');
    //                             var rowIndex = dg.datagrid('getRowIndex', row);
    //                             var ed = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_rm_id'
    //                             });
    //                             var ed2 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_name'
    //                             });
    //                             var ed3 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'uom'
    //                             });
    //                             var ed4 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'qty'
    //                             });
    //                             $(ed.target).textbox('setValue', rows.id);
    //                             $(ed2.target).textbox('setValue', rows.name);
    //                             $(ed3.target).textbox('setValue', rows.uom);
    //                             $(ed4.target).numberbox('setValue', rows.qty);
    //                         }
    //                     }
    //                 }
    //             }, {
    //                 field: 'item_rm_number',
    //                 width: 200,
    //                 halign: 'center',
    //                 title: "Part No Supplier",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'uom',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "Uom",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'qty_ng',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "Reject",
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         required: true,
    //                         precision: 2
    //                     }
    //                 }
    //             }, {
    //                 field: 'qty',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "Scrap",
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         required: true,
    //                         precision: 2
    //                     }
    //                 }
    //             }, {
    //                 field: 'remarks',
    //                 width: 200,
    //                 halign: 'center',
    //                 title: "Remarks",
    //                 editor: {
    //                     type: 'textbox'
    //                 }
    //             }]
    //         ],
    //         onClickRow: function(rowIndex) {
    //             if (lastIndex != rowIndex) {
    //                 $(this).datagrid('endEdit', lastIndex);
    //                 $(this).datagrid('beginEdit', rowIndex);
    //             }
    //             lastIndex = rowIndex;
    //         },
    //         onBeginEdit: function(rowIndex, row) {
    //             var editors = $('#dg2').datagrid('getEditors', rowIndex);
    //         }
    //     });
    // }
    // var editIndex = undefined;

    // function endEditing() {
    //     if (editIndex == undefined) {
    //         return true
    //     }
    //     if ($('#dg2').datagrid('validateRow', editIndex)) {
    //         $('#dg2').datagrid('endEdit', editIndex);
    //         editIndex = undefined;
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    // function append() {
    //     var type = $("#type").combobox('getValue');
    //     var workorder = $("#workorder").combogrid('getValue');
    //     if (type != "" || workorder != "") {
    //         if (endEditing()) {
    //             $('#dg2').datagrid('appendRow', {
    //                 qty: '0'
    //             });
    //             editIndex = $('#dg2').datagrid('getRows').length - 1;
    //             $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
    //         }
    //     } else {
    //         toastr.error("Please Choose Type and Workorder first");
    //     }
    // }

    // function removeit() {
    //     if (editIndex == undefined) {
    //         return
    //     }
    //     $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
    //     editIndex = undefined;
    // }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.approved_to == "") {
                toastr.warning("Cannot edit this data, already approved");
            } else {
                $('#dlg_update').dialog('open');
                $('#frm_update').form('load', row);

                url_save = '<?= base_url('planning/scraps/update') ?>?id=' + btoa(row.id);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/scraps/delete') ?>',
                            data: {
                                id: row.id,
                                workorder: row.workorder,
                                item_rm_id: row.item_rm_id,
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

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_scraps.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_document = $("#filter_document").combobox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');
        var filter_source = $("#filter_source").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_document=" + filter_document +
            "&filter_family_id" + filter_family_id + "&filter_item_rm_id=" + filter_item_rm_id + "&filter_source=" + filter_source +
            "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('planning/scraps/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/scraps/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_document = $("#filter_document").combobox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');
        var filter_source = $("#filter_source").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_document=" + filter_document +
            "&filter_family_id" + filter_family_id + "&filter_item_rm_id=" + filter_item_rm_id + "&filter_source=" + filter_source +
            "&filter_status=" + filter_status;
        window.location.assign('<?= base_url('planning/scraps/print/excel') ?>' + url);
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
                    var trans_date = $("#trans_date").datebox('getValue');
                    var document = $("#document").textbox('getValue');
                    var type = $("#type").combobox('getValue');
                    var workorder = $("#workorder").combogrid('getValue');

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getSelections');
                    var totalrows = rows.length;
                    if (totalrows > 0) {
                        for (let z = 0; z < totalrows; z++) {
                            if (parseInt(rows[z].qty) > parseInt(rows[z].stock_wip)) {
                                toastr.error("qty accepted cannot be greater than qty WIP");
                                return false;
                            }
                        }

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('planning/scraps/scraps_no/' . $id_menu) ?>/" + btoa(trans_date),
                            dataType: "html",
                            success: function(scraps_no) {
                                if (scraps_no == "") {
                                    toastr.info("Please Check Configuration in Serial No First");
                                    return false;
                                } else {
                                    var document = scraps_no;
                                }

                                for (let i = 0; i < totalrows; i++) {
                                    if (rows[i].item_rm_id) {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('planning/scraps/create') ?>',
                                            data: {
                                                trans_date: trans_date,
                                                document: document,
                                                type: type,
                                                workorder: workorder,
                                                item_rm_id: rows[i].item_rm_id,
                                                qty: rows[i].qty,
                                                ng: rows[i].qty_reject,
                                                uom: rows[i].uom,
                                                source: rows[i].source,
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
                                $('#dlg_insert').dialog('close');
                            }
                        });
                    } else {
                        toastr.warning("Please Select data in the table first");
                    }
                }
            }]
        });

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Update',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
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
                            $('#dlg_update').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        //UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/scraps/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/scraps/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            $.messager.progress('close');
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/scraps/uploadclearFailed') ?>"
                            });
                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('planning/scraps/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('planning/scraps/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }
                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $("#trans_date").datebox({
            onChange: function(val) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('planning/scraps/scraps_no/' . $id_menu) ?>/" + btoa(val),
                    dataType: "html",
                    success: function(scraps_no) {
                        if (scraps_no == "") {
                            toastr.info("Please Check Configuration in Serial No First");
                            $('#document').textbox('clear');
                        } else {
                            $("#document").textbox('setValue', scraps_no);
                        }
                    }
                });
            }
        });

        $('#workorder').combogrid({
            url: '<?= base_url('planning/item_ng/readItemNg') ?>',
            panelWidth: 350,
            idField: 'document',
            textField: 'document',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Rejection No",
            columns: [
                [{
                    field: 'document',
                    title: 'Document',
                    width: 150
                }, {
                    field: 'workorder',
                    title: 'Workorder',
                    width: 150,
                    align: 'center'
                }]
            ],
            onSelect: function(val, row) {
                var type = $("#type").combobox('getValue');
                if (type == "") {
                    toastr.warning("Type cannot empty!");
                    $('#workorder').combogrid('clear');
                }
            }
        });

        $("#filter_document").combobox({
            url: '<?= base_url('planning/scraps/readDocument') ?>',
            valueField: 'document',
            textField: 'document',
            prompt: "Choose Document",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        //Get Product Family
        $("#filter_family_id").combobox({
            url: '<?= base_url('master/item_familys/readNotFg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $('#filter_item_rm_id').combogrid({
                    url: '<?= base_url('master/item_rm/reads/') ?>' + row.number,
                    panelWidth: 550,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Product No",
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
                    ],
                });
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
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
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

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };
</script>