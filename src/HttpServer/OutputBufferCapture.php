<?php

namespace Magpie\HttpServer;

use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Contexts\Scoped;
use Throwable;

/**
 * Support output buffer capturing
 */
class OutputBufferCapture extends Scoped
{
    /**
     * @var string|SafetyCommonException|null The captured result
     */
    protected string|SafetyCommonException|null $result = null;


    /**
     * Constructor
     * @throws SafetyCommonException
     */
    public function __construct()
    {
        if (!ob_start()) throw new OperationFailedException();
    }


    /**
     * Capture the result
     * @return string
     * @throws SafetyCommonException
     */
    public function capture() : string
    {
        $this->succeeded();
        $this->release();

        if ($this->result === null) $this->result = new OperationFailedException();
        if ($this->result instanceof SafetyCommonException) throw $this->result;

        return $this->result ?? '';
    }


    /**
     * @inheritDoc
     */
    protected function onRelease() : void
    {
        $content = ob_get_contents();
        $this->result = $content !== false ? $content : new InvalidStateException();

        ob_end_clean();
    }


    /**
     * @inheritDoc
     */
    protected function onCrash(Throwable $ex) : void
    {
        parent::onCrash($ex);

        $this->result = $ex instanceof SafetyCommonException ? $ex : new OperationFailedException(previous: $ex);
    }
}