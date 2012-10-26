<?php
/**
 * @author mini
 * @data	
 */


/**
 * define app HOME path
 */

defined('RUNPATH') || define('RUNPATH', dirname(__FILE__));
defined('MINI_EXCEPTION_HANDLER') || define('MINI_EXCEPTION_HANDLER',true);
defined('MINI_ERROR_HANDLER') || define('MINI_ERROR_HANDLER',true);
defined('MINI_DEBUG') || define('MINI_DEBUG', true);

/**
 * include bootstrap  file
 */
include RUNPATH."/config/bootstrap.php";

/**
 * include php miniframework import file
 */
$mini = '../mini/mini.class.php';
include $mini;
/**
 * set config path
 */
$config = RUNPATH."/config/config.xml";

mini::run(RUNPATH,$config)->assembly(RUNPATH."/config/~autoload.php")->web();
?>
