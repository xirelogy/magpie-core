<?php

namespace Magpie\Facades\Mime\Detectors;

use Magpie\Facades\Mime\MimeDetectable;
use Magpie\System\Kernel\Kernel;

abstract class BaseMimeDetector implements MimeDetectable
{
    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    public final function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(MimeDetectable::class, $this);
    }
}