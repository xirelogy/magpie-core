<?php

namespace Magpie\HttpServer\Contents;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\General\Concepts\PrimitiveBinaryContentable;
use Magpie\General\Contents\SimpleBinaryContent;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\HttpServer\HeaderCollection;
use Magpie\HttpServer\Headers\ColonSeparatedHeaderValue;

/**
 * Handle multipart/form-data POST body content
 */
#[FactoryTypeClass(MultipartFormPostBodyContent::CONTENT_TYPE, PostBodyContent::class)]
class MultipartFormPostBodyContent extends PostBodyContent
{
    /**
     * Current content type
     */
    public const CONTENT_TYPE = 'multipart/form-data';


    /**
     * @inheritDoc
     */
    protected function onGetVariables() : iterable
    {
        /** @var ColonSeparatedHeaderValue|null $contentType */
        $contentType = $this->headers->safeOptional(CommonHttpHeader::CONTENT_TYPE, ColonSeparatedHeaderValue::createParser());
        if ($contentType === null) return;

        $boundary = $contentType->safeOptional('boundary', StringParser::create(), '');

        $parts = preg_split('/\\R?-+' . preg_quote($boundary, '/') . '/s', $this->body);
        array_pop($parts);  // Final part is always empty

        foreach ($parts as $part) {
            if (empty($part)) continue;

            [$partHeaders, $partBody] = preg_split('/\\R\\R/', $part, 2);
            $partHeaders = static::parseMultipartHeaders($partHeaders);

            yield from static::decodeMultipart($partHeaders, $partBody);
        }
    }


    /**
     * Parse the headers in each 'part' of the multipart form
     * @param string $headerBody
     * @return HeaderCollection
     */
    protected static function parseMultipartHeaders(string $headerBody) : HeaderCollection
    {
        $headers = function () use ($headerBody) : iterable {
            $headerLines = preg_split('/\\R/', $headerBody, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($headerLines as $headerLine) {
                $colonPos = strpos($headerLine, ':');
                if ($colonPos === false) continue;

                $headerName = substr($headerLine, 0, $colonPos);
                $headerValue = substr($headerLine, $colonPos + 1);

                $replacedHeaderName = str_replace('-', '_', strtoupper($headerName));
                yield trim($replacedHeaderName) => trim($headerValue);
            }
        };

        return new class($headers()) extends HeaderCollection {
            /**
             * Constructor
             * @param iterable $keyValues
             * @param string|null $prefix
             */
            public function __construct(iterable $keyValues, ?string $prefix = null)
            {
                parent::__construct($keyValues, $prefix);
            }
        };
    }


    /**
     * Decode for the corresponding content from given headers and body
     * @param HeaderCollection $headers
     * @param string $body
     * @return iterable<string, string|PrimitiveBinaryContentable>
     */
    protected static function decodeMultipart(HeaderCollection $headers, string $body) : iterable
    {
        /** @var ColonSeparatedHeaderValue|null $contentDisposition */
        $contentDisposition = $headers->safeOptional(CommonHttpHeader::CONTENT_DISPOSITION, ColonSeparatedHeaderValue::createParser());
        if ($contentDisposition === null) return;
        if ($contentDisposition->safeOptional('') !== 'form-data') return;

        // Must have corresponding name
        $name = $contentDisposition->safeOptional('name', StringParser::create());
        if ($name === null) return null;

        // Check for filename
        $filename = $contentDisposition->safeOptional('filename', StringParser::create());

        if ($filename !== null) {
            yield $name => static::decodeMultipartFile($headers, $filename, $body);
        } else {
            yield $name => $body;
        }
    }


    /**
     * Decode for the corresponding content as a file
     * @param HeaderCollection $headers
     * @param string $filename
     * @param string $body
     * @return PrimitiveBinaryContentable
     */
    protected static function decodeMultipartFile(HeaderCollection $headers, string $filename, string $body) : PrimitiveBinaryContentable
    {
        /** @var ColonSeparatedHeaderValue|null $contentType */
        $contentType = $headers->safeOptional(CommonHttpHeader::CONTENT_TYPE, ColonSeparatedHeaderValue::createParser());
        $contentType = $contentType?->safeOptional('');

        return SimpleBinaryContent::create($body, $contentType, $filename);
    }
}