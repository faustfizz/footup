<?php

/**
 * Hard Coded by Faust
 */

namespace App\Controller;

class Home extends BaseController
{
    public function index()
    {
        if ($this->request->method() === 'post') {
            $image = $this->request->file('image');
            // $image->save();
            echo $image->name;
        }

        return $this->view("accueil", [
            "titre" => "Accueil"
        ]);
    }
    
}
