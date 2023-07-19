<?php

namespace Magpie\Codecs\Traits;

use Exception;
use Magpie\Codecs\Impls\ParserArgTypeContext;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\InvalidArgumentException;
use Magpie\Exceptions\ParseFailedException;

/**
 * Commonly handled parser
 * @template T
 */
trait CommonParser
{
    /**
     * Parse given value
     * @param mixed $value
     * @param string|null $hintName
     * @return T
     * @throws ArgumentException
     */
    public function parse(mixed $value, ?string $hintName = null) : mixed
    {
        try {
            return $this->onParse($value, $hintName);
        } catch (ArgumentException $ex) {
            throw $ex;
        } catch (ParseFailedException $ex) {
            throw new InvalidArgumentException($hintName, $ex, argType: ParserArgTypeContext::getArgType(), previous: $ex);
        } catch (Exception $ex) {
            throw new InvalidArgumentException($hintName, argType: ParserArgTypeContext::getArgType(), previous: $ex);
        }
    }


    /**
     * Parse given value
     * @param mixed $value
     * @param string|null $hintName
     * @return T
     * @throws Exception
     */
    protected abstract function onParse(mixed $value, ?string $hintName) : mixed;
}