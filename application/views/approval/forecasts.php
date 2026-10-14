<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="
    url: '<?= base_url('approvals/approvalForecasts/'.base64_encode($approved_to).'/'.base64_encode($approved_by)) ?>',
    pagination: false,
    clientPaging: false,
    rownumbers: true,
    fit: true">
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Details</th>
            <th rowspan="2" data-options="field:'customer_id',width:120,halign:'center'">Customer ID</th>
            <th rowspan="2" data-options="field:'customer_name',width:220,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'issued_date',width:100,halign:'center'">Issued Date</th>
            <th colspan="2" data-options="field:'',width:200,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th rowspan="2" data-options="field:'reason',width:200,halign:'center'">Reason</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'p_month',width:80,align:'center'"> Month</th>
            <th data-options="field:'p_year',width:80,align:'center'"> Year</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
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
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'item_fg_number',width:150,halign:'center'">Product No EBWS</th>
                <th data-options="field:'number_customer',width:150,halign:'center'">Product No Customer</th>
                <th data-options="field:'item_fg_name',width:250,halign:'center'">Product Name</th>
                <th data-options="field:'month_1',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m1">M1</b></th>
                <th data-options="field:'month_2',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m2">M2</b></th>
                <th data-options="field:'month_3',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m3">M3</b></th>
                <th data-options="field:'month_4',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m4">M4</b></th>
                <th data-options="field:'month_5',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m5">M5</b></th>
                <th data-options="field:'month_6',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m6">M6</b></th>
                <th data-options="field:'month_7',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m7">M7</b></th>
                <th data-options="field:'month_8',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m8">M8</b></th>
                <th data-options="field:'month_9',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m9">M9</b></th>
                <th data-options="field:'month_10',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m10">M10</b></th>
                <th data-options="field:'month_11',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m11">M11</b></th>
                <th data-options="field:'month_12',width:80,halign:'center',align:'right',formatter: numberformat"><b id="m12">M12</b></th>
                <th data-options="field:'remark',width:100,halign:'center'">Remark</th>
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
		$('#dg').datagrid('toExcel', "approval_<?= $table ?>.xls");
	}

	function approve() {
		var rows = $('#dg_details').datagrid('getSelections');
        if (rows.length > 0) {
            $("#dlg_details").window('close');

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

    function BtnDetail(val, row) {
        var details = "details('"+row.customer_id+"','"+row.p_month+"','"+row.p_year+"','"+row.revision+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function details(customer_id, p_month, p_year, revision) {
        $("#dlg_details").window('open');
        $('#dlg_details').window('setTitle', 'Detail of ' + customer_id);

        $.ajax({
            type: "post",
            url: "<?= base_url('planning/forecasts/readPeriodLists') ?>",
            data: "p_month=" + p_month + "&p_year=" + p_year + "&revision=" + revision,
            dataType: "json",
            success: function(result) {
                $('#dg_details').datagrid({
                    url: '<?= base_url('approvals/approvalForecastDetail/'.base64_encode($approved_to).'/'.base64_encode($approved_by)) ?>?customer_id=' + btoa(customer_id) + "&p_month=" + p_month + "&p_year=" + p_year + "&revision=" + revision,
                    clientPaging: false,
                    remoteFilter: true,
                    rownumbers: true,
                    singleSelect: false,
                    fit:true,
                    onLoadSuccess: function(){
                        $("#m1").html(result[0].name);
                        $("#m2").html(result[1].name);
                        $("#m3").html(result[2].name);
                        $("#m4").html(result[3].name);
                        $("#m5").html(result[4].name);
                        $("#m6").html(result[5].name);
                        $("#m7").html(result[6].name);
                        $("#m8").html(result[7].name);
                        $("#m9").html(result[8].name);
                        $("#m10").html(result[9].name);
                        $("#m11").html(result[10].name);
                        $("#m12").html(result[11].name);
                    }
                });
            }
        });
    }
</script>