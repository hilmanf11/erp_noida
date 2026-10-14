<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="
    url: '<?= base_url('approvals/approvalBom/'.base64_encode($approved_to).'/'.base64_encode($approved_by)) ?>',
    pagination: false,
    clientPaging: false,
    rownumbers: true,
    fit: true">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Details</th>
            <th data-options="field:'drawing',width:80,halign:'center',formatter: btnDrawing">Drawing</th>
            <th data-options="field:'item_fg_number',width:150,halign:'center'">Product No EBWS</th>
            <th data-options="field:'number_customer',width:200,halign:'center'">Product No Customer</th>
            <th data-options="field:'item_fg_name',width:120,halign:'center'">Product Name</th>
            <th data-options="field:'uom',width:80,halign:'center'">Uom</th>
            <th data-options="field:'revision',width:50,align:'center'">Rev</th>
            <th data-options="field:'created_by',width:120,halign:'center'">Create By</th>
            <th data-options="field:'created_date',width:150,halign:'center'">Create Date</th>
        </tr>
    </thead>
</table>

<div id="dlg_details" class="easyui-window" title="Detail Data" data-options="closed: true, modal:true, closed: true, minimizable:false, collapsible:false" style="width: 800px; height: 500px; top: 50px;">
    <table id="dg_details" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'item_rm_id',width:100,halign:'center'">Part No EBWS</th>
                <th data-options="field:'item_rm_number',width:250,halign:'center'">Part No Supplier</th>
                <th data-options="field:'item_rm_name',width:200,halign:'center'">Part Name</th>
                <!-- <th data-options="field:'maker',width:100,halign:'center'">Maker</th>
                <th data-options="field:'type',width:100,halign:'center'">Type</th> -->
                <th data-options="field:'main_process',width:100,halign:'center'">Process Main</th>
                <th data-options="field:'product_family_name',width:150,halign:'center'">Product Family</th>
                <th data-options="field:'uom',width:80,halign:'center'">Uom</th>
                <th data-options="field:'composition_old',width:80,halign:'center',align:'right'">QPA Prev</th>
                <th data-options="field:'composition',width:80,halign:'center',align:'right'">QPA New</th>
                <th data-options="field:'revision',width:50,align:'center'">Rev</th>
                <th data-options="field:'remark',width:100,halign:'center'">Remarks</th>
                <th data-options="field:'created_by',width:120,halign:'center'">Created By</th>
                <th data-options="field:'created_date',width:150,halign:'center'">Created Date</th>
                <th data-options="field:'updated_by',width:120,halign:'center'">Updated By</th>
                <th data-options="field:'updated_date',width:150,halign:'center'">Updated Date</th>
            </tr>
        </thead>
    </table>
</div>

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
                            var row = json[number-1];
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
                    $.messager.prompt('Reason', 'Please input your reason!!', function(message){
                        if (message){
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
                                    var row = json[number-1];
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
                        }else{
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

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnDrawing(val){
        if(val == null || val == ""){
            return "-";
        }else{
            return "<a class='btn btn-primary btn-sm' style='pointer-events: auto; opacity:1; width:100%;' target='_blank' href='<?= base_url('assets/documents/bom/') ?>"+val+"'><i class='fa fa-eye'></i> View</a>";
        }
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.item_fg_id+"','"+row.number_customer+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function details(item_fg_id, number_customer) {
        $("#dlg_details").window('open');
        $('#dlg_details').window('setTitle', 'Detail of ' + number_customer);

        $('#dg_details').datagrid({
            url: '<?= base_url('approvals/approvalBomDetails/'.base64_encode($approved_to).'/'.base64_encode($approved_by)) ?>?item_fg_id=' + btoa(item_fg_id),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
            rowStyler: function(index,row){
                if (row.composition_old != row.composition){
                    return 'background-color:#caffc7;font-weight:bold;';
                }

                if(row.remove == 1){
                    return 'background-color:#e5b5b5;font-weight:bold;';
                }
            }
        });
    }
</script>