<?php

namespace Magpie\Tasks;

use Magpie\General\Packs\PackContext;
use Throwable;

/**
 * Task result representing failure
 */
class FailedTaskResult extends TaskResult
{
    /**
     * @var Throwable Error describing the failure
     */
    public readonly Throwable $ex;


    /**
     * Constructor
     * @param Throwable $ex
     */
    public function __construct(Throwable $ex)
    {
        parent::__construct(false);

        $this->ex = $ex;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->ex = $this->ex;
    }
}