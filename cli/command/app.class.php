<?php
class mini_cli_command_app extends mini_cli_command
{
    public $rootPath = '';
    public function run($args)
    {
        if(! isset($args[0]))
            throw new Exception('the Web application location is not specified.');
        $path = strtr($args[0] ,'/\\' ,DIRECTORY_SEPARATOR);
        if(strpos($path ,DIRECTORY_SEPARATOR) === false)
            $path = '.' . DIRECTORY_SEPARATOR . $path;
        $dir = rtrim(realpath(dirname($path)) ,'\\/');
        if($dir === false || ! is_dir($dir))
            throw new Exception("The directory '$path' is not valid. Please make sure the parent directory exists.");
        if(basename($path) === '.')
            $this->rootPath = $path = $dir;
        else
            $this->rootPath = $path = $dir . DIRECTORY_SEPARATOR . basename($path);
        if($this->confirm("Create a Web application under '$path'?")) {
            
            $sourceDir = realpath(dirname(__FILE__) . '/../app');
            if($sourceDir === false)
                throw new Exception("\nUnable to locate the source directory.\n");
            
            $list = $this->buildFileList($sourceDir ,$path);
            $list['index.php']['callback'] = array(
                    $this,
                    'generateIndex' 
            );
            
            $this->copyFiles($list);
            echo "\nYour application has been created successfully under {$path}.\n";
        }
    }
    public function copyFiles($fileList)
    {
        $overwriteAll = false;
        foreach($fileList as $name => $file) {
            $source = strtr($file['source'] ,'/\\' ,DIRECTORY_SEPARATOR);
            $target = strtr($file['target'] ,'/\\' ,DIRECTORY_SEPARATOR);
            $callback = isset($file['callback']) ? $file['callback'] : null;
            $params = isset($file['params']) ? $file['params'] : null;
            
            if(is_dir($source)) {
                $this->mkdir($target);
                continue;
            }
            
            if($callback !== null)
                $content = call_user_func($callback ,$source ,$params);
            else
                $content = file_get_contents($source);
            if(is_file($target)) {
                if($content === file_get_contents($target)) {
                    echo "  unchanged $name\n";
                    continue;
                }
                if($overwriteAll)
                    echo "  overwrite $name\n";
                else {
                    echo "      exist $name\n";
                    echo "            ...overwrite? [Yes|No|All|Quit] ";
                    $answer = trim(fgets(STDIN));
                    if(! strncasecmp($answer ,'q' ,1))
                        return;
                    else if(! strncasecmp($answer ,'y' ,1))
                        echo "  overwrite $name\n";
                    else if(! strncasecmp($answer ,'a' ,1)) {
                        echo "  overwrite $name\n";
                        $overwriteAll = true;
                    } else {
                        echo "       skip $name\n";
                        continue;
                    }
                }
            } else {
                $this->mkdir(dirname($target));
                echo "   generate $name\n";
            }
            file_put_contents($target ,$content);
        }
    }
    public function generateIndex($source, $params)
    {
        $content = file_get_contents($source);
        $mini = realpath(dirname(__FILE__) . '/../../mini.class.php');
        $mini = $this->getRelativePath($mini ,$this->rootPath . DIRECTORY_SEPARATOR . 'index.php');
        $mini = str_replace('\\' ,'\\\\' ,$mini);
        return preg_replace('/\$mini\s*=(.*?);/' ,"\$mini=$mini;" ,$content);
    }
    protected function getRelativePath($path1, $path2)
    {
        $segs1 = explode(DIRECTORY_SEPARATOR ,$path1);
        $segs2 = explode(DIRECTORY_SEPARATOR ,$path2);
        $n1 = count($segs1);
        $n2 = count($segs2);
        
        for($i = 0; $i < $n1 && $i < $n2; ++ $i) {
            if($segs1[$i] !== $segs2[$i])
                break;
        }
        
        if($i === 0)
            return "'" . $path1 . "'";
        $up = '';
        for($j = $i; $j < $n2 - 1; ++ $j)
            $up .= '/..';
        for(; $i < $n1 - 1; ++ $i)
            $up .= '/' . $segs1[$i];
        
        return 'dirname(__FILE__).\'' . $up . '/' . basename($path1) . '\'';
    }
    public function buildFileList($sourceDir, $targetDir, $baseDir = '')
    {
        $list = array();
        $handle = opendir($sourceDir);
        while(($file = readdir($handle)) !== false) {
            if($file === '.' || $file === '..' || $file === '.svn' || $file === '.yii')
                continue;
            $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $file;
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $file;
            $name = $baseDir === '' ? $file : $baseDir . '/' . $file;
            $list[$name] = array(
                    'source'=>$sourcePath,
                    'target'=>$targetPath 
            );
            if(is_dir($sourcePath))
                $list = array_merge($list ,$this->buildFileList($sourcePath ,$targetPath ,$name));
        }
        closedir($handle);
        return $list;
    }
    public function mkdir($directory)
    {
        if(! is_dir($directory)) {
            $this->mkdir(dirname($directory));
            echo "      mkdir " . strtr($directory ,'\\' ,'/') . "\n";
            mkdir($directory);
        }
    }
    public function help()
    {
        return <<<EOD
USAGE
  minic app <app-path>

DESCRIPTION
  This command generates an Yii Web Application at the specified location.

PARAMETERS
 * app-path: required, the directory where the new application will be created.
   If the directory does not exist, it will be created. After the application
   is created, please make sure the directory can be accessed by Web users.

EOD;
    }
    /**
     * Asks user to confirm by typing y or n.
     *
     * @param string $message
     *            to echo out before waiting for user input
     * @return bool if user confirmed
     *        
     * @since 1.1.9
     */
    public function confirm($message)
    {
        echo $message . ' [yes|no] ';
        return ! strncasecmp(trim(fgets(STDIN)) ,'y' ,1);
    }
}