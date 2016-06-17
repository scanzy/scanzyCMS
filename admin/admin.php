<?php

//includes misc functions (db connection, conf loading, ecc)
require_once '../shared.php';
require_once './dbhelper.php';

//----------------------------------------------------------------------------------------------
//AUTHENTICATION

//checks if there was login
function alreadyLogged()
{  
    //if no data from session
    return isset($_SESSION['username']);    
}

//isAdmin
function isAdmin() { return ($_SESSION['usergroup'] == "Admins"); }

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

//-------------------------------------------------------------------------------------------
//AJAX MODE

//if ajax request (perform action)
if (isset($_REQUEST['action']))
{
    //sets error handler
    setErrMode(ERR_MODE_AJAX);

    //check if login action
    if ($_REQUEST['action'] == "login")
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

    //every action requires login, if no login sends 401 error
    if (!alreadyLogged()) die2(401, "Login required");

    //sets callbacks
    $dberrorcallback = function($msg) { die2(500, $msg); };
    $paramserrorcallback = function($msg) { die2(400, $msg); };

    //checks setup/logout action
    if ($_REQUEST['action'] == "setup") db_setup($dberrorcallback);
    if ($_REQUEST['action'] == "logout") { session_destroy(); exit(); }

    //checks config/user actions
    if ($_REQUEST['request'] == "config" || $_REQUEST['request'] == "user")
    {
        //if not admin, sends 403 error
        if (!isAdmin()) die2(403, "Only Admins can view/edit configuration or users");

        //TODO: ini helper

        exit();
    }

    //gets helper object
    $helper = getHelper($_REQUEST['request'], $paramserrorcallback, $dberrorcallback);
    
    //selects action type
    switch($_REQUEST['action'])
    {
        //reads db
        case "get": sendJSON($helper->getItems2()); break;

        //modifies db (updating last modified info touching file)
        case "new": $helper->newItem2(); touch(FILE_TOUCH); break;
        case "edit": $helper->editItem2(); touch(FILE_TOUCH); break;

        //deletes item and related elements
        case "del": $helper->delItem2(); touch(FILE_TOUCH);

            //deletes related items
            switch($_REQUEST['request'])
            {
                case "content": 
                    
                    //deletes files with that content id
                    DBhelper::delItem("Files", array("id" => "ContentId"), array(), $paramserrorcallback, $dberrorcallback);

                    //deletes substitutions for that content and of that content
                    DBhelper::delItem("Substitutions", array("id" => "SearchId"), array(), $paramserrorcallback, $dberrorcallback);
                    DBhelper::delItem("Substitutions", array("id" => "ReplaceId"), array(), $paramserrorcallback, $dberrorcallback);

                    //deletes contenttags and macrotags
                    DBhelper::delItem("ContentTags", array("id" => "ContentId"), array(), $paramserrorcallback, $dberrorcallback);
                    DBhelper::delItem("MacroTags", array("id" => "ContentId"), array(), $paramserrorcallback, $dberrorcallback);

                    break;

                case "tag": 
                    
                    //deletes contenttags and macrotags
                    DBhelper::delItem("ContentTags", array("id" => "TagId"), array(), $paramserrorcallback, $dberrorcallback);
                    DBhelper::delItem("MacroTags", array("id" => "TagId"), array(), $paramserrorcallback, $dberrorcallback);
                    
                    break;
            }
            break;
        
        //error
        default: die2(400, "Unknown action"); break;
    }

    exit();
}

//sends json to client
function sendJSON($obj)
{
    header("Content-Type: application/json");
    echo json_encode($obj);
    exit();
}

//----------------------------------------------------------------------------------------------
//HTML MODE

//sets error handler
setErrMode(ERR_MODE_HTML);

//redirects to login page if no login 
if (!alreadyLogged()) redirect("./login.html");

