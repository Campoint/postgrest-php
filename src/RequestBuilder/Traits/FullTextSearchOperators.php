<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Traits;

use PostgrestPhp\RequestBuilder\Enums\FilterOperators;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;

/**
 * Trait to represent full text search operators.
 */
trait FullTextSearchOperators
{
    /**
     * Full text search operator.
     *
     * @param string $columnName The name of the column.
     * @param string $value The value to use.
     * @param string|null $language The language to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function fts(string $columnName, string $value, ?string $language = null): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(FilterOperators::FULL_TEXT_SEARCH, $this->negateNextFilter);
        return $this->ftsHelper($operator, $columnName, $value, $language);
    }

    /**
     * Plain full text search operator.
     *
     * @param string $columnName The name of the column.
     * @param string $value The value to use.
     * @param string|null $language The language to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function plfts(string $columnName, string $value, ?string $language = null): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(FilterOperators::PLAIN_FULL_TEXT_SEARCH, $this->negateNextFilter);
        return $this->ftsHelper($operator, $columnName, $value, $language);
    }

    /**
     * Phrase full text search operator.
     *
     * @param string $columnName The name of the column.
     * @param string $value The value to use.
     * @param string|null $language The language to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function phfts(string $columnName, string $value, ?string $language = null): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(FilterOperators::PHRASE_FULL_TEXT_SEARCH, $this->negateNextFilter);
        return $this->ftsHelper($operator, $columnName, $value, $language);
    }

    /**
     * Websearch full text search operator.
     *
     * @param string $columnName The name of the column.
     * @param string $value The value to use.
     * @param string|null $language The language to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
     */
    public function wfts(string $columnName, string $value, ?string $language = null): PostgrestRequestBuilder
    {
        $operator = $this->negateOperator(FilterOperators::WEBSEARCH_FULL_TEXT_SEARCH, $this->negateNextFilter);
        return $this->ftsHelper($operator, $columnName, $value, $language);
    }

    /**
     * Full text search operator helper.
     * Prevents code duplication.
     * @param string $operator The operator to use.
     * @param string $columnName The name of the column.
     * @param string $value The value to use.
     * @param null|string $language The language to use.
     * @return PostgrestRequestBuilder The PostgrestRequestBuilder instance.
     */
    private function ftsHelper(
        string $operator,
        string $columnName,
        string $value,
        ?string $language = null
    ): PostgrestRequestBuilder {
        if ($language !== null) {
            $operator .= sprintf('(%s)', $language);
        }
        return $this->filterRawColumn($columnName, $operator, $this->helper::escapeString($value));
    }
}
