<?php

namespace Magpie\Cryptos\Providers;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\CipherSetup;
use Magpie\Cryptos\Concepts\SymmetricCipherAlgorithmInitializable;
use Magpie\Cryptos\Concepts\SymmetricCipherAlgorithmProvidable;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Exceptions\GeneralCryptoException;
use Magpie\Cryptos\Impls\ImplProvidedCipherSetup;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Traits\StaticClass;
use Throwable;

/**
 * Collection of all supported symmetric cipher algorithms
 */
class SymmetricCipherAlgorithms
{
    use StaticClass;

    /**
     * @var array<SymmetricCipherAlgorithmInitializable> All initializers
     */
    protected static array $initializers = [];
    /**
     * @var bool If initialized
     */
    protected static bool $isInitialized = false;
    /**
     * @var array<string, array<int, array<SymmetricCipherAlgorithmProvidable>>>
     * Map of algorithm type classes to their specific providers (weighted)
     */
    protected static array $algorithms = [];


    /**
     * Register an initializer
     * @param SymmetricCipherAlgorithmInitializable $initializer
     * @return void
     */
    public static function registerInitializer(SymmetricCipherAlgorithmInitializable $initializer) : void
    {
        static::$initializers[] = $initializer;
    }


    /**
     * Register a provider
     * @param SymmetricCipherAlgorithmProvidable $provider
     * @param int $weight
     * @return void
     */
    public static function registerProvider(SymmetricCipherAlgorithmProvidable $provider, int $weight = 10) : void
    {
        $algoTypeClass = $provider->getAlgoTypeClass();

        $weightedAlgorithms = static::$algorithms[$algoTypeClass] ?? [];
        $providers = $weightedAlgorithms[$weight] ?? [];
        $providers[] = $provider;

        $weightedAlgorithms[$weight] = $providers;
        ksort($weightedAlgorithms);
        static::$algorithms[$algoTypeClass] = $weightedAlgorithms;
    }


    /**
     * Initialize a cipher setup according to rules and default providers
     * @param string $algoTypeClass
     * @param int|null $blockNumBits
     * @param string|null $mode
     * @param Context|null $context
     * @return CipherSetup
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function _initializeCipherSetup(string $algoTypeClass, ?int $blockNumBits, ?string $mode, ?Context $context) : CipherSetup
    {
        static::ensureInitialized();

        if (!array_key_exists($algoTypeClass, static::$algorithms)) {
            throw new UnsupportedValueException($algoTypeClass, _l('symmetric cipher algorithm'));
        }

        /** @var SafetyCommonException|CryptoException|null $lastEx */
        $lastEx = null;

        $contextTypeClass = ($context !== null) ? $context::getTypeClass() : null;
        $weightedAlgorithms = static::$algorithms[$algoTypeClass];
        foreach ($weightedAlgorithms as $providers) {
            foreach ($providers as $provider) {
                try {
                    if ($contextTypeClass !== null && $contextTypeClass != $provider::getTypeClass()) continue;
                    $service = $provider->createCipherSetupService($blockNumBits, $mode);
                    if ($service !== null) return new ImplProvidedCipherSetup($algoTypeClass, $service);
                } catch (SafetyCommonException|CryptoException $ex) {
                    $lastEx = $lastEx ?? $ex;
                }
            }
        }

        // Throws a proper exception
        throw ($lastEx ?? new UnsupportedValueException($algoTypeClass, _l('symmetric cipher algorithm')));
    }


    /**
     * Ensure that initializers are performed
     * @return void
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected static function ensureInitialized() : void
    {
        if (static::$isInitialized) return;
        static::$isInitialized = true;

        try {
            foreach (static::$initializers as $initializer) {
                $initializer->initialize();
            }
        } catch (SafetyCommonException|CryptoException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new GeneralCryptoException(previous: $ex);
        }
    }
}