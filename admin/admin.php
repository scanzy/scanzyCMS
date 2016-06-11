<?php

//includes info about db and misc functions
require_once '../config.php';

//if ajax request (perform action)
if (isset($_REQUEST['action']))
{
    //every action requires login, if no login sends 401 error
    if (!alreadyLogged()) die2(401, "Login required");

    //sets callbacks
    $dberrorcallback = function($msg) { die2(500, $msg); };
    $paramserrorcallback = function($msg) { die2(400, $msg); };

    //checks setup action
    if ($_REQUEST['action'] == "setup") db_setup($dberrorcallback);

    //gets helper object
    $helper = getHelper($_REQUEST['request']);
    
    //selects action type
    switch($_REQUEST['action'])
    {
        //reads db
        case "get": sendJSON($helper->getItems2()); break;

        //modifies db (updating last modified info touching file)
        case "new": $helper->newItem2(); touch(FILE_TOUCH); break;
        case "edit": $helper->editItem2(); touch(FILE_TOUCH); break;

        //deletes item and related elements
        case "del": $helper->delItem2(); 

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
            touch(FILE_TOUCH); break;
        
        //error
        default: die2(400, "Unknown action"); break;
    }

    exit();
}

//sends json to client
function sendJSON($obj)
{
    header("Content-Type: application/json");
    echo json_encode($obj);
    exit();
}

//gets db helper for type specified
function getHelper($type)
{
    //selects request type
    switch($type)
    {
        case "content": 

            return new DBhelper("Content", // table name
                array("id" => "Id"), // required where params 
                array("parentid" => "ParentId"), // optional where params
                array("text" => "Text", "parentid" => "ParentId"), // required insert params
                array("name" => "Name"), // optional insert params
                $paramserrorcallback, $dberrorcallback); // callbacks
                break; 

        case "substitution":

            return new DBhelper("Substitutions", // table name
                array("searchid" => "SearchId", "macro" => "Macro"), // required where params 
                array("replaceid" => "ReplaceId", "index" => "OrderIndex"), // optional where params
                array("searchid" => "SearchId", "macro" => "Macro", "replaceid" => "ReplaceId"), // required insert params
                array("index" => "OrderIndex"), // optional insert params
                $paramserrorcallback, $dberrorcallback); // callbacks
                break; 

        case "file":

            return new DBhelper("Files", // table name
                array("url" => "Url"), // required where params 
                array("contentid" => "ContentId"), // optional where params
                array("url" => "Url", "contentid" => "ContentId"), // required insert params
                array(), // optional insert params
                $paramserrorcallback, $dberrorcallback); // callbacks
                break; 

        case "tag":
            
            return new DBhelper("Tags", // table name
                array("id" => "Id"), // required where params 
                array(), // optional where params
                array("tag" => "Tag"), // required insert params
                array(), // optional insert params
                $paramserrorcallback, $dberrorcallback); // callbacks
                break; 

        case "contenttag":

            return new DBhelper("ContentTags", // table name
                array("tagid" => "TagId", "contentid" => "ContentId"), // required where params 
                array(), // optional where params
                array("tagid" => "TagId", "contentid" => "ContentId"), // required insert params
                array(), // optional insert params
                $paramserrorcallback, $dberrorcallback); // callbacks
                break; 

        case "macrotag":

            return new DBhelper("MacroTags", // table name
                array("tagid" => "TagId", "contentid" => "ContentId", "macro" => "Macro"), // required where params 
                array(), // optional where params
                array("tagid" => "TagId", "contentid" => "ContentId", "macro" => "Macro"), // required insert params
                array(), // optional insert params
                $paramserrorcallback, $dberrorcallback); // callbacks
                break; 

        default: die2(400, "Unknown request"); break;
    }
}

//class to easily manage database rows using request data
class DBhelper
{
    //saves data into object (for easier later access)
    public function __construct($tablename, $requiredwhereparams, $optionalwhereparams, $requiredparams, $optionalparams, $paramserrorcallback, $dberrorcallback)
    {
        $this->tablename = $tablename;
        $this->requiredwhereparams = $requiredwhereparams;
        $this->optionalwhereparams = $optionalwhereparams;
        $this->requiredparams = $requiredparams;
        $this->optionalparams = $optionalparams;
        $this->paramserrorcallback = $paramserrorcallback;
        $this->dberrorcallback = $dberrorcallback;
    }

    //non-static versions of public function below
    public function newItem2() { return self::newItem($this->tablename, $this->requiredparams, $this->optionalparams, $this->paramserrorcallback, $this->dberrorcallback); }
    public function editItem2() { return self::editItem($this->tablename, $this->requiredwhereparams, $this->optionalwhereparams, $this->optionalparams, $this->paramserrorcallback, $this->dberrorcallback); }
    public function delItem2() { return self::delItem($this->tablename, $this->requiredwhereparams, $this->optionalwhereparams, $this->paramserrorcallback, $this->dberrorcallback); }
    public function getItems2($select = "*", $orderbysql = "") { return self::getItems($this->tablename, $select, $this->optionalwhereparams, $this->paramserrorcallback, $this->dberrorcallback, $orderbysql); }
    
