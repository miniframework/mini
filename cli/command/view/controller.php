<?php echo "<?php\r\n";?>
class <?php echo $modelName;?>Controller extends mini_web_controller
{
	public function perinit()
	{
		$firsterror = $this->request->get("firsterror");
		$this->view->firsterror = $firsterror;
	}
	public function doList()
	{
       
        $model = $this->model('<?php echo $modelName;?>');
		$page = $model->page(array("request"=>$this->request, "route"=>$this->route,"url"=>array("admin","<?php echo $modelName;?>","list")));
		$this->view->page = $page;
		$models = $model->getList();
		$this->view->models = $models;
	}
	public function doAddview()
	{

	}
	public function doAdd()
	{
		$model = $this->model('<?php echo $modelName;?>')->createByRequest($this->request);
		if($model->hasErrors())
		{
			$firsterror = $model->getFirstError();
			$this->error($firsterror);
		}
		$jumpurl = $this->route->createUrl("admin","<?php echo $modelName;?>", "list");
		$this->response->setRedirect($jumpurl);
	}
	public function doModifyview()
	{
		$id = $this->request->get("id");
		$model = $this->model("<?php echo $modelName;?>")->getByPk($id);
		if($model->hasErrors())
		{
			$firsterror = $model->getFirstError();
			$this->error($firsterror);
		}
		$this->view->model = $model;
	}
	public function doModify()
	{
		$model = $this->model("<?php echo $modelName;?>")->setByRequest($this->request);
		if($model->hasErrors())
		{
			$firsterror = $model->getFirstError();
			$this->error($firsterror);
		}
		$jumpurl = $this->route->createUrl("admin","<?php echo $modelName;?>", "list");
		$this->response->setRedirect($jumpurl);
	}
	public function doDelete()
	{
		$deletePk = $this->request->get("delete");
		
		if(is_array($deletePk))
		{
			foreach($deletePk as $pk)
			{
				$this->delete($pk);
			}
		}
		else 
		{
			$this->delete($deletePk);
		}
		$jumpurl = $this->route->createUrl("admin","<?php echo $modelName;?>", "list");
		$this->response->setRedirect($jumpurl);
	}
	private function error($message)
	{
		
		$jumpurl = $this->route->createUrl("admin","<?php echo $modelName;?>", "list",array("firsterror"=>$firsterror));
		$this->jump($jumpurl);
	}
	private function delete($pk)
	{
		$model = $this->model("<?php echo $modelName;?>")->getByPk($pk);
		if($model->hasErrors())
		{
			$firsterror = $model->getFirstError();
			$this->error($firsterror);
		}
		$model->delete();
	}
}