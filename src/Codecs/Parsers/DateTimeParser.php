<?php

namespace Magpie\Codecs\Parsers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Magpie\Codecs\Traits\CommonTimeImmutableParser;
use Magpie\Exceptions\NullException;

/**
 * Date-time (expressed in formatted string) parser
 * @extends CreatableParser<CarbonInterface|null>
 */
class DateTimeParser extends CreatableParser
{
    use CommonTimeImmutableParser;


    /**
     * @var string|null Timezone where the value is expected to be expressed in
     */
    protected ?string $timezone = null;
    /**
     * @var bool If empty strings are recognized as null
     */
    protected bool $isEmptyStringAsNull = false;
    /**
     * @var bool If null values allowed
     */
    protected bool $isNullAllowed = true;
    /**
     * @var array<string, CarbonInterface|null> Specific values
     */
    protected array $specifics = [];


    /**
     * With specific timezone
     * @param string $timezone
     * @return $this
     */
    public function withTimezone(string $timezone) : static
    {
        $this->timezone = $timezone;
        return $this;
    }


    /**
     * Specify if empty strings are recognized as null
     * @param bool $isEmptyStringAsNull
     * @return $this
     */
    public function withEmptyStringAsNull(bool $isEmptyStringAsNull = true) : static
    {
        $this->isEmptyStringAsNull = $isEmptyStringAsNull;
        return $this;
    }


    /**
     * Specify if null allowed
     * @param bool $isNullAllowed
     * @return $this
     */
    public function withNullAllowed(bool $isNullAllowed = true) : static
    {
        $this->isNullAllowed = $isNullAllowed;
        return $this;
    }


    /**
     * With specific recognition of value to be translated as equivalent
     * @param string $value
     * @param CarbonInterface|null $equivalent
     * @return $this
     */
    public function withSpecific(string $value, ?CarbonInterface $equivalent) : static
    {
        $this->specifics[trim($value)] = $equivalent;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) return $value;

        if ($value instanceof DateTimeInterface) return new Carbon($value);

        $value = StringParser::create()
            ->withTrimming()
            ->withEmptyAsNull()
            ->parse($value, $hintName);

        if (array_key_exists($value, $this->specifics)) {
            return $this->specifics[$value];
        }

        if ($this->isEmptyStringAsNull && $value === '') {
            $value = null;
        }

        if (is_empty_string($value)) {
            if (!$this->isNullAllowed) throw new NullException();
            return null;
        }

        $ret = Carbon::parse($value, $this->timezone);
        if ($this->isImmutableOutput) $ret = $ret->toImmutable();
        return $ret;
    }
}