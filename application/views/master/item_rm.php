<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'foto',width:35,halign:'center',formatter:FormatterImage">Foto</th>
        <th data-options="field:'id',width:120,halign:'center'">Part No EBWS</th>
        <th data-options="field:'number',width:200,halign:'center'">Part No Supplier</th>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'name',width:150,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'uom',width:100,halign:'center'">Uom</th>
            <th rowspan="2" data-options="field:'item_category_name',width:150,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'item_family_name',width:150,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'maker_name',width:150,halign:'center'">Maker Name</th>
            <th rowspan="2" data-options="field:'type',width:100,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'hsn_number',width:100,halign:'center'">HSN No</th>
            <th rowspan="2" data-options="field:'igst',width:100,halign:'center'">IGST %</th>
            <th rowspan="2" data-options="field:'sgst',width:100,halign:'center'">SGST %</th>
            <th rowspan="2" data-options="field:'cgst',width:100,halign:'center'">CGST %</th>
            <th rowspan="2" data-options="field:'account_number',width:150,halign:'center'">COA No</th>
            <th rowspan="2" data-options="field:'account_name',width:150,halign:'center'">COA Name</th>
            <th rowspan="2" data-options="field:'description',width:150,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'supply',width:80,align:'center', styler:cellStyler, formatter:cellFormatterSup">Supply</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; height: 550px; padding:10px; top: 10px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <div style="width: 100%; float: left;">
            <div style="width: 100%; float: left;">
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Require Data</b></legend>
                    <div style="width: 80%; float: left;">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Category</span>
                            <input style="width:60%;" name="item_category_id" id="item_category_id" required="" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Product Family</span>
                            <input style="width:60%;" name="item_family_id" id="item_family_id" required="" class="easyui-combobox">
                        </div>
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Product Family No</span>
                            <input style="width:60%;" name="item_family_number" id="item_family_number" disabled class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Foto</span>
                            <input style="width:60%;" name="foto" id="foto" class="easyui-filebox" data-options="prompt:'Choose Foto Part', accept:'.png, .jpg, .jpeg'">
                        </div>
                    </div>
                    <div style="width: 20%; float: left;">
                        <center>
                            <img id="fotoPreview" src="<?= base_url('assets/image/users/default.png') ?>" width="100"><br>
                        </center>
                    </div>
                </fieldset>
            </div>
            <div style="width: 50%; float: left;">
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Digital Code Data</b></legend>
                    <div class="fitem" id="t_kind">
                        <span style="width:35%; display:inline-block;">Kind</span>
                        <input style="width:60%;" name="kind" id="kind" class="easyui-combogrid">
                    </div>
                    <div class="fitem" id="t_size">
                        <span style="width:35%; display:inline-block;">Size</span>
                        <input style="width:60%;" name="size" id="size" class="easyui-combogrid">
                    </div>
                    <div class="fitem" id="t_color_1">
                        <span style="width:35%; display:inline-block;">Color 1</span>
                        <input style="width:60%;" name="color_1" id="color_1" class="easyui-combogrid">
                    </div>
                    <div class="fitem" id="t_color_2">
                        <span style="width:35%; display:inline-block;">Color 2</span>
                        <input style="width:60%;" name="color_2" id="color_2" class="easyui-combogrid">
                    </div>

                    <!-- Terminal -->
                    <div class="fitem" id="t_male">
                        <span style="width:35%; display:inline-block;">Male/Female/LA</span>
                        <select style="width:60%;" name="male" id="male" panelHeight="auto" class="easyui-combobox">
                            <option value="1">MALE</option>
                            <option value="2">FEMALE</option>
                            <option value="3">LA</option>
                            <option value="4">JT</option>
                            <option value="5">FB</option>
                            <option value="6">BATT</option>
                        </select>
                    </div>
                    <div class="fitem" id="t_waterproof">
                        <span style="width:35%; display:inline-block;">Water Proof</span>
                        <select style="width:60%;" name="waterproof" id="waterproof" panelHeight="auto" class="easyui-combobox">
                            <option value="1">NON WP</option>
                            <option value="2">WP</option>
                        </select>
                    </div>
                    <div class="fitem" id="t_platting">
                        <span style="width:35%; display:inline-block;">Platting</span>
                        <input style="width:60%;" name="platting" id="platting" class="easyui-combogrid">
                    </div>
                    <!---------------------->

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Type</span>
                        <select style="width:60%;" name="type" id="type" required="" panelHeight="auto" class="easyui-combobox">
                            <option value="IMPORT">IMPORT</option>
                            <option value="LOCAL">LOCAL</option>
                        </select>
                    </div>
                    
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Part No EBWS</span>
                        <input style="width:50%;" name="id" id="id" required="" class="easyui-textbox" data-options="prompt:'Automatic'" readonly>
                        <a href="javascript:;" class="easyui-linkbutton" title="Generate ID" data-options="plain:false" onclick="autoid()"><i class="fa fa-history"></i></a>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Part No Supplier</span>
                        <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Maker</span>
                        <input style="width:60%;" name="maker_id" id="maker_id" required="" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Description</span>
                        <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                    </div>
                </fieldset>
            </div>
            <div style="width: 50%; float: left;">
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Additional Data</b></legend>
                    <!-- <div class="fitem">
                        <span style="width:35%; display:inline-block;">Foto</span>
                        <input style="width:60%;" name="foto" id="foto" class="easyui-filebox" data-options="prompt:'Choose Foto Part', accept:'.png, .jpg, .jpeg'">
                    </div> -->
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Unit of Measure</span>
                        <input style="width:60%;" name="uom" id="uom" required="" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">HSN No</span>
                        <input style="width:60%;" name="hsn_code" id="hsn_code" required class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">COA No</span>
                        <input style="width:60%;" name="account_number" id="account_number" class="easyui-textbox" readonly>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">COA Name</span>
                        <input style="width:60%;" name="account_name" id="account_name" class="easyui-textbox" readonly>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%;" name="description" id="description" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supply</span>
                        <select style="width:30%;" name="supply" id="supply" required="" panelHeight="auto" class="easyui-combobox">
                            <option value="0">YES</option>
                            <option value="1">NO</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Status</span>
                        <select style="width:30%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                            <option value="0">Active</option>
                            <option value="1">Not Active</option>
                        </select>
                    </div>
                </fieldset>
            </div>
        </div>
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
<iframe id="printout" src="<?= base_url('master/item_rm/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    function sync() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Send Data to MRP Banshu',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            requestData(rows.length, rows);
            function requestData(total, json, number = 1, value = 0) {
                if (value < 100) {
                    value = Math.floor((number / total) * 100);
                    $('#p_upload2').progressbar('setValue', value);
                    $('#p_start2').html(number);
                    $('#p_finish2').html(total);

                    $.post('<?= base_url('master/item_rm/createSync') ?>', {
                        data: json[number - 1]
                    }, function(note) {
                        var result = eval('(' + note + ')');
                        requestData(total, json, number + 1, value);

                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                        $("#p_remarks2").append(title + "<br>");

                        if (value == 100) {
                            Swal.close();
                            Swal.fire('Good job!', 'Process Send Data Completed!', 'success');
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
            toastr.warning("Please Select data in the table first");
        }
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_rm/create') ?>';
        method = 'ADD';
        $('#frm_insert').form('clear');
        
        $('#status').combobox('setValue', '0');
        $("#t_kind").hide();
        $("#t_size").hide();
        $("#t_color_1").hide();
        $("#t_color_2").hide();
        $("#t_male").hide();
        $("#t_waterproof").hide();
        $("#t_platting").hide();

        $("#kind").combogrid('enable');
        $("#size").combogrid('enable');
        $("#color_1").combogrid('enable');
        $("#color_2").combogrid('enable');
        $("#male").combogrid('enable');
        $("#waterproof").combogrid('enable');
        $("#platting").combogrid('enable');
        $("#type").combogrid('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            if(row.foto != "" && row.foto != null){
                $('#fotoPreview').attr('src', row.foto);
            }else{
                $('#fotoPreview').attr('src', '<?= base_url("assets/image/users/default.png") ?>');
            }

            $('#kind').combogrid({
                url: '<?= base_url('master/item_kinds/reads/'); ?>' + row.item_family_id,
                panelWidth: 300,
                idField: 'number',
                textField: 'name',
                mode: 'remote',
                fitColumns: true,
                prompt: "Choose Kind",
                onLoadSuccess: function(){
                    $("#kind").combogrid('setValue', row.kind);
                },
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
            });

            $('#size').combogrid({
                url: '<?= base_url('master/item_sizes/reads/'); ?>' + row.item_family_id,
                panelWidth: 300,
                idField: 'number',
                textField: 'name',
                mode: 'remote',
                fitColumns: true,
                prompt: "Choose Size",
                onLoadSuccess: function(){
                    $("#size").combogrid('setValue', row.size);
                },
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
            });

            $("#kind").combogrid('disable');
            $("#size").combogrid('disable');
            $("#color_1").combogrid('disable');
            $("#color_2").combogrid('disable');
            $("#male").combogrid('disable');
            $("#waterproof").combogrid('disable');
            $("#platting").combogrid('disable');
            $("#type").combogrid('disable');

            url_save = '<?= base_url('master/item_rm/update') ?>?id=' + btoa(row.id);
            method = 'EDIT';
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
                                url: '<?= base_url('master/item_rm/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_item_rm.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/item_rm/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function autoid(){
        if(method == "EDIT"){
            toastr.error("You cannot change the ebws part number for the update function");
            return false;
        }

        var item_family_number = $("#item_family_number").textbox('getValue');
        var kind = $("#kind").combogrid('getValue');
        var size = $("#size").combogrid('getValue');
        var color_1 = $("#color_1").combogrid('getValue');
        var color_2 = $("#color_2").combogrid('getValue');
        var male = $("#male").combogrid('getValue');
        var waterproof = $("#waterproof").combogrid('getValue');
        var platting = $("#platting").combogrid('getValue');
        var type = $("#type").combogrid('getValue');

        if(item_family_number != "" || type != ""){
            $.ajax({
                type : "post",
                url : "<?= base_url('master/item_rm/autoid')?>",
                data: {
                    "item_family_number" : item_family_number,
                    "kind" : kind,
                    "size" : size,
                    "color_1" : color_1,
                    "color_2" : color_2,
                    "male" : male,
                    "waterproof" : waterproof,
                    "platting" : platting,
                    "type" : type,
                },
                dataType : "json",
                success : function(response){
                    if(response.code.length == 10){
                        $('#id').textbox('setValue', response.code);
                    }else{
                        toastr.info("Please complete your digital code data");
                        $('#id').textbox('clear');
                    }
                }
            });
        }else{
            toastr.info("Please Choose Product Family and Type");
        }
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_rm/datatables') ?>',
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
                    var hsn_code = $("#hsn_code").combogrid('getValue');

                    if(hsn_code.length >= 6){
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
                    }else{
                        toastr.error("Please Input HSN Code Correctly");
                    }
                }
            }]
        });
    });

    $('#uom').combobox({
        url:'<?= base_url('master/uom/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Unit of Measure',
    });

    $('#maker_id').combobox({
        url:'<?= base_url('master/makers/reads'); ?>',
        valueField:'id',
        textField:'name',
        prompt: 'Choose Maker',
    });

    $('#color_1').combogrid({
        url: '<?= base_url('master/item_colors/reads'); ?>',
        panelWidth: 300,
        idField: 'number',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Color",
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
    });

    $('#color_2').combogrid({
        url: '<?= base_url('master/item_colors/reads'); ?>',
        panelWidth: 300,
        idField: 'number',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Color",
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
    });

    $('#platting').combogrid({
        url: '<?= base_url('master/item_platting/reads'); ?>',
        panelWidth: 300,
        idField: 'number',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Platting",
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
    });

    $('#item_category_id').combobox({
        url:'<?= base_url('master/item_categories/reads/FG'); ?>',
        valueField:'id',
        textField:'name',
        prompt: 'Choose Category',
        onSelect: function(category){
            $('#item_family_id').combobox({
                url:'<?= base_url('master/item_familys/reads/'); ?>' + category.id,
                valueField:'id',
                textField:'name',
                prompt: 'Choose Product Family',
                onSelect: function(family) {
                    var arrFamily = ["7","8","9","10","11","12","13","14","15","16","17","18","19"];

                    if(category.number == "RM"){
                        //Wire & Tape
                        if(family.number == "1" || family.number == "4"){
                            $("#t_kind").show();
                            $("#t_size").show();
                            $("#t_color_1").show();
                            $("#t_color_2").show();
                            $("#t_male").hide();
                            $("#t_waterproof").hide();
                            $("#t_platting").hide();
                        //Accesoris
                        }else if ($.inArray(family.number, arrFamily) != -1){
                            $("#t_kind").show();
                            $("#t_size").hide();
                            $("#t_color_1").show();
                            $("#t_color_2").hide();
                            $("#t_male").hide();
                            $("#t_waterproof").hide();
                            $("#t_platting").hide();
                        //Terminal
                        }else if(family.number == "2"){
                            $("#t_kind").hide();
                            $("#t_size").hide();
                            $("#t_color_1").hide();
                            $("#t_color_2").hide();
                            $("#t_male").show();
                            $("#t_waterproof").show();
                            $("#t_platting").show();
                        //Connector
                        }else if(family.number == "3"){
                            $("#t_kind").hide();
                            $("#t_size").hide();
                            $("#t_color_1").show();
                            $("#t_color_2").hide();
                            $("#t_male").show();
                            $("#t_waterproof").show();
                            $("#t_platting").hide();
                        //Fuse
                        }else if(family.number == "5" || family.number == "6"){
                            $("#t_kind").show();
                            $("#t_size").show();
                            $("#t_color_1").show();
                            $("#t_color_2").hide();
                            $("#t_male").hide();
                            $("#t_waterproof").hide();
                            $("#t_platting").hide();
                        }else{
                            $("#t_kind").hide();
                            $("#t_size").hide();
                            $("#t_color_1").hide();
                            $("#t_color_2").hide();
                            $("#t_male").hide();
                            $("#t_waterproof").hide();
                            $("#t_platting").hide();
                        }
                    }else{
                        $("#t_kind").hide();
                        $("#t_size").hide();
                        $("#t_color_1").hide();
                        $("#t_color_2").hide();
                        $("#t_male").hide();
                        $("#t_waterproof").hide();
                        $("#t_platting").hide();
                    }

                    $("#item_family_number").textbox('setValue', family.number);
                    $("#account_number").textbox('setValue', family.account_number);
                    $("#account_name").textbox('setValue', family.account_name);

                    $('#kind').combogrid({
                        url: '<?= base_url('master/item_kinds/reads/'); ?>' + family.id,
                        panelWidth: 300,
                        idField: 'number',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Kind",
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
                    });

                    $('#size').combogrid({
                        url: '<?= base_url('master/item_sizes/reads/'); ?>' + family.id,
                        panelWidth: 300,
                        idField: 'number',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Size",
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
                    });
                }
            });
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
                width: 150
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
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };
     //FORMATTER STATUS
     function cellFormatterSup(value) {
        if (value == 0) {
            return 'YES';
        } else {
            return 'NO';
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

    function FormatterImage(val) {
        if (val == "" || val == null) {
            return '<img style="width: 100%;" src="<?= base_url('assets/image/users/default.png'); ?>" />';
        } else {
            return '<a href="' + val + '" target="_blank"><img style="width: 100%;" src="' + val + '" /></a>';
        }
    };

    // UPLOAD DATA
    $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/item_rm/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/item_rm/upload') ?>',
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
                                url: "<?= base_url('master/item_rm/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/item_rm/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/item_rm/uploadcreateFailed') ?>",
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