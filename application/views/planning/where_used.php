<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Part No EBWS</span>
                    <input style="width:60%;" id="filter_item_rm_id" name="filter_item_rm_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height: 95%; border: 0;"></iframe>
</div>

<script>
    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');
        var url = "?filter_item_rm_id=" + btoa(filter_item_rm_id);

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/where_used/print') ?>' + url);
    }

    function excel() {
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_item_rm_id=" + btoa(filter_item_rm_id);
        window.location.assign('<?= base_url('planning/where_used/print/excel') ?>' + url);
    }

    $(function() {
        //Filter Cutoff
        $('#filter_item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads'); ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part No EBWS",
            columns: [
                [{
                    field: 'id',
                    title: 'Part No EBWS',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 150
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });
    });
</script>