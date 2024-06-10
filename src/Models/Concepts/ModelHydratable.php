<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;

/**
 * May hydrate a model value
 * @template T
 */
interface ModelHydratable
{
    /**
     * Hydrate using specific model values
     * @param array $values
     * @return mixed
     * @throws SafetyCommonException
     */
    public function hydrate(array $values) : mixed;
}