<?php
class mini_db_condition
{
    const PARAM_PREFIX = ':mini';
    /**
     *
     * @var integer the global counter for anonymous binding parameters.
     * This counter is used for generating the name for the anonymous
     * parameters.
     */
    public static $paramCount = 0;
    /**
     *
     * @var mixed the columns being selected. This refers to the SELECT clause
     * in an SQL
     * statement. The property can be either a string (column names separated by
     * commas)
     * or an array of column names. Defaults to '*', meaning all columns.
     */
    public $select = '*';
    /**
     *
     * @var boolean whether to select distinct rows of data only. If this is set
     * true,
     * the SELECT clause would be changed to SELECT DISTINCT.
     */
    public $distinct = false;
    /**
     *
     * @var string query condition. This refers to the WHERE clause in an SQL
     * statement.
     * For example, <code>age>31 AND team=1</code>.
     */
    public $condition = '';
    /**
     *
     * @var array list of query parameter values indexed by parameter
     * placeholders.
     * For example, <code>array(':name'=>'Dan', ':age'=>31)</code>.
     */
    public $params = array();
    /**
     *
     * @var integer maximum number of records to be returned. If less than 0, it
     * means no limit.
     */
    public $limit = - 1;
    /**
     *
     * @var integer zero-based offset from where the records are to be returned.
     * If less than 0, it means starting from the beginning.
     */
    public $offset = - 1;
    /**
     *
     * @var string how to sort the query results. This refers to the ORDER BY
     * clause in an SQL statement.
     */
    public $order = '';
    /**
     *
     * @var string how to group the query results. This refers to the GROUP BY
     * clause in an SQL statement.
     * For example, <code>'projectID, teamID'</code>.
     */
    public $group = '';
    /**
     *
     * @var string how to join with other tables. This refers to the JOIN clause
     * in an SQL statement.
     * For example, <code>'LEFT JOIN users ON users.id=authorID'</code>.
     */
    public $join = '';
    /**
     *
     * @var string the condition to be applied with GROUP-BY clause.
     * For example, <code>'SUM(revenue)<50000'</code>.
     */
    public $having = '';
    public $alias;

    /**
     * Constructor.
     * 
     * @param array $data criteria initial property values (indexed by property
     * name)
     */
    public function __construct($data = array())
    {
        foreach($data as $name => $value)
            $this->$name = $value;
    
    }

    /**
     * Remaps criteria parameters on unserialize to prevent name collisions.
     */
    public function __wakeup()
    {
        $map = array();
        $params = array();
        foreach($this->params as $name => $value) {
            $newName = self::PARAM_PREFIX . self::$paramCount ++;
            $map[$name] = $newName;
            $params[$newName] = $value;
        }
        $this->condition = strtr($this->condition ,$map);
        $this->params = $params;
    
    }

    /**
     * Appends a condition to the existing {@link condition}.
     * The new condition and the existing condition will be concatenated via the
     * specified operator
     * which defaults to 'AND'.
     * The new condition can also be an array. In this case, all elements in the
     * array
     * will be concatenated together via the operator.
     * This method handles the case when the existing condition is empty.
     * After calling this method, the {@link condition} property will be
     * modified.
     * 
     * @param mixed $condition the new condition. It can be either a string or
     * an array of strings.
     * @param string $operator the operator to join different conditions.
     * Defaults to 'AND'.
     */
    public function addCondition($condition, $operator = 'AND')
    {
        if(is_array($condition)) {
            if($condition === array())
                return $this;
            $condition = '(' . implode(') ' . $operator . ' (' ,$condition) . ')';
        }
        if($this->condition === '')
            $this->condition = $condition;
        else
            $this->condition = '(' . $this->condition . ') ' . $operator . ' (' . $condition . ')';
        return $this;
    
    }

    /**
     * Appends a search condition to the existing {@link condition}.
     * The search condition and the existing condition will be concatenated via
     * the specified operator
     * which defaults to 'AND'.
     * The search condition is generated using the SQL LIKE operator with the
     * given column name and
     * search keyword.
     * 
     * @param string $column the column name (or a valid SQL expression)
     * @param string $keyword the search keyword. This interpretation of the
     * keyword is affected by the next parameter.
     * @param boolean $escape whether the keyword should be escaped if it
     * contains characters % or _.
     * When this parameter is true (default), the special characters % (matches
     * 0 or more characters)
     * and _ (matches a single character) will be escaped, and the keyword will
     * be surrounded with a %
     * character on both ends. When this parameter is false, the keyword will be
     * directly used for
     * matching without any change.
     * @param string $operator the operator used to concatenate the new
     * condition with the existing one.
     * Defaults to 'AND'.
     * @param string $like the LIKE operator. Defaults to 'LIKE'. You may also
     * set this to be 'NOT LIKE'.
     */
    public function addSearchCondition($column, $keyword, $escape = true, $operator = 'AND', $like = 'LIKE')
    {
        if($keyword == '')
            return $this;
        if($escape)
            $keyword = '%' . strtr($keyword ,array('%'=>'\%','_'=>'\_','\\'=>'\\\\')) . '%';
        $condition = $column . " $like " . self::PARAM_PREFIX . self::$paramCount;
        $this->params[self::PARAM_PREFIX . self::$paramCount ++] = $keyword;
        return $this->addCondition($condition ,$operator);
    
    }

