<?php

namespace Magpie\General\Concepts;

/**
 * Interface to anything that can provide binary content (primitive)
 */
interface PrimitiveBinaryContentable extends BinaryDataProvidable
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
}