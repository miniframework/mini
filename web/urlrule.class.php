<?php
class mini_web_urlrule extends mini_base_component implements mini_base_rule
{

    public function init()
    {
    }

    /**
     * default parse url rule
     * 
     * @see mini_base_rule::parseUrl()
     */
    public function parseUrl($urlmanager, $request, $app)
    {
        $app = $request->app;
        if(empty($app))
            return false;
        $urlmanager->setApp($request->app);
        
        $dispatch = mini_base_application::app()->getDispatch();
        $c = $request->c;
        if(! empty($c)) {
            $urlmanager->setController($c);
        } else {
            $urlmanager->setController($dispatch->defaultcontroller);
        }
        $a = $request->a;
        if(! empty($a)) {
            $urlmanager->setAction($a);
        } else {
            $urlmanager->setAction($dispatch->defaultaction);
        }
        return true;
    
    }

    public function createUrl($app, $controller, $action, $params = array(), $query = array())
    {
        $config = mini::getConfig();
        $default = $config->default;
        if(empty($default)) {
            $dispatch = mini_base_application::app()->getDispatch();
            $default['app'] = $dispatch->defaultapp;
            $default['controller'] = $dispatch->defaultcontroller;
            $default['action'] = $dispatch->defaultaction;
        }
        if($app != $default['app']) {
            $query_array['app'] = strtolower($app);
        }
        if($controller != $default['controller']) {
            $query_array['c'] = strtolower($controller);
        }
        if($action != $default['action']) {
            $query_array['a'] = strtolower($action);
        }
        if(! empty($params)) {
            $query_array = array_merge($query_array ,$params);
        }
        if(! empty($query)) {
            $query_array = array_merge($query_array ,$query);
        }
        if(! empty($query_array)) {
            $query_string = http_build_query($query_array);
            $url = "/?" . $query_string;
        } else {
            $url = "/";
        }
        return $url;
    
    }
}
?>