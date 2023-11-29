<?php

namespace Magpie\System\Concepts;

/**
 * May resolve path for AutoloadReflection
 */
interface AutoloadReflectionPathResolvable
{
    /**
     * Try to resolve the given path
     * @param string $rootPath
     * @param string $realPath
     * @return void
     */
    public function tryResolvePath(string $rootPath, string& $realPath) : void;
}