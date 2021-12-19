<?php

/**
 * Ce fichier est utilisé pour servir de configuration seulement 
 */

namespace App\Config;

class Form
{
    /**
     * Definir les class à appliquer dans champs ( inputs )
     * @var array
     */
    public static $class = array(
        // for all input
        'default'   =>  "form-control",
        // for input type text
        'form_group'=>  "form-group",
        'label'     =>  "form-control-label",
        'text'      =>  "",
        'tel'      =>  "",
        'textarea'  =>  "",
        'file'      =>  "",
        'date'      =>  "date",
        'month'      =>  "date",
        'datetime'  =>  "datetime",
        'number'    =>  "",
        'checkbox'  =>  "",
        'hidden'  =>  "",
        'email'     =>  "",
        'password'  =>  "",
        'radio'     =>  "",
        'select'    =>  "",
        'submit'    =>  "btn btn-outline-primary"
    );
}

