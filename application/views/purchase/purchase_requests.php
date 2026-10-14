<style>
    @keyframes blink {
        0%, 50%, 100% {
            opacity: 1;
        }
        25%, 75% {
            opacity: 0;
        }
    }

    .blink {
        animation: blink 1s infinite;
        font-size: 20px !important;
        color: red;
        font-weight: bold;
    }
</style>

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',width:120,halign:'center'">PR No</th>
            <th rowspan="2" data-options="field:'request_date',width:100,halign:'center'">PR Date</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
            <th rowspan="2" data-options="field:'item_family_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'total_pr',width:100,halign:'center',align:'right',formatter:numberformat">Total PR</th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th rowspan="2" data-options="field:'print_pr',width:80,halign:'center',formatter:BtnPrint">Print PR</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Create PO</th>
            <th rowspan="2" data-options="field:'attachment',width:80,align:'center',formatter: btnDetails">Attachment</th>
            <!-- <th colspan="2" data-options="field:'',width:100,halign:'center'">Confirm PR</th> -->
            <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Created</th>
        </tr>
        <tr>
            <th data-options="field:'send',width:120,align:'center',formatter:BtnSend">Create</th>
            <th data-options="field:'create_po',width:150,align:'center'">Date</th>
            <!-- <th data-options="field:'confirm',width:100,align:'center', styler:cellStylerConfirm, formatter:cellFormatterConfirm">Status</th> -->
            <!-- <th data-options="field:'confirm_date',width:150,align:'center'">Date</th> -->
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'">By</th>
            <th data-options="field:'created_date',width:120,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar3" style="height: auto; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Detail</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" id="d_request_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" id="detailEdit" class="easyui-linkbutton" onclick="detailEdit()"><i class="fa fa-edit"></i> Edit Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" id="d_supplier" class="easyui-textbox" readonly>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="d_item_family_id" class="easyui-textbox" readonly>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="toolbar" style="height: 195px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period Cutoff</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" id="filter_item_rm" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Generating Process</b></legend>

            <b>Calculation Share Order</b>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <br>
            <center>
                <a href="#" onclick="confirmPo()" id="confirmPo" style="text-decoration: none;"><div class="blink">Confirm Edit PO</div></a>
            </center>
        </fieldset>
    </div>
    <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="adds()"><i class="fa fa-plus"></i> Additional</a>
    <?= $button ?>
</div>

<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="p_month" disabled class="easyui-combobox" required data-options="prompt:'Month'">
                    <input style="width:30%;" name="p_year" disabled class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" name="revision" disabled required class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Vendor Name</span>
                    <input style="width:60%;" name="supplier_id" disabled class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor Name</span>
                    <input style="width:60%;" name="supplier_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="item_family_id" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" name="item_rm_number" disabled class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item Name</span>
                    <input style="width:60%;" name="item_rm_name" disabled class="easyui-textbox">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" name="request_no" id="update_request_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ / MOQ</span>
                    <input style="width:30%;" name="mpq" id="mpq" readonly class="easyui-numberbox">
                    <input style="width:30%;" name="moq" id="moq" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Share Order</span>
                    <input style="width:30%;" name="share_order" id="share_order" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leadtime</span>
                    <input style="width:30%;" name="leadtime" id="leadtime" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Need</span>
                    <input style="width:25%;" name="need" id="need" required="true" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request</span>
                    <input style="width:25%;" id="qty_edit" required="true" class="easyui-numberbox">
                    <input style="width:25%;" name="qty" id="qty" required="true" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:50%;" name="remarks" id="remarks" required class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Status</span>
                    <input style="width:50%;" name="status_data" id="status_data" required class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 550px; padding:10px; top: 20px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period</span>
                        <input style="width:30%;" name="p_month" id="p_month" class="easyui-combobox" required data-options="prompt:'Month'">
                        <input style="width:30%;" name="p_year" id="p_year" required class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Revision</span>
                        <select style="width:60%;" name="revision" id="revision" required class="easyui-combobox" panelHeight="auto">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">PR Date</span>
                        <input style="width:60%;" name="request_date" id="request_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">PR No</span>
                        <input style="width:60%;" name="request_no" id="request_no" readonly required class="easyui-textbox">
                    </div>
                </div>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Attachment</span>
                        <input style="width:60%;" name="attachment_upload" id="attachment_upload" class="easyui-filebox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Attachment</span>
                        <input style="width:60%;" name="attachment" id="attachment" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Name</span>
                        <input style="width:60%;" name="supplier_id" id="supplier_id" required class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" name="item_family_id" id="item_family_id" required class="easyui-combobox">
                    </div>
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Purchase Request Additional" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<div id="dlg_purchase_request" class="easyui-dialog" title="Detail Purchase Request" data-options="closed: true,modal:true" style="width: 100%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_purchase_request" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar3" showFooter="true"></table>
</div>

