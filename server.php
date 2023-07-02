<?php

/**
 * FOOTUP FRAMEWORK  2021 - 2023
 * *****************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. That mean you don't worry for having Apache but Apache is better
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

// Don't Touch this line otherwise FootUp will take the base_url from the .env file
$_SERVER["BASE_URL"] = "http://".($_SERVER["SERVER_NAME"] ?? $_SERVER["SERVER_ADDR"]).":".$_SERVER["SERVER_PORT"];

require_once __DIR__.'/public/index.php';
