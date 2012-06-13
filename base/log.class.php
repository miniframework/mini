<?php
class mini_base_log extends mini_base_component
{
    /**
     * log levels
     * 
     * @var string
     */
    public $levels = '';
    /**
     * log categories
     * 
     * @var string
     */
    public $categories = '';

    /**
     * user config
     * 
     * @param mini_boot_config $config
     */
    public function __construct($config)
    {
        if(isset($config['levels']) && ! empty($config['levels'])) {
            $this->levels = $config['levels'];
        }
        if(isset($config['categories']) && ! empty($config['categories'])) {
            $this->categories = $config['categories'];
        }
        $this->logParams($config);
    
    }

    public function init()
    {
    }

    /**
     * process log
     * 
     * @param mini_base_log $logger
     */
    public function processLogs($logger)
    {
        $logs = $logger->getLogs($this->levels ,$this->categories);
        if(! empty($logs)) {
            $this->process($logs);
        }
    
    }

    /**
     * formate log message
     * 
     * @param string $message
     * @param string $level
     * @param string $category
     * @param string $time
     * @return string
     */
    protected function formatLogMessage($message, $level, $category, $time)
    {
        return @date('Y/m/d H:i:s' ,$time) . " [$level] [$category] $message\n";
    
    }
}