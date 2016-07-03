<?php

require_once __DIR__.'/configload.php';

//--------------------------------------------------------------------------------------------
//ERROR HANDLING

define("ERROR_404_PAGE", "../errors/404.html"); //404 error page here
define("ERROR_500_PAGE", "../errors/500.html"); //500 error page here

//ajax mode
function errorSend($code, $msg = "", $showmsg = FALSE)
{ 
    $codenames = array(
        400 => "Bad Request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Page Not Found",
        500 => "Internal Server Error"
    );

    header($_SERVER['SERVER_PROTOCOL']." ".$code." ".$codenames[$code], TRUE, $code); //sends header with error code
    if ($showmsg == TRUE) echo $msg; //shows message if configured
    if ($msg != "") error_log($msg); //logs error if message specified
    die();
}

//sends error page
function error404page()
{     
    header($_SERVER['SERVER_PROTOCOL']." 404 Page Not Found", TRUE, 404); //header
    @readfile(__DIR__."\\".ERROR_404_PAGE);  //displays error page  
    die();
}

//sends error page
function error500page()
{
    header($_SERVER['SERVER_PROTOCOL']." 500 Internal Server Error", TRUE, 500); //header  
    @readfile(__DIR__."\\".ERROR_500_PAGE); //displays error page               
    die();
}

//error handler
function errorHandlerHtml($code, $msg, $file, $line)
{
    error_log("ERROR $code in file '$file' at line $line: $msg"); //logs error
    error500page(); //sends page
}
 
//exception handler
function exceptionHandlerHtml($ex)
{
    error_log("EXCEPTION ".$ex->GetCode()." in file '".$ex->GetFile()."' at line ".$ex->GetLine().": ".$ex->GetMessage()); //logs exception
    error500page(); //sends page
}   

//sets error mode (handlers)
function setErrModeHtml() 
{ 
    ini_set('display_errors', 0); //hides errors 
    ini_set('log_errors', 1); //but logs them

    set_error_handler("errorHandlerHtml"); //errors
    set_exception_handler("exceptionHandlerHtml"); //exceptions
}

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