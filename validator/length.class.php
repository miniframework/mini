<?php
class mini_validator_length extends mini_base_validator
{
    
    	public $max;
    	public $min;
    	public $is;
    	public $tooShort;
    	public $tooLong;
    	public $allowEmpty=true;
    	public $encoding;
    	protected function validateAttribute($object,$attribute, $value)
    	{
    	    
    		if($this->allowEmpty && $this->isEmpty($value))
    			return;
    
    		if(function_exists('mb_strlen') && $this->encoding!==false)
    			$length=mb_strlen($value, $this->encoding ? $this->encoding : 'utf-8');
    		else
    			$length=strlen($value);
    
    		if($this->min!==null && $length<$this->min)
    		{
    			$message=$this->tooShort!==null?$this->tooShort:'{attribute} is too short (minimum is {min} characters).';
    			$this->addError($object,$attribute,$message,array('{min}'=>$this->min));
    		}
    		if($this->max!==null && $length>$this->max)
    		{
    			$message=$this->tooLong!==null?$this->tooLong:'{attribute} is too long (maximum is {max} characters).';
    			$this->addError($object,$attribute,$message,array('{max}'=>$this->max));
    		}
    		if($this->is!==null && $length!==$this->is)
    		{
    			$message=$this->message!==null?$this->message:'{attribute} is of the wrong length (should be {length} characters).';
    			$this->addError($object,$attribute,$message,array('{length}'=>$this->is));
    		}
    	}
}