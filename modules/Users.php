<?php

//MODULE Users

class Users
{
    const USERS_FILE = "../config/users.ini";

    //loads users in $_SESSION['scanzycms-users'] reading from users.ini
    public static function load()
    {
        $_SESSION['scanzycms-users'] = parse_ini_file(self::USERS_FILE, TRUE); //gets data
        if ($_SESSION['scanzycms-users'] == FALSE) 
            Errors::send(500, "Error while parsing users data");
        return $_SESSION['scanzycms-users'];
    }

    //writes configuration in $_SESSION['scanzycms-users'] to users.ini file
    public static function saveUsers()
    {
        if(write_ini_file(self::USERS_FILE, $_SESSION['scanzycms-users'], TRUE) == FALSE)
            Errors::send("Error while saving users data");
    }

    //called to process requests about users
    public static function usersRequest()
    {
        switch($_REQUEST['action'])
        {
            case "add": break;
            case "edit": break;
            case "remove": break;
            default: Errors::send(400, "Unknown action"); break;
        }
    }
}

?>