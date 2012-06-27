<?php
class mini_cli_command_curd extends mini_cli_command
{
    public $viewName = array("List","addview","modifyview");
    public function run($args)
    {
        if(isset($args[0]) && $args[0]=='create' )
        {
            if(isset($args[1]) && !empty($args[1]))
            {
                $modelPath = mini::getRunPath()."/models";
                $modelFile = $args[1].".class.php";
                $modelName = $args[1];
                if(!file_exists($modelPath."/".$modelFile))
                {
                    echo "model file:$modelFile not exists.";
                    return;
                }
                if(!class_exists($modelName))
                {
                    echo "model class:$modelName not  exists.";
                    return;
                }
                $controllerPath =  mini::getRunPath()."/apps/admin";
                $controllerFile = $modelName.".class.php";
                if(file_exists($controllerPath."/".$controllerFile))
                {
                    echo "controller file: $controllerFile exists. delete first.";
                    return;
                }
                $model = mini_db_model::model($modelName);
                if($this->confirm("Create a controller under '$controllerPath'?")) {
                	$this->createController($model);
                	$viewPath =  mini::getRunPath()."/views/admin";
                	if(!is_dir($viewPath."/".$modelName))
                	{
                	    mkdir($viewPath."/".$modelName);
                	}
                	foreach($this->viewName as $name)
                	{
                	    $createview = "create$name";
                	    $this->$createview($model);
                	}
                	$this->addMenu($model);
                }
            }    
            
        		
        }
        else
        {
        	echo  $this->help();
        }

    }
    public function addMenu($model)
    {
        $modelName = get_class($model);
        $viewPath =  mini::getRunPath()."/views/admin/index";
        $viewFile = "menu.view.php";
        if(file_exists($viewPath."/".$viewFile))
        {
            $menuTag = '<!-- {Mini-Crud-Menu} -->';
            $menuView = file_get_contents($viewPath."/".$viewFile);
            $modelMenu = '<li><a href="<?php echo $this->createUrl("admin","'.$modelName.'","list");?>" target="main">'.$model->modelTag.'</a></li>';
            $modelMenu .= "\r\n".$menuTag;
            $menuView = str_replace($menuTag, $modelMenu, $menuView);
            file_put_contents($viewPath."/".$viewFile,$menuView);
            echo "add model Menu successfull.\r\n";
        }
        else
        {
            echo "Menu file not exists.\r\n";
            return;
        }
    }
    public function createmodifyview($model)
    {
    	$modelName = get_class($model);
    	ob_start();
    	ob_implicit_flush(false);
    	include dirname(__FILE__)."/view/modifyview.php";
    	$content = ob_get_clean();
    
    	$viewPath =  mini::getRunPath()."/views/admin";
    	$viewFile = "modifyview.view.php";
    	$view = $viewPath."/".$modelName."/".$viewFile;
    	if(file_exists($view))
    	{
    		echo "view file: $viewFile exists. delete first.\r\n";
    		return;
    	}
    	 
    	file_put_contents($view, $content);
    	echo $viewFile." view create successfull.\r\n";
    }
    public function createaddview($model)
    {
    	$modelName = get_class($model);
    	ob_start();
    	ob_implicit_flush(false);
    	include dirname(__FILE__)."/view/addview.php";
    	$content = ob_get_clean();
    
    	$viewPath =  mini::getRunPath()."/views/admin";
    	$viewFile = "addview.view.php";
    	$view = $viewPath."/".$modelName."/".$viewFile;
    	if(file_exists($view))
    	{
    		echo "view file: $viewFile exists. delete first.\r\n";
    		return;
    	}
    	
    	file_put_contents($view, $content);
    	echo $viewFile." view create successfull.\r\n";
    }
    public function createList($model)
    {
        $modelName = get_class($model);
        ob_start();
        ob_implicit_flush(false);
        include dirname(__FILE__)."/view/list.php";
        $content = ob_get_clean();
        
        $viewPath =  mini::getRunPath()."/views/admin";
        $viewFile = "list.view.php";
        $view = $viewPath."/".$modelName."/".$viewFile;
    	if(file_exists($view))
    	{
    		echo "view file: $viewFile exists. delete first.\r\n";
    		return;
    	}
    	
    	file_put_contents($view, $content);
        echo $viewFile." view create successfull.\r\n";
    }
   
    public function createController($model)
    {
        $modelName = get_class($model);
        
        ob_start();
        ob_implicit_flush(false);
        include dirname(__FILE__)."/view/controller.php";
        $content = ob_get_clean();
        
        $controllerPath =  mini::getRunPath()."/apps/admin";
        $controllerFile = $modelName.".class.php";
        
        file_put_contents($controllerPath."/".$controllerFile, $content);
        
        echo $modelName.".class.php controller create successfull.\r\n";
    }
    public function help()
    {
        	return <<<EOD
USAGE
  curd create [model]
        
DESCRIPTION
  create curd for model in apps/admin.
        
EOD;
    }
    
}