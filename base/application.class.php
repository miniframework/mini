<?php
class mini_base_application
{
    /**
     *
     * @var mini_base_application applicatin handle single
     */
    private static $app = null;
    /**
     *
     * @var string application name
     */
    private $name = 'My Application';
    /**
     *
     * @var int application id
     */
    private $id = 0;
    /**
     *
     * @var array created object.
     */
    private $components = array();
    /**
     *
     * @var string app relative path name, default apps.
     */
    private $apphome = "apps";
    /**
     *
     * @var string view relative path name, default views.
     */
    private $viewhome = "views";
    /**
     *
     * @var string layout path name, default /views/layout.
     */
    private $layouthome = "/views/layout";
    /**
     *
     * @var string partial path name, default /views/partial.
     */
    private $partialhome = "/views/partial";
    private $layoutPath = "";
    private $viewPath = "";
    private $appPath = "";
    private $runPath = "";
    private $partialPath = "";
    /**
     *
     * @var mini_boot_config config handle get from mini
     */
    private $config = null;
    /**
     *
     * @var mini_base_log config handle get from mini
     */
    private $logger = null;

    /**
     * construct create id , get logger , config from mini and init path
     */
    private function __construct()
    {
        if(mini::$handle == null)
            mini::e("must first init mini.");
        $this->id = sprintf('%x' ,crc32($this->name . time()));
        $this->initLogger();
        $this->initConfig();
        $this->initPath();
        $this->initEvent();
    
    }

    /**
     * get logger handle from mini
     */
    private function initLogger()
    {
        $this->logger = mini::getLogger();
    
    }

    /**
     * get logger
     * 
     * @return mini_base_log
     */
    public function getLogger()
    {
        return $this->logger;
    
    }

    /**
     * get config handle from mini
     */
    private function initConfig()
    {
        $this->config = mini::getConfig();
    
    }

    /**
     * init event add mini_web_event to events list.
     */
    private function initEvent()
    {
        $this->getEvent()->addEvent(new mini_web_event());
    
    }

    /**
     * get user config
     * 
     * @return mini_boot_config
     */
    public function getConfig()
    {
        return $this->config;
    
    }

    /**
     * init application run path ,view path, app path, layout path, partial
     * path.
     */
    private function initPath()
    {
        $this->setRunPath();
        $this->setViewpath();
        $this->setAppPath();
        $this->setLayoutPath();
        $this->setPartialPath();
    
    }

    /**
     * set layout path
     * 
     * @param string $layoutPath
     */
    public function setLayoutPath($layoutPath = null)
    {
        if($layoutPath == null) {
            $this->layoutPath = $this->runPath . "/" . $this->layouthome;
        } else {
            $this->layoutPath = $layoutPath;
        }
    
    }

    /**
     * set partial path.
     * 
     * @param string $partialPath
     */
    public function setPartialPath($partialPath = null)
    {
        if($partialPath == null) {
            $this->partialPath = $this->runPath . "/" . $this->partialhome;
        } else {
            $this->partialPath = $partialPath;
        }
    
    }

    /**
     * get partial path
     * 
     * @return string
     */
    public function getPartialPath()
    {
        return $this->partialPath;
    
    }

    /**
     * get layout path
     * 
     * @return string
     */
    public function getLayoutPath()
    {
        return $this->layoutPath;
    
    }

    /**
     * set application root path
     * 
     * @return string
     */
    public function getRunPath()
    {
        return $this->runPath;
    
    }

    /**
     * set application root path
     */
    public function setRunPath()
    {
        $this->runPath = mini::getRunPath();
    
    }

    /**
     * return app path
     * 
     * @return string
     */
    public function getAppHome()
    {
        return $this->appPath;
    
    }

    /**
     * set app path
     * 
     * @param string $appPath
     */
    public function setAppPath($appPath = null)
    {
        if($appPath == null) {
            $this->appPath = $this->runPath . "/" . $this->apphome;
        } else {
            $this->appPath = $appPath;
        }
    
    }

    /**
     * set view path
     * 
     * @param string $viewPath
     */
    public function setViewPath($viewPath = null)
    {
        if($viewPath == null) {
            $this->viewPath = $this->runPath . "/" . $this->viewhome;
        } else {
            $this->viewPath = $viewPath;
        }
    
    }

    /**
     * get view path
     * 
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    
    }

    /**
     * create a application
     * 
     * @return mini_base_application
     */
    public static function app()
    {
        if(self::$app == null)
            self::$app = new self();
        return self::$app;
    
    }

    /**
     * return application id
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    
    }

    /**
     * process request
     * 
     * @return mini_base_application
     */
    public function process()
    {
        $route = $this->getUrlManager()->parseUrl($this->getRequest());
        $this->run($route);
        return $this;
    
    }

    /**
     * dispatch request
     * 
     * @param mini_web_urlmanager $route
     */
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

    /**
     * end application {@link mini::end()}
     */
    public function end()
    {
        mini::end();
    
    }

    /**
     * create a dispatch is singleton
     * 
     * @return mini_web_dispatch
     */
    public function getDispatch()
    {
        return $this->getComponent("mini_web_dispatch");
    
    }

    /**
     * create a request is singleton
     * 
     * @return mini_http_request
     */
    public function getRequest()
    {
        return $this->getComponent("mini_http_request");
    
    }

    /**
     * get event from mini
     * 
     * @return mini_base_event
     */
    public function getEvent()
    {
        return mini::getEvent();
    
    }

    /**
     * create a urlmanager is singleton
     * 
     * @return mini_web_urlmanager
     */
    public function getUrlManager()
    {
        return $this->getComponent("mini_web_urlmanager");
    
    }

    /**
     * create a response is singleton
     * 
     * @return mini_http_response
     */
    public function getResponse()
    {
        return $this->getComponent("mini_http_response");
    
    }

    /**
     * create singleton extends mini_base_component object
     * 
     * @param string $name
     * @return object
     */
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