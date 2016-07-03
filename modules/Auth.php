<?php

//MODULE Auth (login and authentication)

class Auth
{
    //tries login from post parameters
    public static function login() 
    {
        //check parameters
        if (!isset($_POST['username']) || !isset($_POST['password']))
            Errors::send(400, "Required username and password POST params");
        
        $users = Users::loadUsers(); //loads users data

        //checks if finds user
        foreach($users as $type => $usergroup)
            
            //check if user exists
            if(isset($usergroup[$_POST['username']])) 

                //checks password
                if ($usergroup[$_POST['username']] == $_POST['password']) 
                {
                    //saves username and usertype
                    $_SESSION['username'] = $_POST['username'];
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