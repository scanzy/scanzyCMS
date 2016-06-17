<?php

//DATABASE UTILS

//gets db helper for type specified
function getHelper($type, $paramserrorcallback, $dberrorcallback)
{
    $helper = new DBhelper($paramserrorcallback, $dberrorcallback); // callbacks

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

        default: $paramserrorcallback("Unknown request"); return NULL; break;
    }
    return $helper;
}

//class to easily manage database rows using request data
class DBhelper
{
    //saves callbacks into object (for easier later access)
    public function __construct($paramserrorcallback, $dberrorcallback)
    {
        $this->paramserrorcallback = $paramserrorcallback;
        $this->dberrorcallback = $dberrorcallback;
    }

    public $tablename = "";
    public $requiredwhereparams = array();
    public $optionalwhereparams = array();
    public $requiredparams = array();
    public $optionalparams = array();
    
    //non-static versions of public function below
    public function newItem2() { return self::newItem($this->tablename, $this->requiredparams, $this->optionalparams, $this->paramserrorcallback, $this->dberrorcallback); }
    public function editItem2() { return self::editItem($this->tablename, $this->requiredwhereparams, $this->optionalwhereparams, $this->optionalparams, $this->paramserrorcallback, $this->dberrorcallback); }
    public function delItem2() { return self::delItem($this->tablename, $this->requiredwhereparams, $this->optionalwhereparams, $this->paramserrorcallback, $this->dberrorcallback); }
    public function getItems2($select = "*") { return self::getItems($this->tablename, $select, $this->optionalwhereparams, $this->paramserrorcallback, $this->dberrorcallback); }
    
    //creates a new item, reading data from request
    public static function newItem($tablename, $requiredparams, $optionalparams, $paramserrorcallback, $dberrorcallback)
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
        return self::performQuery("INSERT INTO ".$tablename." ".$cols." VALUES ".$vals.";", $requiredparams, $optionalparams, $dberrorcallback);
    }

    //edits an item, reading data from request
    public static function editItem($tablename, $requiredwhereparams, $optionalwhereparams, $optionalparams, $paramserrorcallback, $dberrorcallback)
    {
        //checks parameters
        if (self::checkParams($requiredwhereparams, $paramserrorcallback) == FALSE) return FALSE;
        if (count($optionalparams) == 0) { $paramserrorcallback("No modification specified"); return FALSE; } //if no params

        //builds sql
        $sql = "UPDATE FROM ".$tablename." SET  ";
        foreach($requiredparams as $p => $c) $sql.= $c."=:".strtolower($c).", ";
        foreach($optionalparams as $p => $c) if (isset($_REQUEST[$p])) $sql.= $c."=:".strtolower($c).", ";

        //removes last ", "
        $sql = substr($sql, 0, -2);

        //adds where
        $sql .= self::buildWhereSql($requiredwhereparams, $optionalwhereparams).";";

        //performs query
        return self::performQuery($sql, $requiredwhereparams, array_merge($optionalparams, $optionalwhereparams), $dberrorcallback);
    }

    //deletes an item, reading data from request
    public static function delItem($tablename, $requiredwhereparams, $optionalwhereparams, $paramserrorcallback, $dberrorcallback)
    {
        //checks parameters
        if (self::checkParams($requiredwhereparams, $paramserrorcallback) == FALSE) return FALSE;

        //builds sql
        $sql = "DELETE FROM ".$tablename.self::buildWhereSql($requiredwhereparams, $optionalwhereparams).";";

        //performs query
        return self::performQuery($sql, $requiredwhereparams, $optionalwhereparams, $dberrorcallback);
    }

    //gets item info, reading data from request
    public static function getItems($tablename, $select, $optionalwhereparams, $paramserrorcallback, $dberrorcallback)
    {
        //builds sql
        $sql = "SELECT ".$select." FROM ".$tablename.self::buildWhereSql(array(), $optionalwhereparams).";";

        //performs query
        return self::performQuery($sql, array(), $optionalwhereparams, $dberrorcallback);
    }

    //checks if required parameters have been set
    static function checkParams($params, $errorcallback)
    {
        foreach($params as $p => $c) //for each param
            if (!isset($_REQUEST[$p])) //if is not set
            { $errorcallback("Missing required parameter '".$p."'"); return FALSE; } //error
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
    static function performQuery($sql, $requiredparams, $optionalparams, $errorcallback)
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
        catch(PDOException $e) { $errorcallback($e->GetMessage()); return FALSE; }
    }
}

//used to create database tables
function db_setup($errorcallback)
{
    //connects to database
    try { $conn = connect(); } 
    catch(PDOException $e) { $errorcallback("Connection error: ".$e->getMessage()); }

    $sql = "CREATE TABLE Contents (
                Id int,
                Text varchar(8191),
                ParentId int,
                Name varchar(31),
                CacheTime int(8) NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (ParentId) REFERENCES Contents(Id)
            );
            CREATE TABLE Substitutions (
                SearchId int NOT NULL,
                Macro varchar(31) NOT NULL,
                OrderIndex int NOT NULL,
                ReplaceId int NOT NULL,
                FOREIGN KEY (SearchId) REFERENCES Contents(Id),
                FOREIGN KEY (ReplaceId) REFERENCES Contents(Id)
            );
            CREATE TABLE Files (
                Url varchar(31) UNIQUE NOT NULL,
                ContentId int NOT NULL,
                FOREIGN KEY (ContentId) REFERENCES Contents(Id)
            );
            CREATE TABLE Tags (
                Id int, 
                Tag varchar(31) NOT NULL,
                PRIMARY KEY (Id)
            );
            CREATE TABLE ContentTags (
                TagId int NOT NULL,
                ContentId int NOT NULL,
                FOREIGN KEY (TagId) REFERENCES Tags(Id),
                FOREIGN KEY (ContentId) REFERENCES Contents(Id)
            );
            CREATE TABLE MacroTags (
                TagId int NOT NULL,
                ContentId int NOT NULL,
                Macro varchar(31) NOT NULL,
                FOREIGN KEY (TagId) REFERENCES Tags(Id),
                FOREIGN KEY (ContentId) REFERENCES Contents(Id),
                FOREIGN KEY (Macro) REFERENCES Substitutions(Macro)
            );";

    //executes query
    try { $conn->exec($sql); } 
    catch(PDOException $e) { $errorcallback("SQL error: ".$e->getMessage()); }
    exit();
}   

//used to delete database tables
function db_reset($errorcallback)
{
    //connects to database
    try { $conn = connect(); } 
    catch(PDOException $e) { $errorcallback("Connection error: ".$e->getMessage()); }

    //deletes tables
    $errors = array();
    $tables = array("Contents", "Substitutions", "Files", "Tags", "ContentTags", "MacroTags");
    
    //executes queries
    foreach($tables as $t)        
        try { $conn->exec("DROP TABLE ".$t); } //deletes table 
        catch(PDOException $e) { $errors[] = $e; } //adds error

    if (count($errors) > 0) //sends errors if any
        $errorcallback(count($errors)." of ".count($tables)." table(s) NOT deleted, they might not exist");

    exit();
}

?>