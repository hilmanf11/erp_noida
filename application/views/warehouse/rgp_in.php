<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <!-- <th rowspan="2" data-options="field:'item_fg_id',width:150,align:'center'">Product ID</th> -->
            <!-- <th rowspan="2" data-options="field:'print_status',width:80,align:'center',formatter:BtnPrint">Print</th> -->
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusFormat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'process_type',width:100,halign:'center',align:'center'">Process<br>Type</th>
            <th rowspan="2" data-options="field:'number',width:200,halign:'center'">EBWS Document No</th>
            <th rowspan="2" data-options="field:'transaction_date',width:120,halign:'center'">Transaction<br>Date</th>
            <th rowspan="2" data-options="field:'company_id',width:120,halign:'center'">Company ID</th>
            <th rowspan="2" data-options="field:'company_name',width:200,halign:'center'">Company Name</th>
            <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'item_category_name',width:150,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'departement_name',width:150,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'reference_number',width:150,halign:'center'">Reference</th>
            <th rowspan="2" data-options="field:'rgp_number',width:200,halign:'center'">RGP Out No.</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 270px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Type</span>
                    <select style="width:60%;" id="filter_process_type" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="">ALL</option>
                        <option value="RGP">RGP IN</option>
                        <option value="NRGP">RGP OUT TO NRGP IN</option>
                        <option value="FOC">FOC IN</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">EBWS Document No.</span>
                    <input style="width:60%;" id="filter_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Company Name</span>
                    <input style="width:60%;" id="filter_company" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" id="filter_plant" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement</span>
                    <input style="width:60%;" id="filter_departement" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <input style="width:60%;" id="filter_rgp_type" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 98%; height: 600px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <span style="width:35%; display:inline-block;">Process Type</span>
                <input style="width:60%;" name="process_type" id="process_type" class="easyui-combobox" required>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:60%;" number="transaction_date" id="transaction_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">EBWS Document No</span>
                    <input style="width:60%;" name="number" id="number" readonly class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Company Name</span>
                    <input style="width:60%;" name="company_id" id="company_id" class="easyui-combogrid" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="plant" id="plant" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" name="item_category_id" id="item_category_id" class="easyui-combobox" required>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement</span>
                    <input style="width:60%;" name="departement_id" id="departement_id" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <input style="width:60%;" name="rgp_type_id" id="rgp_type_id" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Reference Number</span>
                    <input style="width:60%;" name="reference_number" id="reference_number" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">RGP/NRGP Out No.</span>
                    <input style="width:60%;" name="rgp_number" id="rgp_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Note</span>
                    <input style="width:60%;" name="notes" id="notes" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Item Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Upload -->
