<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">History</th>
            <th colspan="4" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th rowspan="2" data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th rowspan="2" data-options="field:'supplier_po',width:150,halign:'center'">Supplier PO</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:100,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:150,halign:'center'">Description</th>
            <!-- <th rowspan="2" data-options="field:'item_supplier',width:150,halign:'center'">Part Supplier</th> -->
            <th rowspan="2" data-options="field:'type',width:100,align:'center'">Supplier Type</th>
            <th rowspan="2" data-options="field:'origin',width:120,halign:'center'">Supplier Origin</th>
            <th rowspan="2" data-options="field:'item_family_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'maker_name',width:150,halign:'center'">Maker</th>
            <th rowspan="2" data-options="field:'maker_type',width:100,halign:'center'">Maker Type</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatComa">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:80,halign:'center',align:'right',formatter:numberformatComa">MOQ</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'valid_date',width:100,align:'center'">Valid Date</th>
            <th rowspan="2" data-options="field:'attachment',width:100,halign:'center',formatter:BtnAttachment">Attachment 1</th>
            <th rowspan="2" data-options="field:'attachment2',width:100,halign:'center',formatter:BtnAttachment2">Attachment 2</th>
            <th rowspan="2" data-options="field:'attachment3',width:100,halign:'center',formatter:BtnAttachment3">Attachment 3</th>
            <th rowspan="2" data-options="field:'hsn_number',width:100,halign:'center'">HSN Code</th>
            <th rowspan="2" data-options="field:'igst',width:80,align:'center'">IGST %</th>
            <th rowspan="2" data-options="field:'sgst',width:80,align:'center'">SGST %</th>
            <th rowspan="2" data-options="field:'cgst',width:80,align:'center'">CGST %</th>
            <th rowspan="2" data-options="field:'leadtime',width:80,align:'center'">Mfg L/T<br>(Days)</th>
            <th rowspan="2" data-options="field:'transit',width:80,align:'center'">Transit<br>(Days)</th>
            <th rowspan="2" data-options="field:'share_order',width:80,align:'center'">Share Order %<br></th>
            <th rowspan="2" data-options="field:'safety_stock',width:80,halign:'center',align:'right',formatter:numberformatComa">Safety <br>Stock<br>(%)</th>
            <th rowspan="2" data-options="field:'calculate',width:80,align:'center'">Calculate<br>MPQ</th>
            <th rowspan="2" data-options="field:'remarks',width:250,halign:'center'">Remarks</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'status_approved',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:150,align:'center'">By</th>
            <th data-options="field:'approved_to',width:150,align:'center'">Next</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="filter_suppliers" id="filter_suppliers" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="filter_maker" id="filter_maker" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="filter_item_rm" id="filter_item_rm" class="easyui-combogrid">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Part Supplier</span>
                    <input style="width:60%;" name="filter_item_vendor" id="filter_item_vendor" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Share Order</span>
                    <input style="width:60%;" name="filter_share_order" id="filter_share_order" class="easyui-combobox">
                </div>
                <div class="fitem" style="margin-bottom: 5px;">
                    <span style="width:35%; display:inline-block;">Approval</span>
                    <select style="width:60%;" name="filter_status_price" id="filter_status_price" panelHeight="auto" class="easyui-combobox">
                        <option value="">ALL</option>
                        <option value="Approved">Approved</option>
                        <option value="Checking">Checking</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Double Item</span>
                    <input name="filter_check" id="filter_check" class="easyui-checkbox" value="1">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" onclick="excel_price()" plain="true"><i class="fa fa-file"></i> Export Price History</a>
    </div>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 49%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="supplier_po" id="supplier_po" disabled class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="item_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="item_rm_id" id="item_rm_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Part Supplier</span>
                    <input style="width:60%;" name="item_supplier" id="item_supplier" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="maker_id" id="maker_id" readonly class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ</span>
                    <input style="width:60%;" name="mpq" id="mpq" class="easyui-numberbox" data-options="precision:2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MOQ</span>
                    <input style="width:60%;" name="moq" id="moq" class="easyui-numberbox" data-options="precision:2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mfg L/T (Days)</span>
                    <input style="width:60%;" name="leadtime" id="leadtime" required="" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transit (Days)</span>
                    <input style="width:60%;" name="transit" id="transit" required="" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" name="currency" id="currency" required panelHeight="auto" class="easyui-combobox">
                </div>
            </div>

            <div style="width: 49%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Price</span>
                    <input style="width:60%;" name="price" id="price" class="easyui-numberbox" required data-options="precision:4">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Valid Date</span>
                    <input style="width:60%;" name="valid_date" id="valid_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Share Order %</span>
                    <input style="width:60%;" name="share_order" required="" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Safety Stock (%)</span>
                    <input style="width:60%;" name="safety_stock" required="" class="easyui-numberbox" data-options="precision:2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Calculate MPQ</span>
                    <select style="width:60%;" name="calculate" class="easyui-combobox" panelHeight="auto">
                        <option value="YES">YES</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment 1</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox" accept=".jpg, .png, .jpeg, .pdf, .xls, .xlsx, .docx">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment 2</span>
                    <input style="width:60%;" name="attachment2" id="attachment2" class="easyui-filebox" accept=".jpg, .png, .jpeg, .pdf, .xls, .xlsx, .docx">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment 3</span>
                    <input style="width:60%;" name="attachment3" id="attachment3" class="easyui-filebox" accept=".jpg, .png, .jpeg, .pdf, .xls, .xlsx, .docx">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%; height:70px;" name="remarks" required class="easyui-textbox" multiline="true">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_history" class="easyui-dialog" title="History Supplier Contract" data-options="closed: true,modal:true" style="width: 90%; height: 500px; top: 0px; left: 0px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'created_date',width:150,halign:'center'">Created Date</th>
                <th data-options="field:'created_by',width:150,halign:'center'">Created By</th>
                <!-- <th data-options="field:'item_rm_number',width:200,halign:'center'">Part No</th> -->
                <!-- <th data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatComa">MPQ</th> -->
                <!-- <th data-options="field:'moq',width:80,halign:'center',align:'right',formatter:numberformatComa">MOQ</th> -->
                <!-- <th data-options="field:'uom',width:80,align:'center'">UoM</th> -->
                <th data-options="field:'price',width:80,halign:'center',align:'right',formatter:numberformat">Price</th>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'valid_date',width:120,align:'center'">Valid Date</th>
                <!-- <th data-options="field:'leadtime',width:80,align:'center'">Leadtime</th> -->
                <!-- <th data-options="field:'share_order',width:80,align:'center'">Share Order</th> -->
                <th data-options="field:'remarks',width:200,halign:'center'">Remarks</th>
            </tr>
        </thead>
    </table>
