<?php

declare(strict_types=1);

namespace Footup\Files;

/*
|--------------------------------------------------------------------------
| File — file system operations
|
| Usage:
|   File::get('storage/file.txt')                    // Read content
|   File::put('storage/file.txt', 'content')         // Write
|   File::append('storage/file.txt', 'more')         // Append
|   File::delete('storage/file.txt')                 // Delete
|   File::exists('storage/file.txt')                 // Check exists
|   File::copy('from.txt', 'to.txt')                 // Copy
|   File::move('from.txt', 'to.txt')                 // Move
|   File::size('file.txt')                           // Size in bytes
|   File::mime('file.txt')                           // MIME type
|--------------------------------------------------------------------------
*/

class File
{
    /**
     * Get file contents
     */
    public static function get(string $path): string
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException("Failed to read file: {$path}");
        }

        return $contents;
    }

    /**
     * Write content to file (overwrites if exists)
     */
    public static function put(string $path, string $content): void
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException("Failed to write file: {$path}");
        }
    }

    /**
     * Append content to file
     */
    public static function append(string $path, string $content): void
    {
        if (!file_exists($path)) {
            self::put($path, $content);

            return;
        }

        if (file_put_contents($path, $content, FILE_APPEND) === false) {
            throw new \RuntimeException("Failed to append to file: {$path}");
        }
    }

    /**
     * Delete a file
     */
    public static function delete(string $path): void
    {
        if (file_exists($path) && !unlink($path)) {
            throw new \RuntimeException("Failed to delete file: {$path}");
        }
    }

    /**
     * Check if file exists
     */
    public static function exists(string $path): bool
    {
        return file_exists($path) && is_file($path);
    }

    /**
     * Check if path is directory
     */
    public static function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Copy a file
     */
    public static function copy(string $from, string $to): void
    {
        if (!self::exists($from)) {
            throw new \RuntimeException("File not found: {$from}");
        }

        $dir = dirname($to);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!copy($from, $to)) {
            throw new \RuntimeException("Failed to copy file from {$from} to {$to}");
        }
    }

    /**
     * Move/rename a file
     */
    public static function move(string $from, string $to): void
    {
        if (!self::exists($from)) {
            throw new \RuntimeException("File not found: {$from}");
        }

        $dir = dirname($to);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!rename($from, $to)) {
            throw new \RuntimeException("Failed to move file from {$from} to {$to}");
        }
    }

    /**
     * Get file size in bytes
     */
    public static function size(string $path): int
    {
        if (!self::exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $size = filesize($path);

        if ($size === false) {
            throw new \RuntimeException("Failed to get file size: {$path}");
        }

        return $size;
    }

    /**
     * Get MIME type
     */
    public static function mime(string $path): string
    {
        if (!self::exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        // Try finfo first
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);

            if ($mime !== false) {
                return $mime;
            }
        }

        // Fallback to extension mapping
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'txt' => 'text/plain',
            'html', 'htm' => 'text/html',
            'pdf' => 'application/pdf',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'csv' => 'text/csv',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };
    }

    /**
     * Get file extension
     */
    public static function extension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * Get file name without extension
     */
    public static function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Get directory name
     */
    public static function directory(string $path): string
    {
        return dirname($path);
    }

    /**
     * Create directory if not exists
     */
    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = true): void
    {
        if (!is_dir($path) && !mkdir($path, $mode, $recursive)) {
            throw new \RuntimeException("Failed to create directory: {$path}");
        }
    }

    /**
     * Delete directory recursively
     */
    public static function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = scandir($path);

        if ($files === false) {
            throw new \RuntimeException("Failed to read directory: {$path}");
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = "{$path}/{$file}";

            if (is_dir($fullPath)) {
                self::deleteDirectory($fullPath);
            } else {
                self::delete($fullPath);
            }
        }

        if (!rmdir($path)) {
            throw new \RuntimeException("Failed to delete directory: {$path}");
        }
    }
}
