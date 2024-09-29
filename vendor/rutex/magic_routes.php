<?php 
use rutex\Route;

Route::$magic_urls[] = "ping";
Route::get("/ping", function ($parm) { header("Content-Type: application/json"); return result(true, $_SERVER["REMOTE_ADDR"]); });

Route::$magic_urls[] = "engine";
Route::get("/engine", function ($parm) { include "engine.php"; exit; });

if (getenv("APP_RUN_MODE")=="DEV") {
    Route::$magic_urls[] = "phpinfo";
    Route::get("/phpinfo", function ($parm) { phpInfo(); exit;});
}

//RUTAS RELACIONADAS CON CONAX_SERVER
if (empty(getenv("CONAX_SERVER"))) {
    $_SESSION["CONAX_SERVER"] = false;
    return;
}

if (!isset($_SESSION["CONAX_SERVER"])) {
    //verificar (ONCE) que conax server responda
    $conax_server = trim(getenv("CONAX_SERVER"), "/");

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

    //javacript para invocar a conax
    Route::get("/rutex.js", function($parm) {
        $conax_server = $_SESSION["CONAX_SERVER"];
        ob_start();
        include "rutex.js";
        return ob_get_clean();
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