<?php
abstract class mini_db_model
{
    /**
     * the active record Object-Relational Mapping
     * 
     * @var mini_db_record
     */
    public $record = null;
    /**
     * the table metadata information
     * 
     * @var mini_db_schema
     */
    public $schema = null;
    /**
     * build a query criteria, such as conditions, ordering by
     * 
     * @var mini_db_condition
     */
    public $condition = null;
    /**
     * model attributes
     * 
     * @var array
     */
    protected $attributes = array();
    /**
     * whether is auto increment column
     * 
     * @var boolean
     */
    protected $autoIncrement = false;
    /**
     * whether is auto save to db
     * 
     * @var boolean
     */
    protected $autoSave = false;
    /**
     * model update columns
     * 
     * @var array
     */
    protected $updateColumns = array();
    /**
     * whether is model new create
     * 
     * @var boolean
     */
    protected $isInsert = false;
    /**
     * whether is model deleted
     * 
     * @var boolean
     */
    protected $isDelete = false;
    /**
     * whether is model dirty
     * 
     * @var boolean
     */
    protected $isDirty = false;
    private $validators = array();
    protected $page = null;
    private $errors = array();
    /**
     * mini_db_model construct
     * create model metadata info on schema and init record.
     * whether necessary property set primaryKey version and so on.
     */
    protected function __construct()
    {
        if(empty($this->table) || empty($this->columns) || ! is_array($this->columns) || empty($this->primaryKey))
            mini::e("create model table , columns , primaryKey  not empty");
        
        $this->record = new mini_db_record($this);
        
        $this->schema = $this->record->getConnection()->getSchema()->setTable($this->table)->setColumns($this->columns)->primaryKey($this->primaryKey);
        
        $initSelect[] = $this->primaryKey;
        // if autoSave= true must version
        if($this->autoSave) {
            if(! in_array("version" ,$this->columns)) {
                mini::e("if use autoSave must add version to cloumns");
            }
            
            $initSelect[] = "version";
        }
        // init must select colomns
        $this->condition = new mini_db_condition(array("select"=>implode("," ,$initSelect)));
        $this->init();
    
    }
     
    /**
     * create a model
     * 
     * @param string $class
     * @return mini_db_model
     */
    public static function model($class)
    {
        return new $class();
    
    }
    public function page($params = array())
    {
        $this->page = new mini_tool_page($params);
        return $this->page;
    }
    public function getPage()
    {
        return $this->page;
    }
    /**
     * init model
     */
    protected function init()
    {
    }

    /**
     * get model is autosave
     * 
     * @return boolean
     */
    public function isAutoSave()
    {
        return $this->autoSave;
    
    }

    /**
     * get model is dirty
     * 
     * @return boolean
     */
    public function isDirty()
    {
        return $this->isDirty;
    
    }

    /**
     * get global only id
     */
    protected function getGeneratorId()
    {
        return mini_tool_generator::getInstance()->getNextID();
    
    }

    /**
     * get is auto increment
     * 
     * @return boolean
     */
    public function isAutoIncrement()
    {
        return $this->autoIncrement;
    
    }

