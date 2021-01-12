<?php
/**
 * FOOTUP - 0.1 - 12.01.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package FOOTUP/Model
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Core;
use App\Config\Config;
use mysqli;
use PDO;

class Model {
    public static $db = null;

    public function __construct($init = false, ?array $db_config = null, ?string $connector = "mysqli")
    {
        return self::db($init, $db_config, $connector);
    }

    public function __destruct()
    {
        self::$db = null;
    }

    static function db($init = false, $config = null, $connector = "mysqli"){
        if($init){
            Config::$config = empty($config) ? Config::$config : array_merge(Config::$config, $config);

            if(self::$db !== null) return self::$db;

            self::$db = $connector === "mysqli" ? (
                new mysqli(
                    Config::$config["db_host"], Config::$config["db_user"],
                    Config::$config["db_password"], Config::$config["db_name"],
                    Config::$config["db_port"]
                )
            ) :
            (new PDO(
                    "mysql:dbname=".Config::$config["db_name"].";host=".Config::$config["db_host"].";port=".Config::$config["db_port"],
                    Config::$config["db_user"],
                    Config::$config["db_password"]
                )
            );
            return self::$db;
        }else{
            return self::$db;
        }
    }

}