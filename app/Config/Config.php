<?php
/**
 * FOOTUP - 0.1 - 12.01.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/App/Config
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace App\Config;

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
        "lang"        =>  "fr",
        "timezone"    => 'Africa/Nairobi'
    ];

    /**
     * @var array
     */
    public $page_error = [
        "300s"      =>  BASE_PATH."error/300.html",
        "4s"        =>  BASE_PATH."error/400.html",
        "404s"      =>  BASE_PATH."error/404.html",
        "5s"        =>  BASE_PATH."error/500.html",
        "500s"      =>  BASE_PATH."error/500.html",
    ];

    /**
     * @var array
     */
    public $config = [
        // Configuration des dossiers
        /**
         * Dissier uploads pour les fichiers téléversés [relative to public dir]
         */
        "store_dir" => BASE_PATH."uploads/",
        /**
         * Dissier assets pour les fichiers statics css, js, img [relative to public dir]
         */
        "assets_dir" => BASE_PATH."assets/",
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
        "db_host"   => "localhost",
        /**
         * Utilisateur de la base de données
         */
        "db_user"   => "root",
        /**
         * Mot de passe de la base de données
         */
        "db_pass"   => "123456",
        /**
         * Nom de la base de données
         */
        "db_name"   => "test",
        /**
         * Port du serveur de la base de données
         */
        "db_port"   => "3306",
        /**
         * Nom de la base de données
         * 'pdomysql', 'pdopgsql', 'pdosqlite'
         */
        "db_type"   => "pdomysql"
    ];

    public function __construct(?array $config = null, ?array $page_error = null, ?array $locale = null)
    {
        $this->config = !empty($config) ? array_merge($this->config, $config) : $this->config;
        $this->page_error = !empty($page_error) ? array_merge($this->page_error, $page_error) : $this->page_error;
        $this->locale = !empty($locale) ? array_merge($this->locale, $locale) : $this->locale;
    }

}
