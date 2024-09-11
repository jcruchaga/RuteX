<?php 
namespace rutex;
use rutex\Route;

class BaseController {
    function view($route, $parm = [])   { return Route::view($route, $parm) ;}
    function redirect($route)           { header("location: $route")        ;}
}
