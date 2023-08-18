<?php

namespace Magpie\System\Kernel;

use ErrorException;
use Exception;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\General\Traits\ReleaseOnDestruct;
use Magpie\General\Traits\StaticClass;
use Magpie\HttpServer\PhpResponse;
use Magpie\System\Concepts\AbnormalExitHandleable;
use Magpie\System\Impls\DefaultAbnormalExitHandle;
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

        try {
            $previousHandler = set_error_handler(function (int $errLevel, string $errMessage, string $errFile = '', int $errLine = 0) use (&$previousHandler) {
                if (error_reporting() & $errLevel) {
                    throw new ErrorException($errMessage, 0, $errLevel, $errFile, $errLine);
                }
            });
        } catch (Exception) {
            // This catch should not function, just to avoid some IDEs complaining about possible
            // exceptions thrown or uncaught exceptions
        }

        set_exception_handler(function (Throwable $ex) {
            restore_exception_handler();
            static::abnormalExit($ex);
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