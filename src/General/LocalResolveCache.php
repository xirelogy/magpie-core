<?php

namespace Magpie\General;

use Closure;
use Magpie\General\Sugars\Excepts;

/**
 * A cache for resolvable items (locally)
 * @template KT
 * @template VT
 */
class LocalResolveCache
{
    /**
     * @var array<KT, VT|null> Cached items
     */
    protected array $map = [];
    /**
     * @var Closure Resolver function
     */
    protected readonly Closure $resolveFn;
    /**
     * @var Closure|null Accept key function
     */
    protected readonly ?Closure $acceptKeyFn;


    /**
     * Constructor
     * @param callable(KT):VT|null $resolveFn
     * @param callable(mixed, mixed|null):KT|null $acceptKeyFn
     * @noinspection PhpDocSignatureInspection
     */
    protected function __construct(callable $resolveFn, ?callable $acceptKeyFn)
    {
        $this->resolveFn = $resolveFn;
        $this->acceptKeyFn = $acceptKeyFn;
    }


    /**
     * Resolve for the corresponding key
     * @param KT $key
     * @param mixed|null $context
     * @return VT|null
     */
    public function resolve(mixed $key, mixed $context = null) : mixed
    {
        if ($this->acceptKeyFn !== null) $key = ($this->acceptKeyFn)($key);

        if (!array_key_exists($key, $this->map)) {
            $value = Excepts::noThrow(fn () => ($this->resolveFn)($key, $context));
            $this->map[$key] = $value;
        }

        return $this->map[$key];
    }


    /**
     * Create a new instance
     * @param callable(KT):VT|null $resolveFn
     * @param callable(mixed, mixed|null):KT|null $acceptKeyFn
     * @return static
     * @noinspection PhpDocSignatureInspection
     */
    public static function create(callable $resolveFn, ?callable $acceptKeyFn = null) : static
    {
        return new static($resolveFn, $acceptKeyFn);
    }
}