    /**
     * Appends an IN condition to the existing {@link condition}.
     * The IN condition and the existing condition will be concatenated via the
     * specified operator
     * which defaults to 'AND'.
     * The IN condition is generated by using the SQL IN operator which requires
     * the specified
     * column value to be among the given list of values.
     * 
     * @param string $column the column name (or a valid SQL expression)
     * @param array $values list of values that the column value should be in
     * @param string $operator the operator used to concatenate the new
     * condition with the existing one.
     * Defaults to 'AND'.
     */
    public function addInCondition($column, $values, $operator = 'AND')
    {
        if(($n = count($values)) < 1)
            return $this->addCondition('0=1' ,$operator); // 0=1 is used because
                                                         // in MSSQL value alone
                                                         // can't be used in
                                                         // WHERE
        if($n === 1) {
            $value = reset($values);
            if($value === null)
                return $this->addCondition($column . ' IS NULL');
            $condition = $column . '=' . self::PARAM_PREFIX . self::$paramCount;
            $this->params[self::PARAM_PREFIX . self::$paramCount ++] = $value;
        } else {
            $params = array();
            foreach($values as $value) {
                $params[] = self::PARAM_PREFIX . self::$paramCount;
                $this->params[self::PARAM_PREFIX . self::$paramCount ++] = $value;
            }
            $condition = $column . ' IN (' . implode(', ' ,$params) . ')';
        }
        return $this->addCondition($condition ,$operator);
    
    }

    /**
     * Appends an NOT IN condition to the existing {@link condition}.
     * The NOT IN condition and the existing condition will be concatenated via
     * the specified operator
     * which defaults to 'AND'.
     * The NOT IN condition is generated by using the SQL NOT IN operator which
     * requires the specified
     * column value to be among the given list of values.
     * 
     * @param string $column the column name (or a valid SQL expression)
     * @param array $values list of values that the column value should not be
     * in
     * @param string $operator the operator used to concatenate the new
     * condition with the existing one.
     * Defaults to 'AND'.
     */
    public function addNotInCondition($column, $values, $operator = 'AND')
    {
        if(($n = count($values)) < 1)
            return $this;
        if($n === 1) {
            $value = reset($values);
            if($value === null)
                return $this->addCondition($column . ' IS NOT NULL');
            $condition = $column . '!=' . self::PARAM_PREFIX . self::$paramCount;
            $this->params[self::PARAM_PREFIX . self::$paramCount ++] = $value;
        } else {
            $params = array();
            foreach($values as $value) {
                $params[] = self::PARAM_PREFIX . self::$paramCount;
                $this->params[self::PARAM_PREFIX . self::$paramCount ++] = $value;
            }
            $condition = $column . ' NOT IN (' . implode(', ' ,$params) . ')';
        }
        return $this->addCondition($condition ,$operator);
    
    }

    /**
     * Appends a condition for matching the given list of column values.
     * The generated condition will be concatenated to the existing {@link
     * condition}
     * via the specified operator which defaults to 'AND'.
     * The condition is generated by matching each column and the corresponding
     * value.
     * 
     * @param array $columns list of column names and values to be matched
     * (name=>value)
     * @param string $columnOperator the operator to concatenate multiple column
     * matching condition. Defaults to 'AND'.
     * @param string $operator the operator used to concatenate the new
     * condition with the existing one.
     * Defaults to 'AND'.
     */
    public function addColumnCondition($columns, $columnOperator = 'AND', $operator = 'AND')
    {
        $params = array();
        foreach($columns as $name => $value) {
            if($value === null)
                $params[] = $name . ' IS NULL';
            else {
                $params[] = $name . '=' . self::PARAM_PREFIX . self::$paramCount;
                $this->params[self::PARAM_PREFIX . self::$paramCount ++] = $value;
            }
        }
        return $this->addCondition(implode(" $columnOperator " ,$params) ,$operator);
    
    }

    /**
     * Adds a comparison expression to the {@link condition} property.
     *
     * This method is a helper that appends to the {@link condition} property
     * with a new comparison expression. The comparison is done by comparing a
     * column
     * with the given value using some comparison operator.
     */
    public function compare($column, $op, $value, $operator = 'AND')
    {
        $this->addCondition($column . " " . $op . " " . self::PARAM_PREFIX . self::$paramCount ,$operator);
        $this->params[self::PARAM_PREFIX . self::$paramCount ++] = $value;
        
        return $this;
    
    }

