<?php

/**
 * FOOTUP - 0.1.6 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @uses Html::h1("Content !", ["attr" => "value", "..." => "..."]) | where h1 is in $pairs or $impairs arrays
 * 
 * @package Footup\Html
 * @version 0.2
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Html;

/**
 * HTML Class
 * 
 * @method static string a(string text, array attributes = [])
 * @method static string abbr(string text, array attributes = [])
 * @method static string address(string htmlContent, attributes = [])
 * @method static string article(string html, array attributes = [])
 * @method static string aside(string html, array attributes = [])
 * @method static string audio(string html, array attributes = [])
 * @method static string b(string text, array attributes = [])
 * 
 * @see Html::$pairs and Html::$impairs
 */
class Html
{
    /**
     * @var array
     */
    public static $pairs = array(
        'a',
        'abbr',
        'address', // NORMAL | BLOCK_TAG
        'article', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'aside', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'audio', // NORMAL
        'b',
        'bdi',
        'bdo',
        'blockquote', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'body',
        'button',
        'canvas', // NORMAL | BLOCK_TAG
        'caption',
        'cite',
        'code',
        'colgroup',
        'datalist',
        'dd', // NORMAL | BLOCK_TAG
        'del',
        'details', // NORMAL | AUTOCLOSE_P,
        'dfn',
        'dialog', // NORMAL | AUTOCLOSE_P,
        'div', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'dl', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'dt',
        'em',
        'fieldset', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'figcaption', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'figure', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'footer', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'form', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'h1', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'h2', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'h3', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'h4', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'h5', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'h6', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'head',
        'header', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'hgroup', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'html',
        'i',
        'iframe', // NORMAL | TEXT_RAW
        'kbd',
        'ins',
        'label',
        'legend',
        'li',
        'map',
        'mark',
        'menu', // NORMAL | AUTOCLOSE_P,
        'meter',
        'nav', // NORMAL | AUTOCLOSE_P,
        'noscript', // NORMAL | BLOCK_TAG
        'object',
        'ol', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'optgroup',
        'option',
        'output', // NORMAL | BLOCK_TAG
        'p', // NORMAL | AUTOCLOSE_P | BLOCK_TAG | BLOCK_ONLY_INLINE
        'pre', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'progress',
        'q',
        'rp',
        'rt',
        'ruby',
        's',
        'samp',
        'script', // NORMAL | TEXT_RAW
        'section', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'select',
        'small',
        'span',
        'strong',
        'style', // NORMAL | TEXT_RAW
        'sub',
        'summary', // NORMAL | AUTOCLOSE_P,
        'sup',
        'table', // NORMAL | BLOCK_TAG
        'tbody',
        'td',
        'textarea', // NORMAL | TEXT_RCDATA
        'tfoot', // NORMAL | BLOCK_TAG
        'th',
        'thead',
        'time',
        'title', // NORMAL | TEXT_RCDATA
        'tr',
        'u',
        'ul', // NORMAL | AUTOCLOSE_P | BLOCK_TAG
        'var',
        'video', // NORMAL | BLOCK_TAG

        // Legacy?
        'noframes', // RAW_TEXT
        'frameset',
        'center',
        'dir',
        'listing', // AUTOCLOSE_P
        'plaintext', // AUTOCLOSE_P | TEXT_PLAINTEXT
        'applet',
        'marquee',
        'noembed', // RAW_TEXT
    );

    /**
     * @var array
     */
    public static $impairs = array(
        'area', // NORMAL | VOID_TAG
        'base', // NORMAL | VOID_TAG
        'br', // NORMAL | VOID_TAG
        'col', // NORMAL | VOID_TAG
        'frame', // NORMAL | VOID_TAG
        'command', // NORMAL | VOID_TAG
        'embed', // NORMAL | VOID_TAG
        'hr', // NORMAL | VOID_TAG
        'img', // NORMAL | VOID_TAG
        'input', // NORMAL | VOID_TAG
        'keygen', // NORMAL | VOID_TAG
        'link', // NORMAL | VOID_TAG
        'meta', // NORMAL | VOID_TAG
        'param', // NORMAL | VOID_TAG
        's',
        'samp',
        'source', // NORMAL | VOID_TAG
        'track', // NORMAL | VOID_TAG
        'wbr', // NORMAL | VOID_TAG

        // Legacy?
        'basefont', // VOID_TAG
        'isindex', // VOID_TAG
    );

    public static function __callStatic($name, $arguments)
    {
        $name = strtolower($name);
        if(in_array($name, self::$pairs))
        {
            $content = $arguments && count($arguments) >= 1 ? array_shift($arguments) : "";
            $attributes = $arguments && count($arguments) >= 1 ? self::attributes($arguments) : "";

            return "<{$name} {$attributes}>{$content}</{$name}>";
        }

        if(in_array($name, self::$impairs))
        {
            $content = $arguments && count($arguments) >= 1 ? array_shift($arguments) : "";
            $attributes = $arguments && count($arguments) >= 1 ? self::attributes($arguments) : "";
            
            return "<{$name} {$attributes}  />";
        }

        throw new \Exception(text("Core.classNoMethod", [$name, get_called_class()]));
    }

    public static function attributes(array $attr)
    {
        $string = "";
        foreach($attr as $k => $v)
        {
            $string .= self::analyze($v);
        }
        return $string;
    }

    protected static function analyze(array $data)
    {
        $string = "";
        foreach($data as $k => $v)
        {
            $string .= "{$k}='{$v}' ";
        }
        return $string;
    }
    
}