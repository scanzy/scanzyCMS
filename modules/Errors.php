<?php
    
//MODULE Errors (errors and exceptions handling)

class Errors
{
    //HTML MODE

    const ERROR_404_PAGE = "../errors/404.html"; //404 error page here
    const ERROR_500_PAGE = "../errors/500.html"; //500 error page here

    //sends error page
    public static function page404()
    {     
        self::sendHeader(404); //header
        @readfile(__DIR__."\\".self::ERROR_404_PAGE);  //displays error page  
        die();
    }

    //sends error page
    public static function page500()
    {
        self::sendHeader(500); //header  
        @readfile(__DIR__."\\".self::ERROR_500_PAGE); //displays error page               
        die();
    }

    //sets error mode (handlers)
    public static function setModeHtml() 
    { 
        ini_set('display_errors', 0); //hides errors 
        ini_set('log_errors', 1); //but logs them

        set_error_handler("Errors::errorHandlerHtml"); //errors
        set_exception_handler("Errors::exceptionHandlerHtml"); //exceptions
    }

    //error handler
    public static function errorHandlerHtml($code, $msg, $file, $line)
    {
        error_log("ERROR $code in file '$file' at line $line: $msg"); //logs error
        self::page500(); //sends page
    }
 
    //exception handler
    public static function exceptionHandlerHtml($ex)
    {
        error_log("EXCEPTION ".$ex->GetCode()." in file '".$ex->GetFile()."' at line ".$ex->GetLine().": ".$ex->GetMessage()); //logs exception
        self::page500(); //sends page
    } 

    //---------------------------------------------------------------------------------------------
    //AJAX MODE
    
    //error handler
    public static function errorHandlerAjax($code, $msg, $file, $line)
    { 
        error_log("ERROR $code in file '$file' at line $line: $msg");
        self::send(500, $msg);
    }
 
    //exception handler
    public static function exceptionHandlerAjax($ex)
    { 
        error_log("EXCEPTION ".$ex->GetCode()." in file '".$ex->GetFile()."' at line ".$ex->GetLine().": ".$ex->GetMessage());
        self::send(500, $ex->GetMessage());
    } 

    //ajax mode
    public static function send($code, $msg = "", $showmsg = FALSE)
    { 
        self::sendHeader($code);
        if ($msg != "") echo $msg; //shows message if any
        die();
    }    

    //sets error mode (handlers)
    public static function setModeAjax() 
    { 
        ini_set('display_errors', 0); //hides errors 
        ini_set('log_errors', 1); //but logs them

        set_error_handler("Errors::errorHandlerAjax"); //errors
        set_exception_handler("Errors::exceptionHandlerAjax"); //exceptions
    }

    //----------------------------------------------------------------------------------
    //GLOBAL

    //sends error header
    public static function sendHeader($code)
    {
        $codenames = array(
            400 => "Bad Request",
            401 => "Unauthorized",
            403 => "Forbidden",
            404 => "Page Not Found",
            500 => "Internal Server Error"
        );

        //sends header with error code
        header($_SERVER['SERVER_PROTOCOL']." ".$code." ".$codenames[$code], TRUE, $code); 
    }   
}
 ?>