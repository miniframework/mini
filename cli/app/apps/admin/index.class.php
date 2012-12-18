<?php
class IndexController extends mini_web_controller
{
    public function doIndex()
    {
       
    }
    public function doHeader()
    {
    	
    }
    public function doMain()
    {
        $serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
        $this->view->serverinfo = $serverinfo;
        $db = mini_db_connection::getHandle();
        $row= $db->find("SELECT VERSION() as dbversion");
        $this->view->dbversion = $row['dbversion'];
        $magic_quote_gpc = get_magic_quotes_gpc() ? 'On' : 'Off';
        $allow_url_fopen = ini_get('allow_url_fopen') ? 'On' : 'Off';
        $fileupload = @ini_get('file_uploads') ? ini_get('upload_max_filesize'):0;
        
        $this->view->fileupload = $fileupload;
        $this->view->magic_quote_gpc = $magic_quote_gpc;
        $this->view->allow_url_fopen = $allow_url_fopen;
    }
    public function doMenu()
    {
    	
    }
}