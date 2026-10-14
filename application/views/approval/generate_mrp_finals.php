<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="
    pagination: false,
    clientPaging: false,
    rownumbers: true,
    fit: true">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Details</th>
            <th data-options="field:'period',width:80,align:'center'">Period</th>
            <th data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th data-options="field:'supplier_po',width:150,halign:'center'">Supplier PO</th>
            <th data-options="field:'supplier_name',width:300,halign:'center'">Supplier Name</th>
            <th data-options="field:'type',width:120,halign:'center'">Supplier Type</th>
            <th data-options="field:'state',width:120,halign:'center'">State</th>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'total_qty',width:100,align:'right',halign:'center',formatter:numberformat">Total Qty</th>
            <th data-options="field:'total_amount',width:120,align:'right',halign:'center',formatter:numberformatPrice">Total Amount</th>
            <!-- <th data-options="field:'remarks1',width:200,halign:'center'">Remarks Approval 1</th>
            <th data-options="field:'remarks2',width:200,halign:'center'">Remarks Approval 2</th>
            <th data-options="field:'remarks3',width:200,halign:'center'">Remarks Approval 3</th> -->
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approveAll()"><i class="fa fa-check"></i> Approve All</a>
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="disapproveAll()"><i class="fa fa-times"></i> Disapprove All</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel Summary</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel2()"><i class="fa fa-file"></i> Export Excel Details</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>

    <div style="float: right;">
        Period :
        <input style="width: 150px;" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
        <input style="width: 80px;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
        <input style="width: 80px;" id="filter_revision" class="easyui-combobox" data-options="prompt:'Rev'" panelHeight="auto">
        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
    </div>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar2" style="height: 35px;">
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approve()"><i class="fa fa-check"></i> Approve</a>
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel1()"><i class="fa fa-file"></i> Export Excel</a>
</div>

