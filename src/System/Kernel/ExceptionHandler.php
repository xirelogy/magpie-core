<?php

namespace Magpie\System\Kernel;

use ErrorException;
use Exception;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Contexts\ClosureScoped;
use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\General\Traits\ReleaseOnDestruct;
use Magpie\General\Traits\StaticClass;
use Magpie\HttpServer\PhpResponse;
use Magpie\System\Concepts\AbnormalExitHandleable;
use Magpie\System\Concepts\SysErrorHandleable;
use Throwable;

/**
 * System exception handler
 */
class ExceptionHandler
{
    use StaticClass;

    /**
     * @var bool If booted up
     */
    protected static bool $isBoot = false;
    /**
     * @var Releasable|null Old error handler
     */
    protected static ?Releasable $errorHandle = null;
    /**
     * @var AbnormalExitHandleable|null Specific abnormal handle
     */
    protected static ?AbnormalExitHandleable $abnormalHandle = null;


    /**
     * Boot up
     * @return void
     * @internal
     */
    public static function _boot() : void
    {
        if (static::$isBoot) return;
        static::$isBoot = true;

        static::$errorHandle = static::createSysErrorHandleInScope(new class implements SysErrorHandleable {
            /**
             * @inheritDoc
             */
            public function onError(int $errNo, string $errStr, string $errFile, int $errLine) : bool
            {
                if (error_reporting() & $errNo) {
                    throw new ErrorException($errStr, 0, $errNo, $errFile, $errLine);
                }
                return true;
            }
        });

        set_exception_handler(function (Throwable $ex) {
            restore_exception_handler();
            static::abnormalExit($ex);
        });
    }


    /**
     * Set the system error handler (PHP's native error handler) in scope
     * @param SysErrorHandleable|null $handler
     * @return Releasable
     */
    public static function createSysErrorHandleInScope(?SysErrorHandleable $handler) : Releasable
    {
        /** @var callable|null $previousHandler */
        $previousHandler = null;

        return ClosureScoped::create(function () use (&$previousHandler, $handler) {
            $previousHandler = set_error_handler($handler ? $handler->onError(...) : null);
        }, function () use (&$previousHandler) {
            set_error_handler($previousHandler);
        });
    }


    /**
     * Set the error level in scope
     * @param int $level
     * @return Releasable
     */
    public static function setScopeErrorLevel(int $level = E_ALL) : Releasable
    {
        $oldLevel = error_reporting($level);

        return new class($oldLevel) implements Releasable {
            use ReleaseOnDestruct;


            /**
             * Constructor
             * @param int $oldLevel
             * @param bool $isReleased
             */
            public function __construct(
                protected int $oldLevel,
                protected bool $isReleased = false,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function release() : void
            {
                if ($this->isReleased) return;
                $this->isReleased = true;

                error_reporting($this->oldLevel);
            }
        };
    }


    /**
     * Cause a critical error
     * @param Throwable|string $cause
     * @return never
     */
    public static function systemCritical(Throwable|string $cause) : never
    {
        $ex = $cause instanceof Throwable ? $cause : new Exception($cause);

        static::abnormalExit($ex);
    }


    /**
     * Ignore exception but leave a warning
     * @param string $sourceClassName
     * @param string $source
     * @param Throwable $ex
     * @param bool $isShowTrace
     * @return void
     */
    public static function ignoredAndWarn(string $sourceClassName, string $source, Throwable $ex, bool $isShowTrace = true) : void
    {
        if (!Kernel::hasCurrent()) return;

        $logger = Kernel::current()->getLogger();

        $logger->warning("In $sourceClassName::$source, ignored exception: " . $ex->getMessage());
        if ($isShowTrace) $logger->warning("Exception trace:\n" . $ex->getTraceAsString());
    }


    /**
     * Handle exception
     * @param Exception $ex
     * @return void
     */
    public static function handle(Exception $ex) : void
    {
        static::abnormalExit($ex);
    }


    /**
     * Specify abnormal exit handler
     * @param AbnormalExitHandleable $handle
     * @return void
     */
    public static function handleAbnormalExitUsing(AbnormalExitHandleable $handle) : void
    {
        static::$abnormalHandle = $handle;
    }


    /**
     * Exit abnormally
     * @param Exception|null $ex
     * @return never
     */
    protected static function abnormalExit(?Throwable $ex = null) : never
    {
        if (Kernel::getEntranceContext() === 'web') {
            PhpResponse::httpResponseCode(CommonHttpStatusCode::INTERNAL_SERVER_ERROR);
        }

        $handle = static::$abnormalHandle ?? new DefaultAbnormalExitHandle();
        $handle->handleAbnormalExit($ex, static::isDebug());

        exit();
    }


    /**
     * If currently in debug mode
     * @return bool
     */
    public static function isDebug() : bool
    {
        $ret = env('APP_DEBUG', false);
        return ($ret === true); // Only 'true' when 'true'
    }
}