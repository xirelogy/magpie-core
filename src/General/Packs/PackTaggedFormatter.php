<?php

namespace Magpie\General\Packs;

use Iterator;
use Magpie\Codecs\Concepts\CustomFormattable;
use Magpie\General\Concepts\Packable;
use Magpie\General\Concepts\PackSelectConditionable;
use Magpie\General\Concepts\PackSelectEnumerable;
use Magpie\General\Concepts\PackSelectModifiable;

/**
 * Tag a pack target with specific selections
 */
class PackTaggedFormatter implements CustomFormattable, PackSelectModifiable
{
    /**
     * @var mixed Target value (that was tagged)
     */
    public readonly mixed $target;
    /**
     * @var PackSelectors Currently effective selectors
     */
    protected PackSelectors $selectors;


    /**
     * Constructor
     * @param mixed $target
     * @param array<string|null> $selectorSpecs
     */
    protected function __construct(mixed $target, array $selectorSpecs)
    {
        $this->target = $target;
        $this->selectors = new PackSelectors();
        $this->selectors->select(...$selectorSpecs);
    }


    /**
     * @inheritDoc
     */
    public function select(?string ...$selectorSpecs) : static
    {
        $this->selectors->select(...$selectorSpecs);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function selectFrom(?PackSelectEnumerable $container) : static
    {
        $this->selectors->selectFrom($container);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function propagate(PackSelectConditionable $container, string ...$selectors) : static
    {
        $this->selectors->propagate($container, ...$selectors);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function format(?string $formatterClass = null, ?PackSelectEnumerable $packSelectors = null) : mixed
    {
        // Null exit
        if ($this->target === null) return null;

        // Handle Packable directly
        if ($this->target instanceof Packable) {
            return PackableFormatter::safePack($this->target, $packSelectors, ...$this->flattenSelectors());
        }

        // Handle any arrays by expanding it
        if (is_array($this->target) || ($this->target instanceof Iterator)) {
            $ret = [];
            foreach ($this->target as $subTarget) {
                $ret[] = static::for($subTarget, ...$this->flattenSelectors());
            }
            return $ret;
        }

        // Otherwise, pass-thru
        return $this->target;
    }


    /**
     * Flatten current selectors
     * @return array<string>
     */
    private function flattenSelectors() : array
    {
        return iter_flatten($this->selectors->getSelectors());
    }


    /**
     * Tag the target with given selectors
     * @param mixed $target
     * @param string|null ...$selectorSpecs
     * @return static
     */
    public static function for(mixed $target, ?string... $selectorSpecs) : static
    {
        return new static($target, $selectorSpecs);
    }
}