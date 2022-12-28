<?php

namespace Magpie\HttpServer;

use Magpie\System\Concepts\Capturable;

class ServerCollection extends Collection implements Capturable
{
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