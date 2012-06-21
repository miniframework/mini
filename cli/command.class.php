<?php
abstract  class mini_cli_command extends mini_base_component
{
    public $console = null;
    public function __construct($console)
    {
        $this->console = $console;
    }
    public function init()
    {
        
    }
    abstract function help();
    /**
     * Asks user to confirm by typing y or n.
     *
     * @param string $message
     *            to echo out before waiting for user input
     * @return bool if user confirmed
     *
     * @since 1.1.9
     */
    public function confirm($message)
    {
    	echo $message . ' [yes|no] ';
    	return ! strncasecmp(trim(fgets(STDIN)) ,'y' ,1);
    }
}