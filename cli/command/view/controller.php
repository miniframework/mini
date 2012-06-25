<?php echo "<?php\r\n";?>
class <?php echo $modelName;?>Controller extends mini_web_controller
{
	public function doList()
	{
		$<?php echo $modelName;?> = $this->model('<?php echo $modelName;?>');
		$this->view-><?php echo $modelName;?>s = $<?php echo $modelName;?>->getAll();
	}
	public function doAddview()
	{

	}
	public function doAdd()
	{
		$<?php echo $modelName;?> = $this->model('<?php echo $modelName;?>');
		$<?php echo $modelName;?> = $<?php echo $modelName;?>->createByRequest($this->request);
		if($<?php echo $modelName;?>->hasErrors())
		{
			$errors = getErrors();
		}
		$jumpurl = $this->route->createUrl('admin','<?php echo $modelName;?>', 'list');
		$this->response->setRedirect($jumpurl);
	}
}