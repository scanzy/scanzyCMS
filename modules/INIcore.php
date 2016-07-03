<?php

//MODULE INIcore (core ini features)

class INIcore
{
    //writes ini file (returns FALSE on fail)
    public static function write_ini_file($path, $data, $usesections = FALSE, $mode = INI_SCANNER_NORMAL)
    {
        $f = fopen($path, "w"); //opens file
        if ($f == FALSE) return FALSE;

        if ($usesections)
            foreach($data as $secname => $section)
            {
                fwrite($f, "[".$secname."]".PHP_EOL); //writes section head
                foreach($section as $name => $value) //for each entry
                fwrite($f, self::write_ini_entry($name, $value, $mode)); //writes it in ini file
                fwrite($f, PHP_EOL); // \n or \r\n
            }
        else
        {
            foreach($data as $name => $value) //for each entry
            fwrite($f, self::write_ini_entry($name, $value, $mode)); //writes it in ini file
        }

        fclose($f); //closes file
        return TRUE;
    }

    //returns an ini file line from name and value
    public static function write_ini_entry($name, $value, $mode = INI_SCANNER_NORMAL)
    {
        switch($mode)
        {
            case INI_SCANNER_TYPED: //checks special values
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

    //writes to ini file adding/editing entries read from request
    public static function write_from_request($path, $prototype, $data, $usesections = FALSE, $mode = INI_SCANNER_NORMAL)
    {
        if ($usesections)
        { 
            //for each entry of prototype (sections)
            foreach($prototype as $secname => $section)
                if (isset($_REQUEST[$secname])) //if specified param, merges data
                    $data[$secname] = array_merge($data[$secname], $_REQUEST[$secname]);
        }
        else 
        {
            //for each entry of prototype
            foreach($prototype as $name)
                if (isset($_REQUEST[$name])) //if specified param, sets data (or updates)
                    $data[$name] = $_REQUEST[$name];
        }

        //writes to file
        return self::write_ini_file($path, $data, $usesections, $mode);
    }
}
?>