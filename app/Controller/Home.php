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

        /**
         * Using Shared Model
         */
        model("Contact")->paginate(2);
        echo model("Contact")->getPaginator()->displayLinks();

        return $this->view("accueil", [
            "titre" => text("Core.classNotFound", ["Home"])
        ]);
    }
    
}
