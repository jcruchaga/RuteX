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

    static function get(string $url, array $data=null, array $headers=null, int $timeout=3):array {
        return self::exec("GET", $url, $data, $headers, $timeout);
    }

    static function post(string $url, array $data=null, array $headers=null, int $timeout=3):array {
        return self::exec("POST", $url, $data, $headers, $timeout);
    }

    static function put(string $url, array $data=null, array $headers=null, int $timeout=3):array {
        return self::exec("PUT", $url, $data, $headers, $timeout);
    }

    static function delete(string $url, array $data=null, array $headers=null, int $timeout=3):array {
        return self::exec("DELETE", $url, $data, $headers, $timeout);
    }

    static function exec(string $method, string $url, array $data=null, array $headers=null, int $timeout=3):array {
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

        $headers[] = "X-RuteX-TS: " . time();
        curl_setopt(self::$ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt(self::$ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        self::$buffer = curl_exec(self::$ch);
        $success      = false;
        
        if (curl_errno(self::$ch)) self::$buffer = curl_error(self::$ch);
        else $success = true;

        return self::result($success, self::$buffer);
    }

    static function close() {
        if (self::$ch) curl_close(self::$ch);
    }

    static function error():array {
        return ["erno" => curl_errno(self::$ch), "errmsg" => curl_error(self::$ch)];
    }

    static function response(bool $decode=false) {
        if ($decode) return json_decode(self::$buffer, true);
        else return self::$buffer;
    }

    static private function result($success, $content) {
        return ["success" => $success, "content" => $content];
    }

}

