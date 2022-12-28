<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Models\Concepts\ModelStorageProvidable;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Model;
use Magpie\Models\Schemas\TableSchema;

/**
 * A model storage provider that is deferring
 * @internal
 */
abstract class DeferringModelStorageProvider implements ModelStorageProvidable
{
    /**
     * @var TableSchema Associated table schema
     */
    protected readonly TableSchema $tableSchema;


    /**
     * Constructor
     * @param TableSchema $tableSchema
     */
    protected function __construct(TableSchema $tableSchema)
    {
        $this->tableSchema = $tableSchema;
    }


    /**
     * Defer the storage provider initialization
     * @param Model $instance
     * @param string $connection
     * @return ModelStorageProvidable
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public abstract function defer(Model $instance, string $connection) : ModelStorageProvidable;


    /**
     * @inheritDoc
     */
    public function getTableSchema() : TableSchema
    {
        return $this->tableSchema;
    }


    /**
     * @inheritDoc
     */
    public function isNew() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getAttributes() : iterable
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function hasAttribute(string $key) : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function getAttribute(string $key) : mixed
    {
        throw new UnexpectedException();
    }


    /**
     * @inheritDoc
     */
    public function setAttribute(string $key, mixed $value) : void
    {
        // nop
    }


    /**
     * @inheritDoc
     */
    public function getIdentifyingAttributes() : iterable
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getChangedAttributes() : iterable
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function resetChanges(array $savedAttributes) : void
    {
        // nop
    }


    /**
     * @inheritDoc
     */
    public function destroy() : void
    {
        // nop
    }
}