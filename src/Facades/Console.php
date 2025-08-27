<?php

namespace Magpie\Facades;

use Closure;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Consoles\Concepts\Consolable;
use Magpie\Consoles\Concepts\ConsoleDisplayable;
use Magpie\Consoles\ConsoleProvider;
use Magpie\Consoles\DisplayStyle;
use Magpie\Consoles\Inputs\PromptWithOption;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Concepts\StreamWriteable;
use Magpie\General\Traits\StaticClass;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\Concepts\LogRelayable;
use Magpie\Logs\Concepts\LogStringFormattable;
use Magpie\Logs\Formats\SimpleConsoleLogStringFormat;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;
use Magpie\Logs\Loggers\DefaultLogger;
use Magpie\Logs\LogLevel;
use Magpie\Logs\LogRelay;
use Magpie\System\Kernel\Kernel;
use Stringable;

/**
 * Console facade
 */
class Console
{
    use StaticClass;


    /**
     * Output a text to console with 'emergency' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function emergency(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::EMERGENCY);
    }


    /**
     * Output a text to console with 'alert' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function alert(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::ALERT);
    }


    /**
     * Output a text to console with 'critical' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function critical(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::CRITICAL);
    }


    /**
     * Output a text to console with 'error' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function error(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::ERROR);
    }


    /**
     * Output a text to console with 'warning' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function warning(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::WARNING);
    }


    /**
     * Output a text to console with 'notice' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function notice(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::NOTICE);
    }


    /**
     * Output a text to console with 'info' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function info(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::INFO);
    }


    /**
     * Output a text to console with 'debug' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function debug(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::DEBUG);
    }


    /**
     * Output a text to console with (optionally) a display style
     * @param Stringable|string|null $text
     * @param DisplayStyle|null $style
     * @return void
     */
    public static function output(Stringable|string|null $text, ?DisplayStyle $style = null) : void
    {
        ConsoleProvider::default()?->output($text, $style);
    }


    /**
     * Display on console
     * @param ConsoleDisplayable|null $target
     * @return void
     */
    public static function display(?ConsoleDisplayable $target) : void
    {
        ConsoleProvider::default()?->display($target);
    }


    /**
     * A value is required (mandatory) from console input
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param Parser<T>|null $parser
     * @return T
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function requires(PromptWithOption|Stringable|string|null $prompt, ?Parser $parser = null) : mixed
    {
        return static::ensureProvider()->requires($prompt, $parser);
    }


    /**
     * A value is optionally required from console input
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param Parser<T>|null $parser
     * @param T|null $default
     * @return T|null
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function optional(PromptWithOption|Stringable|string|null $prompt, ?Parser $parser = null, mixed $default = null) : mixed
    {
        return static::ensureProvider()->optional($prompt, $parser, $default);
    }


    /**
     * A value is required (mandatory) from console input.
     * Loop and retry until valid or maximum number of tries exceeded.
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param int|null $maxTries
     * @param Parser<T>|null $parser
     * @return T
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function requiresLoop(PromptWithOption|Stringable|string|null $prompt, ?int $maxTries = null, ?Parser $parser = null) : mixed
    {
        return static::ensureProvider()->requiresLoop($prompt, $maxTries, $parser);
    }


    /**
     * A value is optionally required from console input.
     * Loop and retry until valid or maximum number of tries exceeded.
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param int|null $maxTries
     * @param Parser|null $parser
     * @param T|null $default
     * @return T|null
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function optionalLoop(PromptWithOption|Stringable|string|null $prompt, ?int $maxTries = null, ?Parser $parser = null, mixed $default = null) : mixed
    {
        return static::ensureProvider()->optionalLoop($prompt, $maxTries, $parser, $default);
    }


    /**
     * Create a logging relay target that redirects to console
     * @param LogStringFormattable|null $logFormatter
     * @param LogConfig|null $logConfig
     * @return LogRelayable
     */
    public static function asLogRelay(?LogStringFormattable $logFormatter = null, ?LogConfig $logConfig = null) : LogRelayable
    {
        $logFormatter = $logFormatter ?? new SimpleConsoleLogStringFormat();
        $logConfig = $logConfig ?? Kernel::current()->getConfig()->createDefaultLogConfig();

        return new class(static::output(...), $logFormatter, $logConfig) extends LogRelay {
            /**
             * @var Closure Output function
             */
            private readonly Closure $outputFn;
            /**
             * @var LogStringFormattable Associated formatter
             */
            private readonly LogStringFormattable $logFormatter;


            /**
             * Constructor
             * @param callable(Stringable|string|null,DisplayStyle|null):void $outputFn
             * @param LogStringFormattable $logFormatter
             * @param LogConfig $logConfig
             */
            public function __construct(callable $outputFn, LogStringFormattable $logFormatter, LogConfig $logConfig)
            {
                parent::__construct($logConfig, null);
                $this->outputFn = $outputFn;
                $this->logFormatter = $logFormatter;
            }


            /**
             * @inheritDoc
             */
            public function log(LogEntry $record) : void
            {
                $displayStyle = static::translateLevel($record->level);
                $formatted = $this->logFormatter->format($record, $this->config);

                ($this->outputFn)($formatted, $displayStyle);
            }


            /**
             * Translate log level to display style
             * @param LogLevel $level
             * @return DisplayStyle
             */
            protected static function translateLevel(LogLevel $level) : DisplayStyle
            {
                return match ($level) {
                    LogLevel::EMERGENCY => DisplayStyle::EMERGENCY,
                    LogLevel::ALERT => DisplayStyle::ALERT,
                    LogLevel::CRITICAL => DisplayStyle::CRITICAL,
                    LogLevel::ERROR => DisplayStyle::ERROR,
                    LogLevel::WARNING => DisplayStyle::WARNING,
                    LogLevel::NOTICE => DisplayStyle::NOTICE,
                    LogLevel::INFO => DisplayStyle::INFO,
                    LogLevel::DEBUG => DisplayStyle::DEBUG,
                };
            }
        };
    }


