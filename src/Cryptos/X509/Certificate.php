<?php

namespace Magpie\Cryptos\X509;

use Carbon\CarbonInterface;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PublicKey;
use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\CryptoObject;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Numerals;
use Magpie\Cryptos\Providers\CertificateImporter;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Packs\PackContext;
use Magpie\General\Sugars\Excepts;
use Magpie\Objects\BinaryData;

/**
 * A X.509 certificate
 */
abstract class Certificate extends CryptoObject implements TypeClassable
{
    /**
     * Certificate version
     * @return int
     */
    public abstract function getVersion() : int;


    /**
     * Certificate serial number
     * @return Numerals|null
     */
    public abstract function getSerialNumber() : ?Numerals;


    /**
     * Certificate name
     * @return Name|string
     */
    public abstract function getName() : Name|string;


    /**
     * Subject name
     * @return Name|string
     */
    public abstract function getSubject() : Name|string;


    /**
     * Issuer name
     * @return Name|string
     */
    public abstract function getIssuer() : Name|string;


    /**
     * Certificate validity starts (inclusive)
     * @return CarbonInterface
     */
    public abstract function getValidFrom() : CarbonInterface;


    /**
     * Certificate validity until (inclusive)
     * @return CarbonInterface
     */
    public abstract function getValidUntil() : CarbonInterface;


    /**
     * Subject alternative names
     * @return iterable<Name|string>
     */
    public abstract function getSubjectAltNames() : iterable;


    /**
     * Certificate's associated public key
     * @return PublicKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function getPublicKey() : PublicKey;


    /**
     * Certificate's fingerprint using given hashing algorithm
     * @param Hasher|string $hashAlgorithm
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function getFingerprint(Hasher|string $hashAlgorithm) : BinaryData
    {
        $hashTypeClass = $hashAlgorithm instanceof Hasher ? $hashAlgorithm->getTypeClass() : $hashAlgorithm;

        return $this->onGetFingerprint($hashTypeClass);
    }


    /**
     * Handling certificate's fingerprint using given hashing algorithm
     * @param string $hashTypeClass
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract function onGetFingerprint(string $hashTypeClass) : BinaryData;


    /**
     * Check if the fingerprint of current certificate is verified by the verifier (certificate or public key)
     * @param Certificate|PublicKey $verifier
     * @return bool
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function verifyUsing(Certificate|PublicKey $verifier) : bool;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->version = $this->getVersion();
        $ret->serialNumber = $this->getSerialNumber();
        $ret->name = $this->getName();
        $ret->subject = $this->getSubject();
        $ret->issuer = $this->getIssuer();
        $ret->validFrom = $this->getValidFrom();
        $ret->validUntil = $this->getValidUntil();
        $ret->subjectAltNames = $this->getSubjectAltNames();
    }


    /**
     * @inheritDoc
     */
    protected static function onImport(CryptoFormatContent $source, ?Context $context) : static
    {
        if ($context === null) {
            foreach (CertificateImporter::getTryImporterLists() as $tryImporter) {
                $ret = Excepts::noThrow(fn () => $tryImporter->import($source, static::class));
                if ($ret instanceof static) return $ret;
            }
        }

        $context = $context ?? CertificateImporter::getDefaultContext();

        $className = ClassFactory::resolve($context->getTypeClass(), self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificImport($source);
    }


    /**
     * Specifically parse from certificate source
     * @param CryptoFormatContent $source
     * @return static
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    protected abstract static function specificImport(CryptoFormatContent $source) : static;
}