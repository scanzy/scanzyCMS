<?php

require_once "../../../autoload.php";

//API config/get (gets configuration)

Errors::setModeAjax();
Auth::requireLevel(Auth::ADMIN);
Shared::sendJSON(Config::get());