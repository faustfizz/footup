<?php
/**
 * Hard Coded by Faust
 */
namespace App\Controller;
use Core\Controller;
use App\Model\Data;

class Home extends Controller{

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        // $data = func_get_args();
        
        return $this->view("accueil", [
            "titre" => "Accueil"
        ]);
    }

}