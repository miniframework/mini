<?php $this->layout("admin_main");?>	
<div class="container">
	<h3>系统信息</h3>
	<ul class="memlist fixwidth">
        <li><em>MiniKan 程序版本:</em>Mini 1.0 Release</li>
        <li><em>操作系统及 PHP:</em><?php echo $serverinfo;?></li>
        <li><em>服务器软件:</em><?php echo $_SERVER['SERVER_SOFTWARE'];?></li>
        <li><em>MySQL 版本:</em><?php echo $dbversion;?></li>
        <li><em>上传许可:</em><?php echo $fileupload;?></li>
        <li><em>主机名:</em><?php echo $_SERVER['SERVER_NAME']. "(".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].")";?></li>
        <li><em>magic_quote_gpc:</em><?php echo $magic_quote_gpc;?></li>
        <li><em>allow_url_fopen:</em><?php echo $allow_url_fopen;?></li>    
	</ul>
</div>