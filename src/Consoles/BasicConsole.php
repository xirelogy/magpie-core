<?php

namespace Magpie\Consoles;

use Magpie\Codecs\Impls\ParserArgTypeContext;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Consoles\Concepts\Consolable;
use Magpie\Consoles\Inputs\PromptWithOption;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Str;
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


    /**
     * @inheritDoc
     */
    public function requires(PromptWithOption|Stringable|string|null $prompt, ?Parser $parser = null) : mixed
    {
        $value = $this->obtain($prompt, null);
        if (Str::isNullOrEmpty($value)) throw new MissingArgumentException(argType: _l('input'));

        if ($parser !== null) {
            $value = ParserArgTypeContext::parseUsingArgType(_l('input'), $parser, $value, null);
            if (Str::isNullOrEmpty($value)) throw new MissingArgumentException(argType: _l('input'));
        }

        return $value;
    }


    /**
     * @inheritDoc
     */
    public function optional(PromptWithOption|Stringable|string|null $prompt, ?Parser $parser = null, mixed $default = null) : mixed
    {
        $value = $this->obtain($prompt, $default);
        if (Str::isNullOrEmpty($value)) $value = $default;

        if ($parser !== null && $value !== null) {
            $value = ParserArgTypeContext::parseUsingArgType(_l('input'), $parser, $value, null);
        }

        return $value;
    }


    /**
     * @inheritDoc
     */
    public function requiresLoop(PromptWithOption|Stringable|string|null $prompt, ?int $maxTries = null, ?Parser $parser = null) : mixed
    {
        $numTry = 0;
        while (true) {
            ++$numTry;
            try {
                return $this->requires($prompt, $parser);
            } catch (ArgumentException $ex) {
                if ($maxTries !== null && $numTry >= $maxTries) throw $ex;
                $this->error($ex->getMessage());
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function optionalLoop(PromptWithOption|Stringable|string|null $prompt, ?int $maxTries = null, ?Parser $parser = null, mixed $default = null) : mixed
    {
        $numTry = 0;
        while (true) {
            ++$numTry;
            try {
                return $this->optional($prompt, $parser, $default);
            } catch (ArgumentException $ex) {
                if ($maxTries !== null && $numTry >= $maxTries) throw $ex;
                $this->error($ex->getMessage());
            }
        }
    }


    /**
     * Obtain input from console
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param string|null $default
     * @return string|null
     * @throws SafetyCommonException
     */
    protected abstract function obtain(PromptWithOption|Stringable|string|null $prompt, ?string $default) : ?string;
}