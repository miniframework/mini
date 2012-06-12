<?php
class mini_web_view extends mini_base_component
{
    /**
     *
     * @var string default view ext.
     */
    private $extension = ".view.php";
    /**
     *
     * @var string view content
     */
    private $output = "";
    /**
     *
     * @var array layout names
     */
    private $layouts = array();
    /**
     *
     * @var array action pass params to layout.
     */
    public $layoutdata = array();
    /**
     *
     * @var array action pass params to view.
     */
    private $data = array();

    /**
     * init view
     *
     * @see mini_base_component::init()
     */
    public function init()
    {
    }

    /**
     * action pass params to view
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    
    }

    /**
     * get param from data
     *
     * @param string $key
     * @return string:
     */
    public function __get($key)
    {
        return $this->data[$key];
    
    }

    /**
     * set view ext.
     *
     * @param string $ext
     */
    public function setExtension($ext)
    {
        $this->extension = $ext;
    
    }

    /**
     * render view
     *
     * @param string $viewName view name
     * @param string $actionName action name
     * @param string $viewPath view path
     * @param boolean $return is ob_start if true echo view content , if false
     * return view content
     * @return string view content
     */
    public function render($viewName, $actionName, $viewPath, $return = false)
    {
        if(empty($this->output)) {
            if(($viewFile = $this->getViewFile($viewName ,$actionName ,$viewPath)) !== false) {
                $this->output = $this->renderFile($viewFile ,$this->data ,true);
                // if(!empty($this->layouts)) {
                // foreach($this->layouts as $key => $layoutName)
                
                while(! empty($this->layouts)) {
                    $layoutName = array_shift($this->layouts);
                    if(($layoutFile = $this->getLayoutFile($layoutName)) !== false) {
                        $dataArray = array_merge($this->layoutdata ,array("content"=>$this->output));
                        $this->output = $this->renderFile($layoutFile ,$dataArray ,true);
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

    /**
     * get layout path
     *
     * @return string
     */
    public function getLayoutPath()
    {
        return mini_base_application::app()->getLayoutPath();
    
    }

    /**
     * set layout params
     *
     * @param string $key
     * @param string $value
     */
    public function setLayoutData($key, $value)
    {
        $this->layoutdata[$key] = $value;
    
    }

    /**
     * set layout name and pass params
     *
     * @param srting $layoutName layout name
     * @param srting $data pass layout params
     */
    public function layout($layoutName, $data = array())
    {
        if(! in_array($layoutName ,$this->layouts)) {
            array_unshift($this->layouts ,$layoutName);
            $this->layoutdata = array_merge($this->layoutdata ,$data);
        }
    
    }

    /**
     * render view file and extract data
     *
     * @param string $viewFile
     * @param array $data
     * @param boolean $return
     * @return string file content
     */
    private function renderFile($viewFile, $data, $return)
    {
        if(is_array($data))
            extract($data ,EXTR_PREFIX_SAME ,'data');
        if($return) {
            ob_start();
            ob_implicit_flush(false);
            require ($viewFile);
            return ob_get_clean();
        } else
            require ($viewFile);
    
    }

    /**
     * get layout file
     *
     * @param string $layoutName layout name
     * @return string layout file path
     */
    private function getLayoutFile($layoutName)
    {
        $layoutPath = $this->getLayoutPath();
        $layoutFile = $layoutPath . "/" . $layoutName;
        $layoutFile .= $this->extension;
        if(! file_exists($layoutFile))
            mini::e('layout file {layoutFile} not exists.' ,array('{layoutFile}'=>$layoutFile));
        return $layoutFile;
    
    }

    /**
     * get view file
     *
     * @param string $viewName view name
     * @param string $actionName action name
     * @param string $viewPath view path
     * @return string view file path
     */
    private function getViewFile($viewName, $actionName, $viewPath)
    {
        $viewName = empty($viewName) ? $actionName : $viewName;
        $viewName .= $this->extension;
        $viewFile = $viewPath . "/" . $viewName;
        if(! file_exists($viewFile))
            mini::e('view file {viewFile} not exists.' ,array('{viewFile}'=>$viewFile));
        return $viewFile;
    
    }

    /**
     * call dispatch render controller
     *
     * @param string $app app name
     * @param string $controller controller name
     * @param string $action action name
     * @param array $params pass controller params
     */
    private function controller($app, $controller, $action, $params = array())
    {
        $route = mini_base_application::app()->getUrlManager();
        $route->setApp($app);
        $route->setController($controller);
        $route->setAction($action);
        $dispatch = mini_base_application::app()->getDispatch();
        
        if($dispatch->getControllerId() == $app . $controller . $action) {
            mini::e('controller {controller} not same parent.' ,array('{controller}'=>$dispatch->getControllerId()));
        }
        $class = $dispatch->runController($route ,mini_web_dispatch::CONTROLLER_TYPE ,$params);
        if($class == null) {
            mini::e('controller {controller} not exitst.' ,array('{controller}'=>$controller));
        }
    
    }

    /**
     * get partial file
     *
     * @param string $partialName partial name
     * @param string $partialPath partial path
     * @return string partial file path
     */
    private function getPartialFile($partialName, $partialPath)
    {
        $partialFile = $partialPath . "/" . $partialName;
        if(! file_exists($partialFile))
            mini::e('view file {partialFile} not exists.' ,array('{partialFile}'=>$partialFile));
        return $partialFile;
    
    }

    /**
     * get partial path
     *
     * @return string
     */
    private function getPartialPath()
    {
        return mini_base_application::app()->getPartialPath();
    
    }

    /**
     * render partial file
     *
     * @param string $partialName partial name
     * @param string $data pass partial params
     * @param string $return is ob_start
     * @return string partial content
     */
    private function partial($partialName, $data = array(), $return = false)
    {
        $partialPath = $this->getPartialPath();
        if(($partialFile = $this->getPartialFile($partialName ,$partialPath)) !== false) {
            $output = $this->renderFile($partialFile ,$data ,true);
        }
        if($return) {
            return $output;
        } else {
            echo $output;
        }
    
    }

    private function provider($dataProvider, $params = array(), $filter, $columns, $view, $type = 'php')
    {
    }
}
?>