<style>
    .scan {
        width: 100%;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 40px !important;
    }
</style>
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar" data-options="fitColumns:true">
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'label_no',halign:'center',width:200">Serial No</th>
            <th rowspan="2" data-options="field:'item_id',width:150,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'item_category_name',width:150,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'item_family_name',width:150,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 210px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">RGP/NRGP No.</span>
                    <input style="width:70%;" name="rgp_number" id="rgp_number" class="easyui-combogrid">
                </div>
                <div style="width: 30%; float: left; margin: 5px;">
                    <fieldset style="width: 100%; height: 100px; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                        <legend><b>Total QTY</b></legend>
                        <center>
                            <b style="font-size:20px !important;" id="t_capacity">0</b>
                        </center>
                    </fieldset>
                </div>
                <div style="width: 30%; float: left; margin: 5px;">
                    <fieldset style="width: 100%; height: 100px; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                        <legend><b>Total Scan</b></legend>
                        <center>
                            <b style="font-size:20px !important; color:green;" id="t_instock">0</b>
                        </center>
                    </fieldset>
                </div>
                <div style="width: 30%; float: left; margin: 5px;">
                    <fieldset style="width: 100%; height: 100px; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                        <legend><b>Balance</b></legend>
                        <center>
                            <b style="font-size:20px !important; color: red;" id="t_balance">0</b>
                        </center>
                    </fieldset>
                </div>
            </div>
            <div style="width: 70%; float: left;">
                <div class="fitem" style="padding:0 50px 0 50px;">
                    <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
                    <input style="width:100%; height: 80px;" type="text" id="label_no" name="label_no" class="scan" placeholder="SCAN LABEL HERE">
                    <div style="float: right;">
                        <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>
<audio id="serialDuplicate">
    <source src="<?= base_url('assets/audio/serial_duplicate.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialSuccess">
    <source src="<?= base_url('assets/audio/serial_success.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialNotFound">
    <source src="<?= base_url('assets/audio/serial_notfound.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialError">
    <source src="<?= base_url('assets/audio/error.mp3') ?>" type="audio/mp3">
</audio>
<script>
    function reload() {
        window.location.reload();
    }
    $(function() {
        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");
        var serialError = document.getElementById("serialError");

        //Scan Label
        $('#label_no').focus();
        $('#label_no').keypress(function(e) {
            if (e.which == 13) {
                var label_no = $("#label_no").val();
                var rgp_number = $("#rgp_number").combobox('getValue');

                if (rgp_number != "") {
                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('warehouse/rgp_scan_out/getPoReceipt') ?>",
                        data: "label_no=" + label_no,
                        dataType: "json",
                        success: function(json) {
                            if (json.total > 0) {
                                var row = json.rows;
                                for (let i = 0; i < json.total; i++) {
                                    $.ajax({
                                        type: "POST",
                                        url: "<?= base_url('warehouse/rgp_scan_out/create') ?>",
                                        data: "label_no=" + label_no +
                                            "&rgp_number=" + rgp_number +
                                            "&item_id=" + row[i].item_id +
                                            // "&receipt_id=" + row[i].receipt_id +
                                            // "&po_no=" + row[i].po_no +
                                            "&qty=" + row[i].qty,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                serialSuccess.play();
                                                toastr.success(result.message, result.title);
                                                $("#label_no").val('');
                                                $('#label_no').focus();
                                                var instock = $("#t_instock").html();
                                                var balance = $("#t_balance").html();

                                                $("#t_instock").html((parseInt(instock) + parseInt(row[i].qty)));
                                                $("#t_balance").html((parseInt(balance) - parseInt(row[i].qty)));
                                            } else {
                                                if (result.title == 'Available') {
                                                    serialDuplicate.play();
                                                } else {
                                                    serialError.play();
                                                }
                                                toastr.error(result.message, result.title);
                                                $("#label_no").val('');
                                                $('#label_no').focus();
                                            }
                                        }
                                    });
                                }
                                $('#dg').datagrid({
                                    url: '<?= base_url('warehouse/rgp_scan_out/datatables/') ?>' + window.btoa(label_no),
                                    rownumbers: true
                                });
                            } else {
                                serialNotFound.play();
                                toastr.warning("Label not found!");
                                $("#label_no").val('');
                            }
                        }
                    });
                } else {
                    toastr.info("Please Select First RGP Number");
                    $("#label_no").val('');
                    $('#label_no').focus();
                }
            }
        });

        $('#rgp_number').combobox({
            url: '<?= base_url('warehouse/rgp_scan_out/readDocument'); ?>',
            valueField: 'number',
            textField: 'number',
            prompt: 'Choose RGP Document',
            mode: 'remote',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(data) {
                $("#t_capacity").html(data.total_qty);
                $("#t_instock").html(parseInt(data.total_scan));
                $("#t_balance").html(parseInt(data.balance));
            }
        });
    });

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>