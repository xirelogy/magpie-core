<?php

namespace Magpie\Logs\Relays;

use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Sugars\Excepts;
use Magpie\Logs\Concepts\LogStringFormattable;
use Magpie\Logs\Formats\SimpleLogStringFormat;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;

/**
 * File-based relay log
 */
abstract class FileBasedLogRelay extends ConfigurableLogRelay
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
     * Specify the log formatter
     * @param LogStringFormattable $logFormatter
     * @return $this
     */
    public function withFormatter(LogStringFormattable $logFormatter) : static
    {
        $this->logFormatter = $logFormatter;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function log(LogEntry $record) : void
    {
        $formatted = $this->logFormatter->format($record, $this->config);

        $path = static::getLogFullPath($this->getFilename());

        $file = fopen($path, 'a');
        fwrite($file, "$formatted\n");
        fclose($file);
    }


    /**
     * Get filename
     * @return string
     */
    protected abstract function getFilename() : string;


    /**
     * Get log full path
     * @param string $filename
     * @return string
     */
    protected static final function getLogFullPath(string $filename) : string
    {
        $prefix = null;
        $lastSlash = strrpos($filename, '/');

        if ($lastSlash !== false) {
            $prefix = substr($filename, 0, $lastSlash);
            while(str_starts_with($prefix, '/')) $prefix = substr($prefix, 1);
            while(str_ends_with($prefix, '/')) $prefix = substr($prefix, 0, -1);
            $filename = substr($filename, $lastSlash + 1);
        }

        $logPath = static::getLogBasePath($prefix);
        return "$logPath/$filename";
    }


    /**
     * Get log base (directory) path
     * @param string|null $relDir Relative directory in related to the initial base path
     * @return string
     */
    protected static function getLogBasePath(?string $relDir = null) : string
    {
        $logPath = project_path('/storage/logs');

        if (!is_empty_string($relDir)) {
            if (!str_starts_with($relDir, '/')) $relDir = "/$relDir";
            while (str_ends_with($relDir, '/')) {
                $relDir = substr($relDir, 0, -1);
            }
            $logPath .= $relDir;
        }

        Excepts::noThrow(fn () => LocalRootFileSystem::instance()->createDirectory($logPath));

        return $logPath;
    }
}