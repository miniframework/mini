<?php
class mini_db_unitofwork
{
    /**
     * mini_db_unitofwork handle
     * 
     * @var mini_db_unitofwork
     */
    public static $unitofwork = null;
    /**
     * store model in map
     * 
     * @var mini_struct_map
     */
    public $map = null;
    /**
     * store model in treemap [tab][key]
     * 
     * @var array
     */
    public $treemap = array();

    /**
     * init map
     * mini_db_unitofwork construct
     */
    private function __construct()
    {
        $this->map = new mini_struct_map();
    
    }

    /**
     * create a mini_db_unitofwork
     * 
     * @return mini_db_unitofwork
     */
    public static function getHandle()
    {
        if(self::$unitofwork == null)
            self::$unitofwork = new self();
        return self::$unitofwork;
    
    }

    /**
     * model is exists by key
     * 
     * @param string $key
     * @return boolean
     */
    public function exists($key)
    {
        return $this->map->exists($key);
    
    }

    /**
     * get a model from map
     * 
     * @param string $key
     * @return mini_db_model
     */
    public function get($key)
    {
        return $this->map->get($key);
    
    }

    /**
     * register a model to unitofwork
     * 
     * @param mini_db_model $model
     */
    public function register($model)
    {
        $key = $model->getKey();
        $this->map->add($key ,$model);
        $treekey = $model->getTag();
        
        $this->treemap[$treekey][$key] = $model;
    
    }

    /**
     * commit unitofwork and clear map and treemap
     */
    public function clear()
    {
        $this->commit();
        $this->map->clear();
        $this->treemap = array();
    
    }

    /**
     * flush model to db
     * 
     * @param array $treekey
     */
    public function flush($treekey)
    {
        if(isset($this->treemap[$treekey]) && ! empty($this->treemap[$treekey])) {
            foreach($this->treemap[$treekey] as $key => $model) {
                $model->save();
            }
        }
    
    }

    /**
     * commit unitofwork
     */
    public function commit()
    {
        if(! empty($this->treemap))
            foreach($this->treemap as $treekey => $list) {
                $this->flush($treekey);
            }
    
    }
}