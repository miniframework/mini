<?php
class mini_db_record
{
    /**
     * model handle
     * 
     * @var mini_db_model
     */
    public $model = null;

    /**
     *
     * @param mini_db_model $model
     */
    public function __construct($model)
    {
        if(! $model instanceof mini_db_model) {
            mini::e("model must extends mini_db_model");
        }
        $this->model = $model;
    
    }

    /**
     * get db builder
     * 
     * @return mini_db_builder
     */
    public function getCommandBuilder()
    {
        if($this->model->schema == null) {
            mini::e("model must set schema");
        }
        return $this->model->schema->getBuilder();
    
    }

    /**
     * get db connection
     * 
     * @return mini_db_connection
     */
    public function getConnection()
    {
        return mini_db_connection::getHandle();
    
    }

    /**
     * get model by sql bind mode.
     * model name is classmodel
     * 
     * @param string $sql
     * @param array $params
     * @param string $classmodel
     * @return mini_db_model
     */
    public static function get($sql, $params = array(), $classmodel)
    {
        if(! class_exists($classmodel)) {
            mini::e("model class {class} not exists" ,array('{class}'=>$classmodel));
        }
        $model = mini_db_model::model($classmodel);
        mini_db_builder::getHandle()->bindValues($sql ,$params);
        mini_db_unitofwork::getHandle()->flush($model->schema->table);
        $row = mini_db_connection::getHandle()->find($sql);
        return $model->record->build($row);
    
    }

    /**
     * get models by sql bind mode.
     * model name is classname
     * 
     * @param string $sql
     * @param array $params
     * @param string $classmodel
     * @return array
     */
    public static function getAll($sql, $params = array(), $classmodel = '')
    {
        if(! class_exists($classmodel)) {
            mini::e("model class {class} not exists" ,array('{class}'=>$classmodel));
        }
        $model = mini_db_model::model($classmodel);
        mini_db_builder::getHandle()->bindValues($sql ,$params);
        mini_db_unitofwork::getHandle()->flush($model->schema->table);
        $rows = mini_db_connection::getHandle()->findAll($sql);
        return $model->record->buildAll($rows);
    
    }

    /**
     * flush model update to db
     */
    public function flush()
    {
        if($this->model->isAutoSave()) {
            mini_db_unitofwork::getHandle()->flush($this->model->schema->table);
        }
    
    }

    /**
     * commit unitofwork clear cache, before update by sql condition
     */
    public function clear()
    {
        if($this->model->isAutoSave()) {
            mini_db_unitofwork::getHandle()->clear();
        }
    
    }

    /**
     * get model by pk first from unitofwork next db
     * 
     * @param string $pk
     * @param array $select
     * @return mini_db_model
     */
    public function findByPk($pk, $select = '*')
    {
        $mapkey = $this->model->getKey($pk);
        $unitofwork = mini_db_unitofwork::getHandle();
        if($unitofwork->exists($mapkey)) {
            $model = $unitofwork->get($mapkey);
            if(! $model->isDirty())
                return $model;
            else
                return null;
        } else {
            $condition = new mini_db_condition();
            $condition->select = $select;
            $condition->compare($this->model->schema->primaryKey ,'=' ,$pk);
            $condition->mergeWith($this->model->condition);
            $sql = $this->getCommandBuilder()->findCommand($this->model->schema ,$condition);
            $row = $this->getConnection()->find($sql);
            return $this->build($row);
        }
    
    }

    /**
     * get model by condition from db
     * 
     * @param mini_db_condition $condition
     * @return mini_db_model
     */
    public function find($condition)
    {
        $condition->mergeWith($this->model->condition);
        
        $sql = $this->getCommandBuilder()->findCommand($this->model->schema ,$condition);
        $this->flush();
        $row = $this->getConnection()->find($sql);
        
        return $this->build($row);
    
    }

