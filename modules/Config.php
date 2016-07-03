<?php

//MODULE Config (configuration read/write)

class Config
{
    const CONFIG_FILE = __DIR__."/../config/config.ini";

    //loads configuration in $_SESSION['scanzycms-config'] reading from config.ini
    public static function load()
    {
        $_SESSION['scanzycms-config'] = parse_ini_file(self::CONFIG_FILE, TRUE, INI_SCANNER_TYPED); //gets data
        if ($_SESSION['scanzycms-config'] == FALSE) 
        {
            unset($_SESSION['scanzycms-config']); //ON ERROR
            Errors::send(500, "Error while parsing configuration");            
        }
    }

    //gets configuration (using session cache if possible)
    public static function get()
    {
        //loads config if needed
        if (!isset($_SESSION['scanzycms-config'])) self::load();
        return $_SESSION['scanzycms-config'];
    }

    //writes configuration in $_SESSION['scanzycms-config'] to config.ini file
    public static function save()
    {
        if(write_ini_file(self::CONFIG_FILE, $_SESSION['scanzycms-config'], TRUE, INI_SCANNER_TYPED) == FALSE)
            Errors::send(500, "Error while saving configuration");
    }

    //used to store last db modification time
    public static function lastMod() { filemtime(self::CONFIG_FILE); }

    //called to touch config file (so we know last modification)
    public static function touch() { touch(self::CONFIG_FILE); }

    //updates config using request params
    public static function update()
    {
        if (INIcore::write_from_request(self::CONFIG_FILE, //updates config
            array('DB' => array('host', 'name', 'user', 'pwd'), array('Macro' => array('prefix', 'suffix'))),
            self::get(), TRUE) == FALSE) 
                Errors::send(500, "Error while saving configuration"); //if error

        self::load(); //reloads saved data
    }

    //test config
    public static function test($host, $name, $user, $pwd)
    {
        // host test here
        if (filter_var(gethostbyname($host), FILTER_VALIDATE_IP) === FALSE) Errors::send(400, "Invalid host '$host'");
                
        //tests db connection config
        $c = new PDO("mysql:host=$host;dbname=$name", $user, $pwd);
    }
}
?>
