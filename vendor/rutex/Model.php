<?php
namespace rutex;

use mysqli;
use Exception;

const MODEL_VERSION = "2.5";

class Model {

    protected $dbconn;
    protected $cursor;

    //inherited al instanciar clase que hereda de Model, ej: User.php
    //Por defecto usa el datastore "default", eso se puede modificar en la clase de la entidad
    //Por ejemplo agregando: protected $datastore = "legacy"; y creando el archivo app/config/datastores/legacy.php
    protected $dbcfg, $table, $struct, $sqlVerbose, $datastore= "default";

    private $result, $record;

    public $current;

    //variables usadas para armar la condicion del Where
    protected $condition= [], $orCondition= [], $orderBy="";

    static function version() {return MODEL_VERSION;}

    public function __construct() {
        $this->dbcfg = getConfig("datastores/{$this->datastore}");

        $this->sqlVerbose = $this->dbcfg["SQL_VERBOSE"];
        $this->connect();
    }

    private function connect() {
        if (is_null($this->dbconn)) {
            try {

                $this->dbconn = new MySqlI($this->dbcfg["DB_HOST"], 
                                           $this->dbcfg["DB_USERNAME"],
                                           $this->dbcfg["DB_PASSWORD"], 
                                           $this->dbcfg["DB_DATABASE"], 
                                           $this->dbcfg["DB_PORT"]);

                $this->dbconn->autocommit($this->dbcfg["DB_AUTOCOMMIT"]);

                //FORZAR EL USO DE UTF8
                $this->dbconn->set_charset("utf8mb4");
                // $this->dbconn->set_charset("utf8");

            } catch(Exception $e) {
                die(htmlError("500", "Falló la conexión a la base de datos"));
                //throw new Exception("Falló la conexión a la base de datos. ErrMsg:{$e->getMessage()}");
            }
        }
    }

    //OBSERVABILIDAD
    function viewSelectCmd($fields="*", $page=0, $rows=16) {
        return $this->makeSelectCmd($fields, $page, $rows);
    }

    private function result($success, $content) {
        $this->result = ["success"=>$success, "content"=>$content];
    }

    function success() {
        return $this->result["success"];
    }

    function content() {
        return $this->result["content"];
    }

    private function BlankFields() {
        foreach($this->struct as $fieldName => $required) {
            $this->{$fieldName} = "";
        }
    }

    private function RequiredFields_Verify($data)  {
        $this->record   = [];
        $requiredFields = [];

        foreach($this->struct as $fieldName => $required) {
            if (isset($data[$fieldName]) && $data[$fieldName]) $this->record[$fieldName] = $data[$fieldName];
            else if ($required) $requiredFields[] = $fieldName;
        }

        $verified = empty($requiredFields);

        if ($verified) $this->result(true, "Todos los campos obligatorios están cargados");
        else           $this->result(false, "Faltan datos obligatorios: " . implode(", ", $requiredFields));

        return $verified;
    }


    private function query(string $sqlcmd) {
        $this->cursor = $this->dbconn->query($sqlcmd);

        $this->BlankFields();
        return $this;
    }

    function getWhereCondition() {
        if (count($this->orCondition) > 0) {
            $this->condition[] = $this->orCondition;
            $this->orCondition = [];
        }


        $strcond = "";
        $and     = "";
        foreach($this->condition as $orcollection) {
            $or = "";

            $ORCondition = "";
            $ORCount     = 0;
            foreach($orcollection as $cnd) {
                $ORCount++;

                $value = $this->dbconn->real_escape_string($cnd[2]);
                $ORCondition .= $or . "$cnd[0] $cnd[1] '$value'";

                $or= " or ";
            }

            if ($ORCount>1) $strcond .= $and . "($ORCondition)";
            else            $strcond .= $and .   $ORCondition;

            $and = " and ";
        }
 
        return $strcond;
    }

    private function makeSelectCmd($fields="*", $page=0, $rows=16) {
        $whereCondition = $this->getWhereCondition();

        if (empty($fields)) $fields = "*";
        else if (is_array($fields)) $fields = implode(",", $fields);

        if (empty($this->orderBy)) $orderBy = "";
        else $orderBy = "order by {$this->orderBy}";

        if ($page > 0 && $rows > 0) $limit = "limit " . (($page-1)*$rows) . "," .  $rows;
        else $limit = "";

        if (empty($whereCondition)) $sqlcmd= "select {$fields} from {$this->table}  {$orderBy} {$limit}";
        else $sqlcmd= "select {$fields} from {$this->table} where {$whereCondition} {$orderBy} {$limit}";

        return $sqlcmd;
    }


    function affected_rows() {
        return $this->dbconn->affected_rows;
    }


    function getFirst() {
        $this->current = $this->cursor->fetch_assoc();

        if ($this->current) {
            //Carga los campos en las variables de la entidad
            foreach($this->struct as $fieldName => $required) {
                if (isset($this->current[$fieldName])) 
                   $this->{$fieldName} = $this->current[$fieldName];
            }
        }

        return $this->current;
    }

