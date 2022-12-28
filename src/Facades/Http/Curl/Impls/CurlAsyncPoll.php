<?php

namespace Magpie\Facades\Http\Curl\Impls;

use CurlHandle;
use CurlMultiHandle;
use Exception;
use Fiber;
use Magpie\Codecs\ParserHosts\ArrayParserHost;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Facades\Random;
use Magpie\General\DateTimes\Duration;
use Magpie\General\DateTimes\Specific\DurationInMicroseconds;
use Magpie\General\Randoms\RandomCharset;
use Magpie\General\Traits\SingletonInstance;
use Magpie\System\Concepts\MainLoopPollable;
use Magpie\System\Kernel\EasyFiber;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\MainLoop;
use function curl_getinfo;
use function curl_multi_add_handle;
use function curl_multi_exec;
use function curl_multi_info_read;
use function curl_multi_init;
use function curl_multi_select;

/**
 * Poll for asynchronous events
 * @internal
 */
class CurlAsyncPoll implements MainLoopPollable
{
    use SingletonInstance;

    /**
     * @var CurlMultiHandle Associated handle
     */
    protected readonly CurlMultiHandle $mh;
    /**
     * @var array<CurlHandle> Currently interested CURL handles
     */
    protected array $handles = [];
    /**
     * @var array<string, Fiber> Outgoing receiver fibers
     */
    protected array $fibers = [];
    /**
     * @var bool If registered
     */
    protected bool $isRegistered = false;


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->mh = curl_multi_init();
    }


    /**
     * Execute asynchronously on the given handle (like curl_exec)
     * @param CurlHandle $ch
     * @param Fiber $fiber
     * @return string
     * @throws Exception
     */
    public function asyncExec(CurlHandle $ch, Fiber $fiber) : string
    {
        // Setup handle key
        $chKey = Random::string(6, RandomCharset::LOWER_ALPHANUM);
        CurlSafeUtils::setOpt($ch, CURLOPT_PRIVATE, $chKey);

        // Make association between the handles and fibers
        curl_multi_add_handle($this->mh, $ch);
        $this->fibers[$chKey] = $fiber;
        $this->handles[] = $ch;

        // Register to the event loop
        if (!$this->isRegistered) {
            MainLoop::registerPoll($this);
            $this->isRegistered = true;
        }

        // Suspend the current fiber
        return EasyFiber::suspend();
    }


    /**
     * @inheritDoc
     */
    public function getPriority() : int
    {
        return MainLoop::PRIORITY_IO;
    }


    /**
     * @inheritDoc
     */
    public function isSupportIdle() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function poll(?Duration $idle) : iterable
    {
        // Call curl_multi_exec()
        do {
            $status = curl_multi_exec($this->mh, $isRunning);
        } while ($isRunning && $status === CURLM_CALL_MULTI_PERFORM);

        // Determine what kind of select() is required
        if ($isRunning) {
            if ($idle !== null) {
                // Perform the idle select
                $idleUSec = $idle->getValueAtPrecisionScale(DurationInMicroseconds::SCALE);
                if ($idleUSec < 100) $idleUSec = 100;   // Force a healthy value

                if (curl_multi_select($this->mh, floatval($idleUSec) / 1000000) === -1) {
                    usleep($idleUSec);
                }
            } else {
                // Otherwise non-idle
                if (curl_multi_select($this->mh, 0) === -1) {
                    usleep(1);
                }
            }
        }

        // Process the messages
        for (;;) {
            $info = curl_multi_info_read($this->mh);
            if ($info === false) break;

            $dispatch = $this->safeTranslateMessage($info);
            if ($dispatch !== null) yield $dispatch;
        }

        // Deregister when no longer running
        if (!$isRunning) {
            MainLoop::deregisterPoll($this);
            $this->isRegistered = false;
        }
    }


    /**
     * Translate a message without throwing exception
     * @param array $info
     * @return CurlAsyncCompletedDispatch|null
     */
    protected function safeTranslateMessage(array $info) : ?CurlAsyncCompletedDispatch
    {
        try {
            return $this->translateMessage($info);
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Translate a message
     * @param array $info
     * @return CurlAsyncCompletedDispatch|null
     * @throws ArgumentException
     * @throws UnsupportedException
     */
    protected function translateMessage(array $info) : ?CurlAsyncCompletedDispatch
    {
        $parser = new ArrayParserHost($info);

        $msg = $parser->requires('msg', IntegerParser::create());
        $result = $parser->requires('result', IntegerParser::create());
        $ch = $parser->optional('handle');

        // When there is no handle, it is probably cancelled
        if ($ch === null) return null;

        // Anything other than CURLMSG_DONE is not expected
        if ($msg !== CURLMSG_DONE) throw new UnsupportedValueException($msg, 'msg');

        // Try to get the corresponding fiber
        $chKey = curl_getinfo($ch, CURLINFO_PRIVATE);
        if ($chKey === null) return null;
        if (!array_key_exists($chKey, $this->fibers)) return null;

        $fiber = $this->fibers[$chKey];
        unset($this->fibers[$chKey]);

        return new CurlAsyncCompletedDispatch($this->mh, $result, $ch, $fiber);
    }
}