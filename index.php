<?php

require "./autoload.php"; //starts session and autoloads classes

//sets error mode html
Errors::setModeHtml();

//reads the url of file to process
$url = Params::optionalString('url', "");

//gets conf data
$conf = Config::get();

//connects to database
$conn = Shared::connect();

//prepares query
$stmt = $conn->prepare("CALL getFileContents(:url, :prefix, :suffix, :lastmod);");
$stmt->bindValue(":url", $url, PDO::PARAM_STR);
$stmt->bindValue(":prefix", $conf['Macro']['prefix'], PDO::PARAM_STR);
$stmt->bindValue(":suffix", $conf['Macro']['suffix'], PDO::PARAM_STR);
$stmt->bindValue(":lastmod", Config::lastMod(), PDO::PARAM_INT);

//executes query getting data in associative array 
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC); 

//displays 404 page (not found)
if ($result['html'] == NULL) Errors::page404();

// sends html
echo $result['html'];
exit();