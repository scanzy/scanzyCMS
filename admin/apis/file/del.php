<?php

require_once "../../../autoload.php";

//API file/del (deletes file)

Errors::setModeAjax();
Auth::requireLevel(Auth::WRITER);

Database::getHelper("file")->delItem2(); //deletes item
Config::touch(); //db modified