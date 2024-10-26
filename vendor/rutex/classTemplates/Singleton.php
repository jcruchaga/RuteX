<?php

class Singleton {
    // Variable estática para almacenar la instancia única
    private static $instance;
 
    // Constructor privado para evitar la creación de instancias mediante "new"
    private function __construct() {
        // Lógica de inicialización, si es necesaria
    }

    private function __clone()
    {
        //singleton no se puede clonar
    }

    private function __wakeup() 
    {    
        //singleton no se puede desserializar
    }

    // Método estático para obtener la instancia única
    public static function getInstance(): Singleton {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
 
    // Métodos y propiedades de la clase
    // ...
}
