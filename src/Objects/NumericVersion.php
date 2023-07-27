<?php

namespace Magpie\Objects;

use Exception;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Throwable;

/**
 * Representation of simple numeric based version
 */
class NumericVersion extends Version
{
    /**
     * @var array<int> All version numbers
     */
    public array $versions;


    /**
     * Constructor
     * @param array<int> $versions
     */
    protected function __construct(array $versions)
    {
        $this->versions = $versions;
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return implode('.', $this->versions);
    }


    /**
     * Major version
     * @return int
     * @throws SafetyCommonException
     */
    public function getMajor() : int
    {
        return $this->versions[0] ?? throw new NullException();
    }


    /**
     * Minor version
     * @return int|null
     */
    public function getMinor() : ?int
    {
        return $this->versions[1] ?? null;
    }


    /**
     * Release / very-minor version
     * @return int|null
     */
    public function getRelease() : ?int
    {
        return $this->versions[2] ?? null;
    }


    /**
     * Create version from given numbers
     * @param int ...$numbers
     * @return static
     */
    public static function fromNumbers(int ...$numbers) : static
    {
        return new static($numbers);
    }


    /**
     * Create version from parsing text
     * @param string $text
     * @return static
     * @throws SafetyCommonException
     */
    public static function parse(string $text) : static
    {
        try {
            $ret = [];
            foreach (explode('.', $text) as $subText) {
                if (is_empty_string(trim($subText))) throw new Exception('Empty string');
                $ret[] = IntegerParser::create()->withStrict()->parse($subText);
            }

            return new static($ret);
        } catch (Throwable) {
            throw new InvalidDataException();
        }
    }
}