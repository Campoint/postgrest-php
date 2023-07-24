<?php

declare(strict_types=1);

namespace PostgrestPhp\Client;

use PostgrestPhp\Client\Base\PostgrestBaseClient;
use PostgrestPhp\Client\Base\PostgrestClientInterface;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;
use PostgrestPhp\Response\PostgrestResponse;
use React\Promise\PromiseInterface;

/**
 * PostgrestAsyncClient is an asynchronous client for PostgREST.
 */
class PostgrestAsyncClient extends PostgrestBaseClient implements PostgrestClientInterface
{
    /**
     * Authenticate the client asynchronously using stored procedure.
     *
     * @return PromiseInterface<true> The response.
     * @link https://postgrest.org/en/stable/how-tos/sql-user-management.html#logins
     */
    public function auth(): PromiseInterface
    {
        return $this->_auth();
    }

    /**
     * Call a stored procedure asynchronously.
     *
     * @param string $functionName The function name.
     * @param array<string, mixed> $params The parameters for the function.
     * @param string $schemaName The schema name of the function.
     * @return PromiseInterface<PostgrestResponse> The response.
     * @link https://postgrest.org/en/stable/references/api/stored_procedures.html
     */
    public function call(
        string $functionName,
        array $params = [],
        string $schemaName = 'public',
        bool $skipAuth = false
    ): PromiseInterface {
        return $this->_call($functionName, $params, $schemaName, $skipAuth);
    }

    /**
     * Run a request asynchronously.
     *
     * @param PostgrestRequestBuilder $requestBuilder The request builder.
     * @param bool $skipAuth Whether to skip authentication (only applied when autoAuth enabled).
     * @return PromiseInterface<PostgrestResponse> The response.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html
     */
    public function run(PostgrestRequestBuilder $requestBuilder, bool $skipAuth = false): PromiseInterface
    {
        return $this->_run($requestBuilder, $skipAuth);
    }
}
