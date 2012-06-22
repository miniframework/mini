<?php
interface mini_base_rule
{
    /**
     * parse url by request
     * @param mini_web_urlmanager $urlmanager
     * @param mini_http_request $request
     * @param string $app
     */
    public function parseUrl($urlmanager, $request, $app);
    /**
     * create url
     */
    public function createUrl($app, $controller, $action, $params=array(),$query=array());
}
?>