<?php
class mini_validator_email extends mini_base_validator
{
    /**
     */
    public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
    /**
     *
     * @var string the regular expression used to validate email addresses with
     * the name part.
     * This property is used only when {@link allowName} is true.
     * @see allowName
     */
    public $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';
    /**
     *
     * @var boolean whether to allow name in the email address . Defaults to
     * false.
     * @see fullPattern
     */
    public $allowName = false;
    /**
     *
     * @var boolean whether to check the MX record for the email address.
     * Defaults to false. To enable it, you need to make sure the PHP function
     * 'checkdnsrr'
     * exists in your PHP installation.
     */
    public $checkMX = false;
    /**
     *
     * @var boolean whether to check port 25 for the email address.
     * Defaults to false.
     */
    public $checkPort = false;
    /**
     *
     * @var boolean whether the attribute value can be null or empty. Defaults
     * to true,
     * meaning that if the attribute is empty, it is considered valid.
     */
    public $allowEmpty = true;

    protected function validateAttribute($object, $attribute, $value)
    {
        if($this->allowEmpty && $this->isEmpty($value))
            return;
        if(! $this->validateValue($value)) {
            $message = $this->message !== null ? $this->message : '{attribute} is not a valid email address.';
            $this->addError($object ,$attribute ,$message);
        }
    
    }

    public function validateValue($value)
    {
        // make sure string length is limited to avoid DOS attacks
        $valid = is_string($value) && strlen($value) <= 254 && (preg_match($this->pattern ,$value) || $this->allowName && preg_match($this->fullPattern ,$value));
        if($valid)
            $domain = rtrim(substr($value ,strpos($value ,'@') + 1) ,'>');
        if($valid && $this->checkMX && function_exists('checkdnsrr'))
            $valid = checkdnsrr($domain ,'MX');
        if($valid && $this->checkPort && function_exists('fsockopen'))
            $valid = fsockopen($domain ,25) !== false;
        return $valid;
    
    }
}
