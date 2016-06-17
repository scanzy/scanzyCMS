<?php

require_once __DIR__.'/iniwritecore.php';

define("USERS_FILE", "../config/users.ini");

//----------------------------------------------------------------------------------------------
//AUTHENTICATION

//tries login from post parameters
function login() 
{
    //check parameters
    if (!isset($_POST['username']) || !isset($_POST['password']))
        die2(400, "Required username and password params");
        
    $users = loadUsers(); //loads users data

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
function alreadyLogged()
{  
    //if no data from session
    return isset($_SESSION['username']);    
}

//isAdmin
function isAdmin() { return ($_SESSION['usergroup'] == "Admins"); }

//----------------------------------------------------------------------------------------------
//USERS DATA

//loads users in $_SESSION['scanzycms-users'] reading from users.ini
function loadUsers()
{
    $_SESSION['scanzycms-users'] = parse_ini_file(USERS_FILE, TRUE); //gets data
    if ($_SESSION['scanzycms-users'] == FALSE) 
        die2(500, "Error while parsing users data");
    return $_SESSION['scanzycms-users'];
}

//writes configuration in $_SESSION['scanzycms-users'] to users.ini file
function saveUsers()
{
    if(write_ini_file(USERS_FILE, $_SESSION['scanzycms-users'], TRUE) == FALSE)
        die2("Error while saving users data");
}

?>