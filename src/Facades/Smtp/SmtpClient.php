<?php

namespace Magpie\Facades\Smtp;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\LogContainable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Concepts\SystemBootable;

/**
 * A SMTP client
 */
abstract class SmtpClient implements TypeClassable, LogContainable, SystemBootable
{
    /**
     * Create a new outgoing mail (to-be sent)
     * @return SmtpMail
     * @throws SafetyCommonException
     */
    public abstract function createMail() : SmtpMail;


    /**
     * Initialize a client
     * @param SmtpClientConfig $config
     * @param string|null $typeClass
     * @return static
     * @throws SafetyCommonException
     */
    public static function initialize(SmtpClientConfig $config, ?string $typeClass = null) : static
    {
        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitialize($config);
    }


    /**
     * Initialize a client specifically for this type of adaptation
     * @param SmtpClientConfig $config
     * @return static
     * @throws SafetyCommonException
     */
    protected abstract static function specificInitialize(SmtpClientConfig $config) : static;
}