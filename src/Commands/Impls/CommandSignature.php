<?php

namespace Magpie\Commands\Impls;

use Exception;
use Magpie\Commands\Attributes\CommandArgumentDescription;
use Magpie\Commands\Attributes\CommandArgumentDescriptionL;
use Magpie\Commands\Attributes\CommandOptionDescription;
use Magpie\Commands\Attributes\CommandOptionDescriptionL;
use Magpie\Commands\Exceptions\CommandOptionException;
use Magpie\Commands\Exceptions\DisallowedCommandOptionPayloadException;
use Magpie\Commands\Exceptions\MissingCommandArgumentException;
use Magpie\Commands\Exceptions\MissingCommandOptionPayloadException;
use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\General\Str;
use Magpie\General\Sugars\Quote;
use Magpie\HttpServer\ServerCollection;
use Magpie\Locales\Concepts\Localizable;
use Magpie\Locales\I18n;
use ReflectionClass;

/**
 * Command signature
 * @internal
 */
class CommandSignature
{
    /**
     * @var string The command only
     */
    public readonly string $command;
    /**
     * @var Localizable|string|null The command description
     */
    public readonly Localizable|string|null $description;
    /**
     * @var array<string, CommandOptionDefinition> Command options
     */
    public readonly array $options;
    /**
     * @var array<string, CommandArgumentDefinition> Command arguments
     */
    public readonly array $arguments;
    /**
     * @var string|null Payload class name
     */
    public ?string $payloadClassName = null;


    /**
     * Constructor
     * @param string $command
     * @param Localizable|string|null $description
     * @param array<string, CommandOptionDefinition> $options
     * @param array<string, CommandArgumentDefinition> $arguments
     */
    protected function __construct(string $command, Localizable|string|null $description, array $options, array $arguments)
    {
        $this->command = $command;
        $this->description = $description;
        $this->options = $options;
        $this->arguments = $arguments;
    }


    /**
     * Create request from console arguments
     * @param ServerCollection $serverVars
     * @param int $argc
     * @param array $argv
     * @param int $commandIndex
     * @return ImplRequest
     * @throws Exception
     */
    public function createRequest(ServerCollection $serverVars, int $argc, array $argv, int $commandIndex) : ImplRequest
    {
        $command = $argv[$commandIndex] ?? throw new UnexpectedException();

        $outOptions = [];
        $outArguments = [];

        $thisArgIndex = $commandIndex + 1;

        // Process options ahead of arguments
        while (true) {
            if ($thisArgIndex >= $argc) break;

            $thisArg = $argv[$thisArgIndex];

            // Allow '--' to be options/arguments separator
            if ($thisArg === '--') {
                ++$thisArgIndex;
                break;
            }

            if (str_starts_with($thisArg, '--')) {
                $this->handleOptions($outOptions, $thisArg);
                ++$thisArgIndex;
            } else {
                break;
            }
        }

        // Then process the arguments
        foreach ($this->arguments as $argumentKey => $argumentDef) {
            if ($thisArgIndex >= $argc) {
                if (!$argumentDef->isMandatory) break;
                throw new MissingCommandArgumentException($argumentKey, $this->getCommandString());
            }
            $thisArg = $argv[$thisArgIndex];

            $outArguments[$argumentKey] = $thisArg;
            ++$thisArgIndex;
        }

        // Update options
        foreach ($this->options as $optionKey => $optionDef) {
            if (array_key_exists($optionKey, $outOptions)) continue;
            if ($optionDef->hasPayload) continue;
            $outOptions[$optionKey] = false;
        }

        return new ImplRequest($command, $outOptions, $outArguments, $serverVars);
    }


    /**
     * Handle argument like an option
     * @param array<string, string|bool> $options
     * @param string $thisArg
     * @return void
     * @throws CommandOptionException
     */
    protected function handleOptions(array &$options, string $thisArg) : void
    {
        $thisArg = substr($thisArg, 2); // Remove leading dash
        $thisArgPayload = null;

        $equalPos = strpos($thisArg, '=');

        if ($equalPos !== false) {
            $thisArgPayload = substr($thisArg, $equalPos + 1);
            $thisArg = substr($thisArg, 0, $equalPos);
        }

        if (array_key_exists($thisArg, $this->options)) {
            $optionDefinition = $this->options[$thisArg];
            if ($optionDefinition->hasPayload) {
                if ($thisArgPayload === null) throw new MissingCommandOptionPayloadException($thisArg, $this->command);
            } else {
                if ($thisArgPayload !== null) throw new DisallowedCommandOptionPayloadException($thisArg, $this->command);
                $thisArgPayload = true;
            }
        } else {
            if (Str::isNullOrEmpty($thisArgPayload)) $thisArgPayload = true;
        }


        $options[$thisArg] = $thisArgPayload;
    }


