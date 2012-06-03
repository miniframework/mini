<?php
class mini_cli_command_help extends mini_cli_command
{
   
    public function run($args)
    {
        
       $commands = $this->console->commands;
       if(isset($args[0]))
			$name=strtolower($args[0]);
       
		if(!isset($args[0]) || !in_array($name, $commands))
		{
			echo <<<EOD
At the prompt, you may enter a PHP statement or one of the following commands:

EOD;
			echo ' - '.implode("\n - ",$commands);
			echo <<<EOD


Type 'help <command-name>' for details about a command.


EOD;
		}
		else
			echo $this->console->createCommand($name)->help();
    }
    public function help()
    {
        	return <<<EOD
USAGE
  help [command-name]
        
DESCRIPTION
  Display the help information for the specified command.
  If the command name is not given, all commands will be listed.
        
PARAMETERS
 * command-name: optional, the name of the command to show help information.
        
EOD;
    }
    
}