<?php

/**
 * Hard Coded by Faust
 */

namespace App\Controller;

use App\Model\Contact;
use Footup\Config\DotEnv\DotEnv;

class Home extends BaseController
{
    public function index()
    {
        if ($this->request->method() === 'post') {
            $image = $this->request->file('image');
            // $image->save();
            echo $image->name;
        }
        
        $contact = new Contact([
            "idcont"    =>  12,
            "nom"       =>  "Faiz",
            "email"     =>  "ifaz@io.io",
            "objet"     =>  "Quittance",
            "motif"     =>  "Plainte",
            "message"   =>  "Lorem ipsum dolor sit amet consectetur adipisicing elit. Consequatur vero nemo natus eos autem quidem rem omnis? Impedit aperiam dolorem officia? Sint quos culpa, tempore possimus officiis a sed unde."
        ]);
        
        // $contact->getForm("#", [], true);
        
        var_dump(request()->env('email'));
        // echo request("name");

        return $this->view("accueil", [
            "titre" => "Accueil"
        ]);
    }
    
}
