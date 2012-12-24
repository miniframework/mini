<?php
class mini_base_debug
{
    const DEBUG_CLOSE = 0;
    const DEBUG_SHOW = 1;
    const DEBUG_FILE = 2;
    public static $n = 0;

    public static function trace($message = '', $category = '', $file = '', $line = '')
    {
        $debug = mini::getConfig()->debug;
        if(isset($debug['type'])) {
            self::$n ++;
            
            $request = mini::createComponent('mini_http_request');
            $uri = '';
            if(isset($debug['uri']) && $debug['uri'] == 1) {
                $uri = $request->getRequestUri();
            }
            $mem = '';
            if(isset($debug['memory']) && $debug['memory'] == 1) {
                $mem = memory_get_peak_usage();
            }
            $cate = '';
            if(isset($debug['category']) && $debug['category'] == '*') {
                $message = self::$n . ".{" . $message . "}{" . $category . "}{" . $mem . "}{" . $uri . "}{" . $file . "}{" . $line . "}\r\n";
            } else if(isset($debug['category']) && $debug['category'] == $message) {
                $message = self::$n . ".{" . $message . "}{" . $category . "}{" . $mem . "}{" . $uri . "}{" . $file . "}{" . $line . "}\r\n";
            } else {
            }
            
            $type = $debug['type'];
            if($type == self::DEBUG_SHOW) {
                echo $message;
            } else if($type == self::DEBUG_FILE) {
                $logger = mini::getLogger();
                $logger->log($message ,'trace' ,'debug.trace');
                $logger->flush();
            } else if(isset($debug['key']) && isset($debug['value'])) {
                $key = $debug['key'];
                $value = $debug['value'];
                
                if($request->get($key) == $value) {
                    echo $message;
                }
            }
        }
    
    }
}