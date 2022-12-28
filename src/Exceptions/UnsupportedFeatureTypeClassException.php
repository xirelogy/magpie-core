<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to feature type class not supported
 */
class UnsupportedFeatureTypeClassException extends UnsupportedException
{
    /**
     * Constructor
     * @param string $featureTypeClass The feature's type class not supported
     * @param string $subjectTypeClass The subject's type class to be supported with the feature
     * @param string $assocClassName The association base class to relate the feature and the subject
     * @param Throwable|null $previous
     */
    public function __construct(string $featureTypeClass, string $subjectTypeClass, string $assocClassName, ?Throwable $previous = null)
    {
        $message = static::formatMessage($featureTypeClass, $subjectTypeClass, $assocClassName);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $featureTypeClass
     * @param string $subjectTypeClass
     * @param string $assocClassName
     * @return string
     */
    protected static function formatMessage(string $featureTypeClass, string $subjectTypeClass, string $assocClassName) : string
    {
        return _format_safe(_l('Cannot create instance of \'{{2}}\' for the feature \'{{0}}\' under the subject \'{{1}}\''), $featureTypeClass, $subjectTypeClass, $assocClassName)
            ?? _l('Cannot create instance of given subject-feature pair');
    }
}