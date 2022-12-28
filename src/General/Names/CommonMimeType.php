<?php

/** @noinspection PhpUnused */

namespace Magpie\General\Names;

use Magpie\General\Traits\StaticClass;

/**
 * Common MIME types
 */
class CommonMimeType
{
    use StaticClass;

    public const BINARY = 'application/octet-stream';
    public const CSS = 'text/css';
    public const FORM_URLENCODED = 'application/x-www-form-urlencoded';
    public const JPEG = 'image/jpeg';
    public const JPG = self::JPEG;
    public const JS = 'application/javascript';
    public const JSON = 'application/json';
    public const XML = 'application/xml';
    public const PNG = 'image/png';
    public const TXT = 'text/plain';
}