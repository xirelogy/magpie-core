<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Magpie\General\Contexts\Scoped;

/**
 * Apply specific umask in scope
 */
class LocalUmaskScoped extends Scoped
{
    /**
     * @var int Old umask before scope
     */
    protected readonly int $oldUmask;


    /**
     * Constructor
     * @param int $umask
     */
    protected function __construct(int $umask)
    {
        $this->oldUmask = umask($umask);
    }


    /**
     * @inheritDoc
     */
    protected function onRelease() : void
    {
        umask($this->oldUmask);
    }


    /**
     * Create a new scope guard
     * @param int $umask
     * @return static
     */
    public static function create(int $umask) : static
    {
        return new static($umask);
    }
}