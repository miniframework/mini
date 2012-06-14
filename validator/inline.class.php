<?php
class mini_validator_inline extends mini_base_validator
{
    public $method;
    public $params;

    protected function validateAttribute($object, $attribute, $value)
    {
        $method = $this->method;
        $object->$method($attribute ,$value ,$this->params);
    
    }
}