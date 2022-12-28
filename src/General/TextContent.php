<?php

namespace Magpie\General;

use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\IOs\StreamConstants;
use Magpie\General\Traits\StaticClass;

/**
 * Ability to handle text content
 */
class TextContent
{
    use StaticClass;


    /**
     * Get text rows
     * @param string $text
     * @return iterable<string>
     */
    public static function getRows(string $text) : iterable
    {
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        return explode("\n", $text);
    }


    /**
     * Get text rows from stream
     * @param StreamReadable $stream
     * @param int $maxChunkSize
     * @return iterable<string>
     * @throws StreamException
     */
    public static function getStreamRows(StreamReadable $stream, int $maxChunkSize = StreamConstants::DEFAULT_CHUNK_SIZE) : iterable
    {
        $contents = function () use ($stream, $maxChunkSize) : iterable {
            while ($stream->hasData()) {
                yield $stream->read($maxChunkSize);
            }
        };

        return static::getIterableRows($contents());
    }


    /**
     * Filter text rows from stream of iterated contents
     * @param iterable<string> $contents
     * @return iterable<string>
     */
    public static function getIterableRows(iterable $contents) : iterable
    {
        $buffer = '';

        foreach ($contents as $content) {
            $buffer .= $content;
            yield from static::getLinesFromBuffer($buffer);
        }

        if (strlen($buffer) > 0) yield $buffer;
    }


    /**
     * Extract lines of text from buffer
     * @param string $buffer
     * @return iterable<string>
     */
    public static function getLinesFromBuffer(string &$buffer) : iterable
    {
        while (true) {
            [$cutPos, $cutLength] = static::findLineBreak($buffer);
            if ($cutPos < 0) break;

            yield substr($buffer, 0, $cutPos);
            $buffer = substr($buffer, $cutPos + $cutLength);
        }
    }

    
    /**
     * Find line break
     * @param string $buffer
     * @return array<int> The position of the line break and the length of the line break
     */
    protected static function findLineBreak(string $buffer) : array
    {
        $rPos = strpos($buffer, "\r");
        $nPos = strpos($buffer, "\n");
        if ($rPos === false && $nPos === false) return [-1, -1];

        // Detect '\n'
        if ($nPos !== false && ($rPos === false || $rPos > $nPos)) return [$nPos, 1];

        // Detect '\r\n'
        if ($nPos !== false && $rPos !== false && ($nPos - $rPos === 1)) return [$rPos, 2];

        // Possible '\r'
        if ($rPos !== false && ($nPos === false || $nPos > ($rPos + 1))) return [$rPos, 1];

        // Fallback condition, treated as single break
        if ($rPos === false) return [$nPos, 1];
        if ($nPos === false) return [$rPos, 1];
        return [min($rPos, $nPos), 1];
    }
}