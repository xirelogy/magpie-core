<?php

namespace Magpie\General\IOs;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamWriteable;
use Magpie\General\Concepts\TargetReadable;
use Magpie\General\Concepts\TargetWritable;
use Magpie\General\Contexts\ScopedCollection;
use Magpie\General\Traits\StaticClass;
use Throwable;

/**
 * Stream IO support
 */
class IoStream
{
    use StaticClass;


    /**
     * Copy from source to target
     * @param StreamReadable|TargetReadable $source
     * @param StreamWriteable|TargetWritable $target
     * @return void
     * @throws SafetyCommonException
     * @throws StreamException
     */
    public static function copy(StreamReadable|TargetReadable $source, StreamWriteable|TargetWritable $target) : void
    {
        $allScopes = [];

        // Convert source if required
        if ($source instanceof TargetReadable) {
            foreach ($source->getScopes() as $scope) {
                $allScopes[] = $scope;
            }
            $source = $source->createStream();
        }

        // Convert target if required
        if ($target instanceof TargetWritable) {
            foreach ($target->getScopes() as $scope) {
                $allScopes[] = $scope;
            }
            $target = $target->createStream();
        }

        // Execute in scope
        $allScopes = new ScopedCollection($allScopes);
        try {
            $allScopes->run(function () use ($source, $target) {
                static::copyStream($source, $target);
            });
        } catch (SafetyCommonException|StreamException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new UnexpectedException(previous: $ex);
        }
    }


    /**
     * Copy from source stream to target stream
     * @param StreamReadable $source
     * @param StreamWriteable $target
     * @return void
     * @throws SafetyCommonException
     * @throws StreamException
     */
    protected static function copyStream(StreamReadable $source, StreamWriteable $target) : void
    {
        while ($source->hasData()) {
            $chunk = $source->read();

            // Write, until whole chunk is written
            while (true) {
                $written = $target->write($chunk);
                if ($written >= strlen($chunk)) break;
                $chunk = substr($chunk, $written);
            }
        }

        // Close after write
        $target->close();
    }
}