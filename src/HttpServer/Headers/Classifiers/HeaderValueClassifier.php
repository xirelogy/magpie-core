<?php

namespace Magpie\HttpServer\Headers\Classifiers;

use Closure;
use Magpie\General\Concepts\ArraySortable;
use Magpie\HttpServer\Headers\ColonSeparatedHeaderValue;

/**
 * May classify header values according to specific criteria
 */
abstract class HeaderValueClassifier
{
    /**
     * @var self|null Parent classifier
     */
    protected readonly ?self $parent;


    /**
     * Constructor
     * @param HeaderValueClassifier|null $parent
     */
    protected function __construct(?self $parent = null)
    {
        $this->parent = $parent;
    }


    /**
     * Create relevant sorter instance
     * @return ArraySortable<ColonSeparatedHeaderValue>
     */
    public function getSorter() : ArraySortable
    {
        return static::createClosureSorter(function (array $values) : array {
            // Allocate according to classification
            $classes = [];
            /** @var ColonSeparatedHeaderValue $value */
            foreach ($values as $value) {
                $classKey = $this->getClassifierKey($value);
                $classValues = $classes[$classKey] ?? [];
                $classValues[] = $value;
                $classes[$classKey] = $classValues;
            }

            // Defer to parent sorter
            if ($this->parent !== null) {
                $parentSorter = $this->parent->getSorter();
                $retClasses = [];
                foreach ($classes as $classKey => $classValues) {
                    $retClasses[$classKey] = $parentSorter->sort($classValues);
                }
                $classes = $retClasses;
            }

            // Sort
            $this->sortClassifiers($classes);

            // Flatten and return
            $ret = [];
            foreach ($classes as $classKey => $classValues) {
                _used($classKey);
                foreach ($classValues as $classValue) {
                    $ret[] = $classValue;
                }
            }
            return $ret;
        });
    }


    /**
     * Get classifier key for given value
     * @param ColonSeparatedHeaderValue $value
     * @return string|int
     */
    protected abstract function getClassifierKey(ColonSeparatedHeaderValue $value) : string|int;


    /**
     * Sort the classes
     * @param array<string|int, array<ColonSeparatedHeaderValue>> $classes
     * @return void
     */
    protected abstract function sortClassifiers(array &$classes) : void;


    /**
     * Create a closure sorter
     * @param callable(array<ColonSeparatedHeaderValue>):array<ColonSeparatedHeaderValue> $fn
     * @return ArraySortable<ColonSeparatedHeaderValue>
     */
    private static function createClosureSorter(callable $fn) : ArraySortable
    {
        return new class($fn) implements ArraySortable {
            /**
             * Constructor
             * @param Closure $fn
             */
            public function __construct(
                protected Closure $fn,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function sort(array $values) : array
            {
                return ($this->fn)($values);
            }
        };
    }


    /**
     * Create an instance
     * @param HeaderValueClassifier|null $parent
     * @return static
     */
    public static function create(?self $parent = null) : static
    {
        return new static($parent);
    }
}