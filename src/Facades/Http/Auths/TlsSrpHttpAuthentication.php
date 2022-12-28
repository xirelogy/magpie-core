<?php

namespace Magpie\Facades\Http\Auths;

/**
 * TLS-SRP (Secure Remote Password) authentication
 */
class TlsSrpHttpAuthentication extends UsernamePasswordHttpAuthentication
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'tls-srp';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}