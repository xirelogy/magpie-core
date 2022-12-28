<?php

namespace Magpie\Facades\Mime\Resolvers;

use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Contents\BinaryContent;
use Magpie\General\TextContent;
use Magpie\General\Traits\SingletonInstance;
use Magpie\System\HardCore\ClassCache;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * MIME content types and extensions resolver from apache.org
 */
class ApacheOrgMimeResolver extends BaseMimeResolver
{
    use SingletonInstance;


    /**
     * Current type class
     */
    public const TYPECLASS = 'apache-org';


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
    protected static function mapMimeTypes() : iterable
    {
        try {
            $body = static::readContent();
            foreach (TextContent::getRows($body) as $row) {
                if (is_empty_string($row)) continue;
                if (str_starts_with($row, '#')) continue;

                $data = explode("\t", $row);
                if (count($data) < 2) throw new InvalidDataException();

                // Separate into MIME type and extensions
                $mimeType = $data[0];
                $extensions = $data[count($data) - 1];
                $extensions = explode(' ', $extensions);

                foreach ($extensions as $extension) {
                    yield $extension => $mimeType;
                }
            }
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Read content
     * @return string
     * @throws Exception
     */
    private static function readContent() : string
    {
        $cacheDirectory = ClassCache::getClassDirectory(static::class);
        $cachePath = $cacheDirectory . '/mime.types';

        $fs = LocalRootFileSystem::instance();

        if (!$fs->isFileExist($cachePath)) {
            $content = BinaryContent::downloadFrom('https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');

            $fs->createDirectory($cacheDirectory);
            $fs->writeFile($cachePath, $content);
        }

        return $fs->readFile($cachePath)->getData();
    }
}