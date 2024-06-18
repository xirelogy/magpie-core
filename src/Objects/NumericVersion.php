<?php

namespace Magpie\Objects;

use Exception;
use Magpie\Codecs\Concepts\ObjectParseable;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Throwable;

/**
 * Representation of simple numeric based version
 */
class NumericVersion extends Version implements ObjectParseable
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


    public function compare(Version $rhs) : int|null
    {
        if (!$rhs instanceof NumericVersion) return null;

        $lhsCount = count($this->versions);
        $rhsCount = count($rhs->versions);

        if ($lhsCount <= 0 || $rhsCount <= 0) return null;  // Must be comparable

        for ($i = 0; $i < $lhsCount; ++$i) {
            if ($i >= $rhsCount) {
                // RHS run out of data, current version considered to be after RHS
                return 1;
            }

            $lhsPart = $this->versions[$i];
            $rhsPart = $rhs->versions[$i];

            if ($lhsPart < $rhsPart) return -1;
            if ($lhsPart > $rhsPart) return 1;
        }

        // Check if RHS is more specific than current version
        // If yes, current version considered to be prior to RHS
        if ($rhsCount > $lhsCount) return -1;

        // Both versions identical
        return 0;
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


    /**
     * @inheritDoc
     */
    public static function createParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : static {
            $value = StringParser::create()->parse($value, $hintName);
            return static::parse($value);
        });
    }
}