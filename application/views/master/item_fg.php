<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'foto',width:35,halign:'center',formatter:FormatterImage">Foto</th>
            <th data-options="field:'number',width:150,halign:'center'">Product No EBWS</th>
            <th data-options="field:'name',width:150,halign:'center'">Description</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'number_customer',width:150,halign:'center'">Part No Customer</th>
            <th rowspan="2" data-options="field:'alias',width:150,halign:'center'">Product No Invoice</th>
            <th rowspan="2" data-options="field:'cct',width:80,halign:'center', styler:cellStylerCct">CCT Qty</th>
            <th rowspan="2" data-options="field:'customer_name',width:250,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'harnes_type_name',width:100,halign:'center'">Harness Type</th>
            <th rowspan="2" data-options="field:'control_id',width:100,halign:'center'">Control No</th>
            <th rowspan="2" data-options="field:'polybag',width:150,halign:'center'">Polybag Label</th>
            <th rowspan="2" data-options="field:'box_label',width:70,align:'center'">Box Label</th>
            <th rowspan="2" data-options="field:'lot',width:80,align:'center'">Lot<br>Production</th>
            <!-- <th rowspan="2" data-options="field:'ng_ration',width:90,align:'center'">NG Ratio (%)</th> -->
            <!-- <th rowspan="2" data-options="field:'is_no',width:100,halign:'center'">IS No.</th> -->
            <th rowspan="2" data-options="field:'weight',width:100,align:'center'">Weight (gram)</th>
            <th rowspan="2" data-options="field:'leadtime',width:100,align:'center'">Prod. Leadtime (day)</th>
            <th rowspan="2" data-options="field:'mpq',width:50,align:'center'">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:50,align:'center'">MOQ</th>
            <th rowspan="2" data-options="field:'uom',width:50,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty_box',width:80,align:'center'">QTY/Box</th>
            <th rowspan="2" data-options="field:'safety_stock',width:100,align:'center'">Safety Stock %</th>
            <!-- <th rowspan="2" data-options="field:'box_sub',width:80,align:'center'">QTY/Sub Box</th> -->
            <th rowspan="2" data-options="field:'hsn_code',width:100,halign:'center'">HSN No</th>
            <th rowspan="2" data-options="field:'type',width:100,align:'center'">Type</th>
            <th rowspan="2" data-options="field:'account_number',width:150,halign:'center'">COA No</th>
            <th rowspan="2" data-options="field:'account_name',width:150,halign:'center'">COA Name</th>
            <th rowspan="2" data-options="field:'attachment',width:100,halign:'center',formatter:cellbutton">Attachment</th>
            <th rowspan="2" data-options="field:'logo',width:100,align:'center', styler:cellStyler, formatter:cellFormatterLogo">Logo</th>
            <th rowspan="2" data-options="field:'revision',width:80,halign:'center'">Revision</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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
