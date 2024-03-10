<?php

namespace Magpie\Facades\Smtp;

/**
 * Representation of already sent mail
 */
abstract class SmtpSentMail
{
    /**
     * Message ID
     * @return string
     */
    public abstract function getMessageId() : string;


    /**
     * Export the entire sent email as MIME message
     * @return string
     */
    public abstract function exportAsMimeMessage() : string;
}