<?php

namespace Magpie\Tasks;

use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * Task execution result
 * @template T
 */
abstract class TaskResult implements Packable
{
    use CommonPackable;


    /**
     * @var bool If the task is executed successfully
     */
    public readonly bool $isSuccess;


    /**
     * Constructor
     * @param bool $isSuccess
     */
    protected function __construct(bool $isSuccess)
    {
        $this->isSuccess = $isSuccess;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->isSuccess = $this->isSuccess;
    }
}