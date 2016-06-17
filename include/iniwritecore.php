<?php

//INI WRITE CORE

//writes ini file (returns FALSE on fail)
function write_ini_file($path, $data, $usesections = FALSE, $mode = INI_SCANNER_NORMAL)
{
    $f = fopen($path, "w"); //opens file
    if ($f == FALSE) return FALSE;

    if ($usesections)
        foreach($data as $secname => $section)
        {
            fwrite($f, "[".$secname."]".PHP_EOL); //writes section head
            foreach($section as $name => $value) //for each entry
            fwrite($f, write_ini_entry($name, $value, $mode)); //writes it in ini file
            fwrite($f, PHP_EOL); // \n or \r\n
        }
    else
    {
        foreach($data as $name => $value) //for each entry
        fwrite($f, write_ini_entry($name, $value, $mode)); //writes it in ini file
    }

    fclose($f); //closes file
    return TRUE;
}

//returns an ini file line from name and value
function write_ini_entry($name, $value, $mode = INI_SCANNER_NORMAL)
{
    switch($mode)
    {
        case INI_SCANNED_TYPED: //checks special values
            if ($value === TRUE) return $name." = true".PHP_EOL;
            if ($value === FALSE) return $name." = false".PHP_EOL;
            if ($value === NULL) return $name." = null".PHP_EOL;
            if (is_numeric($value)) return $name." = ".$value.PHP_EOL;            
            break;

        case INI_SCANNER_NORMAL: default: break;         
    }
    //default (quotes)
    return $name." = \"".$value."\"".PHP_EOL;
}

?>