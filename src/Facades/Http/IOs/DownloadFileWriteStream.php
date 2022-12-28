<?php

namespace Magpie\Facades\Http\IOs;

use Exception;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Facades\Http\Bodies\HttpDownloadedFileClientResponseBody;
use Magpie\Facades\Http\Concepts\DownloadStreamWriteable;
use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\General\Contents\FileBinaryContent;
use Magpie\General\IOs\FileWriteStream;

/**
 * A file write stream for download purpose
 */
class DownloadFileWriteStream extends FileWriteStream implements DownloadStreamWriteable
{
    /**
     * @inheritDoc
     */
    public function finalizeAsBody() : HttpClientResponseBody
    {
        try {
            $this->close();
        } catch (Exception $ex) {
            throw new OperationFailedException(previous: $ex);
        }

        $content = FileBinaryContent::from($this->path);
        return new HttpDownloadedFileClientResponseBody($content);
    }
}