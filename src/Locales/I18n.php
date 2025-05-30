<?php

namespace Magpie\Locales;

use Closure;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Contexts\ClosureReleaseScoped;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Traits\StaticClass;
use Magpie\Locales\Concepts\Localizable;
use Magpie\Locales\Traits\CommonLoadTranslation;
use Magpie\Locales\Traits\CommonNormalizeLocale;
use Magpie\System\Kernel\Kernel;

/**
 * Internationalization (i18n) support
 */
class I18n
{
    use StaticClass;
    use CommonLoadTranslation;
    use CommonNormalizeLocale;


    /**
     * The default locale, whenever not specified
     */
    public final const DEFAULT_LOCALE = 'en_us';

    /**
     * @var string|null The current specific locale
     */
    private static ?string $currentLocale = null;
    /**
     * @var array<string, array<string, string>> Caches to the translations
     */
    private static array $cachedTranslations = [];
    /**
     * @var array<string, string> Locale aliases
     */
    private static ?array $localeAliases = null;


    /**
     * Tag the target as localizable
     * @param Localizable|string $text
     * @param string|null $className
     * @return Localizable
     */
    public static function tag(Localizable|string $text, ?string $className = null) : Localizable
    {
        if ($text instanceof Localizable) return $text;

        return new I18nTaggedLocale($text, $className);
    }


    /**
     * Tag the target as a fake localizable, returning the text ad-verbatim
     * @param Localizable|string $text
     * @return Localizable
     */
    public static function verbatim(Localizable|string $text) : Localizable
    {
        if ($text instanceof Localizable) $text = $text->getDefaultTranslation();

        return new class($text) implements Localizable {
            /**
             * Constructor
             * @param string $text
             */
            public function __construct(
                protected readonly string $text,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function getTranslation(string $locale) : string
            {
                return $this->getDefaultTranslation();
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
            public function __toString() : string
            {
                return $this->getDefaultTranslation();
            }
        };
    }


    /**
     * Defer translation into specific locale translation
     * @param callable(string):string $fn
     * @return Localizable
     */
    public static function defer(callable $fn) : Localizable
    {
        return new class($fn) implements Localizable {
            /**
             * Constructor
             * @param Closure(string):string $fn
             */
            public function __construct(
                protected readonly Closure $fn,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function getTranslation(string $locale) : string
            {
                return ($this->fn)($locale);
            }


            /**
             * @inheritDoc
             */
            public function getDefaultTranslation() : string
            {
                return $this->getTranslation(I18n::getCurrentLocale());
            }


            /**
             * @inheritDoc
             */
            public function __toString() : string
            {
                return $this->getDefaultTranslation();
            }
        };
    }


    /**
     * Translate text
     * @param Localizable|string $text Original text
     * @param string|null $className Class name (context) of the text
     * @param string|null $locale If not null, a specific locale to be used for translation
     * @return string
     */
    public static function translate(Localizable|string $text, ?string $className = null, ?string $locale = null) : string
    {
        $locale = $locale ?? static::getCurrentLocale();

        if ($text instanceof Localizable) {
            if ($locale === '') return $text->getDefaultTranslation();
            return $text->getTranslation($locale);
        }

        if ($locale === '') return $text;
        foreach (explode(',', $locale) as $thisLocale) {
            $translations = static::getTranslations($className, $thisLocale);
            if (array_key_exists($text, $translations)) return $translations[$text];
        }

        // Special translation: '_' will be recognized as default
        $defaultTranslations = static::getTranslations($className, '_');
        if (array_key_exists($text, $defaultTranslations)) return $defaultTranslations[$text];

        return $text;
    }


    /**
     * Get translations for given class (context) and locale
     * @param string|null $className
     * @param string $locale
     * @return array<string, string>
     */
    protected static function getTranslations(?string $className, string $locale) : array
    {
        // Normalize the class name and locale
        $className = $className ?? '';
        $locale = static::normalizeLocale($locale);

        $cacheKey = "$className::$locale";

        if (!array_key_exists($cacheKey, static::$cachedTranslations)) {
            static::$cachedTranslations[$cacheKey] = static::loadTranslations($className, $locale);
        }

        return static::$cachedTranslations[$cacheKey];
    }


    /**
     * Accept locale string (specification)
     * @param string $locale
     * @return string
     */
    public static function acceptLocaleString(string $locale) : string
    {
        // Remove anything after dot
        if (($dotPos = strpos($locale, '.')) !== false) {
            $locale = substr($locale, 0, $dotPos);
        }

        $locale = static::normalizeLocale($locale);
        $aliases = static::getLocaleAliases();

        $ret = [];
        foreach (explode(',', $locale) as $subLocale) {
            if (array_key_exists($subLocale, $aliases)) $subLocale = $aliases[$subLocale];
            $ret[] = $subLocale;
        }

        return implode(',', $ret);
    }


    /**
     * Get configured locale aliases
     * @return array<string, string>
     */
    protected static function getLocaleAliases() : array
    {
        // Special case only when kernel is not yet available
        if (!Kernel::hasCurrent()) return [];

        if (static::$localeAliases === null) {
            // Initialize aliases
            $aliases = [];

            $mapFilename = project_path('/boot/locale_map.php');
            if (LocalRootFileSystem::instance()->isFileExist($mapFilename)) {
                $aliases = include $mapFilename;
            }

            static::$localeAliases = $aliases;
        }

        return static::$localeAliases;
    }


    /**
     * Current locale
     * @return string
     */
    public static function getCurrentLocale() : string
    {
        if (static::$currentLocale !== null) return static::$currentLocale;

        $systemLocale = static::getSystemCurrentLocale();
        return static::normalizeLocale($systemLocale);
    }


    /**
     * System locale
     * @return string
     */
    protected static function getSystemCurrentLocale() : string
    {
        return env('APP_LOCALE', static::DEFAULT_LOCALE);
    }


    /**
     * Current locale, or fallback to the default if null provided
     * @param string|null $locale
     * @return void
     */
    public static function setCurrentLocale(?string $locale) : void
    {
        if ($locale !== null) {
            $locale = static::acceptLocaleString($locale);
            $locale = static::normalizeLocale($locale);
        }

        static::$currentLocale = $locale;
    }


    /**
     * Create a locale scope
     * @param string|null $locale
     * @return Scoped
     */
    public static function createLocaleScoped(?string $locale) : Scoped {
        $oldCurrentLocale = static::$currentLocale;
        static::setCurrentLocale($locale);

        return ClosureReleaseScoped::create(function () use ($oldCurrentLocale) {
            static::$currentLocale = $oldCurrentLocale;
        });
    }
}