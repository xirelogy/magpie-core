<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Schemas\TableSchema;

/**
 * Model storage provider
 */
interface ModelStorageProvidable
{
    /**
     * Table schema
     * @return TableSchema
     * @throws SafetyCommonException
     */
    public function getTableSchema() : TableSchema;


    /**
     * If storage corresponds to newly created model
     * @return bool
     */
    public function isNew() : bool;


    /**
     * All attributes
     * @return iterable<string, mixed>
     */
    public function getAttributes() : iterable;


    /**
     * Check if attributes exist for model
     * @param string $key
     * @return bool
     */
    public function hasAttribute(string $key) : bool;


    /**
     * Get attributes for model
     * @param string $key
     * @return mixed
     * @throws SafetyCommonException
     */
    public function getAttribute(string $key) : mixed;


    /**
     * Set attributes for model
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws SafetyCommonException
     */
    public function setAttribute(string $key, mixed $value) : void;


    /**
     * The identifying attributes to identify model of current storage in the database
     * @return iterable<string, mixed>
     */
    public function getIdentifyingAttributes() : iterable;


    /**
     * All changed attributes
     * @return iterable<string, mixed>
     */
    public function getChangedAttributes() : iterable;


    /**
     * Reset changes tracked
     * @param array<string, mixed> $savedAttributes
     * @return void
     */
    public function resetChanges(array $savedAttributes) : void;


    /**
     * Destroy the storage
     * @return void
     */
    public function destroy() : void;
}