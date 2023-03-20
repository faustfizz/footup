<?php
namespace Footup\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ClassLocator
{
    public static function findRecursive(string $namespace): array
    {
        $namespacePath = self::translateNamespacePath($namespace);

        if ($namespacePath === '') {
            return [];
        }

        return self::searchClasses($namespace, $namespacePath);
    }

    protected static function translateNamespacePath(string $namespace): string
    {
        $namespace = strtr($namespace, ['Footup\\' => SYS_PATH, 'App\\' => APP_PATH, "\\"   =>  DS]);

        if (empty($namespace)) {
            return '';
        }

        return realpath(strtr($namespace, ['\\' =>    "/", "//"  =>  "/"])) ?: '';
    }

    private static function searchClasses(string $namespace, string $namespacePath): array
    {
        $classes = [];

        /**
         * @var \RecursiveDirectoryIterator $iterator
         */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($namespacePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        /** 
         * @var \SplFileInfo $item
         */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $nextPath = $iterator->current()->getPathname();
                $namespace = $namespace . '\\' . $item->getFilename();
                $classes = array_merge($classes, self::searchClasses($namespace, $nextPath));
                continue;
            }
            if ($item->isFile() && $item->getExtension() === 'php') {
                $class = $namespace. '\\' . $item->getBasename('.php');
                if (!class_exists($class)) {
                    continue;
                }
                if(!in_array($class, $classes))
                {
                    $classes[] = $class;
                }
            }
        }

        return $classes;
    }
}