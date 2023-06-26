<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Magpie\Cryptos\Impls\ImplSymmCipher;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Exceptions\InvalidArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;

/**
 * Specific implementation for symmetric cipher
 * @internal
 */
class SpecImplSymmCipher implements ImplSymmCipher
{
    /**
     * Default mode
     */
    public const DEFAULT_MODE = 'cbc';

    /**
     * @var string Algorithm type class (as in common standard)
     */
    protected string $algoTypeClass;
    /**
     * @var string Algorithm type class (as in OpenSSL's standard)
     */
    protected string $openSslAlgoTypeClass;
    /**
     * @var bool If multiple block size supported
     */
    protected bool $hasMultiBlockSize;
    /**
     * @var AlgorithmBlockSetup Current block size setup
     */
    protected AlgorithmBlockSetup $blockSetup;
    /**
     * @var string|null Current selected mode
     */
    protected ?string $mode;


    /**
     * Constructor
     * @param string $algoTypeClass
     * @param string $openSslAlgoTypeClass
     * @param bool $hasMultiBlockSize
     * @param AlgorithmBlockSetup $blockSetup
     * @param string|null $mode
     * @throws SafetyCommonException
     */
    public function __construct(string $algoTypeClass, string $openSslAlgoTypeClass, bool $hasMultiBlockSize, AlgorithmBlockSetup $blockSetup, ?string $mode)
    {
        $this->algoTypeClass = $algoTypeClass;
        $this->openSslAlgoTypeClass = $openSslAlgoTypeClass;
        $this->hasMultiBlockSize = $hasMultiBlockSize;
        $this->blockSetup = $blockSetup;

        if ($mode !== null && !array_key_exists($mode, $blockSetup->modes)) {
            throw new InvalidArgumentException('mode');
        }

        $this->mode = static::checkMode($mode ?? static::getDefaultMode($blockSetup), $blockSetup);
    }


    /**
     * @inheritDoc
     */
    public function getBlockNumBits() : int
    {
        return $this->blockSetup->blockNumBits;
    }


    /**
     * @inheritDoc
     */
    public function setMode(string $mode) : string
    {
        $this->mode = static::checkMode($mode, $this->blockSetup);
        return $mode;
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
        if (!SpecImplSymmAlgorithms::checkAlgorithmKeySize($this->openSslAlgoTypeClass, $this->blockSetup->blockNumBits, $keyNumBits)) {
            throw new InvalidArgumentException('key');
        }
    }


    /**
     * @inheritDoc
     */
    public function checkIv(string $iv) : void
    {
        $ivNumBits = $this->getIvNumBits();
        if ($ivNumBits === null) throw new UnsupportedException(_l('Cannot determine IV bit size'));
        if (($ivNumBits % 8) !== 0) throw new UnsupportedValueException($ivNumBits, _l('IV bit size'));

        $inNumBytes = floor($ivNumBits / 8);
        if (strlen($iv) != $inNumBytes) throw new InvalidArgumentException('iv');
    }


    /**
     * @inheritDoc
     */
    public function createCipher(string $key, ?string $iv, ?string $mode, ?Padding $padding) : SpecCipher
    {
        $openSslAlgoName = $this->getOpenSslAlgoName();

        return new SpecCipher($this, $openSslAlgoName, $key, $iv, $mode, $padding);
    }


    /**
     * OpenSSL algorithm name
     * @return string
     */
    protected function getOpenSslAlgoName() : string
    {
        $ret = $this->openSslAlgoTypeClass;
        if ($this->hasMultiBlockSize) $ret .= '-' . $this->getBlockNumBits();
        if ($this->mode !== null) $ret .= '-' . $this->mode;

        return $ret;
    }


    /**
     * Check that the mode is supported
     * @param string|null $mode
     * @param AlgorithmBlockSetup $blockSetup
     * @return string|null
     * @throws SafetyCommonException
     */
    protected static function checkMode(?string $mode, AlgorithmBlockSetup $blockSetup) : ?string
    {
        if ($mode === null) return null;

        if (!array_key_exists($mode, $blockSetup->modes)) throw new InvalidArgumentException('mode');
        return $mode;
    }


    /**
     * Get the default mode
     * @param AlgorithmBlockSetup $blockSetup
     * @return string|null
     */
    protected static function getDefaultMode(AlgorithmBlockSetup $blockSetup) : ?string
    {
        if (array_key_exists(static::DEFAULT_MODE, $blockSetup->modes)) return static::DEFAULT_MODE;
        if (count($blockSetup->modes) > 0) return iter_first($blockSetup->modes);
        return null;
    }
}