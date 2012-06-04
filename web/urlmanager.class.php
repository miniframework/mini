<?php
class mini_web_urlmanager extends mini_base_component
{
    private $rules = array();
    private $urlrule = "mini_web_urlrule";
    private $app = "";
    private $controller = "";
    private $action = "";
    public $caseSensitive = true;
    public function init()
    {
        $this->process();
    }
    public function process()
    {
        $router = mini_base_application::app()->getConfig()->router;
        if(!array_key_exists('class', $router['rule']))
        {
	        foreach ( $router['rule'] as $k => $rule ) {
	            $this->addRules($rule);
	        }
        }
	    else 
            $this->addRules($router['rule']);
    }
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
            throw new Exception("rule must implements of mini_web_baserule");
        }
    }
    public function getAction()
    {
        return $this->action;
    }
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
    public function parseUrl($request)
    {
        foreach ( $this->rules as $k => $rule ) {
            if($rule['class']->parseUrl($this, $request, $rule['app']))
                return $this;
        
        }
        return $this;
    }
}
?>