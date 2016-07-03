<?php

//MODULE Auth (login and authentication)

class Auth
{
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
                    //saves username and usertype
                    $_SESSION['username'] = $username;
                    $_SESSION['usergroup'] = $type;

                    echo "true"; //success!
                    exit();
                }

        echo "false"; //login failed
        exit();
    }

    //checks if there was login
    public static function isLogged() { return isset($_SESSION['username']); } //checks data from session

    //has provoleges of that user certain user
    //public static function hasAuthOf($user) { return ($_SESSION['usergroup'] <= self::users[$user]); }
}