<?php

require_once "../../../autoload.php";

//API config/test (tests db config data)

Errors::setModeAjax();
Auth::requireLevel(Auth::ADMIN);

//gets params
$host = Params::requiredString('host');
$name = Params::requiredString('name');
$user = Params::requiredString('user');
$pwd = Params::requiredString('pwd');

//tests config
Config::test($host, $name, $user, $pwd);