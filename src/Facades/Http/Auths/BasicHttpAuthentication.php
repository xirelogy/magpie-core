<?php

namespace Magpie\Facades\Http\Auths;

/**
 * Basic HTTP authentication
 */
class BasicHttpAuthentication extends UsernamePasswordHttpAuthentication
{
    public const TYPECLASS = 'basic';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}