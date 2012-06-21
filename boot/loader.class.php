<?php
class mini_boot_loader
{
    /**
     * namespace is array key is namepace, value is include_path
     *
     * @var array
     */
    private $namespace = array();
    private static $handle = null;
    private $registerCall = array();
    /**
     * mini autoloader class
     */
    private function __construct()
    {
    }

    /**
     * get mini_boot_loader
     *
     * @return mini_boot_loader
     */
    public static function getHandle()
    {
        if(self::$handle == null) {
            self::$handle = new self();
        }
        return self::$handle;
    
    }

    /**
     * add autoloader namespace
     *
     * @param string $namespace
     * @param string $path
     */
    public function addNamespace($namespace, $path)
    {
        if(empty($namespace))
        {
            $this->namespace[] = $path;
        }
        else {
            $this->namespace[$namespace] = $path;
        }
    
    }

    /**
     * register callback getClass
     */
    public function loader($path, $namespace = 'mini')
    {
        $this->namespace[$namespace] = $path;
        array_unshift($this->registerCall,array($this,"getClass"));
        spl_autoload_register(array($this,"getClass"));
        return $this;
    
    }

    public function register($callback)
    {
        spl_autoload_register($callback);
        if(!empty($this->registerCall))
            foreach($this->registerCall as $callback)
            {
                spl_autoload_unregister($callback);
                spl_autoload_register($callback);
            }
         array_unshift($this->registerCall,$callback);
    
    }

    /**
     * spl_autoload_register callback function
     *
     * @param string $classname
     * @throws exception if class file not exists
     */
    private function getClass($classname)
    {
        //xhprof show class_exists slow
      //  if(! class_exists($classname)) {
            $classname_arr = explode("_" ,$classname);
            
            if(array_key_exists($classname_arr[0] ,$this->namespace)) {
                $classfile = $this->getClassfile($classname_arr);
                if(file_exists($this->namespace['mini'] . '/' . $classfile)) {
                    include_once $this->namespace['mini'] . '/' . $classfile;
                } else {
                    mini::e("class {classname} not find class file {classfile}" ,array('{classname}'=>$classname,'{classfile}'=>$classfile));
                }
            } else {
                foreach($this->namespace as $namespace => $path)
                {
                    $classfile = $this->getClassfile($classname_arr, 0);
                    if(file_exists($path . '/' . $classfile)) {
                    	include_once $path . '/' . $classfile;
                    	
                    	return;
                    } 
                }
                mini::e("class {classname} not in namespace!" ,array('{classname}'=>$classname));
            }
       // }
    
    }

    /**
     * from classname change to class file path example
     * mini_db_mysql->mini/db/mysql.class.php
     *
     * @param array $classname
     * @return string
     */
    private function getClassfile($classname_arr,$root = 1)
    {
        $count = count($classname_arr);
        if($count == 1) {
            return $classname_arr[0] . ".class.php";
        } else {
            $classfile = '';
            for($i = $root; $i < $count - 1; $i ++) {
                $classfile .= $classname_arr[$i] . "/";
            }
            $classfile .= $classname_arr[$count - 1] . ".class.php";
            return $classfile;
        }
    
    }
}
?>