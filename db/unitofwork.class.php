<?php
class mini_db_unitofwork
{
    public static $unitofwork = null;
    public $map = null;
    public $treemap = array();
    private function __construct()
    {
        $this->map = new mini_struct_map();
    }
    public static function getHandle()
    {
        if(self::$unitofwork == null)
            self::$unitofwork = new self();
        return self::$unitofwork;
    }
    public function exists($key)
    {
        return $this->map->exists($key);
    }
    public function get($key)
    {
        return $this->map->get($key);
    }
    public function register($model)
    {
        $key = $model->getKey();
        $this->map->add($key, $model);
        $treekey = $model->getTag();
        
        $this->treemap[$treekey][$key] = $model;
    }
    public function clear()
    {
       $this->commit();
       $this->map->clear();
       $this->treemap = array();
    }
    public function flush($treekey)
    {
        if(isset($this->treemap[$treekey]) && !empty($this->treemap[$treekey]))
        {
            foreach($this->treemap[$treekey] as $key => $model)
        	{
        	    $model->save();
        	}
        }
    }
    
    public function commit()
    {
        if(!empty($this->treemap))
            foreach($this->treemap as $treekey => $list)
        {
                $this->flush($treekey);
        }
    }
}