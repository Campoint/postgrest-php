<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Traits;

use PostgrestPhp\RequestBuilder\Enums\Operator;
use PostgrestPhp\RequestBuilder\Exceptions\FilterLogicException;
use PostgrestPhp\RequestBuilder\Exceptions\NotUnifiedValuesException;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;

/**
 * Trait to represent logic modifier operators.
 */
trait ModifierOperators
{
    private bool $negateNextFilter = false;

    private bool $allNextFilter = false;

    private bool $anyNextFilter = false;

    /**
     * Negate next operator.
     *
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function not(): PostgrestRequestBuilder
    {
        $this->negateNextFilter = ! $this->negateNextFilter;
        return $this;
    }

    /**
     * Enable 'all' modifier for next operator (which supports it).
     *
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If any() modifier already active.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function all(): PostgrestRequestBuilder
    {
        if ($this->anyNextFilter) {
            throw new FilterLogicException(FilterLogicException::BOTH_MODIFIERS_ACTIVE);
        }
        $this->allNextFilter = true;
        return $this;
    }

    /**
     * Enable 'any' modifier for next operator (which supports it).
     *
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @throws FilterLogicException If all() modifier already active.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function any(): PostgrestRequestBuilder
    {
        if ($this->allNextFilter) {
            throw new FilterLogicException(FilterLogicException::BOTH_MODIFIERS_ACTIVE);
        }
        $this->anyNextFilter = true;
        return $this;
    }

    /**
     * Prepend negation to operator if necessary.
     *
     * @param Operator $operator The operator to use.
     * @param bool $negate Whether to negate the operator.
     * @return string The operator.
     */
    private function negateOperator(Operator $operator, bool $negate): string
    {
        if ($negate) {
            $this->negateNextFilter = false;
            return sprintf('%s.%s', Operator::NOT->value, $operator->value);
        }
        return $operator->value;
    }

    /**
     * Prepend 'all' modifier to operator.
     *
     * @param string $operator The operator to use.
     * @return string The operator.
     */
    private function allOperator(string $operator): string
    {
        $this->allNextFilter = false;
        return sprintf('%s(%s)', $operator, Operator::ALL->value);
    }

    /**
     * Prepend 'any' modifier to operator.
     *
     * @param string $operator The operator to use.
     * @return string The operator.
     */
    private function anyOperator(string $operator): string
    {
        $this->allNextFilter = false;
        return sprintf('%s(%s)', $operator, Operator::ANY->value);
    }

    /**
     * Check whether all values in an array are of same type and check whether a modifier is active.
     *
     * @param int $numValues The number of values in the array.
     * @param string[]|int[]|float[] $value The array to check.
     * @throws NotUnifiedValuesException If the value types are not unified.
     * @throws FilterLogicException If multiple values are passed without modifier like all() or any().
     */
    private function checkOperatorModifier(int $numValues, array $value): void
    {
        if ($numValues > 1 && ! $this->helper::checkUnifiedValueTypes($value)) {
            throw new NotUnifiedValuesException(NotUnifiedValuesException::NOT_UNIFIED_ARRAY);
        }
        if ($numValues > 1 && ! $this->allNextFilter && ! $this->anyNextFilter) {
            throw new FilterLogicException(FilterLogicException::MISSING_MODIFIER);
        }
    }

    /**
     * Apply active modifier to operator.
     *
     * @param string $operator The operator to use.
     * @return string The operator.
     */
    private function applyOperatorModifier(string $operator): string
    {
        if ($this->allNextFilter) {
            return $this->allOperator($operator);
        }
        return $this->anyOperator($operator);
    }
}
