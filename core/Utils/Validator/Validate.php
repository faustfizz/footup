<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Utils\Validator
 * @version 0.0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Utils\Validator;

use Countable;
use Footup\Utils\Arrays\ArrDots;
use Footup\Utils\Str;

class Validate
{
    /**
     * @param Validator $validator
     */
    public static function addRuleSet(Validator $validator)
    {
        $validator
            ->addRule('present', [static::class, 'present'])
            ->addRule('required', [static::class, 'required'])
            ->addRule('required-if', [static::class, 'requiredIf'])
            ->addRule('required-with', [static::class, 'requiredWith'])
            ->addRule('required-with-all', [static::class, 'requiredWithAll'])
            ->addRule('required-with-any', [static::class, 'requiredWithAny'])
            ->addRule('required-without', [static::class, 'requiredWithout'])

            ->addRule('equals', [static::class, 'equals'])
            ->addRule('not-equals', [static::class, 'notEquals'])
            ->addRule('identical', [static::class, 'identical'])
            ->addRule('not-identical', [static::class, 'notIdentical'])

            ->addRule('in', [static::class, 'in'])
            ->addRule('not-in', [static::class, 'notIn'])
            ->addRule('contains', [static::class, 'contains'])
            ->addRule('contains-only', [static::class, 'containsOnly'])
            ->addRule('min-arr-count', [static::class, 'minArrCount'])
            ->addRule('max-arr-count', [static::class, 'maxArrCount'])

            ->addRule('min', [static::class, 'min'])
            ->addRule('max', [static::class, 'max'])
            ->addRule('greater-than', [static::class, 'greaterThan'])
            ->addRule('less-than', [static::class, 'lessThan'])

            ->addRule('alpha', [static::class, 'alpha'])
            ->addRule('alpha-numeric', [static::class, 'alphaNumeric'])
            ->addRule('min-str-len', [static::class, 'minStrLen'])
            ->addRule('max-str-len', [static::class, 'maxStrLen'])
            ->addRule('str-len', [static::class, 'strLen'])
            ->addRule('human-name', [static::class, 'humanName'])

            ->addRule('is', [static::class, 'is'])
            ->addRule('password', [static::class, 'password'])

            ->addRule('email', [static::class, 'email'])
            ->addRule('date', [static::class, 'date'])
            ->addRule('url', [static::class, 'url'])
            ->addRule('uuid', [static::class, 'uuid'])

            ->addRule('card-number', [static::class, 'cardNumber'])

            ->addRule('regex', [static::class, 'regex'])
            ->addRule('not-regex', [static::class, 'notRegex']);
    }

    /**
     * Checks whether or not $value is filled, 
     * i.e. $value is no empty string, array or Countable and not null.
     * 
     * @param mixed $value
     * 
     * @return bool true when $value is filled, elsewise false
     */
    protected static function isFilled($value)
    {
        return !(
            (is_null($value)) ||
            (is_string($value) && $value === '') ||
            ((is_array($value) || is_a($value, Countable::class)) && empty($value))
        );
    }

