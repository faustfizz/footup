<?php

/**
 * FOOTUP -  2021 - 2023
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Lang
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Lang;

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
		if (strpos($linea, '.') === false)
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
		$file = strtolower($file);

		$output = $this->langs[$locale][$file][$parsedLine] ?? null;
		if ($output !== null)
		{
			return $output;
		}

		foreach (explode('.', $parsedLine) as $row)
		{
			if (! isset($current))
			{
				$current = $this->langs[$locale][$file] ?? null;
			}

			$output = $current[$row] ?? null;
			if (is_array($output))
			{
				$current = $output;
			}
		}

		if ($output !== null)
		{
			return $output;
		}

		$row = current(explode('.', $parsedLine));
		$key = substr($parsedLine, strlen($row) + 1);

		return $this->langs[$locale][$file][$row][$key] ?? null;
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
			$file = $fileAndKey;
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
		list($file, $line) = explode(".", $line, 2);

		if (! isset($this->langs[$locale][$file]) || ! array_key_exists($line, $this->langs[$locale][$file]))
		{
			$this->load($file, $locale);
		}

		return [
			$file,
			$line,
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
	 * @param string  $file
	 * @param string  $locale
	 * @param boolean $return
	 *
	 * @return void|array
	 */
	protected function load(string $file, string $locale, bool $return = false)
	{
		$file = strtolower($file);


        $this->loadedFiles[$locale] = !array_key_exists($locale, $this->loadedFiles) ? [] : $this->loadedFiles[$locale];

		if (in_array($file, $this->loadedFiles[$locale], true))
		{
			// Don't load it more than once.
			return [];
		}
        
        $this->langs[$locale] = !array_key_exists($locale, $this->langs) ? [] : $this->langs[$locale];

        $this->langs[$locale][$file] = !array_key_exists($file, $this->langs[$locale]) ? [] : $this->langs[$locale][$file];

		$path = "Lang/{$this->getLocaleDirname($locale)}/{$file}.json";

		$lang = $this->require($path);
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


        $this->loadedFiles[$locale] = !array_key_exists($locale, $this->loadedFiles) ? [] : $this->loadedFiles[$locale];

		if (!in_array($file, $this->loadedFiles[$locale], true))
		{
			// load it more.
			$this->loadedFiles[$locale][] = $file;
		}

		$path = "Lang/{$this->getLocaleDirname($locale)}/{$file}.json";

		if(isset($this->langs[$locale][$file]))
		{
			$lang = $this->langs[$locale][$file];
		}else{
			$lang = $this->require($path);
	
			$lang = $lang ? json_decode($lang, true) : [];
		}

		if(is_array($key))
		{
			foreach($key as $k => $v)
			{
				$lang[$k] = $v;
			}
		}else{
			$lang[$key] = $value;
		}


		$json = json_encode($lang, JSON_PRETTY_PRINT);

		try{
			if(!is_dir(APP_PATH."Lang/{$this->getLocaleDirname($locale)}"))
			{
				@mkdir(APP_PATH."Lang/{$this->getLocaleDirname($locale)}", 0777, true);
			}

			if(!file_exists(APP_PATH.$path) && !file_exists(SYS_PATH.$path))
			{
				file_put_contents(APP_PATH.$path, $json);
			}
			file_put_contents(APP_PATH.$path, $json);
		}catch(\Exception $e){
			return $e->getMessage();
		}

		
		// Merge our string
		$this->langs[$locale][$file] = $lang;

		return true;
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
	public function removeLine(string $file, $key = null, string $locale = null)
	{
		if (strpos($file, '.'))
		{
			list($file, $key) = explode('.', $file, 2);
		}

		$file = strtolower($file);


        $this->loadedFiles[$locale] = !array_key_exists($locale, $this->loadedFiles) ? [] : $this->loadedFiles[$locale];

		if (!in_array($file, $this->loadedFiles[$locale], true))
		{
			// load it more.
			$this->loadedFiles[$locale][] = $file;
		}

		$path = "Lang/{$this->getLocaleDirname($locale)}/{$file}.json";

		if(isset($this->langs[$locale][$file]))
		{
			$lang = $this->langs[$locale][$file];
		}else{
			$lang = $this->require($path);
	
			$lang = $lang ? json_decode($lang, true) : [];
		}

		if(is_array($key))
		{
			foreach($key as $k)
			{
				unset($lang[$k]);
			}
		}else{
			unset($lang[$key]);
		}


		$json = json_encode($lang ?? [], JSON_PRETTY_PRINT);

		try{
			if(!is_dir(APP_PATH."Lang/{$this->getLocaleDirname($locale)}"))
			{
				@mkdir(APP_PATH."Lang/{$this->getLocaleDirname($locale)}", 0777, true);
			}

			if(!file_exists(APP_PATH.$path) && !file_exists(SYS_PATH.$path))
			{
				file_put_contents(APP_PATH.$path, $json);
			}
			file_put_contents(APP_PATH.$path, $json);
		}catch(\Exception $e){
			return $e->getMessage();
		}

		
		// Merge our string
		$this->langs[$locale][$file] = $lang;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * @param string $path
	 * @return mixed
	 */
	protected function require(string $path)
	{
        if(!file_exists(APP_PATH.$path) && !file_exists(SYS_PATH.$path))
        {
            return [];
        }

        $path = file_exists(APP_PATH.$path) ? APP_PATH.$path : SYS_PATH.$path;
        
		$strings = file_get_contents($path);


		return $strings;
	}
}