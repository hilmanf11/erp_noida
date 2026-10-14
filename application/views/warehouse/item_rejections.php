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
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'label_no',halign:'center',width:200">Serial No</th>
            <th rowspan="2" data-options="field:'supplier_name',halign:'center',width:200">Supplier</th>
            <!-- <th rowspan="2" data-options="field:'receipt_no',width:140,halign:'center'">Receipt No</th> -->
            <th rowspan="2" data-options="field:'bc_document',width:100,halign:'center'">Inovice No</th>
            <!-- <th rowspan="2" data-options="field:'bc_date',width:100,halign:'center'">Invoice Date</th> -->
            <th rowspan="2" data-options="field:'po_no',width:170,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:150,halign:'center'">Part No EBWS</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
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
            <div class="fitem" style="padding:0 150px 0 150px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
                <input style="width:100%; height: 80px;" type="text" id="label_no" name="label_no" class="scan" placeholder="SCAN LABEL HERE">
            </div>
            <div class="fitem" style="padding:0 150px 10px 150px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
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
<script>
    function reload() {
        window.location.reload();
    }
    $(function() {
        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");

        //Scan Label
        $('#label_no').focus();
        $('#label_no').keypress(function(e) {
            if (e.which == 13) {
                var label_no = $("#label_no").val();

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/item_rejections/getPoReceipt') ?>",
                    data: "label_no=" + label_no,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/item_rejections/create') ?>",
                                    data: "label_no=" + label_no +
                                        "&item_rm_id=" + row[i].item_rm_id +
                                        "&receipt_no=" + row[i].receipt_no +
                                        "&receipt_id=" + row[i].receipt_id +
                                        "&po_no=" + row[i].po_no +
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
                                            serialDuplicate.play();
                                            toastr.error(result.message, result.title);
                                            $("#label_no").val('');
                                            $('#label_no').focus();
                                        }
                                    }
                                });
                            }
                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/item_rejections/datatables/') ?>' + window.btoa(label_no),
                                rownumbers: true
                            });
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label not found!");
                            $("#label_no").val('');
                        }
                    }
                });
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