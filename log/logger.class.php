<?php
class mini_log_logger
{
    const LEVEL_TRACE='trace';
    const LEVEL_WARNING='warning';
    const LEVEL_ERROR='error';
    const LEVEL_INFO='info';
    const LEVEL_PROFILE='profile';
    public $autoFlush=10000;
    /**
     * @var boolean this property will be passed as the parameter to {@link flush()} when it is
     * called in {@link log()} due to the limit of {@link autoFlush} being reached.
     * By default, this property is false, meaning the filtered messages are still kept in the memory
     * by each log route after calling {@link flush()}. If this is true, the filtered messages
     * will be written to the actual medium each time {@link flush()} is called within {@link log()}.
     * @since 1.1.8
     */
    /**
     * @var array log messages
     */
    private $logs=array();
    /**
     * @var integer number of log messages
    */
    private $logCount=0;
    /**
     * @var array log levels for filtering (used when filtering)
     */
    private $levels;
    /**
     * @var array log categories for filtering (used when filtering)
     */
    private $categories;
    /**
     * @var array the profiling results (category, token => time in seconds)
     */
    private $timings;
    /**
     * @var boolean if we are processing the log or still accepting new log messages
     * @since 1.1.9
     */
    private $processing = false;
    
    private static $handle = null;
    private $manager = null;
    private function __construct()
    {
        $this->init();
    }
    public function init()
    {
        $this->manager = new mini_log_manager();
    }
    
    public static function getHandle()
    {
        if(self::$handle == null)
            self::$handle = new self();
        return self::$handle;
    }
    
    public function log($message,$level='info',$category='app')
    {
    	$this->logs[]=array($message,$level,$category,microtime(true));
    	$this->logCount++;
    	if($this->autoFlush>0 && $this->logCount>=$this->autoFlush && !$this->processing)
    	{
    		$this->processing=true;
    		$this->flush();
    		$this->processing=false;
    	}
    	return $this;
    }
    public function getLogs($levels='',$categories='')
    {
    	$this->levels=preg_split('/[\s,]+/',strtolower($levels),-1,PREG_SPLIT_NO_EMPTY);
    	$this->categories=preg_split('/[\s,]+/',strtolower($categories),-1,PREG_SPLIT_NO_EMPTY);
    	if(empty($levels) && empty($categories))
    		return $this->logs;
    	else if(empty($levels))
    		return array_values(array_filter(array_filter($this->logs,array($this,'filterByCategory'))));
    	else if(empty($categories))
    		return array_values(array_filter(array_filter($this->logs,array($this,'filterByLevel'))));
    	else
    	{
    		$ret=array_values(array_filter(array_filter($this->logs,array($this,'filterByLevel'))));
    		return array_values(array_filter(array_filter($ret,array($this,'filterByCategory'))));
    	}
    }
    
    /**
     * Filter function used by {@link getLogs}
     * @param array $value element to be filtered
     * @return array valid log, false if not.
     */
    private function filterByCategory($value)
    {
    	foreach($this->categories as $category)
    	{
    		$cat=strtolower($value[2]);
    		if($cat===$category || (($c=rtrim($category,'.*'))!==$category && strpos($cat,$c)===0))
    			return $value;
    	}
    	return false;
    }
    
    /**
     * Filter function used by {@link getLogs}
     * @param array $value element to be filtered
     * @return array valid log, false if not.
     */
    private function filterByLevel($value)
    {
    	return in_array(strtolower($value[1]),$this->levels)?$value:false;
    }
    public function getMemoryUsage()
    {
        
        
    	if(function_exists('memory_get_usage'))
    		return memory_get_usage();
    	else
    	{
    		$output=array();
    		if(strncmp(PHP_OS,'WIN',3)===0)
    		{
    			exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST',$output);
    			return isset($output[5])?preg_replace('/[\D]/','',$output[5])*1024 : 0;
    		}
    		else
    		{
    			$pid=getmypid();
    			exec("ps -eo%mem,rss,pid | grep $pid", $output);
    			$output=explode("  ",$output[0]);
    			return isset($output[1]) ? $output[1]*1024 : 0;
    		}
    	}
    }
    public function flush()
    {
		$logs = $this->manager->getLogs();
		foreach($logs as $log)
		{
		    $log->processLogs($this);
		}
		$this->logs=array();
		$this->logCount=0;
    }
}