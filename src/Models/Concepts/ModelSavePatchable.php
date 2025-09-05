<?php

namespace Magpie\Models\Concepts;

/**
 * May patch model save
 */
interface ModelSavePatchable
{
    /**
     * Notify that a model save had happened
     * @param string $tableModelClass Table 's model class name
     * @return void
     */
    public function notifySave(string $tableModelClass) : void;
}