<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-treegrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <!-- <th data-options="field:'item_fg_id',width:150,align:'center'">Product ID</th> -->
            <th data-options="field:'id',width:150,halign:'center'">Product No EBWS</th>
            <th data-options="field:'number',width:200,halign:'center'">Product No Customer</th>
            <th data-options="field:'name',width:300,halign:'center'">Description</th>
            <th data-options="field:'prodfam',width:120,halign:'center'">Product Family</th>
            <th data-options="field:'begin',width:100,halign:'center',align:'right',formatter:numberFormat">Begin</th>
            <th data-options="field:'qty_in',width:100,halign:'center',align:'right',formatter:numberFormat">IN</th>
            <th data-options="field:'qty_out',width:100,halign:'center',align:'right',formatter:numberFormat">OUT</th>
            <th data-options="field:'end_stock',width:100,halign:'center',align:'right',formatter:numberFormat">Ending</th>
            <th data-options="field:'details',width:100,halign:'center',align:'right',formatter:btnDetails">Details</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 49%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Report Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <option value="RECAP">RECAP</option>
                    <option value="DETAIL">DETAIL</option>
                </select>
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Trans Type</span>
                <input style="width:60%;" id="filter_transtype" class="easyui-combobox">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- DIALOG DETAILS -->
<div id="dlg_details" class="easyui-dialog" title="Detail Data" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/serial_transactions/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_transtype = $("#filter_transtype").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_family=" + filter_item_family + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_transtype=" + filter_transtype;
        
        if(filter_item_family != ""){
            $('#dg').treegrid({
                url: '<?= base_url('warehouse/serial_transactions/datatables') ?>' + url,
                pagination: false,
                rownumbers: true,
                idField: 'id',
                treeField: 'id',
                singleSelect: false,
                fit: true,
                pageList: [20, 50, 100, 500, 1000],
                pageSize: 20,
                onBeforeLoad: function(row, param) {
                    if (!row) {
                        param.id = 0;
                    }
                },
                rowStyler: function(row) {
                    if (row.state != "closed") {
                        return 'background-color:#CFE6FF;font-weight:bold;';
                    }
                },
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('warehouse/serial_transactions/print') ?>' + url);
        }else{
            toastr.info("Please Select Product Family");
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_transtype = $("#filter_transtype").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_family=" + filter_item_family + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_transtype=" + filter_transtype;
        
        window.location.assign('<?= base_url('warehouse/serial_transactions/print/excel') ?>' + url);
    }

    function btnDetails(val, row) {
        // if (val != "closed") {
        var onClick = "createLabel('"+row.id+"','"+row.mpq+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" onClick="'+onClick+'"><i class="fa fa-eye"></i> View</a>';
        // }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#filter_transtype").combobox({
            url: '<?= base_url('master/transaction_types/reads') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Select Transaction Type",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_item_family").combobox({
            url: '<?= base_url('warehouse/report_history_transactions/readItemFamily/001') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Select Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $('#filter_items').combogrid({
                    url: '<?= base_url('master/item_rm/reads/') ?>' + row.id,
                    panelWidth: 550,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Part No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'id',
                            title: 'Part No EBWS',
                            width: 150
                        },{
                            field: 'number',
                            title: 'Part No Supplier',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 200
                        }, ]
                    ]
                });
            }
        });

        $('#filter_items').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Part No EBWS',
                    width: 150
                },{
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }, ]
            ]
        });
    });

    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
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
</script>