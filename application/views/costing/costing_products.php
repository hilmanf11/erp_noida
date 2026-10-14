<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%; height: 630px;" toolbar="#toolbar" showFooter="true">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <!-- <th rowspan="2" data-options="field:'history',width:100,halign:'center',formatter:btnWorkorder">Detail</th> -->
            <th rowspan="2" data-options="field:'details',width:80,halign:'center',formatter:btnDetails">Detail</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:120,halign:'center'">Product No EBWS</th>
            <th rowspan="2" data-options="field:'number_customer',width:150,halign:'center'">Product No Customer</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'total_qty',width:100,halign:'center', align:'right',formatter:priceformat">Quantity</th>
            <th rowspan="2" data-options="field:'umh',width:100,halign:'center', align:'right',formatter:priceformat">UMH</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'">Total Manufacture Cost</th>
            <th rowspan="2" data-options="field:'direct_total',width:100,halign:'center', align:'right',formatter:priceformat2">Cost Product</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <!-- <tr>
            <th colspan="4" data-options="field:'',width:100,halign:'center'"> Direct Material</th>
            <th colspan="4" data-options="field:'',width:100,halign:'center'"> Direct Labor</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> FOH</th>
        </tr> -->
        <tr>
            <th data-options="field:'direct_material',width:100,halign:'center',align:'right',formatter:priceformat">Kanban<br>Supply</th>
            <th data-options="field:'direct_requestion',width:100,halign:'center',align:'right',formatter:priceformat">Material<br>Requestion</th>
            <th data-options="field:'direct_material_total',width:100,halign:'center',align:'right',formatter:priceformat">Total</th>
            <!-- <th data-options="field:'direct_material_pcs',width:100,halign:'center',align:'right',formatter:priceformat">Pcs</th> -->
            <!-- <th data-options="field:'direct_labor',width:100,halign:'center',align:'right',formatter:priceformat">Regular</th>
            <th data-options="field:'direct_overtime',width:100,halign:'center',align:'right',formatter:priceformat">Overtime</th>
            <th data-options="field:'direct_labor_total',width:100,halign:'center',align:'right',formatter:priceformat">Total</th>
            <th data-options="field:'direct_labor_pcs',width:100,halign:'center',align:'right',formatter:priceformat">Pcs</th>
            <th data-options="field:'direct_foh',width:100,halign:'center',align:'right',formatter:priceformat">FOH</th>
            <th data-options="field:'direct_foh_pcs',width:100,halign:'center',align:'right',formatter:priceformat">Pcs</th> -->
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 235px; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Periode</span>
                <input style="width:30%;" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 28%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <div style="float: left; width: 50%;">
                <b>Calculate Product No</b>
                <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            </div>
            <div style="float: left; width: 50%;">
                <b id="txt_item">-</b>
                <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
            </div>
            <div style="float: left; margin-top: 10px; width: 100%;">
                <b>Calculate Costing Final</b>
                <div id="p_upload3" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start3">0</b> Of <b id="p_finish3">0</b></center>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<!-- Detail Workorder -->
<div id="dlg_workorder" class="easyui-window" title="Detail Workorder" data-options="closed: true,modal:true" style="width: 800px; height: 400px; top: 20px;">
    <table id="dg_workorder" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'details',width:80,halign:'center',formatter:btnDetails">Detail</th>
                <th data-options="field:'periode',width:80,halign:'center'">Period</th>
                <!-- <th data-options="field:'wp',width:40,halign:'center'">WP</th> -->
                <!-- <th data-options="field:'workorder',width:150,halign:'center'">Workorder</th> -->
                <!-- <th data-options="field:'item_fg_number',width:120,halign:'center'">Product No EBWS</th> -->
                <!-- <th data-options="field:'number_customer',width:200,halign:'center'">Product No Customer</th> -->
                <!-- <th data-options="field:'item_fg_name',width:150,halign:'center'">Description</th> -->
                <th data-options="field:'qty',width:100,halign:'center',align:'right',formatter:priceformat">WO Qty</th>
                <!-- <th data-options="field:'umh',width:60,halign:'center',align:'right',formatter:priceformat">UMH</th> -->
                <!-- <th data-options="field:'total_umh',width:100,halign:'center',align:'right',formatter:priceformat">Total UMH</th> -->
                <!-- <th data-options="field:'direct_labor',width:100,halign:'center',align:'right',formatter:priceformat">Direct Labor</th> -->
                <!-- <th data-options="field:'direct_overtime',width:100,halign:'center',align:'right',formatter:priceformat">Direct Overtime</th> -->
                <!-- <th data-options="field:'direct_foh',width:100,halign:'center',align:'right',formatter:priceformat">Direct FOH</th> -->
                <th data-options="field:'direct_material',width:120,halign:'center',align:'right',formatter:priceformat">Direct Material</th>
                <th data-options="field:'direct_requestion',width:120,halign:'center',align:'right',formatter:priceformat">Direct Requestion</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Detail Details -->
