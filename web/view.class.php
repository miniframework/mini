<?php
class mini_web_view extends mini_base_component
{
    private $extension = ".view.php";
    private $output = "";
    private $layouts = array();
    public $layoutdata = array();
    private $data = array();
    public function init()
    {
    
    }
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    public function __get($key)
    {
        return $this->data[$key];
    }
    public function setExtension($ext)
    {
        $this->extension = $ext;
    }
    public function render($viewName, $actionName, $viewPath, $return = false)
    {
        if(empty($this->output)) {
            if(($viewFile = $this->getViewFile($viewName, $actionName, $viewPath)) !== false) {
                $this->output = $this->renderFile($viewFile, $this->data, true);
//                if(!empty($this->layouts)) {
//                    foreach($this->layouts as $key => $layoutName)
                    
                    while(!empty($this->layouts))
                    {
                        $layoutName = array_shift($this->layouts);
	                    if(($layoutFile = $this->getLayoutFile($layoutName)) !== false) {
	                        $dataArray = array_merge($this->layoutdata, array("content" => $this->output));
	                        $this->output = $this->renderFile($layoutFile, $dataArray, true);
	                    }
                    }
            }
        }
        if($return) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }
    public function getLayoutPath()
    {
        return mini_base_application::app()->getLayoutPath();
    }
    public function setLayoutData($key, $value)
    {
        $this->layoutdata[$key] = $value;
    }
    public function layout($layoutName, $data = array())
    {
        if(!in_array( $layoutName, $this->layouts))
        {
            array_unshift($this->layouts, $layoutName);
            $this->layoutdata = array_merge($this->layoutdata, $data);
        }
    }
    private function renderFile($viewFile, $data, $return)
    {
        if(is_array($data))
            extract($data, EXTR_PREFIX_SAME, 'data');
        if($return) {
            ob_start();
            ob_implicit_flush(false);
            require ($viewFile);
            return ob_get_clean();
        } else
            require ($viewFile);
    }
    private function getLayoutFile($layoutName)
    {
        $layoutPath = $this->getLayoutPath();
        $layoutFile = $layoutPath . "/" . $layoutName;
        $layoutFile .= $this->extension;
        if(! file_exists($layoutFile))
            throw new Exception($layoutFile . " not exists!");
        return $layoutFile;
    
    }
    private function getViewFile($viewName, $actionName, $viewPath)
    {
        $viewName = empty($viewName) ? $actionName : $viewName;
        $viewName .= $this->extension;
        $viewFile = $viewPath . "/" . $viewName;
        if(! file_exists($viewFile))
            throw new Exception($viewFile . " not exists!");
        return $viewFile;
    
    }
    private function controller($app, $controller, $action, $params = array())
    {
        $route = mini_base_application::app()->getUrlManager();
        $route->setApp($app);
        $route->setController($controller);
        $route->setAction($action);
        $dispatch = mini_base_application::app()->getDispatch();
        
        if($dispatch->getControllerId() == $app . $controller . $action) {
            throw new Exception("controller not same parent!");
        }
        $class = $dispatch->runController($route, mini_web_dispatch::CONTROLLER_TYPE, $params);
        
        if($class == null) {
            throw new Exception("$controller not exitst!");
        }
    }
    private function getPartialFile($partialName, $partialPath)
    {
        $partialFile = $partialPath . "/" . $partialName;
        if(! file_exists($partialFile))
            throw new Exception($partialFile . " not exists!");
        return $partialFile;
    }
    private function getPartialPath()
    {
        return mini_base_application::app()->getPartialPath();
    }
    private function partial($partialName, $data = array(), $return = false)
    {
        $partialPath = $this->getPartialPath();
        if(($partialFile = $this->getPartialFile($partialName, $partialPath)) !== false) {
            $output = $this->renderFile($partialFile, $data, true);
        }
        if($return) {
            return $output;
        } else {
            echo $output;
        }
    }
    private function provider($dataProvider, $params = array(), $filter, $columns, $view,  $type = 'php')
    {
//        array("id"=> "like  %hello%",
//               "and "
    }
}
?>