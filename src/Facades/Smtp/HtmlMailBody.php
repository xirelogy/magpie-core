<?php

namespace Magpie\Facades\Smtp;

/**
 * Mail body in HTML format
 */
class HtmlMailBody extends MailBody
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