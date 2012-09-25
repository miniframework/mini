<?php
class mini_db_connection
{
    /**
     * mini_db_connection handle
     *
     * @var array
     */
    private static $handle = array();
    /**
     * dbconfig info array(key,handle)
     *
     * @var array
     */
    private $dbconfig = array();

    /**
     * private mini_db_connection construct
     */
    private function __construct($config = array())
    {
        $this->loadConfig($config = null);
    
    }

    /**
     * get mini_db_connection handle
     *
     * @return mini_db_connection
     */
    public static function getHandle($config = array())
    {
        if(self::$handle == null) {
            self::$handle = new self($config = array());
        }
        return self::$handle;
    
    }

    /**
     * load db config from config.xml and new mini_db_mysql
     *
     * @throws if master or slave name not exists , and slave percent add not
     * 100%
     */
    private function loadConfig($config = array())
    {
        // get mini_boot_config from registry
        // get db config info
        if($config == null) {
            $dbconfig = mini::getConfig()->db;
        } else {
            $dbconfig = $config['db'];
        }
        
        // if db config empty throw exception
        if(empty($dbconfig)) {
            mini::e("not find mysql config for <db></db>");
        }
        $master = $dbconfig['master']['name'];
        // if master name empty throw exception
        if(empty($master)) {
            mini::e("master mysql must input name attr <master name=''></db>");
        }
        // get db config to dbconfig
        $this->dbconfig['master'] = array("host"=>$dbconfig['master']['host'],"user"=>$dbconfig['master']['user'],"pass"=>$dbconfig['master']['pass'],"port"=>$dbconfig['master']['port'],"dbname"=>$dbconfig['master']['dbname'],"charset"=>$dbconfig['master']['charset'],"name"=>$dbconfig['master']['name']);
        // get real db handle to dbconfig['handle']
        $this->dbconfig['master']['handle'] = $this->getDbObj($this->dbconfig['master']);
        
        // start db slave config info
        $slave = $dbconfig['slave'];
        // if key name not exists, maybe only 1 slave
        if(array_key_exists("name" ,$slave)) {
            $percent = 100;
            $this->dbconfig['slave'][0] = array("host"=>$dbconfig['slave']['host'],"user"=>$dbconfig['slave']['user'],"pass"=>$dbconfig['slave']['pass'],"port"=>$dbconfig['slave']['port'],"dbname"=>$dbconfig['slave']['dbname'],"charset"=>$dbconfig['slave']['charset'],"spercent"=>0,"epercent"=>$percent,"name"=>$dbconfig['slave']['name']);
            $this->dbconfig['slave'][0]['handle'] = $this->getDbObj($this->dbconfig['slave'][0]);
        } else {
            // else foreach slave info , get slave info to dbconfig['slave']
            // percent 0 1: 0-40 , 2:40-60 so percent +=
            // config['slave']['percent']
            $percent = 0;
            foreach($slave as $key => $value) {
                if(! array_key_exists("name" ,$value)) {
                    mini::e("slave mysql must input name attr <slave name=''></slave>");
                }
                $this->dbconfig['slave'][$key] = array("host"=>$value['host'],"user"=>$value['user'],"pass"=>$value['pass'],"port"=>$value['port'],"dbname"=>$value['dbname'],"charset"=>$value['charset'],"spercent"=>$percent,"epercent"=>$value['percent'] + $percent,"name"=>$value['name']);
                $this->dbconfig['slave'][$key]['handle'] = $this->getDbObj($this->dbconfig['slave'][$key]);
                $percent += $value['percent'];
            }
        }
        // if percent add lt 100 or gt 100 throw exception
        if($percent < 100 || $percent > 100) {
            mini::e("slave mysql percent must 100% <percent></percent>");
        }
    
    }

    public function getSchema()
    {
        $dbconfig = $this->dbconfig;
        switch (1) {
            case 1 :
                $schema = new mini_db_schema();
                
                break;
        }
        return $schema;
    
    }

    /**
     * return db handle for config db driver type
     *
     * @param array $config
     * @return db handle
     */
    public function getDbObj($config)
    {
        switch (1) {
            case 1 :
                $dbObj = new mini_db_mysql($config);
                
                break;
        }
        if($dbObj instanceof mini_db_interface) {
            return $dbObj;
        } else {
            mini::e("dbObj must instance of mini_db_interface");
        }
    
    }

    /**
     * get handle mini_db_mysql for name (master or other) if other rand (1,
     * 100) from slave
     *
     * @param string $name
     * @return mini_db_mysql
     */
    public function getDbHandle($name = "")
    {
        if($name == "master") {
            return $this->dbconfig['master']['handle'];
        } else {
            $r = rand(1 ,100);
            foreach($this->dbconfig['slave'] as $key => $value) {
                if($r >= $value['spercent'] && $r <= $value['epercent']) {
                    return $value['handle'];
                }
            }
        }
    
    }

    /**
     * get query from master mini_db_mysql
     *
     * @param string $sql
     * @return int
     */
    public function query($sql)
    {
        $db = $this->getDbHandle("master");
        $db->query($sql);
        return $db->affected();
    
    }

    public function insert($sql)
    {
        $db = $this->getDbHandle("master");
        $db->query($sql);
        return $db->lastInsertId();
    
    }

    /**
     * call mini_db_mysql unbuffer_query $obj extends unbuffer interface
     * that call will call callback() function
     *
     * @param string $sql
     * @param object $obj
     */
    public function unbuffer($sql, $unbuffer, $callback='')
    {
        $this->getDbHandle()->unbuffer($sql ,$unbuffer, $callback);
    
    }

    /**
     * fetch row if $obj is true return object
     *
     * @param string $sql
     * @param bool $obj
     * @return array
     */
    public function find($sql, $obj = false)
    {
        if(! $obj) {
            return $this->getDbHandle()->find($sql);
        } else {
            return $this->getDbHandle()->findObj($sql);
        }
    
    }

    /**
     * fetch rows if $obj is true return object array
     *
     * @param string $sql
     * @param bool $obj
     * @return array
     */
    public function findAll($sql, $obj = false)
    {
        if(! $obj) {
            return $this->getDbHandle()->findAll($sql);
        } else {
            return $this->getDbHandle()->findObjAll($sql);
        }
    
    }
}
?>