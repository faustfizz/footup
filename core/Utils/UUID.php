<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Utils
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Utils;

class UUID
{
    /**
     * @var string
     */
    const VALID_PATTERN = '^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$';

    /**
     * Verify uuid validity
     *
     * @param string $uuid
     * @return boolean
     */
    public static function isValid($uuid)
    {
        return preg_match('/' . self::VALID_PATTERN . '/i', $uuid) === 1;
    }

    /**
     * This function use by the default the v4 function to generate UUID,
     * You can specify the version in the second parameter
     *
     * @param string $string
     * @param int $version
     * @param boolean $upper make all alphabetics letters uppercase
     * @return string
     */
    public static function make($string = null, $version = null, $upper = false)
    {
        $version = $version ?? 4;
        return self::uuid($string, $version, $upper);
    }

    /**
     * Bits version for UUID
     *
     * @param int $version
     * @return int
     */
    private static function bitVersion($version)
    {
        if (!is_int($version) || empty($version)) {
            return 0x40;
        }
        return 16 * (int) $version;
    }

    /**
     * Generate UUID string
     *
     * @param string $string
     * @param int $version
     * @param boolean $upper make all alphabetics letters uppercase
     * @return string
     */
    private static function uuid($string = null, $version = 4, $upper = false)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = sha1(time() . ($string ?? random_bytes(8)));

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for ($i = 0; $i < strlen($data); $i += 2) {
            $nstr .= chr(ord($data[$i]) . ord($data[$i + 1]));
        }

        // Set version to 4
        $nstr[6] = chr(ord($nstr[6]) & 0x0f | self::bitVersion($version));
        $nstr[7] = chr(ord($nstr[7]) & 0x0f | self::bitVersion($version));
        // Set bits 6-7 to 10
        $nstr[8] = chr(ord($nstr[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($nstr), 4));

        return $upper ? strtoupper($uuid) : $uuid;
    }

    /**
     * Undocumented function
     *
     * @param [type] $string
     * @param boolean $upper
     * @return string
     */
    public static function v1($string = null, $upper = false)
    {
        return self::uuid($string, 1, $upper);
    }

    /**
     * Generate UUID with bits version 2
     *
     * @param string $string a random string to use with bytes
     * @param boolean $upper
     * @return string
     */
    public static function v2($string = null, $upper = false)
    {
        return self::uuid($string, 2, $upper);
    }

    /**
     * Generate UUID with bits version 3
     *
     * @param string $string a random string to use with bytes
     * @param boolean $upper
     * @return string
     */
    public static function v3($string = null, $upper = false)
    {
        return self::uuid($string, 3, $upper);
    }

    /**
     * Generate UUID string
     *
     * @param string $string
     * @param boolean $upper make all alphabetics letters uppercase
     * @return string
     */
    public static function v4($string = null, $upper = false)
    {
        return self::uuid($string, 4, $upper);
    }

    /**
     * Generate an UUID v5 based on a string. This UUID is intended
     * to be used as a unique identifier for things.
     * 
     * @param string $string
     * @param boolean $upper make all alphabetics letters uppercase
     * @return string
     */
    public static function v5($string = null, $upper = false)
    {
        return self::uuid($string, 5, $upper);
    }

    /**
     * Generate an UUID v6 based on a string. This UUID is intended
     * to be used as a unique identifier for things.
     * 
     * @param string $string
     * @param boolean $upper make all alphabetics letters uppercase
     * @return string
     */
    public static function v6($string = null, $upper = false)
    {
        return self::uuid($string, 6, $upper);
    }

}