<?php

namespace Magpie\Facades\Http\Supports;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedFeatureTypeClassException;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\General\Factories\ClassFactory;

/**
 * HTTP client request option processor
 */
abstract class HttpClientRequestOptionProcessor
{
    /**
     * @var HttpClientRequestOption Associated option
     */
    protected readonly HttpClientRequestOption $option;


    /**
     * Constructor
     * @param HttpClientRequestOption $option
     */
    protected function __construct(HttpClientRequestOption $option)
    {
        $this->option = $option;
    }


    /**
     * Apply the option
     * @param HttpClientRequestOptionContext $context
     * @return void
     * @throws SafetyCommonException
     * @throws ClientException
     */
    public abstract function apply(HttpClientRequestOptionContext $context) : void;


    /**
     * Create option processor
     * @param HttpClientRequestOption $option
     * @param string $httpTypeClass
     * @return static
     * @throws UnsupportedFeatureTypeClassException
     * @throws ClassNotOfTypeException
     */
    public static function create(HttpClientRequestOption $option, string $httpTypeClass) : static
    {
        $className = ClassFactory::resolveFeature($option->getTypeClass(), $httpTypeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return new $className($option);
    }
}