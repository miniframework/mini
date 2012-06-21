<?php
/**
 * Defines the mini framework installation path.
 */
defined('MINI_PATH') || define('MINI_PATH' ,dirname(__FILE__));
/**
 * Defines the exception handling should be enabled,defaults to true.
 */
defined('MINI_EXCEPTION_HANDLER') || define('MINI_EXCEPTION_HANDLER' ,true);
/**
 * Defines the error handling should be enabled, defaults to true
 */
defined('MINI_ERROR_HANDLER') || define('MINI_ERROR_HANDLER' ,true);
/**
 * Defines the debug is opened or closed, defaults to close false.
 */
defined('MINI_DEBUG') || define('MINI_DEBUG' ,true);
class mini
{
    /**
     * a mini handle
     *
     * @var mini
     */
    public static $handle = null;
    /**
     * application run root path.
     *
     * @var string
     */
    private static $runPath = '';
    /**
     * user config
     *
     * @var mini_boot_config
     */
    private static $config = null;
    /**
     * auto loader
     *
     * @var mini_boot_loader
     */
    private static $loader = null;
    /**
     * event handle
     *
     * @var mini_base_event
     */
    private static $event = null;

    /**
     * construct init error handle,exception handle,init autoload, loader user
     * config file,init event
     *
     * @param string $runPath set app run path.
     * @param string $config set user config path.
     *
     */
    private function __construct($runPath, $config)
    {
        
        $this->perLoad();
        $this->initHandle();
        if(! file_exists($runPath))
            throw new Exception("$runPath not exists!");
        self::$runPath = realpath($runPath);
        self::$loader = $this->initLoader();
        self::$config = $this->initConfig($config);
        self::$event = $this->initEvent();
        $this->loadDefault();
       
    
    }
    public function perLoad()
    {
        $logPath = MINI_PATH."/log";
        $basePath = MINI_PATH."/base";
        $perload = array(
                $basePath."/component.class.php",
                $basePath."/log.class.php",
                $logPath."/manager.class.php",
                $logPath."/logger.class.php",
                $logPath."/file.class.php");
        
        foreach($perload as $perfile)
        {
            include_once $perfile;
        }
    }
    public static function loadDefault()
    {
        $modelPath  = self::getRunPath()."/models";
        self::$loader->addNamespace('',$modelPath);
        
    }
    /**
     * loader mini class
     *
     * @return mini_boot_loader
     */
    private function initLoader()
    {
        include_once realpath(dirname(__FILE__) . "/boot/loader.class.php");
        return mini_boot_loader::getHandle()->loader(MINI_PATH);
    
    }

    /**
     * set error and exception handle, if MINI_EXCEPTION_HANDLER or
     * MINI_EXCEPTION_HANDLER is true.
     */
    private function initHandle()
    {
        if(MINI_EXCEPTION_HANDLER)
            set_exception_handler(array($this,'handleException'));
        if(MINI_ERROR_HANDLER)
            set_error_handler(array($this,'handleError') ,error_reporting());
    
    }

    /**
     * creat a mini_base_event handle.
     *
     * @return mini_base_event
     */
    private function initEvent()
    {
        return new mini_base_event();
    
    }

    /**
     * set_exception_handler callback function
     *
     * @param Exception $exception
     */
    public function handleException($exception)
    {
        $category = 'exception.' . get_class($exception);
        restore_error_handler();
        restore_exception_handler();
        $message = $exception->__toString();
        if(isset($_SERVER['REQUEST_URI']))
            $message .= "\nREQUEST_URI=" . $_SERVER['REQUEST_URI'];
        if(isset($_SERVER['HTTP_REFERER']))
            $message .= "\nHTTP_REFERER=" . $_SERVER['HTTP_REFERER'];
        $message .= "\n---";
        $this->displayException($exception);
        try {
            self::getLogger()->log($message ,mini_log_logger::LEVEL_ERROR ,$category);
            self::end();
        } catch (Exception $e) {
            if(MINI_DEBUG) {
                echo $e->getMessage();
            }
        }
    
    }

    /**
     * Displays the uncaught PHP exception.
     *
     * @param Exception $exception
     */
    public function displayException($exception)
    {
        if(MINI_DEBUG) {
            echo '<h1>' . get_class($exception) . "</h1>\n";
            echo '<p>' . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')</p>';
            echo '<pre>' . $exception->getTraceAsString() . '</pre>';
        }
    
    }

    /**
     * set_error_handler callback function.
     *
     * @param integer $code the level of the error raised
     * @param string $message the error message
     * @param string $file the filename that the error was raised in
     * @param integer $line the line number the error was raised at
     */
    public function handleError($code, $message, $file, $line)
    {
        if($code & error_reporting()) {
            // disable error capturing to avoid recursive errors
            restore_error_handler();
            restore_exception_handler();
            $log = "$message ($file:$line)\nStack trace:\n";
            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if(count($trace) > 3)
                $trace = array_slice($trace ,3);
            foreach($trace as $i => $t) {
                if(! isset($t['file']))
                    $t['file'] = 'unknown';
                if(! isset($t['line']))
                    $t['line'] = 0;
                if(! isset($t['function']))
                    $t['function'] = 'unknown';
                $log .= "#$i {$t['file']}({$t['line']}): ";
                if(isset($t['object']) && is_object($t['object']))
                    $log .= get_class($t['object']) . '->';
                $log .= "{$t['function']}()\n";
            }
            if(isset($_SERVER['REQUEST_URI']))
                $log .= 'REQUEST_URI=' . $_SERVER['REQUEST_URI'];
            $this->displayError($code ,$message ,$file ,$line);
            try {
                self::getLogger()->log($log ,mini_log_logger::LEVEL_ERROR ,'php');
                self::end();
            } catch (Exception $e) {
                if(MINI_DEBUG) {
                    echo $e->getMessage();
                }
            }
        }
    
    }

