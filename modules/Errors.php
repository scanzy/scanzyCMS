<?php
    
//ERROR HANDLING MODULE

define("ERROR_404_PAGE", "../errors/404.html"); //404 error page here
define("ERROR_500_PAGE", "../errors/500.html"); //500 error page here

//ajax mode
function errorSend($code, $msg = "", $showmsg = FALSE)
{ 
    sendHeader($code);
    if ($showmsg == TRUE) echo $msg; //shows message if configured
    if ($msg != "") error_log($msg); //logs error if message specified
    die();
}

//sends error header
function sendHeader($code)
{
    $codenames = array(
        400 => "Bad Request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Page Not Found",
        500 => "Internal Server Error"
    );

    header($_SERVER['SERVER_PROTOCOL']." ".$code." ".$codenames[$code], TRUE, $code); //sends header with error code
}

//sends error page
function error404page()
{     
    sendHeader(404); //header
    @readfile(__DIR__."\\".ERROR_404_PAGE);  //displays error page  
    die();
}

//sends error page
function error500page()
{
    sendHeader(500); //header  
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
 ?>