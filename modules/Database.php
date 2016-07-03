<?php

//MODULE Database (uses helper to edit/add/remove rows)

class Database
{
    const SQL_FOLDER = "../sql";
    const SQL_SETUP = "setup.sql";
    const SQL_RESET = "reset.sql";
    const SQL_TEST = "test.sql";

    //database setup/reset/test
    public static function setup() { self::sql_exec_from_file(__DIR__."/".self::SQL_FOLDER."/".self::SQL_SETUP); }
    public static function reset() { self::sql_exec_from_file(__DIR__."/".self::SQL_FOLDER."/".self::SQL_RESET); }
    public static function test() { self::sql_exec_from_file(__DIR__."/".self::SQL_FOLDER."/".self::SQL_TEST); }

    //used to execute sql code in file
    function sql_exec_from_file($path)
    {
        $sql = file_get_contents($path); //gets sql handling IO errors
        if ($sql == FALSE) Errors::send(500, 'I/O error reading sql code from file '.$path);

        //connects and executes query
        $conn = Shared::connect();     
        $conn->exec($sql); 
    }
}