    /**
     * Get full command string
     * @return string
     */
    public function getCommandString() : string
    {
        $ret = $this->command;
        foreach ($this->arguments as $argumentKey => $argumentDef) {
            $ret .= ' ' . Quote::brace($argumentDef->isMandatory ? $argumentKey : ($argumentKey . '?'));
        }

        return $ret;
    }


    /**
     * Parse from given signature text
     * @param string $signature
     * @param Localizable|string|null $description
     * @param ReflectionClass|null $class
     * @return static
     * @throws Exception
     */
    public static function parse(string $signature, Localizable|string|null $description, ?ReflectionClass $class = null) : static
    {
        $tokens = static::tokenize($signature);

        if (count($tokens) < 1) throw new InvalidDataFormatException(_l('Missing command'));

        $command = null;
        $arguments = [];
        $options = [];
        foreach ($tokens as $token) {
            if ($command === null) {
                $command = $token;
            } else {
                $argumentContent = static::removeBrace($token);
                if (str_starts_with($argumentContent, '--')) {
                    $option = static::acceptOption($argumentContent);
                    if (array_key_exists($option->name, $options)) throw new DuplicatedKeyException($option->name, _l('option'));
                    $options[$option->name] = $option;
                } else {
                    $argument = static::acceptArgument($argumentContent);
                    if (array_key_exists($argument->name, $arguments)) throw new DuplicatedKeyException($argument->name, _l('argument'));
                    $arguments[$argument->name] = $argument;
                }
            }
        }

        if ($class !== null) {
            // Update the signature argument descriptions
            foreach (static::listArgumentDescriptionsFromAttributes($class) as $argument => $argDescription) {
                if (!array_key_exists($argument, $arguments)) continue;
                $arguments[$argument]->description = $argDescription;
            }

            // Update the signature option descriptions
            foreach (static::listOptionDescriptionsFromAttributes($class) as $option => $optDescription) {
                if (!array_key_exists($option, $options)) continue;
                $options[$option]->description = $optDescription;
            }
        }

        return new static($command, $description, $options, $arguments);
    }


    /**
     * Remove braces
     * @param string $argument
     * @return string
     */
    protected static function removeBrace(string $argument) : string
    {
        if (str_starts_with($argument, '{')) $argument = substr($argument, 1);
        if (str_ends_with($argument, '}')) $argument = substr($argument, 0, -1);

        return trim($argument);
    }


    /**
     * Accept argument
     * @param string $content
     * @return CommandArgumentDefinition
     */
    protected static function acceptArgument(string $content) : CommandArgumentDefinition
    {
        $isMandatory = true;
        if (str_ends_with($content, '?')) {
            $content = substr($content, 0, -1);
            $isMandatory = false;
        }

        return new CommandArgumentDefinition($content, $isMandatory);
    }


    /**
     * Accept option
     * @param string $content
     * @return CommandOptionDefinition
     */
    protected static function acceptOption(string $content) : CommandOptionDefinition
    {
        $hasPayload= false;
        $content = substr($content, 2); // Remove leading dash-dash

        if (str_ends_with($content, '=')) {
            $content = trim(substr($content, 0, -1));
            $hasPayload = true;
        }

        return new CommandOptionDefinition($content, $hasPayload);
    }


    /**
     * List all argument descriptions
     * @param ReflectionClass $class
     * @return iterable<string, string|Localizable>
     */
    protected static function listArgumentDescriptionsFromAttributes(ReflectionClass $class) : iterable
    {
        foreach ($class->getAttributes(CommandArgumentDescription::class) as $attribute) {
            /** @var CommandArgumentDescription $instance */
            $instance = $attribute->newInstance();
            if (Str::isNullOrEmpty($instance->desc)) continue;
            yield $instance->name => $instance->desc;
        }

        foreach ($class->getAttributes(CommandArgumentDescriptionL::class) as $attribute) {
            /** @var CommandArgumentDescriptionL $instance */
            $instance = $attribute->newInstance();
            if (Str::isNullOrEmpty($instance->desc)) continue;
            yield $instance->name => I18n::tag($instance->desc, $class->name);
        }
    }


