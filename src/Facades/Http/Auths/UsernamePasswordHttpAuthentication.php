<?php

namespace Magpie\Facades\Http\Auths;

use Magpie\Facades\Http\HttpAuthentication;
use Magpie\Objects\BasicUsernamePassword;

/**
 * HTTP authentication based on username/password
 */
abstract class UsernamePasswordHttpAuthentication extends HttpAuthentication
{
    /**
     * @var BasicUsernamePassword Associated credentials
     */
    public BasicUsernamePassword $credentials;


    /**
     * Constructor
     * @param BasicUsernamePassword $credentials
     */
    protected function __construct(BasicUsernamePassword $credentials)
    {
        $this->credentials = $credentials;
    }


    /**
     * Create from given credentials
     * @param BasicUsernamePassword $credentials
     * @return static
     */
    public static function fromCredentials(BasicUsernamePassword $credentials) : static
    {
        return new static($credentials);
    }


    /**
     * Create from given username password
     * @param string $username
     * @param string $password
     * @return static
     */
    public static function fromUsernamePassword(string $username, string $password) : static
    {
        return new static(new BasicUsernamePassword($username, $password));
    }
}