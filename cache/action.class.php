<?php
class mini_cache_action
{
    private $event = null;
    private $key = null;
    private $expire = 0;
    private $type = "";
    private $data = null;
    public $controller = null;

    public function beginCache($controller)
    {
        $this->controller = $controller;
        $this->event = mini_base_application::app()->getEvent();
        
        $this->event->onbeginCache(array("app"=>$controller->app,"controller"=>$controller->controller,"action"=>$controller->action) ,$controller ,$this);
    
    }

    public function cache($type, $key, $expire)
    {
        $this->type = $type;
        $this->key = $key;
        $this->expire = $expire;
        
        $cache = mini_cache_manager::getHandle();
        $caching = $cache->getCache($type);
        if($cachedata = $caching->get($key)) {
            $this->data = $cachedata;
            if($this->controller->parentId) {
                echo $this->data;
            }
        } else {
            if($this->controller->parentId) {
                ob_start();
                ob_implicit_flush(false);
            }
        }
    
    }

    public function getData()
    {
        if(! $this->key)
            return false;
        return $this->data;
    
    }

    public function endCache($value)
    {
        if($this->key != null && ! $this->data) {
            $cache = mini_cache_manager::getHandle();
            $caching = $cache->getCache($this->type);
            if($this->controller->parentId) {
                $value = ob_get_clean();
            }
            $caching->set($this->key ,$value ,$this->expire);
            echo $value;
        }
    
    }
}