<?php
class mini_struct_map implements IteratorAggregate,ArrayAccess,Countable
{
    private $data=array();
    
    private $readOnly=false;
    
    public function __construct($data=null,$readOnly=false)
    {
    	if($data!==null)
    		$this->copyFrom($data);
    	$this->setReadOnly($readOnly);
    }
    
    public function getIterator()
    {
    	return new ArrayIterator($this->data);
    }
    /**
     * @return boolean whether this map is read-only or not. Defaults to false.
     */
    public function getReadOnly()
    {
    	return $this->readOnly;
    }
    public function get($key)
    {
    	if(isset($this->data[$key]))
    		return $this->data[$key];
    	else
    		return null;
    }
    public function isEmpty()
    {
        return empty($this->data);
    }
    /**
     * @param boolean $value whether this list is read-only or not
     */
    protected function setReadOnly($value)
    {
    	$this->readOnly=$value;
    }
    public function copyFrom($data)
    {
    	if(is_array($data) || $data instanceof Traversable)
    	{
    		if($this->getCount()>0)
    			$this->clear();
    		if($data instanceof mini_struct_map)
    			$data=$data->data;
    		foreach($data as $key=>$value)
    			$this->add($key,$value);
    	}
    	else if($data!==null)
    		mini::t('Map data must be an array or an object implementing Traversable.');
    }
    public function count()
    {
    	return $this->getCount();
    }
    public function getCount()
    {
    	return count($this->data);
    }
    public function add($key,$value)
    {
    	if(!$this->readOnly)
    	{
    		if($key===null)
    			$this->data[]=$value;
    		else
    			$this->data[$key]=$value;
    	}
    	else
    		mini::t('The map is read only.');
    }
    public function remove($key)
    {
    	if(!$this->readOnly)
    	{
    		if(isset($this->data[$key]))
    		{
    			$value=$this->data[$key];
    			unset($this->data[$key]);
    			return $value;
    		}
    		else
    		{
    			// it is possible the value is null, which is not detected by isset
    			unset($this->data[$key]);
    			return null;
    		}
    	}
    	else
    		mini::t('The map is read only.');
    }
    public function clear()
    {
    	foreach(array_keys($this->data) as $key)
    		$this->remove($key);
    }
    public function exists($key)
    {
    	return isset($this->data[$key]) || array_key_exists($key,$this->data);
    }
 
    /**
     * @return array the list of items in array
     */
    public function toArray()
    {
    	return $this->data;
    }
    /**
     * Returns whether there is an element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to check on
     * @return boolean
     */
    public function offsetExists($offset)
    {
    	return $this->exists($offset);
    }
    
    /**
     * Returns the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to retrieve element.
     * @return mixed the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset)
    {
    	return $this->get($offset);
    }
    
    /**
     * Sets the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to set element
     * @param mixed $item the element value
     */
    public function offsetSet($offset,$item)
    {
    	$this->add($offset,$item);
    }
    
    /**
     * Unsets the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset)
    {
    	$this->remove($offset);
    }
}

class mini_struct_mapiterator implements Iterator
{
    /**
     * @var array the data to be iterated through
     */
    private $data;
    /**
     * @var array list of keys in the map
     */
    private $keys;
    /**
     * @var mixed current key
     */
    private $key;
    
    /**
     * Constructor.
     * @param array $data the data to be iterated through
     */
    public function __construct(&$data)
    {
    	$this->data=&$data;
    	$this->keys=array_keys($data);
    	$this->key=reset($this->keys);
    }
    
    /**
     * Rewinds internal array pointer.
     * This method is required by the interface Iterator.
     */
    public function rewind()
    {
    	$this->key=reset($this->keys);
    }
    
    /**
     * Returns the key of the current array element.
     * This method is required by the interface Iterator.
     * @return mixed the key of the current array element
     */
    public function key()
    {
    	return $this->key;
    }
    
    /**
     * Returns the current array element.
     * This method is required by the interface Iterator.
     * @return mixed the current array element
     */
    public function current()
    {
    	return $this->data[$this->key];
    }
    
    /**
     * Moves the internal pointer to the next array element.
     * This method is required by the interface Iterator.
     */
    public function next()
    {
    	$this->key=next($this->keys);
    }
    
    /**
     * Returns whether there is an element at current position.
     * This method is required by the interface Iterator.
     * @return boolean
     */
    public function valid()
    {
    	return $this->key!==false;
    }
}