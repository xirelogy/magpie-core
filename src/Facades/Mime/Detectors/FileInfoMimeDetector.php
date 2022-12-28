<?php

namespace Magpie\Facades\Mime\Detectors;

use Exception;
use finfo;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Traits\SingletonInstance;

/**
 * MIME type detector using 'finfo'
 */
class FileInfoMimeDetector extends BaseMimeDetector
{
    use SingletonInstance;

    /**
     * Current type class
     */
    public const TYPECLASS = 'fileinfo';
    /**
     * @var finfo Target handle
     */
    private finfo $handle;


    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->handle = new finfo(FILEINFO_MIME_TYPE);
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
     */
    public function detectMimeType(BinaryDataProvidable|string|null $content) : ?string
    {
        if ($content === null) return null;

        try {
            $content = $content instanceof BinaryDataProvidable ? $content->getData() : $content;
        } catch (Exception) {
            return null;
        }

        $ret = @$this->handle->buffer($content) ?: null;
        if (static::isNonConclusive($ret)) return null;

        return $ret;
    }


    /**
     * If MIME type is non-conclusive
     * @param string|null $mimeType
     * @return bool
     */
    protected static function isNonConclusive(?string $mimeType) : bool
    {
        if ($mimeType === null) return true;

        /** @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
        switch ($mimeType) {
            case 'application/octet-stream':
            case 'application/x-empty':
                return true;
            default:
                return false;
        }
    }
}