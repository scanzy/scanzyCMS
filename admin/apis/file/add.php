<?php

require_once "../../../autoload.php";

//API file/add (add file)

Errors::setModeAjax();
Auth::requireLevel(Auth::WRITER);

Database::getHelper("file")->newItem2(); //adds item
Config::touch(); //db modified