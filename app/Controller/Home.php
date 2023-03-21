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
        // unsetText("core.classNotFound", null, "en_US");
        // echo text("Core.home", ["Home"], "en_US");

        return $this->view("accueil", [
            "titre" => text("Core.classNotFound", ["Home"])
        ]);
    }
    
}
