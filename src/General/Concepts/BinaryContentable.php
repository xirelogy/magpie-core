<?php

namespace Magpie\General\Concepts;

/**
 * Interface to anything that can provide binary content
 */
interface BinaryContentable extends BinaryDataProvidable
{
    /**
     * Associated MIME type (if any)
     * @return string|null
     */
    public function getMimeType() : ?string;


    /**
     * Associated filename (if any)
     * @return string|null
     */
    public function getFilename() : ?string;


    /**
     * Binary content data size
     * @return int
     */
    public function getDataSize() : int;
}