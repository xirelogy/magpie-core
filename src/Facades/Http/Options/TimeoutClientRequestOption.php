<?php

namespace Magpie\Facades\Http\Options;

use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\General\DateTimes\Duration;

/**
 * Specify timeout for HTTP client operation
 */
class TimeoutClientRequestOption extends HttpClientRequestOption
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'timeout';
    /**
     * @var Duration Timeout duration
     */
    public readonly Duration $timeout;


    /**
     * Constructor
     * @param Duration $timeout
     */
    protected function __construct(Duration $timeout)
    {
        parent::__construct();

        $this->timeout = $timeout;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Create option
     * @param int|Duration $timeout
     * @return static
     */
    public static function create(int|Duration $timeout) : static
    {
        $timeout = Duration::accept($timeout);

        return new static($timeout);
    }
}