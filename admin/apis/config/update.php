<?php

require_once "../../../autoload.php";

//API config/update (updates configuration)

Errors::setModeAjax();
Auth::requireLevel(Auth::ADMIN);
Config::update();