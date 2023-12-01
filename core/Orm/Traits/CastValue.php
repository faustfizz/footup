<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Orm
 * @version 0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm\Traits;
use Footup\I18n\Time;

trait CastValue
{
    /**
     * Array with keys as columns and values as types to cast when reading the column
     * For now, we use casting just for accessing
     *
     * @var array
     */
    protected $casts    = [
        'updated_at'    =>  Time::class,
        'created_at'    =>  Time::class,
    ];

    /**
     * Cast value
     *
     * @param string $column column name
     * @return mixed
     */
    protected function castValue(string $column)
    {
        $castType = $this->casts[$column] ?? null;

        if(is_null($castType)) {
            return $this->data[$column];
        }

        if(function_exists('is_'.$castType)) {
            $value = $this->data[$column];
            settype($value, $castType);
            return $value;
        }

        if(class_exists($castType)) {
            return new $castType($this->data[$column]);
        }
    }
    
    /**
     * Cast an array of data
     *
     * @param array $columns
     * @return array
     */
    protected function castAll(array $columns)
    {
        $data = [];
        foreach ($columns as $key => $column) {
            # code...
            $data[$column] = $this->castValue($column);
        }
        return $data;
    }
}