<?php

namespace Magpie\Models;

use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use Magpie\Models\Concepts\Modelable;
use Magpie\Models\Concepts\ModelAccessible;

/**
 * Common multi-model accessor
 */
abstract class BaseModelAccessor implements Modelable, ModelAccessible
{
    use CommonPackable;

    /**
     * @var array<string, Model> All mapped models
     */
    protected array $models;


    /**
     * Constructor
     * @param array<string, Model> $models
     */
    protected function __construct(array $models)
    {
        $this->models = $models;
    }


    /**
     * @inheritDoc
     */
    public function getModels() : iterable
    {
        yield from $this->models;
    }


    /**
     * @inheritDoc
     */
    public function accessModel(string $tableClassName) : ?Model
    {
        return $this->models[$tableClassName] ?? null;
    }


    /**
     * @inheritDoc
     */
    public function accessModels(string ...$tableClassNames) : array
    {
        $ret = [];
        foreach ($tableClassNames as $tableClassName) {
            $ret[] = $this->accessModel($tableClassName);
        }

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->models = $this->getModels();
    }
}