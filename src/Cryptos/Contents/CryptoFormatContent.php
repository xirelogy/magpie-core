<?php

namespace Magpie\Cryptos\Contents;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Contents\SimpleBinaryContent;
use Magpie\General\Packs\PackContext;
use Magpie\Objects\CommonObject;

/**
 * Content with format to store cryptographic related data
 */
abstract class CryptoFormatContent extends CommonObject implements TypeClassable
{
    /**
     * @var BinaryDataProvidable associated data
     */
    public BinaryDataProvidable $data;
    /**
     * @var string|null Password to access the content
     */
    public ?string $password;


    /**
     * Constructor
     * @param BinaryDataProvidable $data
     * @param string|null $password
     */
    protected function __construct(BinaryDataProvidable $data, ?string $password = null)
    {
        $this->data = $data;
        $this->password = $password;
    }


    /**
     * Specify content password
     * @param string|null $password
     * @return $this
     */
    public function withPassword(?string $password) : static
    {
        $this->password = $password;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->typeClass = static::getTypeClass();
    }


    /**
     * Accept variant of crypto related content, or it's source content
     * @param self|CryptoContent|BinaryDataProvidable|string $spec
     * @return self
     * @throws SafetyCommonException
     */
    public static function accept(self|CryptoContent|BinaryDataProvidable|string $spec) : self
    {
        if ($spec instanceof CryptoContent) $spec = $spec->upgradeToCryptoFormatContent();
        if ($spec instanceof self) return $spec;

        if (is_string($spec)) $spec = SimpleBinaryContent::create($spec);

        return PemCryptoFormatContent::fromData($spec);
    }
}