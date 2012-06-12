<?php
class mini_db_record
{
    public $model = null;
    
    public function __construct($model)
    {
        if(! $model instanceof mini_db_model) {
            mini::e("model must extends mini_db_model");
        }
        $this->model = $model;
    }
    public function getCommandBuilder()
    {
        if($this->model->schema == null) {
            mini::e("model must set schema");
        }
        return $this->model->schema->getBuilder();
    }
    public function getConnection()
    {
        return mini_db_connection::getHandle();
    }
    public static function get($sql, $params = array(), $classmodel)
    {
        if(! class_exists($classmodel)) {
            mini::e("model class {class} not exists",array('{class}'=>$classmodel));
        }
        $model = mini_db_model::model($classmodel);
        mini_db_builder::getHandle()->bindValues($sql ,$params);
        mini_db_unitofwork::getHandle()->flush($model->schema->table);
        $row = mini_db_connection::getHandle()->find($sql);
        return $model->record->build($row);
    }
    public static function getAll($sql, $params = array(), $classmodel = '')
    {
        if(! class_exists($classmodel)) {
             mini::e("model class {class} not exists",array('{class}'=>$classmodel));
        }
        $model = mini_db_model::model($classmodel);
        mini_db_builder::getHandle()->bindValues($sql ,$params);
        mini_db_unitofwork::getHandle()->flush($model->schema->table);
        $rows = mini_db_connection::getHandle()->findAll($sql);
        return $model->record->buildAll($rows);
    }
    public function flush()
    {
       if($this->model->isAutoSave())
       {
           mini_db_unitofwork::getHandle()->flush($this->model->schema->table);
       }
        
    }
    public function clear()
    {
        if($this->model->isAutoSave())
        {
        	mini_db_unitofwork::getHandle()->clear();
        }
    }
    public function findByPk($pk, $select = '*')
    {
        $mapkey = $this->model->getKey($pk);
        $unitofwork = mini_db_unitofwork::getHandle();
        if($unitofwork->exists($mapkey)) {
             $model = $unitofwork->get($mapkey);
             if(!$model->isDirty())
                 return $model;
             else
                  return null;
        } else {
            $condition = new mini_db_condition();
            $condition->select = $select;
            $condition->compare($this->model->schema->primaryKey, '=', $pk);
            $condition->mergeWith($this->model->condition);
            $sql = $this->getCommandBuilder()->findCommand($this->model->schema ,$condition);
            $row = $this->getConnection()->find($sql);
            return $this->build($row);
        }
    }
    public function find($condition)
    {
        $condition->mergeWith($this->model->condition);
        
        $sql = $this->getCommandBuilder()->findCommand($this->model->schema ,$condition);
        $this->flush();
        $row = $this->getConnection()->find($sql);
        
        return $this->build($row);
    }
    public function findAll($condition)
    {
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->findCommand($this->model->schema ,$condition);
        $this->flush();
        $rows = $this->getConnection()->findAll($sql);
        
        return $this->buildAll($rows);
    }
    public function findBySql($where, $params, $columns = array('*'))
    {
        $condition = new mini_db_condition(array(
                "select"=>implode(',' ,$columns) 
        ));
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->findSqlCommand($this->model->schema ,$condition ,$where ,$params);
        $this->flush();
        $row = $this->getConnection()->find($sql);
        return $this->build($row);
    }
    public function findAllBySql($where, $params, $columns = array('*'))
    {
        $condition = new mini_db_condition(array(
                "select"=>implode(',' ,$columns) 
        ));
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->findSqlCommand($this->model->schema ,$condition ,$where ,$params);
        $this->flush();
        $row = $this->getConnection()->findAll($sql);
        return $this->buildAll($row);
    }
    public function build($row)
    {
        if(! empty($row)) {
            foreach($row as $name => $value) {
               
                if(!array_key_exists($name, $this->model->getAttributes())) {
                    $this->model->set($name ,$value);
                }
            }
            mini_db_unitofwork::getHandle()->register($this->model);
            return $this->model;
        } else {
            return null;
        }
    }
    public function buildAll($rows)
    {
        $class = get_class($this->model);
        if(! empty($rows)) {
            foreach($rows as $key => $row) {
                
                $model = mini_db_model::model($class);
                foreach($row as $name => $value) {
                    if(!array_key_exists($name, $this->model->getAttributes())) {
                        $model->set($name ,$value);
                    }
                }
                mini_db_unitofwork::getHandle()->register($model);
                $models[] = $model;
            }
            return $models;
        } else {
            return array();
        }
    }
    public function buildUpdate($data)
    {
        $pk = $this->model->get($this->model->schema->primaryKey);
        $version = 0;
        if($this->model->isAutoSave())
            $version = $data['version'];
        $sql = $this->getCommandBuilder()->updateCommandByPk($this->model->schema ,$data ,$pk, $version);
        return $this->getConnection()->query($sql);
    }
    public function update($data, $condition)
    {
        $this->clear();
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->updateCommand($this->model->schema ,$data ,$condition);
        return $this->getConnection()->query($sql);
    }
    public function buildInsert($data)
    {
        $sql = $this->getCommandBuilder()->insertCommand($this->model->schema ,$data);
        return $this->getConnection()->query($sql);
    }
    public function insert($data)
    {
        $sql = $this->getCommandBuilder()->insertCommand($this->model->schema ,$data);
        return $this->getConnection()->insert($sql);
    }
    public function buildDelete()
    {
        $pk = $this->model->get($this->model->schema->primaryKey);
        
        $sql = $this->getCommandBuilder()->deleteCommandByPk($this->model->schema ,$pk);
        return $this->getConnection()->query($sql);
    }
    public function delete($condition)
    {
        $this->clear();
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->deleteCommand($this->model->schema ,$condition);
        return $this->getConnection()->query($sql);
    }
}
?>