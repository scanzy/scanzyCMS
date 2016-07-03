<?php

require_once "../../../autoload.php";

//API db/reset (resets database tables and procedures)

Auth::requireLevel(Auth::ADMIN);
Database::reset();