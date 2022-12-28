<?php

namespace Magpie\General\Factories;

use Magpie\Exceptions\DuplicatedException;
use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\Annotations\NamedLabel;
use Magpie\Locales\ClosureLocaleResolver;
use Magpie\Locales\Concepts\Localizable;
use Magpie\Locales\I18nMultiLocale;
use Magpie\Locales\Traits\CommonLoadTranslation;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

/**
 * Named label provider
 */
class NamedLabelProvider
{
    use CommonLoadTranslation;

    /**
     * @var string Source class name
     */
    protected readonly string $className;
    /**
     * @var array<string, Localizable> All labels
     */
    protected readonly array $labels;


    /**
     * Constructor
     * @param string $className
     * @param array<string, Localizable> $labels
     */
    protected function __construct(string $className, array $labels)
    {
        $this->className = $className;
        $this->labels = $labels;
    }


    /**
     * Get the localized label for given value
     * @param string|int $value
     * @param Localizable|string $defaultText
     * @return Localizable
     */
    public function getLabel(string|int $value, Localizable|string $defaultText = '') : Localizable
    {
        $value = "$value";
        if (array_key_exists($value, $this->labels)) return $this->labels[$value];

        if ($defaultText instanceof Localizable) return $defaultText;
        return new I18nMultiLocale($defaultText);
    }


    /**
     * Create provider instance from collecting NamedLabel from target class
     * @param class-string $className
     * @return static
     * @throws SafetyCommonException
     */
    public static function from(string $className) : static
    {
        $resolver = ClosureLocaleResolver::create(function (string $text, string $locale) use ($className) : ?string {
            $translations = static::loadTranslations($className, $locale);
            return $translations[$text] ?? null;
        });

        try {
            $outLabels = [];

            $class = new ReflectionClass($className);
            foreach ($class->getReflectionConstants(ReflectionClassConstant::IS_PUBLIC) as $constant) {
                $constantValue = $constant->getValue();
                if (!is_int($constantValue) && !is_string($constantValue)) continue;

                $constantValue = "$constantValue";

                $defaultLabel = null;
                $mappedLabels = [];
                foreach (static::listNamedLabelEntriesFromAttribute($constant) as $namedLabel) {
                    if (is_empty_string($namedLabel->locale)) {
                        if ($defaultLabel !== null) throw new DuplicatedException();
                        $defaultLabel = $namedLabel->label;
                    } else {
                        if (array_key_exists($namedLabel->locale, $mappedLabels)) throw new DuplicatedKeyException($namedLabel->locale);
                        $mappedLabels[$namedLabel->locale] = $namedLabel->label;
                    }
                }

                if ($defaultLabel === null) throw new MissingArgumentException();

                $localeString = new I18nMultiLocale($defaultLabel);
                $localeString->withResolver($resolver);

                foreach ($mappedLabels as $locale => $label) {
                    $localeString->define($locale, $label);
                }

                $outLabels[$constantValue] = $localeString;
            }

            return new static($className, $outLabels);
        } catch (ReflectionException $ex) {
            throw new OperationFailedException(previous: $ex);
        }
    }


    /**
     * Get all NamedLabel entries
     * @param ReflectionClassConstant $constant
     * @return iterable<NamedLabel>
     */
    private static function listNamedLabelEntriesFromAttribute(ReflectionClassConstant $constant) : iterable
    {
        foreach ($constant->getAttributes(NamedLabel::class) as $attribute) {
            yield $attribute->newInstance();
        }
    }
}