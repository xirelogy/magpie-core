<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\Cipher;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\CipherSetup;
use Magpie\Cryptos\Concepts\SymmetricCipherSetupServiceable;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;

/**
 * A cipher setup from specific provider
 * @internal
 */
class ImplProvidedCipherSetup extends CipherSetup
{
    /**
     * @var SymmetricCipherSetupServiceable Service interface
     */
    protected readonly SymmetricCipherSetupServiceable $service;


    /**
     * Constructor
     * @param string $algoTypeClass
     * @param SymmetricCipherSetupServiceable $service
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function __construct(string $algoTypeClass, SymmetricCipherSetupServiceable $service)
    {
        parent::__construct($algoTypeClass, static::createImplFromService($service));

        $this->service = $service;
    }


    /**
     * @inheritDoc
     */
    public function create() : Cipher
    {
        if ($this->key === null) throw new MissingArgumentException('key');

        return $this->service->createCipher($this->key, $this->iv, $this->padding);
    }


    /**
     * Translate service interface to ImplSymmCipher
     * @param SymmetricCipherSetupServiceable $service
     * @return ImplSymmCipher
     */
    protected static final function createImplFromService(SymmetricCipherSetupServiceable $service) : ImplSymmCipher
    {
        return new class($service) implements ImplSymmCipher {
            /**
             * Constructor
             * @param SymmetricCipherSetupServiceable $service
             */
            public function __construct(
                protected SymmetricCipherSetupServiceable $service,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function getBlockNumBits() : int
            {
                return $this->service->getBlockNumBits();
            }


            /**
             * @inheritDoc
             */
            public function setMode(string $mode) : string
            {
                return $this->service->setMode($mode);
            }


            /**
             * @inheritDoc
             */
            public function getIvNumBits() : ?int
            {
                return $this->service->getIvNumBits();
            }


            /**
             * @inheritDoc
             */
            public function checkKey(string $key) : void
            {
                $this->service->checkKey($key);
            }


            /**
             * @inheritDoc
             */
            public function checkIv(string $iv) : void
            {
                $this->service->checkIv($iv);
            }


            /**
             * @inheritDoc
             */
            public function createCipher(string $key, ?string $iv, ?string $mode, ?Padding $padding) : Cipher
            {
                // Cipher cannot be created from here
                throw new UnexpectedException();
            }
        };
    }
}