<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <!-- <th rowspan="2" data-options="field:'item_fg_id',width:150,align:'center'">Product ID</th> -->
            <th rowspan="2" data-options="field:'item_fg_number',width:150,halign:'center'">Product No EBWS</th>
            <th rowspan="2" data-options="field:'number_customer',width:200,halign:'center'">Product No Customer</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'revision',width:100,halign:'center'">Revision</th>
            <th rowspan="2" data-options="field:'drawing',width:100,halign:'center', formatter:btnDrawing">Drawing</th>
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

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 160px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 75%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="openDlgCompare()"><i class="fa fa-retweet"></i> Compare Bom</a>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="openDlgCopy()"><i class="fa fa-copy"></i> Copy Bom</a>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="toolbar3">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="appendCopy()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeitCopy()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 98%; height: 550px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No EBWS</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 30%; float: left;" hidden>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No Customer</span>
                    <input style="width:60%;" name="number_customer" id="number_customer" readonly class="easyui-textbox">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Drawing</span>
                    <input style="width:60%;" name="u_drawing" id="u_drawing" class="easyui-filebox" accept=".png, .jpg, .jpeg">
                </div>
                <div class="fitem" hidden>
                    <span style="width:25%; display:inline-block;">Drawing</span>
                    <input style="width:70%;" name="drawing" id="drawing" class="easyui-textbox">
                </div>
            </div>
            <div style="width: 40%; float: left;" id="frm_revision">
                <div class="fitem">
                    <span style="width:15%; display:inline-block;">Last Rev</span>
                    <input style="width:20%;" name="revision" id="revision" class="easyui-combobox">
                    <span style="width:15%; display:inline-block;">&nbsp; &nbsp; New Rev</span>
                    <input style="width:20%;" name="new_revision" id="new_revision" readonly class="easyui-textbox">
                    <a style="width:15%;" class="easyui-linkbutton" onclick="preview()">Preview</a>
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Bill of Material Lists" toolbar="#toolbar2"></table>
    </form>
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

<!-- Compare -->
<div id="dlg_compare" class="easyui-dialog" title="Compare" data-options="closed: true,modal:true" style="width: 98%; height: 550px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_compare" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source Product</span>
                    <input style="width:60%;" name="item_fg_id_source" id="item_fg_id_source" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Compare Product</span>
                    <input style="width:60%;" name="item_fg_id_compare" id="item_fg_id_compare" required="" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 20%; float: left;">
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Rev</span>
                    <input style="width:30%;" name="revision_source" id="revision_source" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Rev</span>
                    <input style="width:30%;" name="revision_compare" id="revision_compare" required="" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter_compare()"><i class="fa fa-search"></i> Compare BOM</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="pdf_compare()"><i class="fa fa-print"></i> Print</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="excel_compare()"><i class="fa fa-file"></i> Export Excel</a>
                </div>
            </div>
        </fieldset>
        <div id="p-compare" class="easyui-panel" title="Print Preview" style="width:100%;">
            <iframe id="printout_compare" src="" style="width: 100%; height: 330px; border: 0;"></iframe>
        </div>
    </form>
</div>

