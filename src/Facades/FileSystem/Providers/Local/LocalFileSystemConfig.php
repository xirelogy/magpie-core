<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Facades\FileSystem\FileSystemConfig;
use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * A local file system
 */
#[FactoryTypeClass(LocalFileSystem::TYPECLASS, FileSystemConfig::class)]
class LocalFileSystemConfig extends FileSystemConfig
{
    /**
     * @var string Root path
     */
    public readonly string $rootPath;


    /**
     * Constructor
     * @param string $rootPath
     */
    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return LocalFileSystem::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        $root = $parserHost->requires($envKey->key('ROOT'), StringParser::create());

        return new static($root);
    }
}