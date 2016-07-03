<?php

//MODULE DBcore (used to easily manage data)

class DBcore
{
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
        if (self::checkParams($requiredparams) == FALSE) return FALSE;

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
        if (count($optionalparams) == 0) { Errors::send(400, "No modification specified"); return FALSE; } //if no params

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
            { Errors::send(400, "Missing required parameter '".$p."'"); return FALSE; } //error
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
    public static function performQuery($sql, $requiredparams, $optionalparams)
    {
        //connects to database
        $conn = Shared::connect();

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
    }
}