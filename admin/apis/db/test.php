<?php

require_once "../../../autoload.php";

//API db/test (tests database tables and procedures)

Errors::setModeAjax();
Auth::requireLevel(Auth::ADMIN);
Database::test();