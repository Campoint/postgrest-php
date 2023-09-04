<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder;

use PostgrestPhp\RequestBuilder\Enums\CountType;
use PostgrestPhp\RequestBuilder\Enums\DataFormat;
use PostgrestPhp\RequestBuilder\Enums\DuplicateResolution;
use PostgrestPhp\RequestBuilder\Enums\OrderNulls;
use PostgrestPhp\RequestBuilder\Enums\ReturnFormat;
use PostgrestPhp\RequestBuilder\Exceptions\DataEncodingException;
use PostgrestPhp\RequestBuilder\Exceptions\FilterLogicException;
use PostgrestPhp\RequestBuilder\Exceptions\NotUnifiedValuesException;
use PostgrestPhp\RequestBuilder\Traits\FilterOperators;

/**
 * PostgREST request builder.
 * Class for dynamically building PostgREST requests.
 */
class PostgrestRequestBuilder
{
    use FilterOperators;

    /**
     * @var string[]
     */
    protected array $filters;

    private string $method;

    /**
     * @var array<string, string>
     */
    private array $headers;

    private ?string $body;

    private Helper $helper;

    /**
     * Create a new PostgREST request builder.
     *
     * @param string $schemaName The schema name to use for the request.
     * @param string $tableName The table name to use for the request.
     */
    final public function __construct(
        private string $schemaName,
        private string $tableName,
    ) {
        $this->headers = [
            'Accept-Profile' => $this->schemaName,
            'Content-Profile' => $this->schemaName,
        ];
        $this->filters = [];
        $this->method = '';
        $this->body = null;
        $this->helper = new Helper();
    }

    /**
     * Get header value by name.
     *
     * @param string $name The name of the header.
     * @return string|null The value of the header, or null if it does not exist.
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Set specific header.
     *
     * @param string $name The name of the header.
     * @param string $value The value of the header.
     * @return PostgrestRequestBuilder The request builder.
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set the columns to select from the table.
     *
     * @param string ...$columns The columns to select.
     * @return PostgrestRequestBuilder The request builder.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#read
     */
    public function select(string ...$columns): self
    {
        $this->method = 'GET';
        if (count($columns) > 0) {
            $selectFilter = sprintf('select=%s', implode(',', $columns));
            array_push($this->filters, $selectFilter);
        }
        return $this;
    }

    /**
     * Insert data into the table.
     *
     * @param array<string, mixed>|array<array<string, mixed>> $data The data to insert.
     * @param string[] $columns Which columns to insert
     * @param bool $missingAsDefault Whether to use the default value for missing columns.
     * @param DataFormat $dataFormat The format in which the data will be sent.
     * @param ReturnFormat $returnFormat The format in which the response will be returned.
     * @return PostgrestRequestBuilder The request builder.
     * @throws NotUnifiedValuesException If the columns are not of the same type.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#insert
     */
    public function insert(
        array $data,
        array $columns = [],
        bool $missingAsDefault = false,
        DataFormat $dataFormat = DataFormat::JSON,
        ReturnFormat $returnFormat = ReturnFormat::NONE,
    ): self {
        if (count($columns) > 0) {
            if (! $this->helper::checkUnifiedValueTypes($columns)) {
                throw new NotUnifiedValuesException(NotUnifiedValuesException::NOT_UNIFIED_ARRAY);
            }
            $columnsFilter = sprintf('columns=%s', $this->helper::implodeWithBraces($columns, '', ''));
            $this->filterRaw($columnsFilter);
        }
        return $this->_insert(
            $data,
            $missingAsDefault,
            $dataFormat,
            $returnFormat,
        );
    }

