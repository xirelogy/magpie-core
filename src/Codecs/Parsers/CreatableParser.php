<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Codecs\Traits\CommonParser;
use Magpie\General\Traits\StaticCreatable;

/**
 * A parser that is creatable (created using static `create()`)
 * @template T
 * @extends Parser<T>
 */
abstract class CreatableParser implements Parser
{
    /** @use CommonParser<T> */
    use StaticCreatable;
    use CommonParser;
}