<?php

namespace Magpie\Consoles;

use Magpie\Consoles\Concepts\Consolable;
use Stringable;

/**
 * Basic console implementation
 */
abstract class BasicConsole implements Consolable
{
    /**
     * @inheritDoc
     */
    public function emergency(Stringable|string|null $text) : void
    {
        $this->output($text, DisplayStyle::EMERGENCY);
    }


    /**
     * @inheritDoc
     */
    public function alert(Stringable|string|null $text) : void
    {
        $this->output($text, DisplayStyle::ALERT);
    }


    /**
     * @inheritDoc
     */
    public function critical(Stringable|string|null $text) : void
    {
        $this->output($text, DisplayStyle::CRITICAL);
    }


    /**
     * @inheritDoc
     */
    public function error(Stringable|string|null $text) : void
    {
        $this->output($text, DisplayStyle::ERROR);
    }


    /**
     * @inheritDoc
     */
    public function warning(Stringable|string|null $text) : void
    {
        $this->output($text, DisplayStyle::WARNING);
    }


    /**
     * @inheritDoc
     */
    public function notice(Stringable|string|null $text) : void
    {
        $this->output($text, DisplayStyle::NOTICE);
    }


    /**
     * @inheritDoc
     */
    public function info(Stringable|string|null $text) : void
    {
        $this->output($text, DisplayStyle::INFO);
    }


    /**
     * @inheritDoc
     */
    public function debug(Stringable|string|null $text) : void
    {
        $this->output($text, DisplayStyle::DEBUG);
    }
}