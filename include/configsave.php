<?php

require_once __DIR__.'/iniwritecore.php';

//------------------------------------------------------------------------------------------------
//CONFIGURATION SAVE

//writes configuration in $_SESSION['scanzycms-config'] to config.ini file
function setConfig()
{
    if(write_ini_file(CONFIG_FILE, $_SESSION['scanzycms-config'], TRUE, INI_SCANNER_TYPED) == FALSE)
        die2("Error while saving configuration");
}

?>
