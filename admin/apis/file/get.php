<?php

require_once "../../../autoload.php";

//API file/get (gets files)

Errors::setModeAjax();
Auth::requireLevel(Auth::WRITER);

Shared::sendJSON(Database::getHelper("file")->getItems2());