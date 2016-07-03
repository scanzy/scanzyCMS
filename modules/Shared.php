<?php

require_once __DIR__.'/ConfigLoad.php';

//--------------------------------------------------------------------------------------------
//CONNECTION

//if not already connected, connects to database, returning the pdo object
function connect()
{
    //returns previous connection if already connected
    if (isset($GLOBALS['scanzycms-conn'])) return $GLOBALS['scanzycms-conn'];

    //reads configuration from config.ini if needed
    $conf = loadConfig();

    //connects to database
    $GLOBALS['scanzycms-conn'] = new PDO("mysql:". 
        "host=".$conf['DB']['host'].(isset($conf['DB']['port']) ? $conf['DB']['port'] : "")
        .";dbname=".$conf['DB']['name'].";charset=utf8", 
        $conf['DB']['user'], $conf['DB']['pwd']);

    $GLOBALS['scanzycms-conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $GLOBALS['scanzycms-conn'];      
}

//-----------------------------------------------------------------------------------------------
//OUTPUT

//redirects to some page
function redirect($url){ echo "<script>window.location = '".$url."'</script>"; exit(); }

//sends json to client
function sendJSON($obj)
{
    header("Content-Type: application/json");
    echo json_encode($obj);
    exit();
}

?>