    function getCursor() {
        return $this->cursor->fetch_all(MYSQLI_ASSOC);
    }

    function getById($id) {
        $id = $this->dbconn->real_escape_string($id);
        return $this->query("select * from {$this->table} where id={$id}")->getFirst();
    }

    function getAll($fields="*") {
        return $this->select($fields)->getcursor();
//        return $this->query("select * from {$this->table}")->getCursor();
    }


    //Armado de condicion de búsqueda
    function where(string $field, string $op, string $value) {
        $this->condition   = [];
        $this->orCondition = [[$field, $op, $value]];
        $this->orderBy     = "";

        $this->BlankFields();
        
        return $this;
    }

    function or (string $field, string $op, string $value) {
        $this->orCondition[] = [$field, $op, $value];
        return $this;
    }

    function and (string $field, string $op, string $value) {
        if (count($this->orCondition) > 0) {
            $this->condition[] = $this->orCondition;
            $this->orCondition = [];
        }

        $this->orCondition[] = [$field, $op, $value];;
        return $this;
    }

    function orderBy(string $orderBy) {
        $this->orderBy = $orderBy;
        return $this;
    }

    function totPages($rows=16) {
        $whereCondition = $this->getWhereCondition();
        if (empty($whereCondition)) $sqlcmd= "select count(*) as reccount from {$this->table}";
        else $sqlcmd= "select count(*) as reccount from {$this->table} where {$whereCondition}";

        $this->query($sqlcmd);
        $reccount = $this->cursor->fetch_assoc()["reccount"];
        return ceil($reccount / $rows);
    }
    
    function select($fields="*", $page=0, $rows=16) {
        $sqlcmd = $this->makeSelectCmd($fields, $page, $rows);
        return $this->query($sqlcmd);
    }

    function getPage($page=0, $rows=16) {
        $url  = "/" . trim(strtolower(preg_replace("#\?(.+)#", "", $_SERVER["REQUEST_URI"])), "/") . "?page=";

        $totPages = $this->totPages($rows);
        $page     = min(max($page, 1), $totPages);

        $this->select("*", $page, $rows);

        return [
            "firstPage_url" => $url . "1",
            "prevPage_url"  => ($page>1) ? $url . $page - 1 : null,
            "nextPage_url"  => ($page < $totPages) ? $url . $page + 1 : null,
            "lastPage_url"  => $url . $totPages,
            "lastPage"      => $totPages,
            "currentPage"   => $page,
            "data"          => $this->getCursor(),
        ];
    }

    function paginate($rows=16) {
        $page = $_GET["page"] ?? 1;
        return $this->getPage($page, $rows);
    }


     function insert(array $data) {
        //verificar que los campos a insertar estén en la estructura
        if (!$this->RequiredFields_Verify($data)) {
            if ($this->sqlVerbose) throw new Exception("ERROR on sql insert => {$this->content()}");
            return false;
        }

        $sqlcmd= "insert into {$this->table} (" . implode(",", array_keys($this->record)) . ") values (" . trim(str_repeat("?,", count($this->record)), ",") . ")";

        try {
            $stmt= $this->dbconn->prepare($sqlcmd);
            $stmt->execute(array_values($this->record));

            //recupera el registro recien guardado
            $insert_id = $this->dbconn->insert_id;

            $this->getById($insert_id);

            $this->result(true, $this->current);
            return true;

        } catch (Exception $e) {
            if ($this->sqlVerbose) throw new Exception("ERROR on sql insert => {$e->getMessage()}");
            $this->result(false, $e->getMessage());
            return false;
        }
    }


    function update(int $id, array $replacements) {
        $id     = $this->dbconn->real_escape_string($id);
        $fields = [];
        $values = [];

        //verificar que los campos a reemplazar estén en la estructura
        foreach($this->struct as $fieldName => $required) {
            if (isset($replacements[$fieldName])) {
                $fields[] = $fieldName;
                $values[] = $replacements[$fieldName];
            }
        }

        if (empty($fields)) {
            $this->result(false, "No hay campos para reemplazar");
            return false;
        }

        $sqlcmd = "update {$this->table} set " . implode('=?,', $fields) . "=? where id=$id";

        try {
            $stmt= $this->dbconn->prepare($sqlcmd);
            $stmt->execute($values);

            //recupera el registro recien actualizado
            $this->getById($id);
            $this->result(true, $this->current);
            return true;

        } catch (Exception $e) {
            if ($this->sqlVerbose) throw new Exception("ERROR on sql update => {$e->getMessage()}");
            $this->result(false, $e->getMessage());
            return false;
        }
    }

    function delete(int $id) {
        $id= $this->dbconn->real_escape_string($id);
        $this->query("delete from {$this->table} where id=$id");

        if ($this->affected_rows()>0) $this->result(true, "Registro Eliminado OK.");
        else $this->result(false, "NO se eliminaron registros");

        return ($this->success());
    }
}