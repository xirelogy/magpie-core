<?php

namespace Magpie\General\Contents\Impls;

use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Sugars\Excepts;

/**
 * Upgraded binary content from simple BinaryDataProvidable
 * @internal
 */
class UpgradedBinaryContent implements BinaryContentable
{
    /**
     * @var BinaryDataProvidable Base content
     */
    protected BinaryDataProvidable $content;


    /**
     * Constructor
     * @param BinaryDataProvidable $content
     */
    public function __construct(BinaryDataProvidable $content)
    {
        $this->content = $content;
    }


    /**
     * @inheritDoc
     */
    public function getMimeType() : ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getFilename() : ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getData() : string
    {
        return $this->content->getData();
    }


    /**
     * @inheritDoc
     */
    public function getDataSize() : int
    {
        return Excepts::noThrow(fn () => strlen($this->content->getData()), 0);
    }
}