<!-- <div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
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
</div> -->

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
<iframe id="printout" src="<?= base_url('warehouse/rgp_in/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA

    var action = 'add';

    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('warehouse/rgp_in/create') ?>';
        $('#frm_insert').form('clear');

        $('#reference_number').textbox('setValue', '-');
        $('#process_type').combobox({
            url: '<?= base_url('warehouse/rgp_in/readProcessType'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Type',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(val) {
                if (val.id == 'FOC') {
                    autonumberFoc();
                } else if (val.id == 'NRGP') {
                    autonumberNrgp();
                } else {
                    $("#transaction_date").datebox({
                        onChange: function(val) {
                            autonumber("<?= $id_menu ?>", val);
                        }
                    })
                }

                $('#transaction_date').datebox('setValue', "<?= date("Y-m-d") ?>");

                var item_category_id = $('#item_category_id').combobox('getValue');
                var company_id = $('#company_id').combogrid('getValue');
                $('#rgp_number').combobox({
                    url: '<?= base_url('warehouse/rgp_in/readDocumentOut?process_type='); ?>' + val.id + '&company_id=' + company_id + '&item_category_id=' + item_category_id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: 'Choose RGP Out',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(rgp) {
                        var number = $("#number").textbox('getValue');
                        var process_type = $('#process_type').combobox('getValue');
                        var item_category_id = $('#item_category_id').combobox('getValue');
                        var company_id = $('#company_id').combogrid('getValue');
                        addTable(process_type, item_category_id, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp.number) + '&action=' + action);
                    }
                });
                var number = $("#number").textbox('getValue');
                var rgp_number = $("#rgp_number").combobox('getValue');
                var item_category_id = $('#item_category_id').combobox('getValue');
                var company_id = $('#company_id').combogrid('getValue');

                $('#dg2').datagrid('loadData', []);
                addTable(val.id, item_category_id, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp_number) + '&action=' + action);
            }
        });

        $('#item_category_id').combobox({
            url: '<?= base_url('warehouse/rgp_in/readItemCategories'); ?>',
            valueField: 'id',
            textField: 'number',
            prompt: 'Choose RGP Out',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(category) {
                var number = $("#number").textbox('getValue');
                var process_type = $('#process_type').combobox('getValue');
                var rgp_number = $("#rgp_number").combobox('getValue');
                var company_id = $('#company_id').combogrid('getValue');

                $('#dg2').datagrid('loadData', []);
                addTable(process_type, category.id, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp_number) + '&action=' + action);

                $('#rgp_number').combobox({
                    url: '<?= base_url('warehouse/rgp_in/readDocumentOut?process_type='); ?>' + process_type + '&company_id=' + company_id + '&item_category_id=' + category.id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: 'Choose RGP Out',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(rgp) {
                        var number = $("#number").textbox('getValue');
                        var process_type = $('#process_type').combobox('getValue');
                        var company_id = $('#company_id').combogrid('getValue');
                        addTable(process_type, category.number, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp.number) + '&action=' + action);
                    }
                });
            }
        });

    }

    function autonumber(id_menu, trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/rgp_in/autonumber') ?>/" + id_menu + "/" + btoa(trans_date),
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

    function autonumberFoc() {
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/rgp_in/autonumberfoc') ?>",
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

    function autonumberNrgp() {
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/rgp_in/autonumbernrgp') ?>",
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

    function addTable(process_type = "", item_category_id = "", $company_id = "", link = "") {
        if (process_type == 'FOC' || process_type == 'RGP') {
            var readonly = false;
        } else {
            var readonly = true;
        }
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 200,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: false
                        }
                    },
                    hidden: true
                }, {
                    field: 'item_id',
                    width: 160,
                    halign: 'center',
                    title: "Part No EBWS",
                    editor: {
                        type: 'combogrid',
                        options: {
                            readonly: readonly,
                            url: '<?= base_url('warehouse/rgp_out/readItemCategory/'); ?>' + item_category_id + "/" + window.btoa($company_id),
                            required: true,
                            panelWidth: 600,
                            idField: 'id',
                            textField: 'id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part No EBWS',
                            columns: [
                                [{
                                    field: 'id',
                                    title: 'Part No EBWS',
                                    width: 150
                                }, {
                                    field: 'number',
                                    title: 'Part No Supplier',
                                    width: 150
                                }, {
                                    field: 'name',
                                    title: 'Description',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_number'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_family_name'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed7 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'hsn_code'
                                });

                                $(ed.target).textbox('setValue', rows.name);
                                $(ed3.target).textbox('setValue', rows.number);
                                $(ed4.target).textbox('setValue', rows.item_family_name);
                                $(ed5.target).textbox('setValue', rows.uom);
                                $(ed7.target).textbox('setValue', rows.hsn_code);
                            }
                        }
                    }
                }, {
                    field: 'item_number',
                    width: 150,
                    halign: 'center',
                    title: "Part No Supplier",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: readonly
                        }
                    }
                }, {
                    field: 'item_name',
                    width: 150,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: readonly
                        }
                    }
                }, {
                    field: 'item_family_name',
                    width: 100,
                    halign: 'center',
                    title: "Product Family",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: readonly
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: readonly
                        }
                    }
                }, {
                    field: 'qty',
                    width: 100,
                    align: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            // readonly: readonly,
                            onChange: function() {
                                summaryFormula();
                            }
                        }
                    }
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
                            readonly: readonly
                        }
                    },
                    formatter: function(value, row) {
                        return row.currency;
                    }
                }, {
                    field: 'hsn_code',
                    width: 100,
                    halign: 'center',
                    title: "HSN Code",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'price',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            required: true,
                            readonly: readonly,
                            onChange: function() {
                                summaryFormula();
                            }
                        }
                    }
                }, {
                    field: 'value',
                    width: 100,
                    align: 'center',
                    title: "Value",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            readonly: readonly
                        }
                    }
                }, {
                    field: 'igst_value',
                    width: 100,
                    align: 'center',
                    title: "IGST",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            readonly: readonly
                        }
                    }
                }, {
                    field: 'sgst_value',
                    width: 100,
                    align: 'center',
                    title: "SGST",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            readonly: readonly
                        }
                    }
                }, {
                    field: 'cgst_value',
                    width: 100,
                    align: 'center',
                    title: "CGST",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            readonly: readonly
                        }
                    }
                }, {
                    field: 'total',
                    width: 100,
                    align: 'center',
                    title: "Total",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            readonly: readonly
                        }
                    }
                }, ]
            ],
            onClickCell: onClickCell
        });
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            var ed = $('#dg2').datagrid('getEditor', {
                index: editIndex,
                field: 'currency'
            });
            if (ed) {
                var text = $(ed.target).combobox('getText'); // get combobox text
                var row = $('#dg2').datagrid('getRows')[editIndex];
                row.currency = text; // update field value
            }
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCell(index, field) {
        // var process_type = $("#process_type").combobox('getValue');
        // if (process_type != 'FOC' || process_type == 'RGP') {
        //     // alert('tidak bisa edit');
        // } else {
        if (editIndex != index) {
            if (endEditing()) {
                $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    $('#dg2').datagrid('selectRow', editIndex);
                }, 0);
            }
        }
        // }
    }

    function append() {
        var process_type = $("#process_type").combobox('getValue');
        var company_id = $("#company_id").combogrid('getValue');
        var item_category_id = $("#item_category_id").combobox('getValue');
        var departement_id = $("#departement_id").combobox('getValue');
        var rgp_type_id = $("#rgp_type_id").combobox('getValue');
        var reference_number = $("#reference_number").textbox('getValue');
        var rgp_number = $("#rgp_number").combobox('getValue');

        if (process_type == 'NRGP') {
            toastr.error("Can't add or remove for process type RGP/NRGP");
        } else {
            if (process_type != "" && company_id != "" && item_category_id != "" && departement_id != "" && rgp_type_id != "" && reference_number != "") {
                if (endEditing()) {
                    $('#dg2').datagrid('appendRow', {
                        currency: 'INR',
                        // qty: '0',
                    });
                    editIndex = $('#dg2').datagrid('getRows').length - 1;
                    $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
                }
            } else {
                toastr.error("Please Fill Require Field");
            }

        }
    }

    function removeit() {
        var process_type = $("#process_type").combobox('getValue');

        // if (process_type == 'NRGP') {
        //     toastr.error("Can't add or remove for process type RGP/NRGP");
        // } else {
        if(action == "add"){
            if (editIndex == undefined) {
                return true;
            }

            var dg = $('#dg2');
            var ed = dg.datagrid('getEditor', {
                index: editIndex,
                field: 'item_id'
            });

            var number = $("#number").textbox('getValue');
            var item_id = $(ed.target).textbox('getValue');

            $.ajax({
                method: 'post',
                url: '<?= base_url('warehouse/rgp_in/delete') ?>',
                data: {
                    number: number,
                    item_id: item_id
                },
                success: function(result) {
                    var result = eval('(' + result + ')');
                    toastr.success(result.message);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    toastr.error("This data is already used in other modules, You must delete it First", "Error");

                },
                complete: function(data) {
                    $('#dg').datagrid('reload');
                }
            });

            $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
            editIndex = undefined;
        }else{
            toastr.error("Can't remove this data");
        }
    }

    function summaryFormula() {
        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: rowIndex,
            field: 'item_id'
        });

        var ed2 = dg.datagrid('getEditor', {
            index: rowIndex,
            field: 'qty'
        });

        var ed3 = dg.datagrid('getEditor', {
            index: rowIndex,
            field: 'price'
        });

        var ed4 = dg.datagrid('getEditor', {
            index: rowIndex,
            field: 'value'
        });

        var ed5 = dg.datagrid('getEditor', {
            index: rowIndex,
            field: 'igst_value'
        });

        var ed6 = dg.datagrid('getEditor', {
            index: rowIndex,
            field: 'sgst_value'
        });

        var ed7 = dg.datagrid('getEditor', {
            index: rowIndex,
            field: 'cgst_value'
        });

        var ed8 = dg.datagrid('getEditor', {
            index: rowIndex,
            field: 'total'
        });

        var qty = $(ed2.target).numberbox('getValue');
        var price = $(ed3.target).numberbox('getValue');


        var item_id = $(ed.target).combobox('getValue');
        var company_id = $('#company_id').combobox('getValue');
        var plant = $('#plant').combobox('getValue');
        var rgp_number = $('#rgp_number').combobox('getValue');

        if (rgp_number != "") {
            $.ajax({
                type: "post",
                url: '<?= base_url('warehouse/rgp_in/readStockRgp') ?>',
                data: {
                    rgp_number: rgp_number,
                    item_id: item_id
                },
                dataType: "json",
                success: function(result) {
                    if (parseInt(qty) > parseInt(result.balance)) {
                        toastr.warning("Qty must be less than or equal to balance (" + result.balance + ")");
                    } else {
                        var value = qty * price;
                        $(ed4.target).numberbox('setValue', value);
                        $.ajax({
                            type: "post",
                            url: '<?= base_url('warehouse/rgp_in/readIgst') ?>',
                            data: {
                                item_id: item_id,
                                company_id: company_id,
                                plant: plant,
                            },
                            dataType: "json",
                            success: function(result) {
                                var igst_value = (value * result.igst) / 100;
                                var sgst_value = (value * result.sgst) / 100;
                                var cgst_value = (value * result.cgst) / 100;
                                var total_value = value + igst_value + sgst_value + cgst_value;
                                $(ed5.target).numberbox('setValue', igst_value);
                                $(ed6.target).numberbox('setValue', sgst_value);
                                $(ed7.target).numberbox('setValue', cgst_value);
                                $(ed8.target).numberbox('setValue', total_value);

                                if (result.state == null) {
                                    toastr.warning("Please update state in customer or suppliers data", "Information");
                                }
                            }
                        });
                    }
                }
            });
        } else {
            var value = qty * price;
            $(ed4.target).numberbox('setValue', value);

            var item_id = $(ed.target).combobox('getValue');
            var company_id = $('#company_id').combogrid('getValue');
            var plant = $('#plant').combobox('getValue');
            $.ajax({
                type: "post",
                url: '<?= base_url('warehouse/rgp_in/readIgst') ?>',
                data: {
                    item_id: item_id,
                    company_id: company_id,
                    plant: plant,
                },
                dataType: "json",
                success: function(result) {
                    var igst_value = (value * result.igst) / 100;
                    var sgst_value = (value * result.sgst) / 100;
                    var cgst_value = (value * result.cgst) / 100;
                    var total_value = value + igst_value + sgst_value + cgst_value;
                    $(ed5.target).numberbox('setValue', igst_value);
                    $(ed6.target).numberbox('setValue', sgst_value);
                    $(ed7.target).numberbox('setValue', cgst_value);
                    $(ed8.target).numberbox('setValue', total_value);

                    if (result.state == null) {
                        toastr.warning("Please update state in customer or suppliers data", "Information");
                    }
                }
            });
        }
    }

    //EDIT DATA
    function update() {
        action = 'update';
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.status == 1) {
                toastr.error("Status is Close, You Cannot Update Data", "Information");
            } else {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#number").textbox('disable');

                $('#dg2').datagrid('loadData', []);
                $('#process_type').combobox({
                    url: '<?= base_url('warehouse/rgp_in/readProcessType'); ?>',
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Choose Type',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(val) {
                        var company_id = $('#company_id').combogrid('getValue');
                        var item_category_id = $('#item_category_id').combobox('getValue');
                        $('#rgp_number').combobox({
                            url: '<?= base_url('warehouse/rgp_in/readDocumentOut?process_type='); ?>' + val.id + '&company_id=' + company_id + '&item_category_id=' + item_category_id,
                            valueField: 'number',
                            textField: 'number',
                            prompt: 'Choose RGP Out',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                            onLoadSuccess: function() {
                                $("#rgp_number").combobox('setValue', row.rgp_number);
                            },
                            onSelect: function(rgp) {
                                var number = $("#number").textbox('getValue');
                                var process_type = $('#process_type').combobox('getValue');
                                var item_category_id = $('#item_category_id').combobox('getValue');
                                var company_id = $('#company_id').combogrid('getValue');
                                addTable(process_type, item_category_id, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp.number) + '&action=' + action);
                            }
                        });
                        var number = $("#number").textbox('getValue');
                        var rgp_number = $("#rgp_number").combobox('getValue');
                        var item_category_id = $('#item_category_id').combobox('getValue');
                        var company_id = $('#company_id').combogrid('getValue');

                        $('#dg2').datagrid('loadData', []);
                        addTable(val.id, item_category_id, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp_number) + '&action=' + action);
                    }
                });
                setTimeout(function() {
                    $("#process_type").combobox('setValue', row.process_type);
                }, 100);

                $('#item_category_id').combobox({
                    url: '<?= base_url('warehouse/rgp_in/readItemCategories'); ?>',
                    valueField: 'id',
                    textField: 'number',
                    prompt: 'Choose RGP Out',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(category) {
                        var number = $("#number").textbox('getValue');
                        var process_type = $('#process_type').combobox('getValue');
                        var rgp_number = $("#rgp_number").combobox('getValue');
                        var company_id = $('#company_id').combogrid('getValue');

                        $('#dg2').datagrid('loadData', []);
                        addTable(process_type, category.id, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp_number) + '&action=' + action);

                        $('#rgp_number').combobox({
                            url: '<?= base_url('warehouse/rgp_in/readDocumentOut?process_type='); ?>' + process_type + '&company_id=' + company_id + '&item_category_id=' + category.id,
                            valueField: 'number',
                            textField: 'number',
                            prompt: 'Choose RGP Out',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                            onSelect: function(rgp) {
                                var number = $("#number").textbox('getValue');
                                var process_type = $('#process_type').combobox('getValue');
                                var company_id = $('#company_id').combogrid('getValue');
                                addTable(process_type, category.number, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp.number) + '&action=' + action);
                            }
                        });
                    }
                });

                $("#process_type").combobox('disable');
                $("#company_id").combogrid('disable');
                $("#plant").combobox('disable');
                $("#item_category_id").combobox('disable');
                $("#rgp_number").combobox('disable');
                $("#transaction_date").datebox('setValue', row.transaction_date);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
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
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + rows[0].transaction_date + "&menus_id=<?= $id_menu ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

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

                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('warehouse/rgp_in/delete') ?>',
                                        data: {
                                            number: row.number
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');
                                            requestDelete(total, json, number + 1, value);

                                            if (number == total) {
                                                $('#dg').datagrid('reload');
                                                Swal.close();
                                                Swal.fire(
                                                    'Delete Completed',
                                                    'Delete Data has been completed, You cannot restore data that has been deleted',
                                                    'success'
                                                );
                                            }
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            toastr.error(jqXHR.statusText);
                                            swal.close();
                                        },
                                    });
                                }
                            }
                        }
                    });
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_bom.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_process_type = $("#filter_process_type").combobox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_company = $("#filter_company").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_rgp_type = $("#filter_rgp_type").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_process_type=" + window.btoa(filter_process_type) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_company=" + window.btoa(filter_company) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_departement=" + window.btoa(filter_departement) +
            "&filter_rgp_type=" + window.btoa(filter_rgp_type);

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/rgp_in/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                if (row.process_type == 'FOC') {
                    var hidden = false;
                } else if (row.process_type == 'NRGP' && row.item_category_name != 'OTHER') {
                    var hidden = false;
                } else {
                    var hidden = true;
                }

                ddv.datagrid({
                    url: '<?= base_url('warehouse/rgp_in/datatableDetails?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'rgp_id',
                            title: 'RGP ID',
                            halign: 'center',
                            width: 160
                        }, {
                            field: 'item_id',
                            title: 'Part No EBWS',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_number',
                            title: 'Part No Supplier',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_name',
                            title: 'Description',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_category_name',
                            title: 'Category',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'item_family_name',
                            title: 'Product Family',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Price',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'value',
                            title: 'Value',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'igst_value',
                            title: 'IGST',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'sgst_value',
                            title: 'SGST',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'cgst_value',
                            title: 'CGST',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'total',
                            title: 'Total Value',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'mpq',
                            title: 'MPQ',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            hidden: hidden,
                        }, {
                            field: 'qty_label',
                            title: 'Qty Label',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            hidden: hidden,
                        }, {
                            field: 'btnPrint',
                            title: 'Label',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            hidden: hidden,
                            formatter: BtnPrint
                        }, ]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/rgp_in/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_process_type = $("#filter_process_type").combobox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_company = $("#filter_company").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_rgp_type = $("#filter_rgp_type").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_process_type=" + window.btoa(filter_process_type) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_company=" + window.btoa(filter_company) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_departement=" + window.btoa(filter_departement) +
            "&filter_rgp_type=" + window.btoa(filter_rgp_type);

        window.location.assign('<?= base_url('warehouse/rgp_in/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function btnDrawing(val) {
        if (val == null || val == "") {
            return "-";
        } else {
            return "<a class='btn btn-primary btn-sm' style='pointer-events: auto; opacity:1; width:100%;' target='_blank' href='<?= base_url('assets/documents/bom/') ?>" + val + "'><i class='fa fa-eye'></i> View</a>";
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

    $(function() {
        //ADD DATA
        addTable();
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {

                    var process_type = $("#process_type").combobox('getValue');
                    var transaction_date = $("#transaction_date").datebox('getValue');
                    var number = $("#number").textbox('getValue');
                    var company_id = $("#company_id").combogrid('getValue');
                    var plant = $("#plant").combobox('getValue');
                    var item_category_id = $("#item_category_id").combobox('getValue');
                    var departement_id = $("#departement_id").combobox('getValue');
                    var rgp_type_id = $("#rgp_type_id").combobox('getValue');
                    var reference_number = $("#reference_number").textbox('getValue');
                    var rgp_number = $("#rgp_number").combobox('getValue');
                    var notes = $("#notes").textbox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + transaction_date + "&menus_id=<?= $id_menu ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            // $('#dg2').datagrid('acceptChanges');
                            var rows = $('#dg2').datagrid('getRows');
                            var totalrows = rows.length;
                            endEditing();
                            if (rgp_number == '') {
                                $('#dlg_insert').dialog('close');
                                Swal.fire({
                                    title: 'Please Wait...',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                requestData(totalrows, rows);
                            } else {
                                let qty_over = 0;
                                for (let i = 0; i < rows.length; i++) {
                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('warehouse/rgp_in/readStockRgp') ?>',
                                        data: {
                                            rgp_number: rgp_number,
                                            item_id: rows[i].item_id
                                        },
                                        dataType: "json",
                                        async: false, // Makes the request synchronous
                                        success: function(result) {
                                            if (parseInt(rows[i].qty) > parseInt(result.balance)) {
                                                qty_over++;
                                            }
                                        }
                                    });
                                }

                                if (qty_over > 0) {
                                    toastr.warning("Qty must be less than or equal to balance", "Information");
                                } else {
                                    $('#dlg_insert').dialog('close');
                                    Swal.fire({
                                        title: 'Please Wait...',
                                        showConfirmButton: false,
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        },
                                    });

                                    requestData(totalrows, rows);
                                }
                            }

                            function requestData(total, json, numberData = 1, value = 0, success = 1, failed = 1) {
                                var i = (numberData - 1);
                                if (value < 100) {
                                    value = Math.floor((numberData / total) * 100);
                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('warehouse/rgp_in/create') ?>',
                                        data: {
                                            process_type: process_type,
                                            transaction_date: transaction_date,
                                            number: number,
                                            company_id: company_id,
                                            plant: plant,
                                            item_category_id: item_category_id,
                                            departement_id: departement_id,
                                            rgp_type_id: rgp_type_id,
                                            reference_number: reference_number,
                                            rgp_number: rgp_number,
                                            notes: notes,
                                            item_id: json[i].item_id,
                                            uom: json[i].uom,
                                            qty: json[i].qty,
                                            currency: json[i].currency,
                                            hsn_code: json[i].hsn_code,
                                            price: json[i].price,
                                            value: json[i].value,
                                            igst_value: json[i].igst_value,
                                            sgst_value: json[i].sgst_value,
                                            cgst_value: json[i].cgst_value,
                                            total: json[i].total,
                                        },
                                        dataType: "json",
                                        success: function(result) {
                                            if (i == (total - 1)) {
                                                Swal.close();
                                                Swal.fire({
                                                    title: result.message,
                                                    icon: result.theme,
                                                    confirmButtonText: 'Ok',
                                                    allowOutsideClick: false,
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        // window.location.reload();
                                                        $('#dg').datagrid('reload');
                                                    }
                                                });
                                            }

                                            requestData(total, json, numberData + 1, value, success + 1, failed + 0);
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('warehouse/rgp_in/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('warehouse/rgp_in/upload') ?>',
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
                                url: "<?= base_url('warehouse/rgp_in/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('warehouse/rgp_in/uploadCreate') ?>",
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
                                                    url: "<?= base_url('warehouse/rgp_in/uploadcreateFailed') ?>",
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

        $('#item_category_id').combobox({
            url: '<?= base_url('warehouse/rgp_in/readItemCategories'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Category',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(category) {
                var number = $("#number").textbox('getValue');
                var process_type = $('#process_type').combobox('getValue');
                var company_id = $('#company_id').combogrid('getValue');
                var rgp_number = $('#rgp_number').combobox('getValue');
                $('#dg2').datagrid('loadData', []);
                addTable(process_type, category.id, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp_number) + '&action=' + action);
            }
        });

        $('#rgp_number').combobox({
            url: '<?= base_url('warehouse/rgp_in/readDocumentOut'); ?>',
            valueField: 'number',
            textField: 'number',
            prompt: 'Choose RGP Out',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(rgp) {
                var number = $("#number").textbox('getValue');
                var process_type = $('#process_type').combobox('getValue');
                var item_category_id = $('#item_category_id').combobox('getValue');
                var company_id = $('#company_id').combogrid('getValue');
                addTable(process_type, item_category_id, company_id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp.number) + '&action=' + action);
            }
        });

        $('#company_id').combogrid({
            url: '<?= base_url('warehouse/rgp_in/readCompany') ?>',
            panelWidth: 460,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Company",
            columns: [
                [{
                    field: 'id',
                    title: 'ID',
                    width: 100
                }, {
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Company Name',
                    width: 160
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }]
            ],
            onSelect: function(valItem, rowItem) {
                if (rowItem.type == 'CUSTOMER') {
                    $('#plant').combobox({
                        url: '<?= base_url('master/customers/readAddress/') ?>' + window.btoa(rowItem.id),
                        valueField: 'plant',
                        textField: 'plant',
                        prompt: "Choose Plant",
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combobox('clear').combobox('textbox').focus();
                            }
                        }],
                        onSelect: function(irow) {
                            // 
                        }
                    });
                } else {
                    $("#plant").combobox('clear');
                }

                var process_type = $('#process_type').combobox('getValue');
                $('#rgp_number').combobox({
                    url: '<?= base_url('warehouse/rgp_in/readDocumentOut?process_type='); ?>' + process_type + '&company_id=' + rowItem.id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: 'Choose RGP Out',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(rgp) {
                        var number = $("#number").textbox('getValue');
                        var process_type = $('#process_type').combobox('getValue');
                        var item_category_id = $('#item_category_id').combobox('getValue');
                        addTable(process_type, item_category_id, rowItem.id, '<?= base_url('warehouse/rgp_in/datatableUpdates?number=') ?>' + window.btoa(number) + '&rgp_number=' + window.btoa(rgp.number) + '&action=' + action);
                    }
                });
                // $("#uom").textbox('setValue', rowItem.uom);
            }
        });

        $('#departement_id').combobox({
            url: '<?= base_url('master/departements/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Department',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(category) {
                // 
            }
        });

        $('#rgp_type_id').combobox({
            url: '<?= base_url('master/rgp_types/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Type',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(category) {
                // 
            }
        });
    });

    $('#filter_number').combobox({
        url: '<?= base_url('warehouse/rgp_in/readDocument'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Dokumen No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(category) {
            // 
        }
    });

    $('#filter_category').combobox({
        url: '<?= base_url('warehouse/rgp_in/readItemCategories'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Category',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(category) {
            // 
        }
    });

    $('#filter_company').combogrid({
        url: '<?= base_url('warehouse/rgp_in/readCompany') ?>',
        panelWidth: 460,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Company",
        columns: [
            [{
                field: 'id',
                title: 'ID',
                width: 100
            }, {
                field: 'number',
                title: 'Code',
                width: 100
            }, {
                field: 'name',
                title: 'Company Name',
                width: 260
            }]
        ],
        onSelect: function(valItem, rowItem) {
            if (rowItem.type == 'customer') {
                $('#filter_plant').combobox({
                    url: '<?= base_url('master/customers/readAddress/') ?>' + window.btoa(rowItem.id),
                    valueField: 'plant',
                    textField: 'plant',
                    prompt: "Choose Plant",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(irow) {
                        // 
                    }
                });
            } else {
                $("#filter_plant").combobox('clear');
            }
            // $("#uom").textbox('setValue', rowItem.uom);
        }
    });

    $('#filter_departement').combobox({
        url: '<?= base_url('master/departements/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Department',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(category) {
            // 
        }
    });

    $('#filter_rgp_type').combobox({
        url: '<?= base_url('master/rgp_types/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Type',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
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

    function statusFormat(value, row) {
        if (row.process_type != 'NRGP') {
            if (value == 0) {
                return "<b style='color:green;'>OPEN</b>";
            } else if (value == 1) {
                return "<b style='color:red;'>CLOSE</b>";
            } else if (value == 2) {
                return "<b style='color:red;'>CANCEL</b>";
            }
        } else {
            return "-";
        }
    }

    function statusStyle(value, row, index) {
        if (row.process_type != 'NRGP') {
            if (value == 0) {
                return 'background-color:#C8FFCC;';
            } else if (value == 1) {
                return 'background-color:#FFC8C8;';
            } else if (value == 2) {
                return 'background-color:#7285e9;';
            }
        } else {
            // 
        }
    }

    function BtnPrint(val, row) {
        var send = "print_label('" + row.number + "','" + row.item_id + "','" + row.qty + "','" + row.mpq + "','" + row.qty_label + "','" + row.item_category_id + "','" + row.approved_to + "')";
        return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="' + send + '"><i class="fa fa-print"></i> Print</a>';

    }


    function cellStylerApproval(value, row, index) {
        // if (row.approved_to == null) {
        //     return 'background: #FF5F5F; color:white;';
        // } else 
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        // if (row.approved_to == null) {
        //     return 'Not Setting';
        // } else 
        if (row.approved_to == "" || row.approved_to == null) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function print_label(rgp_number, item_id, qty, qty_mpq, qty_label, category, approved_to) {
        if (qty_mpq == 'null' || qty_mpq == '') {
            toastr.error("Please check MPQ first!");
        } else {
            console.log(approved_to);
            if (approved_to == '' || approved_to == 'null') {
                $.ajax({
                    type: "POST",
                    async: true,
                    url: "<?= base_url('warehouse/rgp_in/create_check_label') ?>",
                    data: {
                        "number": rgp_number,
                        "item_id": item_id
                    },
                    cache: false,
                    dataType: "json",
                    success: function(check) {
                        if (check.total == 0) {
                            $("#dlg_label").dialog('open');
                            requestDataBox(qty_label, qty);

                            function requestDataBox(total, qty, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    if (parseInt(qty) > parseInt(qty_mpq)) {
                                        var qty_final = qty_mpq;
                                    } else {
                                        var qty_final = qty;
                                    }

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('warehouse/rgp_in/create_label') ?>",
                                        data: {
                                            "rgp_number": rgp_number,
                                            "qty": qty_final,
                                            "item_id": item_id
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.title != "OK") {
                                                if (result.theme == "success") {
                                                    $('#p_success').html(success);
                                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                    var qty_balance = (parseInt(qty) - parseInt(qty_mpq));
                                                    requestDataBox(total, qty_balance, number + 1, value, success + 1, failed + 0);
                                                } else {
                                                    $('#p_failed').html(failed);
                                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                    var qty_balance = (parseInt(qty) - parseInt(qty_mpq));
                                                    requestDataBox(total, qty_balance, number + 1, value, success + 0, failed + 1);
                                                }

                                                $("#p_remarks").append(title + "<br>");

                                                if (value == 100) {
                                                    $("#dlg_label").dialog('close');
                                                    $('#dg').datagrid('reload');
                                                    toastr.success("Create Label Completed");
                                                    window.open("<?= base_url('warehouse/rgp_in/print_label/') ?>" + window.btoa(rgp_number) + "/" + category + "/" + window.btoa(item_id), '_blank');
                                                }
                                            } else {
                                                $("#dlg_label").dialog('close');
                                                window.open("<?= base_url('warehouse/rgp_in/print_label/') ?>" + window.btoa(rgp_number) + "/" + category + "/" + window.btoa(item_id), '_blank');
                                            }
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        toastr.error("Connection Time Out, Please Wait");
                                        requestDataBox(total, qty, number, value, success, failed);
                                    });
                                }
                            }
                        } else {
                            window.open("<?= base_url('warehouse/rgp_in/print_label/') ?>" + window.btoa(rgp_number) + "/" + category + "/" + window.btoa(item_id), '_blank');
                        }
                    }
                });
            } else {
                toastr.error("RGP Number Document Need to Approve");
            }
        }
    }
</script>