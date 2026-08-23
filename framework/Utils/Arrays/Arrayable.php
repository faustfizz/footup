<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Utils\Arrays
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Utils\Arrays;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}