    /**
     * Insert data into the table, or resolve conflict if rows already exists.
     *
     * @param array<string,mixed>|array<array<string,mixed>> $data The data to insert.
     * @param DataFormat $dataFormat The format in which the data will be sent.
     * @param ReturnFormat $returnFormat The format in which the response will be returned.
     * @param DuplicateResolution $duplicateResolution The resolution to use for duplicate rows.
     * @param string[] $onConflict The columns to use for conflict resolution.
     * @return PostgrestRequestBuilder The request builder.
     * @throws FilterLogicException If the duplicate resolution is not set.
     * @throws NotUnifiedValuesException If the onConflict columns are not of the same type.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#upsert
     */
    public function upsert(
        array $data,
        DataFormat $dataFormat = DataFormat::JSON,
        ReturnFormat $returnFormat = ReturnFormat::NONE,
        DuplicateResolution $duplicateResolution = DuplicateResolution::MERGE,
        array $onConflict = [],
    ): self {
        if ($duplicateResolution === DuplicateResolution::NONE) {
            throw new FilterLogicException(FilterLogicException::DUPLICATE_RESOLUTION_REQUIRED);
        }
        if (count($onConflict) > 0) {
            if (! $this->helper::checkUnifiedValueTypes($onConflict)) {
                throw new NotUnifiedValuesException(NotUnifiedValuesException::NOT_UNIFIED_ARRAY);
            }
            $onConflictFilter = sprintf('on_conflict=%s', $this->helper::implodeWithBraces($onConflict, '', ''));
            $this->filterRaw($onConflictFilter);
        }

        return $this->_insert(
            $data,
            false,
            $dataFormat,
            $returnFormat,
            $duplicateResolution,
        );
    }

    /**
     * Update the table.
     *
     * @param array<string, mixed> $data Key-value pairs (Column + Value).
     * @param ReturnFormat $returnFormat The format in which the response will be returned.
     * @return PostgrestRequestBuilder The request builder.
     * @throws DataEncodingException If the data could not be encoded as JSON.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#update
     */
    public function update(array $data, ReturnFormat $returnFormat = ReturnFormat::NONE): self
    {
        $this->method = 'PATCH';
        $this->headers['Content-Type'] = 'application/json';
        $tmp = json_encode($data);
        if ($tmp === false) {
            throw new DataEncodingException(DataEncodingException::JSON_ENCODING_FAILED);
        }
        $this->body = $tmp;
        $this->setReturnFormat($returnFormat);
        return $this;
    }

    /**
     * Delete from the table.
     *
     * @param ReturnFormat $returnFormat The format in which the response will be returned.
     * @return PostgrestRequestBuilder The request builder.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#delete
     */
    public function delete(ReturnFormat $returnFormat = ReturnFormat::NONE): self
    {
        $this->method = 'DELETE';
        $this->setReturnFormat($returnFormat);
        return $this;
    }

    /**
     * Order results by the given columns.
     *
     * @param OrderColumn ...$columns The columns by which to order.
     * @return PostgrestRequestBuilder The request builder.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#ordering
     */
    public function orderBy(OrderColumn ...$columns): self
    {
        $allColumnOrders = [];
        foreach ($columns as $column) {
            $order = sprintf('%s.%s', $this->helper::escapeString($column->name), $column->direction->value);
            if ($column->orderNulls !== OrderNulls::NONE) {
                $order .= sprintf('.%s', $column->orderNulls->value);
            }
            array_push($allColumnOrders, $order);
        }
        $orderFilter = sprintf('order=%s', implode(',', $allColumnOrders));
        return $this->filterRaw($orderFilter);
    }

    /**
     * Set the limit for the results.
     *
     * @param int $limit The limit.
     * @return PostgrestRequestBuilder The request builder.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#limits-and-pagination
     */
    public function limit(int $limit): self
    {
        return $this->filterRaw(sprintf('limit=%d', $limit));
    }

    /**
     * Set the offset for the results.
     *
     * @param int $offset The offset.
     * @return PostgrestRequestBuilder The request builder.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#limits-and-pagination
     */
    public function offset(int $offset): self
    {
        return $this->filterRaw(sprintf('offset=%d', $offset));
    }

