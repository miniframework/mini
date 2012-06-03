<?php
/**
 * auto load class.
 * @author wzb
 * @data 2012-05-07 add 
 *
 */
class mini_boot_loader
{
    /**
     * namespace is array  key is namepace, value is include_path
     *
     * @var array
     */
    private $namespace = array();
    private static $handle = null;
    /**
     * mini autoloader  class
     *
     */
    private function __construct()
    {
        $mini_path = dirname(__FILE__) . "/../../";
        $this->namespace['mini'] = $mini_path;
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
        $this->namespace[$namespace] = $path;
    }
    /**
     * register callback getClass
     *
     */
    public function loader()
    {
        spl_autoload_register(array($this, "getClass"));
    }
    /**
     * spl_autoload_register callback function
     *
     * @param string $classname
     * @throws exception  if class file not exists
     */
    private function getClass($classname)
    {
        $classname_arr = explode("_", $classname);
        if(array_key_exists($classname_arr[0], $this->namespace)) {
            $classfile = $this->getClassfile($classname_arr);
            if(file_exists($this->namespace['mini'] . $classfile)) {
                include_once $this->namespace['mini'] . $classfile;
            }
        }
    
    }
    /**
     * 
     * from classname change to class file path example mini_db_mysql->mini/db/mysql.class.php
     * 
     * @param array $classname
     * @return string
     */
    private function getClassfile($classname_arr)
    {
        $count = count($classname_arr);
        if($count == 1) {
            return $classname_arr[0] . ".class.php";
        } else {
	    $classfile = '';
            for($i = 0; $i < $count - 1; $i ++) {
                $classfile .= $classname_arr[$i] . "/";
            }
            $classfile .= $classname_arr[$count - 1] . ".class.php";
            return $classfile;
        }
    
    }
}
?>
