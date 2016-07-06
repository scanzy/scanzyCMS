<?php

require "../autoload.php"; //starts session and autoloads classes

//sets error handler
Errors::setModeHtml();

//redirects to login page if no login 
if (!Auth::isLogged()) Shared::redirect("./login.html");

//default page
$url = Params::optionalString('url', "");
if ($url == "") Shared::redirect("./dashboard");

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

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

    <?php if (in_array($url, array('newtemplate'))) { ?>
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/codemirror.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/codemirror.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/addon/edit/closebrackets.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/addon/edit/closetag.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/addon/edit/matchtags.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/addon/fold/xml-fold.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/mode/css/css.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/mode/htmlmixed/htmlmixed.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/mode/javascript/javascript.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.16.0/mode/xml/xml.min.js"></script>
    <?php } ?>        

        <link rel="stylesheet" type="text/css" href="bootstrap-ex.css" />
        <link rel="stylesheet" type="text/css" href="style.css" />

        <script src="libs/translate.js"></script>
        <script src="libs/shake.js"></script>       
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

        <?php switch ($url) { case "dashboard": ?>
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

        <?php break; case "newfile": ?>
        <div class="container page">
            <div class="box title"><h1>New File</h1></div>

            <div id="file-new" class="box">               
                <div class="row">                      
                    <div class="col-sm-6 form-group">
                        <label for="file-url">File URL:</label>
                        <input id="file-url" type="text" class="form-control input-md" />
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="file-template">Template:</label>
                        <select id="file-template" class="selectpicker" data-width="100%">
                            <option value="null">-Select a template-</option>
                            <option value="0">Template0</option>
                        </select> 
                    </div>                    
                </div>
                
                

                <div class="line"></div>
                <div class="right">
                    <button id="file-cancel" class="btn btn-lg btn-default">Cancel</button>
                    <button id="file-save" class="btn btn-lg btn-success">Save</button>
                </div>
            </div>
        </div>

        <?php break; case "templates": ?>
        <div class="container page">
            <div class="box title"><h1>Templates</h1></div>
            <div class="box"><div id="templates-list"></div></div>
        </div>

        <?php break; case "newtemplate": ?>
        <div class="container page">
            <div class="box title"><h1>New Template</h1></div>

            <div id="template-new" class="box">               
                <div class="row">                      
                    <div class="col-sm-6 form-group">
                        <label for="template-name">Template name:</label>
                        <input id="template-name" type="text" class="form-control input-md" />
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="template-parent">Template parent:</label>
                        <select id="template-parent" class="selectpicker" data-width="100%">
                            <option value="null">-None-</option>
                            <option value="0">0</option>
                        </select> 
                    </div>                    
                </div>
                
                <div id="template-simple"> 
                    <div class="form-group">
                        <label for="template-html">Template HTML:</label>
                        <textarea id="template-html" class="form-control vresize" rows="15"></textarea>
                    </div>
                </div>

                <div id="template-derived" class="hidden">

                </div> 

                <div class="line"></div>
                <div class="right">
                    <button id="template-cancel" class="btn btn-lg btn-default">Cancel</button>
                    <button id="template-save" class="btn btn-lg btn-success">Save</button>
                </div>
            </div>
        </div>

        <?php break; case "settings": ?>
        <div class="container page">
            <div class="box title"><h1>Settings</h1></div>
            
            <div class="row noselect">
                <div class="col-lg-4 col-lg-offset-2 col-sm-6">
                    <form id="db-conn" class="box" role="form" autocomplete="off">
                        <h3 class="title-grey">Database connection</h3>
                        <div class="line"></div> 
                                           
                        <div class="form-group">
                            <label for="dbhost">Host:</label>
                            <input type="text" class="form-control" id="dbhost">
                        </div>
                        <div class="form-group">
                            <label for="dbname">Database name:</label>
                            <input type="text" class="form-control" id="dbname">
                        </div>
                        <div class="form-group">
                            <label for="dbuser">Username:</label>
                            <input type="text" class="form-control" id="dbuser">
                        </div>
                        <div class="form-group">
                            <label for="dbpwd">Password:</label>
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

                <div class="col-lg-4 col-sm-6">
                    <div class="box">
                        <h3 class="title-grey">Database initialization</h3>
                        <div class="line"></div>

                        <div id="db-msgs2" class="progress">
                            <div id="db-setting-up" class="progress-bar progress-bar-info progress-bar-striped active fill hidden"><span>Setting database up...</span></div>
                            <div id="db-testing2" class="progress-bar progress-bar-info progress-bar-striped active fill hidden"><span>Testing database...</span></div>
                            <div id="db-resetting" class="progress-bar progress-bar-info progress-bar-striped active fill hidden"><span>Resetting database...</span></div>                 
                            <div id="db-setup-ok" class="progress-bar progress-bar-success fill hidden"><span>Database set up successfully</span></div>
                            <div id="db-test2-ok" class="progress-bar progress-bar-success fill hidden"><span>Database test OK</span></div>
                            <div id="db-reset-ok" class="progress-bar progress-bar-success fill hidden"><span>Database resetted successfully</span></div>
                            <div id="db-setup-error" class="progress-bar progress-bar-danger fill hidden"><span>Error during setup</span></div>
                            <div id="db-test2-error" class="progress-bar progress-bar-danger fill hidden"><span>Database test error</span></div>
                            <div id="db-reset-error" class="progress-bar progress-bar-danger fill hidden"><span>Error during reset</span></div>
                        </div>   

                        <div class="right">
                            <button id="db-setup" class="btn db-button btn-success">Setup database</button>
                            <button id="db-test2" class="btn db-button left btn-info">Test database</button> 
                        </div>

                        <div class="line"></div>
                        <button id="db-reset" class="btn btn-block db-button btn-danger">Reset database</button>  
                    </div>
                </div>
            </div>
        </div>

        <?php break; default: ?><script>window.location.href = "./";</script><?php echo "</body></html>"; exit(); break; } ?>

        <div id="footer">
            <span>Powered by <b>ScanzySoftware</b></span>
        </div>  

        <script src="libs/messages.js"></script>
        <script src="libs/confirm.js"></script>
        <script src="libs/scanzyload.js"></script>
        <script src="libs/scanzytable.js"></script>
        <script src="shared.js"></script>
        
        <script src="pages-<?php echo $url; ?>.js"></script>
    </body>
</html>
