<?php

session_start();

define("CONFIG_FILE", "../config/config.ini");

//------------------------------------------------------------------------------------------------
//CONFIGURATION LOAD

//loads configuration in $_SESSION['scanzycms-config'] reading from config.ini
function loadConfig()
{
    if (!isset($_SESSION['scanzycms-config'])) 
    {
        $_SESSION['scanzycms-config'] = parse_ini_file(__DIR__."/".CONFIG_FILE, TRUE, INI_SCANNER_TYPED); //gets data
        if ($_SESSION['scanzycms-config'] == FALSE) die2(500, "Error while parsing configuration");
    }
    return $_SESSION['scanzycms-config'];
}

?>