    /**
     * get models by condition from db
     * 
     * @param mini_db_condition $condition
     * @return mini_db_model
     */
    public function findAll($condition)
    {
        $condition->mergeWith($this->model->condition);
        $this->findCount($condition);
        $this->page($condition);
        $sql = $this->getCommandBuilder()->findCommand($this->model->schema ,$condition);
        $this->flush();
        $rows = $this->getConnection()->findAll($sql);
        
        return $this->buildAll($rows);
    
    }
    public function page($condition)
    {
        if($this->model->getPage() != null)
        {
           $page = $this->model->getPage();
           $page->setCount($this->findCount($condition))->applyLimit($condition);
        }
        
    }
    public function findCount($condition)
    {
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->countCommand($this->model->schema ,$condition);
        $this->flush();
        $row = $this->getConnection()->find($sql);
        return $row['count(*)'];
    }
    /**
     * get model by sql from db
     * 
     * @param string $where
     * @param array $params
     * @param array $columns
     * @return mini_db_model
     */
    public function findBySql($where, $params, $columns = array('*'))
    {
        $condition = new mini_db_condition(array("select"=>implode(',' ,$columns)));
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->findSqlCommand($this->model->schema ,$condition ,$where ,$params);
        $this->flush();
        $row = $this->getConnection()->find($sql);
        return $this->build($row);
    
    }

    /**
     * get models by sql from db
     * 
     * @param string $where
     * @param array $params
     * @param array $columns
     * @return mini_db_model
     */
    public function findAllBySql($where, $params, $columns = array('*'))
    {
        $condition = new mini_db_condition(array("select"=>implode(',' ,$columns)));
        $condition->mergeWith($this->model->condition);
        $this->page($condition);
        $sql = $this->getCommandBuilder()->findSqlCommand($this->model->schema ,$condition ,$where ,$params);
        $this->flush();
        $row = $this->getConnection()->findAll($sql);
        return $this->buildAll($row);
    
    }

    /**
     * build model by row
     * 
     * @param array $row
     * @return mini_db_model
     */
    public function build($row)
    {
        if(! empty($row)) {
            foreach($row as $name => $value) {
                
                if(! array_key_exists($name ,$this->model->getAttributes())) {
                    $this->model->set($name ,$value);
                }
            }
            mini_db_unitofwork::getHandle()->register($this->model);
            return $this->model;
        } else {
            return null;
        }
    
    }

    /**
     * build models by row
     * 
     * @param array $row
     * @return mini_db_model
     */
    public function buildAll($rows)
    {
        $class = get_class($this->model);
        if(! empty($rows)) {
            foreach($rows as $key => $row) {
                
                $model = mini_db_model::model($class);
                foreach($row as $name => $value) {
                    if(! array_key_exists($name ,$this->model->getAttributes())) {
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

    /**
     * update model by data
     * 
     * @param array $data
     */
    public function buildUpdate($data)
    {
        $pk = $this->model->get($this->model->schema->primaryKey);
        $version = 0;
        if($this->model->isAutoSave())
            $version = $data['version'];
        $sql = $this->getCommandBuilder()->updateCommandByPk($this->model->schema ,$data ,$pk ,$version);
        return $this->getConnection()->query($sql);
    
    }

    /**
     * update model by data
     * 
     * @param array $data
     */
    public function update($data, $condition)
    {
        $this->clear();
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->updateCommand($this->model->schema ,$data ,$condition);
        return $this->getConnection()->query($sql);
    
    }

    /**
     * insert model by data
     * 
     * @param array $data
     * @return int affect row num
     */
    public function buildInsert($data)
    {
        $sql = $this->getCommandBuilder()->insertCommand($this->model->schema ,$data);
        return $this->getConnection()->query($sql);
    
    }

    /**
     * insert model by data
     * 
     * @param array $data
     * @return int last_insert_id
     */
    public function insert($data)
    {
        $sql = $this->getCommandBuilder()->insertCommand($this->model->schema ,$data);
        return $this->getConnection()->insert($sql);
    
    }

    /**
     * delete model by pk
     * 
     * @param array $data
     * @return int affect row num
     */
    public function buildDelete()
    {
        $pk = $this->model->get($this->model->schema->primaryKey);
        
        $sql = $this->getCommandBuilder()->deleteCommandByPk($this->model->schema ,$pk);
        return $this->getConnection()->query($sql);
    
    }

    /**
     * delete model by condition
     * 
     * @param array $data
     * @return int affect row num
     */
    public function delete($condition)
    {
        $this->clear();
        $condition->mergeWith($this->model->condition);
        $sql = $this->getCommandBuilder()->deleteCommand($this->model->schema ,$condition);
        return $this->getConnection()->query($sql);
    
    }
}
?>