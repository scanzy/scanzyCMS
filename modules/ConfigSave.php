<?php

require_once __DIR__.'/INIcore.php';

//------------------------------------------------------------------------------------------------
//CONFIGURATION SAVE

//writes configuration in $_SESSION['scanzycms-config'] to config.ini file
function setConfig()
{
    if(write_ini_file(CONFIG_FILE, $_SESSION['scanzycms-config'], TRUE, INI_SCANNER_TYPED) == FALSE)
        errorSend(500, "Error while saving configuration");
}

//called to process requests about config
function configRequest()
{
    switch($_REQUEST['action'])
    {
        //send config 
        case "get": sendJSON(loadConfig()); break;

        case "update": 
            if (write_from_request(CONFIG_FILE, //updates config
                array('DB' => array('host', 'name', 'user', 'pwd'), array('Macro' => array('prefix', 'suffix'))),
                loadConfig(), TRUE) == FALSE) 
                    errorSend(500, "Error while saving configuration"); //if error

            //deletes current config session data so it can be reloaded when needed at next request
            unset($_SESSION['scanzycms-config']);
            break;

        case "test": 

            // host test here
            if (filter_var(gethostbyname($_REQUEST['host']), FILTER_VALIDATE_IP) === FALSE) errorSend(400, "Invalid host");
                
            //tests db connection config
            $c = new PDO("mysql:host=".$_REQUEST['host'].";dbname=".
                $_REQUEST['name'], $_REQUEST['user'], $_REQUEST['pwd']);
         
            break;

        default: errorSend(400, "Unknown action"); break;
    }
    exit();
}

?>
