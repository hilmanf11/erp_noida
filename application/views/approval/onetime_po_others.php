<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="
    url: '<?= base_url('approvals/approvalonetimePOther/' . base64_encode($approved_to) . '/' . base64_encode($approved_by)) ?>',
    pagination: false,
    clientPaging: false,
    rownumbers: true,
    fit: true">
    <thead>
        <tr>
            <th data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Details</th>
            <th data-options="field:'po_no',width:150,halign:'center'">PO No</th>
            <th data-options="field:'po_date',width:120,halign:'center'">PO Date</th>
            <th data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th data-options="field:'supplier_name',width:300,halign:'center'">Supplier Name</th>
            <th data-options="field:'type',width:120,halign:'center'">Supplier Type</th>
            <th data-options="field:'state',width:120,halign:'center'">State</th>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'grandtotal',width:150,halign:'center',align:'right',formatter: numberformatPrice">Grand Total</th>
            <th data-options="field:'remarks',width:280,halign:'center'">Note</th>
            <th data-options="field:'payment_term',width:280,halign:'center'">Payment Term</th>
            <th data-options="field:'incoterm',width:280,halign:'center'">Incoterm</th>
            <th data-options="field:'terms',width:100,halign:'center',formatter:btnTerms">Terms<br>Condition</th>
            <!-- <th data-options="field:'remarks1',width:200,halign:'center'">Remarks Approval 1</th> -->
            <!-- <th data-options="field:'remarks2',width:200,halign:'center'">Remarks Approval 2</th> -->
            <!-- <th data-options="field:'remarks3',width:200,halign:'center'">Remarks Approval 3</th> -->
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar2" style="height: 35px;">
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approve()"><i class="fa fa-check"></i> Approve</a>
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
</div>

<div id="dlg_details" class="easyui-window" title="Detail Data" data-options="closed: true, modal:true, closed: true, minimizable:false, collapsible:false" style="width: 800px; height: 500px; top: 50px;">
    <table id="dg_details" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead frozen="true">
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'item_number',width:150,halign:'center'">Part No</th>
                <th data-options="field:'item_name',width:250,halign:'center'">Part Name</th>
            </tr>
        </thead>
        <thead>
            <tr>
                <th data-options="field:'item_family_name',width:150,halign:'center'">Product Family</th>
                <th data-options="field:'uom',width:80,halign:'center'">Uom</th>
                <th data-options="field:'mpq',width:80,halign:'center'">MPQ</th>
                <th data-options="field:'moq',width:80,halign:'center'">MOQ</th>
                <th data-options="field:'qty',width:80,halign:'center',align:'right',formatter: numberformat">Qty</th>
                <th data-options="field:'currency',width:80,halign:'center'">Currency</th>
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter: numberformatPrice">Price</th>
                <th data-options="field:'total',width:100,halign:'center',align:'right',formatter: numberformatPrice">Total</th>
                <th data-options="field:'created_by',width:120,halign:'center'">Create By</th>
                <th data-options="field:'created_date',width:150,halign:'center'">Create Date</th>
            </tr>
        </thead>
    </table>
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
        $('#dg_details').datagrid('toExcel', "approval_<?= $table ?>.xls");
    }

    function approve() {
        var rows = $('#dg_details').datagrid('getSelections');
        if (rows.length > 0) {
            $("#dlg_details").window('close');

            Swal.fire({
                title: 'Approve Data',
                text: "Are you sure? You want to Approve this data!",
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
                                    tablename: "purchase_order_others",
                                    // message: message
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestApprove(total, json, number + 1, value);

                                    if (number == total) {
                                        $('#dg_details').datagrid('reload');
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
                                            tablename: "purchase_order_others",
                                            message: message
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');
                                            requestApprove(total, json, number + 1, value);

                                            if (number == total) {
                                                $('#dg_details').datagrid('reload');
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

    function BtnAttachment(val, row) {
        if (val == "" || val == null) {
            return '-';
        } else {
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="' + val + '"><i class="fa fa-paperclip"></i> View</a>';
        }
    }

    function BtnDetail(val, row) {
        var details = "details('" + row.po_no + "','" + row.supplier_name + "')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="' + details + '"><i class="fa fa-eye"></i> View</a>';
    }

    function details(po_no, supplier_name) {
        $("#dlg_details").window('open');
        $('#dlg_details').window('setTitle', 'Detail of ' + supplier_name);

        $('#dg_details').datagrid({
            url: '<?= base_url('approvals/approvalonetimePOtherDetails/' . base64_encode($approved_to) . '/' . base64_encode($approved_by)) ?>?po_no=' + btoa(po_no),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit: true,
        });
    }

    function btnTerms(val, row) {
        var details = "terms('" + row.po_no + "')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="' + details + '"><i class="fa fa-eye"></i> View</a>';
    }

    function terms(po_no) {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_orders_2/readTerm') ?>",
            data: "po_no=" + po_no,
            dataType: "json",
            success: function(json) {
                Swal.fire({
                    title: 'Terms Condition',
                    html: json.terms,
                    showCloseButton: true,
                    confirmButtonText: 'Got it!'
                });
            }
        });
    }
</script>