<div id="dlg_confirm" class="easyui-dialog" title="Confrim Edit PO" data-options="closed: true,modal:true" style="width: 100%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_confirm" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'confirm',width:100,halign:'center',formatter:BtnConfirm">Confirm</th>
                <th data-options="field:'p_month',width:50,halign:'center'">Month</th>
                <th data-options="field:'p_year',width:50,halign:'center'">Year</th>
                <th data-options="field:'revision',width:50,halign:'center'">Rev</th>
                <th data-options="field:'request_no',width:180,halign:'center'">PR No</th>
                <th data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
                <th data-options="field:'item_family_name',width:150,halign:'center'">Product Family</th>
                <th data-options="field:'item_rm_number',width:200,halign:'center'">Item ID</th>
                <th data-options="field:'item_rm_name',width:150,halign:'center'">Item Name</th>
                <th data-options="field:'last_qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty Last</th>
                <th data-options="field:'total_qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty New</th>
                <th data-options="field:'remarks',width:250,halign:'center'">Remarks</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        if (filter_month != "" && filter_year != "" && filter_revision != "") {
            $.messager.prompt('Generate PR', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
                if (r == "PURCHASEREQUEST"){
                    Swal.fire({
                        title: 'Please Wait for Generating Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: "get",
                        url: "<?= base_url('purchase/purchase_requests/getDataMrp') ?>",
                        data: "filter_month=" + window.btoa(filter_month) +
                            "&filter_year=" + window.btoa(filter_year) +
                            "&filter_revision=" + window.btoa(filter_revision) +
                            "&filter_item_family=" + window.btoa(filter_item_family) +
                            "&filter_item_rm=" + window.btoa(filter_item_rm) +
                            "&id_menu=<?= base64_encode($id_menu) ?>",
                        dataType: "json",
                        success: function(rows) {
                            Swal.close();

                            if(rows['total'] > 0){
                                requestData(rows['total'], rows);

                                function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                    if (value < 100) {
                                        value = Math.floor((number / total) * 100);
                                        $('#p_upload').progressbar('setValue', value);
                                        $('#p_start').html(number);
                                        $('#p_finish').html(total);

                                        $.post('<?= base_url('purchase/purchase_requests/create') ?>', {
                                            data: json[number - 1]
                                        }, function(note) {
                                            var result = eval('(' + note + ')');

                                            if (result.theme == "success") {
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                                filter();
                                            }
                                        }).fail(function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                toastr.error("Connection time out");

                                                requestData(total, json, number, value, success + 0, failed + 1);
                                            }
                                        });
                                    }
                                }
                            }else{
                                toastr.warning("Data MRP Not Found");
                            }
                        },
                        error: function(rows) {
                            Swal.fire('Failed!', 'Generate Purchase Requst is Failed, Please Try Again!', 'error');
                        }
                    });
                }
            });

            $('.messager-input').attr('type', 'password');
        } else {
            Swal.close();
            toastr.warning("Please Select Period & Revision", "Information");
        }
    }

    function adds(){
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');

        $('#dg2').datagrid('loadData', []);

        $("#request_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#p_month").combobox('setValue', "<?= date("m") ?>");
        $("#p_year").combobox('setValue', "<?= date("Y") ?>");
        $("#revision").combobox('setValue', "0");
    }

    //INSERT ADD ROW
    function addTable(supplier_id, item_family_id) {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            columns: [
                [{
                    field: 'item_rm_id',
                    width: 120,
                    halign: 'center',
                    title: "Part No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('purchase/purchase_requests/readParts/') ?>' + btoa(supplier_id) + "/" + btoa(item_family_id),
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
                                    width: 120
                                },{
                                    field: 'number',
                                    title: 'Part No Supplier',
                                    width: 200
                                }, {
                                    field: 'name',
                                    title: 'Part Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'number'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'mpq'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'moq'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'share_order'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'leadtime'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });
                                var ed7 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });
                                var ed8 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });
                                var ed9 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'currency'
                                });

                                $(ed6.target).numberbox({
                                    onChange: function(val){
                                        var result = (Math.ceil(val / rows.mpq) * rows.mpq);
                                        
                                        
                                        $(ed6.target).numberbox('setValue', result);
                                        $(ed8.target).numberbox('setValue', Math.round(parseFloat(result) * parseFloat(rows.price), 2));
                                    }
                                });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).numberbox('setValue', rows.mpq);
                                $(ed3.target).numberbox('setValue', rows.moq);
                                $(ed4.target).numberbox('setValue', rows.share_order);
                                $(ed5.target).numberbox('setValue', rows.leadtime);
                                $(ed7.target).numberbox('setValue', rows.price);
                                $(ed9.target).textbox('setValue', rows.currency);
                            }
                        }
                    }
                }, {
                    field: 'number',
                    width: 200,
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
                            required: true,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'moq',
                    width: 80,
                    halign: 'center',
                    title: "MOQ",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'share_order',
                    width: 100,
                    halign: 'center',
                    title: "Share Order",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'leadtime',
                    width: 80,
                    halign: 'center',
                    title: "Leadtime",
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
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'currency',
                    width: 80,
                    halign: 'center',
                    title: "Currency",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true,
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
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'total',
                    width: 100,
                    halign: 'center',
                    title: "Total",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 200,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
                        }
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                editIndex = rowIndex;
                lastIndex = rowIndex;
            },
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
        var item_family_id = $("#item_family_id").combobox('getValue');
        if (item_family_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Product Family first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //Edit Data
    function update() {
        toastr.info("Not Ready this Function");
    }

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
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/purchase_requests/delete') ?>',
                                data: {
                                    request_no: row.request_no
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
                                    toastr.error("Cannot Process your request");
                                    Swal.close();
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

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('purchase/purchase_requests/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('purchase/purchase_requests/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('purchase/purchase_requests/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    function detailEdit(){
        var row = $('#dg_purchase_request').datagrid('getSelected');
        if (row) {
            $('#dlg_update').dialog('open');
            $('#frm_update').form('load', row);

            $('#update_request_no').combogrid({
                url: '<?= base_url('purchase/purchase_requests/readPr/') ?>' + btoa(row.supplier_id) + '/' + btoa(row.item_family_id),
                panelWidth: 500,
                idField: 'request_no',
                textField: 'request_no',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select Purchase Request No",
                columns: [
                    [{
                        field: 'request_no',
                        title: 'PR No',
                        width: 200
                    }, {
                        field: 'supplier_name',
                        title: 'Supplier Name',
                        width: 250
                    }]
                ]
            });

            $("#update_request_no").combogrid('setValue', row.request_no);

            $("#qty_edit").numberbox({
                onChange: function(val){
                    // if(parseInt(val) <= parseInt(row.purchase_order)){
                        if(parseInt(val) == 0){
                            var hasil = Math.ceil(parseInt(val));
                        }else{
                            if(parseInt(val) > row.moq){
                                var hasil = (Math.ceil(parseInt(val) / row.mpq) * row.mpq);
                            }else{
                                var hasil = (Math.ceil(parseInt(val) / row.moq) * row.moq);
                            }
                        }
                            
                        $("#qty").numberbox('setValue', hasil);
                    // }else{
                    //     toastr.error("Your Qty > Mrp Results");
                    //     $("#purchase_request").numberbox('setValue', row.purchase_order);
                    // }
                }
            });
            
            $('#status_data').textbox('setValue', "EDIT");
            // $('#p_month').combobox('disable');
            // $('#p_year').combobox('disable');
            // $('#revision').combobox('disable');
            // $('#supplier_id').combobox('disable');
            // $('#item_family_id').combobox('disable');
            // $('#part_no').combogrid('disable');
            // $('#part_name').textbox('disable');

            url_save = '<?= base_url('purchase/purchase_requests/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function details(request_no, supplier, item_family_id, push="") {
        $("#dlg_purchase_request").dialog('open');

        $("#d_request_no").textbox('setValue', request_no);
        $("#d_supplier").textbox('setValue', supplier);
        $("#d_item_family_id").textbox('setValue', item_family_id);

        var revision = $("#filter_revision").combobox('getValue');
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        $.ajax({
            type: "get",
            url: "<?= base_url('purchase/purchase_requests/readMonths') ?>",
            data: "month=" + filter_month + "&year=" + filter_year,
            dataType: "json",
            success: function(monthName) {
                $('#dg_purchase_request').datagrid({
                    url: '<?= base_url('purchase/purchase_requests/datatableDetails/') ?>' + btoa(request_no) + "/" + revision + "?filter_item_rm=" + btoa(filter_item_rm) + "&filter_month=" + btoa(filter_month) + "&filter_year=" + btoa(filter_year),
                    clientPaging: false,
                    remoteFilter: true,
                    rownumbers: true,
                    singleSelect: false,
                    fit:true,
                    columns: [
                        [{
                            field: 'ck',
                            checkbox: true,
                            rowspan: 2
                        }, {
                            field: 'item_rm_number',
                            title: 'Item ID',
                            width: 200,
                            halign: 'center',
                            rowspan: 2
                        }, {
                            field: 'item_rm_name',
                            title: 'Item Name',
                            width: 200,
                            halign: 'center',
                            rowspan: 2
                        }, {
                            field: 'share_order',
                            title: 'Share<br>Order',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2
                        }, {
                            field: 'mpq',
                            title: 'MPQ',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2
                        },{
                            field: 'moq',
                            title: 'MOQ',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2
                        },{
                            field: 'leadtime',
                            title: 'Leadtime',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2
                        },{
                            field: 'need',
                            title: 'Need',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        },{
                            field: 'qty',
                            title: 'Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        },{
                            field: '',
                            title: 'Purchase History',
                            width: 200,
                            align: 'center',
                            colspan: 3
                        }, {
                            field: 'avg',
                            title: 'Xbar',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        }, {
                            field: '',
                            title: 'Forecast History',
                            width: 200,
                            align: 'center',
                            colspan: 3
                        }, {
                            field: '',
                            title: 'Next Forecast',
                            width: 200,
                            align: 'center',
                            colspan: 3
                        }, {
                            field: '',
                            title: 'Forecast Supplier',
                            width: 100,
                            align: 'center',
                            colspan: 3
                        }, {
                            field: 'avg_forecast',
                            title: 'Xbar',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            width: 200,
                            halign: 'center',
                            rowspan: 2,
                        }, {
                            field: 'status_data',
                            title: 'Status',
                            width: 80,
                            align: 'center',
                            rowspan: 2,
                        }],[{
                            field: 'm3',
                            title: monthName[0],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'm2',
                            title: monthName[1],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'm1',
                            title: monthName[2],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_m3',
                            title: monthName[0],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_m2',
                            title: monthName[1],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_m1',
                            title: monthName[2],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_plus_1',
                            title: monthName[4],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_plus_2',
                            title: monthName[5],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_plus_3',
                            title: monthName[6],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'prodplan_1',
                            title: 'M 1',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'prodplan_2',
                            title: 'M 2',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'prodplan_3',
                            title: 'M 3',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }]
                    ]
                });
            }
        });
    }

    function sendPo(request_no) {
        $.messager.prompt('Generate PO', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
            if (r == "PURCHASEORDER"){
                Swal.fire({
                    title: 'Please Wait for Generating Data',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "get",
                    url: "<?= base_url('purchase/purchase_orders/getData') ?>",
                    data: "request_no=" + window.btoa(request_no) + "&id_menu=20240103000001",
                    dataType: "json",
                    success: function(rows) {
                        Swal.close();

                        if(rows['total'] > 0){
                            requestData(rows['total'], rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('purchase/purchase_orders/create2') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');

                                        if (result.theme == "success") {
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                            $("#dg").datagrid('reload');
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            toastr.error("Connection time out");

                                            requestData(total, json, number, value, success + 0, failed + 1);
                                        }
                                    });
                                }
                            }
                        }else{
                            toastr.warning("Data Purchase Requests Not Found");
                        }
                    },
                    error: function(rows) {
                        Swal.close();
                        Swal.fire('Failed!', 'Generate Purchase Requst is Failed, Please Try Again!', 'error');
                    }
                });
            }
        });
    }

    function checkConfirmPo(){
        $.ajax({
            type: "post",
            url: '<?= base_url('purchase/purchase_requests/datatableConfirm') ?>',
            dataType: "json",
            success: function (response) {
                if(response.total > 0){
                    $("#confirmPo").show();
                }else{
                    $("#confirmPo").hide();
                }
            }
        });
    }

    checkConfirmPo();
    setInterval(checkConfirmPo, 10000);

    function confirmPo() {
        $("#dlg_confirm").dialog('open');
        
        $('#dg_confirm').datagrid({
            url: '<?= base_url('purchase/purchase_requests/datatableConfirm') ?>',
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        });
    }

    function confirm(request_no, item_rm_id) {
        $.ajax({
            type: "post",
            url: '<?= base_url('purchase/purchase_requests/updateConfirm') ?>',
            data: "request_no=" + request_no + "&item_rm_id=" + item_rm_id,
            dataType: "json",
            success: function (response) {
                toastr.success("Data has been Confirmed");
                $("#dg_confirm").datagrid("reload");
            }
        });
    }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('purchase/purchase_requests/print_request/') ?>'+btoa(row.request_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
    }

    $(function() {
    	$("#add").html("Generate");

        $('#dg').datagrid({
            url: '<?= base_url('purchase/purchase_requests/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        filter();

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Send to Approval',
                iconCls: 'icon-ok',
                handler: function() {
                    var p_month = $("#p_month").combobox('getValue');
                    var p_year = $("#p_year").combobox('getValue');
                    var revision = $("#revision").combobox('getValue');
                    var supplier_id = $("#supplier_id").combobox('getValue');
                    var request_no = $("#request_no").textbox('getValue');
                    var attachment = $("#attachment").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var item_family_id = $("#item_family_id").combobox('getValue');

                    if (supplier_id == "" || item_family_id == "") {
                        toastr.error("please complete your input data");
                    } else {
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();
                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_rm_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('purchase/purchase_requests/createAdds') ?>',
                                    data: {
                                        p_month: p_month,
                                        p_year: p_year,
                                        revision: revision,
                                        request_no: request_no,
                                        request_date: request_date,
                                        supplier_id: supplier_id,
                                        attachment: attachment,
                                        item_family_id: item_family_id,
                                        item_rm_id: rows[i].item_rm_id,
                                        // mpq: rows[i].mpq,
                                        // moq: rows[i].moq,
                                        // share_order: rows[i].share_order,
                                        price: rows[i].price,
                                        total: rows[i].total,
                                        qty: rows[i].qty,
                                        remarks: rows[i].remarks,
                                        status_data: "MANUAL"
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            toastr.success(result.message, result.title);
                                        } else {
                                            toastr.error(result.message, result.title);
                                        }
                                    },
                                    error: function(){
                                        toastr.error("Duplicate Data");
                                    }
                                });
                            }
                        }
                        $('#dg').datagrid('reload');
                        $('#dlg_insert').dialog('close');
                    }
                }
            }]
        });

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Save',
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
                            $('#dg_purchase_request').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#attachment_upload').filebox({
            buttonText: 'Browse File',
            accept: '.jpg, .png, .pdf',
            onChange: function () {
                var files = $(this).filebox('files');
                var formData = new FormData();

                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    formData.append('file', file, file.name);
                }

                $.ajax({
                    url: '<?= base_url('purchase/purchase_requests/uploadatt') ?>',
                    type: 'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (data) {
                        if (data.success == true) {
                            toastr.success(data.message);
                            $('#attachment').textbox('setValue', data.filename); // Mengatur nilai pada textbox
                        } else {
                            toastr.error(data.message);
                        }
                    }
                });
            }
        });

        //ADD
        $('#p_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
        });

        $('#p_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
        });

        $('#revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Revision',
        });

        $('#supplier_id').combobox({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Supplier',
            onSelect: function(supp){
                $('#item_family_id').combobox({
                    url: '<?php echo base_url('purchase/purchase_requests/readprodfam/'); ?>' + btoa(supp.id),
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Select Product Family',
                    onSelect: function(prodfam){
                        addTable(supp.id, prodfam.id);

                        var p_month = $("#p_month").combobox('getValue');
                        var p_year = $("#p_year").combobox('getValue');
                        
                        // var code = "PR"+supp.number+p_year+p_month+"A"+prodfam.number;
                        
                        $.ajax({
                            type: "post",
                            // url: "<?= base_url("purchase/purchase_requests/purchaseRequestNo/") ?>"+btoa(code)+"/adds",
                            url: "<?= base_url("purchase/purchase_requests/purchaseRequestNo/".$id_menu."/adds") ?>",
                            dataType: "html",
                            success: function (response) {
                                if(response == ""){
                                    toastr.info("Please Check Configuration in Serial No First");
                                }else{
                                    $("#request_no").textbox('setValue', response);
                                }
                            }
                        });
                    }
                });
            }
        });

        //FILTER DATA
        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Revision',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_item_family').combobox({
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
            onSelect: function(row){
                $('#filter_item_rm').combogrid({
                    url: '<?= base_url('master/item_rm/reads/') ?>' + row.id,
                    panelWidth: 400,
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
                            field: 'number',
                            title: 'Part No',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Part Name',
                            width: 200
                        }]
                    ]
                });
            }
        });

        // $('#filter_item_rm').combogrid({
        //     url: '<?= base_url('purchase/generate_mrp/readParts') ?>',
        //     panelWidth: 400,
        //     idField: 'item_id',
        //     textField: 'item_id',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Select Part No",
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combogrid('clear').combogrid('textbox').focus();
        //         }
        //     }],
        //     columns: [
        //         [{
        //             field: 'item_id',
        //             title: 'Part No',
        //             width: 200
        //         }, {
        //             field: 'item_name',
        //             title: 'Part Name',
        //             width: 200
        //         }]
        //     ]
        // });
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

    function cellStyler(value, row, index) {
        if (value == "OPEN") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });

        return formatter.format(value);
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.request_no+"','"+row.supplier_name+"','"+row.item_family_id+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnSend(val, row) {
        if (row.approved_to == null || row.approved_to == "") {
            var send = "sendPo('"+row.request_no+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+send+'"><i class="fa fa-plus"></i> Create PO</a>';
        } else {
            return '-';
        }
    }

    function BtnConfirm(val, row) {
        var confirm = "confirm('"+row.request_no+"','"+row.item_rm_id+"')";
        return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+confirm+'"><i class="fa fa-check"></i> Confirm</a>';
    }

    function cellStylerConfirm(value, row, index) {
        if (value == 0) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterConfirm(val, row) {
        if (val == 0) {
            return 'Not Confirm';
        } else {
            return 'Confirmed';
        }
    };

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(row.approved_to == ""){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == null) {
            return 'Not Setting';
        } else if(row.approved_to == ""){
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function btnDetails(val, row, index) {
        var attachment = row.attachment;
        
        if (attachment != null && attachment != "") {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('assets/documents/purchase_requests/') ?>'+row.attachment+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
        } else {
            return '-';
        }
    }
</script>