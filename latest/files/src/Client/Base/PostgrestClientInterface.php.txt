<?php

declare(strict_types=1);

namespace PostgrestPhp\Client\Base;

use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;
use PostgrestPhp\Response\PostgrestResponse;
use React\Promise\PromiseInterface;

/**
 * Interface for both synchronous and asynchronous PostgREST clients.
 */
interface PostgrestClientInterface
{
    /**
     * Check whether the client is authenticated.
     */
    public function isAuthenticated(): bool;

    /**
     * Manually set the authentication token.
     *
     * @param string $token The authentication token.
     */
    public function setAuthToken(string $token): void;

    /**
     * Enable auto authentication feature.
     * Will check token expiration with every request.
     *
     * @param int $autoAuthGrace The grace period subtracted from the token expiration time.
     */
    public function enableAutoAuth(int $autoAuthGrace = 300): void;

    /**
     * Disable auto authentication feature
     */
    public function disableAutoAuth(): void;

    /**
     * Authenticate the client.
     *
     * @return ?PromiseInterface<null> Whether the authentication was successful.
     */
    public function auth(): ?PromiseInterface;

    /**
     * Create a new request builder for a table in a schema.
     *
     * @param string $schemaName Name of the schema
     * @param string $tableName Name of the table
     */
    public function from(string $schemaName, string $tableName): PostgrestRequestBuilder;

    /**
     * Call a stored procedure.
     *
     * @param string $functionName The name of the stored procedure to call.
     * @param array<string,mixed> $params The parameters to pass to the stored procedure.
     * @param string $schemaName The schema name of the stored procedure.
     * @param bool $skipAuth Whether to skip authentication.
     * @return PostgrestResponse|PromiseInterface<PostgrestResponse> The response from the PostgREST server.
     */
    public function call(
        string $functionName,
        array $params = [],
        string $schemaName = 'public',
        bool $skipAuth = false,
    ): PostgrestResponse|PromiseInterface;

    /**
     * Run a query.
     *
     * @param PostgrestRequestBuilder $requestBuilder The request builder.
     * @param bool $skipAuth Whether to skip authentication.
     * @return PostgrestResponse|PromiseInterface<PostgrestResponse> The response from the PostgREST server.
     */
    public function run(
        PostgrestRequestBuilder $requestBuilder,
        bool $skipAuth = false
    ): PostgrestResponse|PromiseInterface;
}
