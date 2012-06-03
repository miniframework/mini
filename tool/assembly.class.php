<?php
class mini_tool_assembly
{
    private $assemblyFile;
    private $includePaths;
    private $excludePaths;
    private $classes;
    
    public function __construct($assemblyFile, $includePaths, $excludePaths = array())
    {
        $this->assemblyFile = $assemblyFile;
        $this->includePaths = $includePaths;
        $this->excludePaths = $excludePaths;
        $this->classes = array();
    }
    public function generate($ROOT_CLASS_PATH = "")
    {
        if(! empty($this->includePaths)) {
            foreach ( $this->includePaths as $path ) {
                $files = $this->searchFiles($path);
                $this->searchClasses($files);
            }
            $this->writeAssemblyFile($ROOT_CLASS_PATH);
        }
    }
    private function writeAssemblyFile($ROOT_CLASS_PATH = "")
    {
        $fileContent = "<?php\n";
        $fileContent .= "    function mini_autoload(\$classname) {\n";
        $fileContent .= "        \$classname = strtolower(\$classname);\n";
        $fileContent .= "        static \$classpath = array(\n";
        if(empty($ROOT_CLASS_PATH)) {
            foreach ( $this->classes as $key => $value ) {
                $fileContent .= "          '$key' => '$value',\n";
            }
        } else {
            $prefix = $ROOT_CLASS_PATH . "/";
            foreach ( $this->classes as $key => $value ) {
                $value = substr_replace($value, "", 0, strlen($prefix));
                $fileContent .= "          '$key' => '$value',\n";
            }
        }
        $fileContent .= "        );\n";
        $fileContent .= "        if (!empty(\$classpath[\$classname])) {\n";
        if(empty($ROOT_CLASS_PATH)) {
            $fileContent .= "            include_once(\$classpath[\$classname]);\n";
        } else {
            $fileContent .= "            include_once('$ROOT_CLASS_PATH'.'/'.\$classpath[\$classname]);\n";
        }
        $fileContent .= "        }\n";
        $fileContent .= "    }\n";
        $fileContent .= "?>";
        file_put_contents($this->assemblyFile, $fileContent);
        // echo $fileContent;
    // echo "\n generator {$this->assemblyFile} successed!\n";
    }
    private function searchFiles($path)
    {
        if(!file_exists($path))
        {
            throw new Exception("$path not exists");
        }
        $filelist = array();
        foreach ( scandir($path) as $file ) {
            if($file == '.' || $file == '..')
                continue;
            
            $file = "$path/$file";
            if(is_dir($file) && array_search($file, $this->excludePaths) === false) {
                $filelist = array_merge($filelist, $this->searchFiles($file));
                continue;
            }
            
            if(preg_match("/.+\.php$/", $file))
                $filelist[] = $file;
        }
        return $filelist;
    }
    private function searchClasses($files)
    {
        $regexs = array("/^\s*class\s+(\S+)\s*/", "/^\s*abstract\s*class\s+(\S+)\s*/", "/^\s*interface\s+(\S+)\s*/");
        foreach ( $files as $file ) {
            $lines = file($file);
            foreach ( $lines as $line ) {
                foreach ( $regexs as $regex ) {
                    if(preg_match($regex, $line, $match)) {
                        $class = $match[1];
                        $this->insertClass($class, $file);
                    }
                }
            }
        }
    }
    private function insertClass($class, $file)
    {
        $class = strtolower($class);
        if(isset($this->classes[$class]) == false) {
            $this->classes[$class] = $file;
        } else {
            throw new Exception("Repeatedly Class $class => $file\n");
        }
    }
}
?>