<?php
class mini_boot_config
{
    /**
     * config xml file path
     *
     * @var string
     */
    private $path = "";
    /**
     * simplexml_load_file xml data
     *
     * @var SimpleXMLElement
     */
    private $config = null;
    private static $handle = null;
    /**
     * tmp __get value
     *
     * @var SimpleXMLElement
     */
    private $_data = null;

    /**
     * mini mini_boot_config class
     */
    private function __construct($path)
    {
        if(! file_exists($path)) {
            mini::e("config file {path} not exists!" ,array('{path}'=>$path));
        } else {
            $this->path = $path;
            libxml_use_internal_errors(true);
            $xmlobj = simplexml_load_file($this->path);
            if(empty($xmlobj))
            {
                mini::e("config not load xml file {file}" ,array('{file}'=>$this->path));
            }
            $this->config = (array)$this->toArray($xmlobj);
            
            if(! $this->config) {
                $error = libxml_get_errors();
                libxml_clear_errors();
                foreach($error as $k => $v) {
                    $message .= "path:" . $path . "\tline:" . $v->line . "\tcolumn" . $v->column . "\tmessage:" . $v->message;
                }
                mini::e("config xml libxml_get_errors: {message} " ,array('{message}'=>$message));
            }
        }
    
    }

    /**
     * get mini_boot_loader
     *
     * @return mini_boot_config
     */
    public static function getHandle($path)
    {
        if(self::$handle == null) {
            self::$handle = new self($path);
        }
        return self::$handle;
    
    }

    /**
     * get config xml object
     *
     * @return SimpleXMLElement
     */
    public function getConfig()
    {
        return $this->config;
    
    }

    /**
     * get config node magic method
     *
     * @param string $name
     * @return array
     */
    public function __get($name)
    {
        if(isset($this->config[$name]))
        {
            return $this->config[$name];
        }
        else
            return null;
    }

    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    
    }

    /**
     * Convert xml to array
     * from /Zend/Config/Xml.php , Zend_Config_Xml->_toArray modify
     *
     * @param SimpleXMLElement $xmlObject
     * @return array
     */
    private function toArray($xmlObject = null)
    {
        $config = array();
        $nsAttributes = $xmlObject->attributes();
        
        // Search for parent node values
        if(count($xmlObject->attributes()) > 0) {
            foreach($xmlObject->attributes() as $key => $value) {
                if($key === 'extends') {
                    continue;
                }
                
                $value = (string) $value;
                
                if(array_key_exists($key ,$config)) {
                    if(! is_array($config[$key])) {
                        $config[$key] = array($config[$key]);
                    }
                    
                    $config[$key][] = $value;
                } else {
                    $config[$key] = $value;
                }
            }
        }
        
        if(count($xmlObject->children()) > 0) {
            foreach($xmlObject->children() as $key => $value) {
                if(count($value->children()) > 0) {
                    $value = $this->toArray($value);
                } else if(count($value->attributes()) > 0) {
                    $attributes = $value->attributes();
                    if(isset($attributes['value'])) {
                        $value = (string) $attributes['value'];
                    } else {
                        $value = $this->toArray($value);
                    }
                } else {
                    $value = (string) $value;
                }
                
                if(array_key_exists($key ,$config)) {
                    if(! is_array($config[$key]) || ! array_key_exists(0 ,$config[$key])) {
                        $config[$key] = array($config[$key]);
                    }
                    
                    $config[$key][] = $value;
                } else {
                    $config[$key] = $value;
                }
            }
        } else if(! isset($xmlObject['extends']) && ! isset($nsAttributes['extends']) && (count($config) === 0)) {
            // Object has no children nor attributes and doesn't use the extends
            // attribute: it's a string
            $config = (string) $xmlObject;
        }
        return $config;
    
    }
}
?>