<?php
class mini_db_builder
{
    const PARAM_PREFIX = ':minib';
    public static $builder = null;
    public static function getHandle()
    {
        if(self::$builder == null)
            self::$builder = new self();
        return self::$builder;
    }
    public function findCommand($schema, $condition, $alias = 't')
    {
        
        if($condition->alias != '') {
            $alias = $condition->alias;
        }
        $alias = $schema->quoteTableName($alias);
        $prefix = $alias . '.';
        
        if(is_array($condition->select))
        {
            foreach($condition->select as $k =>$name)
            {
               if($pos=strrpos($name,'.') == false)
               {
                   $select[] = $prefix . $schema->quoteColumnName($name);
               }
               else
               {
                   $select[] =  $schema->quoteColumnName($name);
               }
            }
           $select = implode(', ' ,$select);
            
        }
        else
        {
            $select = $condition->select;
        }
       // $select = is_array($condition->select) ? implode(', ' ,$condition->select) : $condition->select;
        
        if($select === '*' && ! empty($condition->join)) {
          
            $select = array();
            foreach($schema->getColumnNames() as $name)
                $select[] = $prefix . $schema->quoteColumnName($name);
            $select = implode(', ' ,$select);
        }
        $sql = ($condition->distinct ? 'SELECT DISTINCT' : 'SELECT') . " {$select} FROM {$schema->getTable()} $alias";
        $sql = $this->applyJoin($sql ,$condition->join);
        $sql = $this->applyCondition($sql ,$condition->condition);
        $sql = $this->applyGroup($sql ,$condition->group);
        $sql = $this->applyHaving($sql ,$condition->having);
        $sql = $this->applyOrder($sql ,$condition->order);
        $sql = $this->applyLimit($sql ,$condition->limit ,$condition->offset);
        $sql = $this->bindValues($sql ,$condition->params);
        return $sql;
    }
    public function findSqlCommand($schema, $condition, $where, $params=array())
    {
        $select = is_array($condition->select) ? implode(', ' ,$condition->select) : $condition->select;
        $sql = "select" . " {$select} FROM {$schema->getTable()} " . $where;
        $sql = $this->bindValues($sql ,$params);
        return $sql;
        
    }
    public function deleteCommand($schema, $condition)
    {
        $sql = "DELETE FROM {$schema->table}";
        $sql = $this->applyJoin($sql ,$condition->join);
        $sql = $this->applyCondition($sql ,$condition->condition);
        $sql = $this->applyGroup($sql ,$condition->group);
        $sql = $this->applyHaving($sql ,$condition->having);
        $sql = $this->applyOrder($sql ,$condition->order);
        $sql = $this->applyLimit($sql ,$condition->limit ,$condition->offset);
        $sql = $this->bindValues($sql ,$condition->params);
        return $sql;
    }
    public function insertCommand($schema, $data)
    {
        $fields = array();
        $values = array();
        $placeholders = array();
        $i = 0;
        foreach($data as $name => $value) {
            if(in_array($name ,$schema->getColumns()) && ($value !== null)) {
                $fields[] = $name;
                $placeholders[] = self::PARAM_PREFIX . $i;
                $values[self::PARAM_PREFIX . $i] = $value;
                $i ++;
            }
        }
        if($fields === array()) {
            $pks = is_array($schema->primaryKey) ? $schema->primaryKey : array(
                    $schema->primaryKey 
            );
            foreach($pks as $pk) {
                $fields[] = $pk;
                $placeholders[] = 'NULL';
            }
        }
        $sql = "INSERT INTO {$schema->table} (" . implode(', ' ,$fields) . ') VALUES (' . implode(', ' ,$placeholders) . ')';
        
        $sql = $this->bindValues($sql ,$values);
        return $sql;
    }
    public function deleteCommandByPk($schema, $pk)
    {
        $placeholders = array();
        $i = 0;
        $placeholders = $schema->primaryKey.'='.self::PARAM_PREFIX.$i;
        $values[self::PARAM_PREFIX . $i] = $pk;
        $sql = "DELETE FROM {$schema->table}";
        $sql = $this->applyCondition($sql, implode(" and ", $placeholders));
        return $sql;
    }
    public function updateCommandByPk($schema, $data, $pk, $version=0)
    {
        $fields = array();
        $values = array();
        $i = 0;
        foreach($data as $name => $value) {
        	if(in_array($name ,$schema->getColumns())) {
        
        		$fields[] = $name . '=' . self::PARAM_PREFIX . $i;
        		$values[self::PARAM_PREFIX . $i] = $value;
        		$i ++;
        	}
        }
        if($fields === array())
        	throw new Exception('No columns are being updated for table "{table}');
        $placeholders[] = $schema->primaryKey.'='.self::PARAM_PREFIX.$i;
        $values[self::PARAM_PREFIX . $i] = $pk;
        $i++;
        if($version > 0)
        {
            $placeholders[] = 'version'.'<'.self::PARAM_PREFIX.$i;
            $values[self::PARAM_PREFIX . $i] = $version;
        }
        
        
         $sql = "UPDATE {$schema->table} SET " . implode(', ' ,$fields);
         $sql = $this->applyCondition($sql, implode('and ', $placeholders));
         $sql =  $this->bindValues($sql, $values);
         return $sql;
    }
    public function updateCommand($schema, $data, $condition)
    {
        $fields = array();
        $values = array();
        $i = 0;
        foreach($data as $name => $value) {
            if(in_array($name ,$schema->getColumns())) {
                
                $fields[] = $name . '=' . self::PARAM_PREFIX . $i;
                $values[self::PARAM_PREFIX . $i] = $value;
                $i ++;
            }
        }
        if($fields === array())
            throw new Exception('No columns are being updated for table "{table}');
        $sql = "UPDATE {$schema->table} SET " . implode(', ' ,$fields);
        $sql = $this->applyJoin($sql ,$condition->join);
        $sql = $this->applyCondition($sql ,$condition->condition);
        $sql = $this->applyOrder($sql ,$condition->order);
        $sql = $this->applyLimit($sql ,$condition->limit ,$condition->offset);
        $params = array_merge($values ,$condition->params);
        $sql = $this->bindValues($sql ,$params);
        return $sql;
    }
    public function applyOrder($sql, $orderBy)
    {
        if($orderBy != '')
            return $sql . ' ORDER BY ' . $orderBy;
        else
            return $sql;
    }
    public function bindValues($sql, $params)
    {
        if(! empty($params))
            foreach($params as $name => $value) {
                if($name[0] !== ':')
                    $name = ':' . $name;
                $sql = $this->bindValue($sql ,$name ,$value);
            }
        return $sql;
    }
    public function bindValue($sql, $name, $value)
    {
        return $sql = str_replace($name ,"'" . $value . "'" ,$sql);
    }
    public function applyJoin($sql, $join)
    {
        if($join != '')
            return $sql . ' ' . $join;
        else
            return $sql;
    }
    public function applyCondition($sql, $condition)
    {
        if($condition != '')
            return $sql . ' WHERE ' . $condition;
        else
            return $sql;
    }
    public function applyGroup($sql, $group)
    {
        if($group != '')
            return $sql . ' GROUP BY ' . $group;
        else
            return $sql;
    }
    public function applyHaving($sql, $having)
    {
        if($having != '')
            return $sql . ' HAVING ' . $having;
        else
            return $sql;
    }
    public function applyLimit($sql, $limit, $offset)
    {
        if($limit >= 0)
            $sql .= ' LIMIT ' . (int) $limit;
        if($offset > 0)
            $sql .= ' OFFSET ' . (int) $offset;
        return $sql;
    }
}
?>