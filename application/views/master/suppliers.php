<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'id',width:100,align:'center'">Supplier ID</th>
            <th data-options="field:'supplier_po',width:150,align:'center'">Supplier<br>PO</th>
            <th data-options="field:'name',width:250,halign:'center'">Supplier Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'number',width:80,align:'center'">Supplier<br>Code</th>
            <th rowspan="2" data-options="field:'supplier_type',width:100,align:'center'">Supplier<br>Type</th>
            <th rowspan="2" data-options="field:'type',width:80,align:'center'">Type</th>
            <th rowspan="2" data-options="field:'address',width:250,halign:'center'">Address</th>
            <th rowspan="2" data-options="field:'origin',width:120,halign:'center'">Country of Origin</th>
            <th rowspan="2" data-options="field:'state',width:120,halign:'center'">State</th>
            <th rowspan="2" data-options="field:'contact_person',width:120,halign:'center'">Contact Person</th>
            <th rowspan="2" data-options="field:'telp',width:120,halign:'center'">Telp</th>
            <th rowspan="2" data-options="field:'fax',width:120,halign:'center'">Fax</th>
            <th rowspan="2" data-options="field:'email',width:200,halign:'center'">Email</th>
            <th rowspan="2" data-options="field:'website',width:150,halign:'center'">Website</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'payment_term',width:80,halign:'center',align:'right'">Payment<br>Term (Day)</th>
            <th rowspan="2" data-options="field:'incoterm',width:80,align:'center'">Incoterm</th>
            <th rowspan="2" data-options="field:'vat_status',align:'center',width:80">Vat Status</th>
            <th rowspan="2" data-options="field:'vat',halign:'center',width:80,align:'right'">Vat</th>
            <th rowspan="2" data-options="field:'commodity',width:80,align:'center'">Commodity</th>
            <th rowspan="2" data-options="field:'tds',halign:'center',width:120">TDS</th>
            <th rowspan="2" data-options="field:'tax',halign:'center',width:120">Tax No</th>
            <th rowspan="2" data-options="field:'gst',halign:'center',width:120">GST No</th>
            <th rowspan="2" data-options="field:'pen',halign:'center',width:120">PAN No</th>
            <th rowspan="2" data-options="field:'ifsc',halign:'center',width:120">Bank IFSC Code</th>
            <th rowspan="2" data-options="field:'bank_account',width:150,halign:'center'">Bank Account</th>
            <th rowspan="2" data-options="field:'bank_name',width:150,halign:'center'">Bank Name</th>
            <th rowspan="2" data-options="field:'idn_no',width:100,halign:'center'">IDN No</th>
            <th rowspan="2" data-options="field:'msmi',width:50,halign:'center'">MSMI</th>
            <!-- <th rowspan="2" data-options="field:'terms',width:100,halign:'center',formatter:btnTerms">Terms<br>Condition</th> -->
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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

<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 70%; height: 550px; padding:10px; top: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier ID</span>
                    <input style="width:30%;" name="id" id="ids" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Code</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier PO</span>
                    <input style="width:60%;" name="supplier_po" id="supplier_po" readonly="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Type</span>
                    <select style="width:60%;" name="supplier_type" id="supplier_type" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="RAW MATERIAL">RAW MATERIAL</option>
                        <option value="CAPITAL GOODS">CAPITAL GOODS</option>
                        <option value="SERVICE">SERVICE</option>
                        <option value="CONSUMABLE">CONSUMABLE</option>
                        <option value="OTHER">OTHER</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" name="type" id="type" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="LOCAL">LOCAL</option>
                        <option value="IMPORT">IMPORT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Contact Person</span>
                    <input style="width:60%;" name="contact_person" id="contact_person" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Address</span>
                    <input style="width:60%;" name="address" id="address" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Telepon</span>
                    <input style="width:60%;" name="telp" id="telp" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Fax</span>
                    <input style="width:60%;" name="fax" id="fax" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Email</span>
                    <input style="width:60%;" name="email" id="email" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Website</span>
                    <input style="width:60%;" name="website" id="website" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" name="currency" id="currency" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Country of Origin</span>
                    <input style="width:60%;" name="origin" id="origin" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">State</span>
                    <input style="width:60%;" name="state" id="state" class="easyui-combobox">
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">TDS</span>
                    <input style="width:60%;" name="tds" id="tds" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment Term</span>
                    <input style="width:60%;" name="payment_term" id="payment_term" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoterm</span>
                    <input style="width:60%;" name="incoterm" id="incoterm" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vat Status</span>
                    <select style="width:30%;" name="vat_status" id="vat_status" class="easyui-combobox" panelHeight="auto">
                        <option value="VAT">VAT</option>
                        <option value="NON VAT">NON VAT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">VAT (%)</span>
                    <input style="width:30%;" name="vat" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Commodity</span>
                    <select style="width:30%;" name="commodity" id="commodity" class="easyui-combobox" required panelHeight="auto">
                        <option value="YES">YES</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Tax No</span>
                    <input style="width:60%;" name="tax" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">GST No</span>
                    <input style="width:60%;" name="gst" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PAN No</span>
                    <input style="width:60%;" name="pen" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">IDN No</span>
                    <input style="width:60%;" name="idn_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MSMI</span>
                    <select style="width:30%;" name="msmi" id="msmi" class="easyui-combobox" panelHeight="auto">
                        <option value="YES">YES</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank IFSC Code</span>
                    <input style="width:60%;" name="ifsc" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Account</span>
                    <input style="width:60%;" name="bank_account" id="bank_account" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Name</span>
                    <input style="width:60%;" name="bank_name" id="bank_name" class="easyui-textbox">
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

