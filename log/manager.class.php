<?php
class mini_log_manager
{
    
    private $logs = null;
    public function __construct()
    {
        $this->logs = new mini_struct_map();
        $this->init();
    }
    public function init()
    {
        $config = mini::getConfig();
        
        $logger = $config->logger;
         
        if(!empty($logger))
        {
            if(!isset($logger['log'][0]) || !is_array($logger['log'][0]))
            {
                $logger['log'] = array($logger['log']);
            }
            foreach($logger['log'] as $log)
            {
                if(!class_exists($log['class']));
                {
                    $class =  mini::createComponent($log['class'], $log);
                    $this->logs->add($log['name'], $class);
                }
            }
        }
    }
    
    public function getLogs()
    {
        return $this->logs;
    }
    
}