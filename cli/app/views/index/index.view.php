<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>MiniKan  Control Panel</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" href="/styles/admin/images/admincp.css" type="text/css" media="all" />
</head>
<body scroll="no">
<table cellpadding="0" cellspacing="0" width="100%" height="100%">
	<tr>
		<td colspan="2" height="69"><iframe src="<?php echo $this->createUrl("admin","index","header");?>" name="header" width="100%" height="69" scrolling="no" frameborder="0"></iframe></td>
	</tr>
	<tr>
		<td valign="top" width="160"><iframe src="<?php echo $this->createUrl("admin","index","menu");?>" name="menu" target="main" width="160" height="100%" scrolling="no" frameborder="0"></iframe></td>
		<td valign="top"><iframe src="<?php echo  $this->createUrl("admin","index","main");?>" name="main" width="100%" height="100%" frameborder="0" scrolling="yes" style="overflow:visible;"></iframe></td>
	</tr>
</table>
</body>
</html>