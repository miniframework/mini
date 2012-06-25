<?php
class mini_cli_command_curd extends mini_cli_command
{
    public $viewName = array("List","addview");
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
                }
            }    
            
        		
        }
        else
        {
        	echo  $this->help();
        }

    }
    public function createaddview($model)
    {
    	$modeName = get_class($model);
    	ob_start();
    	ob_implicit_flush(false);
    	include dirname(__FILE__)."/view/addview.php";
    	$content = ob_get_clean();
    
    	$viewPath =  mini::getRunPath()."/views/admin";
    	$viewFile = "addview.view.php";
    	$view = $viewPath."/".$modeName."/".$viewFile;
    	if(file_exists($view))
    	{
    		echo "view file: $viewFile exists. delete first.";
    		return;
    	}
    	
    	file_put_contents($view, $content);
    	echo $viewFile." view create successfull.\r\n";
    }
    public function createList($model)
    {
        $modeName = get_class($model);
        ob_start();
        ob_implicit_flush(false);
        include dirname(__FILE__)."/view/list.php";
        $content = ob_get_clean();
        
        $viewPath =  mini::getRunPath()."/views/admin";
        $viewFile = "list.view.php";
        $view = $viewPath."/".$modeName."/".$viewFile;
    	if(file_exists($view))
    	{
    		echo "view file: $viewFile exists. delete first.";
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