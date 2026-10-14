<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print',width:59,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'delivery_order_no',width:160,halign:'center'">Delivery Order No</th>
            <th rowspan="2" data-options="field:'delivery_order_date',width:100,halign:'center'">Delivery Order<br>Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:100,halign:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:250,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'delivery_note_no',width:150,halign:'center'">Delivery Note</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" id="filter_plant" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order No</span>
                    <input style="width:60%;" id="filter_delivery_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" id="filter_sales_order_no" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 550px; padding:10px; top: 20px; left: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 40%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order Date</span>
                    <input style="width:40%;" name="delivery_order_date" id="delivery_order_date" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order No</span>
                    <input style="width:60%;" name="delivery_order_no" id="delivery_order_no" readonly required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview</a>
                </div>
            </div>
            <div style="width: 40%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:40%;" name="delivery_date" id="delivery_date" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="plant" id="plant" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Delivery Order List" idField="item_number">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th hidden data-options="field:'item_fg_id',width:150">Product ID</th>
                    <th data-options="field:'item_fg_number',width:150">Product No EBWS</th>
                    <th data-options="field:'number_customer',width:200">Product No Customer</th>
                    <th data-options="field:'item_fg_name',width:200">Description</th>
                    <th data-options="field:'sales_order_no',width:200,editor:{type:'combobox', options:{required:true}}">Sales Order No</th>
                    <th data-options="field:'customer_order_no',width:120,editor:{type:'textbox', options:{readonly:true}}">Customer Order No</th>
                    <th data-options="field:'uom',width:80">UoM</th>
                    <th data-options="field:'qty_so',width:100,editor:{type:'numberbox', options:{readonly:true}}">Qty SO</th>
                    <th data-options="field:'qty_remain',width:100,editor:{type:'numberbox', options:{required:true}}">Qty Remain</th>
                    <th data-options="field:'qty_del',width:100,editor:{type:'numberbox', options:{readonly:true}}">Qty Delivery</th>
                    <th data-options="field:'qty_do',width:100,editor:{type:'numberbox', options:{required:true}}">Qty DO</th>
                    <!-- <th data-options="field:'qty_do',width:100,editor:{type:'numberbox', options: {onChange: checkValue}}">Qty DO</th> -->
                    <th data-options="field:'stock_fg',width:100,editor:{type:'numberbox', options:{readonly:true}}">Stock WHS</th>
                    <th data-options="field:'balance',width:100,editor:{type:'numberbox', options:{readonly:true}}">Balance</th>
                </tr>
            </thead>
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('sales/delivery_orders/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        $("#delivery_order_date").datebox('enable');
        $("#delivery_date").combobox('enable');
        $("#customer_id").combobox('enable');
        $("#btnPreview").linkbutton('enable');

        $("#delivery_order_date").datebox({
            onChange: function(val) {
                var customer_id = $("#customer_id").combobox('getValue');

                if (customer_id == "") {
                    toastr.info("Please Choose Customer");
                } else {
                    number("<?= $id_menu ?>", val);
                }
            }
        });

        $("#delivery_order_date").datebox('setValue', "<?= date("Y-m-d") ?>");

        $('#customer_id').combobox({
            url: '<?= base_url('sales/sales_order_deliveries/readCustomer'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Customer',
            onSelect: function(customer) {
                var delivery_order_date = $("#delivery_order_date").datebox('getValue');

                number("<?= $id_menu ?>", delivery_order_date);

                $('#delivery_date').combobox({
                    url: '<?= base_url('sales/sales_order_deliveries/readTransdate/'); ?>' + btoa(customer.id),
                    valueField: 'trans_date',
                    textField: 'trans_date',
                    prompt: 'Choose Delivery Date',
                    onSelect: function(delivery_date) {
                        $('#plant').combobox({
                            url: '<?= base_url('sales/sales_order_deliveries/readPlant/'); ?>' + btoa(customer.id) + "/" + btoa(delivery_date.trans_date),
                            valueField: 'plant',
                            textField: 'plant',
                            prompt: 'Choose Plant',
                        });
                    }
                });
            }
        });

        action = "ADD";
    }

    function number(id_menu, trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('sales/delivery_orders/number/') ?>" + id_menu + "/" + btoa(trans_date),
            dataType: "html",
            success: function(result) {
                if (result == "") {
                    toastr.info("Please Check Configuration in Serial No First");
                    $("#delivery_order_no").textbox('clear');
                } else {
                    $("#delivery_order_no").textbox('setValue', result);
                }
            }
        });
    }

    function preview(url = "") {
        var delivery_date = $("#delivery_date").combobox('getValue');
        var customer_id = $("#customer_id").combobox('getValue');
        var plant = $("#plant").combobox('getValue');

        if (url == "") {
            var urlGet = "<?= base_url('sales/delivery_orders/datatablesTemp/') ?>" + btoa(delivery_date) + "/" + btoa(customer_id) + "/" + btoa(plant);
        } else {
            var urlGet = url;
        }

        if (delivery_date == "" || customer_id == "") {
            toastr.warning('Please Select Delivery Date and Customer First', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: urlGet,
                fitColumns: false,
                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                    var sales_order = $(editors[0].target);
                    var customer_order_no = $(editors[1].target);
                    var qty_so = $(editors[2].target);
                    var qty_remain = $(editors[3].target).numberbox('getValue');
                    var qty_del = $(editors[4].target).numberbox('getValue');
                    var qty_do = $(editors[5].target);
                    var stock_fg = $(editors[6].target).numberbox('getValue');
                    var balance = $(editors[7].target);

                    sales_order.combobox({
                        url: '<?= base_url('sales/delivery_orders/readSo/'); ?>' + btoa(row.trans_date) + "/" + btoa(row.customer_id) + "/" + btoa(row.item_fg_id) + "/" + btoa(row.plant),
                        valueField: 'sales_order_no',
                        textField: 'sales_order_no',
                        prompt: 'Choose SO',
                        onSelect: function(so) {
                            qty_so.numberbox('setValue', so.qty);
                            customer_order_no.textbox('setValue', so.customer_order_no);
                            $(editors[3].target).numberbox('setValue', so.qty_remain);
                            balance.numberbox('setValue', (parseInt(so.qty_remain) - (parseInt(qty_del))));
                        },
                        formatter: function(row) {
                            return row.sales_order_no + " | " + row.plant;
                        }
                    });

                    qty_do.numberbox({
                        onChange: function(val) {
                            if (parseInt(val) > parseInt(qty_del)) {
                                var qty_final = qty_del;
                            } else {
                                var qty_final = val;
                            }

                            if (parseInt(val) > parseInt(qty_del)) {
                                toastr.info("Qty DO Cannot > Qty Delivery");
                                qty_do.numberbox('setValue', qty_del);
                            } else if (parseInt(val) > parseInt(qty_remain)) {
                                toastr.info("Qty DO Cannot > Qty Remain");
                                qty_do.numberbox('setValue', qty_del);
                            }

                            balance.numberbox('setValue', (parseInt(qty_remain) - (parseInt(qty_final))));
                        }
                    })

                    sales_order.combobox('setValue', row.sales_order_no);
                }
            });
        }
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.status == 0) {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);

                $("#delivery_order_date").datebox('disable');
                $("#delivery_date").combobox('disable');
                $("#customer_id").combobox('disable');
                $("#customer_order_no").combobox('disable');
                $("#btnPreview").linkbutton('disable');

                preview("<?= base_url('sales/delivery_orders/datatableUpdates?delivery_order_no=') ?>" + btoa(row.delivery_order_no));

                action = "EDIT";
            } else {
                toastr.error("Status is Closed")
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        if (row.status != 0) {
                            toastr.error("cannot delete because the status is close/cancel");
                            return false;
                        }

                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('sales/delivery_orders/delete') ?>',
                            data: {
                                delivery_order_no: row.delivery_order_no
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("This data is already used in other modules, You must delete it First", "Error");
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_delivery_order_no = $("#filter_delivery_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('sales/delivery_orders/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.delivery_order_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('sales/delivery_orders/datatableDetails?delivery_order_no=') ?>' + window.btoa(row.delivery_order_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'status',
                            title: '#',
                            halign: 'center',
                            width: 100,
                            formatter: function(val, row) {
                                if (val == 0) {
                                    var details = "cancelDo('" + row.item_fg_id + "','" + row.delivery_order_no + "')";
                                    return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" onclick="' + details + '">Cancel</a>';
                                } else {
                                    return "-";
                                }
                            }
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No EBWS',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Description',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'sales_order_no',
                            title: 'Sales Order No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'customer_order_no',
                            title: 'Customer Order No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty_so',
                            title: 'SO Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_remain',
                            title: 'Remain Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_do',
                            title: 'Total DO',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_del',
                            title: 'Delivery Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'stock',
                            title: 'Stock',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'stock_bal',
                            title: 'Balance',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }]
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
        $("#printout").attr('src', '<?= base_url('sales/delivery_orders/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_delivery_order_no = $("#filter_delivery_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('sales/delivery_orders/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function cancelDo(item_fg_id, delivery_order_no) {
        $.messager.prompt('Cancel DO', 'Please input The Password', function(r) {
            if (r == "CANCELDO") {
                $.ajax({
                    type: "post",
                    url: "<?= base_url("sales/delivery_orders/cancelDo"); ?>",
                    data: "item_fg_id=" + item_fg_id + "&delivery_order_no=" + delivery_order_no,
                    dataType: "json",
                    success: function(response) {
                        toastr.success(response.message, response.title);
                        $('#dg').datagrid('reload');
                    }
                });
            }
        });
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var customer_id = $("#customer_id").combobox('getValue');
                    var delivery_order_date = $("#delivery_order_date").datebox('getValue');
                    var delivery_date = $("#delivery_date").combobox('getValue');
                    var plant = $("#plant").combobox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('sales/delivery_orders/number/' . $id_menu) ?>/" + btoa(delivery_order_date),
                        dataType: "html",
                        success: function(result) {
                            if (result == "") {
                                toastr.info("Please Check Configuration in Serial No First");
                                return false;
                            } else {
                                if (action == "ADD") {
                                    var delivery_order_no = result;
                                } else {
                                    var delivery_order_no = $("#delivery_order_no").textbox('getValue');
                                }

                                $('#dg_request').datagrid('acceptChanges');
                                var rows = $('#dg_request').datagrid('getSelections');
                                var totalrows = rows.length;

                                for (let d = 0; d < totalrows; d++) {
                                    if (rows[d].sales_order_no == "") {
                                        toastr.error("Sales Order Cannot Empty!");
                                        return false;
                                    }
                                }

                                if (totalrows > 0) {
                                    if (customer_id != "" && delivery_order_date != "" && delivery_date != "") {
                                        $('#dlg_insert').dialog('close');

                                        Swal.fire({
                                            title: 'Please Wait. . .',
                                            showConfirmButton: false,
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            },
                                        });

                                        requestData(totalrows, rows);

                                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                            var i = (number - 1);
                                            if (value < 100) {
                                                if (parseInt(json[i].qty_remain) >= parseInt(json[i].qty_do)) {
                                                    value = Math.floor((number / total) * 100);
                                                    $.ajax({
                                                        type: "post",
                                                        url: '<?= base_url('sales/delivery_orders/create') ?>',
                                                        data: {
                                                            customer_id: customer_id,
                                                            delivery_order_date: delivery_order_date,
                                                            delivery_order_no: delivery_order_no,
                                                            delivery_date: delivery_date,
                                                            plant: plant,
                                                            remarks: remarks,
                                                            item_fg_id: json[i].item_fg_id,
                                                            sales_order_no: json[i].sales_order_no,
                                                            customer_order_no: json[i].customer_order_no,
                                                            uom: json[i].uom,
                                                            qty_so: json[i].qty_so,
                                                            qty_remain: json[i].qty_remain,
                                                            qty_do: json[i].qty_do,
                                                            qty_del: json[i].qty_del,
                                                            stock: json[i].stock_fg,
                                                            stock_bal: json[i].balance,
                                                        },
                                                        dataType: "json",
                                                        success: function(result) {
                                                            if (result.theme == "success") {
                                                                if (value >= 100) {
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
                                                                } else {
                                                                    requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                                }
                                                            } else {
                                                                if (value >= 100) {
                                                                    Swal.close();
                                                                    toastr.error(result.message, result.title);
                                                                } else {
                                                                    toastr.error(result.message, result.title);
                                                                    requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                                }
                                                            }
                                                        }
                                                    });
                                                } else {
                                                    toastr.error("Cannot Create Data because Remain <= qty DO");
                                                }
                                            }
                                        }

                                        $('#dg').datagrid('reload');
                                        $('#dlg_insert').dialog('close');
                                    } else {
                                        toastr.error("Please Completed your input");
                                    }
                                } else {
                                    toastr.warning("Please Selection in the table first");
                                }
                            }
                        }
                    });
                }
            }]
        });
    });

    $('#filter_customer_id').combobox({
        url: '<?= base_url('master/customers/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose All',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(customer) {
            $('#filter_plant').combobox({
                url: '<?= base_url('master/customers/readAddress/') ?>' + btoa(customer.id),
                valueField: 'plant',
                textField: 'plant',
                prompt: 'Select Plant',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });

            $('#filter_delivery_order_no').combobox({
                url: '<?= base_url('sales/delivery_orders/readDeliveryOrders/'); ?>' + customer.id,
                valueField: 'delivery_order_no',
                textField: 'delivery_order_no',
                prompt: 'Choose All',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
                onSelect: function(deliver_order) {
                    $('#filter_customer_order_no').combobox({
                        url: '<?= base_url('sales/delivery_orders/readCustomerOrder/'); ?>' + deliver_order.deliver_order_no,
                        valueField: 'customer_order_no',
                        textField: 'customer_order_no',
                        prompt: 'Choose All',
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combobox('clear').combobox('textbox').focus();
                            }
                        }],
                    });
                }
            });

            $('#filter_sales_order_no').combobox({
                url: '<?= base_url('sales/sales_orders/readSalesOrder/'); ?>' + btoa(customer.id),
                valueField: 'sales_order_no',
                textField: 'sales_order_no',
                prompt: 'Choose All',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });
        }
    });

    $('#filter_item_fg').combogrid({
        url: '<?= base_url("master/item_fg/reads") ?>',
        panelWidth: 550,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Product No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
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
                title: 'Product Name',
                width: 200
            }]
        ],
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else if (value == 1) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #b2c2ff; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'OPEN';
        } else if (value == 1) {
            return 'CLOSE';
        } else {
            return 'CANCEL';
        }
    };

    // FORMAT tahun-bulan-tanggal
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

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

    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnPrint(val, row) {
        var print = "print_do('" + row.delivery_order_no + "')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_do(delivery_order_no) {
        window.open("<?= base_url('sales/delivery_orders/print_do/') ?>" + window.btoa(delivery_order_no), "_blank", "width=1200,height=600");
    }

    function checkValue(newValue, oldValue) {
        if (newValue > 0) {
            $(this).numberbox('readonly', true);
        } else {
            $(this).numberbox('readonly', false);
        }
    }
</script>