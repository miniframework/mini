<?php
class mini_http_request extends mini_base_component
{
    private $params = array();
    private $scriptUrl = null;
    private $baseUrl;
    private $securePort;
    private $hostInfo;
    private $pathInfo;
    private $port;
    private $requestUri;

    public function __construct()
    {
    }

    public function init()
    {
        $this->normalize();
    
    }

    protected function normalize()
    {
        if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            if(isset($_GET))
                $_GET = $this->stripSlashes($_GET);
            if(isset($_POST))
                $_POST = $this->stripSlashes($_POST);
            if(isset($_REQUEST))
                $_REQUEST = $this->stripSlashes($_REQUEST);
            if(isset($_COOKIE))
                $_COOKIE = $this->stripSlashes($_COOKIE);
        }
    
    }

    public function stripSlashes(&$data)
    {
        return is_array($data) ? array_map(array($this,'stripSlashes') ,$data) : stripslashes($data);
    
    }

    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    
    }

    public function __get($key)
    {
        return $this->get($key);
    
    }

    public function get($key)
    {
        switch (true) {
            case isset($this->params[$key]) :
                return $this->params[$key];
            case isset($_GET[$key]) :
                return $_GET[$key];
            case isset($_POST[$key]) :
                return $_POST[$key];
        }
        return null;
    
    }

    public function getPathInfo()
    {
        if($this->pathInfo === null) {
            $pathInfo = $this->getRequestUri();
            
            if(($pos = strpos($pathInfo ,'?')) !== false)
                $pathInfo = substr($pathInfo ,0 ,$pos);
            
            $pathInfo = $this->decodePathInfo($pathInfo);
            
            $scriptUrl = $this->getScriptUrl();
            $baseUrl = $this->getBaseUrl();
            if(strpos($pathInfo ,$scriptUrl) === 0)
                $pathInfo = substr($pathInfo ,strlen($scriptUrl));
            else if($baseUrl === '' || strpos($pathInfo ,$baseUrl) === 0)
                $pathInfo = substr($pathInfo ,strlen($baseUrl));
            else if(strpos($_SERVER['PHP_SELF'] ,$scriptUrl) === 0)
                $pathInfo = substr($_SERVER['PHP_SELF'] ,strlen($scriptUrl));
            else
                mini::e('HttpRequest is unable to determine the path info of the request.');
            
            $this->pathInfo = trim($pathInfo ,'/');
        }
        return $this->pathInfo;
    
    }

    protected function decodePathInfo($pathInfo)
    {
        $pathInfo = urldecode($pathInfo);
        
        // is it UTF-8?
        // http://w3.org/International/questions/qa-forms-utf-8.html
        if(preg_match('%^(?:
		   [\x09\x0A\x0D\x20-\x7E]            # ASCII
		 | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		 | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
		 | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		 | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
		 | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
		 | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		 | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
		)*$%xs' ,$pathInfo)) {
            return $pathInfo;
        } else {
            return utf8_encode($pathInfo);
        }
    
    }

    public function getRequestUri()
    {
        if($this->requestUri === null) {
            if(isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
                $this->requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
            else if(isset($_SERVER['REQUEST_URI'])) {
                $this->requestUri = $_SERVER['REQUEST_URI'];
                if(! empty($_SERVER['HTTP_HOST'])) {
                    if(strpos($this->requestUri ,$_SERVER['HTTP_HOST']) !== false)
                        $this->requestUri = preg_replace('/^\w+:\/\/[^\/]+/' ,'' ,$this->requestUri);
                } else
                    $this->requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i' ,'' ,$this->requestUri);
            } else if(isset($_SERVER['ORIG_PATH_INFO']))             // IIS 5.0 CGI
            {
                $this->requestUri = $_SERVER['ORIG_PATH_INFO'];
                if(! empty($_SERVER['QUERY_STRING']))
                    $this->requestUri .= '?' . $_SERVER['QUERY_STRING'];
            } else
                mini::e('HttpRequest is unable to determine the request URI.');
        }
        
        return $this->requestUri;
    
    }

    public function getBaseUrl($absolute = false)
    {
        if($this->baseUrl === null)
            $this->baseUrl = rtrim(dirname($this->getScriptUrl()) ,'\\/');
        return $absolute ? $this->getHostInfo() . $this->baseUrl : $this->baseUrl;
    
    }

    public function getScriptUrl()
    {
        if($this->scriptUrl === null) {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if(basename($_SERVER['SCRIPT_NAME']) === $scriptName)
                $this->scriptUrl = $_SERVER['SCRIPT_NAME'];
            else if(basename($_SERVER['PHP_SELF']) === $scriptName)
                $this->scriptUrl = $_SERVER['PHP_SELF'];
            else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName)
                $this->scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            else if(($pos = strpos($_SERVER['PHP_SELF'] ,'/' . $scriptName)) !== false)
                $this->scriptUrl = substr($_SERVER['SCRIPT_NAME'] ,0 ,$pos) . '/' . $scriptName;
            else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'] ,$_SERVER['DOCUMENT_ROOT']) === 0)
                $this->scriptUrl = str_replace('\\' ,'/' ,str_replace($_SERVER['DOCUMENT_ROOT'] ,'' ,$_SERVER['SCRIPT_FILENAME']));
            else
                mini::e('HttpRequest is unable to determine the entry script URL.');
        }
        return $this->scriptUrl;
    
    }

    public function getSecurePort()
    {
        if($this->securePort === null)
            $this->securePort = $this->getIsSecureConnection() && isset($_SERVER['SERVERport']) ? (int) $_SERVER['SERVERport'] : 443;
        return $this->securePort;
    
    }

    public function getIsSecureConnection()
    {
        return isset($_SERVER['HTTPS']) && ! strcasecmp($_SERVER['HTTPS'] ,'on');
    
    }

    public function getHostInfo($schema = '')
    {
        if($this->hostInfo === null) {
            if($secure = $this->getIsSecureConnection())
                $http = 'https';
            else
                $http = 'http';
            if(isset($_SERVER['HTTP_HOST']))
                $this->hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            else {
                $this->hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getPort();
                if(($port !== 80 && ! $secure) || ($port !== 443 && $secure))
                    $this->hostInfo .= ':' . $port;
            }
        }
        if($schema !== '') {
            $secure = $this->getIsSecureConnection();
            if($secure && $schema === 'https' || ! $secure && $schema === 'http')
                return $this->hostInfo;
            
            $port = $schema === 'https' ? $this->getSecurePort() : $this->getPort();
            if($port !== 80 && $schema === 'http' || $port !== 443 && $schema === 'https')
                $port = ':' . $port;
            else
                $port = '';
            
            $pos = strpos($this->hostInfo ,':');
            return $schema . substr($this->hostInfo ,$pos ,strcspn($this->hostInfo ,':' ,$pos + 1) + 1) . $port;
        } else
            return $this->hostInfo;
    
    }

    public function getPort()
    {
        if($this->port === null)
            $this->port = ! $this->getIsSecureConnection() && isset($_SERVER['SERVERport']) ? (int) $_SERVER['SERVERport'] : 80;
        return $this->port;
    
    }
}
?>