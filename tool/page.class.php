<?php
class mini_tool_page
{
    public $conut = 0;
    public $pagesize = 15;
    public $currentpage = 0;
    public $pageVar = "page";
    public $request = null;
    public $pageHtml = "";
    public $url = array();
    public $route = null;
    public $showpage = 10;

    public function __construct($params = array())
    {
        foreach($params as $key => $value) {
            $this->$key = $value;
        }
    
    }

    public function setRoute($route)
    {
        $this->route = $route;
    
    }

    public function createUrl($page)
    {
        $url = $this->url;
        if(empty($url) || ! is_array($url) || $this->route == null) {
            $page_query = $_GET;
            $page_query[$this->pageVar] = $page;
            
            return $this->pathInfo() . "?" . http_build_query($page_query);
        } else {
            
            $page = array($this->pageVar=>$page);
            if(isset($url[3]) && is_array($url[3])) {
                array_merge($page ,$url[3]);
            }
            return $this->route->createUrl($url[0] ,$url[1] ,$url[2] ,$page);
        }
    
    }

    public function pathInfo()
    {
        if(! array_key_exists('PATH_INFO' ,$_SERVER)) {
            $pos = strpos($_SERVER['REQUEST_URI'] ,$_SERVER['QUERY_STRING']);
            
            $asd = substr($_SERVER['REQUEST_URI'] ,0 ,$pos - 2);
            $asd = substr($asd ,strlen($_SERVER['SCRIPT_NAME']) + 1);
            
            return $asd;
        } else {
            return trim($_SERVER['PATH_INFO'] ,'/');
        }
    
    }

    public function pageHtml($currentcss = "page_current")
    {
        $pagecount = $this->getPageCount();
        $current = $this->getCurrentPage();
        $html = "";
        if($current != 1)
            $html .= '<a href="' . $this->createUrl($current - 1) . '">上一页</a>';
        if($pagecount <= $this->showpage) {
            for($i = 1; $i <= $pagecount; $i ++) {
                $class = "";
                if($i == $current)
                    $class = "class=\"$currentcss\"";
                $html .= '<a ' . $class . ' href="' . $this->createUrl($i) . '">' . $i . '</a>';
            }
        } else {
            $startpage = ceil($this->showpage / 2);
            $start = $current - $startpage;
            if($start <= 0)
                $start = 1;
            if($start != 1) {
                $html .= '<a href="' . $this->createUrl(1) . '">1</a>';
                $html .= "....";
            }
            
            for($i = $start; $i <= $current; $i ++) {
                $class = "";
                if($i == $current)
                    $class = "class=\"$currentcss\"";
                $html .= '<a  ' . $class . ' href="' . $this->createUrl($i) . '">' . $i . '</a>';
            }
            $end = $current + $startpage;
            if($end >= $pagecount)
                $end = $pagecount;
            for($i = $current + 1; $i <= $end; $i ++) {
                $html .= '<a href="' . $this->createUrl($i) . '">' . $i . '</a>';
            }
            
            if($end != $pagecount) {
                $html .= "....";
                $html .= '<a href="' . $this->createUrl($pagecount) . '">' . $pagecount . '</a>';
            }
        }
        if($current != $pagecount)
            $html .= '<a href="' . $this->createUrl($current + 1) . '">下一页</a>';
        return $html;
    
    }

    public function pageCallback($obj)
    {
        $obj->pageCallback($this);
        return $this;
    
    }

    public function setRequest($request)
    {
        $this->request = $request;
    
    }

    public function setPageSize($size)
    {
        $this->pagesize = $size;
    
    }

    public function getPageSize()
    {
        return $this->pagesize;
    
    }

    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    
    }

    public function setPageVar($var)
    {
        $this->pageVar = $var;
    
    }

    public function getLimitArr()
    {
        return array($this->getLimit(),$this->getOffset());
    
    }

    public function applyLimit($condition)
    {
        $condition->limit = $this->getLimit();
        $condition->offset = $this->getOffset();
    
    }

    public function getOffset()
    {
        return ($this->getCurrentPage() - 1) * $this->getPageSize();
    
    }

    public function getLimit()
    {
        return $this->getPageSize();
    
    }

    public function getPageCount()
    {
        return ceil($this->count / $this->pagesize);
    
    }

    public function getCurrentPage()
    {
        $page = "";
        if($this->request instanceof mini_http_request) {
            $page = $this->request->get($this->pageVar);
        } else {
            $page = isset($_GET[$this->pageVar]) ? $_GET[$this->pageVar] : 0;
        }
        if(empty($page) || ! is_numeric($page) || $page <= 1) {
            $page = 1;
        }
        $this->currentpage = $page;
        return $page;
    
    }
}