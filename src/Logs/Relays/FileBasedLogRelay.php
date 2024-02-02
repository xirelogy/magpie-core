<?php

namespace Magpie\Logs\Relays;

use Magpie\Exceptions\FileOperationFailedException;
use Magpie\Exceptions\InvalidPathException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\FileSystem\FileSystem;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\IOs\PhpIo;
use Magpie\Logs\Concepts\LogStringFormattable;
use Magpie\Logs\Formats\SimpleLogStringFormat;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;
use Throwable;

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
        try {
            $formatted = $this->logFormatter->format($record, $this->config);

            $path = static::getLogFullPath($this->getFilename());

            $file = PhpIo::fopen($path, 'a');
            $written = fwrite($file, "$formatted\n");
            if ($written === false) throw new FileOperationFailedException($path, FileOperationFailedException::writeOperation());
            fclose($file);
        } catch (Throwable $ex) {
            ExceptionHandler::systemCritical($ex);
        }
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
     * @throws SafetyCommonException
     */
    protected static final function getLogFullPath(string $filename) : string
    {
        $originalFilename = $filename;

        $prefix = null;
        $lastSlash = strrpos($filename, '/');

        if ($lastSlash !== false) {
            $prefix = substr($filename, 0, $lastSlash);
            while(str_starts_with($prefix, '/')) $prefix = substr($prefix, 1);
            while(str_ends_with($prefix, '/')) $prefix = substr($prefix, 0, -1);
            $filename = substr($filename, $lastSlash + 1);
        }

        if (empty($filename)) throw new InvalidPathException($originalFilename);

        $logPath = static::getLogBasePath($prefix);
        return "$logPath/$filename";
    }


    /**
     * Get log base (directory) path
     * @param string|null $relDir Relative directory in related to the initial base path
     * @return string
     * @throws SafetyCommonException
     */
    protected static final function getLogBasePath(?string $relDir = null) : string
    {
        // Relative log path to the project is now configurable
        $relPath = Kernel::current()->getConfig()->getProjectRelativeLogPath();
        while (str_ends_with($relPath, '/')) {
            $relPath = substr($relPath, -1);
        }

        $basePath = project_path($relPath);
        $logPath = $basePath;

        if (!is_empty_string($relDir)) {
            if (!str_starts_with($relDir, '/')) $relDir = "/$relDir";
            while (str_ends_with($relDir, '/')) {
                $relDir = substr($relDir, 0, -1);
            }
            $logPath .= $relDir;
        }

        // Security check
        $logPath = FileSystem::normalizePath($logPath);
        if ($basePath != $logPath && !str_starts_with($logPath, "$basePath/")) {
            throw new InvalidPathException($logPath);
        }

        LocalRootFileSystem::instance()->createDirectory($logPath);

        return $logPath;
    }
}