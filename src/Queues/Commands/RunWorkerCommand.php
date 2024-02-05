<?php

namespace Magpie\Queues\Commands;

use Magpie\Codecs\Parsers\BooleanParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandOptionDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Events\ClosureEventTemporaryReceiver;
use Magpie\Events\Concepts\Eventable;
use Magpie\Events\EventDelivery;
use Magpie\Facades\Console;
use Magpie\General\DateTimes\Duration;
use Magpie\Queues\Commands\Features\QueueWorkerFeature;
use Magpie\Queues\Events\QueuedItemCompletedEvent;
use Magpie\Queues\Events\QueuedItemEvent;
use Magpie\Queues\Events\QueuedItemExceptionEvent;
use Magpie\Queues\Events\QueuedItemFailedEvent;
use Magpie\Queues\Events\QueuedItemRunningEvent;
use Magpie\Queues\Events\WorkerStartedEvent;

#[CommandSignature('queue:run-worker {--once} {--queue=} {--timeout=}')]
#[CommandDescriptionL('Run queue\'s worker')]
#[CommandOptionDescriptionL('once', 'The worker should only run once and exit')]
#[CommandOptionDescriptionL('queue', 'Target queue name')]
#[CommandOptionDescriptionL('timeout', 'Timeout in seconds to wait for job from queue')]
class RunWorkerCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $isOnce = $request->options->requires('once', BooleanParser::create());
        $queueName = $request->options->optional('queue', StringParser::create());
        $timeout = $request->options->optional('timeout', Duration::createSecondParser(), $isOnce ? 30 : 5);

        $queueReceiver = ClosureEventTemporaryReceiver::create(function (Eventable $event) : void {
            if (!$event instanceof QueuedItemEvent) return;

            $displayName = $event->getEventState()->getDisplayName();
            $showExceptionFn = function() use($event) {
                $ex = $event->getEventState()->getLastException();
                if ($ex === null) return;
                Console::error($ex->getMessage());
                Console::warning($ex->getTraceAsString());
            };

            switch ($event::class) {
                case QueuedItemRunningEvent::class:
                    Console::info(_format_safe(_l('{{0}} running...'), $displayName) ?? _l('Job running...'));
                    break;
                case QueuedItemCompletedEvent::class:
                    Console::info(_format_safe(_l('{{0}} completed'), $displayName) ?? _l('Job completed'));
                    break;
                case QueuedItemExceptionEvent::class:
                    Console::error(_format_safe(_l('{{0}} crashed with exception'), $displayName) ?? _l('Job crashed with exception'));
                    $showExceptionFn();
                    break;
                case QueuedItemFailedEvent::class:
                    Console::error(_format_safe(_l('{{0}} failed'), $displayName) ?? _l('Job failed'));
                    $showExceptionFn();
                    break;
                default:
                    break;
            }
        });

        // Indicate worker started
        EventDelivery::subscribe([
            WorkerStartedEvent::class,
        ], ClosureEventTemporaryReceiver::create(function (Eventable $event) {
            _used($event);
            Console::info(_l('Worker started'));
        }));

        QueueWorkerFeature::run($queueName, $isOnce, $timeout, $queueReceiver);
    }
}