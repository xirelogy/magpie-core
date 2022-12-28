<?php

namespace Magpie\General\Contents;

use Magpie\Exceptions\HttpClientException;
use Magpie\Exceptions\HttpClientStatusFailedException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\Facades\Http\Headers\HttpContentDisposition;
use Magpie\Facades\Http\HttpClient;
use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\FileSystemAccessible;
use Magpie\General\Contents\Impls\UpgradedBinaryContent;
use Magpie\General\Contents\Impls\UpgradedFileBinaryContent;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonMimeType;
use Magpie\General\Traits\StaticClass;

/**
 * Utilities related to BinaryContentable
 */
class BinaryContent
{
    use StaticClass;


    /**
     * Upgrade simple BinaryDataProvidable to a BinaryContentable
     * @param BinaryDataProvidable $content
     * @return BinaryContentable
     */
    public static function asBinaryContentable(BinaryDataProvidable $content) : BinaryContentable
    {
        if ($content instanceof BinaryContentable) return $content;

        return new UpgradedBinaryContent($content);
    }


    /**
     * Enforce that the content must be file system accessible
     * @param BinaryDataProvidable $content Content to be inspected
     * @param bool|null $isTemporary If a temporary had been created
     * @return FileSystemAccessible&BinaryContentable
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public static function getFileSystemAccessible(BinaryDataProvidable $content, ?bool &$isTemporary = null) : BinaryContentable&FileSystemAccessible
    {
        $isTemporary = false;
        if ($content instanceof FileSystemAccessible) {
            if ($content instanceof BinaryContentable) {
                /** @var BinaryContentable&FileSystemAccessible $content */
                return $content;
            } else {
                /** @var BinaryDataProvidable&FileSystemAccessible $content */
                return new UpgradedFileBinaryContent($content);
            }
        }

        $ret = TemporaryBinaryContent::fromContent($content);
        $isTemporary = true;
        return $ret;
    }


    /**
     * Download binary content from given URL
     * @param string $url
     * @param string $defaultFilename
     * @return BinaryContentable
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws HttpClientException
     * @throws ClientException
     */
    public static function downloadFrom(string $url, string $defaultFilename = 'content') : BinaryContentable
    {
        $response = HttpClient::initialize()->get($url);

        $httpStatusCode = $response->getHttpStatusCode();
        if ($httpStatusCode !== 200) throw new HttpClientStatusFailedException($httpStatusCode);

        $headers = $response->getHeaders();
        $filename = HttpContentDisposition::decodeFilename($headers->optional(CommonHttpHeader::CONTENT_DISPOSITION)) ?? $defaultFilename;
        $contentType = $headers->optional(CommonHttpHeader::CONTENT_TYPE, default: CommonMimeType::BINARY);
        $content = $response->getBody()->getData();

        return SimpleBinaryContent::create($content, $contentType, $filename);
    }
}