<?php

namespace Magpie\Cryptos\Impls\Traits;

use Magpie\Exceptions\FileOperationFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Objects\BinaryData;

/**
 * Support hashing from file using native PHP functionalities
 * @internal
 */
trait HashFromFile
{
    /**
     * Handle calculation of hash from file
     * @param string $path
     * @return BinaryData
     * @throws SafetyCommonException
     */
    protected function onHashFile(string $path) : BinaryData
    {
        $result = $this->onHashFileNative($path);
        if ($result === false) throw new FileOperationFailedException($path);

        return BinaryData::fromBinary($result);
    }


    /**
     * Handle calculation of hash from file using native PHP functionalities
     * @param string $path
     * @return string|false
     */
    protected abstract function onHashFileNative(string $path) : string|false;
}