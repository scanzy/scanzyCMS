<?php

define("DBHOST", "localhost");
define("DBNAME", "scanzycms");
define("DBUSER", "root");
define("DBPASS", "root");

define("FILE_ERROR_404", "404.html");
define("FILE_ERROR_500", "500.html");
define("FILE_TOUCH", "file.txt");

define("MACRO_PREFIX", "<macro>");
define("MACRO_SUFFIX", "</macro>");

$GLOBALS['scanzycms-users'] = array(  

    "bob" => "bob's password",
    "luigi" => "luigi's password"
    
);

//--------------------------------------------------------------------------------------------
//SHARED FUNCTIONS

//connects to database, returning the pdo object
function connect()
{
    //returns previous connection if already connected
    if (isset($GLOBALS['scanzycms-conn'])) return $GLOBALS['scanzycms-conn'];

    //connects to database
    $GLOBALS['scanzycms-conn'] = new PDO("mysql:host=".DBHOST.";dbname=".DBNAME, DBUSER, DBPASS);
    $GLOBALS['scanzycms-conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $GLOBALS['scanzycms-conn'];      
}

//outputs file contents eventually replacing text
function echofile($path, $search = "", $replace = "")
{
    $content = @file_get_contents($path); //tries to read from file
    if ($content === FALSE) die("Error: can't read from file '".$path."'");

    if ($search == "") echo $content; //outputs content
    else echo str_ireplace($search, $replace, $content);
    exit();
}

//used to store last db modification time
define("LAST_MOD", filemtime(__DIR__.'\\'.FILE_TOUCH));

//redirects to some page
function redirect($url){ echo "<script>window.location = '".$url."'</script>"; exit(); }

//checks if there was login
function alreadyLogged()
{  
    //if no data from session
    session_start();
    return isset($_SESSION['username']);    
}

//sends an error using http header
function die2($code, $msg = "")
{
    $codenames = array(400 => "Bad Request", 401 => "Unauthorized", 500 => "Internal Server Error");
    header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$codenames[$code], TRUE, $code);
    die($msg);
}

//displays error page
function die3($msg) { echofile(FILE_ERROR_500, "%error%", $msg); }