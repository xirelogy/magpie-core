<?php

namespace Magpie\Consoles;

/**
 * Common console display styles
 */
enum DisplayStyle : string
{
    /**
     * emergency
     */
    case EMERGENCY = 'emergency';
    /**
     * alert
     */
    case ALERT = 'alert';
    /**
     * critical
     */
    case CRITICAL = 'critical';
    /**
     * error
     */
    case ERROR = 'error';
    /**
     * warning
     */
    case WARNING = 'warning';
    /**
     * notice
     */
    case NOTICE = 'notice';
    /**
     * info
     */
    case INFO = 'info';
    /**
     * Note
     * @deprecated Not one of the PSR3 log level: instead please use 'debug'
     */
    case NOTE = 'comment';
    /**
     * strong
     * @deprecated Not one of the PSR3 log level: instead please use 'notice'
     */
    case STRONG = 'strong';
    /**
     * debug
     */
    case DEBUG = 'debug';
}