</div>

<div id="dlg_price" class="easyui-dialog" title="Add New Price" data-options="closed: true,modal:true" style="width: 70%; height: 450px; top: 0px; left: 0px;" toolbar="#toolbar2">
    <table id="dg_price" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'start_date',width:100,align:'center'">Start Date</th>
                <th data-options="field:'valid_date',width:100,align:'center'">Valid Date</th>
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'remarks',width:250,halign:'center'">Remarks</th>
                <th data-options="field:'created_date',width:150,halign:'center'">Created Date</th>
                <th data-options="field:'created_by',width:150,halign:'center'">Created By</th>
                <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Approved</th>
                <th data-options="field:'approved_by',width:100,align:'center'">Approved By</th>
                <th data-options="field:'approved_date',width:120,align:'center'">Approved Date</th>
            </tr>
        </thead>
    </table>
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
<iframe id="printout" src="<?= base_url('master/supplier_item_others/print') ?>" style="width: 100%;" hidden></iframe>
<script>

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/supplier_item_others/create') ?>';
        $('#frm_insert').form('clear');

        valid_date = "";
        $("#supplier_id").combogrid('enable');
        $("#item_rm_id").combogrid('enable');
        $("#item_family").combobox('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#item_family").combobox('setValue', row.item_family_name);
            $("#item_rm_id").combogrid('setValue', row.item_rm_number);
            $("#supplier_id").combogrid('disable');
            $("#item_rm_id").combogrid('disable');
            $("#item_family").combobox('disable');
            
            valid_date = row.valid_date;
            url_save = '<?= base_url('master/supplier_item_others/update') ?>?id=' + btoa(row.id);
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
                                url: '<?= base_url('master/supplier_item_others/delete') ?>',
                                data: {
                                    id: row.id,
                                    supplier_id: row.supplier_id,
                                    item_rm_id: row.item_rm_id,
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

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_supplier_item_others.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_vendor = $("#filter_item_vendor").combobox('getValue');
        var filter_share_order = $("#filter_share_order").combobox('getValue');
        var filter_status_price = $("#filter_status_price").combobox('getValue');
        var filter_check = $("#filter_check").checkbox('options');

        var url = "?filter_suppliers=" + window.btoa(filter_suppliers) +
            "&filter_item_rm=" + window.btoa(filter_item_rm) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_item_vendor=" + window.btoa(filter_item_vendor) +
            "&filter_share_order=" + window.btoa(filter_share_order) +
            "&filter_status_price=" + window.btoa(filter_status_price) +
            "&filter_check=" + filter_check.checked;

        $('#dg').datagrid({
            url: '<?= base_url('master/supplier_item_others/datatables') ?>' + url,
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/supplier_item_others/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_vendor = $("#filter_item_vendor").combobox('getValue');
        var filter_share_order = $("#filter_share_order").combobox('getValue');
        var filter_status_price = $("#filter_status_price").combobox('getValue');
        var filter_check = $("#filter_check").checkbox('options');

        var url = "?filter_suppliers=" + window.btoa(filter_suppliers) +
            "&filter_item_rm=" + window.btoa(filter_item_rm) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_item_vendor=" + window.btoa(filter_item_vendor) +
            "&filter_share_order=" + window.btoa(filter_share_order) +
            "&filter_status_price=" + window.btoa(filter_status_price) +
            "&filter_check=" + filter_check.checked;

        window.location.assign('<?= base_url('master/supplier_item_others/print/excel') ?>' + url);
    }

    function excel_price(){
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_vendor = $("#filter_item_vendor").combobox('getValue');
        var filter_share_order = $("#filter_share_order").combobox('getValue');
        var filter_status_price = $("#filter_status_price").combobox('getValue');
        var filter_check = $("#filter_check").checkbox('options');

        var url = "?filter_suppliers=" + window.btoa(filter_suppliers) +
            "&filter_item_rm=" + window.btoa(filter_item_rm) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_item_vendor=" + window.btoa(filter_item_vendor) +
            "&filter_share_order=" + window.btoa(filter_share_order) +
            "&filter_status_price=" + window.btoa(filter_status_price) +
            "&filter_check=" + filter_check.checked;

        window.location.assign('<?= base_url('master/supplier_item_others/excel_price') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function numberformat(value, row) {
        if (value != null) {
            if(row.currency == "INR"){
                var digit = 4;
            }else{
                var digit = 5;
            }

            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: digit
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatComa(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function cellStylerApproval(value, row, index) {
        if(row.approved_to == "" || row.approved_to == null){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if(row.approved_to == "" || row.approved_to == null){
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function BtnDetail(val, row) {
        var details = "details('"+row.supplier_id+"','"+row.item_rm_id+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnAttachment(val, row) {
        if(val == "" || val == null){
            return "-";
        }else{
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="'+val+'"><i class="fa fa-eye"></i> View</a>';
        }
    }

    function BtnAttachment2(val, row) {
        if(val == "" || val == null){
            return '-';
        }else{
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="' + val + '"><i class="fa fa-paperclip"></i> View</a>';
        }
    }

    function BtnAttachment3(val, row) {
        if(val == "" || val == null){
            return '-';
        }else{
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="' + val + '"><i class="fa fa-paperclip"></i> View</a>';
        }
    }

    function details(supplier_id, item_rm_id) {
        $("#dlg_history").dialog('open');

        $('#dg_history').datagrid({
            url: '<?= base_url('master/supplier_item_others/datatableHistory') ?>?supplier_id=' + btoa(supplier_id) + "&item_rm_id=" + btoa(item_rm_id),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        });
    }

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

    $(function() {
        //SETTING DATAGRID EASYUI
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Send to Approval',
                iconCls: 'icon-ok',
                handler: function() {
                    var valid_date2 = $("#valid_date").datebox('getValue');
                    
                    if(valid_date > valid_date2){
                        toastr.error("Valid Date Cannot < Valid Date Before");
                        return false;
                    }

                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            if($(this).form('validate') === true){
                                var valid_date = $("#valid_date").datebox('getValue');
                                var mpq = $("#mpq").numberbox('getValue');
                                var moq = $("#moq").numberbox('getValue');
                                var price = $("#price").numberbox('getValue');
                                var leadtime = $("#leadtime").numberbox('getValue');
                                var transit = $("#transit").numberbox('getValue');

                                if(mpq <= 0 || moq <= 0 || price <= 0 || leadtime <= 0 || transit <= 0){
                                    toastr.error("MPQ, MOQ, Price, Leadtime / Transit Cannot be 0");
                                    return false;
                                }

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
                        },
                        error: function(){
                            toastr.error("Failed to save data, please check your data requirements again", "error");
                            $('#dg').datagrid('reload');
                            Swal.close();
                        }
                    });
                }
            }]
        });

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/supplier_item_others/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/supplier_item_others/upload') ?>',
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
                                url: "<?= base_url('master/supplier_item_others/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/supplier_item_others/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/supplier_item_others/uploadcreateFailed') ?>",
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

        $('#maker_id').combogrid({
            url: '<?php echo base_url('master/makers/reads'); ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker",
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                },{
                    field: 'name',
                    title: 'Name',
                    width: 200
                },{
                    field: 'type',
                    title: 'Type',
                    width: 100
                }]
            ]
        });

        //Get Supplier
        $('#filter_suppliers').combogrid({
            url: '<?= base_url('master/suppliers/reads') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Supplier No',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 250
                }, ]
            ]
        });

        $('#filter_maker').combogrid({
            url: '<?php echo base_url('master/makers/reads'); ?>',
            panelWidth: 450,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                },{
                    field: 'name',
                    title: 'Name',
                    width: 200
                },{
                    field: 'type',
                    title: 'Type',
                    width: 100
                }]
            ]
        });

        $('#filter_item_vendor').combobox({
            url: '<?php echo base_url('master/supplier_item_others/readVendor'); ?>',
            valueField: 'item_supplier',
            textField: 'item_supplier',
            prompt: 'Select Part Supplier',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_share_order').combobox({
            url: '<?php echo base_url('master/supplier_item_others/readShareOrder'); ?>',
            valueField: 'share_order',
            textField: 'share_order',
            prompt: 'Select Share Order',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_category').combobox({
            url: '<?php echo base_url('master/item_familys/reads/C01'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Category',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_item_rm').combogrid({
            url: '<?= base_url('planning/item_rm/readNotRm') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Part ID',
                    width: 80
                },{
                    field: 'number',
                    title: 'Part No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Part Name',
                    width: 200
                }, ]
            ]
        });

        //Get Part
        $('#supplier_id').combogrid({
            url: '<?= base_url('master/suppliers/reads') ?>',
            panelWidth: 700,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier No',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 250
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }, {
                    field: 'origin',
                    title: 'Origin',
                    width: 150
                }, ]
            ],
            onSelect: function(val, row) {
                $("#supplier_po").textbox("setValue", row.supplier_po);
                $("#currency").textbox("setValue", row.currency);
                if(row.commodity == "NO"){
                    $("#valid_date").datebox("disable");
                }else{
                    $("#valid_date").datebox("enable");
                }
            }
        });

        $('#item_family').combobox({
            url: '<?php echo base_url('master/item_familys/reads/C01'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Product Family'
        });
        
        $('#item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/readNotRm') ?>',
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
                    width: 100
                }, {
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }, ]
            ],
            onSelect:function(valMaker, rowMaker){
                $("#maker_id").combogrid('setValue', rowMaker.maker_id);
            }
        });

        $('#currency').combobox({
            url: '<?php echo base_url('master/currencies/reads'); ?>',
            valueField: 'name',
            textField: 'name',
            prompt: 'Select Currency',
        });

        $('#filter_item_rm').combogrid({
            url: '<?= base_url('master/item_rm/readNotRm'); ?>',
            panelWidth: 350,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            pagination: true,
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
                    width: 100
                }, ]
            ]
        });
    });
</script>