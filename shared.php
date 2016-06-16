<?php
    
//used to store public functions used by various other php files
//use: require_once './shared.php' or require_once '../shared.php' (depending on your php file directory)

define("CONFIG_FILE", "config.ini");
define("USERS_FILE", "users.ini");

//----------------------------------------------------------------------------------------------
//ERROR HANDLING

define("ERR_MODE_AJAX", 0); //sends error using plain text (default)
define("ERR_MODE_HTML", 1); //sends error displaying error page

//sets or handle error mode
function setErrMode($mode) { $GLOBALS['scanzycms-errmode'] = $mode; }
function getErrMode() { return (isset($GLOBALS['scanzycms-errmode']) ? $GLOBALS['scanzycms-errmode'] : ERR_MODE_AJAX ); }

//sends an error response
function die2($code, $msg = "")
{
    //sends header with error
    $codenames = array(400 => "Bad Request", 401 => "Unauthorized", 500 => "Internal Server Error");
    header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$codenames[$code], TRUE, $code);

    switch (getErrMode()) 
    {
        case ERR_MODE_HTML: //html handler (html page)
            if (isset($_SESSION['scanzycms-config']['Errors'][$code]))
            {
                setErrMode(ERR_MODE_AJAX); //to prevent infinite loop on errors
                header("X-error-msg: ".$msg); //send message as header
                echofile($_SESSION['scanzycms-config']['Errors'][$code]);                
            }      
            break;    
        
        case ERR_MODE_AJAX: default: die($msg); break; //ajax handler only text 
    }
    exit();
}

//------------------------------------------------------------------------------------------------
//CONFIGURATION

//loads configuration in $_SESSION['scanzycms-config'] reading from config.ini
function loadConfig()
{
    session_start();
    if (!isset($_SESSION['scanzycms-config'])) 
    {
        $_SESSION['scanzycms-config'] = parse_ini_file(__DIR__.'\\'.ONFIG_FILE, TRUE, INI_SCANNER_TYPED); //gets data
        if ($_SESSION['scanzycms-config'] == FALSE) die2(500, "Error while parsing configuration");
    }
    return $_SESSION['scanzycms-config'];
}

//writes configuration in $_SESSION['scanzycms-config'] to config.ini file
function setConfig()
{
    session_start();
    if(write_ini_file(__DIR__.'\\'.CONFIG_FILE, $_SESSION['scanzycms-config'], TRUE, INI_SCANNER_TYPED) == FALSE)
        die2("Error while saving configuration");
}

//writes ini file (returns FALSE on fail)
function write_ini_file($path, $data, $usesections = FALSE, $mode = INI_SCANNER_NORMAL)
{
    $f = fopen($path, "w"); //opens file
    if ($f == FALSE) return FALSE;

    if ($usesections)
        foreach($data as $secname => $section)
        {
            fwrite($f, "[".$secname."]".PHP_EOL); //writes section head
            foreach($section as $name => $value) //for each entry
            fwrite($f, write_ini_entry($name, $value, $mode)); //writes it in ini file
            fwrite($f, PHP_EOL); // \n or \r\n
        }
    else
    {
        foreach($data as $name => $value) //for each entry
        fwrite($f, write_ini_entry($name, $value, $mode)); //writes it in ini file
    }

    fclose($f); //closes file
    return TRUE;
}

//returns an ini file line from name and value
function write_ini_entry($name, $value, $mode = INI_SCANNER_NORMAL)
{
    switch($mode)
    {
        case INI_SCANNED_TYPED: //checks special values
            if ($value === TRUE) return $name." = true".PHP_EOL;
            if ($value === FALSE) return $name." = false".PHP_EOL;
            if ($value === NULL) return $name." = null".PHP_EOL;
            if (is_numeric($value)) return $name." = ".$value.PHP_EOL;            
            break;

        case INI_SCANNER_NORMAL: default: break;         
    }
    //default (quotes)
    return $name." = \"".$value."\"".PHP_EOL;
}

//--------------------------------------------------------------------------------------------
//CONNECTION

//if not already connected, connects to database, returning the pdo object
function connect()
{
    //returns previous connection if already connected
    if (isset($GLOBALS['scanzycms-conn'])) return $GLOBALS['scanzycms-conn'];

    //reads configuration from config.ini if needed
    loadConfig();

    //connects to database
    $GLOBALS['scanzycms-conn'] = new PDO("mysql:host=".$_SESSION['scanzycms-config']['DB']['host'].";dbname=".
        $_SESSION['scanzycms-config']['DB']['name'], 
        $_SESSION['scanzycms-config']['DB']['user'],
        $_SESSION['scanzycms-config']['DB']['pwd']);

    $GLOBALS['scanzycms-conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $GLOBALS['scanzycms-conn'];      
}

//used to store last db modification time
define("LAST_MOD", filemtime(__DIR__.'\\'.CONFIG_FILE));

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

//----------------------------------------------------------------------------------------------
//AUTHENTICATION

//checks if there was login
function alreadyLogged()
{  
    //if no data from session
    session_start();
    return isset($_SESSION['username']);    
}

?>
