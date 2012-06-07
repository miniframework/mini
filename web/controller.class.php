<?php
class mini_web_controller extends mini_base_component
{
    public $oapp = "";
    public $ocontroller = "";
    public $oaction = "";
    public $app = "";
    public $controller = "";
    public $action = "";
    public $request = null;
    public $response = null;
    public $route = null;
    public $view = null;
    public $cancelRender = false;
    public $params = array();
    public $id = "";
    public $parentId = "";
    public $data = array();
    public $config = null;
    public $event = null;
    public $logger = null;
    public function run($action)
    {
        $this->event->onbeforeAction(array("app"=>$this->app,"controller"=>$this->controller, "action"=>$this->action));
        $this->doInit();
        $this->$action();
        $this->event->onendAction(array("app"=>$this->app,"controller"=>$this->controller, "action"=>$this->action));
        $this->event->onautoSave(array("app"=>$this->app,"controller"=>$this->controller, "action"=>$this->action));
        if(! $this->cancelRender && !$this->response->isRedirect()) {
            $view = $this->render();
            $this->response->appendBody($view);
        }
    }
    
    public function init()
    {
       
        $this->route = mini_base_application::app()->getUrlManager();
        $this->request = mini_base_application::app()->getRequest();
        $this->response = mini_base_application::app()->getResponse();
        $this->config = mini_base_application::app()->getConfig();
        $this->logger = mini_base_application::app()->getLogger();
        $this->event = mini_base_application::app()->getEvent();
        $this->view = mini::createComponent("mini_web_view");
        $this->openRender();
    }
    public function doInit()
    {
        
    }
    public function closeRender()
    {
        return $this->cancelRender = true;
    }
    public function openRender()
    {
        return $this->cancelRender = false;
    }
    public function getViewPath()
    {
        $basePath = mini_base_application::app()->getViewPath();
        $viewPath = $basePath . "/" . $this->app . "/" . $this->controller;
        return $viewPath;
    }
    public function render($viewName = "", $return = true)
    {
        $this->closeRender();
        if($this->parentId) $return = false;
        return $this->view->render($viewName, $this->action, $this->getViewPath(), $return);
    }
    public function setControllerMap($map)
    {
        $this->oapp = $map['oapp'];
        $this->ocontroller = $map['ocontroller'];
        $this->oaction = $map['oaction'];
        $this->app = $map['app'];
        $this->controller = $map['controller'];
        $this->action = $map['action'];
        $this->id = $this->app.$this->controller.$this->action;
    }
    public function setParams($params = array())
    {
        $this->params = $params;
    }
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }
    public function model($class)
    {
        return mini_db_model::model($class);
    }
    public function jump($url)
    {
        $this->event->onautoSave(array("app"=>$this->app,"controller"=>$this->controller, "action"=>$this->action));
        header("Location: ".$url);
        mini_base_application::app()->end();
    }
   
}
?>