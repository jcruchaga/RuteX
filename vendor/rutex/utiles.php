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

function view($route, $parm=[])      { return Route::view($route, $parm)                                ;}
function result($success, $content)  { return ["success"=>$success,"content"=>$content]                 ;}
function viewsFolder()               { return $_ENV["VIEWS_FOLDER"] ?? "../app/views"                   ;}
function rutexLayout($layoutName)    { return __DIR__ . "/layouts/$layoutName.php"                      ;}
function redirect($route, $code=303) { header("location: $route", true, $code)                          ;}
function segment($segmentName)       { return viewsFolder() . "/segments/$segmentName.php"              ;}
function page($pageName)             { return viewsFolder() . "/$pageName.php"                          ;}
function isMobile()                  { return preg_match('/Mobi|Android/i', $_SERVER['HTTP_USER_AGENT']);}

function http_get(string $url, array $data = null, array $headers = null, int $timeout=3):array {
    return Http::exec("GET", $url, $data, $headers, $timeout);
}

function http_post(string $url, array $data = null, array $headers = null, int $timeout=3):array {
    return Http::exec("POST", $url, $data, $headers, $timeout);
}

function http_put(string $url, array $data = null, array $headers = null, int $timeout=3):array {
    return Http::exec("PUT", $url, $data, $headers, $timeout);
}

function http_close() {
    return Http::close();
}

function http_response(bool $decode=false) { return Http::response($decode)           ;}
function http_error()                      { return Http::error()                     ;}
function http_ping(string $url):bool       { return http::get("$url/ping")["success"] ;}

function http_headers() {
    $headers = [];
    foreach($_SERVER as $key => $value) {
        if (str_starts_with($key, "HTTP_"))
            $headers[strtolower(substr($key,5))] = $value;
    }
    return $headers;
}

//Esta funcion es para volver desde un iframe a la página llamadora y que èsta (la llamadora) se abra en el top.windows 
//(sin quedar atrapado dentro del iframe)
function returnto($referer)         { echo "<script>top.window.location='$referer'</script>"; exit ;}

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

function render($fileName, $__TMP_data=[]) {
    extract($__TMP_data);
    unset($__TMP_data);

    ob_start();
    include $fileName;
    return ob_get_clean();
}

function renderLayout($layoutName, $parm=[]) { return render(__DIR__ . "/layouts/$layoutName.php", $parm) ;}
function run($script, $parm)                 { return render("../app/controllers/$script.php", $parm)     ;}

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

function copy_folder($source_folder, $target_folder, $recursive=true, $verbose=false) {
    $count = 0;

    if (!file_exists($target_folder)) {
        mkdir($target_folder, 0777, true);
        if ($recursive) {
            if ($verbose) echo "mkdir <strong>$target_folder</strong><br>";
            $count++;
        }
    }

    foreach(scandir($source_folder) as $item_name) {
        if (!str_contains(".#..", $item_name)) {
            $source_filename = "$source_folder/$item_name";
            $target_filename = "$target_folder/$item_name";

            if (is_file($source_filename)) {
                    if (!file_exists($target_filename) || filemtime($source_filename) > filemtime($target_filename)) {
                        if ($verbose) echo "copy $source_filename to <strong>$target_filename</strong><br>";
                        copy($source_filename, $target_filename);
    
                        $count++;
                    }
                } 
            elseif ($recursive) 
                $count += copy_folder($source_filename, $target_filename, $recursive, $verbose);

        }
    }
    return $count;
}

function encrypt($string, $key) {
    $iv = openssl_random_pseudo_bytes(16);  //openssl_cipher_iv_length('aes-256-cbc'));
    return $iv . openssl_encrypt($string, 'aes-256-cbc', $key, 0, $iv);
}

function decrypt($string, $key) {
    $iv = substr($string, 0, 16);  //openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt(substr($string, 16), 'aes-256-cbc', $key, 0, $iv);
}
        
function encrypt64($string, $key) { return base64_encode(encrypt($string, $key)) ;}
function decrypt64($string, $key) { return decrypt(base64_decode($string), $key) ;}

function encryptWS($string)       { return encrypt64($string, session_id())      ;}
function decryptWS($string)       { return decrypt64($string, session_id())      ;}

function fileServer($fileName) {
    return '/archive?file="' . encryptWS($fileName) . '"';
 }