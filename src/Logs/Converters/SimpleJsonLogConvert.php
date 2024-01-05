<?php

namespace Magpie\Logs\Converters;

use Magpie\Codecs\Formats\Formatter;
use Magpie\Codecs\Formats\JsonGeneralFormatter;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Sugars\Quote;
use Magpie\Logs\LogEntry;

/**
 * May convert data into JSON format
 */
class SimpleJsonLogConvert extends LogConvert
{
    /**
     * @var string Object name
     */
    protected readonly string $name;
    /**
     * @var Formatter Context formatter
     */
    protected readonly Formatter $contextFormatter;
    /**
     * @var bool If this is the first record
     */
    protected bool $isFirst = true;


    /**
     * Constructor
     * @param string $name Log container name
     * @param Formatter|null $contextFormatter Specific formatter instance to be used to format context
     */
    public function __construct(string $name = 'logs', ?Formatter $contextFormatter = null)
    {
        $this->name = $name;
        $this->contextFormatter = $contextFormatter ?? JsonGeneralFormatter::create();
    }


    /**
     * @inheritDoc
     */
    protected function convertPrefixes() : iterable
    {
        yield '{' . Quote::double($this->name) . ':';
        yield '[';
    }


    /**
     * @inheritDoc
     */
    protected function convertRecord(LogEntry $record) : iterable
    {
        if ($this->isFirst) {
            $this->isFirst = false;
        } else {
            yield ',';
        }

        yield Excepts::noThrow(fn () => SimpleJSON::encode(obj([
            'loggedAt' => $record->loggedAt?->getTimestamp(),
            'source' => $record->source,
            'level' => $record->level->name,
            'message' => $record->message,
            'context' => $this->contextFormatter->format(obj($record->context)),
        ])), '{}');
    }


    /**
     * @inheritDoc
     */
    protected function convertSuffixes() : iterable
    {
        yield ']';
        yield '}';
    }
}