<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'detail',width:80,align:'center',formatter: btnDetails">Address</th>
            <th rowspan="2" data-options="field:'id',width:80,align:'center'">Customer<br>ID</th>
            <th rowspan="2" data-options="field:'name',width:250,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'number',width:80,align:'center'">Customer<br>Code</th>
            <th rowspan="2" data-options="field:'line_id',width:80,align:'center'">Line ID</th>
            <th rowspan="2" data-options="field:'line_no',width:80,align:'center'">Line No</th>
            <th rowspan="2" data-options="field:'type',width:80,align:'center'">Type</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'cin_no',width:180,halign:'center'">CIN No</th>
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
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>
<!-- TOOLBAR CUSTOMERS ADDRESS DATAGRID -->
<div id="toolbar2" style="height: 35px;">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="add2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="update2()"><i class="fa fa-edit"></i> Update</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted2()"><i class="fa fa-trash"></i> Delete</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="upload2()"><i class="fa fa-upload"></i> Upload</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="download_excel2()"><i class="fa fa-download"></i> Download Template</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 900px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer ID</span>
                    <input style="width:30%;" name="id" id="ids" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Code</span>
                    <input style="width:60%;" name="number" id="number" class="easyui-validatebox" data-options="required:true,validType:'length[2,2]'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Line ID</span>
                    <input style="width:60%;" name="line_id" id="line_id" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Line No</span>
                    <input style="width:60%;" name="line_no" id="line_no" required="" class="easyui-combobox">
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" name="type" id="type" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="LOCAL">LOCAL</option>
                        <option value="EXPORT">EXPORT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" name="currency" id="currency" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">CIN No</span>
                    <input style="width:60%;" name="cin_no" id="cin_no" class="easyui-textbox">
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

<!-- DIALOG CUSTOMER ADDRESS -->
<div id="dlg_details" class="easyui-dialog" title="Customer Address" data-options="closed: true,modal:true" style="width: 99%; height: 500px; top:10px; left: 10px;">
    <table id="dg2" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead>
            <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" hidden data-options="field:'id',width:120,halign:'center'">ID</th>
                <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
                <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
                <th rowspan="2" data-options="field:'department',width:100,halign:'center'">Department</th>
                <th rowspan="2" data-options="field:'payment_term',width:100,halign:'center'">Payment Term</th>
                <th rowspan="2" data-options="field:'gst_no',width:100,halign:'center'">GST No</th>
                <th rowspan="2" data-options="field:'vendor_code',width:100,halign:'center'">Vendor Code</th>
                <th rowspan="2" data-options="field:'pan_no',width:100,halign:'center'">PAN No</th>
                <th rowspan="2" data-options="field:'ifsc_code',width:100,halign:'center'">IFSC Code</th>
                <th rowspan="2" data-options="field:'bank_account',width:100,halign:'center'">Bank Account</th>
                <th rowspan="2" data-options="field:'bank_name',width:100,halign:'center'">Bank Name</th>
                <th rowspan="2" data-options="field:'ship_pin',width:100,halign:'center'">Shipping PIN</th>
                <th rowspan="2" data-options="field:'ship_address',width:250,halign:'center'">Shipping Address</th>
                <th rowspan="2" data-options="field:'ship_country',width:150,halign:'center'">Shipping Country</th>
                <th rowspan="2" data-options="field:'ship_state',width:150,halign:'center'">Shipping State</th>
                <th rowspan="2" data-options="field:'ship_city',width:150,halign:'center'">Shipping City</th>
                <th rowspan="2" data-options="field:'bill_pin',width:100,halign:'center'">Bill PIN</th>
                <th rowspan="2" data-options="field:'bill_address',width:250,halign:'center'">Billing Address</th>
                <th rowspan="2" data-options="field:'bill_country',width:150,halign:'center'">Billing Country</th>
                <th rowspan="2" data-options="field:'bill_state',width:150,halign:'center'">Billing State</th>
                <th rowspan="2" data-options="field:'bill_city',width:150,halign:'center'">Billing City</th>
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
</div>

