<?php

/**
 * FOOTUP FRAMEWORK
 * *************************
 * A Rich Featured LightWeight PHP MVC Framework - Hard Coded by Faustfizz Yous
 * 
 * @package Footup\Lang
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Lang;

use Footup\Utils\Arrays\ArrDots;
use Locale;
use MessageFormatter;

class Lang
{
	/**
	 * @var array
	 */
	protected $langs = [];

	/**
	 * @var string
	 */
	protected $locale;

	/**
	 * @var boolean
	 */
	protected $intlSupport = false;

	/**
	 * @var array
	 */
	protected $loadedFiles = [];

	//--------------------------------------------------------------------

	public function __construct(string $locale = null)
	{
		$this->locale = is_null($locale) ? (Locale::getDefault() ?? config()->lang) : $locale;

		if (class_exists('MessageFormatter'))
		{
			$this->intlSupport = true;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * @param string $locale
	 * @return $this
	 */
	public function setLocale(string $locale = null)
	{
		if (! is_null($locale))
		{
			$this->locale = $locale;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function getLocale(): string
	{
		return $this->locale;
	}

	//--------------------------------------------------------------------

	/**
	 * @param string $linea Line.
	 * @param array  $args Arguments.
	 * @return string|string[] Returns line.
	 */
	public function getText(string $linea, array $args = [])
	{
		// if no file is given, just parse the line
		if (strpos($linea, '.') === false && !$this->localeFileExists($this->locale))
		{
			return $this->formatMessage($linea, $args);
		}

		// Parse out the file name and the actual alias.
		// Will load the langs file and strings.
		list($file, $line) = $this->parseLine($linea, $this->locale);

		$output = $this->getOutput($this->locale, $file, $line);
		
		if ($output === null && strpos($this->locale, '_'))
		{
			list($locale) = explode('_', $this->locale, 2);
			
			list($file, $line) = $this->parseLine($linea, $locale);

			$output = $this->getOutput($locale, $file, $line);
		}

		// if still not found, try French
		if ($output === null)
		{
			list($file, $line) = $this->parseLine($linea, 'fr');

			$output = $this->getOutput('fr', $file, $line);
		}

		$output = $output ?? $line;

		return $this->formatMessage($output, $args);
	}

	//--------------------------------------------------------------------

	/**
	 * @return array|string|null
	 */
	private function getOutput(string $locale, string $file, string $parsedLine)
	{
		$output = $this->langs[$locale][$file][$parsedLine] ?? null;
		if ($output !== null)
		{
			return $output;
		}

		return ArrDots::get($this->langs[$locale][$file], $parsedLine);

		// foreach (explode('.', $parsedLine) as $row)
		// {
		// 	if (! isset($current))
		// 	{
		// 		$current = $this->langs[$locale][$file] ?? null;
		// 	}

		// 	$output = $current[$row] ?? null;
		// 	if (is_array($output))
		// 	{
		// 		$current = $output;
		// 	}
		// }

		// if ($output !== null)
		// {
		// 	return $output;
		// }

		// $row = current(explode('.', $parsedLine));
		// $key = substr($parsedLine, strlen($row) + 1);

		// return $this->langs[$locale][$file][$row][$key] ?? null;
	}

	//--------------------------------------------------------------------

	/**
	 * Add or edit a line 
	 *
	 * @param string $fileAndKey ex: File.key or Menu.home
	 * @param mixed $value
	 * @param string|null $locale
	 * @return bool|string
	 */
	public function setText(string $fileAndKey, mixed $value = null, string $locale = null)
	{
		if (strpos($fileAndKey, '.'))
		{
			list($file, $key) = explode('.', $fileAndKey, 2);
		}else{
			$file = $locale ?? $this->locale;
			$key = $fileAndKey;
		}
		
		return $this->write(($locale ?? $this->locale), $file, $key, $value);
	}

	//--------------------------------------------------------------------

	/**
	 * Write into file with given key and value
	 *
	 * @param string $file
	 * @param string|array $key
	 * @param mixed $value
	 * @param string $locale
	 * @return bool|string
	 */
	public function setInput(string $file, $key, mixed $value = null, string $locale = null)
	{
		if(!is_array($key) && is_null($value))
		{
			return false;
		}

		return $this->write(($locale ?? $this->locale), $file, $key, $value);
	}

	/**
	 * Parses the langs string which should include the
	 * filename as the first segment (separated by period).
	 *
	 * @param string $line
	 * @param string $locale
	 *
	 * @return array
	 */
	protected function parseLine(string $line, string $locale): array
	{
		$langFilePath = null;

		// We try first to see if we have a lang file with the name of the current locale like fr.json so line become the key even in array dot notation
		if ($fileExists = $this->localeFileExists($locale)) {
			$langFilePath = $fileExists->file;

			if (!isset($this->langs[$locale][$langFilePath]) || !ArrDots::has($this->langs[$locale][$langFilePath], $line)) {
				$this->load($langFilePath, $locale);
			}

			return [
				$langFilePath,
				$line
			];
		}

		list($file, $line) = explode(".", $line, 2);

		// check if the $file like fr/message.json exists, we load it and $line become a key
		$fileExists = $this->fileExists($file);
		$langFilePath = $fileExists->file;
		
		if (! isset($this->langs[$locale][$langFilePath]) || ! ArrDots::has($this->langs[$locale][$langFilePath], $line))
		{
			$this->load($langFilePath, $locale);
		}

		return [
			$langFilePath,
			$line
		];
	}


	/**
	 * Get Locale directory
	 *
	 * @param string|null $locale
	 * @return string
	 */
	private function getLocaleDirname(string $locale = null): string
	{
		if ($locale ?? strpos($this->locale, '_'))
		{
			list($dirname) = explode('_', $this->locale, 2);

			return $dirname;
		}
		return $locale ?? $this->locale;
	}

	//--------------------------------------------------------------------

	/**
	 * @param string|array $message Message.
	 * @param array	       $args    Arguments.
	 *
	 * @return string|array Returns formatted message.
	 */
	protected function formatMessage($message, array $args = [])
	{
		if (! $this->intlSupport || $args === [])
		{
			return $message;
		}

		if (is_array($message))
		{
			foreach ($message as $index => $value)
			{
				$message[$index] = $this->formatMessage($value, $args);
			}

			return $message;
		}

		return MessageFormatter::formatMessage($this->locale, $message, $args);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads a langs file in the current locale. If $return is true,
	 * will return the file's contents, otherwise will merge with
	 * the existing langs lines.
	 *
	 * @param string  $file a file path
	 * @param string  $locale
	 * @param boolean $return
	 *
	 * @return void|array
	 */
	protected function load(string $file, string $locale, bool $return = false)
	{
        $this->loadedFiles[$locale] = !array_key_exists($locale, $this->loadedFiles) ? [] : $this->loadedFiles[$locale];

		if (in_array($file, $this->loadedFiles[$locale], true))
		{
			// Don't load it more than once.
			return [];
		}
        
        $this->langs[$locale] = !array_key_exists($locale, $this->langs) ? [] : $this->langs[$locale];

		$lang = $this->require($file);
		$lang = $lang ? json_decode($lang, true) : [];

		if ($return)
		{
			return $lang;
		}

		$this->loadedFiles[$locale][] = $file;

		// Merge our string
		$this->langs[$locale][$file] = $lang;
	}

	//--------------------------------------------------------------------

	/**
	 * Write into file
	 *
	 * @param string $locale
	 * @param string $file
	 * @param string|array $key
	 * @param mixed $value
	 * @return bool|string
	 */
	protected function write(string $locale, string $file, $key, mixed $value = null)
	{
		$file = strtolower($file);
		$filePath = null;

		if ($fileExists = $this->localeFileExists($locale)) {
			$filePath = $fileExists->file;
		}

		if ($fileExists = $this->fileExists($file)) {
			$filePath = $fileExists->file;
		}

        $this->loadedFiles[$locale] = !array_key_exists($locale, $this->loadedFiles) ? [] : $this->loadedFiles[$locale];

		if (!in_array($filePath, $this->loadedFiles[$locale], true))
		{
			// load it more.
			$this->loadedFiles[$locale][] = $filePath;
		}

		if(isset($this->langs[$locale][$filePath]))
		{
			$lang = $this->langs[$locale][$filePath];
		}else{
			$lang = $this->require($filePath);
	
			$lang = $lang ? json_decode($lang, true) : [];
		}

		if(is_array($key))
		{
			if ($this->localeFileExists($locale)) {
				$lang[$file] = $key;
			} else {
				foreach($key as $k => $v)
				{
					$lang[$k] = $v;
				}
			}

		} else {
			if ($this->localeFileExists($locale)) {
				$lang[$file] = [$key => $value];
			} else {
				$lang[$key] = $value;
			}
		}

		$json = json_encode($lang, JSON_PRETTY_PRINT);

		try{
			file_put_contents($filePath, $json);
		}catch(\Exception $e){
			return $e->getMessage();
		}
		
		// Merge our string
		$this->langs[$locale][$filePath] = $lang;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Write into file
	 *
	 * @param string $locale
	 * @param string $file
	 * @param string|array|null $key
	 * @param mixed $value
	 * @return bool|string
	 */
	public function removeLine(string $file, $key = null, string $locale = null)
	{
		if (strpos($file, ".") !== false) {
			list($file, $key) = explode('.', $file, 2);
		}

		$locale = $locale ?? $this->locale;

		$file = strtolower($file);
		$filePath = null;

		if ($fileExists = $this->localeFileExists($locale)) {
			$filePath = $fileExists->file;
		}

		if ($fileExists = $this->fileExists($file)) {
			$filePath = $fileExists->file;
		}

		$this->loadedFiles[$locale] = !array_key_exists($locale, $this->loadedFiles) ? [] : $this->loadedFiles[$locale];

		if (!in_array($filePath, $this->loadedFiles[$locale], true)) {
			// load it more.
			$this->loadedFiles[$locale][] = $filePath;
		}

		if (isset($this->langs[$locale][$filePath])) {
			$lang = $this->langs[$locale][$filePath];
		} else {
			$lang = $this->require($filePath);

			$lang = $lang ? json_decode($lang, true) : [];
		}

		if (is_array($key)) {
			if ($this->localeFileExists($locale)) {
				unset($lang[$file]);
			} else {
				ArrDots::remove($lang, $key);
			}

		} else {
			if ($this->localeFileExists($locale)) {
				unset($lang[$file]);
			} elseif ($key) {
				ArrDots::remove($lang, $key);
			}
		}

		$json = json_encode($lang, JSON_PRETTY_PRINT);

		try {
			file_put_contents($filePath, $json);
		} catch (\Exception $e) {
			return $e->getMessage();
		}

		// Merge our string
		$this->langs[$locale][$filePath] = $lang;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * @param string $path -- come from fileExists function in this same class
	 * @return mixed
	 */
	protected function require(string $path)
	{
        if(!file_exists($path) && !file_exists($path))
        {
            return [];
        }
        
		$strings = file_get_contents($path);

		return $strings;
	}

	/**
	 * If a single locale file exists like fr.json
	 * 
	 * @param string $locale
	 * @return object|false
	 */
	protected function localeFileExists(string $locale)
	{
		$localFile = "Lang/{$locale}.json";

		if(!file_exists(APP_PATH.$localFile) && !file_exists(SYS_PATH.$localFile))
        {
            return false;
        }

        $localFile = file_exists(APP_PATH.$localFile) ? APP_PATH.$localFile : SYS_PATH.$localFile;

		return (object) ['exists' => true, 'file' => $localFile];
	}

	/**
	 * If a single locale file exists like fr/file.json
	 * 
	 * @param string $file
	 * @return object|false
	 */
	protected function fileExists(string $file)
	{
		$file = strtolower($file);

		$localFile = "Lang/{$this->getLocaleDirname()}/{$file}.json";

		if(!file_exists(APP_PATH.$localFile) && !file_exists(SYS_PATH.$localFile))
        {
            return false;
        }

        $localFile = file_exists(APP_PATH.$localFile) ? APP_PATH.$localFile : SYS_PATH.$localFile;

		return (object) ['exists' => true, 'file' => $localFile];
	}

}