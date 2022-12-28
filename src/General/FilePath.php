<?php

namespace Magpie\General;

use Magpie\General\Traits\StaticClass;

/**
 * File path related utilities
 */
class FilePath
{
    use StaticClass;


    /**
     * Get filename from path
     * @param string $path Path or filename
     * @return string|null
     */
    public static function getFilename(string $path) : ?string
    {
        return Str::trimWithEmptyAsNull(basename($path));
    }


    /**
     * Get file extension from path or filename
     * @param string $path Path or filename
     * @return string|null
     */
    public static function getExtension(string $path) : ?string
    {
        return Str::trimWithEmptyAsNull(pathinfo($path, PATHINFO_EXTENSION));
    }


    /**
     * Change file extension
     * @param string $path Path or filename
     * @param string|null $newExtension
     * @return string
     */
    public static function changeExtension(string $path, ?string $newExtension) : string
    {
        // Do not change extension if not provided
        if ($newExtension === null) return $path;

        // Check directory
        $dir = dirname($path);
        if ($dir === '.') $dir = '';    // Special case

        // Check filename
        $filename = basename($path);
        if ($filename === '.') return $path;
        if ($filename === '..') return $path;

        // Remove old extension if any
        $dotPos = strrpos($filename, '.');
        if ($dotPos !== false && $dotPos !== 0) {
            $filename = substr($filename, 0, $dotPos);
        }

        // Assign extension
        $filename .= '.' . $newExtension;

        if (!is_empty_string($dir)) {
            return "$dir/$filename";
        } else {
            return $filename;
        }
    }
}