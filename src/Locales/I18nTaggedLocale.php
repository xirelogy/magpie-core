<?php

namespace Magpie\Locales;

use Magpie\Locales\Concepts\Localizable;

/**
 * Tagged locale
 */
class I18nTaggedLocale implements Localizable
{
    /**
     * @var string Basic text
     */
    protected readonly string $text;
    /**
     * @var string|null Associated class name
     */
    protected readonly ?string $className;


    /**
     * Constructor
     * @param string $text
     * @param string|null $className
     */
    public function __construct(string $text, ?string $className)
    {
        $this->text = $text;
        $this->className = $className;
    }


    /**
     * @inheritDoc
     */
    public function getDefaultTranslation() : string
    {
        return $this->text;
    }


    /**
     * @inheritDoc
     */
    public function getTranslation(string $locale) : string
    {
        return I18n::translate($this->text, $this->className, $locale);
    }


    /**
     * Export to source cache
     * @param array $ret
     * @return void
     * @internal
     */
    public function _sourceCacheExportTo(array& $ret) : void
    {
        $ret['text'] = $this->text;
        $ret['className'] = $this->className;
    }


    /**
     * Import from source cache
     * @param array $data
     * @return static
     * @internal
     */
    public static function _sourceCacheImportFrom(array $data) : static
    {
        return new static($data['text'], $data['className']);
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        $locale = I18n::getCurrentLocale();
        return $this->getTranslation($locale);
    }
}