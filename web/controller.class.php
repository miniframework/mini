<?php
class mini_web_controller extends mini_base_component
{
    /**
     * original app name from router not formated.
     * 
     * @var string
     */
    public $oapp = "";
    /**
     * original controller name from router not formated.
     * 
     * @var string
     */
    public $ocontroller = "";
    /**
     * original action name from router not formated.
     * 
     * @var string
     */
    public $oaction = "";
    /**
     * formated app name.
     * 
     * @var string
     */
    public $app = "";
    /**
     * formated controller name.
     * 
     * @var string
     */
    public $controller = "";
    /**
     * formated action name.
     * 
     * @var string
     */
    public $action = "";
    /**
     *
     * @var mini_http_request
     */
    public $request = null;
    /**
     *
     * @var mini_http_response
     */
    public $response = null;
    /**
     *
     * @var mini_web_urlmanager
     */
    public $route = null;
    /**
     *
     * @var mini_web_view
     */
    public $view = null;
    /**
     *
     * @var mini_boot_config
     */
    public $config = null;
    /**
     *
     * @var mini_base_event
     */
    public $event = null;
    /**
     *
     * @var mini_base_log
     */
    public $logger = null;
    /**
     * whether is render view
     * 
     * @var boolean
     */
    public $cancelRender = false;
    /**
     * user call dispatch set params {@link mini_web_dispatch}
     * 
     * @var array
     */
    public $params = array();
    /**
     *
     * @var string controller id
     */
    public $id = "";
    /**
     *
     * @var parent controller id
     */
    public $parentId = "";
    public $cacheProperties = array();
    public $cachedata = '';
    /**
     * call controller method action from a request
     * 
     * @param string $action action name
     */
    public function run($action)
    {
        $this->event->onbeforeAction(array("app"=>$this->app,"controller"=>$this->controller,"action"=>$this->action));
        $this->doInit();
        $this->event->onbeginCache(array("app"=>$this->app,"controller"=>$this->controller,"action"=>$this->action),$this);
        if(!$this->cachedata)
            $this->$action();
        $this->event->onendAction(array("app"=>$this->app,"controller"=>$this->controller,"action"=>$this->action));
        $this->event->onautoSave(array("app"=>$this->app,"controller"=>$this->controller,"action"=>$this->action));
        if(! $this->cancelRender && ! $this->response->isRedirect()) {
            if(!$this->cachedata)
            {
                $view = $this->render();
                $this->event->onendCache($this, $view);
            }
            else
            {
                $view = $this->cachedata;
            }
            $this->response->appendBody($view);
        }
    
    }

    /**
     * init router, request, config, config, event, view from application.
     * 
     * @see mini_base_component::init()
     */
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

    /**
     * action per call method
     */
    public function doInit()
    {
    }

    /**
     * close view render
     * 
     * @return boolean
     */
    public function closeRender()
    {
        return $this->cancelRender = true;
    
    }

    /**
     * open view render
     * 
     * @return boolean
     */
    public function openRender()
    {
        return $this->cancelRender = false;
    
    }

    /**
     * create view path
     * 
     * @return string
     */
    public function getViewPath()
    {
        $basePath = mini_base_application::app()->getViewPath();
        $viewPath = $basePath . "/" . $this->app . "/" . $this->controller;
        return $viewPath;
    
    }

    /**
     * render view file
     * 
     * @param string $viewName view name
     * @param boolean $return is ob_start
     * @return string
     */
    public function render($viewName = "", $return = true)
    {
        $this->closeRender();
        if($this->parentId)
            $return = false;
        return $this->view->render($viewName ,$this->action ,$this->getViewPath() ,$return);
    
    }

    /**
     * set controller oapp,app,controller,ocontroller and so on from dispatch.
     * 
     * @param array $map
     */
    public function setControllerMap($map)
    {
        $this->oapp = $map['oapp'];
        $this->ocontroller = $map['ocontroller'];
        $this->oaction = $map['oaction'];
        $this->app = $map['app'];
        $this->controller = $map['controller'];
        $this->action = $map['action'];
        $this->id = $this->app . $this->controller . $this->action;
    
    }

    /**
     * user call dispatch set params {@link mini_web_dispatch}
     * 
     * @param array $params
     */
    public function setParams($params = array())
    {
        $this->params = $params;
    
    }

    /**
     * set parent id from dispatch
     * 
     * @param string $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    
    }

    /**
     * create a model
     * 
     * @param mini_db_model $class
     */
    public function model($class)
    {
        return mini_db_model::model($class);
    
    }

    /**
     * location to url, and call application end
     * 
     * @param string $url
     */
    public function jump($url)
    {
        $this->event->onautoSave(array("app"=>$this->app,"controller"=>$this->controller,"action"=>$this->action));
        header("Location: " . $url);
        mini_base_application::app()->end();
    
    }
}
?>