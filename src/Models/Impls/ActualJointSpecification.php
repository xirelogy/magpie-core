<?php

namespace Magpie\Models\Impls;

use Magpie\Models\Concepts\QueryArgumentable;
use Magpie\Models\Enums\JointType;
use Magpie\Models\Schemas\TableSchema;

/**
 * The joint specification
 * @internal
 */
class ActualJointSpecification extends BaseJointSpecifiable implements QueryArgumentable
{
    /**
     * @var JointType Joint type between the two tables
     */
    protected readonly JointType $type;


    /**
     * Constructor
     * @param TableSchema $baseSchema
     * @param TableSchema $jointSchema
     * @param JointType $type
     */
    public function __construct(TableSchema $baseSchema, TableSchema $jointSchema, JointType $type)
    {
        parent::__construct($baseSchema, $jointSchema);

        $this->type = $type;
    }


    /**
     * @inheritDoc
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        $q = $context->grammar?->getIdentifierQuote();

        $ret = new QueryStatement(strtoupper($this->type->value) . ' JOIN ' . $q->quote($this->jointSchema->getName()));

        $ret->appendJoinIfNotEmpty(' ON ', $this->clause->_finalize($context));

        return $ret;
    }
}