    /**
     * is set value in attributes.
     * 
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        if(isset($this->attributes[$name]))
            return true;
        else
            return false;
    
    }

    /**
     * get model all attributes;
     * 
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    
    }

    /**
     * create a new model
     * 1.model autoSave=true
     * if model update,insert,delete, do not call save(), because request end,
     * model auto flush to db
     * but must add version columns in table.
     *
     * 2.model autoSave=false
     * if model update,insert, delete must call save() flush to db
     *
     * 3.model autoIncrement=true
     * table primarykey columns must autoincrement.
     * waring:
     * autoIncrement=true && autoSave=false
     * if model->create() must immediately call save(), because model call
     * __get() must primaykey extist.
     *
     * 4.model autoIncrement=false
     * model primayKey = getGeneratorId(), that generator globally unique id.
     * from table idgenerator
     * or Override getGeneratorId method.
     * 
     * @param array $data
     * @return mini_db_model
     */
    public function create($data = array())
    {
        if($this->isDirty == true)
            mini::e("model deleted,dirty is true not create model.");
        
        if(!$this->validator($data, "create")) return null;
        $data['version'] = 1;
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
        
        mini_db_unitofwork::getHandle()->register($this);
        return $this;
    
    }
    public function setByRequest($request, $message='')
    {
        $pk = $request->get($this->getPrimaryKey());
        $model = $this->getByPk($pk, $message);
        if($model === null)
        {
            return null;
        }
        foreach($this->columns as $k => $column)
        {
        	if($column == $this->primaryKey || $column == 'version')
        		continue;
        	if($request->get($column) !==null)
        	{
        		$this->$column = $request->get($column);
        	}
        }
        return $this;
    }
    public function createByRequest($request)
    {
        foreach($this->columns as $k => $column)
        {
            if($column == $this->primaryKey || $column == 'version')
                continue;
            if($request->get($column) !==null)
            {
                $row[$column] = $request->get($column);
            }
        }
        return $this->create($row);
    }
    /**
     * get model attributes if columns in attributes and not set get attributes
     * from db.
     * 
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        // check primaryKey iswether value
        $this->checkPrimaryKey();
        // if model deleted not get value
        if($this->isDirty == true)
            mini::e("model deleted,dirty is true not get attributes.");
            // get attributes value
        if(isset($this->attributes[$name]))
            return $this->attributes[$name];
        else if(in_array($name ,$this->columns) &&  $this->isInsert==true)
        {
            return null;
        }
        else if(in_array($name ,$this->columns)) {
            $condition = new mini_db_condition();
            $condition->select = $name;
            $condition->compare($this->primaryKey ,"=" ,$this->attributes[$this->primaryKey]);
            $this->record->find($condition);
            return $this->attributes[$name];
        } else {
            mini::e("class {class} not find {attributes} attributes" ,array("{class}"=>__CLASS__,"{attributes}"=>$name));
        }
    
    }

    /**
     * According function relations() and scopes() maps return model
     * 
     * @param string $name
     * @param array $argv
     * @return mini_db_model
     */
    public function __call($name, $argv)
    {
        $relations = $this->relations();
        $scopes = $this->scopes();
        if(isset($relations[$name])) {
            $related = $relations[$name][0];
            $model = $relations[$name][1];
            $columns = $relations[$name][2];
            $paramnum = count($relations[$name]);
            if($related == 'hasbelong') {
                $pk = $relations[$name][3];
                return mini_db_model::model($model)->getByPk($this->$pk ,$columns);
            } else if($related == 'hasmany') {
                $method = $relations[$name][3];
                if(is_array($columns)) {
                    foreach($columns as $k => $column) {
                        $params[$k] = $this->$column;
                    }
                }
                if(isset($argv[0]) && is_array($argv[0])) {
                    $params = array_merge($params ,$argv);
                }
                if(empty($method)) {
                    mini::e("model relations hasmany must set {name} method on argv[3]" ,array('{name}'=>$name));
                }
                return mini_db_model::model($model)->$method($params);
            }
        } else if(isset($scopes[$name])) {
            $condition = new mini_db_condition($scopes[$name]);
            if(!empty($argv[0]))
            {
                $condition->params = $argv[0];
            }
            if($scopes[$name]['hasmany'] == true) {
                return $this->record->findAll($condition);
                
            } else {
                return $this->record->find($condition);
            }
        } else {
            mini::e("class {class} not find method {name} " ,array('{class}'=>__CLASS__,'{name}'=>$name));
        }
    
    }

    /**
     * return model relations map
     * 
     * @return array
     */
    public function relations()
    {
        return array();
    
    }

    /**
     * return map for select condition
     * 
     * @return array
     */
    public function scopes()
    {
        return array();
    
    }

    /**
     * set model attributs and set updateColumns
     * 
     * @param array $attr
     */
    public function setAttr($attr = array())
    {
        
        // if model deleted not set value
        if($this->isDirty == true)
            mini::e("model deleted,dirty is true not get attributes.");
            
            // if $name in columns, create update Columns
        if($this->validator($attr,"update"))
        {
            if(! empty($attr))
                foreach($attr as $name => $value) {
                    if(in_array($name ,$this->columns) && (! isset($this->attributes[$name]) || $value != $this->attributes[$name])) {
                        $this->updateColumns[$name] = $value;
                    }
                    $this->attributes[$name] = $value;
                }
        }
    
    }

    /**
     * set model attributs and set updateColumns
     * 
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        
        // if model deleted not set value
        if($this->isDirty == true)
            mini::e("model deleted,dirty is true not set attributes.");
        if($this->validator(array($name=>$value),"update"))
        {   
                // if $name in columns, create update Columns
            if(in_array($name ,$this->columns) && (! isset($this->attributes[$name]) || $value != $this->attributes[$name])) {
                $this->updateColumns[$name] = $value;
            }
            $this->attributes[$name] = $value;
        }
    
    }
    public function validator($data, $on)
    {
        foreach($this->getValidators() as $validator)
        {
            if($validator->applyTo($on))
        	$validator->validate($this,$data);
        }
        return !$this->hasErrors();
    }
    public function getValidators()
    {
        $validators=new mini_struct_list();
        foreach($this->rules() as $rule)
        {
        	if(isset($rule[0],$rule[1]))  // attributes, validator name
        		$validators->add(mini_base_validator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2)));
        	else
        		mini::e('{class} has an invalid validation rule. The rule must specify attributes to be validated and the validator name.',
        				array('{class}'=>get_class($this)));
        }
        return $validators;
    }
    /**
     * get model by primaryKey
     * 
     * @param string $pk
     * @param array $select
     * @return mini_db_model
     */
    public function getByPk($pk, $select = array('*'), $message='')
    {
        if(empty($pk))
            mini::e("pk not empty.");
        $model = $this->record->findByPk($pk ,$select);
      
        return $model;
    }

