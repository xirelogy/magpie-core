<?php

namespace Magpie\Facades\Http;

use Magpie\General\DateTimes\Duration;

/**
 * HTTP client request statistics
 */
class HttpClientRequestTimeStatistics
{
    /**
     * @var Duration|null Total duration
     */
    public ?Duration $total = null;
    /**
     * @var Duration|null Lookup (name resolution) duration
     */
    public ?Duration $lookup = null;
    /**
     * @var Duration|null Connect duration
     */
    public ?Duration $connect = null;
    /**
     * @var Duration|null Handshaking/negotiation duration
     */
    public ?Duration $handshake = null;
    /**
     * @var Duration|null Transfer duration
     */
    public ?Duration $transfer = null;
}