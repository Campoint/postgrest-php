<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Traits;

use PostgrestPhp\RequestBuilder\Enums\Operator;
use PostgrestPhp\RequestBuilder\Exceptions\FilterLogicException;
use PostgrestPhp\RequestBuilder\Exceptions\NotUnifiedValuesException;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;

/**
 * Trait to represent greater/less operators.
 */
trait GreaterLessOperators
{
    /**
     * Greater than operator.
     *
     * @param string $columnName The name of the column.
     * @param string|int|float ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function gt(string $columnName, string|int|float ...$value): PostgrestRequestBuilder
    {
        return $this->greaterLessHelper(Operator::GREATER_THAN, $columnName, ...$value);
    }

    /**
     * Greater than or equal operator.
     *
     * @param string $columnName The name of the column.
     * @param string|int|float ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function gte(string $columnName, string|int|float ...$value): PostgrestRequestBuilder
    {
        return $this->greaterLessHelper(Operator::GREATER_THAN_OR_EQUAL, $columnName, ...$value);
    }

    /**
     * Less than operator.
     *
     * @param string $columnName The name of the column.
     * @param string|int|float ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function lt(string $columnName, string|int|float ...$value): PostgrestRequestBuilder
    {
        return $this->greaterLessHelper(Operator::LESS_THAN, $columnName, ...$value);
    }

    /**
     * Less than or equal operator.
     *
     * @param string $columnName The name of the column.
     * @param string|int|float ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function lte(string $columnName, string|int|float ...$value): PostgrestRequestBuilder
    {
        return $this->greaterLessHelper(Operator::LESS_THAN_OR_EQUAL, $columnName, ...$value);
    }

    /**
     * Greater than operator helper.
     * Prevents code duplication.
     * @param Operator $op The operator to use.
     * @param string $columnName The name of the column.
     * @param string|int|float ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @throws NotUnifiedValuesException If the value types are not unified.
     */
    private function greaterLessHelper(
        Operator $op,
        string $columnName,
        string|int|float ...$value,
    ): PostgrestRequestBuilder {
        $numValues = count($value);
        $this->checkOperatorModifier($numValues, $value);
        $operator = $this->negateOperator($op, $this->negateNextFilter);
        if ($numValues > 1) {
            $operator = $this->applyOperatorModifier($operator);
            $transformedValue = $this->helper::implodeWithBraces($value, '{', '}');
            return $this->filterRawColumn($columnName, $operator, $transformedValue);
        }
        $transformedValue = $this->helper::escapeString($value[0]);
        return $this->filterRawColumn($columnName, $operator, $transformedValue);
    }
}
