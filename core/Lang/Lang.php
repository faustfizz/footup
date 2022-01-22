<?php

/**
 * FOOTUP - 0.1.3 - 12.2021
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Lang
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */

namespace Footup\Lang;

use FilesystemIterator;
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

		// if still not found, try English
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
        $this->loadedFiles[$locale] = !array_key_exists($locale, $this->loadedFiles) ? [] : $this->loadedFiles[$locale];

		if (in_array($file, $this->loadedFiles[$locale], true))
		{
			// Don't load it more than once.
			return [];
		}
        
        $this->langs[$locale] = !array_key_exists($locale, $this->langs) ? [] : $this->langs[$locale];

        $this->langs[$locale][$file] = !array_key_exists($file, $this->langs[$locale]) ? [] : $this->langs[$locale][$file];

		$path = "Lang/{$locale}/{$file}.php";

		$lang = $this->require($path);

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
	 * @param string $path
	 * @return array
	 */
	protected function require(string $path): array
	{
        if(!file_exists(APP_PATH.$path) && !file_exists(SYS_PATH.$path))
        {
            return [];
        }

        $path = file_exists(APP_PATH.$path) ? APP_PATH.$path : SYS_PATH.$path;
        
		$strings = require $path;


		return $strings;
	}
}