<?php

namespace Magpie\HttpServer\Headers;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Exceptions\ArgumentException;

/**
 * A value with quality (priority) in comma separated HTTP header value
 */
class QualityValue
{
    /**
     * Default quality value
     */
    public const DEFAULT_QUALITY = 1.0;

    /**
     * @var string The payload value
     */
    public readonly string $value;
    /**
     * @var float Associated quality
     */
    public readonly float $quality;
    /**
     * @var array<string, string> Other optional options
     */
    public readonly array $options;


    /**
     * Constructor
     * @param string $value
     * @param float $quality
     * @param array<string, string> $options
     */
    public function __construct(string $value, float $quality = self::DEFAULT_QUALITY, array $options = [])
    {
        $this->value = $value;
        $this->quality = $quality;
        $this->options = $options;
    }


    /**
     * Construct from given ColonSeparatedHeaderValue
     * @param ColonSeparatedHeaderValue $value
     * @param float $quality
     * @return static
     * @throws ArgumentException
     */
    public static function fromColonSeparatedHeaderValue(ColonSeparatedHeaderValue $value, float $quality = self::DEFAULT_QUALITY) : static
    {
        $payloadValue = $value->requires('', StringParser::create());

        $options = [];
        foreach ($value->all() as $optKey => $optValue) {
            if ($optKey === '' || $optKey === 'q') continue;
            $options[$optKey] = $optValue;
        }

        return new static($payloadValue, $quality, $options);
    }
}