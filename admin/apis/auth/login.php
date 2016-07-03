<?php

require_once "../../../autoload.php";

//API auth/login (tries log in)

Errors::setModeAjax();
Auth::login();