<?php

namespace Magpie\Cryptos\Contents;

use Magpie\Cryptos\ContentEncoding;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Contents\SimpleBinaryContent;

/**
 * May store content related to cryptography
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