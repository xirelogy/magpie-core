<?php

namespace Magpie\HttpServer;

/**
 * Request state associated with a HTTP request
 */
class RequestState
{
    /**
     * @var array<string, mixed> State map
     */
    protected array $variables = [];


    /**
     * Constructor
     */
    public function __construct()
    {

    }


    /**
     * If variable exist
     * @param string $key
     * @return bool
     */
    public function hasVariable(string $key) : bool
    {
        return array_key_exists($key, $this->variables);
    }


    /**
     * Get variable, or fallback to default if not exist
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getVariable(string $key, mixed $default = null) : mixed
    {
        return $this->variables[$key] ?? $default;
    }


    /**
     * Set variable in the state
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setVariable(string $key, mixed $value = true) : void
    {
        $this->variables[$key] = $value;
    }
}