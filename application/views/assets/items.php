<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'foto',width:35,halign:'center',formatter:FormatterImage">Foto</th>
        <th data-options="field:'id',width:100,halign:'center'">Assets No</th>
        <th data-options="field:'number',width:200,halign:'center'">Assets Name</th>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'uom',width:100,halign:'center'">Uom</th>
            <!-- <th rowspan="2" data-options="field:'item_category_name',width:150,halign:'center'">Category</th> -->
            <th rowspan="2" data-options="field:'item_family_name',width:150,halign:'center'">Asset Category</th>
            <th rowspan="2" data-options="field:'maker_name',width:150,halign:'center'">Maker Name</th>
            <th rowspan="2" data-options="field:'type',width:100,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'hsn_number',width:100,halign:'center'">HSN No</th>
            <th rowspan="2" data-options="field:'igst',width:100,halign:'center'">IGST %</th>
            <th rowspan="2" data-options="field:'sgst',width:100,halign:'center'">SGST %</th>
            <th rowspan="2" data-options="field:'cgst',width:100,halign:'center'">CGST %</th>
            <th rowspan="2" data-options="field:'account_number',width:150,halign:'center'">COA No</th>
            <th rowspan="2" data-options="field:'account_name',width:150,halign:'center'">COA Name</th>
            <th rowspan="2" data-options="field:'description',width:150,halign:'center'">Remarks</th>
            <!-- <th rowspan="2" data-options="field:'supply',width:80,align:'center', styler:cellStyler, formatter:cellFormatterSup">Supply</th> -->
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
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Category</span>
                            <input style="width:60%;" name="item_category_id" id="item_category_id" required="" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Asset Category</span>
                            <input style="width:60%;" name="item_family_id" id="item_family_id" required="" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Status</span>
                            <select style="width:30%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                                <option value="0">Active</option>
                                <option value="1">Not Active</option>
                            </select>
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
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Type</span>
                        <select style="width:60%;" name="type" id="type" required="" panelHeight="auto" class="easyui-combobox">
                            <option value="IMPORT">IMPORT</option>
                            <option value="LOCAL">LOCAL</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Asset No</span>
                        <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" data-options="prompt:'Automatic'" readonly>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Asset Name</span>
                        <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Description</span>
                        <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Maker</span>
                        <input style="width:60%;" name="maker_id" id="maker_id" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Departement</span>
                        <input style="width:60%;" name="departement" id="departement" required=""  class="easyui-textbox">
                    </div>
                </fieldset>
            </div>
            <div style="width: 50%; float: left;">
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Additional Data</b></legend>
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
                        <input style="width:60%;" name="account_number" id="account_number" class="easyui-textbox" readonly required>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">COA Name</span>
                        <input style="width:60%;" name="account_name" id="account_name" class="easyui-textbox" readonly required>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%;" name="description" id="description" class="easyui-textbox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Supply</span>
                        <select style="width:30%;" name="supply" id="supply" required="" panelHeight="auto" class="easyui-combobox">
                            <option value="0">YES</option>
                            <option value="1">NO</option>
                        </select>
                    </div>
                </fieldset>
            </div>
        </div>
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

<!-- PDF -->
<iframe id="printout" src="<?= base_url('assets/items/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    function autoid(item_family_number){

        if(item_family_number != ""){
            $.ajax({
                type : "post",
                url : "<?= base_url('assets/items/autoid')?>",
                data: {
                    "item_family_number" : item_family_number
                },
                dataType : "json",
                success : function(response){
                    $('#id').textbox('setValue', response.code);
                }
            });
        }else{
            toastr.info("Please Choose Product Family");
        }
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('assets/items/create') ?>';
        $('#frm_insert').form('clear');
        
        $('#status').combobox('setValue', '0');
        $('#supply').combobox('setValue', '1');
        $('#type').combobox('setValue', 'LOCAL');
        $('#item_category_id').combobox('enable');
        $('#item_family_id').combobox('enable');

        $('#item_category_id').combobox({
            url:'<?= base_url('master/item_categories/readAsset/FA'); ?>',
            valueField:'id',
            textField:'name',
            prompt: 'Choose Category',
            onLoadSuccess: function(loadCat){
                $('#item_category_id').combobox('setValue', loadCat[0].id);
            },
            onSelect: function(category){
                $('#item_family_id').combobox({
                    url:'<?= base_url('master/item_familys/reads/'); ?>' + category.id,
                    valueField:'id',
                    textField:'name',
                    prompt: 'Choose Product Family',
                    onSelect: function(family) {
                        autoid(family.number);
                        $("#account_number").textbox('setValue', family.account_number);
                        $("#account_name").textbox('setValue', family.account_name);
                    }
                });
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            
            $('#item_category_id').combobox({
                url:'<?= base_url('master/item_categories/readAsset/FA'); ?>',
                valueField:'id',
                textField:'name',
                prompt: 'Choose Category',
                onLoadSuccess: function(loadCat){
                    $('#item_category_id').combobox('setValue', loadCat[0].id);
                },
                onSelect: function(category){
                    $('#item_family_id').combobox({
                        url:'<?= base_url('master/item_familys/reads/'); ?>' + category.id,
                        valueField:'id',
                        textField:'name',
                        prompt: 'Choose Product Family',
                        onLoadSuccess: function(){
                            $("#item_family_id").combobox('setValue', row.item_family_id);
                        },
                        onSelect: function(family) {
                            $("#account_number").textbox('setValue', family.account_number);
                            $("#account_name").textbox('setValue', family.account_name);
                        }
                    });
                }
            });

            $('#item_category_id').combobox('disable');
            $('#item_family_id').combobox('disable');

            if(row.foto != "" && row.foto != null){
                $('#fotoPreview').attr('src', row.foto);
            }else{
                $('#fotoPreview').attr('src', '<?= base_url("assets/image/users/default.png") ?>');
            }

            url_save = '<?= base_url('assets/items/update') ?>?id=' + btoa(row.id);
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
                                url: '<?= base_url('assets/items/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_master_assets.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('assets/items/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('assets/items/datatables') ?>',
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
                    window.open('<?= base_url('assets/items/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('assets/items/upload') ?>',
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
                                url: "<?= base_url('assets/items/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('assets/items/uploadCreate') ?>",
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
                                                    url: "<?= base_url('assets/items/uploadcreateFailed') ?>",
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