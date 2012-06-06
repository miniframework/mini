<?php
class mini
{
    /**
     * mini handle
     *
     * @var mini
     */
    private static $handle = null;
    /**
     * project home path
     *
     * @var string
     */
    private $home = "";
    /**
     * set home path
     *
     * @param string $home            
     */
    private static $config = null;
    private static $runPath = '';
    private function __construct($home)
    {
        $this->home = realpath($home);
        self::$runPath = $this->home;
        $this->initLoader();
        $this->initHandle();
    }
    private function initLoader()
    {
        // autoload mini class
        include_once realpath(dirname(__FILE__) . "/boot/loader.class.php");
        mini_boot_loader::getHandle()->loader();
    }
    private function initHandle()
    {
        set_exception_handler(array($this,'handleException'));
        set_error_handler(array($this,'handleError'),error_reporting());
    }
    public function handleException($exception)
    {
        $category='exception.'.get_class($exception);
        restore_error_handler();
        restore_exception_handler();
        $message=$exception->__toString();
        if(isset($_SERVER['REQUEST_URI']))
        	$message.="\nREQUEST_URI=".$_SERVER['REQUEST_URI'];
        if(isset($_SERVER['HTTP_REFERER']))
        	$message.="\nHTTP_REFERER=".$_SERVER['HTTP_REFERER'];
        $message.="\n---";
        self::getLogger()->log($message, mini_log_logger::LEVEL_ERROR, $category);
        $this->displayException($exception);
        self::end();
        
       
    }
    public function displayException($exception)
    {
    		echo '<h1>'.get_class($exception)."</h1>\n";
    		echo '<p>'.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().')</p>';
    		echo '<pre>'.$exception->getTraceAsString().'</pre>';
    }
    public function handleError($code,$message,$file,$line)
    {
    	if($code & error_reporting())
    	{
    		// disable error capturing to avoid recursive errors
    		restore_error_handler();
    		restore_exception_handler();
    
    		$log="$message ($file:$line)\nStack trace:\n";
    		$trace=debug_backtrace();
    		// skip the first 3 stacks as they do not tell the error position
    		if(count($trace)>3)
    			$trace=array_slice($trace,3);
    		foreach($trace as $i=>$t)
    		{
    			if(!isset($t['file']))
    				$t['file']='unknown';
    			if(!isset($t['line']))
    				$t['line']=0;
    			if(!isset($t['function']))
    				$t['function']='unknown';
    			$log.="#$i {$t['file']}({$t['line']}): ";
    			if(isset($t['object']) && is_object($t['object']))
    				$log.=get_class($t['object']).'->';
    			$log.="{$t['function']}()\n";
    		}
    		if(isset($_SERVER['REQUEST_URI']))
    			$log.='REQUEST_URI='.$_SERVER['REQUEST_URI'];
    		self::getLogger()->log($log, mini_log_logger::LEVEL_ERROR, 'php');
          
            $this->displayError($code,$message,$file,$line);
            self::end();
    	}
    }
    public function displayError($code,$message,$file,$line)
	{
		
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message ($file:$line)</p>\n";
			echo '<pre>';

			$trace=debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if(count($trace)>3)
				$trace=array_slice($trace,3);
			foreach($trace as $i=>$t)
			{
				if(!isset($t['file']))
					$t['file']='unknown';
				if(!isset($t['line']))
					$t['line']=0;
				if(!isset($t['function']))
					$t['function']='unknown';
				echo "#$i {$t['file']}({$t['line']}): ";
				if(isset($t['object']) && is_object($t['object']))
					echo get_class($t['object']).'->';
				echo "{$t['function']}()\n";
			}

			echo '</pre>';
		
	}
    public static function end()
    {
        
        self::getLogger()->flush();
        exit;
    }
    /**
     * get mini handle
     *
     * @param string $home
     *            project home path
     * @return string
     */
    public static function run($home)
    {
        if(self::$handle == null)
            self::$handle = new self($home);
        return self::$handle;
    }
    public static function console()
    {
        include_once realpath(dirname(__FILE__) . "/boot/loader.class.php");
        mini_boot_loader::getHandle()->loader();
        $console = self::createComponent("mini_cli_console");
        try {
        $console->run();
        
        } catch(Exception $e)
        {
            self::getLogger()->log($message, 'cli', 'console')
            ->flush();
        }
    }
   
    public static function getLogger()
    {
        return  mini_log_logger::getHandle();
    }
    /**
     * start load user config create ~autoload file, registry global vars
     *
     * @param string $path            
     * @return mini
     */
    public function boot($path)
    {
        
        // add user config
        $config = mini_boot_config::getHandle($path);
        // set project home path
        $config->home = $this->home;
        // set user autoload file
        $autoload = $config->autoload;
        if(! file_exists($this->home . '/' . $autoload['file'])) {
            $autodirs = $autoload['dirs'];
            if(is_array($autodirs)) {
                foreach($autodirs as $dir) {
                    $dirs[] = $this->home . '/' . $dir;
                }
            } else if(! empty($autodirs)) {
                $dirs[] = $this->home . '/' . $autodirs;
            }
            
            $generator = new mini_tool_assembly($this->home . '/' . $autoload['file'] ,$dirs);
            $generator->generate();
        }
        include_once $this->home . '/' . $autoload['file'];
        spl_autoload_register("mini_autoload");
        // include user autoload file
        
        self::$config = $config;
        // register config
        return $this;
    }
    /**
     * start mvc
     */
    public function web()
    {
        mini_base_application::app()->process()->end();
    }
    public static function getRunPath()
    {
        return self::$runPath;
    }
    public static function getConfig()
    {
        return self::$config;
    }
    /**
     * create object, class must extends mini_base_component.
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
            throw new Exception('Object configuration must be an array containing a "class" element.');
        }
        if(!class_exists($type))
        {
            throw new Exception("$type class not exists!");
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
                // Note: ReflectionClass::newInstanceArgs() is available for PHP
                // 5.1.3+
                // $object=$class->newInstanceArgs($args);
                $object = call_user_func_array(array(
                        $class,
                        'newInstance' 
                ) ,$args);
            }
        } else {
            $object = new $type();
        }
        
        if(! method_exists($object ,"init"))
            throw new Exception("create component must exists method init!");
        $object->init();
        return $object;
    }
}
?>