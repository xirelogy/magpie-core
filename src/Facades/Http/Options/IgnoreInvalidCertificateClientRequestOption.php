<?php

namespace Magpie\Facades\Http\Options;

use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\General\Traits\StaticCreatable;

/**
 * Ignore invalid certificates
 */
class IgnoreInvalidCertificateClientRequestOption extends HttpClientRequestOption
{
    use StaticCreatable;

    /**
     * Current type class
     */
    public const TYPECLASS = 'ignore-invalid-cert';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}