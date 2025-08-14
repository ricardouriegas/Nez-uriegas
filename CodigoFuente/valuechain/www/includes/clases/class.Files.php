<?php

class Files
{

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
    }


    public static  function getDirContents($dir, &$results = array(), $level = 0)
    {   
        // printf("%s\n", $dir);
        // print_r($results);
        // printf("\n\n");
        $files = scandir($dir);
        //$results[] = array("text" => $dir, "children" => array());
        $i = 0;
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (is_dir($path)) {
                if ($value != "." && $value != "..") {
                    $aux = array("text" => $path, "children" => array());
                    $results["children"][] =  $aux;
                    Files::getDirContents($path, $results["children"][$i], $level+1);
                    $i++;
                }
            }
        }

        return $results;
    }
}

//$files = array("text" => "/home/domizzi/Documents/INE/", "children" => array());
//print_r(Files::getDirContents("/home/domizzi/Documents/INE/", $files));
//echo json_encode(Files::getDirContents("/home/domizzi/Documents/INE/", $files));

#Files::getDirContents("/home/domizzi/Documents/INE/");
//print_r(Files::getDirContents(getenv("SHAREDVOLUME")));

#echo json_encode(Files::getDirContents($_ENV["SHAREDVOLUME"]));