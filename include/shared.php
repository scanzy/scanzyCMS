<?php

require_once __DIR__.'/configload.php';

//--------------------------------------------------------------------------------------------
//ERROR HANDLING

define("ERRORS_DIR", "errors"); //error pages are here

define("ERR_MODE_AJAX", 0); //sends error using plain text (default)
define("ERR_MODE_HTML", 1); //sends error displaying error page

//sets or handle error mode
function setErrMode($mode) { $GLOBALS['scanzycms-errmode'] = $mode; }
function getErrMode() { return (isset($GLOBALS['scanzycms-errmode']) ? $GLOBALS['scanzycms-errmode'] : ERR_MODE_AJAX ); }

//sends an error response
function die2($code, $msg = "")
{
    //sends header with error
    $codenames = array(400 => "Bad Request", 401 => "Unauthorized", 403 => "Forbidden", 500 => "Internal Server Error");
    header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$codenames[$code], TRUE, $code);

    switch (getErrMode()) 
    {
        case ERR_MODE_HTML: //html handler (html page)
            
            //only if specified in config
            $conf = loadConfig();
            if (isset($conf['Errors'])) 
                if (isset($conf[$code])) 
            {
                setErrMode(ERR_MODE_AJAX); //to prevent infinite loop on errors
                header("X-error-msg: ".$msg); //send message as header
                echofile(ERRORS_DIR."/".$conf['Errors'][$code]);                
            }      
            break;    
        
        case ERR_MODE_AJAX: default: die($msg); break; //ajax handler only text 
    }
    exit();
}

//php error handler
function errorHandler($level, $msg, $file, $line) { die2(500, "PHP error in file ".$file." at line ".$line.". Error: ".$msg); }
set_error_handler("errorHandler"); //sets error handler

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
    $GLOBALS['scanzycms-conn'] = new PDO("mysql:host=".$conf['DB']['host'].";dbname=".
        $conf['DB']['name'], 
        $conf['DB']['user'], 
        $conf['DB']['pwd']);

    $GLOBALS['scanzycms-conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $GLOBALS['scanzycms-conn'];      
}

//used to store last db modification time
define("LAST_MOD", filemtime(__DIR__.'/'.CONFIG_FILE));

//-----------------------------------------------------------------------------------------------
//OUTPUT

//outputs file contents eventually replacing text
function echofile($path, $search = "", $replace = "")
{
    $content = @file_get_contents($path); //tries to read from file
    if ($content === FALSE) die2("Error: can't read from file '".$path."'");

    if ($search == "") echo $content; //outputs content
    else echo str_ireplace($search, $replace, $content);
    exit();
}

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