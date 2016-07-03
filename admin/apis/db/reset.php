<?php

require_once "../../../autoload.php";

//API db/reset (resets database tables and procedures)

Errors::setModeAjax();
Auth::requireLevel(Auth::ADMIN);
Database::reset();