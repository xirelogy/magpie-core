<?php

namespace Magpie\Facades\Redis;

/**
 * A LUA script to be executed in Redis context
 */
class RedisLuaScript
{
    /**
     * @var string The script's content
     */
    public readonly string $content;
    /**
     * @var int Number of keys involved
     */
    public readonly int $numberOfKeys;


    /**
     * Constructor
     * @param string $content
     * @param int $numberOfKeys
     */
    protected function __construct(string $content, int $numberOfKeys)
    {
        $this->content = $content;
        $this->numberOfKeys = $numberOfKeys;
    }


    /**
     * Create a script
     * @param string $content The script's content
     * @param int $numberOfKeys Number of keys involved
     * @return static
     */
    public static function create(string $content, int $numberOfKeys) : static
    {
        return new static($content, $numberOfKeys);
    }
}