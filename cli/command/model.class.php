<?php
class mini_cli_command_model extends mini_cli_command
{
    public $connection;
    public function run($args)
    {
       $this->connection = mini_db_connection::getHandle();
       $modelPath = mini::getRunPath()."/models";
       if($args[0] == 'show')
       {  
           if($args[1] == 'all')
           {
              
               $rows =  $this->connection->findAll("show  tables");
               if(!empty($rows))
               {
                   foreach($rows as $tables)
                    foreach($tables as $table)
                   {
                       $this->showTableCreate($table);
                   }
            	   
               }
           }
           else
           {
               $this->showTableCreate($args[1]);
           }
        }
        else if($args[0]=='create')
        {
            if($args[1] == 'all')
            {
                if($this->confirm("Create all model under '$modelPath'?")) {
                	$rows =  $this->connection->findAll("show  tables");
                	if(!empty($rows))
                	{
                		foreach($rows as $tables)
                			foreach($tables as $table)
                			{
                				$this->createModel($table);
                			}
                
                	}
                }
            }
            else
            {
                if($this->confirm("Create a model under '$modelPath'?")) {
                		$this->createModel($args[1]);
                }
            }
        }
    }
    public function createModel($table)
    {
        $rows = $this->showTableDesc($table);
        $autoSave = 'false';
        $autoIncrement = 'false';
        foreach($rows as $k => $column)
        {
            $field[] = "'".$column['Field']."'";
            if(!empty($column['Key'])) $primaryKey = $column['Field'];
            if($column['Field'] == 'version') $autoSave = 'true';
            if(!empty($column['Extra'])) $autoIncrement = 'true';
        }
        $columns = implode(",", $field);
        $modelClass ="<?php 
class $table extends mini_db_model
{
    protected  \$table = '$table';
    protected  \$columns = array($columns);
    protected  \$primaryKey = '$primaryKey';
    protected  \$autoSave = $autoSave;
    protected  \$autoIncrement = $autoIncrement;

    // NOTE: you should only define rules for those attributes that
    public function rules()
    {
        return array();
    }
    // NOTE:array relational rules            
    public function relations()
    {
        return array();
    }
    // NOTE:user defind select scopes            
    public function scopes()
    {
        return array();
    }
}"; 
        $modelPath = mini::getRunPath()."/models";
        $modelFile = $modelPath."/".$table.".class.php";
        
        if(file_exists($modelFile))
        {
            echo "[waring]".$table.".class.php file exists not create, if create please rm file.\r\n";
        }
        else
        {
            file_put_contents($modelFile, $modelClass);
            echo $table.".class.php file create successfull.\r\n";
        }
        
    }
    public function showTableDesc($table)
    {
    	$rows =  $this->connection->findAll("desc $table");
    	return $rows;
    }
    public function showTableCreate($table)
    {
        $row =  $this->connection->find("show create table $table");
        echo "table:".$row['Table']."\r\n";
        echo "create sql:\r\n\t".trim($row['Create Table'])."\r\n\r\n";
    }
    public function help()
    {
        	return <<<EOD
USAGE
  model [show|create] [all|table]
        
DESCRIPTION
  create a model from table.
        
EOD;
    }
    
}