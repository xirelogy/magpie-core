<?php

namespace Magpie\Cryptos\Contents;

/**
 * A block of content
 */
class BlockContent
{
    /**
     * @var string Content type
     */
    public readonly string $type;
    /**
     * @var string Content data
     */
    public readonly string $data;


    /**
     * Constructor
     * @param string $type
     * @param string $data
     */
    public function __construct(string $type, string $data)
    {
        $this->type = $type;
        $this->data = $data;
    }
}