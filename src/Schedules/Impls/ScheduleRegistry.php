<?php

namespace Magpie\Schedules\Impls;

use Exception;
use Magpie\Commands\CommandRegistry;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Concepts\SourceCacheable;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\HardCore\SourceCache;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * Registry of all schedules
 * @internal
 */
class ScheduleRegistry implements SystemBootable, SourceCacheable
{
    use StaticClass;


    /**
     * All schedule entries
     * @return iterable<ScheduledEntry>
     * @throws Exception
     */
    public static function getEntries() : iterable
    {
        $cachedEntries = SourceCache::instance()->getCache(static::class);
        if (is_array($cachedEntries)) {
            foreach ($cachedEntries as $cachedEntry) {
                yield ScheduledEntry::sourceCacheImport($cachedEntry);
            }
            return;
        }

        yield from static::compileEntries();
    }


    /**
     * Compile all schedule entries
     * @return iterable<ScheduledEntry>
     * @throws Exception
     */
    protected static function compileEntries() : iterable
    {
        foreach (CommandRegistry::_getScheduleDefinitions() as $schedule) {
            yield $schedule->_compile();
        }
    }


    /**
     * @inheritDoc
     */
    public static function saveSourceCache() : void
    {
        $outEntries = [];
        foreach (static::compileEntries() as $entry) {
            $outEntries[] = $entry->sourceCacheExport();
        }

        SourceCache::instance()->setCache(static::class, $outEntries);
    }


    /**
     * @inheritDoc
     */
    public static function deleteSourceCache() : void
    {
        SourceCache::instance()->deleteCache(static::class);
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        CommandRegistry::includeDirectory(__DIR__ . '/../Commands');
        ClassFactory::includeDirectory(__DIR__ . '/../Impls');
    }
}