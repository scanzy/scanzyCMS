<?php

//includes misc functions (db connection, conf loading, ecc)
require_once '../modules/Shared.php';
require_once '../modules/Errors.php';
require_once '../modules/DBrequests.php';
require_once '../modules/Users.php';
require_once '../modules/ConfigSave.php';

//-------------------------------------------------------------------------------------------
//AJAX MODE

//if ajax request (perform action)
if (isset($_REQUEST['action']))
{
    Errors::setModeAjax();

    //saves action
    $action = $_REQUEST['action'];

    //check if login/logout action
    if ($action == "login") login();
    if ($action == "logout") { session_destroy(); exit(); }

    //every action requires login, if no login sends 401 error
    if (!alreadyLogged()) Errors::send(401);

    //checks request
    if (!isset($_REQUEST['request'])) Errors::send(400, "Use parameter 'request'");

    //saves request
    $request = $_REQUEST['request'];

    //setup/test/reset database
    if ($_REQUEST['request'] == "db") if (!isAdmin()) Errors::send(403, "Only Admins can setup/test/reset database"); else 
    { 
        if ($action == "setup") db_setup();
        if ($action == "test") db_test();
        if ($action == "reset") db_reset();
    }

    //config or users requests, if not admin, sends 403 error
    if ($request == "config") if (!isAdmin()) Errors::send(403, "Only Admins can view/edit configuration"); else configRequest();
    if ($request == "users") if (!isAdmin()) Errors::send(403, "Only Admins can view/edit users"); else usersRequest();

    processDBAction($action, $request); //now processes db actions
    exit();
}

//----------------------------------------------------------------------------------------------
//HTML MODE

//sets error handler
Errors::setModeHtml();

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
        <link rel="stylesheet" type="text/css" href="bootstrap-ex.css" />
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
                <div class="col-lg-4 col-lg-offset-4 col-sm-6 col-sm-offset-3">
                    <form id="db-conn" class="box" role="form" autocomplete="off">
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
                            <input type="password" class="form-control" id="dbpwd" autocomplete="off">
                        </div>                       
                        <div id="db-msgs" class="progress">
                            <div id="db-load-error" class="progress-bar progress-bar-danger fill hidden">
                                <span>Error while reading configuration</span>
                                <a onclick="resetForm(); return false;">Retry</a>
                            </div>
                            <div id="db-load" class="progress-bar progress-bar-info progress-bar-striped active fill"><span>Loading configuration...</span></div>
                            <div id="db-saving" class="progress-bar progress-bar-info progress-bar-striped active fill hidden"><span>Saving configuration...</span></div>
                            <div id="db-save-error" class="progress-bar progress-bar-danger fill hidden"><span>Error while saving new configuration</span></div>
                            <div id="db-save-ok" class="progress-bar progress-bar-success fill hidden"><span>New configuration saved</span></div>                   
                        </div>
                        <div class="right">
                            <button id="db-test" class="btn btn-info left db-test">Test connection</button>
                            <button id="db-testing" class="btn btn-info left disabled hidden">Testing...</button>
                            <button id="db-test-ok" class="btn btn-success left hidden db-test" data-toggle="tooltip" data-placement="bottom" title="Click to test again">Connection OK</button>
                            <button id="db-test-error" class="btn btn-danger left hidden db-test" data-toggle="tooltip" data-placement="bottom" title="Click to test again">Connection error</button>
                            <button id="db-save" class="btn btn-success disabled">Save</button>
                            <button id="db-cancel" class="btn btn-default disabled">Cancel</button>    
                        </div>             
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
