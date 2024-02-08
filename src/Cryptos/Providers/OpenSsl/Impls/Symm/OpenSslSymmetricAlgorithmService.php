<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\Cipher;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\CommonCipherMode;
use Magpie\Cryptos\Concepts\SymmetricCipherSetupServiceable;
use Magpie\Cryptos\Exceptions\InvalidBitSizeException;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;

/**
 * Service interface to OpenSSL's symmetric algorithm
 * @internal
 */
class OpenSslSymmetricAlgorithmService implements SymmetricCipherSetupServiceable
{
    /**
     * @var OpenSslSymmetricAlgorithmSeeder Parent seeder
     */
    protected readonly OpenSslSymmetricAlgorithmSeeder $seeder;
    /**
     * @var int|null Current block size
     */
    protected ?int $currentBlockNumBits;
    /**
     * @var string|null Current mode
     */
    protected ?string $currentMode;
    /**
     * @var string Current OpenSSL's algorithm name
     */
    protected string $currentOpenSslAlgoName;


    /**
     * Constructor
     * @param OpenSslSymmetricAlgorithmSeeder $seeder
     * @param int|null $blockNumBits
     * @param string|null $mode
     * @throws SafetyCommonException
     */
    public function __construct(OpenSslSymmetricAlgorithmSeeder $seeder, ?int $blockNumBits, ?string $mode)
    {
        $this->seeder = $seeder;

        $this->seeder->checkBlockSizeAndMode($blockNumBits, $mode);
        $this->currentBlockNumBits = $blockNumBits;
        $this->currentMode = $mode;
        $this->currentOpenSslAlgoName = $this->getOpenSslAlgoName();
    }


    /**
     * @inheritDoc
     */
    public function getBlockNumBits() : int
    {
        return $this->currentBlockNumBits ?? $this->seeder->getDefaultBlockSize();
    }


    /**
     * @inheritDoc
     */
    public function getIvNumBits() : ?int
    {
        $ret = @openssl_cipher_iv_length($this->getOpenSslAlgoName());
        if ($ret === false) return null;

        return $ret * 8;
    }


    /**
     * @inheritDoc
     */
    public function checkKey(string $key) : void
    {
        $keyNumBits = strlen($key) * 8;
        $this->seeder->checkKeySize($keyNumBits, $this->currentBlockNumBits, $this->currentMode);
    }


    /**
     * @inheritDoc
     */
    public function checkIv(string $iv) : void
    {
        $expectedIvNumBits = $this->getIvNumBits();
        if ($expectedIvNumBits === null) {
            // IV must not be supplied / IV must only be blank
            if ($iv !== '') throw new UnsupportedException(_l('IV is not expected but supplied'));
        } else {
            // Check alignment
            if (($expectedIvNumBits % 8) !== 0) throw new UnsupportedValueException($expectedIvNumBits, _l('expected IV bit size'));

            // Check IV
            $ivNumBits = strlen($iv) << 3;
            if ($ivNumBits != $expectedIvNumBits) throw new InvalidBitSizeException($ivNumBits, _l('IV'), $expectedIvNumBits);
        }
    }


    /**
     * @inheritDoc
     */
    public function createCipher(string $key, ?string $iv, ?Padding $padding) : Cipher
    {
        // Check for IV when expected
        if ($iv === null) {
            $expectedIvSize = $this->getIvNumBits();
            if ($expectedIvSize !== null) throw new MissingArgumentException('iv');
        }

        if (static::hasAead($this->currentMode)) {
            return new OpenSslAeadCipher($this->currentOpenSslAlgoName, $key, $iv, $this->currentMode, $padding);
        } else {
            return new OpenSslCipher($this->currentOpenSslAlgoName, $key, $iv, $this->currentMode, $padding);
        }
    }


    /**
     * Get corresponding OpenSSL's algorithm name
     * @return string
     */
    protected function getOpenSslAlgoName() : string
    {
        return $this->seeder->buildOpenSslMethodName($this->currentBlockNumBits, $this->currentMode);
    }


    /**
     * If given mode has AEAD
     * @param string|null $mode
     * @return bool
     */
    protected static function hasAead(?string $mode) : bool
    {
        return match ($mode) {
            CommonCipherMode::CCM,
            CommonCipherMode::GCM,
                => true,
            default,
                => false,
        };

    }
}