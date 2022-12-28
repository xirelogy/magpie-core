<?php

namespace Magpie\Consoles;

use Magpie\Consoles\Concepts\ConsoleDisplayable;

/**
 * A table that can be displayed on console
 */
class ConsoleTable implements ConsoleDisplayable
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'table';

    /**
     * @var array<string> Table headers
     */
    protected array $headers;
    /**
     * @var array<array<string>> Table rows
     */
    protected array $rows = [];


    /**
     * Constructor
     * @param array<string> $headers Table headers
     * @param array<array<string>> $rows Table rows
     */
    public function __construct(array $headers = [], iterable $rows = [])
    {
        $this->headers = $headers;
        $this->rows = iter_flatten($rows, false);
    }


    /**
     * Add header to table
     * @param string $header
     * @return $this
     */
    public function addHeader(string $header) : static
    {
        $this->headers[] = $header;
        return $this;
    }


    /**
     * Add rows to table
     * @param iterable<array<string>> $rows
     * @return $this
     */
    public function addRows(iterable $rows) : static
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
        return $this;
    }


    /**
     * Add row to table
     * @param array<string> $row
     * @return $this
     */
    public function addRow(array $row) : static
    {
        $this->rows[] = $row;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     * @internal
     */
    public function _export() : object
    {
        return obj([
            'headers' => $this->headers,
            'rows' => $this->rows,
        ]);
    }
}