<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\General\Traits\StaticClass;

/**
 * Specific OpenSSL Elliptic Curve's curve name mappings
 * @internal
 */
class SpecImplEcCurveMap
{
    use StaticClass;

    /**
     * @var array<string, string>|null Map from name to OID
     */
    protected static ?array $name2oid = null;
    /**
     * @var array<string, string>|null Map from OID to name
     */
    protected static ?array $oid2name = null;


    /**
     * Resolve OID from name
     * @param string $name
     * @return string|null
     */
    public static function resolveOid(string $name) : ?string
    {
        $map = static::getName2OidMap();
        return $map[$name] ?? null;
    }


    /**
     * Resolve name from OID
     * @param string $oid
     * @return string|null
     */
    public static function resolveName(string $oid) : ?string
    {
        $map = static::getOid2NameMap();
        return $map[$oid] ?? null;
    }


    /**
     * The name to OID map
     * @return array<string, string>
     */
    protected static final function getName2OidMap() : array
    {
        if (static::$name2oid === null) {
            static::populateMaps();
        }

        return static::$name2oid;
    }


    /**
     * The OID to name map
     * @return array<string, string>
     */
    protected static final function getOid2NameMap() : array
    {
        if (static::$oid2name === null) {
            static::populateMaps();
        }

        return static::$oid2name;
    }


    private static function populateMaps() : void
    {
        static::$name2oid = [];
        static::$oid2name = [];

        foreach (static::mapOids() as $name => $oid) {
            if (!array_key_exists($name, static::$name2oid)) static::$name2oid[$name] = $oid;
            if (!array_key_exists($oid, static::$oid2name)) static::$oid2name[$oid] = $name;
        }
    }


    /**
     * Provide list of supported curve names to their OIDs
     * @return iterable<string, string>
     */
    protected static function mapOids() : iterable
    {
        yield 'secp112r1' => '1.3.132.0.6';
        yield 'secp112r2' => '1.3.132.0.7';
        yield 'secp128r1' => '1.3.132.0.28';
        yield 'secp128r2' => '1.3.132.0.29';
        yield 'secp160k1' => '1.3.132.0.9';     // ansip160k1
        yield 'secp160r1' => '1.3.132.0.8';     // ansip160r1
        yield 'secp160r2' => '1.3.132.0.30';    // ansip160r2
        yield 'secp192k1' => '1.3.132.0.31';    // ansip192k1
        yield 'secp224k1' => '1.3.132.0.32';    // ansip224k1
        yield 'secp224r1' => '1.3.132.0.33';    // ansip224r1
        yield 'secp256k1' => '1.3.132.0.10';    // ansip256k1
        yield 'secp384r1' => '1.3.132.0.34';    // ansip384r1, P-384
        yield 'secp521r1' => '1.3.132.0.35';    // ansip521r1, P-521

        yield 'prime192v1' => '1.2.840.10045.3.1.1';    // secp192r1, P-192
        yield 'prime192v2' => '1.2.840.10045.3.1.2';
        yield 'prime192v3' => '1.2.840.10045.3.1.3';
        yield 'prime239v1' => '1.2.840.10045.3.1.4';
        yield 'prime239v2' => '1.2.840.10045.3.1.5';
        yield 'prime239v3' => '1.2.840.10045.3.1.6';
        yield 'prime256v1' => '1.2.840.10045.3.1.7';    // secp256r1, P-256

        yield 'wap-wsg-idm-ecid-wtls1' => '2.23.43.1.4.1';
        // THERE IS NO wap-wsg-idm-ecid-wtls2
        yield 'wap-wsg-idm-ecid-wtls3' => '2.23.43.1.4.3';
        yield 'wap-wsg-idm-ecid-wtls4' => '2.23.43.1.4.4';
        yield 'wap-wsg-idm-ecid-wtls5' => '2.23.43.1.4.5';
        yield 'wap-wsg-idm-ecid-wtls6' => '2.23.43.1.4.6';
        yield 'wap-wsg-idm-ecid-wtls7' => '2.23.43.1.4.7';
        yield 'wap-wsg-idm-ecid-wtls8' => '2.23.43.1.4.8';
        yield 'wap-wsg-idm-ecid-wtls9' => '2.23.43.1.4.9';
        yield 'wap-wsg-idm-ecid-wtls10' => '2.23.43.1.4.10';
        yield 'wap-wsg-idm-ecid-wtls11' => '2.23.43.1.4.11';
        yield 'wap-wsg-idm-ecid-wtls12' => '2.23.43.1.4.12';

        yield 'brainpoolP160r1' => '1.3.36.3.3.2.8.1.1.1';
        yield 'brainpoolP160t1' => '1.3.36.3.3.2.8.1.1.2';
        yield 'brainpoolP192r1' => '1.3.36.3.3.2.8.1.1.3';
        yield 'brainpoolP192t1' => '1.3.36.3.3.2.8.1.1.4';
        yield 'brainpoolP224r1' => '1.3.36.3.3.2.8.1.1.5';
        yield 'brainpoolP224t1' => '1.3.36.3.3.2.8.1.1.6';
        yield 'brainpoolP256r1' => '1.3.36.3.3.2.8.1.1.7';
        yield 'brainpoolP256t1' => '1.3.36.3.3.2.8.1.1.8';
        yield 'brainpoolP320r1' => '1.3.36.3.3.2.8.1.1.9';
        yield 'brainpoolP320t1' => '1.3.36.3.3.2.8.1.1.10';
        yield 'brainpoolP384r1' => '1.3.36.3.3.2.8.1.1.11';
        yield 'brainpoolP384t1' => '1.3.36.3.3.2.8.1.1.12';
        yield 'brainpoolP512r1' => '1.3.36.3.3.2.8.1.1.13';
        yield 'brainpoolP512t1' => '1.3.36.3.3.2.8.1.1.14';

        yield 'SM2' => '1.2.156.10197.1.301';
    }
}