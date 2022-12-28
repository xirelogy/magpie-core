<?php

namespace Magpie\Facades\Mutex;

/**
 * Mutex that can be only satisfied when all relevant mutexes are acquired
 */
class AllMutexes extends Mutex
{
    /**
     * @var array<Mutex> Relevant mutex
     */
    protected readonly array $mutexes;


    /**
     * Constructor
     * @param iterable<Mutex> $mutexes
     */
    protected function __construct(iterable $mutexes)
    {
        parent::__construct();

        $this->mutexes = iter_flatten($mutexes, false);
    }


    /**
     * @inheritDoc
     */
    public function getMutexKey() : string
    {
        return '';  // Purposely invalid value
    }


    /**
     * @inheritDoc
     */
    protected function _createHandles() : array
    {
        $ret = [];

        foreach ($this->mutexes as $mutex) {
            foreach ($mutex->_createHandles() as $handle) {
                $ret[] = $handle;
            }
        }

        return $ret;
    }


    /**
     * Create an instance
     * @param iterable<Mutex> $mutexes
     * @return static
     */
    public static function create(iterable $mutexes) : static
    {
        return new static($mutexes);
    }
}