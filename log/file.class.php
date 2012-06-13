<?php
class mini_log_file extends mini_base_log
{
	/**
	 * @var integer maximum log file size
	 */
	private $maxFileSize=1024; // in KB
	/**
	* @var integer number of log files used for rotation
	*/
	private $maxLogFiles=5;
	/**
	 * @var string directory storing log files
	 */
	private $logPath;
	/**
	 * @var string log file name
	 */
	private $logFile='application.log';
    /**
     * get log config from user config file
     * @param mini_db_config $config
     */
	
	public function logParams($config)
	{

	    if(isset($config['path']) && !empty($config['path']))
	    {
	    	$this->logPath = $config['path'];
	    }
	    if(isset($config['file']) && !empty($config['file']))
	    {
	    	$this->logFile = $config['file'];
	    }
	    if($this->getLogPath()===null)
	    	$this->setLogPath(mini::getRunPath()."/logs");
	}
	/**
	 * @return string directory storing log files. Defaults to application runtime path.
	 */
	public function getLogPath()
	{
		return $this->logPath;
	}

	/**
	 * @param string $value directory for storing log files.
	 * @throws Exception if the path is invalid
	 */
	public function setLogPath($value)
	{
	    
		$this->logPath=realpath($value);
		
		if($this->logPath===false || !is_dir($this->logPath) || !is_writable($this->logPath))
			mini::e('logPath {logPath} does not point to a valid directory. Make sure the directory exists and is writable by the Web server process.',
			        array('{logPath}'=>$this->logPath));
	}

	/**
	 * @return string log file name. Defaults to 'application.log'.
	 */
	public function getLogFile()
	{
		return $this->logFile;
	}

	/**
	 * @param string $value log file name
	 */
	public function setLogFile($value)
	{
		$this->logFile=$value;
	}

	/**
	 * @return integer maximum log file size in kilo-bytes (KB). Defaults to 1024 (1MB).
	 */
	public function getMaxFileSize()
	{
		return $this->maxFileSize;
	}

	/**
	 * @param integer $value maximum log file size in kilo-bytes (KB).
	 */
	public function setMaxFileSize($value)
	{
		if(($this->maxFileSize=(int)$value)<1)
			$this->maxFileSize=1;
	}

	/**
	 * @return integer number of files used for rotation. Defaults to 5.
	 */
	public function getMaxLogFiles()
	{
		return $this->maxLogFiles;
	}

	/**
	 * @param integer $value number of files used for rotation.
	 */
	public function setMaxLogFiles($value)
	{
		if(($this->maxLogFiles=(int)$value)<1)
			$this->maxLogFiles=1;
	}

	/**
	 * Saves log messages in files.
	 * @param array $logs list of log messages
	 */
	
	protected  function process($logs)
	{
		$logFile=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		if(@filesize($logFile)>$this->getMaxFileSize()*1024)
			$this->rotateFiles();
		$fp=@fopen($logFile,'a');
		@flock($fp,LOCK_EX);
		foreach($logs as $log)
			@fwrite($fp,$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]));
		@flock($fp,LOCK_UN);
		@fclose($fp);
	}

	/**
	 * Rotates log files.
	 */
	protected function rotateFiles()
	{
		$file=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		$max=$this->getMaxLogFiles();
		for($i=$max;$i>0;--$i)
		{
			$rotateFile=$file.'.'.$i;
			if(is_file($rotateFile))
			{
				// suppress errors because it's possible multiple processes enter into this section
				if($i===$max)
					@unlink($rotateFile);
				else
					@rename($rotateFile,$file.'.'.($i+1));
			}
		}
		if(is_file($file))
			@rename($file,$file.'.1'); // suppress errors because it's possible multiple processes enter into this section
	}
}