<?php
    
//MODULE INIhelper (used to easily manage ini files)

class INIhelper
{
    public static $INI_FILE = __DIR__."";
    public static $VAR_NAME = ""; 

    //ini data prototype
    protected static $proto = array();

    //loads configuration in $_SESSION['scanzycms-config'] reading from config.ini
    public static function load()
    {
        $_SESSION[static::$VAR_NAME] = parse_ini_file(static::$INI_FILE, TRUE, INI_SCANNER_TYPED); //gets data
        if ($_SESSION[static::$VAR_NAME] == FALSE) 
        {
            unset($_SESSION[static::$VAR_NAME]); //ON ERROR
            Errors::send(500, "Error while parsing ini file");            
        }
    }

    //gets configuration (using session cache if possible)
    public static function get()
    {
        //loads config if needed
        if (!isset($_SESSION[static::$VAR_NAME])) static::load();
        return $_SESSION[static::$VAR_NAME];
    }

    //writes configuration in $_SESSION[VAR_NAME] to INI_FILE
    public static function save()
    {
        if(INIcore::write_ini_file(self::$INI_FILE, $_SESSION[static::$VAR_NAME], TRUE, INI_SCANNER_TYPED) == FALSE)
            Errors::send(500, "Error while saving to ini file");
    }

    //updates config using request params
    public static function update()
    {
        if (INIcore::write_from_request(static::$INI_FILE, //updates config
            static::$proto, static::get(), TRUE) == FALSE) 
                Errors::send(500, "Error while saving ini file"); //if error

        static::load(); //reloads saved data
    }
}