<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'table_name',width:200,halign:'center'">Module</th>
            <th rowspan="2" data-options="field:'user_approval_name_1',width:120,align:'center'">Approval 1</th>
            <th rowspan="2" data-options="field:'user_approval_name_2',width:120,align:'center'">Approval 2</th>
            <th rowspan="2" data-options="field:'user_approval_name_3',width:120,align:'center'">Approval 3</th>
            <th rowspan="2" data-options="field:'user_approval_name_4',width:120,align:'center'">Approval 4</th>
            <th rowspan="2" data-options="field:'user_approval_name_5',width:120,align:'center'">Approval 5</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,halign:'center'"> By</th>
            <th data-options="field:'created_date',width:150,halign:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,halign:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,halign:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true, modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Module</span>
                <select style="width:60%;" name="table_name" id="table_name" required="true" data-options="prompt:'Select Module'" class="easyui-combobox">
                    <option value="users">Users</option>
                    <option value="item_rm">Item RM</option>
                    <option value="item_fg">Item FG</option>
                    <option value="bom">Bill of Material</option>
                    <option value="customer_items">Customer Contract</option>
                    <option value="stock_fg">Upload Data FG</option>
                    <option value="stock_wip">Upload Data WIP</option>
                    <option value="stock_so">Upload Data SO</option>
                    <option value="os_so">Upload OS SO</option>
                    <option value="os_mpp">Upload OS MPP</option>
                    <option value="generate_mrp_finals">Generate MRP</option>
                    <option value="forecasts">Forecasts</option>
                    <option value="supplier_items">Supplier Contract</option>
                    <!-- <option value="supplier_item_prices">Supplier Contract Prices</option> -->
                    <option value="purchase_requests">Purchase Requests</option>
                    <option value="purchase_request_others">Purchase Request Others</option>
                    <option value="purchase_request_services">Purchase Request Services</option>
                    <option value="purchase_request_rfq">Request RFQ</option>
                    <option value="purchase_orders">Purchase Order</option>
                    <option value="purchase_order_others">Purchase Order Others</option>
                    <option value="onetime_po_others">Onetime PO Others</option>
                    <option value="purchase_order_services">Purchase Order Services</option>
                    <option value="purchase_order_rfq">Request for Quotation</option>
                    <option value="supply_materials">Part Requisition</option>
                    <option value="supply_sheets">Kanban Requests</option>
                    <option value="item_ng">Material NG</option>
                    <option value="sto_rm">STO Raw Material</option>
                    <option value="sto_fg">STO Finish Good</option>
                    <option value="scraps">Scraps</option>
                    <option value="item_mpq">Item MPQ</option>
                    <option value="sales_orders">Sales Orders</option>
                    <option value="sample_requests">Purch. Sample Requests</option>
                    <option value="rgp_transaction_in">RGP Transaction IN</option>
                    <option value="rgp_transaction_out">RGP Transaction OUT</option>
                    <option value="scrap_booking">Scrap Booking</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approval 1</span>
                <input style="width:60%;" name="user_approval_1" id="user_approval_1" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approval 2</span>
                <input style="width:60%;" name="user_approval_2" id="user_approval_2" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approval 3</span>
                <input style="width:60%;" name="user_approval_3" id="user_approval_3" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approval 4</span>
                <input style="width:60%;" name="user_approval_4" id="user_approval_4" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approval 5</span>
                <input style="width:60%;" name="user_approval_5" id="user_approval_5" class="easyui-combobox">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('admin/approvals/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('admin/approvals/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('admin/approvals/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.info("Please select one of the data in the table first");
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
                                url: '<?= base_url('admin/approvals/delete') ?>',
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

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT EXCEL
    function excel() {
        window.location.assign('<?= base_url('admin/approvals/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('admin/approvals/datatables') ?>',
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

        //DATA USERS
        $('#user_approval_1').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_approval_2').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_approval_3').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_approval_4').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_approval_4').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        
        //DATA USERS
        $('#user_approval_5').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
    });
</script>