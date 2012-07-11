<?php
class mini_web_urlregex extends mini_base_component implements mini_base_rule
{
    public $rewrite = array();

    public function init()
    {
        $this->rewrite = $this->rewrite();
    
    }

    public function rewrite()
    {
        $runPath = mini_base_application::app()->getRunPath();
        $rewrite = $runPath . "/config/rewrite.php";
        if(! file_exists($rewrite)) {
            mini::e("rewrite file not exists" ,array("{rewrite}"=>$rewrite));
        }
        return include $rewrite;
    
    }

    public function parseUrl($urlmanager, $request, $app)
    {
        $app = $request->app;
        if(!empty($app))
        	return false;
        $uri = $request->getRequestUri();
        preg_match('/([^.|?]*)/' ,$uri ,$urimatch);
        $uri = $urimatch[1];
        foreach($this->rewrite as $key => $map) {
            $keystr = str_replace('/' ,'\/' ,$key);
            $pregkeys = array();
            if(preg_match_all('/<(.*?)>/' ,$key ,$matches)) {
                $pregkeys = $matches[1];
            }
            $pattern = "/^{$keystr}$/i";
            $params = $map;
            if(preg_match($pattern ,$uri ,$match)) {
                foreach($pregkeys as $k => $v) {
                    $params[$v] = isset($map[$v]) ? $map[$v] : $match[$v];
                }
                foreach($params as $key => $param) {
                    if($key == 'app')
                        $urlmanager->setApp($param);
                    else if($key == 'controller')
                        $urlmanager->setController($param);
                    else if($key == 'action')
                        $urlmanager->setAction($param);
                    else
                        $request->$key = $param;
                }
                return true;
            }
        }
        return false;
    
    }

    public function createUrl($app, $controller, $action, $params = array(), $query = array())
    {
        $pattern = '/(\(\?P<(.*?)>(?:.*?)\))/';
        
        $values = array("app"=>$app,"controller"=>$controller,"action"=>$action);
        $values = array_merge($values ,$params);
        foreach($this->rewrite as $key => $map) {
            if(preg_match_all($pattern ,$key ,$matches)) {
                $rewritekey = array_combine($matches[1] ,$matches[2]);
            }
            if((! isset($map['app']) || $app == $map['app']) &&
                 (! isset($map['controller']) || $controller == $map['controller']) &&
                 (! isset($map['action']) || $action == $map['action'])) {
                
                if(! empty($rewritekey)) {
                    foreach($rewritekey as $k => $v) {
                        $rewritevalue[$k] = isset($values[$v]) ? $values[$v] : '';
                    }
                    $uri = strtr($key ,$rewritevalue);
                } else {
                    $uri = $key;
                }
                $uri = str_replace('?' ,'' ,$uri);
                $querystr = http_build_query($query);
                if(!empty($query) && !empty($querystr))
                    $uri = $uri."?".$querystr;
                return $uri;
            }
        }
    
    }
}