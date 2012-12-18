<?php
class indexController extends mini_web_controller
{
	public function doIndex()
	{
	    echo "this is mini error page.";
	    $this->closeRender();
	}
}