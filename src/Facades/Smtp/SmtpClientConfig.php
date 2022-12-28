<?php

namespace Magpie\Facades\Smtp;

/**
 * Configuration for SMTP client
 */
class SmtpClientConfig
{
    /**
     * The default port for redis
     */
    public const DEFAULT_PORT = 25;

    /**
     * @var string Host name
     */
    public string $host;
    /**
     * @var SmtpSecurity|null SMTP Security
     */
    public ?SmtpSecurity $security;
    /**
     * @var int|null Specific port number to connect to
     */
    public ?int $port;
    /**
     * @var SmtpAuthentication|null Specific authentication
     */
    public ?SmtpAuthentication $authentication = null;


    /**
     * Constructor
     * @param string $host
     * @param SmtpSecurity|null $security
     * @param int|null $port
     */
    public function __construct(string $host, ?SmtpSecurity $security = null, ?int $port = null)
    {
        $this->host = $host;
        $this->security = $security;
        $this->port = $port;
    }


    /**
     * Specify the authentication
     * @param SmtpAuthentication $authentication
     * @return $this
     */
    public function withAuth(SmtpAuthentication $authentication) : static
    {
        $this->authentication = $authentication;
        return $this;
    }


    /**
     * Has authentication
     * @return bool
     */
    public function hasAuth() : bool
    {
        return $this->authentication !== null;
    }
}