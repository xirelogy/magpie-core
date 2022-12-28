<?php

namespace Magpie\Facades\Smtp;

/**
 * Mail body in plaintext format
 */
class PlaintextMailBody extends MailBody
{
    /**
     * @var string Mail body content
     */
    public string $body;


    /**
     * Constructor
     * @param string $body
     */
    public function __construct(string $body)
    {
        $this->body = $body;
    }
}