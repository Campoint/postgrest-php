<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Traits;

use PostgrestPhp\RequestBuilder\Enums\Operator;
use PostgrestPhp\RequestBuilder\Exceptions\FilterLogicException;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;

/**
 * Trait to represent pattern matching operators.
 */
trait PatternMatchingOperators
{
    /**
     * Like operator.
     *
     * @param string $columnName The name of the column.
     * @param string ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function like(string $columnName, string ...$value): PostgrestRequestBuilder
    {
        return $this->patternMatchingHelper(Operator::LIKE, $columnName, ...$value);
    }

    /**
     * ILike operator.
     *
     * @param string $columnName The name of the column.
     * @param string ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function ilike(string $columnName, string ...$value): PostgrestRequestBuilder
    {
        return $this->patternMatchingHelper(Operator::ILIKE, $columnName, ...$value);
    }

    /**
     * Match operator.
     *
     * @param string $columnName The name of the column.
     * @param string ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function match(string $columnName, string ...$value): PostgrestRequestBuilder
    {
        return $this->patternMatchingHelper(Operator::MATCH, $columnName, ...$value);
    }

    /**
     * IMatch operator.
     *
     * @param string $columnName The name of the column.
     * @param string ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function imatch(string $columnName, string ...$value): PostgrestRequestBuilder
    {
        return $this->patternMatchingHelper(Operator::IMATCH, $columnName, ...$value);
    }

    /**
     * Pattern matching operator helper.
     * Prevents code duplication.
     * @param Operator $op The operator to use.
     * @param string $columnName The name of the column.
     * @param string ...$value The value(s) to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     */
    private function patternMatchingHelper(
        Operator $op,
        string $columnName,
        string ...$value,
    ): PostgrestRequestBuilder {
        $numValues = count($value);
        if ($numValues > 1 && ! $this->allNextFilter && ! $this->anyNextFilter) {
            throw new FilterLogicException(FilterLogicException::MISSING_MODIFIER);
        }
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
