<?php

namespace Magpie\General\Traits;

use Exception;
use Magpie\General\Concepts\PackSelectEnumerable;
use Magpie\General\Packs\PackContext;

/**
 * Common packable implementation
 */
trait CommonPackable
{
    /**
     * Pack into object
     * @param PackSelectEnumerable|null $globalSelectors Global selectors to be applied and transferred during pack
     * @param string|null ...$selectorSpecs Specific selections to be applied during this pack
     * @return object
     * @throws Exception
     */
    public function pack(?PackSelectEnumerable $globalSelectors = null, ?string... $selectorSpecs) : object
    {
        $ret = obj();

        $context = new class($ret, $globalSelectors, ...$selectorSpecs) extends PackContext {
            /**
             * Constructor
             * @param object $ret
             * @param PackSelectEnumerable|null $globalSelectors
             * @param string|null ...$localSelectorSpecs
             */
            public function __construct(object $ret, ?PackSelectEnumerable $globalSelectors, ?string... $localSelectorSpecs)
            {
                parent::__construct($ret, $globalSelectors, ...$localSelectorSpecs);
            }
        };

        $this->onPack($ret, $context);

        return $ret;
    }


    /**
     * Pack into object
     * @param object $ret Target to pack into
     * @param PackContext $context Associated pack context
     * @return void
     * @throws Exception
     */
    protected abstract function onPack(object $ret, PackContext $context) : void;
}