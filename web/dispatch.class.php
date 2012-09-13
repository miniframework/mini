<?php
class mini_web_dispatch extends mini_base_component
{
    /**
     *
     * @var string default app name
     */
    public $defaultapp = "site";
    /**
     *
     * @var string default controller name
     */
    public $defaultcontroller = "index";
    /**
     *
     * @var string default action name
     */
    public $defaultaction = "index";
    /**
     *
     * @var string default error app name
     */
    private $errorapp = "error";
    /**
     *
     * @var string default error controller name
     */
    private $errorcontroller = "index";
    /**
     *
     * @var string default error action name
     */
    private $erroraction = "index";
    /**
     *
     * @var current app,controller,action id.
     */
    private $controllerId = "";
    /**
     * default:dispatch application ,error:dispatch to error , controller: user
     * call dispatch.
     * 
     * @var string dispatch type
     */
    const DEFAULT_TYPE = 'default';
    const ERROR_TYPE = 'error';
    const CONTROLLER_TYPE = 'controller';

    /**
     * init default app,controller,action name
     * 
     * @see mini_base_component::init()
     */
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

    /**
     * create a controller by request
     * 
     * @param mini_web_urlmanager $route
     * @param string $type 
     * @param array $params set controller params {@link mini_web_view::controller}
     * @return mini_web_controller
     */
    public function runController($route, $type = 0, $params = array())
    {
        if($type == self::DEFAULT_TYPE) {
            $app = $route->getApp();
            $controller = $route->getController();
            $action = $route->getAction();
//             if(empty($app)) {
//                 $app = $this->defaultapp;
//             }
//             if(empty($controller)) {
//                 $controller = $this->defaultcontroller;
//             }
//             if(empty($action)) {
//                 $action = $this->defaultaction;
//             }
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
        $event = mini_base_application::app()->getEvent();
        $event->onbeginApp(array('app'=>$app));
        
        $className = $controller . "Controller";
        $classFile = $appPath . "/" . $app . "/" . $controller . ".class.php";
        if(! file_exists($classFile)) {
            return null;
        }
        require_once $classFile;
        
        if(! class_exists($className ,false) || ! is_subclass_of($className ,"mini_web_controller")) {
            return null;
        }
        $event->onbeforeApp(array('app'=>$app));
        $event->onbeforeController(array('app'=>$app,'controller'=>$controller));
        
        $class = new $className();
        // $class = mini_base_application::app()->getComponent($className);
        $actionName = "do" . ucfirst($action);
        if(! method_exists($class ,$actionName)) {
            return null;
        }
        
        $class->init();
        
        $map = array("oapp"=>$route->getApp(),"ocontroller"=>$route->getController(),"oaction"=>$route->getAction(),"app"=>$app,"controller"=>$controller,"action"=>$action);
        
        $class->setControllerMap($map);
        
        $class->setParentId($this->controllerId);
        $this->controllerId = $app . $controller . $action;
        
        $class->setParams($params);
        
        $class->run($actionName);
        $event->onendController(array('app'=>$app,'controller'=>$controller));
        $event->onendApp(array('app'=>$app));
        
        return $class;
    
    }

    /**
     * return app,controller,action id.
     * 
     * @return string
     */
    public function getControllerId()
    {
        return $this->controllerId;
    
    }
}
?>