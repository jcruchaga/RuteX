<?php 
    use rutex\Route;

    //carga las variables de ambiente
    if (is_readable("../.env")) {
        $lines = file("../.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if ($line && $line[0]!="#" && strpos($line,"=")>0) {
                [$name, $value] = explode('=', $line, 2);
                $name  = trim($name);
                $value = match(strtolower(trim($value))) {
                    "true"  => true,
                    "false" => false,
                    default => trim($value),
                };
                if (!empty($value)) putenv(sprintf('%s=%s', $name, $value));
            }
        }
    }

    //Configurar auto include de los archivos de clases
    spl_autoload_register(function($className) {

        $parse = explode('\\', $className);

        if (strtolower($parse[0])==="rutex") $script = __DIR__ . "/{$parse[1]}.php";
        else $script = '../' . preg_replace('/\\\\/', '/', $className) . '.php';

        if (is_readable($script)) include_once $script;
        else die("<h1>no se pudo cargar la clase $script ++ $className</h1>");
    });

    require_once __DIR__ . "/utiles.php";

    //Carga el archivo rutas de la app
    require_once "../app/web.php";

    session_start();
    Route::listen();
    return;


    function framesController($parm) {
        $path     = trim(strtolower(preg_replace("#\?(.*)#", "", $_SERVER["REQUEST_URI"])), "/");
    
        $folder   = Route::$currentEntry["folder"];
        $frameset = (include "../app/views/$folder/frameset.php");

        //setea el campo utilizado por el render para armar las rutas
        $frameset["path"] = $path;

        $frame = $_GET["frame"] ?? "panel";

        // if (!isset($_GET["frame"])) echo render("frameset", $frameset);
        // elseif ($frame != "panel" || empty($request_uri)) echo view("$name/$frame", $parm);
        // else redirect($request_uri);

        if (!isset($_GET["frame"])) echo render("frameset", $frameset);
        else echo view("$folder/$frame", $parm);

    }
