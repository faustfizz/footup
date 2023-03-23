<?php

/**
 * FOOTUP - 0.1.6 - 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * Ce fichier contient les fonctions globales du framework FOOTUP
 * Ce fichier fait partie du framework
 * 
 * @package Footup/Utils/Validator
 * @version 0.0.2
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Utils\Validator;

use Footup\Utils\Arrays\Arr;
use Footup\Utils\Arrays\ArrDots;
use Footup\Utils\Str;

class Validator
{
    const WILD = '*';

    /**
     * @var array Associative array of rule name to callable
     */
    protected $rules;

    /**
     * @var array Associative array of rule name to message
     */
    protected $messages;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var array
     */
    protected $processedErrors = [];

    /**
     * Labels of input  as [field => label]
     * 
     * @var string[]
     */
    protected $displayAs = [];

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * Validator constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Set the message for the rule with the given name.
     *
     * @param string $name
     * @param string $message
     * @return Validator
     */
    public function setRuleMessage(string $name, string $message)
    {
        $this->messages['rules'][$name] = $message;
        return $this;
    }

    /**
     * Set the message for the attribute with the given name.
     *
     * @param string $name
     * @param string $message
     * @return Validator
     */
    public function setAttributeMessage(string $name, string $message)
    {
        $this->messages['custom'][$name] = $message;
        return $this;
    }

    /**
     * Add a new rule to the validator.
     * ```php
     * function (Validator $validator, array $data, $pattern, $rule, array $parameters) {
     *     foreach ($validator->getValues($data, $pattern) as $attribute => $value) {
     *         if (null === $value) {
     *             continue;
     *         }
     *         if (in_array($value, $parameters)) {
     *             continue;
     *         }
     *
     *         $validator->addError($attribute, $rule, [':values' => implode(', ', $parameters)]);
     *     }
     * }
     * ```
     *
     * @param string $name
     * @param callable $callable
     * @return Validator
     * @see Validator::setRuleMessage()
     */
    public function addRule(string $name, callable $callable)
    {
        $this->rules[$name] = $callable;
        return $this;
    }

    /**
     * Resets the validator to its initial state.
     *
     * @return Validator
     */
    public function reset()
    {
        // Remove all rules and messages
        $this->rules = [];
        $this->messages = ['rules' => [], 'custom' => []];
        $this->clear();

        // Add the initial rules and messages
        Validate::addRuleSet($this);

        return $this;
    }

    /**
     * Clear the validator of all errors.
     *
     * @return Validator
     */
    public function clear()
    {
        $this->errors = [];
        $this->processedErrors = [];

        return $this;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Return a processed array of errors.
     *
     * @return array
     */
    private function processErrors()
    {
        $errors = [];

        foreach ($this->errors as $error) {
            // Process replacements
            $message = ArrDots::get($this->messages['custom'], $error['attribute']) ?? ArrDots::get($this->messages['rules'], $error['rule']);

            foreach ($error['replacements'] as $search => $replace) {
                $replace = strtr($replace, $this->displayAs);

                switch ($search[0]) {
                    case ':':
                        $message = str_replace($search, Str::prettyAttribute($replace), $message);
                        break;
                    case '!':
                        if (!$replace) {
                            break;
                        }
                        // Check if the attribute is singular (use group 1) or plural (use group 2)
                        // Group 2 if plural, group 1 if singular
                        $replace = substr($error['replacements'][':attribute'] ?? '', -1, 1) !== static::WILD
                            ? '$1' : '$2';
                        $message = preg_replace("/$search/", $replace, $message);
                        break;

                    case '%':
                    default:
                        $message = str_replace($search, $replace, $message);
                        break;
                }
            }
            $errors[$error['attribute']][$error['rule']] = strtr($message, $this->displayAs);
        }
        
        return $this->processedErrors = $errors;
    }

    /**
     * Return a processed array of errors.
     *
     * @return array
     */
    public function getProcessedErrors()
    {
        if(empty($this->processedErrors))
        {
            return $this->processErrors();
        }
        
        return $this->processedErrors;
    }

    /**
     * Return a processed array of errors.
     *
     * @param string field
     * 
     * @return null|string|string[]
     */
    public function getErrors(string $field = null)
    {
        if($this->hasError($field))
        {
            return is_null($field) ? $this->processedErrors : $this->processedErrors[$field];
        }
        return [];
    }

    /**
     * Return a processed error string for the given field.
     *
     * @param string field
     * @param string|null rule
     * @return null|string
     */
    public function getError(string $field, $rule = null)
    {
        if($this->hasError($field, $rule))
        {
            return $rule ? $this->processedErrors[$field][$rule] : implode("\n", array_values($this->processedErrors[$field]));
        }
        return null;
    }

    /**
     * Return a processed array of errors.
     *
     * @param string field
     * @param string|null rule
     * @return bool
     */
    public function hasError(string $field = null, $rule = null)
    {
        if(empty($this->processedErrors))
        {
            $this->processErrors();

            if(is_null($field))
            {
                return $this->hasErrors();
            }
        }

        $hasError = isset($this->processedErrors[$field]) && !empty($this->processedErrors[$field]);

        if($rule && $hasError)
        {
            return isset($this->processedErrors[$field][$rule]) && !empty($this->processedErrors[$field][$rule]);
        }
        
        return $hasError;
    }

    /**
     * @param array|null|object $values
     * @param array             $ruleSet
     * @param string|null       $prefix
     *
     * @return bool true if validated false if not
     */
    public function validate($values, array $ruleSet, string $prefix = null) : bool
    {
        // It have done a validation before, we reset it
        if($this->hasErrors())
        {
            $this->clear();
        }

        // If there are no rules, there is nothing to validate
        if(empty($ruleSet)) {
            return false;
        }
        // If there are no values, there is nothing to validate
        if(empty($values)) {
            return false;
        }

        $currentPrefix = $this->prefix;
        if (!empty($prefix)) {
            $this->prefix .= $prefix . '.';
        }

        // For each pattern and its rules
        foreach ($ruleSet as $pattern => $rules) {
            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }
            foreach ($rules as $rule) {
                list($rule, $parameters) = array_pad(explode(':', $rule, 2), 2, '');
                $parameters = array_map('trim', explode(',', $parameters));

                if (Arr::exists($this->rules, $rule)) {
                    call_user_func($this->rules[$rule], $this, $values, $pattern, $rule, $parameters);
                }
            }
        }
        $this->prefix = $currentPrefix;

        return !$this->hasErrors();
    }

    /**
     * @param string      $attribute
     * @param string      $rule
     * @param array       $replacements
     */
    public function addError($attribute, $rule, $replacements = [])
    {
        $replacements = array_merge([
            ':attribute'    => $this->prefix . $attribute,
            '!(\S+)\|(\S+)' => true,
        ], $replacements ?? []);

        $this->errors[] = [
            'attribute'    => $this->prefix . $attribute,
            'rule'         => $rule,
            'replacements' => $replacements,
        ];

        $this->messages['rules'][$rule] = lang("validator.".$rule);
    }

    /**
     * @param array  $array
     * @param string $pattern
     *
     * @return \Generator
     */
    public static function getValues(&$array, $pattern)
    {
        foreach (ArrDots::collate($array, $pattern, static::WILD) as $attribute => $value) {
            yield $attribute => $value;
        }
    }

    /**
     * @param array $array
     * @param string $pattern
     *
     * @return mixed|null First matching value or null
     */
    public static function getValue(&$array, $pattern)
    {
        $imploded = ArrDots::implode($array);
        $pattern  = sprintf('/^%s$/', str_replace(static::WILD, '[0-9]+', $pattern));

        foreach ($imploded as $attribute => $value) {
            if (preg_match($pattern, $attribute) == 0) {
                continue;
            }

            return $value;
        }

        return null;
    }

    /**
     * Set labels of input as [field => label]
     *
     * @param  string[]  $displayAs  Labels of input as [field => label]
     * @param bool $merge if you want to merge | default is a replacement
     *
     * @return  Validator
     */ 
    public function setDisplayAs($displayAs, $merge = false)
    {
        $this->displayAs = $merge ? array_merge($this->displayAs, $displayAs) :$displayAs;

        return $this;
    }
}
