<?php

namespace Magpie\Configurations;

use Magpie\Codecs\ParserHosts\CommonParserHost;
use Magpie\Exceptions\MissingArgumentException;

/**
 * Parser host to read from environment variables (.env files)
 */
class EnvParserHost extends CommonParserHost
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(null);
    }


    /**
     * @inheritDoc
     */
    protected function hasInternal(string|int $inKey) : bool
    {
        return Env::get($inKey) !== null;
    }


    /**
     * @inheritDoc
     */
    protected function obtainRaw(int|string $key, int|string $inKey, bool $isMandatory, mixed $default) : mixed
    {
        $value = Env::get($inKey);
        if ($value === '') $value = null;
        if ($value !== null) return $value;

        if ($isMandatory) throw new MissingArgumentException($this->fullKey($key), argType: $this->argType);
        return $default;
    }


    /**
     * @inheritDoc
     */
    public function fullKey(int|string $key) : string
    {
        return $key;
    }


    /**
     * @inheritDoc
     */
    public function getNextPrefix(int|string|null $key) : ?string
    {
        return $key;
    }


    /**
     * Create corresponding environment key from given components
     * @param string|null ...$components
     * @return string
     */
    public static function makeEnvKey(?string ...$components) : string
    {
        $ret = '';
        foreach ($components as $component) {
            if (is_empty_string($component)) continue;
            $ret .= '_' . strtoupper($component);
        }

        return substr($ret, 1);
    }
}