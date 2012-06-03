<?php
class mini_cli_console extends mini_base_component
{
    public $commands=array("help","app","model","curd");
    
    private $scriptName;
    public function init()
    {
        
    }
    public function run()
    {
        if(!isset($_SERVER['argv'])) 
			throw new Exception("This script must be run from the command line");
        $args = $_SERVER['argv'];
        $this->scriptName=$args[0];
        array_shift($args);
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
        }
        $command->run($args);
    }
    public function createCommand($name)
    {
        $commandClass = "mini_cli_command_$name";
        return mini::createComponent($commandClass, $this);
    }
}