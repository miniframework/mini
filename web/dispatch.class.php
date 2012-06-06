<?php
class mini_web_dispatch extends mini_base_component
{
    private $defaultapp = "site";
    private $defaultcontroller = "index";
    private $defaultaction = "index";
    private $errorapp = "error";
    private $errorcontroller = "index";
    private $erroraction = "index";
    private $controllerId = "";
    const DEFAULT_TYPE = 'default';
    const ERROR_TYPE = 'error';
    const CONTROLLER_TYPE = 'controller';
    
    public function init()
    {
        $config = mini_base_application::app()->getConfig();
        $default = $config->default;
        if(! empty($default['app'])) {
            $this->defaultapp = $default['app'];
        }
        if(! empty($default['controller'])) {
            $this->defaultcontroller = $default['controller'];
        }
        if(! empty($default['action'])) {
            $this->defaultaction = $default['action'];
        }
        $error = $config->error;
        if(! empty($error['app'])) {
            $this->errorapp = $error['app'];
        }
        if(! empty($error['controller'])) {
            $this->errorcontroller = $error['controller'];
        }
        if(! empty($error['action'])) {
            $this->erroraction = $error['action'];
        }
    }
    public function runController($route, $type = 0, $params = array())
    {
        
        if($type == self::DEFAULT_TYPE) {
            $app = $route->getApp();
            $controller = $route->getController();
            $action = $route->getAction();
            if(empty($app)) {
                $app = $this->defaultapp;
            }
            if(empty($controller)) {
                $controller = $this->defaultcontroller;
            }
            if(empty($action)) {
                $action = $this->defaultaction;
            }
        } else if($type == self::ERROR_TYPE) {
            $app = $this->errorapp;
            $controller = $this->errorcontroller;
            $action = $this->erroraction;
        } else {
            $app = $route->getApp();
            $controller = $route->getController();
            $action = $route->getAction();
        }
        
        if($route->caseSensitive) {
            $app = strtolower($app);
            $controller = strtolower($controller);
            $action = strtolower($action);
        }
        $appPath = mini_base_application::app()->getAppHome();
        if(! file_exists($appPath . "/" . $app)) {
            return null;
        }
        $event = mini_base_application::app()->getEvents();
        $event->onbeginApp(array('app'=>$app));
        
        
        $className = $controller . "Controller";
        $classFile = $appPath . "/" . $app . "/" . $controller . ".class.php";
        if(! file_exists($classFile)) {
            return null;
        }
        
        require_once $classFile;
        
        if(! class_exists($className, false) || ! is_subclass_of($className, "mini_web_controller")) {
            return null;
        }
        $event->onbeforeApp(array('app'=>$app));
        $event->onbeforeController(array('app'=>$app,'controller'=>$controller));
        
        
        $class = new $className();
        $actionName = "do" . ucfirst($action);
        if(! method_exists($class, $actionName)) {
            return null;
        }
        
       
        
        
       
        $class->setControllerMap(  $route->getApp(), 
					        $route->getController(), 
					        $route->getAction(), 
					        $app, 
					        $controller, 
					        $action);
	
		$class->setParentId($this->controllerId);
        $class->setParams($params);
		$this->controllerId = $app.$controller.$action;
        $class->init();
        $class->run($actionName);
        $event->onendController(array('app'=>$app,'controller'=>$controller));
        $event->onendApp(array('app'=>$app));
        
        return $class;
    }
    public function getControllerId()
    {
        return $this->controllerId;
    }

}
?>