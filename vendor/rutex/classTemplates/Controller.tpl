namespace app\controllers;
use rutex\BaseController;

class <?=$className?>Controller extends BaseController {
    protected $className = "<?=$className?>";

    function index($parm) {
        //En $parm recibimos las variables del slug

        //En esta función accedemos a la base de datos hacemos controles etc.

        //En $var cargamos Las variables que queramos pasar a la página
        //Ej: $var["title"] = "<?=$className?>";


        /* 
            El contenido de $var se pasará a la página como variables independientes
            Ej: Si se carga $var["title"] = "<?=$className?>" en la pagina existirá una variable $title
            que podrá ser usada dentro del html
        */

        $var["title"] = "<?=$className?>";
        
        //renderizar la página
        return $this->view("<?=strtolower($className)?>/index", $var);
    }
}