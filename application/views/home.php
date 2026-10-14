<body class="easyui-layout">

	<!-- Header -->
	<div data-options="region:'north', border:false" id="header" style="background-image: url(<?= base_url('assets/image/header/' . $profile->theme . '.png') ?>);">
		<div style="float: left;" class="animate__animated animate__bounce animate__slower">
			<img src="<?= base_url('assets/image/logo/' . $profile->theme . '.gif') ?>" width="140">
		</div>

		<div class="logo-company">
			<img src="<?= $config->favicon ?>" width="90"><br>
		</div>
		<div class="name-company">
			<b style="font-size: 16px !important;"><?= $config->name ?></b><br>
			<div class="name-lisence">
				<b><?= $config->description ?></b><br>
			</div>
		</div>

		<div class="logo">
			<a onclick="approval()" href="#" title="Approval" class="notification approval">
				<i class="fa fa-check-square" style="font-size: 25px !important;"></i>
				<div id="approvalCount"></div>
			</a>
			<a onclick="notification()" href="#" title="Notification" class="notification">
				<i class="fa fa-bell" style="font-size: 25px !important;"></i>
				<div id="notificationCount"></div>
			</a>
			<a onclick="profile()" href="#" title="Profile" class="notification">
				<i class="fa fa-users" style="font-size: 25px !important;"></i>
			</a>
			<a href="#" onClick="logout()" title="Logout" class="notification">
				<i class="fa fa-share" style="font-size: 25px !important;"></i>
			</a>
		</div>
	</div>

	<!-- MAIN MENU -->
	<div data-options="region:'west',split:false, collapsed:true, hideCollapsedContent:false, title:'Main Menu'" style="width:250px; padding:10px;">
		<input id="searchMenu" placeholder="Search Menus" onkeypress="searchMenu(this.value)" style="width:100%; padding: 5px;">
		<br><br>
		<ul class="easyui-tree" id="menu" data-options="url:'<?= base_url('home/menus') ?>', method:'get',animate:true, lines:true"></ul>
	</div>

	<!-- USERS ONLINE -->
	<div data-options="region:'east',split:false, collapsed:true, hideCollapsedContent:false" title="Users Online" style="width:250px;">
		<div style="height: 75%; width: 100%; overflow: auto;">
			<table class="user-header" style="width: 100%;">
				<?php
				foreach ($users as $user) {
					if ($user->avatar == "") {
						$avatar = base_url('assets/image/users/default.png');
					} else {
						$avatar = $user->avatar;
					}

					$chats = $this->crud->reads("chats", [], ["from_users_id" => $user->id, "to_users_id" =>  $this->session->id, "status" => 0]);
					if (count($chats) > 0) {
						$totalChats = '<div style="border-radius:50%;background:red; color:white; text-align:center;">' . count($chats) . '</div>';
					} else {
						$totalChats = '';
					}

					$startChats = "onclick='startChats(" . $user->id . ")'";
					echo '	<tr>
								<td style="padding:6px;" width="50">
									<div class="icon-container">
										<img src="' . $avatar . '" class="user-online" />
										<div class="status-circle"></div>
									</div>
								</td>
								<td>
									<a href="#" ' . $startChats . ' style="text-decoration:none;">
										<b style="font-size:12px; color:black;">' . $user->name . '</b><br>
										<small style="color:black;">' . $user->position . '</small>
									</a>
								</td>
								<td style="padding:6px; text-align:right;" width="30">
									' . $totalChats . '
								</td>
							</tr>';
				}
				?>
			</table>
		</div>
		<div style="height: 25%; width: 100%;">
			<center>
				<img src="<?= base_url('assets/image/helpdesk.png') ?>" width="240" />
				<a class="btn btn-lg btn-primary w-75" style="pointer-events: visible; opacity: 1;">SUPPORT SYSTEM</a>
			</center>
		</div>
	</div>

	<!-- FOOTER -->
	<div data-options="region:'south',border:false" style="overflow: hidden;" id="footer">
		Welcome in Application <?= $config->description ?> <b><?= $this->session->name ?></b> You are login in time <?= @$login->created_date ?>
		<span style="float: right;"> Copyright &copy; <?= $config->name ?> 2022 Version 1.0</span>
	</div>

	<!-- TABS AND MODULE -->
	<div data-options="region:'center'" style="width: 100%;">
		<div class="easyui-tabs" id="tabs" style="width:100%; height: 100%;">
			<div title="Dashboard">
				<iframe src="<?= base_url("dashboard/dashboard") ?>" scrolling="yes" id="page" style="border: 0; width: 100%; height: 99%; margin:0;"></iframe>
			</div>
		</div>
	</div>

	<!-- CHANGE PROFILE -->
	<div id="dlg_profile" class="easyui-dialog" title="Profile" data-options="closed: true" style="width: 500px; padding:10px; top: 20px;">
		<form id="frm_profile" method="post" enctype="multipart/form-data" novalidate>
			<fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
				<legend><b>Profile Configuration</b></legend>
				<center>
					<img id="imagePreview" src="<?= $profile_img ?>" width="150">
					<br><br>
				</center>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">Avatar</span>
					<input style="width:60%;" name="avatar" id="avatar" class="easyui-filebox">
				</div>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">Fullname</span>
					<input style="width:60%;" name="name" id="name" value="<?= $profile->name ?>" class="easyui-textbox">
				</div>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">Username</span>
					<input style="width:60%;" name="username" id="username" value="<?= $profile->username ?>" readonly class="easyui-textbox">
				</div>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">Email</span>
					<input style="width:60%;" name="email" id="email" value="<?= $profile->email ?>" class="easyui-textbox">
				</div>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">Phone</span>
					<input style="width:60%;" name="phone" id="phone" value="<?= $profile->phone ?>" class="easyui-textbox">
				</div>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">Position</span>
					<input style="width:60%;" name="position" id="position" value="<?= $profile->position ?>" class="easyui-textbox">
				</div>
			</fieldset>
			<fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
				<legend><b>Theme Application</b></legend>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">Theme App</span>
					<select style="width:60%;" name="theme" id="theme" class="easyui-combobox">
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
			<fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
				<legend><b>Change Password</b></legend>
				<i style="color:red">If you don't want to change the password, don't fill in anything</i>
				<div class="fitem">
					<span style="width:35%; display:inline-block;">New Password</span>
					<input style="width:60%;" name="password" id="password" class="easyui-passwordbox">
				</div>
			</fieldset>
		</form>
	</div>

	<!-- APPROVAL -->
	<div id="dlg_approval" class="easyui-dialog" title="Approvals" data-options="closed: true" style="width: 500px; height: 400px; top: 20px;">
		<ul class="list-header" id="approvalList">

		</ul>
	</div>

	<div id="dlg_approval_detail" class="easyui-window" title="Approval Data" data-options="closed: true,minimizable:false,collapsible:false" style="width: 1000px; height: 400px; top: 60px;">
		<!-- <table id="dg_approval" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar_approval" data-options="fitColumns: false, rownumbers: true"></table> -->
		<center><h2 style="margin: 10px; font-size: 20px !important;" id="header_approval"></h2></center>
		<iframe src="" scrolling="yes" id="pageApproval" style="border: 0; width: 100%; height: 93%; margin:0;"></iframe>
	</div>

	<!-- NOTIFICATIONS -->
	<div id="dlg_notif" class="easyui-dialog" title="Notifications" data-options="closed: true" style="width: 500px; height: 400px; top: 20px;">
		<ul class="list-header" id="notificationList">

		</ul>
	</div>

	<div id="dlg_notification_detail" class="easyui-window" title="Notification Data" data-options="closed: true,minimizable:false,collapsible:false" style="width: 800px; height: 550px; top: 60px;">
		<!-- <table id="dg_approval" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar_approval" data-options="fitColumns: false, rownumbers: true"></table> -->
		<center><h2 style="margin: 10px; font-size: 20px !important;" id="header_notification"></h2></center>
		<iframe src="" scrolling="yes" id="pageNotification" style="border: 0; width: 100%; height: 93%; margin:0;"></iframe>
	</div>

	<!-- CHATS -->
	<div id="dlg_chats" class="easyui-dialog" title="Chats" data-options="closed: true, cls:'c2',border:'thin'" style="width: 400px; height: 600px; top: 60px;">
		<div style="width: 100%;">
			<div style="position: absolute; top: 35px; margin:5px; width: 100%;">
				<table style="width: 100%;">
					<tr>
						<td style="padding:5px;" width="50">
							<div class="icon-container">
								<img src="<?= base_url('assets/image/users/default.png') ?>" class="user-online" width="100" />
								<div class="status-circle"></div>
							</div>
						</td>
						<td>
							<a href="#" style="text-decoration:none;">
								<b style="font-size:12px; color:black;" id="chatName">Name</b><br>
								<small style="color:black;" id="chatPosition">Position</small>
							</a>
						</td>
					</tr>
				</table>
				<hr>
			</div>
			<div style="margin-top: 60px; height: 450px; overflow: auto;" id="messageChats">

			</div>
			<div style="position: absolute; bottom: 0px; margin:10px; width: 93%;">
				<input class="form-control w-100" id="to_users_id" hidden />
				<input class="form-control w-100" id="inputChats" autocomplete="false" autofocus placeholder="Type Message..." />
			</div>
		</div>
	</div>
