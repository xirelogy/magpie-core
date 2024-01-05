<?php

namespace Magpie\General\IOs;

use Iterator;
use Magpie\Exceptions\StreamReadFailureException;
use Magpie\General\Concepts\StreamReadable;

/**
 * Convert iterable (of binary string chunks) into read stream
 */
class IterableReadStream implements StreamReadable
{
    /**
     * @var Iterator<mixed, string> Internal iterator
     */
    private readonly Iterator $iter;
    /**
     * @var string|null Current buffer
     */
    protected ?string $buffer = null;


    /**
     * Constructor
     * @param iterable<string> $provider
     */
    protected function __construct(iterable $provider)
    {
        $this->iter = iter_cursor($provider);
        $this->iter->rewind();
    }


    /**
     * @inheritDoc
     */
    public function hasData() : bool
    {
        if ($this->buffer !== null) return true;

        return $this->pollData();
    }


    /**
     * @inheritDoc
     */
    public function read(?int $max = null) : string
    {
        if ($this->buffer !== null) {
            return $this->readFromBuffer();
        }

        if (!$this->pollData()) throw new StreamReadFailureException();

        return $this->readFromBuffer();
    }


    /**
     * Poll for data
     * @return bool
     */
    protected function pollData() : bool
    {
        if (!$this->iter->valid()) return false;
        $this->buffer = $this->iter->current();
        $this->iter->next();
        return true;
    }


    /**
     * Read data from buffer
     * @param int|null $max
     * @return string
     */
    protected function readFromBuffer(?int $max = null) : string
    {
        // Invalid case, but just for sure
        if ($this->buffer === null) return '';

        if ($max === null || $max <= strlen($this->buffer)) {
            // Max not specified or max not reached, returns everything
            $ret = $this->buffer;
            $this->buffer = null;
        } else {
            // Return partial
            $ret = substr($this->buffer, 0, $max);
            $this->buffer = substr($this->buffer, $max);
        }

        return $ret;
    }


    /**
     * Create an instance
     * @param iterable<string> $provider Source provider of binary string chunks
     * @return static
     */
    public static function create(iterable $provider) : static
    {
        return new static($provider);
    }
}