    /**
     * get model attributes
     * 
     * @param string $name
     * @return string
     */
    public function get($name)
    {
        return $this->attributes[$name];
    
    }

    /**
     * set model model attributes, but not modify updateColumns
     * 
     * @param string $name
     * @param string $value
     */
    public function set($name, $value)
    {
        $this->attributes[$name] = $value;
    
    }

    /**
     * set model deleted and dirty
     */
    public function delete()
    {
        $this->isDelete = true;
        $this->isDirty = true;
    
    }

    /**
     * build insert sql for model property isinsert
     */
    public function buildInsert()
    {
        if($this->isDelete != true && $this->isInsert == true) {
            foreach($this->columns as $value) {
                if(isset($this->attributes[$value]))
                    $data[$value] = $this->attributes[$value];
            }
          //  $this->isInsert = false;
            $lastid = $this->record->insert($data);
            if($this->autoIncrement && ! $this->autoSave) {
                $this->attributes[$this->primaryKey] = $lastid;
            }
        }
    
    }

    /**
     * build delete sql for model property isDelete
     */
    public function buildDelete()
    {
        if($this->isDelete == true) {
            $affectnum = $this->record->buildDelete();
            
            if($affectnum <= 0)
                mini::e("version control delete not affect any row");
        }
    
    }

    /**
     * build update sql for model property updateColumns
     */
    public function buildUpdate()
    {
        if($this->isDelete != true && $this->isInsert != true && ! empty($this->updateColumns)) {
            foreach($this->updateColumns as $name => $value) {
                if(in_array($name ,$this->columns))
                    $data[$name] = $value;
            }
            if($this->autoSave) {
               $data['version'] = $this->attributes['version']= $this->attributes['version'] + 1;
            }
            $this->updateColumns = array();
            $affectnum = $this->record->buildUpdate($data);
            if($affectnum <= 0)
                mini::e("version control update not affect any row");
        }
    
    }

    /**
     * clear model isinsert , updateColumns, isdelete sign
     */
    public function clear()
    {
        $this->isInsert = false;
        $this->updateColumns = array();
        $this->isDelete = false;
    
    }

    /**
     * flush model to db
     */
    public function save()
    {
        $this->buildInsert();
        $this->buildUpdate();
        $this->buildDelete();
        $this->clear();
    
    }
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
    /**
     * check model is set primarykey
     */
    public function checkPrimaryKey()
    {
        if(empty($this->attributes[$this->primaryKey]))
            mini::e("use model must first load  primaryKey data to attributes");
    
    }

    /**
     * get model table
     */
    public function getTag()
    {
        return $this->schema->table;
    
    }

    /**
     * get model key create by table_primarykey
     * 
     * @param string $key
     * @return string
     */
    public function getKey($key = '')
    {
        if(empty($key)) {
            $key = $this->attributes[$this->primaryKey];
        }
        $mapkey = implode("_" ,array($this->getTag(),$key));
        return $mapkey;
    
    }
    public function hasErrors($attribute=null)
    {
    	if($attribute===null)
    		return $this->errors!==array();
    	else
    		return isset($this->errors[$attribute]);
    }
    public function getErrors($attribute=null)
    {
    	if($attribute===null)
    		return $this->errors;
    	else
    		return isset($this->errors[$attribute]) ? $this->errors[$attribute] : array();
    }
    public function getError($attribute)
    {
    	return isset($this->errors[$attribute]) ? reset($this->errors[$attribute]) : null;
    }
    public function addError($attribute,$error)
    {
    	$this->errors[$attribute][]=$error;
    }
    public function addErrors($errors)
    {
    	foreach($errors as $attribute=>$error)
    	{
    		if(is_array($error))
    		{
    			foreach($error as $e)
    				$this->errors[$attribute][]=$e;
    		}
    		else
    			$this->errors[$attribute][]=$error;
    	}
    }
    public function getFirstError()
    {
        foreach($this->errors as $attribute => $error)
        {
            if(isset($this->errors[$attribute]))
            {
                return  reset($this->errors[$attribute]);
            }
           
        }
    }
    public function clearErrors($attribute=null)
    {
    	if($attribute===null)
    		$this->errors=array();
    	else
    		unset($this->errors[$attribute]);
    }
}
?>