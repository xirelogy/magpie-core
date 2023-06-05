<?php

namespace Magpie\Cryptos\Providers\OpenSsl;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PublicKey;
use Magpie\Cryptos\Algorithms\Hashes\CommonHashTypeClass;
use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Contents\ExportOption;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Numerals;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm\SpecImplAsymmKey;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ErrorHandling;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ImportExport;
use Magpie\Cryptos\Providers\OpenSsl\Impls\PemEncoding;
use Magpie\Cryptos\Providers\OpenSsl\Impls\TextUtils;
use Magpie\Cryptos\X509\Certificate;
use Magpie\Cryptos\X509\Name;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Objects\BinaryData;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;

/**
 * Specific OpenSSL certificate instance
 */
#[FactoryTypeClass(SpecCertificate::TYPECLASS, Certificate::class)]
class SpecCertificate extends Certificate
{
    /**
     * Current type class
     */
    public const TYPECLASS = SpecContext::TYPECLASS;

    /**
     * @var OpenSSLCertificate Underlying object
     */
    protected OpenSSLCertificate $inCert;
    /**
     * @var array Certificate details as queried
     */
    protected array $inDetails;


    /**
     * Constructor
     * @param OpenSSLCertificate $inCert
     * @param array $certDetails
     */
    protected function __construct(OpenSSLCertificate $inCert, array $certDetails)
    {
        parent::__construct();

        $this->inCert = $inCert;
        $this->inDetails = $certDetails;
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
    public function getVersion() : int
    {
        return $this->inDetails['version'] + 1;
    }


    /**
     * @inheritDoc
     */
    public function getSerialNumber() : ?Numerals
    {
        return Numerals::fromHex($this->inDetails['serialNumberHex'] ?? '');
    }


    /**
     * @inheritDoc
     */
    public function getName() : Name|string
    {
        $normalized = TextUtils::normalize($this->inDetails['name'] ?? '');
        return Name::tryFromText($normalized) ?? $normalized;
    }


    /**
     * @inheritDoc
     */
    public function getSubject() : Name
    {
        return Name::fromAttributesMap($this->inDetails['subject'] ?? []);
    }


    /**
     * @inheritDoc
     */
    public function getIssuer() : Name
    {
        return Name::fromAttributesMap($this->inDetails['issuer'] ?? []);
    }


    /**
     * @inheritDoc
     */
    public function getValidFrom() : CarbonInterface
    {
        return Carbon::createFromTimestamp($this->inDetails['validFrom_time_t']);
    }


    /**
     * @inheritDoc
     */
    public function getValidUntil() : CarbonInterface
    {
        return Carbon::createFromTimestamp($this->inDetails['validTo_time_t']);
    }


    /**
     * @inheritDoc
     */
    public function getSubjectAltNames() : iterable
    {
        if (!array_key_exists('extensions', $this->inDetails)) return;
        if (!array_key_exists('subjectAltName', $this->inDetails['extensions'])) return;

        foreach (explode(',', $this->inDetails['extensions']['subjectAltName']) as $altName) {
            yield trim($altName);
        }
    }


    /**
     * @inheritDoc
     */
    public function getPublicKey() : PublicKey
    {
        $inKey = ErrorHandling::execute(fn () => openssl_pkey_get_public($this->inCert));

        $implKey = SpecImplAsymmKey::initializeFromKey($inKey);

        return PublicKey::_fromRaw($implKey->getAlgoTypeClass(), $implKey);
    }


    /**
     * @inheritDoc
     */
    protected function onGetFingerprint(string $hashTypeClass) : BinaryData
    {
        $inHashType = static::translateHash($hashTypeClass);

        $data = ErrorHandling::execute(fn () => openssl_x509_fingerprint($this->inCert, $inHashType, true));

        return BinaryData::fromBinary($data);
    }


    /**
     * @inheritDoc
     */
    public function verifyUsing(Certificate|PublicKey $verifier) : bool
    {
        try {
            $inVerifier = static::getInVerifier($verifier);
        } catch (PersistenceException|StreamException $ex) {
            throw new OperationFailedException(previous: $ex);
        }

        $ret = ErrorHandling::execute(fn () => openssl_x509_verify($this->inCert, $inVerifier));
        if ($ret === -1) throw ErrorHandling::captureError();

        return $ret === 1;
    }


    /**
     * Get the internal verifier
     * @param Certificate|PublicKey $verifier
     * @return OpenSSLCertificate|OpenSSLAsymmetricKey
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     * @noinspection PhpConditionAlreadyCheckedInspection
     */
    protected static function getInVerifier(Certificate|PublicKey $verifier) : OpenSSLCertificate|OpenSSLAsymmetricKey
    {
        if ($verifier instanceof SpecCertificate) {
            return $verifier->inCert;
        } elseif ($verifier instanceof Certificate) {
            return SpecCertificate::specificImport($verifier->export())->inCert;
        } else if ($verifier instanceof PublicKey) {
            return openssl_pkey_get_public($verifier->export());
        } else {
            throw new UnsupportedValueException($verifier, _l('certificate verifier'));
        }
    }


    /**
     * @inheritDoc
     */
    public function export(ExportOption ...$options) : string
    {
        [$exportOption, $exportPassword] = ImportExport::checkExportOptions($options, false);
        _used($exportPassword);

        $output = '';
        ErrorHandling::execute(function () use(&$output) {
            return openssl_x509_export($this->inCert, $output);
        });

        return PemEncoding::reformat($output, 'CERTIFICATE', $exportOption);
    }


    /**
     * @inheritDoc
     */
    protected static function specificImport(CryptoContent|BinaryDataProvidable|string $source) : static
    {
        [$data] = ImportExport::readAsPem($source, false);
        $data = PemEncoding::reformat($data, 'CERTIFICATE');

        $inCert = ErrorHandling::execute(fn () => openssl_x509_read($data));

        return static::_fromRaw($inCert);
    }


    /**
     * Construct from raw data
     * @param OpenSSLCertificate $inCert
     * @return static
     * @throws CryptoException
     * @internal
     */
    public static function _fromRaw(OpenSSLCertificate $inCert) : static
    {
        $certDetails = ErrorHandling::execute(fn () => openssl_x509_parse($inCert));

        return new static($inCert, $certDetails);
    }


    /**
     * Translate signature algorithm
     * @param string $typeClass
     * @return string
     * @throws SafetyCommonException
     */
    protected static function translateHash(string $typeClass) : string
    {
        return match ($typeClass) {
            CommonHashTypeClass::SHA1 => 'sha1',
            CommonHashTypeClass::SHA224 => 'sha224',
            CommonHashTypeClass::SHA256 => 'sha256',
            CommonHashTypeClass::SHA384 => 'sha384',
            CommonHashTypeClass::SHA512 => 'sha512',
            CommonHashTypeClass::MD5 => 'md5',
            default => throw new UnsupportedValueException($typeClass, _l('certificate hash algorithm')),
        };
    }
}