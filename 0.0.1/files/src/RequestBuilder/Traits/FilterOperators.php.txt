<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Traits;

use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;

/**
 * Trait to represent filter helpers.
 */
trait FilterOperators
{
    use ModifierOperators;
    use EqualityOperators;
    use GreaterLessOperators;
    use PatternMatchingOperators;
    use FullTextSearchOperators;
    use ArrayRangeOperators;

    /**
     * Raw filter function for columns.
     *
     * @param string $columnName The name of the column.
     * @param string $operator The operator to use.
     * @param string $value The value to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     */
    public function filterRawColumn(string $columnName, string $operator, string $value): PostgrestRequestBuilder
    {
        return $this->filterRaw(sprintf('%s=%s.%s', $columnName, $operator, $value));
    }

    /**
     * Raw filter function for tables.
     *
     * @param string $filter The filter to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     */
    public function filterRaw(string $filter): PostgrestRequestBuilder
    {
        array_push($this->filters, $filter);
        return $this;
    }
}