//default page
if (!isset($_REQUEST['url'])) $_REQUEST['url'] = "";
if ($_REQUEST['url'] == "") redirect("./dashboard");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>scanzyCMS - Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <script src="translate.js"></script>
        <script src="shake.js"></script>       
    </head>
    <body>

        <nav id="topbar" class="navbar-default noselect">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#topbarcontent">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="topbarcontent">
                <ul class="nav">
                    <li><a href="dashboard"><span>Dashboard</span></a></li>
                    </li><li><a href="files">Files</a>
                    </li><li><a href="templates">Templates</a>
                    </li><li><a href="settings">Settings</a>
                    </li><li class="right"><a href="#" id="logout">Logout</a>
                    </li><li class="right"><a href="help">Help</a>
                    </li>
                </ul>
            </div>
        </nav>

        <div id="header" class="center container noselect">
            <h1 class="inline">scanzyCMS</h1>
            <h3 class="inline">admin</h3>
            <div class="inline">
                <!--img width="220" src="logo.png" /-->
            </div>
        </div>

        <?php switch ($_REQUEST['url']) { case "dashboard": ?>
        <div class="container page">
            <div class="box title"><h1>Dashboard</h1></div>

            <div class="box">
                <h2>Heading</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas et ipsum sed dolor vehicula congue quis egestas risus. Cras tortor felis, convallis eget euismod et, varius nec sapien. Pellentesque quis augue sit amet justo faucibus condimentum id non metus. Nullam maximus molestie ex sit amet sagittis. Vivamus non neque tellus. Phasellus tincidunt tellus sit amet nulla pellentesque feugiat. In augue metus, dignissim at egestas vel, ornare sed nibh. Aliquam elementum, purus ut consectetur accumsan, ex nibh efficitur orci, quis maximus augue elit quis libero. Vestibulum feugiat, purus id volutpat sodales, lacus arcu varius neque, ut rutrum mauris magna a leo. </p>                
            </div>
        </div>

        <?php break; case "files": ?>
        <div class="container page">
            <div class="box title"><h1>Files</h1></div>

            <div class="box"><div id="files-list"></div></div>
        </div>

        <?php break; case "templates": ?>
        <div class="container page">
            <div class="box title"><h1>Templates</h1></div>

            <div class="box"><div id="contents-list"></div></div>
        </div>

        <?php break; case "settings": ?>
        <div class="container page">
            <div class="box title"><h1>Settings</h1></div>
            
            <div class="row noselect">
                <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                    <form id="db-conn" class="box" role="form">
                        <h3 class="title" style="color: #aaa">Database connection</h3>
                        <div class="line"></div>                    
                        <div class="form-group">
                            <label for="host">Host:</label>
                            <input type="text" class="form-control" id="dbhost">
                        </div>
                        <div class="form-group">
                            <label for="name">Database name:</label>
                            <input type="text" class="form-control" id="dbname">
                        </div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="dbuser">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" id="dbpwd">
                        </div>
                        <p id="db-msgs" class="center" style="height: 2em">
                            <span id="db-test-error" class="label label-danger hidden">Connection failed</span>
                            <span id="db-test-ok" class="label label-info hidden">Connection OK</span>
                            <span id="db-load-error" class="hidden">
                                <span class="label label-danger">Error while reading configuration</span>
                                <a href="#" onclick="resetForm(); return false;"><span class="label label-info">Retry</span></a>
                            </span>
                            <span id="db-save-error" class="label label-danger hidden">Error while saving new configuration</span>
                            <span id="db-save-ok" class="label label-success hidden">New configuration saved</span>
                        </p>
                        <button id="db-test" class="btn btn-info">Test connection</button>
                        <button id="db-save" class="btn btn-success disabled">Save</button>
                        <button id="db-cancel" class="btn btn-default disabled">Cancel</button>                 
                    </form>
                </div>
            </div>
        </div>

        <?php break; default: ?><script>window.location.href = "./";</script><?php echo "</body></html>"; exit(); break; } ?>

        <div id="footer">
            <span>Powered by <b>ScanzySoftware</b></span>
        </div>  

        <script src="messages.js"></script>
        <script src="confirm.js"></script>
        <script src="scanzytable.js"></script>
        <script src="shared.js"></script>
        
        <script src="pages-<?php echo $_REQUEST['url']; ?>.js"></script>
    </body>
</html>
