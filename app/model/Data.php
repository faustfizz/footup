<?php
namespace App\Model;
use Core\Model;

class Data extends Model{

    public function __construct()
    {
        // Ajouter cette ligne pour initier une connection à une base de données
        // le parametre << ...func_get_args() >> est pour qu'on puisse initier une connexion 
        // à la base au moment de l'initialisation de ce model
        parent::__construct(...func_get_args());

    }
}