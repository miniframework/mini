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
}