<?php

namespace Magpie\General\IOs;

use Exception;
use Magpie\Exceptions\FileOperationFailedException;
use Magpie\General\Traits\StaticClass;
use function fopen;

/**
 * Expose standard PHP IO functions by handling the errors consistently
 */
final class PhpIo
{
    use StaticClass;


    /**
     * Wrapper to fopen
     * @param string $filename
     * @param string $mode
     * @param bool $use_include_path
     * @return resource
     * @throws FileOperationFailedException
     */
    public static function fopen(string $filename, string $mode, bool $use_include_path = false) : mixed
    {
        try {
            $ret = fopen($filename, $mode, $use_include_path);
            if ($ret === false) throw new Exception('fopen() failed');
            return $ret;
        } catch (\Throwable $ex) {
            throw new FileOperationFailedException($filename, _l('open'), previous: $ex);
        }
    }
}