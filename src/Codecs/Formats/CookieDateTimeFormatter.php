<?php

namespace Magpie\Codecs\Formats;

use Carbon\CarbonInterface;
use Magpie\General\Traits\StaticCreatable;

/**
 * Format date/time according to cookie format
 * @link https://www.rfc-editor.org/rfc/rfc7234#section-5.3
 * @link https://www.rfc-editor.org/rfc/rfc7231#section-7.1.1.1
 */
class CookieDateTimeFormatter implements Formatter
{
    use StaticCreatable;


    /**
     * @inheritDoc
     */
    public function format(mixed $value) : mixed
    {
        if ($value instanceof CarbonInterface) {
            return $value->toImmutable()
                ->setTimezone('UTC')
                ->format('D, d-M-Y H:i:s') . ' GMT';
        }

        return $value;
    }
}