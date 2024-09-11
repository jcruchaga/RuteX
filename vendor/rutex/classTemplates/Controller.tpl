namespace app\controllers;
use rutex\BaseController;

class <?=$className?>Controller extends BaseController {
    protected $className = "<?=$className?>";

    function index($parm) {
        //acá accedemos a la base de datos hacemos controles etc.
        //En $parm cargamos Los datos que queramos renderizar en la página
        //Ej: $parm["title"] = "<?=$className?>";


        /* 
            El contenido de $parm se expandirá en la página como variables independientes
            Ej: Si se carga $parm["title"] = "Products" en la pagina existirá una variable $title
            que podrá ser usada con el tag <?=$title?> 
        */

        $parm["title"] = "<?=$className?>";
        
        //renderizar la página con los parámetros que están en $parm
        return $this->view("<?=strtolower($className)?>/index", $parm);
    }
}