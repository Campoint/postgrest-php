<?php

declare(strict_types=1);

namespace PostgrestPhp\Client;

use PostgrestPhp\Client\Base\PostgrestBaseClient;
use PostgrestPhp\Client\Base\PostgrestClientInterface;
use PostgrestPhp\Client\Exceptions\FailedAuthException;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;
use PostgrestPhp\Response\Exceptions\PostgrestErrorException;
use PostgrestPhp\Response\PostgrestResponse;
use function React\Async\await;

/**
 * PostgrestSyncClient is a synchronous client for PostgREST.
 */
class PostgrestSyncClient extends PostgrestBaseClient implements PostgrestClientInterface
{
    /**
     * Authenticate the client synchronously using stored procedure
     *
     * @return true When the authentication was successful.
     * @throws FailedAuthException If the authentication fails.
     * @link https://postgrest.org/en/stable/how-tos/sql-user-management.html#logins
     */
    public function auth(): true
    {
        await($this->_auth());
        return true;
    }

    /**
     * Call a stored procedure synchronously.
     *
     * @param string $functionName The function name.
     * @param array<string, mixed> $params The parameters for the function.
     * @param string $schemaName The schema name of the function.
     * @return PostgrestResponse The response.
     * @throws FailedAuthException If the request fails due to authentication (only applicable with autoAuth enabled).
     * @throws PostgrestErrorException If the request fails.
     * @link https://postgrest.org/en/stable/references/api/stored_procedures.html
     */
    public function call(
        string $functionName,
        array $params = [],
        string $schemaName = 'public',
        bool $skipAuth = false
    ): PostgrestResponse {
        return await($this->_call($functionName, $params, $schemaName, $skipAuth));
    }

    /**
     * Run a request synchronously.
     *
     * @param PostgrestRequestBuilder $requestBuilder The request builder.
     * @param bool $skipAuth Whether to skip authentication.
     * @return PostgrestResponse The response.
     * @throws FailedAuthException If the request fails due to authentication (only applicable with autoAuth enabled).
     * @throws PostgrestErrorException If the request fails.
     * @link https://postgrest.org/en/stable/references/api/tables_views.html
     */
    public function run(PostgrestRequestBuilder $requestBuilder, bool $skipAuth = false): PostgrestResponse
    {
        return await($this->_run($requestBuilder, $skipAuth));
    }
}
