<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="
    url: '<?= base_url('approvals/approvalItemFg/'.base64_encode($approved_to).'/'.base64_encode($approved_by)) ?>',
    pagination: false,
    clientPaging: false,
    rownumbers: true,
    fit: true">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'foto',width:35,halign:'center',formatter:FormatterImage">Foto</th>
            <th data-options="field:'number',width:150,halign:'center'">Product No EBWS</th>
            <th data-options="field:'name',width:150,halign:'center'">Description</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th data-options="field:'number_customer',width:150,halign:'center'">Part No Customer</th>
            <th data-options="field:'alias',width:150,halign:'center'">Product No Invoice</th>
            <th data-options="field:'cct',width:80,halign:'center', styler:cellStylerCct">CCT Qty</th>
            <th data-options="field:'customer_name',width:250,halign:'center'">Customer</th>
            <th data-options="field:'harnes_type_name',width:100,halign:'center'">Harness Type</th>
            <th data-options="field:'control_id',width:100,halign:'center'">Control No</th>
            <th data-options="field:'polybag',width:150,halign:'center'">Polybag Label</th>
            <th data-options="field:'box_label',width:70,align:'center'">Box Label</th>
            <th data-options="field:'lot',width:80,align:'center'">Lot<br>Production</th>
            <!-- <th data-options="field:'ng_ration',width:90,align:'center'">NG Ratio (%)</th> -->
            <!-- <th data-options="field:'is_no',width:100,halign:'center'">IS No.</th> -->
            <th data-options="field:'weight',width:100,align:'center'">Weight (gram)</th>
            <th data-options="field:'leadtime',width:100,align:'center'">Prod. Leadtime (day)</th>
            <th data-options="field:'mpq',width:50,align:'center'">MPQ</th>
            <th data-options="field:'moq',width:50,align:'center'">MOQ</th>
            <th data-options="field:'uom',width:50,align:'center'">UoM</th>
            <th data-options="field:'qty_box',width:80,align:'center'">QTY/Box</th>
            <!-- <th data-options="field:'box_sub',width:80,align:'center'">QTY/Sub Box</th> -->
            <th data-options="field:'hsn_code',width:100,halign:'center'">HSN No</th>
            <th data-options="field:'type',width:100,align:'center'">Type</th>
            <th data-options="field:'account_number',width:150,halign:'center'">COA No</th>
            <th data-options="field:'account_name',width:150,halign:'center'">COA Name</th>
            <th data-options="field:'attachment',width:100,halign:'center',formatter:cellbutton">Attachment</th>
            <th data-options="field:'logo',width:100,align:'center', styler:cellStyler, formatter:cellFormatterLogo">Logo</th>
            <th data-options="field:'revision',width:80,halign:'center'">Revision</th>
            <th data-options="field:'status',width:100,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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
                                            Swal.close();
                                            toastr.error("Cannot Disapprove, Because Data already Relation for another module");
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
		const formatter = new Intl.NumberFormat('id-ID');

		return "<b>" + formatter.format(value) + "</b>";
	}

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //CELLSTYLE CCT
    function cellStylerCct(value, row, index) {
        if (row.total_cct == row.cct) {
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

     //FORMATTER LOGO
     function cellFormatterLogo(value) {
        if (value == 0) {
            return 'YES';
        } else {
            return 'NO';
        }
    };

    function FormatterImage(val) {
        if (val == "" || val == null) {
            return '<img style="width: 100%;" src="<?= base_url('assets/image/users/default.png'); ?>" />';
        } else {
            return '<a href="' + val + '" target="_blank"><img style="width: 100%;" src="' + val + '" /></a>';
        }
    };

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    //
    function cellbutton(value) {
        if (value != null) {
            return '<a target="_blank" href="' + value + '" class="btn btn-primary btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-eye"></i> View</a>';
            // alert(value);
        }
    };
</script>