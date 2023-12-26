<?php

namespace Magpie\System\HardCore\AutoloadResolvers;

use Magpie\System\Concepts\AutoloadReflectionPathResolvable;

class AutoloadLinkPathResolver implements AutoloadReflectionPathResolvable
{
    /**
     * @var string Source path, the path being linked
     */
    protected string $sourcePath;
    /**
     * @var string Target path, the destination where the path being linked
     */
    protected string $targetPath;


    /**
     * Constructor
     * @param string $sourcePath
     * @param string $targetPath
     */
    public function __construct(string $sourcePath, string $targetPath)
    {
        $this->sourcePath = static::normalizePath($sourcePath);
        $this->targetPath = static::normalizePath($targetPath);
    }


    /**
     * @inheritDoc
     */
    public function tryResolvePath(string $rootPath, string &$realPath) : void
    {
        $checkTargetPath = realpath($rootPath . $this->targetPath);
        if (!str_starts_with($realPath, $checkTargetPath)) return;

        $realPath = $rootPath . $this->sourcePath . substr($realPath, strlen($checkTargetPath) + 1);
    }


    protected static function normalizePath(string $path) : string
    {
        $ret = $path;
        if (!str_starts_with($ret, '/')) $ret = "/$ret";
        if (!str_ends_with($ret, '/')) $ret = "$ret/";

        return $ret;
    }
}