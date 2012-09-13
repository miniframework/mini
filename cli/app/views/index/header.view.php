<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MiniKan Control Panel</title>
<link rel="stylesheet" href="/styles/admin/images/admincp.css" type="text/css" media="all" />
</head>
<body>
<div class="mainhd">
	<div class="logo">MiniKan Administrator's Control Panel</div>
	<div class="uinfo">
		<p>欢迎, <em>admin</em> [ <a href="admin.php?m=user&a=logout" target="_top">退出</a> ]</p>
			<p id="others"><a href="#" class="othersoff" onclick="showmenu(this);">切换到其他系统</a></p>
			<ul id="header_menu_menu" style="display: none">
				<li><a href="admin.php?m=frame&a=main&iframe=1" target="main" class="tabon">电影</a></li>
				<li><a href="admin.php?m=setting&a=ls&iframe=1" target="main">文章</a></li>
			</ul>
			<script type="text/javascript">
				function showmenu(ctrl) {
					ctrl.className = ctrl.className == 'otherson' ? 'othersoff' : 'otherson';
					var menu = parent.document.getElementById('toggle');
					if(!menu) {
						menu = parent.document.createElement('div');
						menu.id = 'toggle';
						menu.innerHTML = '<ul>' + document.getElementById('header_menu_menu').innerHTML + '</ul>';
						var obj = ctrl;
						var x = ctrl.offsetLeft;
						var y = ctrl.offsetTop;
						while((obj = obj.offsetParent) != null) {
							x += obj.offsetLeft;
							y += obj.offsetTop;
						}
						menu.style.left = x + 'px';
						menu.style.top = y + ctrl.offsetHeight + 'px';
						menu.className = 'togglemenu';
						menu.style.display = '';
						parent.document.body.appendChild(menu);
					} else {
						menu.style.display = menu.style.display == 'none' ? '' : 'none';
					}
				}
			</script>
	</div>
</div>
</body>
</html>