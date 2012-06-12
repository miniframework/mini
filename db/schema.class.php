<?php
class mini_db_schema
{
    private $builder = "";
    
    public $table = "";
    public $primaryKey = "";
    public $columns = array();
    public function getBuilder()
    {
        if(empty($this->table) || empty($this->columns))
            mini::e("get builder before schema must set table and columns");
        if(empty($this->builder))
             $this->builder = mini_db_builder::getHandle();
            return  $this->builder;
    }
    public function primaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }
    public function setTable($table)
    {
       $this->table = $table;
       return $this;
    }
    public function getTable()
    {
        return $this->table;
    }
    public function getColumns()
    {
        return $this->columns;
    }
    public function setColumns($columns = array())
    {
        $this->columns = $columns;
         return $this;
    }
    public function quoteTableName($name)
	{
		if(strpos($name,'.')===false)
			return $this->quoteSimpleTableName($name);
		$parts=explode('.',$name);
		foreach($parts as $i=>$part)
			$parts[$i]=$this->quoteSimpleTableName($part);
		return implode('.',$parts);

	}

	/**
	 * Quotes a simple table name for use in a query.
	 * A simple table name does not schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 * @since 1.1.6
	 */
	public function quoteSimpleTableName($name)
	{
		return '`'.$name.'`';
	}
	public function quoteColumnName($name)
	{
		if(($pos=strrpos($name,'.'))!==false)
		{
			$prefix=$this->quoteTableName(substr($name,0,$pos)).'.';
			$name=substr($name,$pos+1);
		}
		else
			$prefix='';
		return $prefix . ($name==='*' ? $name : $this->quoteSimpleColumnName($name));
	}

	/**
	 * Quotes a simple column name for use in a query.
	 * A simple column name does not contain prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 * @since 1.1.6
	 */
	public function quoteSimpleColumnName($name)
	{
		return '`'.$name.'`';
	}
	
	public function getColumnNames()
	{
	    return $this->columns;
	}
}
?>