<!-- Copy or Update -->
<div id="dlg_copy" class="easyui-dialog" title="Copy Bom" data-options="closed: true,modal:true" style="width: 98%; height: 550px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_copy" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 40%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source Product No</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id_copy_1" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">New Product No</span>
                    <input style="width:60%;" name="item_fg_id_copy" id="item_fg_id_copy_2" class="easyui-combogrid">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block; vertical-align:top;">Type</span>
                    <span style="width:60%; display:inline-block;">
                        <div style="width:100%; margin-bottom: 5px; display:inline-block;">
                            <input class="easyui-radiobutton" type="radio" name="type" value="1" required> Update Source
                        </div>
                        <div style="width:100%; margin-bottom: 5px; display:inline-block;">
                            <input class="easyui-radiobutton" type="radio" name="type" value="2" required> Copy to New
                        </div>
                        <div style="width:100%; margin-bottom: 5px; display:inline-block;">
                            <input class="easyui-radiobutton" type="radio" name="type" value="3" required> Update Source & Copy to New
                        </div>
                    </span>
                </div>
            </div>
        </fieldset>
        <table id="dg-copy" class="easyui-datagrid" style="width:100%;" title="Bill of Material Lists" toolbar="#toolbar3"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/bom/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('master/bom/create') ?>';
        $('#frm_insert').form('clear');
        $("#item_fg_id").combogrid('enable');
        $("#frm_revision").show();
    }

    function preview(){
        var item_fg_id = $("#item_fg_id").combogrid('getValue');
        var revision = $("#revision").combobox('getValue');

        if(item_fg_id != "" && revision != ""){
            addTable('<?= base_url('master/bom/datatableUpdates?item_fg_id=') ?>' + window.btoa(item_fg_id) + "&revision=" + revision);
        }else{
            toastr.error("Please select Product No and Revision!");
        }

    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
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
                            url: '<?= base_url('master/item_rm/reads'); ?>',
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

                                var item_fg_id = $("#item_fg_id").combogrid('getValue');

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_name'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_number'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_family_name'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'composition'
                                });

                                $(ed.target).textbox('setValue', rows.name);
                                $(ed3.target).textbox('setValue', rows.number);
                                $(ed4.target).textbox('setValue', rows.item_family_name);
                                $(ed5.target).textbox('setValue', rows.uom);
                                $(ed6.target).numberbox('setValue', 0);
                            }
                        }
                    }
                }, {
                    field: 'item_rm_number',
                    width: 150,
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
                    width: 150,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_family_name',
                    width: 120,
                    halign: 'center',
                    title: "Product Family",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
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
                            readonly: true
                        }
                    }
                }, {
                    field: 'main_process',
                    width: 150,
                    halign: 'center',
                    title: "Main Process",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/item_process_main/reads'); ?>',
                            required: true,
                            panelWidth: 300,
                            idField: 'name',
                            textField: 'name',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Main Process',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Code',
                                    width: 80
                                }, {
                                    field: 'name',
                                    title: 'Name',
                                    width: 200
                                }]
                            ],
                        }
                    }
                }, {
                    field: 'composition',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    title: "QPA",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 6,
                        }
                    }
                }, {
                    field: 'revision',
                    width: 80,
                    align: 'center',
                    title: "Revision",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'remove',
                    width: 80,
                    align: 'center',
                    hidden: true,
                    title: "Remove",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }]
            ],
            onClickCell: onClickCell,
            rowStyler: function(index,row){
                if (row.remove == 1){
                    return 'background-color:red; color:#fff; font-weight:bold;';
                }
            }
        });
    }

    function addTableCopy(link = "") {
        $('#dg-copy').datagrid({
            url: link,
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
                            url: '<?= base_url('master/item_rm/reads'); ?>',
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

                                var item_fg_id = $("#item_fg_id").combogrid('getValue');

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_name'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_number'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_family_name'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'composition'
                                });

                                $(ed.target).textbox('setValue', rows.name);
                                $(ed3.target).textbox('setValue', rows.number);
                                $(ed4.target).textbox('setValue', rows.item_family_name);
                                $(ed5.target).textbox('setValue', rows.uom);
                                $(ed6.target).numberbox('setValue', 0);
                            }
                        }
                    }
                }, {
                    field: 'item_rm_number',
                    width: 150,
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
                    width: 150,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_family_name',
                    width: 120,
                    halign: 'center',
                    title: "Product Family",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
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
                            readonly: true
                        }
                    }
                }, {
                    field: 'main_process',
                    width: 150,
                    halign: 'center',
                    title: "Main Process",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/item_process_main/reads'); ?>',
                            required: true,
                            panelWidth: 300,
                            idField: 'name',
                            textField: 'name',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Main Process',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Code',
                                    width: 80
                                }, {
                                    field: 'name',
                                    title: 'Name',
                                    width: 200
                                }]
                            ],
                        }
                    }
                }, {
                    field: 'composition',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    title: "QPA",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 6,
                        }
                    }
                }, {
                    field: 'revision',
                    width: 80,
                    align: 'center',
                    title: "Revision",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'remove',
                    width: 80,
                    hidden: true,
                    align: 'center',
                    title: "Remove",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }]
            ],
            onClickCell: onClickCellCopy,
            rowStyler: function(index,row){
                if (row.remove == 1){
                    return 'background-color:red; color:#fff; font-weight:bold;';
                }
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

    function onClickCell(index, field) {
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
    }

    function append() {
        var item_fg_id = $("#item_fg_id").combogrid('getValue');
        if (item_fg_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Product No EBWS first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }

        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'item_rm_id'
        });

        var ed2 = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'remove'
        });

        var item_fg_id = $("#item_fg_id").combogrid('getValue');
        var item_rm_id = $(ed.target).textbox('getValue');
        $(ed2.target).numberbox('setValue', 1);

        // $.ajax({
        //     method: 'post',
        //     url: '<?= base_url('master/bom/remove') ?>',
        //     data: {
        //         item_fg_id: row.item_fg_id,
        //         item_rm_id: item_rm_id
        //     },
        //     success: function(result) {
        //         var result = eval('(' + result + ')');
        //         toastr.success(result.message);
        //         $('#dg2').datagrid('reload');
        //     },
        //     error: function(jqXHR, textStatus, errorThrown) {
        //         toastr.error("This data is already used in other modules, You must delete it First", "Error");

        //     },
        //     complete: function(data) {
        //         $('#dg').datagrid('reload');
        //     }
        // });

        endEditing();
        // $('#dg2').datagrid('reload');
        // $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //  copy datagrid
    var editIndexCopy = undefined;

    function endEditingCopy() {
        if (editIndexCopy == undefined) {
            return true
        }
        if ($('#dg-copy').datagrid('validateRow', editIndexCopy)) {
            $('#dg-copy').datagrid('endEdit', editIndexCopy);
            editIndexCopy = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCellCopy(index, field) {
        if (editIndexCopy != index) {
            if (endEditingCopy()) {
                $('#dg-copy').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndexCopy = index;
            } else {
                setTimeout(function() {
                    $('#dg-copy').datagrid('selectRow', editIndexCopy);
                }, 0);
            }
        }
    }

    function appendCopy() {
        var item_fg_id = $("#item_fg_id_copy_1").combogrid('getValue');
        if (item_fg_id != "") {
            if (endEditingCopy()) {
                $('#dg-copy').datagrid('appendRow', {
                    qty: '0'
                });
                editIndexCopy = $('#dg-copy').datagrid('getRows').length - 1;
                $('#dg-copy').datagrid('selectRow', editIndexCopy).datagrid('beginEdit', editIndexCopy);
            }
        } else {
            toastr.error("Please Choose Product No EBWS first");
        }
    }

    function removeitCopy() {
        if (editIndexCopy == undefined) {
            return true;
        }

        var dg = $('#dg-copy');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndexCopy,
            field: 'item_rm_id'
        });

        var item_fg_id = $("#item_fg_id_copy_1").combogrid('getValue');
        var item_rm_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('master/bom/remove') ?>',
            data: {
                item_fg_id: row.item_fg_id,
                item_rm_id: item_rm_id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
                $('#dg-copy').datagrid('reload');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error("This data is already used in other modules, You must delete it First", "Error");

            },
            complete: function(data) {
                $('#dg').datagrid('reload');
            }
        });

        // $('#dg-copy').datagrid('cancelEdit', editIndexCopy).datagrid('deleteRow', editIndexCopy);
        editIndexCopy = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');

        if(row.approved_to == ''){
            toastr.error("Cannot Update Because the status is Approved");
            return false;
        }

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#item_fg_id").combogrid('disable');
            $("#frm_revision").hide();

            addTable('<?= base_url('master/bom/datatableUpdates?item_fg_id=') ?>' + window.btoa(row.item_fg_id));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function openDlgCompare() {
        $('#dlg_compare').dialog('open');
        $('#frm_compare').form('clear');
        $("#printout_compare").attr('src', '');
    }

    //EDIT DATA
    function openDlgCopy() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_copy').dialog('open');
            $('#frm_copy').form('load', row);
            $("#item_fg_id_copy_1").combogrid('disable');

            addTableCopy('<?= base_url('master/bom/datatableUpdates?item_fg_id=') ?>' + window.btoa(row.item_fg_id));
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
                                url: '<?= base_url('master/bom/delete') ?>',
                                data: {
                                    item_fg_id: row.item_fg_id
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
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        $('#dg').datagrid({
            url: '<?= base_url('master/bom/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/bom/print') ?>' + url);
    }

    function filter_compare() {
        var item_fg_id_source = $("#item_fg_id_source").combogrid('getValue');
        var item_fg_id_compare = $("#item_fg_id_compare").combogrid('getValue');
        var revision_source = $("#revision_source").combobox('getValue');
        var revision_compare = $("#revision_compare").combobox('getValue');

        var url = "?item_fg_id_source=" + window.btoa(item_fg_id_source) +
            "&item_fg_id_compare=" + window.btoa(item_fg_id_compare) + 
            "&revision_source=" + window.btoa(revision_source) +
            "&revision_compare=" + window.btoa(revision_compare);
            
        if (item_fg_id_source == "" || item_fg_id_compare == "" || revision_source == "" || revision_compare == "") {
            toastr.warning("Please select Product and Revision!");
        } else {
            $("#printout_compare").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout_compare").attr('src', '<?= base_url('master/bom/print_compare') ?>' + url);
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function pdf_compare() {
        $("#printout_compare").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        window.location.assign('<?= base_url('master/bom/print/excel') ?>' + url);
    }

    //PRINT EXCEL
    function excel_compare() {
        var item_fg_id_source = $("#item_fg_id_source").combogrid('getValue');
        var item_fg_id_compare = $("#item_fg_id_compare").combogrid('getValue');
        var revision_source = $("#revision_source").combobox('getValue');
        var revision_compare = $("#revision_compare").combobox('getValue');

        var url = "?item_fg_id_source=" + window.btoa(item_fg_id_source) +
            "&item_fg_id_compare=" + window.btoa(item_fg_id_compare) + 
            "&revision_source=" + window.btoa(revision_source) +
            "&revision_compare=" + window.btoa(revision_compare);
            
        if (item_fg_id_source == "" || item_fg_id_compare == "" || revision_source == "" || revision_compare == "") {
            toastr.warning("Please select Product and Revision!");
        } else {
            window.location.assign('<?= base_url('master/bom/print_compare/excel') ?>' + url);
        }
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
        addTableCopy();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/bom/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.item_fg_number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('master/bom/datatableDetails?number=') ?>' + window.btoa(row.item_fg_number) + "&filter_item_rm_id=" + window.btoa(filter_item_rm_id),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_rm_id',
                            title: 'Part No EBWS',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_rm_number',
                            title: 'Part No Supplier',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_rm_name',
                            title: 'Description',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'main_process',
                            title: 'Process Main',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'product_family_name',
                            title: 'Product Family',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'maker_name',
                            title: 'Maker',
                            align: 'center',
                            width: 150
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'composition',
                            title: 'QPA',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'revision',
                            title: 'Revision',
                            width: 80,
                            align: 'center',
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    },
                    rowStyler: function(index,row){
                        if(row.remove == 1){
                            return 'background-color:#e5b5b5;font-weight:bold;';
                        }
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Send to Approval',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#dlg_insert').dialog('close');

                    var item_fg_id = $("#item_fg_id").combogrid('getValue');
                    var drawing = $("#drawing").textbox('getValue');
                    var new_revision = $("#new_revision").textbox('getValue');

                    Swal.fire({
                        title: 'Please Wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    combinedBom = [];
                    for (let i = 0; i < totalrows; i++) {
                        var json = rows[i];
                        // if(json.remove == 0){
                        if(json.item_rm_id == "" || json.main_process == ""){
                            toastr.error("Please Completed your data!");
                            return false;
                        }

                        combinedBom.push({
                            item_fg_id: item_fg_id,
                            revision: new_revision,
                            drawing: drawing,
                            item_rm_id: json.item_rm_id,
                            main_process: json.main_process,
                            composition: json.composition,
                            remove: json.remove
                        });
                        // }
                    }

                    $.ajax({
                        type: "post",
                        url: '<?= base_url('master/bom/create') ?>',
                        data: JSON.stringify({ dataBom: combinedBom }),
                        dataType: "json",
                        success: function (result) {
                            Swal.close();
                            
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
            }]
        });

        $('#dlg_copy').dialog({
            buttons: [{
                text: 'Send to Approval',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#dlg_copy').dialog('close');

                    var item_fg_id_copy_1 = $("#item_fg_id_copy_1").combogrid('getValue');
                    var item_fg_id_copy_2 = $("#item_fg_id_copy_2").combogrid('getValue');
                    // var type = $("input[name='type']:checked").val();

                    // if (type === undefined) {
                    //     toastr.error("Please Choose Type Copy");
                    // } else {
                        // if ((type == '2' || type == '3') && item_fg_id_copy_2 == '') {
                        if (item_fg_id_copy_2 == '') {
                            toastr.error("Please Choose New Product No EBWS");
                        } else {
                            Swal.fire({
                                title: 'Please Wait...',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            $('#dg-copy').datagrid('acceptChanges');
                            var rows = $('#dg-copy').datagrid('getRows');
                            var totalrows = rows.length;
                            endEditing();

                            combinedBom = [];
                            for (let i = 0; i < totalrows; i++) {
                                var json = rows[i];
                                if(json.remove == 0){
                                    combinedBom.push({
                                        item_fg_id_copy_1: item_fg_id_copy_1,
                                        item_fg_id_copy_2: item_fg_id_copy_2,
                                        type: "COPY",
                                        item_rm_id: json.item_rm_id,
                                        main_process: json.main_process,
                                        composition: json.composition,
                                    });
                                }
                            }

                            $.ajax({
                                type: "post",
                                url: '<?= base_url('master/bom/copy') ?>',
                                data: JSON.stringify({ dataBom: combinedBom }),
                                dataType: "json",
                                success: function (result) {
                                    Swal.close();
                                    
                                    if(result.theme == "error"){
                                        $('#dlg_copy').dialog('open');
                                        toastr.error(result.message);
                                        return false;
                                    }

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
                    // }
                }
            }]
        });

        $('#item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads/'); ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number_customer',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No EBWS",
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
            onSelect: function(val, row) {
                $("#number_customer").textbox('setValue', row.number_customer);

                $.ajax({
                    type: "post",
                    url: "<?= base_url('master/bom/readRevision') ?>",
                    data: "item_fg_id=" + row.id,
                    dataType: "json",
                    success: function (data) {
                        if(data.rows.length > 0){
                            $("#revision").combobox({
                                data: data.rows,
                                valueField: 'revision',
                                textField: 'revision',
                            });

                            $("#revision").combobox('setValue', data.rows[0].revision);
                            $("#new_revision").textbox('setValue', data.new_revision);
                        }else{
                            toastr.info("History Bill of Material Cannot Found");
                            $("#revision").combobox('setValue', 0);
                            $("#new_revision").textbox('setValue', 0);
                        }
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

        $("#u_drawing").filebox({
            onChange: function() {
                var drawing = $("#drawing").textbox('getValue');
                var files = $(this).filebox('files')[0];

                var formData = new FormData();
                formData.append('file', files);

                $.ajax({
                    url: "<?= base_url('master/bom/uploadDrawing/') ?>" + btoa(drawing),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        var data = JSON.parse(response);
                        toastr.info(data.msg);
                        $("#drawing").textbox('setValue', data.file);
                    }
                });
            }
        });

        $('#item_fg_id_source').combogrid({
            url: '<?= base_url('master/item_fg/reads/'); ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No EBWS",
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
            onSelect: function(val, row) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('master/bom/readRevision') ?>",
                    data: "item_fg_id=" + row.id,
                    dataType: "json",
                    success: function (data) {
                        if(data.rows.length > 0){
                            $("#revision_source").combobox({
                                data: data.rows,
                                valueField: 'revision',
                                textField: 'revision',
                            });

                            $("#revision_source").combobox('setValue', data.rows[0].revision);
                        }
                    }
                });
            }
        });

        $('#item_fg_id_compare').combogrid({
            url: '<?= base_url('master/item_fg/reads/'); ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No EBWS",
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
            onSelect: function(val, row) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('master/bom/readRevision') ?>",
                    data: "item_fg_id=" + row.id,
                    dataType: "json",
                    success: function (data) {
                        if(data.rows.length > 0){
                            $("#revision_compare").combobox({
                                data: data.rows,
                                valueField: 'revision',
                                textField: 'revision',
                            });

                            $("#revision_compare").combobox('setValue', data.rows[0].revision);
                        }
                    }
                });
            }
        });

        $('#item_fg_id_copy_1').combogrid({
            url: '<?= base_url('master/item_fg/reads/'); ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No EBWS",
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
            ]
        });

        $('#item_fg_id_copy_2').combogrid({
            url: '<?= base_url('master/item_fg/reads/'); ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No EBWS",
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
            ]
        });

        // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/bom/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/bom/upload') ?>',
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
                                url: "<?= base_url('master/bom/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/bom/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/bom/uploadcreateFailed') ?>",
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
</script>