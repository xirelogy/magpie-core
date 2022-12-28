<?php

namespace Magpie\Facades\Mime;

use Magpie\System\Concepts\DefaultProviderRegistrable;

/**
 * May resolve between MIME content types and extensions
 */
interface MimeResolvable extends DefaultProviderRegistrable
{
    /**
     * Get extension for MIME type
     * @param string|null $mimeType
     * @return string|null
     */
    public function getExtension(?string $mimeType) : ?string;


    /**
     * Get MIME type for extension
     * @param string|null $extension
     * @return string|null
     */
    public function getMimeType(?string $extension) : ?string;
}