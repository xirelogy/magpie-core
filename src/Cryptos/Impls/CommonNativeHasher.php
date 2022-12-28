<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\Cryptos\Impls\Traits\HashFromFile;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Objects\BinaryData;

/**
 * Common hasher utilizing PHP
 * @internal
 */
abstract class CommonNativeHasher extends Hasher
{
    use HashFromFile;


    /**
     * @var string Native algorithm
     */
    protected string $nativeAlgo;


    /**
     * Constructor
     * @param string $nativeAlgo
     */
    protected function __construct(string $nativeAlgo)
    {
        $this->nativeAlgo = $nativeAlgo;
    }


    /**
     * @inheritDoc
     */
    protected function onHashFileNative(string $path) : string|false
    {
        return @hash_file($this->nativeAlgo, $path, true);
    }


    /**
     * @inheritDoc
     */
    protected function onHash(string $data) : BinaryData
    {
        $ret = @hash($this->nativeAlgo, $data, true);
        if ($ret === false) throw new OperationFailedException();

        return BinaryData::fromBinary($ret);
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize() : static
    {
        // Common native hasher cannot be initialized in this manner
        throw new UnsupportedException();
    }


    /**
     * Create a specific native instance for current hasher
     * @param string $typeClass
     * @param string $nativeAlgo
     * @return static
     */
    public static function createNativeInstance(string $typeClass, string $nativeAlgo) : static
    {
        $className = 'CommonNativeHasher•D•' . $typeClass;
        $baseClassName = self::class;

        if (!class_exists($className, false)) {
            eval('class ' . $className . ' extends ' . $baseClassName . ' { '
                . 'public function __construct(string $nativeAlgo) { parent::__construct($nativeAlgo); }'
                . 'public static function getTypeClass() : string { return \'' . $typeClass . '\'; }'
                . ' }');
        }

        return new $className($nativeAlgo);
    }
}