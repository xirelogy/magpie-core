<?php

namespace Magpie\Queues\Providers\Generators;

use Magpie\Facades\Random;
use Magpie\General\Randoms\RandomCharset;
use Magpie\Queues\Concepts\QueueIdentityProvidable;
use Magpie\System\Kernel\Kernel;

/**
 * Provide queue identity using randomly generated string
 */
class RandomQueueIdentityProvider implements QueueIdentityProvidable
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'random';
    /**
     * Default output length
     */
    public const DEFAULT_LENGTH = 32;

    /**
     * @var int Output length
     */
    public readonly int $length;


    /**
     * Constructor
     * @param int $length
     */
    public function __construct(int $length = self::DEFAULT_LENGTH)
    {
        $this->length = $length;
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
    public function generateId() : string|int
    {
        return Random::string($this->length, RandomCharset::LOWER_ALPHANUM);
    }


    /**
     * @inheritDoc
     */
    public function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(QueueIdentityProvidable::class, $this);
    }
}