<?php

/**
 * Ce fichier est utilisé pour servir de configuration seulement 
 */

namespace App\Config;

class Form
{
    /**
     * Definir les class à appliquer dans champs ( inputs )
     * @example - "inputType" => [
     *  "wrapper"       =>  "col-lg-6",
     *  "form_group"    =>  "input-group input-group-outline my-3",
     *  "label"         =>  "form-label",
     *  "input"         =>  "form-control form-control-lg"
     * ]
     * @var array
     */
    public static $class = array(
        // for all input
        'default'   =>  [
            // div's class. e: col-lg-4
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg"
        ],
        'text'      =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg"
        ],
        'tel'       =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg"
        ],
        'textarea'   =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-static my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg"
        ],
        'select'   =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-static my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-select form-select-lg"
        ],
        'checkbox'   =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "form-check form-switch my-3",
            "label"         =>  "form-check-label",
            "input"         =>  "form-check-input"
        ],
        'radio'   =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "form-check my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-check-input"
        ],
        'date'      =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg date datepicker"
        ],
        'datetime'      =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg datetime datetimepicker"
        ],
        'time'      =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg time timepicker"
        ],
        'month'      =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg date datepicker"
        ],
        'file'       =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control form-control-lg"
        ],
        'color'      =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "input-group input-group-outline my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-control-color form-control-lg"
        ],
        'submit'    =>  "btn btn-outline-primary"
    );

}

