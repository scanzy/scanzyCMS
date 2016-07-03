<?php

require_once "../../../autoload.php";

//API db/test (tests database tables and procedures)

Auth::requireLevel(Auth::ADMIN);
Database::test();