<?php
abstract class mini_base_validator
{
    public static $builtInValidators = array('email','length');
    public $attributes;
    public $message;
    public $skipOnError = false;
    public $on;

    public static function createValidator($name, $object, $attributes, $params = array())
    {
        if(is_string($attributes))
            $attributes = preg_split('/[\s,]+/' ,$attributes ,- 1 ,PREG_SPLIT_NO_EMPTY);
        if(isset($params['on'])) {
            if(is_array($params['on']))
                $on = $params['on'];
            else
                $on = preg_split('/[\s,]+/' ,$params['on'] ,- 1 ,PREG_SPLIT_NO_EMPTY);
        } else
            $on = array();
        
        if(method_exists($object ,$name)) {
            $validator = new mini_validator_inline();
            $validator->attributes = $attributes;
            $validator->method = $name;
            $validator->params = $params;
            if(isset($params['skipOnError']))
                $validator->skipOnError = $params['skipOnError'];
        } else {
            $params['attributes'] = $attributes;
            if(in_array($name ,self::$builtInValidators))
                $className = "mini_validator_" . $name;
            else
                $className = $name;
            if(! class_exists($className))
                mini::e("class {class} not exists" ,array('{class}'=>$className));
            $validator = new $className();
            foreach($params as $name => $value)
                $validator->$name = $value;
        }
        
        $validator->on = empty($on) ? array() : array_combine($on ,$on);
        
        return $validator;
    
    }
    public function applyTo($on)
    {
    	return empty($this->on) || isset($this->on[$on]);
    }
    public function validate($object, $attributes)
    {
        foreach($this->attributes as $attr) {
            if(array_key_exists($attr ,$attributes)) {
                if(! $this->skipOnError || ! $object->hasErrors($attr))
                    $this->validateAttribute($object ,$attr ,$attributes[$attr]);
            }
        }
    
    }

    protected function addError($object, $attribute, $message, $params = array())
    {
        $params['{attribute}'] = $attribute;
        $object->addError($attribute ,strtr($message ,$params));
    
    }

    protected function isEmpty($value, $trim = false)
    {
        return $value === null || $value === array() || $value === '' || $trim && is_scalar($value) && trim($value) === '';
    
    }
}