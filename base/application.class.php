<?php
class mini_base_application
{
    private static $app = null;
    private $name = 'My Application';
    private $id = 0;
    private $components = array();
    private $apphome = "apps";
    private $viewhome = "views";
    private $layouthome = "/views/layout";
    private $partialhome = "/views/partial";
    private $layoutPath = "";
    private $viewPath = "";
    private $appPath = "";
    private $runPath = "";
    private $partialPath = "";
    private $config = null;
    private $logger = null;
    private function __construct()
    {
        
        if(mini::$handle == null) 
             mini::e("must first init mini.");
        $this->id = sprintf('%x' ,crc32($this->name . time()));
        $this->initLogger();
        $this->initConfig();
        $this->initPath();
        
    }
    private function initLogger()
    {
        $this->logger = mini::getLogger();
    }
    public function getLogger()
    {
        return $this->logger;
    }
    private function initConfig()
    {
        $this->config = mini::getConfig();
    }
    private function initEvent()
    {
      $this->getEvents()->addEvent(new mini_web_event());
    }
    public function getConfig()
    {
        return $this->config;
    }
    private function initPath()
    {
        $this->setRunPath();
        $this->setViewpath();
        $this->setAppPath();
        $this->setLayoutPath();
        $this->setPartialPath();
    }
    public function setLayoutPath($layoutPath = null)
    {
        if($layoutPath == null) {
            $this->layoutPath = $this->runPath . "/" . $this->layouthome;
        } else {
            $this->layoutPath = $layoutPath;
        }
    }
    public function setPartialPath($partialPath = null)
    {
        if($partialPath == null) {
            $this->partialPath = $this->runPath . "/" . $this->partialhome;
        } else {
            $this->partialPath = $partialPath;
        }
    }
    public function getPartialPath()
    {
        return $this->partialPath;
    }
    public function getLayoutPath()
    {
        return $this->layoutPath;
    }
    public function getRunPath()
    {
        return $this->runPath;
    }
    public function setRunPath()
    {
        $this->runPath = mini::getRunPath();
    }
    public function getAppHome()
    {
        return $this->appPath;
    }
    public function setAppPath($appPath = null)
    {
        if($appPath == null) {
            $this->appPath = $this->runPath . "/" . $this->apphome;
        } else {
            $this->appPath = $appPath;
        }
    }
    public function setViewPath($viewPath = null)
    {
        if($viewPath == null) {
            $this->viewPath = $this->runPath . "/" . $this->viewhome;
        } else {
            $this->viewPath = $viewPath;
        }
    }
    public function getViewPath()
    {
        return $this->viewPath;
    }
    public static function app()
    {
        if(self::$app == null)
            self::$app = new self();
        return self::$app;
    }
    public function getId()
    {
        return $this->id;
    }
    public function process()
    {
        $route = $this->getUrlManager()->parseUrl($this->getRequest());
        $this->run($route);
        return $this;
    }
    public function run($route)
    {
        $dispatch = $this->getDispatch();
        $controller = $dispatch->runController($route ,mini_web_dispatch::DEFAULT_TYPE);
        if($controller == null) {
            $controller = $dispatch->runController($route ,mini_web_dispatch::ERROR_TYPE);
            if($controller == null) {
                mini::e("errorController not exists");
            }
        }
        $this->getResponse()->sendResponse();
    }
    public function end()
    {
        mini::end();
    }
    public function getDispatch()
    {
        return $this->getComponent("mini_web_dispatch");
    }
    public function getRequest()
    {
        return $this->getComponent("mini_http_request");
    }
    public function getEvent()
    {
        return mini::getEvent();
    }
    public function getUrlManager()
    {
        return $this->getComponent("mini_web_urlmanager");
    }
    public function getResponse()
    {
        return $this->getComponent("mini_http_response");
    }
    // public function getView()
    // {
    // return $this->getComponent("mini_web_view");
    // }
    public function getComponent($name)
    {
        if(isset($this->components[$name])) {
            $component = $this->components[$name];
        } else {
            $component = mini::createComponent($name);
            $this->components[$name] = $component;
        }
        return $component;
    }
}
?>