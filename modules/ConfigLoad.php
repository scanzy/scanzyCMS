<?php

spl_autoload_register(function($class) { require_once __DIR__."/$class.php"; }); //autoload other modules



const CONFIG_FILE = "../config/config.ini");

//------------------------------------------------------------------------------------------------
//CONFIGURATION LOAD

//loads configuration in $_SESSION['scanzycms-config'] reading from config.ini
function loadConfig()
{
    //gets config if needed
    if (!isset($_SESSION['scanzycms-config'])) 
    {
        $_SESSION['scanzycms-config'] = parse_ini_file(__DIR__."/".self::CONFIG_FILE, TRUE, INI_SCANNER_TYPED); //gets data
        if ($_SESSION['scanzycms-config'] == FALSE) Errors::send(500, "Error while parsing configuration");
    }
    return $_SESSION['scanzycms-config'];
}

//used to store last db modification time
define("LAST_MOD", filemtime(__DIR__.'/'.CONFIG_FILE));

//called to touch config file (so we know last modification)
function db_modified() { touch(__DIR__.'/'.CONFIG_FILE); }

?>