<?php
    
session_start();
spl_autoload_register(function($class) { require_once __DIR__."/modules/$class.php"; }); //autoload modules