<?php
    
//includes misc functions (db connection, error handling, ecc)
require_once './include/shared.php';

//sets error mode html
setErrMode(ERR_MODE_HTML);

//reads the url of file to process
if (!isset($_GET['url'])) $_GET['url'] = "";

try 
{
    //connects to database
    $conn = connect();

    //prepares query
    $stmt = $conn->prepare("SELECT ContentId FROM Files WHERE Url=:url");
    $stmt->bindParam(":url", $_GET['url'], PDO::PARAM_STR);

    //executes query getting data in associative array 
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC); 

    //displays 404 page (not found)
    if ($result == FALSE) die2(404);

    //gets content and sends it
    echo getContent($result['ContentId']);

} //catches errors displaying error page
catch(PDOException $e) { die2(500, $e->getMessage()); }

exit();

//gets content text
function getContent($id)
{
    //reads content info from database
    $info = getContentInfo($id);
      
    //returns cached value if not modified (cache hit)
    if (LAST_MOD > $info['CacheTime']) return $info['Text'];

    //reads substitution info from database
    $subs = getSubs($id);

    //if direct content (no parent content)
    if ($info['ParentId'] == NULL) $text = $info['Text'];

    //if not direct content, replaces macros in parent
    else $text = getContent($info['ParentId']);

    //now performs substitutions reading content text from database (eventually using cache)
    foreach($subs as $sub) $text = str_replace(
        $_SESSION['scanzycms-config']['Macro']['prefix'].$sub['Macro'].
        $_SESSION['scanzycms-config']['Macro']['suffix'], getContent($sub['ReplaceId']), $text);

    //everything has been processed so it caches result
    setContentCache($id, $text);
    return $text; 
}

//gets content information (associative array from database row)
function getContentInfo($id)
{
    //reads content info from database
    $conn = connect();
    $stmt = $conn->prepare("SELECT Text, ParentId, CacheTime FROM Contents WHERE Id=:id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC); //checks empty result
    if (empty($result)) die2(404, "Not found content with id ".$id);

    return $result;
}

//saves result in cache
function setContentCache($id, $text)
{
    $conn = connect();
    $stmt = $conn->prepare("UPDATE Contents SET Text=:text, CacheTime=UNIX_TIMESTAMP(NOW()) WHERE Id=:id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->bindParam(":text", $text, PDO::PARAM_STR);
    $stmt->execute();
}

//gets substitutions for given content id
function getSubs($contentid)
{
    //reads content info from database
    $conn = connect();
    $stmt = $conn->prepare("SELECT Macro, ReplaceId FROM Substitutions WHERE SearchId=:id ORDER BY OrderIndex ASC");
    $stmt->bindParam(":id", $contentid, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}