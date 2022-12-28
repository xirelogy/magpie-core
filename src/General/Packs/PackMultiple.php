<?php

namespace Magpie\General\Packs;

use Magpie\General\Concepts\Packable;
use Magpie\General\Concepts\PackSelectEnumerable;

/**
 * Pack multiple targets together
 */
class PackMultiple implements Packable
{
    /**
     * @var array<Packable|object> Targets to be packed together
     */
    protected readonly array $targets;
    /**
     * @var array<string, mixed> Additional fields
     */
    protected array $fields = [];


    /**
     * Constructor
     * @param iterable<Packable|object> $targets
     */
    protected function __construct(iterable $targets)
    {
        $this->targets = iter_flatten($targets, false);
    }


    /**
     * Specify additional field and values
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public final function withField(string $name, mixed $value) : static
    {
        $this->fields[$name] = $value;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function pack(?PackSelectEnumerable $globalSelectors = null, ?string ...$selectorSpecs) : object
    {
        $ret = obj();

        foreach ($this->targets as $target) {
            while ($target instanceof Packable) {
                $target = $target->pack($globalSelectors, ...$selectorSpecs);
            }

            foreach ($target as $key => $value) {
                $ret->{$key} = $value;
            }
        }

        foreach ($this->fields as $key => $value) {
            $ret->{$key} = $value;
        }

        return $ret;
    }


    /**
     * Create an instance
     * @param Packable|object ...$targets
     * @return static
     * @noinspection PhpDocSignatureInspection
     */
    public static function create(object ...$targets) : static
    {
        return new static($targets);
    }
}