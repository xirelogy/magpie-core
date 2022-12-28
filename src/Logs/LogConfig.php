<?php

namespace Magpie\Logs;

/**
 * Log configuration
 */
class LogConfig
{
    /**
     * Default time format string
     */
    public const DEFAULT_TIME_FORMAT = 'Y-m-d H:i:s';
    /**
     * @var string Timezone where log messages are based upon
     */
    public string $timezone;
    /**
     * @var string Time format string
     */
    public string $timeFormat;
    /**
     * @var string Default source name to be used in absence of source
     */
    public string $defaultSource;


    /**
     * Constructor
     * @param string $timezone
     * @param string $timeFormat
     * @param string $defaultSource
     */
    public function __construct(string $timezone, string $timeFormat, string $defaultSource)
    {
        $this->timezone = $timezone;
        $this->timeFormat = $timeFormat;
        $this->defaultSource = $defaultSource;
    }


    /**
     * System's default source
     * @return string
     */
    public static function systemDefaultSource() : string
    {
        return env('APP_NAME', 'app');
    }
}