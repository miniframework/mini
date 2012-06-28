<?php
class mini_validator_required extends mini_base_validator
{
	public $requiredValue;
	public $strict=false;
	protected function validateAttribute($object,$attribute, $value)
	{
		if($this->requiredValue!==null)
		{
			if(!$this->strict && $value!=$this->requiredValue || $this->strict && $value!==$this->requiredValue)
			{
				$message=$this->message!==null?$this->message:'{attribute} must be {value}.';
				$this->addError($object,$attribute,$message,array('{value}'=>$this->requiredValue));
			}
		}
		else if(empty($value))
		{
			$message=$this->message!==null?$this->message:'{attribute} cannot be blank.';
			$this->addError($object,$attribute,$message);
		}
	}

}
