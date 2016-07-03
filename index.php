<?php

session_start();
spl_autoload_register(function($class) { require_once "./modules/$class.php"; }); //autoload modules

//sets error mode html
Errors::setModeHtml();

//reads the url of file to process
$url = Params::optionalString('url', "");

//connects to database
$conn = Shared::connect();

//prepares query
$stmt = $conn->prepare("SELECT ContentId FROM Files WHERE Url=:url");
$stmt->bindParam(":url", $url, PDO::PARAM_STR);

//executes query getting data in associative array 
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC); 

//displays 404 page (not found)
if ($result == FALSE) Errors::page404();

//gets content and sends it
echo CMScore::getContent($result['ContentId']);
exit();