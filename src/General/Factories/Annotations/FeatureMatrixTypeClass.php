<?php

namespace Magpie\General\Factories\Annotations;

use Attribute;

/**
 * Declares that current `class` is associated to given map of subject and feature on given association class
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class FeatureMatrixTypeClass
{
    /**
     * @var string Feature's type class
     */
    public string $featureTypeClass;
    /**
     * @var string Subject's type class
     */
    public string $subjectTypeClass;
    /**
     * @var string Association class name, where the subject-feature map is based on
     */
    public string $assocClassName;


    /**
     * Constructor
     * @param string $featureTypeClass Feature's type class
     * @param string $subjectTypeClass Subject's type class
     * @param string $assocClassName Association class name, where the subject-feature map is based on
     */
    public function __construct(string $featureTypeClass, string $subjectTypeClass, string $assocClassName)
    {
        $this->featureTypeClass = $featureTypeClass;
        $this->subjectTypeClass = $subjectTypeClass;
        $this->assocClassName = $assocClassName;
    }
}