<?php

namespace Magpie\Schedules\Impls;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Cron\CronExpression;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\General\Sugars\Excepts;
use Magpie\System\Concepts\SourceCacheTranslatable;

/**
 * A scheduled entry
 */
class ScheduledEntry implements SourceCacheTranslatable
{
    /**
     * @var string Cron expression
     */
    public readonly string $cronExpression;
    /**
     * @var string|null Specific timezone to evaluate current definition
     */
    public readonly ?string $timezone;
    /**
     * @var ScheduleRunner Target to run for current entry
     */
    public readonly ScheduleRunner $runner;
    /**
     * @var bool If running in background
     */
    public readonly bool $isRunInBackground;


    /**
     * Constructor
     * @param string $cronExpression
     * @param string|null $timezone
     * @param ScheduleRunner $runner
     * @param bool $isRunInBackground
     */
    public function __construct(string $cronExpression, ?string $timezone, ScheduleRunner $runner, bool $isRunInBackground)
    {
        $this->cronExpression = $cronExpression;
        $this->timezone = $timezone;
        $this->runner = $runner;
        $this->isRunInBackground = $isRunInBackground;
    }


    /**
     * @inheritDoc
     */
    public function sourceCacheExport() : array
    {
        return [
            'cronExpression' => $this->cronExpression,
            'timezone' => $this->timezone,
            'runner' => $this->runner->sourceCacheExport(),
            'isRunInBackground' => $this->isRunInBackground,
        ];
    }


    /**
     * If the entry is due to run now
     * @param CarbonInterface|null $refTime
     * @return bool
     */
    public function isDue(?CarbonInterface $refTime) : bool
    {
        $refTime = $refTime ?? Carbon::now();
        $refTime = $refTime->toImmutable();

        $timezone = $this->timezone ?? SystemTimezone::default();

        $cron = new CronExpression($this->cronExpression);
        return $cron->isDue($refTime, $timezone);
    }


    /**
     * Determine the next run time
     * @param CarbonInterface|null $refTime
     * @return CarbonImmutable|null
     */
    public function getNextRunTime(?CarbonInterface $refTime) : ?CarbonImmutable
    {
        $refTime = $refTime ?? Carbon::now();
        $refTime = $refTime->toImmutable();

        $timezone = $this->timezone ?? SystemTimezone::default();

        $cron = new CronExpression($this->cronExpression);
        $ret = Excepts::noThrow(fn () => $cron->getNextRunDate($refTime, timeZone: $timezone));
        if ($ret === null) return null;

        return Carbon::parse($ret)->toImmutable();
    }


    /**
     * @inheritDoc
     */
    public static function sourceCacheImport(array $data) : static
    {
        $runner = ScheduleRunner::sourceCacheImport($data['runner']);

        return new static(
            $data['cronExpression'],
            $data['timezone'],
            $runner,
            $data['isRunInBackground'],
        );
    }
}