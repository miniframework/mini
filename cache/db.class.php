<?php
class mini_cache_db extends mini_base_cache
{
    public $cacheTableName = 'minicache';
    public $autoCreateCacheTable = true;
    private $db;
    private $gcProbability = 100;
    private $gced = false;

    public function perinit()
    {
        $db = $this->getConnection();
        if($this->autoCreateCacheTable) {
            $sql = "DELETE FROM {$this->cacheTableName} WHERE expire>0 AND expire<" . time();
            try {
                $db->query($sql);
            } catch (Exception $e) {
                $this->createCacheTable($db ,$this->cacheTableName);
            }
        }
    
    }

    public function getConnection()
    {
        $this->db = mini_db_connection::getHandle();
        return $this->db;
    
    }

    protected function createCacheTable($db, $tableName)
    {
        $sql = <<<EOD
CREATE TABLE $tableName
(
	id CHAR(128) PRIMARY KEY,
	expire INTEGER,
	value LONGBLOB
)
EOD;
        $db->query($sql);
    
    }

    protected function getValue($key)
    {
        $time = time();
        $sql = "SELECT value FROM {$this->cacheTableName} WHERE id='$key' AND (expire=0 OR expire>$time)";
        $db = $this->getConnection();
        $row = $db->find($sql);
        return $row['value'];
    
    }

    protected function setValue($key, $value, $expire)
    {
        $this->deleteValue($key);
        return $this->addValue($key ,$value ,$expire);
    
    }

    protected function addValue($key, $value, $expire)
    {
        if(! $this->gced && mt_rand(0 ,1000000) < $this->gcProbability) {
            $this->gc();
            $this->gced = true;
        }
        
        if($expire > 0)
            $expire += time();
        else
            $expire = 0;
        $sql = "INSERT INTO {$this->cacheTableName} (id,expire,value) VALUES ('$key',$expire,:value)";
        $builder = mini_db_builder::getHandle();
        $params = array(":value"=>$value);
        $sql = $builder->bindValues($sql ,$params);
        $this->getConnection()->query($sql);
    
    }

    protected function deleteValue($key)
    {
        $sql = "DELETE FROM {$this->cacheTableName} WHERE id='$key'";
        $this->getConnection()->query($sql);
        return true;
    
    }

    protected function gc()
    {
        $this->getConnection()->query("DELETE FROM {$this->cacheTableName} WHERE expire>0 AND expire<" . time());
    
    }

    protected function flushValues()
    {
        $this->getConnection()->query("DELETE FROM {$this->cacheTableName}");
        return true;
    
    }
}