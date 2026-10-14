<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:180,halign:'center'">Sample No.</th>
            <!-- <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th> -->
            <th rowspan="2" data-options="field:'item_rm_number',width:150,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:150,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'item_category_name',width:200,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'item_family_name',width:200,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatDefault">Qty</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'amount',width:120,halign:'center',align:'right',formatter:numberformatDefault">Amount</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:120,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 230px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 35%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="print_sample_slip()"><i class="fa fa-print"></i> Print Sample Slip</a>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add Data" data-options="closed: true,modal:true" style="width: 90%; height: 550px; padding:10px; top: 10px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 35%; float: left;">
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Number</span>
                    <input style="width:60%;" name="number" id="number" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Category</span>
                    <input style="width:60%;" name="item_category_id" id="item_category_id" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:25%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="item_family_id" id="item_family_id" class="easyui-combobox">
                </div>
            </div>
        </fieldset>

        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Item List" toolbar="#toolbar2" data-options="rownumbers: true">
        </table>
    </form>
</div>

<!-- Update -->
<div id="dlg_update" class="easyui-dialog" title="Update Sample Request" data-options="closed: true, modal:true" style="width: 500px; height: 550px; padding:10px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sample No.</span>
                <input style="width:60%;" name="number" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No EBWS</span>
                <input style="width:60%;" name="item_rm_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No Supplier</span>
                <input style="width:60%;" name="item_rm_number" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:40%;" name="qty" id="qty" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Currency</span>
                <input style="width:40%;" name="currency" id="currency" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Price</span>
                <input style="width:40%;" name="price" id="price" class="easyui-numberbox" data-options="precision: 4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Amount</span>
                <input style="width:40%;" name="amount" id="amount" readonly class="easyui-numberbox" data-options="precision: 4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" class="easyui-textbox">
            </div>
        </fieldset>
    </form>

    <!-- <div class="alert alert-info" role="alert">
        If you change the data then approval will start from the beginning
    </div> -->
</div>

