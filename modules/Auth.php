<?php

//MODULE Auth (login and authentication)

class Auth
{
    //user types
    const ADMIN = 0;
    const DESIGNER = 1;
    const WRITER = 2;

    //and privilege levels
    public static $userlevels = array(
        "Admins" => self::ADMIN,
        "Designers" => self::DESIGNER,
        "Writers" => self::WRITER
    );

    //tries login from post parameters
    public static function login() 
    {
        //check parameters
        $username = Params::requiredString('username');
        $password = Params::requiredString('password');
        
        $users = Users::load(); //loads users data

        //checks if finds user
        foreach($users as $type => $usergroup)
            
            //check if user exists
            if(isset($usergroup[$username])) 

                //checks password
                if ($usergroup[$username] == $password) 
                {
                    //saves username, usertype and level
                    $_SESSION['username'] = $username;
                    $_SESSION['usergroup'] = $type;
                    $_SESSION['userlevel'] = self::$userlevels[$type];

                    echo "true"; //success!
                    exit();
                }

        echo "false"; //login failed
        exit();
    }

    //checks if there was login
    public static function isLogged() { return isset($_SESSION['username']); } //checks data from session

    //checks if current user has privileges of that certain user (has lower or equal level)
    public static function requireLevel($level) 
    { 
        $code = 401; //sends 401 if no login
        if (self::isLogged()) 
        {
            $code = 403; //sends 403 if no required level
            if ($level >= $_SESSION['userlevel']) return;             
        }
        Errors::send($code, "Required user level $level (".array_search($level, self::$userlevels).")"); 
    }
}