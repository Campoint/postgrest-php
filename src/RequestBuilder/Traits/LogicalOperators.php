<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Traits;

use PostgrestPhp\RequestBuilder\Enums\LogicOperators;
use PostgrestPhp\RequestBuilder\LogicOperatorCondition;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;
use Stringable;

/**
 * Trait to represent logic operators like and/or.
 * Useful for building more complex queries,
 * but you will need to escape the strings yourself.
 */
trait LogicalOperators
{
    /**
     * Basic implementation for 'and' logical operator.
     * You will need to provide each condition as a string,
     * and escape it yourself. Alternatively, you can use
     * the LogicOperatorCondition class to build the condition,
     * but this route does not support nested and/or conditions.
     *
     * @param Stringable ...$condition The conditions to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     */
    public function and(Stringable ...$condition): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(LogicOperators::AND, $this->negateNextFilter);
        $filter = sprintf('%s=(%s)', $operator, implode(',', $condition));
        return $this->filterRaw($filter);
    }

    /**
     * Basic implementation for 'or' logical operator.
     * You will need to provide each condition as a string,
     * and escape it yourself. Alternatively, you can use
     * the LogicOperatorCondition class to build the condition,
     * but this route does not support nested and/or conditions.
     *
     * @param Stringable ...$condition The conditions to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     */
    public function or(Stringable ...$condition): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(LogicOperators::OR, $this->negateNextFilter);
        $filter = sprintf('%s=(%s)', $operator, implode(',', $condition));
        return $this->filterRaw($filter);
    }
}
