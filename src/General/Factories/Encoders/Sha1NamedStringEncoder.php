<?php

namespace Magpie\General\Factories\Encoders;

use Magpie\Cryptos\Algorithms\Hashes\CommonHashTypeClass;
use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\General\Concepts\NamedStringEncodable;
use Magpie\System\HardCore\NumberCodecs\BinaryUint64Codec;
use Magpie\System\Kernel\Kernel;

/**
 * Named string encoder using trimmed SHA-1 (into a 64-bit integer)
 */
class Sha1NamedStringEncoder implements NamedStringEncodable
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'sha1';
    /**
     * @var string Salt value
     */
    protected readonly string $salt;


    /**
     * Constructor
     * @param string $salt Salt value
     */
    public function __construct(string $salt = '')
    {
        $this->salt = $salt;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(NamedStringEncodable::class, $this);
    }


    /**
     * @inheritDoc
     */
    public final function encode(string $value) : int
    {
        $plaintext = $value . $this->salt;

        $hashed = Hasher::initialize(CommonHashTypeClass::SHA1)->hash($plaintext)->asBinary();
        $partial = substr($hashed, -8);

        // NOTE: always drop the highest bit if set (due to PHP's limitation)
        return BinaryUint64Codec::decodeBigEndian($partial) & 0x7fffffffffffffff;
    }
}