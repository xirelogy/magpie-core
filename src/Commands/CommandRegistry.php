<?php

namespace Magpie\Commands;

use Exception;
use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Concepts\CommandSchedulable;
use Magpie\Commands\Exceptions\UnknownCommandException;
use Magpie\Commands\Impls\CommandSignature as ImplCommandSignature;
use Magpie\Exceptions\ConflictException;
use Magpie\General\Traits\StaticClass;
use Magpie\Locales\Concepts\Localizable;
use Magpie\Locales\I18n;
use Magpie\Schedules\ScheduleDefinition;
use Magpie\System\HardCore\AutoloadReflection;
use ReflectionClass;

/**
 * Command registry
 */
class CommandRegistry
{
    use StaticClass;


    /**
     * @var array<string> All directories providing Command
     */
    protected static array $directories = [];
    /**
     * @var array<string, ImplCommandSignature> Command lookup
     */
    protected static array $commandMap = [];
    /**
     * @var array<class-string<Command>, string> Command class map
     */
    protected static array $commandClasses = [];


    /**
     * Include given directory for discovery of commands
     * @param string $path
     * @return void
     */
    public static function includeDirectory(string $path) : void
    {
        static::$directories[] = $path;
    }


    /**
     * Find a command signature for given command handler class name
     * @param class-string<Command> $commandClass
     * @return ImplCommandSignature|null
     * @internal
     */
    public static function _getSignature(string $commandClass) : ?ImplCommandSignature
    {
        $commandKey = static::$commandClasses[$commandClass] ?? null;
        if ($commandKey === null) return null;

        return static::$commandMap[$commandKey] ?? null;
    }


    /**
     * Find a command signature to handle given command
     * @param string $command
     * @return ImplCommandSignature
     * @throws Exception
     * @internal
     */
    public static function _route(string $command) : ImplCommandSignature
    {
        if (!array_key_exists($command, static::$commandMap)) {
            throw new UnknownCommandException($command);
        }

        return static::$commandMap[$command];
    }


    /**
     * All commands
     * @return iterable<string, ImplCommandSignature>
     * @internal
     */
    public static function _all() : iterable
    {
        yield from static::$commandMap;
    }


    /**
     * Boot up
     * @return void
     * @throws Exception
     * @internal
     */
    public static function _boot() : void
    {
        $autoload = AutoloadReflection::instance();

        foreach ($autoload->expandDiscoverySourcesReflection(static::$directories) as $class) {
            // Find signature
            $signatureAttr = static::findSignatureFromAttribute($class);
            if ($signatureAttr === null) continue;

            // Find other attributes
            $descriptionAttr = static::findDescriptionFromAttribute($class);

            // Parse the signature
            $signature = ImplCommandSignature::parse($signatureAttr, $descriptionAttr);

            // Register the command
            $commandKey = $signature->command;
            if (array_key_exists($commandKey, static::$commandMap)) {
                $existingSignature = static::$commandMap[$commandKey];
                if ($existingSignature->payloadClassName !== $class->name) throw new ConflictException();
            } else {
                $signature->payloadClassName = $class->name;
                static::$commandMap[$commandKey] = $signature;
                static::$commandClasses[$class->name] = $commandKey;
            }
        }

        ksort(static::$commandMap);
    }


    /**
     * Discover all schedule definitions
     * @return iterable<ScheduleDefinition>
     * @throws Exception
     * @internal
     */
    public static function _getScheduleDefinitions() : iterable
    {
        $autoload = AutoloadReflection::instance();

        foreach ($autoload->expandDiscoverySourcesReflection(static::$directories) as $class) {
            $className = $class->name;
            if (!is_subclass_of($className, CommandSchedulable::class)) continue;

            yield from $className::getSchedules();
        }
    }


    /**
     * Find signature
     * @param ReflectionClass $class
     * @return string|null
     */
    protected static function findSignatureFromAttribute(ReflectionClass $class) : ?string
    {
        $attribute = iter_first($class->getAttributes(CommandSignature::class));
        return $attribute?->newInstance()?->signature ?? null;
    }


    /**
     * Find description
     * @param ReflectionClass $class
     * @return Localizable|string|null
     */
    protected static function findDescriptionFromAttribute(ReflectionClass $class) : Localizable|string|null
    {
        $localeAttribute = iter_first($class->getAttributes(CommandDescriptionL::class));
        if ($localeAttribute !== null) {
            return I18n::tag($localeAttribute->newInstance()->desc, $class->name);
        }

        $attribute = iter_first($class->getAttributes(CommandDescription::class));
        return $attribute?->newInstance()?->desc ?? null;
    }
}