<div id="toolbar" style="height: 35px;">
    <a href="javascript:;" class="easyui-linkbutton" id="sync" data-options="plain:true" onclick="sync()"><i class="fa fa-history"></i> Push Data</a>
    <?= $button ?>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 95%; padding:10px; top: 10px; left: 10px;">
    <form id="frm_insert" method="post" novalidate enctype="multipart/form-data">
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>

            <div style="float:left; width:33%;">
                <center>
                    <img id="fotoPreview" src="<?= base_url('assets/image/users/default.png') ?>" width="155">
                    <br><br>
                </center>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Foto</span>
                    <input style="width:60%;" name="foto" id="foto" class="easyui-filebox" data-options="prompt:'Choose Foto Product', accept:'.png, .jpg, .jpeg'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No EBWS</span>
                    <input style="width:50%;" name="number" id="number" readonly required="" class="easyui-textbox">
                    <a href="javascript:;" class="easyui-linkbutton" title="Generate ID" data-options="plain:false" onclick="autoid()"><i class="fa fa-history"></i></a>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Description</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No Customer</span>
                    <input style="width:60%;" name="number_customer" id="number_customer" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">CCT Qty</span>
                    <input style="width:60%;" name="cct" id="cct" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No Invoice</span>
                    <input style="width:60%;" name="alias" id="alias" class="easyui-textbox">
                </div>
            </div>

            <div style="float:left; width:33%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required class="easyui-combogrid">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Customer No</span>
                    <input style="width:60%;" name="customer_number" id="customer_number" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" name="type" id="type" required="" class="easyui-combobox" panelHeight="auto">
                        <option value="LOCAL">LOCAL</option>
                        <option value="EXPORT">EXPORT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Harness Type</span>
                    <input style="width:60%;" name="harnes_type" id="harnes_type" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">HSN No</span>
                    <input style="width:60%;" name="hsn_code" id="hsn_code" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Control No</span>
                    <input style="width:60%;" name="control_id" id="control_id" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lot Production (MPP)</span>
                    <input style="width:60%;" name="lot" id="lot" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Polybag</span>
                    <input style="width:60%;" name="polybag" id="polybag" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Box Label</span>
                    <select style="width:60%;" name="box_label" id="box_label" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="YES">YES</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">NG Ratio</span>
                    <input style="width:30%;" name="ng_ration" id="ng_ration" data-options="buttonText:'%',buttonAlign:'right'" precision="5" class="easyui-numberbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">IS No.</span>
                    <input style="width:60%;" name="is_no" id="is_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Weight</span>
                    <input style="width:40%;" name="weight" id="weight" precision="2" data-options="buttonText:'Gram',buttonAlign:'right'" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Prod. Leadtime</span>
                    <input style="width:60%;" name="leadtime" id="leadtime" data-options="buttonText:'Day',buttonAlign:'right'" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Safety Stock</span>
                    <input style="width:30%;" name="safety_stock" id="safety_stock" data-options="buttonText:'%',buttonAlign:'right', precision:2" class="easyui-numberbox">
                </div>
            </div>
            <div style="float:left; width:33%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ</span>
                    <input style="width:60%;" name="mpq" id="mpq" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MOQ</span>
                    <input style="width:60%;" name="moq" id="moq" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">UoM</span>
                    <input style="width:60%;" name="uom" id="uom" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lot Qty/Box</span>
                    <input style="width:60%;" name="qty_box" id="qty_box" class="easyui-numberbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Qty/Sub Box</span>
                    <input style="width:60%;" name="box_sub" id="box_sub" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">COA No</span>
                    <input style="width:60%;" name="account_number" id="account_number" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">COA Name</span>
                    <input style="width:60%;" name="account_name" id="account_name" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Logo</span>
                    <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">YES</option>
                        <option value="1">NO</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" name="revision" id="revision" required panelHeight="auto" class="easyui-combobox">
                        <option value="00">00</option>
                        <option value="01">01</option>
                        <option value="02">02</option>
                        <option value="03">03</option>
                        <option value="04">04</option>
                        <option value="05">05</option>
                        <option value="06">06</option>
                        <option value="07">07</option>
                        <option value="08">08</option>
                        <option value="09">09</option>
                        <option value="10">10</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">Active</option>
                        <option value="1">Not Active</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- Sync -->
