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
    private $homePath = "";
    private $partialPath = "";
    private $config = null;
    private function __construct()
    {
        $this->id = sprintf('%x' ,crc32($this->name . time()));
        $this->initConfig();
        $this->initPath();
        $this->initEvent();
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
        $this->setHomePath();
        $this->setViewpath();
        $this->setAppPath();
        $this->setLayoutPath();
        $this->setPartialPath();
    }
    public function setLayoutPath($layoutPath = null)
    {
        if($layoutPath == null) {
            $this->layoutPath = $this->homePath . "/" . $this->layouthome;
        } else {
            $this->layoutPath = $layoutPath;
        }
    }
    public function setPartialPath($partialPath = null)
    {
        if($partialPath == null) {
            $this->partialPath = $this->homePath . "/" . $this->partialhome;
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
    public function setHomePath()
    {
        $this->homePath = $this->config->home;
    }
    public function getAppHome()
    {
        return $this->appPath;
    }
    public function setAppPath($appPath = null)
    {
        if($appPath == null) {
            $this->appPath = $this->homePath . "/" . $this->apphome;
        } else {
            $this->appPath = $appPath;
        }
    }
    public function setViewPath($viewPath = null)
    {
        if($viewPath == null) {
            $this->viewPath = $this->homePath . "/" . $this->viewhome;
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
    }
    public function run($route)
    {
        $dispatch = $this->getDispatch();
        $controller = $dispatch->runController($route ,mini_web_dispatch::DEFAULT_TYPE);
        if($controller == null) {
            $controller = $dispatch->runController($route ,mini_web_dispatch::ERROR_TYPE);
            if($controller == null) {
                throw new Exception("errorController not exitst!");
            }
        }
    }
    public function getDispatch()
    {
        return $this->getComponent("mini_web_dispatch");
    }
    public function getRequest()
    {
        return $this->getComponent("mini_http_request");
    }
    public function getEvents()
    {
        return $this->getComponent("mini_base_event");
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