<?php
class mini_cache_file extends mini_base_cache
{
    public  $path = '';
    public  $directoryLevel=1;
    private $gcProbability=100;
    private $gced=false;
    private $cacheFileSuffix = ".bin";
    public function perinit()
    {
    	if(empty($this->path))
    		$this->path=mini::getRunPath()."/caches";
    	if(!is_dir($this->path))
    		mkdir($this->path,0777,true);
    }
    protected function getValue($key)
    {
    	$cacheFile=$this->getCacheFile($key);
    	if(($time=@filemtime($cacheFile))>time())
    		return @file_get_contents($cacheFile);
    	else if($time>0)
    		@unlink($cacheFile);
    	return false;
    }
    protected function setValue($key,$value,$expire)
    {
    	if(!$this->gced && mt_rand(0,1000000)<$this->gcProbability)
    	{
    		$this->gc();
    		$this->gced=true;
    	}
    
    	if($expire<=0)
    		$expire=31536000; // 1 year
    	$expire+=time();
    
    	$cacheFile=$this->getCacheFile($key);
    	if($this->directoryLevel>0 && !file_exists(dirname($cacheFile)))
    		mkdir(dirname($cacheFile),0777,true);
    	if(file_put_contents($cacheFile,$value,LOCK_EX)!==false)
    	{
    		chmod($cacheFile,0777);
    		return @touch($cacheFile,$expire);
    	}
    	else
    		return false;
    }
    protected function addValue($key,$value,$expire)
    {
    	$cacheFile=$this->getCacheFile($key);
    	if(@filemtime($cacheFile)>time())
    		return false;
    	return $this->setValue($key,$value,$expire);
    }
    protected function deleteValue($key)
    {
    	$cacheFile=$this->getCacheFile($key);
    	return @unlink($cacheFile);
    }
    protected function flushValues()
    {
    	$this->gc(false);
    	return true;
    }
    public function gc($expiredOnly=true,$path=null)
    {
    	if($path===null)
    		$path=$this->path;
    	if(($handle=opendir($path))===false)
    		return;
    	while(($file=readdir($handle))!==false)
    	{
    		if($file[0]==='.')
    			continue;
    		$fullPath=$path.DIRECTORY_SEPARATOR.$file;
    		if(is_dir($fullPath))
    			$this->gc($expiredOnly,$fullPath);
    		else if($expiredOnly && @filemtime($fullPath)<time() || !$expiredOnly)
    			@unlink($fullPath);
    	}
    	closedir($handle);
    }
    protected function getCacheFile($key)
    {
    	if($this->directoryLevel>0)
    	{
    		$base=$this->path;
    		for($i=0;$i<$this->directoryLevel;++$i)
    		{
    			if(($prefix=substr($key,$i+$i,2))!==false)
    				$base.=DIRECTORY_SEPARATOR.$prefix;
    		}
    		return $base.DIRECTORY_SEPARATOR.$key.$this->cacheFileSuffix;
    	}
    	else
    		return $this->path.DIRECTORY_SEPARATOR.$key.$this->cacheFileSuffix;
    }
}