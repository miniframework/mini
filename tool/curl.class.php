<?php
class mini_tool_curl
{
    
    public $timeout = 10;
    public $encoding = "gzip";
    public $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)";
    public $error = '';
    public $errorno = 0;
    public $infocode = array();
    public $retryhistory = 0;
    public $proxy = '';
    public $cookiefile = '';
    public function __construct($params = array())
    {
        foreach($params as $key => $param)
            $this->$key = $param;
    }
    public function getData($url, $header = array(), $retry=3, $ispost=false, $post=array(), $charset='UTF-8', $isdebug=true)
    {
    	for($i = 0; $i<=$retry ; $i++)
    	{
    	    if(!$ispost)
    	    {
    	        $data = $this->get($url, $header);
    	    } else {
    	        $data = $this->post($url,$post, $header);
    	    }
    	    
        	if($this->errorno == 28 || $this->error || $this->infocode['http_code'] != '200')
        	{
        	    $this->retryhistory = $i;
        	    if($isdebug)
        	    echo "<----retry $i.>\r\n";
        	}
        	else
        	{
        	    break;
        	}
    	}
    
    	if($this->error || $this->infocode['http_code'] != '200')
    	{
    	    if($isdebug)
			echo "curl get url:$url content error.".$this->error."\r\n";
    	}
    	
    	$content_type = $this->infocode['content_type'];
		preg_match('/charset=(.*)?/', $content_type, $match);
		$charset = strtoupper($charset);
		if(!empty($match[1]) && $match[1] != $charset)
		{
		    $data = mb_convert_encoding($data, $charset, $match[1]);
		}
    	return $data;
	}
	public  function getHeader($url,$header=array(),$ispost=false,$post=array())
	{  
	    if(!$ispost)
	    {
	        $data = $this->get($url, $header,true);
	    } else {
	    	$data = $this->post($url, $post, $header,true);
	    }
	}
    public  function get($url, $header=array(),$nobody=false)
    {
    	$ch = curl_init();
    	
    	curl_setopt($ch,CURLOPT_URL, $url);
    	if($nobody)
    		curl_setopt($ch, CURLOPT_NOBODY, 1);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_REFERER, $url);
    	if(!empty($this->proxy)) {
    	    curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
    	}
    	if (!empty($header)) {
    		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	}
    	if(!empty($this->cookiefile)) {
    	    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
    	    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);
    	}
    	curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    	curl_setopt($ch, CURLOPT_ENCODING, $this->encoding);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    	curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
    	
    	$data = curl_exec($ch);
    	if(curl_errno($ch))
    	{
    	    $this->errorno = curl_errno($ch);
    	    $this->error = "curl_error:".curl_errno($ch)." message:".curl_error($ch);
    	}
    	$this->infocode = curl_getinfo($ch);
    	curl_close($ch);
    	return $data;
    }
    public  function post($url, $post, $header = array(),$nobody=false)
    {
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    	
    	if(!empty($this->proxy)) {
    		curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
    	}
    	if (!empty($header)) {
    		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	}
    	if(!empty($this->cookiefile)) {
    		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
    		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);
    	}
    	curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    	curl_setopt($ch, CURLOPT_ENCODING, $this->encoding);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    	curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
    	if($nobody)
    		curl_setopt($ch, CURLOPT_NOBODY, 1);
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