<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use Magpie\General\Traits\StaticClass;

/**
 * Text utilities for OpenSSL
 * @internal
 */
class TextUtils
{
    use StaticClass;


    /**
     * State: init
     */
    private const STATE_INIT = 0;
    /**
     * State: slash
     */
    private const STATE_SLASH = 1;
    /**
     * State: slash-X
     */
    private const STATE_SLASH_X1 = 2;
    /**
     * State: slash-Xed
     */
    private const STATE_SLASH_X2 = 3;


    /**
     * Normalize text string
     * @param string $text
     * @return string
     */
    public static function normalize(string $text) : string
    {
        $ret = '';
        $state = static::STATE_INIT;
        $xBuffer = '';

        $textLength = strlen($text);
        for ($i = 0; $i < $textLength; ++$i) {

            $c = substr($text, $i, 1);

            $isReparse = true;
            while ($isReparse) {
                $isReparse = false;
                switch ($state) {
                    case static::STATE_INIT:
                        switch ($c) {
                            case '\\':
                                $state = static::STATE_SLASH;
                                break;
                            default:
                                $ret .= $c;
                                break;
                        }
                        break;

                    case static::STATE_SLASH:
                        switch ($c) {
                            case 'a':
                                $ret .= "\a";
                                $state = static::STATE_INIT;
                                break;
                            case 'r':
                                $ret .= "\r";
                                $state = static::STATE_INIT;
                                break;
                            case 'n':
                                $ret .= "\n";
                                $state = static::STATE_INIT;
                                break;
                            case 'x':
                                $state = static::STATE_SLASH_X1;
                                break;
                            case '\\':
                                $ret .= '\\';
                                $state = static::STATE_INIT;
                                break;
                        }
                        break;

                    case static::STATE_SLASH_X1:
                        $xBuffer .= $c;
                        $state = static::STATE_SLASH_X2;
                        break;

                    case static::STATE_SLASH_X2:
                        $xBuffer .= $c;
                        $ret .= static::hexChar($xBuffer);
                        $xBuffer = '';
                        $state = static::STATE_INIT;
                        break;
                }
            }
        }

        return $ret;
    }


    /**
     * Equivalent character
     * @param string $hex
     * @return string
     */
    protected static function hexChar(string $hex) : string
    {
        $n = static::hexNum($hex);
        if ($n === null) return '';

        return chr($n);
    }


    /**
     * Equivalent number
     * @param string $hex
     * @return int|null
     */
    private static function hexNum(string $hex) : ?int
    {
        $ret = 0;

        for ($i = 0; $i < strlen($hex); ++$i) {
            $c = strtolower(substr($hex, $i, 1));
            $p = strpos('0123456789abcdef', $c);
            if ($p === false) return null;

            $ret *= 16;
            $ret += $p;
        }

        return $ret;
    }
}