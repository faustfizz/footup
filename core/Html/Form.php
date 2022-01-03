<?php

/**
 * FOOTUP - 0.1.3 - 12.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @uses Html::h1("Content !", ["attr" => "value", "..." => "..."]) | where h1 is in $pairs or $impairs arrays
 * 
 * @package Footup\Html
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Html;

use App\Config\Form as ConfigForm;

class Form
{

    protected $output = "";
    protected $selectPart = "";
    public $action = "#";
    protected $fields = [];

    protected $config = [
        'open'      => true,
        'close'     => true,
        'from_db'   => true
    ];
    protected $submitText = "Envoyer";

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
    protected $class = array(
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
    
    /**
     * Contruct
     *
     * @param string $action
     * @param array $fields
     * @param array|null $data
     * @param [bool] ...$config
     */
    public function __construct(string $action = "#", array $fields, array $data = null, ...$config) {
        $this->config = array_merge($this->config, $config);
        $this->class = array_merge($this->class, ConfigForm::$class);
        $this->action = $action;
        $this->prepareFields($fields, $data);
    }

    public function add($name, array $attributes)
    {
        $this->fields[] = (object) ['name' => $name, "attributes" => $attributes];
        return $this;
    }

    public function build()
    {
        if($this->config['open'] === true)
        {
            $this->output .= $this->open($this->action);
        }

        foreach ($this->fields as $key => $field) {
            # code...
            $class = isset($this->class[$field->attributes["type"]]) ? $this->class[$field->attributes["type"]] : $this->class["default"];
            $field->attributes["class"] = isset($field->attributes["class"]) && !empty($field->attributes["class"]) ? $field->attributes["class"]." ".$class["input"] : $class["input"];

            $this->output .= 
            $field->attributes["type"] != "hidden" ? Html::div(
                Html::div(
                    Html::label(
                        ucwords(strtr($field->attributes["name"], ["_" => " "])),
                        ["class"    =>  $class["label"]]
                    ).
                    Html::{$field->name}(null, $field->attributes),
                    ["class" => $class[ "form_group"]]
                ), ["class" => $class[ "wrapper"]]
            ) : Html::{$field->name}(null, $field->attributes);
        }

        $this->output .= $this->selectPart." ". $this->submitBtn();

        if($this->config['close'] === true)
        {
            $this->output .= $this->close();
        }
        return $this;
    }

    public function open(string $action = "#")
    {
        return "<form action='{$action}' method='post' enctype='multipart/form-data'>";
    }

    public function close()
    {
        return "</form>";
    }

    private function submitBtn()
    {
        return Html::button($this->submitText, ["type" => "submit", "class" => $this->class['submit']]);
    }

    private function switchType(object $field)
    {
        $type = $field->crudType;
        switch($field->name)
        {
            case 'image':
            case 'picture':
            case 'photo':
            case 'cover':
                $type = "file";
                break;
            case 'password':
            case 'hash':
            case 'pass':
                $type = "password";
                break;
            case 'color':
            case 'colour':
            case 'couleur':
            case 'tint':
                $type = "color";
            case 'telephone':
            case 'phone':
            case 'phone_number':
            case 'tel':
                $type = "tel";
                break;
            case 'email':
            case 'mail':
            case 'mail_address':
                $type = "email";
                break;
        }
        $field->crudType = $type;
        return $field;
    }

    private function prepareFields(array $fields, $data = array())
    {
        if($this->config['from_db'] === true)
        {
            foreach($fields as $field)
            {
                $field = (object) $field;
                $field->type = $field->crudType;
                $field = $this->switchType($field);
        
                if($field->isPrimaryKey && (empty(array_values($data)) || empty($data) || !empty($data) && !isset($data[$field->name]))) continue;
        
                if($field->isPrimaryKey && isset($data[$field->name]))
                {
                    $field->crudType = $field->type = "hidden";
                }

                switch($field->crudType)
                {
                    case "select":
                        $this->dropdown((object)array_merge([
                            "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field ), $data);
                        break;
                    case "textarea":
                        $this->textarea((object)array_merge([
                            "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field ), $data);
                        break;
                    default:
                        $this->add(
                            "input",
                            array_filter([
                                "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                                "name"      =>  $field->name,
                                "id"        =>  $field->name,
                                "required"  =>  !$field->null,
                                "type"      =>  $field->crudType,
                                "maxLength" =>  $field->maxLength
                            ])
                        );
                }
            }
        }else{
            foreach($fields as $field)
            {
                $field = (object) $field;
                $field->crudType = $field->type;
                $field = $this->switchType($field);

                switch($field->crudType)
                {
                    case "select":
                        $this->dropdown((object)array_merge([
                            "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field), $data);
                        break;
                    case "textarea":
                        $this->textarea((object)array_merge([
                            "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field), $data);
                        break;
                    default:
                        $this->add(
                            "input",
                            array_merge([
                                "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                                "name"      =>  $field->name,
                                "id"        =>  $field->id
                            ], (array)$field)
                        );
                }
            }
        }

        return $this;
    }

    private function textarea(object $field, $data)
    {
        $class = $this->class["textarea"];

        $this->selectPart .= 
        Html::div(
            Html::div(
                Html::label(
                    ucwords(strtr($field->name, ["_" => " "])),
                    ["class"    =>  $class["label"]]
                ).
                Html::textarea(
                    isset($data[$field->name]) ? $data[$field->name] : $field->default,
                    array_filter(["class" => $class['input'], "name" =>  $field->name, "id" =>  $field->name, "required" => !$field->null, "maxLength" => $field->maxLength])
                )
                ,
                ["class" => $class[ "form_group"]]
            ),
            ["class" => $class[ "wrapper"]]
        );
    }

    private function dropdown(object $field, $data)
    {
        $class = $this->class["select"];

        $opt = "";
        foreach ($field->options as $key => $value) {
            # code...
            $attr = array("value" => $value);
            if($field->default && strtolower($field->default) == strtolower($value) || (isset($data[$field->name]) && $value == $data[$field->name]))
            {
                $attr['selected'] = true;
            }

            $attr = array_filter($attr);

            $opt .= Html::option(ucfirst($value), $attr);
        }
        $this->selectPart .= 
        Html::div(
            Html::div(
                Html::label(
                    ucwords(strtr($field->name, ["_" => " "])),
                    ["class"    =>  $class["label"]]
                ).
                Html::select(
                    $opt,
                    array_filter(["class" => $class['input'], "name" =>  $field->name, "id" =>  $field->name, "required" => !$field->null, "maxLength" => $field->maxLength])
                )
            ,
                ["class" => $class[ "form_group"]]
            ),
            ["class" => $class[ "wrapper"]]
        );
    }

    public function print($display = false)
    {
        if($display)
        {
            echo $this->output;
        }else{
            return $this->output;
        }
    }

    public function __call($name, $arguments)
    {
        $this->output .= Html::{$name}($arguments);

        throw new \Exception(text("Core.classNoMethod", [__CLASS__, $name]));
    }
    
    public function __toString()
    {
        $this->build()->print();
    }
    
}