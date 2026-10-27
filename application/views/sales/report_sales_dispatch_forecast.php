<script>
	function myformatter(date) {
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
	}

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

<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
	<div style="width: 100%; display: flex; flex-wrap: wrap;">
		<fieldset style="width: 85%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
			<legend><b>Form Filter Data</b></legend>
			<div style="width: 50%; float: left;">
				<div class="fitem">
					<span style="width:30%; display:inline-block;">Date Range</span>
					<input style="width:32%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
					<input style="width:32%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'End Date',formatter:myformatter,parser:myparser, editable:false">
				</div>
				<div class="fitem">
					<span style="width:30%; display:inline-block;">Customer</span>
					<input style="width:65%;" id="filter_customer" class="easyui-combobox">
				</div>
			</div>
			<div style="width: 50%; float: left;">
				<div class="fitem">
					<span style="width:30%; display:inline-block;">Plant Filter</span>
					<input style="width:65%;" id="filter_plant" class="easyui-combobox">
				</div>
				<div class="fitem">
					<span style="width:30%; display:inline-block;"></span>
					<a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
				</div>
			</div>
		</fieldset>
	</div>
	<div style="margin-left: 10px; margin-bottom:5px;">
		<?= $button ?>
	</div>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%; margin-top: 5px;">
	<iframe id="printout" src="" style="width: 100%; height: 520px; border: 0;"></iframe>
</div>

<script>
	function filter() {
		try {
			var filter_from = $("#filter_from").datebox("getValue");
			var filter_to = $("#filter_to").datebox("getValue");
			var filter_customer = $("#filter_customer").combobox("getValue");
			var filter_plant = $("#filter_plant").combobox("getValue");

			var url = "?filter_from=" + window.btoa(filter_from) +
				"&filter_to=" + window.btoa(filter_to) +
				"&filter_customer=" + encodeURIComponent(filter_customer) +
				"&filter_plant=" + encodeURIComponent(filter_plant);

			if (filter_from == "" || filter_to == "") {
				toastr.warning("Please select Date Range (Start Date & End Date)!");
			} else {
				$("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px; font-family:Arial,sans-serif; color:#1F4E78;'>Loading Report... Please Wait...</b></center>");
				$("#printout").attr('src', '<?= base_url('sales/report_sales_dispatch_forecast/print') ?>' + url);
			}
		} catch (e) {
			console.warn("EasyUI datebox not yet initialized:", e);
		}
	}

	function excel() {
		try {
			var filter_from = $("#filter_from").datebox("getValue");
			var filter_to = $("#filter_to").datebox("getValue");
			var filter_customer = $("#filter_customer").combobox("getValue");
			var filter_plant = $("#filter_plant").combobox("getValue");

			var url = "?filter_from=" + window.btoa(filter_from) +
				"&filter_to=" + window.btoa(filter_to) +
				"&filter_customer=" + encodeURIComponent(filter_customer) +
				"&filter_plant=" + encodeURIComponent(filter_plant);

			if (filter_from == "" || filter_to == "") {
				toastr.warning("Please select Date Range (Start Date & End Date)!");
			} else {
				window.location.assign('<?= base_url('sales/report_sales_dispatch_forecast/print/excel') ?>' + url);
			}
		} catch (e) {
			console.warn("EasyUI datebox not yet initialized:", e);
		}
	}

	function pdf() {
		var iframe = document.getElementById("printout");
		if (iframe && iframe.contentWindow) {
			iframe.contentWindow.focus();
			iframe.contentWindow.print();
		} else {
			toastr.warning("Report preview is not ready to print.");
		}
	}

	function reload() {
		window.location.reload();
	}

	$(function() {
		$('#filter_customer').combobox({
			url: '<?= base_url('sales/report_sales_dispatch_forecast/readCustomers') ?>',
			valueField: 'id',
			textField: 'name',
			prompt: 'Select Customer (or ALL)',
			icons: [{
				iconCls: 'icon-clear',
				handler: function(e) {
					$(e.data.target).combobox('clear').combobox('textbox').focus();
					$('#filter_plant').combobox('reload', '<?= base_url('sales/report_sales_dispatch_forecast/readPlants') ?>');
				}
			}],
			onSelect: function(cus) {
				$('#filter_plant').combobox({
					url: '<?= base_url('sales/report_sales_dispatch_forecast/readPlants?customer_id=') ?>' + encodeURIComponent(cus.id),
					valueField: 'name',
					textField: 'name',
					prompt: 'Select Plant (or ALL)',
					icons: [{
						iconCls: 'icon-clear',
						handler: function(e) {
							$(e.data.target).combobox('clear').combobox('textbox').focus();
						}
					}]
				});
			}
		});

		$('#filter_plant').combobox({
			url: '<?= base_url('sales/report_sales_dispatch_forecast/readPlants') ?>',
			valueField: 'name',
			textField: 'name',
			prompt: 'Select Plant (or ALL)',
			icons: [{
				iconCls: 'icon-clear',
				handler: function(e) {
					$(e.data.target).combobox('clear').combobox('textbox').focus();
				}
			}]
		});

		// Auto trigger initial filter on page load
		setTimeout(function() {
			filter();
		}, 300);
	});
</script>