    /**
     * Displays the captured PHP error.
     * This method displays the error in HTML.
     *
     * @param integer $code error code
     * @param string $message error message
     * @param string $file error file
     * @param string $line error line
     */
    public function displayError($code, $message, $file, $line)
    {
        if(MINI_DEBUG) {
            echo "<h1>PHP Error [$code]</h1>\n";
            echo "<p>$message ($file:$line)</p>\n";
            echo '<pre>';
            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if(count($trace) > 3)
                $trace = array_slice($trace ,3);
            foreach($trace as $i => $t) {
                if(! isset($t['file']))
                    $t['file'] = 'unknown';
                if(! isset($t['line']))
                    $t['line'] = 0;
                if(! isset($t['function']))
                    $t['function'] = 'unknown';
                echo "#$i {$t['file']}({$t['line']}): ";
                if(isset($t['object']) && is_object($t['object']))
                    echo get_class($t['object']) . '->';
                echo "{$t['function']}()\n";
            }
            echo '</pre>';
        }
    
    }

    /**
     * application will be terminated by this method.
     */
    public static function end()
    {
        self::getLogger()->flush();
        exit();
    
    }

    /**
     * create a mini handle
     *
     * @param string $runPath application run root path
     * @param string $config user config file path
     * @return mini
     */
    public static function run($runPath, $config)
    {
        if(self::$handle == null)
            self::$handle = new self($runPath ,$config);
        return self::$handle;
    
    }

    public static function console()
    {
        include_once realpath(dirname(__FILE__) . "/boot/loader.class.php");
        mini_boot_loader::getHandle()->loader(MINI_PATH);
        $console = self::createComponent("mini_cli_console");
        try {
            $console->run();
        } catch (Exception $e) {
            echo $e->getMessage()."\r\n";
        }
    
    }

    /**
     * get mini_base_event handle.
     *
     * @return mini_base_event
     */
    public static function getEvent()
    {
        return self::$event;
    
    }

    /**
     * create a mini_log_logger handle.
     *
     * @return mini_log_logger
     */
    public static function getLogger()
    {
        return mini_log_logger::getHandle();
    
    }

    /**
     * start load user config create ~autoload file, registry global vars.
     *
     * @param string $autofile set autoload file
     * @return mini
     */
    public function assembly($autofile)
    {
        // set user autoload file
        $loader = self::$config->loader;
        if(! file_exists($autofile)) {
            $autodirs = $loader['dirs'];
            if(!empty($autodirs))
            {
                if(is_array($autodirs)) {
                    foreach($autodirs as $dir) {
                        $dirs[] = self::$runPath . '/' . $dir;
                    }
                } else if(! empty($autodirs)) {
                    $dirs[] = self::$runPath . '/' . $autodirs;
                }
                $generator = new mini_tool_assembly($autofile ,$dirs);
                $generator->generate();
            }
        }
        if(is_readable($autofile)) {
            include_once $autofile;
            self::$loader->register("mini_autoload");
        }
        return $this;
    
    }

    /**
     * create a mini_boot_config handle
     *
     * @param string $path
     * @return mini_boot_config
     */
    public function initConfig($path)
    {
        return mini_boot_config::getHandle($path);
    
    }

    /**
     * start create application.
     */
    public function web()
    {
        mini_base_application::app()->process()->end();
    
    }

    /**
     * return application run path
     *
     * @return string
     */
    public static function getRunPath()
    {
        return self::$runPath;
    
    }

    /**
     * return mini_boot_config handle
     *
     * @return mini_boot_config
     */
    public static function getConfig()
    {
        return self::$config;
    
    }

    /**
     * throw mini_base_excetpion
     *
     * @param string $message exception message
     * @param array $params replace params key {xxx} in message sub string to
     * value
     * @throws mini_base_exception
     */
    public static function e($message, $params = array())
    {
        $message = $params !== array() ? strtr($message ,$params) : $message;
        throw new mini_base_exception($message);
    
    }

    /**
     * create object, class must extends mini_base_component{@link
     * mini_base_component}.
     *
     * @param array $class
     * @return object
     */
    public static function createComponent($class)
    {
        if(is_string($class)) {
            $type = $class;
        } else if(isset($class['class'])) {
            $type = $class['class'];
        } else {
            mini::e('Object configuration must be an array containing a "class" element.');
        }
        if(! class_exists($type)) {
            mini::e("class {class} not exists!" ,array('{class}'=>$type));
        }
        if(($n = func_num_args()) > 1) {
            $args = func_get_args();
            if($n === 2) {
                $object = new $type($args[1]);
            } else if($n === 3) {
                $object = new $type($args[1] ,$args[2]);
            } else if($n === 4) {
                $object = new $type($args[1] ,$args[2] ,$args[3]);
            } else {
                unset($args[0]);
                $class = new ReflectionClass($type);
                $object = call_user_func_array(array($class,'newInstance') ,$args);
            }
        } else {
            $object = new $type();
        }
        if(! method_exists($object ,"init"))
            mini::e("create component must exists method init!");
        $object->init();
        return $object;
    
    }
}
?>