<!-- Upload -->
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
<iframe id="printout" src="<?= base_url('purchase/sample_requests/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $("#number").textbox('clear');

        $.ajax({
            type: "post",
            url: "<?= base_url("purchase/sample_requests/autonumber/" . $id_menu . "/add") ?>",
            dataType: "json",
            success: function(response) {
                $("#number").textbox('setValue', response.code);
            }
        });

        $("#item_category_id").combobox({
            url: '<?php echo base_url('master/item_categories/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Category',
            onSelect: function(category) {
                var item_family_id = $("#item_family_id").combobox('getValue');
                addTable(category.id, item_family_id);
            }
        });

        $("#item_family_id").combobox({
            url: '<?php echo base_url('master/item_familys/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Family',
            onSelect: function(family) {
                var item_category_id = $("#item_category_id").combobox('getValue');
                addTable(item_category_id, family.id);
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.datatable == "1") {
                toastr.error("Please Select Details of Sample No. <br>" + row.number);
            } else {
                $('#dlg_update').dialog('open');
                $('#frm_update').form('load', row);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function addTable(category_id, family_id, link = "") {
        var lastIndex;
        $('#dg_request').datagrid({
            singleSelect: true,
            columns: [
                [{
                    field: 'delete',
                    width: 120,
                    readonly: true,
                    halign: 'center',
                    title: "Action",
                    formatter: removebtn
                }, {
                    field: 'item_rm_id',
                    width: 150,
                    halign: 'center',
                    title: "Part No EBWS",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('purchase/sample_requests/readItems') ?>?item_family_id=' + family_id + '&item_category_id=' + category_id,
                            required: true,
                            panelWidth: 550,
                            idField: 'item_rm_id',
                            textField: 'item_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part',
                            columns: [
                                [{
                                    field: 'item_rm_id',
                                    title: 'Part No EBWS',
                                    width: 100
                                }, {
                                    field: 'item_number',
                                    title: 'Part No Supplier',
                                    width: 200
                                }, {
                                    field: 'item_name',
                                    title: 'Description',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg_request');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_number'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_name'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });

                                // var ed4 = dg.datagrid('getEditor', {
                                //     index: rowIndex,
                                //     field: 'total'
                                // });

                                $(ed.target).textbox('setValue', rows.item_number);
                                $(ed2.target).textbox('setValue', rows.item_name);
                                $(ed3.target).textbox('setValue', rows.uom);
                                // $(ed4.target).numberbox('setValue', 0);
                            }
                        }
                    }
                }, {
                    field: 'item_rm_number',
                    width: 200,
                    readonly: true,
                    halign: 'center',
                    title: "Part No Supplier",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_rm_name',
                    width: 200,
                    readonly: true,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    readonly: true,
                    halign: 'center',
                    title: "UOM",
                    editor: {
                        type: 'textbox',
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
                            required: true,
                            precision: 0,
                            onChange: function(value) {
                                var dg = $('#dg_request');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'amount'
                                });

                                var price = $(ed.target).numberbox('getValue');
                                $(ed2.target).textbox('setValue', (parseFloat(value) * parseFloat(price)));
                            }
                        }
                    }
                }, {
                    field: 'price',
                    width: 100,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 4,
                            onChange: function(value) {
                                var dg = $('#dg_request');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'amount'
                                });

                                var qty = $(ed.target).numberbox('getValue');
                                $(ed2.target).textbox('setValue', (parseFloat(qty) * parseFloat(value)));
                            }
                        }
                    },
                    formatter: numberformat
                }, {
                    field: 'currency',
                    width: 100,
                    halign: 'center',
                    align: 'center',
                    title: "Currency",
                    editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/currencies/reads'); ?>',
                            required: true,
                            valueField: 'number',
                            textField: 'name',
                            prompt: 'Choose Currency',
                        }
                    },
                    formatter: function(value, row) {
                        return row.currency;
                    }
                }, {
                    field: 'amount',
                    width: 100,
                    halign: 'center',
                    title: "Amount",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                            precision: 2
                        }
                    },
                    formatter: numberformatDefault
                }, {
                    field: 'remarks',
                    width: 150,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
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
            // onClickRow: function(rowIndex) {
            //     $(this).datagrid('beginEdit', rowIndex);
            // },
            // onBeginEdit: function(rowIndex, row) {
            //     var editors = $('#dg_request').datagrid('getEditors', rowIndex);
            //     var delivery_date = $(editors[6].target);
            //     var po_date = $("#po_date").datebox('getValue');

            //     delivery_date.add(delivery_date).datebox({
            //         onChange: function() {
            //             var f_delivery_date = delivery_date.datebox('getValue');
            //             if (f_delivery_date < po_date) {
            //                 delivery_date.datebox('clear');
            //                 toastr.warning("Po Date > Expected Date");
            //             }
            //         }
            //     });

            //     delivery_date.datebox('setValue', row.delivery_date);
            // }
        });
    }

    function removebtn(value, row, index) {
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

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg_request').datagrid('validateRow', editIndex)) {
            var ed = $('#dg2').datagrid('getEditor', {
                index: editIndex,
                field: 'currency'
            });
            if (ed) {
                var text = $(ed.target).combobox('getText'); // get combobox text
                var row = $('#dg2').datagrid('getRows')[editIndex];
                row.currency = text; // update field value
            }
            $('#dg_request').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        if (endEditing()) {

            $('#dg_request').datagrid('appendRow', {
                "action": 0,
                "qty": 0,
                "price": 0,
                "amount": 0,
                "currency": 'INR',
            });

            editIndex = $('#dg_request').datagrid('getRows').length - 1;
            $('#dg_request').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg_request').datagrid('selectRow', getRowIndex(target));
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
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of Sample No. <br>" + row.number);
                        } else {
                            if (row.status == "0") {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('purchase/sample_requests/delete') ?>',
                                    data: {
                                        id: row.id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        $('#dg').treegrid('reload');
                                    }
                                });
                            } else {
                                toastr.error("You cannot update this data, because status Sample No. is closed");
                            }
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_purchase_sample_requests.xls') ?>');
    }

    function filter() {
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        url = "?filter_category=" + filter_category + "&filter_product_family=" + filter_product_family + "&filter_part_no=" + filter_part_no;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/sample_requests/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'number',
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
        $("#printout").attr('src', '<?= base_url('purchase/sample_requests/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        url = "?filter_category=" + filter_category + "&filter_product_family=" + filter_product_family + "&filter_part_no=" + filter_part_no;

        window.location.assign('<?= base_url('purchase/sample_requests/print/excel') ?>' + url);
    }

    function print_sample_slip() {
        var rows = $('#dg').treegrid('getSelected');
        if (rows) {
            var filter_category = $("#filter_category").combobox('getValue');
            var filter_product_family = $("#filter_product_family").combobox('getValue');

            window.open("<?= base_url('purchase/sample_requests/print_sample_slip/') ?>" + window.btoa(rows.number) + '?filter_category=' + window.btoa(filter_category) + '&filter_product_family=' + window.btoa(filter_product_family), "_blank");
        } else {
            toastr.warning("Please select data in the table first!");
        }
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
                    var number = $("#number").textbox('getValue');
                    var item_category_id = $("#item_category_id").combobox('getValue');
                    var item_family_id = $("#item_family_id").combobox('getValue');

                    if (number == "") {
                        toastr.warning('Please Complete Input', 'Required');
                    } else {
                        $('#dg_request').datagrid('acceptChanges');
                        var rows = $('#dg_request').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();

                        if (totalrows > 0) {
                            $.messager.confirm('Warning', 'Are you sure you want to Save this data?', function(r) {
                                if (r) {
                                    for (var i = 0; i < totalrows; i++) {
                                        var row = rows[i];
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('purchase/sample_requests/create') ?>',
                                            data: {
                                                number: number,
                                                item_rm_id: row.item_rm_id,
                                                qty: row.qty,
                                                price: row.price,
                                                currency: row.currency,
                                                amount: row.amount,
                                                remarks: row.remarks,
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

                                    $('#dg').treegrid('reload');
                                    $('#dlg_insert').dialog('close');
                                }
                            });
                        } else {
                            toastr.warning("Please select one of the data in the table first!", "Information");
                        }
                    }
                }
            }]
        });

        //Update Data
        $('#dlg_update').dialog({
            buttons: [{
                // text: 'Send to Approval',
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
                        url: '<?= base_url('purchase/sample_requests/update') ?>',
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
                        }
                    });
                }
            }]
        });

        $("#qty").numberbox({
            onChange: function(val) {
                var price = $("#price").numberbox('getValue');
                $("#amount").numberbox('setValue', (price * val));
            }
        });

        $("#price").numberbox({
            onChange: function(val) {
                var qty = $("#qty").numberbox('getValue');
                $("#amount").numberbox('setValue', (qty * val));
            }
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('purchase/sample_requests/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('purchase/sample_requests/upload') ?>',
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
                                url: "<?= base_url('purchase/sample_requests/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('purchase/sample_requests/uploadCreate') ?>",
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
                                                    url: "<?= base_url('purchase/sample_requests/uploadcreateFailed') ?>",
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
    });


    $("#filter_category").combobox({
        url: '<?php echo base_url('master/item_categories/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Select Category',
        onSelect: function(category) {
            // 
        }
    });

    $('#filter_product_family').combobox({
        url: '<?php echo base_url('master/item_familys/readss/C01'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Select Product Family',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(row) {
            $('#filter_part_no').combogrid({
                url: '<?= base_url('master/item_rm/reads/') ?>' + row.id,
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

    $("#currency").combobox({
        url: '<?php echo base_url('master/currencies/reads'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Select Currency',
        onSelect: function(category) {
            // 
        }
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
    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg_request').datagrid('validateRow', editIndex)) {
            $('#dg_request').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function numberformatDefault(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat("ja-JP", {
            minimumFractionDigits: 4
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

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return '';
        } else if (row.approved_to == "") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == null) {
            return '';
        } else if (row.approved_to == "") {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };
</script>