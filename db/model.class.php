<?php
abstract class mini_db_model
{
    public $record = null;
    public $schema = null;
    public $condition = null;
    protected $attributes = array();
    protected $relations = array();
    protected $autoIncrement = false;
    protected $autoSave = false;
    protected $updateColumns = array();
    protected $isInsert = false;
    protected $isDelete = false;
    protected $isDirty = false;
    protected function __construct()
    {
        
        // mini_db_model abstract
        // if(get_class($this) == "mini_db_model")
        // throw new Exception("mini_db_model __construct is private!");
        // if($this->autoIncrement == true)
        // if(is_array($this->primaryKey))
        // throw new Exception("if autoIncrement , primaryKey must only");
        // table , columns,primaryKey not empty
        if(empty($this->table) || empty($this->columns) || ! is_array($this->columns) || empty($this->primaryKey))
            throw new Exception("create model table , columns , primaryKey must not empty!");
            // init record
        $this->record = new mini_db_record($this);
        
        // if(! is_array($this->primaryKey))
        // $this->primaryKey = array(
        // $this->primaryKey
        // );
        // init schema
        $this->schema = $this->record->getConnection()->getSchema()->setTable($this->table)->setColumns($this->columns)->primaryKey($this->primaryKey);
        
        $initSelect[] = $this->primaryKey;
        // if autoSave= true must version
        if($this->autoSave) {
            if(! in_array("version" ,$this->columns)) {
                throw new Exception("if use autoSave must add {version} cloumn!");
            }
            
            $initSelect[] = "version";
        }
        // init must select colomns
        $this->condition = new mini_db_condition(array(
                "select"=>implode("," ,$initSelect) 
        ));
        
        $this->init();
    }
    public static function model($class)
    {
        return new $class();
    }
    protected function init()
    {}
    public function isAutoSave()
    {
        return $this->autoSave;
    }
    public function isDirty()
    {
        return $this->isDirty;
    }
    protected function getGeneratorId()
    {
        return mini_tool_generator::getInstance()->getNextID();
    }
    public function isAutoIncrement()
    {
        return $this->autoIncrement;
    }
    public function __isset($name)
    {
        if(isset($this->attributes[$name]))
            return true;
        else
            return false;
    }
    public function getAttributes()
    {
        return $this->attributes;
    }
    public function create($data = array())
    {
       
        if($this->isDirty == true)
            throw new Exception("object Dirty!");
        $this->validcreate($data);
        
        $this->attributes = $data;
        
        if(! $this->autoIncrement) {
            // if primaryKey not autoIncrement, get value from getGeneratorId()
            $this->attributes[$this->primaryKey] = $this->getGeneratorId();
            $this->isInsert = true;
        } else {
            if($this->autoSave) {
                // if autoSave insert data ,and get last_insert_id
                $lastid = $this->record->insert($data);
                $this->attributes[$this->primaryKey] = $lastid;
            } else
                // if not autoSave set Insert sign = true
                $this->isInsert = true;
        }
        return $this;
    }
    public function __get($name)
    {
        // check primaryKey iswether value
        $this->checkPrimaryKey();
        // if model deleted not get value
        if($this->isDirty == true)
            throw new Exception("object Dirty!");
            // get attributes value
        if(isset($this->attributes[$name]))
            return $this->attributes[$name];
            // get relation model
        else if(isset($this->relations[$name]))
            return $this->getRelation($name);
            // if in columns send select name from table where primaryKey='';
        else if(in_array($name ,$this->columns)) {
            $condition = new mini_db_condition();
            $condition->select = $name;
            $condition->compare($this->primaryKey ,"=" . $this->attributes[$this->primaryKey] ,true);
            $this->record->find($condition);
            return $this->attributes[$name];
        } else {
            throw new Exception("not find $name attributes!");
        }
    }
    public function setAttr($attr = array())
    {
        
    	// if model deleted not set value
    	if($this->isDirty == true)
    		throw new Exception("model deleted!");
    
    	$this->validAttr($attr);
    	// if $name in columns, create update Columns
    	if(!empty($attr))
    	foreach($attr as $name => $value)
    	{
        	if(in_array($name ,$this->columns) && (! isset($this->attributes[$name]) || $value != $this->attributes[$name])) {
        		$this->updateColumns[$name] = $value;
        	}
        	$this->attributes[$name] = $value;
    	}
    }
    public function __set($name, $value)
    {
        
        // if model deleted not set value
        if($this->isDirty == true)
            throw new Exception("model deleted!");
        
        $this->validupdate(array($name=>$value));
            // if $name in columns, create update Columns
        if(in_array($name ,$this->columns) && (! isset($this->attributes[$name]) || $value != $this->attributes[$name])) {
            $this->updateColumns[$name] = $value;
        }
        $this->attributes[$name] = $value;
    }
    public function getByPk($pk = '', $select = array('*'))
    {
        return $this->record->findByPk($pk ,$select);
    }
    public function get($name)
    {
        return $this->attributes[$name];
    }
    public function set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    public function delete()
    {
        $this->isDelete = true;
        $this->isDirty = true;
    }
    public function buildInsert()
    {
        if($this->isDelete != true && $this->isInsert == true) {
            foreach($this->columns as $value) {
                if(isset($this->attributes[$value]))
                    $data[$value] = $this->attributes[$value];
            }
            $this->isInsert = false;
            $lastid = $this->record->insert($data);
            if($this->autoIncrement && ! $this->autoSave) {
                $this->attributes[$this->primaryKey] = $lastid;
            }
        }
    }
    public function buildDelete()
    {
        if($this->isDelete == true) {
            $this->isDelete = false;
            $affectnum = $this->record->buildDelete();
            
            if($affectnum <= 0) throw new Exception("version controller delete not affect!");
        }
    }
    public function buildUpdate()
    {
        if($this->isDelete != true && $this->isInsert != true && ! empty($this->updateColumns)) {
            foreach($this->updateColumns as $name => $value) {
                if(in_array($name ,$this->columns))
                    $data[$name] = $value;
            }
            if($this->autoSave)
            {
                $data['version'] = $this->attributes['version'] + 1;
            }
            $this->updateColumns = array();
            $affectnum = $this->record->buildUpdate($data);
            if($affectnum <= 0) throw new Exception("version controller update not affect!");
        }
    }
    public function clear()
    {
        $this->isInsert = false;
        $this->updateColumns = array();
        $this->isDelete = false;
    }
    public function save()
    {
        $this->buildInsert();
        $this->buildUpdate();
        $this->buildDelete();
        $this->clear();
    }
    public function validupdate()
    {
    
    }
    public function validcreate($data=array())
    {
    
    }
    public function checkPrimaryKey()
    {
        if(empty($this->attributes[$this->primaryKey]))
            throw new Exception("must first load model primaryKey data");
    }
    public function getTag()
    {
        return $this->table;
    }
    public function getKey($key = '')
    {
        if(empty($key)) {
            $key = $this->attributes[$this->primaryKey];
        }
        $mapkey = implode("_" ,array(
                $this->table,
                $key 
        ));
        return $mapkey;
    }
}
?>