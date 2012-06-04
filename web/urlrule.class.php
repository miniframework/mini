<?php
class mini_web_urlrule extends mini_base_component implements mini_base_rule
{
    public function init(){}
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
    public function createUrl()
    {
    
    }
}
?>