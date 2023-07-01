<?php

namespace Magpie\Cryptos\Providers\OpenSsl;

use Magpie\Cryptos\Concepts\TryContentHandleable;
use Magpie\Cryptos\Contents\BinaryBlockContent;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Contents\Pkcs12CryptoFormatContent;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Encodings\Pem;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Exceptions\DecryptionFailedException;
use Magpie\Cryptos\Exceptions\PasswordRequiredCryptoException;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ErrorHandling;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Symm\SpecSymmetricCipherAlgorithms;
use Magpie\Cryptos\Providers\Pkcs12CryptoFormatContentHandler;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\BinaryData;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * OpenSSL specific context
 */
#[FactoryTypeClass(SpecContext::TYPECLASS, Context::class)]
class SpecContext extends Context
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'openssl';



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
    protected static function specificInitialize() : static
    {
        return new static();
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        $registrar
            ->provides(Context::class)
            ;

        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        parent::systemBoot($context);

        ClassFactory::includeDirectory(__DIR__);
        ClassFactory::includeDirectory(__DIR__ . '/Impls');

        if (extension_loaded('openssl')) {
            SpecSymmetricCipherAlgorithms::register();
            Pkcs12CryptoFormatContentHandler::registerTryImporter(static::createPkcs12ContentHandler());
        }

        ClassFactory::setDefaultTypeClassCheck(Context::class, function () : ?string {
            if (!extension_loaded('openssl')) return null;
            return static::TYPECLASS;
        });
    }


    /**
     * Create an instance of PKCS#12 content handler
     * @return TryContentHandleable
     */
    protected static function createPkcs12ContentHandler() : TryContentHandleable
    {
        return new class implements TryContentHandleable {
            /**
             * Try to decode PKCS12 blocks
             * @param string $data
             * @param string $password
             * @return iterable<string>
             * @throws CryptoException
             */
            protected static function decodePkcs12(string $data, string $password) : iterable
            {
                ErrorHandling::clearErrors();

                $ret = openssl_pkcs12_read($data, $blocks, $password);
                if (!$ret) {
                    $ex = ErrorHandling::captureError();
                    if (str_starts_with($ex->getMessage(), 'error:11800071:')) {
                        throw new DecryptionFailedException();
                    }
                    throw $ex;
                }

                return $blocks;
            }


            /**
             * @inheritDoc
             */
            public function getBinaryBlocks(CryptoFormatContent $content) : ?iterable
            {
                if (!$content instanceof Pkcs12CryptoFormatContent) return null;

                $data = $content->data->getData();
                $password = $content->password;

                if ($password === null) throw new PasswordRequiredCryptoException();

                $retBinaryBlocks = [];
                foreach (static::decodePkcs12($data, $password) as $block) {
                    if (!Pem::hasContentType($block)) continue;
                    foreach (Pem::decode($block) as $content) {
                        $retBinaryBlocks[] = new BinaryBlockContent($content->type, BinaryData::fromBase64($content->data));
                    }
                }

                return $retBinaryBlocks;
            }
        };
    }
}