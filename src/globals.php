<?php

use Magpie\Codecs\Formats\PrettyGeneralFormatter;
use Magpie\Configurations\Env;
use Magpie\Exceptions\StringFormatterException;
use Magpie\Facades\Log;
use Magpie\General\Str;
use Magpie\General\Sugars\StringFormatter;
use Magpie\General\Sugars\StringOf;
use Magpie\HttpServer\Response;
use Magpie\Locales\Concepts\Localizable;
use Magpie\Locales\I18n;
use Magpie\System\HardCore\StackTraversal;
use Magpie\System\Kernel\Kernel;

/**
 * Mark argument(s) as used. This is commonly used to avoid some compiler or IDE
 * nagging that variables are not used.
 * @param mixed ...$args
 * @return void
 */
function _used(mixed ...$args) : void
{

}


/**
 * Helper to mark something is throwable. This is commonly used to avoid some compiler
 * or IDE nagging that exceptions are not thrown. Example: `_throwable() ?? throw new WhateverException()`
 * @param int|null $index When specified, allow multiple throwables to be specified
 * @return bool|null
 */
function _throwable(?int $index = null) : ?bool
{
    _used($index);

    return true;
}


/**
 * Get the localized string for given text
 * @param string $text
 * @param string|null $className
 * @return Localizable
 */
function _l(string $text, ?string $className = null) : Localizable
{
    $className = $className ?? StackTraversal::getLastClassName(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10));

    return I18n::tag($text, $className);
}


/**
 * Apply arguments to given format string.
 * @param string $format Format string, where each placement is marked
 *                      around two brace brackets (`{{n}}`) with a zero-based
 *                      index as payload. When index is not specified then it
 *                      will take the current brace position.
 * @param mixed ...$args Arguments
 * @return string
 * @throws StringFormatterException
 */
function _format(string $format, mixed ...$args) : string
{
    return StringFormatter::format($format, ...$args);
}


/**
 * Apply arguments to given format string, returning null when exception occurs
 * @param string $format Format string, where each placement is marked
 *                      around two brace brackets (`{{n}}`) with a zero-based
 *                      index as payload. When index is not specified then it
 *                      will take the current brace position.
 * @param mixed ...$args Arguments
 * @return string|null
 */
function _format_safe(string $format, mixed ...$args) : ?string
{
    try {
        return StringFormatter::format($format, ...$args);
    } catch (Throwable) {
        return null;
    }
}


/**
 * Construct object for given key-values
 * @param iterable<string, mixed> $values
 * @return object
 */
function obj(iterable $values = []) : object
{
    $ret = new stdClass();

    foreach ($values as $key => $value) {
        $ret->{$key} = $value;
    }

    return $ret;
}


/**
 * Log an emergency message
 * @param string|Stringable $message
 * @param array $context
 * @return void
 */
function log_emergency(string|Stringable $message, array $context = []) : void
{
    Log::emergency($message, $context);
}


/**
 * Log an alert message
 * @param string|Stringable $message
 * @param array $context
 * @return void
 */
function log_alert(string|Stringable $message, array $context = []) : void
{
    Log::alert($message, $context);
}


/**
 * Log a critical message
 * @param string|Stringable $message
 * @param array $context
 * @return void
 */
function log_critical(string|Stringable $message, array $context = []) : void
{
    Log::critical($message, $context);
}


/**
 * Log a error message
 * @param string|Stringable $message
 * @param array $context
 * @return void
 */
function log_error(string|Stringable $message, array $context = []) : void
{
    Log::error($message, $context);
}


/**
 * Log a warning message
 * @param string|Stringable $message
 * @param array $context
 * @return void
 */
function log_warning(string|Stringable $message, array $context = []) : void
{
    Log::warning($message, $context);
}


/**
 * Log a notice message
 * @param string|Stringable $message
 * @param array $context
 * @return void
 */
function log_notice(string|Stringable $message, array $context = []) : void
{
    Log::notice($message, $context);
}


/**
 * Log an info message
 * @param string|Stringable $message
 * @param array $context
 * @return void
 */
function log_info(string|Stringable $message, array $context = []) : void
{
    Log::info($message, $context);
}


/**
 * Log a debug message
 * @param string|Stringable $message
 * @param array $context
 * @return void
 */
function log_debug(string|Stringable $message, array $context = []) : void
{
    Log::debug($message, $context);
}


/**
 * Get environment variable
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function env(string $key, mixed $default = null) : mixed
{
    return Env::get($key, $default);
}


/**
 * Apply pretty format and then use `dd` to dump the values
 * @param mixed ...$args
 * @return never
 */
function dd_pretty(mixed ...$args) : never
{
    $formatter = PrettyGeneralFormatter::create();

    $outArgs = [];
    foreach ($args as $arg) {
        $outArgs[] = $formatter->format($arg);
    }

    dd(...$outArgs);
}


/**
 * Flatten any iterables/arrays into arrays
 * @param iterable $target
 * @param bool $isPreserveKeys
 * @return array
 */
function iter_flatten(iterable $target, bool $isPreserveKeys = true) : array
{
    if (is_array($target)) {
        if (!$isPreserveKeys) return array_values($target);
        return $target;
    }

    // Actual implementation
    $ret = [];
    foreach ($target as $key => $value) {
        if ($isPreserveKeys) {
            $ret[$key] = $value;
        } else {
            $ret[] = $value;
        }
    }

    return $ret;
}


/**
 * Return the first item if available
 * @template T
 * @param iterable<T> $target
 * @return T|null
 */
function iter_first(iterable $target) : mixed
{
    foreach ($target as $item) {
        return $item;
    }

    return null;
}


/**
 * Return the filtered items, removing any items with resulting null
 * @template T
 * @param iterable<T> $target
 * @param callable(T):T|null $filterFn
 * @return iterable<T>
 */
function iter_filter(iterable $target, callable $filterFn) : iterable
{
    foreach ($target as $item) {
        $newItem = $filterFn($item);
        if ($newItem === null) continue;

        yield $newItem;
    }
}


/**
 * Expand into iterable array if not yet an array
 * @template T
 * @param array<T>|T $target
 * @return iterable<T>
 */
function iter_expand(mixed $target) : iterable
{
    if (is_array($target)) return $target;
    return [$target];
}


/**
 * If string is null or empty
 * @param string|null $value
 * @return bool
 */
function is_empty_string(?string $value) : bool
{
    return Str::isNullOrEmpty($value);
}


/**
 * Project path
 * @param string $path
 * @return string
 */
function project_path(string $path) : string
{
    if (!str_starts_with($path, '/')) $path = "/$path";

    if (!Kernel::hasCurrent()) return $path;

    $prefix = Kernel::current()->projectPath;
    while (str_ends_with($prefix, '/')) {
        $prefix = substr($prefix, 0, -1);
    }

    return $prefix . $path;
}


/**
 * Create a response
 * @param string $content
 * @param int|null $httpStatusCode
 * @return Response
 */
function response(string $content = '', ?int $httpStatusCode = null) : Response
{
    return new Response($content, $httpStatusCode);
}


/**
 * Get the corresponding string representation of target
 * @param mixed $target
 * @return string
 */
function stringOf(mixed $target) : string
{
    return StringOf::target($target);
}
