<?php

namespace Magpie\Facades\Smtp;

/**
 * SMTP connection security
 */
enum SmtpSecurity : string
{
    /**
     * No encryption
     */
    case NONE = 'none';
    /**
     * SSL encryption
     */
    case SSL = 'ssl';
    /**
     * TLS encryption (aka StartTLS)
     */
    case TLS = 'tls';
}