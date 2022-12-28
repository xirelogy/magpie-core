<?php

namespace Magpie\Models\Concepts;

use Magpie\Models\Model;

interface ModelAccessible
{
    /**
     * All associated models
     * @return iterable<string, Model>
     */
    public function getModels() : iterable;


    /**
     * Access to specific model according to given table class name
     * @param string $tableClassName
     * @return Model|null
     */
    public function accessModel(string $tableClassName) : ?Model;


    /**
     * Access to all models according to given table class names
     * @param string ...$tableClassNames
     * @return array<Model|null>
     */
    public function accessModels(string ...$tableClassNames) : array;
}