    /**
     * present
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function present(Validator $validator, $data, $pattern, $rule)
    {
        if (ArrDots::has($data, $pattern, $validator::WILD)) {
            return;
        }

        $validator->addError($pattern, $rule);
    }

    /**
     * required
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function required(Validator $validator, $data, $pattern, $rule)
    {
        // Check pattern is present
        if (!ArrDots::has($data, $pattern, $validator::WILD)) {
            $validator->addError($pattern, $rule);
        }

        // Check value is not null
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            // not allowed: null, '', [], empty instance Countable
            if (static::isFilled($value)) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * required-if:another-field,value
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function requiredIf(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $values = array_slice($parameters, 1);

        // Calculate comparability
        list($field, $isWild, $overlap) = static::_isComparable($validator, $parameters, $pattern);

        // If pattern is not present
        if (!ArrDots::has($data, $pattern, $validator::WILD)) {
            foreach (Validator::getValues($data, $field) as $fieldAttribute => $fieldValue) {
                if (null === $fieldValue || !in_array($fieldValue, $values)) {
                    continue;
                }

                $attribute = $isWild ? Str::overlapLeftMerge($overlap, $fieldAttribute, $pattern) : $pattern;
                $validator->addError($attribute, $rule, [':field' => $fieldAttribute, '%value' => implode(',', $values)]);
            }
            return;
        }

        // Check value is not null
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            $fieldAttribute = $isWild ? Str::overlapLeftMerge($overlap, $attribute, $field) : $field;
            $fieldValue = ArrDots::get($data, $fieldAttribute);

            if (!static::isFilled($fieldValue) || !in_array($fieldValue, $values)) {
                continue;
            }

            if (static::isFilled($value)) {
                continue;
            }

            $validator->addError($attribute, $rule, [':field' => $fieldAttribute, '%value' => implode(',', $values)]);
        }
    }

    /**
     * required-with:another-field
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function requiredWith(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        // Calculate comparability
        list($field, $isWild, $overlap) = static::_isComparable($validator, $parameters, $pattern);

        // If the required with field exists and the pattern field does not
        if (ArrDots::has($data, $field, $validator::WILD) && !ArrDots::has($data, $pattern, $validator::WILD)) {
            $validator->addError($pattern, $rule, [':field' => $field]);
        }

        // Check value is not null
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            $fieldAttribute = $isWild ? Str::overlapLeftMerge($overlap, $attribute, $field) : $field;
            $fieldValue = ArrDots::get($data, $fieldAttribute);

            if (!static::isFilled($fieldValue)) {
                continue;
            }
            if (static::isFilled($value)) {
                continue;
            }

            $validator->addError($attribute, $rule, [':field' => $fieldAttribute]);
        }
    }

    /**
     * required-with-all:another-field(,another-field)*
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function requiredWithAll(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        // Find the overlaps and if the fields are wild
        $overlaps = [];
        $longest = 0;
        foreach ($parameters as $k => $field) {

            // Calculate comparability
            list($isWild, $overlap) = static::_isComparable($validator, $field, $pattern);
            $overlaps[$k] = $overlap;

            // Store the longest overlap
            $longest = $isWild && strlen($overlaps[$k]) > strlen($overlaps[$longest]) ? $k : $longest;
        }

        // If the pattern field does not exist
        if (!ArrDots::has($data, $pattern, $validator::WILD)) {
            // Check that all "required with" fields are present and not null
            $required = false;
            foreach (Validator::getValues($data, $parameters[$longest]) as $attribute => $value) {
                $required = true;
                foreach ($parameters as $k => $field) {
                    $fieldAttribute = $overlaps[$k] ? Str::overlapLeftMerge($overlaps[$k], $attribute, $field) : $field;
                    $fieldValue = ArrDots::get($data, $fieldAttribute);
                    $required = $required && static::isFilled($fieldValue);
                    if (!$required) {
                        break;
                    }
                }
                if ($required) {
                    break;
                }
            }

            if ($required) {
                $validator->addError($pattern, $rule);
            }
            return;
        }

        // Check value is required and not null
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            // Check that all "required with" fields are present and not null
            $required = true;
            foreach ($parameters as $k => $field) {
                $fieldAttribute = $overlaps[$k] ? Str::overlapLeftMerge($overlaps[$k], $attribute, $field) : $field;
                $fieldValue = ArrDots::get($data, $fieldAttribute);
                $required = $required && static::isFilled($fieldValue);
                if (!$required) {
                    break;
                }
            }

            // If required and value is null
            if ($required && $value === null) {
                $validator->addError($pattern, $rule);
            }
        }
    }

    /**
     * required-with-any:another-field(,another-field)*
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function requiredWithAny(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        // Find the overlaps and if the fields are wild
        $overlaps = [];
        $longest = 0;
        foreach ($parameters as $k => $field) {

            // Calculate comparability
            list($isWild, $overlap) = static::_isComparable($validator, $field, $pattern);
            $overlaps[$k] = $overlap;

            // Store the longest overlap
            $longest = $isWild && strlen($overlaps[$k]) > strlen($overlaps[$longest]) ? $k : $longest;
        }

        // If the pattern field does not exist
        if (!ArrDots::has($data, $pattern, $validator::WILD)) {
            // Check that any "required with" fields are present and not null
            $required = array_reduce($parameters, function ($required, $field) use ($validator, $data) {
                if (!$required && ArrDots::has($data, $field, $validator::WILD)) {
                    foreach (Validator::getValues($data, $field) as $value) {
                        $required = $required || static::isFilled($value);
                    }

                }
                return $required;
            }, false);

            if ($required) {
                $validator->addError($pattern, $rule);
            }
            return;
        }

        // Check value is required and not null
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            // Check that any "required with" fields are present and not null
            $required = false;
            foreach ($parameters as $k => $field) {
                $fieldAttribute = $overlaps[$k] ? Str::overlapLeftMerge($overlaps[$k], $attribute, $field) : $field;
                $fieldValue = ArrDots::get($data, $fieldAttribute);
                $required = $required || static::isFilled($fieldValue);
                if ($required) {
                    break;
                }
            }

            // If required and value is null
            if ($required && $value === null) {
                $validator->addError($pattern, $rule);
            }
        }
    }

    /**
     * required-without:another-field
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function requiredWithout(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        // Calculate comparability
        list($field, $isWild, $overlap) = static::_isComparable($validator, $parameters, $pattern);

        // If the required with field exists and the pattern field does not
        if (!ArrDots::has($data, $field, $validator::WILD) && !ArrDots::has($data, $pattern, $validator::WILD)) {
            $validator->addError($pattern, $rule, [':field' => $field]);
        }

        // Check value is not null
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (static::isFilled($value)) {
                continue;
            }

            $fieldAttribute = $isWild ? Str::overlapLeftMerge($overlap, $attribute, $field) : $field;
            $fieldValue = ArrDots::get($data, $fieldAttribute);
            if (static::isFilled($fieldValue)) {
                continue;
            }

            $validator->addError($attribute, $rule, [':field' => $fieldAttribute]);
        }
    }


    /**
     * equals:another-field
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function equals(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        // Calculate comparability
        list($field, $isWild, $overlap) = static::_isComparable($validator, $parameters, $pattern);

        // Check values are equal
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            $fieldAttribute = $isWild ? Str::overlapLeftMerge($overlap, $attribute, $field) : $field;
            $fieldValue = ArrDots::get($data, $fieldAttribute);

            if ($fieldValue == $value) {
                continue;
            }

            $validator->addError($attribute, $rule, [':field' => $fieldAttribute]);
        }
    }

    /**
     * not-equals:another-field
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function notEquals(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        // Calculate comparability
        list($field, $isWild, $overlap) = static::_isComparable($validator, $parameters, $pattern);

        // Check values are equal
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            $fieldAttribute = $isWild ? Str::overlapLeftMerge($overlap, $attribute, $field) : $field;
            $fieldValue = ArrDots::get($data, $fieldAttribute);

            if ($fieldValue != $value) {
                continue;
            }

            $validator->addError($attribute, $rule, [':field' => $fieldAttribute]);
        }
    }

    /**
     * identical:another-field
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function identical(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        // Calculate comparability
        list($field, $isWild, $overlap) = static::_isComparable($validator, $parameters, $pattern);

        // Check values are equal
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            $fieldAttribute = $isWild ? Str::overlapLeftMerge($overlap, $attribute, $field) : $field;
            $fieldValue = ArrDots::get($data, $fieldAttribute);

            if ($fieldValue === $value) {
                continue;
            }

            $validator->addError($attribute, $rule, [':field' => $fieldAttribute]);
        }
    }

    /**
     * not-identical:another-field
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function notIdentical(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        // Calculate comparability
        list($field, $isWild, $overlap) = static::_isComparable($validator, $parameters, $pattern);

        // Check values are equal
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            $fieldAttribute = $isWild ? Str::overlapLeftMerge($overlap, $attribute, $field) : $field;
            $fieldValue = ArrDots::get($data, $fieldAttribute);

            if ($fieldValue !== $value) {
                continue;
            }

            $validator->addError($attribute, $rule, [':field' => $fieldAttribute]);
        }
    }

    /**
     * in:<value>(,<value>)*
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function in(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value) {
                continue;
            }
            if (in_array($value, $parameters)) {
                continue;
            }

            $validator->addError($attribute, $rule, ['%values' => implode(', ', $parameters)]);
        }
    }

    /**
     * not-in:<value>(,<value>)*
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function notIn(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value) {
                continue;
            }
            if (!in_array($value, $parameters)) {
                continue;
            }

            $validator->addError($attribute, $rule, ['%values' => implode(', ', $parameters)]);
        }
    }

    /**
     * contains:<value>(,<value>)*
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function contains(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (is_countable($value) && count($parameters) == count(array_intersect($value, $parameters))) {
                continue;
            }

            $validator->addError($attribute, $rule, [':values' => implode(', ', $parameters)]);
        }
    }

    /**
     * contains-only:<value>(,<value>)*
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function containsOnly(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (is_countable($value) && count($value) == count(array_intersect($value, $parameters))) {
                continue;
            }

            $validator->addError($attribute, $rule, [':values' => implode(', ', $parameters)]);
        }
    }

    /**
     * min-arr-count:<minimum_value>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function minArrCount(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $min = $parameters[0];

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value) {
                continue;
            }

            if (is_countable($value) && count($value) >= $min) {
                break;
            }

            $validator->addError($attribute, $rule, [':min' => $min]);
        }
    }

    /**
     * max-arr-count:<minimum_value>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function maxArrCount(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $max = $parameters[0];

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value) {
                continue;
            }

            if (is_countable($value) && count($value) <= $max) {
                break;
            }

            $validator->addError($attribute, $rule, [':max' => $max]);
        }
    }

    /**
     * min:<minimum-value>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function min(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $min = $parameters[0];

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || ($value != '0' && empty($value))) {
                continue;
            }

            if ($value >= $min) {
                break;
            }

            $validator->addError($attribute, $rule, [':min' => $min]);
        }
    }

    /**
     * max:<minimum_value>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function max(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $max = $parameters[0];

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || ($value != '0' && empty($value))) {
                continue;
            }

            if ($value <= $max) {
                break;
            }

            $validator->addError($attribute, $rule, [':max' => $max]);
        }
    }

    /**
     * greater-than:<another_field>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function greaterThan(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $lowerBound = Validator::getValue($data, $parameters[0]);
        if (null === $lowerBound) {
            return;
        }
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if ($value > $lowerBound) {
                continue;
            }
            $validator->addError($attribute, $rule, [':value' => $value]);
        }
    }

    /**
     * less-than:<another_field>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function lessThan(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $upperBound = Validator::getValue($data, $parameters[0]);
        if (null === $upperBound) {
            return;
        }
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if ($value < $upperBound) {
                continue;
            }

            $validator->addError($attribute, $rule, [':value' => $value]);
        }
    }

    /**
     * alpha
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function alpha(Validator $validator, $data, $pattern, $rule)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (preg_match('/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i', $value) === 1) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * alpha-numeric
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function alphaNumeric(Validator $validator, $data, $pattern, $rule)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (preg_match('/^([a-z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i', $value) === 1) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * min-str-len:<minimum_value>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function minStrLen(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $min = $parameters[0];

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }

            if (strlen($value) >= $min) {
                break;
            }

            $validator->addError($attribute, $rule, [':min' => $min]);
        }
    }

    /**
     * max-str-len:<minimum_value>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function maxStrLen(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $max = $parameters[0];

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }

            if (strlen($value) <= $max) {
                break;
            }

            $validator->addError($attribute, $rule, [':max' => $max]);
        }
    }

    /**
     * str-len:<exact-length>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function strLen(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $length = $parameters[0];

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (strlen($value) === (int) $length) {
                continue;
            }

            $validator->addError($attribute, $rule, [':length' => $length]);
        }
    }

    /**
     * human-name
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function humanName(Validator $validator, $data, $pattern, $rule)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (preg_match('/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ \'-])+$/i', $value) === 1) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * is:<type>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function is(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $is_a_func = sprintf('is_%s', $parameters[0]);
        if (!function_exists($is_a_func)) {
            return;
        }

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            // As "is:<type>" is validating value type only ignore null
            if (null === $value) {
                continue;
            }
            if (call_user_func($is_a_func, $value)) {
                continue;
            }

            $validator->addError($attribute, $rule, [':type' => $parameters[0]]);
        }
    }

    /**
     * password <no parameter>
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     *
     * ^: anchored to beginning of string
     * \S*: any set of characters
     * (?=\S{8,}): of at least length 8
     * (?=\S*[a-z]): containing at least one lowercase letter
     * (?=\S*[A-Z]): and at least one uppercase letter
     * (?=\S*[\d]): and at least one number
     * (?=\S*[-+_!@#$%^&*.,?)(|}{`]): and at least one symbol
     * $: anchored to the end of the string
     */
    public static function password(Validator $validator, $data, $pattern, $rule)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {

            if (preg_match('/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[-+_!@#$%^&*.,?)(|}{`])\S*$/', $value)) {
                continue;
            }

            $validator->addError($attribute, $rule, [':chars' => 8]);
        }
    }

    /**
     * email
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function email(Validator $validator, $data, $pattern, $rule)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (false !== filter_var($value, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * date:(format)?
     *
     * @link http://php.net/manual/en/datetime.createfromformat.php
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function date(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $format = !empty($parameters[0]) ? $parameters[0] : 'Y-m-d';
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            $d = \DateTime::createFromFormat($format, $value);
            if ($d && $d->format($format) == $value) {
                continue;
            }

            $validator->addError($attribute, $rule, [':format' => $format]);
        }
    }

    /**
     * url
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function url(Validator $validator, $data, $pattern, $rule)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (false !== filter_var($value, FILTER_VALIDATE_URL)) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * uuid
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function uuid(Validator $validator, $data, $pattern, $rule)
    {
        $uuidPattern = '/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/';

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (is_string($value) && 1 === preg_match($uuidPattern, $value)) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * card-number
     *
     * @see http://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
     *
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     */
    public static function cardNumber(Validator $validator, $data, $pattern, $rule)
    {
        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }

            // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
            $number = preg_replace('/\D/', '', $value);

            // Set the string length and parity
            $numberLength = strlen($number);
            $parity = $numberLength % 2;

            // Loop through each digit and do the maths
            $total = 0;
            for ($i = 0; $i < $numberLength; $i++) {
                $digit = $number[$i];
                // Multiply alternate digits by two
                if ($i % 2 == $parity) {
                    $digit *= 2;
                    // If the sum is two digits, add them together (in effect)
                    if ($digit > 9) {
                        $digit -= 9;
                    }
                }
                // Total up the digits
                $total += $digit;
            }

            // If the total mod 10 equals 0, the number is valid
            if ($total % 10 == 0) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * custom regex validation
     * 
     * When using the regex / not_regex patterns, it may be necessary to specify
     * rules in an array instead of using | delimiters, especially if the regular
     * expression contains a | character.
     * 
     * @see https://www.php.net/preg_match
     * 
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function regex(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $regexPattern = join(',', $parameters);

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (is_string($value) && preg_match($regexPattern, $value)) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * custom regex validation
     * 
     * When using the regex / not_regex patterns, it may be necessary to specify
     * rules in an array instead of using | delimiters, especially if the regular
     * expression contains a | character.
     * 
     * @see https://www.php.net/preg_match
     * 
     * @param Validator $validator
     * @param array $data
     * @param string $pattern
     * @param string $rule
     * @param array  $parameters
     */
    public static function notRegex(Validator $validator, $data, $pattern, $rule, $parameters)
    {
        $regexPattern = join(',', $parameters);

        foreach (Validator::getValues($data, $pattern) as $attribute => $value) {
            if (null === $value || empty($value)) {
                continue;
            }
            if (is_string($value) && !preg_match($regexPattern, $value)) {
                continue;
            }

            $validator->addError($attribute, $rule);
        }
    }

    /**
     * Check if we can match the pattern with field
     *
     * @param Validator $validator
     * @param array $parameters
     * @param string $pattern
     * @return array
     */
    private static function _isComparable($validator, $parameters, $pattern)
    {
        $field = $parameters[0];
        $isWild = strpos($field, $validator::WILD) !== false;
        $overlap = Str::overlapLeft($field, $pattern);

        // Check that the pattern and field can be compared
        if ($isWild && $overlap === false) {
            throw new \InvalidArgumentException(text("validator.cannotMatch", [$pattern, $field]));
        }

        return [$field, $isWild, $overlap];
    }
}