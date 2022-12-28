<?php

namespace Magpie\General\Simples;

use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\General\Traits\StaticClass;

/**
 * Encoding/decoding using URL-safe base64
 */
class SimpleUrlBase64
{
    use StaticClass;


    /**
     * Encode the binary data
     * @param string $data
     * @return string
     */
    public static function encode(string $data) : string
    {
        $ret = base64_encode($data);
        $ret = str_replace('+', '-', $ret);
        $ret = str_replace('/', '_', $ret);
        return rtrim($ret, '=');
    }


    /**
     * Decode into binary data
     * @param string $data
     * @return string
     * @throws InvalidDataFormatException
     */
    public static function decode(string $data) : string
    {
        $data = str_replace('-', '+', $data);
        $data = str_replace('_', '/', $data);
        while ((strlen($data) % 4) != 0) {
            $data .= '=';
        }

        $ret = @base64_decode($data, true);
        if ($ret === false) throw new InvalidDataFormatException();

        return $ret;
    }
}