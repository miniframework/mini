<?php
class mini_cache_manager 
{
    private $caches = null;
    private static $handle = null;
    private function __construct()
    {
        $this->caches = new mini_struct_map();
        $this->init();
    }
    public function init()
    {
        $cache = mini::getConfig()->cache;
    }
    public static function getHandle()
    {
        if(self::$handle == null)
            self::$handle = new self();
        return self::$handle;
    }
    public function addCache($name, $caching)
    {
       $cache = mini::createComponent($caching);
       $cache->setParams($caching);
       $cache->perinit();
       $this->caches->add($name,$cache);
       return $cache;
    }
    public function getCache($name)
    {
        if($this->caches->exists($name))
        {
            return $this->caches->get($name);
        }
        else 
        {
            $cache = mini::getConfig()->cache;
            if(!empty($cache) && array_key_exists($name, $cache))
            {
                   return $this->addCache($name, $cache[$name]);
            }
            else 
            {
                mini::e("cache name {name} not exists",array("{name}"=>$name));
            }
            
            
        }
    }
}