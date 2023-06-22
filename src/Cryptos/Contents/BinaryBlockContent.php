<?php

namespace Magpie\Cryptos\Contents;

use Magpie\Objects\BinaryData;

/**
 * A block of binary content
 */
class BinaryBlockContent
{
    /**
     * @var string|null Content type (if available)
     */
    public readonly ?string $type;
    /**
     * @var BinaryData Payload binary data
     */
    public readonly BinaryData $data;


    /**
     * Constructor
     * @param string|null $type
     * @param BinaryData $data
     */
    public function __construct(?string $type, BinaryData $data)
    {
        $this->type = $type;
        $this->data = $data;
    }
}