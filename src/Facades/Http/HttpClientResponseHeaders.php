<?php

namespace Magpie\Facades\Http;

use Magpie\Codecs\Concepts\Collectable;
use Magpie\Codecs\ParserHosts\CommonParserHost;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Sugars\Quote;
use Magpie\General\Traits\CommonPackable;

/**
 * HTTP client response headers
 */
abstract class HttpClientResponseHeaders extends CommonParserHost implements Packable, Collectable
{
    use CommonPackable;


    /**
     * @var array<string, mixed> Header collection
     */
    protected array $headers = [];
    /**
     * @var array<string, string> Formatted header names
     */
    protected array $formattedHeaderNames = [];


    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct(null);
    }


    /**
     * @inheritDoc
     */
    protected function hasInternal(string|int $inKey) : bool
    {
        return array_key_exists($inKey, $this->headers);
    }


    /**
     * @inheritDoc
     */
    protected function obtainRaw(int|string $key, int|string $inKey, bool $isMandatory, mixed $default) : mixed
    {
        if (!array_key_exists($inKey, $this->headers)) {
            if ($isMandatory) throw new MissingArgumentException($this->fullKey($key));
            return $default;
        }

        return $this->headers[$inKey];
    }


    /**
     * @inheritDoc
     */
    public function fullKey(int|string $key) : string
    {
        return $this->prefix . Quote::square($key);
    }


    /**
     * @inheritDoc
     */
    public function getNextPrefix(int|string|null $key) : ?string
    {
        if (is_empty_string($this->prefix)) return $key;
        if (is_empty_string($key)) return $this->prefix;

        return $this->prefix . Quote::square($key);
    }


    /**
     * @inheritDoc
     */
    protected function acceptKey(int|string $key) : string|int
    {
        return strtolower($key);
    }


    /**
     * @inheritDoc
     */
    protected function formatKey(int|string $key) : string|int
    {
        return $this->formattedHeaderNames[$key] ?? $key;
    }



    /**
     * @inheritDoc
     */
    public function getKeys() : iterable
    {
        foreach ($this->formattedHeaderNames as $name) {
            yield $name;
        }
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $values = [];
        foreach ($this->all() as $key => $value) {
            $values[] = obj([
                'name' => $key,
                'value' => $value,
            ]);
        }

        $ret->values = $values;
    }


    /**
     * @inheritDoc
     */
    public function all() : iterable
    {
        foreach ($this->headers as $key => $value) {
            yield $this->formatKey($key) => $value;
        }
    }
}