    /**
     * Count the result range.
     *
     * @param CountType $countType The type of count to perform.
     * @return PostgrestRequestBuilder The request builder.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#exact-count
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#planned-count
     * @link https://postgrest.org/en/stable/references/api/tables_views.html#estimated-count
     */
    public function count(CountType $countType): self
    {
        $this->appendPreferHeader($countType->value);
        return $this;
    }

    /**
     * Get data from class which is needed to run the request
     *
     * @return array<int, mixed>
     */
    public function getRequestData(): array
    {
        $queryString = implode('&', $this->filters);
        if (strlen($queryString) > 0) {
            $url = sprintf('%s?%s', $this->tableName, $queryString);
            return [$this->method, $url, $this->headers, $this->body ?? ''];
        }

        return [$this->method, $this->tableName, $this->headers, $this->body ?? ''];
    }

    /**
     * Underlying function which backs insert() and upsert().
     *
     * @param array<array<string,mixed>> $data The data to insert.
     * @param bool $missingAsDefault Whether to use the default value for missing columns.
     * @param DataFormat $dataFormat The format in which the data will be sent.
     * @param ReturnFormat $returnFormat The format in which the response will be returned.
     * @param DuplicateResolution $duplicateResolution The resolution to use for duplicate rows.
     * @return PostgrestRequestBuilder The request builder.
     * @throws DataEncodingException If the data could not be encoded as JSON or the DataFormat chosen is not supported.
     */
    private function _insert(
        array $data,
        bool $missingAsDefault = false,
        DataFormat $dataFormat = DataFormat::JSON,
        ReturnFormat $returnFormat = ReturnFormat::NONE,
        DuplicateResolution $duplicateResolution = DuplicateResolution::NONE,
    ): self {
        $this->method = 'POST';

        $this->encodeData($data, $dataFormat);

        if ($missingAsDefault) {
            $this->setMissingAsDefault();
        }
        $this->setReturnFormat($returnFormat);
        $this->setDuplicateResolution($duplicateResolution);

        return $this;
    }

    /**
     * Encode the given data in the given format.
     *
     * @param array<array<string,mixed>> $data The data to encode.
     * @param DataFormat $dataFormat The format in which to encode the data.
     * @throws DataEncodingException If the data could not be encoded as JSON.
     */
    private function encodeData(array $data, DataFormat $dataFormat): void
    {
        if ($dataFormat === DataFormat::CSV) {
            $this->headers['Content-Type'] = 'text/csv';
            $this->body = $this->helper::convertToCSV($data);
            return;
        }
        $this->headers['Content-Type'] = 'application/json';
        $tmp = json_encode($data);
        if ($tmp === false) {
            throw new DataEncodingException(DataEncodingException::JSON_ENCODING_FAILED);
        }
        $this->body = $tmp;
    }

    /**
     * Set the missing columns to their default values.
     */
    private function setMissingAsDefault(): void
    {
        $this->appendPreferHeader('missing=default');
    }

    /**
     * Set the return format.
     *
     * @param ReturnFormat $returnFormat The return format.
     */
    private function setReturnFormat(ReturnFormat $returnFormat): void
    {
        if ($returnFormat === ReturnFormat::NONE) {
            return;
        }
        $this->appendPreferHeader($returnFormat->value);
    }

    /**
     * Set the duplicate resolution.
     *
     * @param DuplicateResolution $duplicateResolution The duplicate resolution.
     */
    private function setDuplicateResolution(DuplicateResolution $duplicateResolution): void
    {
        if ($duplicateResolution === DuplicateResolution::NONE) {
            return;
        }
        $this->appendPreferHeader($duplicateResolution->value);
    }

    /**
     * Append the Prefer header.
     *
     * @param string $preferHeader The Prefer header.
     */
    private function appendPreferHeader(string $preferHeader): void
    {
        if (! key_exists('Prefer', $this->headers)) {
            $this->headers['Prefer'] = $preferHeader;
            return;
        }
        $this->headers['Prefer'] = sprintf('%s, %s', $this->headers['Prefer'], $preferHeader);
    }
}