    /**
     * List all option descriptions
     * @param ReflectionClass $class
     * @return iterable<string, string|Localizable>
     */
    protected static function listOptionDescriptionsFromAttributes(ReflectionClass $class) : iterable
    {
        foreach ($class->getAttributes(CommandOptionDescription::class) as $attribute) {
            /** @var CommandOptionDescription $instance */
            $instance = $attribute->newInstance();
            if (Str::isNullOrEmpty($instance->desc)) continue;
            yield $instance->name => $instance->desc;
        }

        foreach ($class->getAttributes(CommandOptionDescriptionL::class) as $attribute) {
            /** @var CommandOptionDescriptionL $instance */
            $instance = $attribute->newInstance();
            if (Str::isNullOrEmpty($instance->desc)) continue;
            yield $instance->name => I18n::tag($instance->desc, $class->name);
        }
    }


    /**
     * Initial state
     */
    protected const T_STATE_INIT = 0;
    /**
     * Command expected
     */
    protected const T_STATE_CMD = 1;
    /**
     * Expecting argument
     */
    protected const T_STATE_PRE_ARG = 2;
    /**
     * Expecting start of argument (open brace)
     */
    protected const T_STATE_ARG_START = 3;
    /**
     * Expecting argument content
     */
    protected const T_STATE_ARG = 4;


    /**
     * Tokenize the signature
     * @param string $text
     * @return array<string>
     * @throws Exception
     */
    protected static function tokenize(string $text) : array
    {
        $ret = [];

        $textLength = strlen($text);
        $state = static::T_STATE_INIT;
        $buffer = '';

        for ($i = 0; $i < $textLength; ++$i) {
            $ch = substr($text, $i, 1);
            $isReparse = true;

            while ($isReparse) {
                $isReparse = false;

                switch ($state) {
                    case static::T_STATE_INIT:
                        // Initial state, remove any excessive spaces, until ready for next
                        if (!static::isSpace($ch)) {
                            $state = static::T_STATE_CMD;
                            $isReparse = true;
                        }
                        break;

                    case static::T_STATE_CMD:
                        // Expecting command
                        if (!static::isSpace($ch)) {
                            $buffer .= $ch;
                        } else {
                            static::parseAcceptBuffer($ret, $buffer);
                            $state = static::T_STATE_PRE_ARG;
                            $isReparse = true;
                        }
                        break;

                    case static::T_STATE_PRE_ARG:
                        // Expecting argument
                        if (!static::isSpace($ch)) {
                            $state = static::T_STATE_ARG_START;
                            $isReparse = true;
                        }
                        break;

                    case static::T_STATE_ARG_START:
                        // Expecting start of argument (open brace)
                        if ($ch === '{') {
                            $buffer .= $ch;
                            $state = static::T_STATE_ARG;
                        } else {
                            throw new InvalidDataFormatException(_format_safe(_l('Unexpected character \'{{0}}\' in command signature'), $ch) ?? _l('Unexpected character in command signature'));
                        }
                        break;

                    case static::T_STATE_ARG:
                        // Expecting argument content
                        $buffer .= $ch;
                        if ($ch === '}') {
                            static::parseAcceptBuffer($ret, $buffer);
                            $state = static::T_STATE_PRE_ARG;
                        }
                        break;

                    default:
                        throw new UnexpectedException();
                }
            }
        }

        static::parseAcceptBuffer($ret, $buffer);

        return $ret;
    }


    /**
     * Accept buffer
     * @param array $ret
     * @param string $buffer
     * @return void
     */
    protected static function parseAcceptBuffer(array &$ret, string &$buffer) : void
    {
        if (is_empty_string($buffer)) return;

        $ret[] = $buffer;
        $buffer = '';
    }


    /**
     * If character is space
     * @param string $ch
     * @return bool
     */
    protected static function isSpace(string $ch) : bool
    {
        /** @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
        switch ($ch) {
            case ' ';
            case "\t";
            case "\r";
            case "\n";
            case "\0";
                return true;
            default:
                return false;
        }
    }
}