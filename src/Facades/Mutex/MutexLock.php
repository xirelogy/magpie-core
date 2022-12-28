<?php

namespace Magpie\Facades\Mutex;

use Magpie\General\Concepts\Releasable;
use Magpie\General\Traits\ReleaseOnDestruct;

/**
 * A mutex lock
 */
abstract class MutexLock implements Releasable
{
    use ReleaseOnDestruct;
}