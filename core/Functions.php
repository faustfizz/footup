<?php
/**
 * FOOTUP - 0.1.6-Alpha - 2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * Ce fichier contient les fonctions globales du framework FOOTUP
 * Ce fichier fait partie du framework
 * 
 * @package Footup
 * @version 0.1.5
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

use Footup\Footup;
use Footup\Html\Html;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Http\Session;
use Footup\Config\Config;
use Footup\Config\Mime;
use Footup\I18n\Time;
use Footup\Lang\Lang;
use Footup\Model;
use Footup\Utils\Shared;
use Footup\Utils\Validator\Validator;

// Tableau de caractères à remplacer
defined("STRTR") or define("STRTR", array(

	// Aa
	'/Α|Ά|А|À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
	'/α|ά|а|à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',

	// Bb
	'/Β|Б/' => 'B',
	'/β|б/' => 'b',

	// Cc
	'/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
	'/ç|ć|ĉ|ċ|č/' => 'c',

	// Dd
	'/Δ|Д|Ð|Ď|Đ/' => 'D',
	'/δ|д|ð|ď|đ/' => 'd',

	// Ee
	'/Ε|Έ|Е|Э|Є|È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
	'/ε|έ|е|э|є|è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',

	// Ff
	'/Φ|Ф/' => 'F',
	'/φ|ф|ƒ/' => 'f',

	// Gg
	'/Γ|Г|Ĝ|Ğ|Ġ|Ģ/' => 'G',
	'/γ|г|ĝ|ğ|ġ|ģ/' => 'g',

	// Hh
	'/Х|Ĥ|Ħ/' => 'H',
	'/х|ĥ|ħ/' => 'h',

	// Ii
	'/Η|Ή|Ι|Ί|И|Ы|І|Ї|Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
	'/η|ή|ι|ί|и|ы|і|ї|ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',

	// Jj
	'/Ĵ/' => 'J',
	'/ĵ/' => 'j',

	// Kk
	'/Κ|К|Ķ/' => 'K',
	'/κ|к|ķ/' => 'k',

	// Ll
	'/Λ|Л|Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
	'/λ|л|ĺ|ļ|ľ|ŀ|ł/' => 'l',

	// Mm
	'/Μ|М/' => 'M',
	'/μ|м/' => 'm',

	// Nn
	'/Ν|Н|Ñ|Ń|Ņ|Ň/' => 'N',
	'/ν|н|ñ|ń|ņ|ň|ŉ/' => 'n',

	// Oo
	'/Ο|Ό|О|Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
	'/ο|ό|о|ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',

	// Pp
	'/Π|П/' => 'P',
	'/π|п/' => 'p',

	// Qq
	// '//' => 'Q',
	// '//' => 'q',

	// Rr
	'/Ρ|Р|Ŕ|Ŗ|Ř/' => 'R',
	'/ρ|р|ŕ|ŗ|ř/' => 'r',

	// Ss
	'/Σ|С|Ś|Ŝ|Ş|Š/' => 'S',
	'/σ|ς|с|ś|ŝ|ş|š|ſ/' => 's',

	// Tt
	'/Τ|Т|Ţ|Ť|Ŧ/' => 'T',
	'/τ|т|ţ|ť|ŧ/' => 't',

	// Uu
	'/У|Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
	'/у|ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',

	// Vv
	'/В/' => 'V',
	'/в/' => 'v',

	// Ww
	'/Ω|Ώ|Ŵ/' => 'W',
	'/ω|ώ|ŵ/' => 'w',

	// Xx
	'/Χ/' => 'X',
	'/χ/' => 'x',

	// Yy
	'/Υ|Ύ|Ψ|Й|Ý|Ÿ|Ŷ/' => 'Y',
	'/υ|ύ|ψ|й|ý|ÿ|ŷ/' => 'y',

	// Zz
	'/Ζ|З|Ź|Ż|Ž/' => 'Z',
	'/ζ|з|ź|ż|ž/' => 'z',

	'/Θ/' => 'Th',
	'/θ/' => 'th',

	'/Ξ/' => 'Ks',
	'/ξ/' => 'ks',

	'/Ё/' => 'Yo',
	'/ё/' => 'yo',

	'/Ж/' => 'Zh',
	'/ж/' => 'zh',

	'/Ц/' => 'Ts',
	'/ц/' => 'ts',

	'/Ч/' => 'Ch',
	'/ч/' => 'ch',

	'/Ш/' => 'Sh',
	'/ш/' => 'sh',

	'/Щ/' => 'Sch',
	'/щ/' => 'sch',

	'/Ь|Ъ/' => '',
	'/ь|ъ/' => '',

	'/Ю/' => 'Yu',
	'/ю/' => 'yu',

	'/Я/' => 'Ya',
	'/я/' => 'ya',

	'/Æ|Ǽ/' => 'AE',
	'/Ä/' => 'Ae',
	'/ä|æ|ǽ/' => 'ae',

	'/Œ/' => 'OE',
	'/Ö/' => 'Oe',
	'/ö|œ/' => 'oe',

	'/Ü/' => 'Ue',
	'/ü/' => 'ue',

	'/Ĳ/' => 'IJ',
	'/ĳ/' => 'ij',

	'/ß/'=> 'ss',

));

////////////#--------------------------#///////////

if (! function_exists('directory_map')) {
    /**
     * Create a Directory Map
     *
     * Reads the specified directory and builds an array
     * representation of it. Sub-folders contained with the
     * directory will be mapped as well.
	 * 
	 * @copyright CodeIgniter 4 FileSystem Helper
     *
     * @param string $sourceDir      Path to source
     * @param int    $directoryDepth Depth of directories to traverse
     *                               (0 = fully recursive, 1 = current dir, etc)
     * @param bool   $hidden         Whether to show hidden files
     */
    function directory_map(string $sourceDir, int $directoryDepth = 0, bool $hidden = false): array
    {
        try {
            $fp = opendir($sourceDir);

            $fileData  = [];
            $newDepth  = $directoryDepth - 1;
            $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            while (false !== ($file = readdir($fp))) {
                // Remove '.', '..', and hidden files [optional]
                if ($file === '.' || $file === '..' || ($hidden === false && $file[0] === '.')) {
                    continue;
                }

                if (is_dir($sourceDir . $file)) {
                    $file .= DIRECTORY_SEPARATOR;
                }

                if (($directoryDepth < 1 || $newDepth > 0) && is_dir($sourceDir . $file)) {
                    $fileData[$file] = directory_map($sourceDir . $file, $newDepth, $hidden);
                } else {
                    $fileData[] = $file;
                }
            }

            closedir($fp);

            return $fileData;
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (! function_exists('directory_mirror')) {
    /**
     * Recursively copies the files and directories of the origin directory
     * into the target directory, i.e. "mirror" its contents.
	 * 
	 * @copyright CodeIgniter 4 FileSystem Helper
     *
     * @param string $originDir orignal directory (source)
     * @param string $targetDir destination directory (target)
	 * 
     * @param bool $overwrite Whether individual files overwrite on collision
     *
     * @throws InvalidArgumentException
     */
    function directory_mirror(string $originDir, string $targetDir, bool $overwrite = true): void
    {
        if (! is_dir($originDir = rtrim($originDir, '\\/'))) {
            throw new InvalidArgumentException(text('file.dirNotExist', [$originDir]));
        }

        if (! is_dir($targetDir = rtrim($targetDir, '\\/'))) {
            @mkdir($targetDir, 0755, true);
        }

        $dirLen = strlen($originDir);

        /**
         * @var SplFileInfo $file
         */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($originDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $file) {
            $origin = $file->getPathname();
            $target = $targetDir . substr($origin, $dirLen);

            if ($file->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755);
                }
            } elseif (! is_file($target) || ($overwrite && is_file($target))) {
                copy($origin, $target);
            }
        }
    }
}

