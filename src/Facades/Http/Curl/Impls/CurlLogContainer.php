<?php

namespace Magpie\Facades\Http\Curl\Impls;

use Magpie\Exceptions\OperationFailedException;
use Magpie\General\IOs\TemporaryWriteStream;
use Magpie\General\Sugars\Excepts;
use Magpie\General\TextContent;
use Magpie\Logs\Concepts\Loggable;

/**
 * Log container for CURL
 * @internal
 */
class CurlLogContainer
{
    /**
     * @var Loggable Associated logger
     */
    protected readonly Loggable $logger;
    /**
     * @var TemporaryWriteStream Temporary stream
     */
    protected readonly TemporaryWriteStream $stream;
    /**
     * @var bool If container already finalized
     */
    protected bool $isFinalized = false;


    /**
     * Constructor
     * @param Loggable $logger
     * @throws OperationFailedException
     */
    public function __construct(Loggable $logger)
    {
        $this->logger = $logger;
        $this->stream = TemporaryWriteStream::create();
    }


    /**
     * Handle for use
     * @return resource
     */
    public function getHandle() : mixed
    {
        return $this->stream->getResourceHandle();
    }


    /**
     * Finalize the container
     * @return void
     */
    public function finalize() : void
    {
        if ($this->isFinalized) return;
        $this->isFinalized = true;

        Excepts::noThrow(function () {
            $readStream = $this->stream->finalize();
            foreach (TextContent::getStreamRows($readStream) as $row) {
                $this->logger->debug($row);
            }
        });
    }
}