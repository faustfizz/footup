<?php

/**
 * Ce fichier est utilisé pour servir de configuration seulement 
 */

namespace App\Config;

class Form
{
    public static $config = [
        'open'      => true,
        'close'     => true,
        'from_db'   => true
    ];

    public static function submitBtn()
    {
        return "<button type='submit' class='btn btn-success'> Envoyer </button>";
    }

    public static function getHtmlInput(object $field, string $validityClass = '')
    {
        switch ($field->type) {
            case 'select':
                return "<div class='form-group mb-3'>
                            <div class='form-floating'>
                                <select class='form-select $validityClass' id='floating{$field->name}' name='{$field->name}' aria-label='".($field->label ?? ucfirst($field->name))."' $field->inline_attributes>
                                    ".implode('\n', $field->htmlOptions)."
                                </select>
                                <label for='floating{$field->name}'>".($field->label ?? ucfirst($field->name))."</label>
                            </div>
                        </div>";
            case 'checkbox':
            case 'radio':
                return "<div class='mb-3'>
                            <input type='radio' class='btn-check $validityClass' name='{$field->name}' id='btn-check-{$field->name}' autocomplete='off' $field->inline_attributes>
                            <label class='btn btn-outline-success' for='btn-check-{$field->name}'>".($field->label ?? ucfirst($field->name))."</label>
                        </div>";
            case 'file':
                return "<div class='mb-3'>
                            <label for='{$field->name}' class='form-label'>Choose file</label>
                            <input type='file' class='form-control $validityClass' name='{$field->name}' id='{$field->name}' $field->inline_attributes />
                        </div>";
            case 'hidden':
                return "<input type='hidden' class='form-control $validityClass' value='$field->value' name='{$field->name}' id='{$field->name}' $field->inline_attributes />";
            case 'textarea':
                return "<div class='form-group mb-3'>
                            <div class='form-floating'>
                                <textarea class='form-control $validityClass' id='floating{$field->name}' name='{$field->name}' style='height: 100px' $field->inline_attributes>$field->value</textarea>
                                <label for='floating{$field->name}'>".($field->label ?? ucfirst($field->name))."</label>
                            </div>
                        </div>";
            case 'text':
            case 'tel':
            case 'email':
            case 'password':
            case 'time':
            case 'color':
            case 'datetime':
            case 'date':
            case 'datetime-local':
            case 'number':
            case 'month':
                default:
                    return "<div class='form-group mb-3'>
                                <div class='form-floating'>
                                    <input type='{$field->type}' class='form-control $validityClass' id='floating{$field->name}' name='{$field->name}' value='$field->value' $field->inline_attributes>
                                    <label for='floating{$field->name}'>".($field->label ?? ucfirst($field->name))."</label>
                                </div>
                            </div>";
        }
    }
    

}

