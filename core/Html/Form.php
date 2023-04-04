<?php

/**
 * FOOTUP - 0.1.6 2021 - 2023
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
            "form_group"    =>  "form-check form-switch d-flex align-items-center my-3",
            "label"         =>  "form-check-label mb-0 ms-2 order-1",
            "input"         =>  "form-check-input order-0"
        ],
        'radio'   =>  [
            "wrapper"       =>  "",
            "form_group"    =>  "form-check my-3",
            "label"         =>  "form-label",
            "input"         =>  "form-check-input order-0"
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
    public function __construct(string $action = "#", array $fields = null, array $data = []) {
        $this->config = array_merge($this->config, ConfigForm::$config);
        $this->submitText = trim(ConfigForm::$submitText);
        $this->class = array_merge($this->class, ConfigForm::$class);
        $this->action = $action;
        $this->prepareFields($fields, $data);
    }

    public function add($name, array $attributes)
    {
        $this->fields[$attributes['name']] = (object) ['name' => $name, "attributes" => $attributes];
        return $this;
    }

    public function build()
    {
        if($this->config['open'] === true)
        {
            $this->output .= $this->open($this->action)."<div class='row'>";
        }

        foreach ($this->fields as $key => $field) {
            # code...
            $class = isset($this->class[$field->attributes["type"]]) ? $this->class[$field->attributes["type"]] : $this->class["default"];
            $field->attributes["class"] = isset($field->attributes["class"]) && !empty($field->attributes["class"]) ? $field->attributes["class"]." ".$class["input"] : $class["input"];

            if(in_array($field->attributes["type"], ['date', 'datetime', 'month']) )
            {
                $field->attributes["type"] = "text";
            }

            if($field->attributes["type"] == "file" && in_array($field->attributes["name"], ['picture', 'cover', 'image', 'photo']))
            {
                $field->attributes["accept"] = ".jpeg,.png,.jpg";
            }

            if(in_array($field->attributes["type"], ['radio', 'checkbox']))
            {
                if(isset($field->attributes["required"]))
                    unset($field->attributes["required"]);

                if(isset($field->attributes["value"]) && !empty($field->attributes["value"]) && $field->attributes["value"] != 0)
                {
                    $field->attributes["checked"] = "true";
                }
            }

            if(isset($field->attributes["label"]))
            {
                $label = $field->attributes["label"];
                unset($field->attributes["label"]);
            }else{
                $label = $field->attributes["name"];
            }

            $this->output .= 
            $field->attributes["type"] != "hidden" ? Html::div(
                Html::div(
                    Html::label(
                        ucwords(strtr($label, ["_" => " ", "[" => "", "]" => ""])),
                        ["class"    =>  $class["label"], "for"  =>  $field->attributes["id"]]
                    ).
                    Html::{$field->name}(null, $field->attributes),
                    ["class" => $class[ "form_group"]]
                ), ["class" => $class[ "wrapper"]]
            ) : Html::{$field->name}(null, $field->attributes);
        }

        $this->output .= $this->selectPart." </div>". $this->submitBtn();

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
        return Html::button($this->submitText, ["type" => "submit", "class" => "mt-5 ".$this->class['submit']]);
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
            case 'cpassword':
            case 'hash':
            case 'pass':
                $type = "password";
                break;
            case 'color':
            case 'colour':
            case 'couleur':
            case 'tint':
                $type = "color";
                break;
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

    /**
     * Undocumented function
     *
     * @param array $fields
     * @param array $data
     * @return Form
     */
    public function prepareFields(array $fields = array(), $data = array())
    {
        if(empty($fields))
            return $this;


        if($this->config['from_db'] === true)
        {
            foreach($fields as $key => $field)
            {
                $field = (object) $field;
                $field->type = $field->crudType;
                $field = $this->switchType($field);
        
                if($field->isPrimaryKey && (empty(array_values($data)) || empty($data) || !empty($data) && !isset($data[$field->name]))) continue;
        
                if($field->isPrimaryKey && isset($data[$field->name]) || strtolower($field->name) === "slug")
                {
                    $field->crudType = $field->type = "hidden";
                }

                switch($field->crudType)
                {
                    case "radio":
                        $this->radio((object)array_merge([
                            "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field ), $data);
                        break;
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
                                "value"     =>  isset($data[$field->name]) && !in_array($field->name, ['password', 'pass', 'hash']) ? $data[$field->name] : $field->default,
                                "name"      =>  $field->name,
                                "label"     =>  isset($field->label) ? $field->label : $field->name,
                                "id"        =>  $field->name,
                                "required"  =>  !$field->null,
                                "type"      =>  $field->crudType,
                                "maxLength" =>  $field->maxLength
                            ])
                        );
                }
            }
        }else{
            foreach($fields as $key => $field)
            {
                $field = (object) $field;
                $field->crudType = $field->type;
                $field = $this->switchType($field);

                switch($field->crudType)
                {
                    case "radio":
                        $this->radio((object)array_merge([
                            "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field ), $data);
                        break;
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
                    ucwords(strtr(isset($field->label) ? $field->label : $field->name, ["_" => " ", "[" => "", "]" => ""])),
                    ["class"    =>  $class["label"], "for"  =>  $field->name]
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

    public function dropdown(object $field, $data)
    {
        $class = $this->class["select"];

        $opt = "";
        foreach ($field->options as $key => $value) {
            # code...
            if(is_array($value))
            {
                $attr = array("value" => $value['value']);

                if($field->default && strtolower($field->default) == strtolower($value['value']) || (isset($data[$field->name]) && $value['value'] == $data[$field->name]))
                {
                    $attr['selected'] = true;
                }

                $attr = array_filter($attr);

                $opt .= Html::option(ucfirst($value['label']), $attr);
            }else{
                $attr = array("value" => $value);
                if($field->default && strtolower($field->default) == strtolower($value) || (isset($data[$field->name]) && $value == $data[$field->name]))
                {
                    $attr['selected'] = true;
                }
    
                $attr = array_filter($attr);
    
                $opt .= Html::option(ucfirst($value), $attr);
            }
        }
        $this->selectPart .= 
        Html::div(
            Html::div(
                Html::label(
                    ucwords(strtr(isset($field->label) ? $field->label : $field->name, ["_" => " ", "[" => "", "]" => ""])),
                    ["class"    =>  $class["label"], "for"  =>  $field->name]
                ).
                Html::select(
                    $opt,
                    array_filter(["class" => $class['input'], "name" =>  $field->name, "id" =>  isset($field->id) ? $field->id : $field->name, "required" => !$field->null, "multiple" => isset($field->multiple) && $field->multiple === true])
                )
            ,
                ["class" => $class[ "form_group"]]
            ),
            ["class" => $class[ "wrapper"]]
        );
    }

    public function radio(object $field, $data)
    {
        $class = $this->class["checkbox"];
        
        $attr = ["class" => $class['input'], "name" =>  $field->name, "id" =>  isset($field->id) ? $field->id.'_inactive' : $field->name.'_inactive', "required" => !$field->null, "type" => "radio", "value" => 0];

        $this->selectPart .= 
        Html::div(
            Html::h6(
                ucwords(strtr(isset($field->label) ? $field->label : $field->name, ["_" => " ", "[" => "", "]" => ""])),
                ["class"    =>  "text-uppercase text-body text-xs font-weight-bolder mt-3"]
            ).
            Html::div(
                Html::ul(
                    Html::li(
                        Html::div(
                            Html::input(
                                "",
                                array_merge($attr, array_filter(["id" =>  isset($field->id) ? $field->id.'_active' : $field->name.'_active', "value" => 1, "checked" => 1 == $field->value ? true : false]))
                            ).
                            Html::label("Oui",
                                ["class"    =>  $class["label"], "for"  =>  $field->name.'_active']
                            ),
                            ['class' => "form-check form-switch ps-0"]
                        ),
                        ['class' => "list-group-item border-0 px-0"]
                    ).Html::li(
                        Html::div(
                            Html::input(
                                "",
                                array_merge($attr, array_filter(["value" => 0, "checked" => 0 == $field->value ? true : false]))
                            ).
                            Html::label("Non",
                                ["class"    =>  $class["label"], "for"  =>  $field->name.'_inactive']
                            ),
                            ['class' => "form-check form-switch ps-0"]
                        ),
                        ['class' => "list-group-item border-0 px-0"]
                    ),
                    ['class' => 'list-group']
                ),
                ["class" => "ps-5"]
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

        throw new \Exception(text("Core.classNoMethod", [$name, get_class()]));
    }
    
    public function __toString()
    {
        return $this->build()->print();
    }
    
}