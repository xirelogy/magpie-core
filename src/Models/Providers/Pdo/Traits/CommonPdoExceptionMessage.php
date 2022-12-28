<?php

namespace Magpie\Models\Providers\Pdo\Traits;

use Magpie\Models\Providers\Pdo\PdoError;

trait CommonPdoExceptionMessage
{
    /**
     * Format message
     * @param PdoError $error
     * @param string $reasonMessage
     * @param string $defaultMessage
     * @return string
     */
    protected static function formatMessageUsing(PdoError $error, string $reasonMessage, string $defaultMessage) : string
    {
        $reason = $error->getSimpleMessage();

        if (!empty($reason)) {
            return _format_safe($reasonMessage, $reason) ?? $defaultMessage;
        }

        return $defaultMessage;
    }
}