<?php
use rutex\Route;

function say($caption, $text="", $newline=true, $separator=", ")  {
    if (is_array($text)) $text = json_encode($text);
    echo "$caption <b>$text</b>", ($newline)?"<br>":$separator;
}

function htmlError($code="404", $msg="NOT Found", $uri="") {
    return Route::htmlError($code, $msg, $uri);
}

function view($route, $parm = [])   { return Route::view($route, $parm)                                       ;}
function result($success, $content) { return ["success"=>$success,"content"=>$content]                        ;}
function viewsFolder()              { return $_ENV["VIEWS_FOLDER"] ?? "../app/views"                          ;}
function rutexLayout($layoutName)   { return __DIR__ . "/layouts/$layoutName.php"                             ;}
function redirect($route)           { header("location: $route")                                              ;}
function Segment($segmentName)      {return viewsFolder() . "/$segmentName.php"                               ;}

function run($script, $parm)       { 
    ob_start();
    include "../app/controllers/$script.php";
    return ob_get_clean();
}

function booleanize($boolstr) {
    return match(strtolower($boolstr)) {
        "true"      => true,
        "yes"       => true,
        "1"         => true,
        "verdadero" => true,
        default     => false,
    };
}

function getConfig($filename) {
    if     (is_readable("../config/$filename.php"))  return include "../config/$filename.php";
    elseif (str_contains($filename, "datastores"))   return [
                                                        "SQL_VERBOSE"   => getenv("SQL_VERBOSE"),
                                                        "DB_HOST"       => getenv("DB_HOST"),
                                                        "DB_PORT"       => (int) getenv("DB_PORT"),
                                                        "DB_DATABASE"   => getenv("DB_DATABASE"),
                                                        "DB_USERNAME"   => getenv("DB_USERNAME"),
                                                        "DB_PASSWORD"   => getenv("DB_PASSWORD"),
                                                        "DB_AUTOCOMMIT" => getenv("DB_AUTOCOMMIT"),
                                                     ];
    else throw new Exception("ERROR: " . __FUNCTION__ . "(\"/config/$filename.php\") File not found");
}


function getRandomToken($length = 7) {
    $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $numbers = "0123456789";

    $randomToken = getRandomString($letters, 3);
    $count       = 3;
    for ($slice=0; $count < $length; $slice++) {
        $randomToken .= "_" . getRandomString($letters, 3);
        $count       += 4;
    }

    return substr($randomToken, 0, $length);
}

function getRandomString($genoma, $length = 10) {
    $lastChar     = strlen($genoma) - 1;
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $genoma[rand(0, $lastChar)];
    }

    return $randomString;
}

function copyFolder($folder, $targetPath) {
    copyFiles($folder, "$targetPath/$folder");
}

function copyFiles($sourceFolder, $targetFolder) {
    if (!file_exists($targetFolder)) mkdir($targetFolder, 0777, true);

    $dir = opendir($sourceFolder);
    while(($file = readdir($dir)) !== false) {
        if (is_file("$sourceFolder/$file")) copy("$sourceFolder/$file", "$targetFolder/$file");
    }
    closedir($dir);
}

function render($layoutName, $data) {
    ob_start();
    extract($data);
    include __DIR__ . "/layouts/$layoutName.php" ;
    return ob_get_clean();
}