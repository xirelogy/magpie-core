<?php

namespace Magpie\Logs;

/**
 * Common log levels (compliant with PSR-3)
 */
enum LogLevel : int
{
    /**
     * emergency: system is unusable
     */
    case EMERGENCY = 0;
    /**
     * alert: action must be taken immediately
     */
    case ALERT = 1;
    /**
     * critical: critical conditions
     */
    case CRITICAL = 2;
    /**
     * error: error conditions
     */
    case ERROR = 3;
    /**
     * warning: warning conditions
     */
    case WARNING = 4;
    /**
     * notice: normal, but signification, condition
     */
    case NOTICE = 5;
    /**
     * info: informational message
     */
    case INFO = 6;
    /**
     * debug: debug level message
     */
    case DEBUG = 7;
}