<!-- DIALOG SAVE AND UPDATE CUSTOMER ADDRESS -->
<div id="dlg_insert2" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert2" method="post" novalidate>
        <div style="float: left; width: 100%;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Basic Information</b></legend>
                <div style="float:left; width:50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer ID</span>
                        <input style="width:60%;" name="customer_id" id="customer_id" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Plant</span>
                        <input style="width:60%;" name="plant" id="plant" required class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Department</span>
                        <input style="width:60%;" name="department" id="department" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Term (Day)</span>
                        <input style="width:60%;" name="payment_term" id="payment_term" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">GST No</span>
                        <input style="width:60%;" name="gst_no" id="gst_no" class="easyui-textbox">
                    </div>
                </div>
                <div style="float:left; width:50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Vendor Code</span>
                        <input style="width:60%;" name="vendor_code" id="vendor_code" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">PAN No</span>
                        <input style="width:60%;" name="pan_no" id="pan_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">IFSC Code</span>
                        <input style="width:60%;" name="ifsc_code" id="ifsc_code" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Bank Account</span>
                        <input style="width:60%;" name="bank_account" id="bank_account" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Bank Name</span>
                        <input style="width:60%;" name="bank_name" id="bank_name" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
            <fieldset style="width:49%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Shipping Information</b></legend>
                <div style="float:left; width:100%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Pin Code</span>
                    <input style="width:60%;" name="ship_pin" id="ship_pin" data-options="validType:'length[0,6]'" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Address</span>
                    <input style="width:60%;" name="ship_address" id="ship_address" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Country</span>
                    <input style="width:60%;" name="ship_country" id="ship_country" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">State</span>
                    <input style="width:60%;" name="ship_state" id="ship_state" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">City</span>
                    <input style="width:60%;" name="ship_city" id="ship_city" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Contact Person</span>
                    <input style="width:60%;" name="ship_contact" id="ship_contact" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Telp</span>
                    <input style="width:60%;" name="ship_telp" id="ship_telp" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Fax</span>
                    <input style="width:60%;" name="ship_fax" id="ship_fax" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Email</span>
                    <input style="width:60%;" name="ship_email" id="ship_email" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Website</span>
                    <input style="width:60%;" name="ship_website" id="ship_website" class="easyui-textbox">
                </div>
            </fieldset>
            <fieldset style="width:49%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Billing Information</b></legend>
                <div style="float:left; width:100%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Pin Code</span>
                    <input style="width:60%;" name="bill_pin" id="bill_pin" data-options="validType:'length[0,6]'" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Address</span>
                    <input style="width:60%;" name="bill_address" id="bill_address" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Country</span>
                    <input style="width:60%;" name="bill_country" id="bill_country" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">State</span>
                    <input style="width:60%;" name="bill_state" id="bill_state" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">City</span>
                    <input style="width:60%;" name="bill_city" id="bill_city" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Contact Person</span>
                    <input style="width:60%;" name="bill_contact" id="bill_contact" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Telp</span>
                    <input style="width:60%;" name="bill_telp" id="bill_telp" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Fax</span>
                    <input style="width:60%;" name="bill_fax" id="bill_fax" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Email</span>
                    <input style="width:60%;" name="bill_email" id="bill_email" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Website</span>
                    <input style="width:60%;" name="bill_website" id="bill_website" class="easyui-textbox">
                </div>
            </fieldset>
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