    /**
     * Create a logging target and redirect to console
     * @param LogStringFormattable|null $logFormatter
     * @param LogConfig|null $logConfig
     * @return Loggable
     */
    public static function asLogger(?LogStringFormattable $logFormatter = null, ?LogConfig $logConfig = null) : Loggable
    {
        $relay = static::asLogRelay($logFormatter, $logConfig);

        return new DefaultLogger($relay);
    }


    /**
     * Create a stream to write to console output
     * @return StreamWriteable
     */
    public static function asWriteStream() : StreamWriteable
    {
        return new class(static::output(...)) implements StreamWriteable {
            /**
             * @var Closure(Stringable|string|null,DisplayStyle|null):void Output function
             */
            private readonly Closure $outputFn;
            /**
             * @var string Output buffer
             */
            private string $buffer;


            /**
             * Constructor
             * @param callable(Stringable|string|null,DisplayStyle|null):void $outputFn
             */
            public function __construct(callable $outputFn)
            {
                $this->outputFn = $outputFn;
                $this->buffer = '';
            }


            /**
             * @inheritDoc
             */
            public function write(string $data) : int
            {
                $this->buffer .= $data;
                $this->processBuffer();
                return strlen($data);
            }


            /**
             * @inheritDoc
             */
            public function close() : void
            {
                $this->processBuffer();
                if (strlen($this->buffer) <= 0) return;

                // Dump remaining in a line, and return
                ($this->outputFn)($this->buffer);
                $this->buffer = '';
            }


            /**
             * Process the buffer, output any pending lines
             * @return void
             */
            private function processBuffer() : void
            {
                $this->buffer = str_replace("\r", '', $this->buffer);

                for (;;) {
                    $nPos = strpos($this->buffer, "\n");
                    if ($nPos === false) return;

                    $line = substr($this->buffer, 0, $nPos);
                    ($this->outputFn)($line);
                    $this->buffer = substr($this->buffer, $nPos + 1);
                }
            }
        };
    }


    /**
     * Ensure that a provider is available
     * @return Consolable
     * @throws SafetyCommonException
     */
    protected static function ensureProvider() : Consolable
    {
        return ConsoleProvider::default() ?? throw new UnsupportedException(_l('No console provider available'));
    }
}