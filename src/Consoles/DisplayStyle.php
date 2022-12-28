<?php

namespace Magpie\Consoles;

/**
 * Common console display styles
 */
enum DisplayStyle : string
{
    /**
     * error
     */
    case ERROR = 'error';
    /**
     * warning
     */
    case WARNING = 'warning';
    /**
     * info
     */
    case INFO = 'info';
    /**
     * strong
     */
    case STRONG = 'strong';
    /**
     * debug
     */
    case DEBUG = 'debug';
}