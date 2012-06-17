<?php
class mini_base_cache extends mini_base_component
{
    public $keyPrefix;
    public function init(){
        
        if($this->keyPrefix===null)
        	$this->keyPrefix= "cache";
    }
    public function setParams($params)
    {
        if(!empty($params))
            foreach($params as $key => $value)
                $this->$key = $value;
    }
    protected function generateUniqueKey($key)
    {
    	return md5($this->keyPrefix.$key);
    }
    public function get($id)
    {
    	if(($value=$this->getValue($this->generateUniqueKey($id)))!==false)
    	{
    		$data=unserialize($value);
    		if(!is_array($data))
    			return false;
    		return $data[0];
    	}
    	return false;
    }
    public function set($id,$value,$expire=0)
    {
    	$data=array($value);
    	return $this->setValue($this->generateUniqueKey($id),serialize($data),$expire);
    }
    public function add($id,$value,$expire=0)
    {
    	$data=array($value);
    	return $this->addValue($this->generateUniqueKey($id),serialize($data),$expire);
    }
    public function delete($id)
    {
    	return $this->deleteValue($this->generateUniqueKey($id));
    }
    public function flush()
    {
    	return $this->flushValues();
    }
}