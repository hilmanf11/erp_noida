<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Process</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusFormat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'request_no',width:150,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'trans_date',width:120,align:'center'">Request Date</th>
            <th rowspan="2" data-options="field:'request_name',width:200,halign:'center'">Request Name</th>
            <th rowspan="2" data-options="field:'total_qty',width:80,halign:'center',align:'right', formatter:numberformat">Total Qty</th>
            <th rowspan="2" data-options="field:'reason',width:200,halign:'center'">Note</th>
            <th rowspan="2" data-options="field:'location_other_number',width:100,halign:'center'">Location Store</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: auto; padding:5px;">
    <div class="fitem">
        <?= $button ?>
        <span style="width:60px; display:inline-block; margin-left:10px;">Part No</span>
        <input style="width:20%;" id="filter_part_no" class="easyui-combogrid">
        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
    </div>
</div>

<div id="toolbar2" style="height: auto; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Detail</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" id="request_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" id="request_name" class="easyui-textbox" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Location Store</span>
                    <input style="width:60%;" id="location_other_number" class="easyui-textbox" readonly>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:60%;" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Accepted Date</span>
                    <input style="width:60%;" id="out_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Status</span>
                    <input style="width:60%;" id="status" class="easyui-numberbox" readonly>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<div id="dlg_detail" class="easyui-dialog" title="Detail Request No" data-options="closed: true,modal:true" style="width: 85%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_detail" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2" showFooter="true">
        <!-- <thead>
            <tr>
                <th data-options="field:'item_rm_id',width:120,halign:'center'">Part No EBWS</th>
                <th data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
                <th data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
                <th data-options="field:'uom',width:80,align:'center'">UoM</th>
                <th data-options="field:'stock',width:80,halign:'center',align:'right',formatter:numberformat">Stock</th>
                <th data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
                <th data-options="field:'balance',width:80,halign:'center',align:'center',formatter:numberformat">Balance</th>
                <th data-options="field:'remarks',width:200,halign:'center'">Remarks</th>
            </tr>
        </thead> -->
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/issued_others_out/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/issued_others_out/delete') ?>',
                            data: {
                                request_no: row.request_no
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("This data is already used in other modules, You must delete it First", "Error");

                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('warehouse/issued_others_out/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function filter() {
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        url = "?filter_part_no=" + window.btoa(filter_part_no);

        $('#dg').datagrid('load', {
            filter_part_no: filter_part_no
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/issued_others_out/print') ?>' + url);
    }

    $('#dg').datagrid({
        url: '<?= base_url('warehouse/issued_others_out/datatables') ?>',
        pagination: true,
        clientPaging: false,
        remoteFilter: true,
        rownumbers: true,
        fit: true,
        pageList: [20, 50, 100, 500, 1000],
        pageSize: 20,
    });

    $(function() {

        $('#dg').datagrid().datagrid('enableFilter');

        //SAVE DATA
        $('#dlg_detail').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var status = $("#status").numberbox('getValue');

                    if (status == 0) {
                        $.messager.confirm('Warning', 'Are you sure you want to save this data', function(r) {
                            if (r) {
                                var request_no = $("#request_no").textbox('getValue');
                                var trans_date = $("#trans_date").datebox('getValue');
                                var request_name = $("#request_name").textbox('getValue');
                                var out_date = $("#out_date").datebox('getValue');
                                var location_other_number = $("#location_other_number").textbox('getValue');

                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url('warehouse/issued_others_out/numberDocStore/') ?>" + location_other_number,
                                    dataType: "html",
                                    success: function(result) {
                                        $('#dg_detail').datagrid('acceptChanges');
                                        var rows = $('#dg_detail').datagrid('getRows');
                                        var totalrows = rows.length;

                                        for (let z = 0; z < totalrows; z++) {
                                            if(parseInt(rows[z].qty) > parseInt(rows[z].ending_stock)){
                                                toastr.error("Part No "+rows[z].item_rm_number+" Qty Cannot > Ending Stock OT");
                                                return false;
                                            }
                                        }

                                        if (totalrows > 0) {
                                            for (let i = 0; i < totalrows; i++) {
                                                if (rows[i].balance >= 0) {
                                                    $.ajax({
                                                        type: "post",
                                                        url: '<?= base_url('warehouse/issued_others_out/update') ?>',
                                                        data: {
                                                            request_no: request_no,
                                                            trans_date: trans_date,
                                                            request_name: request_name,
                                                            location_other_number: location_other_number,
                                                            document_in: result,
                                                            out_by: "<?= $this->session->name ?>",
                                                            out_date: out_date,
                                                            item_rm_id: rows[i].item_rm_id,
                                                            stock: rows[i].stock,
                                                            qty: rows[i].qty,
                                                            balance: rows[i].balance,
                                                            remarks: rows[i].remarks,
                                                            status_store: '0'
                                                        },
                                                        dataType: "json",
                                                        success: function(result) {
                                                            Swal.fire({
                                                                title: result.message,
                                                                icon: result.theme,
                                                                confirmButtonText: 'Ok',
                                                                allowOutsideClick: false,
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    window.location.reload();
                                                                }
                                                            });
                                                        }
                                                    });
                                                } else {
                                                    toastr.error("Failed to Save Data, Balance cannot empty");
                                                }
                                            }

                                            $('#dg').datagrid('reload');
                                            $('#dlg_detail').dialog('close');
                                        } else {
                                            toastr.warning("Please Select data in the table first!");
                                        }
                                    }
                                });


                            }
                        });
                    } else {
                        toastr.error("The Data Already Created");
                    }
                }
            }, {
                text: 'Cancel',
                handler: function() {
                    var status = $("#status").numberbox('getValue');

                    if (status == 0) {
                        $.messager.prompt('Reason', 'Please input the reason, why the Request is Cancel ?', function(r) {
                            if (r) {
                                var request_no = $("#request_no").textbox('getValue');
                                var trans_date = $("#trans_date").datebox('getValue');
                                var request_name = $("#request_name").textbox('getValue');
                                var out_date = $("#out_date").datebox('getValue');

                                $('#dg_detail').datagrid('acceptChanges');
                                var rows = $('#dg_detail').datagrid('getRows');
                                var totalrows = rows.length;

                                if (totalrows > 0) {
                                    for (let i = 0; i < totalrows; i++) {
                                        if (rows[i].item_rm_id) {
                                            $.ajax({
                                                type: "post",
                                                url: '<?= base_url('warehouse/issued_others_out/canceled') ?>',
                                                data: {
                                                    request_no: request_no,
                                                    trans_date: trans_date,
                                                    request_name: request_name,
                                                    out_date: out_date,
                                                    out_by: "<?= $this->session->name ?>",
                                                    item_rm_id: rows[i].item_rm_id,
                                                    stock: rows[i].stock,
                                                    remarks: rows[i].remarks,
                                                    status: '2',
                                                    status_store: '2',
                                                    reason: r
                                                },
                                                dataType: "json",
                                                success: function(result) {
                                                    Swal.fire({
                                                        title: result.message,
                                                        icon: result.theme,
                                                        confirmButtonText: 'Ok',
                                                        allowOutsideClick: false,
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            window.location.reload();
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    }

                                    $('#dg').datagrid('reload');
                                    $('#dlg_detail').dialog('close');
                                } else {
                                    toastr.warning("Please Select data in the table first!");
                                }
                            }
                        });
                    } else {
                        toastr.error("The Data Already Created");
                    }
                }
            }]
        });

        $('#item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
            onSelect: function(valItem, rowItem) {
                $("#uom").textbox('setValue', rowItem.uom);
            }
        });
    });

    function details(request_no, trans_date, request_name, status, location_other_number) {
        $("#dlg_detail").dialog('open');

        $("#request_no").textbox('setValue', request_no);
        $("#request_name").textbox('setValue', request_name);
        $("#trans_date").datebox('setValue', trans_date);
        $("#status").numberbox('setValue', status);
        $("#location_other_number").textbox('setValue', location_other_number);
        $("#out_date").datebox('setValue', "<?= date("Y-m-d") ?>");

        // $('#dg_detail').datagrid({
        //     url: "<?= base_url('warehouse/issued_others_out/datatableDetails/') ?>" + btoa(request_no),
        //     clientPaging: false,
        //     remoteFilter: true,
        //     rownumbers: true,
        //     singleSelect: false,
        //     fit:true,
        // });

        var lastIndex;
        var dg = $('#dg_detail').datagrid({
            url: "<?= base_url('warehouse/issued_others_out/datatableDetails/') ?>" + btoa(request_no),
            rownumbers: true,
            singleSelect: true,
            fit: true,
            columns: [
                [{
                    field: 'item_rm_id',
                    width: 120,
                    halign: 'center',
                    title: "Part No EBWS",
                }, {
                    field: 'item_rm_number',
                    width: 200,
                    halign: 'center',
                    title: "Part No Supplier",
                }, {
                    field: 'item_rm_name',
                    width: 200,
                    halign: 'center',
                    title: "Description",
                }, {
                    field: 'ending_stock',
                    width: 80,
                    halign: 'center',
                    title: "Ending Stock",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'stock',
                    width: 80,
                    halign: 'center',
                    title: "Stock",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            onChange: function(qty) {
                                var dg = $('#dg_detail');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'balance'
                                });

                                var stock = $(ed.target).numberbox('getValue');
                                var balance = (parseInt(stock) - parseInt(qty));

                                if (balance >= 0) {
                                    $(ed2.target).numberbox('setValue', balance);
                                } else {
                                    toastr.error("Balance cannot < 0");
                                    $(ed2.target).numberbox('clear');
                                }
                            }
                        }
                    }
                }, {
                    field: 'balance',
                    width: 80,
                    halign: 'center',
                    title: "Balance",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            required: true
                        }
                    }
                }, {
                    field: 'total_issued',
                    width: 100,
                    halign: 'center',
                    title: "Total Issued",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            required: true
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 200,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg_detail').datagrid('getEditors', rowIndex);
            }
        });

        var editIndex = undefined;

        function endEditing() {
            if (editIndex == undefined) {
                return true
            }
            if ($('#dg_detail').datagrid('validateRow', editIndex)) {
                $('#dg_detail').datagrid('endEdit', editIndex);
                editIndex = undefined;
                return true;
            } else {
                return false;
            }
        }
    }

    $('#filter_part_no').combogrid({
        url: '<?= base_url('master/item_rm/readNotRm/'); ?>',
        panelWidth: 550,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Part No",
        columns: [
            [{
                field: 'id',
                title: 'Part ID',
                width: 100
            }, {
                field: 'number',
                title: 'Part No',
                width: 200
            }, {
                field: 'name',
                title: 'Part Name',
                width: 200
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }]
    });

    function BtnDetail(val, row) {
        var details = "details('" + row.request_no + "','" + row.trans_date + "','" + row.request_name + "','" + row.status + "','" + row.location_other_number + "' )";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="' + details + '"><i class="fa fa-eye"></i> View</a>';
    }

    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
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

    function statusFormat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSE</b>";
        } else if (value == 2) {
            return "<b style='color:white;'>CANCEL</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#7285e9;';
        }
    }
</script>