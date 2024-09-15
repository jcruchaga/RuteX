namespace app\models;
use rutex\Model;

class <?=$className?> extends Model {
    protected $table = "<?=strtolower($className)?>s";

    //Estructura de la tabla indicando los campos obligatorios en el insert
    protected $struct = [
        "id"         => ["required" => false    ],
        "fieldName1" => ["required" => true     ],
        "fieldName2" => ["required" => false    ],
        "fieldName3" => ["required" => true     ],
    ];
}