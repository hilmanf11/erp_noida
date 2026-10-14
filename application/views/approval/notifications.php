<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="
    url: '<?= base_url('notifications/datatables/'.base64_encode($name).'/'.base64_encode($user_id_from).'/'.base64_encode($user_id_to)) ?>',
    pagination: false,
    clientPaging: false,
    rownumbers: true,
    fit: true">
    <thead>
    <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'id',width:120,halign:'center'">ID</th>
            <th data-options="field:'name',width:120,halign:'center'">Action</th>
            <th data-options="field:'table_name',width:150,halign:'center'">Module</th>
            <th data-options="field:'reason',width:200,halign:'center'">Reason</th>
            <th data-options="field:'logs',width:100,formatter:BtnDetail">Detail</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
	<a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="deleteData()"><i class="fa fa-times"></i> Delete</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<div id="dlg_detail" class="easyui-dialog" title="Detail Description" data-options="closed: true,modal:true" style="width: 500px; height: 500px; padding:10px; top: 20px;">
    <div id="remarks">

    </div>
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

	function deleteData() {
		var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Delete Data',
                text: "Are you sure? You want to Delete this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Delete it!'
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
                                url: '<?= base_url('notifications/delete') ?>',
                                data: {
                                    id: row.id
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestApprove(total, json, number + 1, value);

                                    if (number == total) {
                                        $('#dg').datagrid('reload');
                                        Swal.close();
                                        Swal.fire(
                                            'Delete Completed',
                                            'Delete Data has been completed, You cannot restore data that has been Delete',
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

    function details(id) {
        $("#dlg_detail").dialog('open');
        $.ajax({
            type: "post",
            url: "<?= base_url('notifications/datatableDetails') ?>",
            data: "id=" + id,
            dataType: "html",
            success: function(remarks) {
                $("#remarks").html(remarks);
            }
        });
    }

    function BtnDetail(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="details(' + row.id + ')"><i class="fa fa-eye"></i> Details</a>';
    }
</script>