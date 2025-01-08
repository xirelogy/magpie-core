<?php

namespace Magpie\Routes\Annotations;

use Attribute;

/**
 * Route prefix to be concatenated from sections along class hierarchy and then applied to all entries within the class
 */
#[Attribute(Attribute::TARGET_CLASS)]
class RoutePrefixSection
{
    /**
     * @var string Prefix path
     */
    public readonly string $path;


    /**
     * Constructor
     * @param string $path Prefix path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }
}