<?php
class mini_web_urlmanager extends mini_base_component
{
    /**
     * 
     * @var mini_base_rule
     */
    private $rules = array();
    /**
     * 
     * @var default route
     */
    private $urlrule = "mini_web_urlrule";
    /**
     * app name
     * @var string 
     */
    private $app = "";
    /**
     * controller name
     * @var string  
     */
    private $controller = "";
    /**
     * action name
     * @var string 
     */
    private $action = "";
    /**
     * is case sensitive
     * @var boolean
     */
    public $caseSensitive = true;
    public function init()
    {
        $this->process();
    }
    /**
     * process router by request and add route to rules
     */
    public function process()
    {
        $router = mini_base_application::app()->getConfig()->router;
        if(!array_key_exists('class', $router['rules']))
        {
	        foreach ( $router['rules'] as $k => $rule ) {
	            $this->addRules($rule);
	        }
        }
	    else 
            $this->addRules($router['rules']);
    }
    /**
     * add rule to rules
     * @param mini_base_rule $rule
     * @param boolean $append
     */
    public function addRules($rule, $append = true)
    {
        $rule['class'] = mini_base_application::app()->getComponent($rule['class']);
        if($rule['class'] instanceof mini_base_rule) {
            if(!array_key_exists($rule['app'], $this->rules))
            {
	            if($append) {
	                $this->rules[$rule['app']] = $rule;
	            } else {
	                array_unshift($this->rules, $rule);
	            }
            }
        } else {
            mini::e("rule {rule} must implements of mini_base_rule",array('{rule}'=>$rule));
        }
    }
    /**
     * while parse url
     * @param mini_http_requst $request
     * @return mini_web_urlmanager
     */
    public function parseUrl($request)
    {
    	foreach ( $this->rules as $k => $rule ) {
    		if($rule['class']->parseUrl($this, $request, $rule['app']))
    			return $this;
    
    	}
    	return $this;
    }
    /**
     * 
     * @return srting get action
     */
    public function getAction()
    {
        return $this->action;
    }
    /**
     * @return  srting set  action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    public function getController()
    {
        return $this->controller;
    }
    public function setController($controller)
    {
        $this->controller = $controller;
    }
    public function setApp($app)
    {
        $this->app = $app;
    }
    public function getApp()
    {
        return $this->app;
    }
}
?>