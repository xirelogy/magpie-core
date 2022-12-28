<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\Cryptos\Impls\Traits\HashFromFile;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Objects\BinaryData;

/**
 * Common HMAC hasher utilizing PHP
 * @internal
 */
abstract class CommonNativeHmacHasher extends Hasher
{
    use HashFromFile;


    /**
     * @var string Native algorithm
     */
    protected string $nativeAlgo;
    /**
     * @var string HMAC key
     */
    protected string $key;


    /**
     * Constructor
     * @param string $nativeAlgo
     * @param string $key
     */
    protected function __construct(string $nativeAlgo, string $key)
    {
        $this->nativeAlgo = $nativeAlgo;
        $this->key = $key;
    }


    /**
     * @inheritDoc
     */
    protected function onHashFileNative(string $path) : string|false
    {
        return @hash_hmac_file($this->nativeAlgo, $path, $this->key, true);
    }


    /**
     * @inheritDoc
     */
    protected function onHash(string $data) : BinaryData
    {
        $ret = @hash_hmac($this->nativeAlgo, $data, $this->key, true);
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
     * @param BinaryData $key
     * @return static
     */
    public static function createNativeInstance(string $typeClass, string $nativeAlgo, BinaryData $key) : static
    {
        $className = 'CommonNativeHmacHasher•D•' . $typeClass;
        $baseClassName = self::class;

        if (!class_exists($className, false)) {
            eval('class ' . $className . ' extends ' . $baseClassName . ' { '
                . 'public function __construct(string $nativeAlgo, string $key) { parent::__construct($nativeAlgo, $key); }'
                . 'public static function getTypeClass() : string { return \'hmac-' . $typeClass . '\'; }'
                . ' }');
        }

        return new $className($nativeAlgo, $key->asBinary());
    }
}