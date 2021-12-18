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
    
    /**
     * Contruct
     *
     * @param string $action
     * @param array $fields
     * @param array|null $data
     * @param [bool] ...$config
     */
    public function __construct(string $action = "#", array $fields, array $data = null, ...$config) {
        array_merge($this->config, $config);
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
            $this->output .= 
            $field->attributes["type"] != "hidden" ? Html::div(
                Html::label(
                    ucwords(strtr($field->attributes["name"], ["_" => " "])),
                    ["class"    =>  self::$class["label"]]
                ).
                Html::{$field->name}(null, $field->attributes),
                ["class" => "form-group"]
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
        return Html::button($this->submitText, ["type" => "submit", "class" => self::$class['submit']]);
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
                            "class"     => self::$class['default']." ".self::$class[$field->type],
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field ), $data);
                        break;
                    case "textarea":
                        $this->textarea((object)array_merge([
                            "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                            "class"     => self::$class['default']." ".self::$class[$field->type],
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field ), $data);
                        break;
                    default:
                        $this->add(
                            "input",
                            array_filter([
                                "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                                "class"     =>  self::$class['default']." ".self::$class[$field->crudType],
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
                            "class"     => self::$class['default']." ".self::$class[$field->type],
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field), $data);
                        break;
                    case "textarea":
                        $this->textarea((object)array_merge([
                            "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                            "class"     => self::$class['default']." ".self::$class[$field->type],
                            "name"      =>  $field->name,
                            "id"        =>  $field->id
                        ], (array)$field), $data);
                        break;
                    default:
                        $this->add(
                            "input",
                            array_merge([
                                "value"     =>  isset($data[$field->name]) ? $data[$field->name] : $field->default,
                                "class"     => self::$class['default']." ".self::$class[$field->type],
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
        $this->selectPart .= 
        Html::div(
            Html::label(
                ucwords(strtr($field->name, ["_" => " "])),
                ["class"    =>  self::$class["label"]]
            ).
            Html::textarea(
                isset($data[$field->name]) ? $data[$field->name] : $field->default,
            array_filter(["class" => self::$class['default'], "name" =>  $field->name, "id" =>  $field->name, "required" => !$field->null, "maxLength" => $field->maxLength]))
            ,
            ["class" => "form-group"]
        );
    }

    private function dropdown(object $field, $data)
    {
        $opt = "";
        foreach ($field->options as $key => $value) {
            # code...
            $attr = array_filter(array("value" => $value, "selected" => (!$field->default && strtolower($field->default) == strtolower($value) || (isset($data[$field->name]) && $value == $data[$field->name] ? $data[$field->name] : $field->default))));

            $opt .= Html::option(ucfirst($value), $attr);
        }
        $this->selectPart .= 
            Html::div(
                Html::label(
                    ucwords(strtr($field->name, ["_" => " "])),
                    ["class"    =>  self::$class["label"]]
                ).
                Html::select(
                $opt,
                array_filter(["class" => self::$class['default'], "name" =>  $field->name, "id" =>  $field->name, "required" => !$field->null, "maxLength" => $field->maxLength])),
                ["class" => "form-group"]
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

        throw new \Exception(__CLASS__ . ' not such method[' . $name . ']');
    }
    
    public function __toString()
    {
        $this->build()->print();
    }
    
}