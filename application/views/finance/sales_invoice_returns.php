<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'gl_no',width:120,align:'center'">GL NO</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'currency',width:100,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:120,halign:'center',align:'right',formatter: priceformat">Sub Total</th>
            <th rowspan="2" data-options="field:'total_grand',width:120,halign:'center',align:'right',formatter: priceformat">Grand Total</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
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
<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:30%;" id="filter_trans_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_trans_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="filter_customer" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_invoice()"><i class="fa fa-print"></i> Sales Invoice</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Plant</span>
                <input style="width:60%;" name="filter_plant" id="filter_plant" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Document No</span>
                <input style="width:60%;" name="filter_sales_invoice" id="filter_sales_invoice" class="easyui-combobox">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="toolbar2">
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a> -->
</div>

<div id="toolbar3">
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit3()"><i class="fa fa-times"></i> Remove</a> -->
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 550px; padding:10px; top: 10px; left:5px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Trans Date</span>
                        <input style="width:60%;" id="trans_date" name="trans_date" required="" readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Document No</span>
                        <input style="width:60%;" readonly id="number" name="number" class="easyui-textbox" data-options="prompt:'Automatic From Purchase Invoce Date & Customer'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Return No</span>
                        <input style="width:60%;" required="" id="dn_number" name="dn_number" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" name="journal_type_id" readonly id="journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer</span>
                        <input style="width:20%;" id="plant" name="plant" readonly class="easyui-textbox">
                        <input style="width:40%;" id="customer_id" name="customer_name" readonly class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%;" id="remarks" name="remarks" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Sales Invoicing Lists" data-options="singleSelect: true" toolbar="#toolbar2" rownumbers="true" , idField="dn_number">
            <thead>
                <tr>
                    <!-- <th rowspan="2" data-options="field:'delete',width:120,formatter:removebtn">Action</th> -->
                    <th hidden rowspan="2" data-options="field:'id',width:150,editor: {type: 'textbox'}">ID</th>
                    <th rowspan="2" data-options="field:'rtn_number',width:150,editor: {type: 'textbox', options: {required: true}}">Sales Return No</th>
                    <th rowspan="2" data-options="field:'dn_number',width:150,editor: {type: 'textbox', options: {required: true}}">Invoice No</th>
                    <th rowspan="2" data-options="field:'so_number',width:160,editor: {type: 'textbox', options: {required: true}}">SO. No</th>
                    <th rowspan="2" data-options="field:'customer_po',width:120, editor: {type: 'textbox', options: {required: true}}">Customer PO</th>
                    <th rowspan="2" data-options="field:'item_fg_id',width:150, editor: {type: 'textbox', options: {required: true}}" hidden>Product Id</th>
                    <th rowspan="2" data-options="field:'item_fg_number',width:170,editor: {type: 'textbox', options: {required: true}}">Product No EBWS</th>
                    <th rowspan="2" data-options="field:'number_customer',width:200,editor: {type: 'textbox', options: {required: true}}">Product No Customer</th>
                    <th rowspan="2" data-options="field:'item_fg_name',width:150,editor: {type: 'textbox', options: {required: true}}">Description</th>
                    <th rowspan="2" data-options="field:'hsn_code',width:100,editor: {type: 'textbox', options: {readonly: true}}">HSN Code</th>
                    <th rowspan="2" data-options="field:'uom',width:80, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/uom/reads') ?>',
                            editable:false,
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose Uom'
                        }}">UoM</th>
                    <th rowspan="2" data-options="field:'qty',width:80, formatter:numberformat,editor: {
                        type: 'numberbox', 
                        options: {
                            required: true,
                            readonly: true,
                            onChange: function(value, oldValue) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });

                                var type = $('#type').combobox('getValue');
                                if(type == 'SCRAP' && parseInt(value) > parseInt(oldValue)){
                                    toastr.error('Qty Scrap cannot more bigger from old qty');
                                    $(ed3.target).numberbox('setValue', oldValue);
                                    return false;
                                }

                                var price = $(ed2.target).numberbox('getValue');
                                $(ed.target).textbox('setValue', (parseFloat(value) * parseFloat(price)));
                            }
                        }
                    }">Qty</th>
                    <th colspan="7" data-options="field:'',align:'center'">Original Currency</th>
                    <th colspan="3" data-options="field:'',align:'center'">Local Currency</th>
                    <th rowspan="2" data-options="field:'account_number',width:100, halign:'center', editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            panelWidth: 320,
                            idField: 'account_number',
                            textField: 'account_number',
                            mode: 'remote', 
                            fitColumns: true,
                            prompt: 'Choose Account No',
                            columns: [
                                [{
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
                                }, {
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }, ]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }}">Account No</th>
                    <th rowspan="2" data-options="field:'account_name',width:150, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th rowspan="2" data-options="field:'account_type',width:100, halign:'center', editor: {
                        type: 'combobox',
                        options: {
                            data: [{
                                'id':'DEBIT'
                            },{
                                'id':'CREDIT'
                            }],
                            valueField: 'id',
                            textField: 'id',
                            prompt: 'Choose Debit/Credit',
                            panelHeight: 'auto'
                        }}">Debit/Credit</th>
                </tr>
                <tr>
                    <th data-options="field:'currency',width:80, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/currencies/reads') ?>',
                            editable:false,
                            valueField: 'number',
                            textField: 'number',
                            prompt: 'Choose Currencies'
                        }}">Currency</th>
                    <th data-options="field:'price',width:80, halign:'center',align:'right', formatter:priceformat,editor: {type: 'numberbox', 
                        options: {
                            required: true,
                            precision: 4,
                            readonly: true,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var type = $('#type').combobox('getValue');

                                var ed = dg.datagrid('getEditor', {index: rowIndex,field: 'total'});
                                var ed2 = dg.datagrid('getEditor', {index: rowIndex,field: 'qty'});
                                var item_fg_id = dg.datagrid('getEditor', {index: rowIndex, field: 'item_fg_id'});
                                var hsn_code = dg.datagrid('getEditor', {index: rowIndex, field: 'hsn_code'});
                                var igst = dg.datagrid('getEditor', {index: rowIndex, field: 'igst'});
                                var sgst = dg.datagrid('getEditor', {index: rowIndex, field: 'sgst'});
                                var cgst = dg.datagrid('getEditor', {index: rowIndex, field: 'cgst'});
                                var total2 = dg.datagrid('getEditor', {index: rowIndex, field: 'total2'});
                                var rate = dg.datagrid('getEditor', {index: rowIndex, field: 'rate'});
                                var account_number = dg.datagrid('getEditor', {index: rowIndex, field: 'account_number'});
                                var account_name = dg.datagrid('getEditor', {index: rowIndex, field: 'account_name'});
                                var account_type = dg.datagrid('getEditor', {index: rowIndex, field: 'account_type'});

                                var item_fg_id = $(item_fg_id.target).textbox('getValue');
                                var qty = $(ed2.target).numberbox('getValue');
                                var total = (parseFloat(value) * parseFloat(qty));
                                var customer_id = $('#customer_id').combogrid('getValue');
                                var plant = $('#plant').textbox('getValue');

                                $(ed.target).textbox('setValue', total);

                                $.ajax({
                                    type: 'post',
                                    url: '<?= base_url("finance/sales_invoice_returns/readGst/") ?>' + type,
                                    data: {
                                        customer_id: customer_id,
                                        plant: plant,
                                        item_fg_id: item_fg_id
                                    },
                                    dataType: 'json',
                                    success: function (gst) {
                                        $(igst.target).numberbox('setValue', (parseFloat(total) * parseFloat(gst.igst)));
                                        $(sgst.target).numberbox('setValue', (parseFloat(total) * parseFloat(gst.sgst)));
                                        $(cgst.target).numberbox('setValue', (parseFloat(total) * parseFloat(gst.cgst)));
                                        $(total2.target).numberbox('setValue', ((parseFloat(total) * parseFloat(gst.igst)) + (parseFloat(total) * parseFloat(gst.sgst)) + (parseFloat(total) * parseFloat(gst.cgst)) + parseFloat(total)));
                                        $(account_number.target).combogrid('setValue', gst.account_number);
                                        $(account_name.target).textbox('setValue', gst.account_name);
                                        $(account_type.target).combobox('setValue', 'CREDIT');
                                        $(rate.target).numberbox('setValue', 1);
                                    }
                                });
                            }
                        }}">Price</th>
                    <th data-options="field:'total',width:120, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">Amount</th>
                    <th data-options="field:'igst',width:100, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">IGST</th>
                    <th data-options="field:'sgst',width:100, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">SGST</th>
                    <th data-options="field:'cgst',width:100, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">CGST</th>
                    <th data-options="field:'total2',width:120, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">Amount<br>After Tax</th>
                    <th data-options="field:'rate',width:80, halign:'center',align:'right', formatter:priceformat,editor: {type: 'numberbox', 
                        options: {
                            required: true,
                            precision: 2,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total2'
                                });

                                var total2 = $(ed2.target).numberbox('getValue');
                                $(ed.target).textbox('setValue', (parseFloat(value) * parseFloat(total2)));
                            }
                        }}">Rate</th>
                    <th data-options="field:'currency_local',align:'center',width:80">Currency</th>
                    <th data-options="field:'total_local',width:120, formatter:priceformat, halign:'center', align:'right', editor: {type: 'numberbox', options: {readonly: true, precision: 2}}">Amount</th>
                </tr>
            </thead>
        </table>

        <div style="width: 50%; float: left; margin-top:20px;">
            <a style="width: 100%;" class="easyui-linkbutton c2" onclick="addJournal()">Add to Journal</a>
            <br><br>
            <table id="dg3" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbar3"></table>

            <div class="fitem">
                <b style="width:45%; display:inline-block; padding-left: 50px;">BALANCE TOTAL</b>
                <input style="width:18%;" id="balance_debit" name="balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:18%;" id="balance_credit" name="balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
            </div>
        </div>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">TAXABLE VALUE</b>
                        <input style="width:60%;" id="total_sub" name="total_sub" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">IGST</b>
                        <input style="width:40%;" id="total_igst" name="total_igst" class="easyui-numberbox" disabled data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SGST</b>
                        <input style="width:40%;" id="total_sgst" name="total_sgst" class="easyui-numberbox" disabled data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">CGST</b>
                        <input style="width:40%;" id="total_cgst" name="total_cgst" class="easyui-numberbox" disabled data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">TCS Amount</b>
                        <select style="width:20%;" id="tcs_option" name="tcs_option" panelHeight="auto" class="easyui-combobox">
                            <option value="0">0%</option>
                            <option value="0.1">0.1%</option>
                            <option value="1">1%</option>
                        </select>
                        <input style="width:40%;" id="tcs" name="tcs" disabled required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">FREIGHT</b>
                        <input style="width:40%;" id="total_freight" name="total_freight" class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">INVOICE VALUE</b>
                        <input style="width:60%;" id="total_grand" name="total_grand" disabled required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem" hidden>
                        <b style="width:35%; display:inline-block;">CONVERT IDR</b>
                        <input style="width:60%;" id="total_grand_local" name="total_grand_local" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        $('#dg2').datagrid({
            onBeforeEdit: function(index, row) {
                row.editing = true;
                $(this).datagrid('refreshRow', index);
            },
            onAfterEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
            onCancelEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
        });

        proses = "ADD";

        $('#dg3').datagrid('loadData', []);

        $("#trans_date").datebox('enable');
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#customer_id").combobox('enable');
        $("#dn_number").combobox('enable');
        $("#preview").linkbutton('enable');
        
        $("#tcs_option").combobox('setValue', 0);
        $("#tcs").numberbox('setValue', 0);
        $("#total_freight").numberbox('setValue', 0);

        number("<?= $menus_id ?>");
    }

    function addJournal() {
        var customer_id = $("#customer_id").combogrid('getValue');

        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;

        var rows2 = $('#dg3').datagrid('getRows');
        var totalrows2 = rows2.length;

        if (totalrows > 0) {
            var data_array = [];
            var data_array2 = [];
            var total_sub = 0;
            var total_local = 0;
            var total_igst = 0;
            var total_sgst = 0;
            var total_cgst = 0;

            for (let i = 0; i < totalrows; i++) {
                var data = {
                    account_number: rows[i].account_number,
                    account_name: rows[i].account_name,
                    account_type: rows[i].account_type,
                    total: rows[i].total
                }

                total_igst += Math.abs(parseFloat(rows[i].igst));
                total_sgst += Math.abs(parseFloat(rows[i].sgst));
                total_cgst += Math.abs(parseFloat(rows[i].cgst));

                if(rows[i].account_type == "DEBIT"){
                    total_sub -= Math.abs(parseFloat(rows[i].total));
                    total_local -= Math.abs(parseFloat(rows[i].total_local));
                }else{
                    total_sub += Math.abs(parseFloat(rows[i].total));
                    total_local += Math.abs(parseFloat(rows[i].total_local));
                }
                
                data_array.push(data);
            }

            $("#total_sub").numberbox('setValue', Math.abs(total_sub));

            $("#total_igst").numberbox('setValue', total_igst);
            $("#total_cgst").numberbox('setValue', total_cgst);
            $("#total_sgst").numberbox('setValue', total_sgst);

            var total_tcs = $("#tcs").numberbox('getValue');
            var total_freight = $("#total_freight").numberbox('getValue');
            var total_grand = (parseFloat(Math.abs(total_sub)) + parseFloat(total_igst) + parseFloat(total_cgst) + parseFloat(total_sgst) + parseFloat(total_tcs) - parseFloat(total_freight));

            $("#total_grand").numberbox('setValue', (total_grand));
            $("#total_grand_local").numberbox('setValue', Math.abs(total_local));

            var tds_val = 0;
            // var arr_tds = ["17000001","17000002","17000003","17000004","17000005","17000006"];
            var arr_ap = ["27000001"];

            for (let z = 0; z < totalrows2; z++) {
                var debit = rows2[z].debit;
                var credit = rows2[z].credit;

                if (rows2[z].account_number == "17000010") {
                    var debit = total_igst;
                    var credit = 0;
                }

                if (rows2[z].account_number == "17000009") {
                    var debit = total_cgst;
                    var credit = 0;
                }

                if (rows2[z].account_number == "17000008") {
                    var debit = total_sgst;
                    var credit = 0;
                }

                if (rows2[z].account_number == "46000001") {
                    var debit = 0;
                    var credit = total_freight;
                }

                if (rows2[z].account_number == "17000007") {
                    var debit = total_tcs;
                    var credit = 0;
                }

                if (rows2[z].account_number == "20100008") {
                    var debit = 0;
                    var credit = total_tcs;
                }

                if (jQuery.inArray(rows2[z].account_number, arr_ap) >= 0) {
                    var debit = 0;
                    var credit = total_grand;
                }

                var data2 = {
                    account_number: rows2[z].account_number,
                    account_name: rows2[z].account_name,
                    debit: debit,
                    credit: credit,
                    flag: rows2[z].flag,
                }

                data_array2.push(data2);
            }

            // if (taxes > 0 && vat_val == 0) {
            //     var data2 = {
            //         account_number: "1154105",
            //         account_name: "TAX",
            //         debit: disc_tax,
            //         credit: 0,
            //         flag: "3",
            //     }

            //     data_array2.push(data2);
            // }

            var jsonData = JSON.stringify(data_array);
            var jsonData2 = JSON.stringify(data_array2);

            $.ajax({
                type: "POST",
                url: "<?= base_url('finance/sales_invoice_returns/createJson') ?>",
                data: {
                    jsonData: jsonData,
                    jsonData2: jsonData2,
                },
                success: function(response) {
                    addTable2('<?= base_url('finance/sales_invoice_returns/calculateJournal') ?>');

                    setTimeout(function() {
                        balance_journal();
                    }, 2000);
                },
            });
        } else {
            toastr.warning("please selections your data in table first");
        }
    }

    function addTable2(link = "") {
        var lastIndex;
        var dg = $('#dg3').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'account_number',
                    width: 100,
                    halign: 'center',
                    title: "Account No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            panelWidth: 320,
                            idField: 'account_number',
                            textField: 'account_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Account No',
                            columns: [
                                [{
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
                                }, {
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg3');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }
                    }
                }, {
                    field: 'account_name',
                    width: 200,
                    halign: 'center',
                    title: "Account Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'debit',
                    width: 110,
                    halign: 'center',
                    title: "Debit",
                    formatter: priceformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 4
                        }
                    }
                }, {
                    field: 'credit',
                    width: 110,
                    halign: 'center',
                    title: "Credit",
                    formatter: priceformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 4
                        }
                    }
                }, {
                    field: 'flag',
                    width: 50,
                    halign: 'center',
                    title: "Order",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true
                        }
                    }
                }, ],
            ],
            onBeginEdit: function(rowIndex, row) {
                balance_journal();
            }
        });
    }

    function balance_journal() {
        var rows = $('#dg3').datagrid('getRows');
        var totalrows = rows.length;

        if (totalrows > 0) {
            var debit = 0;
            var credit = 0;
            for (let i = 0; i < totalrows; i++) {
                debit += parseFloat(rows[i].debit);
                credit += parseFloat(rows[i].credit);
            }

            $("#balance_debit").numberbox('setValue', debit);
            $("#balance_credit").numberbox('setValue', credit);
        }
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.status == 0) {
                if(row.gl_no == null){
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);

                    $("#trans_date").datebox('disable');
                    $("#customer_id").combobox('disable');
                    $("#dn_number").combobox('disable');
                    $("#preview").linkbutton('disable');

                    proses = "EDIT";

                    $('#customer_id').combogrid({
                        url: '<?= base_url('master/customers/reads') ?>',
                        panelWidth: 420,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Customer",
                        columns: [
                            [{
                                field: 'number',
                                title: 'Customer No',
                                width: 120
                            }, {
                                field: 'name',
                                title: 'Customer Name',
                                width: 250
                            }, ]
                        ],
                        onLoadSuccess: function(customer_id) {
                            $("#customer_id").combogrid('setValue', row.customer_id);
                        },
                        onSelect: function(index, customer) {
                            var trans_date = $("#trans_date").datebox('getValue');

                            $("#dn_number").combobox({
                                url: '<?= base_url('finance/sales_invoice_returns/readDelivery?customer_id=') ?>' + customer.id,
                                valueField: 'number',
                                textField: 'number',
                                prompt: "Choose Delivery Note",
                                onLoadSuccess: function(delivery_no) {
                                    $("#dn_number").combobox('setValue', row.dn_number);
                                },
                            });
                        }
                    });

                    var lastIndex;
                    var dg = $('#dg2').datagrid({
                        url: '<?= base_url('finance/sales_invoice_returns/reads/') ?>' + window.btoa(row.number),
                        onBeforeEdit: function(index, row) {
                            row.editing = true;
                            $(this).datagrid('refreshRow', index);
                        },
                        onAfterEdit: function(index, row) {
                            row.editing = false;
                            $(this).datagrid('refreshRow', index);
                        },
                        onCancelEdit: function(index, row) {
                            row.editing = false;
                            $(this).datagrid('refreshRow', index);
                        },
                        onLoadSuccess: function(rowzz){
                            $("#total_sub").numberbox('setValue', rowzz.total_sub);
                            $("#total_igst").numberbox('setValue', rowzz.total_igst);
                            $("#total_sgst").numberbox('setValue', rowzz.total_sgst);
                            $("#total_cgst").numberbox('setValue', rowzz.total_cgst);
                            $("#tcs").numberbox('setValue', row.tcs);
                            $("#total_grand").numberbox('setValue', rowzz.total_grand);
                            $("#total_grand_local").numberbox('setValue', rowzz.total_grand_local);
                        }
                    });

                    addTable2('<?= base_url('finance/sales_invoice_returns/readJournals/') ?>' + window.btoa(row.number));

                    setTimeout(function() {
                        balance_journal();
                        $("#number").textbox('setValue', row.number);
                    }, 2000);
                }else{
                    toastr.error("Cannot Update because this Sales Invoice has been created in Posting Journal");
                }
            } else {
                toastr.error("Cannot Update because AR Receipt status is closed");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(id_menu) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/sales_invoice_returns/number/') ?>" + id_menu,
            dataType: "html",
            success: function(result) {
                if(result == ""){
                    toastr.info("Please Check Configuration in Serial No First");
                }else{
                    $("#number").textbox('setValue', result);
                }
            }
        });
    }

    function preview() {
        var customer_id = $("#customer_id").combogrid('getValue');
        var plant = $("#plant").textbox('getValue');
        var dn_number = $("#dn_number").combobox('getValue');
        var trans_date = $("#trans_date").datebox('getValue');
        var journal_type_id = $("#journal_type").combobox('getValue');

        if (dn_number == "" || trans_date == "" || plant == "" || journal_type_id == "") {
            toastr.info('Please completed your data');
        } else {
            var lastIndex;
            var dg = $('#dg2').datagrid({
                url: '<?= base_url('finance/sales_invoice_returns/datatablesTemp') ?>?dn_number=' + window.btoa(dn_number),
                onLoadSuccess: function(row) {
                    $("#total_sub").numberbox('setValue', row.total_sub);
                    $("#total_igst").numberbox('setValue', row.total_igst);
                    $("#total_cgst").numberbox('setValue', row.total_cgst);
                    $("#total_sgst").numberbox('setValue', row.total_sgst);
                    $("#total_grand").numberbox('setValue', row.total_grand);
                    $("#total_grand_local").numberbox('setValue', row.total_local);

                    addTable2('<?= base_url('finance/sales_invoice_returns/readJournal/') ?>' + window.btoa(journal_type_id));
                },
            });
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('closing/locks/checkLock') ?>",
                            data: "period=" + row.trans_date + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function (lock) {
                                if(lock.total > 0){
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                if (row.status != 0) {
                                    toastr.error("Cannot Delete because Sales Invoice status is closed");
                                    return false;
                                }

                                if(row.gl_no != null){
                                    toastr.error("Cannot Delete because this Sales Invoice has been created in Posting Journal");
                                    return false;
                                }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/sales_invoice_returns/delete') ?>',
                                    data: {
                                        number: row.number,
                                        rtn_number: row.rtn_number,
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');

                                        if (result.theme == "success") {
                                            toastr.success(result.message);
                                        } else {
                                            toastr.error(result.message);
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                    },
                                    complete: function(data) {
                                        $('#dg').datagrid('reload');
                                    }
                                });
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');

        var url = "?filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant);

        $('#dg').datagrid({
            url: '<?= base_url('finance/sales_invoice_returns/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/sales_invoice_returns/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');

        var url = "?filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_plant=" + window.btoa(filter_plant);

        window.location.assign('<?= base_url('finance/sales_invoice_returns/print/excel') ?>' + url);
    }

    //PRINT INVOICE
    function print_invoice() {
        var row = $('#dg').datagrid('getSelected');
        
        if (row) {
            if(row.cancel > 0){
                toastr.error("Cannot Print Invoce because the status is cancel");
                return false;
            }

            window.open("<?= base_url('finance/sales_invoice_returns/print_dn/') ?>" + window.btoa(row.number), '_blank', 'location=yes,height=570,width=1000,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please checklist in the table first!", "Information");
        }
    }

    //RELOAD
    function reload() {
        window.location.reload();
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

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function removebtn(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
            return s + c;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
            var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';
            return e + d;
        }
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/sales_invoice_returns/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('finance/sales_invoice_returns/datatables/details?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'rtn_number',
                            title: 'Return No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'dn_number',
                            title: 'Invoice No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'so_number',
                            title: 'Sales Order',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'customer_po',
                            title: 'Customer PO',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No EBWS',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Unit Price',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'total',
                            title: 'Total',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number_edit = $("#number").textbox('getValue');
                    var customer_id = $("#customer_id").combogrid('getValue');
                    var plant = $("#plant").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');
                    var journal_type_id = $("#journal_type").combobox('getValue');

                    var balance_debit = $("#balance_debit").numberbox('getValue');
                    var balance_credit = $("#balance_credit").numberbox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');
                    var tcs_option = $("#tcs_option").combobox('getValue');
                    var tcs = $("#tcs").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');
                    var total_grand_local = $("#total_grand_local").numberbox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + trans_date + "&menus_id=<?= $menus_id ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            if (parseFloat(balance_debit) == parseFloat(balance_credit)) {
                                if (trans_date == "" || customer_id == "" || journal_type_id == "") {
                                    toastr.error("please complete your input data");
                                } else {
                                    $('#dg2').datagrid('acceptChanges');
                                    var rows = $('#dg2').datagrid('getRows');
                                    var totalrows = rows.length;

                                    var rows2 = $('#dg3').datagrid('getRows');
                                    var totalrows2 = rows2.length;

                                    for (let z = 0; z < totalrows2; z++) {
                                        var account_number = rows2[z].account_number;
                                        
                                        if(account_number == ""){
                                            toastr.error("Account Number cannot empty!");
                                            return false;
                                        }
                                    }
                                    

                                    $.ajax({
                                        type: "post",
                                        url: "<?= base_url('finance/sales_invoice_returns/number/' . $menus_id) ?>",
                                        dataType: "html",
                                        success: function(result) {
                                            if(result == ""){
                                                toastr.info("Please Check Configuration in Serial No First");
                                                return false;
                                            }else{
                                                if(proses == "ADD"){
                                                    var number = result;
                                                }else{
                                                    var number = number_edit;
                                                }

                                                $.ajax({
                                                    type: "post",
                                                    url: "<?= base_url('finance/sales_invoice_returns/deleteJournal') ?>",
                                                    data: "number=" + number,
                                                    dataType: "json",
                                                    success: function(response) {
                                                        Swal.fire({
                                                            title: 'Please Wait for Saving Data',
                                                            showConfirmButton: false,
                                                            allowOutsideClick: false,
                                                            allowEscapeKey: false,
                                                            didOpen: () => {
                                                                Swal.showLoading();
                                                            },
                                                        });
                                                    }
                                                });

                                                if (totalrows > 0) {
                                                    requestData(totalrows, rows);
                                                    $('#dlg_insert').dialog('close');

                                                    function requestData(total, json, jml = 1, value = 0) {
                                                        if (value < 100) {
                                                            value = Math.floor((jml / total) * 100);
                                                            var i = (jml - 1);

                                                            $.ajax({
                                                                type: "post",
                                                                url: '<?= base_url('finance/sales_invoice_returns/create') ?>',
                                                                data: {
                                                                    trans_date: trans_date,
                                                                    type: "RETURN",
                                                                    number: number,
                                                                    customer_id: customer_id,
                                                                    plant: plant,
                                                                    journal_type_id: journal_type_id,
                                                                    remarks: remarks,
                                                                    total_sub: total_sub,
                                                                    tcs_option: tcs_option,
                                                                    tcs: tcs,
                                                                    total_grand: total_grand,
                                                                    total_grand_local: total_grand_local,
                                                                    id: json[i].id,
                                                                    dn_number: json[i].dn_number,
                                                                    rtn_number: json[i].rtn_number,
                                                                    so_number: json[i].so_number,
                                                                    customer_po: json[i].customer_po,
                                                                    item_fg_id: json[i].item_fg_id,
                                                                    uom: json[i].uom,
                                                                    hsn_code: json[i].hsn_code,
                                                                    currency_local: json[i].currency_local,
                                                                    currency: json[i].currency,
                                                                    item_no: json[i].item_fg_number,
                                                                    item_name: json[i].item_fg_name,
                                                                    qty: json[i].qty,
                                                                    igst: json[i].igst,
                                                                    sgst: json[i].sgst,
                                                                    cgst: json[i].cgst,
                                                                    price: json[i].price,
                                                                    rate: json[i].rate,
                                                                    total: json[i].total,
                                                                    total2: json[i].total2,
                                                                    total_local: json[i].total_local,
                                                                    account_number: json[i].account_number,
                                                                    account_type: json[i].account_type,
                                                                },
                                                                dataType: "json",
                                                                success: function(result) {
                                                                    requestData(total, json, jml + 1, value);
                                                                    if (jml == total) {
                                                                        
                                                                        if (totalrows2 > 0) {
                                                                            requestData2(totalrows2, rows2);
                                                                            
                                                                            function requestData2(total2, json2, jml2 = 1, value2 = 0) {
                                                                                if (value2 < 100) {
                                                                                    value2 = Math.floor((jml2 / total2) * 100);
                                                                                    var z = (jml2 - 1);

                                                                                    $.ajax({
                                                                                        type: "post",
                                                                                        url: '<?= base_url('finance/sales_invoice_returns/createJournals') ?>',
                                                                                        data: {
                                                                                            number: number,
                                                                                            account_number: json2[z].account_number,
                                                                                            account_name: json2[z].account_name,
                                                                                            debit: json2[z].debit,
                                                                                            credit: json2[z].credit,
                                                                                            flag: json2[z].flag,
                                                                                        },
                                                                                        dataType: "json",
                                                                                        success: function(result2) {
                                                                                            requestData2(total2, json2, jml2 + 1, value2);
                                                                                            if (jml2 == total2) {
                                                                                                Swal.close();

                                                                                                Swal.fire({
                                                                                                    title: 'Posting Journal',
                                                                                                    text: result2.message + ", Do you want to save journal posting to ?",
                                                                                                    icon: result2.theme,
                                                                                                    showCancelButton: true,
                                                                                                    allowOutsideClick: false,
                                                                                                    allowEscapeKey: false,
                                                                                                    confirmButtonText: 'Yes, Add to Journal!'
                                                                                                }).then((msg) => {
                                                                                                    if (msg.isConfirmed) {
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
                                                                                                            url: '<?= base_url('finance/sales_invoice_returns/createPosting') ?>',
                                                                                                            data: {
                                                                                                                number: number
                                                                                                            },
                                                                                                            dataType: "json",
                                                                                                            success: function(result3) {
                                                                                                                Swal.close();

                                                                                                                Swal.fire({
                                                                                                                    title: result3.message,
                                                                                                                    icon: result3.theme,
                                                                                                                    confirmButtonText: 'Ok',
                                                                                                                    allowOutsideClick: false,
                                                                                                                }).then((result3) => {
                                                                                                                    if (result3.isConfirmed) {
                                                                                                                        window.location.reload();
                                                                                                                    }
                                                                                                                });

                                                                                                            }
                                                                                                        });
                                                                                                    }
                                                                                                });
                                                                                            }
                                                                                        }
                                                                                    });
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    }
                                                } else {
                                                    toastr.warning("please selections your data in table first");
                                                }
                                            }
                                        }
                                    });
                                }
                            } else {
                                toastr.error("Balance Debit Cannot match on Balance Credit");
                            }
                        }
                    });
                }
            }]
        });

        $("#filter_customer").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Customers",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(customer){
                $("#filter_plant").combobox({
                    url: '<?= base_url('master/customers/readAddress/') ?>' + btoa(customer.id),
                    valueField: 'plant',
                    textField: 'plant',
                    prompt: "Choose Plant",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#filter_sales_invoice").combobox({
            url: '<?= base_url('finance/sales_invoice_returns/readSalesInvoices') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Document No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#dn_number").combobox({
            url: '<?= base_url('finance/sales_invoice_returns/readDelivery') ?>',
            valueField: 'document_no',
            textField: 'document_no',
            prompt: "Choose Sales Return No",
            onSelect: function(rtnrow, rtnval){
                $("#journal_type").combobox({
                    url: '<?= base_url('finance/journal_types/reads/' . base64_encode("SALES INVOICING")) ?>',
                    valueField: 'id',
                    textField: 'name',
                    prompt: "Choose Journal Types",
                    onLoadSuccess: function(){
                        $("#journal_type").combobox('setValue', rtnrow.journal_type_id);
                    },
                    onSelect: function(row) {
                        addTable2('<?= base_url('finance/purchase_invoices/readJournal/') ?>' + window.btoa(row.id));
                    }
                });

                $("#plant").textbox('setValue', rtnrow.plant);
                $('#customer_id').combogrid({
                    url: '<?= base_url('master/customers/reads') ?>',
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Customer",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Customer No',
                            width: 120
                        }, {
                            field: 'name',
                            title: 'Customer Name',
                            width: 250
                        }, ]
                    ],
                    onLoadSuccess: function(){
                        $("#customer_id").combogrid('setValue', rtnrow.customer_id);
                    },
                });
            }
        });

        $("#tcs_option").combobox({
            onChange: function(tcs) {
                var total_sub = $("#total_sub").numberbox('getValue');
                var total_igst = $("#total_igst").numberbox('getValue');
                var total_cgst = $("#total_cgst").numberbox('getValue');
                var total_sgst = $("#total_sgst").numberbox('getValue');
                var grand_total = (parseFloat(total_sub) + parseFloat(total_igst) + parseFloat(total_cgst) + parseFloat(total_sgst));
                $("#tcs").numberbox('setValue', (parseFloat(grand_total) * parseFloat(tcs)) / 100);
            }
        });
    });

    function priceformat(value, row) {
        if(row.currency != "INR"){
            var digits = 4;
            var format = "ja-JP";
        }else{
            var digits = 2;
            var format = "ja-JP";
        }

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function statusformat(value, row) {
        if(row.cancel == "0"){
            if (value == 0) {
                return "<b style='color:green;'>OPEN</b>";
            } else {
                return "<b style='color:red;'>CLOSED</b>";
            }
        }else{
            return "<b style='color:red;'>CANCEL</b>";
        }
    }

    function statusStyle(value, row, index) {
        if(row.cancel == "0"){
            if (value == 0) {
                return 'background-color:#C8FFCC;';
            } else {
                return 'background-color:#FFC8C8;';
            }
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>