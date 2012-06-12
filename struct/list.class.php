<?php
class mini_struct_list implements IteratorAggregate,ArrayAccess,Countable
{
    private $data=array();
    /**
     * @var integer number of items
    */
    private $count=0;
    /**
     * @var boolean whether this list is read-only
     */
    private $readOnly=false;
    
    /**
     * Constructor.
     * Initializes the list with an array or an iterable object.
     * @param array $data the initial data. Default is null, meaning no initialization.
     * @param boolean $readOnly whether the list is read-only
     * @throws CException If data is not null and neither an array nor an iterator.
     */
    public function __construct($data=null,$readOnly=false)
    {
    	if($data!==null)
    		$this->copyFrom($data);
    	$this->setReadOnly($readOnly);
    }
    /**
     * @return boolean whether this list is read-only or not. Defaults to false.
     */
    public function getReadOnly()
    {
    	return $this->readOnly;
    }
    /**
     * @param boolean $value whether this list is read-only or not
     */
    protected function setReadOnly($value)
    {
    	$this->readOnly=$value;
    }
    
    /**
     * Returns an iterator for traversing the items in the list.
     * This method is required by the interface IteratorAggregate.
     * @return Iterator an iterator for traversing the items in the list.
     */
    public function getIterator()
    {
    	return new ArrayIterator($this->data);
    }
    
    /**
     * Returns the number of items in the list.
     * This method is required by Countable interface.
     * @return integer number of items in the list.
     */
    public function count()
    {
    	return $this->getCount();
    }
    
    /**
     * Returns the number of items in the list.
     * @return integer the number of items in the list
     */
    public function getCount()
    {
    	return $this->count;
    }
    public function get($index)
    {
    	if(isset($this->data[$index]))
    		return $this->data[$index];
    	else if($index>=0 && $index<$this->count) // in case the value is null
    		return $this->data[$index];
    	else
    		mini::e('List index "{index}" is out of bound',array('{index}'=>$index));
    }
    
    public function add($item)
    {
    	$this->insert($this->count,$item);
    	return $this->count-1;
    }
    public function insert($index,$item)
    {
    	if(!$this->readOnly)
    	{
    		if($index===$this->count)
    			$this->data[$this->count++]=$item;
    		else if($index>=0 && $index<$this->count)
    		{
    			array_splice($this->data,$index,0,array($item));
    			$this->count++;
    		}
    		else
    			mini::e('List index "{index}" is out of bound.',array('{index}'=>$index));
    	}
    	else
    		mini::e('The list is read only.');
    }
    public function remove($item)
    {
    	if(($index=$this->indexOf($item))>=0)
    	{
    		$this->removeAt($index);
    		return $index;
    	}
    	else
    		return false;
    }
    public function removeAt($index)
    {
    	if(!$this->readOnly)
    	{
    		if($index>=0 && $index<$this->count)
    		{
    			$this->count--;
    			if($index===$this->count)
    				return array_pop($this->data);
    			else
    			{
    				$item=$this->data[$index];
    				array_splice($this->data,$index,1);
    				return $item;
    			}
    		}
    		else
    			mini::e('List index "{index}" is out of bound.',array('{index}'=>$index));
    	}
    	else
    		mini::e('The list is read only.');
    }
    public function clear()
    {
    	for($i=$this->count-1;$i>=0;--$i)
    		$this->removeAt($i);
    }
    public function exists($item)
    {
    	return $this->indexOf($item)>=0;
    }
    public function indexOf($item)
    {
    	if(($index=array_search($item,$this->data,true))!==false)
    		return $index;
    	else
    		return -1;
    }
    
    /**
     * @return array the list of items in array
     */
    public function toArray()
    {
    	return $this->data;
    }
    public function copyFrom($data)
    {
    	if(is_array($data) || ($data instanceof Traversable))
    	{
    		if($this->count>0)
    			$this->clear();
    		if($data instanceof mini_struct_list)
    			$data=$data->data;
    		foreach($data as $item)
    			$this->add($item);
    	}
    	else if($data!==null)
    		mini::e('List data must be an array or an object implementing Traversable.');
    }
    /**
     * Returns whether there is an item at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to check on
     * @return boolean
     */
    public function offsetExists($offset)
    {
    	return ($offset>=0 && $offset<$this->count);
    }
    
    /**
     * Returns the item at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to retrieve item.
     * @return mixed the item at the offset
     * @throws CException if the offset is invalid
     */
    public function offsetGet($offset)
    {
    	return $this->get($offset);
    }
    
    /**
     * Sets the item at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to set item
     * @param mixed $item the item value
     */
    public function offsetSet($offset,$item)
    {
    	if($offset===null || $offset===$this->count)
    		$this->insert($this->count,$item);
    	else
    	{
    		$this->removeAt($offset);
    		$this->insert($offset,$item);
    	}
    }
    
    /**
     * Unsets the item at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to unset item
     */
    public function offsetUnset($offset)
    {
    	$this->removeAt($offset);
    }
}

class mini_struct_listiterator implements Iterator
{
	/**
	 * @var array the data to be iterated through
	 */
	private $data;
	/**
	 * @var integer index of the current item
	 */
	private $index;
	/**
	 * @var integer count of the data items
	 */
	private $count;

	/**
	 * Constructor.
	 * @param array $data the data to be iterated through
	 */
	public function __construct(&$data)
	{
		$this->data=&$data;
		$this->index=0;
		$this->count=count($this->data);
	}

	/**
	 * Rewinds internal array pointer.
	 * This method is required by the interface Iterator.
	 */
	public function rewind()
	{
		$this->index=0;
	}

	/**
	 * Returns the key of the current array item.
	 * This method is required by the interface Iterator.
	 * @return integer the key of the current array item
	 */
	public function key()
	{
		return $this->index;
	}

	/**
	 * Returns the current array item.
	 * This method is required by the interface Iterator.
	 * @return mixed the current array item
	 */
	public function current()
	{
		return $this->data[$this->index];
	}

	/**
	 * Moves the internal pointer to the next array item.
	 * This method is required by the interface Iterator.
	 */
	public function next()
	{
		$this->index++;
	}

	/**
	 * Returns whether there is an item at current position.
	 * This method is required by the interface Iterator.
	 * @return boolean
	 */
	public function valid()
	{
		return $this->index<$this->count;
	}
}
// $list = new mini_struct_list(array(1,2,3,4));

// $list->add("55");
// $list->remove("55");
// $list->insert(2, "ttt");
// foreach($list as $k => $v)
// {
//     echo $v;
// }