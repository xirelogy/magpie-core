<?php

namespace Magpie\Facades\Smtp;

/**
 * SMTP recipient type
 */
enum SmtpRecipientType : string
{
    /**
     * To address
     */
    case TO = 'to';
    /**
     * Carbon copy address
     */
    case CC = 'cc';
    /**
     * Blind carbon copy address
     */
    case BCC = 'bcc';
}