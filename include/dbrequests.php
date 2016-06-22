<?php

//DATABASE UTILS

//class to easily manage database rows using request data
class DBhelper
{
    //error callbacks
    public static $paramserrorcallback;
    public static $dberrorcallback;

    public $tablename = "";
    public $requiredwhereparams = array();
    public $optionalwhereparams = array();
    public $requiredparams = array();
    public $optionalparams = array();
    
    //non-static versions of public function below
    public function newItem2() { return self::newItem($this->tablename, $this->requiredparams, $this->optionalparams); }
    public function editItem2() { return self::editItem($this->tablename, $this->requiredwhereparams, $this->optionalwhereparams, $this->optionalparams); }
    public function delItem2() { return self::delItem($this->tablename, $this->requiredwhereparams, $this->optionalwhereparams); }
    public function getItems2($select = "*") { return self::getItems($this->tablename, $select, $this->optionalwhereparams); }
    
    //creates a new item, reading data from request
    public static function newItem($tablename, $requiredparams, $optionalparams)
    {
        //checks parameters
        if (self::checkParams($requiredparams, $paramserrorcallback) == FALSE) return FALSE;

        //builds sql
        $cols = $vals = "(";
        foreach($requiredparams as $p => $c) { $cols .= $c.", "; $vals .= ":".strtolower($c).", "; }
        foreach($optionalparams as $p => $c) if (isset($_REQUEST[$p])) { $cols .= $c.", "; $vals .= ":".strtolower($c).", "; }

        //removes last ", " and appends ")";
        $cols = substr($cols, 0, -2).")";
        $vals = substr($vals, 0, -2).")";
        
        //performs query
        return self::performQuery("INSERT INTO ".$tablename." ".$cols." VALUES ".$vals.";", $requiredparams, $optionalparams);
    }

    //edits an item, reading data from request
    public static function editItem($tablename, $requiredwhereparams, $optionalwhereparams, $optionalparams)
    {
        //checks parameters
        if (self::checkParams($requiredwhereparams) == FALSE) return FALSE;
        if (count($optionalparams) == 0) { self::$paramserrorcallback("No modification specified"); return FALSE; } //if no params

        //builds sql
        $sql = "UPDATE FROM ".$tablename." SET  ";
        foreach($requiredparams as $p => $c) $sql.= $c."=:".strtolower($c).", ";
        foreach($optionalparams as $p => $c) if (isset($_REQUEST[$p])) $sql.= $c."=:".strtolower($c).", ";

        //removes last ", "
        $sql = substr($sql, 0, -2);

        //adds where
        $sql .= self::buildWhereSql($requiredwhereparams, $optionalwhereparams).";";

        //performs query
        return self::performQuery($sql, $requiredwhereparams, array_merge($optionalparams, $optionalwhereparams));
    }

    //deletes an item, reading data from request
    public static function delItem($tablename, $requiredwhereparams, $optionalwhereparams)
    {
        //checks parameters
        if (self::checkParams($requiredwhereparams) == FALSE) return FALSE;

        //builds sql
        $sql = "DELETE FROM ".$tablename.self::buildWhereSql($requiredwhereparams, $optionalwhereparams).";";

        //performs query
        return self::performQuery($sql, $requiredwhereparams, $optionalwhereparams);
    }

    //gets item info, reading data from request
    public static function getItems($tablename, $select, $optionalwhereparams)
    {
        //builds sql
        $sql = "SELECT ".$select." FROM ".$tablename.self::buildWhereSql(array(), $optionalwhereparams).";";

        //performs query
        return self::performQuery($sql, array(), $optionalwhereparams);
    }

    //checks if required parameters have been set
    static function checkParams($params)
    {
        foreach($params as $p => $c) //for each param
            if (!isset($_REQUEST[$p])) //if is not set
            { self::$errorcallback("Missing required parameter '".$p."'"); return FALSE; } //error
        return TRUE;
    }

