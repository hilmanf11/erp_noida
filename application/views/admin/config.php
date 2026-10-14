<!-- UPDATE DATA -->
<div class="easyui-panel" title="Configuration" style="width:100%; padding:10px; background:#fafafa;" data-options="collapsible:true, maximizable:false, fit:true">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:39%; height: 570px; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Setting Application</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="number" value="<?= $config->number ?>" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" value="<?= $config->name ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="description" value="<?= $config->description ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Office Address</span>
                <input style="width:60%; height: 60px;" name="address" value="<?= $config->address ?>" multiline="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Factory Address</span>
                <input style="width:60%; height: 60px;" name="address2" value="<?= $config->address2 ?>" multiline="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">State</span>
                <input style="width:60%;" name="state" value="<?= $config->state ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Logo</span>
                <input style="width:60%;" name="logo" class="easyui-filebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Favicon</span>
                <input style="width:60%;" name="favicon" class="easyui-filebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Login</span>
                <input style="width:60%;" name="image" class="easyui-filebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PIN Code</span>
                <input style="width:60%;" name="pin_code" value="<?= $config->pin_code ?>" data-options="validType:'length[0,6]'" required class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CIN No</span>
                <input style="width:60%;" name="cin_no" value="<?= $config->cin_no ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">GST No</span>
                <input style="width:60%;" name="gst_no" value="<?= $config->gst_no ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PAN No</span>
                <input style="width:60%;" name="pan_no" value="<?= $config->pan_no ?>" class="easyui-textbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Theme</span>
                <select style="width:60%;" name="theme" value="<?= $config->theme ?>" class="easyui-combobox">
                    <option value="default">Default</option>
                    <option value="cupertino">Cupertino</option>
                    <option value="black">Black</option>
                    <option value="bootstrap">Bootstrap</option>
                    <option value="gray">Gray</option>
                    <option value="pepper-grinder">Pepper Grinder</option>
                    <option value="material">Material</option>
                    <option value="material-blue">Material Blue</option>
                    <option value="material-teal">Material Teal</option>
                    <option value="metro">Metro</option>
                    <option value="metro-blue">Metro Blue</option>
                    <option value="metro-gray">Metro Gray</option>
                    <option value="metro-green">Metro Green</option>
                    <option value="metro-orange">Metro Orange</option>
                    <option value="metro-red">Metro Red</option>
                    <option value="sunny">Sunny</option>
                </select>
            </div>
        </fieldset>

        <fieldset style="width:30%; height: 570px; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Setting Generate MPS</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Finishgood SS</span>
                <input style="width:60%;" name="fg_ss" value="<?= $config->fg_ss ?>" class="easyui-textbox">
            </div>

            <ul>
                <li>If "YES" then default cutoff 01 - end of month</li>
                <li>If "NO" Please input cutoff days for next month</li>
            </ul>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Current Month</span>
                <?php
                if ($config->cutoff_current == "on") {
                    $on = "checked";
                    $off = "";
                } else {
                    $on = "";
                    $off = "checked";
                }
                ?>
                <input class="easyui-radiobutton" <?= $on ?> name="cutoff_current" id="cutoff_current" value="on"> &nbsp; YES &nbsp;
                <input class="easyui-radiobutton" <?= $off ?> name="cutoff_current" value="off"> &nbsp; NO &nbsp;
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cutoff</span>
                <input style="width:25%;" name="cutoff_day_from" value="<?= $config->cutoff_day_from ?>" id="cutoff_day_from" class="easyui-textbox" data-options="prompt:'From'">
                <input style="width:25%;" name="cutoff_day_to" value="<?= $config->cutoff_day_to ?>" id="cutoff_day_to" class="easyui-textbox" data-options="prompt:'To'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cutoff WP</span>
                <input style="width:25%;" name="wp_day_from" value="<?= $config->wp_day_from ?>" id="wp_day_from" class="easyui-textbox" data-options="prompt:'From'">
                <input style="width:25%;" name="wp_day_to" value="<?= $config->wp_day_to ?>" id="wp_day_to" class="easyui-textbox" data-options="prompt:'To'">
            </div>
        </fieldset>

        <fieldset style="width:30%; height: 570px; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Setting Purchase Invoice</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tax Rates (%)</span>
                <input style="width:60%;" name="tax" value="<?= $config->tax ?>" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PPH Rates (%)</span>
                <input style="width:60%;" name="pph" value="<?= $config->pph ?>" class="easyui-numberbox">
            </div>
            <br>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Account IRIS SGST Sales Invoice</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Email</span>
                    <input style="width:60%;" name="iris_email" value="<?= $config->iris_email ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Password</span>
                    <input style="width:60%;" name="iris_password" value="<?= $config->iris_password ?>" class="easyui-textbox">
                </div>
            </fieldset>

            <!-- <div style="width:100%;">
                <br><br>
                <h2 style="font-weight: bold; font-size: 15px;">Setting Terms and Conditions (Purchase Order)</h2>
                <textarea class="summernote" name="si_terms" id="description">
                    <?= $config->si_terms ?>
                </textarea>
            </div> -->
        </fieldset>
        <div style="float: left; width: 100%;">
            <center>
                <a class="easyui-linkbutton c6" style="width: 200px;" onclick="saved()"><i class="fa fa-save"></i> Save Changes</a>
                <br><br><br>
            </center>
        </div>
    </form>
</div>
<script>
    //Add Data
    function saved() {
        $('#frm_insert').form('submit', {
            url: '<?= base_url('admin/config/update') ?>',
            method: 'POST',
            onSubmit: function() {
                return $(this).form('validate');
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }
            }
        });
    }

    $(function() {
        $('.summernote').summernote({
            height: 300,
            hintMode: 'words',
            hintSelect: 'next',
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']]
            ]
        });
    });
</script>