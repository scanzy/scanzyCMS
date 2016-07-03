<?php

//MODULE Config (configuration read/write)

class Config extends INIhelper
{
    public static $INI_FILE = __DIR__."/../config/config.ini";
    public static $VAR_NAME = "scanzycms-config";

    //ini data prototype
    protected static $proto = array('DB' => array('host', 'name', 'user', 'pwd'), array('Macro' => array('prefix', 'suffix')));

    //used to store last db modification time
    public static function lastMod() { filemtime(self::$INI_FILE); }

    //called to touch config file (so we know last modification)
    public static function touch() { touch(self::$INI_FILE); }

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