    //builds where clause using request params
    static function buildWhereSql($requiredwhereparams, $optionalwhereparams)
    {
        //no query if no where params
        if (count($requiredwhereparams) + count($optionalwhereparams) == 0) return "";

        $where = " WHERE ";
         
        //required params
        foreach($requiredwhereparams as $p => $c)
            $where .= $c."=:".strtolower($c)." AND "; 

        //optional params
        foreach($optionalwhereparams as $p => $c) 
            if (isset($_REQUEST[$p])) $where .= $c."=:".strtolower($c)." AND "; 

        //returns string without last " AND "
        return ($where != " WHERE ") ? substr($where, 0, -5) : "";
    }

    //performs query binding params
    static function performQuery($sql, $requiredparams, $optionalparams)
    {
        try { //connects to database
            $conn = connect();

            //prepares statement
            $stmt = $conn->prepare($sql);

            //lists bindparams
            $bindparams = array();
            foreach($requiredparams as $p => $c) $bindparams[$c] = $_REQUEST[$p];
            foreach($optionalparams as $p => $c) if (isset($_REQUEST[$p])) $bindparams[$c] = $_REQUEST[$p];

            //binds params
            foreach($bindparams as $c => $val) $stmt->bindParam(":".strtolower($c), $val);
            
            $stmt->execute(); //executes query 
            return (substr($sql, 0, 6) == "SELECT") ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->rowCount(); //and returns result

        } //handles pdo errors
        catch(PDOException $e) { self::$dberrorcallback($e->GetMessage()); return FALSE; }
    }
}

//gets db helper for type specified
function getHelper($type)
{
    $helper = new DBhelper(); //creates obj

    //selects request type
    switch($type)
    {
        case "content":             
            $helper->tablename = "Contents"; // table name
            $helper->requiredwhereparams = array("id" => "Id"); // required where params 
            $helper->optionalwhereparams = array("parentid" => "ParentId"); // optional where params
            $helper->requiredparams = array("text" => "Text", "parentid" => "ParentId"); // required insert params
            $helper->optionalparams = array("name" => "Name"); // optional insert params
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

        case "tag":            
            $helper->tablename = "Tags"; // table name
            $helper->requiredwhereparams = array("id" => "Id"); // required where params 
            $helper->requiredparams = array("tag" => "Tag"); // required insert params
            break; 

        case "contenttag":
            $helper->tablename = "ContentTags"; // table name
            $helper->requiredwhereparams = array("tagid" => "TagId", "contentid" => "ContentId"); // required where params 
            $helper->requiredparams = array("tagid" => "TagId", "contentid" => "ContentId"); // required insert params
            break; 

        case "macrotag":
            $helper->tablename = "MacroTags"; // table name
            $helper->requiredwhereparams = array("tagid" => "TagId", "contentid" => "ContentId", "macro" => "Macro"); // required where params 
            $helper->requiredparams = array("tagid" => "TagId", "contentid" => "ContentId", "macro" => "Macro"); // required insert params
            break; 

        default: DBhelper::$paramserrorcallback("Unknown request"); return NULL; break;
    }
    return $helper;
}

