<?php 
namespace rutex;

/** 
 * @author jcruchaga@zonafranja.com>
 * 
 * Router para proyectos con PHP
 */

const ROUTE_VERSION = "2.6";
const VIEWS_FOLDER  = "../app/views";

class Route {

    //function estanova() {}
    static private $routes = [];
    static $lastEntry, $currentEntry;

    static function version() {return ROUTE_VERSION;}
    static private function viewsFolder()   { return $_ENV["VIEWS_FOLDER"] ?? VIEWS_FOLDER; }
    static private function htmlErrorPage() { return $_ENV["HTML_ERROR_PAGE"] ?? __DIR__ . "/htmlErrorPage"; }

    static function get     ($uri, $callback, $folder="") {self::add("GET"    , $uri, $callback         , $folder, false);}
    static function post    ($uri, $callback, $folder="") {self::add("POST"   , $uri, $callback         , $folder, false);}
    static function put     ($uri, $callback, $folder="") {self::add("PUT"    , $uri, $callback         , $folder, false);}
    static function delete  ($uri, $callback, $folder="") {self::add("DELETE" , $uri, $callback         , $folder, false);}
    static function frameset($uri, $folder="")            {self::add("GET"    , $uri, "framesController", $folder, true) ;}

    static private function add($method, $uri, $callback, $folder="", $isFrameset=false) {
        $uri = trim(strtolower($uri), '/');

        self::$routes[$method][$uri]["callback"]   = $callback;
        self::$routes[$method][$uri]["isframeset"] = $isFrameset;
        //self::$routes[$method][$uri]["folder"]     = (empty($folder))?$uri:$folder;

        //Frameset necesita el folder para encontrar frameset.php
        //sino cargar el parametro real que será utilizado en doCallback() para encontrar el script
        if ($isFrameset) self::$routes[$method][$uri]["folder"] = (empty($folder))?$uri:$folder;
        else             self::$routes[$method][$uri]["folder"] = $folder;

        //Puntero al ultimo Entry, para ser usado cuando se implemenen otras propiedades con fluent
        //ejemplo: middlewares
        self::$lastEntry = &self::$routes[$method][$uri];
    }

    static function listen() {
        $method  = strtoupper($_POST["_method"] ?? $_SERVER["REQUEST_METHOD"]);
        $path    = trim(strtolower(preg_replace("#\?(.*)#", "", $_SERVER["REQUEST_URI"])), "/");
        $isPath  = false;

        //El unico path permitido sin referer es "engine"
        if ($path=="rutex.engine" && $method=="GET") {include "engine.php"; exit;}

        if (!empty($path) && empty($_SERVER["HTTP_REFERER"]) && !getenv("ALLOW_URL_LINKS")) return header("location:/");

        if (isset(self::$routes[$method])) {
            if (isset(self::$routes[$method][$path])) {
                $isPath      = true;
                $routesEntry = self::$routes[$method][$path];
                $parm        = [];
            } 
            else {
                //posible ruta con parameros
                foreach(self::$routes[$method] as $uri => $routesEntry) {
                    //saltear las rutas que no tieen parametros embebidos en el path (ya fueron procesadas en las rapidas del paso anterir)
                    if (strpos($uri, ":")==0) continue;

                    //Pattern para extraer los valores que vienen en la url
                    $pattern = "#^" . preg_replace("#:([a-zA-Z0-9-_]+)#", "([a-zA-Z0-9-_]+)", $uri) . "$#";

                    //extraer los nombres de los parametros
                    preg_match_all("#:([a-zA-Z0-9-_]+)#", $uri, $matches);
                    $anames =  (array) $matches[1];

                    if (preg_match($pattern, $path, $matches)) {
                       //RUta encontrada
                       $isPath = true;
                       $parm   = array_combine($anames, array_slice($matches, 1));
                       break;
                    }
                }
            }
        }

        if ($isPath) {
            //Usado por funcion framesController para ubicar el folder del frameset
            self::$currentEntry = $routesEntry;

            if (!$routesEntry["isframeset"]) $_SESSION["request_uri"] = $_SERVER["REQUEST_URI"];
            $response = self::doCallback($routesEntry, $parm);
        }
        else $response = self::htmlError("404", "not found path:", "($method) " . preg_replace("#\?(.+)#", "", $_SERVER["REQUEST_URI"]));

        echo $response;
        exit;
    }

    static function view($route, $parm = []) {
        $result = self::viewNameResolve($route);
        if ($result["success"]) {
            ob_start();
            extract($parm);
            include $result["content"];
            return ob_get_clean();
        } 
        else return $result["content"];
    }

    static function htmlError($code="404", $msg="NOT Found", $uri="") {
        return self::view(self::htmlErrorPage(), ["code" => $code, "msg" => $msg, "uri" => $uri]);
    }

    static private function viewNameResolve($route) {
        //En Este framework TODOS los scripts son .php (insluso los html puros)
        //Los nombres de los scripts se indican SIN EXTENSION 
        $scriptBaseName = self::viewsFolder() . "/{$route}";
        if     (file_exists("$scriptBaseName.php"))  $scriptName = "$scriptBaseName.php";
        // elseif (file_exists("$scriptBaseName.html")) $scriptName = "$scriptBaseName.html";
        // elseif (file_exists($scriptBaseName))        $scriptName = $scriptBaseName;
        elseif (file_exists("$route.php"))           $scriptName = "$route.php";
        // elseif (file_exists("$route.html"))          $scriptName = "$route.php";
        // elseif (file_exists($route))                 $scriptName = $route;
        else   return self::result(false, "<h1>" . __FUNCTION__ . "() ERROR: Not found: {$route}</h1>");

        return self::result(true, $scriptName);
    }

    //METODOS PRIVADOS
    static private function result($success, $content) {
        return ["success" => $success, "content" => $content];
    }
 
    static private function doCallback($routesEntry, $parm = []) {
        //Analiza y procesa el callback
        $callback = $routesEntry["callback"];

        //indicando el folder al crear la ruta en web.php, se puede ejecutar un script
        //que esté en una ruta especifica (debajo de "..app/)
        if (empty($routesEntry["folder"])) $scriptFile = $callback;
        else $scriptFile = "../app/" . $routesEntry["folder"] . "/$callback";

        if (is_callable($callback)) $response = $callback($parm);
        else if (is_array($callback)) {
            $controller = new $callback[0];
            $response   = $controller->{$callback[1]}($parm);
        } else $response = self::view($scriptFile, $parm);

        //Devuelve el resultado (WEB o API)
        if (is_array($response) || is_object($response)) {
            header("Content-Type: application/json");
            $response = json_encode($response);
        } 

        return $response;
    }
}