if(!function_exists("request"))
{
    /**
     * Une fonction pour exposer l'objet Request
     *
     * @param mixed $method_or_index
     * @param mixed $arg
     * @return Request|mixed
     */
    function request($method_or_index = null, $arg = null)
    {
        $req = router()->getRequest();

		if($val = $req->$method_or_index)
        {
            return !empty($arg) ? $req->$method_or_index = $arg : $val;
        }else{
            return $req;
        }
    }
}

if(!function_exists("model"))
{
    /**
     * Une fonction pour retrouver un model d'instance partagée ou non
	 * 
	 * Get the model (Shared Instance or non shared)
     *
     * @param string $modelName
     * @param bool $shared
	 * 
     * @return Model
     */
    function model($modelName, $shared = true)
    {
		return Shared::loadModels($modelName, $shared);
    }
}

if(!function_exists("validator"))
{
    /**
     * Une fonction pour exposer l'objet Validator
     *
     * @return Validator
     */
    function validator()
    {
		return Shared::loadValidator();
    }
}

if(!function_exists("validate"))
{
    /**
     * Une fonction pour exposer l'objet Validator
     *
     * @param array|object $dataToValidate like ["name" => "Said Ali"]
     * @param array $ruleSet like ["name" => "present|min-str-len:3"]
     * @param string $prefixOfAllFields like if you have user[name] it should be user as prefix
     * @return bool
     */
    function validate($dataToValidate, array $ruleSet, string $prefixOfAllFields = null)
    {
		return validator()->validate($dataToValidate, $ruleSet, $prefixOfAllFields);
    }
}

