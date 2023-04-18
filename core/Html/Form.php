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
    public $action = "#";
    protected $fields = [];

    protected $config = [
        'open'      => true,
        'close'     => true,
        'from_db'   => true
    ];

    public ConfigForm $ConfigForm;
    
    /**
     * Contruct
     *
     * @param string $action
     * @param array $fields
     * @param array|null $data
     */
    public function __construct(string $action = "#", array $fields = null, array $data = []) {
        $this->config = array_merge($this->config, ConfigForm::$config);
        $this->action = $action;
        $this->ConfigForm = new ConfigForm;
        $this->prepareFields($fields, $data);
    }

    /**
     * @param array|object $field
     * @param array $data
     * @return self
     */
    public function addHtmlInput($field, array $data)
    {
        $field = is_array($field) ? (object)$field : $field; 
        if(!empty($field->isPrimaryKey) && !empty($data[$field->name]))
        {
            $field->crudType = $field->type = "hidden";
        }

        $field->value = isset($data[$field->name]) && !in_array($field->name, ['password', 'pass', 'hash']) ? $data[$field->name] : $field->default;

        $field->inline_attributes = $this->inlineAttributes($field);

        if(!empty($field->options)){
            $field->htmlOptions = $this->buildOptions($field, $data);
        }
        $html = $this->ConfigForm::getHtmlInput($field);
            
        $this->fields[$field->name] = $html;

        return $this;
    }

    public function build()
    {
        if($this->config['open'] === true)
        {
            $this->output .= $this->open($this->action)."<div class='row'>";
        }

        foreach ($this->fields as $fieldName => $fieldHtml) {
            # code...
            $this->output .= $fieldHtml;
        }

        $this->output .= $this->ConfigForm::submitBtn();

        if($this->config['close'] === true)
        {
            $this->output .= "</div> ".$this->close();
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
        $field->crudType = $field->type = $type;
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


        foreach($fields as $name => $field)
        {
            $field = (object) $field;
            $field->type = $field->crudType;
            $field = $this->switchType($field);

            if(!empty($field->isPrimaryKey) && (empty($data) || empty($data[$field->name])) ) continue;
            
            $this->addHtmlInput(
                    $field,
                    $data
                );
        }

        return $this;
    }

    public function inputAttributes()
    {
        return [
            "name",
            "id",
            "accept",
            "placeholder",
            "required",
            "value",
            "autocomplete",
            "disabled",
            "checked"
        ];
    }

    public function inlineAttributes(object $field)
    {
        $attrsString = "";
        $acceptedAttrs = $this->inputAttributes();
        $field->checked =  ($field->value && !$field->null || $field->value == $field->default) && in_array($field->type, ['radio', 'checkbox']);
        if($field->type == "file" && in_array($field->name, ['picture', 'cover', 'image', 'photo']))
        {
            $field->accept = ".jpeg,.png,.jpg";
        }
        $attrs = array_intersect_key((array)$field, array_flip($acceptedAttrs));
        // filter values
        $attrs = array_filter($attrs);

        foreach (array_filter($attrs) as $attr => $value) {
            # code...
            $attrsString .= " $attr='$value'";
        }
        return $attrsString;
    }

    public function buildOptions(object $field, $data)
    {
        $opt = [];
        foreach ($field->options as $key => $value) {
            # code...
            if(is_numeric($key))
            {
                $attr = array("value" => strtolower($value));

                if($field->default && strtolower($field->default) == strtolower($value['value']) || (isset($data[$field->name]) && $value['value'] == $data[$field->name]))
                {
                    $attr['selected'] = true;
                }
            }else{
                $attr = array("value" => strtolower($key));
                
                if($field->default && strtolower($field->default) == strtolower($value) || (isset($data[$field->name]) && $value == $data[$field->name]))
                {
                    $attr['selected'] = true;
                }
            }
            $attr = array_filter($attr);

            $opt[] = Html::option(ucfirst($value), $attr);
        }
        return $opt;
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