//recognizes right DB action and executes it
function processDBAction()
{
    //sets helper callbacks
    DBhelper::$dberrorcallback = function($msg) { die2(500, $msg); };
    DBhelper::$paramserrorcallback = function($msg) { die2(400, $msg); };

    //gets helper object
    $helper = getHelper($_REQUEST['request']);
    
    //selects action type
    switch($_REQUEST['action'])
    {
        //reads db
        case "get": sendJSON($helper->getItems2()); break;

        //modifies db (updating last modified info touching file)
        case "new": $helper->newItem2(); db_modified(); break;
        case "edit": $helper->editItem2(); db_modified(); break;

        //deletes item and related elements
        case "del": $helper->delItem2(); db_modified();

            //deletes related items
            switch($_REQUEST['request'])
            {
                case "content": 
                    
                    //deletes files with that content id
                    DBhelper::delItem("Files", array("id" => "ContentId"), array(), $paramserrorcallback, $dberrorcallback);

                    //deletes substitutions for that content and of that content
                    DBhelper::delItem("Substitutions", array("id" => "SearchId"), array(), $paramserrorcallback, $dberrorcallback);
                    DBhelper::delItem("Substitutions", array("id" => "ReplaceId"), array(), $paramserrorcallback, $dberrorcallback);

                    //deletes contenttags and macrotags
                    DBhelper::delItem("ContentTags", array("id" => "ContentId"), array(), $paramserrorcallback, $dberrorcallback);
                    DBhelper::delItem("MacroTags", array("id" => "ContentId"), array(), $paramserrorcallback, $dberrorcallback);

                    break;

                case "tag": 
                    
                    //deletes contenttags and macrotags
                    DBhelper::delItem("ContentTags", array("id" => "TagId"), array(), $paramserrorcallback, $dberrorcallback);
                    DBhelper::delItem("MacroTags", array("id" => "TagId"), array(), $paramserrorcallback, $dberrorcallback);
                    
                    break;
            }
            break;
        
        //error
        default: die2(400, "Unknown action"); break;
    }
}

//used to create database tables
function db_setup()
{
    //connects to database
    $conn = connect(); 
    
    $sql = "CREATE TABLE Contents (
                Id int,
                Text varchar(8191),
                TemplateId int,
                CacheTime int(8) NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (ParentId) REFERENCES Templates(Id)
            );
            CREATE TABLE Substitutions (
                SearchId int NOT NULL,
                Macro varchar(31) NOT NULL,
                OrderIndex int NOT NULL,
                ReplaceId int NOT NULL,
                FOREIGN KEY (SearchId) REFERENCES Contents(Id),
                FOREIGN KEY (ReplaceId) REFERENCES Templates(Id)
            );
            CREATE TABLE Templates (
                Id int,
                Name varchar(31),
                Html varchar(8191),
                ContentId int,
                PRIMARY KEY (id),
                FOREIGN KEY (ContentId) REFERNCES Contents(id)
            );
            CREATE TABLE Files (
                Url varchar(31) UNIQUE NOT NULL,
                ContentId int NOT NULL,
                FOREIGN KEY (ContentId) REFERENCES Contents(Id)
            );
            CREATE TABLE Macros (
                SearchId int NOT NULL,
                Macro varchar(31) NOT NULL,
                ReplaceId int NOT NULL,
                FOREIGN KEY (SearchId) REFERENCES Templates(Id),
                FOREIGN KEY (SearchId) REFERENCES Templates(Id)
            );";

    //executes query
    try { $conn->exec($sql); } 
    catch(PDOException $e) { die2(500, "SQL error: ".$e->getMessage()); }
    exit();
}   

//used to check if all tables have been set up correctly
function db_test()
{
    //connects to database
    $conn = connect(); 

    //gets data from tables
    $errors = array();
    $tables = array("Contents", "Substitutions", "Files", "Templates", "Macros");
    
    //executes queries
    foreach($tables as $t)        
        try { $conn->exec("SELECT * FROM TABLE ".$t); } //gets data table 
        catch(PDOException $e) { $errors[] = $e; } //adds error

    if (count($errors) > 0) //sends errors if any
        die2(500, count($errors)." of ".count($tables)." table(s) NOT found, they might not exist");

    exit();
}

//used to delete database tables
function db_reset()
{
    //connects to database
    $conn = connect(); 

    //deletes tables
    $errors = array();
    $tables = array("Contents", "Substitutions", "Files", "Templates", "Macros");
    
    //executes queries
    foreach($tables as $t)        
        try { $conn->exec("DROP TABLE ".$t); } //deletes table 
        catch(PDOException $e) { $errors[] = $e; } //adds error

    if (count($errors) > 0) //sends errors if any
        die2(500, count($errors)." of ".count($tables)." table(s) NOT deleted, they might not exist");

    exit();
}

?>