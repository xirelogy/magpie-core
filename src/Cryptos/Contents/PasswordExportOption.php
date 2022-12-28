<?php

namespace Magpie\Cryptos\Contents;

/**
 * Password option during exporting
 */
class PasswordExportOption extends ExportOption
{
    /**
     * @var string Selected password
     */
    public readonly string $password;


    /**
     * Constructor
     * @param string $password
     */
    protected function __construct(string $password)
    {
        $this->password = $password;
    }


    /**
     * Create password options
     * @param string $password
     * @return static
     */
    public static function create(string $password) : static
    {
        return new static($password);
    }
}