<?php

namespace Magpie\General\Concepts;

/**
 * A readable target with path
 */
interface PathTargetReadable extends TargetReadable
{
    /**
     * Corresponding path
     * @return string
     */
    public function getPath() : string;
}