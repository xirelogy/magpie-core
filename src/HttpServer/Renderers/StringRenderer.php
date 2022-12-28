<?php

namespace Magpie\HttpServer\Renderers;

use Magpie\HttpServer\CommonRenderable;

/**
 * A renderer that treats string as HTML content (ad verbatim)
 */
class StringRenderer extends CommonRenderable
{
    /**
     * @var string Text to be rendered
     */
    protected string $text;


    /**
     * Constructor
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }


    /**
     * @inheritDoc
     */
    protected function onRender() : void
    {
        echo $this->text;
    }
}