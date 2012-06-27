<?php echo '<?php $this->layout("admin_main");?>';?>
<div class="container">
<h3 class="marginbot">编辑<?php echo $model->modelTag;?><a href="<?php echo '<?php echo $this->createUrl("admin","'.$modelName.'","list");?>';?>" class="sgbtn">返回<?php echo $model->modelTag;?>列表</a></h3>
	<div class="mainbox">
		<div id="custom">
			<form action="<?php echo '<?php echo $this->createUrl("admin","'.$modelName.'","modify");?>';?>" method="post">
			<table class="opt">
				<tbody>
					<?php foreach($model->tags() as $column => $tag) {
						  	if($column == $model->getPrimaryKey() || $column =='version') continue;
					?>
                    <tr>
						<th colspan="2"><?php echo $tag; ?>:</th>
					</tr>
					<tr>
						<td><input type="text" class="txt" name="<?php echo $column;?>" value="<?php echo '<?php echo $model->'.$column.';?>';?>"></td>
						<td></td>
                    </tr>
					<?php }?>
				</tbody>
			</table>
			<div class="opt">
			<input type="hidden" name="<?php echo $model->getPrimaryKey();?>"	value="<?php echo '<?php echo $model->'.$model->getPrimaryKey().';?>';?>">
			<input type="submit" name="submit" value=" 提 交 " class="btn" tabindex="3">
			</div>
			</form>
		</div>
	</div>
</div>