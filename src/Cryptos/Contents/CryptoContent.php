<?php

/** @noinspection PhpDeprecationInspection */

namespace Magpie\Cryptos\Contents;

use Magpie\Cryptos\ContentEncoding;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Contents\SimpleBinaryContent;

/**
 * May store content related to cryptography
 * @deprecated CryptoContent had been replaced by CryptoFormatContent
 */
class CryptoContent
{
    /**
     * @var BinaryDataProvidable Source providing the crypto content
     */
    public BinaryDataProvidable $source;
    /**
     * @var ContentEncoding|null Hint on the content encoding
     */
    public ?ContentEncoding $encoding = null;
    /**
     * @var string|null Password to access the content
     */
    public ?string $password = null;


    /**
     * Constructor
     * @param BinaryDataProvidable $source
     */
    protected function __construct(BinaryDataProvidable $source)
    {
        $this->source = $source;
    }


    /**
     * Specify the encoding
     * @param ContentEncoding $encoding
     * @return $this
     */
    public function withEncoding(ContentEncoding $encoding) : static
    {
        $this->encoding = $encoding;
        return $this;
    }


    /**
     * Specify the password
     * @param string $password
     * @return $this
     */
    public function withPassword(string $password) : static
    {
        $this->password = $password;
        return $this;
    }


    /**
     * Convert to CryptoFormatContent to support upgrading from CryptoContent
     * @return CryptoFormatContent|BinaryDataProvidable|string
     * @throws SafetyCommonException
     */
    public function upgradeToCryptoFormatContent() : CryptoFormatContent|BinaryDataProvidable|string
    {
        if ($this->encoding === null) {
            // When encoding not available, likely assumed to be PEM
            if ($this->password === null) return $this->source;

            return PemCryptoFormatContent::fromData($this->source, $this->password);
        }

        return match ($this->encoding) {
            ContentEncoding::PEM => PemCryptoFormatContent::fromData($this->source, $this->password),
            ContentEncoding::DER => DerCryptoFormatContent::fromData($this->source, $this->password),
            ContentEncoding::P12 => Pkcs12CryptoFormatContent::fromData($this->source, $this->password),
            default => throw new UnsupportedValueException($this->encoding),
        };
    }


    /**
     * Create crypto related content from given source
     * @param BinaryDataProvidable $source
     * @return static
     */
    public static function from(BinaryDataProvidable $source) : static
    {
        return new static($source);
    }


    /**
     * Accept variant of crypto related content, or it's source content
     * @param CryptoContent|BinaryDataProvidable|string $spec
     * @return static
     */
    public static function accept(CryptoContent|BinaryDataProvidable|string $spec) : static
    {
        if ($spec instanceof CryptoContent) return $spec;

        if (is_string($spec)) $spec = SimpleBinaryContent::create($spec);

        return static::from($spec);
    }
}