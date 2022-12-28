<?php

namespace Magpie\Facades\Smtp;

/**
 * Basic (username/password) SMTP authentication
 */
class SmtpBasicAuthentication extends SmtpAuthentication
{
    /**
     * @var string Username
     */
    public string $username;
    /**
     * @var string Password
     */
    public string $password;


    /**
     * Constructor
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}