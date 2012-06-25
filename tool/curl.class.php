<?php
class mini_tool_curl
{
    
    public $timeout = 10;
    public $encoding = "gzip";
    public $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)";
    public $error = '';
    public $infocode = array();
    public function __construct($params = array())
    {
        foreach($params as $key => $param)
            $this->$key = $param;
    }
    public  function get($url, $header=array())
    {
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_REFERER, $url);
    	if (!empty($header)) {
    		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	}
    	 
    	curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    	curl_setopt($ch, CURLOPT_ENCODING, $this->encoding);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    	curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
    	$data = curl_exec($ch);
    	if(curl_errno($ch))
    	{
    	    $this->error = "curl_error:".curl_errno($ch)." message:".curl_error($ch);
    	}
    	$this->infocode = curl_getinfo($ch);
    	curl_close($ch);
    	return $data;
    }
    public  function post($url, $post)
    {
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    	if (!empty($header)) {
    		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	}
    	curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    	curl_setopt($ch, CURLOPT_ENCODING, $this->encoding);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    	curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
    	$data = curl_exec($ch);
    	if(curl_errno($ch))
    	{
    		$this->error = "curl_error:".curl_errno($ch)." message:".curl_error($ch);
    	}
    	$this->infocode = curl_getinfo($ch);
    	curl_close($ch);
    	return $data;
    }
}