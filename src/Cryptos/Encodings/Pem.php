<?php

namespace Magpie\Cryptos\Encodings;

use Magpie\Cryptos\Contents\BlockContent;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\General\Str;
use Magpie\General\TextContent;
use Magpie\General\Traits\StaticClass;

/**
 * PEM (Privacy-Enhanced Mail) format
 */
class Pem
{
    use StaticClass;

    /**
     * Text prefix for 'BEGIN'
     */
    protected const TEXT_PREFIX_BEGIN = '-----BEGIN ';
    /**
     * Text prefix for 'END'
     */
    protected const TEXT_PREFIX_END = '-----END ';
    /**
     * Text suffix
     */
    protected const TEXT_SUFFIX = '-----';


    /**
     * Encode as PEM format
     * @param iterable<BlockContent> $blocks
     * @return string
     */
    public static function encode(iterable $blocks) : string
    {
        $ret = '';
        foreach ($blocks as $block) {
            $pemBegin = static::TEXT_PREFIX_BEGIN . strtoupper($block->type) . static::TEXT_SUFFIX;
            $pemEnd = static::TEXT_PREFIX_END . strtoupper($block->type) . static::TEXT_SUFFIX;

            $ret .= "\n$pemBegin" .
                "\n" . wordwrap($block->data, 64, "\n", true) .
                "\n$pemEnd";
        }

        return substr($ret, 1);
    }


    /**
     * Check if the content is PEM format with proper content type
     * @param string $data
     * @return bool
     */
    public static function hasContentType(string $data) : bool
    {
        foreach (TextContent::getRows($data) as $row) {
            // Empty lines ignored
            if (Str::isNullOrEmpty(trim($row))) continue;

            if (str_starts_with($row, static::TEXT_PREFIX_BEGIN) && str_ends_with($row, static::TEXT_SUFFIX)) {
                // Begin line detected, considered valid
                return true;
            } else {
                // Otherwise, something is wrong
                return false;
            }
        }

        return false;
    }


    /**
     * Decode from PEM format
     * @param string $data
     * @return iterable<BlockContent>
     * @throws SafetyCommonException
     */
    public static function decode(string $data) : iterable
    {
        $contentType = null;
        $buffer = '';

        foreach (TextContent::getRows($data) as $row) {
            // Empty lines ignored
            if (Str::isNullOrEmpty(trim($row))) continue;

            if (str_starts_with($row, static::TEXT_PREFIX_BEGIN) && str_ends_with($row, static::TEXT_SUFFIX)) {
                // Begin line
                if ($contentType !== null) throw new UnexpectedException(_l('Unexpected \'BEGIN\' line'));
                $contentType = substr($row, strlen(static::TEXT_PREFIX_BEGIN), -strlen(static::TEXT_SUFFIX));
            } else if (str_starts_with($row, static::TEXT_PREFIX_END) && str_ends_with($row, static::TEXT_SUFFIX)) {
                // End line
                if ($contentType === null) throw new UnexpectedException(_l('Unexpected \'END\' line'));
                $checkContentType = substr($row, strlen(static::TEXT_PREFIX_END), -strlen(static::TEXT_SUFFIX));
                if ($contentType !== $checkContentType) throw new UnexpectedException(_l('Unmatched content type at \'END\' line'));

                yield new BlockContent($contentType, $buffer);

                $contentType = null;
                $buffer = '';
            } else {
                // Content line
                if ($contentType === null) throw new UnexpectedException(_l('Unexpected content without content type'));
                $buffer .= trim($row);
            }
        }

        if ($contentType !== null) throw new UnexpectedException(_l('Unexpected end of file'));
    }
}