<?php

namespace Magpie\Facades\Mime\Resolvers;

use Magpie\Facades\Mime\MimeResolvable;
use Magpie\System\Kernel\Kernel;

abstract class BaseMimeResolver implements MimeResolvable
{
    /**
     * @var array|null Map extension into MIME content types
     */
    protected ?array $mapMimeTypes = null;
    /**
     * @var array|null Map MIME content type into extensions
     */
    protected ?array $mapExtensions = null;


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    public function getExtension(?string $mimeType) : ?string
    {
        $this->ensureMap();

        return $this->mapExtensions[strtolower($mimeType)] ?? null;
    }


    /**
     * @inheritDoc
     */
    public function getMimeType(?string $extension) : ?string
    {
        $this->ensureMap();

        return $this->mapMimeTypes[strtolower($extension)] ?? null;
    }


    /**
     * Ensure map exist
     * @return void
     */
    protected function ensureMap() : void
    {
        if ($this->mapMimeTypes !== null && $this->mapExtensions !== null) return;

        $this->mapMimeTypes = [];
        $this->mapExtensions = [];

        foreach (static::mapMimeTypes() as $extension => $mimeType) {
            // Everything should be lowercase
            $extension = strtolower(trim($extension));
            $mimeType = strtolower(trim($mimeType));

            // Dual direction map
            $this->mapMimeTypes[$extension] = $mimeType;
            if (!array_key_exists($mimeType, $this->mapExtensions)) $this->mapExtensions[$mimeType] = $extension;
        }
    }


    /**
     * All extension to MIME content type map
     * @return iterable<string, string>
     */
    protected abstract static function mapMimeTypes() : iterable;


    /**
     * @inheritDoc
     */
    public final function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(MimeResolvable::class, $this);
    }
}