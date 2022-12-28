<?php

namespace Magpie\Codecs\Concepts;

/**
 * Implement this interface to provide pretty formatting (for PrettyGeneralFormatter)
 */
interface PrettyFormattable
{
    /**
     * Apply pretty formatting
     * @return mixed
     */
    public function prettyFormat() : mixed;
}