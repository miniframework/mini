<?php
class mini_web_helper extends mini_base_component
{
    public $view = null;
    public function init(){}
    public function initView($view)
    {
    	$this->view = $view;
    }
}