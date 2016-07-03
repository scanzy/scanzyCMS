<?php

require_once "../../../autoload.php";

//API auth/login (logs out)

Errors::setModeAjax();
session_destroy();