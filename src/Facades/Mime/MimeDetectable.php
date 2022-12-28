<?php

namespace Magpie\Facades\Mime;

use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\System\Concepts\DefaultProviderRegistrable;

/**
 * May detect MIME type from content
 */
interface MimeDetectable extends DefaultProviderRegistrable
{
    /**
     * Detect MIME type from content
     * @param BinaryDataProvidable|string|null $content
     * @return string|null
     */
    public function detectMimeType(BinaryDataProvidable|string|null $content) : ?string;
}