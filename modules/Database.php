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
    public static function sql_exec_from_file($path)
    {
        $sql = file_get_contents($path); //gets sql handling IO errors
        if ($sql == FALSE) Errors::send(500, 'I/O error reading sql code from file '.$path);

        //connects and executes query
        $conn = Shared::connect();     
        $conn->exec($sql); 
    }

    //gets db helper for type specified
    public static function getHelper($type)
    {
        $helper = new DBcore(); //creates obj

        //selects request type
        switch($type)
        {
            case "content":             
                $helper->tablename = "Contents"; // table name
                $helper->requiredwhereparams = array("id" => "Id"); // required where params 
                $helper->optionalwhereparams = array("templateid" => "TemplateId"); // optional where params
                $helper->requiredparams = array("text" => "Text", "templateid" => "TemplateId"); // required insert params
                break; 

            case "substitution":
                $helper->tablename = "Substitutions"; // table name
                $helper->requiredwhereparams = array("searchid" => "SearchId", "macro" => "Macro"); // required where params 
                $helper->optionalwhereparams = array("replaceid" => "ReplaceId", "index" => "OrderIndex"); // optional where params
                $helper->requiredparams = array("searchid" => "SearchId", "macro" => "Macro", "replaceid" => "ReplaceId"); // required insert params
                $helper->optionalparams = array("index" => "OrderIndex"); // optional insert params
                break; 

            case "file":
                $helper->tablename = "Files"; // table name
                $helper->requiredwhereparams = array("url" => "Url"); // required where params 
                $helper->optionalwhereparams = array("contentid" => "ContentId"); // optional where params
                $helper->requiredparams = array("url" => "Url", "contentid" => "ContentId"); // required insert params
                break; 

             case "macro":
                $helper->tablename = "Macros"; // table name
                $helper->requiredwhereparams = array("searchid" => "SearchId", "macro" => "Macro"); // required where params 
                $helper->optionalwhereparams = array("replaceid" => "ReplaceId", "index" => "OrderIndex"); // optional where params
                $helper->requiredparams = array("searchid" => "SearchId", "macro" => "Macro", "replaceid" => "ReplaceId"); // required insert params
                break; 

            default: Errors::send(500, "Unknown type"); return NULL; break;
        }
        return $helper;
    }
}