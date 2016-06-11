<?php

//includes info about db and misc functions
require_once '../config.php';

//detects mode (ajax/html)
if (isset($_POST['username']) && isset($_POST['password'])) 
{
    //finds user
    if(isset($GLOBALS['scanzycms-users'][$_POST['username']]))
    {
        //checks password
        if($GLOBALS['scanzycms-users'][$_POST['username']] == $_POST['password'])
        {
            //saves username
            session_start();
            $_SESSION['username'] = $_POST['username'];

            echo "true"; //success!
            exit();
        }
    }

    echo "false"; //login failed
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>scanzyCMS - Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css" />
    </head>
    <body>

        <div style="height: 6em;"></div>

        <div id="header" class="center container noselect">
            <h1 class="inline">scanzyCMS</h1>
            <h3 class="inline">admin</h3>
            <div class="inline">
                <!--img width="220" src="logo.png" /-->
            </div>
        </div>

        <div class="container">
            <div class="row noselect">
                <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                    <form id="login" class="box" role="form">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" id="password">
                        </div>
                        <p class="center"><span id="wrongpassword" class="label label-danger hidden">Wrong username or password</span></p>
                        <button id="submit" type="submit" class="btn btn-info btn-block disabled">Login</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            $("#username, #password").on('input', function () {
                if ($("#username").val().trim() != "" && $("#password").val().trim() != "")
                    $("#submit").removeClass('disabled'); else $("#submit").addClass('disabled');
            });

            $("#login").on('submit', function (e) {
                e.preventDefault();
                if ($("#submit").hasClass('disabled')) return;
                $.post("login.php", { username: $("#username").val(), password: $("#password").val() }, function (data) {
                    if (data == "true") window.location.href = "./";
                    else { $("#wrongpassword").removeClass('hidden'); shake($("#login")); }
                });
            });

            function shake(div) {
                var interval = 80; var dist = 8; var times = 4; div.css('position', 'relative');
                for (var i = 0; i < times + 1; i++) div.animate({ left: ((i % 2 == 0 ? dist : dist * -1)) }, interval);
                div.animate({ left: 0 }, interval);
            }

        </script>
    </body>
</html>
