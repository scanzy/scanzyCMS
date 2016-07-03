<?php

require_once "../../../autoload.php";

//API db/setup (setups database tables and procedures)

Errors::setModeAjax();
Auth::requireLevel(Auth::ADMIN);
Database::setup();