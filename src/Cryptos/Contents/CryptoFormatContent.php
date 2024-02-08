<?php

namespace Magpie\Cryptos\Contents;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Exceptions\GeneralCryptoException;
use Magpie\Exceptions\GeneralPersistenceException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\StreamReadFailureException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
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
     * Get all associated binary blocks (in DER format)
     * @return iterable<BinaryBlockContent>
     * @throws SafetyCommonException
     * @throws CryptoException
     * @throws PersistenceException
     * @throws StreamException
     */
    public final function getBinaryBlocks() : iterable
    {
        yield from $this->onGetBinaryBlocks();
    }


    /**
     * Get all associated binary blocks (in DER format)
     * @return iterable<BinaryBlockContent>
     * @throws SafetyCommonException
     * @throws CryptoException
     * @throws PersistenceException
     * @throws StreamException
     */
    protected function onGetBinaryBlocks() : iterable
    {
        _throwable(1) ?? throw new GeneralCryptoException();
        _throwable(2) ?? throw new GeneralPersistenceException();
        _throwable(2) ?? throw new StreamReadFailureException();

        throw new UnsupportedValueException($this, _l('binary blocks from crypto format content'));
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
     * @param self|BinaryDataProvidable|string $spec
     * @return self
     * @throws SafetyCommonException
     */
    public static function accept(self|BinaryDataProvidable|string $spec) : self
    {
        _throwable() ?? throw new UnsupportedException();

        if ($spec instanceof self) return $spec;

        if (is_string($spec)) $spec = SimpleBinaryContent::create($spec);

        return PemCryptoFormatContent::fromData($spec);
    }
}