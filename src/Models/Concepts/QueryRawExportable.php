<?php

namespace Magpie\Models\Concepts;

use Magpie\Models\RawStatement;

/**
 * May export the query content as raw statement
 */
interface QueryRawExportable
{
    /**
     * Export as raw statement
     * @return RawStatement
     */
    public function exportRaw() : RawStatement;
}