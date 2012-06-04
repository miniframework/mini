<?php
class mini_base_log extends mini_base_component
{
    public $levels='';
    public $categories = '';
    public function __construct($config)
    {
        if(isset($config['levels']) && !empty($config['levels']))
        {
        	$this->levels = $config['levels'];
        }
        if(isset($config['categories']) && !empty($config['categories']))
        {
        	$this->categories = $config['categories'];
        }
        $this->logParams($config);
    }
    public function init()
    {
        
      
    }
    public function processLogs($logger)
    {
        $logs = $logger->getLogs($this->levels,$this->categories);
        if(!empty($logs))
        {
            $this->process($logs);
        }
    }
    protected function formatLogMessage($message,$level,$category,$time)
    {
    	return @date('Y/m/d H:i:s',$time)." [$level] [$category] $message\n";
    }
}