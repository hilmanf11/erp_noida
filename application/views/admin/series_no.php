<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;">
    <thead>
        <tr>
            <th data-options="field:'name',width:250">Menu Name</th>
            <!-- <th data-options="field:'status',width:200">Status</th> -->
            <th data-options="field:'detail',width:100,formatter:BtnDetail">Detail</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Menu ID</span>
                <input style="width:60%;" name="menus_id" id="menus_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Header</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Start Date</span>
                <input style="width:60%;" name="start_date" id="start_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">End Date</span>
                <input style="width:60%;" name="end_date" id="end_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Sequence</span>
                <input style="width:60%;" name="sequence" id="sequence" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Format</span>
                <input style="width:50%;" name="format" id="format" class="easyui-numberbox" required readonly>
                <a class="btn btn-primary" style="pointer-events: visible; opacity:1;" onClick="formatNo()"><i class="fa fa-rotate-right"></i></a>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" name="status" class="easyui-combobox">
                    <option value="0">ACTIVE</option>
                    <option value="1">NOT ACTIVE</option>
                </select>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_detail" class="easyui-dialog" title="Detail Data" data-options="closed: true,modal:true" style="width: 80%; top: 0px;">
    <table id="dg_detail" class="easyui-datagrid" style="width:99.5%; height: 400px;" toolbar="#toolbar">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'menu_name',width:250">Menu Name</th>
                <th data-options="field:'number',width:100">Header</th>
                <th data-options="field:'start_date',width:150">Start Date</th>
                <th data-options="field:'end_date',width:150">End Date</th>
                <th data-options="field:'format',width:150">Format</th>
                <th data-options="field:'status',width:80, styler:cellStyler, formatter:cellFormatter">Status</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('admin/series_no/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('admin/series_no/create') ?>';
        $("#sequence").numberbox('setValue', 7);
        // $('#frm_insert').form('clear');
    }

    function formatNo(){
        var number = $("#number").textbox('getValue');
        var start_date = $("#start_date").datebox('getValue');
        var end_date = $("#end_date").datebox('getValue');
        var sequence = $("#sequence").numberbox('getValue');

        if(number == "" && start_date == "" && end_date == ""){
            toastr.error("Please Complete Your Input");
        }else{
            $.ajax({
                type: "post",
                url: "<?= base_url("admin/series_no/format_no") ?>",
                data: {number: number, start_date: start_date, end_date: end_date, sequence: sequence},
                dataType: "json",
                success: function (response) {
                    $("#format").textbox("setValue", response.format);
                }
            });
        }
    }

    //EDIT DATA
    function update() {
        var row = $('#dg_detail').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('admin/series_no/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg_detail').datagrid('getSelections');
        if (rows.length > 0) {
            $('#dlg_detail').dialog('close');

            Swal.fire({
                title: 'Delete Data',
                text: "Are you sure? You want to delete this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, delete it!'
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

                    requestDelete(rows.length, rows);
                    function requestDelete(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('admin/series_no/delete') ?>',
                                data: {
                                    id: row.id
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestDelete(total, json, number + 1, value);

                                    if (number == total) {
                                        $('#dg_detail').datagrid('reload');
                                        Swal.close();
                                        Swal.fire(
                                            'Delete Completed',
                                            'Delete Data has been completed, You cannot restore data that has been deleted',
                                            'success'
                                        );

                                        $('#dlg_detail').dialog('open');
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

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT EXCEL
    function excel() {
        window.location.assign('<?= base_url('admin/series_no/print/excel') ?>');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.id+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-cog"></i> Setup</a>';
    }

    function details(menus_id) {
        $("#dlg_detail").dialog('open');
        $("#menus_id").textbox('setValue', menus_id);

        $('#dg_detail').datagrid({
            url: '<?= base_url('admin/series_no/datatableDetails/') ?>' + btoa(menus_id),
            pagination: true,
            clientPaging: false,
            rownumbers: true,
            singleSelect: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
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

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('admin/series_no/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');
        
        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            if($(this).form('validate') === true){
                                $('#dlg_insert').dialog('close');
                                
                                Swal.fire({
                                    title: 'Please Wait...',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });
                            }else{
                                return $(this).form('validate');
                            }
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg_detail').datagrid('reload');
                            Swal.close();
                        }
                    });
                }
            }]
        });
    });
</script>