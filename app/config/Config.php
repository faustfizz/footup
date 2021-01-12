<?php
/**
 * FOOTUP - 0.1 - 12.01.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package FOOTUP/Config
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace App\Config;

class Config{
    public static $config = [
        // Configuration des dossiers
        /**
         * Dissier contenant les fichiers de votre application
         */
        "app_path" => "../app/",
        /**
         * Dissier contenant les fichiers de configuration de votre application
         */
        "config_path" => "../app/config/",
        /**
         * Dissier contenant les fichiers de vues de votre application
         */
        "view_path" => "../app/view/",
        /**
         * Extension des vues
         */
        "view_ext" => ".php",
        /**
         * Nom du controlleur par défaut
         */
        "default_controller" => "Home",
        /**
         * Méthode par défaut
         */
        "default_method" => "index",

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
        "db_password"   => "",
        /**
         * Nom de la base de données
         */
        "db_name"   => "iut_rapport",
        /**
         * Port du serveur de la base de données
         */
        "db_port"   => "3306",
    ];

    public function __construct(?array $config = null)
    {
        if(!empty($config))
            self::$config = array_merge(self::$config, $config);
    }

}

/* End of file Controllername.php */
