<?php

namespace Magpie\HttpServer\Cookies;

use Magpie\HttpServer\Request;

/**
 * Delete a cookie
 */
class DeleteCookie extends CookieSpec
{
    /**
     * @inheritDoc
     */
    protected function onRender(?Request $request) : void
    {
        $options = $this->_createCookieOptions($request);
        $options['expires'] = 1;

        setrawcookie($this->name, '0', $options);
    }


    /**
     * Create an instance
     * @param string $name
     * @return static
     */
    public static function for(string $name) : static
    {
        return new static($name);
    }
}