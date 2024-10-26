<?php 
use rutex\Route;

Route::get("/", "home");

if (getenv("APP_RUN_MODE")=="DEV") {
    Route::$magic_urls[] = "playground";

    Route::$magic_urls[] = "phpinfo";
    Route::get("/phpinfo", function ($parm) { phpInfo(); exit;});
}

Route::$magic_urls[] = "ping";
Route::get("/ping", function ($parm):array {
    $clientTS = http_headers()["x_rutex_ts"] ?? false;
    if ($clientTS) $ElapTime = time() - $clientTS;
    else           $ElapTime = "unknown";

    return [
                "app_name"    => (getenv("APP_NAME"))?getenv("APP_NAME"):"unknown",
                "app_version" => (getenv("APP_VERSION"))?getenv("APP_VERSION"):"unknown",
                "remote_addr" => $_SERVER["REMOTE_ADDR"],
                "elap_time"   => $ElapTime,
           ];
});

Route::$magic_urls[] = "engine";
Route::get("/engine", function ($parm) { include "engine.php"; exit; });

//javacript del framework
Route::get("/rutex.js", function($parm) {
    $conax_server = $_SESSION["CONAX_SERVER"];
    ob_start();
    include "rutex.js";
    return ob_get_clean();
});

Route::get("/archive", function($parm) {
    if (isset($_GET["file"]) && strlen($_GET["file"]) > 16) {
        $fileName = decryptWS($_GET["file"]);
        if ($fileName && file_exists($fileName)) {
            header("Content-Type: " . mime_content_type($fileName));
            include $fileName;
            exit;
        }
        else return "404 - Not found";
    }
    else return "404 - Not found";
});

/***************************************************************
    ACTIVACION DEL CONTROL DE ACCESO
***************************************************************/
if (!isset($_SESSION["CONAX_SERVER"])) {
    if (empty(getenv("CONAX_SERVER")))  $conax_server = "https://conax.zonafranja.com";
    else                                $conax_server = trim(getenv("CONAX_SERVER"), "/");

    //verificar (UNA SOLA VEZ EN LA SESSION) que conax server responda
    if (http_ping($conax_server)) $_SESSION["CONAX_SERVER"] = $conax_server;
    else $_SESSION["CONAX_SERVER"] = false;
}

if ($_SESSION["CONAX_SERVER"]) {
    //Cargar las rutas que solucionan el control de acceso

    //callback
    Route::$magic_urls[] = "conax_return";
    Route::get("/conax_return", function ($parm) {
        if (isset($_GET["data"])) {
            $response = json_decode(base64_decode($_GET["data"]), true);
            $_SESSION["user"] = $response["user"];
        }
        redirect($response["page"]);
    });

    Route::get("/logout", function ($parm) {
        unset($_SESSION["user"]);
        redirect($parm["referer"]);
    });

    //Rutas experimentales
    Route::get("/login", function ($parm) {
        $conax_server = $_SESSION["CONAX_SERVER"];

        return "<title>" . getenv("APP_NAME") . "</title>
                <link rel=stylesheet href=static/css/user_form.css>
                <div>
                    <iframe src='$conax_server/user/login' width=100% height=80% frameborder='0' scrolling='no'></iframe>
                </div>";

    });

}
