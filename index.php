<?php
    
require_once './modules/Shared.php'; //includes misc functions (db connection, json, etc)
require_once './modules/Errors.php'; //includes error handling functions
require_once './modules/CMScore.php'; //includes functions to get page content

//sets error mode html
setErrModeHtml();

//reads the url of file to process
if (!isset($_GET['url'])) $_GET['url'] = "";

//connects to database
$conn = connect();

//prepares query
$stmt = $conn->prepare("SELECT ContentId FROM Files WHERE Url=:url");
$stmt->bindParam(":url", $_GET['url'], PDO::PARAM_STR);

//executes query getting data in associative array 
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC); 

//displays 404 page (not found)
if ($result == FALSE) error404page();

//gets content and sends it
echo getContent($result['ContentId']);
exit();