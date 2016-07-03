<?php

require_once "../../../autoload.php";

//API db/setup (setups database tables and procedures

Auth::requireLevel(Auth::ADMIN);
Database::setup();