<?php
class mini_db_schema
{
    /**
     * get builder handle
     * 
     * @var mini_db_builder
     */
    private $builder = "";
    /**
     * table name
     * 
     * @var string
     */
    public $table = "";
    /**
     * table primarykey
     * 
     * @var string
     */
    public $primaryKey = "";
    /**
     * table columns
     * 
     * @var array
     */
    public $columns = array();

    /**
     * get builder
     * 
     * @return mini_db_builder
     */
    public function getBuilder()
    {
        if(empty($this->table) || empty($this->columns))
            mini::e("get builder before schema must set table and columns");
        if(empty($this->builder))
            $this->builder = mini_db_builder::getHandle();
        return $this->builder;
    
    }

    /**
     * set primarykey
     * 
     * @param string $primaryKey
     * @return mini_db_schema
     */
    public function primaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    
    }

    /**
     * set table name
     * 
     * @param sting $table
     * @return mini_db_schema
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    
    }

    /**
     * get table name
     * 
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    
    }

    /**
     * get table columns
     * 
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    
    }

    /**
     * set table columns
     * 
     * @param array $columns
     * @return mini_db_schema
     */
    public function setColumns($columns = array())
    {
        $this->columns = $columns;
        return $this;
    
    }

    /**
     * get table columns
     * 
     * @return array:
     */
    public function getColumnNames()
    {
        return $this->columns;
    
    }

    /**
     * quote table name
     * 
     * @param string $name
     * @return string
     */
    public function quoteTableName($name)
    {
        if(strpos($name ,'.') === false)
            return $this->quoteSimpleTableName($name);
        $parts = explode('.' ,$name);
        foreach($parts as $i => $part)
            $parts[$i] = $this->quoteSimpleTableName($part);
        return implode('.' ,$parts);
    
    }

    /**
     * Quotes a simple table name for use in a query.
     * A simple table name does not schema prefix.
     * 
     * @param string $name table name
     * @return string the properly quoted table name
     */
    public function quoteSimpleTableName($name)
    {
        return '`' . $name . '`';
    
    }

    /**
     * quote table columns name
     * 
     * @param string $name
     * @return string
     */
    public function quoteColumnName($name)
    {
        if(($pos = strrpos($name ,'.')) !== false) {
            $prefix = $this->quoteTableName(substr($name ,0 ,$pos)) . '.';
            $name = substr($name ,$pos + 1);
        } else
            $prefix = '';
        return $prefix . ($name === '*' ? $name : $this->quoteSimpleColumnName($name));
    
    }

    /**
     * Quotes a simple column name for use in a query.
     * A simple column name does not contain prefix.
     * 
     * @param string $name column name
     * @return string the properly quoted column name
     */
    public function quoteSimpleColumnName($name)
    {
        return '`' . $name . '`';
    
    }
}
?>