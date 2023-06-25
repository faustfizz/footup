<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Orm
 * @version 0.3
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Orm\Traits;

trait Fillable
{
    /**
     * Add all fillable fields here, if empty, all fields are fillable except  fields added on the **exclude** array
     * 
     * @var string[]
     */
    protected $fillable         = [];

    /**
     * Add all non fillable fields here, if empty, all fields are fillable
     * 
     * Consider using this in case you have too many fields and cannot add them all on **fillable** array
     * 
     * @var string[]
     */
    protected $exclude         = [];

    /**
     * Check if a field is fillable
     *
     * @param string $field
     * @return boolean
     */
    protected function isFillable(string $field)
    {
        return !in_array($field, $this->exclude) || in_array($field, $this->fillable);
    }

    /**
     * Get all mass assignable fields
     *
     * @param array $fields
     * @return array
     */
    protected function getRealFillableKeys(array $fields)
    {
        $returnedKeys = null;
        // if we have a list to exclude, so we use it and we don't use the fillable
        if(!empty($this->exclude)) {
            $returnedKeys = array_filter($fields, function($field){
                return !in_array($field, $this->exclude);
            });
        }else{
            // as we don't have a list of excluded fields, we use the fillable
            if(!empty($this->fillable)) {
                $returnedKeys = array_filter($fields, function($field){
                    return in_array($field, $this->fillable) || $field === $this->getPrimaryKey();
                });
            }
        }

        return $returnedKeys ?? $fields;
    }

    /**
     * Get all fillable fields here, if empty, all fields are fillable except fields added on the **exclude** array
     *
     * @return  string[]
     */ 
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Get all non fillable fields here, if empty, all fields are fillable
     *
     * @return  string[]
     */ 
    public function getExclude()
    {
        return $this->exclude;
    }

}