// --------------------------------------------------------------------
if (! function_exists('function_usable'))
{
	/**
	 * ################## function prise dans CodeIgniter 4 ###################
	 * Function usable
	 *
	 * Executes a function_exists() check, and if the Suhosin PHP
	 * extension is loaded - checks whether the function that is
	 * checked might be disabled in there as well.
	 *
	 * This is useful as function_exists() will return FALSE for
	 * functions disabled via the *disable_functions* php.ini
	 * setting, but not for *suhosin.executor.func.blacklist* and
	 * *suhosin.executor.disable_eval*. These settings will just
	 * terminate script execution if a disabled function is executed.
	 *
	 * The above described behavior turned out to be a bug in Suhosin,
	 * but even though a fix was committed for 0.9.34 on 2012-02-12,
	 * that version is yet to be released. This function will therefore
	 * be just temporary, but would probably be kept for a few years.
	 *
	 * @link   http://www.hardened-php.net/suhosin/
	 * @param  string $functionName Function to check for
	 * @return boolean    TRUE if the function exists and is safe to call,
	 *             FALSE otherwise.
	 *
	 * @codeCoverageIgnore This is too exotic
	 */
	function function_usable(string $functionName): bool
	{
		static $_suhosin_func_blacklist;

		if (function_exists($functionName))
		{
			if (! isset($_suhosin_func_blacklist))
			{
				$_suhosin_func_blacklist = extension_loaded('suhosin') ? explode(',', trim(ini_get('suhosin.executor.func.blacklist'))) : [];
			}

			return ! in_array($functionName, $_suhosin_func_blacklist, true);
		}

		return false;
	}
}

if (!function_exists('is_countable')) {
    function is_countable($var) {
        return (is_array($var) || $var instanceof Countable);
    }
}

if(!function_exists("calledController"))
{
    /**
     * Retrouve le controlleur en cours d'utilisation
     *
     * @param boolean $withNamespace
     * @return string
     */
    function calledController($withNamespace = true)
    {
        $controller = explode("\\", router()->getControllerName());
        return $withNamespace ? router()->getControllerName() : end($controller);
    }
}

if(!function_exists("calledMethod"))
{
    /**
     * Retrouve la méthode couremment utilisée
     *
     * @return string
     */
    function calledMethod()
    {
        return router()->getControllerMethod();
    }
}

// --------------------------------------------------------------------

if(!function_exists("router"))
{
    /**
     * Exposition de l'objet Router
     *
     * @return \Footup\Routing\Router
     */
    function router()
    {
        return Shared::loadRouter();
    }
}

if(!function_exists("frameworkName"))
{
    /**
     * Exposition du nom du framework
     *
     * @return string
     */
    function frameworkName()
    {
        return Footup::NAME;
    }
}

if(!function_exists("frameworkVersion"))
{
    /**
     * Exposition de la version
     *
     * @return string
     */
    function frameworkVersion()
    {
        return Footup::VERSION;
    }
}

if(!function_exists("response"))
{
    /**
     * Exposition de l'objet Response
     *
     * @param mixed $data
     * @param integer $status
     * @param array $header
     * @return Response
     */
    function response($data = '', $status = 200, $header = [])
    {
        return new Response($data, $status, $header);
    }
}

