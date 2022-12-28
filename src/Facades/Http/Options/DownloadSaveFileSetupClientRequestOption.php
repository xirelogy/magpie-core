<?php

namespace Magpie\Facades\Http\Options;

use Magpie\Facades\Http\Concepts\DownloadStreamWriteable;
use Magpie\Facades\Http\Headers\HttpContentDisposition;
use Magpie\Facades\Http\HttpClientResponseHeaders;
use Magpie\Facades\Http\IOs\DownloadFileWriteStream;
use Magpie\Facades\Mime\Mime;
use Magpie\General\FilePath;
use Magpie\General\Names\CommonHttpHeader;

/**
 * Option to setup download to save file
 */
class DownloadSaveFileSetupClientRequestOption extends DownloadSetupClientRequestOption
{
    /**
     * Create save file
     * @param callable(string,HttpClientResponseHeaders,string):string $setupFilenameFn
     * @return static
     */
    public static function createSaveFile(callable $setupFilenameFn) : static
    {
        $setupFn = function (string $url, HttpClientResponseHeaders $returnHeaders) use($setupFilenameFn) : DownloadStreamWriteable {
            $contentType = $returnHeaders->optional(CommonHttpHeader::CONTENT_TYPE);
            $dispositionFilename = HttpContentDisposition::decodeFilename($returnHeaders->optional(CommonHttpHeader::CONTENT_DISPOSITION));
            $urlFilename = FilePath::getFilename($url) ?? '';

            $suggestFilename = static::suggestFilename($urlFilename, $dispositionFilename, $contentType);

            $savePath = $setupFilenameFn($url, $returnHeaders, $suggestFilename);

            return DownloadFileWriteStream::createForPath($savePath);
        };

        return static::create($setupFn);
    }


    /**
     * Get suggested filename
     * @param string $urlFilename
     * @param string|null $dispositionFilename
     * @param string|null $contentType
     * @return string
     */
    protected static function suggestFilename(string $urlFilename, ?string $dispositionFilename, ?string $contentType) : string
    {
        if ($dispositionFilename !== null) return $dispositionFilename;

        $extension = FilePath::getExtension($urlFilename);
        if ($extension !== null) {
            // There is extension, check if content type conflict
            if ($contentType !== null) {
                $extensionContentType = Mime::getMimeType($extension);
                if ($extensionContentType !== $contentType) {
                    $newExtension = Mime::getExtension($contentType);
                    $urlFilename = FilePath::changeExtension($urlFilename, $newExtension);
                }
            }

            return $urlFilename;
        } else {
            // No original extension, try to set one
            $contentTypeExtension = Mime::getExtension($contentType);
            if ($contentTypeExtension !== null) {
                return $urlFilename . '.' . $contentTypeExtension;
            } else {
                return $urlFilename;
            }
        }
    }
}