<div id="dlg_details" class="easyui-window" title="Detail Component No" data-options="closed: true,modal:true" style="width: 800px; height: 400px; top: 20px;">
    <table id="dg_details" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
                <th data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
                <th data-options="field:'item_rm_name',width:150,halign:'center'">Description</th>
                <th data-options="field:'uom',width:80,align:'center'">Uom</th>
                <th data-options="field:'supply_type',width:150,halign:'center'">Supply Type</th>
                <th data-options="field:'supply_date',width:100,halign:'center'">Supply Date</th>
                <th data-options="field:'supply_no',width:150,halign:'center'">Supply No</th>
                <th data-options="field:'issued',width:100,halign:'center',align:'right',formatter:priceformat">Issued</th>
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter:priceformat2">Price</th>
                <th data-options="field:'amount',width:100,halign:'center',align:'right',formatter:priceformat">Amount</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('costing/costing_products/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        $.ajax({
            type: "post",
            url: "<?= base_url('closing/locks/checkLock') ?>",
            data: "period=" + filter_year + "-" + filter_month + "-01" + "&menus_id=<?= $menus_id ?>",
            dataType: "json",
            success: function (lock) {
                if(lock.total > 0){
                    toastr.error("This period is not active by Accounting");
                    return false;
                }

                $.messager.confirm('Warning', 'Are you sure you want to Calculate this period?', function(r) {
                    if (r) {
                        Swal.fire({
                            title: 'Please Wait for Calculating Data',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('costing/costing_products/getData') ?>",
                            data: "month=" + filter_month + "&year=" + filter_year + "&item_fg_id=" + filter_item,
                            dataType: "json",
                            success: function(get) {
                                Swal.close();
                                
                                if (get.total > 0) {
                                    requestData(get.total, get.rows);

                                    function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                        if (value < 100) {
                                            value = Math.floor((number / total) * 100);
                                            $('#p_upload').progressbar('setValue', value);
                                            $('#p_start').html(number);
                                            $('#p_finish').html(total);

                                            var i = (number - 1);
                                            $("#txt_item").html(json[i].number_customer);

                                            Swal.fire({
                                                title: 'Please Wait for Calculating Data',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            $.ajax({
                                                type: "post",
                                                url: "<?= base_url('costing/costing_products/getData2') ?>",
                                                data: "item_fg_id=" + json[i].item_fg_id + "&period=" + json[i].period + "&qty=" + json[i].qty + "&month=" + filter_month + "&year=" + filter_year,
                                                dataType: "json",
                                                success: function(get2) {
                                                    Swal.close();
                                                    
                                                    if (get2.total > 0) {
                                                        requestData2(get2.total, get2.rows);

                                                        function requestData2(total2, json2, number2 = 1, value2 = 0, success2 = 1, failed2 = 1) {
                                                            if (value2 < 100) {
                                                                value2 = Math.floor((number2 / total2) * 100);
                                                                $('#p_upload2').progressbar('setValue', value2);
                                                                $('#p_start2').html(number2);
                                                                $('#p_finish2').html(total2);

                                                                var z = (number2 - 1);

                                                                $.ajax({
                                                                    type: "post",
                                                                    url: '<?= base_url('costing/costing_products/create') ?>',
                                                                    data: json2[z],
                                                                    dataType: "json",
                                                                    success: function(result) {
                                                                        if (result.theme == "success") {
                                                                            requestData2(total2, json2, number2 + 1, value2, success2 + 1, failed2 + 0);
                                                                        } else {
                                                                            requestData2(total2, json2, number2 + 1, value2, success2 + 0, failed2 + 1);
                                                                        }

                                                                        if (value2 == 100) {
                                                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                                        }

                                                                        if (value == 100) {
                                                                            // calculateProducts();
                                                                            Swal.fire('Good job!', 'Generate Costing Products Completed!', 'success');
                                                                            $("#dg").datagrid('reload');
                                                                        }
                                                                    }
                                                                });
                                                            }
                                                        }
                                                    } else {
                                                        toastr.error("Data Kanban, Supply Material Not Found");
                                                        requestData(total, json, number + 1, value, success + 1, failed + 0);

                                                        if (value == 100) {
                                                            calculateProducts();
                                                            // Swal.fire('Good job!', 'Generate Costing Products Completed!', 'success');
                                                            // $("#dg").datagrid('reload');
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                    }
                                } else {
                                    Swal.fire('Not Found!', 'Data Production Schedules Not Found', 'error');
                                }
                            }
                        });
                    }
                });
            }
        });
    }

    function calculateProducts(){
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        Swal.fire({
            title: 'Please Wait for Finishing Data',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('costing/costing_products/getDataProducts') ?>",
            data: "month=" + filter_month + "&year=" + filter_year + "&item_fg_id=" + filter_item,
            dataType: "json",
            success: function(get) {
                Swal.close();
                
                if (get.total > 0) {
                    requestData2(get.total, get.rows);

                    function requestData2(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload3').progressbar('setValue', value);
                            $('#p_start3').html(number);
                            $('#p_finish3').html(total);

                            var i = (number - 1);

                            $.ajax({
                                type: "post",
                                url: '<?= base_url('costing/costing_products/createProducts') ?>',
                                data: json[i],
                                dataType: "json",
                                success: function(result) {
                                    if (result.theme == "success") {
                                        requestData2(total, json, number + 1, value, success + 1, failed + 0);
                                    } else {
                                        requestData2(total, json, number + 1, value, success + 0, failed + 1);
                                    }

                                    if (value == 100) {
                                        Swal.fire('Good job!', 'Generate Costing Products Completed!', 'success');
                                        $("#dg").datagrid('reload');
                                    }
                                }
                            });
                        }
                    }
                } else {
                    Swal.fire('Not Found!', 'Data Not Found, Please Generate First in Inventory RM', 'error');
                }
            }
        });
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    Swal.fire({
                        title: 'Please Wait for Deleting Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('closing/locks/checkLock') ?>",
                            data: "period=" + row.periode + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function (lock) {
                                if(lock.total > 0){
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('costing/costing_products/delete') ?>',
                                    data: {
                                        id: row.id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        Swal.close();
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        $('#dg').datagrid('reload');
                                    }
                                });
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
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_item=" + window.btoa(filter_item);

        $('#dg').datagrid({
            url: '<?= base_url('costing/costing_products/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('costing/costing_products/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_item=" + window.btoa(filter_item);

        window.location.assign('<?= base_url('costing/costing_products/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function downloadFailed() {
        window.open('<?= base_url('costing/costing_products/uploadDownloadFailed') ?>', '_blank');
    }

    function btnWorkorder(val, row) {
        if(val != "Grand Total"){
            var workorder = "viewWorkorder('" + row.periode + "','" + row.item_fg_id + "','" + row.number_customer + "')";
            return '<a class="btn btn-primary w-100" onClick="' + workorder + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
        }else{
            return val;
        }
    }

    function btnDetails(val, row) {
        var details = "viewDetails('" + row.periode + "','" + row.item_fg_id + "')";
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewWorkorder(periode, item_id, number_customer) {
        $("#dlg_workorder").window('open');
        $("#dlg_workorder").window("setTitle", "Detail Workorder " + number_customer);

        $('#dg_workorder').datagrid({
            url: '<?= base_url('costing/costing_products/datatableWorkorders?periode=') ?>' + btoa(periode) + "&item_id=" + btoa(item_id),
            pagination: false,
            rownumbers: true,
            fit:true,
            showFooter: true
        });
    }

    function viewDetails(periode, item_fg_id) {
        $("#dlg_details").dialog('open');
        $('#dg_details').datagrid('loading');
        
        $.ajax({
            url: '<?= base_url('costing/costing_products/datatableDetails') ?>',
            type: 'POST',
            data: {
                periode: periode,
                item_fg_id: item_fg_id
            },
            dataType: 'json',
            success: function(result) {
                $('#dg_details').datagrid('loadData', result).datagrid('loaded');
            }
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
        $("#add").html("Generate Data Cost");

        $('#dg_details').datagrid({
            pagination: true,
            clientPaging: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            rownumbers: true,
            fit:true
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('costing/costing_products/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('costing/costing_products/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_item').combogrid({
            url: '<?= base_url('costing/costing_products/readProduct') ?>',
            panelWidth: 600,
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
                    field: 'id',
                    title: 'ID',
                    width: 100
                }, {
                    field: 'number',
                    title: 'No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 150
                },]
            ]
        });
    });

    function priceformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat("ja-JP", {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function priceformat2(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat("ja-JP", {
                minimumFractionDigits: 4
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>