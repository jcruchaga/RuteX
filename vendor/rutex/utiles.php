<?php
use rutex\Route;
use rutex\Model;
use rutex\Http;

function say($caption, $text="", $newlines=1)  {
    if (is_array($text) || is_object($text)) $text = json_encode($text);
    echo "$caption <b>$text</b>";
    if ($newlines>0) echo str_repeat("<br>", $newlines);
    else echo ", ";
}

function showmap($map) {
    echo "<table>";
    foreach($map as $key => $value) {
        echo "<tr><td>$key<td>$value";
    }
    echo "</table>";
}


function htmlError($code="404", $msg="NOT Found", $uri="") {
    return Route::htmlError($code, $msg, $uri);
}

function view($route, $parm=[])     { return Route::view($route, $parm)                 ;}
function result($success, $content) { return ["success"=>$success,"content"=>$content]  ;}
function viewsFolder()              { return $_ENV["VIEWS_FOLDER"] ?? "../app/views"    ;}
function rutexLayout($layoutName)   { return __DIR__ . "/layouts/$layoutName.php"       ;}
function redirect($route)           { header("location: $route")                        ;}
function Segment($segmentName)      { return viewsFolder() . "/$segmentName.php"        ;}

function http_get(string $url, array $data = null, array $headers = null, int $timeout=3):bool {
    return Http::exec("GET", $url, $data, $headers, $timeout);
}

function http_post(string $url, array $data = null, array $headers = null, int $timeout=3):bool {
    return Http::exec("POST", $url, $data, $headers, $timeout);
}

function http_put(string $url, array $data = null, array $headers = null, int $timeout=3):bool {
    return Http::exec("PUT", $url, $data, $headers, $timeout);
}

function http_close() {
    return Http::close();
}

function http_response(bool $decode=false, bool $assoc=false) { return Http::response($decode, $assoc)  ;}
function http_error()                                         { return Http::error()                    ;}

function http_ping(string $url):bool {
    if (Http::exec("GET", "$url/ping")) return Http::response(true)->success;
    return false;
}


//Esta funcion es para volver desde un iframe a la página llamadora y que èsta (la llamadora) se abra en el top.windows 
//(sin quedar atrapado dentro del iframe)
function returnto($referer)         { echo "<script>top.window.location='$referer'</script>"; exit ;}


function run($script, $parm) { 
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
        "si"        => true,
        default     => false,
    };
}

function getDatastore($datastore="default") {
    return Model::getDatastore($datastore);
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

function renderLayout($layoutName, $__TMP_data = []) {
    ob_start();

    //Evitar colisión de nombre del array con las variables expandidas 
    extract($__TMP_data);
    unset($__TMP_data);

    include __DIR__ . "/layouts/$layoutName.php" ;
    return ob_get_clean();
}

function loadConfig($fileName):array {
    $result = [];

    if (is_readable($fileName)) {
        $lines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if ($line && $line[0]!="#" && strpos($line,"=")>0) {
                [$name, $value] = explode('=', $line, 2);
                $name  = trim($name);
                $value = trim($value);
                if (!empty($value)) $result[$name] = $value;
            }
        }
    }

    return $result;
}