<div id="dlg_details" class="easyui-window" title="Detail Data" data-options="closed: true, modal:true, closed: true, minimizable:false, collapsible:false" style="width: 800px; height: 500px; top: 50px;">
    <table id="dg_details" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead frozen="true">
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'period',width:80,halign:'center'">Period</th>
                <th data-options="field:'item_rm_id',width:100,halign:'center'">Part No EBWS</th>
                <th data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            </tr>
        </thead>
        <thead>
            <tr>
                <th data-options="field:'item_rm_name',width:250,halign:'center'">Description</th>
                <th data-options="field:'product_family',width:120,halign:'center'">Product Family</th>
                <th data-options="field:'price',width:100,halign:'center'">Price</th>
                <th data-options="field:'class',width:80,halign:'center'">ABC <br> Class</th>
                <th data-options="field:'leadtime',width:80,halign:'center'">Leadtime</th>
                <th data-options="field:'mpq',width:80,halign:'center'">MPQ</th>
                <th data-options="field:'moq',width:80,halign:'center'">MOQ</th>
                <th data-options="field:'qty_rm',width:80,halign:'center',align:'right',formatter: numberformat">WHS</th>
                <th data-options="field:'qty_wip',width:80,halign:'center',align:'right',formatter: numberformat">WIP</th>
                <th data-options="field:'qty_vendor',width:80,halign:'center',align:'right',formatter: numberformat">OS PO</th>
                <th data-options="field:'qty_supply',width:80,halign:'center',align:'right',formatter: numberformat">OS Supply</th>
                <th data-options="field:'qty_wo',width:80,halign:'center',align:'right',formatter: numberformat">OS WO</th>
                <th data-options="field:'balance_1',width:120,halign:'center',align:'right',formatter: numberformat">BALANCE 1</th>
                <th data-options="field:'balance_2',width:120,halign:'center',align:'right',formatter: numberformat">BALANCE 2</th>
                <th data-options="field:'balance_3',width:120,halign:'center',align:'right',formatter: numberformat">BALANCE 3</th>
                <th data-options="field:'balance_4',width:120,halign:'center',align:'right',formatter: numberformat">BALANCE 4</th>
                <th data-options="field:'balance_5',width:120,halign:'center',align:'right',formatter: numberformat">BALANCE 5</th>
                <th data-options="field:'balance_6',width:120,halign:'center',align:'right',formatter: numberformat">BALANCE 6</th>
                <th data-options="field:'share_order',width:80,halign:'center',align:'right',formatter: numberformat">Share Order<br>(%)</th>
                <th data-options="field:'safety_stock_persen',width:80,halign:'center',align:'right',formatter: numberformat">Safety Stock<br>(%)</th>
                <th data-options="field:'safety_stock',width:80,halign:'center',align:'right',formatter: numberformat">Safety Stock</th>
                <th data-options="field:'share_order_qty',width:120,halign:'center',align:'right',formatter: numberformat">Share Order</th>
                <th data-options="field:'total_need',width:120,halign:'center',align:'right',formatter: numberformat">Total Need</th>
                <th data-options="field:'mrp_result',width:120,halign:'center',align:'right',formatter: numberformat">MRP Result</th>
                <th data-options="field:'currency',width:80,halign:'center',align:'center'">Currency</th>
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter: numberformatPrice2">Price</th>
                <th data-options="field:'total_amount',width:120,halign:'center',align:'right',formatter: numberformatPrice">Total Amount</th>
                <th data-options="field:'created_by',width:120,halign:'center'">Create By</th>
                <th data-options="field:'created_date',width:150,halign:'center'">Create Date</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    $(function() {
        $('#dg').datagrid('enableFilter');

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            onSelect: function(month) {
                var filter_year = $("#filter_year").combobox('getValue');

                $('#filter_revision').combobox({
                    url: '<?php echo base_url('planning/generate_mrp/readRevisions?filter_month='); ?>' + btoa(month.id) + "&filter_year=" + btoa(filter_year),
                    valueField: 'revision',
                    textField: 'revision',
                    prompt: 'Rev',
                    formatter: function(val) {
                        return "Revision " + val.revision;
                    },
                    onLoadSuccess: function(row) {
                        $('#filter_revision').combobox('setValue', row[0].revision);
                    }
                });
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            onSelect: function(year) {
                var filter_month = $("#filter_month").combobox('getValue');

                $('#filter_revision').combobox({
                    url: '<?php echo base_url('planning/generate_mrp/readRevisions?filter_month='); ?>' + btoa(filter_month) + "&filter_year=" + btoa(year.id),
                    valueField: 'revision',
                    textField: 'revision',
                    prompt: 'Rev',
                    formatter: function(val) {
                        return "Revision " + val.revision;
                    },
                    onLoadSuccess: function(row) {
                        $('#filter_revision').combobox('setValue', "<?= $revision ?>");
                        filter();
                    }
                });
            }
        });
    });

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        $('#dg').datagrid('loading');

        $.ajax({
            url: '<?= base_url('approvals/approvalGenerateMrpFinals/' . base64_encode($approved_to) . '/' . base64_encode($approved_by)) ?>',
            type: 'POST',
            data: {
                filter_month: filter_month,
                filter_year: filter_year,
                filter_revision: filter_revision
            },
            dataType: 'json',
            success: function(result) {
                $('#dg').datagrid('loadData', result).datagrid('loaded');
            }
        });
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function export_excel() {
        $('#dg').datagrid('toExcel', "approval_<?= $table ?>_summary.xls");
    }

    function export_excel2() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        window.location.assign('<?= base_url('planning/generate_mrp/print/excel/' . base64_encode($approved_to) . '/' . base64_encode($approved_by)) ?>?filter_month=' + btoa(filter_month) + "&filter_year=" + btoa(filter_year) + "&filter_revision=" + btoa(filter_revision));
    }

    function export_excel1() {
        $('#dg_details').datagrid('toExcel', "approval_<?= $table ?>_details.xls");
    }

    function approveAll() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.prompt('Remarks', 'Please input your Remarks!!', function(message) {
                if (message) {
                    Swal.fire({
                        title: 'Please Wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    requestApprove(rows.length, rows);

                    function requestApprove(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number - 1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('approvals/approveAll') ?>',
                                data: {
                                    supplier_id: row.supplier_id,
                                    period: row.period,
                                    revision: row.revision,
                                    tablename: "<?= $table ?>",
                                    message: message
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestApprove(total, json, number + 1, value);

                                    if (number == total) {
                                        filter();
                                        Swal.close();
                                        Swal.fire(
                                            'Approve Completed',
                                            'Approve Data has been completed, You cannot restore data that has been approved',
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

    function approve() {
        var rows = $('#dg_details').datagrid('getSelections');
        if (rows.length > 0) {
            $("#dlg_details").window('close');

            $.messager.prompt('Remarks', 'Please input your Remarks!!', function(message) {
                if (message) {
                    Swal.fire({
                        title: 'Please Wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    requestApprove(rows.length, rows);

                    function requestApprove(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number - 1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('approvals/approve') ?>',
                                data: {
                                    id: row.id,
                                    tablename: "<?= $table ?>",
                                    message: message
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestApprove(total, json, number + 1, value);

                                    if (number == total) {
                                        $('#dg_details').datagrid('reload');
                                        filter();
                                        Swal.close();
                                        Swal.fire(
                                            'Approve Completed',
                                            'Approve Data has been completed, You cannot restore data that has been approved',
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

    function disapproveAll() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Dispprove Data',
                text: "Are you sure? You want to Dispprove this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Dispprove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.messager.prompt('Reason', 'Please input your reason!!', function(message) {
                        if (message) {
                            Swal.fire({
                                title: 'Please Wait...',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            requestApprove(rows.length, rows);

                            function requestApprove(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    var row = json[number - 1];
                                    value = Math.floor((number / total) * 100);

                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('approvals/disapproveAll') ?>',
                                        data: {
                                            supplier_id: row.supplier_id,
                                            period: row.period,
                                            revision: row.revision,
                                            tablename: "<?= $table ?>",
                                            message: message
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');
                                            requestApprove(total, json, number + 1, value);

                                            if (number == total) {
                                                filter();
                                                Swal.close();
                                                Swal.fire(
                                                    'Dispprove Completed',
                                                    'Dispprove Data has been completed, You cannot restore data that has been Dispproved',
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
                        } else {
                            Swal.fire(
                                'Cannot Process',
                                'Please Input your reason to Process Disapprove',
                                'error'
                            );
                        }
                    });
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    function disapprove() {
        var rows = $('#dg_details').datagrid('getSelections');
        if (rows.length > 0) {
            $("#dlg_details").window('close');

            Swal.fire({
                title: 'Dispprove Data',
                text: "Are you sure? You want to Dispprove this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Dispprove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.messager.prompt('Reason', 'Please input your reason!!', function(message) {
                        if (message) {
                            Swal.fire({
                                title: 'Please Wait...',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            requestApprove(rows.length, rows);

                            function requestApprove(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    var row = json[number - 1];
                                    value = Math.floor((number / total) * 100);

                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('approvals/disapprove') ?>',
                                        data: {
                                            id: row.id,
                                            tablename: "<?= $table ?>",
                                            message: message
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');
                                            requestApprove(total, json, number + 1, value);

                                            if (number == total) {
                                                $('#dg_details').datagrid('reload');
                                                filter();
                                                Swal.close();
                                                Swal.fire(
                                                    'Dispprove Completed',
                                                    'Dispprove Data has been completed, You cannot restore data that has been Dispproved',
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
                        } else {
                            Swal.fire(
                                'Cannot Process',
                                'Please Input your reason to Process Disapprove',
                                'error'
                            );
                        }
                    });
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP');

        return formatter.format(value);
    }

    function numberformatPrice2(value, row) {
        var digit = 4;

        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: digit
        });

        return formatter.format(value);
    }

    function numberformatPrice(value, row) {
        if (row.currency == "INR") {
            var digit = 2;
        } else {
            var digit = 4;
        }

        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: digit
        });

        return formatter.format(value);
    }

    function BtnAttachment(val, row) {
        if (val == "" || val == null) {
            return '-';
        } else {
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="' + val + '"><i class="fa fa-paperclip"></i> View</a>';
        }
    }

    function BtnDetail(val, row) {
        var details = "details('" + row.supplier_id + "','" + row.supplier_name + "','" + row.period + "','" + row.revision + "')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="' + details + '"><i class="fa fa-eye"></i> View</a>';
    }

    function details(supplier_id = "", supplier_name = "", period = "", revision = "") {
        $("#dlg_details").window('open');
        $('#dlg_details').window('setTitle', 'Detail of ' + supplier_name);

        $('#dg_details').datagrid({
            url: '<?= base_url('approvals/approvalGenerateMrpFinalDetails/' . base64_encode($approved_to) . '/' . base64_encode($approved_by)) ?>?supplier_id=' + btoa(supplier_id) + "&period=" + btoa(period) + "&revision=" + btoa(revision),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit: true,
        });
    }
</script>