<?php

namespace Magpie\HttpServer;

use Magpie\Codecs\ParserHosts\ArrayCollection;
use Magpie\System\Concepts\Capturable;

/**
 * PHP server arguments collection
 */
class ServerCollection extends ArrayCollection implements Capturable
{
    /**
     * Constructor
     * @param iterable<string, mixed> $keyValues
     * @param string|null $prefix
     */
    protected function __construct(iterable $keyValues, ?string $prefix = null)
    {
        parent::__construct(iter_flatten($keyValues), $prefix);

        $this->argType = _l('server variable');
    }



    /**
     * Get headers collection
     * @return HeaderCollection
     */
    public function getHeaders() : HeaderCollection
    {
        $headers = function () : iterable {
            foreach ($this->all() as $key => $value) {
                if (!str_starts_with($key, 'HTTP_')) continue;
                yield substr($key, 5) => $value;
            }
        };

        return new class($headers()) extends HeaderCollection {
            /**
             * Constructor
             * @param iterable<string, mixed> $keyValues
             * @param string|null $prefix
             */
            public function __construct(iterable $keyValues, ?string $prefix = null)
            {
                parent::__construct($keyValues, $prefix);
            }
        };
    }


    /**
     * @inheritDoc
     */
    public static function capture() : static
    {
        return new static($_SERVER);
    }
}