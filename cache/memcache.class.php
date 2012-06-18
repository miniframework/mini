<?php
class mini_cache_memcache extends mini_base_cache
{
    public $servers = array();
    private $serverconfigs = array();
    private $cache = null;
    public $useMemcached = false;
    public $host ="127.0.0.1";
    public $port=11211;
    public $persistent=true;
    public $weight=1;
    public $timeout=15;
    public $status=true;
    public function perinit()
    {
    	$servers = $this->getServers();
    	$cache=$this->getMemCache();
    	foreach($servers as $k => $server)
    	{
    	  if($this->useMemcached)
					$cache->addServer($server->host,$server->port,$server->weight);
				else
					$cache->addServer($server->host,$server->port,$server->persistent,$server->weight,$server->timeout,$server->status);
    	}
    }
    public function setServers($config)
    {
    	foreach($config as $c)
    		$this->serverconfigs[]=new mini_cache_memcache_config($c);
    }
    public function getServers()
    {
        if(empty($this->servers))
        {
           $this->serverconfigs[] = new mini_cache_memcache_config();
           return $this->serverconfigs;
        }
        if(empty($this->servers[0]))
        {
            $this->servers = array($this->servers);
        }
        foreach($this->servers as $server)
        {
            $this->serverconfigs[] = new mini_cache_memcache_config($server);
        }
        return $this->serverconfigs;
    }
    public function getMemCache()
    {
    	if($this->cache!==null)
    		return $this->cache;
    	else
    		return $this->cache=$this->useMemcached ? new Memcached : new Memcache;
    }
    protected function getValue($key)
    {
    	return $this->cache->get($key);
    }
    protected function setValue($key,$value,$expire)
    {
    	if($expire>0)
    		$expire+=time();
    	else
    		$expire=0;
    
    	return $this->useMemcached ? $this->cache->set($key,$value,$expire) : $this->cache->set($key,$value,0,$expire);
    }
    protected function addValue($key,$value,$expire)
    {
    	if($expire>0)
    		$expire+=time();
    	else
    		$expire=0;
    
    	return $this->useMemcached ? $this->cache->add($key,$value,$expire) : $this->cache->add($key,$value,0,$expire);
    }
    protected function deleteValue($key)
    {
    	return $this->cache->delete($key, 0);
    }
    protected function flushValues()
    {
    	return $this->cache->flush();
    }
}
class mini_cache_memcache_config
{
    public $host = "127.0.0.1";
    public $port=11211;
    public $persistent=true;
    public $weight=1;
    public $timeout=15;
    public $retryInterval=15;
    public $status=true;
    public function __construct($config=array())
    {
    	if(is_array($config))
    	{
    		foreach($config as $key=>$value)
    			$this->$key=$value;
    	}
    }
}