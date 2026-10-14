<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="
        url: '<?= base_url('approvals/approvalRgpTransactionsIn/' . base64_encode($approved_to) . '/' . base64_encode($approved_by)) ?>',
        pagination: false,
        clientPaging: false,
        rownumbers: true,
        fit: true">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'process_type',width:100,halign:'center',align:'center'">Process<br>Type</th>
            <th data-options="field:'number',width:200,halign:'center'">EBWS Document No</th>
            <th data-options="field:'type',width:60,halign:'center',align:'center'">Type</th>
            <th data-options="field:'rgp_id',width:200,halign:'center'">RGP ID</th>
            <th data-options="field:'transaction_date',width:120,halign:'center'">Transaction<br>Date</th>
            <th data-options="field:'company_id',width:120,halign:'center'">Company ID</th>
            <th data-options="field:'company_name',width:200,halign:'center'">Company Name</th>
            <th data-options="field:'plant_number',width:100,halign:'center'">Plant</th>
            <th data-options="field:'item_category_name',width:150,halign:'center'">Category</th>
            <th data-options="field:'departement_name',width:150,halign:'center'">Departement</th>
            <th data-options="field:'reference_number',width:150,halign:'center'">Reference</th>
            <th data-options="field:'rgp_number',width:200,halign:'center'">RGP Out No.</th>
            <th data-options="field:'customer_order_no',width:150,halign:'center'">Customer Order No</th>
            <th data-options="field:'item_id',width:150,halign:'center'">Part No EBWS</th>
            <th data-options="field:'item_number',width:150,halign:'center'">Part No Supplier</th>
            <th data-options="field:'item_name',width:120,halign:'center'">Description</th>
            <th data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'price',width:120,halign:'center',align:'right',formatter:numberformatPrice">Price</th>
            <th data-options="field:'value',width:120,halign:'center',align:'right',formatter:numberformatPrice">Value</th>
            <th data-options="field:'igst_value',width:120,halign:'center',align:'right',formatter:numberformatPrice">IGST</th>
            <th data-options="field:'sgst_value',width:120,halign:'center',align:'right',formatter:numberformatPrice">SGST</th>
            <th data-options="field:'cgst_value',width:120,halign:'center',align:'right',formatter:numberformatPrice">CGST</th>
            <th data-options="field:'total',width:120,halign:'center',align:'right',formatter:numberformatPrice">Total</th>
            <th data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformat">MPQ</th>
            <th data-options="field:'qty_label',width:80,halign:'center',align:'right',formatter:numberformat">Label</th>
            <th data-options="field:'created_by',width:120,halign:'center'">Create By</th>
            <th data-options="field:'created_date',width:150,halign:'center'">Create Date</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approve()"><i class="fa fa-check"></i> Approve</a>
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>
<script>
    $(function() {
        $('#dg').datagrid('enableFilter');
    });
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function export_excel() {
        $('#dg').datagrid('toExcel', "approval_<?= $table ?>.xls");
    }

    function approve() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Approve Data',
                text: "Are you sure? You want to approve this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Approve it!'
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
                                    tablename: "<?= $table ?>"
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestApprove(total, json, number + 1, value);
                                    if (number == total) {
                                        $('#dg').datagrid('reload');
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

    function disapprove() {
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
                                                $('#dg').datagrid('reload');
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
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatPrice(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 4
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>