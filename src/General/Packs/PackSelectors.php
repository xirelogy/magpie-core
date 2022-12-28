<?php

namespace Magpie\General\Packs;

use Magpie\General\Concepts\PackSelectConditionable;
use Magpie\General\Concepts\PackSelectEnumerable;
use Magpie\General\Concepts\PackSelectModifiable;

/**
 * Collection of pack selectors
 */
class PackSelectors implements PackSelectConditionable, PackSelectEnumerable, PackSelectModifiable
{
    /**
     * @var array<string, string> A map of selected selectors
     */
    private array $selectors = [];


    /**
     * Constructor
     */
    public function __construct(?string $selectorSpec = null)
    {
        $this->acceptSelectorSpec($selectorSpec);
    }


    /**
     * @inheritDoc
     */
    public function isAnySelected(string ...$selectors) : bool
    {
        foreach ($selectors as $selector) {
            if (array_key_exists($selector, $this->selectors)) return true;
        }

        return false;
    }


    /**
     * @inheritDoc
     */
    public function isNoneSelected(string ...$selectors) : bool
    {
        return !$this->isAnySelected(...$selectors);
    }


    /**
     * @inheritDoc
     */
    public function getSelectors() : iterable
    {
        foreach ($this->selectors as $selector) {
            yield $selector;
        }
    }


    /**
     * @inheritDoc
     */
    public function select(?string ...$selectorSpecs) : static
    {
        foreach ($selectorSpecs as $selectorSpec) {
            $this->acceptSelectorSpec($selectorSpec);
        }

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function selectFrom(?PackSelectEnumerable $container) : static
    {
        if ($container !== null) {
            foreach ($container->getSelectors() as $selector) {
                $this->select($selector);
            }
        }

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function propagate(PackSelectConditionable $container, string ...$selectors) : static
    {
        foreach ($selectors as $selector) {
            if (!$container->isAnySelected($selector)) continue;
            $this->select($selector);
        }

        return $this;
    }


    /**
     * Accept selector specification into the map
     * @param string|null $selectorSpec
     * @return void
     */
    private function acceptSelectorSpec(?string $selectorSpec) : void
    {
        if ($selectorSpec === null) return;

        foreach (explode(',', $selectorSpec) as $selector) {
            $this->selectors[$selector] = $selector;
        }
    }
}