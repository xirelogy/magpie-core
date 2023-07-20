<?php

namespace Magpie\Commands;

use Magpie\Codecs\ParserHosts\ArrayCollection;

/**
 * Common collection for console command
 */
abstract class Collection extends ArrayCollection
{
    /**
     * @inheritDoc
     */
    public function fullKey(int|string $key) : string
    {
        if (is_empty_string($this->prefix)) return $key;
        return $this->prefix . '.' . $key;
    }


    /**
     * @inheritDoc
     */
    public function getNextPrefix(int|string|null $key) : ?string
    {
        if (is_empty_string($this->prefix)) return $key;
        if (is_empty_string($key)) return $this->prefix;

        return $this->prefix . '.' . $key;
    }
}