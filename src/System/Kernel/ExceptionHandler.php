<?php

namespace Magpie\System\Kernel;

use ErrorException;
use Exception;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Traits\ReleaseOnDestruct;
use Magpie\General\Traits\StaticClass;
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
            http_response_code(500);
        }

        // FIXME
        if ($ex) dd($ex);


        $message = $ex?->getMessage() ?? 'Server error';

        echo "$message\n";

        exit();
    }
}