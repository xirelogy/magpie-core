<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Exceptions\ArgumentException;

/**
 * Parse a provided item and accept into target format
 * @template T
 */
interface Parser
{
    /**
     * Parse given value
     * @param mixed $value
     * @param string|null $hintName
     * @return T
     * @throws ArgumentException
     */
    public function parse(mixed $value, ?string $hintName = null) : mixed;
}