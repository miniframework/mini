<?php
class mini_cli_console extends mini_base_component
{
    public $commands=array("help","app","model","shell");
    
    private $scriptName;
    public function init()
    {
        
    }
    public function run($args = array())
    {
        if(empty($args))
        {
            if(!isset($_SERVER['argv'])) 
    			throw new Exception("This script must be run from the command line");
            $args = $_SERVER['argv'];
            $this->scriptName=$args[0];
            array_shift($args);
        }
        
        if(isset($args[0]))
        {
        	$name=$args[0];
        	array_shift($args);
        }
        else
        	$name='help';
        
        try{
            
            $command =  $this->createCommand($name);
       
        } catch(Exception $e)
        {
            $command = $this->createCommand("help");
            throw new Exception("\r\nERROR:\r\n\t".$e->getMessage());
        }
        
        try{
            $command->run($args);
        } catch(Exception $e)
        {
            
            throw new Exception($command->help()."\r\nERROR:\r\n\t".$e->getMessage());
        }
    }
    public function createCommand($name)
    {
        $commandClass = "mini_cli_command_$name";
        return mini::createComponent($commandClass, $this);
    }
}