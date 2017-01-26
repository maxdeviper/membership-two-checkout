<?php

/**
 * @author      Hedonmark 
 * @copyright   2010
 * @package     Careers Club
 * @filesource  constants.php
 */


/**
 * An error level that forces full stack trace of the exception
 * */
 define('ERR_LEVEL_FULL', '5');
    
/**
 * An error level that forces the display of messages useful for debugging purposes
 * */
 define('ERR_LEVEL_DEBUG', '3');
    
/**
 * An error level that ensures only the user friendly error message will be displayed
 * */
define('ERR_LEVEL_MINIMAL', '1');
    
/**
 * An error level that ensures no error message will be displayed
 * */
define('ERR_LEVEL_SILENT', '0');

/**
 * The global error level to be used throughout the application
 * */
define('GLOB_ERR_LEVEL', ERR_LEVEL_FULL);


define("DB_SERVER", "mysql");
define("DB_PREFIX", "");

?>