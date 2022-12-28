<?php

namespace Magpie\Logs\Concepts;

use Psr\Log\LoggerInterface;

/**
 * Common loggable interface
 */
interface Loggable extends LoggerInterface
{
    /**
     * Split out an individual interface to log under specific sub-source
     * @param string $source
     * @return Loggable
     */
    public function split(string $source) : Loggable;
}