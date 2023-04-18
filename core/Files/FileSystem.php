<?php
/**
 * FOOTUP FRAMEWORK
 * *************************
 * Hard Coded by Faustfizz Yous
 * 
 * @package Footup/Files
 * @version 0.1
 * @author Faustfizz Yous <youssoufmbae2@gmail.com>
 */
namespace Footup\Files;

use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Exception;

class FileSystem
{
    /**
     * @param  string $path
     * @return string
     */
    public static function hash($path)
    {
        return md5_file($path);
    }

    /**
     * @param  string $path
     * @param  bool   $lock
     * @return string
     */
    public static function get($path, $lock = false)
    {
        if (is_file($path)) {
            if ($lock === true) {
                $handle = fopen($path, 'rb');
                $content = '';

                if ($handle) {
                    try {
                        if (flock($handle, LOCK_SH)) {
                            clearstatcache(true, $path);

                            $content = fread($handle, filesize($path) ?: 1);

                            flock($handle, LOCK_UN);
                        }
                    } finally {
                        fclose($handle);
                    }
                }

                return $content;
            }

            return file_get_contents($path);
        }

        throw new Exception(text("File.fileNotExist", [$path]));
    }

    /**
     * @param  string $path
     * @param  string $content
     * @param  bool   $lock
     * @return int
     */
    public static function put(string $path, string $content, bool $lock = false)
    {
        return file_put_contents($path, $content, $lock ? LOCK_EX : 0);
    }

    /**
     * @param string $path
     * @param string $content
     */
    public static function replace(string $path, $content)
    {
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $temp = tempnam(dirname($path), basename($path));

        self::chmod($temp, 0777 - umask());

        self::put($temp, $content);

        self::move($temp, $path);
    }

    /**
     * @param  string $path
     * @param  string $data
     * @return int
     */
    public static function prepend($path, $data)
    {
        if (file_exists($path)) {
            $data .= self::get($path);
        }

        return self::put($path, $data);
    }

    /**
     * @param  string $path
     * @param  string $data
     * @return int
     */
    public static function append($path, $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * @param  string $path
     * @param  int  $permission
     * @return mixed
     */
    public static function chmod(string $path, int $permission = null)
    {
        if (!is_null($permission)) {
            return chmod($path, $permission);
        }

        return mb_substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * @param  string $path
     * @param  bool   $preserve
     * @return bool|void
     */
    public static function delete(string $path, bool $preserve = false)
    {
        if (is_file($path) && file_exists($path)) {
            return @unlink($path);
        } elseif (is_dir($path)) {
            $items = new FilesystemIterator($path);

            foreach ($items as $item) {
                if ($item->isFile()) {
                    $status = self::delete($item);
                } elseif ($item->isDir() && ! $item->isLink()) {
                    if (self::delete($item)) {
                        $status = @rmdir($item);
                    }
                }
            }

            if ($preserve) {
                @rmdir($path);
            }

            return $status;
        }
    }

    /**
     * @param  string $path
     * @param  string $target
     * @return bool
     */
    public static function move($path, $target)
    {
        return rename($path, $target);
    }

    /**
     * @param  string $directory
     * @param  string $destination
     * @param  int    $flag
     * @return bool
     */
    public static function copy(string $directory, string $destination, int $flag = null)
    {
        if (! is_dir($directory) && ! is_file($directory)) {
            return false;
        }

        if (is_dir($directory)) {
            if (! $flag) {
                $flag = FilesystemIterator::SKIP_DOTS;
            }

            if (! is_dir($destination)) {
                self::mkdir($destination, 0755, true);
            }

            $items = new FilesystemIterator($directory, $flag);

            foreach ($items as $item) {
                $target = $destination . '/' . $item->getBasename();

                if ($item->isDir()) {
                    if (! self::copy($item->getPathname(), $target, $flag)) {
                        return false;
                    }
                } else {
                    if (! self::copy($item->getPathname(), $target, $flag)) {
                        return false;
                    }
                }
            }

            return true;
        }

        return copy($directory, $destination);
    }

    /**
     * @param  string $path
     * @param  int    $permission
     * @param  bool   $recursive
     * @return bool
     */
    public static function mkdir(string $path, int $permission = 0755, bool $recursive = true)
    {
        if (is_dir($path) || is_file($path)) {
            return true;
        }

        return mkdir($path, $permission, $recursive);
    }

    /**
     * @param  string $path
     * @param  int    $flag
     * @return RecursiveIteratorIterator|array
     */
    public static function iterator(string $path, int $flag = null)
    {
        if (! is_dir($path)) {
            return [];
        }

        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, $flag ?? RecursiveDirectoryIterator::SKIP_DOTS)
        );
    }
}