<!-- Upload -->
<div id="dlg_upload2" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload2" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload2" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success2">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed2">0</b></span>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks2" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks2">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/customers/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/customers/create') ?>';
        $('#frm_insert').form('clear');

        $('#type').combobox('setValue', 'LOCAL');
        $('#status').combobox('setValue', '0');
        $('#taxes').numberbox('setValue', '11');

        $.ajax({
            type: "post",
            url: "<?= base_url('master/customers/autoid') ?>",
            dataType: "json",
            success: function(response) {
                $('#ids').textbox('setValue', response.id);
            }
        });
    }

    //ADD DATA
    function add2() {
        $('#dlg_insert2').dialog('open');
        var customer_id = $("#customer_id").textbox('getValue');
        url_save2 = '<?= base_url('master/customers/create2') ?>';
        $('#frm_insert2').form('clear');
        $("#customer_id").textbox('setValue', customer_id);
        $("#plant").textbox('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/customers/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function update2() {
        var row = $('#dg2').datagrid('getSelected');
        if (row) {
            $('#dlg_insert2').dialog('open');
            $('#frm_insert2').form('load', row);
            url_save2 = '<?= base_url('master/customers/update2') ?>?id=' + btoa(row.id);
            $("#plant").textbox('disable');

            $('#ship_country').combobox({
                url: '<?= base_url('master/origins/reads'); ?>',
                valueField: 'name',
                textField: 'name',
                prompt: 'Choose Origin',
                onLoadSuccess: function(){
                    $("#ship_country").combobox('setValue', row.ship_country);
                },
                onSelect: function(origin){
                    $('#ship_state').combobox({
                        url: '<?= base_url('master/states/reads/'); ?>' + origin.id,
                        valueField: 'name',
                        textField: 'name',
                        prompt: 'Choose State',
                        onLoadSuccess: function(){
                            $("#ship_state").combobox('setValue', row.ship_state);
                        },
                        onSelect: function(state){
                            $('#ship_city').combobox({
                                url: '<?= base_url('master/cities/reads/'); ?>' + origin.id + '/' + state.id,
                                valueField: 'name',
                                textField: 'name',
                                prompt: 'Choose Cities',
                                onLoadSuccess: function(){
                                    $("#ship_city").combobox('setValue', row.ship_city);
                                },
                            });
                        } 
                    });
                }
            });

            $('#bill_country').combobox({
                url: '<?= base_url('master/origins/reads'); ?>',
                valueField: 'name',
                textField: 'name',
                prompt: 'Choose Origin',
                onLoadSuccess: function(){
                    $("#bill_country").combobox('setValue', row.bill_country);
                },
                onSelect: function(origin){
                    $('#bill_state').combobox({
                        url: '<?= base_url('master/states/reads/'); ?>' + origin.id,
                        valueField: 'name',
                        textField: 'name',
                        prompt: 'Choose State',
                        onLoadSuccess: function(){
                            $("#bill_state").combobox('setValue', row.bill_state);
                        },
                        onSelect: function(state){
                            $('#bill_city').combobox({
                                url: '<?= base_url('master/cities/reads/'); ?>' + origin.id + '/' + state.id,
                                valueField: 'name',
                                textField: 'name',
                                prompt: 'Choose Cities',
                                onLoadSuccess: function(){
                                    $("#bill_city").combobox('setValue', row.bill_city);
                                },
                            });
                        } 
                    });
                }
            });
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
                                url: '<?= base_url('master/customers/delete') ?>',
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

    function deleted2() {
        var rows = $('#dg2').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('master/customers/delete2') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                
                            },
                            complete: function(data) {
                                $('#dg2').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // UPLOAD DATA
    function upload2() {
        $('#dlg_upload2').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_customers.xls') ?>');
    }

     // DOWNLOAD
     function download_excel2() {
        window.location.assign('<?= base_url('template/tmp_customer_address.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/customers/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/customers/datatables') ?>',
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

        $('#dlg_insert2').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert2').form('submit', {
                        url: url_save2,
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
                            $('#dlg_insert2').dialog('close');
                            $('#dg2').datagrid('reload');
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

    function btnDetails(val, row) {
        var details = "details('" + row.id + "')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-list"></i> Detail</a>';
    }

    function details(customer_id) {
        $("#dlg_details").dialog('open');
        $("#customer_id").textbox('setValue', customer_id); // id customer di simpan di textbox customer_id sekaligus saat add id tersimpan

        $('#dg2').datagrid({
            url: '<?= base_url('master/customers/datatables2/') ?>' + btoa(customer_id),
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
        }).datagrid('enableFilter');
    }

    $('#payment_term').combobox({
        url: '<?= base_url('master/payment_terms/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Payment Terms',
    });

    $('#currency').combobox({
        url: '<?= base_url('master/currencies/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Currencies',
    });

    $('#line_no').combobox({
        url: '<?= base_url('master/line_productions/reads'); ?>',
        valueField: 'number',
        textField: 'name',
        prompt: 'Choose Line Production',
    });

    $('#ship_country').combobox({
        url: '<?= base_url('master/origins/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Origin',
        onSelect: function(origin){
            $('#ship_state').combobox({
                url: '<?= base_url('master/states/reads/'); ?>' + origin.id,
                valueField: 'name',
                textField: 'name',
                prompt: 'Choose State',
                onSelect: function(state){
                    $('#ship_city').combobox({
                        url: '<?= base_url('master/cities/reads/'); ?>' + origin.id + '/' + state.id,
                        valueField: 'name',
                        textField: 'name',
                        prompt: 'Choose Cities',
                    });
                } 
            });
        }
    });

    $('#bill_country').combobox({
        url: '<?= base_url('master/origins/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Origin',
        onSelect: function(origin){
            $('#bill_state').combobox({
                url: '<?= base_url('master/states/reads/'); ?>' + origin.id,
                valueField: 'name',
                textField: 'name',
                prompt: 'Choose State',
                onSelect: function(state){
                    $('#bill_city').combobox({
                        url: '<?= base_url('master/cities/reads/'); ?>' + origin.id + '/' + state.id,
                        valueField: 'name',
                        textField: 'name',
                        prompt: 'Choose Cities',
                    });
                } 
            });
        }
    });

    // UPLOAD
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/customers/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/customers/upload') ?>',
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
                            url: "<?= base_url('master/customers/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/customers/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/customers/uploadcreateFailed') ?>",
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

    // UPLOAD
    $('#dlg_upload2').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/customers/uploadDownloadFailed2') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload2').form('submit', {
                    url: '<?= base_url('master/customers/upload2') ?>',
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
                            url: "<?= base_url('master/customers/uploadclearFailed2') ?>"
                        });
                        var json = eval('(' + result + ')');
                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload2').progressbar('setValue', value);
                                $('#p_start2').html(number);
                                $('#p_finish2').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('master/customers/uploadCreate2') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            $('#p_success2').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed2').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('master/customers/uploadcreateFailed2') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks2").append(title + "<br>");
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