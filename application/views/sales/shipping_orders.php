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
<table id="dg" class="easyui-datagrid" style="width:100%; height: 800px;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'delivery_order_no',halign:'center',width:150">Delivery Order No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:150,halign:'center'">Product No EBWS</th>
            <th rowspan="2" data-options="field:'number_customer',width:150,halign:'center'">Product No Customer</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'delivery',width:100,halign:'center',align:'right',formatter:numberformat, styler:numberStyle"> Qty DO</th>
            <th rowspan="2" data-options="field:'shipping',width:100,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2"> Shipping</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 400px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="delivery_order_no" name="delivery_order_no" class="scan" placeholder="SCAN DELIVERY ORDER HERE">
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="product_label" name="product_label" class="scan" placeholder="SCAN ASSY NO HERE">
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="checksheet_label" name="checksheet_label" class="scan" placeholder="SCAN LABEL HERE">
            </div>
            <div class="fitem" style="padding:0 200px 10px 200px;">
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
        $('#dg').datagrid({
            rownumbers: true
        });

        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");
        //Scan Supply Sheet
        $('#delivery_order_no').focus();
        $('#delivery_order_no').keypress(function(e) {
            if (e.which == 13) {
                var delivery_order_no = $(this).val();

                Swal.fire({
                    title: 'Please Wait Checking Delivery Order',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "GET",
                    url: "<?= base_url('sales/shipping_orders/getDeliveryOrders') ?>",
                    data: "delivery_order_no=" + delivery_order_no,
                    dataType: "json",
                    success: function(result) {
                        Swal.close();
                        $('#dg').datagrid('loadData', result).datagrid('loaded');
                        $("#product_label").focus();
                        toastr.success("Delivery Order No match!");
                    },
                    error: function(xhr) {
                        Swal.close();
                        $('#dg').datagrid('loaded');
                        let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Something Wrong in server, please Try again.';
                        toastr.error(errorMsg);
                        $("#delivery_order_no").val('');
                        $("#delivery_order_no").focus();
                    }
                });
            }
        });

        //Product No Label
        $('#product_label').keypress(function(e) {
            if (e.which == 13) {
                var product_label = ambilKode($(this).val());

                var rows = $('#dg').datagrid('getRows');
                if (rows.length > 0) {
                    product_result = false;
                    for (let i = 0; i < rows.length; i++) {
                        if (product_label == rows[i].number_customer) {
                            product_result = true;
                        }
                    }

                    if (product_result == true) {
                        $("#checksheet_label").focus();
                        toastr.success("Product No match!");
                    } else {
                        toastr.warning("Product No not found!");
                        $("#product_label").val('');
                        $("#product_label").focus();
                    }
                } else {
                    toastr.warning("Please Scan Delivery Order First!");
                }
            }
        });

        //Scan Label
        $('#checksheet_label').keypress(function(e) {
            if (e.which == 13) {
                var checksheet_label = $(this).val();
                var delivery_order_no = $("#delivery_order_no").val();
                var product_label = ambilKode($("#product_label").val());

                if (product_label == "" || delivery_order_no == "" || checksheet_label == "") {
                    toastr.warning("Please completed your data scan..");
                    return false;
                }

                Swal.fire({
                    title: 'Please Wait Checking Label No',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('sales/shipping_orders/getChecksheetLabel') ?>",
                    data: "checksheet_label=" + checksheet_label + "&delivery_order_no=" + delivery_order_no + "&product_label=" + product_label,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('sales/shipping_orders/create') ?>",
                                    data: "checksheet_label=" + checksheet_label +
                                        "&delivery_order_no=" + delivery_order_no +
                                        "&workorder=" + row[i].workorder +
                                        "&item_fg_id=" + row[i].item_fg_id +
                                        "&checksheet_number=" + row[i].checksheet_number +
                                        "&qty=" + row[i].qty,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            serialSuccess.play();
                                            toastr.success(result.message, result.title);
                                            $("#checksheet_label").val('');
                                            $("#product_label").val('');
                                            $("#product_label").focus();

                                            Swal.close();
                                        } else {
                                            if (result.title == "Not Scanned In" || result.title == "Not Match") {
                                                serialNotFound.play();
                                            } else {
                                                serialDuplicate.play();
                                            }
                                            toastr.error(result.message, result.title);
                                            $("#checksheet_label").val('');
                                            $("#product_label").val('');
                                            $("#product_label").focus();
                                            Swal.close();
                                        }
                                    }
                                });
                            }

                            $('#dg').datagrid({
                                url: '<?= base_url('sales/shipping_orders/getDeliveryOrders?delivery_order_no=') ?>' + delivery_order_no,
                                rownumbers: true
                            });
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label already Scaned or Label not Registered in Warehouse!");
                            $("#checksheet_label").val('');
                            $("#product_label").val('');
                            $("#product_label").focus();
                            Swal.close();
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

    function numberStyle(value, row, index) {
        if (value <= 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function numberStyle2(value, row, index) {
        if (row.delivery > row.shipping) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function ambilKode(serial) {
        return serial.replace(/\/[A-Z]\/.*/, '');
    }
</script>