    /**
     * Adds a between condition to the {@link condition} property.
     *
     * The new between condition and the existing condition will be concatenated
     * via
     * the specified operator which defaults to 'AND'.
     * If one or both values are empty then the condition is not added to the
     * existing condition.
     * This method handles the case when the existing condition is empty.
     * After calling this method, the {@link condition} property will be
     * modified.
     * 
     * @param string $column the name of the column to search between.
     * @param string $valueStart the beginning value to start the between
     * search.
     * @param string $valueEnd the ending value to end the between search.
     * @param string $operator the operator used to concatenate the new
     * condition with the existing one.
     * Defaults to 'AND'.
     */
    public function addBetweenCondition($column, $valueStart, $valueEnd, $operator = 'AND')
    {
        if($valueStart === '' || $valueEnd === '')
            return $this;
        
        $paramStart = self::PARAM_PREFIX . self::$paramCount ++;
        $paramEnd = self::PARAM_PREFIX . self::$paramCount ++;
        $this->params[$paramStart] = $valueStart;
        $this->params[$paramEnd] = $valueEnd;
        $condition = "$column BETWEEN $paramStart AND $paramEnd";
        
        if($this->condition === '')
            $this->condition = $condition;
        else
            $this->condition = '(' . $this->condition . ') ' . $operator . ' (' . $condition . ')';
        return $this;
    
    }

    /**
     * Merges with another criteria.
     * In general, the merging makes the resulting criteria more restrictive.
     * For example, if both criterias have conditions, they will be 'AND'
     * together.
     * Also, the criteria passed as the parameter takes precedence in case
     * two options cannot be merged (e.g. LIMIT, OFFSET).
     * 
     * @param mixed $criteria the criteria to be merged with. Either an array or
     * @param boolean $useAnd whether to use 'AND' to merge condition and having
     * options.
     * If false, 'OR' will be used instead. Defaults to 'AND'.
     */
    public function mergeWith($criteria, $useAnd = true)
    {
        $and = $useAnd ? 'AND' : 'OR';
        if(is_array($criteria))
            $criteria = new self($criteria);
        if($this->select !== $criteria->select) {
            
            if($this->select === '*' || $criteria->select === '*')
                $this->select = '*';
            else if(is_array($this->select) && in_array('*' ,$this->select))
                $this->select = '*';
            else if(is_array($criteria->select) && in_array('*' ,$criteria->select))
                $this->select = '*';
                // if($this->select==='*')
                // $this->select=$criteria->select;
            else if($criteria->select !== '*') {
                $select1 = is_string($this->select) ? preg_split('/\s*,\s*/' ,trim($this->select) ,- 1 ,PREG_SPLIT_NO_EMPTY) : $this->select;
                $select2 = is_string($criteria->select) ? preg_split('/\s*,\s*/' ,trim($criteria->select) ,- 1 ,PREG_SPLIT_NO_EMPTY) : $criteria->select;
                $this->select = array_merge($select1 ,array_diff($select2 ,$select1));
            }
        }
        
        if($this->condition !== $criteria->condition) {
            if($this->condition === '')
                $this->condition = $criteria->condition;
            else if($criteria->condition !== '')
                $this->condition = "({$this->condition}) $and ({$criteria->condition})";
        }
        
        if($this->params !== $criteria->params)
            $this->params = array_merge($this->params ,$criteria->params);
        
        if($criteria->limit > 0)
            $this->limit = $criteria->limit;
        
        if($criteria->offset >= 0)
            $this->offset = $criteria->offset;
        
        if($criteria->alias !== null)
            $this->alias = $criteria->alias;
        
        if($this->order !== $criteria->order) {
            if($this->order === '')
                $this->order = $criteria->order;
            else if($criteria->order !== '')
                $this->order = $criteria->order . ', ' . $this->order;
        }
        
        if($this->group !== $criteria->group) {
            if($this->group === '')
                $this->group = $criteria->group;
            else if($criteria->group !== '')
                $this->group .= ', ' . $criteria->group;
        }
        
        if($this->join !== $criteria->join) {
            if($this->join === '')
                $this->join = $criteria->join;
            else if($criteria->join !== '')
                $this->join .= ' ' . $criteria->join;
        }
        
        if($this->having !== $criteria->having) {
            if($this->having === '')
                $this->having = $criteria->having;
            else if($criteria->having !== '')
                $this->having = "({$this->having}) $and ({$criteria->having})";
        }
        
        if($criteria->distinct > 0)
            $this->distinct = $criteria->distinct;
    
    }

    /**
     *
     * @return array the array representation of the criteria
     */
    public function toArray()
    {
        $result = array();
        foreach(array('select','condition','params','limit','offset','order','group','join','having','distinct') as $name)
            $result[$name] = $this->$name;
        return $result;
    
    }
}
