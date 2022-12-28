<?php

namespace Magpie\General\IOs;

use Exception;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\StreamReadFailureException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\StreamReadable;

/**
 * Simple read stream
 */
class SimpleReadStream implements StreamReadable
{
    /**
     * @var string Actual content
     */
    protected string $content;
    /**
     * @var int Current content size
     */
    protected int $contentSize;
    /**
     * @var int Current cursor position
     */
    protected int $cursor;


    /**
     * Constructor
     * @param string|BinaryDataProvidable $data
     * @throws StreamException
     */
    public function __construct(string|BinaryDataProvidable $data)
    {
        $this->content = static::acceptContent($data);
        $this->contentSize = strlen($this->content);
        $this->cursor = 0;
    }


    /**
     * @inheritDoc
     */
    public function hasData() : bool
    {
        return $this->getRemainingSize() > 0;
    }


    /**
     * @inheritDoc
     */
    public function read(?int $max = null) : string
    {
        $remainingSize = $this->getRemainingSize();
        $max = $max ?? $remainingSize;
        if ($max > $remainingSize) $max = $remainingSize;

        $ret = substr($this->content, $this->cursor, $max);
        $this->cursor += $max;

        return $ret;
    }


    /**
     * Remaining data size
     * @return int
     */
    protected function getRemainingSize() : int
    {
        return $this->contentSize - $this->cursor;
    }


    /**
     * Accept content
     * @param string|BinaryDataProvidable $data
     * @return string
     * @throws StreamException
     */
    protected static function acceptContent(string|BinaryDataProvidable $data) : string
    {
        try {
            if ($data instanceof BinaryDataProvidable) return $data->getData();
            return $data;
        } catch (StreamException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new StreamReadFailureException(previous: $ex);
        }
    }
}