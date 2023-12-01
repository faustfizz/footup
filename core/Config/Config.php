<?php
/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Config
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Config;

use Footup\Config\DotEnv\DotEnv;
use Footup\Debug\Reporter;
use Locale;

class Config
{
    /**
     * @var string
     */
    public $base_url = "http://localhost";

    /**
     * @var array
     */
    public $locale = [
        "lang" => "fr",
        "timezone" => 'Africa/Nairobi'
    ];

    /**
     * @var array
     */
    public $page_error = [
        "300s" => BASE_PATH . "error/300.html",
        "4s" => BASE_PATH . "error/400.html",
        "404s" => BASE_PATH . "error/404.html",
        "5s" => BASE_PATH . "error/500.html",
        "500s" => BASE_PATH . "error/500.html",
    ];

    /**
     * @var array
     */
    protected $config = [
        // Configuration des dossiers
        /**
         * Dissier uploads pour les fichiers téléversés [relative to public dir]
         */
        "store_dir" => BASE_PATH . "uploads/",
        /**
         * Dissier assets pour les fichiers statics css, js, img [relative to public dir]
         */
        "assets_dir" => BASE_PATH . "assets/",
        /**
         * Extension des vues
         */
        "view_ext" => "php",
        /**
         * Nom du controlleur par défaut
         */
        "default_controller" => "Home",
        /**
         * Méthode par défaut
         */
        "default_method" => "index",

        /**
         * Environement de l'application
         * # possibles values: dev  || prod
         */
        "environment" => "dev",

        /**
         * Serveur de base de données
         */
        "db_host" => "localhost",
        /**
         * Utilisateur de la base de données
         */
        "db_user" => "root",
        /**
         * Mot de passe de la base de données
         */
        "db_pass" => "123456",
        /**
         * Nom de la base de données
         */
        "db_name" => "udc",
        /**
         * Port du serveur de la base de données
         */
        "db_port" => "3306",
        /**
         * Nom de la base de données ( ça utilise pdo )
         * 'mysql', 'pgsql', 'sqlite'
         */
        "db_type" => "mysql"
    ];

    public function __construct(?array $config = null, ?array $page_error = null, ?array $locale = null)
    {
        $env = new DotEnv();

        // Not recommanded but we do it to be sure we get the right document_root
        $_SERVER['DOCUMENT_ROOT'] = BASE_PATH;

        $config = (array) ($config ? ["config" => $config] : new \App\Config\Config());

        $this->config = !empty($config) ? array_merge($this->config, $config["config"]) : $this->config;
        $this->page_error = !empty($page_error) ? array_merge($this->page_error, $page_error) : $this->page_error;
        $this->locale = !empty($locale) ? array_merge($this->locale, $locale) : $this->locale;

        if (isset($_ENV["config"])) {
            $this->config = array_merge($this->config, $_ENV["config"]);
        }

        if (isset($_ENV["locale"])) {
            $this->locale = array_merge($this->locale, $_ENV["locale"]);
        }

        (new Reporter())->enableErrorHandler($this->config["environment"]);


        @date_default_timezone_set($this->locale['timezone']);

        /**
         * Default lang
         */
        if (function_exists("setlocale")) {
            \setlocale(LC_ALL, $this->locale["lang"]);
        }
        if (class_exists("Locale")) {
            Locale::setDefault($this->locale["lang"]);
        }

        if (isset($this->config['base_url'])) {
            $this->base_url = $this->config['base_url'];
            unset($this->config['base_url']);
        }
    }

    public function __set($name, $val)
    {
        if (property_exists($this, $name)) {
            return $this->{$name} = $val;
        }
        if (array_key_exists($name, $this->config)) {
            $this->config[$name] = $val;
        }
        if (array_key_exists($name, $this->locale)) {
            $this->locale[$name] = $val;
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (array_key_exists($name, $this->config)) {
            return $this->config[$name];
        }
        if (array_key_exists($name, $this->locale)) {
            return $this->locale[$name];
        }
        return null;
    }

}