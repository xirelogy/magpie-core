<?php

namespace Magpie\Tasks;

use Magpie\General\Packs\PackContext;

/**
 * Task result representing success
 * @extends TaskResult<T>
 * @template T
 */
class SuccessTaskResult extends TaskResult
{
    /**
     * @var T The result payload of the task execution
     */
    public readonly mixed $result;


    /**
     * Constructor
     * @param T $result
     */
    public function __construct(mixed $result)
    {
        parent::__construct(true);

        $this->result = $result;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->result = $this->result;
    }
}