    //creates a new item, reading data from request
    public static function newItem($tablename, $requiredparams, $optionalparams, $paramserrorcallback, $dberrorcallback)
    {
        //checks parameters
        if (self::checkParams($requiredparams, $paramserrorcallback) == FALSE) return FALSE;

        //builds sql
        $cols = $vals = "(";
        foreach($requiredparams as $p => $c) { $cols .= $c.", "; $vals .= ":".$c.", "; }
        foreach($optionalparams as $p => $c) if (isset($_REQUEST[$p])) { $cols .= $c.", "; $vals .= ":".$c.", "; }

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
        foreach($requiredparams as $p => $c) $sql.= $c."=:".$c.", ";
        foreach($optionalparams as $p => $c) if (isset($_REQUEST[$p])) $sql.= $c."=:".$c.", ";

        //removes last ", "
        $sql = substr($sql, 0, -2);

        //adds where
        $sql .= " ".self::buildWhereSql($requiredwhereparams, $optionalwhereparams).";";

        //performs query
        return self::performQuery($sql, $requiredwhereparams, array_merge($optionalparams, $optionalwhereparams), $dberrorcallback);
    }

    //deletes an item, reading data from request
    public static function delItem($tablename, $requiredwhereparams, $optionalwhereparams, $paramserrorcallback, $dberrorcallback)
    {
        //checks parameters
        if (self::checkParams($requiredwhereparams, $paramserrorcallback) == FALSE) return FALSE;

        //builds sql
        $sql = "DELETE FROM ".$tablename." ".self::buildWhereSql($requiredwhereparams, $optionalwhereparams).";";

        //performs query
        return self::performQuery($sql, $requiredwhereparams, $optionalwhereparams, $dberrorcallback);
    }

    //gets item info, reading data from request
    public static function getItems($tablename, $select, $optionalwhereparams, $paramserrorcallback, $dberrorcallback, $orderbysql = "")
    {
        //builds sql
        $sql = "SELECT ".$select." FROM ".$tablename." ".self::buildWhereSql(array(), $optionalwhereparams)." ".$orderbysql.";";

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
        if (count($requiredwhereparams) + count($optionalwhereparams) <= 0) return "";

        $where = "WHERE ";
         
        //required params
        foreach($requiredwhereparams as $p => $c) 
            $where.=$c."=:".$c." AND "; 

        //optional params
        foreach($optionalparams as $p => $c) 
            if (isset($_REQUEST[$p])) $where.=$c."=:".$c." AND "; 

        //returns string without last " AND "
        return substr($where, 0 -5);
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
            foreach($bindparams as $c => $val) $stmt->bindParam(":".$c, $v);
            
            $stmt->execute(); //executes query 
            return $stmt->fetch(PDO::FETCH_ASSOC); //and returns result

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

//redirects to login page if no login 
if (!alreadyLogged()) redirect("./login.php");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>scanzyCMS - Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css" />
    </head>
    <body>

        <nav id="topbar" class="navbar-default noselect">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#topbarcontent">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="topbarcontent">
                <ul class="nav">
                    <li>
                        <a href="#">Dashboard</a>
                    </li><li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            Files
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="#">New file</a></li>
                            <li><a href="#">View list</a></li>
                        </ul>
                    </li><li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            Contents
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="#">New content</a></li>
                            <li><a href="#">View list</a></li>
                        </ul>
                    </li><li><a href="#">Settings</a>
                    </li><li><a href="#">Logout</a></li>
                </ul>
            </div>
        </nav>

        <div id="header" class="center container noselect">
            <h1 class="inline">scanzyCMS</h1>
            <h3 class="inline">admin</h3>
            <div class="inline">
                <!--img width="220" src="logo.png" /-->
            </div>
        </div>

        <div class="container">
            <div class="box title"><h1>Dashboard</h1></div>

            <div class="box">
                <h2>Heading</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas et ipsum sed dolor vehicula congue quis egestas risus. Cras tortor felis, convallis eget euismod et, varius nec sapien. Pellentesque quis augue sit amet justo faucibus condimentum id non metus. Nullam maximus molestie ex sit amet sagittis. Vivamus non neque tellus. Phasellus tincidunt tellus sit amet nulla pellentesque feugiat. In augue metus, dignissim at egestas vel, ornare sed nibh. Aliquam elementum, purus ut consectetur accumsan, ex nibh efficitur orci, quis maximus augue elit quis libero. Vestibulum feugiat, purus id volutpat sodales, lacus arcu varius neque, ut rutrum mauris magna a leo. </p>                
            </div>
        </div>

        <div id="footer">
            <span>Powered by <b>ScanzySoftware</b></span>
        </div>
    </body>
</html>
