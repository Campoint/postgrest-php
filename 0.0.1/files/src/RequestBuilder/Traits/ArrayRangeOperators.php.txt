<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Traits;

use PostgrestPhp\RequestBuilder\Enums\Operator;
use PostgrestPhp\RequestBuilder\Enums\OverlapType;
use PostgrestPhp\RequestBuilder\Exceptions\NotUnifiedValuesException;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;

/**
 * Trait to represent array range operators.
 */
trait ArrayRangeOperators
{
    /**
     * Contains operator.
     *
     * @param string $columnName The name of the column.
     * @param string[]|int[]|float[] $value The value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function cs(string $columnName, array $value): PostgrestRequestBuilder
    {
        return $this->arrayHelper(Operator::CONTAINS, $columnName, $value, ['{', '}']);
    }

    /**
     * Contained in operator.
     *
     * @param string $columnName The name of the column.
     * @param string[]|int[]|float[] $value The value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function cd(string $columnName, array $value): PostgrestRequestBuilder
    {
        return $this->arrayHelper(Operator::CONTAINED_IN, $columnName, $value, ['{', '}']);
    }

    /**
     * Overlap operator.
     *
     * @param string $columnName The name of the column.
     * @param OverlapType $overlapType The type of overlap to use
     * @param string|int|float ...$value Values for overlap operation
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function ov(
        string $columnName,
        OverlapType $overlapType,
        string|int|float ...$value
    ): PostgrestRequestBuilder {
        if (! $this->helper::checkUnifiedValueTypes($value)) {
            throw new NotUnifiedValuesException(NotUnifiedValuesException::NOT_UNIFIED_ARRAY);
        }
        $operator = $this->negateOperator(Operator::OVERLAP, $this->negateNextFilter);
        $transformedValue = $this->helper::implodeWithBraces($value, $overlapType->value[0], $overlapType->value[1]);
        return $this->filterRawColumn($columnName, $operator, $transformedValue);
    }

    /**
     * Strictly left of operator.
     *
     * @param string $columnName The name of the column.
     * @param int|float $start The start value to use.
     * @param int|float $end The end value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the start and end types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function sl(string $columnName, int|float $start, int|float $end): PostgrestRequestBuilder
    {
        return $this->rangeHelper(Operator::STRICTLY_LEFT_OF, $columnName, $start, $end);
    }

    /**
     * Strictly right of operator.
     *
     * @param string $columnName The name of the column.
     * @param int|float $start The start value to use.
     * @param int|float $end The end value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the start and end types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function sr(string $columnName, int|float $start, int|float $end): PostgrestRequestBuilder
    {
        return $this->rangeHelper(Operator::STRICTLY_RIGHT_OF, $columnName, $start, $end);
    }

    /**
     * Not extend to right of operator.
     *
     * @param string $columnName The name of the column.
     * @param int|float $start The start value to use.
     * @param int|float $end The end value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the start and end types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function nxr(string $columnName, int|float $start, int|float $end): PostgrestRequestBuilder
    {
        return $this->rangeHelper(Operator::NOT_EXTEND_TO_RIGHT, $columnName, $start, $end);
    }

    /**
     * Not extend to left of operator.
     *
     * @param string $columnName The name of the column.
     * @param int|float $start The start value to use.
     * @param int|float $end The end value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the start and end types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function nxl(string $columnName, int|float $start, int|float $end): PostgrestRequestBuilder
    {
        return $this->rangeHelper(Operator::NOT_EXTEND_TO_LEFT, $columnName, $start, $end);
    }

    /**
     * Adjacent operator.
     *
     * @param string $columnName The name of the column.
     * @param int|float $start The start value to use.
     * @param int|float $end The end value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the start and end types are not unified.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function adj(string $columnName, int|float $start, int|float $end): PostgrestRequestBuilder
    {
        return $this->rangeHelper(Operator::ADJACENT, $columnName, $start, $end);
    }

    /**
     * Array operator helper.
     * Prevents code duplication.
     * @param Operator $op The operator to use.
     * @param string $columnName The name of the column.
     * @param string[]|int[]|float[] $value The value to use.
     * @param string[] $braces The braces to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the value types are not unified.
     */
    private function arrayHelper(
        Operator $op,
        string $columnName,
        array $value,
        array $braces,
    ): PostgrestRequestBuilder {
        if (! $this->helper::checkUnifiedValueTypes($value)) {
            throw new NotUnifiedValuesException(NotUnifiedValuesException::NOT_UNIFIED_ARRAY);
        }
        $operator = $this->negateOperator($op, $this->negateNextFilter);
        $transformedValue = $this->helper::implodeWithBraces($value, $braces[0], $braces[1]);
        return $this->filterRawColumn($columnName, $operator, $transformedValue);
    }

    /**
     * Range operator helper.
     * Prevents code duplication.
     * @param Operator $op The operator to use.
     * @param string $columnName The name of the column.
     * @param int|float $start The start value to use.
     * @param int|float $end The end value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws NotUnifiedValuesException If the start and end types are not unified.
     */
    private function rangeHelper(
        Operator $op,
        string $columnName,
        int|float $start,
        int|float $end,
    ): PostgrestRequestBuilder {
        if (gettype($start) !== gettype($end)) {
            throw new NotUnifiedValuesException(NotUnifiedValuesException::NOT_UNIFIED_START_END);
        }
        $operator = $this->negateOperator($op, $this->negateNextFilter);
        $transformedValue = $this->helper::implodeWithBraces([$start, $end], '(', ')');
        return $this->filterRawColumn($columnName, $operator, $transformedValue);
    }
}