// --------------------------------------------------------------------

if(!function_exists("session"))
{
    /**
     * Exposition de l'objet Session
     *
     * @param mixed $key
     * @param string $value
     * @return Session|mixed
     */
    function session($key = null, $value = null)
    {
        $session = Shared::loadSession();

        if(is_array($key))
        {
            return $session->set($key);
        }
        elseif(is_string($key) && !empty($key))
        {
            return !empty($value) ? $session->set($key, $value) : $session->get($key);
        }

		return $session;
    }
}

if (!function_exists('url'))
{
    /**
     * @param mixed $uri
     * @param boolean $withQuery
     * @param string|null $scheme
     * @return string
     */
	function url($uri = '', bool $withQuery = false, string $scheme = null): string
	{
		return base_url($uri, $withQuery, $scheme);
	}
}

//--------------------------------------------------------------------

if (!function_exists('config'))
{
    /**
	 * Retrouve les configurations
	 *
	 * @param string $item
	 * @param mixed $value
	 * @return mixed|Config
	 */
	function config($item = null, $value  = null)
	{
		$config = Shared::loadConfig();

		if(!empty($item) && !empty($value))
		{
			return $config->{$item} = $value;
		}

		return !empty($item) ? $config->{$item} : $config;
	}
}

//--------------------------------------------------------------------

if (! function_exists('base_url'))
{
    /**
     * Génére des urls 
     *
     * @param array|string $uri
     * @param boolean $withQuery
     * @param string|null $scheme
     * @return string
     */
	function base_url($uri = '', bool $withQuery = false, string $scheme = null): string
	{
		$base_url = trim((string) request()->url(false, true), " \n\r\t\v\x00\/")."/";
		$query = "";

		if($scheme)
		{
			$scheme = strtr($scheme, ["://" => ""]);
			$url = parse_url($base_url);
			$base_url = strtr($base_url, [$url["scheme"] => $scheme]);
		}

		if($withQuery)
		{
			$q = request()->query();
			$query = !empty($q) ? "?".http_build_query($q, "_key", "&") : $query;
		}

        if(empty($uri) || $uri == '/')
        {
            return $base_url.$query;
        }

        // convert segment array to string
        $uri = trim(is_array($uri) ? implode('/', $uri) : $uri, " \n\r\t\v\x00\/");
		
        return trim((string) $base_url, " \n\r\t\v\x00\/")."/".$uri.$query;
    }
}

//--------------------------------------------------------------------

if (! function_exists('current_url'))
{
    /**
     * Url actif
     * 
     * @param boolean $withQuery
     * @return string
     */
	function current_url($withQuery = true)
	{
		return request()->url($withQuery);
	}
}

//--------------------------------------------------------------------

if (! function_exists('previous_url'))
{
    /**
     * Ancien URL
     *
     * @param boolean $withQuery
     * @return string
     */
	function previous_url(bool $withQuery = true)
	{
        $url = filter_var(request()->referer(), FILTER_SANITIZE_URL);
        if($url && !$withQuery)
        {
            $pos = strpos($url, "?") !== false ? strpos($url, "?") : strlen($url);
            $url = substr($url, 0, $pos);
        }
		return  $url;
	}
}

//--------------------------------------------------------------------

if (! function_exists('path'))
{
    /**
     * Path de l'url
     *
     * @return string
     */
	function path(): string
	{
        return request()->path();
	}
}

//--------------------------------------------------------------------

if (! function_exists('lang'))
{
    /**
	 * Translation
	 * get language entry
	 *
	 * @param string $indice
	 * @param array $params
	 * @param string|null $locale
	 * @return string
	 */
	function lang(string $indice, array $params = [], string $locale = null): string
	{
		$lang = new Lang($locale);
        return $lang->getText($indice, $params);
	}
}
if (! function_exists('text'))
{
    /**
	 * Translation
	 * get language entry
	 *
	 * @param string $indice
	 * @param array $params
	 * @param string|null $locale
	 * @return string
	 */
	function text(string $indice, array $params = [], string $locale = null): string
	{
		$lang = new Lang($locale);
        return $lang->getText($indice, $params);
	}
}

