<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Traits;

use PostgrestPhp\RequestBuilder\Enums\IsCheck;
use PostgrestPhp\RequestBuilder\Enums\Operator;
use PostgrestPhp\RequestBuilder\Exceptions\FilterLogicException;
use PostgrestPhp\RequestBuilder\Exceptions\NotUnifiedValuesException;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;

/**
 * Trait to represent equality operators.
 */
trait EqualityOperators
{
    /**
     * Equals operator.
     *
     * @param string $columnName The name of the column.
     * @param string|int|float ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function eq(string $columnName, string|int|float ...$value): PostgrestRequestBuilder
    {
        $numValues = count($value);
        $this->checkOperatorModifier($numValues, $value);
        $operator = $this->negateOperator(Operator::EQUAL, $this->negateNextFilter);
        if ($numValues > 1) {
            $operator = $this->applyOperatorModifier($operator);
            $transformedValue = $this->helper::implodeWithBraces($value, '{', '}');
            return $this->filterRawColumn($columnName, $operator, $transformedValue);
        }
        $transformedValue = $this->helper::escapeString($value[0]);
        return $this->filterRawColumn($columnName, $operator, $transformedValue);
    }

    /**
     * Not equal operator.
     *
     * @param string $columnName The name of the column.
     * @param string|int|float ...$value The value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function neq(string $columnName, string|int|float $value): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(Operator::NOT_EQUAL, $this->negateNextFilter);
        return $this->filterRawColumn($columnName, $operator, $this->helper::escapeString($value));
    }

    /**
     * Is operator.
     *
     * @param string $columnName The name of the column.
     * @param IsCheck $value The value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function is(string $columnName, IsCheck $value): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(Operator::IS, $this->negateNextFilter);
        return $this->filterRawColumn($columnName, $operator, $value->value);
    }

    /**
     * In operator.
     *
     * @param string $columnName The name of the column.
     * @param string|int|float ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function in(string $columnName, string|int|float ...$value): PostgrestRequestBuilder
    {
        if (! $this->helper::checkUnifiedValueTypes($value)) {
            throw new NotUnifiedValuesException(NotUnifiedValuesException::NOT_UNIFIED_ARRAY);
        }
        $operator = $this->negateOperator(Operator::IN, $this->negateNextFilter);
        $transformedValue = $this->helper::implodeWithBraces($value, '(', ')');
        return $this->filterRawColumn($columnName, $operator, $transformedValue);
    }

    /**
     * Is distinct from operator.
     *
     * @param string $columnName The name of the column.
     * @param string|int|float $value The value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function isdistinct(string $columnName, string|int|float $value): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(Operator::IS_DISTINCT_FROM, $this->negateNextFilter);
        return $this->filterRawColumn($columnName, $operator, $this->helper::escapeString($value));
    }
}
