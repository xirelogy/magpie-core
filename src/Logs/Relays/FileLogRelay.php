<?php

namespace Magpie\Logs\Relays;

use Magpie\Logs\Concepts\LogStringFormattable;
use Magpie\Logs\Formats\SimpleLogStringFormat;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;
use Magpie\Logs\LogRelay;

/**
 * Relay log
 */
class FileLogRelay extends LogRelay
{
    /**
     * @var LogStringFormattable Log formatter
     */
    protected LogStringFormattable $logFormatter;


    /**
     * Constructor
     * @param LogConfig $config
     * @param string|null $source
     */
    public function __construct(LogConfig $config, ?string $source = null)
    {
        parent::__construct($config, $source);

        $this->logFormatter = new SimpleLogStringFormat();
    }


    /**
     * @inheritDoc
     */
    public function log(LogEntry $record) : void
    {
        $formatted = $this->logFormatter->format($record, $this->config);

        $path = project_path('/storage/logs/app.log');

        $file = fopen($path, 'a');
        fwrite($file, "$formatted\n");
        fclose($file);
    }
}