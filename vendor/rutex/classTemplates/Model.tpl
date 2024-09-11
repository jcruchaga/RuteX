namespace app\models;
use rutex\Model;

class <?=$className?> extends Model {
    protected $table = "<?=strtolower($className)?>s";

    public $id, $fieldName1, $fieldName2;  //crear una variable publica para cada fieldName

    //Estructura de la tabla indicando los campos obligatorios en el insert
    protected $struct = [
         "id"         => false,
         "fieldName1" => true,
         "fieldName2" => true,
         "fieldName3" => false,
    ];
}