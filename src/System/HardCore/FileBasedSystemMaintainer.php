<?php

namespace Magpie\System\HardCore;

use Carbon\Carbon;
use Exception;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\System\Concepts\SystemMaintainable;
use Magpie\System\Kernel\Kernel;

/**
 * System maintenance options using file
 */
class FileBasedSystemMaintainer implements SystemMaintainable
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'file';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function setMaintenanceMode(bool $isUnderMaintenance) : void
    {
        $path = static::getFilePath();

        try {
            if ($isUnderMaintenance) {
                LocalRootFileSystem::instance()->writeFile($path, Carbon::now()->getTimestamp());
            } else {
                LocalRootFileSystem::instance()->deleteFile($path);
            }
        } catch (OperationFailedException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new OperationFailedException(previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    public function isUnderMaintenance() : bool
    {
        $path = static::getFilePath();

        return LocalRootFileSystem::instance()->isFileExist($path);
    }


    /**
     * @inheritDoc
     */
    public function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(SystemMaintainable::class, $this);
    }


    /**
     * Associated file path
     * @return string
     */
    protected static function getFilePath() : string
    {
        return project_path('/storage/caches/magpie/under-maintenance');
    }
}