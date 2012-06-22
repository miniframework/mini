<?php
class mini_web_urlrule extends mini_base_component implements mini_base_rule
{
    public function init(){}
    /**
     * default  parse url rule
     * @see mini_base_rule::parseUrl()
     */
    public function parseUrl($urlmanager, $request, $app)
    {
        $app = $request->app;
        if(empty($app))
            return false;
        $urlmanager->setApp($request->app);
        $urlmanager->setController($request->c);
        $urlmanager->setAction($request->a);
        return true;
    }
    public function createUrl($app, $controller, $action, $params=array(),$query=array())
    {
        $dispatch = mini_base_application::app()->getDispatch();
        if ($app != $dispatch->defaultapp) {
        	$query_array['app'] = strtolower($app);
        }
        if ($controller != $dispatch->defaultcontroller) {
        	$query_array['c'] = strtolower($controller);
        }
        if ($action != $dispatch->defaultaction) {
        	$query_array['a'] = strtolower($action);
        }
        if (! empty($params)) {
        	$query_array = array_merge($query_array, $params);
        }
        if (! empty($query)) {
        	$query_array = array_merge($query_array, $query);
        }
        if (! empty($query_array)) {
        	$query_string = http_build_query($query_array);
        	$url = "/?" . $query_string;
        } else {
        	$url = "/";
        }
        return $url;
    }
}
?>