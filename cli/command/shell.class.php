<?php
class mini_cli_command_shell extends mini_cli_command
{
   
    public function run($args)
    {
        
        if(!isset($args[0]))
           throw new Exception("must  input runPath:  the path to the runPath ");
        if(($configPath=realpath($args[0]."/config/config.xml"))===false || !is_file($configPath))
            throw new Exception(" config-file: the path to the configuration file  not exists");
        $runPath = $args[0];
        mini::run($runPath, $configPath);
        
        
        
        echo <<<EOD
Mini Interactive Tool v1.1 (based on mini v1.0)
Please type 'help' for help. Type 'exit' to quit.
EOD;
       $this->runShell();
    }
    public function runShell()
    {
        $console = mini::createComponent("mini_cli_console");
        while(($line=$this->prompt("\n>>"))!==false)
        {
        	$line=trim($line);
        	if($line==='exit')
        		return;
        	$args =preg_split('/[\s,]+/',rtrim($line,';'),-1,PREG_SPLIT_NO_EMPTY);
        	if(isset($args[0]))
        	{
        	    if(in_array($args[0], $console->commands))
        	    {
        	        try {
        	        $console->run($args);
        	        }
        	        catch (Exception $e)
        	        {
        	           echo  "\r\nERROR:\r\n\t".$e->getMessage();
        	        }
        	    }
        	    else
        	               echo <<<EOD
USAGE
  model show all
  model show table
  model create all
  model create table
  curd  create model
  exit
DESCRIPTION
  This command generates an model at the specified location.

EOD;
        	}
        }
    }
    public function prompt($message)
    {
    	if(extension_loaded('readline'))
    	{
    		$input = readline($message.' ');
    		readline_add_history($input);
    		return $input;
    	}
    	else
    	{
    		echo $message.' ';
    		return trim(fgets(STDIN));
    	}
    }
	public function help()
	{
		return <<<EOD
USAGE
  mini shell [run-path]

DESCRIPTION
  This command allows you to interact with a Web application
  on the command line. It also provides tools to automatically
  generate new controllers, views and data models.

  It is recommended that you execute this command under
  the directory that contains the entry script file of
  the Web application.

PARAMETERS
 * run-path: optional, the path to
   the configuration file for the Web application. 

EOD;
	}
    
}