<div id="dlg_terms" class="easyui-dialog" title="Update Terms & Conditions" data-options="closed: true,modal:true" style="width: 60%; height: 500px; top: 0px; left: 0px; padding:10px;">
    <form id="frm_terms" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; padding: 10px; float: left;">
            <legend><b>Setting Terms & Conditions for Purchase Order</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Supplier ID</span>
                <input style="width:60%;" id="supplier_id_terms" name="supplier_id" class="easyui-textbox">
            </div>

            <div style="width:100%;">
                <textarea class="summernote" name="terms" id="terms"></textarea>
            </div>

            <div style="float: left; width: 100%;">
                <br><br>
                <center>
                    <a class="easyui-linkbutton c6" style="width: 200px;" onclick="saved()"><i class="fa fa-save"></i> Save Changes</a>
                </center>
            </div>
        </fieldset>
        
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
<iframe id="printout" src="<?= base_url('master/suppliers/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/suppliers/create') ?>';
        $('#frm_insert').form('clear');

        $('#type').combobox('setValue', 'LOCAL');
        $('#status').combobox('setValue', '0');
        $('#commodity').combobox('setValue', 'NO');
        $('#msmi').combobox('setValue', 'NO');
        $('#vat_status').combobox('setValue', 'VAT');
        $('#currency').combobox('setValue', 'INR');
        $('#origin').combobox('setValue', 'INDIA');

        $.ajax({
            type: "post",
            url: "<?= base_url('master/suppliers/autoid') ?>",
            dataType: "json",
            success: function(response) {
                $('#ids').textbox('setValue', response.id);
                $('#supplier_po').textbox('setValue', "PO<?= date("ym") ?>-" + response.id);
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/suppliers/update') ?>?id=' + btoa(row.id);
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
                                url: '<?= base_url('master/suppliers/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_suppliers.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/suppliers/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function btnTerms(val, row) {
        var details = "terms('"+row.id+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-edit"></i> Edit</a>';
    }

    function terms(supplier_id) {
        $("#dlg_terms").dialog('open');
        $("#supplier_id_terms").textbox('setValue', supplier_id);

        $.ajax({
            type: "post",
            url: "<?= base_url('master/suppliers/read') ?>",
            data: "supplier_id=" + supplier_id,
            dataType: "json",
            success: function (json) {
                $(".summernote").summernote("code", json.terms);
            }
        });
    }

    function saved() {
        $('#frm_terms').form('submit', {
            url: '<?= base_url('master/suppliers/updateTerms') ?>',
            method: 'POST',
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
            }
        });
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/suppliers/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        $('.summernote').summernote({
            height: 280,
            hintMode: 'words',
            hintSelect: 'next',
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']]
            ]
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
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

    $('#currency').combobox({
        url: '<?= base_url('master/currencies/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Currencies',
    });

    $('#origin').combobox({
        url: '<?= base_url('master/origins/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Origin',
        onSelect: function(origin){
            $('#state').combobox({
                url: '<?= base_url('master/states/reads/'); ?>' + origin.id,
                valueField: 'name',
                textField: 'name',
                prompt: 'Choose State',
                
            });
        }
    });

    $('#payment_term').combobox({
        url: '<?= base_url('master/payment_terms/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Payment Terms',
    });

    $('#incoterm').combobox({
        url: '<?= base_url('master/incoterms/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Incoterms',
    });

    $('#tds').combogrid({
        url: '<?= base_url('master/tds/reads'); ?>',
        panelWidth: 560,
        idField: 'section',
        textField: 'payment',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose TDS",
        columns: [
            [{
                field: 'section',
                title: 'Section',
                width: 100
            }, {
                field: 'payment',
                title: 'For Payment Of',
                width: 250
            }, {
                field: 'exceed',
                title: 'Payment Exceed',
                width: 100
            }, {
                field: 'individual',
                title: 'Individual',
                width: 100
            }]
        ]
    });

    // UPLOAD
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/suppliers/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/suppliers/upload') ?>',
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
                            url: "<?= base_url('master/suppliers/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/suppliers/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/suppliers/uploadcreateFailed') ?>",
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