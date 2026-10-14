<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'created_by',width:180">Created By</th>
            <th data-options="field:'created_date',width:180">Created Date</th>
            <th data-options="field:'ip_address',width:100">Ip Address</th>
            <th data-options="field:'action',width:100">Action</th>
            <th data-options="field:'menu',width:180">Module</th>
            <th data-options="field:'description',width:300">Description</th>
            <th data-options="field:'detail',width:100,formatter:BtnDetail">Detail</th>
            <th data-options="field:'recovery',width:100,formatter:BtnAction">Action</th>
        </tr>
    </thead>
</table>
<div id="dlg_detail" class="easyui-dialog" title="Detail Description" data-options="closed: true,modal:true" style="width: 1000px; height: 550px; padding:10px; top: 20px;">
    <div id="remarks">

    </div>
</div>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>
<!-- PDF -->
<!-- <iframe id="printout" src="<?= base_url('admin/logs/print') ?>" style="width: 100%;" hidden></iframe> -->
<script>
    //DELETE DATA
    // function deleted() {
    //     var rows = $('#dg').datagrid('getSelections');
    //     if (rows.length > 0) {
    //         Swal.fire({
    //             title: 'Delete Data',
    //             text: "Are you sure? You want to delete this data!",
    //             icon: 'warning',
    //             showCancelButton: true,
    //             allowOutsideClick: false,
    //             allowEscapeKey: false,
    //             confirmButtonText: 'Yes, delete it!'
    //         }).then((result) => {
    //             if (result.isConfirmed) {
    //                 Swal.fire({
    //                     title: 'Please Wait...',
    //                     showConfirmButton: false,
    //                     allowOutsideClick: false,
    //                     allowEscapeKey: false,
    //                     didOpen: () => {
    //                         Swal.showLoading();
    //                     },
    //                 });

    //                 requestDelete(rows.length, rows);
    //                 function requestDelete(total, json, number = 1, value = 0) {
    //                     if (value < 100) {
    //                         var row = json[number-1];
    //                         value = Math.floor((number / total) * 100);

    //                         $.ajax({
    //                             method: 'post',
    //                             url: '<?= base_url('admin/logs/delete') ?>',
    //                             data: {
    //                                 id: row.id
    //                             },
    //                             success: function(result) {
    //                                 var result = eval('(' + result + ')');
    //                                 requestDelete(total, json, number + 1, value);

    //                                 if (number == total) {
    //                                     $('#dg').datagrid('reload');
    //                                     Swal.close();
    //                                     Swal.fire(
    //                                         'Delete Completed',
    //                                         'Delete Data has been completed, You cannot restore data that has been deleted',
    //                                         'success'
    //                                     );
    //                                 }
    //                             },
    //                             error: function(jqXHR, textStatus, errorThrown) {
    //                                 toastr.error(jqXHR.statusText);
    //                             },
    //                         });
    //                     }
    //                 }
    //             }
    //         });
    //     } else {
    //         toastr.info("Please select one of the data in the table first");
    //     }
    // }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('admin/logs/datatables') ?>',
            pagination: false,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');
    });
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT EXCEL
    function excel() {
        window.location.assign('<?= base_url('admin/logs/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function details(id) {
        $("#dlg_detail").dialog('open');
        $.ajax({
            type: "post",
            url: "<?= base_url('admin/logs/details') ?>",
            data: "id=" + id,
            dataType: "html",
            success: function(remarks) {
                $("#remarks").html(remarks);
            }
        });
    }

    function recovery(id, menu) {
        Swal.fire({
            title: 'Recovery Data',
            text: "Are you sure? You want to recovery this data!",
            icon: 'warning',
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonText: 'Yes, Do it!'
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

                $.ajax({
                    type: "post",
                    url: "<?= base_url('admin/logs/recovery') ?>",
                    data: "id=" + id + "&menu=" + menu,
                    dataType: "json",
                    success: function(json) {
                        Swal.close();
                        Swal.fire(
                            json.title,
                            json.message,
                            json.theme
                        );
                    }, 
                    error: function(jqXHR, textStatus, errorThrown) {
                        try {
                            var response = JSON.parse(jqXHR.responseText);
                            Swal.fire(
                                response.title,
                                response.message + " | " + response.filename,
                                response.theme
                            );
                        } catch (e) {
                            Swal.fire(
                                'Error',
                                jqXHR.responseText,
                                'error'
                            );
                        }
                    }
                });
            }
        });
    }

    function BtnDetail(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="details(' + row.id + ')"><i class="fa fa-eye"></i> Details</a>';
    }

    function BtnAction(val, row) {
        if(row.action == "Create"){
            var onclick = "recovery('" + row.id + "','"+row.menu+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onclick="'+onclick+'"><i class="fa fa-rotate-right"></i> Recovery</a>';
        }else{
            return '-';
        }
    }
</script>