</body>

<script>
	function searchMenu(e){
		menu(e);
	};

	$(function() {
		var isMobile = /iPhone|iPad|iPod|Android|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

		if (isMobile) {
			window.location.assign('home/device');
		}

		//Deteksi Internet
		window.addEventListener('load', function(event) {
			detectInternet();
		});
		window.addEventListener('online', function(event) {
			detectInternet();
		});
		window.addEventListener('offline', function(event) {
			detectInternet();
		});

		function detectInternet() {
			if (navigator.onLine) {
				Swal.close();
			} else {
				Swal.fire({
					title: 'Connection Time Out, Check Your Connection',
					showConfirmButton: false,
					allowOutsideClick: false,
					allowEscapeKey: false,
					didOpen: () => {
						Swal.showLoading();
					},
				});
			}
		}

		$("#theme").combobox('setValue', '<?= $profile->theme ?>');
		$('#dlg_profile').dialog({
			buttons: [{
				text: 'Save Changes',
				iconCls: 'icon-ok',
				handler: function() {
					$('#frm_profile').form('submit', {
						url: '<?= base_url('home/updateProfile') ?>',
						onSubmit: function() {
							return $(this).form('validate');
						},
						success: function(result) {
							var result = eval('(' + result + ')');

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

							$('#dlg_profile').dialog('close');
						}
					});
				}
			}]
		});

		$('#avatar').filebox({
			onChange: function(value) {
				var id = $(this).filebox('options').fileboxId;
				var files = $('#' + id)[0].files;
				if (files.length) {
					var url = window.URL.createObjectURL(files[0]);
					$('#imagePreview').attr('src', url);
				}
			}
		});

		menu();
	});

	function menu(searchName = ""){
		$('#menu').tree({
			url: '<?= base_url('home/menus?name=') ?>' + searchName,
			method: 'get',
			animate: 'true',
			lines: 'true',
			loadFilter: function(rows) {
				return convert(rows);
			},
			onSelect: function(node) {
				var node = $(this).tree('getSelected');
				if (node) {
					var nama = node.text;
					var link = node.link;
					var id = node.id;
					if (node.attributes) {
						link += "," + node.attributes.p1 + "," + node.attributes.p2;
						nama += "," + node.attributes.p1 + "," + node.attributes.p2;
						id += "," + node.attributes.p1 + "," + node.attributes.p2;
					}
					if ($('#tabs').tabs('exists', nama)) {
						$('#tabs').tabs('select', nama);
					} else {
						if (link == null || link == "") {
							return false;
						} else {
							var content = '<iframe src="' + link + '/index/' + window.btoa(id) + '" scrolling="yes" id="page" style="border: 0; width: 100%; height: 99%; margin:0;"></iframe>';
							//$('#form').panel('setTitle', nama);
							$('#tabs').tabs('add', {
								title: nama,
								content: content,
								closable: true,
								iconCls: node.iconCls
							});
						}
					}
				}
			}
		});
	}

	function convert(rows) {
		function exists(rows, parentId) {
			for (var i = 0; i < rows.length; i++) {
				if (rows[i].id == parentId) return true;
			}
			return false;
		}

		var nodes = [];
		// get the top level nodes
		for (var i = 0; i < rows.length; i++) {
			var row = rows[i];
			if (!exists(rows, row.menus_id)) {
				nodes.push({
					id: row.id,
					text: row.name,
					link: row.link,
					state: row.state,
					iconCls: row.icon
				});
			}
		}

		var toDo = [];
		for (var i = 0; i < nodes.length; i++) {
			toDo.push(nodes[i]);
		}
		while (toDo.length) {
			var node = toDo.shift(); // the parent node
			// get the children nodes
			for (var i = 0; i < rows.length; i++) {
				var row = rows[i];
				if (row.state != "closed") {
					if (row.icon != "") {
						var iconCls = row.icon;
					} else {
						var iconCls = "icon-file";
					}
				} else {
					var iconCls = "";
				}
				if (row.menus_id == node.id) {
					var child = {
						id: row.id,
						text: row.name,
						link: row.link,
						state: row.state,
						iconCls: iconCls
					};
					if (node.children) {
						node.children.push(child);
					} else {
						node.children = [child];
					}
					toDo.push(child);
				}
			}
		}
		return nodes;
	}

	function startChats(user_id) {
		$("#to_users_id").val(user_id);
		$("#dlg_chats").dialog('open');
		var h = $("#messageChats").get(0).scrollHeight;
		$("#messageChats").animate({
			scrollTop: h
		});
		$('#inputChats').focus();

		$.messager.progress({
			title: 'Please waiting',
			msg: 'Open Chat...'
		});

		$.ajax({
			type: "post",
			url: "<?= base_url('admin/users/readId') ?>",
			data: "id=" + user_id,
			dataType: "json",
			success: function(user) {
				$("#chatName").html(user.name);
				$("#chatPosition").html(user.position);
			}
		});

		$.ajax({
			type: "post",
			url: "<?= base_url('home/chats') ?>",
			data: "to_users_id=" + user_id,
			dataType: "html",
			success: function(chats) {
				$("#messageChats").html(chats);
				$.messager.progress('close');
			}
		});
	}

	$('#inputChats').keypress(function(e) {
		if (e.which == 13) {
			var inputChats = $(this).val();
			var to_users_id = $("#to_users_id").val();

			if (inputChats == "") {
				toastr.error("Message empty");
			} else {
				$.ajax({
					type: "post",
					url: "<?= base_url('home/createChats') ?>",
					data: "to_users_id=" + to_users_id + "&messages=" + inputChats,
					dataType: "json",
					success: function(response) {
						toastr.success("Message has been sent");
						startChats(to_users_id);
						$("#inputChats").val('');
						$("#inputChats").focus();
					}
				});
			}
		}
	});

	function profile() {
		$('#dlg_profile').dialog('open');
	}

	function approval() {
		$('#dlg_approval').dialog('open');
	}

	function notification() {
		$('#dlg_notif').dialog('open');
	}

	approvalList();
	approvalCount();
	notificationList();
	notificationCount();
	setInterval(approvalList, 10000);
	setInterval(approvalCount, 10000);

	function approvalList() {
		$.ajax({
			type: "post",
			url: "<?= base_url('approvals/approvalList') ?>",
			dataType: "html",
			success: function(response) {
				$('#approvalList').html(response);
			}
		});
	}

	function notificationList() {
		$.ajax({
			type: "post",
			url: "<?= base_url('notifications/notificationList') ?>",
			dataType: "html",
			success: function(response) {
				$('#notificationList').html(response);
			}
		});
	}

	function approvalCount() {
		$.ajax({
			type: "post",
			url: "<?= base_url('approvals/approvalCount') ?>",
			dataType: "html",
			success: function(response) {
				$('#approvalCount').html(response);
			}
		});
	}

	function notificationCount() {
		$.ajax({
			type: "post",
			url: "<?= base_url('notifications/notificationCount') ?>",
			dataType: "html",
			success: function(response) {
				$('#notificationCount').html(response);
			}
		});
	}

	function approvalDetail(table = "", approved_to = "", approved_by = "", title = "") {
		if (table == "" || approved_to == "") {
			toastr.error("Approval Cannot get Data", "Error");
		} else {
			var outputString = title.replace(/_/g, ' ');
			var header_approval = outputString.toUpperCase();
			$("#header_approval").html(header_approval);

			$('#dlg_approval_detail').window('open');
			$('#pageApproval').attr('src', '<?= base_url("approvals/") ?>' + table + "/" + btoa(approved_to) + "/" + btoa(approved_by));
		}
	}

	function notificationDetail(name = "", user_from = "", user_to = "", table = "") {
		if (user_from == "" || user_to == "") {
			toastr.error("Notification Cannot get Data", "Error");
		} else {
			var outputString = table.replace(/_/g, ' ');
			var header_notification = outputString.toUpperCase();
			$("#header_notification").html("NOTIFICATION " + header_notification);

			$('#dlg_notification_detail').window('open');
			$('#pageNotification').attr('src', '<?= base_url("notifications/getData/") ?>' + btoa(name) + "/" + btoa(user_from) + "/" + btoa(user_to));
		}
	}
	
	function logout() {
		Swal.fire({
			title: 'Please Wait for Logout System',
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
			didOpen: () => {
				Swal.showLoading();
			},
		});

		processingLogout();

		function processingLogout() {
			$.ajax({
				type: "post",
				url: "<?= base_url('login/logout') ?>",
				dataType: "html",
				success: function(response) {
					if (response == 0) {
						setTimeout(function() {
							window.location.assign("<?= base_url('login') ?>");
						}, 2000);
					} else {
						processingLogout();
					}
				}
			});
		}
	}

	function btnDrawing(val){
        if(val == null || val == ""){
            return "-";
        }else{
            return "<a class='btn btn-primary btn-sm' style='pointer-events: auto; opacity:1; width:100%;' target='_blank' href='<?= base_url('assets/documents/bom/') ?>"+val+"'><i class='fa fa-eye'></i> View</a>";
        }
    }

	function numberformat(value, row) {
		const formatter = new Intl.NumberFormat('id-ID');

		return "<b>" + formatter.format(value) + "</b>";
	}
</script>

</html>