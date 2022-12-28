<?php

namespace Magpie\Objects;

/**
 * Basic username/password
 */
class BasicUsernamePassword
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