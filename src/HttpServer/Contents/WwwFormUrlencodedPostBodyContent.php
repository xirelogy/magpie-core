<?php

namespace Magpie\HttpServer\Contents;

use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * Handle application/x-www-form-urlencoded POST body content
 */
#[FactoryTypeClass(WwwFormUrlencodedPostBodyContent::CONTENT_TYPE, PostBodyContent::class)]
class WwwFormUrlencodedPostBodyContent extends PostBodyContent
{
    /**
     * Current content type
     */
    public const CONTENT_TYPE = 'application/x-www-form-urlencoded';


    /**
     * @inheritDoc
     */
    protected function onGetVariables() : iterable
    {
        foreach (explode('&', $this->body) as $pair) {
            $equalPos = strpos($pair, '=');
            if ($equalPos !== false) {
                $key = static::decodeKey(substr($pair, 0, $equalPos));
                if ($key === '') continue;
                $value = static::decodeValue(substr($pair, $equalPos + 1));
                yield $key => $value;
            } else {
                $key = static::decodeKey($pair);
                if ($key === '') continue;
                yield $key => '';
            }
        }
    }


    /**
     * Decode key
     * @param string $text
     * @return string
     */
    protected static function decodeKey(string $text) : string
    {
        return urldecode($text);
    }


    /**
     * Decode value
     * @param string $text
     * @return string
     */
    protected static function decodeValue(string $text) : string
    {
        return urldecode($text);
    }
}