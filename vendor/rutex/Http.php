<?php
namespace rutex;

/** 
 * @author jcruchaga@zonafranja.com>
 * 
 * clase para consumir apis
 */

class Http {
    static private $ch         = null;
    static private $actual_url = null;
    static private $buffer;

    static function get(string $url, array $data=null, array $headers=null, int $timeout=3):bool {
        return self::exec("GET", $url, $data, $headers, $timeout);
    }

    static function post(string $url, array $data=null, array $headers=null, int $timeout=3):bool {
        return self::exec("POST", $url, $data, $headers, $timeout);
    }

    static function put(string $url, array $data=null, array $headers=null, int $timeout=3):bool {
        return self::exec("PUT", $url, $data, $headers, $timeout);
    }

    static function exec(string $method, string $url, array $data=null, array $headers=null, int $timeout=3):bool {
        if (!self::$ch) {
            self::$ch = curl_init();
            curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, 2);
        }

        curl_setopt(self::$ch, CURLOPT_TIMEOUT, $timeout);

        switch (strtoupper($method)) {
            case "POST": curl_setopt(self::$ch, CURLOPT_POST, 1);
                         if ($data) curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $data);
                         break;
            case "PUT" : curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, "PUT");
                         if ($data) curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $data);
                         break;
            default    : if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
        }


        if (!self::$actual_url || self::$actual_url != $url) {
            self::$actual_url = $url;
            curl_setopt(self::$ch, CURLOPT_URL, $url);
        }

        if ($headers) curl_setopt(self::$ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt(self::$ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        self::$buffer = curl_exec(self::$ch); 

        return !curl_errno(self::$ch); 
    }

    static function close() {
        if (self::$ch) curl_close(self::$ch);
    }

    static function error() {
        return curl_error(self::$ch);
    }

    static function response(bool $decode=false, bool $assoc=false) {
        if ($decode) return json_decode(self::$buffer, $assoc);
        else return self::$buffer;
    }

}

