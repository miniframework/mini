<?php
/**
 * @author mini
 * @data	
 */
/**
 * define app HOME path
 */

@define(HOME, dirname(__FILE__));

/**
 * set config path
 */
$config = HOME."/config/config.xml";

/**
 * include php miniframework import file
 */
$mini = '../mini/mini.class.php';
include_once $mini;

/**
 * run(HOME): run miniframework
 * boot($config): load env config 
 * web():  start mvc, and web application
 */
mini::run(HOME)->boot($config)->web();
?>
