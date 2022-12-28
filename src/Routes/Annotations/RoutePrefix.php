<?php

namespace Magpie\Routes\Annotations;

use Attribute;

/**
 * Route prefix to be applied to all entries within the class
 */
#[Attribute(Attribute::TARGET_CLASS)]
class RoutePrefix
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