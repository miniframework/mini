<?php echo '<?php $this->layout("admin_main");?>';?>
<div class="container">
	<h3 class="marginbot">列表<?php echo $model->modelTag;?><a href="<?php echo '<?php echo $this->createUrl("admin","'.$modelName.'","addview");?>';?>" class="sgbtn">添加<?php echo $model->modelTag;?></a></h3>
	<div class="mainbox">
	    <?php echo '<?php if(isset($firsterror) && !empty($firsterror)) {?>';?>
		<div class="errormsg">
			<p><em><?php echo '<?php echo $firsterror;?>';?></em></p>
		</div>
		<?php echo '<?php } ?>';?>
		<form action="<?php echo '<?php echo $this->createUrl("admin","'.$modelName.'","delete");?>';?>" onsubmit="return confirm('该操作不可恢复，您确认要删除吗？');" method="post">
				<table class="datalist"  onmouseover="addMouseEvent(this);">
					<tr>
						<th><input type="checkbox" name="chkall" id="chkall" onclick="checkall('delete[]')" class="checkbox">
						<label for="chkall">删除</label>
						</th>
						<?php foreach($model->tags() as $column => $tag) {
						        if($column == $model->getPrimaryKey() || $column =='version') continue;
						?>
						<th><?php echo $tag;?></th>
						<?php }?>
						<td>操作</td>
					</tr>
					<?php echo '<?php foreach($models as $k => $model) {?>';?>
					
					<tr>
						<td><input type="checkbox" name="delete[]" value="<?php echo '<?php echo $model->'.$model->getPrimaryKey().';?>';?>" class="checkbox"></td>
						<?php foreach($model->tags() as $column => $tag) {
						        if($column == $model->getPrimaryKey() || $column =='version') continue;?>
                        <td><?php echo '<?php echo $model->'.$column.';?>'?></td>
                        <?php }?>
						<td><a href="<?php echo '<?php echo $this->createUrl("admin","'.$modelName.'","modifyview",array("'.$model->getPrimaryKey().'"=>$model->'.$model->getPrimaryKey().'));?>'?>">编辑</a></td>
					</tr>
					<?php echo '<?php } ?>';?>
					<tr class="nobg">
						<td><input type="submit" value="删 除" class="btn"></td>
						<td class="tdpage"></td>
					</tr>		
				</table>
		</form>
	</div>
</div>