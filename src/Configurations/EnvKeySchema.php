<?php

namespace Magpie\Configurations;

/**
 * A key schema to construct key for reading from environment variables
 */
class EnvKeySchema
{
    /**
     * @var array<string> All prefixes
     */
    protected array $prefixes;


    /**
     * Constructor
     * @param string $prefix
     * @param string|null ...$prefixes
     */
    public function __construct(string $prefix, ?string ...$prefixes)
    {
        $this->prefixes = [ $prefix ];

        foreach ($prefixes as $subPrefix) {
            $this->prefixes[] = $subPrefix ?? '';
        }
    }


    /**
     * Add a sub prefix
     * @param string|null $subPrefix
     * @return $this
     */
    public function addPrefix(?string $subPrefix) : static
    {
        $this->prefixes[] = ($subPrefix ?? '');
        return $this;
    }


    /**
     * Create a key
     * @param string|null ...$suffixes
     * @return string
     */
    public function key(?string ...$suffixes) : string
    {
        return EnvParserHost::makeEnvKey(...$this->prefixes, ...$suffixes);
    }
}