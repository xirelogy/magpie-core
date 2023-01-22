<?php

namespace Magpie\Logs\Relays;

use Carbon\Carbon;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Logs\LogConfig;

/**
 * Rotating file-based relay log
 */
#[FactoryTypeClass(RotatingFileLogRelay::TYPECLASS, ConfigurableLogRelay::class)]
class RotatingFileLogRelay extends FileBasedLogRelay
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'rotating-file';
    /**
     * Default date format
     */
    public const DEFAULT_DATE_FORMAT = 'Y-m-d';


    /**
     * @var string Timezone basis for the date to be used for rotation
     */
    protected string $timezone;
    /**
     * @var string Date format string
     */
    protected string $dateFormat;


    /**
     * @inheritDoc
     */
    public function __construct(LogConfig $config, ?string $source = null)
    {
        parent::__construct($config, $source);

        $this->timezone = SystemTimezone::default();
        $this->dateFormat = static::DEFAULT_DATE_FORMAT;
    }


    /**
     * Specify the rotation timezone
     * @param string $timezone
     * @return $this
     */
    public function withTimezone(string $timezone) : static
    {
        $this->timezone = $timezone;
        return $this;
    }


    /**
     * Specify the date format
     * @param string $dateFormat
     * @return $this
     */
    public function withDateFormat(string $dateFormat) : static
    {
        $this->dateFormat = $dateFormat;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function getFilename() : string
    {
        $appName = LogConfig::systemDefaultSource();
        $formattedDate = Carbon::now($this->timezone)->format($this->dateFormat);

        return "$appName-$formattedDate.log";
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        /** @var LogConfig $config */
        $config = $payload[static::ENV_PAYLOAD_CONFIG];

        return (new static($config))
            ->withDateFormat($parserHost->optional($envKey->key('DATEFORMAT'), StringParser::create(), static::DEFAULT_DATE_FORMAT))
            ->withTimezone($parserHost->optional($envKey->key('TIMEZONE'), StringParser::create(), SystemTimezone::default()))
            ;
    }
}