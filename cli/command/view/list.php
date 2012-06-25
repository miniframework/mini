<?php echo '<?php $this->layout("admin_main");?>';?>
<div class="container">
	<h3 class="marginbot">列表<a href="<?php echo '<?php echo $this->createUrl("admin","'.$modeName.'","addview");?>';?>" class="sgbtn">添加</a></h3>
	<div class="mainbox">
		<form id="theform">
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
					<?php echo '<?php foreach($'.$modeName.'s as $k => $v) {?>';?>
					<tr>
						<td><input type="checkbox" name="delete[]" value="1" class="checkbox"></td>
						<?php foreach($model->tags() as $column => $tag) {
						        if($column == $model->getPrimaryKey() || $column =='version') continue;
						?>
						<td><?php echo '<?php echo $v->'.$column.';?>'?></td>
						<?php }?>
						<td>编辑</td>
					</tr>
					<?php echo '<?php } ?>';?>	
				</table>
		</form>
	</div>
</div>