<div id="dlg_sync" class="easyui-dialog" title="Sync Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks2" title="History Sync" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks2">
        </ul>
    </div>
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
<iframe id="printout" src="<?= base_url('master/item_fg/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    function sync() {
        $.messager.confirm('Warning', 'Are you sure you want to push this data?', function(r) {
            if (r) {
                Swal.fire({
                    title: 'Get Data from Database ERP',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "get",
                    url: "<?= base_url('master/item_fg/getSync') ?>",
                    dataType: "json",
                    success: function(rows) {
                        Swal.close();
                        $("#dlg_sync").dialog('open');
                        $("#sync").linkbutton("disable");

                        if(rows['total'] > 0){
                            requestData(rows['total'], rows);
                            function requestData(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload2').progressbar('setValue', value);
                                    $('#p_start2').html(number);
                                    $('#p_finish2').html(total);

                                    $.post('<?= base_url('master/item_fg/createSync') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        requestData(total, json, number + 1, value);

                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                        $("#p_remarks2").append(title + "<br>");

                                        if (value == 100) {
                                            // Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                            $("#sync").linkbutton("enable");
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            Swal.fire({
                                                title: 'Connection Time Out, Check Your Connection',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            requestData(total, json, number, value);
                                        }
                                    });
                                }
                            }
                        }else{
                            Swal.close();
                            Swal.fire('Failed!', 'Data to Push data is 0', 'error');
                        }
                    },
                    error: function(){
                        Swal.close();
                        Swal.fire('Failed!', 'Not Connected in MRP Banshu', 'error');
                    }
                });
            }
        });
    }

    function autoid(){
        var customer_number = $("#customer_number").textbox('getValue');
        var harnes_type = $("#harnes_type").combogrid('getValue');
        var revision = $("#revision").combobox('getValue');

        if(customer_number != "" && harnes_type != "" && revision != ""){
            $.ajax({
                type : "post",
                url : "<?= base_url('master/item_fg/autoid')?>",
                data: {
                    "customer_number" : customer_number,
                    "harnes_type" : harnes_type,
                    "revision" : revision
                },
                dataType : "json",
                success : function(response){
                    $('#number').textbox('setValue', response.code);
                }
            });
        }else{
            toastr.info("Please Choose Customer, Harnes Type and Revision");
        }
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_fg/create') ?>';
        $('#frm_insert').form('clear');

        $('#polybag').combobox('setValue', 'Label Manual Logo BPI');
        $('#box_label').combobox('setValue', 'YES');
        $('#status').combobox('setValue', '0');
        $('#type').combobox('setValue', 'FG');
        $('#account_number').combogrid('setValue', '30000001');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');

        setTimeout(function() { 
            $('#id').textbox('setValue', row.id);
        }, 500);

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            // $('#id').textbox('disable');
            if(row.foto != "" && row.foto != null){
                $('#fotoPreview').attr('src', row.foto);
            }else{
                $('#fotoPreview').attr('src', '<?= base_url("assets/image/users/default.png") ?>');
            }

            url_save = '<?= base_url('master/item_fg/update') ?>?id=' + btoa(row.id);
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
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('master/item_fg/delete') ?>',
                                data: {
                                    id: row.id
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
                                    toastr.error("Delete Failed, Already Relation in another module");
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
        window.location.assign('<?= base_url('template/tmp_item_fg.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/item_fg/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_fg/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Send to Approval',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            if($(this).form('validate') === true){
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
                            }else{
                                return $(this).form('validate');
                            }
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg').datagrid('reload');
                            Swal.close();
                        }
                    });
                }
            }]
        });
    });

    $('#account_number').combogrid({
        url: '<?= base_url('finance/account_coa/reads'); ?>',
        panelWidth: 300,
        idField: 'account_number',
        textField: 'account_number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose COA Number",
        columns: [
            [{
                field: 'account_number',
                title: 'COA Number',
                width: 100
            }, {
                field: 'account_name',
                title: 'COA Name',
                width: 200
            }]
        ],
        onSelect: function(val, coa){
            $("#account_name").textbox('setValue', coa.account_name);
        }
    });

    $('#hsn_code').combogrid({
        url: '<?= base_url('master/hsn/reads'); ?>',
        panelWidth: 600,
        idField: 'number',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose HSN",
        columns: [
            [{
                field: 'number',
                title: 'HSN Number',
                width: 100
            }, {
                field: 'name',
                title: 'HSN Name',
                width: 200
            }, {
                field: 'igst',
                title: 'IGST %',
                width: 80
            }, {
                field: 'sgst',
                title: 'SGST %',
                width: 80
            }, {
                field: 'cgst',
                title: 'CGST %',
                width: 80
            }]
        ],
    });

    $('#harnes_type').combogrid({
        url: '<?= base_url('master/harnes_types/reads'); ?>',
        panelWidth: 350,
        idField: 'number',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Harnes Type",
        columns: [
            [{
                field: 'number',
                title: 'Harness Code',
                width: 100
            }, {
                field: 'name',
                title: 'Harness Name',
                width: 200
            }]
        ],
    });

    $('#customer_id').combogrid({
        url: '<?= base_url('master/customers/reads'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'number',
                title: 'Customer Code',
                width: 100
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 250
            }]
        ],
        onSelect: function(val, row){
            $("#customer_number").textbox('setValue', row.number);
        }
    });

    $('#uom').combobox({
        url: '<?= base_url('master/uom/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Unit of Measure',
    });

    $('#foto').filebox({
        onChange: function(value) {
            var id = $(this).filebox('options').fileboxId;
            var files = $('#' + id)[0].files;
            if (files.length) {
                var url = window.URL.createObjectURL(files[0]);
                $('#fotoPreview').attr('src', url);
            }
        }
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //CELLSTYLE CCT
    function cellStylerCct(value, row, index) {
        if (row.total_cct == row.cct) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };

     //FORMATTER LOGO
     function cellFormatterLogo(value) {
        if (value == 0) {
            return 'YES';
        } else {
            return 'NO';
        }
    };

    function FormatterImage(val) {
        if (val == "" || val == null) {
            return '<img style="width: 100%;" src="<?= base_url('assets/image/users/default.png'); ?>" />';
        } else {
            return '<a href="' + val + '" target="_blank"><img style="width: 100%;" src="' + val + '" /></a>';
        }
    };

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

    //
    function cellbutton(value) {
        if (value != null) {
            return '<a target="_blank" href="' + value + '" class="btn btn-primary btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-eye"></i> View</a>';
            // alert(value);
        }
    };

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/item_fg/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/item_fg/upload') ?>',
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
                            url: "<?= base_url('master/item_fg/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/item_fg/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/item_fg/uploadcreateFailed') ?>",
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
</script>