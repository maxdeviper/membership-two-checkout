<?php

/**
 * @author      ISES Solutions
 * @copyright   2011
 * @filesource  startup_inc.php
 * @description This file MUST BE INCLUDED at the start of every view page to use the constant
 */

if(!defined("STARTUP_INC")){
    @session_start();
    $currentDir = __DIR__;
//    echo "current dir:".$currentDir."<br />";
    $seperator = "";
    if(strpos($currentDir, '\\') > 0){
        $seperator = '\\';
    }
    else{
        $seperator = '/';
    }
    // get path to the startup_inc file folder
    if(function_exists("plugin_dir_path")){
        $currentDir = substr($currentDir,0,  strripos($currentDir,$seperator));
    }
    #define('M2_APP_BASE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/m2_module'); // Development server
    define('M2_APP_BASE_DIR', $currentDir);  // Production server
    #define('APP_HOME_BASE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/dev/avante'); // Production server
    define('SITE_DOMAIN', 'http://'.$_SERVER["HTTP_HOST"]);
    define('SITE_ROOT', SITE_DOMAIN.'/m2_module');  // testing server

//    echo "current dir:".$currentDir."<br />";
    if(!function_exists("plugin_dir_path")){
//        echo "function does not exist<br />";
        define("M2_APP_CLASSES_PATH_ISESS", M2_APP_BASE_DIR . "/isess/classes/");
        define("M2_APP_CLASSES_PATH_APP", M2_APP_BASE_DIR . "/dataexchange/");
        define("M2_CLASSES_PATH_3RDPARTY", M2_APP_BASE_DIR . "/isess/3rdParty/");
    }
    else{
        define("M2_APP_CLASSES_PATH_ISESS", M2_APP_BASE_DIR . "/server/isess/classes/");
        define("M2_APP_CLASSES_PATH_APP", M2_APP_BASE_DIR . "/server/dataexchange/");
        define("M2_CLASSES_PATH_3RDPARTY", M2_APP_BASE_DIR . "/server/isess/3rdParty/");
    }

    function strip_ns_interface($class,$interface){
        return str_replace($interface."\\", '', $class);
    }
    function __autoload_m2($class)
    {
        // check isess class paths
        if (file_exists(M2_APP_CLASSES_PATH_ISESS.'interfaces/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'interfaces/'.$class.'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'epayment/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'epayment/'.$class.'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'epayment/'.strip_ns_interface($class,'epayment').'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'epayment/'.strip_ns_interface($class,'epayment').'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'epayment/interfaces/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'epayment/interfaces/'.$class.'.php');
            return;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'epayment/interfaces/'.strip_ns_interface($class,'epayment').'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'epayment/interfaces/'.strip_ns_interface($class,'epayment').'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'epayment/merchants/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'epayment/merchants/'.$class.'.php');
            return;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'epayment/merchants/'.strip_ns_interface($class,'epayment').'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'epayment/merchants/'.strip_ns_interface($class,'epayment').'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'epayment/merchants/2checkout/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'epayment/merchants/2checkout/'.$class.'.php');
            return;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'epayment/merchants/2checkout/'.strip_ns_interface($class,'epayment').'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'epayment/merchants/2checkout/'.strip_ns_interface($class,'epayment').'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'mail/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'mail/'.$class.'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'util/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'util/'.$class.'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'util/'.strip_ns_interface($class,'isess').'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'util/'.strip_ns_interface($class,'isess').'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'util/'.strip_ns_interface($class,'dataexchange').'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'util/'.strip_ns_interface($class,'dataexchange').'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'util/interfaces/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'util/interfaces/'.$class.'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'util/interfaces/'.strip_ns_interface($class,'isess').'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'util/interfaces/'.strip_ns_interface($class,'isess').'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_ISESS.'util/interfaces/'.strip_ns_interface($class,'epayment').'.php')) {
            require_once (M2_APP_CLASSES_PATH_ISESS.'util/interfaces/'.strip_ns_interface($class,'epayment').'.php');
            return true;
        }
        else if (file_exists(M2_APP_CLASSES_PATH_APP.'/'.$class.'.php')) {
            require_once (M2_APP_CLASSES_PATH_APP.'/'.$class.'.php');
            return true;
        }
        else if (file_exists(M2_CLASSES_PATH_3RDPARTY.'dbclass/'.$class.'.php')) {
            require_once (M2_CLASSES_PATH_3RDPARTY.'dbclass/'.$class.'.php');
            return true;
        }
        else if (file_exists(M2_CLASSES_PATH_3RDPARTY.'mail/'.$class.'.php')) {
            require_once (M2_CLASSES_PATH_3RDPARTY.'mail/'.$class.'.php');
            return true;
        }
    }
    
    spl_autoload_register('__autoload_m2');
    define("STARTUP_INC",true);
}
?>