//--------------------------------------------------------------------

if (! function_exists('setLang'))
{
    /**
	 * Translation
	 * set language entry
	 *
	 * @param string|array $indice
	 * @param string $value or  filename if $indice is an array
	 * @param string|null $locale
	 * @return string|bool
	 */
	function setLang($indice, string $value, string $locale = null): string
	{
		$lang = new Lang($locale);
		if(is_array($indice))
		{
			return $lang->setInput($value, $indice);
		}else{
			return $lang->setText($indice, $value);
		}
	}
}
if (! function_exists('setText'))
{
    /**
	 * Translation
	 * set language entry
	 *
	 * @param string|array $indice
	 * @param string $value or  filename if $indice is an array
	 * @param string|null $locale
	 * @return string|bool
	 */
	function setText($indice, string $value, string $locale = null): string
	{
		$lang = new Lang($locale);
		if(is_array($indice))
		{
			return $lang->setInput($value, $indice);
		}else{
			return $lang->setText($indice, $value);
		}
	}
}

//--------------------------------------------------------------------

if (! function_exists('unsetLang'))
{
    /**
	 * Translation
	 * unset language entry
	 *
	 * @param string $file string or dot string
	 * @param string|array $key
	 * @param string|null $locale
	 * @return string|bool
	 */
	function unsetLang(string $file, $key = null, string $locale = null): string
	{
		$lang = new Lang($locale);
		return $lang->removeLine($file, $key);
	}
}
if (! function_exists('unsetText'))
{
    
    /**
	 * Translation
	 * unset language entry
	 *
	 * @param string $file string or dot string
	 * @param string|array $key
	 * @param string|null $locale
	 * @return string|bool
	 */
	function unsetText(string $file, $key = null, string $locale = null): string
	{
		$lang = new Lang($locale);
		return $lang->removeLine($file, $key);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('redirect'))
{
	/**
     * Redirection
     *
     * @param string $route Route ou url
     * @return void
     */
	function redirect(string $route = '/')
	{
		return response()->redirect(url($route));
	}
}

if (! function_exists('goback'))
{
	/**
     * Redirection en arrière
     *
     * @param string $route Route ou url
     * @return void
     */
	function goback()
	{
		return response()->back();
	}
}

if (! function_exists('to'))
{
	/**
     * Redirection
     *
     * @param string $route Route ou url
     * @return void
     */
	function to(string $route = '/')
	{
		return response()->to(url($route));
	}
}

// ------------------------------------------------------------------------

if (! function_exists('mailto'))
{
	/**
	 * Mailto Link
	 *
	 * @param string $email      the email address
	 * @param string $title      the link title
	 * @param mixed  $attributes any attributes
	 *
	 * @return string
	 */
	function mailto(string $email, string $title = '', $attributes = []): string
	{
		if (trim($title) === '')
		{
			$title = $email;
		}
        $attributes['href'] = "mailto:".$email;

        return Html::a($title, $attributes);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('slugify'))
{
	/**
	 * Create URL Title
	 *
	 * Takes a "title" string as input and creates a
	 * human-friendly URL string with a "separator" string
	 * as the word separator.
	 *
	 * @param  string  $str       Input string
	 * @param  string  $separator Word separator (usually '-' or '_')
	 * @param  boolean $lowercase Whether to transform the output string to lowercase
	 * @return string
	 */
	function slugify(string $str, string $separator = '-', bool $lowercase = true): string
	{
		$qSeparator = preg_quote($separator, '#');

		$trans = [
			'\s+'                    => $separator,
			'(' . $qSeparator . ')+' => $separator,
			'&.+?;'                  => '',
			'[^\w\d\pL\pM _-]'       => ''
		];

        $str = preg_replace(array_keys(STRTR), array_values(STRTR), $str);
		$str = strip_tags($str);
		foreach ($trans as $key => $val)
		{
			$str = preg_replace('#' . $key . '#iu', $val, $str);
		}

		if ($lowercase === true)
		{
			$str = strtolower($str);
		}

		return trim($str);
	}
}

// ---------------------------------------------------------------------

if(!function_exists("getMimeType"))
{
    /**
     * getMimeType
     *
     * @param string $file
     * @return string
     */
    function getMimeType($file)
    {
		return Mime::getMime(pathinfo($file, PATHINFO_EXTENSION));
    }
}

if(!function_exists("favicon"))
{
    /**
     * Link Favicon
     *
     * @param string $file
     * @return string
     */
    function favicon($file)
    {
		$mime = Mime::getMime(pathinfo($file, PATHINFO_EXTENSION));
        return file_exists(BASE_PATH.$file) ? Html::link(null, [
            "rel"       =>  "icon",
            "href"      =>  base_url($file),
            "type"      =>  $mime
        ]) : "";
    }
}

if(!function_exists("assets"))
{
    /**
     * Link assets
     *
     * @param string $file
     * @return string
     */
    function assets($file)
    {
		switch (pathinfo($file, PATHINFO_EXTENSION)) {
			case "css":
				return css($file);
			case "png":
			case "jpg":
			case "jpeg":
			case "gif":
				return img($file);
			case "js":
				return js($file);
			default:
				return "";
		}
    }
}

if(!function_exists("img"))
{
    /**
	 * Link image
	 *
	 * @param string $file
	 * @param string $class
	 * @param int|string $width
	 * @param int|string $height
	 * @return string
	 */
    function img($file, $class = "img-fluid", $width = null, $height = null)
    {
		if(file_exists(ASSETS_DIR."img/".$file))
		{
			return Html::img(null, array_filter([
				"rel"       =>  "image",
				"src"      	=>  base_url(strtr(ASSETS_DIR, [BASE_PATH => "/"])."img/".$file),
				"width"     =>  $width,
				"height"    =>  $height,
				"class"		=>	$class
			]));
		}

		if(file_exists(BASE_PATH."uploads/".$file))
		{
			return Html::img(null, array_filter([
				"rel"       =>  "image",
				"src"      	=>  base_url("uploads/".$file),
				"width"     =>  $width,
				"height"    =>  $height,
				"class"		=>	$class
			]));
		}

        return "";
    }
}

if(!function_exists("css"))
{
    /**
     * Link stylesheet
     *
     * @param string $file
     * @return string
     */
    function css($file)
    {
		$file = strtr($file, [".css" => ""]).".css";
		
        return file_exists(ASSETS_DIR."css/".$file) ? Html::link(null, [
            "rel"       =>  "stylesheet",
            "href"      =>  base_url(strtr(ASSETS_DIR, [BASE_PATH => "/"])."css/".$file),
            "type"      =>  "text/css"
        ]) : "";
    }
}

if(!function_exists("js"))
{
    /**
     * Link javaScript
     *
     * @param string $file
     * @return string
     */
    function js($file)
    {
		$file = strtr($file, [".js" => ""]).".js";
		
        return file_exists(ASSETS_DIR."js/".$file) ? Html::script(null, [
            "src"      =>  base_url(strtr(ASSETS_DIR, [BASE_PATH => "/"])."js/".$file),
            "type"      =>  "text/javascript"
        ]) : "";
    }
}

if (! function_exists('url_is'))
{
	/**
	 * Determines si le path d'url a le path donné en parametre
	 *
	 * @param string|array $path
	 * @return boolean
	 */
	function url_is($path): bool
	{
		return request()->is($path);
	}
}

if (! function_exists('in_url'))
{
	/**
	 * Determines si le path d'url a le path donné en parametre
	 *
	 * @param string $path
	 * @param bool $withQuery
	 * @return boolean
	 */
	function in_url(string $path, bool $withQuery = true): bool
	{
		return stripos(trim(request()->url($withQuery), "/"), trim($path, "/")) !== false;
	}
}

//  -------------------------------------------------------------------------

if(! function_exists("json"))
{
    /**
     * Affiche ou retourne des données en JSON
     *
     * @param array $data
     * @param boolean $echo
     * @param integer $status
     * @param array $headers
	 * 
     * @return Response|void
     */
    function json(array $data, $echo = true, $status = 200, $headers = [])
    {
        return response()->json($data, $echo, $status, $headers);
    }
}

if(! function_exists("dtime"))
{
    /**
     * DateTime function
     *
     * @param string $datetime
     * @return Time
     */
    function dtime($datetime = null)
    {
        return !is_null($datetime) ? new Time($datetime) : Time::now();
    }
}